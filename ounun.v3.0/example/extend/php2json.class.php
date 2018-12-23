<?php
namespace extend;

class php2json
{
    /** @var \ounun\mysqli */
    protected $_db;

    public function __construct(\ounun\mysqli $db)
    {
        $this->_db = $db;
    }

    /**
     * php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_cmd,php2json
     * php /data/www_383434/index.php zrun_cmd,php2json
     * php /data/rbj_www.2015/index.php zrun_cmd,php2json
     * @param $mod
     */
    public function conver($dir_root  = '/data/ossfs/')
    {
        // $dir_root = IsDebug?'/Users/dreamxyp/Transcend/www/pics/':'/data2/webroot/';
        // $dir_root  = '/data/ossfs/';

        // print_r($GLOBALS['_scfg']['db']);
        // $db       = self::db('com_383434');
        do{
            $rs       = $this->_db->data_array('SELECT * FROM `pic_data` where `is_done` = 1  ORDER BY `pic_id` ASC limit 0,10;');
            foreach ($rs as $v)
            {
                $pic_ext  = unserialize($v['pic_ext']);
                if($pic_ext && is_array($pic_ext))
                {
                    foreach ($pic_ext as $v2)
                    {
                        $v3  =  explode('/',$v2);
                        array_pop($v3);

                        $dir  =  $dir_root.implode('/',$v3);
                        $file =  $dir_root.$v2;
                        $url  =  'http://7u2sqb.com1.z0.glb.clouddn.com/'.$v2;

                        echo "\$dir :{$dir}\n";
                        echo "\$file:{$file}\n";
                        if(!file_exists($dir))
                        {
                            mkdir($dir,0777,true);
                        }
                        if(!file_exists($file))
                        {
                            $c = \plugins\curl\http::file_get_contents($url,$url);
                            file_put_contents($file,$c);
                        }
                    }
                    $bind = [
                        // 'pic_ext' => json_encode($pic_ext),
                        'is_done' => 2
                    ];
                    $this->_db->update('`pic_data`',$bind,' `pic_id` = :pic_id ',$v);
                    echo $this->_db->sql()."\n";
                }
                //  foreach ()
                //  print_r($pic_ext);
                echo "pic_id:{$v['pic_id']} <br />\n";
            }
        }while($rs);
    }
}