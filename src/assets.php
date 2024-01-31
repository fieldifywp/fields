<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use function array_values;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_register_style;

/**
 * Enqueues editor assets.
 *
 * @since 0.0.14
 *
 * @return void
 */
function enqueue_editor_assets(): void {
	if ( ! is_admin() ) {
		return;
	}

	$dir        = get_dir();
	$asset_file = $dir . 'public/js/index.asset.php';

	if ( ! file_exists( $asset_file ) ) {
		return;
	}

	$slug = get_slug();
	$uri  = get_uri();

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
			'postType'  => get_post_type(),
			'blocks'    => get_blocks(),
			'metaBoxes' => get_meta_boxes(),
			'settings'  => get_settings(),
		]
	);
}
