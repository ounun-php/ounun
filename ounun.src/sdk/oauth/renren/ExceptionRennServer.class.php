<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:21
 */

namespace sdk\oauth\renren;


/**
 * 服务端的异常
 */
class ExceptionRennServer extends ExceptionRenn
{
    protected $errorCode;

    /**
     *
     * @param unknown $code
     *        	code和message使用父类的属性
     * @param unknown $message
     *        	code和message使用父类的属性
     * @param string $previous
     */
    function __construct($code, $message, $previous = null)
    {
        parent::__construct ( $message, null, $previous );
        $this->errorCode = $code;
    }

    function getErrorCode()
    {
        return $this->errorCode;
    }
}