<?php
namespace App\Core;
final class Csrf {
 public static function token(): string { return $_SESSION['_csrf']??=bin2hex(random_bytes(32)); }
 public static function field(): string { return '<input type="hidden" name="_csrf" value="'.htmlspecialchars(self::token()).'">'; }
 public static function verify(): void { $provided=$_SERVER['HTTP_X_CSRF_TOKEN']??$_POST['_csrf']??''; if(!hash_equals(self::token(),(string)$provided)) throw new HttpException(419,'Your session expired. Refresh and try again.'); }
}
