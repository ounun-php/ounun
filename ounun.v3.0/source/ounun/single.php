<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/9
 * Time: 10:02
 */

namespace ounun;


class single
{
    /** @var self 单例 */
    protected static $_instance;

    /**
     * @return $this 返回数据库连接对像
     */
    public static function instance(): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}
