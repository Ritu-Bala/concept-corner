<?php

add_shortcode( 'eq', 'dey_equation' );
add_shortcode( 'image', 'dey_image' );
add_shortcode( 'box', 'dey_box' );
add_shortcode( 'boxrow', 'dey_boxrow' );
add_shortcode( 'raw', 'dey_raw' );

add_filter( 'the_content', 'dey_shortcode_fixer' );

function generateRandomString( $length = 10 ) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ( $i = 0; $i < $length; $i ++ ) {
		$randomString .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
	}
	return $randomString;
}

function dey_image( $atts, $content = null ) {
	
	$output = '';
	
	$atts = shortcode_atts( array( 'class' => '', 'width' => '', ), $atts );
	
	if ( ! empty( $atts['class'] ) ) {
		$output .= '<div class="' . $atts['class'] . '">';
	}
	
	if ( ! empty( $atts['width'] ) ) {
		$content = str_replace( 'src="', 'style="width: ' . $atts['width'] . '" src="', $content );
	}
	
	$output .= $content;
	
	if ( ! empty( $atts['class'] ) ) {
		$output .= '</div>';
	}
	
	return $output;
}


function dey_box( $atts, $content = null ) {
	
	$output = '';
	$atts = shortcode_atts( array( 'title' => '', 'class' => '', 'wide'  => '1', ), $atts );
	
	switch ( $atts['wide'] ) {
		default:
		case 1:
			$output .= '<div class="col-xs-11 centered">';
			break;
		
		case 2:
			$output .= '<div class="col-xs-6">';
			break;
		
		case 3:
			$output .= '<div class="col-xs-4">';
			break;
	}
	
	$output .= '<div class="box box-default ' . $atts['class'] . '">';
	
	if ( ! empty( $atts['title'] ) ) {
		$output .= '<div class="box-heading"><h3 class="box-title">' . $atts['title'] . '</h3></div>';
	}
	
	$output .= '<div class="box-body">' . do_shortcode( $content ) . '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	return $output;
}


function dey_boxrow( $atts, $content = null ) {
	$string = generateRandomString( 6 );
	$output = '
	<div id="' . $string . '" class="row">' . do_shortcode( $content ) . '</div>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		var data_' . $string . ' = new Array();
		jQuery("#' . $string . ' .box-body").each(function(){
			var height = jQuery(this).outerHeight();
			data_' . $string . '.push(height);
		});
		
		data_' . $string . '.sort(function(a, b){return b-a});

		jQuery("#' . $string . ' .box-body").each(function(){
			jQuery(this).css({ "height": data_' . $string . '[0]+"px"});
		});
	});
	</script>';
	
	return $output;
}


function dey_equation( $atts, $content = null ) {
	$atts = shortcode_atts( array( 'class' => '', ), $atts );
	
	return '<span class="inlineblock ' . $atts['class'] . '">' . do_shortcode( $content ) . '</span>';
}


function dey_shortcode_fixer( $content ) {
	$dey_shortcodes = join( '|', array( 'box', 'boxrow', 'eq', 'raw' ) );
	
	$new_content = preg_replace( "/(<p>)?\[($dey_shortcodes)(\s[^\]]+)?\](<\/p>|<br \/>)?/", "[$2$3]", $content );
	$new_content = preg_replace( "/(<p>)?\[\/($dey_shortcodes)](<\/p>|<br \/>)?/", "[/$2]", $new_content );
	
	return $new_content;
}


function dey_raw( $atts, $content = null ) {
	return do_shortcode( $content );
}