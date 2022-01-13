<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Example;

use Xiaoyangguang\Aop\AspectInterface;
use Xiaoyangguang\Tracer\SpanManage;
use Zipkin\Span;

class GenericAspect implements AspectInterface
{
    /**
     * 前置通知
     * @param $params
     * @param $class
     * @param $method
     */
    public static function beforeAdvice(&$params, $class, $method): void
    {
        //startNextSpan和stopNextSpan 必须一一对应，不能只有startNextSpan没有stopNextSpan
        SpanManage::startNextSpan($class . '::' . $method, function (Span $child_span) use ($params) {
            foreach ($params as $key => $value) {
                $child_span->tag($key, json_encode($value));
            }
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
            $child_span->tag('MethodResult', json_encode($res));
        });
    }

    /**
     * 异常处理
     * @param $throwable
     * @param $params
     * @param $class
     * @param $method
     */
    public static function exceptionHandler($throwable, $params, $class, $method): void
    {
        //异常情况记录信息 并清理调用堆栈
        SpanManage::stopNextSpan(function (Span $child_span) use ($throwable) {
            $child_span->tag('exception.message', $throwable->getMessage());
            $child_span->tag('exception.code', $throwable->getCode());
            $child_span->tag('exception.stacktrace', $throwable->getTraceAsString());
        });
    }
}