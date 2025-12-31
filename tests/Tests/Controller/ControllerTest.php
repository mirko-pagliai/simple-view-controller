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
