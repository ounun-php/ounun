<?php
/** 命名空间 */
namespace sdk\oauth\qihoo;

/**
 *
 */
class QClient
{
    private $_clientId       = '';
    private $_clientSecret   = '';
    private $_accessToken    = '';

    private $_oauth         = null;
    private $_http          = null;
    private $_help          = null;
    private $_sandbox       = false;

    public function __construct($clientId, $clientSecret, $accessToken, $sandbox= false )
    {
        $this->_clientId         = $clientId;
        $this->_clientSecret     = $clientSecret;
        $this->_accessToken      = $accessToken;
        $this->_oauth   = new QOAuth2($clientId, $clientSecret, $accessToken);
        $this->_http    = new QHttp();
        $this->_helper  = new QHelper();
        $this->_sandbox = $sandbox;
    }

    /**
    * Make an request to get user info.
    *
    * @return API results.
    */
    public function userMe()
    {
        $url  = $this->_oauth->getURL('host');
        $url .= '/user/me.json';
        $data = array(
            'access_token'   => $this->_accessToken
        );
        return $this->_oauth->call('get', $url, $data);
    }

    /**
    * Audit
    *
    * @params is the original params send by server
    *   -user_id
    *   -product_id, Id of product be payed
    *   -begin_time, The start time of the bill
    *   -end_time, The end time of the bill
    * @return verified or invalied
    */
    public function payAudit(array $params)
    {
        $url = $this->_oauth->getURL('host');
        if ($this->_sandbox === true)
        {
            $url .= '/sandbox/pay/audit.json';
        } else
        {
            $url .= '/pay/audit.json';
        }
        
        $params['client_id'] = $this->_clientId;
        $params['client_secret'] = $this->_clientSecret;
        return $this->_oauth->call('post', $url, $params);
    }

    /**
    * Call to pay controll server to verify the order signature
    *
    * @params is the original params send by server
    * @return verified or invalied
    */
    public function payVerifyNotification(array $params){
        $url = $this->_oauth->getURL('host');
        
        if ($this->_sandbox === true) {
            $url .= '/sandbox/pay/verify_notification.json';
        } else {
            $url .= '/pay/verify_notification.json';
        }
        
        $params['client_id'] = $this->_clientId;
        $params['client_secret'] = $this->_clientSecret;
        return $this->_oauth->call('post', $url, $params);
    }

    /**
    * Make an payment order request.
    *
    * @productId is product id
    * @productName is product name
    * @userId is user id
    * @notifyUri is notify url sent to app server
    * @returnUri is succeed page show on web browser
    * @return order request url to QIHOO openapi server
    */
    public function getPaymentOrderUrl($productId, $productName, $userId, $notifyUri, $returnUri, $amount)
    {
        $url = $this->_oauth->getURL('host');
        
        $secret = $this->_clientSecret;
        if ($this->_sandbox === true) {
            $url .= '/sandbox/page/pay';
            $secret = $secret.'sandbox';
        } else {
            $url .= '/page/pay';
        }
        
        $data = array(
            'app_key'           => $this->_clientId,
            'user_id'           => $userId,
            'notify_uri'        => $notifyUri,
            'product_id'        => $productId,
            'amount'            => $amount
        );

        $data['sign'] = $this->_helper->getSignature($data, $secret);
        if(!empty($productName)) {
            $data['product_name'] = $productName;
        }
        if(!empty($returnUri)) {
            $data['return_uri'] = $returnUri;
        }

        $query = $this->_http->buildHttpQuery($data);
        return $url."?{$query}";
    }

    public function getPaymentOrderUrlOL($productId, $productName, $productPrice, $payMode, $notifyUri, $signType)
    {
        $url = $this->_oauth->getURL('host');
        
        $secret = $this->_clientSecret;
        if ($this->_sandbox === true) {
            $url .= '/sandbox/page/pay';
            $secret = $secret.'sandbox';
        } else {
            $url .= '/page/pay';
        }
           
        $data = array(
            'app_key'           => $this->_clientId,
            'product_id'        => $productId,
            'product_price'     => $productPrice,
            'pay_mode'          => $payMode,
        );

        if(!empty($productName)) {
            $data['product_name'] = $productName;
        }
        if(!empty($notifyUri)) {
            $data['notify_uri'] = $notifyUri;
        }
        if(!empty($signType)) {
            $data['sign_type'] = $signType;
        }

        $data['sign'] = $this->_helper->getSignature($data, $secret, true); 

        $query = $this->_http->buildHttpQuery($data);
        return $url."?{$query}";
    }
    /**
    * Make an bind request.
    *
    * @return API results.
    */
    public function bind($partnerUserId, $partnerUserName, $partnerUserEmail, $partnerToken)
    {
        $url = $this->_oauth->getURL('host');
        $url .= '/user/bind.json';
        $partnerToken = array(
            'access_token'  => $partnerToken
        );
        $data = array(
            'partner_user_id'   => $partnerUserId,
            'partner_user_name' => $partnerUserName,
            'partner_user_email'=> $partnerUserEmail,
            'partner_token'     => json_encode($partnerToken),
            'access_token'      => $this->_accessToken
        );
        return $this->_oauth->call('get', $url, $data);
    }

    /**
    * Make an unbind request.
    *
    * @return API results.
    */
    public function unbind($partnerUserId)
    {
        $url = $this->_oauth->getURL('host');
        $url .= '/user/unbind.json';
        $data = array(
            'partner_user_id'   => $partnerUserId,
            'access_token'      => $this->_accessToken
        );
        return $this->_oauth->call('get', $url, $data);
    }
}








