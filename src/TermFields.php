<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use WP_Term;
use function add_action;
use function json_decode;
use function printf;

class TermFields {

	/**
	 * Terms config.
	 *
	 * @var array
	 */
	private array $terms = [];

	/**
	 * Sanitizer instance.
	 *
	 * @var Sanitizer $sanitizer
	 */
	private Sanitizer $sanitizer;

	/**
	 * Constructor.
	 *
	 * @param Sanitizer $sanitizer Sanitizer instance.
	 *
	 * @return void
	 */
	public function __construct( Sanitizer $sanitizer ) {
		$this->sanitizer = $sanitizer;
	}

	/**
	 * Registers custom term fields.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param array  $fields   Fields.
	 *
	 * @return void
	 */
	public function register_term_fields( string $taxonomy, array $fields ): void {
		$this->terms[ $taxonomy ] = $fields;
	}

	/**
	 * Register custom term fields.
	 *
	 * @hook admin_init
	 *
	 * @return void
	 */
	public function register_custom_term_field_hooks(): void {
		$terms    = $this->get_custom_term_fields();
		$instance = $this;

		foreach ( $terms as $taxonomy => $fields ) {
			add_action(
				"{$taxonomy}_add_form_fields",
				static fn() => $instance->render_fields( '', $taxonomy ),
				10,
				2
			);

			add_action( "created_{$taxonomy}", [ $this, 'save_fields' ], 10, 3 );
			add_action( "{$taxonomy}_edit_form", [ $this, 'render_fields' ], 10, 2 );
			add_action( "edited_{$taxonomy}", [ $this, 'save_fields' ], 10, 3 );
		}
	}

	/**
	 * Get custom term fields.
	 *
	 * @return array
	 */
	public function get_custom_term_fields(): array {
		return apply_filters( self::class, $this->terms );
	}

	/**
	 * Render fields.
	 *
	 * @param WP_Term|string $tag      Term object.
	 * @param string         $taxonomy Taxonomy.
	 *
	 * @return void
	 */
	public function render_fields( $tag, string $taxonomy ): void {
		printf(
			'<div id="fieldify-term-%s" class="fieldify-term-fields"></div>',
			$taxonomy
		);
	}

	/**
	 * Save fields.
	 *
	 * @param int   $term_id Term ID.
	 * @param int   $tt_id   Term taxonomy ID.
	 * @param array $args    Arguments.
	 *
	 * @return void
	 */
	public function save_fields( int $term_id, int $tt_id, array $args ): void {
		$data = $_POST['fieldify_data'] ?? [];

		if ( empty( $data ) ) {
			return;
		}

		$data = stripslashes( $data );
		$data = json_decode( $data, true );

		if ( ! is_array( $data ) ) {
			return;
		}

		$taxonomy   = $args['taxonomy'] ?? '';
		$taxonomies = $this->get_custom_term_fields();
		$fields     = $taxonomies[ $taxonomy ] ?? [];

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $data as $key => $value ) {
			if ( ! isset( $fields[ $key ] ) ) {
				continue;
			}

			$sanitized = $this->sanitizer->sanitize( $value, $fields[ $key ] );

			update_term_meta( $term_id, $key, $sanitized );
		}
	}
}
