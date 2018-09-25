<?php

class Mathtools_Display {

	/**
	 * plugin slug
	 * @var bool|string
	 */
	private static $slug;

	/**
	 * plugin url
	 * @var bool|string
	 */
	private static $url;

	/**
	 * "mathtools_options" option
	 * @var bool|array
	 */
	private static $saved;

	/**
	 * tools part of config array
	 * @var bool|array
	 */
	private static $tools;

	/**
	 * config array
	 * @var bool|array
	 */
	private static $config;

	/**
	 * "mathtools_fields" option
	 * @var mixed|void
	 */
	private static $field_list;

	/**
	 * list of "use" fields for determining override of options values
	 * @var array
	 */
	private static $use;

	/**
	 * Hard coded taxonomy to use
	 * @var string
	 */
	private static $tax = 'cc_grade';

	/**
	 * Templates class
	 * @var /Mathtools_Templates
	 */
	private static $Templates;

	/**
	 * @var /Mathtools_Tools
	 */
	private static $Tools;
	
	public function __construct( $templates ) {
		self::$url        = Mathtools::get_prop( 'url' );
		self::$slug       = Mathtools::get_prop( 'slug' );
		self::$saved      = Mathtools::get_prop( 'saved' );
		self::$tools      = Mathtools::get_prop( 'tools' );
		self::$config     = Mathtools::get_prop( 'config' );
		self::$Tools      = Mathtools::get_prop( 'Tools' );
		self::$field_list = get_option( 'mathtools_fields' );
		self::$Templates  = new Mathtools_Template( $templates );
		self::$use        = get_option( 'mathtools_use' );
	}

	/**
	 * IS FILTER SET
	 * Checks to see if a called-for filter is allowable on this post.
	 *
	 * @param $post_id
	 * @param $filter
	 *
	 * @return bool
	 */
	private function is_filter_set( $post_id, $filter ) {
		$via = get_post_meta( $post_id, 'via_filter', true );
		if ( $via === 'never' )
			return false;
		if ( $via === 'opts' )
			return true;
		if ( $via === 'custom' ) {
			$filts = get_post_meta( $post_id, 'filter_select' );
			if ( ! in_array( $filter, $filts[0] ) )
				return false;
		}
		return true;
	}

	/**
	 * ALL POSTS IN GRADE
	 * Returns all posts in post types set by this plugin that are allowed in this grade.
	 *
	 * @param $grade
	 * @param $match
	 *
	 * @return array
	 */
	public function all_posts_in_grade( $grade, $match = array() ) {
		$posts = array();
		if ( empty( self::$saved ) )
			return $posts;
		$post_types = array_keys( self::$saved );
		foreach ( $post_types as $type ) {
			$temp = get_posts(
				array(
					'posts_per_page' => -1,
					'post_type' => $type,
					'tax_query' => array(
						array(
							'taxonomy' => self::$tax,
							'field' => 'name',
							'terms' => $grade,
						)
					)
				)
			);
			$posts = array_merge( $posts, $temp );
		}
		// optionally remove posts whose key does not match the value
		if ( ! empty( $match ) ) {
			foreach( $posts as $key => $post ) {
				$check = get_post_meta( $post->ID, $match[0] );
				if ( ! in_array( $match[1], $check ) )
					unset( $posts[$key] );
			}
		}
		return $posts;
	}

	/**
	 * IF POST IN GRADE
	 * Checks if this post is assigned to this grade
	 *
	 * @param $grade
	 * @param $post_id
	 *
	 * @return bool|\WP_Post
	 */
	private function if_post_in_grade( $grade, $post_id ) {

		$check = false;
		if ( empty( self::$saved ) )
			return $check;

		// get post types from the keys of the saved array
		$post_types = array_keys( self::$saved );
		$post = get_post( $post_id );

		// if this post type isn't in the array, bail
		if ( ! in_array( $post->post_type, $post_types ) )
			return $check;

		$taxes = get_the_terms( $post_id, self::$tax );
		if ( ! $taxes )
			return $check;

		// if we find the slug matches the grade, return the post
		foreach( $taxes as $tax ) {
			if ( $tax->slug == $grade )
				return $post;
		}

		return $check;
	}

