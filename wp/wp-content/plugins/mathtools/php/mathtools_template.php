<?php

class Mathtools_Template {

	private static $templates;
	private static $place;

	public function __construct( $templates ) {
		self::$templates = $templates;
	}

	/**
	 * RENDER
	 * A simple templating engine. Looks for {{var}} within the HTML array, replaces values if it can, and returns
	 * the HTML. Params are sent to class static property to be readily accessed by callback.
	 *
	 * @param $template
	 * @param array $params
	 *
	 * @return mixed|string
	 */
	public function render( $template, $params = array() ) {
		if ( ! isset( self::$templates[ $template ] ) ) return '';
		$html        = implode( '', self::$templates[ $template ] );
		$pattern     = '!(\{\{)(.+?)(\}\})!s';
		self::$place = $params;
		return preg_replace_callback( $pattern, array( __CLASS__, 'check_placeholder' ), $html );
	}

	/**
	 * CHECK PLACEHOLDER
	 * Called by the preg_replace_callback in self::render(); returns either the string if set or an empty string
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public function check_placeholder( $key ) {
		return ! empty( self::$place[ $key[2] ] ) ? self::$place[ $key[2] ] : '';
	}
}