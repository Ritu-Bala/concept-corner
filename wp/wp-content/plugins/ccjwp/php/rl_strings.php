<?php

/*
 * STRINGS
 * Functions used to manipulate strings.
 */

class Rl_Strings {

	/**
	 * ENDS WITH
	 * Checks to see if a string ends with another string.
	 *
	 * @since 1.0
	 *
	 * @param array $args - [string]   Original string
	 *                    - [compare]  String to look for at the end of [string]
	 *
	 * @return bool
	 */
	public static function ends_with( $args = array() ) {

		if ( is_array( $args['string'] ) || is_array( $args['compare'] ) ) return false;

		$strlen = strlen( $args['string'] );
		$testlen = strlen( $args['compare'] );

		if ( $testlen > $strlen || $testlen == 0 ) return false;

		return substr_compare( $args['string'], $args['compare'], $strlen - $testlen, $testlen ) === 0;
	}

	/**
	 * MAYBE FORMAT
	 * Modifies a string sent from an array using PHP functions such as "trim" or "strtolower".
	 * Note: Does not check the included modifying function for number of parameters, etc.
	 *
	 * @param string $key           Key of array item string was sent from
	 * @param string $value         String to modify
	 * @param string $mod
	 * @param array  $format
	 *
	 * @return array
	 */
	public static function maybe_format( $key, $value, $mod, $format ) {

		// if the only array is populated, and this key isn't in it, return as is
		if ( ! empty( $format['only'] ) && ! in_array( $key, $format['only'], true ) ) return $value;

		// if not in exceptions array as a plain value
		if ( ! in_array( $key, $format['never'], true ) && ! is_numeric( $key ) ) {

			// allow shorter lowercase
			if ( $mod == 'lower' ) { $mod = 'strtolower'; }

			// change the values
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $v ) {
					$value[$k] = $mod( $v );
				}
			} else {
				$value = $mod( $value );
			}
		}

		return $value;
	}
}