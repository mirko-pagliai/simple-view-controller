<?php
declare(strict_types=1);

/**
 * Example 400 Bad Request.
 *
 * @var int $statusCode
 * @var \Throwable|null $exception
 */

echo 'error ' . $statusCode;
if (isset($exception) && $exception instanceof \Throwable) {
    echo ' - ' . $exception->getMessage();
}
