<?php require v::tpl_fixed('_head.html.php')?>
<div class="container" id="cpcontainer">
    <div class="itemtitle">
        <h3 style="padding-left: 5px;">
        <?php
            echo $rs['task_id']?"修改":"添加";

            $task_list  = [];
            $task_list2 = [];
            foreach (\task\data::list as $k2=> $v2)
            {
                foreach ($v2 as $k=>$v)
                {
                    if($v['site_type'] == $this->_site_type)
                    {
                        $max           = max(3,0.01 * $v['interval']);
                        $v['interval'] = $v['interval'] - rand(1,$max);
                        $v['crontab']  = str_replace('{1-59}',rand(1,59),$v['crontab']);
                        $task_list[$k] = $v;
                        $task_list2[$k2][$k] = $v;
                    }
                }
            }


            $rs['time_begin']  = $rs['time_begin']?$rs['time_begin']:time();
            $rs['time_end']    = $rs['time_end']  ?$rs['time_end']  :time()+10*365*3600*24;

            $rs['data']        = '';
            $rs['args']        = $rs['args']?json_decode($rs['args'],true):['mode'=>0,'data'=>'','exts'=>''];
            //
         ?> - {$page_title_sub}</h3>
    </div>
    <style type="text/css">
        .rem{color: #666666;font-size: 12px;}
        .rem2{color: #CCCCCC;font-size: 12px;}
        .txt2{color: #0000FF;font-size: 12px;}
    </style>
    <form name="cpform" method="post" autocomplete="off" onsubmit="return check_form(this);" id="cpform" >
        <input type="hidden" name="task_id"  value="<?php echo (int)$rs['task_id'];?>" />
        <table class="tb tb2 ">
            <?php if($rs['task_id']){?>
                <tr style="height:20px" class="hover">
                    <td class="td24">#ID</td>
                    <td class="td26"><?php echo $rs['task_id']?></td>
                </tr>
            <?php }?>
            <tr style="height:20px" class="hover">
                <td>选择任务：</td>
                <td>
                    <select name="args[data]" id="args_data" onchange="onchange_args(this)">
                        <?php foreach ($task_list2 as $k2=>$v2){?>
                            <optgroup label="<?php  echo $k2?>">
                                <?php foreach ($v2 as $k => $v){?>
                                    <option value="<?php echo $k?>"><?php echo $v['name']?></option>
                                <?php }?>
                            </optgroup>
                        <?php }?>
                    </select>

                </td>
            </tr>

            <tr style="height:20px" class="hover">
                <td>类型：</td>
                <td>
                    <select name="type" id="task_type" onchange="onchange_type(this.value,false);">
                        <?php foreach (\task\manage::type as $k => $v){?>
                            <option value="<?php echo $k?>"><?php echo "[{$k}]{$v}"?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>

            <tr style="height:20px" class="hover">
                <td>模式:</td>
                <td>
                    <select name="args[mode]" id="args_mode">
                        <?php foreach (\task\manage::mode as $k => $v){?>
                            <option value="<?php echo $k?>"><?php echo "[{$k}]{$v}"?></option>
                        <?php }?>
                    </select>
               </td>
            </tr>

            <tr style="height:20px" class="hover">
                <td class="td24">任务名称：</td>
                <td class="td26"><input class="txt" type="text" name="task_name" value="<?php echo $rs['task_name']?>" style="width: 400px;" /></td>
            </tr>


            <tr style="height:20px" class="hover">
                <td>定时	：</td>
                <td>
                    <div id="data_type_0" style="display: none;">
                        间隔: <input class="txt" type="text" name="interval1" value="<?php echo $rs['interval']?>" style="width: 50px;" />秒
                    </div>

                    <div id="data_type_1" style="display: none;">
                        定时参数: <input class="txt" type="text" name="crontab" value="<?php echo $rs['crontab']?>" style="width: 200px;" />
                        最小间隔: <input class="txt" type="text" name="interval0" value="<?php echo $rs['interval']?>" style="width: 50px;" />秒
                        <div style="padding: 5px;border: #8C8D8E 1px dashed;margin: 5px 0 0 0;">
                            <div class="rem">分钟(0-59)  小时(0-23)  日期(1-31)  月份(1-12)  星期(0-6 周日为0)</div>
                            <div class="txt2">* * * * *  <span class="rem2">每天分钟执行一次</span></div>
                            <div class="txt2">0 3 * * *  <span class="rem2">每天3点0分时执行一次</span></div>
                            <div class="txt2">15,30 3 * * * * <span class="rem2">每天3点15分30分各执行一次</span></div>
                            <div class="txt2">15-30 3 * * * <span class="rem2">每天3点15分到30分期间，每分钟执行一次</span></div>
                            <div class="txt2">0-30/10 3 * * * <span class="rem2">每天3点0分到30分期间，每过10分钟执行一次</span></div>
                            <div class="txt2">0-10,50-59/2 3 * * * <span class="rem2">每天3点0分到30分每分钟执行一次，以及50分到59分之间每2分钟执行一次</span></div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr style="height:20px" class="hover">
                <td>开启时段：</td>
                <td>
                    开启时间(0点0分开始)：<input class="txt" type="text" name="time_begin" onclick="SelectDate(this);" value="<?php echo date('Y-m-d',$rs['time_begin'])?>" style="width: 100px;" /> -
                    结束时间(23点59分结束)：<input class="txt" type="text" name="time_end" onclick="SelectDate(this);" value="<?php echo date('Y-m-d',$rs['time_end'])?>" style="width: 100px;" />
                </td>
            </tr>

            <tr style="height:20px" class="hover">
                <td>扩展参数：</td>
                <td>
                    <input class="txt" type="text" name="args[exts]" id="args_exts" value="<?php echo $rs['args']['exts']?>" style="width: 300px;" />
                    <div class="rem2">默认为空</div>
                    <div class="txt2"><span class="rem2">update:</span>$libs_key,$in_table,$out_table</div>
                    <div class="rem2"><span class="txt2">mm131:</span>libs_v1,pic_data,pics_mm131</div>
                    <div class="rem2"><span class="txt2">99mm:</span>libs_v1,pic_data,pics_99mm</div>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                    <a href="<?php echo \ounun\page_util::page('/task/')?>" style="color: #0000FF;">返回列表</a>
                </td>
            </tr>
        </table>
    </form>
    <script type="text/JavaScript">
        var task_list  =  <?php echo json_encode($task_list,JSON_UNESCAPED_UNICODE);?>;
        // var task_list  =  <?php // echo json_encode($task_list);?>;
        function onchange_type(value,mode)
        {
            // alert(value);
            if(mode && document.getElementById('task_type').value != value)
            {
                document.getElementById('task_type').value = value;
            }
            if(value == 1)
            {
                document.getElementById('data_type_0').style.display = '';
                document.getElementById('data_type_1').style.display = 'none';
            }else
            {
                document.getElementById('data_type_0').style.display = 'none';
                document.getElementById('data_type_1').style.display = '';
            }
        }
        function onchange_args(o)
        {
            // var t = document.getElementById('cpform').task_name.value;
            // alert(o.value);
            // document.getElementById('cpform').task_name.value  = t + '['+o.value+']';
            var o = task_list[o.value];
            if(o)
            {
                document.getElementById('cpform').task_name.value  = o['name'];
                document.getElementById('cpform').crontab.value    = o['crontab'];
                document.getElementById('cpform').interval0.value  = o['interval'];
                document.getElementById('cpform').interval1.value  = o['interval'];
            }

        }
        function check_form(obj)
        {
            if(!obj.task_name.value)
            {
                alert("提示：请输入'任务名称'");
                obj.task_name.focus();
                return false;
            }
            else if(!obj.time_begin.value)
            {
                alert("提示：请输入'开启时间(0点0分开始)'");
                obj.time_begin.focus();
                return false;
            }
            else if(!obj.time_end.value)
            {
                alert("提示：请输入'结束时间(23点59分结束)'");
                obj.time_end.focus();
                return false;
            }
            return true;
        }
        <?php if($rs['task_id']){?>
            onchange_type(<?php echo (int)$rs['type']?>,true);
            document.getElementById('args_mode').value =  <?php echo (int)$rs['args']['mode']?> ;
            document.getElementById('args_data').value =  <?php echo json_encode($rs['args']['data'],JSON_UNESCAPED_UNICODE)?>;
        <?php }else{?>
            onchange_type(0,true);
            document.getElementById('args_mode').value = <?php echo (int)$rs['args']['mode']?>;
            document.getElementById('args_data').value = <?php echo json_encode($rs['args']['data'],JSON_UNESCAPED_UNICODE)?>;
        <?php }?>
        // alert(document.getElementById('args_data').value );

    </script>
</div>
<?php require v::tpl_fixed('_foot.html.php')?>
