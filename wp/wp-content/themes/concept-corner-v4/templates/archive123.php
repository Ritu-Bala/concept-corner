<?php get_header(); ?>

<div id="content" role="main">
	<div class="container">
		<div class="row">

		<?php if (have_posts()) : ?>
		<div id="btn-navigation" class="col-xs-12 col-md-12">
			
			<div class="btn-group pull-right" aria-expanded="false">
				<button type="button" class="btn btn-glossary"><span class="glossaryicon"></span> Glossary</button>
			</div>
			
			<div class="btn-group pull-right hidden" aria-expanded="false">
				<button type="button" class="btn btn-domainsdropdown dropdown-toggle" data-toggle="dropdown">Domains <span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<li><a href="#">Link 1</a></li>
					<li><a href="#">Link 2</a></li>
					<li><a href="#">Link 3</a></li>
				</ul>
			</div>
			
			<?php
			// Unit selector
			$taxslug = get_query_var($wp_query->query_vars['taxonomy']);
			$tax = get_term_by('slug', $taxslug, $wp_query->query_vars['taxonomy']);
			
			if ($tax->parent == 0) {
				$dropdowntitle = 'Units';
				$data['child_of'] = $tax->term_id;
			} else {
				$dropdowntitle = $tax->name;
				$data['parent'] = $tax->parent;
			}

			$args = array(
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'hide_empty' => 0,
				'taxonomy' => $wp_query->query_vars['taxonomy'],
			);
			$args = array_merge($args, $data);
			$units = get_categories($args);
			?>
			<div class="btn-group pull-right" aria-expanded="false">			
				<button type="button" class="btn btn-unitsdropdown dropdown-toggle" data-toggle="dropdown"><?php echo $dropdowntitle ?> <span class="caret"></span></button>
				<ul class="dropdown-menu" role="menu">
					<?php 
					foreach ($units as $unit) { 
						$unit_link = get_term_link($unit);
						?>
						<li role="presentation"><a href="<?php echo $unit_link ?>"><?php echo $unit->name ?></a></li>
					<?php } ?>
				</ul>
			</div>
			
		</div>
		
		<div class="col-xs-12 col-md-12">
			<h1 class="pagetitle"><?php single_cat_title(); ?></h1>
		</div>

		<?php while (have_posts()) : the_post(); ?>
		<div class="col-xs-6 col-md-3">
			<div <?php post_class() ?>>
				<a href="#" class="thumbnail">
					<img src="" data-src="holder.js/100%x180" alt="..." />
				</a>
				<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
			</div>
		</div>
		<?php endwhile; ?>

		<div class="col-xs-12 col-md-12">
			<div class="navigation">
				<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
				<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
			</div>
		</div>
		
		<?php else :

			if ( is_category() ) { // If this is a category archive
				printf("<h2 class='center'>Sorry, but there aren't any posts in the %s category yet.</h2>", single_cat_title('',false));
			} else if ( is_date() ) { // If this is a date archive
				echo("<h2>Sorry, but there aren't any posts with this date.</h2>");
			} else if ( is_author() ) { // If this is a category archive
				$userdata = get_userdatabylogin(get_query_var('author_name'));
				printf("<h2 class='center'>Sorry, but there aren't any posts by %s yet.</h2>", $userdata->display_name);
			} else {
				echo("<h2 class='center'>No posts found.</h2>");
			}
		endif;
		?>

		</div>
	</div>
</div>

<?php get_footer(); ?>
