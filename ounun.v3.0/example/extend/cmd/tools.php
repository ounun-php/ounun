<?php

namespace extend\cmd;

use ounun\api_sdk\com_showapi;

class tools extends \ounun\cmd\cmd
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name = 'adm.tools';
        // 运行 "php think list" 时的简短描述
        $this->description = '工具集';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "工具包内容\n" .
            "./ounun adm:tools oss [6mm,99mm] 文件移动\n";
    }


    public function execute(array $argc_input)
    {
        // 打包下载
        echo "\n ---> " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' ok' . "\n";
    }

    /**
     * php ~/Transcend/www.cms/app.adm/index.php zrun_cmd,oss_mm,hzmm,131
     *
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,131
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,6mm
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,99mm
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,jpg
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,wgyw,yw
     * @param $mod
     */
    public function oss_mm($mod)
    {
        print_r($mod);
        $list = [
            'hzmm_6mm' => ['src' => '/data/ossfs/6mm/', 'taget' => '/tmp/ossfs_io3/6mm/'],
            'hzmm_99mm' => ['src' => '/data/ossfs/99mm/', 'taget' => '/tmp/ossfs_io3/99mm/'],
            'hzmm_jpg' => ['src' => '/data/ossfs/jpg/', 'taget' => '/tmp/ossfs_io3/jpg/'],
            'hzmm_131' => ['src' => '/data/ossfs/131/', 'taget' => '/tmp/ossfs_io3/131/'],
            'wgyw_yw' => ['src' => '/data/ossfs_wgyw/yw/', 'taget' => '/tmp/ossfs_io3/yw/'],
        ];
        if ('hzmm' == $mod[1] && '131' == $mod[2]) {
            $list2 = [$list["{$mod[1]}_{$mod[2]}"]];
        } elseif ('hzmm' == $mod[1] && '6mm' == $mod[2]) {
            $list2 = [$list["{$mod[1]}_{$mod[2]}"]];
        } elseif ('hzmm' == $mod[1] && '99mm' == $mod[2]) {
            $list2 = [$list["{$mod[1]}_{$mod[2]}"]];
        } elseif ('hzmm' == $mod[1] && 'jpg' == $mod[2]) {
            $list2 = [$list["{$mod[1]}_{$mod[2]}"]];
        } elseif ('wgyw' == $mod[1] && 'yw' == $mod[2]) {
            $list2 = [$list["{$mod[1]}_{$mod[2]}"]];
        } else {
            $list2 = array_values($list);
        }
        foreach ($list2 as $v2) {
            $src_dir = $v2['src'];
            $taget_dir = $v2['taget'];
            // print_r($v2);
            // echo "\n";
            if ($src_dir && $taget_dir) {
                $oss = new oss($src_dir, $taget_dir);
                $oss->scan('');
            }
        }
    }

    /**
     * php ~/Transcend/www.cms/app.adm/index.php zrun_cmd,oss_rename
     * @param $mod
     */
    public function oss_rename($mod)
    {
        print_r($mod);
        oss::rename('', '/Users/dreamxyp/Transcend/fcash.cash/gitian.sigs.fcash/');
    }

    /**
     *  php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_75,dtxt_75_files
     */
    public function oss_75($mod)
    {
        print_r($mod);

        $table = $mod[1];
        $order = $mod[2] ? 'DESC' : 'ASC';

        $tables = ['dtxt_75_files'];
        if (!in_array($table, $tables)) {
            exit("\$table:{$table}\n");
        }
        $root = '/tmp/ossfs_io3/';
        $id_start = 0;
        // exit();
        do {
            $where_str = $mod[2] ? ' ' : " and `id` > {$id_start} ";
            $rs = $this->_db->query("SELECT * FROM `dtxt_75_files` WHERE `is_wget` = 0 {$where_str} ORDER BY `id` {$order} limit 0,50;")->column_all();
            foreach ($rs as $v) {
                $id_start = (int)($v['id'] ? $v['id'] : $id_start);
                $wget = $v['src_url'];
                $file = "{$root}{$v['dir']}/{$v['file']}";
                $is_file = file_exists($file) ? '1' : '0';
                $is_update = false;
                if ($is_file) {
                    $is_s = filesize($file);
                    $is_s2 = str_pad($is_s, 10);
                    if ($is_s > 1024) {
                        $is_update = true;
                    } else {
                        \plugins\curl\http::file_get_put($wget, $file);
                        $is_file = file_exists($file) ? '1' : '0';
                        $is_s = '1' == $is_file ? filesize($file) : 0;
                        if ($is_file && $is_s > 1024) {
                            $is_update = true;
                        }
                    }
                } else {
                    \plugins\curl\http::file_get_put($wget, $file);
                    $is_file = file_exists($file) ? '1' : '0';
                    $is_s = '1' == $is_file ? filesize($file) : 0;
                    if ($is_file && $is_s > 1024) {
                        $is_update = true;
                    }
                }

                if ($is_update) {
                    $bind = ['is_wget' => 1];
                    $this->_db->update('`dtxt_75_files`', $bind, " `id` = :id ", $v);
                    echo "i {$v['id']}:{$is_file} s:{$is_s2} f:{$v['file']} url:{$wget}\n";
                } else {
                    $bind = ['is_wget' => -9];
                    $this->_db->update('`dtxt_75_files`', $bind, " `id` = :id ", $v);
                    echo "- i {$v['id']}:{$is_file} f:{$v['file']} url:{$wget}  ------------- {$file}  \n";
                }
            }

        } while ($rs);

    }

    /**
     * php ~/Transcend/www.cms.adm/app.adm/index.php zrun_cmd,test
     * @param $mod
     */
    public function test($mod)
    {
        print_r($mod);
        //
        $crontabs = [
            "* * * * *",
            "0 3 * * *",
            "15,30 3 * * * *",
            "15-30 3 * * *",
            "0-30/10 3 * * *",
            "0-10,50-59/2 3 * * *"
        ];
        foreach ($crontabs as $v) {
            $c = new \plugins\crontab($v);
            $time = time();
            $rs = $c->check(time());
            echo "\$time:{$time}----\n";
            print_r(['$rs' => $rs, 'c' => $c->cron()[1]]);
        }

        $rs = com_showapi::tag("性感秘书尤妮丝黑丝制服让人想入非非");
        print_r(['$rs' => $rs]);
    }
    // ------------------------------------------------------------------------------------
}
