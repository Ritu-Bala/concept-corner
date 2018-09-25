<?php
function sso_autoloader( $find ) {
	
	$base_dir = __DIR__;

	$dirs = array( '', 'inc', 'lib' );
	$prefixes = array( '', 'class-' );
	$classes = array( $find, strtolower( $find ) );

	foreach ( $dirs as $dir ) {
		foreach ( $prefixes as $prefix ) {
			foreach ( $classes as $class ) {

				$d = $dir ? $dir . '/' : '';
				
				/* 
				 * see if the file itself is there:
				 *   /path/to/file-to-find.php
				 */
				$file = $base_dir . '/' . $d . $prefix . $class . '.php';
				if ( file_exists( $file ) ) {
					require_once $file;
					break 3;
				}
				
				/* 
				 * see if the file is in a self-name directory itself is there:
				 *   /path/to/file-to-find/file-to-find.php
				 */
				$file = $base_dir . '/' . $dir . '/' . $class . '/' . $prefix . $class . '.php';
				if ( file_exists( $file ) ) {
					require_once $file;
					break 3;
				}
			}
		}
	}
}