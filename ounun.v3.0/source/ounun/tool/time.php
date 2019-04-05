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
}
