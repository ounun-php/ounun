<?php

namespace collect;


class api
{
    /** @var \ounun\mysqli */
    protected $_db;


    public function __construct(\ounun\mysqli $db)
    {
        $this->_db = $db;
    }

    /** 数据 */
    protected function _db_data($pic_title,$pic_tag,$pic_centent,$pic_ext,$pic_origin,$add_time)
    {
        return array(
            'pic_title'     => $pic_title,
            'pic_tag'       => $pic_tag,
            'pic_centent'   => $pic_centent,
            'pic_ext'       => $pic_ext,
            'pic_origin'    => $pic_origin,
            'add_time'      => $add_time
        );
    }
    /** 插入单条数据 */
    protected function _db_install_one($data)
    {
        if($data)
        {
            $this->_db->insert('`lib_data_pic`',$data);
        }
    }
    /** 上传图片 */
    protected function _put_img($local_filename,$dir,$filename,$bucket='com-reboju-mm')
    {
//        $filename = $dir.$filename;
//        echo "\$local_filename:{$local_filename}\n";
//        echo "\$filename:{$filename}\n";
//        echo "\$bucket:{$bucket}\n";
//        $qiniu = new \plugins\qiniu\Qiniu($GLOBALS['scfg']['qiniu'],$bucket);
//        $rs    = $qiniu->put_file($filename,$local_filename);
//        var_export($rs);
    }
}
