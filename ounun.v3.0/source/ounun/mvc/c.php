<?php
namespace ounun\mvc;

/** 常量 */
class c
{
    /** @var string kw::$container  取值范围: wechat, android, ipad, iphone, ipod, unknown */
    const Container_Wechat  = 'wechat';
    /** @var string  */
    const Container_Android = 'android';
    /** @var string  */
    const Container_Ipad    = 'ipad';
    /** @var string  */
    const Container_Iphone  = 'iphone';
    /** @var string  */
    const Container_Ipod    = 'ipod';
    /** @var string  */
    const Container_Unknown = 'unknown';

    /** @var string kw::$os 取值范围: windows (pc端), mobile(手机端), unknown */
    const Os_Windows = 'windows';
    /** @var string  */
    const Os_Mobile  = 'mobile';
    /** @var string  */
    const Os_Unknown = 'unknown';
}