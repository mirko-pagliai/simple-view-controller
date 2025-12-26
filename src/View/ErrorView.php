<?php
declare(strict_types=1);

namespace SimpleVC\View;

use Throwable;

/**
 * Specialized View class for rendering error pages.
 *
 * This class extends the base View class and is configured specifically for error rendering:
 *
 * - Uses a dedicated error layout (`layouts/error.php`)
 * - Selects appropriate error templates (`400.php` or `500.php`) based on status code
 * - Conditionally includes exception details based on `DEBUG` mode
 */
class ErrorView extends View
{
    /**
     * Initializes the `ErrorView` with error-specific configuration.
     *
     * Sets up the view with the error layout (`layouts/error.php`).
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(layout: 'layouts/error.php');
    }

    /**
     * Determines which error template to use based on the HTTP status code.
     *
     * @param int $statusCode The HTTP status code
     * @return string The path to the error template (relative to templates directory)
     */
    protected function determineTemplate(int $statusCode): string
    {
        return $statusCode < 500 ? 'errors/400.php' : 'errors/500.php';
    }

    /**
     * Renders an error page with the appropriate template and data.
     *
     * Determines which template to use based on the status code:
     *
     * - 4xx errors use `errors/400.php`
     * - 5xx errors use `errors/500.php`
     *
     * If `DEBUG` mode is enabled and an exception is provided, the exception object is passed to the template for
     *  displaying detailed error information.
     *
     * @param int $statusCode The HTTP status code (e.g., 404, 500)
     * @param \Throwable|null $exception The exception that caused the error, if any
     * @return string The rendered HTML content
     */
    public function renderError(int $statusCode, ?Throwable $exception = null): string
    {
        $template = $this->determineTemplate($statusCode);

        $data = ['statusCode' => $statusCode];

        // @phpstan-ignore booleanAnd.leftAlwaysTrue
        if (DEBUG && $exception !== null) {
            $data['exception'] = $exception;
        }

        $content = $this->renderFile($template, $data);

        if ($this->layout !== null) {
            $data['content'] = $content;

            return $this->renderFile($this->layout, $data);
        }

        return $content;
    }
}
