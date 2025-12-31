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
