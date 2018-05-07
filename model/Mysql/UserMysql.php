<?php

namespace hx\Model\Mysql;

use hx\Helper\Db;

class UserMysql
{

    public static $db_user = 'user';

    /**
     * 添加玩家信息
     * @param array $data
     * @return integer
     */
    public static function addUser($data)
    {
        $result = Db::insert(self::$db_user, $data);
        return (int)$result;
    }

    /**
     * 查询玩家信息
     * @param integer $uid
     * @return array
     */
    public static function findUserInfo($uid)
    {
        $result = Db::find(self::$db_user, "`uid` = '{$uid}'");
        if($result) return (array)$result;
        return [];
    }

    /**
     * 修改玩家信息
     * @param integer $uid
     * @param array $data
     * @return integer
     */
    public static function updateUserInfo($uid, $data)
    {
        $result = Db::update(self::$db_user, $data, "uid = '{$uid}'");
        return $result;
    }

}