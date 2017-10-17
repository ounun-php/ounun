<?php
namespace ounun;

/**
 * 强制要求子类定义这些方法
 * Class _cache_base
 * @package ounun
 */
abstract class _cache_base
{
	/**
	 * @var string 模块名称 */
	protected $_mod;
	
    /**
     * 设定数据keys
     * @param $key       */
	abstract public function key($key);
	
    /**
     * 设定数据Value
     * @param $val       */
	abstract public function val($val);
	
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
	abstract public function read();
	
    /**
     * 写入已设定的数据
     * @return bool       */
	abstract public function write();
	
    /**
     * 读取数据中$key的值
     * @param $sub_key        */
	abstract public function get($sub_key);
	
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
	abstract public function set($sub_key,$sub_val);
	
    /**
     * 删除数据
     * @return bool       */
	abstract public function delete();
	
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
	abstract public function filename();
	
	/**
	 * 取得 mod:名称
	 * @return string     */
	abstract public function mod();
}


class _cache_file extends _cache_base
{
	/** @var string 存放路径 */
	private $_root			= '';
    
	/** @var bool false:混合数据 true:字符串 */
	private $_format_string	= false;
    
	/** @var bool false:少量    true:大量 */
	private $_large_scale	= false;

	/** @var string cache文件名称 */
	private $_filename		= null;
    
	/** @var mix    数据 */
	private $_data   		= null;
    
	/** @var bool false:没读    true:已读 */
	private $_is_read       = false;
    
	/**
	 * 设定 file Cache配制
	 * @param string $mod
	 * @param $root
	 * @param bool|false $large_scale
	 * @return bool
	 */
	public function __construct($mod='def',$root=Dir_Cache,$format_string=false,$large_scale=false)
	{
		$this->_mod   		  = $mod;
		$this->_root  		  = $root;
		$this->_large_scale   = $large_scale;
		$this->_format_string = $format_string;
	}

    /**
     * 设定数据keys
     * @param $key      */
	public function key($key)
	{
		if($this->_large_scale)
		{
			$key   	          = md5($key);
			$key   	          = "{$key[0]}{$key[1]}/{$key[2]}{$key[3]}/".substr($key,4);
		}
		if($this->_format_string)
		{
			$this->_filename  = "{$this->_root}{$this->_mod}/{$key}.z";
			$this->_data	  = '';
			$this->_is_read   = false;
		}else
		{
			$this->_filename  = "{$this->_root}{$this->_mod}/{$key}.php";
			$this->_data	  = null;
			$this->_is_read   = false;
		}
	}
    
    /**
     * 设定数据Value
     * @param $val      */
	public function val($val)
	{
        $this->_is_read  = true;
		$this->_data     = $val;
	}
    
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
	public function read()
	{
		if($this->_is_read)
		{
			return $this->_data;
		}
		// read
		$this->_is_read  = true;
		if (file_exists($this->_filename))
		{
			if($this->_format_string)
			{
				$this->_data = file_get_contents($this->_filename);
			}else
			{
				$this->_data = require $this->_filename;
			}
		}
		return $this->_data;
	}
    
    /**
     * 写入已设定的数据
     * @return bool       */
	public function write()
	{
		if(false == $this->_is_read)
		{
			trigger_error("ERROR! \$this->_data:null.", E_USER_ERROR);
		}
		$filedir    = dirname($this->_filename);
		if(!is_dir($filedir))
		{
			mkdir($filedir, 0777, true);
		}
		if(file_exists($this->_filename))
		{
			unlink($this->_filename);
		}
		if($this->_format_string)
		{
			return file_put_contents($this->_filename,$this->_data);
		}else
		{
			$str	= var_export($this->_data,    true);
			return file_put_contents($this->_filename,'<?php '."return {$str};".'?>');
		}
	}
    
    /**
     * 读取数据中$key的值
     * @param $sub_key        */
	public function get($sub_key)
	{
		if($this->_format_string)
		{
			trigger_error("ERROR! format_string.", E_USER_ERROR);
		}
		if(!$this->_is_read)
		{
			$this->read();
		}
		if($this->_data)
		{
			return $this->_data[$sub_key];
		}
		return null;
	}
    
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
	public function set($sub_key,$sub_val)
	{
		if($this->_format_string)
		{
			trigger_error("ERROR! format_string.", E_USER_ERROR);
		}
		if(!$this->_is_read)
		{
			$this->read();
		}
        if(!$this->_data)
        {
            $this->_data   = array();
        }
		$this->_data[$sub_key] = $sub_val;
	}
    
