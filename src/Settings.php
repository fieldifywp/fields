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
		return apply_filters( self::HOOK, [] );
	}

}
