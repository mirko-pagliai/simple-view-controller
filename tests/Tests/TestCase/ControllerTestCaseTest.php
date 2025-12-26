<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleVC\TestCase\ControllerTestCase;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(ControllerTestCase::class)]
class ControllerTestCaseTest extends TestCase
{
    /**
     * @link \SimpleVC\TestCase\ControllerTestCase::getRouteCollection()
     */
    #[Test]
    public function testGetRouteCollection(): void
    {
        $testCase = new class('myTest') extends ControllerTestCase {
            public ?RouteCollection $routes = null;

            public function getRouteCollection(): RouteCollection
            {
                return parent::getRouteCollection();
            }
        };

        $this->assertNull($testCase->routes);

        $routeCollection = $testCase->getRouteCollection();
        $this->assertEquals($testCase->routes, $routeCollection);

        //This tests whether it returns the property value without actually reloading the routes
        $testCase->routes = new RouteCollection();
        $newRouteCollection = $testCase->getRouteCollection();
        $this->assertNotEquals($newRouteCollection, $routeCollection);
    }

    /**
     * @link \SimpleVC\TestCase\ControllerTestCase::executeAction()
     */
    #[Test]
    public function testExecuteActionWithNoExistingRoute(): void
    {
        $testCase = new class('myTest') extends ControllerTestCase {
            public function executeAction(
                string $route,
                string $method = 'GET',
                array $parameters = [],
                array $routeParameters = [],
                array $server = [],
                ?string $content = null,
            ): void {
                parent::executeAction($route, $method, $parameters, $routeParameters, $server, $content);
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route `nonExistingRoute` not found. Available routes: `home`.');
        $testCase->executeAction('nonExistingRoute');
    }
}
