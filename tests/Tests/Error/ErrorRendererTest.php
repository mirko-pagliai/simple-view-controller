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
        $logger = $this->createStub(LoggerInterface::class);

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
