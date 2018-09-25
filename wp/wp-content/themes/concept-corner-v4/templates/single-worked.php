<?php
// this grabs the first problem's content for display if we're only bringing a concept to this template
// duplicate of single concept with appropriate changes

$this_problem = '';

// set the problem content...
if ( ! isset( $nice_filter['problem'] ) ) {
	$this_problem = reset( $cc_content['problems'] );
}
else {
	$this_problem = $cc_content['problems'][ $cc_content['filters']['problem'] ];
}

// this is from last version, a flag to show full width problems with answers underneath.
$f_flag = ( $this_problem['full_width'] == 1 ) ? true : false;

$this_thumb_name = 'thumbnail';

echo '<div id="content" role="main"><div class="container">';

$show_title = true;
include( CC_PATH . '/templates/z-part-header-row.php' );

echo '<div class="row">';

// set column widths based on full flag
$xs = ( $f_flag ) ? 7 : 4;
$md = ( $f_flag ) ? 8 : 4;

echo '<div class="col-xs-<' . $xs . ' col-md-' . $md . ' col-xs-offset-1 col-md-offset-1 verticalborder">';
echo '<div>';
echo '<div class="entry">';

echo '<h2 class="inline-pagetitle">' . $this_problem['display_title'] . '</h2>';
echo apply_filters( 'the_content', $this_problem['post_content'] );

// add second column if not full width
if ( ! $f_flag ) {
	echo '</div>';
	echo '</div>';
	echo '</div>';
	echo '<div class="col-xs-3 col-md-4 verticalborder">';
	echo '<div>';
	echo '<div class="entry">';
}

// solution buttons pulled straight from last version, need styling

$answer_select = '<h3>Solution:</h3><div id="cc-problem-ans-buttons">';
$answer_select .= '<button type="button" data-answer="1"><span>1</span></button>';

if ( isset( $this_problem['answer_2'] ) && $this_problem['answer_2'] ) {
	$answer_select .= '<button type="button" data-answer="2"><span>2</span></button>';
}
if ( isset( $this_problem['answer_3'] ) && $this_problem['answer_3'] ) {
	$answer_select .= '<button type="button" data-answer="3"><span>3</span></button>';
}
if ( isset( $this_problem['answer_4'] ) && $this_problem['answer_4'] ) {
	$answer_select .= '<button type="button" data-answer="4"><span>4</span></button>';
}
if ( isset( $this_problem['answer_5'] ) && $this_problem['answer_5'] ) {
	$answer_select .= '<button type="button" data-answer="5"><span>5</span></button>';
}

$answer_select .= '</div>';

echo $answer_select;

echo '<div id="cc-body-answer-1" class="cc-body-answer">';
echo( apply_filters( 'the_content', $this_problem['answer_1'] ) );
echo '</div>';

echo '<div id="cc-body-answer-2" class="cc-body-answer">';
echo( apply_filters( 'the_content', $this_problem['answer_2'] ) );
echo '</div>';

echo '<div id="cc-body-answer-3" class="cc-body-answer">';
echo( apply_filters( 'the_content', $this_problem['answer_3'] ) );
echo '</div>';

echo '<div id="cc-body-answer-4" class="cc-body-answer">';
echo( apply_filters( 'the_content', $this_problem['answer_4'] ) );
echo '</div>';

echo '<div id="cc-body-answer-5" class="cc-body-answer">';
echo( apply_filters( 'the_content', $this_problem['answer_5'] ) );
echo '</div>';

echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="col-xs-3 col-md-2">';
echo '<div class="conceptsidebar">';

$sidebar = '';

foreach ( $cc_content['problems'] as $prob ) {
	
	if ( $prob['ID'] != $this_problem['ID'] ) {
		
		$this_link = '/explore/' . $nice_filter['grade'] . '.' . $nice_filter['unit'] . '/' . $nice_filter['concept'] .
			'/worked/' . $prob['post_name'];
		
		$sidebar .= '<a href="' . $this_link . '" class="thumbnail videoblock">';
		
		$this_image = '';
		if ( isset( $prob['thumbnail_meta'] ) &&
			array_key_exists( $this_thumb_name, $prob['thumbnail_meta']['sizes'] )
		) {
			$explode = explode( '/', $prob['thumbnail_meta']['file'] );
			array_pop( $explode );
			$this_image_path = implode( '/', $explode ) . '/';
			$this_image = $cc_upload_dir . $this_image_path .
				$prob['thumbnail_meta']['sizes'][ $this_thumb_name ]['file'];
		}
		else if ( isset( $prob['thumbnail_meta'] ) ) {
			$this_image = $cc_upload_dir . $prob['thumbnail_meta']['file'];
		}
		
		if ( isset( $this_image ) && $this_image ) {
			$sidebar .= '<img class="img-responsive" src="' . $this_image . '" alt="' . $prob['display_title'] . '" />';
		}
		
		if ( strstr( $prob['post_content'], '[jwplayer' ) !== false ) {
			$sidebar .= '<span class="playbtn"></span>';
		}
		
		$sidebar .= '</a>';
		$sidebar .= '<h4><a href="' . $this_link . '" rel="bookmark">' . $prob['display_title'] . '</a></h4>';
	}
}

if ( $sidebar ) {
	
	echo '<h3 class="sidebartitle">More</h3>';
	echo $sidebar;
}

echo '</div></div></div></div></div>';

if ( isset( $cc_content['current']['unit']['credits'] ) && $cc_content['current']['unit']['credits'] ) {
	echo '<div id="cc-unit-credits"><div class="entry">';
	echo apply_filters( 'the_content', $cc_content['current']['unit']['credits'] );
	echo '</div></div>';
}