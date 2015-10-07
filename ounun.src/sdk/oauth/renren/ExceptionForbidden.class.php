<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:25
 */

namespace sdk\oauth\renren;


/**
 * http error code is 403 认证通过，但是也不允许其访问。例如超配额
 */
class ExceptionForbiddenServer extends ExceptionRennServer
{
    public function __construct($code, $message, $previous = null)
    {
        parent::__construct ( $code, $message, $previous );
    }
}