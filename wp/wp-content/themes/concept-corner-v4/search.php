<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$debug = '';

get_header(); ?>

<div id="content" role="main">
	<div class="container">
		<div class="row">


			<h2 class="pagetitle">Search Results for "<?php echo $s ?>"</h2>

		</div>
		<div class="row">

			<?php
			if ( is_plugin_active( 'facetwp/index.php' ) ) {

				echo do_shortcode( '[facetwp template="search"]' );
				echo do_shortcode( '[facetwp pager="true"]' );

			} else {

				if ( have_posts() ) :

					while ( have_posts() ) : the_post();
						echo( '<h3>' . the_title() . '</h3>' );
					endwhile;

				else:

					echo( '<p>No results.</p>' );

				endif;
			}
			?>


		</div>
	</div>
</div>

<?php get_footer(); ?>

<?php if ( $debug ) { ?>

<?php if ($query->have_posts()) : ?>
	<?php while ($query->have_posts()) : $query->the_post(); ?>
	<div class="post" id="post-<?php echo(get_the_ID()); ?>">
		<?php
		$friendly_type = array(
			'Video Tutorial',
			'Concept Overview',
			'Example Problem',
			'Glossary Term',
			'Common Core State Mathematics Standard',
			'Inside this Concept',
			'Page',
			'FAQ Entry',
			'Concepts'
		);
		$unfriendly_type= array(
			'cc_video',
			'cc_overview',
			'cc_problem',
			'cc_glossary_term',
			'cc_standard',
			'cc_article',
			'page',
			'cc_faq',
			'cc_concept'
		);
		$type_root = array(
			'/explore/[grade]/concept/[con]/video/[slug]/',
			'/explore/[grade]/concept/[con]/',
			'/explore/[grade]/concept/[con]/worked/[slug]/',
			'/glossary/[grade]/#g[id]',
			'/explore/',
			'/explore/[grade]/concept/[con]/[slug]/',
			'[perma]',
			'/faq/',
			'/explore/[grade]/concept/[con]/'
		);
		$type = get_post_type();
		$this_type = '';
		foreach ( $unfriendly_type as $k=>$u ) {
			if ( $type == $u ) {
				$this_type = $friendly_type[$k];
				$key = $k;
			}
		}
		$lr_id = get_the_ID();
		$meta = get_post_meta( $lr_id );
		if ($type == 'cc_glossary_term') {
			$st = $meta['public_title'][0];
		} else {
			$st = get_the_title();
		}
		$lr_slug = $post->post_name;
		$lr_con = '';
		$this_con = '';
		if (isset($meta['attach']) && $meta['attach']) {
			foreach($meta['attach'] as $me) {
				$m = get_post( $me, ARRAY_A );
				$this_con .=  ' ' . $m['post_title'] . ',';
				$lr_con = $m['post_name'];
			}
		}
		if ( $this_con ) {
			$this_con = substr( $this_con, 0, -1 );
		}
		$lr_grade = '';
		$this_grade = '';
		$grades = get_the_terms( get_the_ID(), 'cc_grade' );
		if ( $grades ) {
			$grade = array_values( $grades );
			foreach($grade as $g) {
				$this_grade .= ' ' . $g->name . ',';
				$lr_grade = $g->name;
			}
		}
		if ( $this_grade ) {
			$this_grade = substr( $this_grade, 0, -1 );
		}
		$lr_perma = '';
		if ( $type == 'page' ) {
			$lr_perma = get_permalink();
		}
		if (!$this_con && $type == 'cc_concept') {
			$lr_con = $post->post_name;
		}
		if ( ! ( $type == 'cc_concept' && ! $this_grade ) ) {
			// build link
			$link_find =  array( '[grade]', '[con]', '[slug]', '[perma]', '[id]');
			$link_replace = array( $lr_grade, $lr_con, $lr_slug, $lr_perma, $lr_id);
			$linker = str_replace($link_find,$link_replace,$type_root[$key]);
			?>
			<div class="cc-sr">
				<h3><a href="<?php echo($linker); ?>" target="_parent"><?php echo($st); ?></a></h3>
			</div>
			<table class="cc-search-table">
				<?php if ($this_type) { ?>
					<tr>
						<td style="text-align: right; font-style: italic;">Content:</td>
						<td><?php echo($this_type); ?></td>
					</tr>
				<?php } if ($this_grade) { ?>
					<tr>
						<td style="text-align: right; font-style: italic;">Grade:</td>
						<td><?php echo($this_grade); ?></td>
					</tr>
				<?php } if ($this_con) { ?>
					<tr>
						<td style="text-align: right; font-style: italic;">Concept:</td>
						<td><?php echo($this_con); ?></td>
					</tr>
				<?php } ?>
			</table>
			</div>
		<?php } endwhile; ?>
<?php else : ?>
	<p>No results match your search term.</p>
<?php endif; ?>

<?php } ?>