<?php
declare(strict_types=1);

use App\Core\Database;
use App\Services\ScoringService;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
if (is_file($root . '/.env')) {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

$statement = Database::connection()->query(
    "SELECT a.id FROM attempts a JOIN tests t ON t.id = a.test_id
     WHERE a.status = 'in_progress'
     AND (t.ends_at <= NOW() OR a.last_seen_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE)) ORDER BY a.id"
);
$attemptIds = $statement->fetchAll(PDO::FETCH_COLUMN);
$scoring = new ScoringService();
$failed = 0;

foreach ($attemptIds as $attemptId) {
    try {
        $scoring->evaluate((int) $attemptId);
        fwrite(STDOUT, "Finalized attempt {$attemptId}.\n");
    } catch (Throwable $exception) {
        $failed++;
        error_log("Attempt {$attemptId} finalization failed: {$exception->getMessage()}");
    }
}

fwrite(STDOUT, sprintf("Processed %d attempt(s); %d failed.\n", count($attemptIds), $failed));
exit($failed === 0 ? 0 : 1);
