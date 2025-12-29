<?php
declare(strict_types=1);

namespace SimpleVC\Error;

use Psr\Log\AbstractLogger;
use RuntimeException;
use Stringable;
use Throwable;
use function SimpleVC\env;

/**
 * PSR-3 compliant console logger.
 *
 * Note: log levels are ignored.
 * Output is written to stderr only when the debug is enabled.
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Constructor method for initializing the stream.
     *
     * @param resource|null $stream An optional stream resource. If not provided, a default stream to 'php://stderr' is used.
     * @return void
     * @throws \RuntimeException If the provided stream is not a valid resource.
     */
    public function __construct($stream = null)
    {
        $stream = $stream ?: fopen('php://stderr', 'w');
        if (!is_resource($stream)) {
            throw new RuntimeException('Invalid stream resource');
        }

        $this->stream = $stream;
    }

    /**
     * Logs a message with a given level and context.
     *
     * @param mixed $level The log level.
     * @param \Stringable|string $message The message to log.
     * @param array<string, mixed> $context Array with additional context information. Can include an `exception` key
     *  with a `Throwable` value.
     * @return void
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        if (env('DEBUG', false) !== true) {
            return;
        }

        fwrite($this->stream, (string)$message . "\n");

        $exception = $context['exception'] ?? null;
        if ($exception instanceof Throwable) {
            fwrite($this->stream, (string)$exception . "\n");
        }
    }
}
