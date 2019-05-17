<?php

namespace ounun\tool;


class verify
{
    /**
     * 验证输入的邮件地址是否合法
     * @param string $email 邮箱字符串
     * @return bool
     */
    static public function email(string $email)
    {
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($email, '@') !== false && strpos($email, '.') !== false) {
            if (preg_match($chars, $email)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 验证输入的手机号码是否合法
     * @param string $mobile_phone
     * @return bool
     */
    static public function mobile(string $mobile_phone)
    {
        $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
        if (preg_match($chars, $mobile_phone)) {
            return true;
        }
        return false;
    }
}
