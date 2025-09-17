<?php
/**
 * Plugin Name:       Spectrum
 * Description:       A collection of block patterns for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            eighteen73
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/eighteen73/spectrum
 * Text Domain:       spectrum
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Useful global constants.
define( 'SPECTRUM_URL', plugin_dir_url( __FILE__ ) );
define( 'SPECTRUM_PATH', plugin_dir_path( __FILE__ ) );

// Require the autoloader.
require_once 'autoload.php';

// Initialise classes.
Registration::instance()->setup();
Admin::instance()->setup();
