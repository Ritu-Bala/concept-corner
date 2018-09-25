<?php

class Pearson_SSO_Payload {
	
	protected static $rule_count = 0;
	
	/**
	 * Ensure payload vars declared critical are present
	 *
	 * @todo: changed how vars were saved
	 *
	 *
	 *
	 * @since    1.0.0
	 *
	 * @param $decrypted
	 *
	 * @return bool
	 */
	public static function sso_critical_vars( $decrypted ) {
		
		Pearson_SSO_Debug::ds(
			'Ensures payload contains any configured critical vars',
			array( 'param decrypted' => $decrypted )
		);
		
		$present = true;
		$o_payvar = Pearson_SSO::get_option('psso_payvargroup');
		$oops = '';
		
		// check to see if there are any critical variables
		if ( ! empty( $o_payvar ) ) {
			
			foreach ( $o_payvar as $cv ) {
				
				$here      = false;
				$critical  = false;
				$case      = false;
				
				if ( ! empty( $cv['atts']) ) {
					foreach ( $cv['atts'] as $att ) {
						${ $att } = true;
					}
				}
				
				foreach ( $decrypted as $key => $item ) {
					
					// if we're supposed to ignore case, set both to lower
					if ( $case ) {
						$key = strtolower( $key );
						$cv['var'] = strtolower( $cv['var'] );
					}
					
					if ( $key == $cv['var'] ) {
						$here = true;
					}
				}
				
				if ( $here === false && $critical ) {
					$oops .= $cv . ' ';
					$present = false;
				}
			}
		}
		
		Pearson_SSO_Debug::de(
			$present ? 'Critical vars present, if configured' : 'Critical vars missing',
			array( $present ? 'returning' : 'missing' => $present ? true : $oops ),
			$present ? 'ok' : 'fail'
		);
		
		return $present;
	}
	
	/**
	 * Set cookies
	 *
	 * @since    1.0.0
	 *           1.5.0 Added ability to have [role] add user role
	 *           1.9.3 Checked for non-removed placeholders (ie, var wasn't in payload)
	 *
	 * @param $decrypted
	 *
	 * @return bool
	 */
	public static function sso_cookies( $decrypted ) {
		
		Pearson_SSO_Debug::ds(
			'Sets any configured cookies',
			array( 'param $decrypted' => $decrypted )
		);
		
		$cookies_ok = true;
		$cookgroup  = Pearson_SSO::get_option( 'psso_cookiegroup' );
		$user       = Pearson_SSO::getter( 'user' );
		
		// check to see if there are any cookies in options
		if ( ! empty( $cookgroup ) ) {
			
			foreach ( $cookgroup as $cookie ) {
				
				// there is no content for the cookie
				if ( ! isset( $cookie['name'] ) || ! isset( $cookie['content'] ) ) {
					continue;
				}
				
				// new parameter, protect un-updated configs
				$cookie['critical'] = isset( $cookie['critical'] ) ? $cookie['critical'] : 'no';
				
				// parse in values for placeholders
				foreach ( $decrypted as $key => $value ) {
					$cookie['name']    = str_replace( '[' . $key . ']', $value, $cookie['name'] );
					$cookie['content'] = str_replace( '[' . $key . ']', $value, $cookie['content'] );
				}
				
				// parse in values for role
				if ( isset( $user['role'] ) && $user['role'] ) {
					$cookie['name']    = str_replace( '[role]', $user['role'], $cookie['name'] );
					$cookie['content'] = str_replace( '[role]', $user['role'], $cookie['content'] );
				}
				else {
					$cookie['name']    = str_replace( '[role]', 'test', $cookie['name'] );
					$cookie['content'] = str_replace( '[role]', 'test', $cookie['content'] );
				}
				
				// for names, get rid of spaces
				$cookie['name'] = str_replace( ' ', '', $cookie['name'] );
				
				// set to expire in a week if no expires given
				if ( ! isset( $cookie['expires'] ) ) {
					$expires = 24 * 7 * ( time() + 3600 );
				}
				else if ( isset( $cookie['expires'] ) && (int) $cookie['expires'] == 0 ) {
					$expires = 24 * 7 * ( time() + 3600 );
				}
				else {
					$expires = (int) $cookie['expires'] * ( time() + 3600 );
				}
				
				// set cookie
				$set = strpos( $cookie['name'], '[' ) === false && strpos( $cookie['content'], '[' ) === false ?
					setcookie( $cookie['name'], $cookie['content'], $expires ) : false;
				
				if ( $set !== true && $cookie['critical'] == 'yes' ) {
					$cookies_ok = false;
				}
				
				Pearson_SSO_Debug::d(
					$set ? 'Set cookie' : 'Could not set cookie',
					array( 'name' => $cookie['name'], 'content' => $cookie['content'], 'expires' => $expires ),
					$set ? 'ok' : 'fail'
				);
				
			}
		}
		
		Pearson_SSO_Debug::de(
			$cookies_ok ? 'Set or skipped cookies' : 'Failed to set critical cookies',
			array( 'returning' => $cookies_ok ),
			$cookies_ok ? 'ok' : 'fail'
		);
		
		return $cookies_ok;
	}
	
