<?php
/**
 * Plugin Name:       Spark
 * Description:       Fetch and manage block patterns from a remote API, providing a centralised pattern library for consistent design across multiple websites.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            eighteen73
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/eighteen73/spark
 * Text Domain:       spark
 *
 * @package Spark
 */

namespace Eighteen73\Spark;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
define( 'SPARK_URL', plugin_dir_url( __FILE__ ) );
define( 'SPARK_PATH', plugin_dir_path( __FILE__ ) );

// Require the autoloader.
require_once 'autoload.php';

// Initialise classes.
Plugin::instance()->setup();
Admin::instance()->setup();

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, [ Plugin::class, 'activation' ] );
register_deactivation_hook( __FILE__, [ Plugin::class, 'deactivation' ] );

// Schedule weekly sync
Cache::instance()->schedule_weekly_sync();
