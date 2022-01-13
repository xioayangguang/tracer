<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Core;

use Webman\App;
use Xiaoyangguang\Aop\AspectInterface;

class Injection implements AspectInterface
{
    public static function beforeAdvice(&$params, $class, $method): void
    {
    }
    public static function afterAdvice(&$res, $params, $class, $method): void
    {
        array_push($res, [App::container()->get(RootSpan::class), 'process']);
    }
    public static function exceptionHandler($throwable, $params, $class, $method): void
    {
    }
}