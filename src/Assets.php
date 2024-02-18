<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Icon;
use RuntimeException;
use function array_values;
use function esc_html;
use function filemtime;
use function function_exists;
use function get_current_screen;
use function get_post_type;
use function glob;
use function is_readable;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;
use const GLOB_ONLYDIR;

/**
 * Assets.
 *
 * @since 0.0.14
 */
class Assets {

	/**
	 * @var Config $config
	 */
	private Config $config;

	/**
	 * @var Blocks $blocks
	 */
	private Blocks $blocks;

	/**
	 * @var MetaBoxes $meta_boxes
	 */
	private MetaBoxes $meta_boxes;

	/**
	 * @var Settings $settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @since 0.0.14
	 *
	 * @param Config    $config     Config.
	 * @param Blocks    $blocks     Blocks.
	 * @param MetaBoxes $meta_boxes Meta boxes.
	 * @param Settings  $settings   Settings.
	 */
	public function __construct(
		Config    $config,
		Blocks    $blocks,
		MetaBoxes $meta_boxes,
		Settings  $settings
	) {
		$this->config     = $config;
		$this->blocks     = $blocks;
		$this->meta_boxes = $meta_boxes;
		$this->settings   = $settings;
	}

	/**
	 * Enqueues editor assets.
	 *
	 * @since 1.0.0
	 *
	 * @throws RuntimeException If asset file is not readable.
	 *
	 * @hook  enqueue_block_editor_assets 10
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		$dir        = $this->config->dir;
		$asset_file = $dir . 'public/js/index.asset.php';

		if ( ! is_readable( $asset_file ) ) {
			throw new RuntimeException( static::class . ' asset file is not readable.' );
		}

		$asset          = require $asset_file;
		$slug           = $this->config->slug;
		$url            = $this->config->url;
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		wp_register_style( ...array_values( [
			'handle' => $slug,
			'src'    => $url . 'public/css/index.css',
			'deps'   => [],
			'ver'    => filemtime( $dir . 'public/css/index.css' ),
			'media'  => 'all',
		] ) );

		wp_enqueue_style( $slug );

		wp_register_script( ...array_values( [
			'handle'    => $slug,
			'src'       => $url . 'public/js/index.js',
			'deps'      => $asset['dependencies'] ?? [],
			'ver'       => $asset['version'] ?? filemtime( $dir . 'public/js/index.js' ),
			'in_footer' => true,
		] ) );

		wp_enqueue_script( $slug );

		wp_localize_script(
			$slug,
			$slug,
			[
				'slug'       => $slug,
				'postType'   => esc_html( get_post_type() ),
				'siteEditor' => $current_screen && $current_screen->base === 'site-editor',
				'blocks'     => $this->blocks->get_blocks(),
				'metaBoxes'  => $this->meta_boxes->get_meta_boxes(),
				'settings'   => $this->settings->get_settings(),
			]
		);
	}

	/**
	 * Registers icons rest route.
	 *
	 * @since 1.0.0
	 *
	 * @hook  after_setup_theme
	 *
	 * @return void
	 */
	public function register_icons(): void {
		$icon_sets = glob( $this->config->dir . 'public/icons/*', GLOB_ONLYDIR );

		foreach ( $icon_sets as $icon_set ) {
			$icon_set = basename( $icon_set );

			Icon::register_icon_set( $icon_set, $this->config->dir . "public/icons/$icon_set" );
		}

		Icon::register_rest_route( 'fieldify/v1' );
	}

}
