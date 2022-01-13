<?php
/**
 * Created by PhpStorm.
 * User: zhangxiaoxiao
 */

namespace Xiaoyangguang\Tracer\Core;

use Workerman\Timer;
use Zipkin\Endpoint;
use Zipkin\Propagation\Map;
use Zipkin\Reporters\Http;
use Zipkin\Samplers\PercentageSampler;
use Zipkin\Span;
use Zipkin\Tracer;
use Zipkin\Tracing;
use Zipkin\TracingBuilder;

class TracerInitialize
{
    /**
     * @var Tracing
     */
    private static $tracing = null;

    /**
     * @var Tracer
     */
    private static $tracer = null;

    /**
     * 默认配置
     * @var array
     */
    protected static $config = [
        'is_enable' => false,
        'rate' => 1,
        'report_time' => 10,
        'service_name' => 'API_SERVICE',
        'ipv4' => null,
        'ipv6' => null,
        'port' => null,
        'endpoint_url' => 'http://127.0.0.1:9411/api/v2/spans',
    ];

    /**
     * Tracer配置
     * @param bool $is_enable
     * @param string $service_name
     * @param string $endpoint_url
     * @param string|null $ipv4
     * @param string|null $port
     * @param int $rate
     * @param int $report_time
     * @param string|null $ipv6
     */
    public static function setConfig(
        bool   $is_enable = true, string $service_name = '', string $endpoint_url = '', string $ipv4 = null,
        string $port = null, int $rate = 1, int $report_time = 10, string $ipv6 = null
    )
    {
        if ($service_name) self::$config['service_name'] = $service_name;
        if ($endpoint_url) self::$config['endpoint_url'] = $endpoint_url;
        self::$config['is_enable'] = $is_enable;
        self::$config['ipv4'] = $ipv4;
        self::$config['port'] = $port;
        self::$config['rate'] = $rate;
        self::$config['report_time'] = $report_time;
        self::$config['ipv6'] = $ipv6;
    }

    /**
     * @return Tracer|null
     */
    public static function getTracer(): ?Tracer
    {
        return self::$tracer;
    }

    /**
     * @param $carrier
     * @return \Zipkin\Propagation\SamplingFlags
     */
    public static function getSamplingFlags($carrier): \Zipkin\Propagation\SamplingFlags
    {
        $extractor = self::$tracing->getPropagation()->getExtractor(new Map());
        return $extractor($carrier);
    }

    /**
     * @param Span $childSpan
     * @return array
     */
    public static function getCarrier(Span $childSpan): array
    {
        $carrier = [];
        $injector = self::$tracing->getPropagation()->getInjector(new Map());
        $injector($childSpan->getContext(), $carrier);
        return $carrier;
    }

    /**
     * 初始化链路追踪
     * @return bool
     * @throws \Exception
     */
    public static function createTracer(): bool
    {
        if (self::$config['is_enable'] and !self::$tracing instanceof Tracing) {
            $ipv4 = self::$config['ipv4'] ?: self::getServerIp();
            $endpoint = Endpoint::create(self::$config['service_name'], $ipv4, self::$config['ipv6'], self::$config['port']);
            $reporter = new Http(['endpoint_url' => self::$config['endpoint_url']]);
            $sampler = PercentageSampler::create(self::$config['rate']);
            self::$tracing = TracingBuilder::create()
                ->havingLocalEndpoint($endpoint)
                ->havingSampler($sampler)
                ->havingReporter($reporter)
                ->build();
            self::$tracer = self::$tracing->getTracer();
            Timer::add(self::$config['report_time'], function () {
                self::$tracer->flush();
            });
            register_shutdown_function(function () {
                self::$tracer->flush();
            });
        }
        return self::$tracing instanceof Tracing;
    }

    /**
     * 获取服务器局域网ip
     * @return mixed
     */
    protected static function getServerIp()
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        exec("ifconfig", $out, $stats);
        if (!empty($out)) {
            if (isset($out[1]) && strstr($out[1], 'addr:')) {
                $tmp_array = explode(":", $out[1]);
                $tmp_ip = explode(" ", $tmp_array[1]);
                if (preg_match($preg, trim($tmp_ip[0]))) {
                    return trim($tmp_ip[0]);
                }
            }
        }
        return '127.0.0.1';
    }
}