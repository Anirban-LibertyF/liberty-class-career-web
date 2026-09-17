<?php
namespace App\Services;
use App\Core\{Auth,Database,HttpException};
final class AttemptService {
 public function start(int $testId): int {
  $db=Database::connection();$db->beginTransaction();try{
   $q=$db->prepare("SELECT t.* FROM tests t JOIN enrollments e ON e.test_id=t.id AND e.student_id=? AND e.status='active' WHERE t.id=? AND t.status='published' AND NOW() BETWEEN t.starts_at AND t.ends_at FOR UPDATE");$q->execute([Auth::id(),$testId]);$test=$q->fetch();if(!$test)throw new HttpException(403,'This test is not available to start.');
   $q=$db->prepare("SELECT id,status,deadline_at,last_seen_at FROM attempts WHERE student_id=? AND test_id=? ORDER BY id DESC LIMIT 1 FOR UPDATE");$q->execute([Auth::id(),$testId]);$a=$q->fetch();
   if($a && $a['status']==='in_progress'){
    $now=time();$lastSeen=strtotime($a['last_seen_at']);$remainingAtPause=strtotime($a['deadline_at'])-$lastSeen;$testEnd=strtotime($test['ends_at']);
    if($lastSeen>=$now-1800&&$remainingAtPause>0&&$testEnd>$now){$resumedDeadline=min($testEnd,$now+$remainingAtPause);$db->prepare('UPDATE attempts SET deadline_at=FROM_UNIXTIME(?),last_seen_at=NOW(),updated_at=NOW() WHERE id=?')->execute([$resumedDeadline,$a['id']]);$db->commit();return (int)$a['id'];}
    $staleId=(int)$a['id'];$db->commit();(new ScoringService())->evaluate($staleId);throw new HttpException(409,'The 30-minute resume window or scheduled test end has expired. Saved answers were finalized.');
   }
   if($a && $a['status']==='submitted')throw new HttpException(409,'This test has already been submitted.');
   $deadline=min(strtotime($test['ends_at']),time()+(int)$test['duration_minutes']*60);
   $q=$db->prepare("INSERT INTO attempts(student_id,test_id,status,started_at,deadline_at,last_seen_at,current_question_position,created_at,updated_at) VALUES(?,?,'in_progress',NOW(),FROM_UNIXTIME(?),NOW(),1,NOW(),NOW())");$q->execute([Auth::id(),$testId,$deadline]);$id=(int)$db->lastInsertId();$db->commit();return $id;
  }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
 }
 public function owned(int $id,bool $mustBeActive=false): array {$db=Database::connection();$q=$db->prepare('SELECT a.*,t.title,t.full_marks,t.ends_at FROM attempts a JOIN tests t ON t.id=a.test_id WHERE a.id=? AND a.student_id=?');$q->execute([$id,Auth::id()]);$a=$q->fetch();if(!$a)throw new HttpException(404,'Attempt not found.');if($mustBeActive&&$a['status']!=='in_progress')throw new HttpException(409,'Attempt is already finalized.');if($mustBeActive&&strtotime($a['last_seen_at'])<time()-1800){(new ScoringService())->evaluate($id);throw new HttpException(409,'The 30-minute resume window has expired. Saved answers were finalized.');}if($mustBeActive)$db->prepare('UPDATE attempts SET last_seen_at=NOW() WHERE id=?')->execute([$id]);return $a;}
 public function assertOpen(array $attempt): void {if(strtotime($attempt['deadline_at'])<=time()){(new ScoringService())->evaluate((int)$attempt['id']);throw new HttpException(409,'Exam time has ended. Your saved answers were submitted.');}}
}
