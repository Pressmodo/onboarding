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

use Laminas\Diactoros\ResponseFactory;
use League\Route\Strategy\JsonStrategy;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
	$_SERVER,
	$_GET,
	$_POST,
	$_COOKIE,
	$_FILES
);

$router = new League\Route\Router();

$responseFactory = new ResponseFactory();

$router->map( 'GET', '/onboarding', '\Pressmodo\Onboarding\Controllers\OnboardingController::view' );
$router->map( 'POST', '/onboarding/upload', '\Pressmodo\Onboarding\Controllers\OnboardingController::upload' )
	->setStrategy( new JsonStrategy( $responseFactory ) );

/**
 * After WP has successfully initialized, we dispatch routes requests only when they match.
 */
add_action(
	'init',
	function() use ( $router, $request ) {
		try {
			$response = $router->dispatch( $request );
			( new Laminas\HttpHandlerRunner\Emitter\SapiEmitter() )->emit( $response );
			exit;
		} catch ( Exception $e ) {
			return;
		}
	}
);
