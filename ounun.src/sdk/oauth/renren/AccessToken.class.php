<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午2:18
 */

namespace sdk\oauth\renren;



class AccessToken
{
    public $type;
    public $accessToken;
    public $refreshToken;
    public $macKey;
    public $macAlgorithm;

    public function __construct($type, $accessToken, $refreshToken, $macKey, $macAlgorithm)
    {
        $this->type = $type;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->macKey = $macKey;
        $this->macAlgorithm = $macAlgorithm;
    }
}