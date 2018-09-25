<?php 
/**
* Template Name: Homepage
*/

global $cc, $cc_content, $cc_grades, $cc_all_grades, $cc_gradename, $cc_track;

global $user_login;

// active grades by name
$active_grades = array();
foreach ( $cc_grades as $key => $grade ) {
	$active_grades[ $key ] = $grade['name'];
}

// focus terms
$focus_terms = get_terms( 'cc_focus' );
$focus_in_grade = array();

foreach ( $focus_terms as $ft ) {

	$any = $cc->get_cc_explanations( 'focus', $ft->term_id );

	foreach ( $cc_grades as $gr ) {

		$focus_in_grade[ $gr['slug'] ] = '';

		foreach( $any as $e	) {

			if ( in_array( $gr['term_id'], $e['cc_grade'] ) ) {

				$f_link = '/explore/' . $gr['name'] . '/focus/' . $ft->slug . '/';
				$focus_in_grade[ $gr['slug'] ] .= '<li><a href="' . $f_link . '">' . $ft->name . '</a></li>';
				break;
			}
		}
	}
}

if ( $user_login == 'rogerlos' && isset( $_GET['debug'] ) ) {

	print 'all grades<br>';
	var_dump( $cc_all_grades );
	print '<hr>';

	print 'grades<br>';
	var_dump( $cc_grades );
	print '<hr>';

	print 'active grades<br>';
	var_dump( $active_grades );
	print '<hr>';

	print 'focus<br>';
	var_dump( $focus_terms );
	print '<hr>';

	print 'focus in grade<br>';
	var_dump( $focus_in_grade );
	print '<hr>';

	print 'registered taxonomies<br>';
	var_dump( get_taxonomies() );
	print '<hr>';

	foreach( $cc_content as $key => $ccc ) {
		if ( $_GET['debug'] == $key || $_GET['debug'] == 'all' ) {
			print $key . '<br>';
			var_dump( $ccc );
		}
	}
	die();
}

get_header();

// CACHE

// get user role for cache key
if ( ! isset( $cc_content['user_role'] ) ) {
	$cache_role = ( isset( $_COOKIE['role'] ) ) ? $_COOKIE['role'] : $cc->get_user_role();
	$cc_content['user_role'] = $cache_role;
} else {
	$cache_role = $cc_content['user_role'];
}

// cache key
$cache_key = 'home-' . $cache_role . '-' . $cc_track;

// cache
if ( $cached = $cc->cc_fragment_cache( $cache_key ) ) {

	// output cached result
	$cc_content['cache'] = 'From cache: ' . $cache_key;

	echo $cached;

} else {

	// capture the main part for caching purposes
	ob_start();

	echo '<h1 class="hidden">Concept Corner</h1><div id="content" role="main"><div class="container"><div class="row no-gutter">';

	if ( have_posts() ) {

		while ( have_posts() ) {

			the_post();

			echo '<div class="col-xs-12 col-md-12"><div class="post" id="post-' . get_the_ID() . '"><div class="homeentry">';

			the_content();

			echo '</div></div></div>';

			if ( $cc_all_grades ) {

				echo '<div id="gradeselector" class="col-xs-12 col-md-12">';

				$total = count( $cc_all_grades );
				$i     = 1;

				foreach ( $cc_all_grades as $key => $grade ) {

					// active? Substitute in active grade
					$active = false;
					if ( in_array( $grade['name'], $active_grades ) ) {
						$active = true;
						$key    = array_search( $grade['name'], $active_grades );
						$grade  = $cc_grades[ $key ];
					}

					$grade_slug = $grade['name'];
					$tracker    = 0;

					/* lets determine item classes */
					$classlvl1  = '';
					$classlvl2  = '';
					$href_class = '';

					if ( $i < ( $total - 3 ) ) { // except last row
						if ( $i % 4 == 1 ) {
							$classlvl1 = 'gradeborderbottomleft';
						} elseif ( $i % 4 == 0 ) {
							$classlvl1 = 'gradeborderbottomright';
						} else {
							$classlvl1 = 'gradeborderbottom';
						}
					}

					if ( $i % 4 != 0 ) { // except last column

						if ( $i < 4 ) {
							$classlvl2 = 'gradeborderlefttop';
						} elseif ( $i > ( $total - 4 ) ) {
							$classlvl2 = 'gradeborderleftbottom';
						} else {
							$classlvl2 = 'gradeborderleft';
						}
					}

					if ( $i % 4 == 0 ) {
						$href_class = 'homecolumn4';
					}

					// if this is an active grade, include link
					if ( $active ) {
						$link       = '<a class="popoverclass aligncenter ' . $href_class . '" title="Grade ' . $grade['name'] . '" href="#">';
						$link_close = '</a>';
					} else {
						$link       = '<span class="nolink aligncenter">';
						$link_close = '</span>';
					}
					
					echo '<div class="col-xs-3 col-md-3">';
					echo '<div class="gradeblock ' . $classlvl1 . '">';
					echo '<div class="' . $classlvl2 . '">';

					echo $link . 'grade <span>' . $grade['name'] . '</span>' . $link_close;

					if ( $active ) {
						echo '<div class="popovercontentwrapper hide"><ul class="unitlist">';

						$all_concepts_link = '/explore/' . $grade_slug . '/';

						if ( (int) $grade_slug > 1 ) {
							echo '<li><a href="' . $all_concepts_link . '">Grade ' . $grade['name'] . ': All Concepts</a></li>';
						}

						if ( ! empty( $cc_content['units'] ) ) {

							foreach ( $cc_content['units'] as $unit ) {

								$unum = '';

								if ( (int) $grade_slug < 9 ) {
									$unum = $grade_slug . '.' . $unit['unit_order'] . ' ';
								}

								if ( in_array( $grade['term_id'], $unit['cc_grade'] ) ) {
									echo '<li><a href="/explore/' . $grade_slug . '.' . $unit['unit_order'] . '/">' . $unum . $unit['display_title'] . '</a></li>';
								}
							}
						}

						// add any relevent focus terms to the menus
						if ( isset( $focus_in_grade[ $grade['slug'] ] ) && $focus_in_grade[ $grade['slug'] ] ) {
							echo $focus_in_grade[ $grade['slug'] ];
						}

						// allow plugins to add items to this menu
						echo apply_filters( 'cc_home_page_add_item', '', $grade_slug );

						echo '</ul></div>';
					}

					echo '</div></div></div>';
					$i ++;
				}

				echo '<div class="clearfix"></div></div>';
			}
		}
	}

	echo '</div></div></div>';

	$output = ob_get_clean();

	global $wp;
	$path = ( CC_FRAGMENT_CACHE_USE_FILES === true ) ? $wp->request : null;

	$cache_result = $cc->cc_fragment_cache( $cache_key, $output, TRUE, $path );

	echo $output;
}

get_footer();