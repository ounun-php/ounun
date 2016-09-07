<?php 
/** 命名空间 */
namespace port;

class erlang
{
    /**
     * 得到服务器当前在线
     * @param uint $sid
     */
    public function online($sid,$host=null,$port=null)
    {
        $data	= "[]";
        return $this->_erlang_call('gm_api','online',$sid,$data,$host,$port);
    }
    /**
     * 踢玩家下线
     * @param uint $sid
     * @param uint $uid
     * @param uint $code
     *  % 您已在别处登录        205).
    % 您的帐号给冻结24小时  210).
    % 您已被禁止登录        215).
    % 您的帐号已被禁止登录  220).
    % 您的帐号已被GM登录,请15分钟后再登录    225).
    % 服务器停服维护,请稍后再试              230).
     * @return array
     *     0 => boolean true
     *     1 => string 'true' (length=4)
     */
    public function newguide($sid,$data,$host=null,$port=null)
    {
//    print_r($data);
        return $this->_erlang_call('gm_api','set_guide',$sid,$data,$host,$port);
    }

    public function offguide($sid,$data,$host=null,$port=null)
    {
        //    print_r($data);
        return $this->_erlang_call('gm_api','off_guide',$sid,$data,$host,$port);
    }


    public function user_out($sid,$type,$uid,$code=225,$host=null,$port=null)
    {
        $data	= "[{$type},{$uid},{$code}]";
        return $this->_erlang_call('gm_api','user_out',$sid,$data,$host,$port);
    }

    public function user_out_delete($sid,$uid,$host=null,$port=null)
    {
        $data	= "[{$uid}]";
        return $this->_erlang_call('gm_api','user_out_delete',$sid,$data,$host,$port);
    }



    public function chat_ban_add($sid,$uid,$second=300,$host=null,$port=null)
    {
        $data	= "[{$uid},{$second}]";
        return $this->_erlang_call('gm_api','chat_ban_add',$sid,$data,$host,$port);
    }

