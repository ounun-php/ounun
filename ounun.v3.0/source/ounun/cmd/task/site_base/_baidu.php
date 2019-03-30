<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/30
 * Time: 02:09
 */

namespace ounun\cmd\task\site_base;


use ounun\api_sdk\com_baidu;

abstract class _baidu extends _site
{
    /** @var com_baidu */
    protected $_baidu_sdk = null;

}