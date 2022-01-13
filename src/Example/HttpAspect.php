<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Example;

use Xiaoyangguang\Tracer\SpanManage;
use Zipkin\Endpoint;
use Zipkin\Span;

//业务不同，组件不同 无法统一
class HttpAspect extends GenericAspect
{
    use AopTrait;

    /**
     * 前置通知
     * @param $params
     * @param $class
     * @param $method
     */
    public static function beforeAdvice(&$params, $class, $method): void
    {
        SpanManage::startNextSpan("Http::{$class}::{$method}", function (Span $child_span) use ($params) {
            foreach ($params as $key => $value) {
                $child_span->tag($key, json_encode($value));
            }
            $child_span->setRemoteEndpoint(Endpoint::create(
                self::$service_name ?: 'Http',
                self::$ipv4 ?: '127.0.0.1',
                self::$ipv6,
                self::$port ?: 80
            ));
        });
    }
}