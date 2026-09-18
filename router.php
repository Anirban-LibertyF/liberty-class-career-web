<?php
declare(strict_types=1);

$root = __DIR__;
$path = urldecode(
    (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/')
);

if (str_starts_with($path, '/cbt/assets/')) {
    $assetRoot = realpath($root . '/cbt/public/assets');
    $assetFile = realpath(
        $root . '/cbt/public' . substr($path, 4)
    );

    if (
        $assetRoot !== false &&
        $assetFile !== false &&
        is_file($assetFile) &&
        str_starts_with($assetFile, $assetRoot . DIRECTORY_SEPARATOR)
    ) {
        $extension = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));

        $types = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];

        if (isset($types[$extension])) {
            header('Content-Type: ' . $types[$extension]);
        }

        readfile($assetFile);
        exit;
    }

    http_response_code(404);
    exit('Asset not found');
}

$requestedFile = realpath($root . $path);

if (
    $requestedFile !== false &&
    is_file($requestedFile) &&
    str_starts_with($requestedFile, $root . DIRECTORY_SEPARATOR)
) {
    return false;
}

require $root . '/index.php';