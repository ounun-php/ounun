<?php

namespace ounun\seo;

use ounun\tool\str;

/** 本插件所在目录 */
define('Dir_Plugins_Pinyin', __DIR__ . '/');

/**
 * 汉字转拼音
 */
class pinyin_gbk
{
    protected $_data = [];

    /**
     * Constructor
     *
     * Simply globalizes the $RTR object.  The front
     * loads the Router class early on so it's not available
     * normally as other classes are.
     *
     * @access    public
     */
    public function __construct()
    {
        $fp = fopen(Dir_Plugins_Pinyin . 'res/pinyin.dat', 'r');
        while (!feof($fp)) {
            $line = trim(fgets($fp));
            $this->_data[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
        }
        fclose($fp);
    }

    /**
     * 汉字转拼音
     * @param string $string 要转换的汉字
     * @param string $from_encoding 汉字编码
     * @param bool $initial 首字母是否大写
     * @param string $space 拼音之间的间隔
     * @return string
     */
    public function convert($string, $from_encoding = 'gbk', $initial = true, $space = '')
    {
        $py = $this->pinyin($string, $from_encoding);
        if ($initial) {
            $rs = [];
            foreach ($py as $v) {
                $rs[] = ucfirst($v);
            }
            $py = $rs;
        }
        return implode($space, $py);
    }

    /**
     * 提取汉字声母（每个字拼音的第一个字母）
     * @param string $string 要提取汉字
     * @param string $from_encoding 汉字编码
     * @return string
     */
    public function head($string, $from_encoding = 'gbk')
    {
        $rs = array();
        $py = $this->pinyin($string, $from_encoding);
        foreach ($py as $v) {
            $rs[] = substr($v, 0, 1);
        }
        return implode('', $rs);
    }

    /**
     * 返回一个数组(一般不用这个)
     * @param string $string
     * @param string $from_encoding
     * @return  array <string, $string>
     */
    public function pinyin($string, $from_encoding = 'gbk')
    {
        if ($from_encoding != 'gbk') {
            $string = mb_convert_encoding($string, 'gbk', $from_encoding);
        }
        $_res = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $_P = ord($string[$i]);
            if ($_P > 0x80) {
                $c = $string[$i] . $string[$i + 1];
                $i++;
                if (isset($this->_data[$c])) {
                    $_res[] = $this->_data[$c];
                }
            } else {
                $_res[] = $string[$i];
            }
        }
        return $_res;
    }
    //echo Pinyin('第二个参数随意设置',2);

    /**
     * 生成字母前缀
     * @param $s0
     * @return int|string
     */
    function letter_first($s0)
    {
        $firstchar_ord = ord(strtoupper($s0{0}));
        if (($firstchar_ord >= 65 and $firstchar_ord <= 91) or ($firstchar_ord >= 48 and $firstchar_ord <= 57)) {
            return $s0{0};
        }
        // $s=iconv("UTF-8","gb2312", $s0);
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
        return 0;//null
    }


    /**
     * 汉字转拼单
     * @param $str
     * @param int $ishead
     * @param int $isclose
     * @return string
     */
    function pinyin2($str, $ishead = 0, $isclose = 1)
    {
        $str = \util::u2g($str);//转成GBK
        global $pinyins;
        $restr = '';
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (count($pinyins) == 0) {
            $fp = fopen('./Lib/Conf/pinyin.dat', 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
                }
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if ($ishead == 0) {
                        $restr .= $pinyins[$c];
                    } else {
                        $restr .= $pinyins[$c][0];
                    }
                } else {
                    $restr .= "_";
                }
                //}else if( eregi("[a-z0-9]",$str[$i]) )
            } else if (preg_match('/[a-z0-9]/i', $str[$i])) {
                $restr .= $str[$i];
            } else {
                $restr .= "_";
            }
        }
        if ($isclose == 0) {
            unset($pinyins);
        }
        return $restr;
    }

    /**
     * 汉字转拼单
     * @param $str
     * @param int $ishead
     * @param int $isclose
     * @return string
     */
    public function pinyin3($str, $ishead = 0, $isclose = 1)
    {
        $str = u2g($str);//转成GBK
        global $pinyins;
        $restr = '';
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (count($pinyins) == 0) {
            $fp = fopen('./Lib/Conf/pinyin.dat', 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
                }
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if ($ishead == 0) {
                        $restr .= $pinyins[$c];
                    } else {
                        $restr .= $pinyins[$c][0];
                    }
                } else {
                    //$restr .= "_";
                }
                //}else if( eregi("[a-z0-9]",$str[$i]) )
            } else if (preg_match('/[a-z0-9]/i', $str[$i])) {
                $restr .= $str[$i];
            } else {
                //$restr .= "_";
            }
        }
        if ($isclose == 0) {
            unset($pinyins);
        }
        return $restr;
    }


    /**
     * 汉字转拼单
     * @param $str
     * @param int $is_head
     * @param int $is_close
     * @return string
     */
    function pinyin4($str, $is_head = 0, $is_close = 1)
    {
        $str = str::utf82gbk($str);//转成GBK
        global $pinyins;
        $restr = '';
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (count($pinyins) == 0) {
            $fp = fopen('./Lib/Conf/pinyin.dat', 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $line = trim(fgets($fp));
                    $pinyins[$line[0] . $line[1]] = substr($line, 3, strlen($line) - 3);
                }
            }
            fclose($fp);
        }
        for ($i = 0; $i < $slen; $i++) {
            if (ord($str[$i]) > 0x80) {
                $c = $str[$i] . $str[$i + 1];
                $i++;
                if (isset($pinyins[$c])) {
                    if ($is_head == 0) {
                        $restr .= $pinyins[$c];
                    } else {
                        $restr .= $pinyins[$c][0];
                    }
                } else {
                    //$restr .= "_";
                }
                //}else if( eregi("[a-z0-9]",$str[$i]) )
            } else if (preg_match('/[a-z0-9]/i', $str[$i])) {
                $restr .= $str[$i];
            } else {
                //$restr .= "_";
            }
        }
        if ($is_close == 0) {
            unset($pinyins);
        }
        return $restr;
    }
}
// $py = new CI_Pinyin();
// $rs  = $py->convert('第23rtg二个参数随意设置','utf-8',false,'');

// var_dump($rs);
// END URI Class

/* End of file URI.php */
/* Location: ./system/core/URI.php */