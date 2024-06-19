<?php

declare( strict_types=1 );

namespace Fieldify\Fields\Integrations;

use Fieldify\Fields\MetaBoxes;
use function array_merge;
use function call_user_func;
use function class_exists;
use function in_array;
use function remove_meta_box;

/**
 * EDD integration.
 *
 * @since 1.0.0
 */
class EDD {

	/**
	 * Meta boxes.
	 *
	 * @var MetaBoxes
	 */
	private MetaBoxes $meta_boxes;

	/**
	 * EDD constructor.
	 *
	 * @param MetaBoxes $meta_boxes Meta boxes.
	 *
	 * @return void
	 */
	public function __construct( MetaBoxes $meta_boxes ) {
		$this->meta_boxes = $meta_boxes;
	}

	/**
	 * Adds custom field support to EDD downloads only during save post.
	 *
	 * @since 0.5.2
	 *
	 * @param array $supports Post type supports.
	 *
	 * @hook  edd_download_supports
	 *
	 * @return array
	 */
	public function add_edd_custom_field_support( array $supports ): array {
		if ( $this->is_enabled() ) {
			$supports[] = 'custom-fields';
		}

		return $supports;
	}

	/**
	 * Removes the EDD custom fields meta box.
	 *
	 * @since 0.5.2
	 *
	 * @param string $post_type The post type.
	 *
	 * @hook  do_meta_boxes
	 *
	 * @return void
	 */
	public function remove_edd_custom_fields_meta_box( string $post_type ): void {
		if ( ! $this->is_enabled() || 'download' !== $post_type ) {
			return;
		}

		global $wp_filter;

		$edd_supports = $wp_filter['edd_download_supports'] ?? null;

		if ( ! isset( $edd_supports->callbacks ) ) {
			return;
		}

		$supports = [];

		foreach ( $edd_supports->callbacks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'] ?? null;

				if ( $function !== __NAMESPACE__ . '\\add_edd_custom_field_support' ) {
					$supports = array_merge(
						call_user_func( $function, [] ),
						$supports
					);
				}
			}
		}

		if ( ! in_array( 'custom-fields', $supports, true ) ) {
			remove_meta_box( 'postcustom', 'download', 'normal' );
		}
	}

	/**
	 * Checks if integration is enabled.
	 *
	 * @return bool
	 */
	private function is_enabled(): bool {
		if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return false;
		}

		$post_types = [];

		foreach ( $this->meta_boxes->get_meta_boxes() as $meta_box ) {
			$post_types = array_merge( $post_types, $meta_box['post_types'] );
		}

		return in_array( 'download', $post_types, true );
	}
}
