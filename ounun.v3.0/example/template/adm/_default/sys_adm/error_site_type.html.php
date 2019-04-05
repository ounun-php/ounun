<?php require v::tpl_fixed('_head.html.php') ?>
<div class="container" id="cpcontainer">
    <div class="floattopempty"></div>
    <table class="tb tb2 " id="tips">
        <tr>
            <th class="partition">站点 <strong><?php echo $this->_site_type ?></strong> 类型有误, 只兼容 <span
                        style="color: red;"><?php echo $_GET['site_type_only'] ?></span> 类型！
            </th>
        </tr>
    </table>
</div>
<?php require v::tpl_fixed('_foot.html.php') ?>
