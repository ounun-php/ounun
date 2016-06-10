<?php
/** 命名空间 */
namespace plugins\rsa;

/**
 * rsa
 * 需要 openssl 支持
 * @author andsky
 */
class RsaPp extends \plugins\rsa\Rsa
{

    private static $_instance;

    const private_key = '
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDA4E8H2qksOnCSoBkq+HH3Dcu0/iWt3iNcpC/BCg0F8tnMhF1Q
OQ98cRUM8eeI9h+S6g/5UmO4hBKMOP3vg/u7kI0ujrCN1RXpsrTbWaqry/xTDgTM
8HkKkNhRSyDJWJVye0mPgbvVnx76en+K6LLzDaQH8yKI/dbswSq65XFcIwIDAQAB
AoGAU+uFF3LBdtf6kSGNsc+lrovXHWoTNOJZWn6ptIFOB0+SClVxUG1zWn7NXPOH
/WSxejfTOXTqpKb6dv55JpSzmzf8fZphVE9Dfr8pU68x8z5ft4yv314qLXFDkNgl
MeQht4n6mo1426dyoOcCfmWc5r7LQCi7WmOsKvATe3nzk/kCQQDp1gyDIVAbUvwe
tpsxZpAd3jLD49OVHUIy2eYGzZZLK3rA1uNWWZGsjrJQvfGf+mW+/zeUMYPBpk0B
XYqlgHJNAkEA0yhhu/2SPJYxIS9umCry1mj7rwji5O2qVSssChFyOctcbysbNJLH
qoF7wumr9PAjjWFWdmCzzEJyxMMurL3gLwJBAIEoeNrJQL0G9jlktY3wz7Ofsrye
j5Syh4kc8EBbuCMnDfOL/iAI8zyzyOxuLhMmNKLtx140h0kkOS6C430M2JUCQCnM
a5RX/JOrs2v7RKwwjENvIqsiWi+w8C/NzPjtPSw9mj2TTd5ZU9bnrMUHlnd09cSt
yPzD5bOAT9GtRVcCexcCQBxXHRleikPTJC90GqrC2l6erYJaiSOtv6QYIh0SEDVm
1o6Whw4FEHUPqMW0Z5PobPFiEQT+fFR02xU3NJrjYy0=
-----END RSA PRIVATE KEY-----';
    
	const public_key = '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4li3tcpmBaYt3ymz7JY0
XAonHSP8K7QEecNl5CCNNWkfpFloYvt+x9kCJhr1M12QpRPdinLEI8gusmAXDp2v
rGokd4vFyqfOW98zJkRRYeR+ICjcrvS3KRxsU90wl+EpBcTQCC8aoTqN/1As95IN
DvHr+DZG2cHVXMibJzNLD/RwJMilHDgGUJAxGphWAj5OYVgReUItcmT2eZazMonO
Yxy5TRjjbNZa2MaGo5KW+nkG1W8CwnsS2/B1judhQpJwbjKuwhZ1gie1Ek5WvHpq
xjJ/E1ioW00oCj+0ZvifQLCUz5uAdQXH1jrIWTKzss+RllqZWFwufQ/DEkHVe3if
5wIDAQAB
-----END PUBLIC KEY-----';
    
    

    public static function instance()
    {
        if (self::$_instance == null) 
        {
            self::$_instance = new self;
        }
        return self::$_instance; 
    }


    public function sign($sourcestr = NULL,$privatekey=self::private_key)
    {
        return base64_encode(parent::sign($sourcestr, self::private_key));
    }

    public function verify($sourcestr = NULL, $signature = NULL,$publickey=self::public_key)
    {
        return parent::verify($sourcestr, $signature, self::public_key);
    }
}
?>
