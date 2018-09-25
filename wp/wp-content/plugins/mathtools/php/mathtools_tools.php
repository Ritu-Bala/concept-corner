<?php

class Mathtools_Tools {

	/**
	 * Holds the tools
	 * @var array
	 */
	private static $tools = array();

	/**
	 * Configuration array
	 * @var array
	 */
	private static $config;

	/**
	 * Configuration array
	 * @var array
	 */
	private static $url;

	// @todo: import via constructor
	private static $slug = array(
		'post' => 'mathtoolsets',
		'opt' => 'mathtoolsets_options'
	);

	/**
	 * @var /Mathtools_Config
	 */
	private static $Config;

	public function __construct( Mathtools_Config $Config, $config, $url ) {

		// import instance of configuration class
		self::$Config = $Config;

		// add the config we imported with needed fields and settings
		self::$config = $config;

		// add the config we imported with needed fields and settings
		self::$url = $url;

		// read tools from config
		self::$tools = self::$Config->read( 'tools' );

		// add icon paths to reflect actual URLs
		$this->icon_urls();
	}

	/**
	 * GET CONFIG
	 * Returns the configuration tool array
	 *
	 * @param string $tool
	 * @return array|bool|mixed|object
	 */
	public function get_config( $tool = '' ) {
		if ( $tool ) {
			if ( isset( self::$tools[ $tool ] ) ) return self::$tools[ $tool ] ;
			return false;
		}
		return self::$tools;
	}

	/**
	 * ICON URLS
	 * Resolves the URL of the standard icons
	 */
	private function icon_urls() {
		foreach( self::$tools as $key => $tool ) {
			if ( $tool['tool_icon'] )
				self::$tools[ $key ]['iconurl'] = self::$url . 'inc/images/' . $tool['tool_icon'];
		}
	}

	/**
	 * CREATE BOXES
	 * Returns the boxes array created via boxes()
	 *
	 * @return mixed
	 */
	public function create_boxes() {
		$boxes = $this->boxes();
		return $boxes[0];
	}

	/**
	 * ADD TO TAB
	 * Returns the tabs array created via boxes()
	 *
	 * @return mixed
	 */
	public function add_to_tab() {
		$boxes = $this->boxes();
		return $boxes[1];
	}

	/**
	 * BOXES
	 * Configures the parameters to build metaboxes for tools
	 *
	 * @return array
	 */
	private function boxes() {
		$boxes = array();
		$tab = array();
		foreach( self::$tools as $key => $tool ) {
			$args = array();
			// box for post
			$args['id'] = $key;
			$args['title'] = $tool['title'];
			$args['object_types'] = array( self::$slug['post'] );
			$args['fields'] = self::$config['assign']['tools'];
			$args['toolflag'] = true;
			$boxes[ $args['id'] ] = $args;
			// box for options
			unset( $args['object_types'] );
			$args['id'] .= '_options';
			$args['show_on'] = array( 'key' => 'options-page', 'value' => array( self::$slug['opt'] ) );
			$args['hookup'] = false;
			$args['fields'] = self::$config['assign']['tools_options'];
			$boxes[ $args['id'] ] = $args;
			$tab[] = $args['id'];
		}

		return array( $boxes, $tab );
	}

	/**
	 * CHECK FOR DEFAULT
	 * Checks to see if the sent key is set for a tool, and returns its value
	 *
	 * @param $tool_id
	 * @param $key
	 * @return string
	 */
	public function check_for_default( $tool_id, $key ) {
		$tool_id = $this->remove_options_string( $tool_id );
		$return = '';
		if ( $key == 'tool_icon' ) {
			$return = isset( self::$tools[$tool_id]['iconurl'] ) ? self::$tools[$tool_id]['iconurl'] : $return;
		} else {
			$return = isset( self::$tools[$tool_id]['defaults'][$key] ) ? self::$tools[$tool_id]['defaults'][$key] : $return;
		}
		return $return;
	}

	/**
	 * GET OPTIONS FIELDS
	 * Gets the options fields for the specified tool
	 *
	 * @param $box_id
	 * @return array|null
	 */
	public function get_options_fields( $box_id ) {

		$tool = $this->remove_options_string( $box_id );
		if ( empty( self::$tools[ $tool ]['options'] ) ) return null;

		$title = self::$config['assign']['toolopts_title'][0];
		$fields = self::$config['assign']['toolopts'];

		// initiate return array with title field
		$return = array(
			'field' => array( $title ),
			'id' => array( '' ),
			'name' => array( '' ),
			'default' => array( '' ),
		);

		foreach( self::$tools[ $tool ]['options'] as $key => $opt ) {

			foreach( $fields as $field ) {
				$return['field'][] = $field;
				$return['id'][] = (string) $key;
				$return['name'][] = strpos( $field, 'title' ) !== false ? $opt : 'Show "' . $opt . '""';
				$return['default'][] = strpos( $field, 'title' ) !== false ? $opt : '';
			}
		}

		return $return;
	}

	/**
	 * REMOVE OPTIONS STRING
	 * Removes the '_options' part of a field ID if it exists
	 *
	 * @param $tool_id
	 * @return string
	 */
	private function remove_options_string( $tool_id ) {
		$remove_options = explode( '_', $tool_id );
		$tool_id = $remove_options[0];
		return $tool_id;
	}
}