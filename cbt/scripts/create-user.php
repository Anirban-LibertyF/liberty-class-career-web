<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
if(class_exists(Dotenv\Dotenv::class))Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$role=$argv[1]??'';$name=$argv[2]??'';$email=$argv[3]??'';$password=$argv[4]??'';$phone=$argv[5]??'';
if(!in_array($role,['admin','student'],true)||!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<10){fwrite(STDERR,"Usage: php scripts/create-user.php admin|student \"Full Name\" email password [phone]\nPassword must be 10+ characters.\n");exit(1);}
$db=App\Core\Database::connection();$hash=password_hash($password,PASSWORD_DEFAULT);
if($role==='admin'){$q=$db->prepare('INSERT INTO admins(name,email,password_hash) VALUES(?,?,?)');$q->execute([$name,$email,$hash]);}
else{if(!$phone){fwrite(STDERR,"Student phone is required.\n");exit(1);}$q=$db->prepare('INSERT INTO students(name,email,phone,password_hash) VALUES(?,?,?,?)');$q->execute([$name,$email,$phone,$hash]);}
fwrite(STDOUT,ucfirst($role)." created successfully.\n");
