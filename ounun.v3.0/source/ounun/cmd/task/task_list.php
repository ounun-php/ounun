<?php


namespace ounun\cmd\task;


class task_list
{
    /** @var array 任务列表 */
    const Tasks = [];

    /**
     * 任务
     * @return array
     */
    static function tasks()
    {
        return static::Tasks;
    }

    /**
     * 任务 分组
     * @return array
     */
    static function groups()
    {
        return [
            0 => '默认分组'
        ];
    }
}