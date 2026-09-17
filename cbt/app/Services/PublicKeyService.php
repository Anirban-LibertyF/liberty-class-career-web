<?php
namespace App\Services;
final class PublicKeyService {
 private const ALPHABET='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
 public static function generate(\PDO $db,string $table): string {
  if(!in_array($table,['tests','questions'],true))throw new \InvalidArgumentException('Unsupported public-key table.');
  for($attempt=0;$attempt<20;$attempt++){$key='';for($i=0;$i<8;$i++)$key.=self::ALPHABET[random_int(0,strlen(self::ALPHABET)-1)];$q=$db->prepare("SELECT 1 FROM {$table} WHERE unique_key=? LIMIT 1");$q->execute([$key]);if(!$q->fetchColumn())return $key;}
  throw new \RuntimeException('Could not generate a unique record key.');
 }
}
