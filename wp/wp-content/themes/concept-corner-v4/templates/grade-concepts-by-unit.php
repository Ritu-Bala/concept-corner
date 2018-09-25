<?php
$this_thumb_name = 'thumbnail';

echo '<!-- c-b-u -->
	<div id="content" role="main">
		<div class="container">';

include( CC_PATH . '/templates/z-part-header-row.php');

echo '<div class="row">';
echo '<div class="col-xs-10 col-md-10 col-xs-offset-1 col-md-offset-1">';
echo '<div class="row">';
echo '<div class="col-xs-12 col-md-12">';

if ( ! isset( $nice_filter['unit'] ) ) {
	echo '<h1 class="pagetitle">Grade ' . $nice_filter['grade'] . ': ' . $cc_content['labels']['explanations'][0] . ' Library</h1>';
} else {
	echo '<h1 class="pagetitle">' . $cc_content['labels']['units'][0] . ' ' .
		$nice_filter['grade'] . '.' . $nice_filter['unit'] . ': ' . $cc_content['units'][ $cc_content['filters']['unit'] ]['display_title'] . '</h1>';
	echo '<h2 class="pagetitle">All ' . $cc_content['labels']['concepts'][1] . '</h2>';
}

echo '</div>';

if ( $cc_content['concepts'] ) {

	// unit counter
	$u = 0;

	foreach ( $cc_content['units'] as $unit ) {

		echo '<div class="col-xs-12 col-md-12"><h2 class="pagetitle">' . $unit['display_title'] . '</h2></div>';

		// column counter
		$i = 1;

		foreach ( $cc_content['concepts'] as $concept ) {

			$empty = array_filter( $concept['cc_explanation'] );

			if ( ! empty( $empty ) && in_array( $concept['ID'], $unit['cc_concept'] ) ) {

				$this_unit = '';
				if ( $cc_content['filters']['unit_position'] !== null ) {
					$this_unit = $cc_content['filters']['unit'];
					$link = $concept_units[ $this_unit ];
				} else if ( isset( $concept['cc_unit']) && $concept['cc_unit'][0] ) {
					$this_unit = $concept['cc_unit'][0];
					$link = $concept_units[ $this_unit ];
				} else {
					$link = '/explore/' . $nice_filter['grade'] . '/';
				}
				echo '<div class="col-xs-6 col-md-3">';
				echo '<div class="conceptsgrid">';
				echo '<a href="' . $link . $concept['post_name'] . '/" class="thumbnail">';

				$this_image = '';

				// use medium for responsive thumbnails, if doesn't exist, use original?
				if ( isset( $concept['image_meta'] ) && array_key_exists( $this_thumb_name, $concept['image_meta']['sizes'] ) ) {
					$explode = explode( '/', $concept['image_meta']['file'] );
					array_pop($explode);
					$this_image_path = implode( '/', $explode ) . '/';
					$this_image =  $cc_upload_dir . '/' . $this_image_path . $concept['image_meta']['sizes'][$this_thumb_name]['file'];
				} else if ( isset( $concept['image_meta'] ) ) {
					$this_image = $cc_upload_dir . '/' . $concept['image_meta']['file'];
				}


				if ( $this_image ) {
					echo '<img class="img-responsive" src="' . $this_image . '" alt="' . $concept['display_title'] . '" />';
				}

				echo '</a>';
				echo '<h4><a href="' . $concept_units[ $this_unit ] . $concept['post_name']  . '" rel="bookmark" title="Permanent Link to ' . $concept['display_title'] . '">' . $concept['display_title'] . '</a></h4>';
				echo '<br/>';
				echo '</div>';
				echo '</div>';

				if ($i%2 == 0) echo '<div class="clearfix visible-xs-block visible-sm-block"></div>';
				if ($i%4 == 0) echo '</div><div class="row">';

				$i++;
			}
		}
		$u++;
	}
}
?>
				</div>
			</div>
		</div>
	</div>
</div>