	/**
	 * MERGED FIELD VALUES
	 * Returns an array consisting of merged values of options and post values. Non-set items are an empty string.
	 *
	 * @param $post
	 *
	 * @return array
	 */
	public function merged_field_values( $post ) {

		$merged   = array();
		$shared   = array();
		$unshared = array();

		$meta     = get_post_meta( $post->ID );
		$meta     = $this->check_use_fields( $meta );
		$opts     = self::$saved[ $post->post_type ];

		$opt_key  = '_options';

		foreach( self::$field_list as $key ) {

			// strip $opt_key from key temporarily to check for shared keys
			if ( substr( $key, ( -1 * strlen( $opt_key ) ) ) == $opt_key ) {

				$newkey = substr( $key, 0, strpos( $key, $opt_key ) );

				if ( in_array( $newkey, self::$field_list ) )
					$shared[] = $newkey;
			}
			// if the options key is in the field list, add it to shared
			else if ( in_array( $key . $opt_key, self::$field_list ) ) {
				$shared[] = $key;
			}
			// otherwise put key in unshared list
			else {
				$unshared[] = $key;
			}
		}

		$shared = array_unique( $shared );

		// for keys which are shared between options and the toolset in question
		foreach( $shared as $key ) {

			$merged[ $key ] = '';

			// see if the option was filled out as the "base" choice
			$merged[ $key ] = isset( $opts[ $key . '_options' ] ) && $opts[ $key . '_options' ] ?
				maybe_unserialize( $opts[ $key . '_options' ] ) : $merged[ $key ];

			// the string 'offal' was added by $this->check_use_fields(); it allows toolsets to remove
			// default string values entirely
			if ( isset( $meta[ $key ] ) && $meta[ $key ] === 'offal' ) {
				$merged[ $key ] = '';
			}
			// otherwise, the meta key takes priority over the options key
			else {
				$merged[ $key ] = isset( $meta[ $key ][0] ) && $meta[ $key ][0] ?
					maybe_unserialize( $meta[ $key ][0] ) : $merged[ $key ];
			}
		}

		// unique keys for either the option or the toolset (for example, filters in options)
		foreach( $unshared as $key ) {

			$merged[ $key ] = '';

			if ( isset( $meta[ $key ] ) && is_array( $meta[ $key ] ) && isset( $meta[ $key ][0] ) && $meta[ $key ][0] ) {
				$merged[ $key ] = $meta[ $key ][0];
			}

			else if ( isset( $opts[ $key ] ) && $opts[ $key ] ) {
				$merged[ $key ] = $opts[ $key ];
			}

			else if ( isset( $meta[ $key ] ) &&  $meta[ $key ] === 'offal' ) {
				$merged[ $key ] = '';
			}

			else if ( isset( $meta[ $key ] ) &&  $meta[ $key ] ) {
				$merged[ $key ] = $meta[ $key ];
			}
		}

		return $merged;
	}

	/**
	 * CHECK "USE" FIELDS
	 * Remove fields from meta array if their parent "use" field is not active.
	 *
	 * @param $meta
	 *
	 * @return mixed
	 */
	private function check_use_fields( $meta ) {
		foreach ( self::$use as $key => $value ) {

			// if the option is not explicitly set in the toolset, remove fields so options are not overwritten
			if ( ! isset( $meta[ $key ] ) || ( isset( $meta[ $key ] ) && $meta[ $key ][0] !== $value['value'] ) ) {
				foreach( $value['fields'] as $f ) {
					if ( isset( $meta[ $f ] ) )
						$meta[ $f ] = '';
				}
			}

			// if use is set and a toolset has specifically emptied a text field, we need to put in a placeholder
			// this allows "get rid of this" on specific toolsets or tools.
			else {
				foreach( $value['fields'] as $f ) {
					if ( ! isset( $meta[ $f ] ) )
						$meta[ $f ] = 'offal';
				}
			}
		}
		return $meta;
	}

