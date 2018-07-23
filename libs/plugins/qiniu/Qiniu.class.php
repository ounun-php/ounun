<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 15/1/31
 * Time: 下午5:44
 */
namespace plugins\qiniu;

/** 本插件所在目录 */
define('Dir_Plugins_Qiniu',           realpath(__DIR__) .'/');

require_once(Dir_Plugins_Qiniu."libs/fop.php");
require_once(Dir_Plugins_Qiniu."libs/rs_utils.php");
require_once(Dir_Plugins_Qiniu."libs/rsf.php");

class Qiniu
{
    private $sdk_ver    = "6.1.9";
    private $host_up	= 'http://upload.qiniu.com';
    private $host_rs	= 'http://rs.qbox.me';
    private $host_rsf	= 'http://rsf.qbox.me';

    private $access_key;
    private $secret_key;

    private $bucket;
    private $client;

    public function __construct($qiniu_config,$bucket=null)
    {
        global $QINIU_ACCESS_KEY;
        global $QINIU_SECRET_KEY;
        //
        $this->access_key = $qiniu_config['access_key'];
        $this->secret_key = $qiniu_config['secret_key'];

        $QINIU_ACCESS_KEY = $this->access_key;
        $QINIU_SECRET_KEY = $this->secret_key;
        if($bucket)
        {
            $this->bucket     = $bucket;
        }else
        {
            $this->bucket     = $qiniu_config['bucket'];
        }
        $this->client         = new \Qiniu_MacHttpClient(null);
    }


    public function put_file($filename,$local_filename,$is_rewrite=1) // => ($putRet, $err)
    {
        if($is_rewrite)
        {
            $this->del_file($filename);
        }
        $key       = $filename;
        $putPolicy = new \Qiniu_RS_PutPolicy($this->bucket);
        $upToken   = $putPolicy->Token(null);
        $putExtra  = new \Qiniu_PutExtra();

        //$putExtra->Params = array('x:test'=>'test');
        $putExtra->CheckCrc = 1;
        list($ret, $err) = \Qiniu_PutFile($upToken, $key, $local_filename, $putExtra);
        //$this->assertNull($err);
        //$this->assertArrayHasKey('hash',   $ret);
        //$this->assertArrayHasKey('x:test', $ret);
        var_dump($err);
        var_dump($ret);

        //list($ret, $err) = \Qiniu_RS_Stat($this->client, $this->bucket, $key);
        // $this->assertNull($err);
        //var_dump($err);
        //var_dump($ret);
    }

    public function del_file($filename) // => $error
    {
        $key = $filename;
        $err = \Qiniu_RS_Delete($this->client, $this->bucket, $key);
        echo  '\Qiniu_RS_Delete($this->client, $this->bucket:'.$this->bucket.', $key:'.$key.')'."\n";
        var_dump($err);
    }

}
