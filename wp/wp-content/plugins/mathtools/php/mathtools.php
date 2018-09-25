<?php

// @todo: ability to add icon as menu trigger

class MathTools {

	/**
	 * Plugin slug
	 * @var string
	 */
	private static $slug = "mathtools";

	/**
	 * Plugin dir path
	 * @var string
	 */
	private static $path;

	/**
	 * Plugin dir URL
	 * @var string
	 */
	private static $url;

	/**
	 * JSON configuration array
	 * @var array
	 */
	private static $config;

	/**
	 * JSON tools array
	 * @var array
	 */
	private static $tools;

	/**
	 * Saved settings from WP
	 * @var array
	 */
	private static $saved = array();

	/**
	 * List of all possible fields
	 * @var array
	 */
	private static $field_list = array();

	/**
	 * List of use fields and the fields they affect
	 * @var array
	 */
	private static $use = array();

	/**
	 * Admin or public
	 * @var string
	 */
	private static $mode;

	/**
	 * Metaboxes
	 * @var
	 */
	private static $boxes;

	/**
	 * Instance of tools class
	 * @var /Mathtools_Tools
	 */
	private static $Tools;

	/**
	 * Instance of config class
	 * @var /Mathtools_Config
	 */
	private static $Config;

	/**
	 * Instance of display class
	 * @var /Mathtools_Display
	 */
	private static $Display;

	public function __construct( $root ) {

		// plugin path
		self::$path = $root;
		self::$url  = plugin_dir_url( self::$path ) . self::$slug . '/';

		// classes used by both admin and public
		self::$Config = new Mathtools_Config( self::$path );

		// general configuration
		self::$config = self::$Config->read( 'config' );

		// tools
		self::$Tools = new Mathtools_Tools( self::$Config, self::$config, self::$url );
		self::$tools = self::$Tools->get_config();

		// post type
		add_action( 'init', array( __CLASS__, 'add_custom_post' ) );

		// get saved options
		foreach( self::$config['posts'] as $post ) {
			self::$saved[ $post['id'] ] = get_option( $post['id'] . '_options' );
		}

		// public or admin?
		self::$mode = is_admin() ? 'admin' : 'public';

		// call initialization function
		self::$mode == 'admin' ? $this->admin_init() : $this->public_init();
	}

	/**
	 * PUBLIC INIT
	 * Initializes the plugin for public-facing WP
	 */
	private function public_init() {

		// add display class
		self::$Display = new Mathtools_Display( self::$config['templates'] );

		// read field list from option
		self::$field_list = get_option( 'mathtools_fields' );

		// add shortcode
		if ( isset( self::$config['shortcodes'] ) ) self::add_shortcodes();

		// enqueue scripts
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );

		// add filters
		if ( ! empty( self::$config['filters'] ) ) self::add_filters();

		// if there is CSS in the options, add it to the head
		add_action( 'wp_head', array( __CLASS__, 'add_css' ) );

