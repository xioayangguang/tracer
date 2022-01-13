<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer;

use Webman\Http\Response;
use Xiaoyangguang\Tracer\core\TracerInitialize;
use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Span;
use const Zipkin\Kind\CLIENT;
use const Zipkin\Kind\SERVER;

class SpanManage
{
    /**
     * @var array
     */
    private static $span_stack = [];

    /**
     * @var bool
     */
    private static $initialization = false;

    /**
     * 创建子span（必须和stopNextSpan一一对应）
     * @param string $span_name
     * @param callable|null $callable
     * @return void|\Zipkin\Span
     */
    public static function startNextSpan(string $span_name, callable $callable = null)
    {
        if (self::$initialization) {
            /** @var Span $parent_span */
            $parent_span = end(self::$span_stack);
            $child_span = TracerInitialize::getTracer()->nextSpan($parent_span->getContext());
            $child_span->setKind(CLIENT);
            $child_span->setName($span_name);
            $child_span->start();
            //用 self::$span_stack[] = $child_span;  比array_push效率更高
            //array_push(self::$span_stack, $child_span);
            self::$span_stack[] = $child_span;
            if (is_callable($callable)) $callable($child_span);
            return $child_span;
        }
    }

    /**
     * 停止子span
     * @param callable|null $callable
     */
    public static function stopNextSpan(callable $callable = null)
    {
        if (self::$initialization) {
            //不在此pop的原因是后置操作如有异常栈会异常
            /** @var Span $child_span */
            $child_span = end(self::$span_stack);
            if ($callable) $callable($child_span);
            $child_span->finish();
            array_pop(self::$span_stack);
        }
    }

    /**
     * 创建 root span
     * @param callable $before_callable
     * @param callable $after_callable
     * @param array|null $carrier
     * @return Response
     * @throws \Throwable
     */
    public static function startRootSpan(callable $before_callable, callable $after_callable, array $carrier = null)
    {
        if (isset($carrier['x-b3-traceid']) and isset($carrier['x-b3-spanid']) and
            isset($carrier['x-b3-parentspanid']) and isset($carrier['x-b3-sampled'])
        ) {
            $root_span = TracerInitialize::getTracer()->nextSpan(TracerInitialize::getSamplingFlags($carrier));
        } else {
            $root_span = TracerInitialize::getTracer()->newTrace(DefaultSamplingFlags::createAsEmpty());
        }
        $root_span->setKind(SERVER);
        self::$span_stack = [];
        self::$initialization = true;
        $root_span->start();
        try {
            array_push(self::$span_stack, $root_span);
            $response = $before_callable($root_span);
            if (is_callable($after_callable)) $after_callable($root_span, $response);
            return $response;
        } catch (\Throwable $throwable) {
            $root_span->tag('method.message', $throwable->getMessage());
            $root_span->tag('method.code', $throwable->getCode());
            $root_span->tag('method.stacktrace', $throwable->getTraceAsString());
            throw $throwable;
        } finally {
            array_pop(self::$span_stack);
            self::$initialization = false;
            $root_span->finish();
            TracerInitialize::getTracer()->flush();
        }
    }
}
