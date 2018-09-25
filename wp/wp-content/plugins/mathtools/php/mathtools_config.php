<?php

class Mathtools_Config {

	private static $path;

	public function __construct( $path ) {
		self::$path = $path;
	}

	/**
	 * READ CONFIG
	 * Reads JSON configuration file(s)
	 *
	 * @param $file
	 *
	 * @return array|mixed|object
	 * @throws \Exception
	 */
	public static function read( $file ) {
		$file    = self::$path . '/config/' . $file . '.json';
		$json    = ( file_exists( $file ) ) ? utf8_encode( file_get_contents( $file ) ) : false;
		$decoded = json_decode( $json, true );
		if ( json_last_error() ) throw new Exception();
		return $decoded;
	}

	/**
	 * GET DOTS
	 * Turns a.b.c.d into $array[a][b][c][d]
	 *
	 * @param $dots
	 * @param $array
	 *
	 * @return mixed|bool|null
	 */
	public static function get_dots( $dots, $array ) {

		if ( ! $dots ) return false;

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
			}

			// check to see if this is an array of arrays, and that one of the child arrays
			// doesn't have an "id" field set to the value sought; this will only work right now if the
			// anonymous array is the final dot
			else {

				foreach( $data as $value ) {

					if ( is_array( $value ) && array_key_exists( 'id', $value ) && $value['id'] === $keys[ $a ] ) {
						$return = $value;
					}
				}

				if ( $return === null ) break;
			}
		}

		return $return;
	}

}