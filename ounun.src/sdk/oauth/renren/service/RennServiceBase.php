<?php
namespace sdk\oauth\renren\service;

class RennServiceBase
{
	protected $client;
	protected $accessToken;
	
	/**
	 * 构造函数
	 */
	function __construct($client, $accessToken)
    {
		$this->client       = $client;
		$this->accessToken  = $accessToken;
	}
}