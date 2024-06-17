<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use WP_Block_Editor_Context;
use function apply_filters;
use function array_replace_recursive;
use function esc_html;
use function esc_html__;
use function in_array;
use function is_array;
use function is_callable;
use function is_null;
use function is_string;
use function post_type_exists;
use function register_post_type;

/**
 * PostTypes class.
 *
 * @since 1.0.0
 */
class PostTypes {

	/**
	 * Post types.
	 *
	 * @var array
	 */
	private array $post_types = [];

	/**
	 * Registers a taxonomy.
	 *
	 * @param string $id   Name.
	 * @param array  $args (Optional). Arguments.
	 *
	 * @return void
	 */
	public function register_post_type( string $id, array $args = [] ): void {
		$this->post_types[ $id ] = $args;
	}

	/**
	 * Registers post types.
	 *
	 * @hook init 11
	 *
	 * @return void
	 */
	public function register_post_types() {
		$post_types = $this->get_custom_post_types();

		foreach ( $post_types as $post_type => $args ) {
			if ( post_type_exists( $post_type ) ) {
				continue;
			}

			$supports = $args['supports'] ?? [];

			// Custom fields are required for meta to save.
			if ( ! in_array( 'custom-fields', $supports, true ) ) {
				$supports[] = 'custom-fields';

				$args['supports'] = $supports;
			}

			register_post_type( $post_type, $args );
		}
	}

	/**
	 * Limit the blocks allowed in changelog post type.
	 *
	 * @param bool|string[]           $allowed_blocks Array of allowable blocks.
	 * @param WP_Block_Editor_Context $post           Current editor context.
	 *
	 * @hook allowed_block_types_all
	 *
	 * @return bool|string[]
	 */
	public function allowed_blocks( $allowed_blocks, WP_Block_Editor_Context $post ) {
		if ( ! $post->post ) {
			return $allowed_blocks;
		}

		$post_types = $this->get_custom_post_types();

		foreach ( $post_types as $name => $args ) {
			if ( $post->post->post_type !== $name ) {
				continue;
			}

			$post_type_allowed_blocks = $args['allowed_blocks'] ?? null;

			if ( is_null( $post_type_allowed_blocks ) ) {
				continue;
			}

			if ( is_string( $post_type_allowed_blocks ) ) {
				$allowed_blocks = [ $post_type_allowed_blocks ];
			} elseif ( is_array( $post_type_allowed_blocks ) ) {
				$allowed_blocks = $post_type_allowed_blocks;
			} elseif ( is_callable( $post_type_allowed_blocks ) ) {
				$allowed_blocks = $post_type_allowed_blocks( $allowed_blocks, $post );
			}
		}

		return $allowed_blocks;
	}

	/**
	 * Returns post type default arguments.
	 *
	 * @param string $post_type Post type.
	 * @param array  $args      Post type arguments.
	 *
	 * @return array
	 */
	public function get_post_type_args( string $post_type, array $args ): array {
		$singular = esc_html( $args['singular'] ?? Str::title_case( $post_type ) );
		$plural   = esc_html( $args['plural'] ?? $singular . 's' );

		$labels = [
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => $singular . esc_html__( ' Archives', 'fieldify' ),
			'attributes'            => $singular . esc_html__( ' Attributes', 'fieldify' ),
			'parent_item_colon'     => esc_html__( 'Parent :', 'fieldify' ) . $singular . ':',
			'all_items'             => esc_html__( 'All ', 'fieldify' ) . $plural,
			'add_new_item'          => esc_html__( 'Add New ', 'fieldify' ) . $singular,
			'add_new'               => esc_html__( 'Add New', 'fieldify' ),
			'new_item'              => esc_html__( 'New ', 'fieldify' ) . $singular,
			'edit_item'             => esc_html__( 'Edit ', 'fieldify' ) . $singular,
			'update_item'           => esc_html__( 'Update ', 'fieldify' ) . $singular,
			'view_item'             => esc_html__( 'View ', 'fieldify' ) . $singular,
			'view_items'            => esc_html__( 'View ', 'fieldify' ) . $plural,
			'search_items'          => esc_html__( 'Search ', 'fieldify' ) . $singular,
			'not_found'             => esc_html__( 'Not found', 'fieldify' ),
			'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'fieldify' ),
			'featured_image'        => esc_html__( 'Featured Image', 'fieldify' ),
			'set_featured_image'    => esc_html__( 'Set featured image', 'fieldify' ),
			'remove_featured_image' => esc_html__( 'Remove featured image', 'fieldify' ),
			'use_featured_image'    => esc_html__( 'Use as featured image', 'fieldify' ),
			'insert_into_item'      => esc_html__( 'Insert into ', 'fieldify' ) . $singular,
			'uploaded_to_this_item' => esc_html__( 'Uploaded to this ', 'fieldify' ) . $singular,
			'items_list'            => $plural . esc_html__( ' list', 'fieldify' ),
			'items_list_navigation' => $plural . esc_html__( ' list navigation', 'fieldify' ),
			'filter_items_list'     => esc_html__( 'Filter ', 'fieldify' ) . $plural . esc_html__( ' list', 'fieldify' ),
		];

		// Note: Custom field support is required for meta fields to work.
		$defaults = [
			'label'               => $plural,
			'description'         => $plural . esc_html__( ' Description', 'fieldify' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt' ],
			'taxonomies'          => [],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-admin-post',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_rest'        => true,
			'capability_type'     => 'page',
		];

		return array_replace_recursive( $defaults, $args );
	}

	/**
	 * Gets custom post types.
	 *
	 * @return ?array
	 */
	private function get_custom_post_types(): ?array {
		$config     = apply_filters( self::class, $this->post_types );
		$post_types = [];

		foreach ( $config as $post_type => $args ) {
			$post_types[ $post_type ] = $this->get_post_type_args( $post_type, $args );
		}

		return $post_types;
	}

}
