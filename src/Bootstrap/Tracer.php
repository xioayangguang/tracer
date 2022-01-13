<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Bootstrap;

use Webman\Bootstrap;
use Webman\Middleware;
use Xiaoyangguang\Aop\Bootstrap\AopRegister;
use Xiaoyangguang\Tracer\Core\RootSpan;
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
            $tracer = config('plugin.xiaoyangguang.tracer.tracer', []);
            //$tracer[Injection::class] = [Middleware::class => ['getMiddleware']];
            Middleware::load(['' => [RootSpan::class]]);
            AopRegister::appendProxy($tracer);
        }
    }
}