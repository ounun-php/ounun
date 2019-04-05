<?php require v::tpl_fixed('_head.html.php') ?>
<div class="container" id="cpcontainer">
    <div class="itemtitle">
        <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
        <!--ul class="tab1">
          <li class="current"><a href="admin.php?action=founder&operation=perm&do=member"><span>管理成员列表</span></a></li>
          <li><a href="admin.php?action=founder&operation=perm&do=group"><span>团队职务</span></a></li>
          <li><a href="admin.php?action=founder&operation=perm&do=notifyusers"><span>管理通知</span></a></li>
        </ul-->
    </div>
    <table class="tb tb2 ">
        <tr class="header">
            <th>账号</th>
            <th>类型职务</th>
            <!--  <th>平台cid</th>
                  <th>平台名称</th> -->
            <th>登陆次数</th>
            <th>最后登陆时间</th>
            <th>手机号</th>
            <th>动态验证</th>
            <th>备注</th>

            <th>功能</th>
        </tr>
        <?php
        foreach ($user_list as $data) {
            ?>
            <tr style="height:20px" class="hover">
                <td style="color:#00F;font-weight:bold;"><?php echo $data['account']; ?></td>
                <td>[<?php echo $data['type']; ?>]<?php echo $data['tname']; ?></td>
                <!--      <td><?php echo $data['cid']; ?></td>
      <td><?php echo $data['cname']; ?></td>-->

                <td><?php echo $data['login_times']; ?></td>
                <td><?php echo $data['login_last'] ? date("Y-m-d H:i:s", $data['login_last']) : '-'; ?></td>
                <td><?php echo $data['tel']; ?></td>
                <td><?php echo $data['google_is']; ?></td>
                <td><?php echo $data['note']; ?></td>
                <td><a href="?act=del&adm_id=<?php echo $data['adm_id']; ?>" onclick="return msg_yn();">删除</a></td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
<?php require v::tpl_fixed('_foot.html.php') ?>
