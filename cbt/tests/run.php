<?php
declare(strict_types=1);
$root=dirname(__DIR__);$fail=[];
$required=['app/Config/routes.php','app/Controllers/AdminApiController.php','app/Controllers/PublicController.php','app/Controllers/StudentController.php','app/Controllers/ExamController.php','app/Controllers/ApiController.php','app/Services/PaymentService.php','app/Services/AttemptService.php','app/Views/layouts/app.php','database/schema.sql','database/migrations/008_external_admin_api.sql','public/assets/css/app.css','public/assets/js/app.js'];
foreach($required as $file)if(!is_file($root.'/'.$file))$fail[]="Missing $file";
$routes=file_get_contents($root.'/app/Config/routes.php');
foreach(["'/cbt'","'/cbt/login'","'/cbt/dashboard'","'/cbt/api/admin/health'","'/cbt/api/admin/tests/sync'","'/cbt/api/attempts/{id}/submit'"] as $needle)if(!str_contains($routes,$needle))$fail[]="Route missing $needle";
if(str_contains($routes,"'/admin")||is_file($root.'/app/Controllers/AdminController.php')||is_dir($root.'/app/Views/admin'))$fail[]='CBT admin UI/routes must not be shipped';
$auth=file_get_contents($root.'/app/Core/Auth.php');if(str_contains($auth,"'admin' role"))$fail[]='Interactive login must be student-only';foreach(["'/cbt/login'",'remember_tokens','password_verify'] as $needle)if(!str_contains($auth,$needle))$fail[]="Auth check missing $needle";
$bridge=file_get_contents($root.'/app/Controllers/AdminApiController.php');foreach(['CBT_ADMIN_API_TOKEN','hash_equals','beginTransaction','external_id','attempts','HttpException(409','rollBack'] as $needle)if(!str_contains($bridge,$needle))$fail[]="Admin bridge check missing $needle";
$schema=file_get_contents($root.'/database/schema.sql');foreach(['external_id VARCHAR(100)','UNIQUE(attempt_id,question_id)','deadline_at','payments','remember_tokens'] as $needle)if(!str_contains($schema,$needle))$fail[]="Schema missing $needle";
$layout=file_get_contents($root.'/app/Views/layouts/app.php');foreach(['/cbt/assets/css/app.css','/cbt/assets/js/app.js','/cbt/dashboard','/cbt/my-tests','/cbt/tests'] as $needle)if(!str_contains($layout,$needle))$fail[]="Unified layout check missing $needle";
$payment=file_get_contents($root.'/app/Services/PaymentService.php');foreach(['hash_hmac','hash_equals','captured','RAZORPAY_KEY_SECRET'] as $needle)if(!str_contains($payment,$needle))$fail[]="Payment verification missing $needle";
$attempt=file_get_contents($root.'/app/Services/AttemptService.php');foreach(['remainingAtPause','resumedDeadline','FROM_UNIXTIME(?)'] as $needle)if(!str_contains($attempt,$needle))$fail[]="Resume check missing $needle";
$js=file_get_contents($root.'/public/assets/js/app.js');foreach(['syncAll(true)','openRazorpay','/cbt/my-tests'] as $needle)if(!str_contains($js,$needle))$fail[]="Client flow missing $needle";
if($fail){fwrite(STDERR,implode(PHP_EOL,$fail).PHP_EOL);exit(1);}fwrite(STDOUT,"Unified CBT static architecture checks passed.\n");
