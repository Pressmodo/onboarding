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
	<meta
	  name="description"
	  content="Web site created using create-react-app"
	/>
	<link rel="apple-touch-icon" href="%PUBLIC_URL%/logo192.png" />
	<title>React App</title>
  </head>
  <body>
	<noscript>You need to enable JavaScript to run this app.</noscript>
	<div id="root"></div>
	<!--
	  This HTML file is a template.
	  If you open it directly in the browser, you will see an empty page.

	  You can add webfonts, meta tags, or analytics to this file.
	  The build step will place the bundled scripts into the <body> tag.

	  To begin the development, run `npm start` or `yarn start`.
	  To create a production bundle, use `npm run build` or `yarn build`.
	-->

	<script>
	<?php echo Helper::localizeScripts( 'pmOnboarding', $jsData ); ?>
	</script>

	<script src='<?php echo esc_url( PM_ONBOARDING_PLUGIN_URL . 'dist/js/react/index.js' ); ?>'></script>

  </body>
</html>
