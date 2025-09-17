<?php
/**
 * Cache class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

use WP_Error;

/**
 * Handles caching of pattern data using WordPress Transients API.
 */
class Cache {
	use Singleton;

	/**
	 * Cache key for storing patterns
	 *
	 * @var string
	 */
	private $cache_key = 'spectrum_patterns';

	/**
	 * Cache expiration time in seconds (12 hours)
	 *
	 * @var int
	 */
	private $cache_expiration = 12 * HOUR_IN_SECONDS;

	/**
	 * Get cached patterns
	 *
	 * @return array|false Array of patterns or false if not cached/expired
	 */
	public function get_patterns(): array|false {
		return get_transient( $this->cache_key );
	}

	/**
	 * Set patterns in cache
	 *
	 * @param array $patterns Array of pattern data to cache
	 * @return bool True on success, false on failure
	 */
	public function set_patterns( $patterns ): bool {
		if ( ! is_array( $patterns ) ) {
			return false;
		}

		return set_transient( $this->cache_key, $patterns, $this->cache_expiration );
	}

	/**
	 * Clear the patterns cache
	 *
	 * @return bool True on success, false on failure
	 */
	public function clear_patterns(): bool {
		return delete_transient( $this->cache_key );
	}

	/**
	 * Check if patterns are cached and not expired
	 *
	 * @return bool True if cached patterns exist, false otherwise
	 */
	public function has_valid_cache(): bool {
		$patterns = $this->get_patterns();
		return $patterns !== false && is_array( $patterns );
	}

	/**
	 * Get cache status information
	 *
	 * @return array Cache status with timestamp and expiration info
	 */
	public function get_cache_status(): array {
		$transient_timeout = get_option( '_transient_timeout_' . $this->cache_key );
		$has_cache = $this->has_valid_cache();

		$status = [
			'has_cache'  => $has_cache,
			'cache_key'  => $this->cache_key,
			'expiration' => $this->cache_expiration,
		];

		if ( $transient_timeout ) {
			$status['expires_at'] = $transient_timeout;
			$status['expires_in'] = max( 0, $transient_timeout - time() );
			$status['is_expired'] = time() > $transient_timeout;
		}

		return $status;
	}

	/**
	 * Force refresh cache by clearing and fetching new data
	 *
	 * @return array|WP_Error Array of patterns on success, WP_Error on failure
	 */
	public function refresh_patterns(): array|WP_Error {
		$this->clear_patterns();

		$api_client = ApiClient::instance();
		$patterns = $api_client->fetch_patterns();

		if ( ! is_wp_error( $patterns ) ) {
			$this->set_patterns( $patterns );
		}

		return $patterns;
	}
}
