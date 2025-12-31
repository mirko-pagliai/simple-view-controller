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

namespace SimpleVC\Controller;

use SimpleVC\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract base class for managing controllers in an application.
 *
 * Provides methods to initialize a view instance, set data to it,
 * and render the resulting content.
 */
abstract class Controller
{
    protected View $view;

    /**
     * Class constructor.
     *
     * Initializes the view instance and sets its default layout.
     *
     * @return void
     */
    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Retrieves the view instance.
     *
     * @return \SimpleVC\View\View The view instance.
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * Sets the given data to the view.
     *
     * @param array<string, mixed> $data An associative array containing data to be set.
     * @return void
     */
    public function set(array $data): void
    {
        $this->view->set($data);
    }

    /**
     * Renders the view and returns the `Response` object.
     *
     * @return \Symfony\Component\HttpFoundation\Response The rendered `Response` object.
     */
    public function render(): Response
    {
        return new Response($this->view->render());
    }
}
