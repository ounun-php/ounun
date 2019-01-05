<?php require v::tpl_fixed('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <table class="tb tb2 ">
            <tr class="header">
                <th style="width: 30px;"></th>
                <th>登录成功!  你好,
                    <?php echo \controller\adm::$auth->purview->purview_group[\controller\adm::$auth->session_get(\model\purview::s_type)];?>
                    <em style="color: #DD0000;font-weight: bold;"><?php echo \controller\adm::$auth->session_get(\model\purview::s_account);?></em></th>
            </tr>
        </table>
    </div>
<?php require v::tpl_fixed('_foot.html.php')?>