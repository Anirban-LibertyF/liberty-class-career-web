<?php
namespace App\Core;
final class RateLimiter {
 public static function hit(string $action,string $identity,int $max,int $windowSeconds): void {
  $db=Database::connection(); $key=hash('sha256',$action.'|'.$identity); $since=date('Y-m-d H:i:s',time()-$windowSeconds);
  $db->prepare('DELETE FROM rate_limits WHERE expires_at < NOW()')->execute();
  $q=$db->prepare('SELECT COUNT(*) FROM rate_limits WHERE action_key=? AND occurred_at>=?'); $q->execute([$key,$since]);
  if((int)$q->fetchColumn()>=$max){ header('Retry-After: '.$windowSeconds); throw new HttpException(429,'Too many requests. Please wait and try again.'); }
  $db->prepare('INSERT INTO rate_limits(action_key,occurred_at,expires_at) VALUES(?,NOW(),DATE_ADD(NOW(),INTERVAL ? SECOND))')->execute([$key,$windowSeconds]);
 }
 public static function identity(): string { return (string)(Auth::id() ?: ($_SERVER['REMOTE_ADDR']??'unknown')); }
}
