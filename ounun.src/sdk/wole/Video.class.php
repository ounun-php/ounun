<?php
/** 命名空间 */
namespace sdk\wole;
/**
 * Video_All 
 * @todo get video list from http://video.56.com 
 *  
 * @param $type
 * @param $t
 * @param $c
 * @param $page
 * @param $rows
 * 
 type:	
 hot为最多观看，
 ding为最多推荐，
 new为最新发布，
 share为最多引用，
 comment为最多评论
 t:
 today今日，
 week本周，
 month本月
 c:	
 3=>'原创', 
 26=>'游戏',
 1=>'娱乐', 
 34=>'亲子', 
 28=>'汽车', 
 27=>'旅游', 
 11=>'女性', 
 14=>'体育', 
 8=>'动漫',  
 10=>'科教', 
 2=>'资讯', 
 39=>'财富',
 40=>'科技',
 41=>'音乐',
 0=>'所有',
 4=>'搞笑',
 * 
 * @uses ApiAbstract
 * @package 
 * @copyright 56.com
 * @author Louis Li <email:zixing.li@renren-inc.com;QQ:838431609> 
 */
class Video extends ApiAbstract
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
	public function all($params)
    {
		$url=$this->domain.'/video/all.json';
		return self::getHttp($url,$params);
    }

    /**
     * @description 获得频道的视频
     *
     * @access public
     *	$params=array('cid'=>$cid, 'page'=>$page, 'num'=>$num);
     *$cid = '68', $page = '1', $num = '20'
     * @param string $cid
     * @param string $page
     * @param string $num
     * @return json
     */
    public function channel($params)
    {
        $url=$this->domain.'/video/channel.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 复杂上传组件地址
     *
    $params=array('sid'=> $sid,'css'=> $css ,'rurl'=> $rurl,'ourl'=> $ourl);
     * @param $sid 第三方的应用的用户名
     * @param $css 获取的样式加密码
     * @param $rurl 失败时跳转的页面，获取返回信息
     * @param $ourl 成功时跳转的页面，获取返回信息
     * @return plugin
     */
    public function custom($params)
    {
        $url=$this->domain."/video/custom.plugin";
        //var_dump($url.'?'.self::signRequest($params));
        return $url.'?'.self::signRequest($params);
    }
    /**
     * @description 复杂上传组件地址
     *
    $params=array('sid'=> $sid,'css'=> $css ,'rurl'=> $rurl,'ourl'=> $ourl);
     * @param $sid 第三方的应用的用户名
     * @param $css 获取的样式加密码
     * @param $rurl 失败时跳转的页面，获取返回信息
     * @param $ourl 成功时跳转的页面，获取返回信息
     * @return plugin
     */
    public function customEasy($params)
    {
        $url=$this->domain."/video/customEasy.plugin";
        return $url.'?'.self::signRequest($params);
    }
    /**
     * @description CustomPost upload 地址
     *
    $params=array('sid'=> $sid,'css'=> $css ,'rurl'=> $rurl,'ourl'=> $ourl);
     * @param $sid 第三方的应用的用户名
     * @param $css 获取的样式加密码
     * @param $rurl 失败时跳转的页面，获取返回信息
     * @param $ourl 成功时跳转的页面，获取返回信息
     * @return json
     */
    public function customPost($params)
    {
        $url=$this->domain."/video/customPost.json";
        return $url.'?'.self::signRequest($params);
    }
    /**
     * @name Get
     * @todo  delete a video
     * @author Louis
     *
     * @param string $params
    $params = array(
    'vid'=>'NzAxOTI2MzM',
    );
     * @access public
     * @return array
     */
    public function delete($params)
    {
        $url=$this->domain."/video/delete.json";
        return self::getHttp($url,$params);
    }
    /**
     * @description Diy复杂上传组件地址
     *
    $params=array('fields'=>'title,content,tags','sid'=> $sid,'css'=> $css ,'rurl'=> $rurl,'ourl'=> $ourl);
     * @param $fields自定义选项
     * @param $sid 第三方的应用的用户名
     * @param $css 获取的样式加密码
     * @param $rurl 失败时跳转的页面，获取返回信息
     * @param $ourl 成功时跳转的页面，获取返回信息
     * @return plugin
     */
    public function diyupload($params)
    {
        $url=$this->domain."/video/diyupload.plugin";
        return $url.'?'.self::signRequest($params);
    }
    /**
     * @description 获取视频信息
     *
    $params=array('vid'=>$flvid);
     * @param $flvid 56视频的flvid
     * @link /video/getVideoInfo.json
     * @return json
     */
    public function getVideoInfo($params)
    {
        $url=$this->domain.'/video/getVideoInfo.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description default:获得56网首页热门的视频
     *
     * @access public
     * @param $paramsarray('cid'=>$cid, 'page'=>$page, 'num'=>$num);
     * @param string $cid default 2
     * @param string $page default 1
     * @param string $num default 10
     * @return json
     */
    public function hot($params)
    {
        $url=$this->domain.'/video/hot.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取视频信息
     *
    $params=array('vid'=>$flvid);
     * @param $flvid 56视频的flvid
     * @link /video/mobile.json
     * @return json
     */
    public function mobile($params)
    {
        $url=$this->domain.'/video/mobile.json';
        return self::getHttp($url,$params);
    }
    /**
     * @name Get
     * @todo
     * @author Louis
     *
     * @param string $params
     * @access public
     * @return array
     */
    public function opera($params)
    {
        $url=$this->domain.'/video/opera.json';
        return self::getHttp($url,$params);
    }

    /**
     * @description 获得56网昨天或某天的推荐的相册视频
     *
     * @access public
    $params=array('day'=>20120705);
     * @param mixed $day
     * @return json|void
     */
    public function recAlbum($params)
    {
        $url=$this->domain.'/video/recAlbum.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获得推荐频道的视频
     *
     * @access public
    $params=array('mid'=>$mid, 'page'=>$page, 'num'=>$num);
     * @param string $mid default 16
     * @param string $page default 1
     * @param string $num default 10
     * @return json
     */
    public function recommend($params)
    {
        $url=$this->domain.'/video/recommend.json';
        return self::getHttp($url,$params);
    }
    /*
	* @description 根据关键字获取搜索结果
	*   $params = array(
	*       'keyword'=> $keyword,  //要查找的关键字
	*       'c'=>1,
	*       't'=>'month', 时间，默认为month
	*       's'=>1,
	*       'page'=>1,     当前页数
	*       'rows'=>$rows, 10 每页显示多少个
	*    );
	* @param $keyword 主要的字段，关键字搜索，其他的默认即可
	* @link  /video/search.json
	* @return json
	*/
    public function  search($params)
    {
        $url=$this->domain.'/video/search.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 获取更新视频信息的接口
     *
    $params=array('vid'=>$flvid,'title'=>$title,'desc'=>$desc,'tag'=>$tag);
     * @param $flvid 56视频的flvid
     * @param $title 56视频的名称
     * @param $desc  56视频的名称的描述
     * @param $tag   56视频的标签
     * @link  /video/update.json
     * @return json
     */
    public function update($params)
    {
        $url=$this->domain.'/video/update.json';
        return self::getHttp($url,$params);
    }
    /**
     * @description 简易上传组件地址
     *
     * @return plugin
     */
    public function upload($params)
    {
        $url=$this->domain."/video/upload.plugin";
        return $url.'?'.self::signRequest($params);
    }
}
