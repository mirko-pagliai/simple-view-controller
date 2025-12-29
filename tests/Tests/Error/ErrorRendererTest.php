<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use SimpleVC\Error\ErrorRenderer;
use SimpleVC\TestCase\ResponseAssertionsTrait;

#[CoversClass(ErrorRenderer::class)]
class ErrorRendererTest extends TestCase
{
    use ResponseAssertionsTrait;

    protected ErrorRenderer $errorRenderer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->errorRenderer = new ErrorRenderer();
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::__construct()
     */
    #[Test]
    public function testConstructWithCustomLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $errorRenderer = new class ($logger) extends ErrorRenderer {
            public ?LoggerInterface $logger;
        };
        $this->assertSame($logger, $errorRenderer->logger);
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testRenderReturns404Response(): void
    {
        $this->_response = $this->errorRenderer->render(404);
        $this->assertResponseError();
        $this->assertResponseContains('Error response: error 404');
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testRenderReturns500Response(): void
    {
        $this->_response = $this->errorRenderer->render(500);
        $this->assertResponseFailure();
        $this->assertResponseContains('Error response: fatal 500');
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testLogsErrorWhenLoggerAndExceptionProvided(): void
    {
        $exception = new RuntimeException('Boom');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $exception->getMessage(),
                compact('exception') + ['status_code' => 500],
            );

        $this->errorRenderer->setLogger($logger);
        $this->_response = $this->errorRenderer->render(500, $exception);
        $this->assertResponseFailure();
        $this->assertResponseContains('Error response: fatal 500');
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testDoesNotLogWithoutException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->never())
            ->method('error');

        $this->errorRenderer->setLogger($logger);
        $this->_response = $this->errorRenderer->render(500);
        $this->assertResponseFailure();
        $this->assertResponseContains('Error response: fatal 500');
    }
}
