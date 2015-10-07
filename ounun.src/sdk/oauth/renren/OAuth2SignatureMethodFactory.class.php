<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:27
 */

namespace sdk\oauth\renren;


/**
 * oauth签名方法的工厂
 */
class OAuth2SignatureMethodFactory
{

    /* 签名方法的数组 */
    private $signature_methods;

    function __construct()
    {
        $this->signature_methods = array ();
        // 注册HMAC_SHA1签名方法
        $signatureMethod_HMAC_SHA1 = new OAuth2SignatureMethod_HMAC_SHA1 ();
        $this->signature_methods [$signatureMethod_HMAC_SHA1->getName ()] = $signatureMethod_HMAC_SHA1;
    }

    /**
     * 根据方法名来获得签名方法
     *
     * @param string $methodName
     * @return OAuth2SignatureMethod
     */
    function getSignatureMethod($methodName)
    {
        return $this->signature_methods [$methodName];
    }
}