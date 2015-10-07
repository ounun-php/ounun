<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午3:26
 */

namespace sdk\oauth\qihoo;


class QHelper
{

    public function getSignature($params, $appSecret, $isKsort=false)
    {
        if($isKsort)
        {
            ksort($params);
        }

        $sigStr = '';
        foreach($params as $value)
        {
            $sigStr .= $value . '#';
        }
        $sigStr .= $appSecret;

        return md5($sigStr);
    }
}