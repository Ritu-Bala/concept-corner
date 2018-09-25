<?php

class Pearson_SSO_Utilities {
	
	/**
	 * Turn an array or object into a table
	 * 
	 * @since    1.0.0
	 *
	 * @param $array
	 * @param string $class
	 *
	 * @return string
	 */
	public static function array2ul( $array, $class = 'psso-var-table' ) {
		
		if ( is_array( $array ) || is_object( $array ) ) {
			
			$out = '<table class="' . $class . '">';
			
			foreach ( $array as $key => $elem ) {
				
				$out .= '<tr><td class="psso-array-key">' . $key . '</td><td class="psso-array-value">';
				
				if ( ! is_array( $elem ) && ! is_object( $elem ) ) {
					
					if ( $elem === null ) {
						$elem = '<code>null</code>';
					}
					else if ( is_bool( $elem ) ) {
						$elem = $elem ? 'true <code>bool</code>' : 'false <code>bool</code>';
					}
					else if ( empty( $elem ) ) {
						$elem = '<code>empty</code>';
					}
					else if ( is_int( $elem ) ) {
						$elem = $elem . ' <code>int</code>';
					}
					else if ( is_float( $elem ) ) {
						$elem = $elem . ' <code>float</code>';
					}
					else if ( is_string( $elem ) && substr( $elem, 0, 4 ) !== '<tab' ) {
						$elem = str_replace( "\r\n", '<br>', $elem );
						$elem = $elem . ' <code>string</code>';
					}
					$out .= $elem;
				}
				
				else {
					$out .= self::array2ul( $elem );
				}
				
				$out .= '</td></tr>';
			}
			
			$out .= '</table>';
		}
		
		else {
			$out = $array;
		}
		
		return $out;
	}
	
	/**
	 * Parses query string
	 * @thanks http://php.net/manual/en/function.parse-str.php#76792
	 *
	 * @param $str
	 *
	 * @return array
	 */
	public static function proper_parse_str($str) {
		# result array
		$arr = array();
		
		# split on outer delimiter
		$pairs = explode('&', $str);
		
		# loop through each pair
		foreach ($pairs as $i) {
			# split into name and value
			list($name,$value) = explode('=', $i, 2);
			
			# if name already exists
			if( isset($arr[$name]) ) {
				# stick multiple values into an array
				if( is_array($arr[$name]) ) {
					$arr[$name][] = $value;
				}
				else {
					$arr[$name] = array($arr[$name], $value);
				}
			}
			# otherwise, simply stick it in a scalar
			else {
				$arr[$name] = $value;
			}
		}
		
		# return result array
		return $arr;
	}
	
	/**
	 * Allows strings to be used as operators
	 *
	 * @since    1.0.0
	 *
	 * @param $v1
	 * @param $op
	 * @param $v2
	 *
	 * @return bool
	 */
	public static function _compare( $v1, $op, $v2 ) {
		$op = trim( $op );
		// if $v2 is an array, perform logical operations against all its members
		if ( is_array( $v2 ) ) {
			foreach ( $v2 as $v ) {
				$test = self::_compare( $v1, $op, $v );
				if ( $test === true ) {
					return true;
				}
			}
			return false;
		}
		// wildcards
		if ( $v2 == '*' ) {
			switch ( $op ) {
				case "=":
					return ! empty( $v1 );
				default:
					return false;
			}
		}
		if ( $v2 == '#' ) {
			switch ( $op ) {
				case "=":
					return is_numeric( $v1 );
				default:
					return false;
			}
		}
		// perform the operation asked for
		switch ( $op ) {
			case "=":
				return $v1 == $v2;
			case "not=":
			case "!=":
				return $v1 != $v2;
			case ">":
				return $v1 > $v2;
			case ">=":
				return $v1 >= $v2;
			case "<":
				return $v1 < $v2;
			case "<=":
				return $v1 <= $v2;
			case "path=":
				return fnmatch( $v2, $v1 );
			default:
				return false;
		}
	}
	
	/**
	 * Escapes strings for running against DB
	 * 
	 * @param $inp
	 *
	 * @return array|mixed
	 */
	public static function mysql_escape_mimic($inp) {
		if(is_array($inp))
			return array_map(__METHOD__, $inp);
		
		if(!empty($inp) && is_string($inp)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), 
				array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
		}
		
		return $inp;
	}
}