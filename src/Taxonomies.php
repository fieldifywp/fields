<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use function apply_filters;
use function esc_html;
use function esc_html__;
use function register_taxonomy;
use function wp_parse_args;

/**
 * Taxonomies class.
 *
 * @since 1.0.0
 */
class Taxonomies {

	/**
	 * Taxonomies.
	 *
	 * @var array
	 */
	private array $taxonomies = [];

	/**
	 * Registers a taxonomy.
	 *
	 * @param string       $id        Name.
	 * @param string|array $post_type Post types.
	 * @param array        $args      Arguments.
	 *
	 * @return void
	 */
	public function register_taxonomy( string $id, $post_type, array $args ): void {
		$args['post_types'] = (array) $post_type;

		$this->taxonomies[ $id ] = $args;
	}

	/**
	 * Register custom taxonomies.
	 *
	 * @hook init 0
	 *
	 * @return void
	 */
	public function register_custom_taxonomies(): void {
		$taxonomies = $this->get_custom_taxonomies();

		foreach ( $taxonomies as $taxonomy => $args ) {
			register_taxonomy(
				$taxonomy,
				$args['post_types'],
				$args
			);
		}
	}

	/**
	 * Gets custom taxonomies.
	 *
	 * @return ?array
	 */
	private function get_custom_taxonomies(): ?array {
		$config     = apply_filters( self::class, $this->taxonomies );
		$taxonomies = [];

		foreach ( $config as $taxonomy => $args ) {
			$singular = esc_html( $args['singular'] ?? Str::title_case( $taxonomy ) );
			$plural   = esc_html( $args['plural'] ?? $singular . 's' );

			$labels = [
				'name'                       => $plural,
				'singular_name'              => $singular,
				'menu_name'                  => $plural,
				'all_items'                  => esc_html__( 'All ', 'fieldify' ) . $plural,
				'parent_item'                => esc_html__( 'Parent ', 'fieldify' ) . $singular,
				'parent_item_colon'          => esc_html__( 'Parent ', 'fieldify' ) . $singular . ':',
				'new_item_name'              => esc_html__( 'New ', 'fieldify' ) . $singular . esc_html__( ' Name', 'fieldify' ),
				'add_new_item'               => esc_html__( 'Add New ', 'fieldify' ) . $singular,
				'edit_item'                  => esc_html__( 'Edit ', 'fieldify' ) . $singular,
				'update_item'                => esc_html__( 'Update ', 'fieldify' ) . $singular,
				'view_item'                  => esc_html__( 'View ', 'fieldify' ) . $singular,
				'separate_items_with_commas' => esc_html__( 'Separate ', 'fieldify' ) . $plural . esc_html__( ' with commas', 'fieldify' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove ', 'fieldify' ) . $plural,
				'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'fieldify' ),
				'popular_items'              => esc_html__( 'Popular ', 'fieldify' ) . $plural,
				'search_items'               => esc_html__( 'Search ', 'fieldify' ) . $plural,
				'not_found'                  => esc_html__( 'Not Found', 'fieldify' ),
				'no_terms'                   => esc_html__( 'No ', 'fieldify' ) . $plural,
				'items_list'                 => $plural . esc_html__( ' list', 'fieldify' ),
				'items_list_navigation'      => $plural . esc_html__( ' list navigation', 'fieldify' ),
			];

			$defaults = [
				'labels'            => $labels,
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'show_in_rest'      => true,
				'post_types'        => [ 'post' ],
			];

			$taxonomies[ $taxonomy ] = wp_parse_args( $args, $defaults );
		}

		return $taxonomies;
	}
}
