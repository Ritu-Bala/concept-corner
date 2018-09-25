<?php
// get the concept category terms
$cc_categories = get_terms('cc_category');
$explanations = $cc_content['explanations'];

function cc_custom_sort($a,$b) {
	return $a['display_title'] > $b['display_title'];
}

usort( $explanations, 'cc_custom_sort' );

echo '<!-- u-a-v --><div id="content" role="main"><div class="container">';

include( CC_PATH . '/templates/z-part-header-row.php');

echo '<div class="row">';
echo '<div class="col-xs-10 col-md-10 col-xs-offset-1 col-md-offset-1">';
echo '<div class="row">';
echo '<div class="col-xs-12 col-md-12">';

echo '<h1 class="pagetitle">Grade ' . $nice_filter['grade'] . ': ' . $cc_content['labels']['explanations'][0] . ' Library</h1>';

echo '</div>';

if ( $explanations ) {

	foreach ( $cc_categories as $cat ) {

		$title = '<div class="col-xs-12 col-md-12"><h2 class="pagetitle">' . $cat->name . '</h2></div>';

		// column counter
		$i = 1;
		$stuff = '';

		foreach ( $explanations as $k => $ex ) {

			$empty = array_filter( $ex['cc_category'] );

			if ( ! empty( $empty ) && in_array( $cat->term_id, $ex['cc_category'] ) ) {

				$stuff .= gcbu_display_item( $ex, $i );
				$i++;
			}
		}

		if ( $stuff ) {
			echo $title;
			echo $stuff;
		}
	}
}

echo '</div></div></div></div></div>';

function gcbu_display_item( $ex, $i ) {

	global $cc_content, $cc_current_grade;

	$this_thumb_name = 'thumbnail';

	$ex['cc_unit'] = array_values( $ex['cc_unit'] );

	$this_unit = $ex['cc_unit'][0];
	
	$link = '/explore/' . $cc_current_grade['name'] . '.' . $cc_content['units'][ $this_unit ]['unit_order'] . '/'
		. $ex[ 'post_name' ] . '/';

	$ret = '<div class="col-xs-6 col-md-3">';
	$ret .= '<div class="conceptsgrid">';
	$ret .= '<a href="' . $link . '" class="thumbnail videoblock">';

	$this_image = '';

	// use medium for responsive thumbnails, if doesn't exist, use original?
	if ( isset( $ex['thumbnail_meta'] ) && array_key_exists( $this_thumb_name, $ex['thumbnail_meta']['sizes'] ) ) {
		
		$explode = explode( '/', $ex['thumbnail_meta']['file'] );
		array_pop( $explode );
		
		$this_image_path = implode( '/', $explode ) . '/';
		
		$this_image = CC_CDN . $this_image_path . $ex['thumbnail_meta']['sizes'][$this_thumb_name]['file'];
	}
	
	else if ( isset( $ex['thumbnail_meta'] ) ) {
		$this_image = CC_CDN . $ex['thumbnail_meta']['file'];
	}
	
	if ( $this_image ) {
		$ret .= '<img class="img-responsive" src="' . $this_image . '" alt="' . $ex['display_title'] . '" />';
	}

	if ( strstr( $ex['post_content'], '[jwplayer' ) !== false ) {
		$ret .= '<span class="playbtn"></span>';
	}
	
	$ret .= '</a>';
	$ret .= '<h4><a href="' . $link  . '" rel="bookmark" title="Permanent Link to ' . $ex['display_title'] . '">' . $ex['display_title'] . '</a></h4>';
	$ret .= '<br/>';
	$ret .= '</div>';
	$ret .= '</div>';

	if ($i%2 == 0) $ret .= '<div class="clearfix visible-xs-block visible-sm-block"></div>';
	if ($i%4 == 0) $ret .= '</div><div class="row">';

	return $ret;
}