<?php
/**
 * Pearson Single Sign On
 *
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 */

/**
 * Pearson SSO Admin functions
 * @package Pearson_SSO_Admin
 * @author    Roger Los <roger@rogerlos.com>
 */

class Pearson_SSO_Admin {

	/**
	 * Instance of this class.
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug
	 * @since    1.0.0
	 * @var      object
	 */
	protected static $slug = null;
	
	/**
	 * Options key for WP settings.
	 * @since    1.0.0
	 * @var      string
	 */
	public static $key = 'psso_options';

	/**
	 * Keep a copy of the main SSO object around
	 * @since    1.0.0
	 * @var array
	 */
	protected static $sso = null;
	
	protected static $cfg = array(
		'text'   => array(),
		'tabs'   => array(),
		'fields' => array(),
		'boxes'  => array(),
		'pages'  => array(),
	);

	/**
	 * Initialize the plugin.
	 * @since     1.0.0
	 */
	private function __construct() {

		if ( ! is_super_admin() ) return;
		
		self::$sso   = Pearson_SSO::get_instance();
		self::$slug  = self::$sso->slug();
		
		// add JSON configuration
		foreach ( array_keys( self::$cfg ) as $k ) {
			self::$cfg[ $k ] = empty( self::$cfg[ $k ] ) ? Pearson_SSO_Admin_Config::get( $k ) : self::$cfg[ $k ];
		}
		
		self::add_cmb();
		
		// include pages, has to be done at "init"
		add_action( 'init', array( __CLASS__, 'add_pages' ), 9999 );
		add_filter( 'cmb2metatabs_before_form', array( __CLASS__, 'before_page' ), 10, 2 );
		add_filter( 'cmb2metatabs_after_form', array( __CLASS__, 'after_page' ), 10, 2 );
		
		// add metaboxes
		add_action( 'cmb2_admin_init', array( __CLASS__, 'add_metaboxes' ) );
		
		add_action( 'cmb2_save_options-page_fields', array( 'Pearson_SSO', 'load_options' ) );

		// Load admin style sheet and JavaScript
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );

		// Add an action link pointing to options page
		$pb = plugin_basename( PSSO_URL ) . self::$slug . '.php';
		add_filter( 'plugin_action_links_' . $pb, array( __CLASS__, 'add_action_links' ) );

		// ajax wrapper
		add_action( 'wp_ajax_psso', array( __CLASS__, 'psso_ajax' ) );
		
