<?php
declare(strict_types=1);

namespace SimpleVC\Test\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleVC\Application;
use SimpleVC\TestCase\ResponseAssertionsTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TestApp\Controller\PagesController;

#[CoversClass(Application::class)]
class ApplicationTest extends TestCase
{
    use ResponseAssertionsTrait;

    /**
     * @link \SimpleVC\Application::getInstance()
     */
    #[Test]
    #[RunInSeparateProcess]
    public function testSingletonPattern(): void
    {
        $application = Application::init();
        $result = Application::getInstance();

        $this->assertSame($application, $result);
    }

    /**
     * @link \SimpleVC\Application::getInstance()
     */
    #[Test]
    #[RunInSeparateProcess]
    public function testGetInstanceThrowsExceptionWhenNotInitialized(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application has not been initialized.');
        Application::getInstance();
    }

    /**
     * @link \SimpleVC\Application::handle()
     */
    #[Test]
    public function testHandleSuccessfulRequest(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [
            '_controller' => [PagesController::class, 'index'],
        ]));

        $app = Application::init($routeCollection);
        $request = Request::create('/test', 'GET');

        $this->_response = $app->handle($request);

        $this->assertResponseIsSuccessful();
        $this->assertResponseContains('This is the index page.');
    }
}
