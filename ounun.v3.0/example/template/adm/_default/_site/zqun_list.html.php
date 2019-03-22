<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>站群标识</th>
                <th>站数</th>
                <th>类型</th>
                <th>站群名称</th>
                <th>所在目录</th>
                <th>SVN地址</th>
                <th>扩展 <a href="zqun_add.html" style="color: #0000FF;">添加</a></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td><a href="site_list.html?zqun_tag=<?php echo urlencode($v['zqun_tag']); ?>"><?php echo $v['zqun_tag']; ?></a></td>
                    <td><?php echo (int)$site_cc[$v['zqun_tag']]['cc']; ?></td>
                    <td><?php echo $v['type']; ?></td>
                    <td><?php echo $v['name']; ?></td>
                    <td><?php echo $v['dir']; ?></td>
                    <td><?php echo $v['svn']; ?></td>
                    <td>
                        <a href="?zqun_tag=<?php echo $v['zqun_tag']?>&act=del" onclick="return msg_yn();">删除</a>
                        <a href="zqun_add.html?zqun_tag=<?php echo $v['zqun_tag']?>">修改</a>
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
<?php require v::require_fixed_comp('_foot.html.php')?>