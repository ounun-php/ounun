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

