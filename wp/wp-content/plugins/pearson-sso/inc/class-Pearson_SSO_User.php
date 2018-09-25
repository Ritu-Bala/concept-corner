<?php

// @todo: MAKE SURE USERNAMES HAVE NO SPACES

class Pearson_SSO_User {
	
	/**
	 * @var array             $user
	 * @var null|int          $user [id]
	 * @var null|string       $user [name]
	 * @var null|string       $user [pass]
	 * @var null|string       $user [mail]
	 * @var null|string       $user [role]
	 * @var WP_User|bool|null $user [WP]
	 */
	protected static $user = array(
		'id'   => null,
		'name' => null,
		'pass' => null,
		'mail' => null,
		'role' => null,
		'WP'   => null,
	);
	
	/**
	 * Used to store the original, unmutated input from payload or Pearson Token
	 *
	 * @var array|null
	 */
	protected static $obj = null;
	
	/**
	 * Public gateway to user login
	 *
	 * @since 1.9.3 changing test var names to make process clearer
	 *
	 * @param $in
	 *
	 * @return bool|mixed|null
	 */
	public static function u( $in ) {
		
		Pearson_SSO_Debug::ds(
			'Gateway to user login',
			array( 'param $in' => $in )
		);
		
		$ret = false;
		
		// put the passed blob-o-stuff into $obj as an array
		self::$obj = is_object( $in ) ? json_decode( json_encode( $in ), true ) : $in;
		
		// get name and role from decrypted
		$args = array(
			'name' => self::get_psso_user(),
			'role' => self::get_psso_role(),
		);
		
		$ok = array(
			'args'      => false,
			'prep'      => false,
			'loguserin' => false,
		);
		
		// check for name and role
		$ok['args'] = $args['name'] !== null && $args['role'] !== null ? true : 'There was no user name and/or role';
		
		// we have name and role
		if ( $ok['args'] === true ) {
			
			// set the argument values
			foreach ( $args as $key => $val ) {
				if ( $val !== null ) {
					self::set( $val, $key );
				}
			}
			
			// try to log the user in, either method returns true if it's ok to continue, or an error message
			$ok['prep'] = self::get( 'WP' ) !== null ? self::user_exists() : self::new_user();
			
			// user was OK, see if they're logged in
			if ( $ok['prep'] === 'ok' ) {
				$ok['loguserin'] = self::should_user_log_in();
			}
			
			// user was logged in already
			if ( $ok['loguserin'] == 'no' ) {
				Pearson_SSO_Debug::d( 'User ok and logged in already', array(), 'ok' );
				$ret = self::get( 'id' );
			}
			
			// now log them in!
			else if ( $ok['loguserin'] ) {
				
				if ( current_user_can( 'manage_options' ) ) {
					Pearson_SSO_Debug::d( 'Not switching users: Current user is an admin.' );
				}
				else {
					self::do_wp_login();
				}
				$ret = self::get( 'id' );
			}
		}
		
		// set the main class user vars here
		if ( $ret ) {
			Pearson_SSO::setter( $ret, 'user', 'id' );
			Pearson_SSO::setter( self::get( 'name' ), 'user', 'username' );
			Pearson_SSO::setter( self::get( 'role' ), 'user', 'role' );
			Pearson_SSO_Log::log( 'sso_user', $ret );
		}
		
		Pearson_SSO_Debug::de(
			'Finished processing user. If failure, check $ok under details.',
			array(
				'returning' => $ret,
				'$ok' => $ok,
				'self::$user' => self::$user
			),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * Checks if user is already logged in
	 *
	 * @return bool
	 */
	private static function should_user_log_in() {
		
		$logged_in = is_user_logged_in();
		$ret = $logged_in ? ( get_current_user_id() != self::get( 'id' ) ) : true;
		
		Pearson_SSO_Debug::d(
			'Should SSO log user in?',
			array(
				'returning'  => $ret === true ? 'yes' : 'no',
				'test1' => 'Is a user logged in? ' . $logged_in,
				'test2' => 'Either: no user logged-in, or logged-in user not the payload user?' . $ret,
			)
		);
		
		return $ret === true ? 'yes' : 'no';
	}
	
	/**
	 * User exists, make sure everything is set and WP is shipshape
	 *
	 * @since 1.9.3 Added rule checks and possibility, since there may not be auto-correct, of returning false
	 *
	 * @return bool
	 */
	private static function user_exists() {
		
		Pearson_SSO_Debug::ds(
			'The user exists, checking everything is in order',
			array( 'self::$user' => self::$user )
		);
		
		$WP = self::get( 'WP' );
		
		// if there is no role set, set it to the default
		if ( self::get( 'role' ) === null )
			self::get_psso_role( '', true );
		
		// check the user role, change the WP meta in favor of the one set if there is a conflict
		if ( empty( $WP->roles ) || ! in_array( self::get( 'role' ), $WP->roles ) ) {
			
			Pearson_SSO_Debug::d( 'Role did not match recorded role', array(), 'bug' );
			self::error_fix( 'wrongrole', self::get( 'role' ) );
		}
		
		$ret = Pearson_SSO::get_errors();
		
		if ( $ret === false ) {
			
			// make a password, if we don't have one
			if ( self::get( 'pass' ) === null ) {
				self::create_password();
			}
			
			// check the password, reset if bad
			$hash = $WP->get( 'user_pass' );
			
			if ( ! wp_check_password( self::get( 'pass' ), $hash ) ) {
				
				Pearson_SSO_Debug::d( 'Password did not match recorded password', array(), 'bug' );
				self::error_fix( 'pass', self::get( 'pass' ) );
			}
		}
		
		
		
		Pearson_SSO_Debug::de(
			'Set user parameters if needed',
			array(
				'returning' => ! $ret ? 'ok' : 'fail',
				'self::$user'  => self::$user,
			)
		);
		
		// Add to log...
		Pearson_SSO_Log::log( 'sso_new', 0 );
		
		return ! $ret ? 'ok' : 'fail';
	}
	
	/**
	 * Creates a new user
	 */
	private static function new_user() {
		
		Pearson_SSO_Debug::ds(
			'New user initialization',
			array( 'self::$user' => self::$user )
		);
		
		// set the password if it isn't set
		if ( self::get( 'pass' ) === null ) {
			self::create_password();
		}
		
		// create an email if it isn't set
		if ( self::get( 'mail' ) === null ) {
			self::create_email();
		}
		
		// check to see if a user with that email exists
		$another_user = get_user_by( 'email', self::get( 'email' ) );
		$user_id      = false;
		$flag         = ! $another_user ? true : false;
		
		// a user exists with that user email
		if ( $another_user !== false ) {
			
			Pearson_SSO_Debug::d( 'A user already exists with that email.', array( 'WP_User' => $another_user, ), 'bug' );
			$flag = self::error_fix( 'fixexist', true, $another_user );
		}
		
		// create a new user
		if ( $flag !== false )
			$user_id = self::create_user();
		
		Pearson_SSO_Debug::de(
			is_numeric( $user_id ) ? 'Created user' : 'Could not create user',
			array(
				is_numeric( $user_id ) ? 'returning ok' : 'error' => $user_id,
			),
			is_numeric( $user_id ) ? 'ok' : 'fail'
		);
		
		return is_numeric( $user_id ) ? 'ok' : 'fail';
	}
	
	/**
	 * @return bool|int|\WP_Error
	 */
	private static function create_user() {
		
		Pearson_SSO_Debug::ds(
			'User creation',
			array( 'self::$user' => self::$user )
		);
		
		$user_id = wp_create_user( self::get( 'name' ), self::get( 'pass' ), self::get( 'mail' ) );
		
		// error
		if ( is_wp_error( $user_id ) ) {
			Pearson_SSO::set_error( $user_id->get_error_message() );
			$user_id = false;
		}
		
		// add new user
		else {
			
			$new_user = new WP_User( $user_id );
			$new_user->set_role( self::get( 'role' ) );
			
			self::set( $new_user, 'WP' );
			
			if ( is_multisite() ) {
				
				$all = wp_get_sites();
				$report = array();
				
				for ( $i = 0; $i < count( $all ); $i ++ ) {
					
					// add user to blog
					$add_user = add_user_to_blog( $all[ $i ]['blog_id'], $user_id, self::get( 'role', false ) );
					
					$report[ $all[ $i ]['blog_id'] ] =
						$add_user === true ? 'ok' : $add_user->get_error_message();
				}
				
				Pearson_SSO_Debug::d(
					'Multisite: Added user to all blogs',
					array(
						'name'   => self::get( 'name', false ),
						'role'   => self::get( 'role', false ),
						'email'  => self::get( 'email', false ),
						'result' => $report,
					)
				);
			}
			
			// Add to log...
			Pearson_SSO_Log::log( 'sso_new', 1 );
		}
		
		Pearson_SSO_Debug::de(
			$user_id ? 'Created user' : 'Failed to create user',
			array( 'self::$user' => self::$user ),
			$user_id ? 'ok' : 'fail'
		);
		
		return $user_id;
	}
	
	/**
	 * WordPress user login
	 */
	private static function do_wp_login() {
		Pearson_SSO_Debug::d( 'WordPress: logging in or switching user' );
		wp_clear_auth_cookie();
		wp_set_current_user( self::get( 'id' ) );
		wp_set_auth_cookie( self::get( 'id' ) );
	}
	
	/**
	 * Gets list of user roles
	 *
	 * @return bool|array
	 */
	public static function get_user_roles() {
		
		global $wp_roles;
		Pearson_SSO_Debug::ds(
			'Gets list of user roles, places into array for use in select', array( 'global $wp_roles' => $wp_roles, )
		);
		$roles = array();
		foreach ( $wp_roles->get_names() as $id => $name ) {
			if ( $id == 'administrator' ) {
				continue;
			}
			$roles[ $id ] = $name;
		}
		Pearson_SSO_Debug::de( 'Returning', array( '$roles' => $roles ), 'ok' );
		
		return $roles;
	}
	
	/**
	 * @param string $check
	 * @param bool $set
	 *
	 * @return null
	 */
	public static function get_psso_role( $check = '', $set = false ) {
		
		// set default role, or none if error should not be corrected
		$default   = Pearson_SSO::get_option( 'psso_defaultrole' );
		$all_roles = Pearson_SSO::get_option( 'psso_usergroup' );
		$rolecase  = Pearson_SSO::get_option( 'psso_defaultrolecase' );
		
		
		Pearson_SSO_Debug::ds(
			$check ? 'Confirming role against config before saving' : 'Checks payload for user roles set in config',
			array(
				'param $check' => $check,
				'self::$obj' => self::$obj,
				'config $default' => $default,
				'config $all_roles' => $all_roles,
				'config ignore $rolecase' => $rolecase,
			)
		);
		
		$role = '';
		
		// check role rules...these will contain the decrypted var to check against decrypt
		if ( ! empty( $all_roles ) && ( $check || self::$obj !== null ) ) {
			
			foreach ( $all_roles as $type ) {
				
				// this is a decryption object
				if ( ! $check ) {
					$checker = array( explode( '::', $type['payload'] ) );
					$pl = self::check_object( $checker, $rolecase );
				}
				// string
				else {
					$pl = $check;
				}
				
				// stupid friggin case setting
				$pl = $rolecase !== null ? strtolower( $pl ) : $pl;
				
				Pearson_SSO_Debug::d( 'Value being checked', array( '$pl' => $pl ), 'ok' );
				
				// check both role and value
				if (
					$pl &&
					( $pl == ( $rolecase !== null ? strtolower( $type['value'] ) : $type['value'] ) ) ||
					( $pl == ( $rolecase !== null ? strtolower( $type['role'] ) : $type['role'] ) )
				) {
					$role = $type['role'];
					Pearson_SSO_Debug::d(
						'Matched a role',
						array(
							'$type[value]' => $type['value'],
							'$type[role]' => $type['role'],
							'$role' => $role,
						),
						'ok'
					);
					break;
				}
			}
		}
		
		if ( ! $role && $default !== null ) {
			
			Pearson_SSO_Debug::d( 'Unable to find a role that matches.', array(), 'bug' );
			$role = self::error_fix( 'norole', $default );
		}
		
		if ( $set && $role ) {
			self::$set( $role, 'role' );
		}
		
		Pearson_SSO_Debug::de( '', array(
			'Returning'              => $role,
		) );
		
		return $role;
	}
	
	/**
	 * Checks to see if a user is in the payload
	 *
	 * @return bool
	 */
	public static function get_psso_user() {
		
		Pearson_SSO_Debug::ds( 'Checks user in the payload or token', array( 'self::$obj' => self::$obj ) );
		
		$uservars = Pearson_SSO::get_option( 'psso_username' );
		$usercase = Pearson_SSO::get_option( 'psso_usernamecase' );
		$user = false;
		
		foreach ( $uservars as $uv ) {
			$user = self::check_object( explode( '::', $uv ), $usercase );
			if ( $user ) {
				break;
			}
		}
		
		Pearson_SSO_Debug::de(
			$user ? 'Found user' : 'No user found in payload or token',
			array( 'returning' => $user ),
			$user ? 'ok' : 'fail'
		);
		
		return $user;
	}
	
	/**
	 * Checks multi-dimensional object
	 *
	 * @param $check
	 * @param null/int/string $case
	 *
	 * @return bool|mixed
	 */
	private static function check_object( $check, $case = null ) {
		
		Pearson_SSO_Debug::ds(
			'Checks multidimensional object',
			array( 'param $check' => $check, 'param $case' => $case, 'self::$obj' => self::$obj )
		);
		
		$item = false;
		$ob = $case !== null ? array_change_key_case( self::$obj ) : self::$obj;
		
		foreach ( $check as $ch ) {
			
			$ch = is_array( $ch ) ? $ch : (array) $ch;
			$last = array_pop( $ch );
			
			if ( ! empty( $ch ) ) {
				foreach ( $ch as $c ) {
					$ob = isset( $ob[ $c ] ) ? $ob[ $c ] : array();
					if ( empty( $ob ) ) {
						break;
					}
				}
			}
			
			// case of check item
			if ( $case !== null ) {
				$last  = strtolower( $last );
			}
			
			if ( isset( $ob[ $last ] ) ) {
				$item = $ob[ $last ];
				break;
			}
			$item = false;
		}
		
		Pearson_SSO_Debug::de( '', array( 'returning' => $item, 'self::$obj, as checked' => $ob ) );
		
		return $item;
	}
	
	
	/**
	 * Get internal user object
	 *
	 * @param $key
	 * @param $log
	 *
	 * @return mixed|null
	 */
	private static function get( $key = '', $log = true ) {
		
		// check key
		$ret = isset( self::$user[ $key ] ) && self::$user[ $key ] !== null ? self::$user[ $key ] : null;
		
		// debug
		if ( $log ) {
			Pearson_SSO_Debug::dopt( '', array( 'self::$user[&nbsp;' . $key . '&nbsp;]' => $ret ) );
		}
		
		return $ret;
	}
	
	/**
	 * @param $value
	 * @param $key
	 * @param $log
	 *
	 * @return mixed
	 */
	private static function set( $value, $key, $log = true ) {
		
		switch ( $key ) {
			
			case 'role':
				
				// if passed a role make sure it's a real role
				self::$user['role'] = self::get_psso_role( $value );
				break;
			
			case 'name':
				
				// if a name is set, we need to clear out every other value
				self::user_null();
				
				// check to see if this is an existing WP user
				self::$user['WP'] = get_user_by( 'login', $value );
				if ( self::$user['WP'] === false ) {
					self::$user['WP'] = null;
				}
				
				// set the user ID
				self::$user['id'] = self::$user['WP'] !== null ? self::$user['WP']->ID : null;
				self::$user['name'] = $value;
				break;
			
			case 'id':
				
				// if the WP key is empty, get it
				self::$user['WP'] = self::$user['WP'] !==
				null ? self::$user['WP'] : get_user_by( 'id', intval( $value ) );
				self::$user['id'] = intval( $value );
				break;
			
			case 'WP':
				
				// if the id key is empty, set it
				self::$user['id'] = self::$user['id'] !== null ? self::$user['id'] : $value->ID;
				self::$user['WP'] = $value;
				break;
			
			default:
				self::$user[ $key ] = $value;
		}
		
		if ( $log ) {
			Pearson_SSO_Debug::dset( '', array( 'self::$user[&nbsp;' . $key . '&nbsp;]' => self::$user[ $key ] ) );
		}
		
		return self::$user[ $key ];
	}
	
	/**
	 * Clears out the self::$user array
	 */
	private static function user_null() {
		$keys = array_keys( self::$user );
		foreach ( $keys as $k ) {
			self::$user[ $k ] = null;
		}
		Pearson_SSO_Debug::d( 'Cleared out the self::$user array' );
	}
	
	/**
	 * Create email
	 */
	private static function create_email() {
		
		$formula = Pearson_SSO::get_option( 'psso_useremail' );
		$name = self::get( 'name' );
		
		if ( $formula === null ) {
			$formula = '';
		}
		
		if ( strpos( $formula, '[user]' ) === false || ! $formula ) {
			$formula = 'ccsocuser+[user]@gmail.com';
		}
		
		$formula = str_replace( '[user]', $name, $formula );
		
		self::set( $formula, 'mail' );
		Pearson_SSO_Debug::d( 'Set user email', array( 'self::$user[mail]' => self::get( 'mail', false ) ) );
	}
	
	/**
	 * Creates simple password
	 */
	private static function create_password() {
		
		$pw = md5( strtolower( self::get( 'name' ) ) . "_" . strtolower( self::get( 'role' ) ) );
		Pearson_SSO_Debug::d(
			'Password created',
			array(
				'param $name' => self::get( 'name', false ),
				'param $type' => self::get( 'role', false ),
				'$pw'         => $pw,
			)
		);
		self::set( $pw, 'pass' );
	}
	
	/**
	 * @param string       $error
	 * @param string       $value
	 * @param WP_User|null $other_WP
	 *
	 * @return mixed
	 */
	private static function error_fix( $error, $value, $other_WP = null ) {
		
		Pearson_SSO_Debug::ds(
			'Checks permissions, if OK, tries to fix errors',
			array(
				'param $error' => $error,
				'param $value' => $value,
				'param $other_WP' => $other_WP ,
				'self::$obj' => self::$obj
			)
		);
		
		$fix = array(
			'norole'    => Pearson_SSO::get_option( 'psso_user_error_norole' ),
			'pass'      => Pearson_SSO::get_option( 'psso_user_error_password' ),
			'wrongrole' => Pearson_SSO::get_option( 'psso_user_error_role' ),
			'fixexist'  => Pearson_SSO::get_option( 'psso_user_error_email' ),
		);
		
		$err = array(
			'norole'    => array(
				'off' => 'Using default role when payload does not match turned off',
			),
			'pass'      => array(
				'off' => 'Resetting passwords is turned off',
			),
			'wrongrole' => array(
				'off' => 'Changing the saved role of a WP user is turned off',
			),
			'fixexist'  => array(
				'off' => 'Deleting existing users is turned off',
			),
			'protected' => array(
				'user' => 'User is protected from SSO fixes',
				'role' => 'Role cannot be altered',
			),
			'user' => 'Not a WP user object',
		);
		
		$protected = array(
			'users' => Pearson_SSO::get_option( 'psso_user_protect' ),
			'roles' => Pearson_SSO::get_option( 'psso_user_protectroles' ),
		);
		
		$WP = self::get( 'WP' );
		
		$ALTER = $other_WP ? $other_WP : $WP;
		
		// make sure the affected user is not protected
		$this_username = $ALTER !== null ? $ALTER->user_login : false;
		
		// check the protected users
		if ( ! empty( $protected['users'] ) && $this_username ) {
			foreach( $protected['users'] as $u ) {
				if ( $this_username == $u ) {
					$value = false;
					Pearson_SSO::set_error( $err['protected']['user'] );
					break;
				}
			}
		}
		
		// check protected roles
		if ( ! empty( $protected['roles'] ) && $this_username ) {
			$protected['roles'][] = 'administrator';
			$ok = array_intersect( $protected['roles'], $ALTER->roles );
			if ( ! empty( $ok ) ) {
				Pearson_SSO::set_error( $err['protected']['role'] );
				$value = false;
			}
		}
		
		// no user, die
		else if ( ! $this_username ) {
			Pearson_SSO::set_error( $err['user'] );
			$value = false;
		}
		
		// if we still have a value
		if ( $value && $fix[ $error ] == 'fix' ) {
			
			switch ( $error ) {
				
				case 'wrongrole':
					$ALTER->set_role( $value );
					break;
				
				case 'pass':
					wp_set_password( self::get( 'pass' ), $ALTER->ID );
					break;
				
				case 'fixexist':
					wp_delete_user( $ALTER->ID );
					break;
			}
		}
		
		// the setting is turned off
		else if ( $value ) {
			
			Pearson_SSO::set_error( $err[ $error ]['off'] );
			$value = false;
		}
		
		Pearson_SSO_Debug::de(
			$value ? 'Fixed' : 'Did not fix, SSO might fail',
			array( 'returning' => $value ),
			$value ? 'ok' : 'fail'
		);
		
		return $value;
	}
}