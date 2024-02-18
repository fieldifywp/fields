<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use WP_Comment;
use WP_Post;
use function add_meta_box;
use function apply_filters;
use function array_key_exists;
use function array_merge;
use function esc_attr;
use function filter_input;
use function in_array;
use function is_a;
use function is_string;
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
	 * Config.
	 *
	 * @var Config
	 */
	private Config $config;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Config $config Config.
	 *
	 * @return void
	 */
	public function __construct( Config $config ) {
		$this->config = $config;
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
		add_filter(
			static::HOOK,
			static fn( array $meta_boxes ): array => array_merge(
				$meta_boxes,
				[ $id => $args ]
			)
		);
	}

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

			foreach ( $fields as $id => $field ) {
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

			foreach ( $fields as $field_id => $field ) {
				if ( $field_id === $meta_key ) {
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
		if ( ! is_a( $object, WP_Post::class ) ) {
			return;
		}

		$meta_boxes = $this->get_meta_boxes();

		foreach ( $meta_boxes as $id => $meta_box ) {
			$slug       = $this->config->slug;
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
						'<div id="%1$s-meta-box-%2$s" class="%1$s-meta-box"></div>',
						$slug,
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
	 * @return array <string, array> Meta boxes.
	 */
	public function get_meta_boxes(): array {
		$meta_boxes = apply_filters( self::HOOK, [] );
		$formatted  = [];

		foreach ( $meta_boxes as $id => $meta_box ) {
			$id = is_string( $id ) ? $id : ( $field['id'] ?? '' );

			if ( ! $id ) {
				continue;
			}

			if ( array_key_exists( $id, $formatted ) ) {
				continue;
			}

			$fields             = $meta_box['fields'] ?? [];
			$meta_box['fields'] = [];

			foreach ( $fields as $field_id => $field ) {
				$field_id = is_string( $field_id ) ? $field_id : ( $field['id'] ?? '' );

				if ( ! $field_id ) {
					continue;
				}

				if ( array_key_exists( $field_id, $meta_box['fields'] ) ) {
					continue;
				}

				$meta_box['fields'][ $field_id ] = $field;
			}

			$formatted[ $id ] = $meta_box;
		}

		return $formatted;
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

		$field_type = $field['control'] ?? 'text';
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
