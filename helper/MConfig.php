<?php

namespace Fw\Helper;

use Fw\Model\Redis\ConfigRedis;

class MConfig
{

    /**
     * 返回配置Config
     *
     * @param 
     * @return  
     */
    public static function get($name = '')
    {
        $config = self::loadConfig();

        return $name ? (isset($config[$name]) ? $config[$name] : '') : $config;
    }

    /**
     * 返回配置DATA
     *
     * @param 
     * @return  
     */
    public static function param($name = '')
    {
        $param = include '../Data/'.$name.'.php';
        return $param;
    }

    /**
     * 返回配置JSON
     *
     * @param string $name
     * @param integer $id
     * @return array
     */
    public static function jsonParam($name = '', $id = 0)
    {
        //从redis中读取配置文件
        $file_contents = ConfigRedis::findConfigData($name, $id);

        if(!$file_contents)
        {
            $file_contents = file_get_contents( '../Data/'.$name.'.json' );
            mb_convert_encoding($file_contents,'UTF-8');
        }

        return (array)json_decode($file_contents, true);
    }

    /**
     * 返回配置JSON
     *
     * @param string $name
     * @param integer $id
     * @return array
     */
    public static function jsonParamById($name = '', $id = 0)
    {
        //从redis中读取配置文件
        $file_contents = ConfigRedis::findConfigData($name, $id);

        if(!$file_contents)
        {
            $file_contents = file_get_contents( '../Data/'.$name.'.json' );
            mb_convert_encoding($file_contents,'UTF-8');
            $file_contents = (array)json_decode($file_contents, true);
            $file_contents = key_exists($id, $file_contents) ? $file_contents[$id] : '';
            return $file_contents;
        }

        return (array)json_decode($file_contents, true);
    }

    /**
     * 加载配置文件
     *
     * @param 
     * @return  
     */
    public static function loadConfig()
    {
        global $_CONF;

        if (!$_CONF) $_CONF = require '../Config/global.php';

        return $_CONF;
    }

}
