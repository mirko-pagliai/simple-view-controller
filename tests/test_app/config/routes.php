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
 *
 * Application must trigger implicit view rendering.
 */
$routes->add('implicit', new Route('/implicit', [
    '_controller' => [$controller, 'implicitRender'],
]));

/*
 * Route whose controller throws an unhandled exception.
 *
 * Application must return a 500 Internal Server Error response.
 */
$routes->add('boom', new Route('/boom', [
    '_controller' => [$controller, 'boom'],
]));

return $routes;
