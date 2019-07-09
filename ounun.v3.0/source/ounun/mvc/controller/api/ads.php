<?php

namespace ounun\mvc\controller\api;

class ads extends \v
{
    /** @var array */
    static protected $ads = [];

    /**
     * 广告  Www - PC
     * @param $mod array
     */
    public function www($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['www']) . ";\n");
    }

    /**
     * 广告 Wap
     * @param $mod array
     */
    public function wap($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['wap']) . ";\n");
    }
}
