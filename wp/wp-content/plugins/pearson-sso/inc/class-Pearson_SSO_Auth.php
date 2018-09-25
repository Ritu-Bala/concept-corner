<?php
/**
 * Pearson Single Sign On
 *
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 */

/**
 * Pearson SSO Authorization
 * @package Pearson_SSO_Auth
 * @author    Roger Los <roger@rogerlos.com>
 */
class Pearson_SSO_Auth {
	
	/**
	 * Instance of this class.
	 *
	 * @since    1.3.0
	 * @var      object
	 */
	protected static $instance = null;
	
	/**
	 * Current user array. 'current': known WP-handy info, 'session' returned info from API
	 *
	 * @since    1.3.0
	 * @var      object
	 */
	protected static $user = array(
		'current' => null,
		'session' => null,
	);
	
	/**
	 * API tokens received
	 *
	 * @var array
	 */
	protected static $tokens = array(
		'client' => null,
		'user'   => null,
	);
	
	/**
	 * Environment information: current code, current config array, config file location
	 *
	 * @var array
	 */
	protected static $env = array(
		'current'       => null,
		'config'        => null,
		'file_location' => null,
	);
	
	/**
	 * Contains all schoolnet API information
	 * @todo: the secret should not be coded here
	 * @var array
	 */
	protected static $schoolnet = array(
		'client_id'     => null,
		'client_secret' => 'C23CD56C-0FD0-4DE6-8B8F-97E4332D9EEA',
		'api_login'     => null,
		'api_token'     => null,
	);
	
	private function __construct() {
		
		Pearson_SSO_Debug::ds( 'Auth class handles transactions with Pearson' );
		
		// loads properties and gets client token
		self::set_properties();
		
		Pearson_SSO_Debug::de( '' );
	}
	
	/**
	 * Instance of this class
	 *
	 * @since    1.3.0
	 * @return bool|object|Pearson_SSO_Auth
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/**
	 * Generic getter function
	 *
	 * @param string $prop
	 * @param array $keys
	 *
	 * @return null
	 */
	public static function getter( $prop, $keys = array() ) {

		$ret = null;
		$rep = '';

		if ( isset( self::${ $prop } ) ) {

			$ret = self::${ $prop };
			$keys = ! is_array( $keys ) ? (array) $keys : $keys;

			if ( ! empty( $keys ) ) {
				$rep = '[&nbsp;' . implode('&nbsp;][&nbsp;', $keys ) . '&nbsp;]';
				
				while ( $akey = array_shift( $keys ) ) {
					$ret = array_key_exists( $akey, $ret ) ? $ret[ $akey ] : null;
					if ( $ret === null ) break;
				}
			}
		}

		Pearson_SSO_Debug::dopt( 'self::$' . $prop . $rep, array( 'self::$' . $prop . $rep => $ret ) );

		return $ret;
	}
	
	/**
	 * Setter, universal
	 * @todo: it would be better to set all vars to SSO class
	 *
	 * @since    1.8.0
	 *
	 * @param string $var
	 * @param array $keys
	 * @param mixed $value
	 */
	public static function setter( $value, $var, $keys = array() ) {

		$keys = ! is_array( $keys ) ? (array) $keys : $keys;
		$rep = empty( $keys ) ? '' : '[&nbsp;' . implode( '&nbsp;][&nbsp;', $keys ) . '&nbsp;]';

		// $keys contains the steps to the key which needs saving in the property
		if ( ! empty( $keys ) ) {
			$lkey = array_pop( $keys );
			while ( $akey = array_shift( $keys ) ) {
				if ( ! array_key_exists( $akey, self::${$var} ) ) {
					self::${$var}[ $akey ] = array();
				}
				self::${$var} = &self::${$var}[ $akey ];
			}
			self::${$var}[ $lkey ] = $value;
		}

		// no keys were sent, this is just a direct setting of a value
		else {
			self::${$var} = $value;
		}

		Pearson_SSO_Debug::dset(
			'self::$' . $var . $rep,
			array( 'self::$' . $var . $rep => $value )
		);
	}
	
