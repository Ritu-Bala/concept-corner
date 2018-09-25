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
 * Pearson SSO Login Form Functions
 *
 * This class only concerns itself with modifying the WP login form itself.
 * Authentication and actual login take place within Pearson_SSO_Auth
 *
 *
 * @package Pearson_SSO_Login
 * @author    Roger Los <roger@rogerlos.com>
 *
 */
class Pearson_SSO_Login {
	
	/**
	 * Gets district list
	 *
	 * @param bool $placeholders
	 *
	 * @return array
	 */
	public static function get_district_list( $placeholders = true ) {
		
		Pearson_SSO_Debug::ds(
			'Gets district list',
			array( 'param $placeholders' => $placeholders )
		);
		
		$real = Pearson_SSO::get_option( 'psso_distgroup' );
		
		Pearson_SSO_Debug::d(
			'Retrieved "real" district list',
			array( '$real' => $real )
		);
		
		$districts = array();
		
		if ( $placeholders ) {
			
			$ph = self::placeholder_districts();
			
			// replace placeholder values with real values
			foreach( $ph as $pkey => $pval ) {
				foreach( $real as $rkey => $rval ) {
					if ( $rval['name'] == $pval['name'] ) {
						$ph[ $pkey ]['env']       = $rval['env'];
						$ph[ $pkey ]['providers'] = isset( $rval['providers'] ) ? $rval['providers'] : '';
						unset( $real[ $rkey ] );
					}
				}
			}
			
			// if the real array is not empty, add to the ph array
			$districts = array_merge( $ph, $real );
		}
		
		Pearson_SSO_Debug::de(
			'Returning districts list',
			array( '$districts' => $districts ),
			! empty ( $districts ) ? 'ok' : 'fail'
		);
		
		return $districts;
	}
	
	/**
	 * @return bool|array
	 */
	private static function placeholder_districts() {
		
		$list      = Pearson_SSO::get_option( 'psso_distfill' );
		$env       = 'abcdef';
		$districts = array();
		$k         = 0;
		
		if ( $list ) {
			$list = explode( "\r\n", $list );
			foreach( $list as $dist ) {
				$districts[ $k ]['name']      = $dist;
				$districts[ $k ]['env']       = $env;
				$districts[ $k ]['providers'] = '';
				$k++;
			}
		}
		
		Pearson_SSO_Debug::dopt( '', array( '$districts' => $districts ) );
		
		return $districts;
	}
	
	/**
	 * Adds district selector to login form
	 *
	 * @param $middle
	 *
	 * @return string
	 */
	public static function login_form_middle( $middle ) {
		
		Pearson_SSO_Debug::ds(
			'Modifies center part of login form',
			array( 'param $middle' => $middle )
		);
		
		$psso_auth_login = Pearson_SSO::get_option( 'psso_auth_login' );
		$psso_auth_login_district_require = Pearson_SSO::get_option( 'psso_auth_login_district_require' );
		$psso_auth_login_show_district = Pearson_SSO::get_option( 'psso_auth_login_show_district' );
		
		$go = (
			$psso_auth_login == 'off' ||
			$psso_auth_login_show_district == 'off' ||
			$psso_auth_login_district_require == 'off'
		) ? false : true;
		
		$districts = self::get_district_list();
		
		if ( $go && $districts ) {
			$middle = ( $psso_auth_login_show_district == 'select' ) ?
				self::add_district_select( $districts ) : self::add_district_autocomplete( $districts );
		}
		
		Pearson_SSO_Debug::de(
			$go ? ( $districts ? 'Added districts to form' : 'No districts found; form not modified' ) : 'Form not modified',
			array( 'returning' => $middle ),
			$go && $districts ? 'ok' : 'fail'
		);
		
		return $middle;
	}
	
	/**
	 * ADD DISTRICT SELECT
	 *
	 * @param $districts
	 *
	 * @return string
	 */
	private static function add_district_select( $districts ) {
		
		Pearson_SSO_Debug::ds( 
			'Builds district select',
			array( 'param $districts' => $districts )
		);

		$select = '<select id="district" name="district"><option value="">Choose a School District:</option>';
		$providers = '<div id="providers">';
		
		// cycle through districts
		foreach ( $districts as $district ) {
			
			// add district choice to select box
			$select .= '<option class="providers-option" value="' . $district['env'] . '">' .
				$district['name'] . '</option>';
			
			// add a providers radio button set if warranted
			if ( isset( $district['providers'] ) && $district['providers'] ) {
				$providers .= self::providers_radio_control( $district );
			}
		}
		
		$select .= '</select>';
		$providers .= '</div>';
		
		Pearson_SSO_Debug::de(
			'Built district select control',
			array( 'returning' => $select . $providers )
		);
		
		return $select . $providers;
	}
	
