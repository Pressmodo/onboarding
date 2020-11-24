<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Setup the router for the plugin.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

use Pressmodo\Onboarding\Helper;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title><?php echo esc_html__( 'Pressmodo Themes: Getting started' ); ?></title>
</head>

<body>
	<noscript>You need to enable JavaScript to run this app.</noscript>
	<div id="root"></div>

	<script>
		<?php echo Helper::localizeScripts( 'pmOnboarding', $jsData ); //phpcs:ignore ?>
	</script>

	<script src='<?php echo esc_url( PM_ONBOARDING_PLUGIN_URL . 'dist/js/react/index.js' ); //phpcs:ignore ?>'></script>

</body>

</html>
