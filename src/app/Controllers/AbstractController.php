<?php declare(strict_types=1);

/**
 * AbstractController
 *
 * App-owned root base class for all controllers.
 * Extends Spin\Core\Controller
 */

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Spin\Core\Controller;

abstract class AbstractController extends Controller
{
    /**
     * handleGET
     *
     * Handle GET requests.
     *
     * @param array $args Route parameters as key:value pairs
     * @return ResponseInterface
     */
    public function handleGET(array $args): ResponseInterface
    {
        return \getResponse()->withStatus(405);
    }

    /**
     * handlePOST
     *
     * Handle POST requests.
     *
     * @param array $args Route parameters as key:value pairs
     * @return ResponseInterface
     */
    public function handlePOST(array $args): ResponseInterface
    {
        return \getResponse()->withStatus(405);
    }

    /**
     * handlePUT
     *
     * Handle PUT requests.
     *
     * @param array $args Route parameters as key:value pairs
     * @return ResponseInterface
     */
    public function handlePUT(array $args): ResponseInterface
    {
        return \getResponse()->withStatus(405);
    }

    /**
     * handlePATCH
     *
     * Handle PATCH requests.
     *
     * @param array $args Route parameters as key:value pairs
     * @return ResponseInterface
     */
    public function handlePATCH(array $args): ResponseInterface
    {
        return \getResponse()->withStatus(405);
    }

    /**
     * handleDELETE
     *
     * Handle DELETE requests.
     *
     * @param array $args Route parameters as key:value pairs
     * @return ResponseInterface
     */
    public function handleDELETE(array $args): ResponseInterface
    {
        return \getResponse()->withStatus(405);
    }
}
