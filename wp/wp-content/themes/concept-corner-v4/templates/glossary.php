<?php
function find_wp() {
	$here = explode( DIRECTORY_SEPARATOR, __FILE__ );
	$wop = '';
	$file = 'wp-blog-header.php';
	foreach ( $here as $h ) {
		if ( $h == 'wp-content' ) break;
		$wop .= $h . DIRECTORY_SEPARATOR;
	}
	if ( file_exists( $wop . $file ) ) return $wop . $file;
	$scanned = array_diff( scandir( $wop ), array( '..', '.' ) );
	foreach ( $scanned as $scan ) {
		if ( file_exists( $wop . $scan . DIRECTORY_SEPARATOR . $file ) )
			return $wop . $scan . DIRECTORY_SEPARATOR . $file;
	}
	return false;
}

$bh = find_wp();

if ( ! $bh ) die( 'Cannot find Wordpress.' );

require_once( $bh );

global $cc, $cc_content, $cc_current_grade, $wpdb;

// not thrilled by this, either...
$cc_gloss_grade      = $_GET['gradeid'];
$cc_gloss_grade_name = $_GET['grade'];
$grade_class         = 'grade-' . $cc_gloss_grade_name;

// pulled from last version of glossary...

$q_select = '';
$q_from   = '';
$q_where  = '';
$q_join   = '';

$q_from  = "
				`ccs_term_relationships` tr,
				`ccs_term_taxonomy` tt,
				`ccs_terms` t,";
$q_where = "AND
				pa.ID = tr.object_id AND
				tr.term_taxonomy_id = tt.term_taxonomy_id  AND
				tt.term_id = t.term_id AND
				t.term_id = '" . $cc_gloss_grade . "' AND
				tt.taxonomy = 'cc_grade'";

$query = "SELECT
			pa.*,
			ma.meta_value as synonym,
			mb.meta_value as public_title,
			GROUP_CONCAT( mc.meta_value SEPARATOR ',' ) as related_terms " . $q_select . "
		FROM " . $q_from . "
			`ccs_posts` pa
		LEFT JOIN
			`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'synonym')
		LEFT JOIN
			`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'public_title')
		LEFT JOIN
			`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = 'related_terms') " . $q_join . "
		WHERE
			1 AND
			pa.post_type = 'cc_glossary_term' AND
			pa.post_status = 'publish' " . $q_where . "
		GROUP BY
			pa.ID
		ORDER BY
			pa.post_title ASC";

$cc_glossary = $wpdb->get_results( $query );

$cc_glossary_public_title = array();
foreach ( $cc_glossary as $v ) {
	$cc_glossary_public_title[ $v->ID ] = $v->post_title;
	if ( isset( $v->public_title ) && $v->public_title ) {
		$cc_glossary_public_title[ $v->ID ] = $v->public_title;
	}

}

$glossary_content = '';
$current_letter   = '';

foreach ( $cc_glossary as $post ) :

	$grade_indicator = '';

	$this_letter = strtolower( substr( $cc_glossary_public_title[ $post->ID ], 0, 1 ) );
	if ( $current_letter != $this_letter ) {
		// add letter to hot link reference
		$letters[ ord( $this_letter ) ] = 1;
		$glossary_content .= '<a id="letter' . $this_letter . '"></a>';
		// make this current letter
		$current_letter = $this_letter;
	}
	$glossary_content .= '<div class="glossaryitem">';
	$glossary_content .= '<a id="g' . $post->ID . '" class="cc-gloss-item-anchor"></a><div class="glosssaryitemtitle">' . $cc_glossary_public_title[ $post->ID ] . '</div>';

	$glossary_content .= '<div class="glosssaryitemcontent">';

	if ( ! empty( $post->synonym ) ) {
		$syn_title = $cc_glossary_public_title[ $post->synonym ];
		$glossary_content .= '<p>See <a href="#g' . $post->synonym . '">' . $syn_title . '</a></p>';
	} else {
		$glossary_content .= do_shortcode( $post->post_content );
	}

	$rel = '';
	if ( ! empty( $post->related_terms ) ) {
		$rel = explode( ',', $post->related_terms );
	}

	$rl = '';
	if ( isset( $rel[0] ) && $rel[0] ) {
		foreach ( $rel as $r ) {
			if ( isset( $cc_glossary_public_title[ $r ] ) && $cc_glossary_public_title[ $r ] ) {
				$rl_title = $cc_glossary_public_title[ $r ];
				$rl .= ' <a href="#g' . $r . '">' . $rl_title . '</a>,';
			}
		}
		if ( $rl ) {
			$glossary_content .= '<p class="cc-gloss-related"><em>Related terms:</em>';
			$rl = mb_substr( $rl, 0, - 1 );
			$glossary_content .= $rl;
			$glossary_content .= '</p>';
		}
	}

	$glossary_content .= '</div></div>';

endforeach;

// build the index div

$nav = array();
for ( $i = 97; $i <= 122; $i ++ ) {
	$letter = chr( $i );
	$labelby_id = ($letter == 'a')?'id="first_letter"' : '';
	if ( isset( $letters[ $i ] ) && $letters[ $i ] > 0 ) {
		$nav[] = '<li><a '.$labelby_id.' href="#letter' . $letter . '"><span>' . strtoupper( $letter ) . '</span></a></li>';
	} else {
		$nav[] = '<span>' . strtoupper( $letter ) . '</span>';
	}
}


// CACHE

// get user role for cache key
if ( ! isset( $cc_content['user_role'] ) ) {
	$cache_role              = ( isset( $_COOKIE['role'] ) ) ? $_COOKIE['role'] : $cc->get_user_role();
	$cc_content['user_role'] = $cache_role;
} else {
	$cache_role = $cc_content['user_role'];
}

// cache key
$cache_key = 'glossary-' . $cache_role . '-' . $grade_class;

// cache
if ( $cached = $cc->cc_fragment_cache( $cache_key ) ) {

	// output cached result
	$cached .= '<!-- Cache from: ' . $cache_key . ' -->';

	echo $cached;

} else {

	// capture the main part for caching purposes
	ob_start();

	echo '<div class="glossary ' . $grade_class . '" aria-hidden="true" role="dialog"><div class="hidden">Dialog content starts</div>';
	echo '<div class="container-fluid">';
	echo '<div class="row">';
	echo '<div class="doubleshadow">';
	echo '<div class="col-xs-3 col-md-2 glossarytitle">';
	echo '<h1 aria-labelled="first_letter">Grade ' . $cc_gloss_grade_name . '<br/>Glossary</h1>';
	echo '</div>';
	echo '<ul class="col-xs-9 col-md-10 letterchoser">';
	foreach ( $nav as $n ) {
		echo $n;
	}

	echo '</ul>';
	echo '<div class="clearfix"></div>';
	echo '</div>';
	echo '<div class="col-xs-12 col-md-12 glossarycontentwrap">';

	echo $glossary_content;

	echo '</div>';
	echo '</div>';
	echo '</div><div class="hidden">Dialog content ends</div>';
	echo '</div>';

	echo '<script>';
	echo 'jQuery(".glossarycontentwrap img").addClass("img-responsive").addClass("aligncenter").removeClass("alignnone").removeAttr("width").removeAttr("height");';
	echo '</script>';

	$output = ob_get_clean();

	$cc->cc_fragment_cache( $cache_key, $output );
	$output .= '<!-- Cache saved: ' . $cache_key . ' -->';

	echo $output;
}