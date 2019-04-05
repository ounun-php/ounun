<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/2
 * Time: 23:47
 */

namespace ounun\tool;


class collect
{
    /**
     * 正则提取正文里指定的第几张图片地址
     * @param string $content
     * @return array
     */
    static function img_urls(string $content): array
    {
        preg_match_all('/<img(.*?)src="(.*?)(?=")/si', $content, $imgarr);///(?<=img.src=").*?(?=")/si
        preg_match_all('/(?<=src=").*?(?=")/si', implode('" ', $imgarr[0]) . '" ', $imgarr);
        return $imgarr[0];
    }
}
