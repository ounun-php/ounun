<?php
namespace seo;
/**
 * 汉字转拼音
 */
class wei
{
	public static function wei($str)
	{
		return strtr($str,cfg_data::wei);
	}
	
	public static function test()
	{
		echo count(cfg_data::wei),"<br />\n";
		$d = cfg_data::wei;
		$f = file_get_contents('42537.txt');
		$f = explode("\n", $f);
		foreach ($f as $v)
		{
			$v2 = explode('→', $v);
			$v3 = trim($v2[0]);
			$v4 = trim($v2[1]);
			$d[$v3] = $v4;
		}
		echo count($f),"<br />\n";
		echo count($d),"<br />\n<pre>";
		var_export($d);
	}
}
//WeiSEO::test();