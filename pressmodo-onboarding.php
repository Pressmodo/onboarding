<?php
/**
 * Plugin Name:     Pressmodo Onboarding
 * Plugin URI:      https://pressmodo.com
 * Description:     Import Pressmodo official themes demo content, widgets and theme settings with just one click.
 * Author:          Sematico LTD
 * Author URI:      https://sematico.com
 * Text Domain:     pressmodo-onboarding
 * Domain Path:     /languages
 * Version:         1.0.2
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://pressmodo.com
 */

use Pressmodo\Onboarding\Plugin;
use Pressmodo\Requirements\Requirements;

defined( 'ABSPATH' ) || exit;

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require dirname( __FILE__ ) . '/vendor/autoload.php';
}

$requirements = new Requirements(
	'Pressmodo Onboarding',
	array(
		'php' => '7.2',
		'wp'  => '5.3',
	)
);

/**
 * Run all the checks and check if requirements has been satisfied.
 * If not - display the admin notice and exit from the file.
 */
if ( ! $requirements->satisfied() ) {
	$requirements->print_notice();
	return;
}

/**
 * Finally load the plugin.
 */
add_action(
	'plugins_loaded',
	function() {

		$onboarding = Plugin::instance( __FILE__ );

		add_action( 'plugins_loaded', array( $onboarding, 'textdomain' ), 11 );

	}
);
