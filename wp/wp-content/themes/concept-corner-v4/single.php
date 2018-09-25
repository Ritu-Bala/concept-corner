<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>
	<div id="content" class="widecolumn" role="main">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-md-12 text-center">
					<br/><br/><br/><br/><br/><br/>
					<h4 class="concepttitle">PREVIEW MODE</h4>
					<br/><br/>
				</div>
				<div class="col-xs-12 col-md-12">
					<div class="gradientline">
						<span>Explanations</span> <span>Worked Examples</span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-7 col-md-8 col-xs-offset-1 col-md-offset-1">
					<div>
						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
							<div class="entry">
								<?php /*<h3><?php the_title(); ?></h3> */ ?>
								<?php the_content('<p class="serif">Read the rest of this entry &raquo;</p>'); ?>
							</div>
						<?php endwhile; endif; ?>
					</div>
				</div>
				<div class="col-xs-3 col-md-2">
				</div>
			</div>
		</div>
	</div>
<?php get_footer(); ?>