    /**
     * 删除数据
     * @return bool       */
	public function delete()
	{
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = false;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = false;
        }
		if (file_exists($this->_filename))
		{
			return unlink($this->_filename);
		}
		return true;
	}
    
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
	public function filename()
	{
		return $this->_filename;
	}
    
	/**
	 * 取得 mod:名称
	 * @return string     */
	public function mod()
	{
		return $this->_mod;
	}
}

class _cache_redis extends _cache_base
{
    /** @var array Redis服务器配制 */
    private $_redis_config	= array();
    
    /** @var \Redis */
    private $_redis			= null;

    /** @var int */
    private $_expire	    = 0;
    
    /** @var bool false:混合数据 true:字符串 */
    private $_format_string	= false;
    
    /** @var bool false:少量    true:大量 */
    private $_large_scale	= false;

    /** @var string key */
    private $_key		    = null;
    
    /** @var mix    数据 */
    private $_data   		= null;
    
    /** @var bool false:没读    true:已读 */
    private $_is_read       = false;

    /**
     * _cache_redis constructor.
     * @param string $mod
     * @param int $expire
     * @param bool $large_scale
     * @param bool $format_string
     */
    public function __construct($mod='def',$expire=0,$large_scale=false,$format_string=false,$auth=null)
    {
		$this->_mod   		    = $mod;
        $this->_redis_config	= array();
        $this->_redis	        = null;
        $this->_auth            = $auth;

        /** @var int */
        $this->_expire	        = $expire;
        $this->_large_scale     = $large_scale;
        $this->_format_string   = $format_string;
    }
    
    /**
     * 设定Redis服务器
     * @param array $servers array(['host','port'],['host','port'],...)
     * @return bool
     */
    public function connect($host,$port)
    {
        $port   = (int)$port;
        // config
        $this->_redis_config[] = array('host'=>$host,'port'=>$port);
        // addServer
        if(null == $this->_redis)
        {
            $this->_redis = new \Redis();
        }
        if($host && $port)
        {
            $this->_redis->connect($host,$port);
            if($this->_auth && $this->_auth['password'])
            {
                $this->_redis->auth($this->_auth['password']);
            }
        }else
        {
            trigger_error("ERROR! Redis::Arguments Error!.", E_USER_ERROR);
        }
    }

    /**
     * 设定数据keys
     * @param $key       */
    public function key($key)
    {
        if($this->_large_scale)
        {
            $key   	          = md5($key);
        }
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = false;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = false;
        }
        $this->_key           = "{$this->_mod}.{$key}";
    }
    
    /**
     * 设定数据Value
     * @param $val       */
    public function val($val)
    {
        $this->_is_read  = true;
        $this->_data     = $val;
    }
    
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
    public function read()
    {
        if($this->_is_read)
        {
            return $this->_data;
        }
        // read
        $this->_is_read  = true;
        if($this->_format_string)
        {
            $this->_data     = $this->_redis->get($this->_key);
        }else
        {
            $str             = $this->_redis->get($this->_key);
            $this->_data     = unserialize($str);
        }
        return $this->_data;
    }
    
    /**
     * 写入已设定的数据
     * @return bool       */
    public function write()
    {
        if(false == $this->_is_read)
        {
            trigger_error("ERROR! \$this->_data:null.", E_USER_ERROR);
        }
        if($this->_format_string)
        {
            return $this->_redis->set($this->_key,$this->_data,$this->_expire);
        }else
        {
            $str	= serialize($this->_data);
            return $this->_redis->set($this->_key,$str,$this->_expire);
        }
    }
    
    /**
     * 读取数据中$key的值
     * @param $sub_key       */
    public function get($sub_key)
    {
        if($this->_format_string)
        {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if(!$this->_is_read)
        {
            $this->read();
        }
        if($this->_data)
        {
            return $this->_data[$sub_key];
        }
        return null;
    }
    
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
    public function set($sub_key,$sub_val)
    {
        if($this->_format_string)
        {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if(!$this->_is_read)
        {
            $this->read();
        }
        if(!$this->_data)
        {
            $this->_data   = array();
        }
        $this->_data[$sub_key] = $sub_val;
    }
    
    /**
     * 删除数据
     * @return bool       */
    public function delete()
    {
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = false;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = false;
        }
        return $this->_redis->delete($this->_key);
    }
    
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
    public function filename()
    {
        return $this->_key;
    }
    
	/**
	 * 取得 mod:名称
	 * @return string     */
	public function mod()
	{
		return $this->_mod;
	}
}

