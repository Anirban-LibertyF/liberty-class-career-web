<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_post();
require_csrf();

$data = input_json();
$studentName = clean($data['studentName'] ?? '', 120);
$phone = clean($data['phone'] ?? '', 20);
$email = clean($data['email'] ?? '', 190);
$interestedIn = clean($data['interestedIn'] ?? '', 120);

if ($studentName === '' || $phone === '' || $interestedIn === '') {
    json_response(['success' => false, 'message' => 'Student name, phone number and interest are required.'], 422);
}
if (!preg_match('/^[0-9+() -]{7,20}$/', $phone)) {
    json_response(['success' => false, 'message' => 'Please enter a valid phone number.'], 422);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
}

try {
    $statement = db()->prepare(
        'INSERT INTO contact_inquiries
        (student_name, phone, email, address, qualification, institute, interested_in, message, status)
        VALUES (:student_name, :phone, :email, :address, :qualification, :institute, :interested_in, :message, "New")'
    );
    $statement->execute([
        ':student_name' => $studentName,
        ':phone' => $phone,
        ':email' => $email,
        ':address' => clean($data['address'] ?? '', 300),
        ':qualification' => clean($data['qualification'] ?? '', 180),
        ':institute' => clean($data['institute'] ?? '', 180),
        ':interested_in' => $interestedIn,
        ':message' => clean($data['message'] ?? '', 1500),
    ]);
    json_response(['success' => true, 'message' => 'Your request has been submitted.', 'id' => (int) db()->lastInsertId()], 201);
} catch (Throwable $error) {
    error_log($error->getMessage());
    json_response(['success' => false, 'message' => 'Unable to submit right now. Please try again.'], 500);
}
