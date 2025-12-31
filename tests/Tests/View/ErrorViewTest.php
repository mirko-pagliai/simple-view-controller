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

namespace SimpleVC\Test\Tests\View;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleVC\View\ErrorView;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

#[CoversClass(ErrorView::class)]
class ErrorViewTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        putenv('DEBUG');
    }

    /**
     * @link \SimpleVC\View\ErrorView::__construct()
     */
    #[Test]
    public function testConstruct(): void
    {
        $errorView = new class extends ErrorView {
            public ?string $layout;
        };

        $this->assertSame('layouts/error.php', $errorView->layout);
    }

    /**
     * @link \SimpleVC\View\ErrorView::determineTemplate()
     */
    #[Test]
    #[TestWith(['errors/400.php', 404])]
    #[TestWith(['errors/400.php', 499])]
    #[TestWith(['errors/500.php', 500])]
    #[TestWith(['errors/500.php', 599])]
    public function testDetermineTemplate(string $expectedTemplate, int $statusCode): void
    {
        $errorView = new class extends ErrorView {
            public function determineTemplate(int $statusCode): string
            {
                return parent::determineTemplate($statusCode);
            }
        };

        $result = $errorView->determineTemplate($statusCode);
        $this->assertSame($expectedTemplate, $result);
    }

    /**
     * @link \SimpleVC\View\ErrorView::renderError()
     */
    #[Test]
    #[TestWith(['Error response: error 404', 404])]
    #[TestWith(['Error response: fatal 500', 500])]
    #[TestWith(['Error response: error 404', 404, new ResourceNotFoundException('Something were wrong...')])]
    #[TestWith(['Error response: fatal 500', 500, new RuntimeException('Runtime!')])]
    public function testRenderError(string $expected, int $statusCode, ?Throwable $exception = null): void
    {
        $errorView = new ErrorView();
        $result = $errorView->renderError($statusCode, $exception);
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[TestWith(['Error response: error 404', 404])]
    #[TestWith(['Error response: fatal 500', 500])]
    #[TestWith(['Error response: error 404 - Something were wrong...', 404, new ResourceNotFoundException('Something were wrong...')])]
    #[TestWith(['Error response: fatal 500 - Runtime!', 500, new RuntimeException('Runtime!')])]
    public function testRenderErrorWithDebugEnabled(string $expected, int $statusCode, ?Throwable $exception = null): void
    {
        putenv('DEBUG=true');
        $errorView = new ErrorView();
        $result = $errorView->renderError($statusCode, $exception);
        $this->assertSame($expected, $result);
    }

    /**
     * @link \SimpleVC\View\ErrorView::renderError()
     */
    #[Test]
    public function testRenderErrorWithNoLayout(): void
    {
        $errorView = new ErrorView();
        $errorView->setLayout(null);

        $result = $errorView->renderError(404);
        $this->assertSame('error 404', $result);
    }
}
