<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use function add_filter;
use function dirname;
use function get_template_directory;
use function str_contains;
use function str_replace;
use function trailingslashit;

/**
 * Returns the slug of the package.
 *
 * @return string
 */
function get_slug(): string {
	return 'fieldify';
}

/**
 * Returns the path to the package.
 *
 * @return string
 */
function get_dir(): string {
	return trailingslashit( dirname( __DIR__ ) );
}

/**
 * Returns the URI to the package.
 *
 * @return string
 */
function get_uri(): string {
	$path = str_replace( dirname( get_template_directory() ), '', get_dir() );

	if ( is_theme() ) {
		return get_template_directory() . $path;
	} else {
		return plugin_dir_url( get_dir() );
	}
}

/**
 * Checks if the package is being used in a theme.
 *
 * @return bool
 */
function is_theme(): bool {
	$package_dir = get_dir();
	$theme_dir   = dirname( get_template_directory() );

	if ( str_contains( $package_dir, $theme_dir ) ) {
		return true;
	}

	return false;
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
}
