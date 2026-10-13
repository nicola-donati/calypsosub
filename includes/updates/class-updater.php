<?php
/**
 * Auto-update integration against theisland.tech's plugin-updates API.
 * calypsosub is not sold as licensed software (unlike theisland.tech's
 * paid plugins, e.g. cf7-automation-pro), so no license key is sent and
 * the API serves update info to anyone who asks for this slug — same as a
 * public WordPress.org plugin update check.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Calypsosub_Updater {

	const SLUG = 'calypsosub';

	const DEFAULT_API_BASE = 'https://theisland.tech/api/plugin-updates';

	const CHECK_TRANSIENT = 'calypsosub_update_check';

	const CHECK_CACHE_SECONDS = 6 * HOUR_IN_SECONDS;

	public function init(): void {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
	}

	/**
	 * @param object $transient
	 * @return object
	 */
	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$check = $this->get_check_result( $transient->checked[ $this->plugin_basename() ] ?? CALYPSOSUB_VERSION );
		if ( ! $check || empty( $check['updateAvailable'] ) || empty( $check['packageUrl'] ) ) {
			return $transient;
		}

		$transient->response[ $this->plugin_basename() ] = (object) array(
			'id'           => 'theisland.tech/' . self::SLUG,
			'slug'         => self::SLUG,
			'plugin'       => $this->plugin_basename(),
			'new_version'  => $check['latestVersion'],
			'url'          => 'https://theisland.tech',
			'package'      => $check['packageUrl'],
			'requires'     => $check['minWordPressVersion'] ?? '',
			'requires_php' => $check['minPhpVersion'] ?? '',
			'tested'       => $check['testedUpToWordPressVersion'] ?? '',
		);

		return $transient;
	}

	/**
	 * @param false|object|array $result
	 * @param string             $action
	 * @param object              $args
	 * @return false|object|array
	 */
	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || self::SLUG !== $args->slug ) {
			return $result;
		}

		$response = $this->request( '/info', array( 'slug' => self::SLUG ) );
		if ( empty( $response['http_ok'] ) || 200 !== $response['code'] ) {
			return $result;
		}

		$body = $response['data'];

		return (object) array(
			'name'         => $body['name'] ?? 'Calypso Sub Arezzo',
			'slug'         => self::SLUG,
			'version'      => $body['version'] ?? CALYPSOSUB_VERSION,
			'requires'     => $body['requiresWordPress'] ?? '',
			'tested'       => $body['testedUpToWordPressVersion'] ?? '',
			'requires_php' => $body['requiresPhp'] ?? '',
			'last_updated' => $body['lastUpdated'] ?? '',
			'sections'     => array(
				'description' => $body['sections']['description'] ?? '',
				'changelog'   => $body['sections']['changelog'] ?? '',
			),
		);
	}

	/**
	 * Cached against the currently-installed version so a cache entry from
	 * before an update (or a rollback) never suppresses a real check.
	 *
	 * @return array|null Decoded response body, or null on any failure.
	 */
	private function get_check_result( $currentVersion ) {
		$cached = get_transient( self::CHECK_TRANSIENT );
		if ( is_array( $cached ) && ( $cached['currentVersion'] ?? null ) === $currentVersion ) {
			return $cached['result'];
		}

		$response = $this->request( '/check', array(
			'slug'           => self::SLUG,
			'currentVersion' => $currentVersion,
			'siteUrl'        => home_url( '/' ),
		) );

		if ( empty( $response['http_ok'] ) || 200 !== $response['code'] ) {
			return null;
		}

		set_transient( self::CHECK_TRANSIENT, array(
			'currentVersion' => $currentVersion,
			'result'         => $response['data'],
		), self::CHECK_CACHE_SECONDS );

		return $response['data'];
	}

	private function plugin_basename() {
		return plugin_basename( CALYPSOSUB_PLUGIN_FILE );
	}

	/**
	 * @return array{http_ok:bool,code?:int,data?:array,error?:string}
	 */
	private function request( $path, array $body ) {
		$base = untrailingslashit( (string) apply_filters( 'calypsosub_update_api_base', self::DEFAULT_API_BASE ) );

		$response = wp_remote_post(
			$base . $path,
			array(
				'timeout' => 15,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'http_ok' => false,
				'error'   => $response->get_error_message(),
			);
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

		return array(
			'http_ok' => true,
			'code'    => (int) wp_remote_retrieve_response_code( $response ),
			'data'    => is_array( $decoded ) ? $decoded : array(),
		);
	}
}
