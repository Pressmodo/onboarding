<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Setup ajax hooks
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
 * Upload the demo package through the onboarding window.
 *
 * @return void
 */
function ajaxDemoPackageUpload() {

	check_ajax_referer( 'pm_onboarding_upload_nonce' );

	wp_send_json_success();

}

add_action( 'wp_ajax_pm_onboarding_upload', __NAMESPACE__ . '\\ajaxDemoPackageUpload' );
