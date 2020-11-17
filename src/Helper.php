<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Helper methods
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

class Helper {

	/**
	 * Convert php array to js object.
	 *
	 * @param string $object_name name of the object
	 * @param array  $l10n data to print
	 * @return array
	 */
	public static function localizeScripts( $object_name, $l10n ) {

		if ( is_array( $l10n ) && isset( $l10n['l10n_print_after'] ) ) { // back compat, preserve the code in 'l10n_print_after' if present.
			$after = $l10n['l10n_print_after'];
			unset( $l10n['l10n_print_after'] );
		}

		foreach ( (array) $l10n as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		$script = "var $object_name = " . wp_json_encode( $l10n ) . ';';

		if ( ! empty( $after ) ) {
			$script .= "\n$after;";
		}

		return $script;

	}

	/**
	 * Get theme documentation url.
	 *
	 * @return string
	 */
	public static function getDocumentationUrl() {

		/**
		 * Filter: Allow developers to modify the documentation url of the theme.
		 *
		 * @param string $url
		 * @return string
		 */
		return apply_filters( 'pressmodo_theme_documentation_url', 'https://docs.pressmodo.com' );

	}

}
