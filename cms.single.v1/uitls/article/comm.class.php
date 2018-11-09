<?php
namespace uitls\article;

class comm extends \uitls\comm
{
    /**
     * 图片相关
     * @param $v
     * @param string $webp
     * @return string
     */
    public static function pic_rec($v, $webp='!14')
    {
        $pic_url     = $v['centent']['urls'];
        if($pic_url)
        {
            $is_img  = $v['is_img'] ? (int)$v['is_img'] : 1;
            $file    = $pic_url[$is_img]['file'];
            return self::static_v2(explode('/',$v['data_only'])[0],$file,$webp);
        }
        return '';
    }

    /**
     * @param $v
     * @return string
     */
    public static function url($v)
    {
        $mulu = \site_cfg::mulu_maps()[$v['category_sub']];
        return "/{$mulu['parent']}/{$mulu['key']}/{$v['data_id']}.html";
    }

    public static function txt_abstract($v)
    {
        if($v && $v['centent'] && $v['centent']['abstract'])
        {
            return $v['centent']['abstract'];
        }
        return '-';
    }


}
