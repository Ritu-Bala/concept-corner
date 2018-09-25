<?php
// get the concept category terms

$explanations = $cc_content['explanations'];

echo '
	<div id="content" role="main">
		<div class="container">';

include( CC_PATH . '/templates/z-part-header-row.php');

echo '<div class="row">';
echo '<div class="col-xs-10 col-md-10 col-xs-offset-1 col-md-offset-1">';
echo '<div class="row">';
echo '<div class="col-xs-12 col-md-12">';

echo '<!-- g-f -->' . "\n" . '<h1 class="pagetitle">Grade ' . $nice_filter['grade'] . ': ' . $this_term['name'] . '</h1>';

echo '</div>';

if ( $cc_content['explanations'] ) {

	// column counter
	$i = 1;
	$stuff = '';

	foreach ( $explanations as $k => $ex ) {

			$stuff .= gcbu_display_item( $ex, $i, $this_term );
			$i++;

	}

	if ( $stuff ) {
		echo $stuff;
	}

}

echo '</div></div></div></div></div>';

function gcbu_display_item( $ex, $i, $term ) {

	global $cc_current_grade;

	$this_thumb_name = 'thumbnail';

	$link = '/explore/' . $cc_current_grade['name'] .  '/focus/' . $term['slug'] . '/' . $ex[ 'post_name' ] . '/';

	$ret = '<div class="col-xs-6 col-md-3">';
	$ret .= '<div class="conceptsgrid">';
	$ret .= '<a href="' . $link . '" class="thumbnail videoblock">';

	$this_image = '';

	// use medium for responsive thumbnails, if doesn't exist, use original?
	if ( isset( $ex['thumbnail_meta'] ) && array_key_exists( $this_thumb_name, $ex['thumbnail_meta']['sizes'] ) ) {
		$explode = explode( '/', $ex['thumbnail_meta']['file'] );
		array_pop($explode);
		$this_image_path = implode( '/', $explode ) . '/';
		$this_image = CC_CDN . $this_image_path . $ex['thumbnail_meta']['sizes'][$this_thumb_name]['file'];
	} else if ( isset( $ex['thumbnail_meta'] ) ) {
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




