<?php
namespace App\Core;
final class HttpException extends \RuntimeException { public function __construct(public int $status,string $message){parent::__construct($message);} }
