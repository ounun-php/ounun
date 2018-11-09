<?php
namespace module\_75;

class api extends \module\base_api
{
    /**
     * 点击 数量
     * @param $mod array
     */
    public function times($mod)
    {
        $data_id = (int)$mod[1];
        // $this->init_page("/api/times/{$pic_id}.js",false,false);


        $this->_db_v->active();
        $this->_db_v->add('`data`',['times'=>1],' `data_id` = ? ',$data_id);

        $rs     = $this->_db_v->row('SELECT `times` FROM  `data` WHERE `data_id` = ? LIMIT 0 , 1;',$data_id);
        echo "try{details_times_update({$data_id},{$rs['times']});}catch(err){}";
    }
}