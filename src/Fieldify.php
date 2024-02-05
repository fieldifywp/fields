<?php

declare( strict_types=1 );

use Blockify\Utilities\Container;
use Blockify\Utilities\Hook;
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
