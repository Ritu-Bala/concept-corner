<?php

class Rl_Merge {

	protected $allkeys = array();
	protected $merge;

	public function __construct( $merge = array() ) {

		// injected config merge array, if present
		$this->merge = $merge;
	}

	/**
	 * MERGE ARRAYS
	 * Merges two config arrays, using modificaton parameters sent with arrays.
	 *
	 * @param array $new
	 * @param array $existing
	 *
	 * @return mixed
	 */
	public function merge_arrays( $new, $existing ) {

		// get all keys from new and existing
		$this->allkeys = $this->allkeys( $new, $existing );

		// recursively merge settings
		$this->re_merge( $new, $existing );

		return $existing;
	}

	/**
	 * RE MERGE
	 * Recursive function to merge arrays. Called only from merge_arrays, and uses modifiers set to class property.
	 * Unless the admin flag is set, $new can modify values of $existing, but not add new keys
	 *
	 * @since 1.0
	 *
	 * @param array $new         New array, merge into existing
	 * @param array $existing    Existing array
	 */
	protected function re_merge( $new, &$existing ) {

		// step through new array
		foreach ( $new as $key => $value ) {

			// if this is an associative array or contains other arrays, or is flagged as "always recurse", recurse
			if ( $this->recurse_check( $value ) )
				$this->re_merge( $new[ $key ], $existing[ $key ] );

			// otherwise, merge the values if they pass the key check
			else {
				// check if the key exists in the existing array
				$existing_value = isset( $existing[ $key ] ) ? $existing[ $key ] : '';

				// filter placeholders as final step and set existing array to new value
				$existing[ $key ] = Rl_Strings::filter_placeholder( $existing_value, $value );
			}
		}
	}

	/**
	 * RECURSE CHECK
	 * True/false -- see if array needs to be re-merged. Checks are ordered least important to most important.
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	protected function recurse_check( $value ) {
		$return = true;
		// return false if value is not an array
		if ( ! is_array( $value ) )
			$return = false;
		// return false if this is a "plain" array
		if ( is_array( $value ) && Rl_Arrays::is_plain( $value ) )
			$return = false;
		return $return;
	}

	/**
	 * ALLKEYS
	 * Merges the keys of the two config arrays into a single, non-numeric-containing array of keys
	 *
	 * @param $new
	 * @param $existing
	 *
	 * @return array
	 */
	private function allkeys( $new, $existing ) {
		$newkeys = Rl_Arrays::multiarray_keys( $new );
		$exkeys = Rl_Arrays::multiarray_keys( $existing );
		$allkeys = array_unique( array_merge( $exkeys, $newkeys ) );
		foreach ( $allkeys as $k => $v ) {
			if ( is_numeric( $v ) )
				unset( $allkeys[$k] );
		}
		return $allkeys;
	}
}