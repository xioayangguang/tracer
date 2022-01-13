<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Example;

use Xiaoyangguang\Tracer\SpanManage;
use Zipkin\Endpoint;
use Zipkin\Span;

class RedisAspect extends GenericAspect
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
        SpanManage::startNextSpan("Redis::{$class}::{$method}", function (Span $child_span) use ($params) {
            if (isset($params['name']) and isset($params['arguments'])) {
                $child_span->tag($params['name'], json_encode($params['arguments']));
            }
            $child_span->setRemoteEndpoint(Endpoint::create(
                self::$service_name ?: 'Redis',
                self::$ipv4 ?: '127.0.0.1',
                self::$ipv6,
                self::$port ?: 6379
            ));
        });
    }

    /**
     * 后置通知
     * @param $res
     * @param $params
     * @param $class
     * @param $method
     */
    public static function afterAdvice(&$res, $params, $class, $method): void
    {
        SpanManage::stopNextSpan(function (Span $child_span) use ($params, $res) {
            $child_span->tag('Result', json_encode($res));
        });
    }
}