	/**
	 * SORT TOOLS
	 * Sort the tools by order
	 *
	 * @param $fields
	 */
	private function sort_tools( $fields ) {
		foreach( self::$tools as $key => $tool ) {
			if ( isset( $fields[ 'tool_order_' . $key ] ) )
				self::$tools[$key]['sort'] = intval( $fields[ 'tool_order_' . $key ] );
		}
		uasort( self::$tools, function( $a, $b ) {
			return $a['sort'] - $b['sort'];
		});
	}

	/**
	 * PRUNE TOOLS
	 * Removes unwanted tools from this data set
	 *
	 * @param $fields
	 */
	private function prune_tools( $fields ) {
		foreach( self::$tools as $key => $tool ) {
			if ( $fields['tool_use_' . $key ] === 'never' )
				unset( self::$tools[$key] );
		}
	}

	/**
	 * ICONIC
	 * Gets the correct icon, or none at all, for the tool as configured
	 *
	 * @param $fields
	 * @param $tool
	 * @param string $type
	 *
	 * @return string
	 */
	private function iconic( $fields, $tool, $type = 'grid' ) {

		$return   = null;
		$standard = self::$Tools->check_for_default( $tool, 'tool_icon' );

		// The specific tool in a specific toolset has been set
		if ( $fields[ 'tool_use_' . $tool ] === 'custom' ) {

			// if the iconshow field has not been set to "follow options" return appropriate result
			switch ( $fields[ 'tool_iconshow_' . $tool ] ) {
				case 'hide':
					$return = '';
					break;
				case 'def':
					$return = $standard;
					break;
				case 'custom':
					$return = isset( $fields[ 'tool_iconfile_' . $tool ] ) ? $fields[ 'tool_iconfile_' . $tool ] : $standard;
					break;
			}
		}

		// The next order of importance is the specific toolset's preference; return nothing if set to hide
		if ( $fields['grid_tool_icons'] === 'hide' && $return === null ) {
			$return = '';
		} else if ( $return === null ) {
			$return = $standard;
			switch ( $fields[ 'tool_iconshow_' . $tool ] ) {
				case 'hide':
					$return = '';
					break;
				case 'custom':
					$return = isset( $fields[ 'tool_iconfile_' . $tool ] ) ? $fields[ 'tool_iconfile_' . $tool ] : $standard;
					break;
			}
		}

		// check that we want to show an icon in menu, use standard if none was set above
		if ( $type === 'menu' && $fields[ 'tool_menu_icon_' . $tool ] !== 'use' ) {
			$return = '';
		} else if ( $type === 'menu' && ! $return ) {
			$return = $standard;
		}

		return $return === null ? '' : $return;
	}

