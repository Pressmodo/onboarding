<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Handles install and activation of plugins.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding\Installers;

use Plugin_Upgrader;
use Pressmodo\ThemeRequirements\TGMPAHelper;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles installation of plugins.
 */
class PluginInstaller {

	/**
	 * Get things started.
	 */
	public function __construct() {
		require_once ABSPATH . '/wp-load.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	}

	/**
	 * Download and install plugin.
	 *
	 * @param string $slug
	 * @return mixed
	 */
	public function installPlugin( $slug ) {

		$tgmpa      = TGMPAHelper::getInstance();
		$sourceType = $tgmpa->plugins[ $slug ]['source_type'];

		$config = [
			'slug'   => $slug,
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			),
		];

		$api = plugins_api( 'plugin_information', $config );

		$skin     = new QuietInstallerSkin( array( 'api' => $api ) );
		$upgrader = new Plugin_Upgrader( $skin );

		if ( $sourceType === 'repo' ) {
			if ( is_wp_error( $api ) ) {
				return $api;
			}
			$install = $upgrader->install( $api->download_link );
		} else {
			$install = $upgrader->install( $tgmpa->get_download_url( $slug ) );
		}

		return $install;

	}

}
