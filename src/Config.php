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
 * Fieldify Singleton.
 *
 * @since 1.0.0
 */
final class Config implements Registerable {

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
	 * Fieldify constructor.
	 *
	 * @param string $file Plugin or theme directory.
	 * @param string $slug Package slug.
	 *
	 * @return void
	 */
	public function __construct( string $file, string $slug = self::SLUG ) {
		$this->dir  = $this->get_dir( $file );
		$this->uri  = str_replace( WP_CONTENT_DIR, content_url(), $this->dir );
		$this->slug = $slug;
	}

	/**
	 * Register the plugin or theme.
	 *
	 * @param Container $container Container.
	 *
	 * @return void
	 */
	public function register( Container $container ): void {
		$services = [
			Assets::class,
			Blocks::class,
			MetaBoxes::class,
			Settings::class,
		];

		foreach ( $services as $service ) {
			$instance = $container->make( $service );

			if ( is_object( $instance ) ) {
				Hook::annotations( $instance );
			}
		}
	}

	/**
	 * Returns the package directory path.
	 *
	 * @param string $file Main plugin or theme file.
	 *
	 * @return string
	 */
	private function get_dir( string $file ): string {
		return trailingslashit(
			implode(
				DIRECTORY_SEPARATOR,
				[
					dirname( $file ),
					implode(
						DIRECTORY_SEPARATOR,
						[
							basename( dirname( __DIR__, 3 ) ),
							basename( dirname( __DIR__, 2 ) ),
							basename( dirname( __DIR__, 1 ) ),
						]
					),
				]
			)
		);
	}

}
