<?php

namespace FW\Helper;

use FW\Helper\MConfig;

class MipRedis
{

    public static $redisCluster = [];

    public static function cluster()
    {
        $redisConfig = MConfig::get('redis');
        //实例化redis
        $redis = new \Redis();
        //连接
        $redis->connect($redisConfig['host'], $redisConfig['port']);
        if(!isset(self::$redisCluster))
        {
            self::$redisCluster = $redis;
        }
        return self::$redisCluster;
    }


}
