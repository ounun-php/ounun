<?php

class util
{
    /**
     * UT*转GBK
     * @param string $str
     * @return string
     */
    public static function u2g(string $str):string
    {
        return mb_convert_encoding($str,'GBK','UTF-8');
    }

    /**
     * GBK转UTF8
     * @param $str
     * @return string
     */
    public static function g2u(string $str):string
    {
        return mb_convert_encoding($str,'UTF-8','GBK');
    }

    /**
     * 去掉换行
     * @param string $str
     * @return string
     */
    public static function nr(string $str):string
    {
        $str = str_replace(["<nr/>", "<rr/>"], ["\n", "\r"], $str);
        return trim($str);
    }

    /**
     * 去掉连续空白
     * @param $str
     * @return string
     */
    public static function nb(string $str):string
    {
        $str = str_replace("　", ' ', str_replace("&nbsp;", ' ', $str));
        $str = preg_replace('/[\r\n\t ]{1,}/', ' ', $str);
        return trim($str);
    }

    /**
     * 字符串截取(同时去掉HTML与空白)
     * @param string $str
     * @param int $start
     * @param int $length
     * @param bool $suffix
     * @return string
     */
    public static function msubstr(string $str,int $length,int $start=0,bool $suffix=false):string
    {
        if($length)
        {
            $str = preg_replace('/<[^>]+>/','',preg_replace('/[\r\n\t ]{1,}/',' ',self::nb($str)));
            return self::msubstr2($str,$length,$start,'utf-8',$suffix);
        }else
        {
            return $str;
        }
    }

    /**
     * 字符串截取
     * @param string $str
     * @param int $start
     * @param int $length
     * @param string $charset
     * @param bool $suffix
     * @return string
     */
    public static  function msubstr2(string $str,int $length,int $start=0,string $charset="utf-8",bool $suffix=true):string
    {
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $length_new = $length;
        $length_chi = 0;
        for ($i = $start; $i < $length; $i++)
        {
            if (ord($match[0][$i]) > 0xa0)
            {
                //中文
            } else
            {
                $length_new++;
                $length_chi++;
            }
        }
        if ($length_chi < $length)
        {
            $length_new = $length + ($length_chi / 2);
        }
        $slice = join("", array_slice($match[0], $start, $length_new));
        if ($suffix && $slice != $str)
        {
            return $slice . "…";
        }
        return $slice;
    }

    /**
     * 输出安全的html
     * @param string $text
     * @param string $tags
     * @return string
     */
    public static function h(string $text, string $tags = ''):string
    {
        $text = trim($text);
        //完全过滤注释
        $text = preg_replace('/<!--?.*-->/', '', $text);
        //完全过滤动态代码
        $text = preg_replace('/<\?|\?' . '>/', '', $text);
        //完全过滤js
        $text = preg_replace('/<script?.*\/script>/', '', $text);

        $text = str_replace('[', '&#091;', $text);
        $text = str_replace(']', '&#093;', $text);
        $text = str_replace('|', '&#124;', $text);
        //过滤换行符
        $text = preg_replace('/\r?\n/', '', $text);
        //br
        $text = preg_replace('/<br(\s\/)?' . '>/i', '[br]', $text);
        $text = preg_replace('/(\[br\]\s*){10,}/i', '[br]', $text);
        //过滤危险的属性，如：过滤on事件lang js
        while (preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat))
        {
            $text = str_replace($mat[0], $mat[1], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat))
        {
            $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
        }
        if ('' == $tags)
        {
            $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        }
        //允许的HTML标签
        $text = preg_replace('/<(' . $tags . ')( [^><\[\]]*)>/i', '[\1\2]', $text);
        //过滤多余html
        $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i', '', $text);
        //过滤合法的html标签
        while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat))
        {
            $text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
        }
        //转换引号
        while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $text, $mat))
        {
            $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
        }
        //过滤错误的单个引号
        while (preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat))
        {
            $text = str_replace($mat[0], str_replace($mat[1], '', $mat[0]), $text);
        }
        //转换其它所有不合法的 < >
        $text = str_replace('<', '&lt;', $text);
        $text = str_replace('>', '&gt;', $text);
        $text = str_replace('"', '&quot;', $text);
        //反转换
        $text = str_replace('[', '<', $text);
        $text = str_replace(']', '>', $text);
        $text = str_replace('|', '"', $text);
        //过滤多余空格
        $text = str_replace('  ', ' ', $text);
        return $text;
    }

    /**
     * 随机生成一组字符串
     * @param int $number
     * @param int $length
     * @param int $mode
     * @return array
     */
