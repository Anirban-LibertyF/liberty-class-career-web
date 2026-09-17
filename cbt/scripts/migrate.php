<?php
declare(strict_types=1);

use App\Core\Database;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

if (class_exists(Dotenv\Dotenv::class) && is_file($root . '/.env')) {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

/** @return list<string> */
function sqlStatements(string $sql): array
{
    $lines = preg_split('/\R/', $sql) ?: [];
    $sql = implode("\n", array_filter($lines, static fn(string $line): bool => !preg_match('/^\s*--/', $line)));
    $statements = [];
    $buffer = '';
    $quote = null;
    $escaped = false;

    foreach (str_split($sql) as $char) {
        if ($escaped) {
            $buffer .= $char;
            $escaped = false;
            continue;
        }
        if ($char === '\\' && $quote !== null) {
            $buffer .= $char;
            $escaped = true;
            continue;
        }
        if (($char === "'" || $char === '"' || $char === '`')) {
            if ($quote === null) {
                $quote = $char;
            } elseif ($quote === $char) {
                $quote = null;
            }
            $buffer .= $char;
            continue;
        }
        if ($char === ';' && $quote === null) {
            if (trim($buffer) !== '') {
                $statements[] = trim($buffer);
            }
            $buffer = '';
            continue;
        }
        $buffer .= $char;
    }

    if (trim($buffer) !== '') {
        $statements[] = trim($buffer);
    }
    return $statements;
}

function executeSqlFile(PDO $db, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException("Cannot read SQL file: {$path}");
    }
    foreach (sqlStatements($sql) as $statement) {
        $db->exec($statement);
    }
}

$db = Database::connection();
$db->exec("CREATE TABLE IF NOT EXISTS migrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$applied = $db->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$applied = array_fill_keys(array_map('strval', $applied), true);
$files = glob($root . '/database/migrations/*.sql') ?: [];
sort($files, SORT_NATURAL);

foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        echo "Already applied: {$name}\n";
        continue;
    }

    echo "Applying: {$name}\n";
    try {
        if ($name === '001_initial_schema.sql') {
            executeSqlFile($db, $root . '/database/schema.sql');
            executeSqlFile($db, $root . '/database/seed.sql');
        } else {
            executeSqlFile($db, $file);
        }
        $stmt = $db->prepare('INSERT INTO migrations(migration) VALUES(?)');
        $stmt->execute([$name]);
        echo "Applied: {$name}\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "Migration failed ({$name}): {$e->getMessage()}\n");
        exit(1);
    }
}

echo "Database migrations complete.\n";
