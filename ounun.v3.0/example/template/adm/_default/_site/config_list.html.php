<?php require v::require_fixed_comp('_head.html.php')?>
    <style type="text/css">
        .txt2{width: 350px;border: 0px;}
    </style>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>#ID</th>
                <th>名称</th>
                <th>模块ID</th>
                <th>key</th>
                <th>value</th>
                <th>操作 <a href="config_add.html" style="color: #0000FF;">添加</a></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td><?php echo $v['id']; ?></td>
                    <td><?php echo $v['name']; ?></td>
                    <td><?php echo $v['mod_id']; ?></td>
                    <td><?php echo $v['key']; ?></td>
                    <td><textarea class="txt2" cols="50" rows="3"><?php echo $v['value'];?></textarea></td>
                    <td>
                        <a href="?id=<?php echo $v['id']?>&act=del" onclick="return msg_yn();">删除</a>
                        <a href="config_add.html?id=<?php echo $v['id']?>">修改</a>
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