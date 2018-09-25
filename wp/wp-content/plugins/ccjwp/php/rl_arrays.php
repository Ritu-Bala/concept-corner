<?php

/*
 * ARRAYS
 * Functions used to manipulate arrays.
 */

class Rl_Arrays {
	
	/**
	 * FLATTEN
	 * Takes a multidimensional array and flattens it. Does not flatten non-associative arrays.
	 *
	 * @since 1.0
	 *
	 * @param array  $array   Array to flatten
	 * @param array  $mods    Optional modifiers:
	 *                        [ignore]   : array - keys to skip; item and children will be skipped
	 *                        [explode]  : array - keys whose plain arrays should also be flattened
	 *                        [dots]     : bool  - whether the explode array contains "." syntax strings
	 * @param null   $prefix  Used when recursing to create keys; cumulative
	 * @param string $sep     character to use as separator
	 *
	 * @return array
	 */
	public static function flatten( $array, $mods = array(), $prefix = null, $sep = '_' ) {
		$items = array();
		$ignore = isset( $mods['ignore'] ) ? $mods['ignore'] : array();
		$explode = isset( $mods['explode'] ) ? $mods['explode'] : array();
		$dots = isset( $mods['dots'] ) ? $mods['dots'] : false;
		// keep adding to our prefix...
		if ( $prefix )
			$prefix .= $sep;
		foreach ( $array as $key => $value ) {
			// skip if this key is in our ignore list
			if ( ! in_array( $key, $ignore ) ) {
				// recurse if this array is an associative array...
				if ( is_array( $value ) && self::is_assoc( $value ) ) {
					$items = array_merge( $items, self::flatten( $value, $mods, $prefix . $key ) );
				}
				// ...or set this endpoint to the value
				else {
					// if this key is in the "explode" array and value is an array, substitute array keys and values
					if ( in_array( $key, $explode ) && is_array( $value ) ) {
						foreach ( $value as $v ) {
							$newkey = $v;
							// the dot syntax allows us to store a.b.c.d and turn it into
							if ( $dots != false ) {
								$dotz = explode ( '.', $v );
								$newkey = array_pop( $dotz );
							}
							$items[ $prefix . $newkey ] = $v;
						}
					} else {
						$items[ $prefix . $key ] = $value;
					}
				}
			}
		}
		return $items;
	}
	
	/**
	 * UNFLATTEN ARRAY
	 * Takes an non-multi-dimensional array whose keys are separated by a common character representing a nesting
	 * structure and makes it a multidimensional array
	 *
	 * @since 1.0
	 *
	 * @param array  $array  Array to unflatten
	 * @param string $sep    Character to look for when exploding keys
	 *
	 * @return array
	 */
	public static function unflatten_array( Array $array, $sep = '_' ) {
		$items = array();
		foreach ( $array as $key => $value ) {
			// if the separator is in the key...
			if ( strpos( $key, $sep ) !== false ) {
				$temp = array();
				$keys = array_reverse( explode( $sep, $key ) );
				$next = array_shift( $keys );
				$temp[ $next ] = $value;
				foreach ( $keys as $k ) {
					$temp[ $k ][$next] = $temp[$next];
					$next = $k;
				}
				$items[ $next ] = isset( $items[ $next ] ) ? $items[ $next ] : array();
				$items[$next] = array_merge_recursive( $items[ $next ], $temp[ $next ] );
			}
			// otherwise, it's just a key/value
			else {
				$items[ $key ] = $value;
			}
		}
		return $items;
	}
	
	/**
	 * IS ASSOC
	 * Checks to see if the array uses int keys or not; returns true is any key is not an int.
	 *
	 * @since 1.0
	 *
	 * @param array $array   Array to check
	 *
	 * @return bool
	 */
	public static function is_assoc( Array $array ) {
		$return = true;
		for ( reset( $array ); is_int( key( $array ) ); next( $array ) ) {
			$return = false;
			$key = key( $array );
			if ( is_null( $key ) )
				$return = true;
		}
		if ( empty( $array ) )
			$return = false;
		return $return;
	}
	
	/**
	 * IS PLAIN
	 * Checks to see if an aray is "plain", that is, contains only values and no arrays or objects
	 *
	 * @since 1.0
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function is_plain( Array $array ) {
		$return = false;
		for ( reset( $array ); is_int( key( $array ) ); next( $array ) ) {
			$return = true;
			$key = key( $array );
			if ( ! is_null( $key ) )
				$return = false;
			if ( is_array( $array[ $key ] ) ) {
				$return = false;
				break;
			}
		}
		if ( empty( $array ) )
			$return = true;
		return $return;
	}
	
	/**
	 * ARRAY BY ORDER
	 * Sorts a multi-dimensional array by the value of items with sent key. Defaults to key 'order'.
	 * Used by usort, typically.
	 *
	 * @since 1.0
	 *
	 * @param array  $a
	 * @param array  $b
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function array_by_order( Array $a, Array $b, $key = 'order' ) {
		return $a[ $key ] - $b[ $key ];
	}
	
	/**
	 * REMOVE EMPTY
	 * Removes all empty elements from the array, recursively
	 *
	 * @since 1.0
	 *
	 * @param array   $input   Array to be "cleaned".
	 *
	 * @return array
	 */
	public static function remove_empty( $input ) {
		// recursion
		foreach ( $input as &$value ) {
			if ( is_array( $value ) )
				$value = self::remove_empty( $value );
		}
		// use array_filter and anonymous function to remove all empty items
		return array_filter(
			$input,
			function( $item ){
				// return false only if item is truly empty
				return $item !== null && $item !== '' && ! empty( $item ) ;
			}
		);
	}
	
