<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use WP_User;
use function apply_filters;
use function esc_html;
use function printf;
use function update_user_meta;

class UserProfile {

	/**
	 * User profile fields.
	 *
	 * @var array
	 */
	private array $user_profile_fields = [];

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
	 * Registers custom user profile fields.
	 *
	 * @param string $id     Fields ID.
	 * @param array  $fields Fields.
	 *
	 * @return void
	 */
	public function register_user_profile_fields( string $id, array $fields ): void {
		$this->user_profile_fields[ $id ] = $fields;
	}

	/**
	 * Get custom user profile fields.
	 *
	 * @return array
	 */
	public function get_user_profile_fields(): array {
		return apply_filters( self::class, $this->user_profile_fields );
	}

	/**
	 * Register custom user profile fields.
	 *
	 * @hook admin_init
	 *
	 * @return void
	 */
	public function register_user_profile_field_hooks(): void {
		if ( ! current_user_can( 'edit_user' ) ) {
			return;
		}

		$instance = $this;
		$configs  = $instance->get_user_profile_fields();

		foreach ( $configs as $id => $fields ) {
			add_action(
				'show_user_profile',
				static fn( WP_User $user ) => $instance->render_user_profile_fields(
					$user,
					$id,
					$fields
				)
			);
			add_action(
				'edit_user_profile',
				static fn( WP_User $user ) => $instance->render_user_profile_fields(
					$user,
					$id,
					$fields
				)
			);
			add_action( 'personal_options_update',
				static fn( int $user_id ) => $instance->save_user_profile_fields(
					$user_id,
					$id,
					$fields
				)
			);
			add_action( 'edit_user_profile_update',
				static fn( int $user_id ) => $instance->save_user_profile_fields(
					$user_id,
					$id,
					$fields
				)
			);
		}
	}

	/**
	 * Render user profile fields.
	 *
	 * @param WP_User $user   User object.
	 * @param string  $id     Fields ID.
	 * @param array   $fields Fields.
	 *
	 * @return void
	 */
	public function render_user_profile_fields( WP_User $user, string $id, array $fields ): void {
		if ( empty( $fields ) ) {
			return;
		}

		printf(
			'<div id="fieldify-user-profile-%s" class="fieldify-user-profile-fields"></div>',
			esc_html( $id )
		);
	}

	/**
	 * Save user profile fields.
	 *
	 * @param int    $user_id User ID.
	 * @param string $id      Fields ID.
	 * @param array  $fields  Fields.
	 *
	 * @return void
	 */
	public function save_user_profile_fields( int $user_id, string $id, array $fields ): void {
		$data = $_POST['fieldify_data'] ?? [];

		if ( empty( $data ) ) {
			return;
		}

		$data = stripslashes( $data );
		$data = json_decode( $data, true );

		if ( ! is_array( $data ) ) {
			return;
		}

		foreach ( $data as $key => $value ) {
			if ( ! isset( $fields[ $key ] ) ) {
				continue;
			}

			$sanitized = $this->sanitizer->sanitize_meta(
				$value,
				$key,
				'user',
				$id,
				$fields[ $key ]
			);

			update_user_meta( $user_id, $key, $sanitized );
		}
	}
}
