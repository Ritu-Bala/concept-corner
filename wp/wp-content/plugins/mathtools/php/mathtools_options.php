<?php
/*
 * MATHTOOLS OPTIONS
 * Add an option page with multiple metaboxes and optional tabs.
 *
 * Note on adding metaboxes: You must explicitly call
 *    object_type( 'options-page' )
 * On metaboxes meant for your options page before you add their fields, or CMB2 will not load the fields properly.
 *
 * You could easily modify the internal function "add_metaboxes()" to add your metaboxes and fields
 * directly within this class. I used the "get_all" method to grab boxes I had previously configured.
 *
 * For tabs, you need to configure a tabs array:
 *
 * self::$props['tabs'] = array(
 * // each tab is an array:
 * 	array(
 *   'id'    => 'your_tab_id',
 *   'title' => 'My Awesome Tab',
 *   'desc'  => '<p>Optional HTML description, shown above metaboxes in this tab.</p>',
 *   'boxes' => array(
 *     // These are your CMB2 metabox IDs.
 *    'boxid1',
 *    'boxid2',
 *   ),
 *  ),
 * )
 *
 * The page will build with the metaboxes hidden in a single container outside of the tabs, and the included JS
 * file will move them on page load and show the boxes in the current tab. The minimized JS is a maintenance problem;
 * if you include the js_uri in the props array it will use whatever external JS that points to instead. See JS file
 * in this archive.
 *
 * The save button will save all options in all tabs, visible or not.
 */

class Mathtools_Options {

	/**
	 * Whether settings notices have already been set
	 *
	 * @var bool
	 * @since  0.1.0
	 */
	protected static $once = false;

	/**
	 * Options page hook, equivalent to get_current_screen()['id']
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected static $options_page = '';

	/**
	 * Holds an instance of this class
	 *
	 * @var Mathtools_Options
	 * @since  0.1.0
	 **/
	protected static $instance = null;

	/**
	 * Properties which can be brought in via constructor
	 *
	 * If you inject the menuargs, they need to match the argument order and items:
	 *
	 * https://codex.wordpress.org/Function_Reference/add_menu_page for top level pages
	 * http://codex.wordpress.org/Function_Reference/add_submenu_page for sub-pages
	 *
	 * This class will build the arguments for you if you don't inject an array.
	 *
	 * @var array
	 * @since  0.1.0
	 */
	private static $props = array(

		'key'        => 'my_options',		// Required: Wordpress settings key
		'title'      => 'My Options',		// Required: Options page title

		'topmenu'    => '',					// Optional: If adding as a subpage, the parent page slug.
		'postslug'   => '',					// Optional: Post type if sub-page of "pages" or custom post type

		'menuargs'   => array(),			// Optional: WP menu arguments array.

		'jsuri'      => '',

		'boxes'      => array(),			// Optional: Array of CMB2 metabox objects (see cmb2_metaboxes(), below)
		'tabs'       => array(),			// Optional: Tab Configuration array (see add_tabs(), below)
	);
	
	/**
	 * CONSTRUCT
	 * Allows you to inject anything within the self::$props array. We need to add tabs here as several
	 * actions need to know if they exist.
	 *
	 * @param array $args    Array of arguments
	 * @since  0.1.0
	 */
	public function __construct( $args ) {
		self::$props = wp_parse_args( $args, self::$props );
		self::$props['tabs'] = $this->add_tabs();
	}

