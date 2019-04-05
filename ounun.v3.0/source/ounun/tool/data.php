<?php

namespace ounun\tool;

class data
{
    /**
     * 递归多维数组转为一级数组
     * @param array $array
     * @return array
     */
    static public function arrays2array(array $array): array
    {
        static $result_array = array();
        foreach ($array as $value) {
            if (is_array($value)) {
                self::arrays2array($value);
            } else {
                $result_array[] = $value;
            }
        }
        return $result_array;
    }


    /**
     * @param    $data  array|string|mixed
     * @param    $key   string
     * @param    $t     string
     * @param    $ps    boolean        true:有父级
     *                               false:没父级
     * @param    $ps_auto boolean   true:$ps无效数组多于1时加s父级 等于1时 没有父级
     *                               false:有没有父级 看$ps
     * @return  string
     */
    public static function array2xml($data, $key, $t = "", $ps = false, $ps_auto = false)
    {
        $xml = '';
        if ('#' == $key) {
            return $xml;
        } elseif (!is_array($data)) {
            if (strstr($key, '$')) {
                $key = substr($key, 1);
                $data = stripslashes($data);
                $xml .= "{$t}<{$key}><![CDATA[{$data}]]></{$key}>\n";
            } else {
                if (is_numeric($data)) {
                    // $data = printf("%s",$data);
                    $data = number_format($data, 0, '', '');
                }
                $xml .= "{$t}<{$key}>{$data}</{$key}>\n";
            }
        } elseif (array_keys($data) === range(0, count($data) - 1)) {
            $key2 = strstr($key, '$') ? substr($key, 1) : $key;
            if ($ps) {
                $xml .= "{$t}<{$key2}s>\n";
                foreach ($data as $data2) {
                    $xml .= self::array2xml($data2, $key, "{$t}\t", $ps, $ps_auto);
                }
                $xml .= "{$t}</{$key2}s>\n";
            } else {
                foreach ($data as $data2) {
                    $xml .= self::array2xml($data2, $key, "{$t}", $ps, $ps_auto);
                }
            }
        } else {
            if ($ps_auto) {
                $ps_c = 0;
                $ps = false; // 是否唯一子结节，唯一子结点就不包
                foreach ($data as $key2 => $data2) {
                    if ('#' != $key2) {
                        $ps_c++;
                    }
                }
                if ($ps_c > 1) {
                    $ps = true;
                }
            }
            //////////////////////////////////////////////////////
            $v = '';
            foreach ($data as $key2 => $data2) {
                $v .= self::array2xml($data2, $key2, "{$t}\t", $ps, $ps_auto);
            }
            if (is_array($data['#'])) {
                $a = '';
                foreach ($data['#'] as $key2 => $data2) {
                    if (is_numeric($data2)) {
                        if ($data2 && strlen($data2) && '0' == substr($data2, 0, 1) && '.' != substr($data2, 1, 1)) {
                            // 0 开头的字符串
                            // $data2 = $data2;
                        } elseif ((float)$data2 != $data2) {
                            $data2 = number_format($data2, 3, '.', '');
                        } else {
                            $data2 = number_format($data2, 0, '', '');
                        }
                    }
                    $a .= " {$key2}=\"{$data2}\"";
                }
                if ($v) {
                    $xml .= "{$t}<{$key}{$a}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                } else {
                    $xml .= "{$t}<{$key}{$a} />\n";
                }
            } else {
                if ($v) {
                    $xml .= "{$t}<{$key}>\n";
                    $xml .= $v;
                    $xml .= "{$t}</{$key}>\n";
                } else {
                    $xml .= "{$t}<{$key} />\n";
                }
            }
        }
        return $xml;
    }

    /**
     * @param string $data_str 数据
     * @param string $fields 字段多个,分格
     * @param string $data_rows_delimiter 行分格符
     * @param string $data_delimiter 数据分格符
     * @param string $fields_delimiter 字段分格符
     * @return array
     */
    public static function str2array(string $data_str, string $fields, string $data_rows_delimiter = "\n", $data_delimiter = ':', string $fields_delimiter = ',')
    {
        $data = explode($data_rows_delimiter, $data_str);
        $fields2 = explode($fields_delimiter, $fields);
        $fields2_len = count($fields2);

        $result = [];
        foreach ($data as $v) {
            $v = trim($v);
            if ($v) {
                $v_data = explode($data_delimiter, $v);
                $v_len = count($v_data);
                if ($fields2_len == $v_len) {
                    $v_data2 = [];
                    foreach ($v_data as $k2 => $v2) {
                        $v_data2[$fields2[$k2]] = $v2;
                    }
                    $result[] = $v_data2;
                }
            }
        }
        return $result;
    }
}
