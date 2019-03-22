<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>

        <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form(this);" >


            <?php if($rs && $rs['id']){?>
                <input type="hidden" name="id" id="id" value="<?php echo $rs['id'];?>"/>
            <?php }?>
            <table class="tb tb2 " align="left">

                <tr style="height:20px" class="hover">
                    <td>名称：</td>
                    <td><input class="txt" style="width: 200px;" type="text" name="name" id="name" value="<?php echo $rs['name']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>模块ID：</td>
                    <td><input class="txt" style="width: 200px;" type="text" name="mod_id" id="mod_id" value="<?php echo $rs['mod_id']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>key：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="key" id="key" value="<?php echo $rs['key']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>value：</td>
                    <td>
                        <textarea class="txt" style="width: 450px;" cols="50" rows="5" name="value" id="value"><?php echo $rs['value']?></textarea>
                        <br />
                        <span style="color: #cd0a0a;">JSON数据</span>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="15">
                        <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                        <a href="<?php echo \ounun\page_util::page('config_list.html')?>" style="color: #0000FF;">返回列表</a>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            function check_form(of)
            {
                if(of.mod_id.value=='')
                {
                    alert('"模块ID"不能为空!');
                    of.mod_id.focus();
                    return false;
                }
                if(of.name.value=='')
                {
                    alert('"名称"不能为空!');
                    of.name.focus();
                    return false;
                }
                if(of.key.value=='')
                {
                    alert('"key"不能为空!');
                    of.key.focus();
                    return false;
                }
                if(of.value.value=='')
                {
                    alert('"value"不能为空!');
                    of.value.focus();
                    return false;
                }
                return true;
            }
            <?php if($rs && $rs['site_tag']){?>
            document.getElementById('cpform').site_tag.value = <?php echo json_encode($rs['site_tag'])?>;
            <?php }?>
        </script>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>