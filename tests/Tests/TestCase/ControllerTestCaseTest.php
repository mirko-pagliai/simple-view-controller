<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
}
