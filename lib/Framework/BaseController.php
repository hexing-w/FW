<?php

namespace FW\Lib\Framework;

use FW\Helper\Log;
use FW\Helper\Response;
use FW\Model\Redis\UserRedis;

class BaseController
{

    /**
     * 原始请求数据
     * @var string
     */
    protected $origin_data;
    protected $noCheckTokenAction = [ 'appLoad', 'weixin', 'weixinLogin', 'weixinPayNotify', 'touristLogin', 'payCall', "inter" ];
    protected $noCheckParamAction = [ 'weixin', 'weixinLogin', 'weixinPayNotify', 'touristLogin', 'payCall', "inter" ];
    public static $action;
    private $token;

    /**
     * 转换后数据
     * @var mixed
     */
    protected $param;

    const REQUEST_TYPE_ARRAY = 1;       //请求参数类型  数组
    const REQUEST_TYPE_OBJECT = 2;      //请求参数类型  对象

    public function __construct()
    {
        $this->origin_data = file_get_contents("php://input");
        $this->convertRequestParams(self::REQUEST_TYPE_OBJECT);
        $path_info = UrlManager::getPathInfo();
        Log::write($path_info, json_encode(json_decode($this->origin_data)), '');

        // 输入参数合法性
        if ( empty($this->param) && !in_array(self::$action, $this->noCheckParamAction) ) Response::invalidRequest();
        //检测action 是否需要进行token验证
        $this->actionCheck();
    }

    /**
     * 检查action是否需要进行token 验证
     * 
     */
    public function actionCheck()
    {
        if ( !in_array(self::$action, $this->noCheckTokenAction) )
        {
            if ( !property_exists( $this->param, 'token' )) Response::invalidRequest();
            $info = json_decode(UserRedis::findUserLoginFlagByUid($this->param->uid));
            if ($info) $this->token = $info->token;
            if ( $this->token !== $this->param->token ) Response::invalidRequest();
        }
    }

    /**
     * 获取post数据流请求
     * @param $type   1 对象 2数组
     * @return mixed
     */
    public function convertRequestParams( $type = self::REQUEST_TYPE_ARRAY )
    {
        if ( $type == self::REQUEST_TYPE_ARRAY  )
        {
            $this->param = json_decode( $this->origin_data, true );
        }
        elseif ( $type == self::REQUEST_TYPE_OBJECT  )
        {
            $this->param = json_decode( $this->origin_data );
        }
        else
        {
            $this->param = [];
        }
    }

    /**
     * 检查参数是否存在
     * @param $params_need
     */
    public function paramsCheck( $params_need )
    {
        if ( !$this->checkParamsExist( $params_need ) )
        {
            Response::paramsMiss();
        }
    }

    /**
     * 检查参数是否存在
     * @param $params_need
     * @return bool
     */
    public function checkParamsExist( $params_need )
    {
        //参数不是数组false
        if ( !is_array( $params_need ) ) return false;
        //空数组校验true
        if ( empty( $params_need ) ) return true;

        //如果参数是数组
        if ( is_array( $this->param ) )
        {
            if( empty( $this->param ) ) return false;
            $param_keys = array_keys( $this->param );
        }
        //如果参数是对象
        elseif ( is_object( $this->param ) )
        {
            $param = get_object_vars( $this->param );
            $param_keys = array_keys( $param);
        }
        else
            return false;

        $unfind_keys_arr = array_diff( $params_need, $param_keys );
        if ( !empty( $unfind_keys_arr ) )
            return false;
        else
            return true;
    }
    
}