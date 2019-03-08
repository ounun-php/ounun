<?php
/**
 * 输出script
 * @param string $str
 * @return string
 */
function script_write(string $str)
{
    return 'document.write(' . json_encode_unescaped($str) . ')';
}


/**
 * @param $length
 * @param bool $numeric
 * @return string
 */
function random($length, $numeric = false)
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