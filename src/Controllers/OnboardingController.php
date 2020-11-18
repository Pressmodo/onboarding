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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
			'ajax_url'             => admin_url( 'admin-ajax.php' ),
			'upload_package_nonce' => wp_create_nonce( 'pm_onboarding_upload_nonce' ),
		];
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

}
