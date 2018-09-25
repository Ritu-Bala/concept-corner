<?php
/**
 * @package S3 Media Storage
 */
/*
Plugin Name: S3 Media Storage
Description: Store media library contents onto S3 directly without the need for temporarily storing files on the filesystem/cron jobs. This is more ideal for multiple web server environemnts.
Version: 1.0.3
Author: Anthony Gentile (mods by Roger Los)
Author URI: http://agentile.com
*/

/*  Copyright 2013  Anthony Gentile  (email : asgentile@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Don't expose any info if called directly
if ( ! function_exists( 'add_action' ) ) {
	echo 'S3 Media Storage WordPress Plugin';
	exit;
}

define( 'S3MS_PLUGIN_VERSION', '1.0.3' );
define( 'S3MS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function s3ms_install() {

}

register_activation_hook( __FILE__, 's3ms_install' );

function s3ms_uninstall() {

}

register_uninstall_hook( __FILE__, 's3ms_uninstall' );

if ( is_admin() ) {
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin.php';
}

function s3_attachment_url( $url, $post_id, $all = false ) {

	$custom_fields = get_post_custom( $post_id );
	$settings = json_decode( get_option( 'S3MS_settings' ), true );
	$upload_dir = wp_upload_dir();
	$not_on_amazon = true;
	$no_meta = false;

	// is there a cloudfront key?

	$cloudfront = isset( $custom_fields['S3MS_cloudfront'] ) ? $custom_fields['S3MS_cloudfront'][0] : null;

	// Determine protocol to serve from

	if ( $settings['s3_protocol'] == 'http' ) {
		$protocol = 'http://';
	} elseif ( $settings['s3_protocol'] == 'https' ) {
		$protocol = 'https://';
	} elseif ( $settings['s3_protocol'] == 'relative' ) {
		$protocol = 'http://';
		if ( isset( $_SERVER['SERVER_PORT'] ) && $_SERVER['SERVER_PORT'] === '443' ) {
			$protocol = 'https://';
		}
	} else {
		$protocol = 'https://';
	}

	$bucket      = isset( $custom_fields['S3MS_bucket'] ) ? $custom_fields['S3MS_bucket'][0] : null;
	$bucket_path = isset( $custom_fields['S3MS_bucket_path'][0] ) ? $custom_fields['S3MS_bucket_path'][0] : null;

	// If we don't have a $bucket, let's try it from settings

	if ( $bucket === null || trim( $bucket ) == '' ) {
		$bucket      = isset( $settings['s3_bucket'] ) ? $settings['s3_bucket'] : null;
		$bucket_path = isset( $settings['s3_bucket_path'] ) ? $settings['s3_bucket_path'] : null;
		$no_meta = true;
	}

	// no bucket, let's go away

	if ( $bucket === null && $all === false ) {
		return $url;
	}

	// get rid of basedir if it's in URL

	$file = str_replace( $upload_dir['baseurl'], '', $url );

	// get rid of the amazon front if it's there

	if ( $cloudfront && trim( $cloudfront ) != '' ) {
		$s3_domain = $protocol . $cloudfront;
	} else {
		$s3_domain = $protocol . $bucket . '.s3.amazonaws.com/';
	}

	// is the remaining file mabobber already an amazon path?

	if ( strstr( $file, $s3_domain ) ) {
		$file = str_replace( $s3_domain, '', $file );
		$bucket_path = '';
	}

	// get rid of slash...

	if ( substr( $file, 0, 1 ) == '/' ) {
		$file = substr( $file, 1 );
	}

	// add the path if it's there

	if ( $bucket_path !== null && $bucket_path ) {
		$file = $bucket_path . '/' . $file;
	}

	// check to see file exists

	if ( ! class_exists( 'S3' ) ) {
		require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'S3.php';
	}

	$s3_check = new S3( $settings['s3_access_key'], $settings['s3_secret_key'] );
	if ( $s3_check->getObjectInfo( $bucket, $file ) ) {
		$not_on_amazon = false;
	}

	// if we can't find the item at all, return WP's url

	if ( $not_on_amazon && $all === false ) {
		return $url;
	}

	// set the meta fields if they were not set
	// this seems like a really kludgy way to do this
	// why are they needed?

	if ( $no_meta ) {
		update_post_meta( $post_id, "S3MS_bucket", $settings['s3_bucket'] );
		update_post_meta( $post_id, "S3MS_bucket_path", $bucket_path );
		update_post_meta( $post_id, "S3MS_file", $file );
		update_post_meta( $post_id, "S3MS_cloudfront", $settings['s3_cloudfront'] );
	}

	// Should serve with respective protocol

	if ( $cloudfront && trim( $cloudfront ) != '' ) {

		$url = $protocol . $cloudfront . '/' . $file;

	} else {

		$url = $protocol . $bucket . '.s3.amazonaws.com/' . $file;
	}

	if ( $all === true ) {
		return array('url' => $url, 'file' => $file, 'missing' => $not_on_amazon );
	} else {
		return $url;
	}

}


function s3_delete_attachment( $url ) {
	$settings = json_decode( get_option( 'S3MS_settings' ), true );

	// Check our settings to see if we even want to delete from S3.
	if ( ! isset( $settings['s3_delete'] ) || (int) $settings['s3_delete'] == 0 ) {
		return true;
	}

	$upload_dir = wp_upload_dir();
	$file       = str_replace( $upload_dir['basedir'], '', $url );
	if ( substr( $file, 0, 1 ) == '/' ) {
		$file = substr( $file, 1 );
	}

	if ( isset( $settings['s3_bucket_path'] ) && $settings['s3_bucket_path'] ) {
		$file = $settings['s3_bucket_path'] . '/' . $file;
	}

	if ( isset( $settings['valid'] ) && (int) $settings['valid'] ) {
		if ( ! class_exists( 'S3' ) ) {
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'S3.php';
		}

		$s3  = new S3( $settings['s3_access_key'], $settings['s3_secret_key'] );
		$ssl = (int) $settings['s3_ssl'];
		$s3->setSSL( (bool) $ssl );
		$s3->setExceptions( true );

		try {
			$s3->deleteObject( $settings['s3_bucket'], $file );

			return true;
		}
		catch ( Exception $e ) {
			//echo $e->getMessage();
			//die();
		}
	}
}

function s3_image_make_intermediate_size( $attachment_path ) {
	$upload_dir = wp_upload_dir();
	$s3_path    = str_replace( $upload_dir['basedir'], '', $attachment_path );
	if ( substr( $s3_path, 0, 1 ) == '/' ) {
		$s3_path = substr( $s3_path, 1 );
	}
	$settings = json_decode( get_option( 'S3MS_settings' ), true );

	if ( isset( $settings['s3_bucket_path'] ) && $settings['s3_bucket_path'] ) {
		$s3_path = $settings['s3_bucket_path'] . '/' . $s3_path;
	}

	if ( isset( $settings['valid'] ) && (int) $settings['valid'] ) {
		if ( ! class_exists( 'S3' ) ) {
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'S3.php';
		}

		$s3  = new S3( $settings['s3_access_key'], $settings['s3_secret_key'] );
		$ssl = (int) $settings['s3_ssl'];
		$s3->setSSL( (bool) $ssl );
		$s3->setExceptions( true );

		$meta_headers = array();
		// Allow for far reaching expires
		$request_headers = array();
		if ( trim( $settings['s3_expires'] ) != '' ) {
			$request_headers = array(
				"Cache-Control" => "max-age=315360000",
				"Expires"       => gmdate( "D, d M Y H:i:s T", strtotime( trim( $settings['s3_expires'] ) ) )
			);
		}

		try {
			$s3->putObjectFile( $attachment_path, $settings['s3_bucket'], $s3_path, S3::ACL_PUBLIC_READ, $meta_headers, $request_headers );
			if ( isset( $settings['s3_delete_local'] ) && $settings['s3_delete_local'] ) {
				@unlink( $attachment_path );
			}
		}
		catch ( Exception $e ) {
			//echo $e->getMessage();
			//die();
		}
	}

	return $s3_path;
}

function s3_update_attachment_metadata( $data, $attachment_id ) {

	$attachment_path = get_attached_file( $attachment_id ); // Gets path to attachment
	$upload_dir      = wp_upload_dir();
	$s3_path         = str_replace( $upload_dir['basedir'], '', $attachment_path );
	if ( substr( $s3_path, 0, 1 ) == '/' ) {
		$s3_path = substr( $s3_path, 1 );
	}
	$settings = json_decode( get_option( 'S3MS_settings' ), true );

	if ( isset( $settings['s3_bucket_path'] ) && $settings['s3_bucket_path'] ) {
		$s3_path = $settings['s3_bucket_path'] . '/' . $s3_path;
	}

	if ( isset( $settings['valid'] ) && (int) $settings['valid'] ) {
		if ( ! class_exists( 'S3' ) ) {
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'S3.php';
		}

		$s3  = new S3( $settings['s3_access_key'], $settings['s3_secret_key'] );
		$ssl = (int) $settings['s3_ssl'];
		$s3->setSSL( (bool) $ssl );
		$s3->setExceptions( true );

		$meta_headers = array();
		// Allow for far reaching expires
		$request_headers = array();
		if ( trim( $settings['s3_expires'] ) != '' ) {
			$request_headers = array(
				"Cache-Control" => "max-age=315360000",
				"Expires"       => gmdate( "D, d M Y H:i:s T", strtotime( trim( $settings['s3_expires'] ) ) )
			);
		}

		try {
			$s3->putObjectFile( $attachment_path, $settings['s3_bucket'], $s3_path, S3::ACL_PUBLIC_READ, $meta_headers, $request_headers );
			// We store per file instead of always just referencing the settings, as if settings change we don't want to break previously
			// uploaded files that refer to different buckets/cloudfront/etc.
			update_post_meta( $attachment_id, "S3MS_bucket", $settings['s3_bucket'] );
			update_post_meta( $attachment_id, "S3MS_bucket_path", $settings['s3_bucket_path'] );
			update_post_meta( $attachment_id, "S3MS_file", $s3_path );
			update_post_meta( $attachment_id, "S3MS_cloudfront", $settings['s3_cloudfront'] );
			if ( ( isset( $data['S3MS_move'] ) && $data['S3MS_move'] ) || ( isset( $settings['s3_delete_local'] ) && $settings['s3_delete_local'] ) ) {
				@unlink( $attachment_path );
			}

			// If we are copy or moving we need to grab any thumbnails as well.
			if ( isset( $data['S3MS_move'] ) ) {
				$c = wp_get_attachment_metadata( $attachment_id );
				if ( isset( $c['sizes'] ) && is_array( $c['sizes'] ) ) {
					foreach ( $c['sizes'] as $size ) {
						// Do a cheap check for - and x to know that we are talking about a resized image
						// e.g. Photo0537.jpg turns into Photo0537-150x150.jpg
						if ( isset( $size['file'] ) && strpos( $size['file'], '-' ) && strpos( $size['file'], 'x' ) ) {
							$parts               = pathinfo( $attachment_path );
							$new_attachment_path = $parts['dirname'] . '/' . $size['file'];
							s3_image_make_intermediate_size( $new_attachment_path );
						}
					}
				}
			}
		}
		catch ( Exception $e ) {
			//echo $e->getMessage();
			//die();
			return $data;
		}
	}

	return $data;
}

function s3ms_pagination_url() {
	parse_str( $_SERVER['QUERY_STRING'], $out );
	unset( $out['s3ms_page'] );
	$out = http_build_query( $out );
	if ( $out ) {
		return '?' . $out . '&s3ms_page=';
	}

	return '?s3ms_page=';
}

function s3ms_pagination( $total, $per_page, $page, $page_action, $group = 10, $offset = 0, $classes = array() ) {
	if ( $total <= $per_page ) {
		return;
	}

	$classes = implode( ' ', $classes );
	$str     = '';

	$start = floor( $page / $group ) * $group;

	$total_pages = ceil( $total / $per_page );

	if ( $start == 0 ) {
		$start = 1;
	}

	// do some adjustment if someone is nearing the
	// end of the group, shift the stack back
	$end_of_group = ( $start + $group ) - 1;
	if ( $end_of_group > $total_pages ) {
		$end_of_group = $total_pages;
	}

	if ( $page == $end_of_group && $page != $total_pages ) {
		$start        = $end_of_group;
		$end_of_group = ( $start + $group ) - 1;
	} elseif ( $page > ( $end_of_group - 2 ) && $end_of_group < $total_pages ) {
		$start += 1;
		$end_of_group = ( $start + $group ) - 1;
	}

	if ( $page == 1 ) {
		$prev = 1 - $offset;
	} elseif ( $page == 0 ) {
		$prev = 0;
	} else {
		$prev = $page - 1;
	}

	if ( $page + 1 > ( $total_pages - $offset ) ) {
		$next = ( $total_pages - $offset );
	} else {
		$next = $page + 1;
	}

	if ( $next == 0 ) {
		$next = 1;
	}

	$end = $total_pages - $offset;
	if ( $end == 0 ) {
		$end = 1;
	}

	$str .= '<a href="' . $page_action . ( 1 - $offset ) . '">Start</a>&nbsp;';
	$str .= '<a href="' . $page_action . $prev . '">Prev</a>&nbsp;';

	for ( $i = $start; $i <= $end_of_group; $i ++ ) {
		if ( $i > $total_pages ) {
			break;
		}
		if ( ( $i - $offset ) == $page ) {
			$str .= '&nbsp;<a href="#">' . $i . '</a>&nbsp;';
		} else {
			$str .= '&nbsp;<a href="' . $page_action . ( $i - $offset ) . '">' . $i . '</a>&nbsp;';
		}
	}
	$str .= '&nbsp;<a href="' . $page_action . $next . '">Next</a>&nbsp;';
	$str .= '&nbsp;<a href="' . $page_action . $end . '">End</a>&nbsp;';

	return $str;
}

/**
 * Register hooks/filters
 */

