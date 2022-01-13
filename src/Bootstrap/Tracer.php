<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Bootstrap;

use Webman\Bootstrap;
use Webman\Middleware;
use Xiaoyangguang\Aop\Bootstrap\AopRegister;
use Xiaoyangguang\Tracer\Core\Injection;
use Xiaoyangguang\Tracer\Core\TracerInitialize;

class Tracer implements Bootstrap
{
    /**
     * @param \Workerman\Worker $worker
     * @return mixed|void
     * @throws \Exception
     */
    public static function start($worker)
    {
        if (TracerInitialize::createTracer()) {
            $tracer = config('tracer');
            //$tracer[Injection::class] = [Middleware::class => ['getMiddleware']];
            AopRegister::appendProxy($tracer);
        }
    }
}