<?php
declare(strict_types=1);

namespace SimpleVC;

use RuntimeException;
use SimpleVC\Controller\Controller;
use SimpleVC\Error\ConsoleLogger;
use SimpleVC\Error\ErrorRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Throwable;

/**
 * Application runtime.
 *
 * This class represents the core runtime of the framework.
 *
 * It is responsible for:
 *  - Loading routing configuration
 *  - Handling the request lifecycle
 *  - Resolving controllers
 *  - Handling errors
 *  - Always returning a Response
 *
 * The consuming application is expected to provide:
 *  - An entry point (`index.php`)
 *  - A routing configuration file at `config/routes.php`
 *
 * The Application never sends the response itself.
 */
class Application
{
    protected UrlMatcher $matcher;

    protected ErrorRenderer $errorRenderer;

    /**
     * Application constructor.
     *
     * If no `RouteCollection` is provided, the application will try to load `config/routes.php` from the current
     *  working directory.
     *
     * @throws \RuntimeException When routing configuration is missing or invalid.
     */
    public function __construct(?RouteCollection $routes = null, ?ErrorRenderer $errorRenderer = null) {
        if ($routes === null) {
            $routesFile = 'config/routes.php';
            if (file_exists($routesFile)) {
                throw new RuntimeException("Missing routing configuration. Expected `{$routesFile}`.");
            }

            // @phpstan-ignore require.fileNotFound
            $routes = require $routesFile;
            if (!$routes instanceof RouteCollection) {
                throw new RuntimeException(
                    "`{$routesFile}` must return an instance of `" . RouteCollection::class . '`.',
                );
            }
        }

        $this->matcher = new UrlMatcher($routes, new RequestContext());

        $this->errorRenderer = $errorRenderer ?? new ErrorRenderer(new ConsoleLogger());
    }

    /**
     * Runs the application and returns a Response.
     *
     * This method represents the full request lifecycle:
     *  - Create the Request (if not provided)
     *  - Match the route
     *  - Resolve and execute the controller
     *  - Handle any error and always return a Response
     */
    public function run(?Request $request = null): Response
    {
        $request ??= Request::createFromGlobals();

        $this->matcher
            ->getContext()
            ->fromRequest($request);

        try {
            // Match route and inject attributes into the request
            $parameters = $this->matcher->match($request->getPathInfo());
            $request->attributes->add($parameters);

            // Resolve controller
            $controllerResolver = new ControllerResolver();
            $argumentResolver = new ArgumentResolver();

            $callable = $controllerResolver->getController($request);

            if (!is_array($callable) || count($callable) !== 2) {
                throw new RuntimeException(
                    "'Invalid controller resolution. Expected `['ControllerName', 'methodName']` callable.'",
                );
            }

            [$controller, $method] = $callable;
            if (!$controller instanceof Controller) {
                throw new RuntimeException('Controller must extend `' . Controller::class . '`.');
            }

            // Inject the request into the view, resolve controller arguments, then execute the controller method
            $controller->getView()->setRequest($request);

            $arguments = $argumentResolver->getArguments($request, $callable);

            $response = $controller->{$method}(...$arguments);

            // If the controller does not return a Response, render the View
            if (!$response instanceof Response) {
                $response = $controller->render();
            }

            return $response;
        } catch (ResourceNotFoundException $exception) {
            return $this->errorRenderer->render(404, $exception);
        } catch (HttpExceptionInterface $exception) {
            return $this->errorRenderer->render($exception->getStatusCode(), $exception);
        } catch (Throwable $exception) {
            return $this->errorRenderer->render(500, $exception);
        }
    }
}
