<?php require v::tpl_fixed('_head.html.php') ?>

    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>
        <form name="cpform" method="post" autocomplete="off" onsubmit="return check_form();" id="cpform">
            <input type="hidden" name="adm_cid" value="<?php echo $platform_cid; ?>"/>
            <table class="tb tb2 ">
                <tr class="header">
                    <th></th>
                    <th>添加管理人员</th>
                    <th></th>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">管理类型：</td>
                    <td class="td26">
                        <select name="adm_type" onchange="type_onchange(this);">
                            <option value="0">请选择..</option>
                            <?php foreach ($purview_group as $k => $v) { ?>
                                <option value="<?php echo $k ?>"><?php echo $v ?></option>
                            <?php } ?>
                        </select></td>
                    <td></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">用户：</td>
                    <td class="td24"><input class="txt" type="text" name="adm_account" value=""/></td>
                    <td></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">密码：</td>
                    <td class="td24"><input class="txt" type="password" name="password" value=""/></td>
                    <td></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">确认密码：</td>
                    <td class="td24"><input class="txt" type="password" name="password2" value=""/></td>
                    <td></td>
                </tr>
                <tr id="popedom" style="height:20px; display:none;">
                    <td class="td24">权限：</td>
                    <td colspan='2'></td>
                    <td></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">手机号码：</td>
                    <td class="td24"><input class="txt" type="text" name="adm_tel" value=""/></td>
                    <td></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">备注：</td>
                    <td class="td24"><textarea class="txt" name="adm_note"></textarea></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="td25"></td>
                    <td colspan="15">
                        <div class="fixsel">
                            <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交"/>
                        </div>
                    </td>
                    <td></td>
                </tr>
            </table>
        </form>
        <script type="text/JavaScript">
            var purview_line = <?php echo oauth()->purview->purview_line;?>;
            var purview_show = <?php echo json_encode($purview_show);?>;

            function type_onchange(o) {
                var type = o.value;
                var popedom = document.getElementById('popedom');
                var popedom_html = purview_show[type];
                if (type <= 10) {
                    popedom_html = '所有权限';
                }
                popedom.style.display = '';
                popedom.children[1].innerHTML = popedom_html;
            }

            function check_form() {
                var cpform_obj = document.getElementById('cpform');
                if (!cpform_obj.adm_account.value) {
                    alert("提示：请输入用户名");
                    cpform_obj.adm_account.focus();
                    return false;
                }
                else if (!cpform_obj.adm_type.value || '0' == cpform_obj.adm_type.value) {
                    alert("提示：请选择管理类型");
                    cpform_obj.adm_type.focus();
                    return false;
                }
                else if (!cpform_obj.password.value) {
                    alert("提示：请输入密码");
                    cpform_obj.password.focus();
                    return false;
                }
                else if (!cpform_obj.password2.value) {
                    alert("提示：请输入确认密码");
                    cpform_obj.password2.focus();
                    return false;
                }
                else if (cpform_obj.password.value != cpform_obj.password2.value) {
                    alert("提示：密码与确认密码不一致");
                    cpform_obj.password.focus();
                    return false;
                } else if (!cpform_obj.password.value || cpform_obj.password.value.length < 6) {
                    alert("提示：请输入6位以上密码");
                    cpform_obj.google.focus();
                    return false;
                }
                return true;
            }

            document.getElementById('cpform').adm_account.focus();
        </script>
    </div>
<?php require v::tpl_fixed('_foot.html.php') ?>