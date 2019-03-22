<?php require v::require_fixed_comp('_head.html.php')?>
<div class="container" id="cpcontainer">
    <div class="itemtitle">
        <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
    </div>
    <table class="tb tb2 ">
        <tr class="header">
            <th>#id</th>
            <th>类型</th>
            <th>排序</th>
            <th class="td24">网站名称</th>
            <th>开始时间</th>
            <th>结束时间</th>
            <th>权重传递</th>
            <th>审核</th>
            <th class="td26">目标URL</th>
            <th>操作</th>
        </tr>
        <?php foreach ($datas as $v){?>
            <tr style="height:20px" class="hover">
                <td><?php echo $v['id'];?></td>
                <td><?php echo $v['type_id'];?></td>
                <td><?php echo $v['sort'];?></td>
                <td><?php echo $v['site_name'];?></td>
                <td><?php echo date('Y-m-d',$v['time_add']);?></td>
                <td><?php echo date('Y-m-d',$v['time_end']);?></td>
                <td><?php echo $v['is_nofollow'];?></td>
                <td><?php echo $v['is_check'];?></td>
                <td><a href="<?php echo $v['url'];?>" target="_blank"><?php echo $v['url'];?></a></td>
                <td>
                    <a href="?act=del&id=<?php echo $v['id']?>" onclick="return msg_yn();">删除</a>
                    <a href="?id=<?php echo $v['id']?>">修改</a>
                </td>
            </tr>
        <?php }?>
    </table>

    <br /><br />
    <div class="itemtitle">
        <h3 style="padding-left: 5px;"><?php echo $rs['id']?"修改":"添加"?> - {$page_title_sub}</h3>
    </div>
    <form name="cpform" method="post" autocomplete="off" onsubmit="return check_form(this);" id="cpform" >
        <input type="hidden" name="id"  value="<?php echo (int)$rs['id'];?>" />
        <table class="tb tb2 ">
            <?php if($rs['id']){?>
                <tr style="height:20px" class="hover">
                    <td class="td24">#ID</td>
                    <td class="td26"><?php echo $rs['id']?></td>
                </tr>
            <?php }?>

            <tr style="height:20px" class="hover">
                <td class="td24">网站名称：</td>
                <td class="td26"><input class="txt" type="text" name="site_name" value="<?php echo $rs['site_name']?>" style="width: 200px;" /></td>
            </tr>
            <tr style="height:20px" class="hover">
                <td>目标URL	：</td>
                <td><input class="txt" type="text" name="url" value="<?php echo $rs['url']?$rs['url']:'https://'?>" style="width: 300px;" /> http(s)://...</td>
            </tr>
            <tr style="height:20px" class="hover">
                <td>类型/排序：</td>
                <td>
                    类型：<input class="txt" type="text" name="type_id" readonly="1" value="<?php echo $rs['type_id']?$rs['type_id']:1?>" style="width: 50px;" />
                    排序：<input class="txt" type="text" name="sort" value="<?php echo $rs['sort']?$rs['sort']:1?>" />
                </td>
            </tr>
            <tr style="height:20px" class="hover">
                <td>开始时间/结束时间：</td>
                <td>
                    开始时间：<input class="txt" type="text" name="time_add" onclick="SelectDate(this);" value="<?php echo date("Y-m-d",($rs['time_add']?$rs['time_add']:time()))?>" />
                    -
                    结束时间：<input class="txt" type="text" name="time_end" onclick="SelectDate(this);" value="<?php echo date("Y-m-d",($rs['time_end']?$rs['time_end']:(time()+3600*3650*24)))?>" />
                </td>
            </tr>
            <tr style="height:20px" class="hover">
                <td>权重传递	：</td>
                <td>
                    <select name="is_nofollow">
                        <option value="1">权重传递</option>
                        <option value="0">权重不传递</option>
                    </select>
                </td>
            </tr>
            <tr style="height:20px" class="hover">
                <td>是否审核：</td>
                <td>
                    <select name="is_check">
                        <option value="0">不通过</option>
                        <option value="1">通过</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                </td>
            </tr>
        </table>
    </form>
    <script type="text/JavaScript">
        function check_form(obj)
        {
            if(!obj.site_name.value)
            {
                alert("提示：请输入'网站名称'");
                obj.site_name.focus();
                return false;
            }
            if(!obj.url.value || 'https://' == obj.url.value )
            {
                alert("提示：请输入'目标URL'");
                obj.url.focus();
                return false;
            }
            if(!obj.sort.value)
            {
                alert("提示：请输入'排序'");
                obj.sort.focus();
                return false;
            }

            return true;
        }
        <?php if($rs['id']){?>
        document.getElementById('cpform').is_nofollow.value = <?php echo (int)$rs['is_nofollow']?>;
        document.getElementById('cpform').is_check.value = <?php echo (int)$rs['is_check']?>;
        <?php }?>
    </script>
</div>
<?php require v::require_fixed_comp('_foot.html.php')?>
