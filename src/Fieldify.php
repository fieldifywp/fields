<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

/**
 * Fieldify Singleton.
 *
 * @since 1.0.0
 */
final class Fieldify {

	/**
	 * Plugin or theme directory.
	 *
	 * @var string
	 */
	private string $dir;

	/**
	 * Constructor.
	 *
	 * @param string $dir Plugin or theme directory.
	 */
	public function __construct( string $dir ) {
		$this->dir = $dir;
	}

	/**
	 * Register the plugin or theme.
	 *
	 * @param string $dir Plugin or theme directory.
	 *
	 * @return self
	 */
	public static function register( string $dir ): self {
		return new self( $dir );
	}


}
