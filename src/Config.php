<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Container;
use Blockify\Utilities\Hook;
use Blockify\Utilities\Interfaces\Registerable;
use Blockify\Utilities\Package;
use function is_object;

/**
 * Config class.
 *
 * @since 1.0.0
 */
class Config implements Registerable {

	/**
	 * Default package slug.
	 *
	 * @var string
	 */
	public const SLUG = 'fieldify';

	/**
	 * Package directory.
	 *
	 * @var string
	 */
	public string $dir;

	/**
	 * Package URI.
	 *
	 * @var string
	 */
	public string $uri;

	/**
	 * Custom slug.
	 *
	 * @var string
	 */
	public string $slug;

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
	 * Config constructor.
	 *
	 * @param string $file Plugin or theme directory.
	 * @param string $slug Package slug.
	 *
	 * @return void
	 */
	public function __construct( string $file, string $slug = self::SLUG ) {
		$this->dir  = Package::dir( $file, __DIR__ );
		$this->uri  = Package::uri( $this->dir );
		$this->slug = $slug;
	}

	/**
	 * Register services.
	 *
	 * @param Container $container Container.
	 *
	 * @return void
	 */
	public function register( Container $container ): void {
		foreach ( $this->services as $id ) {
			$service = $container->make( $id );

			if ( is_object( $service ) ) {
				Hook::annotations( $service );
			}
		}
	}
}
