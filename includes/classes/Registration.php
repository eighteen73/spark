<?php
/**
 * Registration class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

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
	public function register_patterns() {
		$patterns_directory = PATTERNS_PATH . 'patterns';

		if ( ! file_exists( $patterns_directory ) ) {
			return;
		}

		$patterns = glob( $patterns_directory . '/**/*.php' );

		foreach ( $patterns as $pattern ) {
			$pattern_data = get_file_data(
				$pattern,
				[
					'title'         => 'Title',
					'slug'          => 'Slug',
					'description'   => 'Description',
					'viewportWidth' => 'Viewport Width',
					'categories'    => 'Categories',
					'keywords'      => 'Keywords',
					'blockTypes'    => 'Block Types',
					'postTypes'     => 'Post Types',
					'templateTypes' => 'Template Types',
					'inserter'      => 'Inserter',
				]
			);

			if ( empty( $pattern_data['slug'] ) ) {
				continue;
			}

			if ( ! empty( $pattern_data['categories'] ) ) {
				$pattern_data['categories'] = array_map( 'trim', explode( ',', $pattern_data['categories'] ) );
			}

			if ( ! empty( $pattern_data['keywords'] ) ) {
				$pattern_data['keywords'] = array_map( 'trim', explode( ',', $pattern_data['keywords'] ) );
			}

			if ( ! empty( $pattern_data['blockTypes'] ) ) {
				$pattern_data['blockTypes'] = array_map( 'trim', explode( ',', $pattern_data['blockTypes'] ) );
			}

			if ( ! empty( $pattern_data['postTypes'] ) ) {
				$pattern_data['postTypes'] = array_map( 'trim', explode( ',', $pattern_data['postTypes'] ) );
			}

			if ( ! empty( $pattern_data['templateTypes'] ) ) {
				$pattern_data['templateTypes'] = array_map( 'trim', explode( ',', $pattern_data['templateTypes'] ) );
			}

			ob_start();
			include $pattern;
			$pattern_data['content'] = ob_get_clean();

			if ( ! $pattern_data['content'] ) {
				continue;
			}

			register_block_pattern( $pattern_data['slug'], $pattern_data );
		}
	}
}
