<?php
namespace ounun\mvc\model\admin;


class purview
{
    /** 操作日志 - 普通操作 */
    const send_msg  = 0;
    /** 操作日志 - 增加操作 */
    const db_insert = 1;
    /** 操作日志 - 修改操作 */
    const db_update = 2;
    /** 操作日志 - 删除操作 */
    const db_delete = 3;

    /** session id */
    const s_id       = 'id';
    const s_google   = 'g';
    const s_cid      = 'c';
    const s_type     = 'type';
    const s_hash     = 'hash';
    const s_account  = 'acc';
    const s_password = 'p';

    /** cookie_key  */
    const cp_cid          = 'cp_cid';
    const cp_cid_login    = 'cp_cid_login';
    const cp_sid          = 'cp_sid';
    const cp_group        = 'cp_group';
    const cp_game_id      = 'cp_game_id';
    const cp_hall_id      = 'cp_hall_id';


    /** 管理面板 - 应用类型 */
    const app_type_admin  = 'admin';
    /** 站点    - 应用类型 */
    const app_type_site   = 'site';


    /** 导航头 什么都不用显示 */
    const nav_null        = 0;
    /** 导航头 要显示 平台 */
    const nav_cid         = 1;
    /** 导航头 要显示 游戏及服务器 */
    const nav_hub         = 2;
    /** 导航头 要显示 游戏 */
    const nav_game        = 3;
    /** 导航头 要显示 分组 */
    const nav_game_group  = 4;
    /** 导航头 要显示 大厅 hyz 2017-9-27*/
    const nav_hall        = 5;

    /** 网站后台配 */
    public $cfg      = [];
    /** 游戏名 与 LOGO */
    public $cfg_name = [];

    /** table */
    public $db_adm         = '';
    public $db_logs_login  = '';
    public $db_logs_act    = '';

    /** IP限定 */
    public $max_ips       = 20;
    public $max_ip        = 5;

    /** @var array 权限列表 */
    public $purview           = [];
    public $purview_group     = [];
    public $purview_tree_root = [10,20];
    public $purview_tree_coop = [10,20,50];
    public $purview_default   = 'info';
    /** 后台根目录 */
    public $purview_line      = 40;
    /** 邮件仙玉审核权限 */
    public $purview_check     = 40;

    /** @var oauth   */
    public $oauth = null;

    /**
     * 权限检测 多个
     * @param string $key
     * @return bool
     */
    public function check_multi(string $key):bool
    {
        $keys = explode('|',$key);
        if($keys && is_array($keys) )
        {
            foreach($keys as $v)
            {
                if($this->check($v))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 权限检测
     * @param string $key
     * @return bool
     */
    public function check(string $key):bool
    {
        $type  = $this->oauth->session_get(self::s_type);
        if(!$type)
        {
            return false;
        }
        if('tree@root' == $key)
        {
            return in_array($type, $this->purview_tree_root);
        }
        elseif('tree@coop' == $key)
        {
            return in_array($type, $this->purview_tree_coop);
        }
        else
        {
            $key	= explode('@', $key);
            $data	= $this->purview[$key[0]];
            if($data && $data['sub'])
            {
                $sub	= 	$data['sub'][$key[1]];
                if($sub && $sub['key'])
                {
                    return in_array($type, $sub['key']);
                }
                foreach ($data['sub'] as $subs)
                {
                    if ($subs && is_array($subs) && !$subs['key'])
                    {
                        $sub	= 	$subs['data'][$key[1]];
                        if($sub && $sub['key'])
                        {
                            return in_array($type, $sub['key']);
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * 获得权限目录
     * @param int $type
     *
     * @return array
     */
    public function data(int $type=0):array
    {
        $purview	= [];
        if ($this->oauth->login_check())
        {
            if(''==$type)
            {
                $type   = $this->oauth->session_get(self::s_type);
            }
            foreach ($this->purview as $key1 => $data1)
            {
                $purview_sub	= [];
                if($data1['sub'])
                {
                    foreach ($data1['sub'] as $key2 => $data2)
                    {
                        if($data2['key'])
                        {
                            if(in_array($type, $data2['key']))
                            {
                                unset($data2['key']);
                                $purview_sub[$key2] = $data2;
                            }
                        }else
                        {
                            $purview_sub2	= [];
                            foreach ($data2['data'] as $key3 => $data3)
                            {
                                if($data3['key'] && in_array($type, $data3['key']) )
                                {
                                    unset($data3['key']);
                                    $purview_sub2[$key3] = $data3;
                                }
                            }
                            if ($purview_sub2)
                            {
                                $purview_sub[$key2] = ['name' => $data2['name'], 'data'	=> $purview_sub2 ];
                            }
                        }
                    }
                }
                //
                if($purview_sub)
                {
                    $purview[$key1] = [
                        'name' 		=> $data1['name'],
                        'default'	=> $data1['default'],
                        'sub'		=> $purview_sub,
                    ];
                }
            }
        }
        return $purview;
    }

}