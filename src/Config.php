<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Path;
use function dirname;

/**
 * Config class.
 *
 * @since 1.0.0
 */
class Config {

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
	 * Custom slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Config constructor.
	 *
	 * @param string $file Plugin or theme directory.
	 * @param string $slug Package slug.
	 *
	 * @return void
	 */
	public function __construct( string $file, string $slug ) {
		$this->dir  = Path::get_package_dir( $file, dirname( __DIR__ ) );
		$this->url  = Path::get_package_url( $this->dir );
		$this->slug = $slug;
	}

}
