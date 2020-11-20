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

class QuietInstallerSkin extends \WP_Upgrader_Skin {

	public function header() {

	}

	public function footer() {

	}

	public function feedback( $string, ...$args ) {}
}
