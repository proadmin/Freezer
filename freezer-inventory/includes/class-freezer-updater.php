<?php
/**
 * GitHub-based plugin updater for Freezer Inventory.
 *
 * Checks the GitHub releases API for new versions and integrates
 * with the WordPress Plugins page update mechanism.
 */

defined( 'ABSPATH' ) || exit;

class Freezer_Updater {

	const GITHUB_REPO = 'proadmin/Freezer';
	const TRANSIENT_KEY = 'freezer_inventory_update_check';
	const CACHE_TTL = 12 * HOUR_IN_SECONDS;

	private static $plugin_file;

	public static function init( $plugin_file ) {
		self::$plugin_file = $plugin_file;
		add_filter( 'site_transient_update_plugins', array( __CLASS__, 'check_for_update' ) );
		add_filter( 'plugins_api', array( __CLASS__, 'plugin_info' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( __CLASS__, 'post_install' ), 10, 3 );
		add_action( 'load-plugins.php', array( __CLASS__, 'flush_cache_on_plugins_page' ) );
	}

	public static function flush_cache_on_plugins_page() {
		delete_transient( self::TRANSIENT_KEY );
	}

	private static function get_plugin_basename() {
		return plugin_basename( self::$plugin_file );
	}

	private static function fetch_release() {
		$cached = get_transient( self::TRANSIENT_KEY );
		if ( $cached !== false ) {
			return $cached;
		}

		$url = 'https://api.github.com/repos/' . self::GITHUB_REPO . '/releases/latest';
		$response = wp_remote_get( $url, array(
			'headers' => array( 'Accept' => 'application/vnd.github.v3+json' ),
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			set_transient( self::TRANSIENT_KEY, array(), self::CACHE_TTL );
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['tag_name'] ) ) {
			set_transient( self::TRANSIENT_KEY, array(), self::CACHE_TTL );
			return array();
		}

		// Find the zip asset.
		$zip_url = '';
		if ( ! empty( $body['assets'] ) ) {
			foreach ( $body['assets'] as $asset ) {
				if ( substr( $asset['name'], -4 ) === '.zip' ) {
					$zip_url = $asset['browser_download_url'];
					break;
				}
			}
		}
		// Fallback to GitHub's auto-generated zipball.
		if ( ! $zip_url ) {
			$zip_url = $body['zipball_url'];
		}

		$release = array(
			'version'     => ltrim( $body['tag_name'], 'vV' ),
			'zip_url'     => $zip_url,
			'html_url'    => $body['html_url'],
			'description' => isset( $body['body'] ) ? $body['body'] : '',
			'published'   => isset( $body['published_at'] ) ? $body['published_at'] : '',
		);

		set_transient( self::TRANSIENT_KEY, $release, self::CACHE_TTL );
		return $release;
	}

	public static function check_for_update( $transient ) {
		if ( ! is_object( $transient ) ) {
			$transient = new stdClass();
		}

		$release = self::fetch_release();
		if ( empty( $release['version'] ) ) {
			return $transient;
		}

		$current_version = FREEZER_INVENTORY_VERSION;
		if ( version_compare( $release['version'], $current_version, '>' ) ) {
			$plugin_basename = self::get_plugin_basename();
			// Remove from no_update so WordPress doesn't suppress the notice.
			if ( isset( $transient->no_update[ $plugin_basename ] ) ) {
				unset( $transient->no_update[ $plugin_basename ] );
			}
			$transient->response[ $plugin_basename ] = (object) array(
				'slug'        => 'freezer-inventory',
				'plugin'      => $plugin_basename,
				'new_version' => $release['version'],
				'url'         => $release['html_url'],
				'package'     => $release['zip_url'],
			);
		}

		return $transient;
	}

	public static function plugin_info( $result, $action, $args ) {
		if ( $action !== 'plugin_information' || ! isset( $args->slug ) || $args->slug !== 'freezer-inventory' ) {
			return $result;
		}

		$release = self::fetch_release();
		if ( empty( $release['version'] ) ) {
			return $result;
		}

		return (object) array(
			'name'          => 'Freezer Inventory Manager',
			'slug'          => 'freezer-inventory',
			'version'       => $release['version'],
			'author'        => 'Freezer Inventory',
			'homepage'      => 'https://github.com/' . self::GITHUB_REPO,
			'download_link' => $release['zip_url'],
			'sections'      => array(
				'description' => 'Manage your freezer inventory with categories, locations, and PDF/CSV export.',
				'changelog'   => nl2br( esc_html( $release['description'] ) ),
			),
		);
	}

	/**
	 * After install, ensure the directory name matches what WordPress expects.
	 */
	public static function post_install( $response, $hook_extra, $result ) {
		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== self::get_plugin_basename() ) {
			return $result;
		}

		global $wp_filesystem;
		$install_dir = $result['destination'];
		$proper_dir  = WP_PLUGIN_DIR . '/freezer-inventory';

		if ( $install_dir !== $proper_dir ) {
			$wp_filesystem->move( $install_dir, $proper_dir );
			$result['destination'] = $proper_dir;
		}

		activate_plugin( self::get_plugin_basename() );
		return $result;
	}
}
