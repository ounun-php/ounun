<?php
namespace task;

class base_system_push_baidu_xzh extends base 
{
    /** @var \api_sdk\com_baidu  */
    protected $_baidu_sdk = null;

    /**
     * 执行任务
     * @param array $paras
     * @param bool  $is_check
     */
    public function run(array $paras=[],bool $is_check = false)
    {
        if( !$this->check($is_check) ) { return ; }

        $this->_tag       = 'push_baidu_xzh';
        $this->_tag_sub   = '';
        $this->logs_init($this->_tag,$this->_tag_sub);
        $this->_baidu_sdk = new \api_sdk\com_baidu($this->_db,$this->_logs);


        try
        {
            $this->_logs_state = \ounun\logs::state_ok;
            $this->url_push_baidu_xzh();
            $this->msg("Successful push xzh");
        } catch (\Exception $e)
        {
            $this->_logs_state = \ounun\logs::state_fail;
            $this->msg($e->getMessage());
            $this->msg("Fail push xzh");
        }
    }

    /** */
    public function url_push_baidu_xzh()
    {
        $this->_baidu_sdk->do_push_xzh_batch();
        $this->_baidu_sdk->do_push_xzh_realtime();
    }
}
