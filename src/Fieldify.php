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

/**
 * Fieldify facade/proxy.
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
	 * Register block.
	 *
	 * @param string $id   The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	public static function register_block( string $id, array $args ): void {
		self::$container->get( Blocks::class )->register_block( $id, $args );
	}

	/**
	 * Registers a custom post type.
	 *
	 * @param string $id   The post type ID.
	 * @param array  $args (Optional). The post type arguments.
	 *
	 * @return void
	 */
	public static function register_post_type( string $id, array $args = [] ): void {
		self::$container->get( PostTypes::class )->register_post_type( $id, $args );
	}

	/**
	 * Registers a custom taxonomy.
	 *
	 * @param string       $id        The taxonomy ID.
	 * @param string|array $post_type Post type string or array of strings.
	 * @param array        $args      The taxonomy arguments.
	 *
	 * @return void
	 */
	public static function register_taxonomy( string $id, $post_type, array $args ): void {
		self::$container->get( Taxonomies::class )->register_taxonomy( $id, $post_type, $args );
	}

	/**
	 * Registers a meta box.
	 *
	 * @param string $id   The meta box ID.
	 * @param array  $args The meta box arguments.
	 *
	 * @return void
	 */
	public static function register_meta_box( string $id, array $args ): void {
		self::$container->get( MetaBoxes::class )->register_meta_box( $id, $args );
	}

	/**
	 * Registers a custom setting.
	 *
	 * @param string $id   The setting ID.
	 * @param array  $args The setting arguments.
	 *
	 * @return void
	 */
	public static function register_settings( string $id, array $args ): void {
		self::$container->get( Settings::class )->register_settings( $id, $args );
	}

	/**
	 * Registers custom term fields.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param array  $fields   Fields.
	 *
	 * @return void
	 */
	public static function register_term_fields( string $taxonomy, array $fields ): void {
		self::$container->get( TermFields::class )->register_term_fields( $taxonomy, $fields );
	}
}
