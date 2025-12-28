<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleVC\Error\ErrorRenderer;
use SimpleVC\TestCase\ResponseAssertionsTrait;
use Throwable;

#[CoversClass(ErrorRenderer::class)]
class ErrorRendererTest extends TestCase
{
    use ResponseAssertionsTrait;

    /**
     * @link \SimpleVC\Error\ErrorRenderer::parseMessage()
     */
    #[Test]
    public function testParseMessage(): void
    {
        $expected = '[404] Test error message in ' . __FILE__ . ' on line ' . __LINE__ + 2;

        $exception = new RuntimeException('Test error message');

        $errorRenderer = new class extends ErrorRenderer {
            public function parseMessage(int $statusCode, ?Throwable $exception): string
            {
                return parent::parseMessage($statusCode, $exception);
            }
        };

        $result = $errorRenderer->parseMessage(404, $exception);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testRenderWithError(): void
    {
        $errorRenderer = $this->createPartialMock(ErrorRenderer::class, ['writeToConsole', 'writeToLog']);

        foreach (['writeToConsole', 'writeToLog'] as $expectedMethod) {
            $errorRenderer
                ->expects($this->once())
                ->method($expectedMethod)
                ->with(404, null);
        }

        $this->_response = $errorRenderer->render(404);

        $this->assertResponseError();
        $this->assertResponseContains('Error response: error 404');
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testRenderWithFailure(): void
    {
        $errorRenderer = $this->createPartialMock(ErrorRenderer::class, ['writeToConsole', 'writeToLog']);

        foreach (['writeToConsole', 'writeToLog'] as $expectedMethod) {
            $errorRenderer
                ->expects($this->once())
                ->method($expectedMethod)
                ->with(500, null);
        }

        $this->_response = $errorRenderer->render(500);

        $this->assertResponseFailure();
        $this->assertResponseContains('Error response: fatal 500');
    }

    /**
     * @link \SimpleVC\Error\ErrorRenderer::render()
     */
    #[Test]
    public function testRenderWithException(): void
    {
        $exception = new RuntimeException('Test error message');

        $errorRenderer = $this->createPartialMock(ErrorRenderer::class, ['writeToConsole', 'writeToLog']);

        foreach (['writeToConsole', 'writeToLog'] as $expectedMethod) {
            $errorRenderer
                ->expects($this->once())
                ->method($expectedMethod)
                ->with(500, $exception);
        }

        $this->_response = $errorRenderer->render(500, $exception);

        $this->assertResponseFailure();
        $this->assertResponseContains('Error response: fatal 500');
    }
}
