<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>  <div style="font-size: 14px;font-weight: bold;"><?php echo $_GET['host']?></div>
        </div>
        <div>

            <style type="text/css">
                .rem{color: #666666;font-size: 12px;}
                .close{color: #8C8D8E;font-size: 12px;}
                .bash{color: #ffffff;font-size: 12px;}
            </style>
            <!--
            <?php
            //  print_r(['$zqun_cc'=>$zqun_cc,'$data'=>$data]);
            ?>
            -->
            <div style="color: #0000FF;border: #8C8D8E 1px dashed;padding: 10px;margin: 5px;background: black;">
                <?php
                $rs = [];
                echo "<div class='rem'># /data/bt.crontab/crontab_{$_GET['host']}.sh </div><br />";
                echo "<div class='rem'># crontab task </div>";
                foreach ($data as $v)
                {
                    if($v['state'])
                    {
                        echo "<div class='bash'>Php_Cmd  {$zqun_cc[$v['zqun_tag']]['dir']}/app.{$v['site_tag']}/index.php  zrun_cmd,crontab,5,595  {$v['site_tag']}</div>";
                    }else
                    {
                        $rs[] = "<div class='close'># Php_Cmd  {$zqun_cc[$v['zqun_tag']]['dir']}/app.{$v['site_tag']}/index.php  zrun_cmd,crontab,5,595  {$v['site_tag']}</div>";
                    }
                }
                echo "<div class='rem'># close site</div>";
                echo implode('',$rs);
                echo "<div class='rem'># end</div>";
                echo "<br />";
                echo "<div class='rem'># php /www/wwwroot/cms.adm/app.adm/index.php zrun_cmd,crontab_step,6,1 adm  # 模式 0:采集全部   1:检查   2:更新</div>";
                ?>
            </div>
        </div>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>