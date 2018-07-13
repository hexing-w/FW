<?php

namespace FW\Helper;

use FW\Lib\Framework\UrlManager;

class Response {

    /**
     * 返回json
     *
     * @param array $data
     * @return
     */
    public static function jsonResponse($data = [])
    {
        self::out( $data );
    }

    /**
     * 非法请求
     *
     * @param 
     * @return
     */
    public static function invalidRequest()
    {
        self::out( [ 'code' => 1001, 'data' => [] ] );
    }

    /**
     * 参数缺失
     *
     * @param 
     * @return
     */
    public static function paramsMiss()
    {
        self::out( [ 'code' => 1002, 'data' => [] ] );
    }

    public static function out($data)
    {
        header('content-type:application/json;charset=utf8');
        $json = json_encode( $data );
        echo $json;
        $origin_data = file_get_contents("php://input");
        $path_info = UrlManager::getPathInfo();
        Log::write(json_encode(["url"=>$path_info, "param"=>json_decode($origin_data), "response"=>$data]));
        die;
    }

}