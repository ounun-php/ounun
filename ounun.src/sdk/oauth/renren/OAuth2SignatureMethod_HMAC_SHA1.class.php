<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:30
 */

namespace sdk\oauth\renren;



/**
 * 基于HMAC_SHA1算法的签名方法
 * The HMAC-SHA1 signature method uses the HMAC-SHA1 signature algorithm as defined in [RFC2104]
 * where the Signature Base String is the text and the key is the concatenated values (each first
 * encoded per Parameter Encoding) of the Consumer Secret and Token Secret, separated by an '&'
 * character (ASCII code 38) even if empty.
 * - Chapter 9.2 ("HMAC-SHA1")
 */
class OAuth2SignatureMethod_HMAC_SHA1 extends OAuth2SignatureMethod
{
    /**
     * 获得签名方法名
     *
     * @see OAuthSignatureMethod::get_name()
     */
    function getName()
    {
        return "hmac-sha-1";
    }

    /**
     * 生成 签名
     *
     * @see OAuthSignatureMethod::build_signature()
     */
    public function buildSignature($signatureBaseString, $signatureSecret)
    {
        return base64_encode ( hash_hmac ( 'sha1', $signatureBaseString, $signatureSecret, true ) );
    }
}
