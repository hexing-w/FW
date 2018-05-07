<?php

namespace Fw\Helper;

use Fw\Model\Mysql\EPatternMysql;

class EPattern
{

    /**
     * 注册玩家
     *
     * @param array $data
     * @return
     */
    public static function regUser($data)
    {
        $uid = self::generateNonceStr();
        // --
        $row = [
            'aid' => $data['uid'],
            'uid' => $uid,
            'unick' => $data['nickname'],
            'usex' => $data['sex'],
            'upfface' => $data['old_portrait'],
            'uface' => $data['old_portrait'],
            'wxuuid' => $data['unionid'],
            'puid' => $data['puid'],
            'regip' => Ip::getIP(),
            'regtime' => $data['reg_time']
        ];
        EPatternMysql::addUser($row);
        // --
        $row = [
            'aid' => $data['uid'],
            'uid' => $uid,
            'wxuuid' => $data['unionid'],
            'uland' => $data['gold']
        ];
        EPatternMysql::addUserGame($row);
    }

    /**
     * 修改玩家
     *
     * @param array $data
     * @return
     */
    public static function UpUser($data)
    {
        $aid = $data['uid'];
        // --
        $row = [];
        if (isset($data['login_time'])) $row['logintime'] = $data['login_time'];
        if ($row) EPatternMysql::updateUser($aid, $row);
        // --
        $row = [];
        if (isset($data['gold'])) $row['uland'] = $data['gold'];
        if ($row) EPatternMysql::updateUserGame($aid, $row);
    }

    /**
     * 充值下单
     *
     * @param array $data
     * @return
     */
    public static function addPay($data)
    {
        $pstime = time();
        $uuid_4 = new Uuid4();
        $pvtoken = $uuid_4->get('');
        $ptoken = $uuid_4->get('');
        $info = EPatternMysql::findUserInfo($data["aid"]);

       $data = [
            'aid'=> $data["aid"],
            'uid'=> key_exists("uid", $info) ? $info["uid"] : "",
            'game_id'=> $data["app_id"],
            'itemid'=> $data["item_id"],
            'pamt'=> $data["price"],
           'pcoin'=> $data["pcoin"],
            'pbillno'=> $data["agent_bill_id"],
            'ptoken'=> $ptoken,
           'pvtoken'=> $pvtoken,
            'pstime'=> $pstime,  // 下单时间
            'pay_type'=> "wx",  // 支付方式-微信
        ];
       EPatternMysql::addOrder($data);
    }

    /**
     * 修改充值状态
     * @param string $agent_bill_id
     * @param integer $status
     * @return
     */
    public static function upPay($agent_bill_id, $status)
    {
        $now_time = time();
        $data = array(
            'pstatus'=> $status,
            'petime'=> $now_time
        );
        EPatternMysql::updateOrderInfo($agent_bill_id, $data);
    }

    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public static function generateNonceStr($length=32){
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++)
        {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 充值同步
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public static function recharge($data)
    {
        $url = "http://wawaji-proxy-gm.zhaogewu.com/pay/index";
        $center_pay = EPatternMysql::findOrderInfo($data["order_sn"]);
        $config = MConfig::get('heepay');
        $data = [
            "pid" => $center_pay["pid"],
            "aid" => $data["uid"],
            "game_id" => $config["app_id"],
            "itemid" => (string)$data["item_id"],
            "pamt" => (string)$data["pamt"],
            "pstime" => $center_pay["pstime"],
            "petime" => (string)time(),
            "pay_type" => "wx"
        ];
        $result = self::wechat_cx($url, $data);
        Log::write("Epattern_pay_sync param：".json_encode($data));
        Log::write("Epattern_pay_sync param2：".json_encode(json_decode($result)));
    }

    public static function wechat_cx($url,$ds)
    {
        $ch = curl_init();
       // $cacert_url = __DIR__ . '/cacert.pem';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   //SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);      //严格认证
      //  curl_setopt($ch, CURLOPT_CAINFO, $cacert_url);     //证书地址
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ds);
        $output = curl_exec($ch);
        Log::write("Epattern_pay_sync param3：".serialize($output));
        curl_close($ch);
        $tmpInfo = mb_convert_encoding($output, "utf-8", "gb2312");
        return $tmpInfo; // 返回数据，
    }

}