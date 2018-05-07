<?php

namespace Fw\Modules\User\controllers;

use Fw\Lib\Framework\BaseController;
use Fw\Helper\Response;
use Fw\Helper\MConfig;

class UserController extends BaseController
{

    /**
     * 登录时返回玩家资料
     *
     * @param
     * @return 
     */
    public function userInfoAction()
    {
        $code = 200;
        $data = [];

        // 缺失参数
        $this->paramsCheck([ 'uid' ]);

        // --
        Response::jsonResponse(['code' => $code, 'data' => $data]);
    }



}