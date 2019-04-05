<?php
namespace ounun\mvc\controller\api;

class ads extends \v
{
    /** @var array */
    static protected $ads = [];

    /**
     * 广告 PC
     * @param $mod array
     */
    public function pc($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['pc']) . "\n");
    }

    /**
     * 广告 Wap
     * @param $mod array
     */
    public function wap($mod)
    {
        exit('var $__m_g_com=' . json_encode(self::$ads['wap']) . "\n");
    }
}