// Handle original image uploads and edits for that image
add_filter( 'wp_update_attachment_metadata', 's3_update_attachment_metadata', 9, 2 );

// Handle thumbs that are created for that image
add_filter( 'image_make_intermediate_size', 's3_image_make_intermediate_size' );

// Handle when image urls are requested.
add_action( "wp_get_attachment_url", 's3_attachment_url', 9, 2 );
add_action( "wp_get_attachment_thumb_url", 's3_attachment_url', 9, 2 );

// Handle when images are deleted.
add_action( "wp_delete_file", 's3_delete_attachment' );

// We can't hook into add_attachment/edit_attachment actions as these occur too early in the chain as at that point in time, 
// metadata for the attachment has not been associated. So we want to wait, so we can handle both and then delete the local uploaded file.
// add_action("add_attachment", 's3_update_attachment');
// add_action("edit_attachment", 's3_update_attachment');


/**
 * Capture call to the poster thumb ID from JWPlayer, substitute S3 item
 *
 * @param $metadata
 * @param $object_id
 * @param $meta_key
 * @param $single
 */
function s3_jwp_video_posters( $metadata, $object_id, $meta_key, $single ) {

	// get the field name from options
	$settings = json_decode( get_option( 'S3MS_settings' ), true );

	// is this the video thumbnail?
	if ( isset( $settings['s3_jwpmeta'] ) && $meta_key == $settings['s3_jwpmeta'] && isset( $meta_key ) ) {

		$meta_cache = wp_cache_get( $object_id, 'post' . '_meta' );

		if ( ! $meta_cache ) {
			$meta_cache = update_meta_cache( 'post', array( $object_id ) );
			$meta_cache = $meta_cache[$object_id];
		}

		if ( isset( $meta_cache[$meta_key] ) ) {

			// if this is numeric, it's an ID. We want to return the amazon URL
			// if it's not, we'll just return it

			$value = maybe_unserialize( $meta_cache[$meta_key][0] );

			if ( is_numeric( $value ) ) {

				$url = wp_get_attachment_url( $value );

				// bail if nothing is there, return S3 url if it exists

				if ( ! $url ) {
					return '';
				} else {
					$hm = s3_attachment_url( $url, $object_id );
					return $hm;
				}

			} else {
				return $value;
			}
		}
	}
}
add_filter('get_post_metadata', 's3_jwp_video_posters', 10, 4);