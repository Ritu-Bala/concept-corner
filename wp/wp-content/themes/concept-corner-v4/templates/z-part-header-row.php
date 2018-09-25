<?php
// header row used in templates

/**
 * @var array $cc_current_grade
 * @var array $cc_content
 * @var \Concept_Corner $cc
 * @var array $this_explanation
 */

// set this to true to get a drop-down button with focus areas
$focus_drop_flag = false;

echo '<div class="row">';
echo '<div id="btn-navigation" class="col-xs-12 col-md-12">';

// glossary for lower grades
if ( (int) $cc_current_grade['name'] < 9 ) {

	echo '<div class="btn-group pull-right">';
	echo '<a href="'
	     . get_bloginfo( 'template_directory' )
	     . '/templates/glossary.php?gradeid='
	     . $cc_current_grade['id']
	     . '&amp;grade='
	     . $cc_current_grade['name']
	     . '" class="btn btn-glossary fancybox.ajax"><span class="glossaryicon"></span> Glossary</a>';
	echo '</div>';
}

// filter for extra buttons
echo apply_filters( 'cc_additional_header_buttons', '', $cc_current_grade['name'] );

// get terms in taxonomy
$focus_terms = $terms = get_terms( array( 'cc_focus' ) );

// set defaults
$focus_menu_name = 'Focus';
$f_items         = array();

// for each term, determine if there are any matching explanations in this grade
foreach ( $focus_terms as $ft ) {

	$any = $cc->get_cc_explanations( 'focus', $ft->term_id );

	if ( count( $any ) > 0 ) {

		foreach ( $any as $e ) {

			if ( in_array( $cc_content['filters']['grade'], $e['cc_grade'] ) ) {

				// if there is, add it to menu
				$f_link = '/explore/' . $nice_filter['grade'] . '/focus/' . $ft->slug . '/';

				if ( $cc_content['filters']['special_term'] == $ft->term_id ) {
					$focus_menu_name = $ft->name;
				}

				$f_items[] = '<a href="' . $f_link . '" class="">' . $ft->name . '</a>';
				break;
			}
		}
	}
}

// output button or dropdown
if ( isset( $f_items[0] ) && $f_items[0] ) {

	echo '<div class="btn-group pull-right">';

	if ( $focus_drop_flag === false ) {
		$single_button = str_replace( 'class=""', 'class="btn btn-unitsdropdown"', $f_items[0] );
		// this assumes one focus item and that the first one is the one desired...
		echo $single_button;

	} else {
		echo '<button type="button" class="btn btn-unitsdropdown dropdown-toggle" data-toggle="dropdown">'
		     . $focus_menu_name
		     . ' <i class="fa fa-chevron-down" aria-hidden="true"></i></button><ul class="dropdown-menu" role="menu">';
		foreach ( $f_items as $f ) {
			echo '<li role="presentation">' . $f . '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}

$u_items   = '';
$menu_name = $cc_content['labels']['units'][1];

// load units for focus term items

if ( isset( $f_items[0] ) && $f_items[0] && empty( $cc_content['units'] ) ) {
	$cc_content['units'] = $cc->get_cc_units( $cc_content['filters']['grade'] );

	foreach ( $cc_content['units'] as $key => $menu_unit ) {

		$empty = array_filter( $menu_unit['cc_explanation'] );
		if ( empty ( $empty ) ) {
			unset( $cc_content['units'][ $key ] );
		}
	}
}

if ( $cc_content['units'] ) {

	foreach ( $cc_content['units'] as $unit ) {

		$unit_link = '/explore/' . $nice_filter['grade'] . '.' . $unit['unit_order'] . '/';

		$concept_units[ $unit['ID'] ] = $unit_link;

		$this_unit_number = '';

		if ( (int) $nice_filter['grade'] < 9 && (int) $nice_filter['grade'] > 1 ) {
			$this_unit_number = $nice_filter['grade'] . '.' . $unit['unit_order'] . ' ';
		}

		if ( $unit['ID'] == $cc_content['filters']['unit'] ) {
			$menu_name = $this_unit_number . $unit['display_title'];
		}

		$u_items .= '<li role="presentation"><a href="' . $unit_link . '">' . $this_unit_number . $unit['display_title'] . '</a></li>';

	}
}

echo '<div class="btn-group pull-right">';
echo '<button type="button" class="btn btn-unitsdropdown dropdown-toggle" data-toggle="dropdown">';
echo $menu_name . ' <i class="fa fa-chevron-down" aria-hidden="true"></i></button>';
echo '<ul class="dropdown-menu" role="menu">' . $u_items . '</ul>';
echo '</div>';
echo '</div>';

if ( isset( $show_title ) && $show_title === true ) {

	echo '<div class="col-xs-12 col-md-12 text-center">';
	echo '<h4 class="concepttitle">' . $cc_content['labels']['concepts'][0] . '</h4>';
	echo '<h1 class="pagetitle">' . $cc_content['concepts'][ $cc_content['filters']['concept'] ]['display_title'] . '</h1>';
	echo '</div>';

	echo '<div class="col-xs-12 col-md-12"><div class="gradientline">';

	// default subnav
	$subnav = '<span class="gradientline-active">Explanations</span>';

	// need to check if there are problems associated, and whether $this_problem is set to determine which to highlight
	if ( isset( $this_problem ) && $this_problem ) {
		$subnav = '<span><a href="' . '/explore/'
		          . $nice_filter['grade'] . '.'
		          . $nice_filter['unit'] . '/'
		          . $nice_filter['concept']
		          . '/' . '">Explanations</a></span> <span class="gradientline-active">Worked Examples</span>';
	} // are there any problems?
	else if ( ! empty ( $cc_content['problems'] ) ) {
		$subnav .= ' <span><a href="' . '/explore/'
		           . $nice_filter['grade'] . '.'
		           . $nice_filter['unit'] . '/'
		           . $nice_filter['concept']
		           . '/worked/' . '">Worked Examples</a></span>';

	}

	echo $subnav;
	echo '</div></div>';
}

if ( isset( $show_focus ) && $show_focus === true ) {

	$focus_title = $nice_filter['grade'];

	if ( isset( $this_term['name'] ) && $this_term['name'] ) {
		$focus_title .= ': ' . $this_term['name'];
	}

	echo '<div class="col-xs-12 col-md-12 text-center">';
	echo '<h4 class="concepttitle">Grade ' . $focus_title . '</h4>';
	echo '<h1 class="pagetitle">' . $this_explanation['display_title'] . '</h1>';
	echo '</div>';
}

echo '</div>';