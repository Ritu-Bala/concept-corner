<?php

/**
 * @var array $cc_content
 * @var array $cc_current_grade
 */

$page = get_page_by_path( $cc_content['filters']['page'] );

$show_title = false;
$show_focus = true;
$this_explanation['display_title'] = apply_filters( 'cc_page_title', $page->post_title, $page, $cc_current_grade['name'] );

echo '<div id="content" role="main">';
echo '<div class="container">';

include( CC_PATH . '/templates/z-part-header-row.php');

echo '<div class="row">';
echo '<div class="col-xs-12">';
echo '<div class="entry">';

$content = apply_filters( 'the_content', $page->post_content );
// more hacks...
echo $content;

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';