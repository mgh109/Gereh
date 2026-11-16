<?php

error_reporting(E_ALL);
// Public requests never expose the debug toolbar just because .env says development.
// Local developers can explicitly opt in; CLI checks keep diagnostics enabled.
$debug = PHP_SAPI === 'cli' || filter_var($_ENV['GEREH_DEBUG_ENABLED'] ?? $_SERVER['GEREH_DEBUG_ENABLED'] ?? getenv('GEREH_DEBUG_ENABLED') ?: false, FILTER_VALIDATE_BOOL);
ini_set('display_errors', $debug ? '1' : '0');
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', $debug);
defined('CI_DEBUG') || define('CI_DEBUG', $debug);
