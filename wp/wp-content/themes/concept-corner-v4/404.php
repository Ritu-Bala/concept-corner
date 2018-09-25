<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 * Template Name: 404
 */

get_header();

echo '<div id="content" class="narrowcolumn">';
echo '<div id="wrap404">';
echo '<div id="message404">';
echo '<h2 class="center">Oops!</h2>';
echo '<p>We\'re sorry, the page you tried to visit is coming soon (or doesn\'t exist!). '
	. 'You can explore Concept Corner by visiting the <a href="/">home page</a>.</p>';
echo '</div>';
echo '</div>';
echo '</div>';

get_footer();