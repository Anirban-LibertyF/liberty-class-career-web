<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_post();
require_csrf();

$data = input_json();
$studentName = clean($data['studentName'] ?? '', 120);
$courseName = clean($data['courseName'] ?? '', 180);
$accessPlan = clean($data['accessPlan'] ?? '', 120);
$amount = clean($data['amount'] ?? '', 40);

if ($studentName === '' || $courseName === '' || $accessPlan === '' || $amount === '') {
    json_response(['success' => false, 'message' => 'Student, course, plan and amount are required.'], 422);
}

try {
    $statement = db()->prepare(
        'INSERT INTO course_enrollments
        (student_name, phone, email, course_name, access_plan, amount, status)
        VALUES (:student_name, :phone, :email, :course_name, :access_plan, :amount, "Pending")'
    );
    $statement->execute([
        ':student_name' => $studentName,
        ':phone' => clean($data['phone'] ?? '', 20),
        ':email' => clean($data['email'] ?? '', 190),
        ':course_name' => $courseName,
        ':access_plan' => $accessPlan,
        ':amount' => $amount,
    ]);
    json_response(['success' => true, 'message' => 'Enrollment request submitted.', 'id' => (int) db()->lastInsertId()], 201);
} catch (Throwable $error) {
    error_log($error->getMessage());
    json_response(['success' => false, 'message' => 'Unable to submit enrollment right now.'], 500);
}
