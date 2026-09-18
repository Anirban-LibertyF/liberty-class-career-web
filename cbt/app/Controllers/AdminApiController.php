<?php
namespace App\Controllers;

use App\Core\{Database,HttpException,Response};
use App\Services\UploadService;

final class AdminApiController
{
    public function health(): void
    {
        $this->authorize();
        Database::connection()->query('SELECT 1');
        Response::json(['ok'=>true,'service'=>'lcc-cbt-admin-bridge']);
    }

    public function sync(): void
    {
        $this->authorize();
        $payload=json_decode((string)file_get_contents('php://input'),true);
        if(!is_array($payload)) throw new HttpException(400,'A valid JSON payload is required.');
        foreach(['external_id','title','exam_name','category','subject','full_marks','duration_minutes','correct_mark','starts_at','ends_at','fee','status','questions'] as $field){
            if(!array_key_exists($field,$payload)) throw new HttpException(422,'Missing field: '.$field);
        }
        if(!preg_match('/^[A-Za-z0-9._:-]{1,100}$/',(string)$payload['external_id'])) throw new HttpException(422,'Invalid external_id.');
        if(!in_array($payload['status'],['draft','published','archived'],true)||!is_array($payload['questions'])||count($payload['questions'])<1) throw new HttpException(422,'Invalid test status or questions.');
        $thumbnailProvided=array_key_exists('thumbnail',$payload);$newThumbnail=null;$oldThumbnail=null;
        if($thumbnailProvided&&$payload['thumbnail']!==null){
            if(!is_string($payload['thumbnail'])||$payload['thumbnail']==='') throw new HttpException(422,'Thumbnail must be an image data URL or null.');
            $newThumbnail=(new UploadService())->dataImage($payload['thumbnail'],'tests');
        }
        $db=Database::connection();$db->beginTransaction();
        try{
            $subject=trim((string)$payload['subject']);$slug=strtolower(trim((string)preg_replace('/[^a-z0-9]+/i','-',$subject),'-'));
            $db->prepare('INSERT INTO subjects(name,slug,is_active) VALUES(?,?,1) ON DUPLICATE KEY UPDATE is_active=1')->execute([$subject,$slug]);
            $q=$db->prepare('SELECT id FROM subjects WHERE name=?');$q->execute([$subject]);$subjectId=(int)$q->fetchColumn();
            $adminId=(int)($_ENV['CBT_SYNC_ADMIN_ID']??1);
            $q=$db->prepare('SELECT id,thumbnail_path FROM tests WHERE external_id=? FOR UPDATE');$q->execute([(string)$payload['external_id']]);$existing=$q->fetch();$testId=(int)($existing['id']??0);$oldThumbnail=(string)($existing['thumbnail_path']??'');
            $published=$payload['status']==='published'?date('Y-m-d H:i:s'):null;
            $values=[(string)$payload['title'],(string)$payload['exam_name'],(string)$payload['category'],$subjectId,(float)$payload['full_marks'],count($payload['questions']),(int)$payload['duration_minutes'],(float)$payload['correct_mark'],(float)($payload['negative_mark']??0),(string)$payload['starts_at'],(string)$payload['ends_at'],(float)$payload['fee'],(string)$payload['status'],$published];
            if($testId){$q=$db->prepare('SELECT COUNT(*) FROM attempts WHERE test_id=?');$q->execute([$testId]);if((int)$q->fetchColumn()>0)throw new HttpException(409,'A test with attempts cannot have its question set replaced.');$thumbnail=$thumbnailProvided?$newThumbnail:$oldThumbnail;$db->prepare('UPDATE tests SET title=?,exam_name=?,category=?,thumbnail_path=?,subject_id=?,full_marks=?,total_questions=?,duration_minutes=?,correct_mark=?,negative_mark=?,starts_at=?,ends_at=?,fee=?,status=?,published_at=?,updated_at=NOW(),deleted_at=NULL WHERE id=?')->execute([$values[0],$values[1],$values[2],$thumbnail,...array_slice($values,3),$testId]);$db->prepare('DELETE FROM questions WHERE test_id=?')->execute([$testId]);}
            else{$key=strtoupper(substr(hash('sha256',(string)$payload['external_id']),0,8));$db->prepare('INSERT INTO tests(external_id,unique_key,title,exam_name,category,thumbnail_path,subject_id,full_marks,total_questions,duration_minutes,correct_mark,negative_mark,starts_at,ends_at,fee,status,created_by,published_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')->execute([(string)$payload['external_id'],$key,$values[0],$values[1],$values[2],$newThumbnail,...array_slice($values,3,10),$adminId,$published]);$testId=(int)$db->lastInsertId();}
            foreach(array_values($payload['questions']) as $index=>$question){
                $type=(string)($question['type']??'single');if(!in_array($type,['single','multiple','typed'],true))throw new HttpException(422,'Invalid question type.');
                $qKey=strtoupper(substr(hash('sha256',$payload['external_id'].':'.$index),0,8));
                $db->prepare('INSERT INTO questions(unique_key,test_id,position,type,question_text,correct_mark,negative_mark,accepted_text,numeric_tolerance,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,NOW(),NOW())')->execute([$qKey,$testId,$index+1,$type,(string)($question['text']??''),(float)($question['correct_mark']??$payload['correct_mark']),(float)($question['negative_mark']??($payload['negative_mark']??0)),isset($question['accepted_text'])?json_encode($question['accepted_text']):null,$question['numeric_tolerance']??null]);
                $questionId=(int)$db->lastInsertId();foreach(($question['options']??[]) as $option){$db->prepare('INSERT INTO question_options(question_id,option_key,option_text,is_correct) VALUES(?,?,?,?)')->execute([$questionId,(string)$option['key'],(string)($option['text']??''),!empty($option['is_correct'])?1:0]);}
            }
            $db->commit();
            if($thumbnailProvided&&$oldThumbnail!==''&&$oldThumbnail!==$newThumbnail)(new UploadService())->delete($oldThumbnail);
            Response::json(['ok'=>true,'test_id'=>$testId,'external_id'=>$payload['external_id'],'thumbnail'=>$thumbnailProvided?($newThumbnail?'/cbt/media/'.$newThumbnail:null):($oldThumbnail?'/cbt/media/'.$oldThumbnail:null)]);
        }catch(\Throwable $e){if($db->inTransaction())$db->rollBack();if($newThumbnail)(new UploadService())->delete($newThumbnail);throw $e;}
    }

    private function authorize(): void
    {
        $expected=(string)($_ENV['CBT_ADMIN_API_TOKEN']??'');$header=(string)($_SERVER['HTTP_AUTHORIZATION']??'');
        if($expected===''||!str_starts_with($header,'Bearer ')||!hash_equals($expected,substr($header,7))) throw new HttpException(401,'Unauthorized.');
    }
}
