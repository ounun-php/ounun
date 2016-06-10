<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 16/1/3
 * Time: 下午11:02
 */

namespace ounun;


class Xml
{
    /**
     *
     * @param array   $data
     * @param string  $key
     * @param string  $t
     * @param Boolean $ps		true:有父级
     * 							false:没父级
     * @param Boolean $ps_auto  true:$ps无效数组多于1时加s父级 等于1时 没有父级
     * 							false:有没有父级 看$ps
     * @return string
     */
    public static function array2xml($data,$key,$t="",$ps=false,$ps_auto=false)
    {
        $xml 		 = '';
        if ('#' == $key)
        {
            return  $xml;
        }
        elseif(!is_array($data))
        {
            if(strstr($key,'$'))
            {
                $key  	 = substr($key,1);
                $data    = stripslashes($data);
                $xml	.= "{$t}<{$key}><![CDATA[{$data}]]></{$key}>\n";
            }
            else
            {
                if(is_numeric($data))
                {
                    // $data = printf("%s",$data);
                    $data = number_format($data,0,'','');
                }
                $xml	.= "{$t}<{$key}>{$data}</{$key}>\n";
            }
        }elseif(array_keys($data) === range(0, count($data) - 1))
        {
            $key2	 = strstr($key,'$')?substr($key,1):$key;
            if($ps)
            {
                $xml	.= "{$t}<{$key2}s>\n";
                foreach ($data as $data2)
                {
                    $xml.= data2xml($data2,$key,"{$t}\t",$ps,$ps_auto);
                }
                $xml	.= "{$t}</{$key2}s>\n";
            }
            else
            {
                foreach ($data as $data2)
                {
                    $xml.= data2xml($data2,$key,"{$t}",$ps,$ps_auto);
                }
            }
        }else
        {
            if($ps_auto)
            {
                $ps_c	= 0;
                $ps 	= false; // 是否唯一子结节，唯一子结点就不包
                foreach ($data as $key2=>$data2)
                {
                    if('#' != $key2)
                    {
                        $ps_c ++;
                    }
                }
                if($ps_c > 1)
                {
                    $ps = true;
                }
            }
            //////////////////////////////////////////////////////
            $v		 	= '';
            foreach ($data as $key2=>$data2)
            {
                $v	.= data2xml($data2,$key2,"{$t}\t",$ps,$ps_auto);
            }
            if(is_array($data['#']))
            {
                $a   = '';
                foreach ($data['#'] as $key2=>$data2)
                {
                    if(is_numeric($data2))
                    {
                        if($data2 && strlen($data2)  && '0' == substr($data2,0,1) && '.' != substr($data2,1,1))
                        {
                            // 0 开头的字符串
                            // $data2 = $data2;
                        }elseif((int)$data2 != $data2)
                        {
                            $data2 = number_format($data2,3,'.','');
                        }else
                        {
                            $data2 = number_format($data2,0,'','');
                        }
                    }
                    $a .=" {$key2}=\"{$data2}\"";
                }
                if($v)
                {
                    $xml .= "{$t}<{$key}{$a}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                }else
                {
                    $xml .= "{$t}<{$key}{$a} />\n";
                }
            }else
            {
                if($v)
                {
                    $xml .= "{$t}<{$key}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                }else
                {
                    $xml .= "{$t}<{$key} />\n";
                }
            }
        }
        return $xml;
    }
}