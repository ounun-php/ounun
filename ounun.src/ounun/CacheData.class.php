<?php
namespace ounun;

class CacheData extends \ounun\Base
{
    public function __construct($mod_cache='cache_data')
    {
        $this->_db_cache_mod = $mod_cache;
    }
	/********************************************************************************************************
	 * 
	 ********************************************************************************************************/
	private static $_inst = NULL;
	/**
	 * 返回DbCache
	 * @return \ounun\CacheData
	 */
	public  static function inst($mod_cache)
	{
		if(null == self::$_inst)
		{
			self::$_inst = new CacheData($mod_cache);
		}
		return self::$_inst;
	}
	//
    public function cache_read($keys)
	{
		$filename 	= Dir_Data."{$this->_db_cache_mod}/{$keys}.data.inc.php";
		$rs       	= null;
		if (file_exists($filename))
		{
			require $filename;
		}
		return $rs;
	}
    //
    public function cache_delete($keys)
	{
		$filename 	= Dir_Data."{$this->_db_cache_mod}/{$keys}.data.inc.php";
		if (file_exists($filename))
		{
			unlink($filename);
		}
	}
	/**
	 * 写
	 * @param string $mod
	 * @param string $keys
	 * @param mix $data
	 */
    public function cache_write($keys,$data)
	{
		$filedir    = Dir_Data."{$this->_db_cache_mod}/";
		if(!is_dir($filedir))
		{
			mkdir($filedir, 0777, true);
		}
        $filename	= "{$filedir}{$keys}.data.inc.php";
		if(file_exists($filename))
		{
			unlink($filename);
		}
		$str		= var_export($data,true);
		file_put_contents($filename,'<?php '."\n\$rs={$str};\n".'?>');
	}
}
?>