<?php

namespace ounun\tool;

/**
 * php解析crontab时间格式
 *
 * crontab 时间格式:
 * 配置                       说明
 * “* * * * *”           分 时 日 月 周
 * “0 3 * * *”           数字精确配置, 星号为任意.(每天凌晨3点整)
 * “15,30 3 * * *”         逗号表示枚举 (每天3点15分和3点30分)
 * “15-30 3 * * *”         短线表示范围 (每天的3点15分到30分)
 * “0-30/10 3 * * *”       斜杠表示间隔 (每天3点0分到30分之间, 每10分钟一次)
 * “0-10,50-59/2 3 * * *”  优先级:枚举>范围>间隔 (每天3点0分到10分每分钟一次，以及50到59分期间每2分钟一次)
 *
 * 注意：不支持解析以英文名称配置的时间计划(如:”0 4 1 jan “)
 * 应用场景: crontab配置一个php, 然后由php管理多个php的crontab任务
 * crontab 时间格式php解析类(PHP >= 5.4)
 */
class crontab
{
    /** @var array  解析数据 */
    protected $_cron = [];

    /** @var string 空:格式正确      非空:错误提示 */
    protected $_error = '';

    /**
     * 格式化crontab时间设置字符串,用于比较
     * crontab constructor.
     * @param string $cron_str crontab的时间计划字符串，如"15 3 * * *"
     * @param array $cron_array
     */
    public function __construct(string $cron_str = '', array $cron_array = [])
    {
        if ($cron_array && is_array($cron_array) && count($cron_array) == 5) {
            $this->_cron = $cron_array;
        } elseif ($cron_str) {
            // 格式检查
            $cron_str = trim($cron_str);
            $reg = '#^([\*,\/,\-\d]+)( [,\/,\-\d\*]+){4}$#';
            if (!preg_match($reg, $cron_str)) {
                $this->_cron = [];
                $this->_error = "格式错误:{$cron_str}";
                return;
            }
            // 分别解析 分、 时、 日、 月、 周
            $n2m = [['min' => 0, 'max' => 59, 'name' => '分'],
                ['min' => 0, 'max' => 59, 'name' => '小时'],
                ['min' => 1, 'max' => 31, 'name' => '日'],
                ['min' => 1, 'max' => 12, 'name' => '月'],
                ['min' => 0, 'max' => 6, 'name' => '周']];
            $parts = explode(' ', $cron_str);
            foreach ($parts as $k => $v) {
                $tmp = $this->parse($parts[$k], $n2m[$k]['min'], $n2m[$k]['max']); // 分
                if (error_is($tmp)) {
                    $this->_cron = [];
                    $this->_error = "{$n2m[$k]['name']}:" . error_message($tmp);
                    return;
                }
                $this->_cron[$k] = succeed_data($tmp);
            }
        } else {
            $this->_cron = [];
            $this->_error = "cron_str与cron_data 不能为都为空";
        }
        return;
    }

    /**
     * 检查某时间($time)是否符合某个corntab时间计划($str_cron)
     * @param  int $time 时间戳
     * @return array  出错返回string（错误信息）
     */
    public function check(int $time): array
    {
        $cron = $this->cron();
        if (error_is($cron)) {
            return $cron;
        }

        $curr = $this->format($time);
        $cron = succeed_data($cron);
        $succeed = (!$cron[0] || in_array($curr[0], $cron[0]))
            && (!$cron[1] || in_array($curr[1], $cron[1]))
            && (!$cron[2] || in_array($curr[2], $cron[2]))
            && (!$cron[3] || in_array($curr[3], $cron[3]))
            && (!$cron[4] || in_array($curr[4], $cron[4]));
        return succeed($succeed);
    }


    /**
     * 格式化时间戳，以便比较
     * @param int $time 时间戳
     *
     * @return array
     */
    public function format(int $time): array
    {
        return explode('-', date('i-G-j-n-w', $time));
    }

    /**
     * 获得this->cron数据
     * @return array
     */
    public function cron(): array
    {
        if ($this->_error) {
            return error($this->_error);
        } elseif ($this->_cron && count($this->_cron) == 5) {
            return succeed($this->_cron);
        } else {
            return error('对像cron:数据为空');
        }
    }

    /**
     * 解析crontab时间计划里一个部分(分、时、日、月、周)的取值列表
     * @param string $part 时间计划里的一个部分，被空格分隔后的一个部分
     * @param int $f_min 此部分的最小取值
     * @param int $f_max 此部分的最大取值
     *
     * @return array 若为空数组则表示可任意取值
     */
    protected function parse($part, $f_min, $f_max)
    {
        $list = [];
        // 处理"," -- 列表
        if (false !== strpos($part, ',')) {
            $arr = explode(',', $part);
            foreach ($arr as $v) {
                $tmp = $this->parse($v, $f_min, $f_max);
                if (error_is($tmp)) {
                    return $tmp;
                }
                $list = array_merge($list, $tmp['data']);
            }
            return succeed($list);
        }

        // 处理"/" -- 间隔
        $tmp = explode('/', $part);
        $part = $tmp[0];
        $step = isset($tmp[1]) ? $tmp[1] : 1;

        // 处理"-" -- 范围
        if (false !== strpos($part, '-')) {
            list($min, $max) = explode('-', $part);
            if ($min > $max) {
                return error('使用"-"设置范围时，左不能大于右');
            }
        } elseif ('*' == $part) {
            $min = $f_min;
            $max = $f_max;
        } else {
            // 数字
            $min = $max = $part;
        }

        // 空数组表示可以任意值
        if ($min == $f_min && $max == $f_max && $step == 1) {
            return succeed($list);
        }

        // 越界判断
        if ($min < $f_min || $max > $f_max) {
            return error('数值越界。应该：分0-59，时0-59，日1-31，月1-12，周0-6');
        }

        $list = $max - $min > $step ? range($min, $max, $step) : [(int)$min];
        return succeed($list);
    }

}
