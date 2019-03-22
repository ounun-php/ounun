<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>日期</th>
                <th>目标</th>
                <th>数量</th>
                <th></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td><?php echo $v['Ymd']; ?></td>
                    <td><?php echo \api_sdk\com_baidu::type[$v['target_id']]; ?></td>
                    <td><?php echo $v['cc']; ?></td>
                    <td></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>