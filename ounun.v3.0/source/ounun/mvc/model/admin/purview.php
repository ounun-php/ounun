<?php

namespace ounun\mvc\model\admin;


class purview
{
    /** @var int 操作日志 - 普通操作 */
    const send_msg = 0;
    /** @var int 操作日志 - 增加操作 */
    const db_insert = 1;
    /** @var int 操作日志 - 修改操作 */
    const db_update = 2;
    /** @var int 操作日志 - 删除操作 */
    const db_delete = 3;

    /** @var string session id */
    const session_id = 'id';
    const session_google = 'g';
    const session_cid = 'c';
    const session_type = 'type';
    const session_hash = 'hash';
    const session_account = 'acc';
    const session_password = 'p';

    /** cookie_key  */
    const adm_cid = 'adm_cid';
    const adm_cid_login = 'adm_cid_login';
    const adm_sid = 'adm_sid';

    const adm_hall_id = 'adm_hall';
    const adm_game_id = 'adm_game';

    const adm_group_id = 'adm_group';

    const adm_zqun_tag = 'adm_zqun';
    const adm_site_tag = 'adm_site';
    const adm_caiji_tag = 'adm_caiji';


    /** @var string 管理面板 - 应用类型 */
    const app_type_admin = 'admin';
    /** @var string 站点    - 应用类型 */
    const app_type_site = 'site';
    /** @var array 应用类型 */
    const app_type = [
        self::app_type_admin => '后台',
        self::app_type_site  => '站点',
    ];

    /** @var int 导航头 什么都不用显示 */
    const nav_null = 0;
    /** @var int 导航头 要显示 平台 */
    const nav_cid = 1;
    /** @var int 导航头 要显示 游戏及服务器 */
    const nav_hub = 2;
    /** @var int 导航头 要显示 游戏 */
    const nav_game = 3;
    /** @var int 导航头 要显示 分组 */
    const nav_game_group = 4;
    /** @var int 导航头 要显示 大厅 hyz 2017-9-27 */
    const nav_hall = 5;

    /** @var array 网站后台配 */
    public $config = [];
    /** @var array 游戏名 与 LOGO */
    public $config_name = [];

    /** @var string */
    public $table_admin_user = '';
    /** @var string */
    public $table_logs_login = '';
    /** @var string */
    public $table_logs_act = '';

    /** @var int IP限定 */
    public $max_ips = 20;
    /** @var int */
    public $max_ip = 5;

    /** @var array 权限列表 */
    public $purview = [];
    /** @var array */
    public $purview_group = [];
    /** @var array */
    public $purview_tree_root = [10, 20];
    /** @var array */
    public $purview_tree_coop = [10, 20, 50];
    /** @var string */
    public $purview_default = 'info';

    /** @var int 后台根目录 */
    public $purview_line = 40;
    /** @var int 邮件仙玉审核权限 */
    public $purview_check = 40;

    /**
     * 权限检测 多个
     * @param string $key
     * @return bool
     */
    public function check_multi(string $key): bool
    {
        $keys = explode('|', $key);
        if ($keys && is_array($keys)) {
            foreach ($keys as $v) {
                if ($this->check($v)) {
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
    public function check(string $key): bool
    {
        $type = oauth::instance()->session_get(self::session_type);
        if (!$type) {
            return false;
        }
        if ('tree@root' == $key) {
            return in_array($type, $this->purview_tree_root);
        } elseif ('tree@coop' == $key) {
            return in_array($type, $this->purview_tree_coop);
        } else {
            $key = explode('@', $key);
            $data = $this->purview[$key[0]];
            if ($data && $data['sub']) {
                $sub = $data['sub'][$key[1]];
                if ($sub && $sub['key']) {
                    return in_array($type, $sub['key']);
                }
                foreach ($data['sub'] as $subs) {
                    if ($subs && is_array($subs) && !$subs['key']) {
                        $sub = $subs['data'][$key[1]];
                        if ($sub && $sub['key']) {
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
    public function data(int $type = 0): array
    {
        $purview = [];
        if (oauth::instance()->login_check()) {
            if ('' == $type) {
                $type = oauth::instance()->session_get(self::session_type);
            }
            foreach ($this->purview as $key1 => $data1) {
                $purview_sub = [];
                if ($data1['sub']) {
                    foreach ($data1['sub'] as $key2 => $data2) {
                        if ($data2['key']) {
                            if (in_array($type, $data2['key'])) {
                                unset($data2['key']);
                                $purview_sub[$key2] = $data2;
                            }
                        } else {
                            $purview_sub2 = [];
                            foreach ($data2['data'] as $key3 => $data3) {
                                if ($data3['key'] && in_array($type, $data3['key'])) {
                                    unset($data3['key']);
                                    $purview_sub2[$key3] = $data3;
                                }
                            }
                            if ($purview_sub2) {
                                $purview_sub[$key2] = ['name' => $data2['name'], 'data' => $purview_sub2];
                            }
                        }
                    }
                }
                //
                if ($purview_sub) {
                    $purview[$key1] = [
                        'name' => $data1['name'],
                        'default' => $data1['default'],
                        'sub' => $purview_sub,
                    ];
                }
            }
        }
        return $purview;
    }
}
