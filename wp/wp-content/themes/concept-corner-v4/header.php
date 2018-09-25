<?php
/**
 * Common header file for main theme
 */

global $cc,
       $cc_content,
       $cc_grades,
       $cc_current_grade;

$cc_gradename = 'Grade ';
$bodyclass = '';

// update the current grade name
if ( isset( $cc_current_grade['name'] ) ) {
	$cc_gradename .= $cc_current_grade['name'];
}

// if this is a 404 page, add class
if ( is_404() ) {
	$bodyclass = 'template-404';
}

// ie user agent detect and header set
if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== false ) ) {
	header( 'X-UA-Compatible: IE=edge,chrome=1' );
}

$doctype = '<!doctype html>';
$lang = get_bloginfo( 'language' );
$html_tag = '<html class="no-js" lang="' . $lang . '">';

echo $doctype;
echo $html_tag;

echo '<head>';
echo "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TVDHC79');</script>
<!-- End Google Tag Manager -->";

echo '<meta charset="' . get_bloginfo( 'charset' ) . '">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';

echo '<link rel="icon" type="image/png" href="' . get_bloginfo( 'template_directory' ) . '/favicon.ico">';
echo '<title>'.wp_title('',false).'</title>';
wp_head();

echo '</head>';

echo '<body ';
body_class( $bodyclass );
echo '>';

echo '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TVDHC79" 
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';

echo '<div class="cc-sitewrap">';
echo '<div id="header" class="doubleshadow">';
echo '<div class="container">';
echo '<div class="row">';

echo '<div id="logo" class="col-xs-6 col-sm-5 col-md-4 col-lg-3">';
echo '<a href="/" title="Concept Corner">Concept Corner</a>';
echo '</div>';

echo '<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">';

if ( ! is_front_page() && ! is_page( 'login' ) ) {
	
	echo '<div class="dropdown">';
	
	echo '<a href="#" class="btn btn-nobox" data-toggle="dropdown" role="button">';
	echo $cc_gradename;
	echo '<span class="whitearrow"></span></a>';
	
	echo '<ul id="gradeselectordropdown" class="dropdown-menu" role="menu">';
	
	foreach ( $cc_grades as $grade ) {
		$grade_link = '/explore/' . $grade['name'];
		echo '<li role="presentation"><a role="menuitem" tabindex="-1" href="'
			. $grade_link . '">Grade ' . $grade['name'] . '</a></li>';
	}
	
	echo '</ul>';
	echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';