		// log actions
		add_action( 'plugins_loaded', array( 'Pearson_SSO_Log', 'update_db' ) );
		add_action( 'admin_init', array( 'Pearson_SSO_Log', 'clean_log' ) );
		add_filter( 'set-screen-option', array(  __CLASS__, 'set_screen' ), 10, 3 );
	}

	/**
	 * Instance of this class
	 * @return bool|object|Pearson_SSO_Admin
	 */
	public static function get_instance() {
		if ( ! is_super_admin() )      return false;
		if ( null == self::$instance ) self::$instance = new self;
		return self::$instance;
	}
	
	/**
	 * Adds custom metaboxes class
	 */
	public static function add_cmb() {
		require_once( self::$sso->file() . 'lib/CMB2/init.php' );
	}
	
	/**
	 * Called from mcb2_metatabs_options before page filter
	 *
	 * @param $page
	 *
	 * @return string
	 */
	public static function before_page( $content, $page ) {
		$txt = isset( self::$cfg['text'][ 'before-' . $page ] ) ?
			self::$cfg['text'][ 'before-' . $page ] : '';
		return is_array( $txt ) ?  $txt[0]::$txt[1]() : $txt;
	}
	
	/**
	 * Called from mcb2_metatabs_options after page filter
	 * @param $page
	 *
	 * @return string
	 */
	public static function after_page( $content, $page ) {
		$txt = isset( self::$cfg['text'][ 'after-' . $page ] ) ?
			self::$cfg['text'][ 'after-' . $page ] : '';
		return is_array( $txt ) ?  $txt[0]::$txt[1]() : $txt;
	}
	
	/**
	 * Gets config item, expects config key and item key
	 *
	 * @param $what
	 * @param $key
	 *
	 * @return null
	 */
	public static function get_cfg( $what, $key ) {
		
		if ( ! isset( self::$cfg[ $what ] ) ) return null;
		
		$ret = ! empty( self::$cfg[ $what ][ $key ] ) ? self::$cfg[ $what ][ $key ] : null;
		
		// check if the "id" field in a plain array item = $key
		if ( $ret === null ) {
			foreach( self::$cfg[ $what ] as $wh ) {
				if ( isset( $wh['id'] ) && $wh['id'] == $key ) {
					$ret = $wh;
					break;
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 * @todo: does this actually do anything?
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 * @since     1.0.0
	 * @return    null    Return early if no settings page is registered.
	 */
	public static function enqueue_admin_scripts() {
		
		$screen  = get_current_screen();
		$network = is_multisite() ? '-network' : '';
		
		foreach ( self::$cfg['pages'] as $page ) {

			$ident = ! isset( $page['menuargs']['parent_slug'] ) ?
				'toplevel_page_' . self::$slug  . $network:
				self::$slug . '_page_' . $page['menuargs']['menu_slug'] . $network;

			if ( $ident == $screen->id ) {
				wp_enqueue_script(
					self::$slug . '-admin-script',
					PSSO_URL . '/js/admin.js',
					array( 'jquery' ), 
					Pearson_SSO::VERSION 
				);
				wp_enqueue_style( 
					self::$slug . '-admin-styles',
					PSSO_URL . '/css/admin.css',
					array(), 
					Pearson_SSO::VERSION 
				);
			}
		}
	}
	
	/**
	 * Adds CMB2 metabox
	 */
	public static function add_metaboxes() {
		
		if ( empty( self::$cfg['boxes'] ) ) return;
		
		foreach ( self::$cfg['boxes'] as $box ) {
			
			$fields = $box['fields'];
			unset( $box['fields'] );
			
			$cmb = new_cmb2_box( $box );
			
			foreach ( $fields as $field ) {
				self::add_field( $cmb, $field );
			}
			
			$cmb->object_type( 'options-page' );
		}
	}
	
	/**
	 * Adds field to CMB2 metabox
	 *
	 * @param CMB2  $cmb
	 * @param array $field
	 * @param string $group
	 */
	public static function add_field( $cmb,  $field, $group = '' ) {
		
		$field = $group ? $field : self::get_cfg( 'fields', $field );
		
		// this is a group field parent
		if ( $field['type'] == 'group' ) {
			
			$fields = $field['fields'];
			unset( $field[ 'fields' ] );
			
			$group = $cmb->add_field( $field );
			
			foreach( $fields as $f ) {
				self::add_field( $cmb, $f, $group );
			}
		}
		
		// a field for a group
		else if ( $group ) {
			$cmb->add_group_field( $group, $field );
		}
		
		// it's a regular field
		else {
			$cmb->add_field( $field );
		}
	}
	
	public static function add_pages() {
		foreach( self::$cfg['pages'] as $key => $page ) {
			new Cmb2_Metatabs_Options( $page );
		}
	}
	
	public static function register_setting() {
		register_setting( self::$key, self::$key );
	}
	

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public static function add_action_links( $links ) {
		$links[] = '<a href="' . network_admin_url( 'admin.php?page=' . self::$slug ) . '">Configure</a>';
		return $links;
	}
	
	/**
	 * Returns the options key
	 *
	 * @return string
	 */
	public static function key() {
		return self::$key;
	}
	
	/**
	 * Handles test form submissions
	 */
	public function psso_ajax() {

		if ( isset( $_POST['psso_test'] ) && $_POST['psso_test'] ) {

			// process submitted URL into full URL
			$url = parse_url( $_POST['psso_test'] );
			
			if ( ! isset( $url['path'] ) ) {
				$url['path'] = '/';
			}
			if ( ! isset( $url['query'] ) ) {
				$url['query'] = '';
			}

			self::$sso->set_test( $url['path'], $url['query'] );
			self::$sso->sso();
			$success = 1;
			
			if ( Pearson_SSO::get_errors() ) {
				Pearson_SSO_Debug::d( '', Pearson_SSO::get_errors(), 'bail', 'SSO test failed:', false );
				$success = 0;
			} else {
				Pearson_SSO_Debug::d( '', array(), 'success', 'SSO success!', false );
			}
			
			if ( Pearson_SSO::get_option( 'psso_logs', false ) == 'on' && Pearson_SSO::get_option( 'psso_log_tests', false ) == 'on' ) {
				Pearson_SSO_Log::log( 'sso_via', 'Test' );
				Pearson_SSO_Log::log( 'sso_success', $success );
				Pearson_SSO_Log::log( 'sso_debug', Pearson_SSO_Debug::get_debug() );
				Pearson_SSO_Log::write();
			}
			
			$deb = Pearson_SSO_Debug::get_debug();
			
			Pearson_SSO_Debug::html();

			foreach ( $deb as $item ) {
				echo $item;
			}
			
			Pearson_SSO_Debug::html( 'close' );
			
			exit();
		}
	}
}