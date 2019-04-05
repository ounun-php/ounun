<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/2
 * Time: 23:42
 */

namespace ounun\tool;


class color
{
    /**
     * 获取标题颜色
     * @param string $str
     * @param string $color
     * @return string
     */
    static public function color_text(string $str, string $color = ''): string
    {
        if ($color) {
            return "<span style=\"color: {$color}\">{$str}</span>";
        } else {
            return $str;
        }
    }

    /**
     * 获取特定时时间颜色
     * @param string $type
     * @param int $time
     * @param string $color
     * @param int $interval
     * @return string
     */
    static public function color_date(string $type = 'Y-m-d H:i:s', int $time = 0, string $color = 'red', int $interval = 86400): string
    {
        if ((time() - $time) > $interval) {
            return date($type, $time);
        } else {
            return self::color_text(date($type, $time), $color);
        }
    }
}
