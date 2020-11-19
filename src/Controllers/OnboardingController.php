<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Onboarding controller
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding\Controllers;

use Laminas\Diactoros\Response\HtmlResponse;
use Pressmodo\Onboarding\Helper;
use Pressmodo\ThemeRequirements\TGMPAHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Filesystem;
use WP_Theme;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Onboarding controller
 */
class OnboardingController {

	/**
	 * Theme details.
	 *
	 * @var WP_Theme
	 */
	public $theme;

	/**
	 * Get things started.
	 */
	public function __construct() {
		$this->theme = wp_get_theme();
	}

	/**
	 * Get js variables for the react app.
	 *
	 * @return array
	 */
	private function getJsData() {
		return [
			'admin_url'            => esc_url( get_admin_url() ),
			'plugin_url'           => esc_url( PM_ONBOARDING_PLUGIN_URL ),
			'documentation_url'    => Helper::getDocumentationUrl(),
			'support_url'          => 'https://support.pressmodo.com',
			'theme'                => $this->theme->get( 'Name' ),
			'ajax_url'             => esc_url( trailingslashit( home_url() ) . 'onboarding/upload' ),
			'upload_package_nonce' => wp_create_nonce( 'pm_onboarding_upload_nonce' ),
			'verify_plugins_nonce' => wp_create_nonce( 'pm_onboarding_verifyplugins_nonce' ),
			'verification_url'     => esc_url( trailingslashit( home_url() ) . 'onboarding/plugins' ),
		];
	}

	/**
	 * Redirect back to the onboarding homepage when subroutes are accessed directly.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function redirect( ServerRequestInterface $request ) {

		if ( ! empty( $request->getAttribute( 'path' ) ) ) {
			$url = add_query_arg( [ 'page' => $request->getAttribute( 'path' ) ], untrailingslashit( home_url( 'onboarding' ) ) );
		} else {
			$url = untrailingslashit( home_url( 'onboarding' ) );
		}

		wp_safe_redirect( esc_url_raw( $url ) );
		exit;
	}

	/**
	 * Display the react app when viewing the onboarding page.
	 *
	 * @param ServerRequestInterface $request
	 * @return ResponseInterface
	 */
	public function view( ServerRequestInterface $request ) : ResponseInterface {

		ob_start();
		$jsData = $this->getJsData();
		include PM_ONBOARDING_PLUGIN_DIR . '/resources/views/onboarding.php';
		$output = ob_get_clean();

		return new HtmlResponse( $output );

	}

	/**
	 * Upload the demo package.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function upload( ServerRequestInterface $request ) {

		check_ajax_referer( 'pm_onboarding_upload_nonce', 'nonce' );

		$submittedData        = ! empty( $_POST ) && is_array( $_POST ) ? $_POST : [];
		$submittedDemoPackage = isset( $_FILES['file'] ) && ! empty( $_FILES['file'] ) ? $_FILES['file'] : false;

		$fileToUpload = Helper::prepareUploadedFiles( $submittedDemoPackage );

		$uploadedFile = Helper::uploadFile( $fileToUpload[0], [ 'file_key' => 'demo_package' ] );

		if ( is_wp_error( $uploadedFile ) ) {
			wp_send_json_error( [ 'error_message' => $uploadedFile->get_error_message() ], 403 );
		}

		$filesystem = new Filesystem();

		$uploadedFilePath = $uploadedFile->file;
		$extractTo        = trailingslashit( WP_CONTENT_DIR ) . 'pressmodo-demo';

		// Remove any previous folder if it exists.
		$filesystem->remove( [ $extractTo ] );

		WP_Filesystem();

		$unzipped = unzip_file( $uploadedFilePath, $extractTo );

		if ( is_wp_error( $unzipped ) ) {
			wp_send_json_error( [ 'error_message' => $unzipped->get_error_message() ], 403 );
		}

		// Delete the originally uploaded file.
		wp_delete_file( $uploadedFilePath );

		// Determine if config json file is available.
		$configFilePath = trailingslashit( $extractTo ) . 'config.json';

		if ( ! $filesystem->exists( $configFilePath ) ) {
			$filesystem->remove( [ $extractTo ] );
			wp_send_json_error( [ 'error_message' => __( 'The uploaded .zip package does not appear to be a Pressmodo Theme demo package. Please try again or contact support.' ) ], 403 );
		}

		// Verify the demo content belongs to the active theme.
		$configData = $this->getDemoConfiguration();

		if ( ! is_array( $configData ) || ( ! isset( $configData['theme'] ) ) ) {
			wp_send_json_error( [ 'error_message' => __( 'Something went wrong while checking the demo configuration file. Please contact support.' ) ], 403 );
		}

		if ( $configData['theme'] !== get_option( 'stylesheet' ) ) {
			wp_send_json_error( [ 'error_message' => __( 'The uploaded demo package does not belong to the currently active theme on this site. Please upload the appropriate demo package.' ) ], 403 );
		}

		wp_send_json_success();
	}

	/**
	 * Verify that required plugins are installed and activated.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function verifyPlugins( ServerRequestInterface $request ) {

		check_ajax_referer( 'pm_onboarding_verifyplugins_nonce', 'nonce' );

		$configData = $this->getDemoConfiguration();

		if ( ! is_array( $configData ) || ( ! isset( $configData['active_plugins'] ) ) ) {
			wp_send_json_error( [ 'error_message' => __( 'Something went wrong while checking the required plugins for the selected demo. Please contact support.' ) ], 403 );
		}

		$requiredPlugins = $configData['active_plugins'];

		$tgmpa = TGMPAHelper::getInstance();

		$nonInstalledPlugins = [];

		foreach ( $requiredPlugins as $plugin ) {
			if ( ! $tgmpa->is_plugin_installed( $plugin ) || ! $tgmpa->is_plugin_active( $plugin ) ) {
				$nonInstalledPlugins[] = $plugin;
			}
		}

		if ( ! empty( $nonInstalledPlugins ) ) {
			wp_send_json_error(
				[
					'not_found'     => $nonInstalledPlugins,
					'error_message' => __( 'Some required plugins have not been installed or activated. Press the "Install all plugins" button below to proceed with the demo installation.' ),
				],
				403
			);
		} else {
			wp_send_json_success();
		}
	}

	/**
	 * Get the demo config file data.
	 *
	 * @return array
	 */
	private function getDemoConfiguration() {

		$request    = wp_remote_get( content_url( 'pressmodo-demo/config.json' ) );
		$configData = json_decode( wp_remote_retrieve_body( $request ), true );

		return $configData;

	}

}
