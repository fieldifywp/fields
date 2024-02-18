<?php

declare( strict_types=1 );

use Blockify\Utilities\Container;
use Blockify\Utilities\Hook;
use Fieldify\Fields\Assets;
use Fieldify\Fields\Blocks;
use Fieldify\Fields\Config;
use Fieldify\Fields\MetaBoxes;
use Fieldify\Fields\PostTypes;
use Fieldify\Fields\Settings;
use Fieldify\Fields\Taxonomies;

if ( ! class_exists( 'Fieldify' ) ) {

	/**
	 * Fieldify service provider.
	 *
	 * @since 1.0.0
	 */
	class Fieldify {

		/**
		 * Services.
		 *
		 * @var array
		 */
		private static array $services = [
			Assets::class,
			Blocks::class,
			MetaBoxes::class,
			PostTypes::class,
			Settings::class,
			Taxonomies::class,
		];

		/**
		 * Constructor.
		 *
		 * @param string $file Main plugin or theme file.
		 *
		 * @return void
		 */
		public function __construct( string $file ) {
			self::register( $file );
		}

		/**
		 * Registers instance.
		 *
		 * @param string $file Main plugin or theme file.
		 *
		 * @return Container
		 */
		public static function register( string $file ): Container {
			static $instances = [];

			if ( ! isset( $instances[ $file ] ) ) {
				$container = new Container();

				$container->make( Config::class, $file );

				foreach ( static::$services as $id ) {
					$service = $container->make( $id );

					if ( is_object( $service ) ) {
						Hook::annotations( $service );
					}
				}

				$instances[ $file ] = $container;
			}

			return $instances[ $file ];
		}
	}
}
