<?php
namespace App\Services;
use App\Core\Database;
use PDO;
final class ScoringService {
 public function evaluate(int $attemptId): array {
  $db=Database::connection(); $db->beginTransaction();
  try{
   $q=$db->prepare('SELECT a.*,t.full_marks FROM attempts a JOIN tests t ON t.id=a.test_id WHERE a.id=? FOR UPDATE');$q->execute([$attemptId]);$attempt=$q->fetch();
   if(!$attempt) throw new \RuntimeException('Attempt not found');
   if($attempt['status']==='submitted'){ $db->commit(); return $this->summary($attemptId); }
   $rows=$db->prepare("SELECT q.id,q.type,q.correct_mark,q.negative_mark,q.accepted_text,q.numeric_tolerance,r.typed_answer,r.id response_id FROM questions q LEFT JOIN attempt_responses r ON r.question_id=q.id AND r.attempt_id=? WHERE q.test_id=? ORDER BY q.position");$rows->execute([$attemptId,$attempt['test_id']]);
   $correct=$wrong=$skipped=0;$score=0.0;$negative=0.0;
   foreach($rows->fetchAll() as $row){$is=null;$mark=0.0;$selected=[];
    if($row['response_id']){$s=$db->prepare('SELECT option_id FROM response_options WHERE response_id=? ORDER BY option_id');$s->execute([$row['response_id']]);$selected=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));}
    if($row['type']==='typed'){$answer=trim((string)$row['typed_answer']);if($answer===''){$skipped++;}else{$accepted=json_decode($row['accepted_text']?:'[]',true)?:[];$is=$this->typedCorrect($answer,$accepted,$row['numeric_tolerance']);}}
    else { $k=$db->prepare('SELECT id FROM question_options WHERE question_id=? AND is_correct=1 ORDER BY id');$k->execute([$row['id']]);$key=array_map('intval',$k->fetchAll(PDO::FETCH_COLUMN)); if(!$selected){$skipped++;}else{$is=$selected===$key;} }
    if($is===true){$correct++;$mark=(float)$row['correct_mark'];$score+=$mark;}elseif($is===false){$wrong++;$mark=-(float)$row['negative_mark'];$negative+=abs($mark);$score+=$mark;}
    if($row['response_id']){$u=$db->prepare('UPDATE attempt_responses SET is_correct=?,mark_obtained=? WHERE id=?');$u->execute([$is===null?null:(int)$is,$mark,$row['response_id']]);}
   }
   $score=max(0,$score);$total=$correct+$wrong+$skipped;$accuracy=($correct+$wrong)>0?round($correct/($correct+$wrong)*100,2):0;
   $u=$db->prepare("UPDATE attempts SET status='submitted',submitted_at=NOW(),obtained_marks=?,correct_count=?,wrong_count=?,skipped_count=?,negative_marks=?,accuracy=?,updated_at=NOW() WHERE id=?");$u->execute([$score,$correct,$wrong,$skipped,$negative,$accuracy,$attemptId]);AuditService::log('student',(int)$attempt['student_id'],'test_submitted','Test submitted and scored',['attempt_id'=>$attemptId,'test_id'=>(int)$attempt['test_id'],'score'=>$score]);
   $db->commit(); return compact('score','correct','wrong','skipped','negative','accuracy','total');
  }catch(\Throwable $e){$db->rollBack();throw $e;}
 }
 private function typedCorrect(string $answer,array $accepted,mixed $tolerance): bool { foreach($accepted as $v){if(is_numeric($answer)&&is_numeric($v)&&$tolerance!==null){if(abs((float)$answer-(float)$v)<=(float)$tolerance)return true;}elseif(mb_strtolower(trim($answer))===mb_strtolower(trim((string)$v)))return true;}return false;}
 public function summary(int $attemptId): array {$q=Database::connection()->prepare('SELECT obtained_marks score,correct_count correct,wrong_count wrong,skipped_count skipped,negative_marks negative,accuracy FROM attempts WHERE id=?');$q->execute([$attemptId]);return $q->fetch()?:[];}
}
