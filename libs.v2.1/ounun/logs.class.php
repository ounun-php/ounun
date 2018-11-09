<?php

namespace ounun;


class logs
{
    /** @var int 0:正常(灰)  */
    const state_normal = 0;
    /** @var int 1:失败(红色) */
    const state_fail   = 1;
    /** @var int 6:警告突出(橙黄)  */
    const state_warn   = 6;
    /** @var int 99:成功(绿色) */
    const state_ok     = 99;

    const state        = [
        self::state_normal => '正常',
        self::state_fail   => '失败',
        self::state_warn   => '警告',
        self::state_ok     => '成功',
    ];

    /** @var \ounun\mysqli  */
    protected $_db        = null;
    /** @var string 表名 */
    protected $_table     = '';
//    /** @var int 自增长ID  */
//    protected $_id      = 0;
    /** @var int 任务ID  */
    protected $_task_id   = 0;
    /** @var string 分类 */
    protected $_tag       = '';
    /** @var string 子分类 */
    protected $_tag_sub   = '';
    /** @var int 状态  */
//    protected $_state     = 0 ;
    /** @var array 日志数据logs_data  */
    protected $_data      = [];
    /** @var int 添加时间 */
    protected $_time_add  = 0;
    /** @var int 完成时间 */
    // protected $_time_end  = 0;
    /** @var array 任务参数paras  */
    protected $_exts      = [];

    /**
     * logs constructor.
     * @param mysqli $db    \ounun\mysqli
     * @param string $table 表名
     */
    public function __construct(\ounun\mysqli $db,string $table = 'z_task_logs')
    {
        $this->_db    = $db;
        $this->_table = $table;
    }

    /**
     * 任务日志
     * @param int $task_id
     * @param string $tag
     * @param string $tag_sub
     * @param int $time_add
     */
    public function task(int $task_id, string $tag, string $tag_sub, int $time_add)
    {
        $this->_task_id  = $task_id;
        $this->_tag      = $tag;
        $this->_tag_sub  = $tag_sub;
     // $this->_state    = $state;
        $this->_data     = [];
        $this->_time_add = $time_add;
     // $this->_time_end = 0;
        $this->_exts     = [];
    }

    /**
     * 日志数据logs_data
     * @param bool $state   状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色)
     * @param int $time     时间
     * @param string $logs  内容
     */
    public function data(int $state,int $time,string $logs)
    {
        /**  状态  时间 内容  */
        $this->_data[] = ['s'=>$state,'t'=>$time,'l'=>$logs];
    }

    /**
     * 任务参数paras/扩展json
     * @param array $paras
     */
    public function exts(array $paras = [])
    {
        $this->_exts = $paras;
    }

    /**
     * 写入日志
     * @param bool $over_clean 写完是否清理logs数据
     */
    public function write(int $state,float $run_time,bool $over_clean = true)
    {
        if(($this->_task_id || $this->_tag) && $this->_data)
        {
         // $this->_state  = $state;
            $bind = [
                'task_id'  => $this->_task_id,
                'tag'      => $this->_tag,
                'tag_sub'  => $this->_tag_sub,
                'state'    => $state,
                'data'     => json_encode($this->_data,JSON_UNESCAPED_UNICODE),
                'time_add' => $this->_time_add,
                'time_end' => time(),
                'time_run' => $run_time,
                'exts'     => json_encode($this->_exts,JSON_UNESCAPED_UNICODE),
            ];
            $id = $this->_db->insert("`{$this->_table}`",$bind);
            if($id && $over_clean)
            {
                $this->task(0,'','',0);
            }
            echo $this->_db->sql()."\n";
        }
    }
}

//class ld
//{
//
//    /** @var bool 状态 */
//    public $state = false;
//    /** @var int  时间 */
//    public $time = 0;
//    /** @var string 内容 */
//    public $logs = '';
//
//    /**
//     * ld constructor.
//     * @param bool $state   状态
//     * @param int $time     时间
//     * @param string $logs  内容
//     */
//    public function __construct(bool $state,int $time,string $logs)
//    {
//        $this->state = $state;
//        $this->time = $time;
//        $this->logs = $logs;
//    }
//
//}
/**
 * DROP TABLE IF EXISTS `z_task_logs`;
CREATE TABLE IF NOT EXISTS `z_task_logs` (
`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增长ID',
`task_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务ID',
`tag` varchar(128) NOT NULL DEFAULT '' COMMENT '分类/标识',
`tag_sub` varchar(128) NOT NULL DEFAULT '' COMMENT '子分类',
`state` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',
`data` text NOT NULL COMMENT '数据json [{...},{...}]',
`time_add` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
`time_end` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '完成时间',
`exts` text NOT NULL COMMENT '任务参数paras/扩展json',
PRIMARY KEY (`id`),
KEY `state` (`state`),
KEY `cls` (`tag`,`tag_sub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务列表' ROW_FORMAT=COMPACT;
 */