		// if there is JS in the options, add it to the head
		add_action( 'wp_footer', array( __CLASS__, 'add_js' ) );
	}

	/**
	 * ADMIN INIT
	 * Initializes plugin for admin site of WP
	 */
	private static function admin_init() {

			// add CMB2
			require_once self::$path . '/inc/CMB2/init.php';

			// metaboxes
			add_action( 'cmb2_admin_init', array( __CLASS__, 'cmb' ) );

			// reset order of metaboxes
			add_action( 'admin_init', array( __CLASS__, 'metabox_user_order' ) );

			// on plugin install
			register_activation_hook( self::$path, array( __CLASS__, 'flush' ) );

			// scripts
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
	}

	public static function metabox_user_order() {
		$user_id = get_current_user_id();
		delete_user_meta( $user_id, 'meta-box-order_' . self::$slug );
		delete_user_meta( $user_id, 'meta-box-order_' . 'mathtoolsets' );
	}


	/**
	 * ADD CUSTOM POST
	 * Adds custom post types to WP
	 */
	public static function add_custom_post() {

		foreach( self::$config['posts'] as $post ) {

			register_post_type( $post['id'], $post['wp'] );

			// add any metaboxes to the array to be called by CMB
			$boxes = self::$config['assign'][ $post['id'] ];

			// @todo: reorder these by the set order

			foreach ( $boxes as $dots ) {

				// tools meta boxes
				if ( $dots === 'tools' ) {
					$toolboxes = self::$Tools->create_boxes();
					self::$boxes = array_merge( self::$boxes, $toolboxes );
				}

				// non-tools boxes
				else {
					$box = self::$Config->get_dots( $dots, self::$config );
					if ( $box !== null ) self::$boxes[ $box['id'] ] = $box;
				}
			}

			// if options are called for, add options page
			if ( isset( $post['opts'] ) && $post['opts'] ) {

				// add the tools to the tools tab
				foreach( self::$config['options']['tabs'] as $key => $tab ) {
					if ( $tab['id'] === 'tools' ) {
						self::$config['options']['tabs'][$key]['boxes'] = self::$Tools->add_to_tab();
					}
				}

				$args = array(
					'key'    => $post['id'] . '_options',
		//			'jsuri' => self::$url . 'inc/js/' . $post['id'] . '-admin.js',
					'topmenu'    => 'edit.php',
					'postslug'   => $post['id'],
					'title'  => self::$config['options']['title'],
					'tabs'   => self::$config['options']['tabs'],
				);
				Mathtools_Options::get_instance( $args );
			}
		}
	}

	/**
	 * FLUSH
	 * Adds rewrite rules
	 */
	public static function flush() {
		self::add_custom_post();
		flush_rewrite_rules();
	}

	/**
	 * CMB
	 * Adds metaboxes and fields to admin
	 */
	public static function cmb() {

		// building an array of use fields
		$usable = array();

		foreach ( self::$boxes as $box ) {

			// get fields and then remove them from array
			$fields = $box['fields'];
			unset( $box['fields'] );
			$cmb = new_cmb2_box( $box );

			// set the object type explicitly here
			if ( isset( $cmb->meta_box['show_on']['key'] ) ) $cmb->object_type( $cmb->meta_box['show_on']['key'] );
			// options fields will be returned; many dots fields will be repeated
			$optfields = array();

			// the counter starts at one to allow for the title
			$optfieldcount = 1;

			// if this is a "tool" box, check for options and add appropriate fields to matrix
			if ( isset( $box['toolflag'] ) ) {
				$optfields = self::$Tools->get_options_fields( $box['id'] );
				if ( $optfields !== null ) $fields = array_merge( $fields, $optfields['field'] );
			}

			// Set our CMB2 fields
			foreach ( $fields as $dots ) {

				if ( ! is_array( $dots ) ) {

					// get the field from the dots syntax
					$field = Mathtools_Config::get_dots( $dots, self::$config );

					// check to see if this is a group
					if ( $field['type'] === "group" ) {

						$groupfields = $field['fields'];
						unset( $field['fields'] );

						// add the group
						$cmb->add_field( $field );
						self::$field_list[] = $field['id'];

						// add group fields
						foreach ( $groupfields as $groupfield ) {
							$gfield = Mathtools_Config::get_dots( $groupfield, self::$config );
							if ( $gfield !== null ) $cmb->add_group_field( $field['id'], $gfield );
						}
					}

					// regular field
					else {

						// check to see if this is a tool, and if so, add the toolname
						if ( isset( $box['toolflag'] ) ) {

							$checker = $field['id'];

							// if option field, assign id and default from gathered options
							if ( isset( $field['tooloptflag'] ) && $field['type'] != 'title' ) {
								$field['name'] = $optfields['name'][ $optfieldcount ];
								$field['id'] .= '_' . $optfields['id'][ $optfieldcount ];
								$field['default'] = $optfields['default'][ $optfieldcount ];
								$optfieldcount++;
							}

							// check for default value
							else {
								$field['default'] = self::$Tools->check_for_default( $box['id'], $checker );
							}

							// add box to field ID
							$field['id'] .= '_' . $box['id'];

							// add classes and attributes for javascript
							if ( isset( $field['attributes']['data-use'] ) ) {
								$field['attributes']['data-use'] .= $box['id'];
							} else {
								$field['row_classes'] .= $box['id'];
							}

							// if this is an icon field, add ability to restore default icon
							if ( $checker == 'tool_icon' ) {
								$field['desc'] = str_replace( '{{icon}}', $field['default'], $field['desc'] );
								$field['desc'] = str_replace( '{{id}}', $field['id'], $field['desc'] );
							}
						}

						$cmb->add_field( $field );
						self::$field_list[] = $field['id'];

						// check for use fields and their children, and add to our use array
						if ( isset( $field['row_classes'] ) ) {
							$usable[] = array(
								'master' =>	$field['row_classes'] == 'opt-use' ? true : false,
								'value' => isset( $field['attributes']['data-radio'] ) ? $field['attributes']['data-radio'] : 'on',
								'use' => isset( $field['attributes']['data-use'] ) ? $field['attributes']['data-use'] : $field['row_classes'],
								'id' => $field['id'],
							);
						}
					}
				}
			}
		}

		// make an array of use-controlled fields
		self::make_use_array( $usable );

		// save the fields list to an option
		update_option( 'mathtools_fields', self::$field_list );
	}

	/**
	 * MAKE USE ARRAY
	 * Create an array of "use" fields which allows conditional checkboxes and radio buttons.
	 *
	 * @param $usable
	 * @since 0.1.0
	 */
	private static function make_use_array( $usable ) {
		$by_data_use = array();
		// sort the array by use
		foreach( $usable as $u ) {
			if ( $u['master'] ) {
				$by_data_use[ $u['use'] ]['master'] = $u;
			} else {
				$by_data_use[ $u['use'] ]['field'][] = $u['id'];
			}
		}
		// make the property be keyed by field id
		foreach( $by_data_use as $b ) {
			if ( isset( $b['master'] ) ) {
				self::$use[ $b['master']['id'] ] = array(
						'value' => $b['master']['value'],
						'fields' => $b['field'],
				);
			}
		}
		update_option( 'mathtools_use', self::$use );
	}

	/**
	 * PAGE SELECT
	 * Alters the page select field to show list of pages.
	 *
	 * @param $query_args
	 * @return array
	 * @since 0.1.0
	 */
	public static function page_select( $query_args ) {
		$args = wp_parse_args( $query_args, array(
			'post_type'   => 'page',
			'numberposts' => -1,
		) );
		$posts = get_posts( $args );
		$post_options = array();
		$post_options[0] = 'Select Page';
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$post_options[ $post->ID ] = $post->post_title;
			}
		}
		return $post_options;
	}


	/**
	 * CMB RENDER FILTERS
	 * Adds a callback filter to the filters field
	 *
	 * @return array
	 */
	public static function cmb_render_filters() {
		$cmb = func_get_arg( 0 );
		// get the callback
		$call = self::$config['options_cb'][ $cmb->args['id'] ];
		$filts = call_user_func( $call['source']['call'], $call['source']['params'] );
		$options = array();
		if ( isset( $filts['mathtoolsets_filter'] ) && ! empty( $filts['mathtoolsets_filter'] ) ) {
			foreach ( $filts['mathtoolsets_filter'] as $filt ) {
				$options[ $filt['filter_call'] ] = $filt['filter_name'];
			}
		}
		return $options;
	}

	/**
	 * ADD FILTERS
	 * Adds filters from the configured filters list
	 */
	private static function add_filters() {
		if ( empty( self::$saved ) ) return;
		foreach ( self::$saved as $key => $post ) {
			if ( ! isset( $post[ $key . '_filter' ] ) || empty( $post[ $key . '_filter' ] ) ) return;
			foreach ( $post[ $key . '_filter' ] as $filter ) {
				// parse the parameters
				if ( isset( $filter['filter_params'] ) ) {
					$params = explode(',', $filter['filter_params'] );
					$count = count($params) + 1;
				} else {
					$count = 0;
				}
				add_filter(
					$filter['filter_call'],
					array( __CLASS__, $filter['filter_function'] ),
					$filter['filter_priority'],
					$count
				);
			}
		}
	}

	/**
	 * ADD SCRIPTS
	 * Adds needed scripts to WP queues
	 */
	public static function add_scripts() {
		foreach ( self::$config['scripts']['js'] as $key => $script ) {
			if ( $script['mode'] == self::$mode ) {
				wp_enqueue_script(
					$key,
					self::$url . $script['path'],
					$script['dependencies'],
					CCVER,
					true
				);
			}
		}
	}

	/**
	 * ADD SHORTCODES
	 * Adds configured shortcodes to WP
	 */
	private static function add_shortcodes() {
		foreach ( self::$config['shortcodes'] as $short ) {
			add_shortcode( $short['code'], array( __CLASS__, 'shortcode' ) );
		}
	}

	/**
	 * PARSE SHORTCODES
	 * Checks incoming shortcodes and forwards them to appropriate responders
	 *
	 * @param array  $atts
	 * @param string $content
	 * @param string $tag
	 *
	 * @return string
	 */
	public static function shortcode( $atts, $content, $tag ) {

		foreach( self::$config['shortcodes'] as $shortcode ) {
			if ( $shortcode['code'] === $tag ) {
				$defaults = self::shortcode_attributes( $shortcode['atts'] );

				// check to make sure "type" is in allowed types
				$atts['type'] = isset( $atts['type'] ) ? $atts['type'] : 'grid';
				if ( ! in_array( $atts['type'], $defaults['type'] ) )
					return '';

				// get rid of array in the 'type' key
				$defaults['type'] = '';

				$atts = shortcode_atts( $defaults, $atts );

				if ( isset( $atts['type'] ) )
					return self::$Display->display( $atts );
			}
		}
		return '';
	}

	/**
	 * SHORTCODE ATTRIBUTES
	 * Parses our configured attributes
	 *
	 * @param $atts
	 *
	 * @return array
	 */
	private static function shortcode_attributes( $atts ) {
		$defaults = array();
		foreach ( $atts as $akey => $att ) {
			$defaults[ $akey ] = $att;
			if ( isset( $att['global'] ) ) {
				global ${$att['global']};
				$defaults[ $akey ] = ${$att['global']}[ $att['key'] ];
			}
		}
		return $defaults;
	}

	/**
	 * FILTER ITEM
	 * Callback for a filter which asks for single menu item to be inserted
	 *
	 * @param $content
	 * @param $grade
	 *
	 * @return mixed
	 */
	public static function filter_item( $content, $grade ) {
		return self::$Display->home_menu( $grade, 'cc_home_page_add_item' );
	}

	/**
	 * FILTER GRID
	 * Callback for filters calling for a grid
	 *
	 * @param $content
	 * @param $grade
	 *
	 * @return mixed
	 */
	public static function filter_grid( $content, $grade ) {
		$args = array(
			'type'   => 'grid',
			'grade'  => $grade,
			'filter' => '',
		);
		return self::$Display->display( $args );
	}

	/**
	 * FILTER MENU
	 * Callback for filters asking for menu
	 *
	 * @param $content
	 * @param $grade
	 *
	 * @return mixed
	 */
	public static function filter_menu( $content, $grade ) {
		$args = array(
			'type'   => 'menu',
			'grade'  => $grade,
			'filter' => 'cc_additional_header_buttons',
		);
		return self::$Display->display( $args );
	}


	/**
	 * @param string $title
	 * @param \WP_Post $page
	 *
	 * @return mixed
	 */
	public static function filter_title( $title, $page, $grade ) {

		$this_post = false;
		$check = preg_match( '#\[ *mathtools([^\]])*\]#i', $page->post_content, $matches );

		// check to see if there is a grid in the content
		if ( $check < 1 )
			return $title;

		// this is a bit rude and crude - see if there is an ID
		$id_in_code = explode( 'id=', $matches[0] );
		if ( isset( $id_in_code[1] ) ) {
			$only = explode( ' ', $id_in_code[1] );
			$toolset_id = intval( $only[0] );
			$this_post = get_post( $toolset_id );
		}
		// get id from knowing the grade
		else {
			$posts = self::$Display->all_posts_in_grade( $grade );
			if ( empty( $posts ) )
				return $title;
			$this_post = $posts[0];
		}

		if ( ! $this_post )
			return $title;

		// did they want to use the title in options?
		if (
			isset( self::$saved[ $this_post->post_type ]['grid_title_aspage'] ) &&
			self::$saved[ $this_post->post_type ]['grid_title_aspage'] == 'yes' &&
			self::$saved[ $this_post->post_type ]['grid_title']
		) {
			$title = self::$saved[ $this_post->post_type ]['grid_title'];
		}

		$meta = get_post_meta( $this_post->ID, 'grid_title_aspage');
		$meta_title = get_post_meta( $this_post->ID, 'grid_title');

		// did they want to use the title in the page itself?
		if ( isset( $meta[0] ) && $meta[0]== 'yes' && isset( $meta_title[0] ) && $meta_title[0] )
			$title = $meta_title[0];

		return $title;
	}

	/**
	 * GET PROPERTY
	 * Allows access to properties. Could be hardened with allowed list if needs be.
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public static function get_prop( $key ) {
		return  isset( self::${$key} ) ? self::${$key} : false;
	}

	/**
	 * ADD CSS
	 * Adds inline CSS to head of document
	 */
	public static function add_css() {

		// @todo: this will add all css for all post types, it should really be more aware...

		foreach( self::$saved as $saved ) {

			if ( isset( $saved['code_css'] ) && $saved['code_css'] ) {
				echo "\n";
				echo '<style type="text/css" media="screen">';
				echo "\n";
				echo $saved['code_css'];
				echo "\n";
				echo '</style>';
			}
		}
	}

	/**
	 * ADD JS
	 * Adds inline JS to foot of document
	 */
	public static function add_js() {

		// @todo: this will add all js for all post types, it should really be more aware...

		foreach( self::$saved as $saved ) {

			if ( isset( $saved['code_js'] ) && $saved['code_js'] ) {
				echo "\n";
				echo '<script>';
				echo "\n";
				echo $saved['code_js'];
				echo "\n";
				echo '</script>';
			}
		}
	}
}