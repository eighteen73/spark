<?php
/**
 * API Client class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

use WP_Error;

/**
 * Handles API communication with the remote patterns server.
 */
class ApiClient {
	use Singleton;

	/**
	 * API endpoint URL
	 *
	 * @var string
	 */
	private $api_endpoint = 'https://yiwxpfphcyrdpwutnggf.supabase.co/rest/v1/patterns?select=*';

	/**
	 * Fetch patterns from the remote API
	 *
	 * @return array|WP_Error Array of patterns on success, WP_Error on failure
	 */
	public function fetch_patterns(): array|WP_Error {
		$api_key = $this->get_api_key();

		if ( ! $api_key ) {
			return new WP_Error(
				'spectrum_no_api_key',
				__( 'Spectrum API key not found. Please define SPECTRUM_API_KEY in wp-config.php', 'spectrum' )
			);
		}

		$response = wp_remote_get(
			$this->api_endpoint,
			[
				'headers' => [
					'apikey'       => $api_key,
					'Content-Type' => 'application/json',
				],
				'timeout' => 30,
			]
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			return new WP_Error(
				'spectrum_api_error',
				sprintf(
					/* translators: %1$s: HTTP status code, %2$s: response body */
					__( 'API request failed with status %1$s: %2$s', 'spectrum' ),
					$response_code,
					$response_body
				)
			);
		}

		$patterns = json_decode( $response_body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new \WP_Error(
				'spectrum_json_error',
				__( 'Failed to parse API response as JSON', 'spectrum' )
			);
		}

		if ( ! is_array( $patterns ) ) {
			return new \WP_Error(
				'spectrum_invalid_response',
				__( 'API response is not a valid array of patterns', 'spectrum' )
			);
		}

		return $this->validate_patterns( $patterns );
	}

	/**
	 * Get the API key from wp-config.php
	 *
	 * @return string|false API key or false if not found
	 */
	private function get_api_key(): string|false {
		return defined( 'SPECTRUM_API_KEY' ) ? constant( 'SPECTRUM_API_KEY' ) : false;
	}

	/**
	 * Validate and sanitize pattern data
	 *
	 * @param array $patterns Raw pattern data from API
	 * @return array Validated pattern data
	 */
	private function validate_patterns( $patterns ): array {
		$validated_patterns = [];

		foreach ( $patterns as $pattern ) {
			if ( empty( $pattern['name'] ) || empty( $pattern['title'] ) || empty( $pattern['content'] ) ) {
				continue;
			}

			$validated_pattern = [
				'name'    => sanitize_title( $pattern['name'] ),
				'title'   => sanitize_text_field( $pattern['title'] ),
				'content' => wp_kses_post( $pattern['content'] ),
			];

			if ( ! empty( $pattern['description'] ) ) {
				$validated_pattern['description'] = sanitize_textarea_field( $pattern['description'] );
			}

			if ( ! empty( $pattern['lastUpdated'] ) ) {
				$validated_pattern['lastUpdated'] = sanitize_text_field( $pattern['lastUpdated'] );
			}

			if ( ! empty( $pattern['categories'] ) && is_array( $pattern['categories'] ) ) {
				$validated_pattern['categories'] = array_map( 'sanitize_text_field', $pattern['categories'] );
			}

			if ( ! empty( $pattern['keywords'] ) && is_array( $pattern['keywords'] ) ) {
				$validated_pattern['keywords'] = array_map( 'sanitize_text_field', $pattern['keywords'] );
			}

			if ( ! empty( $pattern['viewportWidth'] ) ) {
				$validated_pattern['viewportWidth'] = absint( $pattern['viewportWidth'] );
			}

			if ( ! empty( $pattern['blockTypes'] ) && is_array( $pattern['blockTypes'] ) ) {
				$validated_pattern['blockTypes'] = array_map( 'sanitize_text_field', $pattern['blockTypes'] );
			}

			if ( ! empty( $pattern['postTypes'] ) && is_array( $pattern['postTypes'] ) ) {
				$validated_pattern['postTypes'] = array_map( 'sanitize_text_field', $pattern['postTypes'] );
			}

			if ( ! empty( $pattern['templateTypes'] ) && is_array( $pattern['templateTypes'] ) ) {
				$validated_pattern['templateTypes'] = array_map( 'sanitize_text_field', $pattern['templateTypes'] );
			}

			if ( isset( $pattern['inserter'] ) ) {
				$validated_pattern['inserter'] = (bool) $pattern['inserter'];
			}

			$validated_patterns[] = $validated_pattern;
		}

		return $validated_patterns;
	}
}
