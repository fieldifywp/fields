<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Container;
use Blockify\Utilities\Hook;
use Blockify\Utilities\Interfaces\Registerable;
use function basename;
use function content_url;
use function dirname;
use function implode;
use function is_object;
use function str_replace;
use function trailingslashit;
use const DIRECTORY_SEPARATOR;
use const WP_CONTENT_DIR;

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
		$this->dir  = $this->get_dir( $file, __DIR__ );
		$this->uri  = str_replace( WP_CONTENT_DIR, content_url(), $this->dir );
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

	/**
	 * Returns the package directory path.
	 *
	 * @param string $file Main plugin or theme file.
	 * @param string $src  Package src directory.
	 *
	 * @return string
	 */
	private function get_dir( string $file, string $src ): string {
		return trailingslashit(
			implode(
				DIRECTORY_SEPARATOR,
				[
					dirname( $file ),
					implode(
						DIRECTORY_SEPARATOR,
						[
							basename( dirname( $src, 3 ) ),
							basename( dirname( $src, 2 ) ),
							basename( dirname( $src, 1 ) ),
						]
					),
				]
			)
		);
	}

}
