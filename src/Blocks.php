<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Arr;
use Blockify\Utilities\Path;
use Blockify\Utilities\Str;
use function add_action;
use function apply_filters;
use function array_replace;
use function content_url;
use function dirname;
use function file_exists;
use function filemtime;
use function get_template_directory;
use function in_array;
use function register_block_type;
use function str_replace;
use function wp_enqueue_block_style;
use function wp_list_pluck;

/**
 * Blocks.
 *
 * @since 0.1.0
 */
class Blocks {

	public const  HOOK = 'fieldify_blocks';

	private const DEFAULT_CATEGORY = 'custom';

	/**
	 * Project directory.
	 *
	 * @var string
	 */
	private string $project_dir;

	/**
	 * Project URL.
	 *
	 * @var string
	 */
	private string $project_url;

	/**
	 * Package directory.
	 *
	 * @var string
	 */
	private string $package_dir;

	/**
	 * Meta boxes.
	 *
	 * @var MetaBoxes
	 */
	private MetaBoxes $meta_boxes;

	/**
	 * Constructor.
	 *
	 * @param Config $config The package configuration.
	 *
	 * @return void
	 */
	public function __construct( Config $config, MetaBoxes $meta_boxes ) {
		$this->project_dir = Path::get_project_dir( $config->dir );
		$this->project_url = Path::get_project_url( $this->project_dir );
		$this->package_dir = $config->dir;
		$this->meta_boxes  = $meta_boxes;
	}

	/**
	 * Registers a block.
	 *
	 * @param string $id   The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	public static function register_block( string $id, array $args ): void {
		add_filter(
			static::HOOK,
			static fn( array $blocks ): array => array_merge( $blocks, [ $id => $args ] )
		);
	}

	/**
	 * Register blocks.
	 *
	 * @since 0.1.0
	 *
	 * @hook  init
	 *
	 * @return void
	 */
	public function register_blocks(): void {
		$blocks = $this->get_blocks( false );

		foreach ( $blocks as $name => $args ) {
			$this->register_block_from_args( $name, $args );
		}
	}

	/**
	 * Register the block category.
	 *
	 * @param array $block_categories The block categories.
	 *
	 * @hook block_categories_all 11
	 *
	 * @return array
	 */
	public function register_categories( array $block_categories ): array {
		$blocks = $this->get_blocks();
		$slugs  = wp_list_pluck( $block_categories, 'slug' );

		foreach ( $blocks as $block ) {
			$category = $block['category'] ?? null;

			if ( $category && ! in_array( $category, $slugs ) && ! isset( $slugs[ $category ] ) ) {
				$title              = Str::title_case( $category );
				$slugs[ $category ] = $title;

				$block_categories[] = [
					'slug'  => $category,
					'title' => $title,
				];
			}
		}

		return $block_categories;
	}

	/**
	 * Returns array of custom blocks.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $camel_case Whether to convert keys to camel case.
	 *
	 * @return array
	 */
	public function get_blocks( $camel_case = true ): array {
		$blocks = apply_filters( self::HOOK, [] );

		foreach ( $blocks as $name => $args ) {
			$defaults = [
				'apiVersion' => 2,
				'name'       => $name,
				'title'      => Str::title_case( $name ),
				'category'   => self::DEFAULT_CATEGORY,
				'attributes' => [],
				'enabled'    => true,
			];

			$attrs = $args['attributes'] ?? [];

			if ( $attrs ) {
				foreach ( $attrs as $attr_key => $attribute ) {
					$args['attributes'][ $attr_key ] = $this->meta_boxes->replace_condition_key( $attribute, 'attribute' );
				}
			}

			if ( $camel_case ) {
				$args = Arr::keys_to_camel_case( $args );
			}

			$blocks[ $name ] = array_replace( $defaults, $args );
		}

		return $blocks;
	}

	/**
	 * Fixes block asset src generated by WP.
	 *
	 * @since 1.0.0
	 *
	 * @param string $src Script or style src.
	 *
	 * @hook  script_loader_src
	 * @hook  style_loader_src
	 *
	 * @return string
	 */
	public function replace_src( string $src ): string {
		$search         = 'plugins' . dirname( __DIR__, 3 );
		$project_parent = Path::get_segment( $this->package_dir, -5 );
		$vendor_dir     = Path::get_segment( $project_parent, 3 );

		if ( Str::contains_all( $src, $search, '/blocks/' ) ) {
			$src = str_replace(
				$search,
				$vendor_dir,
				$src
			);
		}

		return $src;
	}

