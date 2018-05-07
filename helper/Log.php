<?php

namespace hx\Helper;

class Log {

    // 写入日志
    public static function write($param)
    {
        $filename = "Logs/input.log.".date("Y-m-d");
        $str = '['.date("Ymd H:i:s").']-----';
        $str .= $param;
        file_put_contents($filename, $str."\r\n", FILE_APPEND );
    }
}