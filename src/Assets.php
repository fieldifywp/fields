<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Icon;
use function array_values;
use function file_exists;
use function filemtime;
use function get_post_type;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_script;
use function wp_register_style;

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
	 * @since 0.0.14
	 *
	 * @hook  enqueue_block_editor_assets 10
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		$dir        = $this->config->dir;
		$asset_file = $dir . 'public/js/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$slug = $this->config->slug;
		$uri  = $this->config->uri;

		$style = [
			'handle' => $slug,
			'src'    => $uri . 'public/css/index.css',
			'deps'   => [],
			'ver'    => filemtime( $dir . 'public/css/index.css' ),
			'media'  => 'all',
		];

		wp_register_style( ...array_values( $style ) );

		wp_enqueue_style( $slug );

		$asset = require $asset_file;

		$script = [
			'handle'    => $slug,
			'src'       => $uri . 'public/js/index.js',
			'deps'      => $asset['dependencies'] ?? [],
			'ver'       => $asset['version'] ?? filemtime( $dir . 'public/js/index.js' ),
			'in_footer' => true,
		];

		wp_register_script( ...array_values( $script ) );

		wp_enqueue_script( $slug );

		wp_localize_script(
			$slug,
			$slug,
			[
				'slug'      => $slug,
				'postType'  => get_post_type(),
				'blocks'    => $this->blocks->get_blocks(),
				'metaBoxes' => $this->meta_boxes->get_meta_boxes(),
				'settings'  => $this->settings->get_settings(),
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
	public function register_icons_rest_route(): void {
		Icon::register_rest_route( $this->config->slug . '/v1' );
	}

}
