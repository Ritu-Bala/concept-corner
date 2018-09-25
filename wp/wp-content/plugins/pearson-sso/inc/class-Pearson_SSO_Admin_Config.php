<?php

class Pearson_SSO_Admin_Config {
	
	protected static $tokens = array();
	
	/**
	 * Returns an array, if config file exists and is parseable
	 *
	 * @param        $what
	 * @param string $path
	 * @param string $prefix
	 *
	 * @return array|mixed|null|object|string
	 */
	public static function get( $what, $path = '', $prefix = 'admin-' ) {
		
		$ret = null;
		
		// build path
		$path = $path ? $path : Pearson_SSO::file() . 'cfg/';
		
		// check for json extension
		$what = ( substr( $what, -5 ) == '.json' ) ? $what : $what . '.json';
		
		// read file
		$ret = self::read( $path . $prefix . $what );
		
		// convert tokens
		$ret = preg_replace_callback( '!("\{\{)(.+?)(\}\}")!s', array( __CLASS__, 'quoted_tokens' ), $ret );
		$ret = preg_replace_callback( '!(\{\{)(.+?)(\}\})!s', array( __CLASS__, 'tokens' ), $ret );
		
		// convert to JSON
		$ret = self::parse_json( $ret );
		
		// return
		return $ret;
	}
	
	/**
	 * Reads file
	 *
	 * @param $file
	 *
	 * @return null|string
	 */
	private static function read( $file ) {
		return ( $file !== null && file_exists( $file ) ) ? file_get_contents( $file ) : null;
	}
	
	/**
	 * Parses JSON
	 *
	 * @param $str
	 *
	 * @return array|mixed|null|object
	 */
	private static function parse_json( $str ) {
		
		// see if json can be parsed, return array
		$ret = json_decode( $str, true );
		
		// set errors if not
		if ( json_last_error() != 'JSON_ERROR_NONE' ) {
			Pearson_SSO::set_error( json_last_error() );
			$ret = null;
		}
		
		return $ret;
	}
	
	private static function quoted_tokens( $arr ) {
		return self::tokens( $arr, true );
	}
	
	
	/**
	 * Allows tokens in config, they will be switched with values in token array. Tokens are in format "{{term}}"
	 *
	 * @param array $arr
	 * @param bool $quotes
	 *
	 * @return mixed
	 */
	private static function tokens( $arr, $quotes = false ) {
		
		$t = self::token_list();
		$ret = null;
		
		$key = explode( ' ', $arr[2] );
		
		switch ( $key[0] ) {
			case 'text':
				$ret = Pearson_SSO_Admin::get_cfg( $key[0], $key[1] );
				// if an array, call the function
				if ( is_array( $ret ) ) {
					$ret = call_user_func( $ret );
				}
				break;
			case 'tabs':
				$ret = Pearson_SSO_Admin::get_cfg( $key[0], $key[1] );
				break;
			default:
				foreach( $t as $k => $v ) {
					if ( $key[0] == $k ) {
						$ret = $v;
						break;
					}
				}
		}
		
		$ret = is_array( $ret ) ? json_encode( $ret ) : ( $quotes ? '"' . $ret . '"' : $ret );
		
		return $ret === null ? '' : $ret;
	}
	
	/**
	 * hard-coded list-o-tokens
	 *
	 * @return array
	 */
	private static function token_list() {
		if ( empty( self::$tokens ) ) {
			self::$tokens = array(
				'slug' => Pearson_SSO::slug(),
				'get_user_roles' => Pearson_SSO_User::get_user_roles(),
				'cipher_select' => Pearson_SSO_Decrypt::cipher_select(),
				'cipher_options_select' => Pearson_SSO_Decrypt::cipher_options_select(),
			);
		}
		return self::$tokens;
	}
}