<?php
namespace ounun\cmd\task\site_base;


use ounun\api_sdk\com_baidu;

class baidu_pc extends _baidu
{

    /**
     * 执行任务
     * @param array $paras
     * @param bool  $is_check
     */
    public function run(array $paras=[],bool $is_check = false)
    {
        if( !$this->check($is_check) ) {
            return ;
        }

        $this->_tag       = 'push_baidu_pc_mip';
        $this->_tag_sub   = '';
        $this->logs_init($this->_tag,$this->_tag_sub);
        $this->_baidu_sdk = new com_baidu($this->_db,$this->_logs);

        try {
            $this->_logs_state = \ounun\logs::state_ok;
            $this->url_push_baidu_pc_mip();
            $this->msg("Successful push pc_mip_wap");
        } catch (\Exception $e) {
            $this->_logs_state = \ounun\logs::state_fail;
            $this->msg($e->getMessage());
            $this->msg("Fail push pc_mip_wap");
        }
    }

    /**   */
    public function url_push_baidu_pc_mip()
    {
        $this->_baidu_sdk->do_push_pc();
        $this->_baidu_sdk->do_push_mip();
    }
}
