<?php

namespace plugins;

/**
 * Class Idcard
 * @package ounun
 */
class id_card
{
    /**
     * 身份证
     * @param  string $id
     * @return bool
     */
    public static function check($id)
    {
        $id = strtoupper($id);
        $regx = '/(^\d{15}$)|(^\d{17}([0-9]|X)$)/';
        $arr_split = array();
        if (!preg_match($regx, $id)) {
            return false;
        }
        if (15 == strlen($id)) //检查15位
        {
            $regx = '/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/';
            preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth)) {
                return false;
            } else {
                return true;
            }
        } else //检查18位
        {
            $regx = '/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/';
            preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
            if (!strtotime($dtm_birth))  //检查生日日期是否正确
            {
                return false;
            } else {
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
                $arr_ch = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
                $sign = 0;
                for ($i = 0; $i < 17; $i++) {
                    $b = (int)$id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n = $sign % 11;
                $val_num = $arr_ch[$n];
                if ($val_num != substr($id, 17, 1)) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * 通过身体份证获得年龄
     *      过了这年的生日才算多了1周岁
     *      不过还是有很多人身份证上的不是自己真正的生日，不过就这么算吧。
     * @param $id
     * @return float
     */
    public static function age($id)
    {
        // 18位身份证号
        // $id   ='430223198203016917';

        // 获得出生年月日的时间戳
        $date = strtotime(substr($id, 6, 8));

        // 获得今日的时间戳
        $today = strtotime('today');

        // 得到两个日期相差的大体年数
        $diff = floor(($today - $date) / 86400 / 365);

        // strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($id, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        return $age;
    }
}
