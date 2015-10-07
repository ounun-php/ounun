<?php
/** 命名空间 */
namespace sdk\oauth\baidu;


/**
 * @package	Baidu
 * @author	zhujianting(zhujianting@baidu.com)
 * @version	v1.0.0
 */
class BaiduException extends \Exception
{
    /**
     * Constructor: initialize the BaiduException instance.
     * 
     * @return void
     */
    public function __construct($message, $code = 0)
    {
        parent::__constructor($message, $code);
    }
}

 
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */