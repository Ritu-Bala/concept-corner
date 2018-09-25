<?php
/**
 * Template Name: Explore
 *
 * This page exists to route requests based on the url seeking them.
 */

/**
 * Access our global items
 */

/**
 * @var Concept_Corner $cc
 */
global $cc, $cc_content, $cc_grades, $cc_current_grade, $cc_track, $user_login;
$cc_upload_dir = CC_CDN;

$debug = '';
if ( isset( $_GET['debug'] ) ) {
	$debug = $_GET['debug'];
}

/**
 * Routing rules.
 *
 */

$routing = array(

	// #0 generic grade match
	array(
		'template' => 'grade-all-concepts.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #1 grades 9, 10, 11, 12
	array(
		'template' => 'upper-all-videos.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '8',
				'op'    => '>',
			),
		),
	),

	// #2 grades K, 1
	array(
		'template' => 'k-video-all.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
		),
	),

	// #2 grades K, 1
	array(
		'template' => 'k-home.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'special',
				'match' => 'k1',
				'op'    => '=',
			),
		),
	),

	array(
		'template' => 'k-page.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'special',
				'match' => 'page',
				'op'    => '=',
			),
		),
	),

	array(
		'template' => 'page.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '',
			),
			array(
				'type'  => 'special',
				'match' => 'page',
				'op'    => '=',
			),
		),
	),

	// #3 generic grade + generic unit
	array(
		'template' => 'grade-all-concepts.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #4 grades 9, 10, 11, 12 + generic unit
	array(
		'template' => 'upper-unit-videos.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '8',
				'op'    => '>',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
		),
	),


	// #5 grades K,1 + generic unit
	array(
		'template' => 'k-video.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #6 grades K,1 + glossary
	array(
		'template' => 'k-glossary.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'special',
				'match' => 'glossary',
				'op'    => '=',
			),
		),
	),

	// #7 grades K,1 + video
	array(
		'template' => 'k-video-all.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'special',
				'match' => 'video',
				'op'    => '=',
			),
		),
	),

	// #8 generic grade + generic unit + generic concept
	array(
		'template' => 'single-concepts.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '>',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'concept',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #9 generic grade + focus keyword + focus term
	array(
		'template' => 'single-focus.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '>',
			),
			array(
				'type'  => 'special',
				'match' => 'focus',
				'op'    => '=',
			),
			array(
				'type'  => 'special_term',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #10 K, 1 + focus keyword + focus term
	array(
		'template' => 'k-video.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '2',
				'op'    => '<',
			),
			array(
				'type'  => 'special',
				'match' => 'focus',
				'op'    => '=',
			),
			array(
				'type'  => 'special_term',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

	// #11 generic grade + generic unit + generic concept + explanation
	array(
		'template' => 'single-concepts.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '>',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'concept',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'explanation',
				'match' => '*',
				'op'    => '=',
			),
		),
	),


	// #12 generic grade + generic unit + generic concept + worked keyword
	array(
		'template' => 'single-worked.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '>',
			),
			array(
				'type'  => 'unit',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'concept',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'special',
				'match' => 'worked',
				'op'    => '=',
			),
		),
	),

	// #13 generic grade + focus keyword + focus term + explanation
	array(
		'template' => 'single-focus.php',
		'filter'   => array(
			array(
				'type'  => 'grade',
				'match' => '*',
				'op'    => '>',
			),
			array(
				'type'  => 'special',
				'match' => 'focus',
				'op'    => '=',
			),
			array(
				'type'  => 'special_term',
				'match' => '*',
				'op'    => '=',
			),
			array(
				'type'  => 'explanation',
				'match' => '*',
				'op'    => '=',
			),
		),
	),

);


/**
 * The filters array within cc_content will tell us about the current URL
 * We will process them in a way that will go from most specific to least specific
 */

$sorting = array();

// transform the filters as follows:
// grades from ID to name
// units from ID to array position
// concepts from ID to slug