	/**
	 * GET DOTS
	 * Turns a.b.c.d into $array[a][b][c][d]
	 *
	 * @param $dots
	 * @param $array
	 *
	 * @return bool|null
	 */
	public static function get_dots( $dots, $array ) {
		if ( ! $dots )
			return false;
		$keys = explode( '.', $dots );
		$count = count( $keys );
		$data = $array;
		$return = false;
		for ( $a = 0; $a < $count; $a++ ) {
			// if the key exists set return to value, or null if it doesn't exist
			$return = array_key_exists( $keys[ $a ], $data ) ? $data[ $keys[ $a ] ] : null;
			// narrow the data to that contained within this key
			if ( $return !== null ) {
				$data = $data[ $keys[ $a ] ];
			} else {
				break;
			}
		}
		return $return;
	}
	
	
	/**
	 * SPLIT KEYS
	 * Turns a key like foo_bar_baz into ['foo','bar','baz'] and sends it to NEST ARRAY
	 *
	 * @param $key
	 * @param $array
	 * @param $no_split
	 * @param $splitter
	 *
	 * @return array
	 */
	public static function split_keys( $key, $array, $no_split = '', $splitter = '_' ) {
		$ns = '|||||';
		if ( $no_split )
			$key = str_replace( $no_split, $ns, $key );
		$keys = explode( $splitter, $key );
		if ( $no_split ) {
			foreach( $keys as $k => $v ) {
				if ( $v == $ns )
					$keys[$k] = $no_split;
			}
		}
		return self::nest_array( $keys, $array );
	}
	
	/**
	 * NEST ARRAY
	 * Takes an array of keys ['foo','bar','baz'] and turns it into [foo][bar][baz]
	 *
	 * @param $keys
	 * @param $array
	 *
	 * @return array
	 */
	public static function nest_array( $keys, $array ) {
		if ( empty( $keys ) ) return $array;
		$first = array_shift( $keys );
		return array( $first => self::nest_array( $keys, $array ) );
	}
	
	/**
	 * REMOVE OUTER KEY
	 * Removes the outer-most key from an array: [0][foo][bar][baz] becomes [foo][bar][baz]
	 * If recurse key is added,
	 *   [recurse][0][foo][bar][baz] = [a]
	 *   [recurse][1][foo][bar][baz] = [b]
	 * becomes
	 *   [recurse][foo][bar][baz] = [a,b]
	 *
	 * @param        $split
	 * @param string $recurse  recurse key
	 *
	 * @return array
	 */
	public static function remove_outer_key( $split, $recurse = '' ) {
		$temp = array();
		foreach ( $split as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( is_numeric( $k ) ) {
					$k = key( $v );
					$val = $v[$k];
				} else {
					$val = $v;
				}
				if ( ! isset( $temp[ $k ] ) )
					$temp[$k] = array();
				$temp[$k] = $temp[$k] + $val;
			}
		}
		if ( $recurse ) {
			$re = self::remove_outer_key( $temp[ $recurse ] );
			unset( $temp[ $recurse ] );
			$temp = $temp + $re;
		}
		return $temp;
	}

	/**
	 * MULTIARRAY KEYS
	 * Return an array of the keys of a nested array
	 *
	 * @param $ar
	 *
	 * @return array
	 */
	public static function multiarray_keys( $ar ) {
		$keys = array();
		foreach( $ar as $k => $v ) {
			$keys[] = $k;
			if ( is_array( $ar[$k] ) )
				$keys = array_merge( $keys, self::multiarray_keys( $ar[$k] ) );
		}
		return $keys;
	}

	/**
	 * Recursive find and replace
	 *
	 * @link http://www.codeforest.net/quick-snip-recursive-find-and-replace
	 *
	 * @param string $find
	 * @param string $replace
	 * @param array|string $array
	 * @return mixed
	 */
	public static function rec_array_replace( $find, $replace, $array ) {
		if ( ! is_array( $array ) )
			return str_replace( $find, $replace, $array );
		$newArray = array();
		foreach ( $array as $key => $value ) {
			$newArray[ $key ] = self::rec_array_replace( $find, $replace, $value );
		}
		return $newArray;
	}
	
}