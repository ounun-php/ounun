<?php

namespace ounun\tool;

class str
{
    /**
     * 格式化字节大小
     * @param number $size 字节数
     * @param string $delimiter 数字和单位分隔符
     * @return string            格式化后的带单位的大小
     */
    static public function format_bytes($size, $delimiter = '')
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * UT*转GBK
     * @param string $str
     * @return string
     */
    static public function utf82gbk(string $str): string
    {
        return \mb_convert_encoding($str, 'GBK', 'UTF-8');
    }

    /**
     * GBK转UTF8
     * @param $str
     * @return string
     */
    static public function gbk2utf8(string $str): string
    {
        return \mb_convert_encoding($str, 'UTF-8', 'GBK');
    }

    /**
     * 去掉换行
     * @param string $str
     * @return string
     */
    static public function nr(string $str): string
    {
        $str = str_replace(["<nr/>", "<rr/>"], ["\n", "\r"], $str);
        return trim($str);
    }

    /**
     * 去掉连续空白
     * @param $str
     * @return string
     */
    static public function nb(string $str): string
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
    static public function msubstr(string $str, int $length, int $start = 0, bool $suffix = false): string
    {
        if ($length) {
            $str = preg_replace('/<[^>]+>/', '', preg_replace('/[\r\n\t ]{1,}/', ' ', self::nb($str)));
            return self::msubstr2($str, $length, $start, 'utf-8', $suffix);
        } else {
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
    static public function msubstr2(string $str, int $length, int $start = 0, string $charset = "utf-8", bool $suffix = true): string
    {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $length_new = $length;
        $length_chi = 0;
        for ($i = $start; $i < $length; $i++) {
            if (ord($match[0][$i]) > 0xa0) {
                //中文
            } else {
                $length_new++;
                $length_chi++;
            }
        }
        if ($length_chi < $length) {
            $length_new = $length + ($length_chi / 2);
        }
        $slice = join("", array_slice($match[0], $start, $length_new));
        if ($suffix && $slice != $str) {
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
    static public function h(string $text, string $tags = ''): string
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
        while (preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1], $text);
        }
        while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
        }
        if ('' == $tags) {
            $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        }
        //允许的HTML标签
        $text = preg_replace('/<(' . $tags . ')( [^><\[\]]*)>/i', '[\1\2]', $text);
        //过滤多余html
        $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i', '', $text);
        //过滤合法的html标签
        while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)) {
            $text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
        }
        //转换引号
        while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i', $text, $mat)) {
            $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
        }
        //过滤错误的单个引号
        while (preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i', $text, $mat)) {
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
    static public function uniqid(): string
    {
        $uniqid_prefix = '';
        $uniqid_filename = '/tmp/php_session_uniqid.txt';
        if (!file_exists($uniqid_filename)) {
            $uniqid_prefix = \substr(\uniqid('', false), 3);
            @file_put_contents($uniqid_filename, $uniqid_prefix);
        }
        if (!$uniqid_prefix) {
            if (file_exists($uniqid_filename)) {
                $uniqid_prefix = @file_get_contents($uniqid_filename);
            }
            if (!$uniqid_prefix) {
                $uniqid_prefix = \substr(\uniqid('', false), 3);
            }
        }
        $session_id = \uniqid($uniqid_prefix, true);
        return \substr($session_id, 0, 24) . \substr($session_id, 25);
    }

    /**
     * XSS漏洞过滤
     * @param string $val
     * @return string
     */
    static public function remove_xss(string $val): string
    {
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
            $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
        }
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
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
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

    /**
     * @param string $str_cn 需要截断的字符串
     * @param int $length 允许字符串显示的最大长度
     * @return string 程序功能：截取全角和半角（汉字和英文）混合的字符串以避免乱码
     */
    static public function substr_cn($str_cn, $length)
    {
        if (strlen($str_cn) > $length) {
            for ($i = 0; $i < $length; $i++) {
                if (ord($str_cn[$i]) > 128) {
                    $i++;
                }
            }
            $str_cn = substr($str_cn, 0, $i) . "..";
        }
        return $str_cn;
    }


    /**
     * @param $length
     * @param bool $numeric
     * @return string
     */
    static public function random($length, $numeric = false)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * 生成字母前缀
     * @param $s0
     * @return int|string
     */
    static public function letter_first($s0)
    {
        $firstchar_ord = ord(strtoupper($s0{0}));
        if (($firstchar_ord >= 65 && $firstchar_ord <= 91) || ($firstchar_ord >= 48 && $firstchar_ord <= 57)) {
            return $s0{0};
        }
        $s = mb_convert_encoding($s0, "GBK", "UTF-8");
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 and $asc <= -20284) return "A";
        if ($asc >= -20283 and $asc <= -19776) return "B";
        if ($asc >= -19775 and $asc <= -19219) return "C";
        if ($asc >= -19218 and $asc <= -18711) return "D";
        if ($asc >= -18710 and $asc <= -18527) return "E";
        if ($asc >= -18526 and $asc <= -18240) return "F";
        if ($asc >= -18239 and $asc <= -17923) return "G";
        if ($asc >= -17922 and $asc <= -17418) return "H";
        if ($asc >= -17417 and $asc <= -16475) return "J";
        if ($asc >= -16474 and $asc <= -16213) return "K";
        if ($asc >= -16212 and $asc <= -15641) return "L";
        if ($asc >= -15640 and $asc <= -15166) return "M";
        if ($asc >= -15165 and $asc <= -14923) return "N";
        if ($asc >= -14922 and $asc <= -14915) return "O";
        if ($asc >= -14914 and $asc <= -14631) return "P";
        if ($asc >= -14630 and $asc <= -14150) return "Q";
        if ($asc >= -14149 and $asc <= -14091) return "R";
        if ($asc >= -14090 and $asc <= -13319) return "S";
        if ($asc >= -13318 and $asc <= -12839) return "T";
        if ($asc >= -12838 and $asc <= -12557) return "W";
        if ($asc >= -12556 and $asc <= -11848) return "X";
        if ($asc >= -11847 and $asc <= -11056) return "Y";
        if ($asc >= -11055 and $asc <= -10247) return "Z";
        return "1";//null
    }

    /**
     * 生成长为度7的16进制字符串
     * @return string
     */
    static public function rand_hex_7()
    {
        $hex7_dec = mt_rand(0, 268435456);
        return dechex($hex7_dec);
    }

    /**
     * 生成长为度$len的16进制字符串
     * @param $len
     * @return bool|string
     */
    static public function rand_hex($len)
    {
        $s = '';
        do {
            $s .= static::rand_hex_7();
        } while (strlen($s) < $len);
        return substr($s, 0, $len);
    }

    /**
     * 生成长为度4的36进制字符串
     * @return string
     */
    static public function rand_base36_4()
    {
        $i = mt_rand(0, 1679615);
        $s = base_convert($i, 10, 36);
        if (strlen($s) < 4) {
            return substr('0000' . $s, -4);
        }
        return $s;
    }

    /**
     * 生成长为度$len的36进制字符串
     * @param $len
     * @return bool|string
     */
    static public function rand_base36($len)
    {
        $s = '';
        do {
            $s .= static::rand_base36_4();
        } while (strlen($s) < $len);
        return substr($s, 0, $len);
    }


    /*
     * 生成随机字符串
     * @param int $length 生成随机字符串的长度
     * @param string $char 组成随机字符串的字符串
     * @return string $string 生成的随机字符串
     */
    static public function rand_base62($length = 32)
    {
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        $l = strlen($char) - 1;
        for ($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, $l)];
        }
        return $string;
    }

    /**
     * @param string $account
     * @return string
     */
    static public function hide_accounts(string $account)
    {
        if (verify::email($account)) {
            return static::hide_email($account);
        } elseif (verify::mobile($account)) {
            return static::hide_mobile($account);
        }
        return static::hide_card($account);
    }

    /**
     * @param string $card
     */
    static public function hide_card(string $card)
    {
        return substr($card, 0, 2) . '****' . substr($card, -4);
    }

    /**
     * @param string $mobile
     */
    static public function hide_mobile(string $mobile)
    {
        return '1***' . substr($name, -4);
    }

    /**
     * @param string $email
     */
    static public function hide_email(string $email)
    {
        $arr = explode('@', $email);
        return substr($arr[0], 0, 2) . '***' . substr($arr[0], -2) . '@' . $arr[1];
    }

    /**
     * @param string $name
     */
    static public function hide_name(string $name)
    {
        return '**' . mb_substr($name, -1, null, 'utf-8');
    }
}
