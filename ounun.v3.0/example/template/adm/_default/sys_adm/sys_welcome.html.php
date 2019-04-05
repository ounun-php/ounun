<?php require v::tpl_fixed('_head.html.php') ?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th style="width: 30px;"></th>
                <th>登录成功! 你好,
                    <?php echo oauth()->purview->purview_group[oauth()->session_get(\app\adm\model\purview::session_type)]; ?>
                    <em style="color: #DD0000;font-weight: bold;"><?php echo oauth()->session_get(\app\adm\model\purview::session_account); ?></em>
                </th>
            </tr>
        </table>
    </div>
<?php require v::tpl_fixed('_foot.html.php') ?>