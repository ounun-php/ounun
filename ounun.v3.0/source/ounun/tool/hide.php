<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/2
 * Time: 23:46
 */

namespace ounun\tool;


class hide
{
    /**
     * IP隐藏第3段
     * @param $ip
     * @return string
     */
    static public function ipv4($ip)
    {
        $ip     = explode('.',$ip);
        $ip[2]  = '*';
        return implode('.',$ip);
    }
}