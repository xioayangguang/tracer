<?php

namespace Xiaoyangguang\Tracer\Example;

trait AopTrait
{
    /**
     * @var string
     */
    private static $service_name = '';
    /**
     * @var string
     */
    private static $ipv4 = '127.0.0.1';
    /**
     * @var null
     */
    private static $ipv6 = null;
    /**
     * @var int
     */
    private static $port = null;

    /**
     * @param string $service_name
     * @param null $ipv4
     * @param null $ipv6
     * @param null $port
     */
    public static function setConfig(string $service_name, $ipv4 = null, $ipv6 = null, $port = null)
    {
        self::$service_name = $service_name;
        self::$ipv4 = $ipv4;
        self::$ipv6 = $ipv6;
        self::$port = $port;
    }
}