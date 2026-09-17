<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $fileConfig = is_file(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : [];
    $host = getenv('DB_HOST') ?: ($fileConfig['db_host'] ?? 'localhost');
    $port = getenv('DB_PORT') ?: ($fileConfig['db_port'] ?? '3306');
    $name = getenv('DB_NAME') ?: ($fileConfig['db_name'] ?? 'liberty_class_career');
    $user = getenv('DB_USER') ?: ($fileConfig['db_user'] ?? 'root');
    $pass = getenv('DB_PASS') ?: ($fileConfig['db_pass'] ?? '');

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
