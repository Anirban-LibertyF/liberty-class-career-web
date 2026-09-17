<?php
declare(strict_types=1);

$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
if(class_exists(Dotenv\Dotenv::class)&&is_file($root.'/.env'))Dotenv\Dotenv::createImmutable($root)->safeLoad();
$database=(string)($_ENV['DB_DATABASE']??'');
if(($_ENV['APP_ENV']??'')!=='test'||!str_ends_with($database,'_test')){
    fwrite(STDOUT,"Database smoke skipped: use APP_ENV=test and a DB_DATABASE ending in _test.\n");
    exit(0);
}
$pdo=App\Core\Database::connection();
$required=['admins','students','tests','questions','question_options','enrollments','payments','attempts','attempt_responses','response_options','audit_logs','rate_limits','remember_tokens','migrations'];
$found=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$missing=array_values(array_diff($required,$found));
if($missing){fwrite(STDERR,'Missing tables: '.implode(', ',$missing).PHP_EOL);exit(1);}
$columns=$pdo->query('SHOW COLUMNS FROM tests')->fetchAll(PDO::FETCH_COLUMN);
foreach(['exam_name','category'] as $column)if(!in_array($column,$columns,true)){fwrite(STDERR,"Missing tests.{$column}\n");exit(1);}
fwrite(STDOUT,"Database contract smoke checks passed.\n");
