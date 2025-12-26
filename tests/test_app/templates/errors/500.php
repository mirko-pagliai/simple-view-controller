<?php
declare(strict_types=1);

/**
 * Example 500 Internal Server Error.
 *
 * @var int $statusCode
 * @var \Throwable|null $exception
 */

echo 'fatal ' . $statusCode;
if (isset($exception) && $exception instanceof \Throwable) {
    echo ' - ' . $exception->getMessage();
}
