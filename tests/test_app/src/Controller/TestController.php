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

namespace TestApp\Controller;

use RuntimeException;
use SimpleVC\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test controller used by the application integration tests.
 *
 * This controller is intentionally minimal and deterministic.
 * Each public method represents a specific runtime scenario that the `Application` class must be able to handle.
 */
final class TestController extends Controller
{
    /**
     * Returns an explicit HTTP response.
     *
     * Used to verify that `Application` correctly handles controllers that return a Response instance directly.
     */
    public function ok(): Response
    {
        return new Response('ok', 200);
    }

    /**
     * Does not return a `Response`.
     *
     * Used to verify that `Application` correctly falls back to implicit view rendering via Controller::render().
     */
    public function implicitRender(): void
    {
        $this->set(['value' => 'implicit']);
    }

    /**
     * Throws an unhandled exception.
     *
     * Used to verify that `Application` catches generic exceptions and converts them into a 500 Internal Server Error response.
     */
    public function boom(): void
    {
        throw new RuntimeException('Boom');
    }
}
