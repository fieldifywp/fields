<?php

declare( strict_types=1 );

/**
 * Pluggable functions.
 */

namespace {

	if ( ! function_exists( 'register_block' ) ) {
		function register_block( string $id, array $args ): void {
			\Fieldify\Fields\register_block( $id, $args );
		}
	}

	if ( ! function_exists( 'register_meta_box' ) ) {
		function register_meta_box( string $id, array $args ): void {
			\Fieldify\Fields\register_meta_box( $id, $args );
		}
	}

	if ( ! function_exists( 'register_settings' ) ) {
		function register_settings( string $id, array $settings ): void {
			\Fieldify\Fields\register_settings( $id, $settings );
		}
	}
}

/**
 * Namespaced functions.
 */

namespace Fieldify\Fields {

	use function add_filter;
	use function array_merge;

	/**
	 * Registers a block.
	 *
	 * @param string $id   The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	function register_block( string $id, array $args ): void {
		add_filter(
			Blocks::HOOK,
			static fn( array $blocks ): array => array_merge( $blocks, [ $id => $args ] )
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
			MetaBoxes::HOOK,
			static fn( array $meta_boxes ): array => array_merge( $meta_boxes, [ $args ] )
		);
	}

	/**
	 * Registers settings.
	 *
	 * @param string $id       The settings ID.
	 * @param array  $settings The settings.
	 *
	 * @return void
	 */
	function register_settings( string $id, array $settings ): void {
		add_filter(
			Settings::HOOK,
			static fn( array $registered_settings ): array => array_merge( $registered_settings, [ $id => $settings ] )
		);
	}

}
