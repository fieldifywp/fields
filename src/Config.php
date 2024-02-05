<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Package;

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
	 * Config constructor.
	 *
	 * @param string $file Plugin or theme directory.
	 * @param string $slug Package slug.
	 *
	 * @return void
	 */
	public function __construct( string $file, string $slug ) {
		$this->dir  = Package::dir( $file, __DIR__ );
		$this->uri  = Package::uri( $this->dir );
		$this->slug = $slug;
	}

}
