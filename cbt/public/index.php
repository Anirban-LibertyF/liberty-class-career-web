<?php
declare(strict_types=1);

use App\Core\App;

$hostRoot = dirname(__DIR__);
$appRoot = is_dir($hostRoot . '/app') ? $hostRoot : $hostRoot . '/lcc_cbt/current';
$envRoot = dirname($appRoot);
require $appRoot . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class)) {
    if (file_exists($envRoot . '/.env')) Dotenv\Dotenv::createImmutable($envRoot)->safeLoad();
    elseif (file_exists($appRoot . '/.env')) Dotenv\Dotenv::createImmutable($appRoot)->safeLoad();
}

App::boot($appRoot)->run();
