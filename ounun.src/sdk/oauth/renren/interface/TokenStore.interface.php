<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-10-3
 * Time: 下午10:04
 */

namespace sdk\oauth\renren;
/**
 * token存储
 */
interface TokenStore
{

    /**
     * 加载token
     *
     * @param string $key
     * @return array 成功返回array('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
     */
    public function loadToken($key);

    /**
     * 保存token
     *
     * @param string $key
     * @param array $token
     */
    public function saveToken($key, $token);
}