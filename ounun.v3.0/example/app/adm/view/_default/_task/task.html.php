<?php require v::tpl_fixed('_head.html.php')?>
<style type="text/css">
    .rem{color: #666666;font-size: 12px;}
    .close{color: #8C8D8E;font-size: 12px;}
    .bash{color: #0000FF;font-size: 12px;}
</style>
<div class="container" id="cpcontainer">
    <div class="itemtitle">
        <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
    </div>
    <table class="tb tb2 ">
        <tr class="header">
            <th>#id</th>
            <th>任务名称</th>
            <th>类型</th>
            <th>定时</th>
            <th>任务参数</th>
            <th>开启时间</th>
            <th>结束时间</th>
            <th>添加时间</th>
            <th>最后执行</th>
            <th>执行次数</th>
            <th>操作[<a href="?act=add">添加</a>]</th>
        </tr>
        <?php foreach ($datas as $v){ ?>
            <tr style="height:20px" class="hover">
                <td><?php echo $v['task_id'];?></td>
                <td><?php echo $v['task_name'];?></td>
                <td style="color: <?php echo $v['type']?'#CC0000':'#EF0000'?>">
                    最小间隔:<strong><?php echo $v['interval']?></strong>秒 <span style="color: <?php echo $v['type']?'#CC0000':'#EF0000'?>"><?php echo \task\manage::type[$v['type']];?></span>
                </td>
                <td>
                    <?php if(\task\manage::type_crontab == $v['type']){ ?>
                        定时:<?php echo $v['crontab']?>

                    <?php }else{?>
                        间隔:<strong><?php echo $v['interval']?></strong>秒
                    <?php }?>
                </td>
                <td>
                    <?php
                    $args = json_decode($v['args'],true);
                    echo "<span style='color: #CCCCCC'>模式:</span>".\task\manage::mode[$args['mode']]." <span style='color: #CCCCCC'>参数:</span>".$args['data'];
                    if($args['exts'])
                    {
                        echo "<div style='color: #0000FF;'>{$args['exts']}</div>";
                    }
                    ?>
                </td>

                <td style="color: darkblue;"><?php echo date('Y-m-d',$v['time_begin']);?></td>
                <td><?php echo date('Y-m-d',$v['time_end']);?></td>
                <td style="color: #8C8D8E"><?php echo date('Y-m-d',$v['time_add']);?></td>
                <td><?php echo $v['time_last']?date('Y-m-d H:i:s',$v['time_last']):'-';?></td>
                <td>
                    <?php if($v['times']){ echo $v['times'];?>次
                        <a href="logs.html?task_id=<?php echo $v['task_id']?>">日志</a>
                    <?php }else{?>
                        -
                    <?php }?>
                </td>
                <td>
                    <a href="?task_id=<?php echo $v['task_id']?>&act=del" onclick="return msg_yn();">删除</a>
                    <a href="?task_id=<?php echo $v['task_id']?>&act=add">修改</a>
                </td>
            </tr>
        <?php }?>
        <tr>
            <td colspan="30">
                <?php echo $ps['note'].' &nbsp;&nbsp; ';
                echo implode('&nbsp;',$ps['page']);?>
            </td>
        </tr>
        <tr>
            <td colspan="30" style="color: #0000FF;border: #8C8D8E 1px dashed;padding: 10px;margin: 20px;background: #ffffff;">
                <?php echo "<div class='bash'>php  {$cmd_serv}  zrun_cmd,crontab,5,595  {$site_info['site_tag']}  <span class='rem'># zrun_cmd,crontab,间隔,运行时长</span></div>"; ?>
                <?php echo "<div class='bash'>php  {$cmd_serv}  zrun_cmd,crontab_step,5  {$site_info['site_tag']} <span class='rem'># zrun_cmd,crontab_step,任务ID</span></div>"; ?>
                <div class='rem'># 本地</div>
                <?php echo "<div class='bash'>php  {$cmd_local}  zrun_cmd,crontab,5,595  {$site_info['site_tag']}  <span class='rem'># zrun_cmd,crontab,间隔,运行时长</span></div>"; ?>
                <?php echo "<div class='bash'>php  {$cmd_local}  zrun_cmd,crontab_step,5  {$site_info['site_tag']} <span class='rem'># zrun_cmd,crontab_step,任务ID</span></div>"; ?>
            </td>
        </tr>
    </table>
</div>
<?php require v::tpl_fixed('_foot.html.php')?>
