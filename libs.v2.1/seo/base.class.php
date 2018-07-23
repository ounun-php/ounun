<?php
namespace seo;

class base
{
    protected $_url;
    protected $_http_host;
    protected $_requst_uri;

    protected $_tag;
    protected $_key;
    protected $_domain;

    protected $_data;

    protected $_seo_title;
    protected $_seo_keywords;
    protected $_seo_description;
    protected $_seo_h1;

    /**
     * _p constructor.
     * @param $key
     * @param string $title
     * @param int $mod_id
     * @param int $data_id
     * @param int $category_id
     */
    public function __construct($key='')
    {
        $this->_url         = '//'.$this->_http_host.$this->_requst_uri;
        $this->_http_host   = $_SERVER['HTTP_HOST'];
        $this->_requst_uri  = $_SERVER['REQUEST_URI'];

        $this->_tag         = explode('.',$this->_http_host,2)[0];
        $this->_domain      = cfg_domain::domain($this->_http_host);

        $this->_data        = [];
        if($key)
        {
            $this->set_key($key);
        }
    }

    /**
     * 设定TKD
     * @param $title
     * @param $keywords
     * @param $description
     */
    public function set_tkd($title,$keywords,$description,$h1='')
    {
        $this->_seo_title       = $title;
        $this->_seo_keywords    = $keywords;
        $this->_seo_description = $description;
        $this->_seo_h1          = $h1;
    }

    /**
     * 设定key
     * @param $key
     */
    public function set_key($key)
    {
        $this->_key         = $key?"{$this->_http_host}.{$key}":$this->_http_host;
    }
    
    /**
     * @param $key
     * @param $val
     */
    public function set($key,$val)
    {
        $this->_data[$key] = $val;
    }

    /**
     * TKD数据
     * @return array
     */
    public function tkd()
    {
        return [
            '{$seo_title}'        => $this->_seo_title,
            '{$seo_keywords}'     => $this->_seo_keywords,
            '{$seo_description}'  => $this->_seo_description,
            '{$seo_h1}'           => $this->_seo_h1,

            '{$etag}'             => $this->_tag,
            '{$ekey}'             => $this->_key,
        ]+$this->_data;
    }

    /**
     * 当前域名
     * @return string
     */
    public function domain()
    {
        return $this->_domain;
    }
}