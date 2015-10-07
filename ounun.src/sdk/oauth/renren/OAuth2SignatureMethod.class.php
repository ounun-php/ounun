<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:28
 */

namespace sdk\oauth\renren;

/**
 * oauth签名方法
 * A class for implementing a Signature Method
 * See section 9 ("Signing Requests") in the spec
 */
abstract class OAuth2SignatureMethod
{
    /**
     * 获得签名方法名
     * Needs to return the name of the Signature Method (ie HMAC-SHA1)
     *
     * @return string
     */
    abstract public function getName();

    /**
     * 生成 签名
     * Build up the signature
     * NOTE: The output of this function MUST NOT be urlencoded.
     * the encoding is handled in OAuthRequest when the final
     * request is serialized
     *
     * @param string $signatureBaseString
     * @param string $signatureSecret
     * @return string
     */
    abstract public function buildSignature($signatureBaseString, $signatureSecret);

    /**
     * 检验签名
     * Verifies that a given signature is correct
     *
     * @param string $signatureBaseString
     * @param string $signatureSecret
     * @param string $signature
     * @return bool
     */
    public function checkSignature($signatureBaseString, $signatureSecret, $signature)
    {
        $built = $this->buildSignature ( $signatureBaseString, $signatureSecret );
        return $built == $signature;
    }
}