<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

class Sanitizer {

	const SANITIZE_TYPES = [
		'text'     => 'sanitize_text_field',
		'textarea' => 'sanitize_text_field',
		'hidden'   => 'sanitize_text_field',
		'color'    => 'sanitize_text_field',
		'unit'     => 'sanitize_text_field',
		'license'  => 'sanitize_text_field',
		'buttons'  => 'sanitize_text_field',
		'search'   => 'sanitize_text_field',
		'select'   => [ self::class, 'sanitize_text_field' ],
	];

	const FIELD_TYPES = [
		'text',
		'toggle',
		'checkbox',
		'number',
		'unit',
		'range',
		'select',
		'multiselect',
		'textarea',
		'hidden',
		'image',
		'embed',
		'gallery',
		'icon',
		'color',
		'repeater',
		'license',
		'code',
		'post',
		'html',
		'buttons',
		'search',
	];

	/**
	 * Sanitize.
	 *
	 * @since 0.1.0
	 *
	 * @param string $type  Type.
	 * @param mixed  $value Value.
	 *
	 * @return mixed
	 */
	public function sanitize_field( string $type, $value ) {
		switch ( $type ) {
			case 'text' | 'textarea' | 'hidden' | 'color' | 'unit' | 'license' | 'buttons' | 'search':
				return sanitize_text_field( $value );

		}
	}
}
