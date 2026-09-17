<?php
namespace App\Controllers;

use App\Core\{Auth,Database,HttpException,Response,View};

final class PublicController
{
    public function trending(): void
    {
        $weekStart = "DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        $sql = "SELECT t.id,t.title,t.exam_name,t.category,t.total_questions,t.full_marks,t.duration_minutes,t.fee,t.starts_at,t.ends_at,t.thumbnail_path,s.name subject,(SELECT COUNT(*) FROM enrollments e WHERE e.test_id=t.id AND e.status='active' AND e.enrolled_at>={$weekStart}) week_enrollments FROM tests t JOIN subjects s ON s.id=t.subject_id WHERE t.status='published' AND t.deleted_at IS NULL AND t.ends_at>NOW() ORDER BY week_enrollments DESC,t.starts_at ASC LIMIT 4";
        $tests = Database::connection()->query($sql)->fetchAll();
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=120');
        Response::json(['ok'=>true,'tests'=>array_map(static function(array $test): array {
            return [
                'id'=>(int)$test['id'],'title'=>$test['title'],'exam'=>$test['exam_name'],'category'=>$test['category'],'subject'=>$test['subject'],
                'questions'=>(int)$test['total_questions'],'marks'=>(float)$test['full_marks'],'duration'=>(int)$test['duration_minutes'],'fee'=>(float)$test['fee'],
                'starts_at'=>$test['starts_at'],'ends_at'=>$test['ends_at'],'week_enrollments'=>(int)$test['week_enrollments'],
                'thumbnail'=>$test['thumbnail_path']?'/cbt/media/'.$test['thumbnail_path']:null,'url'=>'/cbt/test/'.(int)$test['id'],
            ];
        },$tests)]);
    }

    public function catalogue(): void
    {
        $db = Database::connection();
        $search = trim((string) ($_GET['q'] ?? ''));
        $subject = (int) ($_GET['subject'] ?? 0);
        $exam = trim((string) ($_GET['exam'] ?? ''));
        $where = ["t.status='published'", 't.deleted_at IS NULL', 't.ends_at>NOW()'];
        $params = [];
        if ($search !== '') {
            $where[] = '(t.title LIKE ? OR t.exam_name LIKE ? OR t.category LIKE ? OR s.name LIKE ?)';
            $term = '%' . $search . '%';
            array_push($params, $term, $term, $term, $term);
        }
        if ($subject > 0) { $where[] = 't.subject_id=?'; $params[] = $subject; }
        if ($exam !== '') { $where[] = 't.exam_name=?'; $params[] = $exam; }
        $user = Auth::user();
        $studentId = $user && $user['role'] === 'student' ? Auth::id() : 0;
        $sql = "SELECT t.*,s.name subject,e.status enrollment_status,a.id attempt_id,a.status attempt_status FROM tests t JOIN subjects s ON s.id=t.subject_id LEFT JOIN enrollments e ON e.test_id=t.id AND e.student_id=? LEFT JOIN attempts a ON a.id=(SELECT MAX(a2.id) FROM attempts a2 WHERE a2.test_id=t.id AND a2.student_id=?) WHERE " . implode(' AND ', $where) . ' ORDER BY t.starts_at LIMIT 100';
        $query = $db->prepare($sql); $query->execute([$studentId, $studentId, ...$params]); $tests = $query->fetchAll();
        $subjects = $db->query("SELECT DISTINCT s.id,s.name FROM subjects s JOIN tests t ON t.subject_id=s.id WHERE t.status='published' AND t.deleted_at IS NULL AND t.ends_at>NOW() ORDER BY s.name")->fetchAll();
        $exams = $db->query("SELECT DISTINCT exam_name FROM tests WHERE status='published' AND deleted_at IS NULL AND ends_at>NOW() ORDER BY exam_name")->fetchAll(\PDO::FETCH_COLUMN);
        View::render('public/catalogue', compact('tests','subjects','exams','search','subject','exam') + ['title'=>'CBT Exams']);
    }

    public function details(string $id): void
    {
        $query = Database::connection()->prepare("SELECT t.*,s.name subject FROM tests t JOIN subjects s ON s.id=t.subject_id WHERE t.id=? AND t.status='published' AND t.deleted_at IS NULL");
        $query->execute([(int) $id]); $test = $query->fetch();
        if (!$test) throw new HttpException(404, 'Test not found.');
        View::render('public/details', compact('test') + ['title'=>$test['title']]);
    }
}
