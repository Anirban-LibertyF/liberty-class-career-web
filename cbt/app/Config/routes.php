<?php
use App\Controllers\{AuthController,StudentController,ExamController,ApiController,AdminApiController,MediaController,HealthController,PublicController};
return [
 ['GET','/cbt/health',[HealthController::class,'show']],['GET','/cbt/api/public/trending',[PublicController::class,'trending']],
 ['GET','/cbt',[PublicController::class,'catalogue']],['GET','/cbt/catalogue',[PublicController::class,'catalogue']],['GET','/cbt/test/{id}',[PublicController::class,'details']],
 ['GET','/cbt/login',[AuthController::class,'login']],['POST','/cbt/login',[AuthController::class,'authenticate']],['POST','/cbt/logout',[AuthController::class,'logout']],
 ['GET','/cbt/dashboard',[StudentController::class,'dashboard']],['GET','/cbt/tests',[StudentController::class,'tests']],['GET','/cbt/my-tests',[StudentController::class,'myTests']],
 ['POST','/cbt/test/{id}/enroll',[StudentController::class,'enroll']],['POST','/cbt/api/cbt/tests/{id}/enroll',[StudentController::class,'enroll']],
 ['GET','/cbt/payment/{id}',[StudentController::class,'payment']],['POST','/cbt/payment/{id}/complete',[StudentController::class,'completePayment']],['POST','/cbt/api/payments/{id}/complete',[StudentController::class,'completePayment']],['POST','/cbt/profile-photo',[StudentController::class,'profilePhoto']],
 ['GET','/cbt/api/admin/health',[AdminApiController::class,'health']],['POST','/cbt/api/admin/tests/sync',[AdminApiController::class,'sync']],
 ['GET','/cbt/exam/{id}/instructions',[ExamController::class,'instructions']],['POST','/cbt/exam/{id}/start',[ExamController::class,'start']],['GET','/cbt/attempt/{id}',[ExamController::class,'attempt']],['GET','/cbt/result/{id}',[ExamController::class,'result']],['GET','/cbt/result/{id}/pdf',[ExamController::class,'pdf']],
 ['POST','/cbt/api/attempts/{id}/answer',[ApiController::class,'saveAnswer']],['POST','/cbt/api/attempts/{id}/position',[ApiController::class,'savePosition']],['POST','/cbt/api/attempts/{id}/submit',[ApiController::class,'submit']],['GET','/cbt/api/attempts/{id}/state',[ApiController::class,'state']],['GET','/cbt/media/{folder}/{name}',[MediaController::class,'show']],
];
