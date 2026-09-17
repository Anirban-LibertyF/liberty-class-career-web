<?php
namespace App\Core;
final class View {
 public static function render(string $view,array $data=[]): void { extract($data,EXTR_SKIP);$viewFile=dirname(__DIR__).'/Views/'.$view.'.php';require dirname(__DIR__).'/Views/layouts/app.php'; }
 public static function partial(string $view,array $data=[]): void {extract($data,EXTR_SKIP);require dirname(__DIR__).'/Views/'.$view.'.php';}
}
