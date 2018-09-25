<?php

class Rl_Config {

	/**
	 * READ CONFIG
	 * Reads JSON configuration file(s)
	 *
	 * @param string $file
	 * @param string $path
	 *
	 * @return array|mixed|object
	 * @throws \Exception
	 */
	public static function read( $file, $path ) {
		$file    = $path . $file;

		$json    = ( file_exists( $file ) ) ? utf8_encode( file_get_contents( $file ) ) : false;
		$decoded = json_decode( $json, true );
		if ( json_last_error() ) throw new Exception();
		return $decoded;
	}

	/**
	 * RECURSE
	 * Gets all files of extension type within a directory and any sub-directories
	 *
	 * @param string     $path
	 * @param string     $ext         JSON at the moment, but could be XNL or...
	 *
	 * @return array
	 */
	public static function recurse( $path, $ext = 'json' ) {

		if ( ! file_exists( $path ) ) return array();

		$filed = array();

		$rdi = new \RecursiveDirectoryIterator( $path, \RecursiveDirectoryIterator::SKIP_DOTS );
		$rii = new \RecursiveIteratorIterator( $rdi, \RecursiveIteratorIterator::SELF_FIRST );

		foreach( $rii as $f ) {

			$sub = $rdi->getSubPathName();
			$newpath = ! is_dir( $path . $sub ) ? $path : $path . $sub . '/';

			// if file exists
			if ( $f->isFile() && $f->getExtension() == $ext ) {

				$fname = $f->getBasename( '.' . $ext );
				$content = self::read( $f->getFilename(), $newpath );

				if ( is_dir( $path . $sub ) ) {
					$filed[ $sub ][ $fname ] = $content;
				} else {
					$filed[ $fname ] = $content;
				}
			}
		}

		return $filed;
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
			// doesn't have an "id" field set to the value sought; this will only work if the
			// array with the id is the final dot in the set
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