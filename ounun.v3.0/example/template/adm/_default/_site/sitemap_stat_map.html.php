<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th>模块(数量)</th>
                <th></th>
            </tr>
            <?php
            foreach ($data as $v)
            {
                ?>
                <tr style="height:15px;">
                    <td>
                        <?php echo $v['mod']; ?> (<span style="color: #DD0000;"><?php echo $v['cc']; ?></span>)
                        <a href="sitemap_list.html?mod=<?php echo $v['mod']; ?>">详情</a>
                    </td>
                    <td></td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>