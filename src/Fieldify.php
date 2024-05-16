<?php

declare( strict_types=1 );

use Blockify\Container\Container;
use Blockify\Hooks\Hook;
use Fieldify\Fields\Assets;
use Fieldify\Fields\Blocks;
use Fieldify\Fields\Config;
use Fieldify\Fields\MetaBoxes;
use Fieldify\Fields\PostTypes;
use Fieldify\Fields\Settings;
use Fieldify\Fields\Taxonomies;

if ( ! class_exists( 'Fieldify' ) ) {

	/**
	 * Fieldify facade.
	 *
	 * @since 1.0.0
	 */
	class Fieldify {

		/**
		 * Services.
		 *
		 * @var array
		 */
		private const SERVICES = [
			Assets::class,
			Blocks::class,
			MetaBoxes::class,
			PostTypes::class,
			Settings::class,
			Taxonomies::class,
		];

		/**
		 * Registers instance.
		 *
		 * @param string $file Main plugin or theme file.
		 *
		 * @return void
		 */
		public static function register( string $file ): void {
			static $container = null;

			if ( ! is_null( $container ) || ! file_exists( $file ) ) {
				return;
			}

			$container = new Container();

			$container->make( Config::class, $file );

			foreach ( self::SERVICES as $id ) {
				$service = $container->make( $id );

				if ( is_object( $service ) ) {
					Hook::annotations( $service );
				}
			}
		}
	}
}
