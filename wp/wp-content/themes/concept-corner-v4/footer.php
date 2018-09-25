<?php

echo '<div id="footer" role="contentinfo">';
echo '<div class="container">';
echo '<div class="row">';
echo '<div class="col-xs-12 col-md-12">';

wp_nav_menu(
	array('theme_location' => 'footer_nav',
	      'container' => false,
	      'menu_id' => 'footermenu',
	      'menu_class' => 'aligncenter')
);

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</div>';

echo '<div class="windowsize">';
echo '<div class="windowsizebox">';
echo '<p>This product is designed to be viewed in a "landscape" orientation, you have turned your ipad to "portrait"--'
	. 'turn it back to landscape to continue exploring. Thanks!</p>';
echo '</div>';
echo '</div>';

wp_footer();

echo '<script>';
echo '(function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){';
echo '(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),';
echo 'm=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)';
echo '})(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');';
echo 'ga(\'create\', \'UA-42678068-2\', \'auto\');';
echo 'ga(\'send\', \'pageview\');';
echo '</script>';

echo '</body>';
echo '</html>';