	/**
	 * ADD DISTRICT AUTOCOMPLETE
	 *
	 * @param $districts
	 *
	 * @return string
	 */
	private static function add_district_autocomplete( $districts ) {

		Pearson_SSO_Debug::ds(
			'Builds district auto-complete field',
			array( 'param $districts' => $districts )
		);
		
		$psso_auth_login_district = Pearson_SSO::get_option( 'psso_auth_login_district' );
		$extra_fields = '';
		$title = $psso_auth_login_district ? $psso_auth_login_district : 'Enter your School District:';
		
		$new_districts = array();

		// cycle through districts
		foreach ( $districts as $district ) {

			$new_districts[] = array( 'label' => $district['name'], 'value' => $district['env'] );

			// add a providers radio button set if warranted
			if ( isset( $district['providers'] ) && $district['providers'] ) {
				$extra_fields .= self::providers_radio_control( $district );
			}
		}

		wp_localize_script( Pearson_SSO::slug() . '-districts-script', 'pssoConfDist', $new_districts );
		
		// add field to form
		$autocomplete = '<p class="login-username">';
		$autocomplete .= '<label for="district-ac">' . $title . '</label>';
		$autocomplete .= '<input id="district-ac" name="district-ac" class="sso-input">';
		$autocomplete .= '<input type="hidden" id="district" name="district">';
		$autocomplete .= '</p>';

		// add in the providers
		$autocomplete .= $extra_fields;
		
		Pearson_SSO_Debug::de(
			'Built auto-complete',
			array( 'returning' => $autocomplete ),
			'ok'
		);
		
		return $autocomplete;
	}
	
	/**
	 * Radio control for providers
	 *
	 * @param $district
	 *
	 * @return string
	 */
	private static function providers_radio_control( $district ) {
		
		Pearson_SSO_Debug::ds(
			'Adds providers radio control to login form',
			array( 'param $district' => $district )
		);
		
		$radio = '<div class="providers-radio" data-env="' . $district['env'] . '">';
		
		// split providers at new lines
		$items = explode( "\r\n", $district['providers'] );
		foreach ( $items as $item ) {
			$i = explode( ',', $item );
			$radio .= '<input class="providers-item" type="radio" name="providers[' . $district['env'] .
				']" value="' . $i[1] . '">' . $i[0] . ' ';
		}
		$radio .= '</div>';
		
		Pearson_SSO_Debug::de('Returning radio control', array( '$radio' => $radio ) );
		
		return $radio;
	}
	
	/**
	 * ADD LOGIN TOP
	 *
	 * @param $top
	 *
	 * @return bool|string
	 */
	public static function add_login_top( $top ) {
		
		Pearson_SSO_Debug::ds(
			'Modifies login form header and adds errors to form.',
			array( 'param $top' => $top )
		);
		
		$psso_auth_login_title = Pearson_SSO::get_option( 'psso_auth_login_title' );
		$psso_auth_login = Pearson_SSO::get_option( 'psso_auth_login' );
		
		$title = $psso_auth_login_title ? $psso_auth_login_title : 'Login';
		$top = $psso_auth_login == 'on' ? '<h1>' . $title . '</h1>' : $top;
		
		$errors = Pearson_SSO::get_errors();
		$error_messages = '';
		
		// error messages
		if ( $errors !== false ) {
			foreach ( $errors as $error ) {
				$error_messages .= '<p class="cc_login_error">' . $error . '</p>';
			}
		}
		
		// ugh errors via get string
		if ( isset( $_GET['e0'] ) && $_GET['e0'] ) {
			$error_messages .= '<p class="cc_login_error">' . urldecode( $_GET['e0'] ) . '</p>';
		}
		if ( isset( $_GET['e1'] ) && $_GET['e1'] ) {
			$error_messages .= '<p class="cc_login_error">' . urldecode( $_GET['e1'] ) . '</p>';
		}
		if ( isset( $_GET['e2'] ) && $_GET['e2'] ) {
			$error_messages .= '<p class="cc_login_error">' . urldecode( $_GET['e2'] ) . '</p>';
		}
		
		Pearson_SSO_Debug::de(
			$psso_auth_login == 'on' ? 'Added title' : 'Did not modify title',
			array( 'returning' => $top . $error_messages )
		);
		
		return $top . $error_messages;
	}
	
	/**
	 * @param WP_User|WP_Error $user mixed
	 * @param                  $username string
	 *
	 * @return bool
	 */
	public static function login_fail( $user, $username ) {
		
		Pearson_SSO_Debug::ds(
			'Handles login failures',
			array( 'param $user' => $user, 'param $username' => $username )
		);
		
		$referrer = '';
		$e = '';
		$c = 0;
		$to = Pearson_SSO::get_option( 'psso_auth_login_location' );
		
		if ( isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] ) {
			$referrer = $_SERVER['HTTP_REFERER'];
		}
		
		// the normal WP process returned an error
		if ( is_object( $user ) ) {
			$error = $user->get_error_code();
			if ( ! empty( $error ) ) {
				$err = str_replace( array( 'Lost your password', '?' ), '', $user->get_error_message() );
				Pearson_SSO::set_error( $err );
				$e .= '&e' . $c . '=' . urlencode( strip_tags( $err ) );
				$c ++;
			}
		}
		
		// the pearson auth login process returned an error
		if ( is_array( $user ) ) {
			foreach ( $user as $error ) {
				$e .= '&e' . $c . '=' . urlencode( strip_tags( $error ) );
				$c ++;
			}
		}
		
		$u = ( isset( $username ) && $username ) ? '&u=' . $username : '';
		$u = $e                                  ? $u . $e           : '';
		$u = $u                                  ? '?sso=1' . $u     : '';
		
		$to = $to ? $to . $u : '';
		
		$redirect = $referrer && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' );
		
		Pearson_SSO_Debug::de(
			$redirect && $to ? 'Redirecting' : 'Did nothing',
			array( $redirect && $to ? 'redirect' : 'returning' => $redirect && $to ? $to : false ),
			$redirect && $to ? 'bail' : 'fail'
		);
		
		if ( $redirect && $to ) {
			wp_redirect( $to );
			exit;
		}
		
		return false;
	}
}