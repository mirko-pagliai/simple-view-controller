<?php
declare(strict_types=1);

namespace SimpleVC;

use RuntimeException;
use SimpleVC\Controller\Controller;
use SimpleVC\Error\ConsoleLogger;
use SimpleVC\Error\ErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

/**
 * Central application class that manages routing, session, and request handling.
 *
 * This class implements the Singleton pattern and serves as the main entry point for handling HTTP requests. It
 *  coordinates routing, controller resolution, session management, and error handling.
 */
class Application
{
    private static ?self $instance = null;

    private UrlMatcher $matcher;

    private ErrorRenderer $errorRenderer;

    /**
     * Protected constructor to allow extension for testing while enforcing singleton pattern.
     *
     * Initializes the routing matcher and error renderer with the provided route collection.
     *
     * @param \Symfony\Component\Routing\RouteCollection $routeCollection The routes configuration
     * @param \SimpleVC\Error\ErrorRenderer|null $errorRenderer Optional error renderer
     * @return void
     */
    protected function __construct(RouteCollection $routeCollection, ?ErrorRenderer $errorRenderer = null)
    {
        $context = new RequestContext();
        $this->matcher = new UrlMatcher($routeCollection, $context);

        $this->errorRenderer = $errorRenderer ?? new ErrorRenderer(new ConsoleLogger());
    }

    /**
     * Initializes the singleton instance of the class, loading routes from the specified collection or default path.
     *
     * @param \Symfony\Component\Routing\RouteCollection|null $routeCollection The route collection to use, or `null` to
     *  load from `CONFIG/routes.php`
     * @param \SimpleVC\Error\ErrorRenderer|null $errorRenderer Optional error renderer
     * @return self The initialized instance of the class
     */
    public static function init(?RouteCollection $routeCollection = null, ?ErrorRenderer $errorRenderer = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($routeCollection ?? require CONFIG . '/routes.php', $errorRenderer);
        }

        return self::$instance;
    }

    /**
     * Retrieves the application singleton instance.
     *
     * @return self The application instance
     * @throws \RuntimeException If the application has not been initialized
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new RuntimeException('Application has not been initialized.');
        }

        return self::$instance;
    }

    /**
     * Handles an HTTP request and returns the corresponding response.
     *
     * This method:
     * 1. Initializes and configures the session
     * 2. Matches the request to a route
     * 3. Resolves and executes the controller
     * 4. Returns the response or renders error pages for exceptions
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The HTTP request to handle
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        // Initialize session
        $session = new Session(new NativeSessionStorage([
            'cookie_lifetime' => 0,
            'cookie_httponly' => true,
            'cookie_samesite' => 'lax',
        ]));
        $session->start();
        $request->setSession($session);

        // Update request context
        $this->matcher->getContext()->fromRequest($request);

        try {
            // Match route
            $request->attributes->add($this->matcher->match($request->getPathInfo()));

            // Resolve controller
            $controllerResolver = new ControllerResolver();
            $argumentResolver = new ArgumentResolver();

            $controller = $controllerResolver->getController($request);
            if (!$controller) {
                throw new ResourceNotFoundException('Controller not found for the request');
            }

            if (!is_array($controller) || count($controller) !== 2) {
                throw new RuntimeException('Invalid controller format');
            }
            if (!$controller[0] instanceof Controller) {
                throw new RuntimeException('Controller must extend `' . Controller::class . '`.');
            }
            if (!is_string($controller[1])) {
                throw new RuntimeException('Controller method must be a string');
            }

            $controller[0]->getView()->setRequest($request);
            $arguments = $argumentResolver->getArguments($request, $controller);

            $response = call_user_func_array($controller, $arguments);

            if (!$response instanceof Response) {
                $response = $controller[0]->render();
            }

            return $response;
        } catch (ResourceNotFoundException $e) {
            return $this->errorRenderer->render(404, $e);
        } catch (HttpExceptionInterface $e) {
            return $this->errorRenderer->render($e->getStatusCode(), $e);
        } catch (Throwable $e) {
            return $this->errorRenderer->render(500, $e);
        }
    }
}
