<?php require v::tpl_fixed('_head.html.php') ?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3>密码更新</h3>
        </div>
        <form name="cpform" id="cpform" method="post" autocomplete="off" onsubmit="return check_form();">
            <table class="tb tb2 ">
                <tr class="header">
                    <th width="20%"></th>
                    <th>密码更新</th>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">旧密码：</td>
                    <td class="td24"><input class="txt" type="password" name="oldpwd" value=""/></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">新密码：</td>
                    <td class="td24"><input class="txt" type="password" name="newpwd" value=""/></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td class="td24">确认新密码：</td>
                    <td class="td24"><input class="txt" type="password" name="newconfpwd" value=""/></td>
                </tr>
                <tr style="height:20px" class="hover">
                    <td class="td24">动态验证：</td>
                    <td class="td24"><input class="txt" type="text" name="google" value=""/></td>
                </tr>
                <tr>
                    <td class="td25"></td>
                    <td colspan="15">
                        <div class="fixsel">
                            <input type="submit" class="btn" id="submit" name="submit" title="按 Enter 键可随时提交你的修改"
                                   value="修改"/>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            var _cpform_obj = document.getElementById('cpform');

            function check_form() {
                var cpform_obj = _cpform_obj;
                if (!cpform_obj.oldpwd.value) {
                    alert("提示：请输入旧密码");
                    cpform_obj.oldpwd.focus();
                    return false;
                }
                else if (!cpform_obj.newpwd.value) {
                    alert("提示：请输入新密码");
                    cpform_obj.newpwd.focus();
                    return false;
                }
                else if (!cpform_obj.newconfpwd.value) {
                    alert("提示：请输入确认新密码");
                    cpform_obj.newconfpwd.focus();
                    return false;
                }
                else if (cpform_obj.newpwd.value != cpform_obj.newconfpwd.value) {
                    alert("提示：新密码与确认新密码不一致");
                    cpform_obj.newpwd.focus();
                    return false;
                }
                else if (cpform_obj.newpwd.value.length < 6) {
                    alert("提示：新密码长度不能少于6位");
                    cpform_obj.newpwd.focus();
                    return false;
                } else if (!cpform_obj.google.value || cpform_obj.google.value.length != 6) {
                    alert("提示：请输入正确6位数谷歌(洋葱)验证");
                    cpform_obj.google.focus();
                    return false;
                }
                return true;
            }

            _cpform_obj.oldpwd.focus();
        </script>
    </div>
<?php require v::tpl_fixed('_foot.html.php') ?>