<?php
declare(strict_types=1);

namespace SimpleVC\View;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The `View` class provides functionality to render templates with optional layout support.
 *
 * It manages template-related data, integrates request handling, and supports auto-detecting templates
 * based on the current request.
 */
class View
{
    protected string $templatePath;

    protected ?string $layout;

    protected ?Request $request = null;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Constructor method.
     *
     * @param string|null $templatePath The path to the templates' directory. Defaults to `TEMPLATES` constant if not provided.
     * @param string|null $layout The default layout file or null for no layout. Defaults to 'layouts/default.php'.
     * @return void
     *
     * @throws \InvalidArgumentException If the provided template path does not exist.
     */
    public function __construct(
        ?string $templatePath = null,
        ?string $layout = 'layouts/default.php',
    ) {
        $templatePath = rtrim($templatePath ?: TEMPLATES, DS);
        if (!is_dir($templatePath)) {
            throw new InvalidArgumentException("Template path `{$templatePath}` does not exist.");
        }

        $this->templatePath = $templatePath;
        $this->layout = $layout;
    }

    /**
     * Sets the layout template to be used when rendering.
     *
     * @param string|null $layout The layout file path relative to templatePath, or null for no layout.
     * @return self
     */
    public function setLayout(?string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Sets the request object.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request to set.
     * @return self
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Stores the provided data array into the internal storage.
     * Throws an exception if a key in the provided data already exists in the internal storage.
     *
     * @param array<string, mixed> $data An associative array where the keys and values will be added to the internal storage.
     * @return self
     *
     * @throws \InvalidArgumentException If a key in the input array already exists in the storage.
     */
    public function set(array $data): self
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                throw new InvalidArgumentException("Data key `{$key}` already exists.");
            }
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Internal method a PHP template file and returns its output as a string.
     *
     * This method extracts the provided data array into variables for use within the template file, buffers the
     *  template's output, and returns the buffered content.
     *
     * If the file cannot be included, it returns false.
     *
     * @param string $filePath The path to the template file to include.
     * @param array<string, mixed> $data An associative array of data to be extracted as variables within the template.
     * @return string|false The output of the template file as a string, or false on failure.
     *
     * @internal This method is used internally by `renderFile()` to render a template file.
     */
    protected function includeTemplateFile(string $filePath, array $data): string|false
    {
        extract($data, EXTR_SKIP);

        ob_start();
        include $filePath;

        return ob_get_clean();
    }

    /**
     * Renders a template file with the given data.
     *
     * @param string $file The name of the template file to render.
     * @param array<string, mixed> $data An associative array of data to be used within the template.
     * @return string The rendered output of the template.
     *
     * @throws \InvalidArgumentException If the specified template file does not exist.
     * @throws \RuntimeException If the template file returns invalid output, that is not a string.
     */
    protected function renderFile(string $file, array $data): string
    {
        $filePath = $this->templatePath . '/' . $file;
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Template file `{$filePath}` not found.");
        }

        $result = $this->includeTemplateFile($filePath, $data);
        if (!is_string($result)) {
            throw new RuntimeException("Template file `{$filePath}` returned invalid output.");
        }

        return $result;
    }

    /**
     * Renders a template and optionally wraps it in a layout.
     *
     * This method renders the specified template file by using the associated data. If no template is specified, it
     *  will attempt to auto-detect the template based on the incoming request. If a layout is defined, the rendered
     *  content of the template will be embedded into the layout before being returned.
     *
     * @param string|null $template The path to the template file to render. If null, the template is auto-detected.
     * @return string The fully rendered output, which may include the layout if set.
     */
    public function render(?string $template = null): string
    {
        // If no template specified, auto-detect from request
        if ($template === null) {
            $template = $this->autoDetectTemplate();
        }

        // Render template
        $content = $this->renderFile($template, $this->data);

        // If the layout is set, render content inside the layout
        if ($this->layout !== null) {
            $this->data['content'] = $content;

            return $this->renderFile($this->layout, $this->data);
        }

        return $content;
    }

    /**
     * Automatically detects the template path based on the controller and action in the current request.
     *
     * @return string The resolved template path in the format `ControllerName/action_name.php`.
     *
     * @throws \RuntimeException If the request is not set, or if the controller cannot be determined from the request attributes.
     */
    protected function autoDetectTemplate(): string
    {
        if ($this->request === null) {
            throw new RuntimeException('`$this->request` not set. Call `setRequest()` before `render()`.');
        }

        /** @var non-empty-string|array{class-string, non-empty-string} $controller */
        $controller = $this->request->attributes->get('_controller');
        if (!$controller) {
            throw new RuntimeException('`_controller` attribute not found in `$this->request`.');
        }

        /**
         * Handles both:
         *
         *  - Controller as an array `[Namespace\Controller\ClassName::class, 'methodName']`;
         *  - Controller as a string `'Namespace\Controller\ClassName::methodName'`.
         */
        if (is_array($controller)) {
            [$class, $action] = $controller;
        } else {
            [$class, $action] = explode('::', $controller);
        }

        // Extract controller name (remove namespace and "Controller" suffix)
        $controllerName = basename(str_replace('\\', '/', $class));
        $controllerName = str_replace('Controller', '', $controllerName);

        // Convert action to `snake_case`
        $actionName = $this->camelToSnake($action);

        return "{$controllerName}/{$actionName}.php";
    }

    /**
     * Converts a `camelCase` string to `snake_case` format.
     *
     * @param string $input The `camelCase` string to be converted.
     * @return string The resulting `snake_case` string.
     */
    protected function camelToSnake(string $input): string
    {
        $result = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $input) ?: '';
        $result = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $result) ?: '';

        return strtolower($result);
    }
}
