<?php
// session_start();

/** 配制cache_file */
\ounun\scfg::set_global([
    'libs' =>
        [
            'libs_v1'    => ['db'=>'libs_v1','name'=>'库v1',    'table'=>[
                'pic' => [
                    'pics_6mm'  => '源6mm.cc',
                    'pics_99mm' => '源(九妹)99mm.me',
                    'pics_jpg'  => '源(妹子图)mmjpg.com',
                    'pics_mm131'=> '源mm131.com',
                    'pics_xgyw' => '源xgyw.cc',
                ]
            ]],
            'libs_v2'    => ['db'=>'adm',    'name'=>'库v2(主)','table'=>[

            ]],
        ]
]);

/** 配制database */
\ounun\scfg::set_database([
    'www' =>
        [
            'host'       => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
            'database'   => 'v2com_libs_v2',
            'username'   => 'v2cms',
            'password'   => 'kChs2r4s2r716Zd6',
            'charset'    => 'utf8'
        ],
]);