    public function chat_ban_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gm_api','chat_ban_get',$sid,$data,$host,$port);
    }

    public function chat_ban_delete($sid,$uid,$host=null,$port=null)
    {
        $data    = "[{$uid}]";
        return $this->_erlang_call('gm_api','chat_ban_delete',$sid,$data,$host,$port);
    }

    public function chat_ban_uuid_set($sid,$uuid,$second=300,$host=null,$port=null)
    {
        $data    = "[{$uuid},{$second}]";
        return $this->_erlang_call('gm_api','chat_ban_uuid_set',$sid,$data,$host,$port);
    }

    public function chat_ban_uuid_delete($sid,$uuid,$host=null,$port=null)
    {
        $data    = "[{$uuid}]";
        return $this->_erlang_call('gm_api','chat_ban_uuid_delete',$sid,$data,$host,$port);
    }



    /**
     * 得到玩家，现在数据
     * @param uint $sid
     * @param uint $uid
     * @return json 背包等所有玩家的数据
     */
    public function user_info($sid,$uid,$host=null,$port=null)
    {
        $data	= "[{$sid},{$uid}]";
//    print_r($data);
        return $this->_erlang_call('gm_api','user_info',$sid,$data,$host,$port);
    }

    /**
     * 拉玩家回城
     * @param uint $sid
     * @param uint $uid
     */
    public function set_map_xy($sid,$uid,$host=null,$port=null)
    {
        $data	= "[{$uid}]";
        return $this->_erlang_call('gm_api','set_map_xy',$sid,$data,$host,$port);
    }
    /**
     * 冲值成功
     * @param uint $sid
     * @param uint $uid
     * @return Ambigous <\app\gamecore\erl\Ambigous, multitype:, multitype:boolean string >
     */
    public function give_pay($sid,$uid,$rmb,$bindrmb,$oid,$pid,$time,$host=null,$port=null)
    {
        $oid      = "<<".$this->_string_binary($oid).">>";
        $data     = "[{$uid},{$rmb},{$bindrmb},{$oid},{$pid},{$time}]";
        return $this->_erlang_call('gm_api','give_pay',$sid,$data,$host,$port);
    }
    /**
     * GM加经验
     * @param uint $sid
     * @param uint $uid
     * @param uint $exp
     * @return Ambigous <\app\gamecore\erl\Ambigous, multitype:, multitype:boolean string >
     */
    public function give_exp($sid,$uid,$exp,$host=null,$port=null)
    {
        $data	= "[{$uid},{$exp}]";
        return $this->_erlang_call('gm_api','give_exp',$sid,$data,$host,$port);
    }

    /**
     * GM加铜钱
     * @param uint $sid
     * @param uint $uid
     * @param uint $gold
     * @param bool $is_bind  true:邦定 false:没邦定
     */
    public function give_gold($sid,$uid,$gold,$is_bind=true,$host=null,$port=null)
    {
        $is_bind = $is_bind?'true':'false';
        $data	 = "[{$uid},{$gold},{$is_bind}]";
        return $this->_erlang_call('gm_api','give_gold',$sid,$data,$host,$port);
    }

    /**
     * GM给物品
     * @param uint $sid
     * @param uint $uid
     * @param uint $goods_id
     * @param uint $count
     * @param uint $streng
     * @param uint $name_color
     * @param uint $bind
     * @param uint $expiry_type
     * @param uint $expiry
     */
    public function give_goods($sid,$uid,$goods_id,$count=1,$streng=0,$name_color=1,$bind=1,$expiry_type=0,$expiry=0,$host=null,$port=null)
    {
        $goods	= $this->_give_good($goods_id,$count,$streng,$name_color,$bind,$expiry_type,$expiry);
        $data	= "[{$uid},{$goods}]";
        return $this->_erlang_call('gm_api','give_goods',$sid,$data,$host,$port);
    }

    /**
     * GM扣物品
     * @param uint $sid
     * @param uint $uid
     * @param uint $goods_id
     * @param uint $count
     * @param uint $streng
     * @param uint $name_color
     * @param uint $bind
     * @param uint $expiry_type
     * @param uint $expiry
     */
    public function gm_cut_goods($sid,$uid,$goods_id,$count=1,$host=null,$port=null)
    {
        $goods	= "{{$goods_id},{$count}}";
        $data	= "[{$uid},{$goods}]";
        return $this->_erlang_call('gm_api','gm_cut_goods',$sid,$data,$host,$port);
    }

    /**
     * GM扣铜钱
     * @param uint $sid
     * @param uint $uid
     * @param uint $gold
     * @param bool $is_bind  true:邦定 false:没邦定
     */
    public function gm_cut_gold($sid,$uid,$gold,$is_bind=true,$host=null,$port=null)
    {
        $is_bind = $is_bind?'true':'false';
        $data	 = "[{$uid},{$gold},{$is_bind}]";
        return $this->_erlang_call('gm_api','gm_cut_gold',$sid,$data,$host,$port);
    }

    /**
     * GM扣仙玉
     * @param uint $sid
     * @param uint $uid
     * @param uint $gold
     * @param bool $is_bind  true:邦定 false:没邦定
     */
    public function gm_cut_rmb($sid,$uid,$rmb,$host=null,$port=null)
    {
        $data	 = "[{$uid},{$rmb}]";
        return $this->_erlang_call('gm_api','gm_cut_rmb',$sid,$data,$host,$port);
    }

    /**
     * GM扣元宝
     * @param uint $sid
     * @param uint $uid
     * @param uint $gold
     * @param bool $is_bind  true:邦定 false:没邦定
     */
    public function gm_cut_point($sid,$uid,$point,$host=null,$port=null)
    {
        $data	 = "[{$uid},{$point}]";
        return $this->_erlang_call('gm_api','gm_cut_point',$sid,$data,$host,$port);
    }

    /**
     * 向一个或多个玩家发放邮件多个物品
     * @param string $sid
     * @param array $uids
     * @param string $title
     * @param string $content
     * @param uint $gold
     * @param uint $goods_id
     * @param uint $count
     * @param uint $streng
     * @param uint $name_color
     * @param uint $bind
     * @param uint $expiry_type
     * @param uint $expiry
     */
    public function send_mail($sid,$uids,$title,$content,$wgoods,$vgoods=0,$host=null,$port=null)
    {
        $vgoods	 = (int)$vgoods;
        //$goods_id= (int)$goods_id;

        $uids    = "[".implode(",", $uids)."]";
        $title   = "<<".$this->_string_binary($title).">>";
        $content = "<<".$this->_string_binary($content).">>";
        $vgoods  = $vgoods? "{1,$vgoods}":'';
        $goods   = "";
        if($wgoods['goods_id'])
        {
            foreach($wgoods['goods_id'] as $k=>$v)
            {
                $goods[]="{give,{$v},{$wgoods['goods_count'][$k]},{$wgoods['goods_streng'][$k]},{$wgoods['goods_name_color'][$k]},{$wgoods['goods_bind'][$k]},{$wgoods['goods_expiry_type'][$k]},{$wgoods['goods_expiry'][$k]}}";
            }
        }
        if(is_array($goods))
        {
            $goods  = implode(",", $goods);
        }
        $data	= "[{$sid},{$uids},{$title},{$content},[{$goods}],[{$vgoods}]]";
        return $this->_erlang_call('gm_api','send_mail',$sid,$data,$host,$port);
    }

    /**
     * 全服发邮件
     * @param uint $sid
     * @param string $title
     * @param string $content
     * @param uint $gold
     * @param uint $goods_id
     * @param uint $count
     * @param uint $streng
     * @param uint $name_color
     * @param uint $bind
     * @param uint $expiry_type
     * @param uint $expiry
     */
    /*
    public function send_mail_all($sid,$title,$content,$vgoods=0,$goods_id=0,$count=1,$streng=0,$name_color=1,$bind=1,$expiry_type=0,$expiry=0)
    {
        $vgoods	 = (int)$vgoods;
        $goods_id= (int)$goods_id;
        $title   = "<<".$this->_string_binary($title).">>";
        $content = "<<".$this->_string_binary($content).">>";
        $vgoods  = $vgoods? "{1,$vgoods}":'';
        $goods   = "";
        if($goods_id)
        {
            $goods	= $this->_goods_give($goods_id,$count,$streng,$name_color,$bind,$expiry_type,$expiry);
        }
        $data	= "[{$sid},{$title},{$content},[{$goods}],[{$vgoods}]]";
        return $this->_erlang_call('gm_api_master','send_mail_all',$sid,$data);
    }
    */
    /**
     * 全服发邮件多个物品
     * @param uint $sid
     * @param string $title
     * @param string $content
     * @param uint $gold
     * @param uint $goods_id
     * @param uint $count
     * @param uint $streng
     * @param uint $name_color
     * @param uint $bind
     * @param uint $expiry_type
     * @param uint $expiry
     */
    public function send_mail_all($sid,$title,$content,$wgoods,$vgoods=0,$host=null,$port=null)
    {
        $vgoods	 = (int)$vgoods;
        //$goods_id= (int)$goods_id;
        $title   = "<<".$this->_string_binary($title).">>";
        $content = "<<".$this->_string_binary($content).">>";
        $vgoods  = $vgoods? "{1,$vgoods}":'';
        $goods   = "";
        foreach($wgoods['goods_id'] as $k=>$v)
        {
            if($v)
            {
                $goods[]="{give,{$v},{$wgoods['goods_count'][$k]},{$wgoods['goods_streng'][$k]},{$wgoods['goods_name_color'][$k]},{$wgoods['goods_bind'][$k]},{$wgoods['goods_expiry_type'][$k]},{$wgoods['goods_expiry'][$k]}}";
            }
        }
        if (is_array($goods))
        {
            $goods=implode(",", $goods);
        }
        $data	= "[{$sid},{$title},{$content},[{$goods}],[{$vgoods}]]";
        return $this->_erlang_call('gm_api','send_mail_all',$sid,$data,$host,$port);
    }

    /**
     * 游戏间触发邮件
     * @param uint $sid
     * @param uint $id
     */
    public function set_create_mail($sid,$type,$value,$title,$content,$goods,$vgoods,$host=null,$port=null)
    {
        $title   = "<<".$this->_string_binary($title).">>";
        $content = "<<".$this->_string_binary($content).">>";
        $data    = "[{$type},{$value},{$title},{$content},[{$goods}],[{$vgoods}]]";
        return $this->_erlang_call('gm_api','set_create_mail',$sid,$data,$host,$port);
    }

    public function del_create_mail($sid,$type,$value,$host=null,$port=null)
    {
        $data    = "[{$type},{$value}]";
        return $this->_erlang_call('gm_api','del_create_mail',$sid,$data,$host,$port);
    }



    /**
     * 发送公告
     * @param uint $sid
     * @param uint $id
     * @param uint $interval     秒
     * @param uint $begin_time   秒
     * @param uint $end_time     秒
     * @param string $content
     */
    public function send_notice($sid,$id,$msg_type,$interval,$begin_time,$end_time,$show_time,$content,$host=null,$port=null)
    {
        $content = str_replace(array("\n","\r"), '', $content);
        $content = "<<".$this->_string_binary($content).">>";
        $data    = "[{$id},{$msg_type},{$interval},{$begin_time},{$end_time},{$show_time},{$content}]";
        return $this->_erlang_call('gm_api','send_notice',$sid,$data,$host,$port);
    }
    /**
     * 删除公告
     * @param uint $sid
     * @param uint $id
     */
    public function send_notice_del($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        return $this->_erlang_call('gm_api','send_notice_del',$sid,$data,$host,$port);
    }

    /**
     * 精彩活动 读取
     */
    public function sales_time_read($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','sales_time_read',$sid,$data,$host,$port);
    }

    /**
     * 精彩活动 开放/关闭
     * @param uint $sid
     * @param uint $sales_id   活动ID
     * @param uint $is_have    0:不存在   1:存在
     * @param list 生效时段
     *     []:一直生效
     *     [{StartM,StartD,StartH,StartI,EndM,EndD,EndH,EndI},..]:  自然时间{月,日,时,分,月,日,时,分}
     *     [{open,StartD,StartH,StartI,EndD,EndH,EndI},..]:  开服时间(开服当天算第一天){开服天数,时,分,开服天数,时,分}
     *     [{week,StartD,StartH,StartI,EndD,EndH,EndI},..]:  每周活动(1-7){星期几,时,分,星期几,时,分}
     *     [{month,StartD,StartH,StartI,EndD,EndH,EndI},..]: 每月活动(1-31){几号,时,分,几号,时,分}
     */
    public function sales_time_setup($sid,$sales_id,$is_have,$times,$host=null,$port=null)
    {
        $sales_id= (int)$sales_id;
        $is_have = $is_have?1:0;
        $data    = "[{$sales_id},{$is_have},{$times}]";
        return $this->_erlang_call('gm_api','sales_time_setup',$sid,$data,$host,$port);
    }


    /**
     * 精彩活动 读取
     */
    public function d_sale_get($sid,$sales_id,$host=null,$port=null)
    {
        $data    = "[{$sales_id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','d_sale_get',$sid,$data,$host,$port);
    }


    public function d_sale_set($sid,$sales_id,$data,$host=null,$port=null)
    {
        $data    = "[{$sales_id},{$data}]";
        return $this->_erlang_call('gm_api','d_sale_set',$sid,$data,$host,$port);
    }

    public function d_sale_reset($sid,$sales_id,$host=null,$port=null)
    {
        $data    = "[{$sales_id}]";
        return $this->_erlang_call('gm_api','d_sale_reset',$sid,$data,$host,$port);
    }

    public function d_all_reset($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gm_api','d_all_reset',$sid,$data,$host,$port);
    }



    /**
     * 功能活动 读取
     */
    public function active_get_list($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','active_get_list',$sid,$data,$host,$port);
    }
    /**
     * 设定开服时间与跨服节点
     * @param $sid
     * @param $work_year
     * @param $work_month
     * @param $work_day
     * @param $super_id
     * @param null $host
     * @param null $port
     * @return Ambigous
     */
    public function set_work_day($sid,$work_year,$work_month,$work_day,$super_id,$merge_count,$host=null,$port=null)
    {
        $data    = "[{$work_year},{$work_month},{$work_day},{$super_id},{$merge_count}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','set_work_day',$sid,$data,$host,$port);
    }

    public function active_update($sid,$sales_id,$da,$we,$asx,$lv,$host=null,$port=null)
    {
        $sales_id= (int)$sales_id;
//     $is_have = $is_have?1:0;
        $data    = "[{$sales_id},{$da},{$we},{$asx},$lv,{$sales_id}]";
        return $this->_erlang_call('gm_api','active_update',$sid,$data,$host,$port);
    }

    public function active_clear_mysql($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','active_clear_mysql',$sid,$data,$host,$port);
    }

    public function active_delete($sid,$sales_id,$host=null,$port=null)
    {
        $sales_id= (int)$sales_id;
        $data    = "[{$sales_id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','active_delete',$sid,$data,$host,$port);
    }

    public function sales_time_restart($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','sales_time_restart',$sid,$data,$host,$port);
    }

    public function sales_time_add($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','sales_time_add',$sid,$data,$host,$port);
    }

    /**
     * 重开三国基金
     */
    public function open_fund($sid,$begin,$end,$host=null,$port=null)
    {
        $begin['0'] = (int)$begin['0'];
        $begin['1'] = (int)$begin['1'];
        $begin['2'] = (int)$begin['2'];

        $end['0'] = (int)$end['0'];
        $end['1'] = (int)$end['1'];
        $end['2'] = (int)$end['2'];
        $data    = "[{$begin['0']},{$begin['1']},{$begin['2']},{$end['0']},{$end['1']},{$end['2']}]";
        return $this->_erlang_call('gm_api','privilege',$sid,$data,$host,$port);
    }

    public function open_fund_selt($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','get_privilege_date',$sid,$data,$host,$port);
    }


    public function copy_holiday_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','copy_holiday_get',$sid,$data,$host,$port);
    }

    public function copy_holiday($sid,$id,$begin,$end,$host=null,$port=null)
    {
        $data    = "[{$id},{$begin},{$end}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','copy_holiday',$sid,$data,$host,$port);
    }

    public function jieri_time_set($sid,$id,$begin,$end,$host=null,$port=null)
    {
        $data    = "[{$id},{$begin},{$end}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','jieri_time_set',$sid,$data,$host,$port);
    }

    public function jieri_time_read($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','jieri_time_read',$sid,$data,$host,$port);
    }

    public function d_galaturn_get($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','d_galaturn_get',$sid,$data,$host,$port);
    }

    public function d_galaturn_set($sid,$id,$list,$host=null,$port=null)
    {
        $data    = "[{$id},{$list}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','d_galaturn_set',$sid,$data,$host,$port);
    }


    public function d_galarank_get($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','d_galarank_get',$sid,$data,$host,$port);
    }

    public function d_galarank_set($sid,$id,$list,$host=null,$port=null)
    {
        $data    = "[{$id},{$list}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','d_galarank_set',$sid,$data,$host,$port);
    }
    public function reset_turn($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','reset_turn',$sid,$data,$host,$port);
    }

    public function reset_rank($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','reset_rank',$sid,$data,$host,$port);
    }


