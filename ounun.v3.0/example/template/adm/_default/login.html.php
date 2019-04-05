<?php use \app\adm\controller\adm; ?>
<!DOCTYPE html>
<html>
<head>
    <title>登录《{$site_name}》管理中心</title>
    <meta content="{$powered_studio_name} Inc." name="Copyright"/>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type"/>
    <link type="image/x-icon" rel="icon" href="{$static}favicon.ico">
    <link type="text/css" rel="stylesheet" href="{$static_g}adm/admincp.css"/>
    <script type="text/javascript" src="{$static_g}code/md5.js"></script>
</head>
<body>
<script type="text/javascript">
    if (self.parent.frames.length != 0) {
        self.parent.location = document.location;
    }
</script>
<table class="logintb">
    <tr>
        <td class="login" style="background:url({$static}{$site_logo_dir}login_title.png) 50% 5% no-repeat;">
            <h1>《{$site_name}》管理中心</h1>
            <p>
                <a href="{$powered_corp_url}" target="_blank">《{$site_name}》</a>
                由 <a href="{$powered_studio_url}" target="_blank">{$powered_studio_name}</a> 提供维护开发及区块链技术支持的云数据系统
                <br/><br/><br/>
            </p>
        </td>
        <td>
            <form method="post" autocomplete="off" id="loginform" onsubmit="return admin_submit();">
                <p class="logintitle">用户名: </p>
                <p class="loginform">
                    <input name="admin_username" tabindex="1" type="text" class="txt" placeholder="用户名"/>
                </p>
                <p class="logintitle">密　码:</p>
                <p class="loginform">
                    <input name="admin_password" tabindex="2" type="password" class="txt" placeholder="密码"/>
                </p>
                <p class="logintitle">动态码:</p>
                <p class="loginform">
                    <input name="admin_google" tabindex="3" type="text" class="txt" placeholder="动态验证(虚拟MFA),可不填"
                           autocomplete="off"/>
                </p>
                <p class="logintitle">提　示:</p>
                <p class="loginform"><?php echo adm::$auth->purview->max_ip; ?>次登录失败,封帐号或IP</p>
                <p class="loginnofloat">
                    <input value="登录后台" tabindex="4" type="submit" class="btn"/>
                </p>
            </form>
        </td>
    </tr>
</table>
<script type="text/JavaScript">
    var loginform = document.getElementById('loginform');
    loginform.admin_username.focus();

    function admin_submit() {
        if (!loginform.admin_username.value) {
            alert("提示：请输入用户名");
            loginform.admin_username.focus();
            return false;
        }
        else if (!loginform.admin_password.value) {
            alert("提示：请输入密码");
            loginform.admin_password.focus();
            return false;
        }
        var md5 = hex_md5(loginform.admin_password.value);
        loginform.admin_password.value = md5;
        return true;
    }
</script>
<table class="logintb">
    <tr>
        <td colspan="2" class="footer">
            <div class="copyright">
                <p>Powered by <a href="{$powered_studio_url}" target="_blank">{$powered_studio_name}</a></p>
                <p>&copy; 2010-<?php echo date('Y') ?> <a href="{$powered_corp_url}" target="_blank">{$powered_corp_name}</a>
                    Inc.</p>
            </div>
        </td>
    </tr>
</table>
</body>
</html>