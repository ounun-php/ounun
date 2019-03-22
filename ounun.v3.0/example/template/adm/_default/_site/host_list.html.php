<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>Host标识<a>Bash</a></th>
                <th>站数</th>
                <th>服务器类型</th>
                <th>机房</th>
                <th>名称</th>
                <th>内网IP</th>
                <th>公网IP</th>
                <th>操作 <a href="host_add.html" style="color: #0000FF;">添加</a></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td>
                        <a href="site_list.html?host=<?php echo urlencode($v['host_tag']); ?>" style="color: #DD0000;"><?php echo $v['host_tag']; ?></a>
                        <a href="site_bash.html?host=<?php echo urlencode($v['host_tag']); ?>">Bash</a>
                    </td>
                    <td><?php echo (int)$site_cc[$v['host_tag']]['cc']; ?></td>
                    <td><?php echo status::host_type[$v['host_type']]; ?></td>
                    <td><?php echo $v['room']; ?></td>
                    <td><?php echo $v['name']; ?></td>
                    <td style="color:#CCCCCC;"><?php echo $v['private_ip']; ?></td>
                    <td><?php echo $v['public_ip']; ?></td>
                    <td>
                        <a href="?host_tag=<?php echo $v['host_tag']?>&act=del" onclick="return msg_yn();">删除</a>
                        <a href="host_add.html?host_tag=<?php echo $v['host_tag']?>">修改</a>
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