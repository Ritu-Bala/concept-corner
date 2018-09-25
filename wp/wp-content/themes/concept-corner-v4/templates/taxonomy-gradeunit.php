<?php
get_header();

echo '<div id="content" role="main">';
echo '<div class="container">';
echo '<div class="row">';

// Unit selector
$taxslug = get_query_var( $wp_query->query_vars['taxonomy'] );
$tax = get_term_by( 'slug', $taxslug, $wp_query->query_vars['taxonomy'] );

if ( $tax->parent == 0 ) {
	$dropdowntitle = 'Units';
	$data['child_of'] = $tax->term_id;
	$gradeid = $tax->term_id;
}
else {
	$dropdowntitle = $tax->name;
	$data['parent'] = $tax->parent;
	$gradeid = $tax->parent;
}
?>
<div id="btn-navigation" class="col-xs-12 col-md-12">
	
	<div class="btn-group pull-right" aria-expanded="false">
		<a href="<?php bloginfo( 'template_directory' ) ?>/includes/glossary.php?gradeid=<?php echo $gradeid ?>"
		   class="btn btn-glossary fancybox.ajax"><span class="glossaryicon"></span> Glossary</a>
	</div>
	
	<div class="btn-group pull-right hidden" aria-expanded="false">
		<button type="button" class="btn btn-domainsdropdown dropdown-toggle" data-toggle="dropdown">Domains <span
				class="caret"></span></button>
		<ul class="dropdown-menu" role="menu">
			<li><a href="#">Link 1</a></li>
			<li><a href="#">Link 2</a></li>
			<li><a href="#">Link 3</a></li>
		</ul>
	</div>
	
	<?php
	$args = array(
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'hide_empty' => 0,
		'taxonomy'   => $wp_query->query_vars['taxonomy'],
	);
	$args = array_merge( $args, $data );
	$units = get_categories( $args );
	?>
	<div class="btn-group pull-right" aria-expanded="false">
		<button type="button" class="btn btn-unitsdropdown dropdown-toggle"
		        data-toggle="dropdown"><?php echo $dropdowntitle ?> <span class="greenarrow"></span></button>
		<ul class="dropdown-menu" role="menu">
			<?php
			if ( $units ) {
				foreach ( $units as $unit ) {
					$unit_link = get_term_link( $unit );
					?>
					<li role="presentation"><a href="<?php echo $unit_link ?>"><?php echo $unit->name ?></a></li>
					<?php
				}
			} ?>
		</ul>
	</div>

</div>

</div>

<div class="row">
	<div class="col-xs-10 col-md-10 col-xs-offset-1 col-md-offset-1">
		<div class="row">
			
			<div class="col-xs-12 col-md-12">
				<h1 class="pagetitle">
					<?php
					single_cat_title();
					if ( $tax->parent == 0 ) {
						_e( ': All Concepts', 'ccorner' );
					}
					?>
				</h1>
				<?php if ( $tax->parent != 0 ) { ?><h2
					class="pagetitle"><?php _e( 'Concepts', 'ccorner' ); ?></h2><?php } ?>
			</div>
			
			<?php
			if ( have_posts() ) :
				$i = 1;
				while ( have_posts() ) : the_post();
					$post_thumbnail_id = get_post_thumbnail_id( $post_id );
					$postimage = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
					?>
					<div class="col-xs-6 col-md-3">
						<div <?php post_class( 'conceptsgrid' ) ?>>
							<a href="<?php the_permalink() ?>" class="thumbnail">
								<?php if ( $postimage ) { ?>
									<img class="img-responsive" src="<?php echo $postimage[0] ?>"
									     alt="<?php the_title() ?>"/>
								<?php }
								else { ?>
									<img src="holder.js/100%x141" alt="<?php the_title() ?>"/>
								<?php } ?>
							</a>
							<h4><a href="<?php the_permalink() ?>" rel="bookmark"
							       title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
							</h4>
							<br/>
						</div>
					</div>
					
					<?php if ( $i % 2 == 0 ) {
						echo '<div class="clearfix visible-xs-block visible-sm-block"></div>';
					} ?>
					<?php if ( $i % 4 == 0 ) {
						echo '</div><div class="row">';
					} ?>
					
					<?php $i ++; endwhile; ?>
				
				<div class="col-xs-12 col-md-12">
					<div class="navigation">
						<div class="alignleft"><?php next_posts_link( '&laquo; Older Entries' ) ?></div>
						<div class="alignright"><?php previous_posts_link( 'Newer Entries &raquo;' ) ?></div>
					</div>
				</div>
			
			<?php else : ?>
				
				<div class="col-xs-12 col-md-12">
					<?php
					if ( is_category() ) { // If this is a category archive
						printf( "<h2 class='center'>Sorry, but there aren't any posts in the %s category yet.</h2>", single_cat_title( '', false ) );
					}
					else if ( is_date() ) { // If this is a date archive
						echo( "<h2>Sorry, but there aren't any posts with this date.</h2>" );
					}
					else if ( is_author() ) { // If this is a category archive
						$userdata = get_userdatabylogin( get_query_var( 'author_name' ) );
						printf( "<h2 class='center'>Sorry, but there aren't any posts by %s yet.</h2>", $userdata->display_name );
					}
					else {
						echo( "<h2 class='center'>No posts found.</h2>" );
					}
					?>
				</div>
			
			<?php endif; ?>
		</div>
	</div>


</div>
</div>
</div>

<?php
get_footer();