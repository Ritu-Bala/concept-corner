<?php
/**
 * JW Player
 *
 * @package   ccjwp
 * @author    Roger Los <roger@rogerlos.oom>
 * @license   GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       JW Player Version 7
 * Plugin URI:
 * Description:       Use [jwplayer] shortcode inline to add JWPlayer
 * Version:           0.0.1
 * Author:            Roger Los <roger@rogerlos.oom>
 * Author URI:        http://rogerlos.com
 * Text Domain:       ccjwp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: 
 */

if ( ! defined( 'WPINC' ) ) die;

require 'autoloader.php';
spl_autoload_register( 'ccjwp_autoloader' );

define( 'CCJWP_PATH', plugin_dir_path( __FILE__ ) );

$ccjwp = new Cc_Jwp();

// allows devs to call this directly
function ccjwp( $atts, $echo = true ) {
	$ccjwp = new Cc_Jwp();
	$player = $ccjwp->player( $atts );
	if ( $echo == true ) { echo $player; } else { return $player; };
}