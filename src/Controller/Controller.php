<?php
declare(strict_types=1);

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
