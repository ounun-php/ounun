<?php

namespace extend\task\coll;

class com_liaoliaoy extends \ounun\cmd\task\coll_base\_coll
{
    protected $user_id = 48460;

    protected $page = 0;

    protected $pagesize = 20;

    protected $static = 'http://static.liaoliaoy.com/audio/';


    public function __construct(\task\manage $task_manage, string $tag = '', string $tag_sub = '')
    {
        parent::__construct($task_manage, $tag, $tag_sub);

        $this->_tag = 'mp3';
        $this->_tag_sub = 'xgyw.cc';
        $this->configs('http://www.liaoliaoy.com', '`mp3_md`', 'md', '/data/ossfs_io3/', 'libs_v2');
    }


    public function data()
    {

    }


    /**
     *
     */
    public function room_detail()
    {
        $this->msg(__CLASS__ . ':' . __METHOD__);
        $url = "{$this->_url_root}/listenbook/apis/room_detail.php";
        $loop_do = false;
        $room_id = 1;
        do {
            $data = [
                'room_id' => $room_id,
                'user_id' => $this->user_id,
            ];
            $this->msg("url:" . \ounun::url($url, $data));
            $this->msg("----------------------------------------------------------------------------");
            $c2 = \plugins\curl\http::file_post_contents($url, $url, $data);
            $c = json_decode($c2, true);
            $room_id++;
            if ($c['msg'] == 'ok' && $c['result']) {
                $result = $c['result'];
                print_r($result);
                $loop_do = true;
                $this->msg('\$loop_do:true');
            } else {
                $loop_do = true;
                $this->msg($c2);
                $this->msg('\$loop_do:false');
            }
        } while ($loop_do);
    }

    /**
     * @param $room_id
     * @param int $page
     * @param int $pagesize
     */
    public function audio_list()
    {
        $this->msg(__CLASS__ . ':' . __METHOD__);
        $url = "{$this->_url_root}/listenbook/apis/audio_list.php";
        $loop_do = false;
        $pagesize = $this->pagesize;
        $page = 0;
        $room_id = 0;
        do {
            $data = [
                'page' => $page,
                'user_id' => $this->user_id,
                'pagesize' => $pagesize,
                'room_id' => $room_id,
            ];
            $this->msg("url:" . \ounun::url($url, $data));
            $this->msg("----------------------------------------------------------------------------");
            $c = \plugins\curl\http::file_post_contents($url, $url, $data);
            $c = json_decode($c, true);
            if ($c['msg'] == 'ok' && $c['result']) {
                $result = $c['result'];
                print_r($result);
            }
        } while ($loop_do);
    }


    public function audio_list2($page = 17)
    {
        $this->msg(__CLASS__ . ':' . __METHOD__);
        $url = "{$this->_url_root}/listenbook/apis/audio_list.php";
        $loop_do = false;
        $pagesize = $this->pagesize;
        $room_id = 604;
        $data = [
            'page' => $page,
            'user_id' => $this->user_id,
            'pagesize' => $pagesize,
            'room_id' => $room_id,
        ];
        $this->msg("url:" . \ounun::url($url, $data));
        $this->msg("----------------------------------------------------------------------------");
        $c = \plugins\curl\http::file_post_contents($url, $url, $data);
        $c = json_decode($c, true);
        if ($c['msg'] == 'ok' && $c['result']) {
            $result = $c['result'];
            print_r($result);
        }
    }

    /**
     * @param $mp3_url
     * @param $file
     */
    public function save_mp3($mp3_url, $file)
    {
        // $file    = '604/338.mp3';
        $file_local = $this->_dir_root . $file;
        $file_dir = dirname($file_local);
        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0777, true);
        }
        $url = "{$this->static}{$mp3_url}";
        $c = \plugins\curl\http::file_get_contents($url, $this->_url_root);

        file_put_contents($file_local, $c);
    }
}
