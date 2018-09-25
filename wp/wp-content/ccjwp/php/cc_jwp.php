<?php

// Note, this in theory can support JWP6, but jwp6.json is completely unconfigured

class Cc_Jwp {

	private static $slug = 'ccjwp';
	private static $version = '1.1';
	private static $p = '';
	private static $ccjwp_path = '';
	private static $ccjwp_url = '';
	private static $current_url = '';
	private static $cfg = array();
	private static $atts = array();
	private static $mode = '';

	function __construct() {

		// set mode
		self::$mode = is_admin() ? 'admin' : 'public';
		
		// set path and url
		self::$ccjwp_path = CCJWP_PATH;
		self::$ccjwp_url = plugins_url() . '/' . self::$slug . '/';

		// proceed with initialization
		self::$mode == 'public' ? self::public_init() : self::admin_init();
	}
	
	/**
	 * PUBLIC INIT
	 * @todo: at some point, would be nice to peek ahead, but difficult with cc structure
	 */
	private static function public_init() {

		// load config
		self::$cfg = Rl_Config::recurse( self::$ccjwp_path . 'config/' );

		// set current url
		self::$current_url = self::current_url();
	
		// set shortcut to player
		self::$p = 'jwp' . self::$cfg['ccjwp']['player'];
		
		// replace tokens in tokens
		self::$cfg['ccjwp']['tokens'] = self::tokens( array( 'p' => self::$p ), self::$cfg['ccjwp']['tokens'] );
		
		// replace tokens in config
		self::$cfg['ccjwp']['css']    = self::tokens( self::$cfg['ccjwp']['tokens'], self::$cfg['ccjwp']['css'] );
		self::$cfg['ccjwp']['js']     = self::tokens( self::$cfg['ccjwp']['tokens'], self::$cfg['ccjwp']['js'] );
		self::$cfg['jwp7']            = self::tokens( self::$cfg['ccjwp']['tokens'], self::$cfg['jwp7'] );
		
		// add shortcodes
		self::add_shortcodes();

		// add css and js
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_css' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_scripts' ) );
	}
	
	/**
	 * Currently no admin for the plugin at all...
	 */
	private static function admin_init() {

	}
	
	/**
	 * CURRENT URL
	 * Uses PHP to discover current URL
	 *
	 * @return string
	 */
	public static function current_url() {
		return ( isset( $_SERVER['HTTPS'] ) ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
	
	/**
	 * GET
	 * Shortcut to the get_dots array utility, returns configuration vars
	 *
	 * @param       $dots
	 * @param array $arr
	 *
	 * @return bool|mixed|null
	 */
	public static function get( $dots, $arr ) {
		return Rl_Config::get_dots( $dots, $arr );
	}

	/**
	 * SET
	 * Sets static values used by this class
	 *
	 * @param $var
	 * @param $val
	 */
	private static function set( $var, $val ) {
		self::${$var} = $val;
	}

	/**
	 * TOKENS
	 * Replaces tokens in the config with actual values. If the token is defined as an array, it can be:
	 * - A reference to an internal static variable ( 'cfg', 'get.this.value' ) or ( 'dir' )
	 * - A reference to a function ( 'get_wp_url' )
	 * - A reference to a class and method ( '__CLASS__', 'get_plugin_dir' )
	 *
	 * @param $tokens
	 * @param $array
	 *
	 * @return mixed
	 */
	public static function tokens( $tokens, $array ) {
		if ( ! empty( $tokens ) ) {
			foreach ( $tokens as $token => $var ) {
				$v = '';
				if ( is_array( $var ) ) {
					// is this an internal var call?
					if ( isset( self::${$var[0]} ) ) {
						$v = isset( $var[1] ) ? self::get( $var[1], self::${$var[0]} ) : self::${$var[0]};
					}
					// is this a call to a function?
					else if ( function_exists( $var[0] ) ) {
						$v = call_user_func( $var[0] );
					}
					// is this a call to a class method?
					else if ( class_exists( $var[0] ) ) {
						$v = $var[0]::${$var[1]}();
					}
				}
				// was not an array, so the value should replace the token
				else {
					$v = $var;
				}
				$array = Rl_Arrays::rec_array_replace( '{{' . $token . '}}', $v, $array );
			}
		}

		return $array;
	}
	
	/**
	 * ADD SCRIPTS
	 */
	public static function add_scripts() {
		if ( ! empty( self::$cfg['ccjwp']['js'] ) ) {
			foreach ( self::$cfg['ccjwp']['js'] as $js ) {
				wp_enqueue_script( $js['handle'], $js['src'], $js['dep'], $js['ver'], $js['foot'] );
			}
		}
	}
	
	/**
	 * ADD CSS
	 */
	public static function add_css() {
		if ( ! empty( self::$cfg['ccjwp']['css'] ) ) {
			foreach ( self::$cfg['ccjwp']['css'] as $css ) {
				wp_enqueue_style( $css['handle'], $css['src'], $css['dep'], $css['ver'], $css['media'] );
			}
		}
	}
	
	/**
	 * ADD SHORTCODES
	 */
	public static function add_shortcodes() {
		foreach ( self::$cfg[ self::$slug ]['shortcodes'] as $s ) {
			add_shortcode( $s['code'], $s['call'] );
		}
	}
	
	/**
	 * JWP OPTIONS
	 * Checks any passed JWP options to see if they're legit. Does not check values or types.
	 *
	 * @param $opts
	 *
	 * @return mixed
	 */
	private static function jwp_options( $opts ) {
		
		// split each to see if it matches a JWP option
		foreach ( $opts as $k => $v ) {

			// an underscore in an attribute is the cue to split it into an array. There can be nested keys
			$x = explode( '_', strtolower( $k ) );

			// if there is a second index, and the first is "jwp", and it's legit
			if ( isset( $x[1] ) && $x[0] == 'jwp' ) {

				// shift the 'jwp' out of the array
				array_shift( $x );

				// see if it's different than the defined option
				if ( self::check_legit_jwp( $x ) && self::get_jwp_config_value( $x ) !== $v ) {

					$compile = array();
					$count = count( $x ) - 1;

					// place the value into the final key
					$compile[ $x[ $count ] ] = $v;

					for ( $a = ( $count - 1 ); $a >= 0; $a -- ) {
						$compile[ $x[ $a ] ] = $compile;
					}

					array_push( $opts[ self::$p ], $compile );
				}
			}
			
			// otherwise, make sure they're in our other options
			else {
				foreach ( self::$cfg['ccjwp']['shortcode_atts'] as $key => $val ) {
					$opts['ccjwp'][ $key ] = $k == $key ? $v : $val;
				}
			}
			
			// unset the attribute
			unset ( $opts[ $k ] );
		}
		
		// make sure the necessary defaults are returned
		foreach ( self::$cfg['ccjwp']['shortcode_atts'] as $key => $val ) {
			if ( ! isset( $opts['ccjwp'][ $key ] ) ) {
				$opts['ccjwp'][ $key ] = $val;
			}
		}

		// same for JWP defaults
		foreach ( self::$cfg[ self::$p ]['options'] as $key => $val ) {
			if ( ! isset( $opts[ self::$p ][ $key ] ) ) {
				$opts[ self::$p ][ $key ] = $val;
			}
		}
		
		return $opts;
	}
	
	/**
	 * CHECK LEGIT JWP
	 * Checks to be sure passed shortcode parameter is a legit JWP value. Note, it only checks for items two levels
	 * deep; some JWP parameters can be deeper arrays, such as:
	 * - advertising.companiondiv.something.something
	 * This only checks up to "companiondiv"
	 *
	 * @param $x
	 *
	 * @return bool
	 */
	private static function check_legit_jwp( $x ) {
		$ret = false;
		// check if it's a legit parameter
		if ( isset( self::$cfg[ self::$p ]['defaults'][ $x[0] ] ) ) {
			$ret = true;
			// check if there is a second key, if it's not legit, set false
			if ( isset( $x[1] ) && ! isset( self::$cfg[ self::$p ]['defaults'][ $x[0] ][ $x[1] ] ) ) {
				$ret = false;
			}
		}
		return $ret;
	}
	
	/**
	 * GET JWP CONFIG VALUE
	 * Checks our config to gt either an explicitely set option value or the default. Have not tested with deeply
	 * nested JWP config items
	 *
	 * @param $x array  JWP parameter keys as keys
	 *
	 * @return string
	 */
	private static function get_jwp_config_value( $x ) {
		if ( isset( $x[1] ) ) {
			$ret = isset( self::$cfg[ self::$p ]['options'][ $x[0] ][ $x[1] ] ) ?
				self::$cfg[ self::$p ]['options'][ $x[0] ][ $x[1] ] : self::$cfg[ self::$p ]['defaults'][ $x[0] ][ $x[1] ];
		}
		else {
			$ret = isset( self::$cfg[ self::$p ]['options'][ $x[0] ] ) ?
				self::$cfg[ self::$p ]['options'][ $x[0] ] : self::$cfg[ self::$p ]['defaults'][ $x[0] ];
		}

		return $ret;
	}

	/**
	 * GET POSTER IMAGE
	 * This uses the featured image of the post the video file is attached to as the poster image, if it is set.
	 */
	private static function get_poster_image() {
		if (
			self::$atts['ccjwp']['poster'] &&
			empty( self::$atts[ self::$p ]['image'] )
			&& isset( self::$atts['ccjwp']['mediaid'] )
			&& empty( self::$atts[ self::$p ]['playlist'] )
		) {
			$parent = get_post_ancestors( intval( self::$atts['ccjwp']['mediaid'] ) );
			if ( ! empty( $parent ) ) {
				$attachmentUrl = wp_get_attachment_url( get_post_thumbnail_id( $parent[0] ) );

				//self::$atts[ self::$p ]['image'] = wp_get_attachment_url( get_post_thumbnail_id( $parent[0] ) );
				self::$atts[ self::$p ]['image'] = $attachmentUrl;
			}
		}
	}

	/**
	 * GET CLOSED CAPTIONS
	 * Looks to see if there are closed captions on the server in the same location as the video.
	 */
	private static function get_closed_captions() {

		if ( self::$atts['ccjwp']['cc'] ) {

			$tracks = array();
			$types = array_keys( self::$cfg['ccjwp']['cc'] );
			$default = self::$atts['ccjwp']['cclocale'] ? get_locale() : 'en';


			// aws
			$s3a = isset( self::$cfg['ccjwp']['s3']['accesskey'] ) ? self::$cfg['ccjwp']['s3']['accesskey'] : '';
			$s3s = isset( self::$cfg['ccjwp']['s3']['secretkey'] ) ? self::$cfg['ccjwp']['s3']['secretkey'] : '';

			// cut extension from video file name
			$base = explode( '.', self::$atts[ self::$p ]['file'] );
			array_pop( $base );
			$base = implode( '.', $base );
			$httpPrefix = 'http://';
			if (is_ssl()) {
			    $httpPrefix = 'https://';
			}
			//$check = str_replace( $httpPrefix . self::$cfg['ccjwp']['s3']['bucket'] . '.s3.amazonaws.com/', '', $base );
			$check = str_replace( $httpPrefix . 's3.amazonaws.com/' . self::$cfg['ccjwp']['s3']['bucket'] . '/', '', $base );

			// AWS
			$s3 = class_exists( 'S3' ) && $s3a && $s3s ? new S3( $s3a, $s3s ) : null;

			$check_args = array(
				'types'     => $types,
				'check'     => $check,
				'base'      => $base,
				's3'        => $s3,
				'default'   => $default,
			);





			// find cc files based on the automatic extension list on S3
			if ( empty( self::$atts['ccjwp']['ccfiles'] ) && $s3 !== null ) {


				foreach ( $types as $type ) {
					foreach ( self::$cfg['ccjwp']['cc'][ $type ] as $try => $info ) {
						if ( 
							$s3::getObjectInfo( self::$cfg['ccjwp']['s3']['bucket'], $check . $try . '.' . $type ) 
							&& empty( $loaded[ $try ] ) 
						) {
							$tracks[] = array(
								'file'    => $base . $try . '.' . $type,
								'label'   => $info['label'],
								'kind'    => 'captions',
								'default' => in_array( $default, $info['locales'] ) ? true : false,
							);
							$loaded[ $try ] = true;
						}
					}
				}
			}

			// @todo: code to find local cc files
			else if ( empty( self::$atts['ccjwp']['ccfiles'] ) ) {

			}

			// @todo: specific files were passed as a parameter
			else {

			}

			// if we have captions, we need to turn this into a playlist
			if ( ! empty( $tracks ) ) {
				if ( ! isset( self::$atts[ self::$p ]['playlist'] ) ) {
					self::$atts[ self::$p ]['playlist'] = array();
				}
				self::$atts[ self::$p ]['playlist'][] = array(
					'file'   => self::$atts[ self::$p ]['file'],
					'image'  => self::$atts[ self::$p ]['image'],
					'tracks' => $tracks,
				);
				self::$atts[ self::$p ]['file'] = '';
				self::$atts[ self::$p ]['image'] = '';
			}
		}
	}

	private static function add_tracks( $args ) {
		$loaded = array();
		$tracks = array();
		foreach ( $args['types'] as $type ) {
			foreach ( self::$cfg['ccjwp']['cc'][ $type ] as $try => $info ) {
				$args['try']  = $try;
				$args['type'] = $type;
				$file = self::check_cap_file( $args );
				if ( $file && empty( $loaded[ $try ] ) ) {
					$tracks[] = array(
						'file'    => $file,
						'label'   => $info['label'],
						'kind'    => 'captions',
						'default' => in_array( $args['default'], $info['locales'] ) ? true : false,
					);
					$loaded[ $try ] = true;
				}
			}
		}
		return $tracks;
	}

	private static function check_cap_file( $args ) {
		$file = false;
		if ( $args['s3'] && $args['s3']::getObjectInfo( $args['bucket'], $args['check'] . $args['try'] . '.' . $args['type'] ) ) {
			 $file = $args['base'] . $args['try'] . '.' . $args['type'];
		} else {
			// @todo: this checks the local media library
		}
		return $file;
	}

	/**
	 * PLAYER
	 * Returns the actual player.
	 *
	 * @param $atts
	 *
	 * @return mixed|string
	 */
	public static function player( $atts ) {
		
		// early return for no atts
		if ( empty( $atts ) ) {
			return '';
		}

		// integrate with the defaults from JSON config
		self::$atts = self::jwp_options( $atts );

		// set a random ID in case multiple players are in-page
		self::$atts['ccjwp']['id'] = 'jwp' . rand( 0, 1000 );

		// early return for no media id, file, or playlist
		if ( ! self::$atts['ccjwp']['mediaid'] &&
			! self::$atts[ self::$p ]['file'] &&
			empty( self::$atts[ self::$p ]['playlist'] )
		) {
			return '';
		}
		
		// if media id was passed, get the video file, return if it fails
		if ( self::$atts['ccjwp']['mediaid'] ) {
		    // For https, we may have duplicate 
			self::$atts[ self::$p ]['file'] = wp_get_attachment_url( self::$atts['ccjwp']['mediaid'] );
			if ( self::$atts[ self::$p ]['file'] === false ) {
				return '';
			}
		}

       	// Set the poster image
		self::get_poster_image();
		
		// Deal with closed captions
		self::get_closed_captions();
		
		// templates
		$T = new Rl_Template( self::$cfg['ccjwp']['templates'] );
		
		// send a list of the defined tokens to the template renderer, with their values
		$template_vars = array();
		foreach ( self::$cfg['ccjwp']['tokens'] as $tk => $tv ) {
			$template_vars[ $tk ] = self::tokens( self::$cfg['ccjwp']['tokens'], '{{' . $tk . '}}' );
		}

		// get the template
		$return = $T->render( self::$atts['ccjwp']['template'], $template_vars );
		
		// optionally return JS file with the var
		if ( self::$atts['ccjwp']['returnjs'] ) {
			$return .= '<script>'
				. 'var ' . self::$atts['ccjwp']['id'] . '=' . json_encode( self::$atts[ self::$p ] )
				. '</script>';
		}
		
		return $return;
	}
}