class _cache_memcache extends _cache_base
{
	/** @var array Memcache服务器配制 */
	private $_mem_config		= array();
    
	/** @var \Memcache */
	private $_mem				= null;

    /** @var int */
    private $_expire	        = 0;
    
    /** @var bool false:混合数据 true:字符串 */
    private $_format_string	    = false;
    
    /** @var bool false:少量    true:大量 */
    private $_large_scale	    = false;
    
	/** @var int */
	private $_zip_threshold		= 5000; // 5k
    
	/** @var int */
	private $_zip_min_saving	= 0.3;  // 30%
    
	/** @var int */
	private $_flag	            = MEMCACHE_COMPRESSED;
    
	/** @var string key */
	private $_key		    = null;
    
	/** @var mix    数据 */
	private $_data   		= null;
    
	/** @var bool false:没读    true:已读 */
	private $_is_read       = false;
    
	/**
	 * 设定Memcache服务器
	 * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
	 * @return bool
	 */
	public function __construct($mod='def',$expire=0,$format_string=false,$large_scale=false,$zip_threshold=5000,$zip_min_saving=0.3,$flag=MEMCACHE_COMPRESSED)
	{
		$this->_mod   		    = $mod;
		$this->_mem_config	    = array();
        $this->_mem	            = null;

        $this->_expire          = $expire;
        $this->_large_scale     = $large_scale;
        $this->_format_string   = $format_string;
		$this->_zip_threshold   = $zip_threshold;
		$this->_zip_min_saving  = $zip_min_saving;
		$this->_flag            = $flag;
	}
    
	/**
	 * 设定Memcache服务器
	 * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
	 * @return bool
	 */
	public function add_server($host,$port,$weight)
	{
		$port   = (int)$port;
		$weight = (int)$weight;
		// config
		$this->_mem_config[] = array('host'=>$host,'port'=>$port,'weight'=>$weight);
		// addServer
		if(null == $this->_mem)
		{
			$this->_mem = new \Memcache();
		}
		if($host && $port && $weight)
		{
			$this->_mem->addServer($host,$port,true,$weight);
			$this->_mem->setCompressThreshold($this->_zip_threshold, $this->_zip_min_saving);
		}else
		{
			trigger_error("ERROR! Memcache::Arguments Error!.", E_USER_ERROR);
		}
		if(!$this->_mem->getStats())
		{
			trigger_error("ERROR! Memcache::getStats Error!.", E_USER_ERROR);
		}
	}
    
    /**
     * 设定数据keys
     * @param $key       */
	public function key($key)
	{
		if($this->_large_scale)
		{
			$key   	          = md5($key);
		}
		if($this->_format_string)
		{
			$this->_data	  = '';
			$this->_is_read   = false;
		}else
		{
			$this->_data	  = null;
			$this->_is_read   = false;
		}
		$this->_key           = "{$this->_mod}.{$key}";
	}
    
    /**
     * 设定数据Value
     * @param $val       */
	public function val($val)
	{
        $this->_is_read  = true;
		$this->_data     = $val;
	}
    
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
	public function read()
	{
		if($this->_is_read)
		{
			return $this->_data;
		}
		// read
		$this->_is_read  = true;
		$this->_data     = $this->_mem->get($this->_key);
		return $this->_data;
	}
    
    /**
     * 写入已设定的数据
     * @return bool       */
	public function write()
	{
        if(false == $this->_is_read)
		{
			trigger_error("ERROR! \$this->_data:null.", E_USER_ERROR);
		}
		return $this->_mem->set($this->_key,$this->_data,$this->_flag,$this->_expire);
	}
    
