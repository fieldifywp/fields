<?php

declare( strict_types=1 );

namespace Fieldify\Fields;

use Blockify\Utilities\Str;
use stdClass;
use WP_Upgrader;
use function delete_transient;
use function get_option;
use function get_transient;
use function in_array;
use function is_object;
use function is_wp_error;
use function json_decode;
use function property_exists;
use function set_transient;
use function str_replace;
use function version_compare;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;

/**
 * Class LemonSqueezy.
 *
 * @since 1.0.0
 */
class LemonSqueezy {

	/**
	 * Plugin or theme ID.
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Plugin or theme slug.
	 *
	 * @var string
	 */
	public string $slug;

	/**
	 * Current version of the plugin or theme.
	 *
	 * @var string
	 */
	public string $version;

	/**
	 * API URL to the update server.
	 *
	 * @var string
	 */
	public string $api_url;

	/**
	 * Cache key for the transient.
	 *
	 * @var string
	 */
	public string $cache_key;

	/**
	 * Whether to allow caching.
	 *
	 * @var boolean
	 */
	public bool $cache_allowed;

	/**
	 * Plugin or theme name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * Option name for the license key.
	 *
	 * @var string
	 */
	public string $option;

	/**
	 * Updater constructor.
	 *
	 * @param string $id      The ID of the plugin.
	 * @param string $slug    The slug of the plugin.
	 * @param string $version The current version of the plugin.
	 * @param string $api_url The API URL to the update server.
	 *
	 * @return void
	 */
	public function __construct(
		string $id,
		string $slug,
		string $version,
		string $api_url,
		string $name = '',
		string $option = ''
	) {
		$this->id            = $id;
		$this->slug          = $slug;
		$this->version       = $version;
		$this->api_url       = $api_url;
		$this->cache_key     = str_replace( '-', '_', $this->slug ) . '_updater';
		$this->cache_allowed = true; // Only disable this for debugging.
		$this->name          = $name ?: Str::title_case( $slug );
		$this->option        = $option ?: 'license_key';
	}

	/**
	 * Fetch the update info from the remote server running the
	 * Lemon Squeezy plugin.
	 *
	 * @param bool    $force   Force a request to the remote server.
	 * @param ?string $license The license key to use. (Optional).
	 *
	 * @return object|bool
	 */
	public function request( bool $force = false, string $license = null ) {
		$license_key = $license ?? $this->get_license_key();

		if ( ! $license_key ) {
			return false;
		}

		$transient = $force ? false : get_transient( $this->cache_key );

		if ( false !== $transient && $this->cache_allowed ) {
			if ( 'error' === $transient ) {
				return false;
			}

			return json_decode( $transient );
		}

		$remote = wp_remote_get(
			$this->api_url . "/update?license_key={$license_key}",
			[
				'timeout' => 10,
			]
		);

		$body = wp_remote_retrieve_body( $remote );
		$code = wp_remote_retrieve_response_code( $remote );
		$json = (object) [];

		if ( ! empty( $body ) ) {
			$json = json_decode( $body ) ?? (object) [];
		}

		$missing_files = property_exists( $json, 'error_code' ) && 'missing_files' === $json->error_code;

		if (
			is_wp_error( $remote )
			|| ( 200 !== $code && ! $missing_files )
			|| empty( $body )
		) {
			set_transient( $this->cache_key, 'error', MINUTE_IN_SECONDS * 10 );

			return false;
		}

		set_transient( $this->cache_key, $body, DAY_IN_SECONDS );

		return $json;
	}

	/**
	 * Override the WordPress request to return the correct plugin
	 * info.
	 *
	 * @see  https://developer.wordpress.org/reference/hooks/plugins_api/
	 *
	 * @param false|object|array $result ($override for themes_api)
	 * @param string             $action Requested action.
	 * @param object             $args   Arguments for query.
	 *
	 * @hook plugins_api 20
	 * @hook themes_api 20
	 *
	 * @return object|bool
	 */
	public function info( $result, string $action, object $args ) {
		if ( ! in_array( $action, [ 'plugin_information', 'theme_information' ], true ) ) {
			return false;
		}

		if ( $this->slug !== $args->slug ) {
			return false;
		}

		$remote = $this->request();

		if ( ! $remote ) {
			return false;
		}

		if ( ! is_object( $remote ) ) {
			return false;
		}

		if ( ! property_exists( $remote, 'success' ) || ! property_exists( $remote, 'update' ) ) {
			return false;
		}

		if ( ! $remote->success || empty( $remote->update ) ) {
			return false;
		}

		$sections         = $result->sections ?? [];
		$result           = $remote->update;
		$result->name     = $this->name;
		$result->slug     = $this->slug;
		$result->sections = (array) $sections;

		return $result;
	}

	/**
	 * Override the WordPress request to check if an update is available.
	 *
	 * @see  https://make.wordpress.org/core/2020/07/30/recommended-usage-of-the-updates-api-to-support-the-auto-updates-ui-for-plugins-and-themes-in-wordpress-5-5/
	 *
	 * @param object|bool $transient The plugin or theme update data.
	 *
	 * @hook site_transient_update_plugins
	 * @hook site_transient_update_themes
	 *
	 * @return object|bool
	 */
	public function update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$response = (object) [
			'id'            => $this->id,
			'slug'          => $this->slug,
			'plugin'        => $this->id,
			'new_version'   => $this->version,
			'url'           => '',
			'package'       => '',
			'icons'         => [],
			'banners'       => [],
			'banners_rtl'   => [],
			'tested'        => '',
			'requires_php'  => '',
			'compatibility' => new stdClass(),
		];

		$remote = $this->request();

		$success = is_object( $remote ) && property_exists( $remote, 'success' ) && $remote->success;
		$update  = ( is_object( $remote ) && property_exists( $remote, 'update' ) && ! empty( $remote->update ) ) ? $remote->update : false;
		$version = ( $update && property_exists( $remote->update, 'version' ) ) ? $remote->update->version : '';
		$new     = version_compare( $this->version, $version, '<' );

		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		if ( $success && $new ) {
			$response->new_version = $version;
			$response->package     = $update->download_link;

			if ( ! property_exists( $transient, 'response' ) ) {
				$transient->response = [];
			}

			$transient->response[ $response->plugin ] = $response;
		} else {

			if ( ! property_exists( $transient, 'no_update' ) ) {
				$transient->no_update = [];
			}

			$transient->no_update[ $response->plugin ] = $response;
		}

		return $transient;
	}

	/**
	 * When the update is complete, purge the cache.
	 *
	 * @see  https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
	 *
	 * @param WP_Upgrader $upgrader Upgrader instance.
	 * @param array       $options  Options for the upgrader.
	 *
	 * @hook upgrader_process_complete
	 *
	 * @return void
	 */
	public function purge( WP_Upgrader $upgrader, array $options ): void {
		if ( ! $this->cache_allowed ) {
			return;
		}

		if ( ( $options['action'] ?? '' ) !== 'update' ) {
			return;
		}

		$type = $options['type'] ?? '';

		if ( 'plugin' !== $type && 'theme' !== $type ) {
			return;
		}

		$slugs = $options[ $type . 's' ] ?? [];

		if ( ! in_array( $this->slug, $slugs, true ) && ! in_array( $this->id, $slugs ) ) {
			return;
		}

		delete_transient( $this->cache_key );
	}

	/**
	 * Get the license key. Normally, your plugin would have a
	 * settings page where you ask for and store a license
	 * key. Fetch it here.
	 *
	 * @return ?string
	 */
	protected function get_license_key(): ?string {
		return get_option( $this->slug )[ $this->option ] ?? null;
	}
}
