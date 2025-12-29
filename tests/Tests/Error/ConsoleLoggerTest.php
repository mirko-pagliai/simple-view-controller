<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use SimpleVC\Error\ConsoleLogger;

#[CoversClass(ConsoleLogger::class)]
class ConsoleLoggerTest extends TestCase
{
    /**
     * @var resource
     */
    protected $stream;

    protected ConsoleLogger $logger;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $stream = fopen('php://memory', 'w+');
        if (!is_resource($stream)) {
            throw new RuntimeException('Invalid stream resource');
        }
        $this->stream = $stream;

        $this->logger = new ConsoleLogger($this->stream);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        putenv('DEBUG');
    }

    /**
     * @link \SimpleVC\Error\ConsoleLogger::log()
     */
    #[Test]
    public function testLog(): void
    {
        putenv('DEBUG=true');

        $this->logger->log(LogLevel::INFO, 'hello');

        $this->assertSame("hello\n", stream_get_contents($this->stream, -1, 0));
    }

    /**
     * @link \SimpleVC\Error\ConsoleLogger::log()
     */
    #[Test]
    public function testLogWithExceptionTrace(): void
    {
        putenv('DEBUG=true');

        $expectedExceptionLine = __LINE__ + 1;
        $exception = new RuntimeException('boom');

        $this->logger->log(LogLevel::ERROR, 'error', ['exception' => $exception]);

        $output = stream_get_contents($this->stream, -1, 0);

        $this->assertStringContainsString("error\n", $output);
        $this->assertStringContainsString('RuntimeException: boom in ' . __FILE__ . ':' . $expectedExceptionLine, $output);
    }

    /**
     * @link \SimpleVC\Error\ConsoleLogger::log()
     */
    #[Test]
    public function testLogWhenDebugIsFalse(): void
    {
        $this->logger->log(LogLevel::INFO, 'test');

        $this->assertSame('', stream_get_contents($this->stream, -1, 0));
    }
}