$nice_filter = array();
foreach ( $cc_content['filters'] as $k => $f ) {

	if ( $f !== NULL ) {

		if ( $k == 'grade' ) {

			$nice_filter['grade'] = $cc_grades[ $f ]['name'];

		} else if ( $k == 'unit' ) {

			$nice_filter['unit'] = $cc_content['units'][ $f ]['unit_order'];

		} else if ( $k == 'concept' ) {

			$nice_filter['concept'] = $cc_content['concepts'][ $f ]['post_name'];

		} else if ( $k == 'explanation' ) {

			foreach ( $cc_content['explanations'] as $ex ) {
				if ( $f == $ex['ID'] ) {
					$nice_filter['explanation'] = $ex['post_name'];
					break;
				}
			}

		} else if ( $k == 'problem' ) {

			foreach ( $cc_content['problems'] as $prob ) {
				if ( $f == $prob['ID'] ) {
					$nice_filter['problem'] = $prob['post_name'];
					break;
				}
			}

		} else if ( $k == 'special' ) {

			$nice_filter['special'] = $f;

		} else if ( $k == 'special_term' ) {

			$this_term                   = get_term_by( 'id', $f, 'cc_focus', ARRAY_A );
			$nice_filter['special_term'] = $this_term['slug'];

		}
	}
}

// compare our rules to the filter, and score them

foreach ( $routing as $rkey => $rule ) {

	// if match in rules is '*' this gets one point
	// if it's an exact match, it gets 2 points. If it's a generic match it gets one point
	// points are multiplied by number of rules matched.

	$points = 0;

	// we only check a rule if it has the same number of rules or less than the number of filters

	if ( count( $rule['filter'] ) <= count( $nice_filter ) ) {

		foreach ( $rule['filter'] as $check ) {

			foreach ( $nice_filter as $given => $value ) {

				if ( $check['type'] == $given && $value !== NULL ) {

					// all stars are generic matches
					if ( $check['match'] == '*' ) {
						$points ++;
					} else {
						// operators  = > <
						if ( $check['op'] == '=' && $check['match'] == $value ) {
							$points ++;
							$points ++;
						} else if ( $check['op'] == '>' && ( (int) $check['match'] < (int) $value ) ) {
							$points ++;
							$points ++;
						} else if ( $check['op'] == '<' && ( (int) $check['match'] > (int) $value ) ) {
							$points ++;
							$points ++;
						}
					}
				}
			}
		}
	}

	$sorting[ $rkey ] = $points * count( $nice_filter );
}

// sort the results so the highest points are first, and get the best match

arsort( $sorting );
reset( $sorting );
$first_key = key( $sorting );

// set template

$template = $routing[ $first_key ]['template'];

$flagger = '';
// set K flag to load special header and footer
if ( isset( $cc_current_grade['name'] ) && (int) $cc_current_grade['name'] < 2 ) {
	$flagger = 'k';
}

// get user role
if ( ! isset( $cc_content['user_role'] ) ) {
	$cache_role              = ( isset( $_COOKIE['role'] ) ) ? $_COOKIE['role'] : $cc->get_user_role();
	$cc_content['user_role'] = $cache_role;
} else {
	$cache_role = $cc_content['user_role'];
}

$cache_filters = ( ! empty( $nice_filter ) ) ? implode( '-', $nice_filter ) : 'none';
$cache_key     = 'explore-' . $cache_role . '-' . $cache_filters . '-' . $template . '-' . $cc_track;

if ( $debug && $user_login == 'rogerlos' ) {
	print '<h3>User Role</h3>';
	var_dump( $cache_role );
	print '<h3>Cache Key</h3>';
	var_dump( $cache_key );
	print '<h3>Cookies</h3>';
	var_dump( $_COOKIE );
	print '<h3>Sorting</h3>';
	var_dump( $sorting );
	print '<h3>Template</h3>';
	var_dump( $template );
	print '<h3>Flag</h3>';
	var_dump( $flagger );
	print '<h3>"Nice" Filters</h3>';
	var_dump( $nice_filter );
	print '<h3>Filters</h3>';
	var_dump( $cc_content['filters'] );
	print '<h3>Grades</h3>';
	var_dump( $cc_grades );
	print '<h3>Content</h3>';
	var_dump( $cc_content );
	die();
}

// cache
if ( $cached = $cc->cc_fragment_cache( $cache_key ) ) {

	get_header( $flagger );
	
	// output cached result
	$cc_content['cache'] = 'From cache: ' . $cache_key;

	echo $cached;
	
	get_footer();

} else {

	// capture the main part for caching purposes
	ob_start();
	
	get_header( $flagger );

	include( CC_PATH . '/templates/' . $template );
	
	get_footer();

	$output = ob_get_clean();
	
	// if files are defined as OK, use
	global $wp;
	// $path = ( CC_FRAGMENT_CACHE_USE_FILES === true ) ? $wp->request : null;

	// $cache_result = $cc->cc_fragment_cache( $cache_key, $output, TRUE, $path );

	echo $output;
}