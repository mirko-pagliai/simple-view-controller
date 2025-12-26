<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SimpleVC\Controller\Controller;
use SimpleVC\View\View;

#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
    /**
     * @link \SimpleVC\Controller\Controller::getView()
     */
    #[Test]
    public function testGetView(): void
    {
        $view = $this->createStub(View::class);

        $controller = new class extends Controller {
            public View $view;
        };
        $controller->view = $view;

        $this->assertSame($view, $controller->getView());
    }

    /**
     * @link \SimpleVC\Controller\Controller::set()
     */
    #[Test]
    public function testSet(): void
    {
        $view = $this->createMock(View::class);
        $view
            ->expects($this->once())
            ->method('set')
            ->with(['key1' => 'value1']);

        $controller = new class extends Controller {
            public View $view;
        };
        $controller->view = $view;

        $controller->set(['key1' => 'value1']);
    }

    /**
     * @link \SimpleVC\Controller\Controller::render()
     */
    #[Test]
    public function testRender(): void
    {
        $view = $this->createMock(View::class);
        $view
            ->expects($this->once())
            ->method('render')
            ->with(null)
            ->willReturn('test response');

        $controller = new class extends Controller {
            public View $view;
        };
        $controller->view = $view;

        $result = $controller->render();
        $this->assertSame('test response', $result->getContent());
    }
}
