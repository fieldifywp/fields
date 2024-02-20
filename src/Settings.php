<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use function apply_filters;

/**
 * Settings.
 *
 * @since 0.1.0
 */
class Settings {

	public const HOOK = 'fieldify_settings';

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
			$panels = $args['panels'] ?? [];

			foreach ( $panels as $panel_id => $panel ) {
				$panel['initialOpen'] = $panel['initial_open'] ?? false;

				unset( $panel['initial_open'] );

				$args['panels'][ $panel_id ] = $panel;
			}

			$formatted[ $id ] = $args;
		}

		return $formatted;
	}

}
