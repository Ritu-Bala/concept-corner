<?php

class Pearson_SSO_Url {
	
	/**
	 * Checks to see if the starting URL is an allowed location to process SSO
	 *
	 * @since    1.0.0
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public static function sso_url_rules( $url ) {
		
		$url_ok = false;
		$rulez = Pearson_SSO::get_option('psso_urlgroup');
		
		Pearson_SSO_Debug::ds(
			'Checks that the URL user arrived at is configured in options.',
			array( 'param url' => $url )
		);
		
		// see if there are any rules...
		if ( ! empty ( $rulez ) ) {
			
			foreach ( $rulez as $test ) {
				
				// if a rule matches, we're OK
				if ( fnmatch( $test['url'], $url ) && $test['permission'] == 'allow' ) {
					$url_ok = true;
					break;
				}
				
				// if a rule matches, but it's set to "deny" we need to bail
				else if ( fnmatch( $test['url'], $url ) && $test['permission'] == 'deny' ) {
					break;
				}
			}
		}
		
		Pearson_SSO_Debug::de(
			$url_ok ? 'URL matches a rule' : 'URL does not match any rules.',
			array( 'returning' => $url_ok ),
			$url_ok ? 'ok' : 'fail'
		);
		
		return $url_ok;
	}
	
	/**
	 * Make sure the query vars match settings
	 *
	 * @since    1.0.0
	 *           1.5.0 removed debugging notes so this can run early
	 *           1.8.0 Added debug back in...
	 *
	 * @param $query
	 *
	 * @return array|bool
	 */
	public static function sso_query_var_rules( $query ) {
		
		Pearson_SSO_Debug::ds( 'Checks the query string matches rules set in SSO options', array( 'param query' => $query ) );
		
		$o_key = Pearson_SSO::get_option('psso_query_key');
		$o_pay = Pearson_SSO::get_option('psso_query_payload');
		$o_ext = Pearson_SSO::get_option('psso_query_extra');
		
		// for ease, we're going to return an array which uses 'key' and 'payload' no matter what the keys are
		$return_query_vars = array();
		
		$query_vars = Pearson_SSO_Utilities::proper_parse_str( $query );
		
		foreach ( $query_vars as $key => $vars ) {
			
			// we found the "key"
			if ( $o_key == $key ) {
				$return_query_vars['key'] = $vars;
			}
			// we found the payload
			else if ( $o_pay == $key ) {
				$return_query_vars['payload'] = $vars;
			}
			// this is an extra var and those are not allowed unless rules say so
			else if ( $o_ext == 'deny' && $o_key != $key && $o_pay != $key ) {
				$return_query_vars = array();
				break;
			}
			else {
				$return_query_vars[ $key ] = $vars;
			}
		}
		
		// we couldn't find the key or the payload
		if ( ! isset( $return_query_vars['key'] ) || ! isset( $return_query_vars['payload'] ) ) {
			$return_query_vars = false;
		}
		
		Pearson_SSO_Debug::de(
			$return_query_vars ? 'Returning query vars' : 'Could not find key, payload, or both.',
			$return_query_vars ? $return_query_vars : array(),
			$return_query_vars ? 'ok' : 'fail'
		);
		
		return $return_query_vars;
	}
	
}