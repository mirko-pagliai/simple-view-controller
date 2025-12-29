<?php
declare(strict_types=1);

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TestApp\Controller\TestController;

$controller = new TestController();

$routes = new RouteCollection();

/*
 * Route that returns an explicit Response instance.
 */
$routes->add('ok', new Route('/ok', [
    '_controller' => [$controller, 'ok'],
]));

/*
 * Route whose controller does not return a Response.
 * Application must trigger implicit view rendering.
 */
$routes->add('implicit', new Route('/implicit', [
    '_controller' => [$controller, 'implicitRender'],
]));

/*
 * Route whose controller throws an unhandled exception.
 * Application must return a 500 Internal Server Error response.
 */
$routes->add('boom', new Route('/boom', [
    '_controller' => [$controller, 'boom'],
]));

return $routes;
