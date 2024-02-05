<?php

declare( strict_types=1 );

use Blockify\Utilities\Block;
use Blockify\Utilities\Container;
use Blockify\Utilities\Hook;
use Blockify\Utilities\Icon;
use Fieldify\Fields\Assets;
use Fieldify\Fields\Blocks;
use Fieldify\Fields\Config;
use Fieldify\Fields\MetaBoxes;
use Fieldify\Fields\Settings;

if ( ! class_exists( 'Fieldify' ) ) {

	/**
	 * Fieldify service provider.
	 *
	 * @since 1.0.0
	 *
	 * @method static void register( string $file, ?string $slug = null )
	 */
	class Fieldify extends Container {

		/**
		 * Services.
		 *
		 * @var array
		 */
		private array $services = [
			Assets::class,
			Blocks::class,
			MetaBoxes::class,
			Settings::class,
		];

		/**
		 * Constructor.
		 *
		 * @param string  $file Main plugin or theme file.
		 * @param ?string $slug The package slug.
		 *
		 * @return void
		 */
		public function __construct( string $file, ?string $slug = null ) {
			$this->register( $file, $slug );
		}

		/**
		 * Registers services.
		 *
		 * @param string  $file Main plugin or theme file.
		 * @param ?string $slug The package slug.
		 *
		 * @return void
		 */
		protected function register( string $file, ?string $slug = null ): void {
			static $instances = [];

			if ( isset( $instances[ $file ] ) ) {
				return;
			}

			$instances[ $file ] = true;

			$this->make( Config::class, [ $file, $slug ?? strtolower( static::class ) ] );

			foreach ( $this->services as $id ) {
				$service = $this->make( $id );

				if ( is_object( $service ) ) {
					Hook::annotations( $service );
				}
			}
		}

		/**
		 * Magic method to call the register method statically.
		 *
		 * @param string $method    Method name.
		 * @param array  $arguments Method arguments.
		 *
		 * @return void
		 */
		public static function __callStatic( string $method, array $arguments ): void {
			if ( $method !== 'register' ) {
				throw new BadMethodCallException( self::class . '::' . $method . ' can not be called statically.' );
			}

			$static = new static( $arguments[0], $arguments[1] ?? null );
			$static->register( $arguments[0], $arguments[1] ?? null );
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
		Blocks::register_block( $id, $args );
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
		MetaBoxes::register_meta_box( $id, $args );
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
		Settings::register_settings( $id, $args );
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
		return Icon::get_svg( $set, $name, $size );
	}
}

if ( ! function_exists( 'block_is_rendering_preview' ) ) {

	/**
	 * Checks if a block is currently rendering in the editor.
	 *
	 * @return bool
	 */
	function block_is_rendering_preview(): bool {
		return Block::is_rendering_preview();
	}
}
