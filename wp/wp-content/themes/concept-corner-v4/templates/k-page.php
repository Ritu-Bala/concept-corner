<?php

/**
 * @var \Concept_Corner $cc
 * @var array           $cc_current_grade
 * @var array           $cc_content
 */

$page = get_page_by_path( $cc_content['filters']['page'] );

// unit title change to focus term if that's what is chosen...
if ( isset( $cc_content['filters']['unit'] ) && $cc_content['filters']['unit'] ) {
	$unit_title = $cc_content['units'][ $cc_content['filters']['unit'] ]['display_title'];
} else if ( isset( $nice_filter['special_term'] ) ) {
	// @todo: this is a hack, need to look up real title
	$unit_title = str_replace( '-', ' ', $nice_filter['special_term'] );
} else {
	$unit_title = apply_filters( 'cc_page_title', $page->post_title, $page, $cc_current_grade['name'] );
}



// get all explanations in this unit are in $cc_content['explanations']
// we cycle through them, making the first featured, the rest populate the side bar thingy
$featured = array();
$sidebar = array();
$related = array();
$used = array();

$this_thumb_name = 'medium';

// page top area



// related video, get all in grade

//$related_videos = $cc->get_cc_explanations( 'grade', $cc_current_grade['id'] );
//$related_units = $cc->get_cc_units( $cc_current_grade['id'] );

$related_videos = array();

foreach ( $related_videos as $ex ) {

	// don't add if already at page top

	if ( ! in_array( $ex['ID'], $used ) ) {

		// need to suss out the unit number from the related field
		if ( isset( $cc_content['units'][ $ex['cc_unit'][0] ]['unit_order'] ) && $cc_content['units'][ $ex['cc_unit'][0] ]['unit_order'] ) {
			$this_unit_number = '.' . $cc_content['units'][ $ex['cc_unit'][0] ]['unit_order'];
		}

		// see if a unit is set
		else if (isset( $units_for_related[ $ex['cc_unit'][0] ]['unit_order'] ) && $units_for_related[ $ex['cc_unit'][0] ]['unit_order'] ) {
			$this_unit_number = '.' . $units_for_related[ $ex['cc_unit'][0] ]['unit_order'];
		}

		// else it's a focus video, I guess
		else {
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
			'link' => '/explore/' . $cc_current_grade['name'] . $this_unit_number . '/' . $ex['post_name'] . '/'
		);


	}

}

$header_flag = 'page';
include( CC_PATH . '/templates/z-part-header-k.php');
?>

<section id="content">
	<div class="container">
		<div class="row">


			<div class="col-xs-12 col-md-12 cc-k-page-content">

				<?php echo apply_filters( 'the_content', $page->post_content ); ?>


			</div>

		</div>
	</div>
</section>

<section id="list">
	<div class="container">
		<div class="row">

			<?php
			if ( ! empty ( $related ) ) :
				?>

				<div class="col-xs-12 col-md-12">
					<h3 class="green sectiontitle">More Videos</h3>
				</div>

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