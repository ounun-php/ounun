<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:24
 */

namespace sdk\oauth\renren;


class ExceptionInvalidAuthorizationServer extends ExceptionRennServer
{
    public function __construct($code, $message, $previous = null)
    {
        parent::__construct ( $code, $message, $previous );
    }
}