<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
            <form method="get" style="float: right;" onsubmit="return this.q.value?true:false ">
                <input class="txt" name="q" value="<?php echo $_GET['q']?>"> <input type="submit" class="btn" value="搜索">
            </form>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>网站名称</th>
                <th>主域名pc</th>
                <th>分类</th>
                <th>站点标识</th>
                <th>站群</th>
                <th>类型</th>
                <th>cdn</th>
                <th>服务器</th>
                <th>备案</th>
                <th>操作 <a href="site_add.html" style="color: #0000FF;">添加</a></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                $v['beian'] = $v['beian']?$v['beian']:'-'
                ?>
                <tr style="height:15px;">
                    <td style="color: <?php echo $v['state']?'#0000FF':'#cccccc'?>;font-weight: bold;"><?php echo $v['name']; ?></td>
                    <td<?php echo ($v['db'] || $v['type']==adm_purv::app_type_admin)?'':' style="color: #CCCCCC;"'?>><?php echo $v['main_domain']; ?></td>
                    <td><?php echo $v['site_cls']; ?></td>
                    <td><?php echo $v['site_tag']; ?></td>
                    <td><a href="site_list.html?zqun_tag=<?php echo urlencode($v['zqun_tag']); ?>"><?php echo $v['zqun_tag']; ?></a></td>
                    <td><?php echo $v['type']; ?></td>

                    <td><a href="site_list.html?cdn=<?php echo urlencode($v['cdn']); ?>"><?php echo $v['cdn']; ?></a></td>
                    <td><a href="site_list.html?host=<?php echo urlencode($v['host']); ?>"><?php echo $v['host']; ?></a></td>
                    <td><a href="site_list.html?beian=<?php echo urlencode($v['beian']); ?>"><?php echo $v['beian']; ?></a></td>
                    <td>
                        <a href="?site_tag=<?php echo $v['site_tag']?>&act=del" onclick="return msg_yn();">删除</a>
                        <a href="site_add.html?site_tag=<?php echo $v['site_tag']?>">修改</a>
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