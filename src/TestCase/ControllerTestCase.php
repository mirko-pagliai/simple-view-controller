<?php
declare(strict_types=1);

namespace SimpleVC\TestCase;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleVC\Controller\Controller;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base test case class for testing controllers in the application.
 *
 * This class provides utility methods and properties to streamline controller testing by handling route matching,
 * controller instantiation, request simulation, and response capturing. It ensures that controllers and actions are
 * properly validated and that the application's routing mechanism is accurately simulated.
 */
abstract class ControllerTestCase extends TestCase
{
    use ResponseAssertionsTrait;

    protected ?RouteCollection $routes = null;

    /**
     * Retrieves the collection of routes for the application.
     *
     * This method loads routes from the configuration file if they haven't been loaded yet.
     * The configuration file must return an instance of `RouteCollection`. If the returned value
     * is not of the expected type, an exception will be thrown.
     *
     * @return \Symfony\Component\Routing\RouteCollection The collection of routes defined in the configuration file.
     * @throws \RuntimeException If the configuration file does not return an instance of `RouteCollection`.
     */
    protected function getRouteCollection(): RouteCollection
    {
        if (!$this->routes) {
            $this->routes = require CONFIG . '/routes.php';
        }

        if (!$this->routes instanceof RouteCollection) {
            throw new RuntimeException(
                'Route `' . CONFIG . '/routes.php'. '` file must return a `RouteCollection` instance.',
            );
        }

        return $this->routes;
    }

    /**
     * Executes a controller action for the given route.
     *
     * This method performs the following steps:
     *
     * 1. Matches the route name to find the controller class and action method
     * 2. Validates that controller class and action method exist
     * 3. Instantiates the controller (which creates its own `View` instance)
     * 4. Creates a simulated `Request` object with route parameters substituted
     * 5. Injects the `Request` into the controller's view
     * 6. Calls the action method on the controller
     * 7. Renders the view and captures the Response object
     *
     * The resulting `Response` is stored in `$this->response` for assertions.
     *
     * @param string $routeName The route name as defined in routes.php (e.g., `home`, `user_profile`)
     * @param string $method The HTTP method to simulate (GET, POST, PUT, DELETE, etc.). Defaults to 'GET'.
     * @param array<string, mixed> $parameters Request parameters (query params for GET, body params for POST, etc.)
     * @param array<string, mixed> $routeParameters Route placeholder values (e.g., `['id' => 123]` for `/users/{id}`)
     * @param array<string, string> $server Server and headers parameters (`$_SERVER` values)
     * @param string|null $content Raw request body content
     * @return void
     * @throws \RuntimeException If the route is not found, controller/action are invalid, or the class doesn't exist
     */
    protected function executeAction(
        string $routeName,
        string $method = 'GET',
        array $parameters = [],
        array $routeParameters = [],
        array $server = [],
        ?string $content = null,
    ): void {
        $routeCollection = $this->getRouteCollection();

        $route = $routeCollection->get($routeName);

        if ($route === null) {
            $availableRoutes = implode(', ', array_map(fn(string $routeName): string => "`$routeName`", array_keys($routeCollection->all())));
            throw new RuntimeException("Route `{$routeName}` not found. Available routes: {$availableRoutes}.");
        }

        /**
         * Extract controller and action from route defaults.
         *
         * Supports two formats:
         * 1. `_controller` as an array: `['ControllerClass', 'methodName']`
         * 2. Separate `_controller` and `_action`
         */
        $controller = $route->getDefault('_controller');

        if (is_array($controller)) {
            // Format 1: `_controller` is `['ControllerClass', 'methodName']`
            if (count($controller) !== 2) {
                throw new RuntimeException(
                    "Route `{$routeName}` has invalid `_controller` format. Expected [class, method]."
                );
            }
            [$controllerClass, $action] = $controller;
        } else {
            // Format 2: separate `_controller` and `_action`
            $controllerClass = $controller;
            $action = $route->getDefault('_action');

            if ($controllerClass === null || $action === null) {
                throw new RuntimeException(
                    "Route `{$routeName}` is missing required defaults: `_controller` and/or `_action`."
                );
            }
        }

        // Validate controller class exists and extends base Controller
        if (!is_string($controllerClass) || !class_exists($controllerClass)) {
            throw new RuntimeException("Controller class `{$controllerClass}` does not exist.");
        }

        if (!is_subclass_of($controllerClass, Controller::class)) {
            throw new RuntimeException("Controller `{$controllerClass}` must extend " . Controller::class . ".");
        }

        // Validate action method exists
        if (!is_string($action) || !method_exists($controllerClass, $action)) {
            throw new RuntimeException("Action `{$action}` does not exist in controller `{$controllerClass}`.");
        }

        /** @var \SimpleVC\Controller\Controller $controller */
        $controller = new $controllerClass();

        // Replace route placeholders with actual values (URL-encoded)
        $path = $route->getPath();
        foreach ($routeParameters as $key => $value) {
            $path = str_replace("{{$key}}", urlencode((string)$value), $path);
        }

        // Create a simulated Request with proper parameters
        $request = Request::create($path, $method, $parameters, [], [], $server, $content);

        // Set route parameters in request attributes
        foreach ($routeParameters as $key => $value) {
            $request->attributes->set($key, $value);
        }

        // Set the controller in array format for View's autoDetectTemplate()
        $request->attributes->set('_controller', [$controllerClass, $action]);
        $controller->getView()->setRequest($request);

        // Execute the controller action
        $controller->$action();

        // Render view and capture Response
        $this->response = $controller->render();
    }
}