//    public static function build_count_rand(int $number,int $length = 4,int $mode = 1):array
//    {
//        if ($mode == 1 && $length < strlen($number))
//        {
//            // 不足以生成一定数量的不重复数字
//            return false;
//        }
//        $rand = array();
//        for ($i = 0; $i < $number; $i++)
//        {
//            $rand[] = rand_string($length, $mode);
//        }
//        $unqiue = array_unique($rand);
//        if (count($unqiue) == count($rand))
//        {
//            return $rand;
//        }
//        $count = count($rand) - count($unqiue);
//        for ($i = 0; $i < $count * 3; $i++)
//        {
//            $rand[] = rand_string($length, $mode);
//        }
//        $rand = array_slice(array_unique($rand), 0, $number);
//        return $rand;
//    }
    public static function uniqid():string
    {
        $uniqid_prefix     = '';
        $uniqid_filename   = '/tmp/php_session_uniqid.txt';
        if(!file_exists($uniqid_filename))
        {
            $uniqid_prefix = \substr(\uniqid('',false),3);
            @file_put_contents($uniqid_filename,$uniqid_prefix);
        }
        if(!$uniqid_prefix)
        {
            if(file_exists($uniqid_filename))
            {
                $uniqid_prefix = @file_get_contents($uniqid_filename);
            }
            if(!$uniqid_prefix)
            {
                $uniqid_prefix = \substr(\uniqid('',false),3);
            }
        }
        $session_id        = \uniqid($uniqid_prefix,true);
        return \substr($session_id,0,24).\substr($session_id,25);
    }

    /**
     * XSS漏洞过滤
     * @param string $val
     * @return string
     */
    public static function remove_xss(string $val):string
    {
        $val     = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $search  = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++)
        {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true)
        {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++)
            {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++)
                {
                    if ($j > 0)
                    {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2);  // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val);      // filter out the hex tags
                if ($val_before == $val)
                {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }


    /**
     * 字节格式化 把字节数格式为 B K M G T 描述的大小
     * @param int $size
     * @param int $dec
     * @return string
     */
    public static function byte_format(int $size, int $dec = 2):string
    {
        $a = ["B", "KB", "MB", "GB", "TB", "PB"];
        $pos = 0;
        while ($size >= 1024)
        {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }

    /**
     * 递归多维数组转为一级数组
     * @param array $array
     * @return array
     */
    public static function arrays2array(array $array):array
    {
        static $result_array = array();
        foreach ($array as $value)
        {
            if (is_array($value))
            {
                self::arrays2array($value);
            } else
            {
                $result_array[] = $value;
            }
        }
        return $result_array;
    }


    /**
     * 获得$day_nums天前的时间戳
     * @param int $day_nums
     * @return int
     */
    public static function xtime(int $day_nums):int
    {
        $day_time = time() - $day_nums * 3600 * 24;
        return strtotime(date("Y-m-d 00:00:00", $day_time));
    }

    /**
     * 获取标题颜色
     * @param string $str
     * @param string $color
     * @return string
     */
    public static function color_text(string $str,string $color=''):string
    {
        if($color)
        {
            return "<span style=\"color: {$color}\">{$str}</span>";
        }else
        {
            return $str;
        }
    }

    /**
     * 获取特定时时间颜色
     * @param int $time
     * @param string $type
     * @param string $color
     * @return string
     */
    public static function color_date(string $type='Y-m-d H:i:s',int $time,string $color='red',int $interval=86400):string
    {
        if((time()-$time)>$interval)
        {
            return date($type,$time);
        }else
        {
            return self::color_text(date($type,$time),$color);
        }
    }

    /**
     * 生成字母前缀
     * @param $s0
     * @return int|string
     */
    public static function letter_first($s0)
    {
        $firstchar_ord = ord(strtoupper($s0{0}));
        if (($firstchar_ord>=65 && $firstchar_ord<=91) || ($firstchar_ord>=48 && $firstchar_ord<=57))
        {
            return $s0{0};
        }
        $s   = mb_convert_encoding($s0,"GBK","UTF-8");
        $asc = ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319 and $asc<=-20284)return "A";
        if($asc>=-20283 and $asc<=-19776)return "B";
        if($asc>=-19775 and $asc<=-19219)return "C";
        if($asc>=-19218 and $asc<=-18711)return "D";
        if($asc>=-18710 and $asc<=-18527)return "E";
        if($asc>=-18526 and $asc<=-18240)return "F";
        if($asc>=-18239 and $asc<=-17923)return "G";
        if($asc>=-17922 and $asc<=-17418)return "H";
        if($asc>=-17417 and $asc<=-16475)return "J";
        if($asc>=-16474 and $asc<=-16213)return "K";
        if($asc>=-16212 and $asc<=-15641)return "L";
        if($asc>=-15640 and $asc<=-15166)return "M";
        if($asc>=-15165 and $asc<=-14923)return "N";
        if($asc>=-14922 and $asc<=-14915)return "O";
        if($asc>=-14914 and $asc<=-14631)return "P";
        if($asc>=-14630 and $asc<=-14150)return "Q";
        if($asc>=-14149 and $asc<=-14091)return "R";
        if($asc>=-14090 and $asc<=-13319)return "S";
        if($asc>=-13318 and $asc<=-12839)return "T";
        if($asc>=-12838 and $asc<=-12557)return "W";
        if($asc>=-12556 and $asc<=-11848)return "X";
        if($asc>=-11847 and $asc<=-11056)return "Y";
        if($asc>=-11055 and $asc<=-10247)return "Z";
        return "1";//null
    }

    /**
     * 正则提取正文里指定的第几张图片地址
     * @param string $content
     * @return array
     */
    static function img_urls(string $content):array
    {
        preg_match_all('/<img(.*?)src="(.*?)(?=")/si',$content,$imgarr);///(?<=img.src=").*?(?=")/si
        preg_match_all('/(?<=src=").*?(?=")/si',implode('" ',$imgarr[0]).'" ',$imgarr);
        return $imgarr[0];
    }

    /*
     * 参数：
     * $str_cut 需要截断的字符串
     * $length  允许字符串显示的最大长度
     * 程序功能：截取全角和半角（汉字和英文）混合的字符串以避免乱码
     */
    static public function substr_cn($str_cn, $length)
    {
        if (strlen($str_cn) > $length)
        {
            for ($i = 0; $i < $length; $i++)
            {
                if (ord($str_cn[$i]) > 128)
                {
                    $i++;
                }
            }
            $str_cn = substr($str_cn, 0, $i) . "..";
        }
        return $str_cn;
    }


    /**
     * IP隐藏第3段
     * @param $ip
     * @return string
     */
    public static function ip_hide($ip)
    {
        $ip     = explode('.',$ip);
        $ip[2]  = '*';
        return implode('.',$ip);
    }
}
