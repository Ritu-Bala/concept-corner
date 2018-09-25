<?php 
/**
* Template Name: Concept Corner Sign In
*/

if ( class_exists( 'StopWatch') ) { StopWatch::record( 'start: login template' ); }

get_header();

// container div

echo '<div id="content" class="narrowcolum" role="main">';

// make sure the redirect doesn't point to this same page

if ( ! isset( $_GET['redirect'] ) || $_GET['redirect'] == '/login/' ) {
	$re = '/';
} else {
	$re = $_GET['redirect'];
}

// if they're logged in, give them the option to log out

if ( is_user_logged_in() ) {

		echo '<p class="loginformmessage">You\'re currently logged into Concept Corner. Would you like to <a href="' . wp_logout_url( '/login/' ) . '">log out</a>?</p>';

}

// insert login form

else {
	echo '<div id="loginformwrap">';
	wp_login_form( array( 'remember' => false, 'redirect' => $re ) );
	echo '</div>';
}

// remainder of page formatting

echo '</div>';

if ( class_exists( 'StopWatch') ) { StopWatch::record( 'end: login template' ); }

get_footer();
