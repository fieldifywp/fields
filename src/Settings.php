<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use function add_filter;
use function apply_filters;
use function array_merge;
use function register_setting;

/**
 * Settings.
 *
 * @since 0.1.0
 */
class Settings {

	public const HOOK = 'fieldify_settings';

	/**
	 * Meta boxes.
	 *
	 * @var MetaBoxes
	 */
	private MetaBoxes $meta_boxes;

	/**
	 * Rest schema.
	 *
	 * @var RestSchema
	 */
	private RestSchema $rest_schema;

	/**
	 * Meta boxes.
	 *
	 * @param MetaBoxes  $meta_boxes  Meta boxes.
	 * @param RestSchema $rest_schema Rest schema.
	 *
	 * @return void
	 */
	public function __construct( MetaBoxes $meta_boxes, RestSchema $rest_schema ) {
		$this->meta_boxes  = $meta_boxes;
		$this->rest_schema = $rest_schema;
	}

	/**
	 * Registers settings.
	 *
	 * @param string $id       The settings ID.
	 * @param array  $settings The settings.
	 *
	 * @return void
	 */
	public static function register_settings( string $id, array $settings ): void {
		add_filter(
			static::HOOK,
			static fn( array $registered_settings ): array => array_merge( $registered_settings, [ $id => $settings ] )
		);
	}

	/**
	 * Get settings.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$settings  = apply_filters( self::HOOK, [] );
		$formatted = [];

		foreach ( $settings as $id => $args ) {
			if ( ! isset( $args['name'] ) ) {
				$args['name'] = $id;
			}

			$panels = $args['panels'] ?? [];

			foreach ( $panels as $panel_id => $panel ) {
				$panel['initialOpen'] = $panel['initial_open'] ?? false;

				unset( $panel['initial_open'] );

				$args['panels'][ $panel_id ] = $panel;
			}

			$fields = $args['fields'] ?? [];

			foreach ( $fields as $field_id => $field ) {
				$args['fields'][ $field_id ] = $this->meta_boxes->replace_condition_key( $field, 'setting' );

				if ( isset( $field['show_if'] ) ) {
					$args['fields'][ $field_id ]['showIf'] = $field['show_if'];
					$field['showIf']                       = $field['show_if'];

					unset( $args['fields'][ $field_id ]['show_if'] );
				}

				if ( ! empty( $field['showIf'] ?? [] ) ) {
					foreach ( $field['showIf'] as $index => $showIf ) {
						if ( isset( $showIf['setting'] ) ) {
							$args['fields'][ $field_id ]['showIf'][ $index ]['condition'] = $showIf['setting'];

							unset( $args['fields'][ $field_id ]['showIf'][ $index ]['setting'] );
						}
					}
				}
			}

			$formatted[ $id ] = $args;
		}

		return $formatted;
	}

	/**
	 * Register rest settings.
	 *
	 * @since 1.0.0
	 *
	 * @hook  admin_init
	 * @hook  rest_api_init
	 *
	 * @return void
	 */
	public function register_rest_setting(): void {
		$settings = $this->get_settings();

		foreach ( $settings as $id => $args ) {
			$fields = [];

			foreach ( ( $args['fields'] ?? [] ) as $field_id => $field ) {
				$fields[ $field_id ] = $this->rest_schema->get_item_schema( $field ) ?? [
					'type' => 'string',
				];
			}

			register_setting(
				'options',
				$id,
				[
					'description'  => $args['title'] ?? Str::title_case( $id ),
					'type'         => 'object',
					'show_in_rest' => [
						'schema' => [
							'type'       => 'object',
							'properties' => $fields ?? [],
						],
					],
				]
			);
		}
	}

}
