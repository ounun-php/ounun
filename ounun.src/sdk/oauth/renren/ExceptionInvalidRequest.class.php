<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:23
 */

namespace sdk\oauth\renren;

/**
 * http error code is 400.
 * 请求参数错误，参数使业务逻辑无法正常运行下去。
 */
class ExceptionInvalidRequestServer extends ExceptionRennServer
{
    public function __construct($code, $message, $previous = null)
    {
        parent::__construct ( $code, $message, $previous );
    }
}