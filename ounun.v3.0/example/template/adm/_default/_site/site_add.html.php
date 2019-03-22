<?php require v::require_fixed_comp('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        </div>

        <form name="cpform" method="post" autocomplete="off" id="cpform" onsubmit="return check_form(this);" >


            <input type="hidden" name="centent" id="centent"/>
            <table class="tb tb2 " align="left">
                <tr style="height:20px" class="hover">
                    <td class="td24" >站点标识：</td>
                    <td>
                        <?php if($rs && $rs['site_tag']){?>
                            <input type="hidden" name="site_tag" id="site_tag" value="<?php echo $rs['site_tag'];?>"/> <?php echo $rs['site_tag'];?>
                        <?php }else{?>
                            <input class="txt" style="width: 200px;" type="text" name="site_tag" id="site_tag" value="<?php echo $rs['site_tag']?>" />
                        <?php }?>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td class="td24" >站群标识：</td>
                    <td>
                        <select name="zqun_tag">
                            <?php foreach ($zqun as $v){?>
                                <option value="<?php echo $v['zqun_tag']?>"><?php echo $v['type']?> - [<?php echo $v['zqun_tag']?>] - <?php echo $v['name']?></option>
                            <?php }?>
                        </select> （共<?php echo count($zqun)?>组）
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>主域名pc：</td>
                    <td><input class="txt" style="width: 100px;" type="text" name="main_domain" id="main_domain" value="<?php echo $rs['main_domain']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>网站名称：</td>
                    <td><input class="txt" style="width: 200px;" type="text" name="name" id="name" value="<?php echo $rs['name']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>网站分类：</td>
                    <td><input class="txt" style="width: 300px;" type="text" name="site_cls" id="site_cls" value="<?php echo $rs['site_cls']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>CDN服务器商：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="cdn" id="cdn" value="<?php echo $rs['cdn']?>" /></td>
                </tr>


                <tr style="height:20px" class="hover">
                    <td>API通信域名：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="api" id="api" value="<?php echo $rs['api']?>" /></td>
                </tr>


                <tr style="height:20px" class="hover">
                    <td>DNS数据：</td>
                    <td>
                        <?php
                        $rows = 4;
                        $data = [];
                        if($rs['dns']){
                            $dns  = json_decode($rs['dns'],true);
                            foreach ($dns as $v){
                                $data[] = "{$v['tag']}:{$v['sub_domain']}:{$v['cdn']}:{$v['host']}";
                            }
                            $rows = count($dns)+1;
                        }?>
                        <textarea class="txt" style="width: 400px;" cols="50" rows="<?php  echo $rows?>" name="dns" id="dns"><?php echo implode("\n",$data)?></textarea>
                        <br />
                        <span style="color: #0000FF">pc,wap,mip,api,s</span>   标识:主机名:CDN:服务器Host
                        <span style="color: #cd0a0a;">多个请换行</span>
                    </td>
                </tr>


                <tr style="height:20px" class="hover">
                    <td>服务器Host：</td>
                    <td><select name="host" id="host">
                            <?php foreach ($host as $v){?>
                                <option value="<?php echo $v['host_tag']?>"><?php echo $v['host_tag']?> - [<?php echo $v['public_ip']?>] - <?php echo $v['name']?>(<?php echo status::host_type[$v['host_type']]; ?>)</option>
                            <?php }?>
                        </select> （共<?php echo count($host)?>组）
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>备案：</td>
                    <td><input class="txt" style="width: 400px;" type="text" name="beian" id="beian" value="<?php echo $rs['beian']?>" /></td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>统计：</td>
                    <td>
                        <?php
                        $rows = 3;
                        $data = [];
                        if($rs['stat']){
                            $dns  = json_decode($rs['stat'],true);
                            foreach ($dns as $v){
                                $data[] = "{$v['tag']}:{$v['stat_uid']}";
                            }
                            $rows = count($dns)+1;
                        }?>
                        <textarea class="txt" style="width: 300px;" cols="50" rows="<?php  echo $rows?>" name="stat" id="stat"><?php echo implode("\n",$data)?></textarea>
                        <br />
                        统计名称:统计唯一标识 如:<span style="color: mediumorchid;">baidu:23f492175d819ed4203c01bd00c88cbf</span>
                        <span style="color: #cd0a0a;">多个请换行</span>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>数据库：</td>
                    <td>
                        <textarea class="txt" style="width: 400px;" cols="50" rows="4" name="db" id="db"><?php echo $rs['db']?></textarea>
                        <?php if($rs && $rs['api'] && $rs['type'] == adm_purv::app_type_site){?>
                            <a href="/api_interface/mysql/?api=<?php echo $rs['api']?>&site_tag=<?php echo $rs['site_tag'];?>">同步数据库mysql配制</a>
                        <?php }?>
                        <br />
                        <span style="color: #cd0a0a;">JSON数据</span>
                    </td>
                </tr>


                <tr style="height:20px" class="hover">
                    <td>状态：</td>
                    <td>
                        <select name="state" id="state">
                            <option value="0">关闭状态</option>
                            <option value="1">开启</option>
                        </select>
                    </td>
                </tr>

                <tr style="height:20px" class="hover">
                    <td>扩展数据：</td>
                    <td>
                        <textarea class="txt" style="width: 400px;" cols="50" rows="3" name="exts" id="exts"><?php echo $rs['exts']?></textarea>
                        <br />
                        <span style="color: #cd0a0a;">JSON数据</span>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td colspan="15">
                        <input type="submit" class="btn" id="submit_submit" title="按 Enter 键可随时提交你的修改" value="提交" />
                        <a href="<?php echo \ounun\page_util::page('site_list.html')?>" style="color: #0000FF;">返回列表</a>
                    </td>
                </tr>
            </table>
        </form>
        <script type="text/javascript">
            function check_form(of)
            {
                if(of.site_tag.value=='')
                {
                    alert('"站点标识"不能为空!');
                    of.site_tag.focus();
                    return false;
                }
                if(of.main_domain.value=='')
                {
                    alert('"主域名pc"不能为空!');
                    of.main_domain.focus();
                    return false;
                }
                if(of.name.value=='')
                {
                    alert('"网站名称"不能为空!');
                    of.name.focus();
                    return false;
                }
                if(of.site_cls.value=='')
                {
                    alert('"网站分类"不能为空!');
                    of.site_cls.focus();
                    return false;
                }
                if(of.cdn.value=='')
                {
                    alert('"CDN服务器商"不能为空!');
                    of.cdn.focus();
                    return false;
                }
                return true;
            }

            <?php if($rs && $rs['zqun_tag']){?>
            document.getElementById('cpform').zqun_tag.value = <?php echo json_encode($rs['zqun_tag'])?>;
            document.getElementById('cpform').host.value = <?php echo json_encode($rs['host'])?>;
            document.getElementById('cpform').state.value = <?php echo json_encode($rs['state'])?>;
            <?php }?>
        </script>
    </div>
<?php require v::require_fixed_comp('_foot.html.php')?>