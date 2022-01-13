<?php
//区分大小写
use app\controller\Index;
use support\Redis;
use Xiaoyangguang\Tracer\Core\TracerInitialize;
use Xiaoyangguang\Tracer\Example\ElasticsearchAspect;
use Xiaoyangguang\Tracer\Example\GenericAspect;
use Xiaoyangguang\Tracer\Example\MysqlAspect;
use Xiaoyangguang\Tracer\Example\RedisAspect;

TracerInitialize::setConfig(true); //
MysqlAspect::setConfig('业务数据库', '127.0.0.1');
RedisAspect::setConfig('业务Redis');
ElasticsearchAspect::setConfig('业务Elasticsearch');
//HttpAspect::setConfig();

//下面自定义 需要自定义追踪组件 按照Xiaoyangguang\Tracer\Example照猫画虎 简单的很
return [
//    RedisAspect::class => [ //追踪类
//        Redis::class => [  //被追踪类
//            '__callStatic', //被追踪方法
//        ],
//    ],
    GenericAspect::class => [ //追踪类 通用追踪节点 任由开发者发挥
        Index::class => [
            'index',
        ],
    ],
];