	/**
	 * DISPLAY
	 *
	 *
	 * @param array $args
	 *
	 * @return string
	 *
	 * @todo: grid: pop-up menu of options
	 */
	public function display( $args ) {

		$defaults = array(
			'grade'  => '',
			'type'   => 'grid',
			'tool'   => '',
			'id'     => '',
			'filter' => '',
		);

		// set the display parameters. Non-descriptive var set for code length!
		$a = wp_parse_args( $args, $defaults );

		$html = '';
		$posts = array();

		// if an id was passed and it's one of our post types, use it
		if ( $a['id'] ) {
			$post = $this->if_post_in_grade( $a['grade'], $a['id'] );
			if ( ! $post ) return $html;
			$posts[] = $post;
		}

		// otherwise, get a list of eligible toolsets for this grade, one button will be made for each
		else {
			$posts = $this->all_posts_in_grade( $a['grade'] );
			// check posts to be sure they match filter, if set
			foreach ( $posts as $key => $post ) {
				if ( $a['filter'] && ! $this->is_filter_set( $post->ID, $a['filter'] ) )
					unset( $posts[$key] );
			}
		}

		// if nothing is legit, return
		if ( empty( $posts ) ) return $html;

		foreach ( $posts as $post ) {

			$fields = $this->merged_field_values( $post );

			// sort the tools and remove unwanted tools
			$this->sort_tools( $fields );
			$this->prune_tools( $fields );

			// open
			$html .= self::$Templates->render( $a['type'] . 'open', $fields );

			// grid titles and descriptions
			if (
				isset( $fields[ $a['type'] . '_title_aspage' ] ) &&
				$fields[ $a['type'] . '_title_aspage' ] == 'no' &&
				isset( $fields[ $a['type'] . '_title' ] ) &&
				$fields[ $a['type'] . '_title' ]
			) {
				$html .= self::$Templates->render( $a['type'] . 'title', $fields );
			}

			if ( isset( $fields[ $a['type'] . '_desc' ] ) && $fields[ $a['type'] . '_desc' ] ) {
				$fields[ $a['type'] . '_desc' ] = wpautop( $fields[ $a['type'] . '_desc' ] );
				$html .= self::$Templates->render( $a['type'] . 'desc', $fields );
			}

			// menu buttons
			if ( $a['type'] == 'menu' && $fields[ $a['type'] . '_img' ] === 'use' ) {
				$fields['drop_image'] = $fields['menu_imgfile'];
				$html .= self::$Templates->render( $a['type'] . 'buttonimg', $fields );
			} else if ( $a['type'] == 'menu' ) {
				$html .= self::$Templates->render( $a['type'] . 'buttontext', $fields );
			}

			// items open
			$html .= self::$Templates->render( $a['type'] . 'itemsopen', $fields );

			// set number of columns, for menus this is a single column
			$num = 1;
			if ( $a['type'] === 'grid' && isset( $fields['grid_cols'] ) )
				$num = intval( $fields['grid_cols'] );
			if ( $num > 4 )
				$num = 4;

			$cols = array();
			$bs = array(
				'lg' => array( 12, 12, 6, 4, 3 ),
				'md' => array( 12, 12, 6, 4, 4 ),
				'sm' => array( 12, 12, 6, 6, 6 )
			);

			// need to adjust the classes to reflect the number of columns
			$boot = array(
					'lg' => $bs['lg'][ $num ],
					'md' => $bs['md'][ $num ],
					'sm' => $bs['sm'][ $num ],
					'xs' => 12,
			);

			for ( $x = 0; $x < $num ; $x++ ) {
				foreach ( $boot as $bk => $bv ) {
					$fields['grid_class_column'] = str_replace( '{' . $bk . '}', $bv, $fields['grid_class_column'] );
				}
				// grid columns
				$cols[ $x ] = self::$Templates->render( $a['type'] . 'colopen', $fields );
			}

			$c = 0;

			foreach ( self::$tools as $key => $tool ) {

				if ( $c == $num )
					$c = 0;

				// set placeholderrs
				$fields['this_title'] = $fields[ 'tool_title_'. $key ];
				$fields['this_desc']  = '';
				$fields['this_dimx']  = $tool['dim']['x'];
				$fields['this_dimy']  = $tool['dim']['y'];
				$fields['this_icon']  = $this->iconic( $fields, $key, $a['type'] );
				$fields['this_iconw'] = $fields['menu_icon_width'];
				$fields['this_iconh'] = $fields['menu_icon_height'];
				$fields['this_href']  = self::$url . $tool['path'];

				// optional description for grid
				if ( $fields['grid_tool_desc'] === 'show' )
					$fields['this_desc']  = $fields[ 'tool_desc_'. $key ];

				// grid, if same height is set, add a class to the wrapper
				if ( $fields['grid_tool_height'] === 'fixed' )
					$fields['grid_class_tool_wrap'] .= ' tools-grid-fixed';

				// item open
				$cols[ $c ] .= self::$Templates->render( $a['type'] . 'itemopen', $fields );

				// hide icon if none selected
				if ( $fields['this_icon'] )
					$cols[ $c ] .= self::$Templates->render( $a['type'] . 'itemicon', $fields );

				// render item
				$cols[ $c ] .= self::$Templates->render( $a['type'] . 'item', $fields );

				// clear title and link
				$fields['this_title'] = '';
				$fields['this_href']  = '';

				// are we showing flyouts?
				$flyouts = false;

				// for menu
				if ( $fields['menu_flyout'] === 'show' && ! empty( $tool['options'] ) )
					$flyouts = true;

				// for grid
				if ( $a['type'] === 'grid' )
					$flyouts = true;

				// render flyouts
				if ( $flyouts )
					$cols[ $c ] .= $this->flyouts( $a['type'], $tool, $fields, $key );

				$cols[ $c ] .= self::$Templates->render( $a['type'] . 'itemclose' );
				$c++;
			}

			foreach ( $cols as $k => $v ) {
				$cols[ $k ] .= self::$Templates->render( $a['type'] . 'colclose' );
			}

			$html .= implode( '', $cols );

			// menu items close
			$html .= self::$Templates->render( $a['type'] . 'itemsclose' );

			// general close
			$html .= self::$Templates->render( $a['type'] . 'close' );
		}

		return $html;
	}


