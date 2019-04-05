<?php require v::tpl_fixed('_head.html.php') ?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
            <ul class="tab1">
                <li<?php echo empty($_GET['status']) ? ' class="current"' : '' ?>><a
                            href="<?php echo url_build_query(url_original(), $_GET, ['status' => 0]); ?>"><span>错误</span></a>
                </li>
                <li<?php echo $_GET['status'] == '1' ? ' class="current"' : '' ?>><a
                            href="<?php echo url_build_query(url_original(), $_GET, ['status' => 1]); ?>"><span>正常</span></a>
                </li>
            </ul>
        </div>
        <form name="cpform" method="get" id="cpform">
            <input type="hidden" name="status" value="<?php echo (int)$_GET['status'] ?>">
            <table class="tb tb2 ">
                <tr style='height:20px' class='hover'>
                    <td>
                        <div style="padding:5px 0px 5px 0px;">
                            用户名: <input type="text" name="account" value="<?php echo $_GET['account']; ?>"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;模块: <input type="text" name="mod"
                                                                           value="<?php echo $_GET['mod']; ?>"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;子模块: <input type="text" name="mod_sub"
                                                                            value="<?php echo $_GET['mod_sub']; ?>"/>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;操作: <input type="text" name="act"
                                                                           value="<?php echo $_GET['act']; ?>"/>
                            <input type='submit' class='btn' value='搜索'/>
                        </div>
                    </td>
                    <td></td>
                </tr>
            </table>
        </form>
        <table class="tb tb2 ">
            <tr class="header">
                <th>账号</th>
                <th>时间</th>
                <th>模块</th>
                <th>子模块</th>
                <th>操作</th>
                <th width="60%">数据</th>
                <th>状态</th>
                <th>IP地址</th>
                <th>地址</th>
                <th>功能</th>
            </tr>
            <?php foreach ($data as $v) { ?>
                <tr style="height:15px">
                    <td><?php echo $v['account']; ?></td>
                    <td><?php echo date('Y-m-d H:i:s', $v['time']); ?></td>
                    <td><?php echo $v['mod']; ?></td>
                    <td><?php echo $v['mod_sub']; ?></td>
                    <td><?php echo $v['act']; ?></td>
                    <td><input type="text" value='<?php echo $v['exts'] ?>' style="width: 100%;border: 0px;"></td>
                    <td style="height:15px;color: <?php echo $v['status'] ? '#6e1dbf' : '#eeeeee' ?>"><?php echo \c::Logs[$v['status']]; ?></td>
                    <td><?php echo $v['ip']; ?></td>
                    <td><?php echo $v['address']; ?></td>
                    <td><a href="?act=del&id=<?php echo $v['id']; ?>">删除</a></td>
                </tr>
            <?php } ?>
            <tr bgcolor="#FFFFFF">
                <td colspan="30">
                    <?php echo $ps['note'] . ' &nbsp;&nbsp; ';
                    echo implode('&nbsp;', $ps['page']); ?>
                </td>
            </tr>
        </table>
    </div>
<?php require v::tpl_fixed('_foot.html.php') ?>