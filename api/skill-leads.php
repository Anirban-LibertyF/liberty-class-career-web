<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_post();
require_csrf();

$data = input_json();
$studentName = clean($data['studentName'] ?? '', 120);
$phone = preg_replace('/[^0-9+]/', '', clean($data['phone'] ?? '', 20));
$skillId = max(0, (int) ($data['skillId'] ?? 0));
$accessType = clean($data['accessType'] ?? '', 20);
if ($studentName === '' || $skillId < 1 || !preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
    json_response(['success'=>false,'message'=>'Enter a valid name and phone number.'],422);
}
if (!in_array($accessType,['full_bundle','video','material'],true)) {
    json_response(['success'=>false,'message'=>'Select a valid access plan.'],422);
}

$base = rtrim((string) (getenv('LCC_ADMIN_API_BASE_URL') ?: ($_ENV['LCC_ADMIN_API_BASE_URL'] ?? '')), '/');
$token = (string) (getenv('SKILL_LEAD_API_TOKEN') ?: ($_ENV['SKILL_LEAD_API_TOKEN'] ?? ''));
if ($base === '' || $token === '') {
    json_response(['success'=>false,'message'=>'Enrollment service is not configured.'],503);
}
$payload = json_encode(['skill_id'=>$skillId,'student_name'=>$studentName,'phone'=>$phone,'access_type'=>$accessType],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
$context = stream_context_create(['http'=>[
    'method'=>'POST','timeout'=>5,'ignore_errors'=>true,
    'header'=>"Content-Type: application/json\r\nAccept: application/json\r\nX-Skill-Lead-Token: $token\r\nConnection: close\r\n",
    'content'=>$payload,
]]);
$raw = @file_get_contents($base.'/skill_lead_create.php',false,$context);
$admin = is_string($raw) ? json_decode($raw,true) : null;
if (!is_array($admin) || ($admin['status']??'')!=='success') {
    json_response(['success'=>false,'message'=>'Unable to submit your interest right now.'],502);
}
$lead = $admin['data'] ?? [];
$labels=['full_bundle'=>'Full Bundle','video'=>'Videos Only','material'=>'Materials Only'];
$amount=(float)($lead['amount']??0);
$message="New Skill Training Lead\n\nStudent Name: $studentName\nPhone: $phone\nInterested Course: ".($lead['course']??'')."\nPlan: ".$labels[$accessType]."\nAmount: ".($amount>0?'₹'.number_format($amount,2,'.',''):'Free');
$whatsapp=preg_replace('/\D/','',(string)(getenv('SKILL_LEAD_WHATSAPP_NUMBER')?:($_ENV['SKILL_LEAD_WHATSAPP_NUMBER']??'918276015376')));
json_response(['success'=>true,'message'=>'Your interest was submitted. Continue in WhatsApp to send the message.','whatsapp_url'=>'https://wa.me/'.$whatsapp.'?text='.rawurlencode($message)],201);
