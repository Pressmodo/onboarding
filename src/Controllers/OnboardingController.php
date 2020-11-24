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

use Exception;
use Laminas\Diactoros\Response\HtmlResponse;
use Pressmodo\DB\DatabasePrefixer;
use Pressmodo\Onboarding\Helper;
use Pressmodo\Onboarding\Installers\PluginInstaller;
use Pressmodo\Onboarding\SearchReplace;
use Pressmodo\ThemeRequirements\TGMPAHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Thamaraiselvam\MysqlImport\Import;
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
			'admin_url'                   => esc_url( get_admin_url() ),
			'plugin_url'                  => esc_url( PM_ONBOARDING_PLUGIN_URL ),
			'documentation_url'           => Helper::getDocumentationUrl(),
			'support_url'                 => 'https://support.pressmodo.com',
			'theme'                       => $this->theme->get( 'Name' ),
			'ajax_url'                    => esc_url( trailingslashit( home_url() ) . 'onboarding/upload' ),
			'upload_package_nonce'        => wp_create_nonce( 'pm_onboarding_upload_nonce' ),
			'verify_plugins_nonce'        => wp_create_nonce( 'pm_onboarding_verifyplugins_nonce' ),
			'verification_url'            => esc_url( trailingslashit( home_url() ) . 'onboarding/plugins' ),
			'check_required_plugin_nonce' => wp_create_nonce( 'pm_onboarding_check_required_plugin_nonce' ),
			'check_plugin_install_url'    => esc_url( trailingslashit( home_url() ) . 'onboarding/plugin' ),
			'install_plugin_nonce'        => wp_create_nonce( 'pm_onboarding_install_plugin_nonce' ),
			'install_plugin_url'          => esc_url( trailingslashit( home_url() ) . 'onboarding/plugin/install' ),
			'move_media_url'              => esc_url( trailingslashit( home_url() ) . 'onboarding/media' ),
			'move_media_nonce'            => wp_create_nonce( 'pm_onboarding_move_media_nonce' ),
			'install_db_url'              => esc_url( trailingslashit( home_url() ) . 'onboarding/database' ),
			'install_db_nonce'            => wp_create_nonce( 'pm_onboarding_install_db_nonce' ),
			'search_replace_url'          => esc_url( trailingslashit( home_url() ) . 'onboarding/replace' ),
			'search_replace_nonce'        => wp_create_nonce( 'pm_onboarding_search_replace_nonce' ),
			'update_account_nonce'        => wp_create_nonce( 'pm_onboarding_update_account_nonce' ),
			'update_account_url'          => esc_url( trailingslashit( home_url() ) . 'onboarding/database/account' ),
			'replace_db_nonce'            => wp_create_nonce( 'pm_onboarding_replace_db_nonce' ),
			'replace_db_url'              => esc_url( trailingslashit( home_url() ) . 'onboarding/database/replace' ),
			'demo_installed'              => (bool) get_option( 'pressmodo_demo_installed', false ),
			'login_url'                   => wp_login_url( home_url() ),
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

		if ( ! is_array( $configData ) || ( ! isset( $configData['plugins'] ) ) ) {
			wp_send_json_error( [ 'error_message' => __( 'Something went wrong while checking the required plugins for the selected demo. Please contact support.' ) ], 403 );
		}

		$nonInstalledPlugins = $this->getMissingPluginsConfiguration( $configData );

		if ( ! empty( $nonInstalledPlugins ) ) {
			wp_send_json_error(
				[
					'not_found'     => $nonInstalledPlugins,
					'error_message' => __( 'Some required plugins have not been installed or activated. Press the button below to install all the required plugins.' ),
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

	/**
	 * Get list of plugins that are either not active or installed.
	 *
	 * @param array $configData demo configuration
	 * @return array
	 */
	private function getMissingPluginsConfiguration( $configData ) {

		$missingPlugins = [];

		$pluginsRequired = $configData['plugins'];

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $pluginsRequired as $plugin => $pluginData ) {
			if ( ! \is_plugin_active( $plugin ) ) {
				$pluginInfo       = $configData['plugins'][ $plugin ];
				$missingPlugins[] = array_merge( $pluginInfo, [ 'slug' => $plugin ] );
			}
		}

		return $missingPlugins;

	}

	/**
	 * Get the next plugin on the required list.
	 *
	 * @return void
	 */
	public function getNextRequiredPlugin() {

		check_ajax_referer( 'pm_onboarding_check_required_plugin_nonce', 'nonce' );

		$configData = $this->getDemoConfiguration();

		if ( ! is_array( $configData ) || ( ! isset( $configData['plugins'] ) ) ) {
			wp_send_json_error( [ 'error_message' => __( 'Something went wrong while checking for the next required plugin.' ) ], 403 );
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$pluginsRequired = $configData['plugins'];

		foreach ( $pluginsRequired as $plugin ) {
			if ( ! \is_plugin_active( $plugin ) ) {
				wp_send_json_error( [ 'slug' => $plugin ], 403 );
			}
		}

		wp_send_json_success();

	}

	/**
	 * Programmatically install a plugin via ajax.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function installPlugin( ServerRequestInterface $request ) {

		check_ajax_referer( 'pm_onboarding_install_plugin_nonce', 'nonce' );

		$plugin     = isset( $_POST['plugin'] ) && ! empty( $_POST['plugin'] ) ? sanitize_text_field( $_POST['plugin'] ) : false;
		$pluginSlug = strtok( $plugin, '/' );

		if ( ! isset( TGMPAHelper::getInstance()->plugins[ $pluginSlug ] ) ) {
			wp_send_json_error( [ 'error_message' => esc_html__( 'The requested plugin does not seem to be required by the theme.' ) ], 403 );
		}

		$install = ( new PluginInstaller() )->installPlugin( $pluginSlug );

		if ( is_wp_error( $install ) ) {
			wp_send_json_error( [ 'error_message' => $install->get_error_message() ], 403 );
		}

		$activation = activate_plugin( $plugin );

		if ( is_wp_error( $activation ) ) {
			wp_send_json_error( [ 'error_message' => $activation->get_error_message() ], 403 );
		}

		wp_send_json_success( [ 'activated' => $plugin ] );

	}

	/**
	 * Move media folder from demo package.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function installMediaFiles( ServerRequestInterface $request ) {

		check_ajax_referer( 'pm_onboarding_move_media_nonce', 'nonce' );

		$demoMediaFiles = trailingslashit( WP_CONTENT_DIR ) . 'pressmodo-demo/uploads_demo';

		$filesystem = new Filesystem();

		if ( ! $filesystem->exists( $demoMediaFiles ) ) {
			wp_send_json_error( [ 'error_message' => esc_html__( 'Looks like the demo media folder is missing. Please try uploading the package again.' ) ], 403 );
		}

		// Delete site's uploads folder.
		$uploadDir = wp_upload_dir()['path'];

		try {
			$filesystem->remove( $uploadDir );
		} catch ( IOExceptionInterface $exception ) {
			wp_send_json_error( [ 'error_message' => $exception->getMessage() ], 403 );
		}

		// Move demo folder.
		try {
			$filesystem->mirror( $demoMediaFiles, $uploadDir );
		} catch ( \Throwable $th ) {
			wp_send_json_error( [ 'error_message' => $exception->getMessage() ], 403 );
		}

		wp_send_json_success();

	}

	/**
	 * Import the database sql file.
	 *
	 * @return void
	 */
	public function installDatabase() {

		check_ajax_referer( 'pm_onboarding_install_db_nonce', 'nonce' );

		$demoDb = trailingslashit( WP_CONTENT_DIR ) . 'demo.sql';

		$filesystem = new Filesystem();

		if ( ! $filesystem->exists( $demoDb ) ) {
			wp_send_json_error( [ 'error_message' => esc_html__( 'Looks like the demo database file is missing. Please try uploading the package again.' ) ], 403 );
		}

		try {
			$import = new Import( $demoDb, DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'error_message' => $e->getMessage() ], 403 );
		}

		wp_send_json_success();

	}

	/**
	 * Process database urls search and replace.
	 *
	 * @param ServerRequestInterface $request
	 * @return void
	 */
	public function processSearchReplace( ServerRequestInterface $request ) {

		check_ajax_referer( 'pm_onboarding_search_replace_nonce', 'nonce' );

		$db = new SearchReplace();

		$step = isset( $_POST['bsr_step'] ) ? absint( $_POST['bsr_step'] ) : 0;
		$page = isset( $_POST['bsr_page'] ) ? absint( $_POST['bsr_page'] ) : 0;

		$configData = $this->getDemoConfiguration();

		if ( $step === 0 && $page === 0 ) {
			$args = array();
			parse_str( $_POST['bsr_data'], $args );

			// Build the arguements for this run.
			$args = array(
				'select_tables'    => SearchReplace::getTables(),
				'case_insensitive' => 'off',
				'replace_guids'    => 'off',
				'search_for'       => esc_url( $configData['domain'] ),
				'replace_with'     => esc_url( home_url() ),
				'completed_pages'  => isset( $args['completed_pages'] ) ? absint( $args['completed_pages'] ) : 0,
			);

			$args['total_pages'] = isset( $args['total_pages'] ) ? absint( $args['total_pages'] ) : $db->getTotalPages( $args['select_tables'] );

			// Clear the results of the last run.
			delete_transient( 'bsr_results' );
			delete_option( 'bsr_data' );
		} else {
			$args = get_option( 'bsr_data' );
		}

		if ( isset( $args['select_tables'][ $step ] ) ) {

			$result = $db->srdb( $args['select_tables'][ $step ], $page, $args );

			if ( false === $result['table_complete'] ) {
				$page++;
			} else {
				$step++;
				$page = 0;
			}

			// Check if isset() again as the step may have changed since last check.
			if ( isset( $args['select_tables'][ $step ] ) ) {
				$message = sprintf(
					__( 'Processing table %1$d of %2$d: %3$s', 'better-search-replace' ),
					$step + 1,
					count( $args['select_tables'] ),
					esc_html( $args['select_tables'][ $step ] )
				);
			}

			$args['completed_pages']++;
			$percentage = $args['completed_pages'] / $args['total_pages'] * 100;

		} else {
			$db->maybeUpdateSiteUrl();
			$step       = 'done';
			$percentage = '100';
		}

		update_option( 'bsr_data', $args );

		// Store results in an array.
		$result = array(
			'step'       => $step,
			'page'       => $page,
			'percentage' => $percentage,
			'bsr_data'   => build_query( $args ),
		);

		if ( isset( $message ) ) {
			$result['message'] = $message;
		}

		wp_send_json_success( $result );

	}

	/**
	 * Merge the current user account into the demo account.
	 *
	 * @return void
	 */
	public function restoreUserAccount() {

		check_ajax_referer( 'pm_onboarding_update_account_nonce', 'nonce' );

		global $wpdb;

		$account = get_user_by( 'ID', get_current_user_id() );

		$username = $account->data->user_login;
		$password = $account->data->user_pass;
		$nicename = $account->data->user_nicename;
		$email    = $account->data->user_email;
		$url      = esc_url( $account->data->user_url );

		$query = $wpdb->prepare(
			"UPDATE demo_users SET user_pass = %s, user_activation_key = '', user_login = %s, user_nicename = %s, user_email = %s, user_url = %s WHERE ID = %d",
			$password,
			$username,
			$nicename,
			$email,
			$url,
			1
		);

		$wpdb->query( $query ); //phpcs:ignore

		wp_send_json_success();

	}

	/**
	 * Replace the original site db with the demo db.
	 *
	 * @return void
	 */
	public function replaceDatabaseWithDemo() {

		check_ajax_referer( 'pm_onboarding_replace_db_nonce', 'nonce' );

		global $wpdb;

		$prefix = str_replace( '_', '\_', $wpdb->prefix );

		$tables = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" );

		foreach ( $tables as $table ) {
    		$wpdb->query( "DROP TABLE $table" ); //phpcs:ignore
		}

		try {
			$replace = ( new DatabasePrefixer( $wpdb->prefix, 'demo_' ) )->init();
		} catch ( Exception $e ) {
			wp_send_json_error( [ 'error_message' => $e->getMessage() ], 403 );
		}

		update_option( 'pressmodo_demo_installed', true );

		wp_clear_auth_cookie();

		wp_send_json_success();

	}

}
