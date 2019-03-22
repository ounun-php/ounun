<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>#id</th>
                <th>loc</th>
                <th>熊</th>
                <th>模块</th>
                <th>添加</th>
                <th>频率</th>
                <th>last</th>
                <th>权重</th>
                <th>操作</th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                $v['beian'] = $v['beian']?$v['beian']:'-'
                ?>
                <tr style="height:15px;">
                    <td><?php echo $v['url_id']; ?></td>
                    <td>
                        <a href="https://<?php echo $GLOBALS['_site']['dns']['mip']['sub_domain'].$v['loc']?>" target="_blank">mip</a>
                        <a href="https://<?php echo $GLOBALS['_site']['dns']['wap']['sub_domain'].$v['loc']?>" target="_blank">wap</a>
                        <a href="https://<?php echo $GLOBALS['_site']['dns']['pc']['sub_domain'].$v['loc']?>" target="_blank"><?php echo $v['loc']; ?></a>
                    </td>
                    <td><?php echo $v['xzh']; ?></td>
                    <td><a href="?mod=<?php echo $v['mod']; ?>"><?php echo $v['mod'];?></a></td>
                    <td><?php echo date('Y-m-d H:i:s',$v['time_add']); ?></td>
                    <td><?php echo $v['changefreq']; ?></td>

                    <td><?php echo date('Y-m-d',$v['lastmod']); ?></td>
                    <td><?php echo $v['weight']; ?></td>
                    <td>
                        <a href="?url_id=<?php echo $v['url_id']?>&act=del" onclick="return msg_yn();">删除</a>
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