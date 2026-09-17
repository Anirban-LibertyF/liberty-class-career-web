<?php
namespace App\Services;

use App\Core\Database;

final class AuditService
{
    public static function log(string $actorType,?int $actorId,string $type,string $message,array $context=[]): void
    {
        if(!in_array($actorType,['admin','student','system'],true))$actorType='system';
        $q=Database::connection()->prepare('INSERT INTO audit_logs(actor_type,actor_id,type,message,context,created_at) VALUES(?,?,?,?,?,NOW())');
        $q->execute([$actorType,$actorId,$type,mb_substr($message,0,255),$context?json_encode($context,JSON_UNESCAPED_SLASHES):null]);
    }
}
