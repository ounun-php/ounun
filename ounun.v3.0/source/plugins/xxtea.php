<?php

namespace plugins;

class xxtea
{
    /**
     * 加密
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public static function encrypt($str, $key)
    {
        $key = xxtea_key($key);
        return base64_encode(xxtea_encrypt($str, $key));
    }

    /**
     * @param $str
     * @param $key
     * @return string
     */
    public static function encode($str, $key)
    {
        $key = xxtea_key($key);
        return xxtea_encrypt($str, $key);
    }

    /**
     * 解密
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public static function decrypt($str, $key)
    {
        if ($str == "") {
            return "";
        }
        $str = base64_decode($str);
        $key = xxtea_key($key);
        return xxtea_decrypt($str, $key);
    }

    /**
     *
     * @param $str
     * @param $key
     * @return string
     */
    public static function decode($str, $key)
    {
        if ($str == "") {
            return "";
        }
        $key = xxtea_key($key);
        return xxtea_decrypt($str, $key);
    }
}


/**
 * 得到统一的Key
 */
function xxtea_key($key)
{
    $key = str_pad($key, 16, '0');
    return substr($key, 0, 16);
}

/**
 * Load extension
 */
if (!extension_loaded('xxtea')) {
    function xxtea_encrypt($str, $key)
    {
        if ($str == "")
            return "";
        $v = xxtea_str2long($str, true);
        $k = xxtea_str2long($key, false);
        if (count($k) < 4) {
            for ($i = count($k); $i < 4; $i++) {
                $k [$i] = 0;
            }
        }
        $n = count($v) - 1;
        $z = $v [$n];
        $y = $v [0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = 0;
        while (0 < $q--) {
            $sum = xxtea_int32($sum + $delta);
            $e = $sum >> 2 & 3;
            for ($p = 0; $p < $n; $p++) {
                $y = $v [$p + 1];
                $mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
                $z = $v [$p] = xxtea_int32($v [$p] + $mx);
            }
            $y = $v [0];
            $mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $z = $v [$n] = xxtea_int32($v [$n] + $mx);
        }
        return xxtea_long2str($v, false);
    }

    function xxtea_decrypt($str, $key)
    {
        if ($str == "")
            return "";
        $v = xxtea_str2long($str, false);
        $k = xxtea_str2long($key, false);
        if (count($k) < 4) {
            for ($i = count($k); $i < 4; $i++) {
                $k [$i] = 0;
            }
        }
        $n = count($v) - 1;
        $z = $v [$n];
        $y = $v [0];
        $delta = 0x9E3779B9;
        $q = floor(6 + 52 / ($n + 1));
        $sum = xxtea_int32($q * $delta);
        while ($sum != 0) {
            $e = $sum >> 2 & 3;
            for ($p = $n; $p > 0; $p--) {
                $z = $v [$p - 1];
                $mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
                $y = $v [$p] = xxtea_int32($v [$p] - $mx);
            }
            $z = $v [$n];
            $mx = xxtea_int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ xxtea_int32(($sum ^ $y) + ($k [$p & 3 ^ $e] ^ $z));
            $y = $v [0] = xxtea_int32($v [0] - $mx);
            $sum = xxtea_int32($sum - $delta);
        }
        return xxtea_long2str($v, true);
    }

    /**
     * long2str
     *
     * @param array $v
     * @param boolean $w
     * @return string
     */
    function xxtea_long2str($v, $w)
    {
        $len = count($v);
        $n = ($len - 1) << 2;
        if ($w) {
            $m = $v [$len - 1];
            if (($m < $n - 3) || ($m > $n))
                return false;
            $n = $m;
        }
        $s = array();
        for ($i = 0; $i < $len; $i++) {
            $s [$i] = pack("V", $v [$i]);
        }
        if ($w) {
            return substr(join('', $s), 0, $n);
        } else {
            return join('', $s);
        }
    }

    /**
     * str2long
     *
     * @param string $s
     * @param boolean $w
     * @return array
     */
    function xxtea_str2long($s, $w)
    {
        $v = unpack("V*", $s . str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
        if ($w) {
            $v [count($v)] = strlen($s);
        }
        return $v;
    }

    /**
     * 格式转化为int32
     *
     * @param int $n
     * @return int
     */
    function xxtea_int32($n)
    {
        while ($n >= 2147483648) {
            $n -= 4294967296;
        }
        while ($n <= -2147483649) {
            $n += 4294967296;
        }
        return ( int )$n;
    }
}
