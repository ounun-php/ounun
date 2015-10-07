<?php
/** 命名空间 */
namespace sdk\wole;
/**
 * Album_Info 
 * 
 * @uses ApiAbstract
 * @package 
 * @copyright 56.com
 * @author Louis Li <email:zixing.li@renren-inc.com;QQ:838431609> 
 */
class Album extends ApiAbstract
{
    /**
     * @name Get 
     * @todo  
     * @author Louis 
     * 
     * @param string $params 
     * @access public
     * @return array
     */
	public function info($params)
    {
		$url=$this->domain.'/album/info.json';
		return self::getHttp($url,$params);
	}
}
