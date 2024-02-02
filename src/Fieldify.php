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
use const DIRECTORY_SEPARATOR;
use const WP_CONTENT_DIR;

/**
 * Fieldify Singleton.
 *
 * @since 1.0.0
 */
final class Fieldify implements Registerable {

	/**
	 * Package directory.
	 *
	 * @var string
	 */
	public string $dir;

	/**
	 * Package URL.
	 *
	 * @var string
	 */
	public string $url;

	/**
	 * Fieldify constructor.
	 *
	 * @param string $file Plugin or theme directory.
	 */
	public function __construct( string $file ) {
		$package = implode( DIRECTORY_SEPARATOR, [
			basename( dirname( __DIR__, 3 ) ),
			basename( dirname( __DIR__, 2 ) ),
			basename( dirname( __DIR__ ) ),
		] );

		$this->dir = dirname( $file );
		$this->url = str_replace( WP_CONTENT_DIR, content_url(), $this->dir );
	}

	/**
	 * Register the plugin or theme.
	 *
	 * @param string $file Plugin or theme directory.
	 *
	 * @return self
	 */
	public static function init( string $file ): self {
		static $instance = null;

		if ( ! is_object( $instance ) ) {
			$container = Container::factory();
			$instance  = $container->create( self::class, new self( $file ) );
		}

		return $instance;
	}

	/**
	 * Register the plugin or theme.
	 *
	 * @param Container $container Container.
	 *
	 * @return void
	 */
	public function register( Container $container ): void {
		static $registered = null;

		if ( ! is_null( $registered ) ) {
			return;
		}

		$services = [
			Assets::class,
			Blocks::class,
			MetaBoxes::class,
			Settings::class,
		];

		foreach ( $services as $service ) {
			$container->create( $service );

			if ( is_object( $service ) ) {
				Hook::annotations( $service );
			}
		}

		$registered = true;
	}

}
