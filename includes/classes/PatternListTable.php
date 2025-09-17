<?php
/**
 * Pattern List Table class
 *
 * @package Spectrum
 */

namespace Eighteen73\Spectrum;

use WP_List_Table;

/**
 * Custom WP_List_Table for displaying cached patterns
 */
class PatternListTable extends WP_List_Table {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( [
			'singular' => 'pattern',
			'plural'   => 'patterns',
			'ajax'     => false,
		] );
	}

	/**
	 * Get columns for the table
	 *
	 * @return array
	 */
	public function get_columns(): array {
		$columns = [
			'name'          => __( 'Name', 'spectrum' ),
			'title'         => __( 'Title', 'spectrum' ),
			'categories'    => __( 'Categories', 'spectrum' ),
			'description'   => __( 'Description', 'spectrum' ),
			'blockTypes'    => __( 'Block Types', 'spectrum' ),
			'postTypes'     => __( 'Post Types', 'spectrum' ),
			'templateTypes' => __( 'Template Types', 'spectrum' ),
			'viewportWidth' => __( 'Viewport Width', 'spectrum' ),
			'inserter'      => __( 'Inserter', 'spectrum' ),
		];

		return $columns;
	}

	/**
	 * Get default hidden columns
	 *
	 * @return array
	 */
	public static function get_default_hidden_columns(): array {
		return [
			'blockTypes',
			'postTypes',
			'templateTypes',
			'viewportWidth',
		];
	}

	/**
	 * Get sortable columns
	 *
	 * @return array|bool
	 */
	public function get_sortable_columns(): array|bool {
		return [
			'name'  => [ 'name', false ],
			'title' => [ 'title', false ],
		];
	}

	/**
	 * Prepare items for display
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		$cache    = Cache::instance();
		$patterns = $cache->get_patterns();

		if ( ! $patterns || ! is_array( $patterns ) ) {
			$this->items = [];
			return;
		}

		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'title';
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'asc';

		usort( $patterns, function( $a, $b ) use ( $orderby, $order ) {
			if ( ! isset( $a[ $orderby ] ) || ! isset( $b[ $orderby ] ) ) {
				return 0;
			}

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			return ( $order === 'desc' ) ? -$result : $result;
		} );

		$per_page     = $this->get_items_per_page( 'spectrum_patterns_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = count( $patterns );

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );

		$this->items = array_slice( $patterns, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$screen           = get_current_screen();
		$columns          = $this->get_columns();
		$hidden_columns   = $screen ? get_user_option( "manage{$screen->id}columnshidden" ) : [];
		$sortable_columns = $this->get_sortable_columns();

		if ( false === $hidden_columns ) {
			$hidden_columns = self::get_default_hidden_columns();
		}

		$this->_column_headers = [ $columns, $hidden_columns, $sortable_columns ];
	}

	/**
	 * Default column display
	 *
	 * @param array  $item        Pattern data
	 * @param string $column_name Column name
	 * @return string
	 */
	public function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'name':
				return '<code>' . esc_html( $item['name'] ) . '</code>';

			case 'title':
				return '<strong>' . esc_html( $item['title'] ) . '</strong>';

			case 'categories':
				if ( ! empty( $item['categories'] ) ) {
					if ( is_array( $item['categories'] ) ) {
						return esc_html( implode( ', ', $item['categories'] ) );
					} else {
						return esc_html( $item['categories'] );
					}
				}
				return '—';

			case 'description':
				if ( ! empty( $item['description'] ) ) {
					return esc_html( wp_trim_words( $item['description'], 15 ) );
				}
				return '—';

			case 'blockTypes':
				if ( ! empty( $item['blockTypes'] ) && is_array( $item['blockTypes'] ) ) {
					$block_types = array_map( function( $type ) {
						return '<code>' . esc_html( $type ) . '</code>';
					}, $item['blockTypes'] );
					return implode( ', ', $block_types );
				}
				return '—';

			case 'postTypes':
				if ( ! empty( $item['postTypes'] ) && is_array( $item['postTypes'] ) ) {
					$post_types = array_map( function( $type ) {
						return '<code>' . esc_html( $type ) . '</code>';
					}, $item['postTypes'] );
					return implode( ', ', $post_types );
				}
				return '—';

			case 'templateTypes':
				if ( ! empty( $item['templateTypes'] ) && is_array( $item['templateTypes'] ) ) {
					$template_types = array_map( function( $type ) {
						return '<code>' . esc_html( $type ) . '</code>';
					}, $item['templateTypes'] );
					return implode( ', ', $template_types );
				}
				return '—';

			case 'viewportWidth':
				if ( ! empty( $item['viewportWidth'] ) ) {
					return esc_html( $item['viewportWidth'] . 'px' );
				}
				return '—';

			case 'inserter':
				if ( isset( $item['inserter'] ) ) {
					return $item['inserter'] ?
						'<span style="color: #46b450;">●</span> ' . esc_html__( 'Yes', 'spectrum' ) :
						'<span style="color: #dc3232;">●</span> ' . esc_html__( 'No', 'spectrum' );
				}
				return '<span style="color: #46b450;">●</span> ' . esc_html__( 'Yes', 'spectrum' );

			default:
				return '';
		}
	}

	/**
	 * Message to display when no items are found
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No patterns found in cache.', 'spectrum' );
	}

}
