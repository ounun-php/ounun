<?php
namespace sdk\oauth;
/**
 * @brief 设置session配置 
 */

class Session
{
    /**
     * Cache handle
     * @var \ounun\Cache
     */
    private $_cache;
    /**
     * session-lifetime 默认时间
     * @var int
     */
    private $_life_time;
    /**
     * session_id
     * @var string
     */
    private $_session_id;
    /**
     * 类型session_type
     * @var string
     */
    private $_session_type;

    /**
     * 构造函数
     * @param \ounun\Cache $cache
     * @param int $life_time
     */
    public function __construct(array $cache_config,$life_time=72000,$session_type='OID')
    {
        $this->_cache           = new \ounun\Cache();
        $cache_config['expire'] = $life_time;
        $this->_cache->config($cache_config,$session_type);

        $this->_session_type    = $session_type;
        $this->_life_time       = $life_time;
        $this->_session_id      = null;
    }

    public function start()
    {
        if(null == $this->_session_id)
        {
            $this->_session_id = $_COOKIE[$this->_session_type]?$_COOKIE[$this->_session_type]:'OS'.md5( uniqid(rand(), TRUE) );
        }
        setcookie($this->_session_type,$this->_session_id,time()+72000000,'/');
        return $this->_session_id;
    }

    public function session_id($session_id=null)
    {
        if(null == $this->_session_id)
        {
            trigger_error("ERROR! _session_id:null not start.", E_USER_ERROR);
        }
        if($session_id)
        {
            $this->_session_id = $session_id;
        }
        setcookie($this->_session_type,$this->_session_id,time()+72000000,'/');
        return $this->_session_id;
    }
    /**
     * 读session
     * @param $session_key
     * @return mixed|null
     */
    public function read($session_key)
    {
        $this->_cache->key($this->_session_id);
        return $this->_cache->get($session_key);
    }
    /**
     * 读出所有
     * @return mixed|null
     */
    public function read_all()
    {
        $this->_cache->key($this->_session_id);
        return $this->_cache->read();
    }
    /**
     * 写session
     * @param $session_key
     * @param $session_data
     * @return bool
     */
    public function write($session_key, $session_data)
    {
        $this->_cache->key($this->_session_id);
        $this->_cache->set($session_key,$session_data);
        return $this->_cache->write();
    }
    /**
     * 清理session
     * @return bool
     */
    public function clean()
    {
        $this->_cache->key($this->_session_id);
        setcookie($this->_session_type,'',time()-7200,'/');
        return $this->_cache->delete();
    }
}
?>
