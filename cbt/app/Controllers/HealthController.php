<?php
namespace App\Controllers;

use App\Core\{Database,Response};

final class HealthController
{
    public function show(): void
    {
        try {
            Database::connection()->query('SELECT 1')->fetchColumn();
            Response::json([
                'status' => 'ok',
                'app' => 'Liberty Class and Career (CBT)',
                'database' => 'connected',
                'time' => date(DATE_ATOM),
            ]);
        } catch (\Throwable $e) {
            Response::json([
                'status' => 'error',
                'app' => 'Liberty Class and Career (CBT)',
                'database' => 'unavailable',
            ], 503);
        }
    }
}

