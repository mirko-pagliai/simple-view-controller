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

    protected const string DEFAULT_ROUTES_FILE = CONFIG . '/routes.php';

    /**
     * Constructs a new instance of the application.
     *
     * This method initializes the URL matcher and error renderer.
     * If a route collection is not provided, it attempts to load routes from a file.
     *
     * @param \Symfony\Component\Routing\RouteCollection|string|null $routes The route collection instance, a string
     *  path to the routes file, or `null` to use the default `config/routes.php` file.
     * @param \SimpleVC\Error\ErrorRenderer|null $errorRenderer The error renderer instance. Defaults to a new
     *  `ErrorRenderer` with a `ConsoleLogger` if not provided.
     * @return void
     * @throws \RuntimeException If the routes file does not exist or does not return a valid `RouteCollection` instance.
     */
    public function __construct(RouteCollection|string|null $routes = null, ?ErrorRenderer $errorRenderer = null)
    {
        if (!$routes instanceof RouteCollection) {
            $routesFile = $routes ?: self::DEFAULT_ROUTES_FILE;

            if (!file_exists($routesFile)) {
                throw new RuntimeException("Routes file {$routesFile} does not exist.");
            }

            $routes = require $routesFile;
            if (!$routes instanceof RouteCollection) {
                throw new RuntimeException(
                    "Routes file `{$routesFile}` must return an instance of `" . RouteCollection::class . '`.',
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
                    'Invalid controller resolution. A controller method could not be resolved from the request.',
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
