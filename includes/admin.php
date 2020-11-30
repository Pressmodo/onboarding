<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Setup admin related functionalities.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

use Pressmodo\Onboarding\Controllers\OnboardingController;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add a get started menu item to the appearance menu. Modify the url to go to a virtual "onboarding" page.
 */
add_action(
	'admin_menu',
	function() {
		add_theme_page( __( 'Pressmodo Onboarding', 'pressmodo-onboarding' ), __( 'Get started', 'pressmodo-onboarding' ), 'edit_theme_options', 'pressmodo-onboarding', 'pm_admin_page' );
	}
);

/**
 * Displays the onboarding page.
 *
 * @return void
 */
function pm_admin_page() {
	echo '<div id="root"></div>'; //phpcs:ignore
}

add_action(
	'admin_enqueue_scripts',
	function() {

		$screen = get_current_screen();

		if ( $screen->base !== 'appearance_page_pressmodo-onboarding' ) {
			return;
		}

		wp_register_script( 'pm-onboarding', PM_ONBOARDING_PLUGIN_URL . 'dist/js/react/index.js', [], PM_ONBOARDING_VERSION, true );

		wp_enqueue_script( 'pm-onboarding' );

		wp_localize_script( 'pm-onboarding', 'pmOnboarding', OnboardingController::getJsData() );

	}
);