    /**
     * 读取数据中$key的值
     * @param $sub_key        */
	public function get($sub_key)
	{
		if($this->_format_string)
		{
			trigger_error("ERROR! format_string.", E_USER_ERROR);
		}
		if(!$this->_is_read)
		{
			$this->read();
		}
		if($this->_data)
		{
			return $this->_data[$sub_key];
		}
		return null;
	}
    
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
	public function set($sub_key,$sub_val)
	{
		if($this->_format_string)
		{
			trigger_error("ERROR! format_string.", E_USER_ERROR);
		}
		if(!$this->_is_read)
		{
			$this->read();
		}
        if(!$this->_data)
        {
            $this->_data   = array();
        }
        $this->_data[$sub_key] = $sub_val;
	}
    
    /**
     * 删除数据
     * @return bool       */
	public function delete()
	{
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = true;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = true;
        }
		return $this->_mem->delete($this->_key);
	}
    
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
	public function filename()
	{
		return $this->_key;
	}
    
	/**
	 * 取得 mod:名称
	 * @return string     */
	public function mod()
	{
		return $this->_mod;
	}
}



class _cache_memcached extends _cache_base
{
    /** @var array Memcache服务器配制 */
    private $_mem_config		= array();
    
    /** @var \Memcached */
    private $_mem				= null;

    /** @var int */
    private $_expire	        = 0;
    
    /** @var array */
    private $_auth	            = false;
    
    /** @var bool false:混合数据 true:字符串 */
    private $_format_string	    = false;
    
    /** @var bool false:少量    true:大量 */
    private $_large_scale	    = false;

    /** @var string key */
    private $_key		    = null;
    
    /** @var mix    数据 */
    private $_data   		= null;
    
