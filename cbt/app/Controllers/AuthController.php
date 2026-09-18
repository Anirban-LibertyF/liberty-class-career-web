<?php
namespace App\Controllers;
use App\Core\{Auth,Csrf,RateLimiter,Response,View};
final class AuthController {
 public function login(): void { $return=(string)($_GET['return']??'');if($return!==''&&str_starts_with($return,'/cbt/')&&!str_starts_with($return,'//'))$_SESSION['_login_return']=$return;if(Auth::user())Response::redirect($_SESSION['_login_return']??'/cbt/dashboard');View::render('auth/login',['title'=>'Secure Login']); }
 public function authenticate(): void {Csrf::verify();$identity=trim($_POST['identity']??'');$homeLogin=($_POST['login_context']??'')==='home'&&($_POST['return']??'')==='/';RateLimiter::hit('login',($_SERVER['REMOTE_ADDR']??'').'|'.$identity,5,900);if(!Auth::login($identity,(string)($_POST['password']??''),!empty($_POST['remember']))){$_SESSION['_flash']['error']='Invalid ID or password.';$_SESSION['_old']['identity']=$identity;Response::redirect($homeLogin?'/?login=1':'/cbt/login');}$target=$homeLogin?'/':($_SESSION['_login_return']??'/cbt/dashboard');unset($_SESSION['_login_return']);Response::redirect($target);}
 public function logout(): void {Csrf::verify();$return=($_POST['return']??'')==='/'?'/':'/cbt/login';Auth::logout();Response::redirect($return);}
}