	/**
	 * Set the needed properties. If they are not present, auth should fail.
	 *
	 * @since 1.3.0
	 * @return bool
	 */
	public static function set_properties() {
		
		Pearson_SSO_Debug::ds( 'Sets internal properties' );
		
		// flag for unloaded config vars
		$required = false;
		
		// set config vars
		self::setter( Pearson_SSO::get_option( 'psso_auth_client_id' ), 'schoolnet', 'client_id' );
		self::setter( Pearson_SSO::get_option( 'psso_auth_api_login' ), 'schoolnet', 'api_login' );
		self::setter( Pearson_SSO::get_option( 'psso_auth_api_token' ), 'schoolnet', 'api_token' );
		self::setter( Pearson_SSO::get_option( 'psso_auth_api_config_file' ), 'env', 'file_location' );
		
		// get the client token note, only necessary for getting non-user info from api, true = disabled
		$get_token = Pearson_SSO::get_option( 'psso_auth_token' ) == 'on' ? false : true;
		$client = self::get_client_token( $get_token );
		
		// check they all loaded
		if (
			self::$schoolnet['client_id'] &&
			self::$schoolnet['api_login'] &&
			self::$schoolnet['api_token'] &&
			self::$env['file_location']
		) {
			$required = true;
		}
		
		Pearson_SSO_Debug::de( 
			$required ? 'Properties set' : 'Failed to set one or more properties',
			array(
				'self::$schoolnet[&nbsp;client_id&nbsp;]' => self::$schoolnet['client_id'],
				'self::$schoolnet[&nbsp;api_login&nbsp;]' => self::$schoolnet['api_login'],
				'self::$schoolnet[&nbsp;api_token&nbsp;]' => self::$schoolnet['api_token'],
				'self::$env[&nbsp;file_location&nbsp;]'   => self::$env['file_location'],
				'client'                                  => $client,
			),
			$required ? 'ok' : 'fail'
		);
		
		return $required;
	}
	
	/**
	 * Sets token
	 *
	 * @param        $token
	 * @param string $which
	 *
	 * @return bool
	 */
	public static function set_token( $token, $which = 'user' ) {
		
		Pearson_SSO_Debug::ds( 
			'Sets authorization tokens',
			array( 'param $token' => $token, 'param $which' => $which )
		);

		if ( $token ) {
			$keys = is_array( $token ) ? $which : array( $which, 'access_token' );
			self::setter( $token, 'tokens', $keys );
		}

		Pearson_SSO_Debug::de(
			$token ? 'Set token' : 'Could not set token',
			array(),
			$token ? 'ok' : 'fail'
		);
		
		return $token ? true : false;
	}
	
	/**
	 * Sets the current environment
	 *
	 * @since 1.2.0
	 *
	 * @param $env
	 *
	 * @return bool
	 */
	public function set_environment( $env ) {
		
		if ( $env && is_string( $env ) ) {
			self::setter( $env, 'env', 'current' );
			return true;
		}
		return false;
	}
	
