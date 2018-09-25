<?php
// this grabs the first explanation's content for display if we're only bringing a concept to this template
$this_explanation = '';
if ( ! isset( $nice_filter['explanation'] ) ) {
	$this_explanation = reset( $cc_content['explanations'] );
}
else {
	$this_explanation = $cc_content['explanations'][ $cc_content['filters']['explanation'] ];
}

$this_thumb_name = 'thumbnail';
$cc_categories = get_terms( 'cc_category' );

echo '<div id="content" role="main"><div class="container">';

include( CC_PATH . '/templates/z-part-header-row.php' );

echo '<div class="row">';
echo '<div class="col-xs-10 col-md-10 col-xs-offset-1 col-md-offset-1">';

echo '<h1 class="pagetitle">' . $cc_content['labels']['units'][0] . ': ' .
	$cc_content['current']['unit']['display_title'] . '</h1>';

echo '</div></div>';

echo '<div class="row">';
echo '<div class="col-xs-7 col-md-8 col-xs-offset-1 col-md-offset-1">';
echo '<div>';
echo '<div class="entry">';
echo '<div class="cc-jwp">';

echo apply_filters( 'the_content', $this_explanation['post_content'] );

echo '</div>';

echo '<h2>' . $this_explanation['display_title'] . '</h2>';

echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-xs-3 col-md-2">';
echo '<div class="conceptsidebar">';

$sidebar = '';
foreach ( $cc_content['explanations'] as $ex ) {
	
	if ( $ex['ID'] != $this_explanation['ID'] ) {
		
		$this_link = '/explore/' . $nice_filter['grade'] . '.' . $cc_content['current']['unit']['unit_order'] . '/' .
			$ex['post_name'] . '/';
		
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
		
		foreach ( $cc_categories as $cat ) {
			if ( isset( $ex['cc_category'][0] ) && $cat->term_id == $ex['cc_category'][0] ) {
				$sidebar .= '<p>Category: ' . $cat->name . '</p>';
				break;
			}
		}
		$sidebar .= '<h4><a href="' . $this_link . '" rel="bookmark">' . $ex['display_title'] . '</a></h4>';
	}
}

if ( $sidebar ) {
	
	echo '<h3 class="pagetitle">More</h3>';
	echo $sidebar;
}

echo '</div></div></div></div></div>';

if ( isset( $cc_content['current']['unit']['credits'] ) && $cc_content['current']['unit']['credits'] ) {
	echo '<div id="cc-unit-credits"><div class="entry">';
	echo apply_filters( 'the_content', $cc_content['current']['unit']['credits'] );
	echo '</div></div>';
}