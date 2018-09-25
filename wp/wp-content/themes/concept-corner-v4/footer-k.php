<div id="footer" role="contentinfo">
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-md-12">
				<?php wp_nav_menu( array('theme_location' => 'footer_nav', 'container' => false, 'menu_id' => 'footermenu', 'menu_class' => 'aligncenter') ); ?>
			</div>
		</div>
	</div>
</div>

<div class="windowsize">
	<div class="windowsizebox">
		<p>This product is designed to be viewed in a "landscape" orientation, you've turned your ipad to "portrait"-- turn it back to landscape to continue exploring. Thanks!</p>
	</div> 
</div>

<?php wp_footer(); ?>

<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', 'UA-42678068-2', 'auto');
	ga('send', 'pageview');
</script>

</body>
<?php
global $cc_content;
if ( isset( $cc_content['cache'] ) && $cc_content['cache'] ) {
	echo '<!-- ' . $cc_content['cache'] . ' -->' . "\n";
} ?>
</html>