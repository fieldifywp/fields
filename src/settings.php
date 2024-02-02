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
