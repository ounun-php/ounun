<?php require v::tpl_fixed('_head.html.php') ?>
<div class="container" id="cpcontainer">
    <div class="floattopempty"></div>
    <table class="tb tb2 " id="tips">
        <tr>
            <th class="partition">你好, <span
                        style="color: red;"><?php echo oauth()->session_get(\app\adm\model\purview::session_account); ?></span>
                权限受限或功能未开放！
            </th>
        </tr>
    </table>
</div>
<?php require v::tpl_fixed('_foot.html.php') ?>
