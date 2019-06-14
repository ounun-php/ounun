<?php

namespace ounun\mvc;

/** 常量 */
class c
{
    /** @var int 禁用 - 状态 */
    const Status_Disabled = 0;
    /** @var int 使用 - 状态 */
    const Status_Enabled = 1;
    /** @var int 否 - 状态 */
    const Status_No = 0;
    /** @var int 是 - 状态 */
    const Status_Yes = 1;

    /** @var int 正常(灰) */
    const Status_Normal = 0;
    /** @var int 等待空置状态 */
    const Status_Await = 3;
    /** @var int 工作中... */
    const Status_Runing = 5;
    /** @var int 过载状态 */
    const Status_Full = 9;
    /** @var int 完成 - 状态 */
    const Status_Done = 10;
    /** @var int 成功(绿色) */
    const Status_Succeed = 99;
    /** @var int 失败 - 状态 */
    const Status_Fail = 100;
    /** @var int 突出(橙黄) */
    const Status_Warning = 106;

    /** @var int 已删除 */
    const Del_Yes = 1;
    /** @var int 没删险 */
    const Del_No = 0;

    /** @var int 验证成功 */
    const Check_Yes = 1;
    /** @var int 没有验证 */
    const Check_No = 0;

    /** @var int 实名成功 */
    const Realname_Yes = 1;
    /** @var int 没有实名 */
    const Realname_No = 0;

    /** @var int 男性 */
    const Sex_Man = 1;
    /** @var int 女性 */
    const Sex_Woman = 2;
    /** @var int 保密 */
    const Sex_Null = 0;

    const Sex = [
        self::Sex_Man => '男',
        self::Sex_Woman => '女',
        self::Sex_Null => '保密'
    ];

    /** @var int 未婚 1  - 婚姻状况 */
    const Marriage_Yes = 1;
    /** @var int 已婚 2  - 婚姻状况 */
    const Marriage_No = 2;

    /** @var string kw::$container  取值范围: wechat, android, ipad, iphone, ipod, unknown */
    const Container_Wechat = 'wechat';
    /** @var string */
    const Container_Android = 'android';
    /** @var string */
    const Container_Ipad = 'ipad';
    /** @var string */
    const Container_Iphone = 'iphone';
    /** @var string */
    const Container_Ipod = 'ipod';
    /** @var string */
    const Container_Unknown = 'unknown';

    /** @var string kw::$os 取值范围: windows (pc端), mobile(手机端), unknown */
    const Os_Windows = 'windows';
    /** @var string */
    const Os_Mobile = 'mobile';
    /** @var string */
    const Os_Unknown = 'unknown';

    /** @var string Json - 输出Ajax格式 */
    const Format_Json = 'json';
    /** @var string XML - 输出Ajax格式 */
    const Format_Xml = 'xml';
    /** @var string JsonP - 输出Ajax格式 */
    const Format_Jsonp = 'jsonp';
    /** @var string JsonP - 输出Ajax格式 */
    const Format_Eval = 'eval';
    /** @var string JavaScript - 输出Ajax格式 */
    const Format_JS = 'javascript';
    /** @var string Html - 输出Ajax格式 */
    const Format_Html = 'html';
}
