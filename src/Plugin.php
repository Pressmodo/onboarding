<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The class that loads the whole plugin after requirements have been met.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin class
 */
class Plugin {

	/**
	 * Instance of the plugin.
	 *
	 * @var Plugin
	 */
	private static $instance;

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Setup the instance.
	 *
	 * @param string $file the plugin's file.
	 * @return Plugin
	 */
	public static function instance( $file = '' ) {

		// Return if already instantiated.
		if ( self::isInstantiated() ) {
			return self::$instance;
		}

		// Setup the singleton.
		self::setupInstance( $file );

		self::$instance->setupConstants();
		self::$instance->includeFiles();

		// Return the instance.
		return self::$instance;

	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pressmodo-onboarding' ), '0.1.0' );
	}
	/**
	 * Disable un-serializing of the class.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'pressmodo-onboarding' ), '0.1.0' );
	}

	/**
	 * Return whether the main loading class has been instantiated or not.
	 *
	 * @since 0.1.0
	 *
	 * @return boolean True if instantiated. False if not.
	 */
	private static function isInstantiated() {
		// Return true if instance is correct class.
		if ( ! empty( self::$instance ) && ( self::$instance instanceof Plugin ) ) {
			return true;
		}
		// Return false if not instantiated correctly.
		return false;
	}

	/**
	 * Helper method to setup the instance.
	 *
	 * @param string $file the file of the plugin.
	 * @return void
	 */
	private static function setupInstance( $file = '' ) {
		self::$instance       = new Plugin();
		self::$instance->file = $file;
	}

	/**
	 * Setup helper constants.
	 *
	 * @return void
	 */
	private function setupConstants() {

		// Plugin version.
		if ( ! defined( 'PM_ONBOARDING_VERSION' ) ) {
			define( 'PM_ONBOARDING_VERSION', '1.0.3' );
		}
		// Plugin Root File.
		if ( ! defined( 'PM_ONBOARDING_PLUGIN_FILE' ) ) {
			define( 'PM_ONBOARDING_PLUGIN_FILE', $this->file );
		}
		// Plugin Base Name.
		if ( ! defined( 'PM_ONBOARDING_PLUGIN_BASE' ) ) {
			define( 'PM_ONBOARDING_PLUGIN_BASE', plugin_basename( PM_ONBOARDING_PLUGIN_FILE ) );
		}
		// Plugin Folder Path.
		if ( ! defined( 'PM_ONBOARDING_PLUGIN_DIR' ) ) {
			define( 'PM_ONBOARDING_PLUGIN_DIR', plugin_dir_path( PM_ONBOARDING_PLUGIN_FILE ) );
		}
		// Plugin Folder URL.
		if ( ! defined( 'PM_ONBOARDING_PLUGIN_URL' ) ) {
			define( 'PM_ONBOARDING_PLUGIN_URL', plugin_dir_url( PM_ONBOARDING_PLUGIN_FILE ) );
		}

	}

	/**
	 * Allow translations.
	 *
	 * @return void
	 */
	public function textdomain() {
		load_plugin_textdomain( 'pressmodo-onboarding', false, PM_ONBOARDING_PLUGIN_DIR . '/languages' );
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function includeFiles() {
		require_once PM_ONBOARDING_PLUGIN_DIR . 'includes/router.php';
		require_once PM_ONBOARDING_PLUGIN_DIR . 'includes/admin.php';
	}

}