	/**
	 * GET CLIENT TOKEN
	 * Gets the client token from Pearson Auth Service
	 *
	 * @todo: is this really a universal token?
	 * @todo: is this really the app secret?
	 * @todo: this should probably be set only once every so often and grabbed from DB?
	 *
	 * @param bool $disabled
	 *
	 * @return bool|string
	 */
	private static function get_client_token( $disabled = false ) {
		
		Pearson_SSO_Debug::ds( 'Get client token.', array( 'param $disabled' => $disabled ) );
		
		$ret = false;
		
		// we don't send this error to our error container to keep it out of the login form
		$error = '';
		
		if ( ! $disabled ) {
			
			$default_error = Pearson_SSO::get_option( 'psso_error_login_general' );
			$token_error   = Pearson_SSO::get_option( 'psso_error_login_token_client' );
			
			$pea['grant_type']    = 'client_credentials';
			$pea['client_id']     = self::getter( 'schoolnet', 'client_id' );
			$pea['client_secret'] = self::getter( 'schoolnet', 'client_secret' );
			
			$ch = curl_init();
			$curl_options = array(
				CURLOPT_URL            => self::getter( 'schoolnet', 'api_login' ),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_POSTFIELDS     => http_build_query( $pea ),
				CURLOPT_POST           => 1,
				CURLOPT_HTTPHEADER     => array(
					'Accept: application/json',
					'Content-Type: application/x-www-form-urlencoded',
				),
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			);
			
			curl_setopt_array( $ch, $curl_options );
			$reply = curl_exec( $ch );
			curl_close( $ch );
			
			// if the token get did not error
			if ( isset( $reply['status'] ) && $reply['status'] != 'Error' ) {
				self::setter( json_decode( $reply, true ), 'tokens', 'client' );
				$ret = true;
			} else {
				$error = $token_error ? $token_error : $default_error;
			}
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Got token' : $disabled ? 'Disabled' : 'Could not get token',
			$ret || $disabled ? array() : array( 'errors' => $error ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	
	/**
	 * Method for talking with Pearson APIs
	 * 
	 * @param       $use
	 * @param null  $item
	 * @param array $data
	 *
	 * @return array|mixed|object
	 */
	public static function curl_pearson_api( $use, $item = null, $data = array() ) {

		Pearson_SSO_Debug::ds(
			'Works with self::{$use}, checks/sets $item, returns data from API',
			array(
				'param $item' => $item,
				'param $use'  => $use,
				'param $data' => $data,
			)
		);

		// array of results: first is results or false, second is empty or error
		$results = array();

		$e       = $use == 'env' ? 'environment' : 'token';
		$ck      = $use == 'env' ? 'current' : 'user';
		$rep     = $use == 'env' ? $use : 'user';
		$rep_key = $use == 'env' ? 'config' : 'session';
		$url     = '';

		$err = array(
			'default' => Pearson_SSO::get_option( 'psso_error_login_general' ),
			'novar'   => Pearson_SSO::get_option( 'psso_error_login_' . $e . ( $e == 'token' ? '_none' : '_no_env' ) ),
			'format'  => Pearson_SSO::get_option( 'psso_error_login_' . $e . '_sideways' ),
			'apidown' => Pearson_SSO::get_option( 'psso_error_login_' . $e . '_no_server' ),
			'apierr'  => Pearson_SSO::get_option( 'psso_error_login_' . $e . ( $e == 'token' ? '_bad_request' : '_not_json' ) ),
		);

		// if no item and none is currently set
		if ( $item === null && self::getter( $use, $ck ) === null ) {
			Pearson_SSO::set_error( ( $err['novar'] ? $err['novar'] : $err['default'] ) );
		}

		// try to set the item, if sent
		if ( $item !== null ) { 
			$s = $use == 'env' ? self::set_environment( $item ) : self::set_token( $item );
			if ( ! $s ) {
				Pearson_SSO::set_error( ( $err['format'] ? $err['format'] : $err['default'] ) );
			}
		}
		
		// get the item
		$I = self::getter( $use, $ck );

		// if we have a token, try it
		if ( $I !== null ) {

			$url = $use == 'env' ? self::getter( 'env', 'file_location' ) : self::getter( 'schoolnet', 'api_token' );
			
			if ( strstr( $url, '%' ) )  {
				$url = $use == 'env' ?  
					sprintf( $url, $I ) : sprintf( $url, self::getter( 'env', array('config','restUrl') ) );
			}
			
			$x_c = array();
			if ( $use == 'token' ) {
				$x_c = array(
					'Authorization: Bearer ' . self::getter( 'tokens', array('user','access_token' ) ),
					'Accept: application/json',
				);
			}
			
			$curl_options = array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPGET        => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_HTTPHEADER     => $x_c,
			);
			
			$C = curl_init();
			curl_setopt_array( $C, $curl_options );
			$reply = curl_exec( $C );
			
			// api appears to be down
			if ( $reply === false ) {
				Pearson_SSO::set_error( ( $err['apidown'] ? $err['apidown'] : $err['default'] ) );
			}
			
			// reply is a string, see if it decodes
			else if ( is_string( $reply ) ) {
				
				$json = json_decode( $reply, true );
				
				// check to see if json, set var if so
				if ( json_last_error() == JSON_ERROR_NONE ) {
					
					// set the property
					self::setter( $json, $rep, $rep_key );
					
					// set results
					$results = $use == 'env' ? array( 'config' => $json ) : $json;
					
					// for tokens, set additional errors
					if ( $use == 'token' && self::getter( 'user', array( 'session', 'status' ) ) == 'Error' ) {
						Pearson_SSO::set_error( ( $err['apierr'] ? $err['apierr'] : $err['default'] ) );
					}
				}
				
				// not JSON
				else {
					Pearson_SSO::set_error( ( $err['apierr'] ? $err['apierr'] : $err['default'] ) );
				}
			}
			
			// who knows what happened
			else {
				Pearson_SSO::set_error( $err['default'] );
			}
			
			curl_close( $C );
		}

		Pearson_SSO_Debug::de(
			empty( $results ) ? 'Could not set' : 'Set information',
			array(
				'returning' => $results,
				'errors' => Pearson_SSO::get_errors(),
				'file' => $url ? $url : '',
				'$I' => $I,
			),
			empty( $results ) ? 'fail' : 'ok'
		);

		return $results;
	}

	/**
	 * LOGIN CAPTURE
	 * Prototype of the login capture function
	 *
	 * @todo: move to login class
	 *
	 * @param null $user
	 * @param null $pass
	 *
	 * @return bool|array
	 */
	public function login_capture( $user = null, $pass = null ) {

		Pearson_SSO_Debug::ds(
			'Captures login form submissions',
			array(
				'param $user' => $user === null ? '[null]' : $user,
				'param $pass' => $pass === null ? '[null]' : '[password]',
				)
		);

		$ret             = false;
		$chosen_district = '';

		if ( $user !== null && $pass !== null ) {

			$username = sanitize_user( $user );
			$password = trim( $pass );
			
			$psso_auth_login = Pearson_SSO::get_option( 'psso_auth_login' );
			
			// set credentials for login attempt
			$creds = array(
				'user_login'    => $username,
				'user_password' => $password,
			);
			
			// there needs to be a district selected for auth to work
			if ( isset( $_POST['district'] ) ) {
				
				$chosen_district = sanitize_text_field( $_POST['district'] );

				// clears the district field is a fake one is entered
				$chosen_district = $chosen_district == 'abcdef' ? '' : $chosen_district;
				
				Pearson_SSO_Debug::d( 'District was set to: ' . $chosen_district );
			}
			
			// only do the rest of this if option is on
			if ( $psso_auth_login == 'on' && $chosen_district ) {
				
				if ( $this->schoolnet_user_token( $chosen_district, $username, $password ) ) {
					
					// try the token
					$data = self::curl_pearson_api( 'token' );
					
					// the authentication worked
					if ( isset( $data['status'] ) && strtolower( $data['status'] ) == 'success' ) {
						$creds = Pearson_SSO_User::u( $data );
					}
				}
			}
			
			Pearson_SSO_Log::log( 'sso_via', 'Login Form ( ' . $creds['user_login'] . ')' );
			
			$ret = $creds;
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Credentials returned' : 'User or password empty',
			array( 'returning' => array( 'user_login' => $ret['user_login'], 'user_password' => '[password]' ) ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * Get user token from Schoolnet API
	 *
	 * @param $chosen_district
	 * @param $username
	 * @param $password
	 *
	 * @return bool
	 */
	private function schoolnet_user_token( $chosen_district, $username, $password ) {
		
		Pearson_SSO_Debug::ds(
			'Authorize against schoolnet API and set user token',
			array(
				'param $chosen_district' => $chosen_district,
				'param $username' => $username,
				'param $password' => $password
			)
		);
		
		$pearson = false;
		
		// set the environment
		self::setter( $chosen_district, 'env', 'current' );
		
		$provider = isset( $_POST['providers'][ $_POST['district'] ] ) ?
			sanitize_text_field( $_POST['providers'][ $_POST['district'] ] ) : '';
		
		// set username to include provider if required
		$username = $provider ? $provider . '\\' . $username : $username;
		
		$ok           = self::curl_pearson_api( 'env' );
		$api_sekret   = self::getter( 'env', array( 'config', 'appApiSecret' ) );
		$resturl      = self::getter( 'env', array( 'config', 'restUrl' ) );
		$api_login    = self::getter( 'schoolnet', 'api_login' );
		$tokens_acc   = self::getter( 'tokens', array( 'user', 'access_token' ) );
		
		Pearson_SSO_Debug::d(
			'Configured Schoolnet Parameters',
			array(
				'$ok'         => $ok,
				'$api_sekret' => $api_sekret,
				'$resturl'    => $resturl,
				'$api_login'  => $api_login,
				'$tokens_acc' => $tokens_acc
			)
		);
		
		if ( ! empty( $ok ) ) {
			
			$pea['grant_type']    = 'password';
			$pea['username']      = $username;
			$pea['password']      = $password;
			$pea['client_id']     = self::getter( 'schoolnet', 'client_id' );
			$pea['client_secret'] = $api_sekret;
			
			Pearson_SSO_Debug::d(
				'CURL post fields',
				array(
					'$pea[grant_type]'    => $pea['grant_type'],
					'$pea[username]'      => $pea['username'],
					'$pea[password]'      => $pea['password'],
					'$pea[client_id]'     => $pea['client_id'],
					'$pea[client_secret]' => $pea['client_secret'],
				)
			);
			
			// check to see if API URL has printf controls in it
			$auth_url = strpos( "%", $api_login ) !== false  && $resturl ?
				sprintf( $api_login, $resturl ) : $resturl;
			
			$ch = curl_init();
			$curl_options = array(
				CURLOPT_URL            => $auth_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_POSTFIELDS     => http_build_query( $pea ),
				CURLOPT_POST           => 1,
				CURLOPT_HTTPHEADER     => array(
					'Authorization: Bearer ' . $tokens_acc,
					'Accept: application/json',
					'Content-Type: application/x-www-form-urlencoded',
				),
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			);
			
			curl_setopt_array( $ch, $curl_options );
			$reply = curl_exec( $ch );
			curl_close( $ch );
			
			// did we get a reply?
			if ( $reply !== false ) {
				
				$parsed = json_decode( $reply, true );
				
				// if reply isn't an error, set user token
				if ( ! isset( $parsed['status'] ) ) {
					self::setter( $parsed, 'tokens', 'user' );
					$pearson = true;
				}
				// otherwise, set error
				else {
					Pearson_SSO::set_error( $parsed['status'] );
				}
			}
		}
		
		// no environment token
		else {
			Pearson_SSO_Debug::d(
				'Could not get environment information',
				array( '$ok' => $ok ),
				'fail'
			);
		}
		
		Pearson_SSO_Debug::de(
			$pearson ? 'Set user token via Schoolnet authorization' : 'Schoolnet error',
			array( $pearson ? 'returning' : 'errors' => $pearson ? 'true' : Pearson_SSO::get_errors() ),
			$pearson ? 'ok' : 'fail'
		);
		
		return $pearson;
	}
	
	/**
	 * Pearson authorization token submit
	 * Writes to log for now
	 * @param $decrypted
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function sso_auth_token( $decrypted ) {
		
		Pearson_SSO_Debug::ds(
			'Submits authorization token',
			array( 'param $decrypted' => $decrypted )
		);
		
		$ret = false;
		
		// do we have a token?
		if ( isset( $decrypted->token ) && $decrypted->token ) {
			
			$data = self::curl_pearson_api( 'token', $decrypted->token );
			
			// the authentication worked
			if ( isset( $data['status'] ) && strtolower( $data['status'] ) == 'success' ) {
				
				// see if we can log in from the data
				$ret = Pearson_SSO_User::u( $data );
			}
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Logged in from token' : 'Could not log in from token',
			array( 'returning' => $ret ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * Pearson authorization environment variable
	 * Writes to log for now
	 * @param $decrypted
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function sso_auth_env( $decrypted ) {
		
		Pearson_SSO_Debug::ds( 'Wrapper to get environment info if passed in payload.', 
			array( 'param $decrypted' => $decrypted ) 
		);
		
		$env = isset( $decrypted->environment ) ? $decrypted->environment : null;
		$try = $env ? self::curl_pearson_api( 'env', $env ) : array();
		
		Pearson_SSO_Debug::de(
			empty( $try ) ? 'Did not find info' : 'Set environment info',
			array( 'self::$env' => $try ),
			empty( $try ) ? 'fail' : 'ok'
		);
		
		return empty( $try ) ? false : true;
	}
	
	/**
	 * Function called by our version of wp_authenticate
	 *
	 * @return mixed
	 */
	public function get_errors() {
		return Pearson_SSO::get_errors();
	}
	
	
}