<?php
/**
 * Math Tools
 *
 * Adds math tools as either inline tools added individually as inline tools,
 * popups via links, or as a collection added via shortcode. The collection can
 * be a button-launched menu or a grid.
 *
 * @package   Math_Tools
 * @author    Roger Los <roger@rogerlos.oom>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 *
 * @wordpress-plugin
 * Plugin Name:       Math Tools
 * Plugin URI:
 * Description:       Adds javascript math tools to your website
 * Version:           0.0.1
 * Author:            Roger Los <roger@rogerlos.oom>
 * Author URI:        http://rogerlos.com
 * Text Domain:       math-tools
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/Pearson-Foundation/psoc-sso
 */

if ( ! defined( 'WPINC' ) ) die;
require 'autoloader.php';
spl_autoload_register( 'mathtools_autoloader' );
new MathTools( __DIR__ );