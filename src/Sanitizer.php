<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use function function_exists;
use function is_array;
use function is_callable;
use function method_exists;
use function sanitize_text_field;

class Sanitizer {

	private const MAP = [

		// Standard sanitizers.
		'textarea' => 'sanitize_text_field',
		'radio'    => 'sanitize_text_field',
		'color'    => 'sanitize_text_field',
		'license'  => 'sanitize_text_field',
		'unit'     => 'sanitize_text_field',
		'embed'    => 'sanitize_url',
		'toggle'   => 'rest_sanitize_boolean',
		'checkbox' => 'rest_sanitize_boolean',
		'number'   => 'floatval',
		'range'    => 'floatval',
		'image'    => 'intval',
		'post'     => 'intval',
		'html'     => 'wp_kses_post',
		'code'     => 'wp_kses_post', // TODO: Add custom sanitization.

		// Custom sanitizers.
		'text'     => 'sanitize_input_field',
		'select'   => 'sanitize_select_field',
		'gallery'  => 'sanitize_gallery_field',
		'icon'     => 'sanitize_icon_field',
		'repeater' => 'sanitize_repeater_field',
	];

	/**
	 * Sanitize.
	 *
	 * @param mixed  $meta_value     Value.
	 * @param string $meta_key       Meta key.
	 * @param string $object_type    Object type.
	 * @param string $object_subtype Object subtype.
	 * @param array  $field_args     Field args.
	 *
	 * @return mixed
	 */
	public function sanitize_field( $meta_value, string $meta_key, string $object_type, string $object_subtype, array $field_args ) {
		$type = $field_args['type'] ?? 'text';

		if ( ! isset( self::MAP[ $type ] ) ) {
			return sanitize_text_field( $meta_value );
		}

		$callback = self::MAP[ $type ] ?? 'sanitize_text_field';
		$custom   = $field_args['sanitizeCallback'] ?? $field_args['sanitize_callback'] ?? null;

		if ( method_exists( $this, $callback ) ) {
			$sanitized = $this->$callback( $field_args, $meta_value, $meta_key, $object_type, $object_subtype );
		} elseif ( function_exists( $callback ) ) {
			$sanitized = $callback( $meta_value );
		} else {
			$sanitized = sanitize_text_field( $meta_value );
		}

		if ( is_callable( $custom ) ) {
			$sanitized = $custom( $sanitized, $meta_key, $object_type, $object_subtype );
		}

		return $sanitized;
	}

	/**
	 * Sanitize input.
	 *
	 * @param array $field_args Field args.
	 * @param mixed $meta_value Value.
	 *
	 * @return string
	 */
	private function sanitize_input_field( array $field_args, $meta_value ): string {
		$input_type = $field_args['input_type'] ?? $field_args['inputType'] ?? 'text';

		$map = [
			'text'     => 'sanitize_text_field',
			'password' => 'sanitize_text_field',
			'hidden'   => 'sanitize_text_field',
			'date'     => 'sanitize_text_field',
			'time'     => 'sanitize_text_field',
			'url'      => 'sanitize_url',
			'email'    => 'sanitize_email',
			'file'     => 'sanitize_file_name',
		];

		if ( ! isset( $map[ $input_type ] ) ) {
			return sanitize_text_field( $meta_value );
		}

		$callback = $map[ $input_type ];

		if ( ! function_exists( $callback ) ) {
			return sanitize_text_field( $meta_value );
		}

		return $callback( $meta_value );
	}

	/**
	 * Sanitize select.
	 *
	 * @param array $field_args Field args.
	 * @param mixed $meta_value Value.
	 *
	 * @return array
	 */
	private function sanitize_select_field( array $field_args, $meta_value ): array {
		if ( ! is_array( $meta_value ) ) {
			return [];
		}

		$multiple = $field_args['multiple'] ?? false;

		if ( $multiple ) {
			foreach ( $meta_value as $option ) {
				foreach ( $option as $key => $val ) {
					$option[ $key ] = sanitize_text_field( $val );
				}
			}
		} else {
			foreach ( $meta_value as $key => $val ) {
				$meta_value[ $key ] = sanitize_text_field( $val );
			}
		}

		return $meta_value;
	}

	/**
	 * Sanitize gallery.
	 *
	 * @param array $field_args Field args.
	 * @param mixed $meta_value Value.
	 *
	 * @return array
	 */
	private function sanitize_gallery_field( array $field_args, $meta_value ): array {
		if ( ! is_array( $meta_value ) ) {
			return [];
		}

		foreach ( $meta_value as $key => $value ) {
			$meta_value[ $key ] = intval( $value );
		}

		return $meta_value;
	}

	/**
	 * Sanitize icon.
	 *
	 * @param array $field_args Field args.
	 * @param mixed $meta_value Value.
	 *
	 * @return array
	 */
	private function sanitize_icon_field( array $field_args, $meta_value ): array {
		if ( ! is_array( $meta_value ) ) {
			return [];
		}

		foreach ( $meta_value as $key => $value ) {
			$meta_value[ $key ] = sanitize_text_field( $value );
		}

		return $meta_value;
	}

	/**
	 * Sanitize repeater.
	 *
	 * @param array  $field_args     Field args.
	 * @param mixed  $meta_value     Value.
	 * @param string $meta_key       Meta key.
	 * @param string $object_type    Object type.
	 * @param string $object_subtype Object subtype.
	 *
	 * @return array
	 */
	private function sanitize_repeater_field( array $field_args, $meta_value, string $meta_key, string $object_type, string $object_subtype ): array {
		if ( ! is_array( $meta_value ) ) {
			return [];
		}

		$subfields = $field_args['subfields'] ?? [];

		foreach ( $meta_value as $index => $value ) {
			foreach ( $value as $field_id => $field_value ) {
				$subfield_args = $subfields[ $field_id ] ?? [];

				if ( ! $subfield_args ) {
					continue;
				}

				$type = $subfield_args['type'] ?? null;

				if ( ! $type ) {
					continue;
				}

				$meta_value[ $index ][ $field_id ] = $this->sanitize_field( $field_value, $field_id, $object_type, $object_subtype, $subfield_args );
			}
		}

		return $meta_value;
	}
}
