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
$strategy        = new JsonStrategy( $responseFactory );

$router->map( 'GET', '/onboarding', '\Pressmodo\Onboarding\Controllers\OnboardingController::view' );
$router->map( 'GET', '/onboarding/{path:.*}', '\Pressmodo\Onboarding\Controllers\OnboardingController::redirect' );

$router->map( 'POST', '/onboarding/upload', '\Pressmodo\Onboarding\Controllers\OnboardingController::upload' )
	->setStrategy( $strategy );

$router->map( 'POST', '/onboarding/plugins', '\Pressmodo\Onboarding\Controllers\OnboardingController::verifyPlugins' )
	->setStrategy( $strategy );

$router->map( 'POST', '/onboarding/plugin', '\Pressmodo\Onboarding\Controllers\OnboardingController::getNextRequiredPlugin' )
	->setStrategy( $strategy );

$router->map( 'POST', '/onboarding/plugin/install', '\Pressmodo\Onboarding\Controllers\OnboardingController::installPlugin' )
	->setStrategy( $strategy );

$router->map( 'POST', '/onboarding/media', '\Pressmodo\Onboarding\Controllers\OnboardingController::installMediaFiles' )
	->setStrategy( $strategy );

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
