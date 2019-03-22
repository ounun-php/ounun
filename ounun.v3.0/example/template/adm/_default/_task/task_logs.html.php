<?php require v::tpl_fixed('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <style type="text/css">
            .c0{color: #666666;}
            .c1{color: #cd0a0a;}
            .c6{color: #ffbd09;}
            .c99{color: #56ff08;}
            .t{color: #cccccc;}
            .tb{color: darkblue;}
            .te{color: darkred;}
            .tr{color: blueviolet;}
        </style>
        <table class="tb tb2 ">
            <tr class="header">
                <th>#ID</th>
                <th>任务</th>
                <th>日志数据</th>
                <th>扩展</th>
                <th>功能</th>
            </tr>
            <?php

            foreach ($datas as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td><?php echo $v['id']; ?></td>
                    <td>
                        <div class="c<?php echo $v['state']; ?>"><?php echo "[{$v['task_id']}]{$tasks[$v['task_id']]}"; ?></div>
                        <div><?php echo "{$v['tag']}".($v['tag_sub']?"<br />{$v['tag_sub']}":''); ?></div>
                        <br />
                        <?php echo date('Y-m-d',$v['time_add']); ?>
                        <div class="c<?php echo $v['state']; ?>"><?php echo \ounun\logs::state[$v['state']]; ?></div>
                        <div class="tb"><?php echo date('H:i:s',$v['time_add']); ?></div>
                        <div class="te"><?php echo date('H:i:s',$v['time_end']); ?></div>
                    </td>
                    <td>
                        <?php
                            $logs = json_decode($v['data'],true);
                            if($logs && is_array($logs))
                            {
                                foreach ($logs as $l)
                                {
                                    echo "<div><span class='t'>[".date("i:s",$l['t'])."]</span><span class='c{$l['s']}'>{$l['l']}</div></div>";
                                }
                            }else
                            {
                                echo str_replace('},{','}<br />{',$v['data']);
                            }
                        ?>
                        <span class='t'>[执行时间]:</span><span class="tr"><?php echo $v['time_run']; ?>s</span>
                    </td>
                    <td><?php echo $v['exts']; ?></td>
                    <td>
                        <a href="logs.html?id=<?php echo $v['id']?>&act=del" onclick2="return msg_yn();">删除</a>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr bgcolor="#FFFFFF">
                <td colspan="30">
                    <?php echo $ps['note'].' &nbsp;&nbsp; ';
                    echo implode('&nbsp;',$ps['page']);?>
                </td>
            </tr>
        </table>
    </div>
<?php require v::tpl_fixed('_foot.html.php')?>