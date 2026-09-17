<?php
namespace App\Core;
use PDO;
final class Database {
 private static ?PDO $pdo=null;
 public static function connection(): PDO {
  if(!self::$pdo){$dsn=sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',$_ENV['DB_HOST']??'127.0.0.1',$_ENV['DB_PORT']??'3306',$_ENV['DB_DATABASE']??'lcc_cbt');self::$pdo=new PDO($dsn,$_ENV['DB_USERNAME']??'root',$_ENV['DB_PASSWORD']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);$timezone=new \DateTimeZone($_ENV['APP_TIMEZONE']??'Asia/Kolkata');$offset=$timezone->getOffset(new \DateTimeImmutable('now',$timezone));$sign=$offset<0?'-':'+';$offset=abs($offset);$mysqlOffset=sprintf('%s%02d:%02d',$sign,intdiv($offset,3600),intdiv($offset%3600,60));self::$pdo->prepare('SET time_zone = ?')->execute([$mysqlOffset]);}
  return self::$pdo;
 }
}
