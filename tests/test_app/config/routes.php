<?php
declare(strict_types=1);

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use TestApp\TestCase\PagesController;

$routes = new RouteCollection();

// Home route
$routes->add(name: 'home', route: new Route('/', [
    '_controller' => [PagesController::class, 'index'],
]));

return $routes;
