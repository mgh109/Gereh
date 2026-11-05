<?php

use CodeIgniter\Boot;
use Config\Paths;

$minPhpVersion = '8.3';
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    http_response_code(503);
    echo "PHP {$minPhpVersion} or higher is required. Current version: " . PHP_VERSION;
    exit(1);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
