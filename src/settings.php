<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use function apply_filters;

const SETTINGS_FILTER = 'fieldify_settings';

function get_settings(): array {
	return apply_filters( SETTINGS_FILTER, [] );
}
