<?php
/**
 * Main plugin class.
 *
 * @package Spark
 */

namespace Eighteen73\Spark;

use WP_Block_Pattern_Categories_Registry;

/**
 * Handles registration logic.
 */
class Plugin {

	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'register_patterns' ] );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'spark',
			false,
			SPARK_PATH . '/languages'
		);
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public static function activation(): void {}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivation(): void {
		$cache = Cache::instance();
		$cache->clear_patterns();
		$cache->unschedule_weekly_sync();
	}

	/**
	 * Register patterns
	 *
	 * @return void
	 */
	public function register_patterns(): void {
		$cache    = Cache::instance();
		$patterns = $cache->get_patterns();

		// If no cached patterns, try to fetch from API
		if ( false === $patterns ) {
			$api_client = ApiClient::instance();
			$patterns   = $api_client->fetch_patterns();

			// If API fetch was successful, cache the results
			if ( ! is_wp_error( $patterns ) && is_array( $patterns ) ) {
				$cache->set_patterns( $patterns );
			} else {
				// Log the error for debugging
				if ( is_wp_error( $patterns ) ) {
					error_log( 'Spark: Failed to fetch patterns - ' . $patterns->get_error_message() );
				}

				// Fall back to any existing cache or return early
				$patterns = $cache->get_patterns();
				if ( false === $patterns ) {
					return;
				}
			}
		}

		// Register each pattern
		foreach ( $patterns as $pattern ) {
			if ( empty( $pattern['name'] ) || empty( $pattern['title'] ) || empty( $pattern['content'] ) ) {
				continue;
			}

			// Prepare pattern data for registration
			$pattern_data = [
				'title'   => $pattern['title'],
				'content' => $pattern['content'],
			];

			// Add optional properties if they exist
			if ( ! empty( $pattern['description'] ) ) {
				$pattern_data['description'] = $pattern['description'];
			}

			if ( ! empty( $pattern['categories'] ) && is_array( $pattern['categories'] ) ) {
				$pattern_data['categories'] = $this->ensure_pattern_categories( $pattern['categories'] );
			}

			if ( ! empty( $pattern['keywords'] ) && is_array( $pattern['keywords'] ) ) {
				$pattern_data['keywords'] = $pattern['keywords'];
			}

			if ( ! empty( $pattern['viewportWidth'] ) ) {
				$pattern_data['viewportWidth'] = $pattern['viewportWidth'];
			}

			if ( ! empty( $pattern['blockTypes'] ) && is_array( $pattern['blockTypes'] ) ) {
				$pattern_data['blockTypes'] = $pattern['blockTypes'];
			}

			if ( ! empty( $pattern['postTypes'] ) && is_array( $pattern['postTypes'] ) ) {
				$pattern_data['postTypes'] = $pattern['postTypes'];
			}

			if ( ! empty( $pattern['templateTypes'] ) && is_array( $pattern['templateTypes'] ) ) {
				$pattern_data['templateTypes'] = $pattern['templateTypes'];
			}

			if ( isset( $pattern['inserter'] ) ) {
				$pattern_data['inserter'] = $pattern['inserter'];
			}

			register_block_pattern( $pattern['name'], $pattern_data );
		}
	}

	/**
	 * Ensure pattern categories are registered, registering them if needed
	 *
	 * @param array $categories Array of category names
	 * @return array Array of category names (all should be valid after registration)
	 */
	private function ensure_pattern_categories( $categories ): array {
		$valid_categories = [];
		$registry = \WP_Block_Pattern_Categories_Registry::get_instance();

		foreach ( $categories as $category ) {
			// Use category directly as WordPress category slug
			$category_slug = sanitize_title( $category );

			// If category is not registered, register it
			if ( ! $registry->is_registered( $category_slug ) ) {
				$this->register_pattern_category( $category_slug, $category );
			}

			$valid_categories[] = $category_slug;
		}

		return $valid_categories;
	}

	/**
	 * Register a single pattern category
	 *
	 * @param string $category_slug The category slug
	 * @param string $original_category The original category name from API
	 * @return void
	 */
	private function register_pattern_category( $category_slug, $original_category ): void {
		$label = $this->generate_category_label( $original_category );

		register_block_pattern_category( $category_slug, [
			'label' => $label,
		] );
	}

	/**
	 * Generate a human-readable label for a category
	 *
	 * @param string $category_name The original category name from API
	 * @return string Human-readable category label
	 */
	private function generate_category_label( $category_name ): string {
		// Use the original category name as the label
		return $category_name;
	}
}
