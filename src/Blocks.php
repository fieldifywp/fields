<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use function add_action;
use function apply_filters;
use function array_replace;
use function content_url;
use function dirname;
use function file_exists;
use function filemtime;
use function is_string;
use function register_block_type;
use function str_replace;
use function wp_enqueue_block_style;
use function wp_list_pluck;
use const WP_CONTENT_DIR;

/**
 * Blocks.
 *
 * @since 0.1.0
 */
class Blocks {

	public const HOOK = 'fieldify_blocks';

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
		$blocks = $this->get_blocks();

		foreach ( $blocks as $name => $args ) {
			$enabled = $args['enabled'] ?? true;

			if ( ! $enabled ) {
				continue;
			}

			$file = $args['file'] ?? '';

			unset ( $args['file'] );

			if ( $file && file_exists( $file ) ) {

				if ( ! empty( $args ) ) {
					register_block_type( $file, [
						'render_callback' => $args['render_callback'] ?? null,
					] );
				} else {
					register_block_type( $file );
				}
			} else {
				register_block_type( $name, $args );
			}

			$style = $args['style'] ?? '';

			if ( $style ) {
				$path = str_replace( content_url(), WP_CONTENT_DIR, $style );

				if ( file_exists( $path ) ) {
					wp_enqueue_block_style(
						$name,
						[
							'handle' => str_replace( '/', '-', $name ),
							'src'    => $style,
							'path'   => $path,
							'ver'    => filemtime( $path ),
						]
					);
				}
			}

			$view_script = $args['view_script'] ?? '';

			if ( $view_script && is_string( $view_script ) ) {
				add_action(
					'wp_enqueue_scripts',
					static function () use ( $name, $view_script ): void {
						$path = str_replace( content_url(), WP_CONTENT_DIR, $view_script );

						if ( ! file_exists( $path ) ) {
							return;
						}

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
							$view_script,
							$deps ?? [],
							$version ?? filemtime( $path ),
							true
						);
					}
				);
			}
		}
	}

	/**
	 * Register the block category.
	 *
	 * @param array $block_categories The block categories.
	 *
	 * @hook block_categories_all
	 *
	 * @return array
	 */
	public function register_categories( array $block_categories ): array {
		$blocks = $this->get_blocks();
		$slugs  = wp_list_pluck( $block_categories, 'slug' );

		foreach ( $blocks as $block ) {
			$category = $block['category'] ?? null;

			if ( $category && ! isset( $slugs[ $category ] ) ) {
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
	 * @return array
	 */
	public function get_blocks(): array {
		$blocks = apply_filters( self::HOOK, [] );

		foreach ( $blocks as $name => $args ) {
			$defaults = [
				'apiVersion' => 2,
				'name'       => $name,
				'title'      => Str::title_case( $name ),
				'category'   => 'custom',
				'attributes' => [],
				'enabled'    => true,
			];

			$blocks[ $name ] = array_replace( $defaults, $args );
		}

		return $blocks;
	}

}
