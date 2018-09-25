<?php
/**
 * Concept_Corner
 *
 * @package   Concept_Corner
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://conceptcorner.com
 * @copyright 2014 Pearson
 */

/**
 * Autoload helper classes - Props to webdevstudios
 */
spl_autoload_register( 'Concept_Corner::autoload_helpers' );

class Concept_Corner {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 * @since   1.0.0
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * The text domain when internationalizing strings
	 * @since    1.0.0
	 * @var      string
	 */
	protected $plugin_slug = 'concept-corner';

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load public-facing style sheet and JavaScript.
		//	add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		//	add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Features we don't need
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );

		// remove admin bar comments
		add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_comments' ) );

		// remove version numbers
	//	add_filter( 'style_loader_src', array( $this, 'remove_version' ), 9999 );
	//	add_filter( 'script_loader_src', array( $this, 'remove_version' ), 9999 );

		// user fields
		add_action( 'show_user_profile', array( $this, 'add_custom_user_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_custom_user_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_custom_user_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_custom_user_fields' ) );

		// query vars
		add_action( 'init', array( $this, 'cc_add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'cc_add_queryvars' ) );

		// faq
		add_shortcode( 'ccfaq', array( $this, 'cc_faq' ) );

	}

	/**
	 * Return the plugin slug.
	 * @since    1.0.0
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * Autoloads files with classes when needed
	 * @since  1.0.0
	 *
	 * @param  string $class_name Name of the class being requested
	 */
	public static function autoload_helpers( $class_name ) {
		if ( class_exists( $class_name, false ) ) {
			return;
		}

		$dir = dirname( __FILE__ );

		$file = "$dir/includes/$class_name.php";
		if ( file_exists( $file ) ) {
			@include( $file );
		}
	}

	/**
	 * Maps explore and glossary to special templates
	 */
	public static function cc_add_rewrite_rules() {
		add_rewrite_rule(
			'^explore/(.+)?$',
			'index.php?page_id=13&cc_explore=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^glossary/(.+)?$',
			'index.php?page_id=15&cc_glossary=$matches[1]',
			'top'
		);
	}

	/**
	 * Add query vars
	 *
	 * @param $query_vars
	 *
	 * @return array
	 */
	public static function cc_add_queryvars( $query_vars ) {
		$query_vars[] = 'cc_explore';
		$query_vars[] = 'cc_glossary';

		return $query_vars;
	}

	/**
	 * Remove the admin bar from public site
	 * @since    1.0.0
	 */
	public function remove_admin_bar_comments() {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}

	/**
	 * Remove version numbers
	 * @since  1.0.0
	 *
	 * @param  string $src link to check for version numbers
	 *
	 * @return string modified link without numbers
	 */
	public function remove_version( $src ) {
		if ( strpos( $src, 'ver=' ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Get the role of the current user
	 * @return mixed
	 */
	public function user_role() {
		global $current_user;
		$user_roles = $current_user->roles;
		$user_role  = array_shift( $user_roles );

		return $user_role;
	}

	/**
	 * Turn object into an array
	 *
	 * @param $object object to turn into an array
	 *
	 * @return array
	 */
	public function object_to_array( $object ) {
		if ( ! is_object( $object ) && ! is_array( $object ) ) {
			return $object;
		}

		return array_map( 'cc_objectToArray', (array) $object );
	}

	/**
	 * @param      $array
	 * @param bool $key
	 *
	 * @return array|bool
	 */
	public function reindex_array( $array, $key = false ) {
		if ( ! is_array( $array ) || $key === false || is_array( $key ) ) {
			return false;
		} else {
			$new = array();
			foreach ( $array as $v ) {
				foreach ( array_keys( $v ) as $ky ) {

					// Any key with 'cc_' as the initial string, turn into an array
					if ( strpos( $ky, "cc_" ) === 0 ) {
						$v[ $ky ] = explode( ',', $v[ $ky ] );
					}

					// image_meta needs to be unserialized
					if ( $ky == 'image_meta' || $ky == 'thumbnail_meta' ) {
						$v[ $ky ] = maybe_unserialize( $v[ $ky ] );
					}

				}

				$new_key         = $v[ $key ];
				$new[ $new_key ] = $v;

			}

			return $new;
		}
	}

	/**
	 * @param $object
	 * @param $props
	 */
	public function sort_object( &$object, $props ) {
		usort( $object, function ( $a, $b ) use ( $props ) {
			for ( $i = 1; $i < count( $props ); $i ++ ) {
				if ( $a->$props[ $i - 1 ] == $b->$props[ $i - 1 ] ) {
					return $a->$props[ $i ] < $b->$props[ $i ] ? 1 : - 1;
				}
			}

			return $a->$props[0] > $b->$props[0] ? 1 : - 1;
		} );
	}

	public function array_msort($array, $cols) {
		$colarr = array();
		foreach ($cols as $col => $order) {
			$colarr[$col] = array();
			foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		}
		$eval = 'array_multisort(';
		foreach ($cols as $col => $order) {
			$eval .= '$colarr[\''.$col.'\'],'.$order.',';
		}
		$eval = substr($eval,0,-1).');';
		eval($eval);
		$ret = array();
		foreach ($colarr as $col => $arr) {
			foreach ($arr as $k => $v) {
				$k = substr($k,1);
				if (!isset($ret[$k])) $ret[$k] = $array[$k];
				$ret[$k][$col] = $array[$k][$col];
			}
		}
		return $ret;
	}


	static public function sortArrayofObjectByProperty( $array, $property ) {
		$cur = 1;
		$stack[1]['l'] = 0;
		$stack[1]['r'] = count($array)-1;

		do
		{
			$l = $stack[$cur]['l'];
			$r = $stack[$cur]['r'];
			$cur--;

			do
			{
				$i = $l;
				$j = $r;
				$tmp = $array[(int)( ($l+$r)/2 )];

				// split the array in to parts
				// first: objects with "smaller" property $property
				// second: objects with "bigger" property $property
				do
				{
					while( $array[$i]->{$property} < $tmp->{$property} ) $i++;
					while( $tmp->{$property} < $array[$j]->{$property} ) $j--;

					// Swap elements of two parts if necesary
					if( $i <= $j)
					{
						$w = $array[$i];
						$array[$i] = $array[$j];
						$array[$j] = $w;

						$i++;
						$j--;
					}

				} while ( $i <= $j );

				if( $i < $r ) {
					$cur++;
					$stack[$cur]['l'] = $i;
					$stack[$cur]['r'] = $r;
				}
				$r = $j;

			} while ( $l < $r );

		} while ( $cur != 0 );

		return $array;

	}


	/**
	 * @param $path
	 *
	 * @return bool
	 */
	public function delete_dir( $path ) {

		if ( is_file( $path ) ) {
			return unlink( $path );
		} elseif ( is_dir( $path ) ) {
			$scan = glob( rtrim( $path, '/' ) . '/*' );
			foreach ( $scan as $p ) {
				cc_delete_dir( $p );
			}

			return rmdir( $path );
		}

		return false;
	}

	/**
	 * @param $num
	 *
	 * @return string
	 */
	public function ordinal( $num ) {

		if ( ! in_array( ( $num % 100 ), array( 11, 12, 13 ) ) ) {
			switch ( $num % 10 ) {
				// Handle 1st, 2nd, 3rd
				case 1:
					return $num . 'st';
				case 2:
					return $num . 'nd';
				case 3:
					return $num . 'rd';
			}
		}

		return $num . 'th';
	}


	/**
	 * CONTENT
	 * Main function that returns all CC content
	 *
	 * @param string $url
	 *
	 * @return array
	 */
	public function content( $url = '' ) {

		global $cc_track;

		// set the key for this request
		$cache_key = 'content-track-' . $cc_track . 'url-' . str_replace( '/', '-', $url );

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			$cached['cache'] = 'cache: ' . $cache_key;
			return $cached;
		}

		/**
		 * Initial Variables
		 */

		// contains the filters we need to narrow our queries
		$filter['grade']         = null;
		$filter['unit']          = null;
		$filter['concept']       = null;
		$filter['explanation']   = null;
		$filter['problem']       = null;
		$filter['unit_position'] = null;
		$filter['special']       = null;
		$filter['special_term']  = null;
		$filter['page']          = null;

		// this is an array to return the current objects
		$current = array();

		// the following are flags:
		$label_flag = false;

		// exceptions to the explanations filter rule, allows special pages
		$valid_exceptions = array( 'glossary', 'video', 'focus', 'worked', 'page' );

		// exceptions to the "empty concepts" filter for units
		$filter_by_ex = array( 'k', '1', '9', '10', '11' );

		// 404 flag
		$not_404 = false;

		/**
		 * Process URL
		 */

		// url comes to us with some items in "friendly" form. We want to send back IDs

		if ( $url ) {

			// url is split to get some filters
			$parts = array_filter( explode( '/', $url ) );

			// grade and unit
			if ( isset( $parts[0] ) && $parts[0] ) {

				// a dot is used to specify a unit
				$split_grade = explode( '.', $parts[0] );

				// grade is first item in array
				$filter['grade'] = $split_grade[0];

				if ( intval( $filter['grade'] ) == 1 || strtolower( $filter['grade'] ) == 'k' ) {
					$label_flag = 'k';
				} else if ( intval( $filter['grade'] ) > 8 ) {
					$label_flag = '9';
				} else if ( intval( $filter['grade'] ) < 9 && intval( $filter['grade'] ) > 1 ) {
					$label_flag = '2';
				} else if ( $filter['grade'] == 'k1' ) {
					$label_flag        = 'k';
					$filter['grade']   = 'k';
					$filter['special'] = 'k1';
				} else {
					$this->return_404( array( 'parts' => $parts, 'filter' => $filter ) );
				}

				// unit will be determined by number after grade
				if ( isset( $split_grade[1] ) && ( $split_grade[1] || $split_grade[1] === '0' ) ) {
					$filter['unit']          = $split_grade[1];
					$filter['unit_position'] = $filter['unit'];
				}

			}

			if ( $label_flag == 'k' && ! isset( $parts[1] ) && $filter['special'] != 'k1' && $filter['unit'] === null ) {
				$parts[1] = 'video';
			}


			// concepts or explanations

			if ( isset( $parts[1] ) && $parts[1] ) {

				// determine if this matches a "special" filter case

				foreach ( $valid_exceptions as $valid ) {
					if ( $parts[1] == $valid ) {
						$filter['special'] = $valid;
					}
				}

				// if there were no special filters set...

				if ( $filter['special'] === null ) {

					// in K & 1 there are no concepts

					if ( $label_flag != '2' ) {
						$filter['explanation'] = $parts[1];
					} else {

						$filter['concept'] = $parts[1];

					}
				}
			}

			// if the special filter is set to 'focus' then the 3rd position is the term and the 4th is the explanation

			if ( isset( $parts[2] ) && $parts[2] && $filter['special'] == 'focus' ) {

				// we need to look up the filter term ID

				$this_term = get_term_by( 'slug', $parts[2], 'cc_focus', ARRAY_A );

				$filter['special_term'] = $this_term['term_id'];

				if ( isset( $parts[3] ) && $parts[3] ) {

					// this is an explanation
					$filter['explanation'] = $parts[3];
				}

			}

			// if the special filter is page than the slug that follows is the page slug
			else if ( isset( $parts[2] ) && $parts[2] && $filter['special'] == 'page' ) {

				// this is problem slug
				$filter['page'] = $parts[2];
			}

			else if ( isset( $parts[2] ) && $parts[2] ) {

				// catch worked examples
				foreach ( $valid_exceptions as $valid ) {
					if ( $parts[2] == $valid ) {
						$filter['special'] = $valid;
					}
				}

				// explanations if concept is set
				if ( $filter['concept'] !== null && $filter['special'] != 'worked' ) {
					$filter['explanation'] = $parts[2];
				}

			}


			// if the special filter is set to 'worked' the third position is the example slug

			if ( isset( $parts[3] ) && $parts[3] && $filter['special'] == 'worked' ) {

				// this is problem slug
				$filter['problem'] = $parts[3];
			}

		} else {

			$url = parse_url( $_SERVER['REQUEST_URI'] );

			// redirect /explore/ to home page
			if ( $url['path'] == '/explore/' ) {
				header( 'Location: /' );
				exit();
			}

		}

		/**
		 * GET GRADES
		 * Set the grade filter to grade ID
		 * The "grades" array will be used to narrow our concept filtering
		 */

		$all_grades = $this->get_cc_grades();
		$ok_grades  = $this->ids_array( $all_grades );

		/**
		 * SET FILTER ID: Grades
		 */

		// make sure grade filter is set to the grade id
		if ( $filter['grade'] ) {

			foreach ( $all_grades as $key => $grade ) {

				if ( strtolower( $filter['grade'] ) == strtolower( $grade['slug'] ) || strtolower( $filter['grade'] ) == strtolower( $grade['name'] ) ) {
					$filter['grade']  = (int) $grade['term_id'];
					$current['grade'] = $grade;
				}
			}
		}

		/**
		 * EXCEPTIONS: EARLY RETURN
		 * Check for exceptions in the explanation filter (special pages)
		 */

		if ( $filter['special'] == 'video' || $filter['special'] == 'glossary' ) {
			return array(
				'filters' => $filter,
			);
		}


		// set the grade filter

		$grade_envir_filter = null;
		if ( $filter['grade'] !== null && isset( $all_grades[ $filter['grade'] ]['environment'] ) ) {
			$grade_envir_filter = $all_grades[ $filter['grade'] ]['environment'];
		}


		/**
		 * GET CONTENT
		 */

		// sorted into arrays indexed by id

		$all_units = $this->get_cc_units( $filter['grade'], $grade_envir_filter, $ok_grades );
		$ok_units  = $this->ids_array( $all_units );

		$all_concepts = $this->get_cc_concepts( $filter['grade'] );
		$ok_concepts  = $this->ids_array( $all_concepts );

		$all_explanations = $this->get_cc_explanations( $filter['special'], $filter['special_term'], $filter['grade'] );
		$ok_explanations  = $this->ids_array( $all_explanations );

		$all_problems = $this->get_cc_problems( 'grade', $filter['grade'] );
		$ok_problems  = $this->ids_array( $all_problems );


		/**
		 * Remove draft status items
		 * The above arrays won't have draft items as the query specifies "publish", but the relationship
		 * field does have draft items in it.
		 */

		// Units
		$this->remove_associations( $all_units, 'cc_explanation', $ok_explanations );
		$this->remove_associations( $all_units, 'cc_concept', $ok_concepts );

		// Concepts
		$this->remove_associations( $all_concepts, 'cc_explanation', $ok_explanations );
		$this->remove_associations( $all_concepts, 'cc_problem', $ok_problems );
		$this->remove_associations( $all_concepts, 'cc_unit', $ok_units );

		// Explanations
		$this->remove_associations( $all_explanations, 'cc_concept', $ok_concepts );
		$this->remove_associations( $all_explanations, 'cc_unit', $ok_units );
		$this->remove_associations( $all_explanations, 'cc_grade', (array) $filter['grade'] );

		// Problems
		$this->remove_associations( $all_problems, 'cc_concept', $ok_concepts );
		$this->remove_associations( $all_problems, 'cc_grade', (array) $filter['grade'] );




		/**
		 * FILTER: Concepts
		 * Any concept without an explanation can be removed
		 */

		// do not filter the concept list if a focus term is used, to preserve the menu
		// note, there is the possibility that the unit menu will show a unit which contains
		// a single concept which has no explanations not in draft mode
		// this should be better handled by doing a seperate query for non-focus explanations,
		// or better, cache the menus

		if ( $filter['special_term'] === null ) {

			foreach ( $all_concepts as $key => $con ) {
				$empty = array_filter( $con['cc_explanation'] );
				if ( empty ( $empty ) ) {
					unset( $all_concepts[ $key ] );
				}
			}

		}

		/**
		 * REFERENCES: Concepts
		 * Clean out the relation arrays on other content types for concepts we've removed
		 */

		// remove references to now non-existent units
		$ok_concepts = $this->ids_array( $all_concepts );

		$this->remove_associations( $all_units, 'cc_concept', $ok_concepts );
		$this->remove_associations( $all_explanations, 'cc_concept', $ok_concepts );
		$this->remove_associations( $all_problems, 'cc_concept', $ok_concepts );

		$units = $all_units;


		/**
		 * FILTER: UNITS
		 * Remove units which do not have explanations for the grades in the exception array
		 */

		foreach ( $units as $key => $unit ) {
			foreach ( $all_grades as $grade ) {
				if ( in_array( strtolower( $grade['name'] ), $filter_by_ex ) && in_array( $grade['term_id'], $unit['cc_grade'] ) ) {
					$empty = array_filter( $unit['cc_explanation'] );
					if ( empty ( $empty ) ) {
						unset( $units[ $key ] );
					}
				}
			}
		}


		/**
		 * FILTER: UNITS
		 * Remove units which do not have concepts for the other grades
		 */

		foreach ( $units as $key => $unit ) {
			foreach ( $all_grades as $grade ) {
				if ( ! in_array( strtolower( $grade['name'] ), $filter_by_ex ) && in_array( $grade['term_id'], $unit['cc_grade'] ) ) {

					$empty = array_filter( $unit['cc_concept'] );
					if ( empty ( $empty ) ) {
						unset( $units[ $key ] );
					}
				}
			}
		}

		/**
		 * FILTER: UNITS
		 * If the grade filter is set, we can remove all units not in grade
		 */

		if ( $filter['grade'] ) {
			foreach ( $units as $key => $unit ) {
				if ( ! in_array( $filter['grade'], $unit['cc_grade'] ) ) {
					unset( $units[ $key ] );
				}
			}
		}

		/**
		 * REFERENCES: Units
		 * Clean out the relation arrays on other content types for units we've removed
		 */

		// remove references to now non-existent units
		$ok_units = $this->ids_array( $units );
		$this->remove_associations( $all_explanations, 'cc_unit', $ok_units );
		$this->remove_associations( $all_concepts, 'cc_unit', $ok_units );

		/**
		 * SET FILTER ID: Units
		 */

		if ( $filter['unit'] || $filter['unit'] === '0' ) {
			foreach ( $units as $unit ) {

				if ( $unit['unit_order'] == $filter['unit'] ) {
					$filter['unit'] = $unit['ID'];
					$not_404        = true;
					break;
				}
			}
		}

		/**
		 * 404 if unit filter is set and none matched
		 */

		if ( $filter['unit'] !== null && $not_404 === false ) {
			$this->return_404(
				array(
					'break point' => 'unit filter set, but none matched',
					'filter' => $filter,
					'all grades' => $all_grades,
					'all units' => $all_units,
					'units' => $units,
					'all concepts' => $all_concepts,
					'all explanations' => $all_explanations,
					'all problems' => $all_problems
				)
			);
		}

		/**
		 * FILTER: Concepts
		 * We can remove all concepts not associted with one of the returned units
		 */

		$concepts = $all_concepts;

		if ( $label_flag == '2' ) {

			foreach ( $concepts as $key => $concept ) {

				// remove concepts which do not have a unit attached
				$this_units = array_filter( $concept['cc_unit'] );
				if ( empty( $this_units ) ) {
					unset( $concepts[ $key ] );
				}

				// if a unit filter is set, remove this concept if not in unit
				if ( $filter['unit'] !== null ) {
					if ( ! in_array( $concept['ID'], $units[ $filter['unit'] ]['cc_concept'] ) ) {
						unset( $concepts[ $key ] );
					}
				}
			}

			// for ease, make array of concept ids
			$ok_cons = $this->ids_array( $concepts );

			// remove the associations to non-existent concepts from other types
			$this->remove_associations( $all_explanations, 'cc_concept', $ok_cons );
			$this->remove_associations( $all_problems, 'cc_concept', $ok_cons );

			// turn concept filter into an ID, currently a slug
			if ( $filter['concept'] !== null ) {

				$not_404 = false;

				foreach ( $concepts as $concept ) {
					if ( $filter['concept'] == $concept['post_name'] ) {
						$filter['concept'] = (int) $concept['ID'];
						$not_404           = true;
						break;
					}
				}
			}

			/**
			 * 404 if concept filter is set and none matched
			 */

			if ( $filter['concept'] !== null && $not_404 === false ) {
				$this->return_404(
					array(
						'break point' => 'concept filter set, but none matched',
						'filter' => $filter,
						'all grades' => $all_grades,
						'all units' => $all_units,
						'all concepts' => $all_concepts,
						'all explanations' => $all_explanations,
						'all problems' => $all_problems
					)
				);
			}

		} else {

			// we can remove all concepts for other grades

			$concepts = array();

		}

		/**
		 * Filter EXPLANATIONS
		 */

		// remove all empty explanations
		foreach ( $all_explanations as $key => $ex ) {

			// clean out empty explanation arrays
			$empty   = array_filter( $ex['cc_concept'] );
			$emptier = array_filter( $ex['cc_unit'] );

			// if array is empty
			if ( empty( $empty ) && empty( $emptier ) && $filter['special'] != 'focus' ) {
				unset( $all_explanations[ $key ] );
			}

		}

		// filter explanations by unit, but only if no concept has been set
		// filter against single returned unit first, against all units if no flag
		// avoid filtering explanations if a focus has been set

		$explanations_unit = array();
		if ( $label_flag != '2' && $filter['unit'] !== null && $filter['special'] != 'focus' ) {

			foreach ( $all_explanations as $key => $ex ) {

				if ( in_array( $filter['unit'], $ex['cc_unit'] ) ) {

					// assign parent content categories to this explanation
					if ( $label_flag == 'k' ) {
						$ex['cc_category'] = array_unique( array_merge( $units[ $filter['unit'] ]['cc_category'], $ex['cc_category'] ) );
					}

					$explanations_unit[ $key ] = $ex;

				}
			}
		} else if ( $label_flag == 'k' && $filter['special'] != 'focus' ) {

			foreach ( $all_explanations as $key => $ex ) {

				foreach ( $units as $unit ) {

					if ( in_array( $unit['ID'], $ex['cc_unit'] ) ) {

						// assign parent content categories to this explanation
						$ex['cc_category'] = array_filter( array_unique( array_merge( $unit['cc_category'], $ex['cc_category'] ) ) );

						$explanations_unit[ $key ] = $ex;
					}
				}
			}
		}

		if ( empty ( $explanations_unit ) && ( ( $filter['unit'] === null && $label_flag != '2' ) || $label_flag == '2' ) ) {
			$explanations_unit = $all_explanations;
		}

		// filter explanations by concept

		$explanations = array();
		if ( $filter['concept'] !== null ) {
			foreach ( $explanations_unit as $key => $ex ) {
				if ( in_array( $filter['concept'], $ex['cc_concept'] ) ) {
					$explanations[ $key ] = $ex;
				}
			}
		}
		if ( empty ( $explanations ) && $filter['concept'] === null ) {
			$explanations = $explanations_unit;
		}

		// if the focus term is set and a grade filter is set, we need to filter these by grade

		if ( $filter['special'] == 'focus' && $filter['grade'] !== null ) {
			$foc_ex = array();
			foreach ( $explanations as $key => $ex ) {
				if ( in_array( $filter['grade'], $ex['cc_grade'] ) ) {
					$foc_ex[ $key ] = $ex;
				}
			}
			$explanations = $foc_ex;
		}

		// turn exp filter into an ID, currently a slug
		if ( $filter['explanation'] !== null || $label_flag == 'k' ) {

			// check for 404 if the explanation filter is set
			if ( $filter['explanation'] !== null ) {
				$not_404 = false;
			}

			foreach ( $explanations as $ex ) {
				if ( $filter['explanation'] == $ex['post_name'] ) {
					$filter['explanation'] = (int) $ex['ID'];
					$not_404               = true;
				}
			}

			/**
			 * 404 if exp filter is set and none matched
			 */

			if ( $filter['explanation'] !== null && $not_404 === false ) {
				$this->return_404(
					array(
						'break point' => 'explanation filter set, but none matched',
						'filter' => $filter,
						'all grades' => $all_grades,
						'all units' => $all_units,
						'all concepts' => $all_concepts,
						'all explanations' => $all_explanations,
						'all problems' => $all_problems
					)
				);
			}
		}

		/**
		 * Problems
		 */

		// remove all empty problems
		foreach ( $all_problems as $key => $prob ) {

			// clean out empty concept arrays
			$empty = array_filter( $prob['cc_concept'] );

			// if array is empty
			if ( empty( $empty ) ) {
				unset( $all_problems[ $key ] );
			}
		}

		// filter problems by concept

		$problems = array();
		if ( $filter['concept'] !== null ) {
			foreach ( $all_problems as $key => $prob ) {
				if ( in_array( $filter['concept'], $prob['cc_concept'] ) ) {
					$problems[ $key ] = $prob;
				}
			}
		}
		if ( empty ( $problems ) && $filter['concept'] === null ) {
			$problems = $all_problems;
		}

		// turn problem filter into an ID, currently a slug
		if ( $filter['problem'] !== null ) {

			// check for 404 if the explanation filter is set
			if ( $filter['problem'] !== null ) {
				$not_404 = false;
			}

			foreach ( $problems as $prob ) {
				if ( $filter['problem'] == $prob['post_name'] ) {
					$filter['problem'] = (int) $prob['ID'];
					$not_404           = true;
				}
			}

			/**
			 * 404 if exp filter is set and none matched
			 */

			if ( $filter['problem'] !== null && $not_404 === false ) {
				$this->return_404(
					array(
						'break point' => 'problem filter set, but none matched',
						'filter' => $filter,
						'all grades' => $all_grades,
						'all units' => $all_units,
						'all concepts' => $all_concepts,
						'all explanations' => $all_explanations,
						'all problems' => $all_problems
					)
				);
			}
		}

		// set labels

		// @todo: get labels from options

		$labels = array();
		if ( $label_flag == 'k' ) {

			$labels['units']        = array( 'Unit', 'Units' );
			$labels['concepts']     = array( 'Concept', 'Concepts' );
			$labels['explanations'] = array( 'Video', 'Videos' );

		} else if ( $label_flag == '9' ) {

			$labels['units']        = array( 'Unit', 'Units' );
			$labels['concepts']     = array( 'Category', 'Categories' );
			$labels['explanations'] = array( 'Video', 'Videos' );

		} else {

			$labels['units']        = array( 'Unit', 'Units' );
			$labels['concepts']     = array( 'Concept', 'Concepts' );
			$labels['explanations'] = array( 'Explanation', 'Explanations' );

		}

		/*
		 * What we are returning:
		 * filters: item IDs identified from URL sent to us
		 * current: objects that match chosen filters
		 * units: unit in this grade, with empty units removed, and adjusted for track
		 * concepts: concepts in the current unit
		 */

		if ( $filter['grade'] ) {
			$current['grade'] = $all_grades[ $filter['grade'] ];
		}
		if ( $filter['unit'] ) {
			$current['unit'] = $units[ $filter['unit'] ];
		}
		if ( $filter['concept'] ) {
			$current['concept'] = $concepts[ $filter['concept'] ];
		}
		if ( $filter['explanation'] && $filter['explanation'] != 'worked' ) {
			$current['explanation'] = $explanations[ $filter['explanation'] ];
		}
		if ( $filter['problem'] ) {
			$current['problem'] = $problems[ $filter['problem'] ];
		}

		$return = array(
			'filters'      => $filter,
			'current'      => $current,
			'units'        => $units,
			'concepts'     => $concepts,
			'explanations' => $explanations,
			'problems'     => $problems,
			'labels'       => $labels
		);

		// this will return true if successfully cached
		if ( $this->cc_fragment_cache( $cache_key, $return ) ) {
			$return['cache'] = 'Saved cache: Main content query';
		}

		return $return;
	}

	/**
	 * @param array  $items
	 * @param string $field
	 * @param array  $ok
	 */
	private function remove_associations( &$items = array(), $field = '', $ok = array() ) {

		foreach ( $items as $key => $unit ) {
			foreach ( $unit[ $field ] as $k => $v ) {
				if ( ! in_array( $v, $ok ) ) {
					unset( $items[ $key ][ $field ][ $k ] );
				}
			}
		}
	}

	/**
	 * @param $items
	 *
	 * @return array
	 */
	private function ids_array( $items ) {
		$arr = array();
		foreach ( $items as $x ) {
			if ( isset( $x['term_id'] ) ) {
				$arr[] = $x['term_id'];
			} else {
				$arr[] = $x['ID'];
			}
		}

		return $arr;
	}

	/**
	 * Get grades.
	 *
	 * @param bool $ignore_track
	 *
	 * @return array|bool
	 */
	public function get_cc_grades( $ignore_track = false ) {

		// $ignore_track allows us to return a list of parents even if track is set

		global $wpdb, $cc_track;

		// check cache
		$cache_key = 'get-cc-grades-ignore-' . var_export( $ignore_track, true ) . '-track-' . $cc_track;
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		// if this is set to "true" ONLY track grades will be returned to track users

		$only_track_grades = false;

		// if this is set to true, track users will get all grades, but if there is
		// an empty track grade, the regular grade parent will be removed and the
		// grade will be unavailable completely

		$remove_empty_track_grades_only = true;

		// return only parents if no track is set

		$parent_clause = '';

		if ( $cc_track === null || ! $cc_track || $ignore_track === true ) {
			$parent_clause = " AND tt.parent = 0 ";
		}

		// remove counts to allow removal of empty track grades

		$count_clause = '';

		if ( $remove_empty_track_grades_only === false ) {
			$count_clause =  " AND tt.count > 0 ";
		}

		// query construction

		$query =
			"SELECT
				t.term_id,
				t.name,
				t.slug,
				tt.parent,
				tt.count,
				c.environment
			FROM
				ccs_terms t
					LEFT JOIN
						ccs_pods_cc_grade c ON ( t.term_id = c.id AND c.environment <> '' ),
				ccs_term_taxonomy tt
			WHERE
				t.term_id = tt.term_id AND
				tt.taxonomy = 'cc_grade'";
		$query .= $count_clause;
		$query .= $parent_clause;
		$query .= "
			ORDER BY
				t.slug
		";

		$result = $wpdb->get_results( $query, 'ARRAY_A' );

		// if the substitute flag is set, we need to examine the array to see if a child grade matches the track,
		// and substitute it if so

		if ( $cc_track !== null && $cc_track && $ignore_track === false ) {

			$parents = array();
			$children = array();

			// put parents and children in separate arrays
			foreach ( $result as $grade ) {

				if ( $grade['parent'] === '0' ) {
					$parents[] = $grade;
				} else {
					$children[ $grade['parent'] ][] = $grade;
				}
			}

			// loop through the parents
			foreach ( $parents as $key => $parent ) {

				// if the parent has children, see if any of them has a matching environment and substitute it
				if ( isset( $children[ $parent['term_id'] ] ) ) {

					foreach( $children[ $parent['term_id'] ] as $child ) {

						// check to see if environment has multiple ids

						$child['environment'] = trim( $child['environment'] );

						$env = explode( " ", $child['environment'] );

						if ( in_array( $cc_track, $env ) ) {
							$parents[$key]['term_id'] = $child['term_id'];
							$parents[$key]['environment'] = $env;
							$parents[$key]['count'] = $child['count'];
						}
					}
				}
			}

			$result = $parents;
		}

		// remove all grades except track grades, requires flag to be true

		if (
			$only_track_grades === true &&
			$cc_track !== null &&
			$cc_track &&
			$ignore_track === false
		) {

			foreach( $result as $key => $grade ) {

				// not clear under what circumstances environment is not an empty array, but hence second argument

				if (

					( is_array( $grade['environment'] ) && ! in_array( $cc_track, $grade['environment'] ) ) ||
					( ! is_array( $grade['environment'] ) && ! strstr( $grade['environment'], $cc_track ) )

				) {
					unset( $result[$key] );
				}
			}
		}

		// remove empty grades from above if graying out of empty track grades is allowed;
		// if this was false above, empty grades were already removed by query

		if ( $remove_empty_track_grades_only === true ) {

			foreach ( $result as $key => $grade ) {

				if ( $grade['count'] < 1 ) {

					unset( $result[$key] );
				}
			}
		}

		$return = $this->reindex_array( $result, 'term_id' );

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}


	public function get_cc_units( $grade = null, $environment = null, $ok_grades = null ) {

		global $wpdb, $cc_track;

		// set the key for this request
		$cache_key = 'get_cc_units-track-' . $cc_track
		             . '-grade-' . var_export($grade, true)
		             . '-environment-' . var_export($environment, true)
		             . '-okgrades-' . var_export($ok_grades, true)
		;

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		$extra = '';
		if ( $grade !== null ) {
			$extra = " AND ta.term_id = '" . $grade . "'";
		}

		$query = "
			SELECT
				pa.ID,
				pa.post_title,
				pa.post_name,
				pa.post_status,
				md.meta_value AS 'unit_order',
				me.meta_value AS 'display_title',
				mf.meta_value AS 'additional_unit_numbers',
				mg.meta_value AS 'credits',
				mz.meta_value AS 'thumbnail',
				my.meta_value AS 'image_meta',
				GROUP_CONCAT( DISTINCT ma.meta_value SEPARATOR ',' ) AS 'cc_concept',
				GROUP_CONCAT( DISTINCT mb.meta_value SEPARATOR ',' ) AS 'cc_explanation',
				GROUP_CONCAT( DISTINCT ta.term_id SEPARATOR ',' ) AS 'cc_grade',
				GROUP_CONCAT( DISTINCT tb.term_id SEPARATOR ',' ) AS 'cc_category'
			FROM
				`ccs_posts` pa
					LEFT JOIN
						`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'attach_concept')
					LEFT JOIN
						`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'attach_explanation')
					LEFT JOIN
						`ccs_postmeta` mf ON ( pa.ID = mf.post_id AND mf.meta_key = 'additional_unit_numbers')
					LEFT JOIN
						`ccs_postmeta` mg ON ( pa.ID = mg.post_id AND mg.meta_key = 'credits')
					LEFT JOIN
						`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = '_thumbnail_id')
					LEFT JOIN
						`ccs_postmeta` md ON ( pa.ID = md.post_id AND md.meta_key = 'unit_order')
					LEFT JOIN
						`ccs_postmeta` me ON ( pa.ID = me.post_id AND me.meta_key = 'display_title')
					LEFT JOIN
						`ccs_term_relationships` ra ON ( pa.ID = ra.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` ta ON ( ta.term_taxonomy_id = ra.term_taxonomy_id AND ta.taxonomy='cc_grade' )
					LEFT JOIN
						`ccs_term_relationships` rb ON ( pa.ID = rb.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` tb ON ( tb.term_taxonomy_id = rb.term_taxonomy_id AND tb.taxonomy='cc_category' )
					LEFT JOIN
						`ccs_postmeta` mz ON ( mc.meta_value = mz.post_id AND mz.meta_key = '_wp_attached_file' )
					LEFT JOIN
						`ccs_postmeta` my ON ( mc.meta_value = my.post_id AND my.meta_key = '_wp_attachment_metadata' )
			WHERE
				1 AND
				pa.post_type = 'cc_unit' AND
				pa.post_status = 'publish'";
		$query .= $extra;
		$query .= "
			GROUP BY
				pa.ID
			ORDER BY
				'display_title'
		";

		$result = $wpdb->get_results( $query, 'ARRAY_A' );

		$result = $this->array_msort( $result, array( 'unit_order' => 'SORT_ASC,SORT_NUMERIC' ) );

		// discard any units which are not in the $ok_grades array

		if ( $ok_grades !== null ) {

			foreach ( $result as $key => $unit ) {

				// unit is not ok until we say it is...
				$not_ok = true;

				// explode the grades

				$grades = explode( ',', $unit['cc_grade'] );

				foreach( $grades as $gr ) {

					if ( in_array( $gr, $ok_grades ) ) {
						$not_ok = false;
						break;
					}
				}

				if ( $not_ok === true ) {
					unset( $result[ $key ] );
				}
			}
		}

		// if cc_track is not null and environment is, set environment to an array containing cc_track

		if ( $environment === null && $cc_track !== null ) {
			$environment = array( $cc_track );
		}

		// change the unit number if called for
		// the var 'additional_unit_numbers' can contain a string in the following format:
		//    abcdef-0 ghijkl-4
		// this is tracking code hyphen unit number, with a space separating multiple unit numbers
		// we match it to our environment code

		if ( $environment !== null && is_array( $environment ) ) {

			// flag for reordering result array
			$reordered = false;

			foreach ( $result as $key => $unit ) {

				if ( isset( $unit['additional_unit_numbers'] ) && $unit['additional_unit_numbers'] ) {

					$nums = explode( ' ', $unit['additional_unit_numbers'] );

					foreach ( $nums as $num ) {

						$code = explode( '-', $num );

						foreach( $environment as $env ) {

							if ( $env == $code[0] ) {

								$result[ $key ]['additional_unit_numbers'] .= ' original-' . $result[ $key ]['unit_order'];
								$result[ $key ]['unit_order'] = $code[1];
								$reordered = true;
								break;
							}
						}
					}
				}
			}

			// if we changed the unit number, we should reorder the array

			if ( $reordered === true ) {
				$result = $this->array_msort( $result, array( 'unit_order' => 'SORT_ASC,SORT_NUMERIC' ) );
			}
		}

		$return = $this->reindex_array( $result, 'ID' );

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}

	/**
	 * @param null $grade
	 *
	 * @return array|bool
	 */
	public function get_cc_concepts( $grade = null ) {

		global $wpdb;

		// set the key for this request
		$cache_key = 'get_cc_concepts-grade-' . var_export($grade, true);

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		$extra = '';
		if ( $grade !== null ) {
			$extra = " AND ta.term_id = '" . $grade . "'";
		}

		$query = "
			SELECT
				pa.ID,
				pa.post_title,
				pa.post_name,
				pa.post_status,
				md.meta_value AS 'display_title',
				mz.meta_value AS 'thumbnail',
				my.meta_value AS 'image_meta',
				GROUP_CONCAT( DISTINCT ma.meta_value SEPARATOR ',' ) AS 'cc_unit',
				GROUP_CONCAT( DISTINCT mb.meta_value SEPARATOR ',' ) AS 'cc_explanation',
				GROUP_CONCAT( DISTINCT mc.meta_value SEPARATOR ',' ) AS 'cc_problem',
				GROUP_CONCAT( DISTINCT ta.term_id SEPARATOR ',' ) AS 'cc_grade',
				GROUP_CONCAT( DISTINCT tb.term_id SEPARATOR ',' ) AS 'cc_learning_domain',
				GROUP_CONCAT( DISTINCT tc.term_id SEPARATOR ',' ) AS 'cc_category'
			FROM
				`ccs_posts` pa
					LEFT JOIN
						`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'attach_unit')
					LEFT JOIN
						`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'attach_explanation')
					LEFT JOIN
						`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = 'attach_problem')
					LEFT JOIN
						`ccs_postmeta` md ON ( pa.ID = md.post_id AND md.meta_key = 'display_title')
					LEFT JOIN
						`ccs_term_relationships` ra ON ( pa.ID = ra.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` ta ON ( ta.term_taxonomy_id = ra.term_taxonomy_id AND ta.taxonomy='cc_grade' )
					LEFT JOIN
						`ccs_term_relationships` rb ON ( pa.ID = rb.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` tb ON ( tb.term_taxonomy_id = rb.term_taxonomy_id AND tb.taxonomy='cc_learning_domain' )
					LEFT JOIN
						`ccs_term_relationships` rc ON ( pa.ID = rc.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` tc ON ( tc.term_taxonomy_id = rc.term_taxonomy_id AND tc.taxonomy='cc_category' )
					LEFT JOIN
						`ccs_postmeta` mx ON ( pa.ID = mx.post_id AND mx.meta_key = '_thumbnail_id')
					LEFT JOIN
						`ccs_postmeta` mz ON ( mx.meta_value = mz.post_id AND mz.meta_key = '_wp_attached_file' )
					LEFT JOIN
						`ccs_postmeta` my ON ( mx.meta_value = my.post_id AND my.meta_key = '_wp_attachment_metadata' )
			WHERE
				1 AND
				pa.post_type = 'cc_concept' AND
				pa.post_status = 'publish'";
		$query .= $extra;
		$query .= "
			GROUP BY
				pa.ID
			ORDER BY
				pa.post_title ASC
		";
		$result = $wpdb->get_results( $query, 'ARRAY_A' );

		$return = $this->reindex_array( $result, 'ID' );

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}

	/**
	 * GET CC EXPLANATIONS
	 * Get explanations from DB. Can be called from outside this class.
	 *
	 * @param null $filter
	 * @param null $id
	 * @param null $grade
	 *
	 * @return array|bool
	 */
	public function get_cc_explanations( $filter = null, $id = null, $grade = null ) {

		// set the key for this request
		$cache_key = 'get_cc_explanations-grade-' . var_export($grade, true)
		             . '-id-' . var_export($id, true)
		             . '-filter-' . var_export($filter, true)
		;

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		// extra conditions to add to query
		$extra = '';

		// filter by grade
		if ( $grade !== null ) {
			$extra = " AND ta.term_id = '" . $grade . "' ";
		}

		// add some query conditions if a filter is set
		if ( $filter !== null && $id !== null ) {

			if ( $filter == 'unit' ) {
				$extra .= " AND mb.meta_value = '" . $id . "'";
			} else if ( $filter == 'concept' ) {
				$extra .= " AND ma.meta_value = '" . $id . "'";
			} else if ( $filter == 'grade' && $grade === null ) {
				$extra .= " AND ta.term_id = '" . $id . "'";
			} else if ( $filter == 'focus' ) {
				$extra .= " AND tc.term_id = '" . $id . "'";
			}

		}

		global $wpdb;

		$query = "
			SELECT
				pa.ID,
				pa.post_content,
				pa.post_title,
				pa.post_name,
				pa.post_status,
				mc.meta_value AS 'thumbnail_id',
				md.meta_value AS 'display_title',
				me.meta_value AS 'order',
				mf.meta_value AS 'default',
				mz.meta_value AS 'thumbnail',
				my.meta_value AS 'thumbnail_meta',
				GROUP_CONCAT( DISTINCT ma.meta_value SEPARATOR ',' ) AS 'cc_concept',
				GROUP_CONCAT( DISTINCT mb.meta_value SEPARATOR ',' ) AS 'cc_unit',
				GROUP_CONCAT( DISTINCT ta.term_id SEPARATOR ',' ) AS 'cc_grade',
				GROUP_CONCAT( DISTINCT tb.term_id SEPARATOR ',' ) AS 'cc_category',
				GROUP_CONCAT( DISTINCT tc.term_id SEPARATOR ',' ) AS 'cc_focus'
			FROM
				`ccs_posts` pa
					LEFT JOIN
						`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'attach_concept')
					LEFT JOIN
						`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'attach_unit')
					LEFT JOIN
						`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = '_thumbnail_id')
					LEFT JOIN
						`ccs_postmeta` md ON ( pa.ID = md.post_id AND md.meta_key = 'display_title')
					LEFT JOIN
						`ccs_postmeta` me ON ( pa.ID = me.post_id AND me.meta_key = 'order')
					LEFT JOIN
						`ccs_postmeta` mf ON ( pa.ID = mf.post_id AND mf.meta_key = 'default')
					LEFT JOIN
						`ccs_term_relationships` ra ON ( pa.ID = ra.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` ta ON ( ta.term_taxonomy_id = ra.term_taxonomy_id AND ta.taxonomy='cc_grade' )
					LEFT JOIN
						`ccs_term_relationships` rb ON ( pa.ID = rb.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` tb ON ( tb.term_taxonomy_id = rb.term_taxonomy_id AND tb.taxonomy='cc_category' )
					LEFT JOIN
						`ccs_term_relationships` rc ON ( pa.ID = rc.object_id )
					LEFT JOIN
						`ccs_term_taxonomy` tc ON ( tc.term_taxonomy_id = rc.term_taxonomy_id AND tc.taxonomy='cc_focus' )
					LEFT JOIN
						`ccs_postmeta` mz ON ( mc.meta_value = mz.post_id AND mz.meta_key = '_wp_attached_file' )
					LEFT JOIN
						`ccs_postmeta` my ON ( mc.meta_value = my.post_id AND my.meta_key = '_wp_attachment_metadata' )
			WHERE
				1 AND
				pa.post_type = 'cc_explanation' AND
				pa.post_status = 'publish'";
		$query .= $extra;
		$query .= "
			GROUP BY
				pa.ID
			ORDER BY
				mf.meta_value DESC, me.meta_value ASC, md.meta_value ASC
		";
		$result = $wpdb->get_results( $query, 'ARRAY_A' );

		$return = $this->reindex_array( $result, 'ID' );

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}

	/**
	 * GET CC PROBLEMS
	 * DB Query
	 *
	 * @param null $filter
	 * @param null $id
	 *
	 * @return array|bool
	 */
	public function get_cc_problems( $filter = null, $id = null ) {

		global $wpdb;

		// set the key for this request
		$cache_key = 'get_cc_problems-'
		             . '-id-' . var_export($id, true)
		             . '-filter-' . var_export($filter, true)
		;

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		// if a filter is set, add it to the query
		$extra = '';
		if ( $filter !== null && $id !== null ) {

			if ( $filter == 'concept' ) {
				$extra = " AND mg.meta_value = '" . $id . "'";
			} else if ( $filter == 'grade' ) {
				$extra = " AND ta.term_id = '" . $id . "'";
			}

		}

		$query = "
				SELECT
					pa.ID,
					pa.post_title,
					pa.post_name,
					pa.post_content,
					mi.meta_value AS 'display_title',
					ma.meta_value AS 'answer_1',
					mb.meta_value AS 'answer_2',
					mc.meta_value AS 'answer_3',
					mk.meta_value AS 'answer_4',
					mj.meta_value AS 'answer_5',
					md.meta_value AS 'rating',
					me.meta_value AS 'full_width',
					mf.meta_value AS 'order',
					mz.meta_value AS 'thumbnail',
					my.meta_value AS 'thumbnail_meta',
					GROUP_CONCAT( DISTINCT mg.meta_value SEPARATOR ',' ) AS 'cc_concept',
					GROUP_CONCAT( DISTINCT ta.term_id SEPARATOR ',' ) AS 'cc_grade'
				FROM
					`ccs_posts` pa
						LEFT JOIN
							`ccs_postmeta` mi ON ( pa.ID = mi.post_id AND mi.meta_key = 'display_title')
						LEFT JOIN
							`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'answer_1')
						LEFT JOIN
							`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'answer_2')
						LEFT JOIN
							`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = 'answer_3')
						LEFT JOIN
							`ccs_postmeta` mk ON ( pa.ID = mk.post_id AND mk.meta_key = 'answer_4')
						LEFT JOIN
							`ccs_postmeta` mj ON ( pa.ID = mj.post_id AND mj.meta_key = 'answer_5')
						LEFT JOIN
							`ccs_postmeta` md ON ( pa.ID = md.post_id AND md.meta_key = 'rating')
						LEFT JOIN
							`ccs_postmeta` me ON ( pa.ID = me.post_id AND me.meta_key = 'full_width')
						LEFT JOIN
							`ccs_postmeta` mf ON ( pa.ID = mf.post_id AND mf.meta_key = 'order')
						LEFT JOIN
							`ccs_postmeta` mg ON ( pa.ID = mg.post_id AND mg.meta_key = 'attach_concept')
						LEFT JOIN
							`ccs_postmeta` mh ON ( pa.ID = mh.post_id AND mh.meta_key = '_thumbnail_id')
						LEFT JOIN
							`ccs_term_relationships` ra ON ( pa.ID = ra.object_id )
						LEFT JOIN
							`ccs_term_taxonomy` ta ON ( ta.term_taxonomy_id = ra.term_taxonomy_id AND ta.taxonomy='cc_grade' )
						LEFT JOIN
							`ccs_postmeta` mz ON ( mh.meta_value = mz.post_id AND mz.meta_key = '_wp_attached_file' )
						LEFT JOIN
							`ccs_postmeta` my ON ( mh.meta_value = my.post_id AND my.meta_key = '_wp_attachment_metadata' )
				WHERE
					1 AND
					pa.post_type = 'cc_problem' AND
					pa.post_status = 'publish'";
		$query .= $extra;
		$query .= "
				GROUP BY
					pa.ID
				ORDER BY
					mf.meta_value ASC
			";
		$result = $wpdb->get_results( $query, 'ARRAY_A' );

		$return = $this->reindex_array( $result, 'ID' );

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}

	/**
	 * ADD CUSTOM USER FIELDS
	 * Adds custom user fields to user
	 *
	 * @param $user
	 */
	public function add_custom_user_fields( $user ) {

		// @todo: make this a select box based on tracks in DB

		echo '<h3>SSO Information</h3>' .
		     '<table class="form-table"><tbody><tr><th>' .
		     '<label for="track">Track</label></th><td>' .
		     '<input type="text" name="track" id="track" value="' . esc_attr( get_the_author_meta( 'track', $user->ID ) ) . '" class="regular-text" /><br />' .
		     '<span class="description">This is the last environment code sent with this user via SSO.</span>' .
		     '</td></tr></tbody></table>';
	}

	/**
	 * SAVE CUSTOM USER FIELDS
	 * As it says on the tin...
	 *
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function save_custom_user_fields( $user_id ) {

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		update_user_meta( $user_id, 'track', $_POST['track'] );

		return true;
	}

	/**
	 * CC FAQ
	 * Returns formatted Q&As
	 *
	 * @return bool|string
	 */
	public function cc_faq() {

		// set the key for this request
		$cache_key = 'cc_faq';

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		// get arrays with all concept maps that match each of our filters

		$args    = array(
			'post_type'      => 'cc_faq',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'order'          => 'ASC'
		);
		$raw_faq = new WP_Query( $args );

		if ( count( $raw_faq ) > 0 ) {

			$counter = 0;
			$result  = '<div class="cc-faq-layout"><div class="qa-faqs qa-home cf animation-fade accordion collapsible">';

			foreach ( $raw_faq->posts as $faq ) {

				$meta = $this->cc_meta_all( $faq->ID );
				$result .= '<div id="qa-faq' . $counter . '" class="qa-faq cf">';
				$result .= '<h3 class="qa-faq-title"><a class="qa-faq-anchor" href="/faq/how-do-i-access-the-concept-corner/">';
				$result .= $meta['faq_question'];
				$result .= '</a></h3>';
				$result .= '<div class="qa-faq-answer">';
				$result .= wpautop( $meta['faq_answer'] );
				$result .= '</div></div>';
				$counter ++;

			}

			$result .= '</div></div>';

			$return = $result;

			// add to cache
			$this->cc_fragment_cache( $cache_key, $return );

			return $return;

		} else {

			return false;

		}

	}

	/**
	 * CC META ALL
	 * Returns all meta fields on post. I think this is now the same as a native WP function.
	 * @todo: Check to see if post_custom does the same thing and remove if so
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public function cc_meta_all( $post_id ) {

		global $wpdb;

		// set the key for this request
		$cache_key = 'cc-meta-all-post-id-' . $post_id;

		// check cache, return early if it's set
		if ( $cached = $this->cc_fragment_cache( $cache_key ) ) {
			return $cached;
		}

		$data = array();
		$wpdb->query( "
			SELECT `meta_key`, `meta_value`
			FROM $wpdb->postmeta
			WHERE `post_id` = $post_id
		" );
		foreach ( $wpdb->last_result as $v ) {
			$data[ $v->meta_key ] = $v->meta_value;
		}

		$return = $data;

		// add to cache
		$this->cc_fragment_cache( $cache_key, $return );

		return $return;
	}

	/**
	 * RETURN 404
	 * 404 page generator
	 *
	 * @param null $info
	 */
	public function return_404( $info = null ) {

		global $user_login;
		global $wp_query;
		global $cc_content;

		if ( $user_login == 'rogerlos' ) {

			foreach( $info as $key => $item ) {
				print '<hr>';
				print $key . '<br>';
				var_dump( $item );
			}
			print '<hr>';
			print 'CC Content<br>';
			var_dump( $cc_content );
			die();

		} else {

			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			include( get_404_template() );
			exit;
		}
	}

	/**
	 * CC FRAGMENT CACHE
	 * $key concatenated identifier
	 * $data the material to cache
	 * $add_comment insert HTML comment before data
	 * $path URL to page, only used for entire page cache
	 *
	 * @param      $key
	 * @param null $data
	 * @param bool $add_comment
	 * @param null $path
	 *
	 * @return bool|mixed
	 */
	function cc_fragment_cache( $key, $data = null, $add_comment = false, $path = null ) {

		// set time limit to roughly one month
		$ttl = 4 * WEEK_IN_SECONDS;

		if (

			// is caching on?
			( defined( 'CC_FRAGMENT_CACHE' ) && ! CC_FRAGMENT_CACHE === true ) ||

			// is caching on for admins?
			( defined( 'CC_FRAGMENT_CACHE_IF_ADMIN' ) && ! CC_FRAGMENT_CACHE_IF_ADMIN && is_super_admin() ) ||

			// are we in the wordpress admin area?
			( is_admin() )

		) {
			// scram
			return false;
		}

		// MD5: done to keep length of key reasonable
		$key = 'cc_cache_' . md5( $key );

		if ( $data === null ) {

			// if this is not a save request, return transient
			return get_transient( $key );

		} else {

			// otherwise, save it
			if ( $add_comment === true ) {
				$data .= "<!-- Cached on " . date( DATE_RFC2822 ) . "-->\n";
			}
			set_transient( $key, $data, $ttl );

			if ( defined( 'CC_FRAGMENT_CACHE_USE_FILES' ) && CC_FRAGMENT_CACHE_USE_FILES === true && $path !== null ) {

				// make sure first and last character of path is already a "/"
				if ( substr( $path, -1 ) != '/' ) {
					$path .= '/';
				}
				if ( substr( $path, 0, 1 ) != '/' ) {
					$path = '/' . $path;
				}

				// create a path to save the file
				$file_path = ABSPATH . CC_FRAGMENT_CACHE_FLAT_DIR . $path;

				// make sure the directories exist
				if ( ! file_exists ( $file_path ) ) {
					if ( ! mkdir( $file_path, 0, true ) ) {
						die( $file_path );
					}
				}

				// write file
				file_put_contents( $file_path . 'index.html' , $data );
			}

			return true;
		}
	}

	/**
	 * Allows the grabbing of user roles from theme
	 *
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function get_user_role( $user_id = 0 ) {
		( $user_id ) ? get_userdata( $user_id ) : wp_get_current_user();
		if ( current_user_can( 'read_teacher' ) ) {
			return 'cc_teacher';
		} else if ( current_user_can( 'read_student' ) ) {
			return 'cc_student';
		} else {
			return 'none';
		}
	}
}
