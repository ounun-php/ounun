<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:25
 */

namespace sdk\oauth\renren;

/**
 * http error code is 500 内部错误
 */
class ExceptionInternalErrorServer extends ExceptionRennServer
{
    public function __construct($code, $message, $previous = null)
    {
        parent::__construct ( $code, $message, $previous );
    }
}