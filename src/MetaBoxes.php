<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use WP_Comment;
use WP_Post;
use function add_meta_box;
use function apply_filters;
use function array_merge;
use function esc_attr;
use function filter_input;
use function in_array;
use function is_a;
use function printf;
use const FILTER_SANITIZE_FULL_SPECIAL_CHARS;
use const INPUT_GET;

/**
 * Meta boxes.
 *
 * @since 0.1.0
 */
class MetaBoxes {

	public const HOOK = 'fieldify_meta_boxes';

	/**
	 * Registers custom post meta.
	 *
	 * @hook after_setup_theme
	 *
	 * @return void
	 */
	public function register_custom_post_meta(): void {
		$meta_boxes = $this->get_meta_boxes();

		$defaults = [
			'string'  => '',
			'number'  => 0,
			'array'   => [],
			'object'  => [],
			'boolean' => false,
		];

		foreach ( $meta_boxes as $meta_box ) {
			$post_types = $meta_box['post_types'] ?? [ 'post' ];
			$fields     = $meta_box['fields'] ?? [];

			foreach ( $fields as $field ) {
				$id     = $field['id'] ?? '';
				$schema = $this->get_item_schema( $field );
				$type   = $schema['type'];

				$args = [
					'type'         => $type,
					'description'  => $field['label'] ?? Str::title_case( $id ),
					'default'      => $field['default'] ?? $defaults[ $type ] ?? null,
					'single'       => true,
					'show_in_rest' => true,
				];

				if ( in_array( $type, [ 'array', 'object' ], true ) ) {
					$args['show_in_rest'] = [
						'schema' => $schema,
					];
				}

				foreach ( $post_types as $post_type ) {
					register_post_meta( $post_type, $id, $args );
				}
			}
		}
	}

	/**
	 * Hide post meta from default custom fields UI.
	 *
	 * @param bool   $protected True if the meta key is protected.
	 * @param string $meta_key  Meta key.
	 *
	 * @hook is_protected_meta
	 *
	 * @return bool
	 */
	public function hide_from_custom_fields( bool $protected, string $meta_key ): bool {
		$post      = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( ! $post && ! $post_type ) {
			return $protected;
		}

		$meta_boxes = $this->get_meta_boxes();

		foreach ( $meta_boxes as $meta_box ) {
			$fields = $meta_box['fields'] ?? [];

			foreach ( $fields as $field ) {
				$id = $field['id'] ?? '';

				if ( $id === $meta_key ) {
					return true;
				}
			}
		}

		return $protected;
	}

	/**
	 * Register meta boxes.
	 *
	 * @param string             $current_post_type Current post type.
	 * @param WP_Post|WP_Comment $object            Post or Comment object.
	 *
	 * @hook add_meta_boxes
	 *
	 * @return void
	 */
	public function add_custom_meta_boxes( string $current_post_type, $object ): void {

		// TODO: Add support for comments, users and terms.
		if ( ! is_a( $object, 'WP_Post' ) ) {
			return;
		}

		$meta_boxes = $this->get_meta_boxes();
		$ids        = [];

		foreach ( $meta_boxes as $meta_box ) {
			$id = $meta_box['id'] ?? '';

			if ( in_array( $id, $ids, true ) ) {
				continue;
			}

			$ids[]      = $id;
			$title      = $meta_box['title'] ?? Str::title_case( $id );
			$post_types = $meta_box['post_types'] ?? [ 'post' ];

			foreach ( $post_types as $post_type ) {
				if ( $current_post_type !== $post_type ) {
					continue;
				}

				add_meta_box(
					$id,
					$title,
					static fn() => printf(
						'<div id="blockify-meta-box-%s" class="blockify-meta-box"></div>',
						esc_attr( $id )
					),
					$post_type,
					$meta_box['context'] ?? 'normal',
					$meta_box['priority'] ?? 'default',
					$meta_box['fields'] ?? []
				);
			}
		}
	}

	/**
	 * Returns filtered array of custom meta boxes.
	 *
	 * @since 0.5.2
	 *
	 * @return array
	 */
	public function get_meta_boxes(): array {
		return apply_filters( self::HOOK, [] );
	}

	/**
	 * Returns array of custom fields.
	 *
	 * @since 0.5.2
	 *
	 * @param ?string $post_type Post type.
	 *
	 * @return array
	 */
	private function get_custom_fields( ?string $post_type = null ): array {
		$meta_boxes = $this->get_meta_boxes();
		$fields     = [];

		foreach ( $meta_boxes as $meta_box ) {
			if ( $post_type && ! in_array( $post_type, $meta_box['post_types'] ?? [], true ) ) {
				continue;
			}

			$fields = array_merge( $fields, $meta_box['fields'] ?? [] );
		}

		return $fields;
	}

	/**
	 * Get the meta type based on the field type.
	 *
	 * @param array $field Field data.
	 *
	 * @return array
	 */
	private function get_item_schema( array $field ): array {
		$type_map = [
			'text'     => [
				'type' => 'string',
			],
			'url'      => [
				'type' => 'string',
			],
			'email'    => [
				'type' => 'string',
			],
			'phone'    => [
				'type' => 'string',
			],
			'password' => [
				'type' => 'string',
			],
			'date'     => [
				'type' => 'string',
			],
			'textarea' => [
				'type' => 'string',
			],
			'radio'    => [
				'type' => 'string',
			],
			'select'   => [
				'type' => 'string',
			],
			'file'     => [
				'type' => 'string',
			],
			'color'    => [
				'type' => 'string',
			],
			'blocks'   => [
				'type' => 'string',
			],
			'embed'    => [
				'type' => 'string',
			],
			'number'   => [
				'type' => 'number',
			],
			'range'    => [
				'type' => 'number',
			],
			'image'    => [
				'type' => 'number',
			],
			'checkbox' => [
				'type' => 'boolean',
			],
			'toggle'   => [
				'type' => 'boolean',
			],
			'icon'     => [
				'type'       => 'object',
				'properties' => [
					'set'  => [
						'type' => 'string',
					],
					'name' => [
						'type' => 'string',
					],
					'html' => [
						'type' => 'string',
					],
				],
			],
			'gallery'  => [
				'type'  => 'array',
				'items' => [
					'type' => 'number',
				],
			],
			'repeater' => [
				'type'  => 'array',
				'items' => [
					'type' => 'object',
				],
			],
		];

		$field_type = $field['type'] ?? 'text';
		$schema     = $type_map[ $field_type ] ?? [ 'type' => 'string' ];
		$sub_type   = $schema['items']['type'] ?? null;

		if ( $sub_type === 'object' ) {
			$sub_fields = $field['subfields'] ?? [];

			foreach ( $sub_fields as $sub_field ) {
				$schema['items']['properties'][ $sub_field['id'] ?? '' ] = $this->get_item_schema( $sub_field );
			}
		}

		return $schema;
	}
}
