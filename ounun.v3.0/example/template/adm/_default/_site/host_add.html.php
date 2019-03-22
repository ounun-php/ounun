<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>

        <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form(this);" >


            <input type="hidden" name="centent" id="centent"/>
            <table class="tb tb2 " align="left">
                <tr style="height:20px" class="hover">
                    <td class="td24" >Host标识：</td>
                    <td>
                        <?php if($rs && $rs['host_tag']){?>
                            <input type="hidden" name="host_tag" id="host_tag" value="<?php echo $rs['host_tag'];?>"/> <?php echo $rs['host_tag'];?>
                        <?php }else{?>
                            <input class="txt" style="width: 200px;" type="text" name="host_tag" id="host_tag" value="<?php echo $rs['host_tag']?>" />
                        <?php }?>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>服务器类型：</td>
                    <td>
                        <select name="host_type">
                            <?php foreach (status::host_type as $k=>$v){?>
                                <option value="<?php echo $k?>">[<?php echo $k?>] - <?php echo $v?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>机房：</td>
                    <td><input class="txt" style="width: 200px;" type="text" name="room" id="room" value="<?php echo $rs['room']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>名称：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="name" id="name" value="<?php echo $rs['name']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>内网IP：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="private_ip" id="private_ip" value="<?php echo $rs['private_ip']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>公网IP：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="public_ip" id="public_ip" value="<?php echo $rs['public_ip']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>扩展数据：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="exts" id="exts" value="<?php echo $rs['exts']?>" /></td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="15">
                        <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                        <a href="<?php echo \ounun\page_util::page('host_list.html')?>" style="color: #0000FF;">返回列表</a>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            function check_form(of)
            {
                if(of.host_tag.value=='')
                {
                    alert('"Host标识"不能为空!');
                    of.host_tag.focus();
                    return false;
                }
                if(of.room.value=='')
                {
                    alert('"机房"不能为空!');
                    of.room.focus();
                    return false;
                }
                if(of.name.value=='')
                {
                    alert('"名称"不能为空!');
                    of.name.focus();
                    return false;
                }
                if(of.private_ip.value=='')
                {
                    alert('"公网IP"不能为空!');
                    of.private_ip.focus();
                    return false;
                }
                if(of.public_ip.value=='')
                {
                    alert('"公网IP"不能为空!');
                    of.public_ip.focus();
                    return false;
                }
                return true;
            }
            <?php if($rs && $rs['host_tag']){?>
                document.getElementById('cpform').host_type.value = <?php echo json_encode($rs['host_type'])?>;
            <?php }?>
        </script>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>