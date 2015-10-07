<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:02
 */

namespace sdk\oauth\renren;

require   realpath(__DIR__) .'/interface/TokenStore.interface.php';
/**
 * 基于cookie的token存储
 */
class TokenStoreCookie implements TokenStore
{
    /**
     * 加载token
     *
     * @param string $key
     * @return array 成功返回array('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
     */
    public function loadToken($key)
    {
        if (isset ( $_COOKIE [$key] ) && $cookie = $_COOKIE [$key])
        {
            parse_str ( $cookie, $token );
            return new AccessToken ( $token ['type'], $token ['accessToken'], isset ( $token ['refreshToken'] ) ? $token ['refreshToken'] : null, isset ( $token ['macKey'] ) ? $token ['macKey'] : null, isset ( $token ['macAlgorithm'] ) ? $token ['macAlgorithm'] : null );
        } else
        {
            return null;
        }
    }

    /**
     * 保存token
     *
     * @param string $key
     * @param array $token
     */
    public function saveToken($key, $token)
    {
        echo $key;
        setcookie ( $key, http_build_query ( $token ) );
    }
}