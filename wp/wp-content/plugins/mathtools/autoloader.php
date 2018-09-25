<?php
function mathtools_autoloader( $class ) {
	$base_dir = __DIR__;
	$class = strtolower( $class );
	$file = $base_dir . '/php/' . $class . '.php';
	if ( file_exists( $file ) ) require $file;
}