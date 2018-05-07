<?php

namespace hx\Helper;

class Facility
{

    /**
     * 二维数组排序
     *
     * @param array $arrays
     * @param string $sort_key
     * @param string $sort_order
     * @param string $sort_type
     * @return array 
     */
    public static function arraySort($arrays, $sort_key, $sort_order=SORT_ASC, $sort_type=SORT_NUMERIC )
    {   
        if(is_array($arrays)){   
            foreach ($arrays as $array){   
                if(is_array($array)){   
                    $key_arrays[] = $array[$sort_key];   
                }else{   
                    return false;   
                }   
            }   
        }else{   
            return false;   
        }  
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);   
        return $arrays;   
    }

    /**
     * 本周一日期
     *
     * @param array $arrays
     * @param string $sort_key
     * @param string $sort_order
     * @param string $sort_type
     * @return array 
     */
    public static function nowWeekTime()
    {
        $date = date('Y-m-d');
        $first = 1;
        $w = date('w', strtotime($date));
        return date('Ymd', strtotime("$date -".($w ? $w - $first : 6).' days'));
    }

    /**
     * 上周一日期
     *
     * @param array $arrays
     * @param string $sort_key
     * @param string $sort_order
     * @param string $sort_type
     * @return array 
     */
    public static function pexWeekTime()
    {
        $now_start = self::nowWeekTime();
        return date('Ymd', strtotime("$now_start - 7 days"));
    }

    /**
     * 时间格式
     *
     * @param integer $timer
     * @return array
     */
    public static function formatTime($timer)
    {
        $str = '';
        $diff = $_SERVER['REQUEST_TIME'] - $timer;
        $day = floor($diff / 86400);
        $free = $diff % 86400;
        if ($day > 0)
        {
            return $day."天前";
        }
        else
        {
            if($free>0){
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if($hour>0){
                    return $hour."小时前";
                }else{
                    if($free>0){
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if($min>0){
                            return $min."分钟前";
                        }else{
                            if($free>0){
                                return $free."秒前";
                            }else{
                                return '刚刚';
                            }
                        }
                    }else{
                        return '刚刚';
                    }
                }
            }
            else
            {
                return '刚刚';
            }
        }
    }

    /**
     * 跳转
     *
     * @param string $url
     * @return
     */
    public static function redirect($url = '/')
    {
        Header('Location: ' . $url);
        exit;
    }

    /**
     * 图片地址
     *
     * @param string $url
     * @return
     */
    public static function imgurl($name)
    {
        if ($name)
        {
            if (strpos($name, 'http:') === false && strpos($name, 'https:') === false)
                $name = MConfig::get('site_img').'/'.$name;
        }

        return $name;
    }

    /**
     * 计算坐标点的直线距离
     *
     * @param integer $lat1
     * @param integer $lng1
     * @param integer $lat2
     * @param integer $lng2
     * @return
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2)
    {
        $PI = 3.1415926535898;
        $EARTH_RADIUS = 6378.137;
        $radLat1 = $lat1 * ($PI / 180);
        $radLat2 = $lat2 * ($PI / 180);

        $a = $radLat1 - $radLat2;
        $b = ($lng1 * ($PI / 180)) - ($lng2 * ($PI / 180));

        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)));
        return round($s * $EARTH_RADIUS * 10000) / 10000;
    }

    /**
     * 递归创建文件夹
     *
     * @param string $dir
     * @return
     */
    public static function mikdir($dir)
    {
        if(is_dir($dir) || @mkdir($dir, 0777))
        {
            return true;
        }
        else
        {
            $dirArr = explode('/', $dir);
            array_pop($dirArr);
            $newDir = implode('/', $dirArr);
            self::mikdir($newDir);
            if (@mkdir($dir, 0777))
            {
                return true;
            }
        }
    }

}
