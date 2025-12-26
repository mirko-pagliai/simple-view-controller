<?php
declare(strict_types=1);

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Define the root directory of the project
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

const DEBUG = true;
const TEST_APP = ROOT . DS . 'tests' . DS . 'test_app';
const TEMPLATES = TEST_APP . DS . 'templates';

// Define temporary directory
define('TMP', sys_get_temp_dir() . DS . 'simple-view-control');
if (!file_exists(TMP)) {
    mkdir(TMP, 0777, true);
}

// Include Composer autoloader if exists
$composerAutoload = ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
