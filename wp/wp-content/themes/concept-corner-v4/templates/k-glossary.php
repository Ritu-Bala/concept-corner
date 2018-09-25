<?php

// unit title
$unit_title = 'Glossary';

$cc_gloss_grade = $cc_current_grade['id'];

$query = "SELECT
			pa.*,
			ma.meta_value AS synonym,
			mb.meta_value AS public_title,
			md.meta_value AS also_show_number,
			me.meta_value AS integer_value,
			GROUP_CONCAT( mc.meta_value SEPARATOR ',' ) AS related_terms
		FROM
			`ccs_term_relationships` tr,
			`ccs_term_taxonomy` tt,
			`ccs_terms` t,
			`ccs_posts` pa
		LEFT JOIN
			`ccs_postmeta` ma ON ( pa.ID = ma.post_id AND ma.meta_key = 'synonym')
		LEFT JOIN
			`ccs_postmeta` mb ON ( pa.ID = mb.post_id AND mb.meta_key = 'public_title')
		LEFT JOIN
			`ccs_postmeta` mc ON ( pa.ID = mc.post_id AND mc.meta_key = 'related_terms')
		LEFT JOIN
			`ccs_postmeta` md ON ( pa.ID = md.post_id AND md.meta_key = 'also_show_number')
		LEFT JOIN
			`ccs_postmeta` me ON ( pa.ID = me.post_id AND me.meta_key = 'integer_value')
		WHERE
			1 AND
			pa.post_type = 'cc_glossary_term' AND
			pa.post_status = 'publish' AND
			pa.ID = tr.object_id AND
			tr.term_taxonomy_id = tt.term_taxonomy_id  AND
			tt.term_id = t.term_id AND
			t.term_id = '" . $cc_gloss_grade . "' AND
			tt.taxonomy = 'cc_grade'
		GROUP BY
			pa.ID
		ORDER BY
			pa.post_title ASC";

$cc_glossary = $wpdb->get_results( $query );

$cc_glossary_public_title = array();

foreach ( $cc_glossary as $v ) {

	if ( $v->public_title ) {
		$cc_glossary_public_title[ $v->ID ] = $v->public_title;
	} else {
		$cc_glossary_public_title[ $v->ID ] = $v->post_title;
	}

}

$glossary_content = '';
$glossary_number_content = array();
$current_letter   = '';

foreach ( $cc_glossary as $post ) :

	$this_glossary_content = '';
	$this_letter           = strtolower( substr( $cc_glossary_public_title[ $post->ID ], 0, 1 ) );

	if ( $current_letter != $this_letter ) {

		// add letter to hot link reference
		$letters[ ord( $this_letter ) ] = 1;
		$glossary_content .= '<a id="letter' . $this_letter . '" class="cc-anchor-letter"></a>';

		// make this current letter
		$current_letter = $this_letter;
	}

	$this_glossary_content .= '<div class="row"><div class="col-xs-2 col-md-1"></div><div class="glossaryitem col-xs-12 col-md-10">';
	$this_glossary_content .= '<a id="qqqqqq" class="cc-gloss-item-anchor"></a><div class="glossaryitemtitle">' . $cc_glossary_public_title[ $post->ID ] . '</div>';

	$this_glossary_content .= '<div class="glosssaryitemcontent">';

	if ( ! empty( $post->synonym ) ) {
		$syn_title = $cc_glossary_public_title[ $post->ID ];
		$this_glossary_content .= '<p>See <a href="#g' . $post->synonym . '">' . $syn_title . '</a></p>';
	} else {
		$this_glossary_content .= apply_filters( 'the_content', $post->post_content );
	}

	if ( ! empty( $post->related_terms ) ) {
		$rel = explode( ',', $post->related_terms );
	}

	if ( isset( $rel[0] ) && $rel[0] ) {
		$rl = '';
		foreach ( $rel as $r ) {
			if ( isset( $cc_glossary_public_title[ $r ] ) && $cc_glossary_public_title[ $r ] ) {
				$rl_title = $cc_glossary_public_title[ $r ];
				$rl .= ' <a href="#g' . $r . '">' . $rl_title . '</a>,';
			}
		}
		if ( $rl ) {
			$this_glossary_content .= '<p class="cc-gloss-related"><em>Related terms:</em>';
			$rl = mb_substr( $rl, 0, - 1 );
			$this_glossary_content .= $rl;
			$this_glossary_content .= '</p>';
		}
	}

	$this_glossary_content .= '</div></div><div class="col-xs-2 col-md-1"></div></div>';

	// add to numerals section

	if ( isset( $post->also_show_number ) && $post->also_show_number == '1' ) {
		$num_key = 0;
		if ( isset( $post->integer_value ) && $post->integer_value > 0 ) {
			$num_key = $post->integer_value;
		}
		$glossary_number_content[] = array( 'order' => $num_key, 'content' => str_replace( 'qqqqqq', 'num' . $post->ID, $this_glossary_content ) );
	}

	// add ID to href
	$glossary_content .= str_replace( 'qqqqqq', 'g' . $post->ID, $this_glossary_content );

endforeach;

// build the index div

$nav = array();

if ( ! empty( $glossary_number_content ) ) {

	// re-index array

	$number_content = $cc->array_msort( $glossary_number_content, array( 'order' => SORT_NUMERIC ) );

	// add hash symbol to nav

	$nav[] = '<div><a href="#numbers">#</a></div>';

	$add_numbers = '<a id="numbers" class="cc-anchor-letter"></a>';

	// add numbers entries to front of glossary content

	foreach ( $number_content as $num ) {

		$add_numbers .= $num['content'];
	}

	$glossary_content = $add_numbers . $glossary_content;
}

for ( $i = 97; $i <= 122; $i ++ ) {
	$letter = chr( $i );
	if ( isset( $letters[ $i ] ) && $letters[ $i ] > 0 ) {
		$nav[] = '<div><a href="#letter' . $letter . '">' . strtoupper( $letter ) . '</a></div>';
	} else {
		$nav[] = '<div>' . strtoupper( $letter ) . '</div>';
	}
}

// header
$header_flag = 'glossary';
include( CC_PATH . '/templates/z-part-header-k.php');

// content
echo '<section id="list">';
echo '<div class="container">';

echo $glossary_content;

echo '</div>';
echo '</section>';

// JS
echo '<script>';
echo 'jQuery(document).ready(function ($) {';
echo '$(\'.glosssaryitemcontent img\').removeClass(\'alignnone\').addClass(\'img-responsive\').removeAttr("width").removeAttr("height");';
echo '});';
echo '</script>';