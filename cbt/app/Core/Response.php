<?php
namespace App\Core;
final class Response {
 public static function json(array $data,int $status=200): never {http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data,JSON_UNESCAPED_SLASHES);exit;}
 public static function redirect(string $url): never {header('Location: '.$url);exit;}
 public static function error(string $message,int $status): never {
  if(str_starts_with($_SERVER['REQUEST_URI']??'','/cbt/api/'))self::json(['ok'=>false,'error'=>['code'=>self::errorCode($status),'message'=>$message,'fields'=>(object)[]]],$status);
  http_response_code($status);$safe=htmlspecialchars($message,ENT_QUOTES,'UTF-8');$title=$status>=500?'We could not complete that action':'Please check this action';
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Notice | LCC CBT</title><style>body{margin:0;font-family:Arial,sans-serif;background:#fff9ef;color:#191919}.bar{height:8px;background:linear-gradient(90deg,#c9342e,#f26a3d,#d9a900)}main{min-height:calc(100vh - 8px);display:grid;place-items:start center;padding:24px}.box{max-width:620px;background:#fff;border:1px solid #f2c8a9;border-left:6px solid #f26a3d;border-radius:18px;padding:28px;box-shadow:0 18px 50px #7e28151f}.icon{width:52px;height:52px;display:grid;place-items:center;border-radius:50%;background:#fff0d8;color:#b54320;font-size:28px;font-weight:800}h1{font-size:25px;margin:18px 0 10px}p{line-height:1.6}.actions{display:flex;gap:12px;margin-top:22px;flex-wrap:wrap}a{padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:700;background:#c9342e;color:white}.secondary{background:#fff3e7;color:#9f2d28}</style></head><body><div class="bar"></div><main><section class="box" role="alert"><div class="icon">!</div><h1>'.htmlspecialchars($title,ENT_QUOTES,'UTF-8').'</h1><p>'.$safe.'</p><div class="actions"><a href="javascript:history.back()">OK</a></div></section></main></body></html>';exit;
 }
 private static function errorCode(int $status): string{return match($status){401=>'UNAUTHENTICATED',403=>'FORBIDDEN',404=>'NOT_FOUND',409=>'STATE_CONFLICT',419=>'CSRF_EXPIRED',422=>'VALIDATION_ERROR',429=>'RATE_LIMITED',default=>'SERVER_ERROR'};}
}