	/**
	 * Registers a block from given arguments.
	 *
	 * @param string $name The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	private function register_block_from_args( string $name, array $args ): void {
		$enabled = $args['enabled'] ?? true;

		if ( ! $enabled ) {
			return;
		}

		$file = $args['file'] ?? '';

		unset ( $args['file'] );

		if ( $file && file_exists( $file ) ) {
			if ( ! empty( $args ) ) {
				$custom_args = [];

				if ( isset( $args['render_callback'] ) ) {
					$custom_args['render_callback'] = $args['render_callback'];
				}

				$category = $args['category'] ?? '';

				if ( $category && $category !== self::DEFAULT_CATEGORY ) {
					$custom_args['category'] = $category;
				}

				register_block_type( $file, $custom_args );
			} else {
				register_block_type( $file );
			}
		} else {
			register_block_type( $name, $args );
		}

		$this->enqueue_block_style( $name, $args );
		$this->enqueue_view_script( $name, $args );
	}

	/**
	 * Enqueues block style.
	 *
	 * @param string $name The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	private function enqueue_block_style( string $name, array $args ): void {
		$style = $args['style'] ?? '';

		if ( ! $style ) {
			return;
		}

		$path = $this->get_asset_path( $style );

		if ( ! $path ) {
			return;
		}

		$src = $this->get_asset_src( $style );

		wp_enqueue_block_style(
			$name,
			[
				'handle' => str_replace( '/', '-', $name ),
				'src'    => $src,
				'path'   => $path,
				'ver'    => filemtime( $path ),
			]
		);
	}

	/**
	 * Enqueues block view script.
	 *
	 * @param string $name The block name.
	 * @param array  $args The block arguments.
	 *
	 * @return void
	 */
	private function enqueue_view_script( string $name, array $args ): void {
		$script = $args['view_script'] ?? '';

		if ( ! $script ) {
			return;
		}

		$path = $this->get_asset_path( $script );

		if ( ! $path ) {
			return;
		}

		$src      = $this->get_asset_src( $script );
		$instance = $this;

		add_action(
			'wp_enqueue_scripts',
			static fn() => $instance->view_script_callback( $name, $path, $src )
		);
	}

	/**
	 * Returns asset path.
	 *
	 * @param string $asset The asset path.
	 *
	 * @return string
	 */
	private function get_asset_path( string $asset ): string {
		$content_dir = dirname( get_template_directory(), 2 );
		$content_url = content_url();
		$is_relative = ! Str::contains_any( $asset, $content_dir, $content_url );

		if ( $is_relative ) {
			$path = $this->project_dir . $asset;
		} else {
			$path = str_replace( $content_url, $content_dir, $asset );
		}

		return file_exists( $path ) ? $path : '';
	}

	/**
	 * Returns asset src.
	 *
	 * @param string $asset The asset path.
	 *
	 * @return string
	 */
	private function get_asset_src( string $asset ): string {
		$content_dir = dirname( get_template_directory(), 2 );
		$content_url = content_url();
		$is_relative = ! Str::contains_any( $asset, $content_dir, $content_url );

		if ( $is_relative ) {
			return $this->project_url . $asset;
		} else {
			return str_replace( $content_dir, $content_url, $asset );
		}
	}

	/**
	 * Callback for enqueuing view script.
	 *
	 * @param string $name The block name.
	 * @param string $path The view script path.
	 * @param string $src  The view script URL.
	 *
	 * @return void
	 */
	private function view_script_callback( string $name, string $path, string $src ): void {
		$base      = basename( $path );
		$dir       = dirname( $path );
		$asset_php = "$dir/asset.php";
		$version   = null;
		$deps      = null;

		if ( file_exists( $asset_php ) ) {
			$asset   = require $asset_php;
			$version = $asset['version'] ?? null;
			$deps    = $asset['dependencies'] ?? null;
		}

		wp_enqueue_script(
			$name,
			$src,
			$deps ?? [],
			$version ?? filemtime( $path ),
			true
		);
	}

}
