<?php require v::tpl_fixed('_head.html.php')?>
    <div class="container" id="cpcontainer">
        <div class="itemtitle">
            <h3 style="padding-left: 5px;">{$page_title_sub}</h3>
            <div>
                <select onchange="if(this.value){document.location.href = '?table='+this.value;}">
                    <option value="">请选择...</option>
                    <?php

                     foreach ($pics as $k=>$v)
                     {
                         echo "<option value=\"{$k}\"".($_GET['table']==$k?'selected':'').">{$v}</option>";
                     }
                    ?>
                </select>
            </div>
        </div>
        <table class="tb tb2 ">
            <tr bgcolor="#FFFFFF">
                <td colspan="30">
                    <?php echo $ps['note'].' &nbsp;&nbsp; ';
                    echo implode('&nbsp;',$ps['page']);?>
                </td>
            </tr>
            <tr class="header">
                <th>标题/标识/图片</th>
                <th>添加时间/源  <a href="pics_add.html" style="color: #0000FF;">添加</a></th>
            </tr>

            <?php
//            $data = [];  http://adm2.moko8.com/coll/pics_list.html?table=pics_99mm&page=7
//            $ps   = ['note'=>'','page'=>[]];
            list('file_dir' => $file_dir, 'site_src' => $site_src, 'coll2pic' => $coll2pic,'http_res'=>$http_res) = \cfg\coll\data_v1::info_v1($_GET['table']);
            function pics_html($data,$http_res,$file_dir,$loop=0)
            {
                if(is_array($data))
                {
                    if($loop > 2)
                    {
                        echo "[".implode(',',$data)."]";
                    }else
                    {
                        foreach ($data as $v3)
                        {
                            pics_html($v3,$http_res,$file_dir,++$loop);
                        }
                    }
                }else
                {
                    echo "<img src=\"{$http_res}{$file_dir}/{$data}!1\" width=\"80\" onclick='this.src=this.src'>";
                }
            }
            foreach ($data as $v)
            {
                $pic_tags       = json_decode($v['pic_tag'],true);
                $pic_centent    = json_decode($v['pic_centent'],true);
                // $pic_centent = json_decode($v['pic_centent'],true);
                ?>
                <tr style="height:15px;">
                    <td><span style="font-size: 14px;">#<?php echo $v['pic_id']; ?></span><a style="color: #0000FF;"><?php echo $v['pic_title']; ?></a>
                        <?php foreach ($pic_tags as $tag){?>
                            <a href="pics_list.html?tag=<?php echo urlencode($tag); ?>"><?php echo $tag; ?></a>
                        <?php }?><br />
                        <?php pics_html($pic_centent,$http_res,$file_dir,0);?>
                    </td>
                    <td>
                        <?php echo date('Y-m-d H:i:s',$v['add_time']); ?><br />
                        <a href="<?php echo $v['pic_origin_url']; ?>" target="_blank"><?php echo $v['pic_origin_url']; ?></a> <br /><br />

                        <a href="?table=<?php echo $_GET['table']?>&pic_id=<?php echo $v['pic_id']?>&act=del" onclick="return msg_yn();">删除</a>
                        <a href="pics_add.html?table=<?php echo $_GET['table']?>&pic_id=<?php echo $v['pic_id']?>">修改</a>
                    </td>
                </tr>
            <?php
            }
            ?>
            <tr bgcolor="#FFFFFF">
                <td colspan="30">
                    <?php echo $ps['note'].' &nbsp;&nbsp; ';
                    echo implode('&nbsp;',$ps['page']);?>
                </td>
            </tr>
        </table>
    </div>
<?php require v::tpl_fixed('_foot.html.php')?>