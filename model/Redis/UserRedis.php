<?php

namespace Fw\Model\Redis;

use Fw\Helper\MConfig;
use Fw\Helper\MipRedis;
use Fw\Helper\PrePperation;
use Fw\Helper\Response;

class UserRedis
{


    public static $tb_uid_prefix = 'uid';
    public static $tb_user_prefix = 'user_'；

    /**
     * 查找用户
     *
     * @param integer $uid
     * @return
     */
    public static function findUserByUid($uid)
    {
        return MipRedis::cluster('user', $uid)->hgetall(self::$tb_user_prefix.$uid);
    }
    /**
     * 查找用户指定字段
     *
     * @param integer $uid
     * @param integer $field
     * @return
     */
    public static function findUserFieldByUid($uid, $field)
    {
        return MipRedis::cluster('user', $uid)->hget(self::$tb_user_prefix.$uid, $field);
    }

 
}