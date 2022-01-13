<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Core;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use Xiaoyangguang\Tracer\SpanManage;
use Zipkin\Span;

class RootSpan implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        return SpanManage::startRootSpan(function (Span $root_span) use ($request, $next) {
            $root_span->setName('MiddlewareTrack' . request()->controller . "::" . request()->action);
            foreach ($request->header() as $key => $item) {
                $root_span->tag("http.header.{$key}", $item);
            }
            $root_span->tag('http.ip', $request->getRealIp());
            $root_span->tag('http.url', $request->url());
            $root_span->tag('http.method', $request->method());
            return $next($request);
        }, function (Span $root_span, Response $response) {
            $root_span->tag('http.response.code', $response->getStatusCode());
            $root_span->tag('http.response.data', $response->rawBody());
        }, $request->header());
    }
}