// 限时商城
    public function shopxs_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','shopxs_get',$sid,$data,$host,$port);
    }

    public function shopxs_set($sid,$begin,$end,$host=null,$port=null)
    {
        $data    = "[{$begin},{$end}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','shopxs_set',$sid,$data,$host,$port);
    }

    public function shopxs_del($sid,$begin,$end,$host=null,$port=null)
    {
        $data    = "[{$begin},{$end}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','shopxs_del',$sid,$data,$host,$port);
    }


    public function login_reward_get($sid,$host=null,$port=null)
    {
        $data    = "[]";

        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','login_reward_get',$sid,$data,$host,$port);
    }

    public function login_reward_set($sid,$begin,$end,$goods,$host=null,$port=null)
    {
        $data    = "[{$begin},{$end},{$goods}]";
// 	 echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','login_reward_set',$sid,$data,$host,$port);
    }



    public function login_reward_delete($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','login_reward_delete',$sid,$data,$host,$port);
    }

    public function copy_money_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','copy_money_get',$sid,$data,$host,$port);
    }

    public function copy_money_set($sid,$begin,$end,$multiple,$host=null,$port=null)
    {
        $data    = "[{$begin},{$end},{$multiple}]";
        return $this->_erlang_call('gm_api','copy_money_set',$sid,$data,$host,$port);
    }

    public function thousand_time_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_time_get',$sid,$data,$host,$port);
    }

    public function thousand_time_set($sid,$begin,$end,$host=null,$port=null)
    {
        $data    = "[{$begin},{$end}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_time_set',$sid,$data,$host,$port);
    }
    public function thousand_time_delete($sid,$id,$host=null,$port=null)
    {
        $data    = "[{$id}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_time_delete',$sid,$data,$host,$port);
    }

    public function thousand_rank_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_rank_get',$sid,$data,$host,$port);
    }

    public function thousand_rank_set($sid,$list,$host=null,$port=null)
    {
        $str='';
        foreach ($list as $value)
        {
            $name = "<<".$this->_string_binary($value['name']).">>";
            $str.="{ {$value['uid']},{$name},{$value['pro']},{$value['sid']},{$value['harm']},{$value['time']},{$value['time2']}},";
            // 	 		thousand_rank_set
        }

        $idx = strrpos($str,',');
        $str = substr($str,0,$idx).substr($str,$idx+1);
        $str='['."$str".']';
        $data    = "[{$str}]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_rank_set',$sid,$data,$host,$port);
    }

    public function thousand_all_rank_get($sid,$host=null,$port=null)
    {
        $data    = "[]";
        // echo "\$sid:{$sid} ";
        return $this->_erlang_call('gm_api','thousand_all_rank_get',$sid,$data,$host,$port);
    }

    public function thousand_harm_delete($sid,$uid,$host=null,$port=null)
    {
        $data    = "[{$uid}]";
        return $this->_erlang_call('gm_api','thousand_harm_delete',$sid,$data,$host,$port);
    }


    public function thousand_harm_update($sid,$uid,$harm,$time,$host=null,$port=null)
    {
        $data    = "[{$uid},{$harm},{$time}]";
        return $this->_erlang_call('gm_api','thousand_harm_update',$sid,$data,$host,$port);
    }

    public function fighters_update($sid,$uid,$floor,$host=null,$port=null)
    {
        $data    = "[{$uid},{$floor}]";
        return $this->_erlang_call('gm_api','fighters_update',$sid,$data,$host,$port);
    }

    /**
     *排行榜删除
     */
    public function role_delete($sid,$uid,$host=null,$port=null)
    {
        $data    = "[{$uid}]";
        return $this->_erlang_call('gm_api','role_delete',$sid,$data,$host,$port);
    }

    /**
     * 微信绑定
     * @param uint $sid
     * @param uint $uid
     * @return json 背包等所有玩家的数据
     */
    public function wx_band($sid,$uid,$host=null,$port=null)
    {
        $data	= "[{$uid}]";
//     print_r($host);
        return $this->_erlang_call('gm_api','wx_band',$sid,$data,$host,$port);
    }


    /**
     * 开放系统
     * @param uint $sid
     * @param uint $uid
     * @param uint $task_id
     */

    public function get_sysopen_list($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gm_api','get_funs_state',$sid,$data,$host,$port);
    }

    public function set_sysopen_list($sid,$data,$host=null,$port=null)
    {
        return $this->_erlang_call('gm_api','set_funs_state',$sid,$data,$host,$port);
    }

    public function all_sys_open($sid,$uid,$host=null,$port=null)
    {
        $data    = "[{$uid}]";
        return $this->_erlang_call('gm_api','all_sys_open',$sid,$data,$host,$port);
    }

    /**
     * 每周二固定给表中玩家(内部)发放固定绑定元宝
     * @param uint $sid
     * @param uint $uid
     * @param uint $bindrmb
     */
    public function inside_rmb($sid,$uid,$bindrmb,$host=null,$port=null)
    {
        $data    = "[{$uid},{$sid},{$bindrmb}]";
        return $this->_erlang_call('gm_api','inside_rmb',$sid,$data,$host,$port);
    }


    /**
     * 开启服务器
     * @param uint $sid
     */
    public function game_start($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gc_server','game_start',$sid,$data,$host,$port);
    }
    /**
     * 重load数据
     * @param uint $sid
     * @param uint $force    true:强行  false:通知
     */
    public function game_load($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gc_server','l',$sid,$data,$host,$port);
    }
    /**
     * 关闭服务器
     * @param uint $sid
     * @param uint $force    true:强行  false:通知
     */
    public function game_stop($sid,$force=false,$host=null,$port=null)
    {
        $data    = "[".($force?"true":"false")."]";
        return $this->_erlang_call('gc_server','game_stop',$sid,$data,$host,$port);
    }
    /**
     * 强行关闭服务器,并结束结点
     * @param uint $sid
     */
    public function game_stop_node($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gc_server','game_stop_node',$sid,$data,$host,$port);
    }
    /**
     * 编译 服务器data数据
     * @param uint $sid
     */
    public function cmd_master_make($sid,$host=null,$port=null)
    {
        $data    = "[]";
        return $this->_erlang_call('gc_master','cmd_make',$sid,$data,$host,$port);
    }
    /**
     * 编译 开节点
     * @param uint $sid
     */
    public function cmd_master_open_node($serv_ver,$sid,$host=null,$port=null)
    {
        $dir = "/data/".Const_Project_No."_game/ver{$serv_ver}/beam.release/";
        if(file_exists($dir))
        {
            $yrl     = "/data/xm_game/dir.xm.config/ini.all.config.{$sid}.yrl";
            if(file_exists($yrl))
            {
                $cmd     = "erl -name ".Const_Project_No."_s{$sid}@{$host} +P 1024000 +K true -smp enable -kernel ".
                    "{inet_dist_listen_min,7000} {inet_dist_listen_max,7999} -boot start_sasl ".
                    "-config sasl_log -setcookie ce686762db75e -detached -s gc_server gc_start";

                $dir	 = "<<".$this->_string_binary($dir).">>";
                $cmd	 = "<<".$this->_string_binary($cmd).">>";
                $data    = "[{$dir},{$cmd}]";
                return $this->_erlang_call('gc_master','cmd_open_node',$sid,$data,$host,$port);
            }else
            {
                return "not file config.yrl !!!";
            }
        }else
        {
            return "only linux file_exists !!!";
        }

    }
    /**
     * 统一调用调用
     * @param uint   $sid
     * @param string $fun
     * @param string $arg_data
     * @return Ambigous <multitype:, multitype:boolean string >
     */
    private function _erlang_call($mod,$fun,$sid,$arg_data,$host=null,$port=null)
    {
        $host || $host = Const_Serv_Host;
        $port || $port = Const_Serv_PortMaster;
        $time = time();
        $md5  = md5($sid.'_'.$arg_data.'_'.$time.'_'.Const_Key_Conn_Private);
        //echo $sid.'_'.$arg_data.'_'.$time.'_'.Const_Key_Conn_Private,"<br />";
        //echo $host,':',$port," ","{{$sid},\"{$md5}\",{$time},{$arg_data}}";
        //exit();
        return \ounun\Http::Erlang($mod,$fun,"{ {$sid},\"{$md5}\",{$time},{$arg_data}}",$host,$port);
    }

    /**
     * 得到give数据元组
     * @param uint $goods_id 	物品ID
     * @param uint $count    	数量
     * @param uint $streng	  	强化等级
     * @param uint $name_color  物品名称的颜色
     * @param uint $bind		是否绑定(0:不绑定 1:绑定)
     * @param uint $expiry_type 有效期类型，0:不失效，1：秒，  2：天，请多预留几个以后会增加
     * @param uint $expiry		有效期，到期后自动消失，并发系统邮件通知
     */
    private function _give_good($goods_id,$count,$streng,$name_color,$bind,$expiry_type,$expiry)
    {
        // {give,2005,1,0,1,1,0,0}
        return "{give,{$goods_id},{$count},{$streng},{$name_color},{$bind},{$expiry_type},{$expiry}}";
    }

    /**
     *
     */
    private function _string_binary($string)
    {
        $i = 0;
        $number = array();
        while (isset($string{$i}))
        {
            $number[]= ord($string{$i});
            //++$i;
            $i++;
        }
        return implode(',', $number);
    }
}

