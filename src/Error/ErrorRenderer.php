<?php
declare(strict_types=1);

/**
 * simple-view-controller: a lightweight PHP framework focused exclusively on the Controller and View layers
 * Copyright (c) Mirko Pagliai (https://github.com/mirko-pagliai)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Mirko Pagliai (https://github.com/mirko-pagliai)
 * @link          https://github.com/mirko-pagliai/simple-view-controller CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace SimpleVC\Error;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use SimpleVC\View\ErrorView;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Handles the rendering of error pages for HTTP exceptions and errors.
 *
 * This class is responsible for:
 *
 * - Rendering appropriate error templates (`400.php` or `500.php`)
 * - Creating HTTP Response objects with correct status codes
 * - Optionally logging errors via an injected PSR-3 compatible logger
 */
class ErrorRenderer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Constructor.
     *
     * @param \Psr\Log\LoggerInterface|null $logger Optional PSR-3 logger for error logging
     * @return void
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger !== null) {
            $this->setLogger($logger);
        }
    }

    /**
     * Renders an error page and returns an HTTP Response.
     *
     * This method:
     * 1. Logs the error if a logger is configured and an exception is provided
     * 2. Renders the error page using ErrorView
     * 3. Returns a `Response` object with the rendered content
     *
     * @param int $statusCode The HTTP status code (e.g., 404, 500)
     * @param \Throwable|null $exception The exception that caused the error, if any
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response containing the rendered error page
     */
    public function render(int $statusCode, ?Throwable $exception = null): Response
    {
        // Log error if logger is available and exception exists
        if (isset($this->logger) && $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
                'status_code' => $statusCode,
            ]);
        }

        // Render error page
        $view = new ErrorView();
        $content = $view->renderError($statusCode, $exception);

        return new Response($content, $statusCode);
    }
}
