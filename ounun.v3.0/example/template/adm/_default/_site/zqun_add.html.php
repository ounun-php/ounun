<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>

        <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form(this);" >


            <input type="hidden" name="centent" id="centent"/>
            <table class="tb tb2 " align="left">
                <tr style="height:20px" class="hover">
                    <td class="td24" >站群标识：</td>
                    <td>
                        <?php if($rs && $rs['zqun_tag']){?>
                            <input type="hidden" name="zqun_tag" id="zqun_tag" value="<?php echo $rs['zqun_tag'];?>"/> <?php echo $rs['zqun_tag'];?>
                        <?php }else{?>
                            <input class="txt" style="width: 200px;" type="text" name="zqun_tag" id="zqun_tag" value="<?php echo $rs['zqun_tag']?>" />
                        <?php }?>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>类型：</td>
                    <td><input class="txt" style="width: 100px;" type="text" name="type" id="type" value="<?php echo $rs['type']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>站群名称：</td>
                    <td><input class="txt" style="width: 200px;" type="text" name="name" id="name" value="<?php echo $rs['name']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>所在目录：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="dir" id="dir" value="<?php echo $rs['dir']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>SVN地址：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="svn" id="svn" value="<?php echo $rs['svn']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>扩展数据：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="exts" id="exts" value="<?php echo $rs['exts']?>" /></td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="15">
                        <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                        <a href="<?php echo \ounun\page_util::page('zqun_list.html')?>" style="color: #0000FF;">返回列表</a>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            function check_form(of)
            {
                if(of.zqun_tag.value=='')
                {
                    alert('"站群标识"不能为空!');
                    of.zqun_tag.focus();
                    return false;
                }
                if(of.type.value=='')
                {
                    alert('"类型"不能为空!');
                    of.type.focus();
                    return false;
                }
                if(of.name.value=='')
                {
                    alert('"站群名称"不能为空!');
                    of.name.focus();
                    return false;
                }
                if(of.dir.value=='')
                {
                    alert('"所在目录"不能为空!');
                    of.dir.focus();
                    return false;
                }
                if(of.svn.value=='')
                {
                    alert('"SVN地址"不能为空!');
                    of.svn.focus();
                    return false;
                }
                return true;
            }
        </script>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>