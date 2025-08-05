<?php
/**
 * Registration class
 *
 * @package Patterns
 */

namespace Eighteen73\Patterns;

/**
 * Handles registration logic.
 */
class Registration {
	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'init', [ $this, 'register_patterns' ] );
	}

	/**
	 * Register patterns
	 *
	 * @return void
	 */
	public function register_patterns() {}
}