	/**
	 * FLYOUTS
	 * RENDERS OPTIONS FLYOUTS OR BUTTONS
	 *
	 * @param $type
	 * @param $tool
	 * @param $fields
	 * @param $key
	 *
	 * @return string
	 */
	private function flyouts( $type, $tool, $fields, $key ) {

		$html = '';

		// grid buttons
		if ( ( $fields['grid_tool_opts'] === 'hide' || $fields['grid_tool_opts'] === 'buttons' ) && $type === 'grid' ) {

			if ( empty( $tool['options'] ) || $fields['grid_tool_opts'] === 'hide' ) {

				$fields['this_title'] = $fields['tool_title' . '_' . $key ];
				$fields['this_href'] = self::$url . $tool['path'];
				$html .= self::$Templates->render( $type . 'buttonitem', $fields );

			} else {

				$html .= $this->grid_fly_items( $tool, $fields, $key, $type, 'buttonitem' );
			}
		}

		// grid pop-up menus
		if ( $type === 'menu' || ( $type === 'grid' && $fields['grid_tool_opts'] === 'menuclick' ) ) {

			if ( $type === 'grid' ) {
				$type = 'menu';
				$fields['menu_fly_class'] = $fields['menu_ul_class'];
				$fields['menu_fly_li_class'] = $fields['menu_tool_li_class'];
				$fields['menu_fly_a_class'] = $fields['menu_tool_a_class'];
			}


			$html .= self::$Templates->render( $type . 'subitemsopen', $fields );
			$html .= $this->grid_fly_items( $tool, $fields, $key, $type, 'subitem' );
			$html .= self::$Templates->render( $type . 'subitemsclose' );
		}

		return $html;
	}

	private function grid_fly_items( $tool, $fields, $key, $type, $template ) {

		$html = '';

		foreach ( $tool['options'] as $optkey => $op ) {

			// if this isn't set, skip it
			if ( ! $fields[ 'toolopt_use_' . $optkey . '_' . $key ] )
				continue;

			$fields['this_title'] = $fields[ 'toolopt_title_' . $optkey . '_' . $key ];
			$fields['this_href']  = self::$url . $tool['path'] . '?mode=' . $optkey;

			// render the item
			$html .= self::$Templates->render( $type . $template, $fields );
		}

		return $html;
	}

	/**
	 * HOME MENU
	 * Builds the home menu item
	 *
	 * @param $grade
	 * @param string $filter
	 *
	 * @return mixed|string
	 */
	public function home_menu( $grade, $filter = '' ) {

		// get all posts across post types
		$posts = $this->all_posts_in_grade( $grade );

		if ( empty( $posts ) )
			return '';

		// check to see if any settings have this filter enabled
		foreach ( $posts as $key => $post ) {
			if ( ! $this->is_filter_set( $post->ID, $filter) )
				unset( $posts[ $key ]);
		}

		if ( empty( $posts ) )
			return '';

		$fields = $this->merged_field_values( $posts[0] );

		if ( empty( $fields['grid_page'] ) )
			return '';

		$fields['insert_link'] = str_replace( '{{grade}}', $grade, $fields['insert_link'] );

		// get the page slug
		$page = get_post( $fields['grid_page'] );

		$fields['insert_link'] = str_replace( '{{page}}', $page->post_name, $fields['insert_link'] );

		return self::$Templates->render( 'homeli', $fields );
	}
}