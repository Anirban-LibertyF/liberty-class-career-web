<?php
namespace App\Core;
use Throwable;
final class App {
 private Router $router;
 private function __construct(private string $root){$this->router=new Router();}
 public static function boot(string $root): self {$app=new self($root);date_default_timezone_set($_ENV['APP_TIMEZONE']??'Asia/Kolkata');ini_set('session.use_strict_mode','1');ini_set('session.cookie_httponly','1');ini_set('session.cookie_samesite','Lax');if(($_ENV['APP_ENV']??'production')==='production')ini_set('session.cookie_secure','1');session_name($_ENV['SESSION_NAME']??'lcc_cbt_session');session_start();Auth::restoreRemembered();foreach(require $root.'/app/Config/routes.php' as $route)$app->router->add(...$route);return $app;}
 public function run(): void {
  try{$this->router->dispatch($_SERVER['REQUEST_METHOD'],parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/');}
  catch(HttpException $e){$this->handle($e->getMessage(),$e->status);}
  catch(Throwable $e){error_log($e);$this->handle(($_ENV['APP_DEBUG']??'false')==='true'?$e->getMessage():'Something went wrong. Please try again.',500);}
 }
 private function handle(string $message,int $status): never {
  $uri=$_SERVER['REQUEST_URI']??'/';if(str_starts_with($uri,'/cbt/api/'))Response::error($message,$status);
  if(($_SERVER['REQUEST_METHOD']??'GET')==='POST'){$_SESSION['_flash']['error']=$message;$_SESSION['_old']=array_filter($_POST,static fn($key)=>$key!=='_csrf',ARRAY_FILTER_USE_KEY);$referer=$_SERVER['HTTP_REFERER']??'/';$target=parse_url($referer,PHP_URL_PATH)?:'/';if(!str_starts_with($target,'/'))$target='/';Response::redirect($target);}
  Response::error($message,$status);
 }
}
