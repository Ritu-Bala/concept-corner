<?php

/**
 * @var \Concept_Corner $cc
 * @var array           $cc_content
 * @var array           $cc_current_grade
 */

// need to get units directly
$cc_content['units'] = $cc->get_cc_units( $cc_content['filters']['grade'] );

$title_extra = 'Kindergarten';
if ( $cc_current_grade['name'] == 1 ) {
	$title_extra = 'Grade 1';
}

// unit title
$unit_title = 'All ' . $title_extra . ' Videos';

// get all explanations in this unit are in $cc_content['explanations']
// we cycle through them, making the first featured, the rest populate the side bar thingy

$related = array();

$this_thumb_name = 'medium';

// related video, get all in grade
$related_videos = $cc->get_cc_explanations( 'grade', $cc_current_grade['id'] );

foreach ( $related_videos as $ex ) {

	// need to suss out the unit number from the related field
	if ( isset( $cc_content['units'][$ex['cc_unit'][0]]['unit_order'] ) && $cc_content['units'][$ex['cc_unit'][0]]['unit_order'] ) {
		$this_unit_number = '.' . $cc_content['units'][$ex['cc_unit'][0]]['unit_order'];
	} else {
		// @todo: this is not the way to do this...need to look up terms and use discovered values
		$this_unit_number = '/focus/math-practice-videos';
	}

	$this_image = '';
	if ( isset( $ex['thumbnail_meta'] ) && in_array( $this_thumb_name, $ex['thumbnail_meta']['sizes'] ) ) {
		// @todo: this isn't catching this
		$explode = explode( '/', $ex['thumbnail_meta']['file'] );
		array_pop( $explode );
		$this_image_path = implode( '/', $explode );
		$this_image =  $cc_upload_dir . $this_image_path . $ex['thumbnail_meta']['sizes'][$this_thumb_name]['file'];
	} else if ( isset( $ex['thumbnail_meta'] ) ) {
		$this_image = $cc_upload_dir . $ex['thumbnail_meta']['file'];
	}

	if ( isset( $this_image ) && $this_image ) {
		$pix = '<img class="img-responsive" src="' . $this_image . '" alt="' . $ex['display_title'] . '" />';
	}

	$related[] = array (
		'title' => $ex['display_title'],
		'thumb' => $pix,
		'link' => '/explore/' . $cc_current_grade['name'] .  $this_unit_number . '/' . $ex['post_name'] . '/'
	);

}

$header_flag = 'video';
include( CC_PATH . '/templates/z-part-header-k.php');
?>

<section id="list">
	<div class="container">
		<div class="row">

			<?php
			if ( ! empty ( $related ) ) :
			?>

				<?php
				foreach ( $related as $rel ) {
				?>
					<div class="col-xs-4 col-md-3">
						<div class="blockwrap grayblock">

							<a href="<?php  echo $rel['link']; ?>"><?php echo $rel['thumb']; ?>

								<div class="blockcontent">
									<h4 class="aligncenter"><?php echo $rel['title']; ?></h4>
								</div>
							</a>
						</div>
					</div>
			<?php
				}
			endif;
			?>
		
		</div>
	</div>
</section>