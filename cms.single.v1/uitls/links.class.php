<?php

namespace uitls;

class links
{
    /** @var array 数据 */
    public $data            = [];


    /**
     * 获得友连数据
     * @return array 友连数据
     */
    static public function data()
    {
        return self::instance()->data;
    }

    /** @var \uitls\links */
    protected static $_instance = null;
    public static function instance()
    {
        if(self::$_instance)
        {
            return self::$_instance;
        }
        $cls             = static::class;
        self::$_instance = new $cls();
        return self::$_instance;
    }
}
