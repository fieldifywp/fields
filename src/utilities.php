<?php

declare( strict_types=1 );

use Blockify\Utilities\Block;
use Blockify\Utilities\Container;
use Blockify\Utilities\Icon;
use Fieldify\Fields\Blocks;
use Fieldify\Fields\Config;
use Fieldify\Fields\MetaBoxes;
use Fieldify\Fields\Settings;

if ( ! class_exists( 'Fieldify' ) ) {

	/**
	 * Fieldify factory/facade.
	 *
	 * @since 1.0.0
	 */
	class Fieldify {

		/**
		 * Registers the package configuration.
		 *
		 * @param string $file Main plugin or theme file.
		 * @param string $slug The package slug.
		 *
		 * @return Config
		 */
		public static function register( string $file, string $slug = Config::SLUG ): Config {
			static $configs = [];

			if ( ! isset( $configs[ $file ] ) ) {
				$container        = Container::instance( $file );
				$configs[ $file ] = $container->make( Config::class, [ $file, $slug ] );
			}

			return $configs[ $file ];
		}

		/**
		 * Registers a block.
		 *
		 * @param string $id   The block name.
		 * @param array  $args The block arguments.
		 *
		 * @return void
		 */
		public static function register_block( string $id, array $args ): void {
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
		public static function register_meta_box( string $id, array $args ): void {
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
		public static function register_settings( string $id, array $settings ): void {
			add_filter(
				Settings::HOOK,
				static fn( array $registered_settings ): array => array_merge( $registered_settings, [ $id => $settings ] )
			);
		}
	}
}

if ( ! function_exists( 'register_block' ) ) {

	/**
	 * Registers a block.
	 *
	 * @param string $id   The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	function register_block( string $id, array $args ): void {
		Fieldify::register_block( $id, $args );
	}
}

if ( ! function_exists( 'register_meta_box' ) ) {

	/**
	 * Registers a meta box.
	 *
	 * @param string $id   The meta box ID.
	 * @param array  $args The meta box arguments.
	 *
	 * @return void
	 */
	function register_meta_box( string $id, array $args ): void {
		Fieldify::register_meta_box( $id, $args );
	}
}

if ( ! function_exists( 'register_settings' ) ) {

	/**
	 * Registers settings.
	 *
	 * @param string $id   The settings ID.
	 * @param array  $args The settings.
	 *
	 * @return void
	 */
	function register_settings( string $id, array $args ): void {
		Fieldify::register_settings( $id, $args );
	}
}

if ( ! function_exists( 'get_icon' ) ) {

	/**
	 * Returns svg string for given icon.
	 *
	 * @since 0.9.10
	 *
	 * @param string          $set  Icon set.
	 * @param string          $name Icon name.
	 * @param string|int|null $size Icon size.
	 *
	 * @return string
	 */
	function get_icon( string $set, string $name, $size = null ): string {
		return Icon::get_icon( $set, $name, $size );
	}
}

if ( ! function_exists( 'is_rendering_preview' ) ) {

	/**
	 * Checks if a block is currently rendering in the editor.
	 *
	 * @return bool
	 */
	function is_rendering_preview(): bool {
		return Block::is_rendering_preview();
	}
}
