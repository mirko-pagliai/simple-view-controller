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

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Define the root directory of the project
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

const TEST_APP = ROOT . DS . 'tests' . DS . 'test_app';
const CONFIG = TEST_APP . DS . 'config';
const TEMPLATES = TEST_APP . DS . 'templates';

// Define temporary directory
define('TMP', sys_get_temp_dir() . DS . 'simple-view-controller');
if (!file_exists(TMP)) {
    mkdir(TMP, 0777, true);
}

// Include Composer autoloader
require_once ROOT . '/vendor/autoload.php';
