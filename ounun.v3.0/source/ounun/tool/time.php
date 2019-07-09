<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/2
 * Time: 23:45
 */

namespace ounun\tool;


class time
{
    /**
     * 获得$day_nums天前的时间戳
     * @param int $day_nums
     * @return int
     */
    static public function xtime(int $day_nums): int
    {
        $day_time = time() - $day_nums * 3600 * 24;
        return strtotime(date("Y-m-d 00:00:00", $day_time));
    }

    /**
     * @param $time
     * @return string
     */
    static public function reckon($time)
    {
        $time_curr = time();
        // if($time > $time_curr){ return false; }
        $time_poor = $time_curr - $time;
        if ($time_poor <= 0) {
            $str = '刚刚';
        } else if ($time_poor < 60 && $time_poor > 0) {
            $str = $time_poor . '秒之前';
        } else if ($time_poor >= 60 && $time_poor <= 60 * 60) {
            $str = floor($time_poor / 60) . '分钟前';
        } else if ($time_poor > 60 * 60 && $time_poor <= 3600 * 24) {
            $str = floor($time_poor / 3600) . '小时前';
        } else if ($time_poor > 3600 * 24 && $time_poor <= 3600 * 24 * 7) {
            if (floor($time_poor / (3600 * 24)) == 1) {
                $str = "昨天";
            } else if (floor($time_poor / (3600 * 24)) == 2) {
                $str = "前天";
            } else {
                $str = floor($time_poor / (3600 * 24)) . '天前';
            }
        } else if ($time_poor > 3600 * 24 * 7) {
            $str = date("Y-m-d", $time);
        }
        return $str;
    }
}
