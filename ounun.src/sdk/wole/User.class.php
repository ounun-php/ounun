<?php
/** 命名空间 */
namespace sdk\wole;

class User extends ApiAbstract
{
	/**
	* @description 获取当前应用上传视频列表(新)
	* @param page->页码，rows->每页显示多少
	* @link  /user/app2Videos.json 
	* @return json
	*/
	public function app2Videos($params)
    {
		$url = $this->domain.'/user/app2Videos.json';
		return self::getHttp($url,$params);
	}

    /**
     * @description 获取应用信息(新)
     * @link  /user/appProfile.json
     * @return json
     */
    public function appProfile($params)
    {
        $url = $this->domain.'/user/appProfile.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取某应用下某用户的视频列表(新)
     * @param sid->第三方的用户标识，字符串型。，page->页码，rows->每页显示多少
     * @link  /user/appUserVideos.json
     * @return json
     */
    public function appUserVideos($params)
    {
        $url = $this->domain.'/user/appUserVideos.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取应用上传的视频
     *
     * @param 获得该appid下所有上传的视频
     * @param s->按时间排序，page->页码，rows->每页显示多少
     * @link  /user/appVideos.json
     * @return json
     */
    public function appVideos($params)
    {
        $url = $this->domain.'/user/appVideos.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获得用户的评论或视频的评论
     *
     * $params=array('tid'=>$tid,'access_token'=>$token,'type'=> $type,'page'=>1,'rows'=>10, 'pct'=> $pct);
     * $tid = 'onesec', $type = 'user', $pct = 1
     * @param $tid 用户在56网站的user_id或视频的flvid
     * @param $type user/flv
     * @param $token oauth2认证后的令牌
     * @param $pct  1为普通视频 3是相册视频
     * @return json
     */
    public function userComments($params)
    {
        $url=$this->domain.'/user/userComments.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取用户的个人信息
     * 		$params=array('userid'=>$userid,'access_token'=>$token);
     * @param $userid 用户在56网站的user_id或视频的flvid
     * @param $token oauth2认证后的令牌
     * @link  /user/userProfile.json
     * @return json
     */
    public function userProfile($params)
    {
        $url=$this->domain.'/user/userProfile.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取用户的上传的视频
     *
     * $params=array('userid'=>$userid,'access_token'=>$token,'s'=>'time','page'=>1,'rows'=>10);
     * @param $userid 用户在56网站的user_id或视频的flvid
     * @param $token oauth2认证后的令牌
     * @link  /user/userVideos.json
     * @return json
     */
    public function userVideos($params)
    {
        $url=$this->domain.'/user/userVideos.json';
        return self::getHttp($url,$params);
    }
}
