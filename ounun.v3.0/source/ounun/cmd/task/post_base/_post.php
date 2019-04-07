<?php
namespace ounun\cmd\task\post_base;

use ounun\cmd\console;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\mvc\model\admin\purview;

abstract class _post extends task_base
{
    public static $name = '更新内容 [post]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 8 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;
    /** @var string 类型 */
    public static $site_type = purview::app_type_site;


    /** @var string  网站数据 - 数据 - 表名 */
    public static $table_site_data = '';
    /** @var string  网站数据 - 附件 - 表名 */
    public static $table_site_attachment = '';

    /**
     * @return array
     */
    public function status()
    {
        $this->_logs_status = manage::Logs_Fail;
        manage::logs_msg("error:" . __METHOD__, $this->_logs_status);
        return [];
    }

    /**
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        $site_tag = ($input && is_array($input)) ? ((int)array_shift($input)) : '';

        print_r([
            '$site_tag' => $site_tag
        ]);

        console::echo(__METHOD__, console::Color_Red);
        try {
            $this->_logs_status = manage::Logs_Succeed;
            manage::logs_msg("Successful update:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status);
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status);
            manage::logs_msg("Fail Coll tag:{$this->_tag} tag_sub:{$this->_tag_sub}",manage::Logs_Fail);
        }
    }
}
