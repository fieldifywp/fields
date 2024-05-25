<?php

declare( strict_types=1 );

use Blockify\Container\Container;
use Blockify\Container\ContainerFactory;
use Blockify\Hooks\Hook;
use Fieldify\Fields\Assets;
use Fieldify\Fields\Blocks;
use Fieldify\Fields\Config;
use Fieldify\Fields\MetaBoxes;
use Fieldify\Fields\PostTypes;
use Fieldify\Fields\Settings;
use Fieldify\Fields\Taxonomies;
use Fieldify\Fields\TermFields;
use Fieldify\Fields\UserInterface;

/**
 * Fieldify facade.
 *
 * @since 1.0.0
 */
final class Fieldify {

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
		TermFields::class,
		UserInterface::class,
	];

	/**
	 * Container instance.
	 *
	 * @var ?Container
	 */
	private static ?Container $container = null;

	/**
	 * Registers instance.
	 *
	 * @param string $file Main plugin or theme file.
	 *
	 * @return void
	 */
	public static function register( string $file ): void {
		if ( ! is_null( self::$container ) || ! file_exists( $file ) ) {
			return;
		}

		self::$container = ContainerFactory::create( self::class );

		self::$container->make( Config::class, $file );

		foreach ( self::SERVICES as $id ) {
			Hook::annotations( self::$container->make( $id ) );
		}
	}

	/**
	 * Registers custom term fields.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param array  $fields   Fields.
	 *
	 * @return void
	 */
	public static function register_custom_term_fields( string $taxonomy, array $fields ): void {
		self::$container->get( TermFields::class )->register_custom_term_fields( $taxonomy, $fields );
	}
}
