<?php
/**
 * Header file for K-1
 */

global $cc, $cc_content, $cc_grades, $cc_current_grade, $template, $post;

// ie user agent header
if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== false ) ) {
	header( 'X-UA-Compatible: IE=edge,chrome=1' );
}

echo '<!doctype html>';
echo '<html class="no-js" lang="' . get_bloginfo( 'language' ) . '">';

echo '<head>';
echo "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-TVDHC79');</script>
<!-- End Google Tag Manager -->";

echo '<meta charset="' . get_bloginfo( 'charset' ) . '">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';

echo '<link rel="icon" type="image/png" href="'. get_bloginfo( 'template_directory' ) . '/favicon.ico" />';
echo '<title>'.wp_title('',false).'</title>';
wp_head();

echo '</head>';

$bodcls =  $template == 'k-glossary.php' ? 'glossarypage' : '';
echo '<body '; body_class( $bodcls ); echo '>';
echo '<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TVDHC79" 
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->';