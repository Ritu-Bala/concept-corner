<?php

/**
 * @var $cc_content array
 */

// this grabs the first explanation's content for display if we're only bringing a concept to this template
$this_explanation = ! isset( $nice_filter['explanation'] ) ?
	$this_explanation = reset( $cc_content['explanations'] ) :
	$this_explanation = $cc_content['explanations'][ $cc_content['filters']['explanation'] ];

$this_thumb_name = 'thumbnail';
$show_title = true;
$sidebar = '';

echo '<div id="content" role="main"><div class="container cc-single-concept-template">';

include( CC_PATH . '/templates/z-part-header-row.php' );

echo '<div class="row">';

echo '<div class="col-xs-7 col-md-8 col-xs-offset-1 col-md-offset-1">';
echo '<div>';
echo '<div class="entry">';
echo '<h2 class="inline-pagetitle">' . $this_explanation['display_title'] . '</h2>';

if ( strstr( $this_explanation['post_content'], '[jwplayer' ) !== false ) {
	echo '<div class="cc-jwp">';
}

//echo apply_filters( 'the_content', $this_explanation['post_content'] );
$content = apply_filters( 'the_content', $this_explanation['post_content'] );

// more hacks...
echo $content;

if ( strstr( $this_explanation['post_content'], '[jwplayer' ) !== false ) {
	echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-xs-3 col-md-2">';
echo '<div class="conceptsidebar">';

foreach ( $cc_content['explanations'] as $ex ) {

	if ( $ex['ID'] != $this_explanation['ID'] ) {

		$this_link = '/explore/' . $nice_filter['grade'] . '.' . $nice_filter['unit'] . '/' . $nice_filter['concept'] .
			'/' . $ex['post_name'];

		$sidebar .= '<a href="' . $this_link . '" class="thumbnail videoblock">';

		$this_image = '';
		if ( isset( $ex['thumbnail_meta'] ) && array_key_exists( $this_thumb_name, $ex['thumbnail_meta']['sizes'] ) ) {
			$explode = explode( '/', $ex['thumbnail_meta']['file'] );
			array_pop( $explode );
			$this_image_path = implode( '/', $explode ) . '/';
			$this_image = $cc_upload_dir . $this_image_path .
				$ex['thumbnail_meta']['sizes'][ $this_thumb_name ]['file'];
		}
		else if ( isset( $ex['thumbnail_meta'] ) ) {
			$this_image = $cc_upload_dir . $ex['thumbnail_meta']['file'];
		}

		if ( isset( $this_image ) && $this_image ) {
			$sidebar .= '<img class="img-responsive" src="' . $this_image . '" alt="' . $ex['display_title'] . '" />';
		}

		if ( strstr( $ex['post_content'], '[jwplayer' ) !== false ) {
			$sidebar .= '<span class="playbtn"></span>';
		}

		$sidebar .= '</a>';
		$sidebar .= '<h4><a href="' . $this_link . '" rel="bookmark">' . $ex['display_title'] . '</a></h4>';
	}
}

if ( $sidebar ) {

	echo '<h3 class="sidebartitle">More</h3>';
	echo $sidebar;
}

echo '</div></div></div></div></div>';

if ( isset( $cc_content['current']['unit']['credits'] ) && $cc_content['current']['unit']['credits'] ) {
	echo '<div id="cc-unit-credits"><div class="entry">';
	echo apply_filters( 'the_content', $cc_content['current']['unit']['credits'] );
	echo '</div></div>';
}
