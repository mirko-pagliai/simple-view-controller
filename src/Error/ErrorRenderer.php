<?php
declare(strict_types=1);

namespace SimpleVC\Error;

use SimpleVC\View\ErrorView;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Handles the rendering of error pages for HTTP exceptions and errors.
 *
 * This class is responsible for:
 *
 * - Logging errors to the console (`error_log()`)
 * - Rendering appropriate error templates (`400.php` or `500.php`)
 * - Creating HTTP Response objects with correct status codes
 */
class ErrorRenderer
{
    /**
     * Parses an exception into a formatted error message string.
     *
     * This method generates a string representation of an exception by including the HTTP status code, exception
     *  message, file, and line where the exception occurred.
     *
     * @param int $statusCode The HTTP status code associated with the error.
     * @param \Throwable $exception The exception instance to parse.
     * @return string A formatted string containing the error details.
     *
     * @internal
     */
    protected function parseMessage(int $statusCode, Throwable $exception): string
    {
        return sprintf(
            '[%d] %s in %s on line %d',
            $statusCode,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        );
    }

    protected function writeToConsole(int $statusCode, ?Throwable $exception): void
    {
        // @phpstan-ignore booleanNot.alwaysFalse
        if (!DEBUG) {
            return;
        }
        if ($exception === null) {
            return;
        }

        $message = $this->parseMessage($statusCode, $exception);

        // Write directly to STDERR using fwrite (avoids output buffering issues)
        $stderr = fopen('php://stderr', 'w');
        if ($stderr !== false) {
            fwrite($stderr, $message . "\n");
            fwrite($stderr, $exception->getTraceAsString() . "\n\n");
            fclose($stderr);
        }
    }

    protected function writeToLog(int $statusCode, ?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        error_log($this->parseMessage($statusCode, $exception));
        error_log($exception->getTraceAsString());
    }

    /**
     * Renders an error page and returns an HTTP Response.
     *
     * This method:
     *
     * 1. Logs the error details to the console (if an exception is provided)
     * 2. Determines the appropriate template based on status code
     * 3. Renders the error page using ErrorView
     * 4. Returns a Response object with the rendered content
     *
     * @param int $statusCode The HTTP status code (e.g., 404, 500)
     * @param \Throwable|null $exception The exception that caused the error, if any
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response containing the rendered error page
     */
    public function render(int $statusCode, ?Throwable $exception = null): Response
    {
        $this->writeToLog($statusCode, $exception);
        $this->writeToConsole($statusCode, $exception);

        $view = new ErrorView();
        $content = $view->renderError($statusCode, $exception);

        return new Response($content, $statusCode);
    }
}
