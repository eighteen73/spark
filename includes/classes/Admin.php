<?php
/**
 * Admin class
 *
 * @package Spark
 */

namespace Eighteen73\Spark;

/**
 * Handles admin interface and cache management.
 */
class Admin {

	use Singleton;

	/**
	 * Run on init
	 *
	 * @return void
	 */
	public function setup(): void {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'handle_admin_actions' ] );
		add_action( 'admin_notices', [ $this, 'show_admin_notices' ] );

		// Schedule weekly sync
		add_action( 'spark_weekly_sync', [ $this, 'weekly_sync' ] );
	}

	/**
	 * Add admin menu
	 *
	 * @return void
	 */
	public function add_admin_menu(): void {
		$hook = add_options_page(
			__( 'Spark', 'spark' ),
			__( 'Spark', 'spark' ),
			'manage_options',
			'spark',
			[ AdminPage::class, 'render' ]
		);

		// Add screen options support
		add_action( "load-{$hook}", [ $this, 'add_screen_options' ] );
	}

	/**
	 * Add screen options to the admin page
	 *
	 * @return void
	 */
	public function add_screen_options(): void {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Add per-page option
		add_screen_option( 'per_page', [
			'label'   => __( 'Patterns per page', 'spark' ),
			'default' => 20,
			'option'  => 'spark_patterns_per_page',
		] );

		// Register columns for screen options
		$table = new PatternListTable();
		add_filter( "manage_{$screen->id}_columns", [ $table, 'get_columns' ] );

		// Set up default hidden columns for new users
		$hidden_columns = get_user_option( "manage{$screen->id}columnshidden" );
		if ( false === $hidden_columns ) {
			$hidden_columns = PatternListTable::get_default_hidden_columns();
			update_user_option( get_current_user_id(), "manage{$screen->id}columnshidden", $hidden_columns );
		}
	}

	/**
	 * Handle admin actions
	 *
	 * @return void
	 */
	public function handle_admin_actions(): void {
		if ( ! isset( $_POST['spark_action'] ) || ! wp_verify_nonce( $_POST['spark_nonce'], 'spark_admin' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$cache = Cache::instance();

		switch ( $_POST['spark_action'] ) {
			case 'update_patterns':
				$result = $cache->update_patterns();

				if ( is_wp_error( $result ) ) {
					set_transient( 'spark_admin_error', $result->get_error_message(), 30 );
				} else {
					set_transient( 'spark_admin_success', sprintf( __( '%d patterns synced successfully', 'spark' ), count( $result ) ), 30 );
				}
				break;
		}

		wp_redirect( admin_url( 'options-general.php?page=spark' ) );
		exit;
	}

	/**
	 * Show admin notices
	 *
	 * @return void
	 */
	public function show_admin_notices(): void {
		$success_message = get_transient( 'spark_admin_success' );
		$error_message   = get_transient( 'spark_admin_error' );

		if ( $success_message ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( $success_message )
			);
			delete_transient( 'spark_admin_success' );
		}

		if ( $error_message ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				esc_html( $error_message )
			);
			delete_transient( 'spark_admin_error' );
		}
	}

	/**
	 * Weekly sync handler
	 *
	 * @return void
	 */
	public function weekly_sync(): void {
		$cache = Cache::instance();
		$cache->update_patterns();
	}
}
