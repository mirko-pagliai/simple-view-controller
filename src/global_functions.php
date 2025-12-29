<?php
declare(strict_types=1);

namespace SimpleVC;

if (!function_exists('env')) {
    /**
     * Retrieves the value of an environment variable with optional default.
     *
     * This helper function reads environment variables using `getenv()` and provides support for:
     *
     * - Default values when the environment variable is not set
     * - Automatic boolean conversion for common boolean string representations ('true', 'false', '1', '0')
     *
     * Example usage:
     * ```
     * // Returns false if DEBUG is not set
     * $debug = env('DEBUG', false);
     * ```
     *
     * To set environment variables:
     *
     * - In production/development: Use a `.env` file and load it with a package like `josegonzalez/dotenv`
     * - In tests: Use `putenv('DEBUG=true')` to override values
     *
     * @param string $key The environment variable name
     * @param mixed $default The default value to return if the environment variable is not set
     * @return mixed The environment variable value, with boolean strings converted to actual booleans
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }
        if ($value === 'null') {
            return null;
        }

        // Handle boolean strings
        if (in_array(strtolower($value), ['true', 'false', '1', '0'], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }
}