	/**
	 * Set the environment var
	 *
	 * @since 1.8.0
	 *
	 * @param $decrypted
	 *
	 * @return bool|null|string
	 */
	public static function sso_track( $decrypted ) {
		
		Pearson_SSO_Debug::ds(
			'Sets the track, testing decrypted against configured options',
			array( 'param $decrypted' => $decrypted )
		);
		
		$ret    = '';
		$var    = Pearson_SSO::get_option('psso_auth_api_config_payload_var');
		$checks = Pearson_SSO::get_option('psso_trackconfirm');
		
		if ( $var && isset( $decrypted->{$var} ) && ! empty( $checks ) ) {
			
			$en = null;
			
			foreach( $checks as $check ) {
				
				switch ( $check['chk'] ) {
					
					case 'file':
						Pearson_SSO_Debug::d( 'Check: Using file' );
						$en = Pearson_SSO::$Auth->sso_auth_env( $decrypted );
						$en = empty( $en ) ? null : $en;
						break;
					
					case 'list':
						Pearson_SSO_Debug::d( 'Check: Using district list' );
						$dist = Pearson_SSO_Login::get_district_list();
						if ( $dist !== null ) {
							foreach ( $dist as $dis ) {
								if ( $dis['env'] == $decrypted->{$var} ) {
									$en = $dis['env'];
									break;
								}
							}
						}
						$en = empty( $en ) ? null : $en;
						break;
					
					case 'always':
						Pearson_SSO_Debug::d( 'Check: No confirmation needed' );
						$en = isset( $decrypted->{$var} ) ? $decrypted->{$var} : null;
						break;
					
					case 'off':
						Pearson_SSO_Debug::d( 'Check: Tracking is off' );
						$en = '';
						break;
				}
				
				if ( $en !== null ) break;
			}
			
			$ret = $en === null ? '' : $en;
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Set track' : 'Did not set track',
			array( 'returning' => $ret ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * See what the routing rules say for SSO
	 *
	 * @since    1.0.0
	 *
	 * @param $decrypted
	 * @param $path
	 *
	 * @return string
	 */
	public static function sso_routing( $decrypted, $path ) {
		
		Pearson_SSO_Debug::ds(
			'Sets route given decrypted vars and path',
			array( 'param $decrypted' => $decrypted, 'path' => $path ) );
		
		$psso_ruleerror = Pearson_SSO::get_option('psso_ruleerror');
		$default_route  = ! empty( $psso_ruleerror ) ? $psso_ruleerror : '/';
		$rulez          = Pearson_SSO::get_option('psso_rulegroup');
		
		// set the route
		$router = ! empty( $rulez ) ? self::routing_rules( $decrypted, $path, $rulez, $default_route ) : $default_route;
		
		Pearson_SSO_Debug::de(
			'Route set,',
			array(
				'fallback' => $default_route,
				'rules processed' => self::$rule_count . ' of ' . count( $rulez ),
				'returning' => $router
			)
		);
		
		return $router;
	}
	
	/**
	 * Checks the routing rules
	 *
	 * @param $decrypted
	 * @param $path
	 * @param $rulez
	 * @param $router
	 *
	 * @return mixed
	 */
	private static function routing_rules( $decrypted, $path, $rulez, $router ) {
		
		Pearson_SSO_Debug::ds(
			'Routing rules',
			array(
				'param $decrypted' => $decrypted,
				'param $path'      => $path ,
				'param $rulez'     => $rulez,
				'param $router'    => $router,
			)
		);
		
		self::$rule_count = 0;
		$report = array();
		
		foreach ( $rulez as $route ) {
			
			self::$rule_count++;
			
			// there is no content for the route
			if ( ! isset( $route['rule'] ) || ! isset( $route['go'] ) ) {
				$report[ self::$rule_count ] = 'No content for rule, skipping';
				continue;
			}
			
			// parse in values for placeholders
			foreach ( $decrypted as $key => $value ) {
				$route['rule'] = str_replace( '[' . $key . ']', $value, $route['rule'] );
				$route['go']   = str_replace( '[' . $key . ']', $value, $route['go'] );
			}
			
			// parse in path for [urlPath]
			$route['rule'] = str_replace( '[urlPath]', $path, $route['rule'] );
			$route['go']   = str_replace( '[urlPath]', $path, $route['go'] );
			
			$report[ self::$rule_count ] = $route;
			
			// does the rule match?
			if ( self::routing_rules_check( $route ) ) {
				$router = $route['go'];
				break;
			}
		}
		
		Pearson_SSO_Debug::ds(
			'Routing rules complete',
			array(
				'returning' => $router,
				'rules checked' => $report,
			)
		);
		
		return $router;
	}
	
	/**
	 * Checks the logic of the routing rule
	 *
	 * @param $route
	 *
	 * @return bool
	 */
	private static function routing_rules_check( $route ) {
		
		Pearson_SSO_Debug::ds(
			'Routing rule checker',
			array(
				'param $route' => $route,
			)
		);
		
		// logic operator array
		$logic = array( ' = ', ' not= ', ' != ', ' >= ', ' > ', ' <= ', ' < ', ' path= ' );
		
		// split rule at new lines
		$laws = explode( "\r\n", $route['rule'] );
		
		// our check of the route
		$obey = true;
		
		// loop through checks
		foreach ( $laws as $law ) {
			
			// only need to continue if obey is still true
			if ( $obey === true ) {
				
				// loop through logic operators
				foreach ( $logic as $op ) {
					
					// if the operator is present
					if ( strpos( $law, $op ) > 0 ) {
						
						// explode into variables
						$test = explode( $op, $law );
						
						// wrong number of arguments
						if ( count( $test ) != 2 ) {
							break;
						}
						
						// check second argument to see if it's an array
						if ( strpos( $test[1], '{' ) ) {
							str_replace( array( '{', '}' ), '', $test[1] );
							$test[1] = explode( ',', $test[1] );
						}
						
						// compare
						$obey = Pearson_SSO_Utilities::_compare( trim( $test[0] ), $op, trim( $test[1] ) );
					}
				}
			}
		}
		
		Pearson_SSO_Debug::de(
			'Completed check',
			array(
				'returning' => $obey,
			)
		);
		
		return $obey;
	}
}