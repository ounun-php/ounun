<?php
namespace extend\_test;

// "en_US"=>"English",
class oss
{
    protected $_src_dir    = '';

    protected $_taget_dir  = '';

    public function __construct(string $src_dir,string $taget_dir)
    {
        $this->_src_dir   = $src_dir;
        $this->_taget_dir = $taget_dir;
    }

    function scan($dir)
    {
        $src_dir2 = $this->_src_dir.$dir;
        echo "{$src_dir2} \t --> i\n";
        if ($dh = opendir($src_dir2)) {
            while (($file = readdir($dh)) !== false) {
                // echo $src_dir2.$file." \t --> \n";
                if($file=="." || $file=="..") {

                }elseif(is_dir($src_dir2.$file)) {
                    if(!file_exists($this->_taget_dir.$dir)) {
                        echo "mkdir \t -> ".$this->_taget_dir.$dir."\n";
                        mkdir($this->_taget_dir.$dir,0777,true);
                    }
                    //echo Dir_Src.$dir.$file." --> d\n";
                    $this->scan("{$dir}{$file}/");
                } else {
                    $taget_file2 = $this->_taget_dir.$dir.$file;
                    if(!file_exists($taget_file2)) {
                        $dir3 = dirname($taget_file2);
                        if(!file_exists($dir3)) {
                            echo "mkdir \t -> ".$dir3."\n";
                            mkdir($dir3,0777,true);
                        }
                        echo "{$dir3} \t ->  ".$src_dir2.$file." \t -> ".$taget_file2."\n";
                        copy($src_dir2.$file,$taget_file2);
                    }
                }
            }
            closedir($dh);
        }
    }

    static public function  rename(string $dir,string $dir_root)
    {
        $src_dir2 = $dir_root.$dir;
        echo "{$src_dir2} \t --> dir\n";
        if ($dh = opendir($src_dir2)) {
            while (($file = readdir($dh)) !== false) {
                // echo $src_dir2.$file." \t --> \n";
                if($file=="." || $file=="..") {

                }elseif(is_dir($src_dir2.$file)) {

                    self::rename("{$dir}{$file}/",$dir_root);
                } else {
                    if(strstr($file,'litecoin')) {
                        $file2 = str_replace('litecoin','fcash',$file);
                        echo " {$file} --> {$file2}\n";
                        \rename($src_dir2.$file,$src_dir2.$file2);
                    }
                }
            }
            closedir($dh);
        }
    }
}
// oss::list_dir('');