	/**
	 * GET INSTANCE
	 * Returns the running object. Not wild about singletons, but hey, I'm just cribbing code here.
	 *
	 * @param array $args    Array of arguments
	 * @return Mathtools_Options
	 * @since  0.1.0
	 **/
	public static function get_instance( $args = array() ) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Mathtools_Options( $args );
			self::$instance->hooks();
		}
		return self::$instance;
	}
	
	/**
	 * HOOKS
	 * Initiate hooks. Note there are additional actions within: add_metaboxes() add_options_page()
	 *
	 * @since 0.1.0
	 */
	public function hooks() {

		// Register setting
		add_action( 'admin_init', array( $this, 'register_setting' ) );

		// Adds page to admin with menu entry
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 12 );

		// Include CSS for this options page as style tag in head if tabs are configured
		add_action( 'admin_head', array( $this, 'add_css' ) );

		// Adds JS to foot if tabs are configured
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
	}

	/**
	 * REGISTER SETTING
	 *
	 * @since  0.1.0
	 */
	public function register_setting() {
		register_setting( self::$props['key'], self::$props['key'] );
	}

	/**
	 * ADD OPTIONS PAGE
	 *
	 * @since 0.1.0
	 */
	public function add_options_page() {

		// set which WP function will be called based on the value of 'topmenu'
		$callback = self::$props['topmenu'] ? 'add_submenu_page' : 'add_menu_page';

		// this is kind of ugly, but so is the WP function!
		$args = $this->build_menu_args();
		self::$options_page = $callback( $args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6] );

		// Include CMB CSS in the head to avoid FOUC, called here as we need the screen ID
		add_action( 'admin_print_styles-' . self::$options_page , array( 'CMB2_hookup', 'enqueue_cmb_css' ) );

		// Adds existing metaboxes, see note in function, called here as we need the screen ID
		add_action( 'add_meta_boxes_' . self::$options_page, array( $this, 'add_metaboxes' ) );

		// On page load, do "metaboxes" actions, called here as we need the screen ID
		add_action( 'load-' . self::$options_page, array( $this, 'do_metaboxes' ) );
	}

	/**
	 * BUILD MENU ARGS
	 * Builds the arguments needed to add options page to admin menu if they are not injected.
	 *
	 * Create top level menu when self::$props['topmenu'] = '' otherwise create submenu. You can pass null
	 * to create a page without a menu entry, but you will need to link to it somewhere.
	 *
	 * @return array
	 * @since 0.1.0
	 */
	private function build_menu_args() {

		// if a menu arguments array was injected, return it
		if ( ! empty( self::$props['menuargs'] ) )
			return self::$props['menuargs'];

		// otherwise build the menu page from the page title and options-slug
		$args = array();
		if ( self::$props['topmenu'] ) {
			// adds a post_type get var, to allow post options pages
			$add = self::$props['postslug'] ? '?post_type=' . self::$props['postslug'] : '';
			$args[] = self::$props['topmenu'] . $add;		// parent
		}
		$args[] = $args[] = self::$props['title'];			// title (2x)
		$args[] = 'manage_options';							// capabilities
		$args[] = self::$props['key'];						// slug
		$args[] = array( $this, 'admin_page_display' );		// callback
		if ( ! self::$props['topmenu'] )
			$args[] = ''; 									// icon
		$args[] = '';										// position, empty

		return $args;
	}

	/**
	 * ADD SCRIPTS
	 * Add WP's metabox script, either by itself or as dependency of the tabs script. Added only to this options page.
	 * If you role your own script, note the localized values being passed here.
	 *
	 * @param string $hook_suffix
	 * @since 0.1.0
	 * @throws \Exception
	 */
	public function add_scripts( $hook_suffix ) {

		// only add the script to the options page and only if there are tabs present
		if ( $hook_suffix !== self::$options_page || empty( self::$props['tabs'] ) )
			return;

		// unenque the other script
		wp_dequeue_script( 'mathtools-use' );

		$script   = plugin_dir_url( dirname(__FILE__) )  . 'inc/js/mathtoolsets-admin.js';
		$page     = self::$props['key'];
		$posttype = self::$props['postslug'];
		$local    = 'CMB2OptAdmin';

		// if the JS uri was injected, adjust the script location
		if ( self::$props['jsuri'] )
			$script = self::$props['jsuri'];

		// check to see if file exists, throws exception if it does not
		$headers = @get_headers( $script );
		if ( $headers[0] == 'HTTP/1.1 404 Not Found' ) {
			throw new Exception( 'CMB2 Multibox Options: Passed Javascript file missing.' );
		}

		// 'postboxes' needed for metaboxes to work properly
		wp_enqueue_script( 'postbox' );

		// enqueue the script
		wp_enqueue_script( $page . '-admin', $script, array( 'postbox' ), false, true );

		// localize script to give access to this page's slug
		wp_localize_script( $page . '-admin', $local, array(
			'page'     => $page,
			'posttype' => $posttype,
		) );
	}

	/**
	 * ADD CSS
	 * Adds a couple of rules (plus any injected CSS) to clean up WP styles if tabs are included
	 */
	public function add_css() {

		// if tabs are not being used, return
		if ( empty( self::$props['tabs'] ) )
			return;

		// add css to clean up tab styles in admin as used here
		$css = '<style type="text/css">';
		$css .= '#poststuff h2.nav-tab-wrapper{padding-bottom:0;}'; // cleans up tabs within postboxes
		$css .= '.opt-hidden{display:none;}';                       // hide metaboxes until moved
		$css .= '</style>';

		echo $css;
	}

	/**
	 * ADD METABOXES
	 * Adds CMB2 metaboxes.
	 *
	 * @since  0.1.0
	 */
	public function add_metaboxes() {

		self::$props['boxes'] = $this->cmb2_metaboxes();

		foreach ( self::$props['boxes'] as $box ) {

			// skip if this should not be shown
			if ( ! $this->should_show( $box ) )
				continue;

			$id = $box->meta_box['id'];

			// add notice if settings are saved
			add_action( 'cmb2_save_options-page_fields_' . $id, array( $this, 'settings_notices' ), 10, 2 );

			// add callback to allow classes to be added for use in tabbed layouts
			if ( ! empty( self::$props['tabs'] ) )
				add_filter( 'postbox_classes_' . self::$options_page . '_' . $id, array( $this, 'add_metabox_classes' ) );

			// closed by default...
			if ( $box->meta_box['closed'] )
				add_filter( 'postbox_classes_' . self::$options_page . '_' . $id, array( $this, 'close_metabox_class' ) );

			// add meta box
			add_meta_box(
				$box->meta_box['id'],
				$box->meta_box['title'],
				array( $this, 'metabox_callback' ),
				self::$options_page,
				$box->meta_box['context'],
				$box->meta_box['priority']
			);
		}
	}

	/**
	 * SHOULD SHOW
	 * Mimics the CMB2 "should show" function to prevent boxes which should not be shown on this options page from
	 * appearing.
	 *
	 * @param CMB2 $box
	 * @return bool
	 * @since  0.1.0
	 */
	private function should_show( $box ) {
		if ( ! isset( $box->meta_box['show_on']['key'] ) )
			return false;
		if ( $box->meta_box['show_on']['key'] != 'options-page' )
			return false;
		if ( ! in_array( self::$props['key'], $box->meta_box['show_on']['value'] ) )
			return false;
		return true;
	}

	/**
	 * ADD METABOX CLASSES
	 * The "hidden" class hides metaboxes until they have been moved to appropriate tab, if tabs are used.
	 *
	 * @param array $classes
	 * @since 0.1.0
	 * @return array
	 */
	public function add_metabox_classes( $classes ) {
		$classes[] = 'opt-hidden';
		return $classes;
	}

	/**
	 * CLOSE METABOX CLASS
	 * Adds class to closed-by-default metaboxes
	 *
	 * @param array $classes
	 * @since 0.1.0
	 * @return array
	 */
	public function close_metabox_class( $classes ) {
		$classes[] = 'closed';
		return $classes;
	}

	/**
	 * DO METABOXES
	 * Triggers the loading of our metaboxes on this screen.
	 *
	 * @since 0.1.0
	 */
	public function do_metaboxes() {
		do_action( 'add_meta_boxes_' . self::$options_page, null );
		do_action( 'add_meta_boxes', self::$options_page, null );
	}

	/**
	 * METABOX CALLBACK
	 * Builds the fields and saves them.
	 *
	 * @since  0.1.0
	 */
	public static function metabox_callback() {

		// get the metabox, fishing the ID out of the arguments array
		$args = func_get_args();
		$cmb = cmb2_get_metabox( $args[1]['id'], self::$props['key'] );

		// save fields
		if ( $cmb->prop( 'save_fields' )
			&& isset( $_POST['submit-cmb'], $_POST['object_id'], $_POST[ $cmb->nonce() ] )
			&& wp_verify_nonce( $_POST[ $cmb->nonce() ], $cmb->nonce() )
			&& self::$props['key'] && $_POST['object_id'] == self::$props['key']
		) {
			$cmb->save_fields( self::$props['key'], $cmb->mb_object_type(), $_POST );
		}

		// show the fields
		$cmb->show_form();
	}
	
	/**
	 * ADMIN PAGE DISPLAY
	 * Admin page markup. Modify to reflect your needs (ie, add second column, etc.)
	 *
	 * @since  0.1.0
	 */
	public function admin_page_display() {

		// Page wrapper
		echo '<div class="wrap cmb2-options-page ' . self::$props['key'] . '">';

		// Title
		echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

		// form wraps all tabs
		echo '<form class="cmb-form" method="post" id="mathtools-options-form" '
			 . 'enctype="multipart/form-data" encoding="multipart/form-data">';

		// hidden object_id field
		echo '<input type="hidden" name="object_id" value="' . self::$props['key'] . '">';

		// add postbox, which allows use of metaboxes
		echo '<div id="poststuff">';

		// this particular page has a single column.
		echo '<div id="post-body" class="metabox-holder columns-1">';
		echo '<div id="postbox-container-1" class="postbox-container">';

		// add tabs...
		$this->render_tabs();

		// add boxes...
		do_meta_boxes( self::$options_page, 'normal', null );

		// close markup
		echo '</div>';
		echo '</div>';
		echo '</div>';

		// add submit button and close remaining markup
		echo '<input type="submit" name="submit-cmb" value="Save" class="button-primary">';
		echo '</form>';
		echo '</div>';

		// reset the notices flag
		self::$once = false;
	}

	/**
	 * SETTINGS NOTICES
	 * Added a check to make sure its only called once for the page...
	 *
	 * @param string $object_id
	 * @param array  $updated
	 * @since  0.1.0
	 */
	public function settings_notices( $object_id, $updated ) {

		// bail if this isn't a notice for this page or we've already added a notice
		if ( $object_id !== self::$props['key'] || empty( $updated ) || self::$once )
			return;

		// add notifications
		add_settings_error( self::$props['key'] . '-notices', '', __( 'Settings updated.', 'mathtools' ), 'updated' );
		settings_errors( self::$props['key'] . '-notices' );

		// set the flag so we don't pile up the notices
		self::$once = true;
	}

	/**
	 * RENDER TABS
	 * Echoes tabs if they've been configured. The containers will have their metaboxes moved into them by javascript.
	 *
	 * @since 0.1.0
	 */
	private function render_tabs() {

		if ( empty( self::$props['tabs'] ) )
			return;

		$containers = '';
		$tabs = '';

		foreach( self::$props['tabs'] as $tab ) {

			// add tabs navigation
			$tabs .= '<a href="#" id="opt-tab-' . $tab['id'] . '" class="nav-tab opt-tab" '
					 . 'data-optcontent="#opt-content-' . $tab['id'] . '">';
			$tabs .= $tab['title'];
			$tabs .= '</a>';

			// add tabs containers, javascript will use the data attribute to move metaboxes to within proper tab
			$contents = implode( ',', $tab['boxes'] );

			// tab container markup
			$containers .= '<div class="opt-content" id="opt-content-' . $tab['id'] . '" '
						   . ' data-boxes="' . $contents . '">';
			$containers .= $tab['desc'];
			$containers .= '<div class="meta-box-sortables ui-sortable">';
			$containers .= '</div></div>';
		}

		echo '<h2 class="nav-tab-wrapper">';
		echo $tabs;
		echo '</h2>';
		echo $containers;
	}

	/**
	 * CMB2 METABOXES
	 * Allows three methods of adding metaboxes:
	 *
	 * 1) Injected boxes are added to the boxes array
	 * 2) Add additional boxes (or boxes if none were injected) the usual way
	 * 3) If array is still empty, call CMB2_Boxes::get_all();
	 *
	 * @return array|\CMB2[]
	 * @since 0.1.0
	 */
	private function cmb2_metaboxes() {

		// add any injected metaboxes
		$boxes = self::$props['boxes'];

		/*
		 * You can add your own boxes the usual way by assigning each to the $boxes array.
		 *
		 * $box = new_cmb2_box( $args );
		 * ...
		 * Add fields...
		 * ...
		 * $boxes[] = $box;
		 *
		 * Repeat as necessary.
		 *
		 * IMPORTANT!
		 * You MUST add this call to each box, or the fields within will fail to save.
		 *   $box->object_type( 'options-page' )
		 */

		// if array is still empty, call CMB2_Boxes::get_all(), presumes boxes were added elsewhere in your program
		return empty( $boxes ) ? CMB2_Boxes::get_all() : $boxes;
	}


	/**
	 * ADD TABS
	 * Add tabs to your options page.
	 *
	 * @return mixed
	 * @since 0.1.0
	 */
	private function add_tabs() {

		// add any injected tabs
		$tabs = self::$props['tabs'];

		/*
		 * To add tabs here, each tab should look like this:
		 *
		 * $tabs[] = array(
		 *   'id'    => 'your_tab_id',
         *   'title' => 'Your Tab Title',
         *   'desc'  => '<p>Optional HTML to display above the metaboxes on this tab.</p>',
         *   'boxes' => array(
		 *      'cmb2_metabox_id',
		 *      'another_cmb2_metabox_id',
		 *      'etc',
		 *      'etcetera'
		 *   ),
		 * );
		 */

		return $tabs;
	}
}