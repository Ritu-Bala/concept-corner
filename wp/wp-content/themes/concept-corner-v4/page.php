<?php

// look for a custom field called "redirect" if there is one, send user to that page instead
$qobj = get_queried_object();

if ( isset( $qobj->post_type ) && 'page' == $qobj->post_type ) {
	
	$custom = get_post_custom( $qobj->ID );
	
	if ( isset( $custom['redirect'] ) && $custom['redirect'] ) {
		wp_safe_redirect( $custom['redirect'][0], '301' );
		exit;
	}
}

// use sidebar?
$use_sidebar = false;

get_header();

echo '<div id="content" role="main">';
echo '<div class="container">';

echo '<div class="row">';

if ( have_posts() ) {
	
	while ( have_posts() ) {
		
		the_post();
		
		// if no sidebar, add spacer
		echo $use_sidebar ? '' : '<div class="col-xs-2"></div>';
		
		
		echo '<div class="' . ( $use_sidebar ? 'col-xs-12' : 'col-xs-8' ) . '">';
		echo '<h2 class="pagetitle">' . get_the_title() . '</h2>';
		echo '</div>';
		
		// close title row, open content row
		echo '</div><div class="row">';
		
		// if no sidebar, add spacer
		echo $use_sidebar ? '' : '<div class="col-xs-2"></div>';
		
		// adjust, depending on sidebar
		echo '<div class="' . ( $use_sidebar ? 'col-xs-8 col-md-9' : 'col-xs-8' ) . '">';
		echo '<div '; post_class(); echo ' id="post-' . get_the_ID() . '">';
		echo '<div class="entry">';
		
		the_content();
		
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		// if sidebar, show it
		if ( $use_sidebar ) {
			echo '<div class="col-xs-4 col-md-3">';
			get_sidebar();
			echo '</div>';
		}
	}
}

// post not found
else {
	
	echo '<div class="col-xs-12 col-md-12">';
	echo '<h3>Sorry, the content cannot be found.</h3>';
	echo '</div>';
	
}

echo '</div>';
echo '</div>';
echo '</div>';

get_footer();