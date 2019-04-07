<?php require v::tpl_fixed('_head.html.php') ?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3><?php echo $page_title_sub; ?></h3>
        </div>
        <?php $cs = $ext['google']['secret']; ?>
        <?php if ($ext && $ext['google'] && $ext['google']['is']) { ?>
            <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form();">
                <input type="hidden" name="act" value="del"/>
                <table class="tb tb2 ">
                    <tr class="header">
                        <th></th>
                        <th>关闭 谷歌(洋葱)动态验证</th>
                    </tr>
                    <tr style="height:20px" class="hover">
                        <td class="td24">登录密码：</td>
                        <td><input class="txt" type="password" name="password"/></td>
                    </tr>
                    <tr style="height:20px" class="hover">
                        <td class="td24">谷歌验证：</td>
                        <td><input class="txt" type="text" name="google"/></td>
                    </tr>
                    <tr>
                        <td class="td25"></td>
                        <td colspan="15">
                            <div class="fixsel">
                                <input type="submit" class="btn" id="submit" name="submit" title="按 Enter 键可随时提交你的修改"
                                       value="关闭Google身份验证"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>

        <?php }else{ ?>
            <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form();">
                <input type="hidden" name="act" value="set"/>
                <table class="tb tb2 ">
                    <tr class="header">
                        <th></th>
                        <th>设定 谷歌 动态验证</th>
                    </tr>
                    <tr style="height:20px" class="hover">
                        <td class="td24">帐户标识：</td>
                        <td><input class="txt" type="text" value="{$site_name}" readonly="readonly"/></td>
                    </tr>

                    <tr style="height:20px" class="hover" id="tr_down">
                        <td class="td24">第一步:下载<br/><br/>
                            扫码下载阿里云APP<br/>
                            <span style="color: #cd0a0a;">比Google身份验证器好用</span></td>
                        <td>
                            <br/>
                            <a href="https://hd.m.aliyun.com/act/download.html" target="_blank"
                               style="font-size: 16px;">
                                安装:扫码下载阿里云APP手机端 "点击下载" 或 "手机扫描下面二维码"
                            </a> <br/><br/>
                            <a href="https://hd.m.aliyun.com/act/download.html" target="_blank">
                                <img src="{$static_g}adm/aliyun-app300.png" border="0">
                            </a>
                            扫码下载阿里云APP
                            <br/>
                            <br/>
                            <br/>
                            <a href="#" onclick="step_2();">
                                <span style="color: #DD0000;font-size: 18px;">(已安装可跳过)</span>我已安装好，进行下一步
                            </a>
                        </td>
                    </tr>

                    <tr style="height:20px;display:none;" class="hover" id="tr_2">
                        <td class="td24">第二步:扫码或手输</td>
                        <td>
                            <br/>
                            <a href="#" onclick="step_2_good();"><span style="font-size: 16px;">扫码</span>(正常人都选这个)</a>
                            <br/>
                            <a href="#" onclick="step_2_bad();"><span
                                        style="font-size: 16px;">手输</span>(手机摄像头坏了，用手输入)</a><br/><br/>
                        </td>
                    </tr>
                    <tr style="height:20px;display: none;" class="hover" id="tr_2_good">
                        <td class="td24">(扫码)身份密钥：</td>
                        <td>
                            <br/>
                            <a href="#" style="font-size: 16px;">
                                点开"阿里云"手机App -> "扫一扫"功能
                            </a><br/>
                            <img src="{$static_g}adm/aliyun-app400.png" height="388"/> <img
                                    src="{$static_g}adm/aliyun-app500.png" height="388"/>
                            <br/><br/>
                            <?php
                            $project_no = Const_Code;
                            $adm_account_id = oauth()->session_get(\app\adm\model\purview::session_id);
                            $site_name = \ounun\config::$tpl_replace_str['{$site_name}'];
                            $adm_account = oauth()->session_get(\app\adm\model\purview::session_account);
                            ?>
                            <img height="388"
                                 src="<?php echo (new \plugins\google\auth_code())->qrcode_google_url_get("{$site_name}#{$adm_account}#{$project_no}#" . date("Y-m-d") . "#{$adm_account_id}", $cs, $adm_account . "《{$site_name}》") ?>"><br/>
                            <br/>
                            <br/>
                            <br/>
                            <a href="#" onclick="step_3();"> <span style="color: #DD0000;font-size: 18px;">(已前扫码/手输过，请先删除本项)</span>我扫码好，进行下一步</a>
                        </td>
                    </tr>

                    <tr style="height:20px;display: none;" class="hover" id="tr_2_bad">
                        <td class="td24">(手输)身份密钥：</td>
                        <td>
                            <br/>
                            <a href="#" style="font-size: 16px;">
                                点开"阿里云"手机App,输入已下内容 </a><br/>
                            <img src="{$static_g}adm/aliyun-app400.png" height="388"/> <img
                                    src="{$static_g}adm/aliyun-app600.png" height="388"/>
                            <br/><br/>
                            <br/>
                            用户名:<input class="txt" type="text" style="width: 300px;"
                                       value="{$site_name} - <?php echo $adm_account; ?>" readonly="readonly"/><br/>
                            授权码:<input class="txt" type="text" style="width: 300px;" value="<?php echo $cs; ?>"
                                       readonly="readonly"/>
                            <br/>
                            <br/>
                            <br/>
                            <a href="#" onclick="step_3();"> <span style="color: #DD0000;font-size: 18px;">(已前扫码/手输过，请先删除本项)</span>我扫码好，进行下一步</a>
                        </td>
                    </tr>


                    <tr style="display: none;" id="tr_3_code">
                        <td class="td25">第三步:<br/>洋葱动态验证</td>
                        <td colspan="15">
                            <br/>
                            <br/>
                            <br/>
                            <div class="fixsel">
                                登录密码: <input class="txt" type="password" name="password"/>(后台帐号登录密码)
                            </div>
                            <div class="fixsel">
                                动态验证: <input class="txt" type="text" name="google"/>(手机上显示的6位数字,新添加的在最后一项)
                            </div>
                            <br/>
                            <br/>
                            <a href="#" onclick="step_2();"> 返回上一步 </a></td>
                    </tr>
                    <tr style="display: none;" id="tr_3_btn">
                        <td class="td25"></td>
                        <td colspan="15">
                            <div class="fixsel">
                                <input type="submit" class="btn" id="submit" title="按 Enter 键可随时提交你的修改"
                                       value="保存 并 开启Google身份验证"/>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
            <script type="text/javascript">
                function step_init() {
                    document.getElementById('tr_down').style.display = '';

                    document.getElementById('tr_2').style.display = 'none';
                    document.getElementById('tr_2_good').style.display = 'none';
                    document.getElementById('tr_2_bad').style.display = 'none';

                    document.getElementById('tr_3_code').style.display = 'none';
                    document.getElementById('tr_3_btn').style.display = 'none';
                }


                function step_2() {
                    document.getElementById('tr_down').style.display = 'none';

                    document.getElementById('tr_2').style.display = '';
                    document.getElementById('tr_2_good').style.display = '';
                    document.getElementById('tr_2_bad').style.display = 'none';

                    document.getElementById('tr_3_code').style.display = 'none';
                    document.getElementById('tr_3_btn').style.display = 'none';
                }

                function step_2_good() {
                    document.getElementById('tr_2_good').style.display = '';
                    document.getElementById('tr_2_bad').style.display = 'none';
                }

                function step_2_bad() {
                    document.getElementById('tr_2_good').style.display = 'none';
                    document.getElementById('tr_2_bad').style.display = '';
                }

                function step_3() {
                    document.getElementById('tr_down').style.display = 'none';

                    document.getElementById('tr_2').style.display = 'none';
                    document.getElementById('tr_2_good').style.display = 'none';
                    document.getElementById('tr_2_bad').style.display = 'none';

                    document.getElementById('tr_3_code').style.display = '';
                    document.getElementById('tr_3_btn').style.display = '';
                }

                step_init();
            </script>
        <?php } ?>
        <script type="text/javascript">
            function check_form() {
                var cpform_obj = document.getElementById('cpform');
                if (!cpform_obj.password.value) {
                    alert("提示：请输入新密码");
                    cpform_obj.password.focus();
                    return false;
                } else if (!cpform_obj.google.value || cpform_obj.google.value.length != 6) {
                    alert("提示：请输入正确6位数谷歌(洋葱)验证");
                    cpform_obj.google.focus();
                    return false;
                }
                return true;
            }
        </script>
    </div>
<?php require v::tpl_fixed('_foot.html.php') ?>