<?php
/**
 * @var array $cc_current_grade
 */

if ( ! isset( $header_flag ) )
	$header_flag = 'page';

$buttons = array( 'video', 'glossary' );

/**
 * Open header
 */
echo '<header id="header">';
echo '<div class="container">';
echo '<div class="row">';

/**
 * Nav column
 */
echo '<div class="col-md-2 cc-k-header-buttons cc-k-header-left-buttons">';

// buttons
foreach ( $buttons as $button ) {
	echo $header_flag == $button ? '<span class="cc-k-nolink">' : '<a class="cc-k-header-btn" href="/explore/';
	echo $header_flag == $button ? '' : $cc_current_grade['name'];
	echo $header_flag == $button ? '' : '/' . $button . '/" title="Back to ' . $button . '">';
	echo '<img src="' . CC_LOCATION . '/img/' . $button;
	echo $header_flag == $button ? '-off' : '';
	echo '.png" class="cc-k-header-btn-img">';
	echo $header_flag == $button ? '</span>' : '</a>';
}

echo '</div>';

/**
 * Title column
 */
echo '<div class="col-md-8">';

// logo
echo '<div id="logo" class="aligncenter">';
echo '<h1><a href="/explore/k1/">Concept Corner</a></h1>';
echo '</div>';

// title
echo '<div id="pagetitle" class="aligncenter">';
echo '<h2>' . $unit_title . '</h2>';
echo '</div>';

// glossary letters
if ( $header_flag == 'glossary' ) {
	echo '<div id="k-glossary-letters">';
	foreach ( $nav as $n ) {
		echo $n;
	}
	echo '</div>';
}

echo '</div>';

/**
 * Final column
 */
echo '<div class="col-md-2 cc-k-header-buttons cc-k-header-right-buttons">';

// extra buttons
$buttons = apply_filters( 'cc_additional_header_buttons', '', $cc_current_grade['name'] );
// I can't find this garbage, so hacky hack hack...
echo $buttons;

echo '</div>';

/**
 * Close header
 */
echo '</div>';
echo '</div>';
echo '</header>';