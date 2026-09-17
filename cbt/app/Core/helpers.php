<?php
use App\Core\Csrf;
function e(mixed $value): string { return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8'); }
function csrf_field(): string { return Csrf::field(); }
function old(string $key,string $default=''): string { return e($_SESSION['_old'][$key]??$default); }
function flash(string $key): ?string { $v=$_SESSION['_flash'][$key]??null; unset($_SESSION['_flash'][$key]); return $v; }
