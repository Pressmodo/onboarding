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

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add a get started menu item to the appearance menu. Modify the url to go to a virtual "onboarding" page.
 */
add_action(
	'admin_menu',
	function() {

		global $submenu;

		add_theme_page( __( 'Pressmodo Onboarding' ), __( 'Get started' ), 'edit_theme_options', 'pressmodo-onboarding', 'theme_option_page' );

		foreach ( $submenu as $key => $menu ) {
			if ( is_array( $menu ) ) {
				foreach( $menu as $subMenuKey => $subMenuItem ) {
					if ( $subMenuItem[2] === 'pressmodo-onboarding' ) {
						$submenu[ $key ][ $subMenuKey ][ 2 ] = trailingslashit( get_site_url( null, 'onboarding' ) ); //phpcs:ignore
					}
				}
			}
		}

	}
);
