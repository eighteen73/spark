<?php
/**
 * Plugin Name:       Patterns
 * Description:       Patterns for the Spark theme
 * Version:           1.0.0
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Author:            eighteen73
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       patterns
 *
 * @package           Patterns
 */

namespace Eighteen73\Patterns;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
define( 'PATTERNS_URL', plugin_dir_url( __FILE__ ) );
define( 'PATTERNS_PATH', plugin_dir_path( __FILE__ ) );

// Require the autoloader.
require_once 'autoload.php';

// Initialise classes.
Registration::instance()->setup();
