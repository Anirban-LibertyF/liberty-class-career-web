<?php
namespace App\Core;

final class Router {
 private array $routes=[];
 public function add(string $method,string $path,array $handler): void { $this->routes[]=[$method,$path,$handler]; }
 public function dispatch(string $method,string $uri): void {
   if ($method==='POST' && isset($_POST['_method'])) $method=strtoupper((string)$_POST['_method']);
   foreach($this->routes as [$verb,$path,$handler]){
     if($verb!==$method) continue;
     $regex='#^'.preg_replace('/\{([a-zA-Z_]+)\}/','(?P<$1>[^/]+)',$path).'/?$#';
     if(preg_match($regex,$uri,$m)){ $args=array_filter($m,'is_string',ARRAY_FILTER_USE_KEY); $controller=new $handler[0](); $controller->{$handler[1]}(...array_values($args)); return; }
   }
   throw new HttpException(404,'Page not found.');
 }
}
