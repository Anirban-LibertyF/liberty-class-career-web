<?php
namespace App\Core;
use PDO;
final class Auth {
 public static function user(): ?array { return $_SESSION['user']??null; }
 public static function id(): int { return (int)(self::user()['id']??0); }
 public static function login(string $identity,string $password,bool $remember=false): bool {
  $db=Database::connection();
  $q=$db->prepare("SELECT id,name,email,password_hash,'student' role,profile_photo,status account_status FROM students WHERE (email=? OR phone=?) AND deleted_at IS NULL LIMIT 1");
  $q->execute([$identity,$identity]); $u=$q->fetch();
  if(!$u || $u['account_status']!=='active' || !password_verify($password,$u['password_hash'])) return false;
  session_regenerate_id(true); unset($u['password_hash'],$u['account_status']); $_SESSION['user']=$u;
  if($remember) self::issueRememberToken($u);
  return true;
 }
 public static function restoreRemembered(): void {
  if(self::user() || empty($_COOKIE['lcc_remember'])) return;
  [$selector,$validator]=array_pad(explode(':',(string)$_COOKIE['lcc_remember'],2),2,'');
  if(!preg_match('/^[a-f0-9]{24}$/',$selector)||!preg_match('/^[a-f0-9]{64}$/',$validator)){self::clearRememberCookie();return;}
  $db=Database::connection();$q=$db->prepare("SELECT rt.id,rt.user_id,rt.role,rt.validator_hash,a.name admin_name,a.email admin_email,a.profile_photo admin_photo,s.name student_name,s.email student_email,s.profile_photo student_photo,s.status student_status FROM remember_tokens rt LEFT JOIN admins a ON rt.role='admin' AND a.id=rt.user_id AND a.deleted_at IS NULL LEFT JOIN students s ON rt.role='student' AND s.id=rt.user_id AND s.deleted_at IS NULL WHERE rt.selector=? AND rt.expires_at>NOW() LIMIT 1");$q->execute([$selector]);$row=$q->fetch();
  $valid=$row&&hash_equals($row['validator_hash'],hash('sha256',$validator))&&($row['role']==='admin'||$row['student_status']==='active');
  if(!$valid){if($row)$db->prepare('DELETE FROM remember_tokens WHERE id=?')->execute([$row['id']]);self::clearRememberCookie();return;}
  $_SESSION['user']=['id'=>(int)$row['user_id'],'role'=>$row['role'],'name'=>$row['role']==='admin'?$row['admin_name']:$row['student_name'],'email'=>$row['role']==='admin'?$row['admin_email']:$row['student_email'],'profile_photo'=>$row['role']==='admin'?$row['admin_photo']:$row['student_photo']];session_regenerate_id(true);
 }
 public static function logout(): void { self::revokeRememberToken();$_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}session_destroy(); }
 public static function require(?string $role=null): array { $u=self::user(); if(!$u) Response::redirect('/cbt/login'); if($role && $u['role']!==$role) throw new HttpException(403,'You do not have permission to access this page.'); return $u; }
 private static function issueRememberToken(array $user): void {$selector=bin2hex(random_bytes(12));$validator=bin2hex(random_bytes(32));$expires=time()+2592000;$db=Database::connection();$db->prepare('DELETE FROM remember_tokens WHERE expires_at<=NOW() OR (user_id=? AND role=?)')->execute([$user['id'],$user['role']]);$db->prepare('INSERT INTO remember_tokens(user_id,role,selector,validator_hash,expires_at,created_at) VALUES(?,?,?,?,FROM_UNIXTIME(?),NOW())')->execute([$user['id'],$user['role'],$selector,hash('sha256',$validator),$expires]);setcookie('lcc_remember',$selector.':'.$validator,['expires'=>$expires,'path'=>'/','secure'=>(($_ENV['APP_ENV']??'production')==='production'),'httponly'=>true,'samesite'=>'Lax']);}
 private static function revokeRememberToken(): void {if(empty($_COOKIE['lcc_remember']))return;$selector=explode(':',(string)$_COOKIE['lcc_remember'],2)[0]??'';if(preg_match('/^[a-f0-9]{24}$/',$selector))Database::connection()->prepare('DELETE FROM remember_tokens WHERE selector=?')->execute([$selector]);self::clearRememberCookie();}
 private static function clearRememberCookie(): void {setcookie('lcc_remember','',['expires'=>time()-3600,'path'=>'/','secure'=>(($_ENV['APP_ENV']??'production')==='production'),'httponly'=>true,'samesite'=>'Lax']);}
}
