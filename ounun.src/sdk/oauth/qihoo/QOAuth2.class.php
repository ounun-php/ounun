<?php
/** 命名空间 */
namespace sdk\oauth\qihoo;
/**
 * Provides access to the QIHOO360 Platform.  This class provides
 * a majority of the functionality needed.
 */
class QOAuth2
{

    private $_clientId         = ""; // api key
    private $_clientSecret     = ""; // app secret
    private $_redirectUri      = ""; // call back
    // Set up the API root URL.
    private $_host             = 'https://openapi.360.cn';
    private $_authorizeURL     = 'https://openapi.360.cn/oauth2/authorize';
    private $_accessTokenURL   = 'https://openapi.360.cn/oauth2/access_token';

    public function __construct($clientId, $clientSecret, $accessToken)
    {
        $this->http            = new QHttp();

        $this->_clientId       = $clientId;
        $this->_clientSecret   = $clientSecret;
        $this->_accessToken    = $accessToken;
    }


    /**
     * Get API root URL
     *
     * @param string $name     url name.
     *
     * @return a string of url.
     */
    public function getURL($name)
    {
        switch ($name)
        {
            case 'host':
                return $this->_host;
            case 'authorize':
                return $this->_authorizeURL;
            case 'accesstoken':
                return $this->_accessTokenURL;
        }
    }

    /**
     * Make authorize URL for request authorize code
     *
     * @param string $response_type
     *                 = 'code'     Get authorize code.
     *                 = 'token'    It's Implicit Grant mode.
     *
     * @return a new access token and refresh token.
     */
    public function getAuthorizeURL($responseType, $redirectUri, $scope=null, $state=null, $display=null)
    {
        $data = array(
            'client_id'         => $this->_clientId,
            'response_type'     => $responseType,
            'redirect_uri'      => $redirectUri,
        );
        if(!empty($scope))      $data['scope'] = $scope;
        if(!empty($state))      $data['state'] = $state;
        if(!empty($display))    $data['display'] = $display;
        $query = $this->http->buildHttpQuery($data);
        return $this->_authorizeURL . "?{$query}";
    }

    /**
     * Get access token by refresh token
     *
     * @param string $code     Authorized Code get by send HTTP Authorize request.
     *
     * @return a new access token and refresh token.
     */
    public function getAccessTokenByCode($code, $redirectUri)
    {
        $data = array(
            'grant_type'       => "authorization_code",
            'code'             => $code,
            'client_id'        => $this->_clientId,
            'client_secret'    => $this->_clientSecret,
            'redirect_uri'     => $redirectUri,
            'scope'            => 'basic'
        );

        $request = $this->call('get', $this->_accessTokenURL, $data);
        return $request;
    }


    /**
     * Get access token by refresh token
     *
     * @param string $refresh_token     A string of refresh token.
     * @param string $scope             Scope limit.
     *
     * @return a new access token and refresh token.
     */
    function getAccessTokenByRefreshToken($refresh_token, $scope)
    {
        $data = array(
            'grant_type'    => "refresh_token",
            'refresh_token' => $refresh_token,
            'client_id'     => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'scope'         => $scope,
        );
        $request = $this->call('get', $this->_accessTokenURL, $data);
        return $request;
    }

    /**
     * Make an POST/GET request
     *
     * @param string $method  It's "GET" or "POST".
     * @param string $url     A request url like "https://example.com".
     * @param array  $data    An array to make query string like "example1=&example2=" .
     *
     * @return API results.
     */
    public function call($method, $url, $data = array() )
    {
        return $this->http->$method($url, $data);
    }

}
