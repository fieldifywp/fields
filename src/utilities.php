<?php

declare( strict_types=1 );

/**
 * Pluggable functions.
 */

namespace {

	if ( ! function_exists( 'register_block' ) ) {
		function register_block( string $name, array $args ): void {
			\Fieldify\Fields\register_block( $name, $args );
		}
	}

	if ( ! function_exists( 'register_meta_box' ) ) {
		function register_meta_box( string $id, array $args ): void {
			\Fieldify\Fields\register_meta_box( $id, $args );
		}
	}
}

/**
 * Namespaced functions.
 */

namespace Fieldify\Fields {

	use function add_filter;
	use function basename;
	use function content_url;
	use function dirname;
	use function explode;
	use function get_template_directory;
	use function implode;
	use function is_null;
	use function str_contains;
	use function str_replace;
	use function trailingslashit;
	use function wp_list_pluck;
	use const DIRECTORY_SEPARATOR;
	use const WP_CONTENT_DIR;
	use const WP_PLUGIN_DIR;

	/**
	 * Returns the slug of the package.
	 *
	 * @return string
	 */
	function get_slug(): string {
		return basename( dirname( __DIR__, 2 ) );
	}

	/**
	 * Returns the URI to the package.
	 *
	 * @return string
	 */
	function get_uri(): string {
		return trailingslashit( str_replace( WP_CONTENT_DIR, content_url(), get_dir() ) );
	}

	/**
	 * Returns the directory of the package.
	 *
	 * @param string|null $function The function name.
	 *
	 * @return string|null
	 */
	function get_dir( ?string $function = null ): ?string {
		static $dir = null;

		if ( ! is_null( $dir ) || ! $function ) {
			return trailingslashit( $dir );
		}

		$backtrace  = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$files      = wp_list_pluck( $backtrace, 'file' );
		$plugin_dir = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR;
		$theme_dir  = dirname( get_template_directory() ) . DIRECTORY_SEPARATOR;
		$plugin     = null;
		$theme      = null;

		foreach ( $files as $file ) {
			if ( str_contains( $file, $plugin_dir ) ) {
				$plugin = $file;
				break;
			}

			if ( str_contains( $file, $theme_dir ) ) {
				$theme = $file;
				break;
			}
		}

		if ( $plugin || $theme ) {
			$parts      = $plugin ? explode( $plugin_dir, $plugin ) : explode( $theme_dir, $theme );
			$base       = $parts[1] ?? '';
			$base_dir   = explode( DIRECTORY_SEPARATOR, $base )[0] ?? '';
			$parent_dir = trailingslashit( ( $plugin ? $plugin_dir : $theme_dir ) . $base_dir );
			$dir_parts  = explode( DIRECTORY_SEPARATOR, dirname( __DIR__ ) );
			$last_three = array_slice( $dir_parts, -3, 3, true );
			$dir        = $parent_dir . implode( DIRECTORY_SEPARATOR, $last_three );
		}

		return trailingslashit( $dir );
	}
	
	/**
	 * Sets the directory of the package.
	 *
	 * @param string $function The function name.
	 *
	 * @return void
	 */
	function set_dir( string $function ): void {
		get_dir( $function );
	}

	/**
	 * Registers a block.
	 *
	 * @param string $name The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	function register_block( string $name, array $args ): void {
		add_filter(
			BLOCKS_FILTER,
			static fn( array $blocks ): array => array_merge( $blocks, [ $name => $args ] )
		);

		set_dir( __FUNCTION__ );

		add_action(
			'enqueue_block_assets',
			static fn() => enqueue_editor_assets()
		);
	}

	/**
	 * Registers a meta box.
	 *
	 * @param string $id   The meta box ID.
	 * @param array  $args The meta box arguments.
	 *
	 * @return void
	 */
	function register_meta_box( string $id, array $args ): void {
		$args['id'] = $id;

		add_filter(
			META_BOXES_FILTER,
			static fn( array $meta_boxes ): array => array_merge( $meta_boxes, [ $args ] )
		);

		set_dir( __FUNCTION__ );

		add_action(
			'enqueue_block_assets',
			static fn() => enqueue_editor_assets()
		);
	}

	/**
	 * Converts a string to title case.
	 *
	 * @param string   $string The string to convert.
	 * @param string[] $search Characters to replace (optional).
	 *
	 * @return string
	 */
	function to_title_case( string $string, array $search = [ '-', '_' ] ): string {
		return trim( ucwords( str_replace( $search, ' ', $string ) ) );
	}


}
