
<div class="k-container">
	
	<div id="splitlayout" class="splitlayout">
		<div class="intro">
			<div class="logo">
				<a href="/"><span><?php bloginfo('name') ?></span></a>
			</div>

			<div class="side side-left" data-location="k">
				<div class="intro-content">
					<img src="<?php echo CC_LOCATION . '/img/K.jpg'; ?>" width="135" height="135" alt="kindergarten" />
					<h2><span>Kindergarten</span></h2>
					<img src="<?php echo CC_LOCATION . '/img/k1tree.png'; ?>" width="212" height="396" alt="kindergarten tree" />
				</div>
				<div class="overlay"></div>
				<div class="grass"></div>
			</div>

			<div class="side side-right" data-location="1">
				<div class="intro-content">
					<img src="<?php echo CC_LOCATION . '/img/1.jpg'; ?>" width="135" height="135" alt="1st grade" />
					<h2><span>1<sup>st</sup> Grade</span></h2>
					<img src="<?php echo CC_LOCATION . '/img/g1tree.png'; ?>" width="212" height="396" alt="1st grade tree" />
				</div>
				<div class="overlay"></div>
				<div class="grass"></div>
			</div>

		</div>
		


		<div class="page page-left">
			<div class="page-inner">
<?php
// get grade ID for K
$k_units = array();

foreach ( $cc_grades as $g ) {

	if ( "K" == $g['name'] ) {

		// get the units for K
		$k_units = $cc->get_cc_units( $g['term_id'] );
		break;
	}
}

if ( ! empty( $k_units ) ) {

	$i = 0;

	foreach( $k_units as $k ) {

		// @todo: add in image handling...

		// no units without explanations
		if ( ! empty ( $k['cc_explanation'] ) ) {

			echo '<div class="unit_box">';
			echo '<a href="/explore/K.' . $i . '/" title="' . $k['display_title'] . '">';

			$this_image = '';
			if ( isset( $k['image_meta'] ) ) {
				$this_image = $cc_upload_dir . $k['image_meta']['file'];
			}

			if ( $this_image ) {
				echo '<img class="img-responsive img-circle" src="' . $this_image . '" />';
			}


			echo '<span class="unit_title">' . $k['display_title']  . '</span>';
			echo '</a>';
			echo '</div>';
			$i++;
		}
	}
}
?>
				<div class="clearfix"></div>
			</div>
		</div>

		<div class="page page-right">
			<div class="page-inner">
<?php
// get grade ID for 1
$one_units = array();

foreach ( $cc_grades as $g ) {

	if ( "1" == $g['name'] ) {

		// get the units for K
		$one_units = $cc->get_cc_units( $g['term_id'] );
		break;
	}
}

if ( ! empty( $one_units ) ) {

	$i = 0;

	foreach( $one_units as $k ) {

		// @todo: add in image handling...

		if ( ! empty ( $k['cc_explanation'] ) ) {

			echo '<div class="unit_box">';
			echo '<a href="/explore/1.' . $i . '/" title="' . $k['display_title'] . '">';

			$this_image = '';
			if ( isset( $k['image_meta'] ) ) {
				$this_image = $cc_upload_dir . $k['image_meta']['file'];
			}

			if ( $this_image ) {
				echo '<img class="img-responsive img-circle" src="' . $this_image . '" />';
			}

			echo '<span class="unit_title">' . $k['display_title']  . '</span>';
			echo '</a>';
			echo '</div>';
			$i++;
		}
	}
}
?>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		$('body').removeClass('page');
	});
</script>