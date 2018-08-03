<?php
namespace module_pics;

class stat_api extends \v
{
    /**
     * 广告
     * @param $mod array
     */
    public function m($mod)
    {
        echo 'var m_gcom='.json_encode(\app\ads::m );
    }

    /**
     * 广告
     * @param $mod array
     */
    public function times($mod)
    {
        $pic_id = (int)$mod[1];

        $this->init_page("/times/{$pic_id}.js",false,false);

        $this->_db_v->active();
        $this->_db_v->add('`pic_data`',['pic_times'=>1],' `pic_id` = ? ',$pic_id);

        $rs     = $this->_db_v->row('SELECT `pic_times` FROM  `pic_data` WHERE `pic_id` = ? LIMIT 0 , 1;',$pic_id);
        echo "try{details_pic_times_update({$pic_id},{$rs['pic_times']});}catch(err){}";
    }
}