    /** @var bool false:没读    true:已读 */
    private $_is_read       = false;
    
    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function __construct($mod='def',$expire=0,$format_string=false,$large_scale=false,$auth=false)
    {
        $this->_mod   		    = $mod;
        $this->_mem_config	    = array();
        $this->_mem	            = null;

        $this->_expire          = $expire;
        $this->_auth            = $auth;
        $this->_large_scale     = $large_scale;
        $this->_format_string   = $format_string;
    }
    
    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function add_server($host,$port,$weight)
    {
        $port   = (int)$port;
        $weight = (int)$weight;
        // config
        $this->_mem_config[] = array('host'=>$host,'port'=>$port,'weight'=>$weight);
        // addServer
        if(null == $this->_mem)
        {
            $this->_mem = new \Memcached();
            $this->_mem->setOption(\Memcached::OPT_COMPRESSION,     false); //关闭压缩功能
            $this->_mem->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);  //使用binary二进制协议
        }
        if($host && $port && $weight)
        {
            $this->_mem->addServer($host,$port,$weight);
            if($this->_auth && $this->_auth['username'] && $this->_auth['password'])
            {
                $this->_mem->setSaslAuthData($this->_auth['username'], $this->_auth['password']);
            }
        }else
        {
            trigger_error("ERROR! Memcached::Arguments Error!.", E_USER_ERROR);
        }
//        if(!$this->_mem->getStats())
//        {
//            trigger_error("ERROR! Memcached::getStats Error!.", E_USER_ERROR);
//        }
    }
    
    /**
     * 设定数据keys
     * @param $key       */
    public function key($key)
    {
        if($this->_large_scale)
        {
            $key   	      = md5($key);
        }
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = false;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = false;
        }
        $this->_key           = "{$this->_mod}.{$key}";
    }
    
    /**
     * 设定数据Value
     * @param $val       */
    public function val($val)
    {
        $this->_is_read  = true;
        $this->_data     = $val;
    }
    
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
    public function read()
    {
        if($this->_is_read)
        {
            return $this->_data;
        }
        // read
        $this->_is_read  = true;
        $this->_data     = $this->_mem->get($this->_key);
        return $this->_data;
    }
    
    /**
     * 写入已设定的数据
     * @return bool       */
    public function write()
    {
        if(false == $this->_is_read)
        {
            trigger_error("ERROR! \$this->_data:null.", E_USER_ERROR);
        }
        return $this->_mem->set($this->_key,$this->_data,$this->_expire);
    }
    
    /**
     * 读取数据中$key的值
     * @param $sub_key        */
    public function get($sub_key)
    {
        if($this->_format_string)
        {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if(!$this->_is_read)
        {
            $this->read();
        }
        if($this->_data)
        {
            return $this->_data[$sub_key];
        }
        return null;
    }
    
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
    public function set($sub_key,$sub_val)
    {
        if($this->_format_string)
        {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if(!$this->_is_read)
        {
            $this->read();
        }
        if(!$this->_data)
        {
            $this->_data   = array();
        }
        $this->_data[$sub_key] = $sub_val;
    }
    
    /**
     * 删除数据
     * @return bool       */
    public function delete()
    {
        if($this->_format_string)
        {
            $this->_data	  = '';
            $this->_is_read   = true;
        }else
        {
            $this->_data	  = null;
            $this->_is_read   = true;
        }
        return $this->_mem->delete($this->_key);
    }
    
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
    public function filename()
    {
        return $this->_key;
    }
    
    /**
     * 取得 mod:名称
     * @return string     */
    public function mod()
    {
        return $this->_mod;
    }
}



class cache
{
    const Type_File         = 1;
    const Type_Memcache     = 2;
    const Type_Redis        = 3;
    const Type_Memcached    = 4;

	/**  @var _cache_base  */
	private   $_drive		= null;
    
	/** @var int 驱动类型  0:[错误,没设定驱动] 1:File 2:Memcache 3:Redis */
	protected $_type		= 0;
    
	/**
	 * 构造函数
	 */
    public function __construct()
    {
        $this->_type   = 0;
    }
	/**
	 * 设定 Cache配制
	 * @param array $config Cache配制
		$GLOBALS['scfg']['cache1'] = array
		(
			'type' 			=> \ounun\Cache::Type_File,
			'mod'  			=> 'html',
			'root' 			=> Dir_Cache,
			'format_string' => false,
			'large_scale' 	=> true,
		);
		$GLOBALS['scfg']['cache2'] = array
		(
			'type'          => \ounun\Cache::Type_Memcache,
			'mod'  			=> 'html',
			'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
			'zip_threshold' => 5000,
			'zip_min_saving'=> 0.3,
			'expire'        => (3600*24*30 - 3600),
			'flag'          => MEMCACHE_COMPRESSED,
			'format_string' => false,
			'large_scale' 	=> true,
		);
        $GLOBALS['scfg']['cache2'] = array
        (
            'type'          => \ounun\Cache::Type_Memcached,
            'mod'  			=> 'html',
            'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
            'auth'          => array('username'=>'username','password'=>'password'),
            'expire'        => (3600*24*30 - 3600),
            'format_string' => false,
            'large_scale' 	=> true,
        );
		$GLOBALS['scfg']['cache3'] = array
		(
			'type'    		=> \ounun\Cache::Type_Redis,
			'mod'  			=> 'html',
			'sfg'     		=> array(array('host'=>'192.168.1.181','port'=>6379)),
			'expire'  		=> (3600*24*30 - 3600),
	 		'format_string' => false,
			'large_scale' 	=> true,
		);
	 */
	public function config($config,$mod=null)
	{
        $mod                 = $mod?$mod:$config['mod'];
		$type_list           = [self::Type_File,self::Type_Memcache,self::Type_Memcached,self::Type_Redis];
		$type                = in_array($config['type'],$type_list)?$config['type']:self::Type_File;
		if(self::Type_Redis == $type)
		{
			$sfg             = $config['sfg'];
			$expire          = $config['expire'];
            $auth            = $config['auth'];
			$format_string   = $config['format_string'];
			$large_scale     = $config['large_scale'];
			$this->config_redis($sfg,$mod,$expire,$large_scale,$format_string,$auth);
		}elseif(self::Type_Memcache == $type)
		{
			$sfg             = $config['sfg'];
			$zip_threshold   = $config['zip_threshold'];
			$zip_min_saving  = $config['zip_min_saving'];
			$expire          = $config['expire'];
			$flag            = $config['flag'];
			$format_string   = $config['format_string'];
			$large_scale     = $config['large_scale'];
			$this->config_memcache($sfg,$mod,$expire,$format_string,$large_scale,$zip_threshold,$zip_min_saving,$flag);
        }elseif(self::Type_Memcached == $type)
        {
            $sfg             = $config['sfg'];
            $expire          = $config['expire'];
            $auth            = $config['auth'];
            $format_string   = $config['format_string'];
            $large_scale     = $config['large_scale'];
            $this->config_memcached($sfg,$mod,$expire,$format_string,$large_scale,$auth);
		}else //if(self::Type_File == $type)
		{
			$root            = $config['root'];
			$format_string   = $config['format_string'];
			$large_scale     = $config['large_scale'];
			$this->config_file($mod,$root,$format_string,$large_scale);
		}
	}
    
	/**
	 * 设定 file Cache配制
	 * @param string $mod
	 * @param $root
	 * @param bool|false $large_scale
	 * @return bool
	 */
	public function config_file($mod='def',$root=Dir_Cache,$format_string=false,$large_scale=false)
	{
		if(0 == $this->_type)
		{
			$this->_type	= self::Type_File;
			$this->_drive	= new _cache_file($mod,$root,$format_string,$large_scale);
		}else
		{
			trigger_error("ERROR! Repeat Seting:Cache->config_file().", E_USER_ERROR);
		}
	}
    
	/**
	 * 设定Memcache服务器
	 * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
	 * @return bool
	 */
	public function config_memcache(array $servers,$mod='def',$expire=0,$format_string=false,$large_scale=false,$zip_threshold=5000,$zip_min_saving=0.3,$flag=MEMCACHE_COMPRESSED)
	{
		if(0 == $this->_type)
		{
			$this->_type	= self::Type_Memcache;
			$this->_drive	= new _cache_memcache($mod,$expire,$format_string,$large_scale,$zip_threshold,$zip_min_saving,$flag);
			if(is_array($servers))
			{
				foreach($servers as $v)
				{
					$this->_drive->add_server($v['host'],$v['port'],$v['weight']);
				}
			}
		}else
		{
			trigger_error("ERROR! Repeat Seting:Cache->config_memcache().", E_USER_ERROR);
		}
	}
    
    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port'],['host','port'],...)
     * @return bool
     */
    public function config_redis(array $servers,$mod='def',$expire=0,$format_string=false,$large_scale=false,$auth=false)
    {
        if(0 == $this->_type)
        {
            $this->_type	= self::Type_Redis;
            $this->_drive	= new _cache_redis($mod,$expire,$large_scale,$format_string,$auth);
            if(is_array($servers))
            {
                foreach($servers as $v)
                {
                    $this->_drive->connect($v['host'],$v['port']);
                }
            }
        }else
        {
            trigger_error("ERROR! Repeat Seting:Cache->config_redis().", E_USER_ERROR);
        }
    }
    
    /**
     * 设定Memcached服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function config_memcached(array $servers,$mod='def',$expire=0,$format_string=false,$large_scale=false,$auth=false)
    {
        if(0 == $this->_type)
        {
            $this->_type	= self::Type_Memcached;
            $this->_drive	= new _cache_memcached($mod,$expire,$format_string,$large_scale,$auth);
            if(is_array($servers))
            {
                foreach($servers as $v)
                {
                    $this->_drive->add_server($v['host'],$v['port'],$v['weight']);
                }
            }
        }else
        {
            trigger_error("ERROR! Repeat Seting:Cache->config_memcached().", E_USER_ERROR);
        }
    }
    
    /**
     * 设定数据keys
     * @param $keys       */
	public function key($keys)
	{
		$this->_drive->key($keys);
	}
    
    /**
     * 设定数据Value
     * @param $vals       */
	public function val($vals)
	{
		$this->_drive->val($vals);
	}
    
    /**
     * 读取数据
     * @param $keys
     * @return mixed|null */
	public function read()
	{
		return $this->_drive->read();
	}
    
    /**
     * 写入已设定的数据
     * @return bool       */
	public function write()
	{
		return $this->_drive->write();
	}
    
    /**
     * 读取数据中$key的值
     * @param $sub_key        */
	public function get($sub_key)
	{
		return $this->_drive->get($sub_key);
	}
    
    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals       */
	public function set($sub_key,$sub_val)
	{
		$this->_drive->set($sub_key,$sub_val);
	}
    
    /**
     * 删除数据
     * @return bool       */
	public function delete()
	{
		return $this->_drive->delete();
	}
    
    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string     */
	public function filename()
	{
		return $this->_drive->filename();
	}
} 
