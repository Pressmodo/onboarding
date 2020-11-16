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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

$router->map(
	'GET',
	'/onboarding',
	function ( ServerRequestInterface $request ) : ResponseInterface { //phpcs:ignore
		$response = new Laminas\Diactoros\Response();
		$response->getBody()->write( '<h1>Hello, World!</h1>' );
		return $response;
	}
);

/**
 * After WP has successfully initialized, we dispatch routes requests only when they match.
 */
add_action(
	'init',
	function() use ( $router, $request ) {
		try {
			$response = $router->dispatch( $request );
			( new Laminas\HttpHandlerRunner\Emitter\SapiEmitter() )->emit( $response );
		} catch ( Exception $e ) {
			return;
		}
	}
);
