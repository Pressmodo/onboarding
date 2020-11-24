<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Authentication middleware for the router.
 *
 * @package   pressmodo-onboarding
 * @author    Sematico LTD <hello@sematico.com>
 * @copyright 2020 Sematico LTD
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL-3.0-or-later
 * @link      https://sematico.com
 */

namespace Pressmodo\Onboarding\Middlewares;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Determine that all requests must be made by an administrator.
 */
class AuthMiddleware implements MiddlewareInterface {
	/**
	 * {@inheritdoc}
	 */
	public function process( ServerRequestInterface $request, RequestHandlerInterface $handler ) : ResponseInterface {

		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			return $handler->handle( $request );
		}

		return new RedirectResponse( admin_url() );
	}
}
