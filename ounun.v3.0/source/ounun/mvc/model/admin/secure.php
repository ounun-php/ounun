<?php

namespace ounun\mvc\model\admin;

use plugins\xxtea;

class secure
{
    /** @var string 加密码KEY */
    protected $key = '';

    /**
     * secure constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * 管理员密码加密方式
     * @param $password string 密码
     * @return string
     */
    public function password($password)
    {
        return md5(md5($password) . md5($this->key));
    }

    /**
     * 通过data获得url
     * @param  $url  string
     * @param  $data array
     * @return string
     */
    public function url(string $url = '', array $data = [])
    {
        unset($data['sign']);

        // 没有时间 自动加上
        if (!$data['time']) {
            $data['time'] = time();
        }

        ksort($data);
        $rs = [];
        foreach ($data as $k => $v) {
            $rs[] = "{$k}={$v}";
        }
        $rs[] = "key={$this->key}";
        $sign2_s = implode('&', $rs);

        $data['sign'] = md5($sign2_s);
        return url_build_query($url, $data);
    }

    /**
     * @param string $url_root
     * @param array $paras
     * @return array
     */
    public function wget(string $url_root, array $paras = [])
    {
        $url = $this->url($url_root, $paras);
        $c = @\plugins\curl\http::file_get_contents($url);
        echo "\$url:{$url}\n";
        // '$c'=>$c
        // print_r(['$url'=>$url,'$c'=>$c]);
        if ($c) {
            $json = json_decode($c, true);
            if ($json && $json['ret'] && $json['data']) {
                $data = $this->decode($json['data']);
                if ($data) {
                    $json['ret'] = true;
                    $json['data'] = $data;
                    return $json;
                } else {
                    $error_msg = "出错:解码出错:({$json['data']})";
                }
            } elseif ($json && $json['error']) {
                $json['ret'] = false;
                return $json;
            } else {
                $error_msg = "出错:数据出错:({$c})";
            }
        } else {
            $error_msg = "出错:服务器没反:({$url_root})";
        }
        return ['ret' => false, 'error' => $error_msg];
    }

    /**
     * 输出
     * @param array $data
     */
    public function outs(array $data)
    {
        $data['ret'] = $data['ret'] ? true : false;
        if ($data['ret'] && $data['data']) {
            $data['data'] = $this->encode($data['data']);
        }
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 参数校验
     * @param array $data
     * @param int $time_now
     * @param int $time_fault_tolerant
     * @return array [true,'ok'] | [false,Error]
     */
    public function check(array $data, int $time_now, int $time_fault_tolerant = 600): array
    {
        $time_diff = $time_now - (int)$data['time'];
        if ($time_diff > $time_fault_tolerant) {
            return error("出错:运行超时");
        }
        if ($time_diff < 0 - $time_fault_tolerant / 10) {
            return error("出错:运行超时(服务器时间有误:{$time_diff})");
        }
        $sign = $data['sign'];
        unset($data['sign']);
        //
        ksort($data);
        $rs = [];
        foreach ($data as $k => $v) {
            $rs[] = "{$k}={$v}";
        }
        $rs[] = "key={$this->key}";
        $sign2_s = implode('&', $rs);
        $sign2 = md5($sign2_s);
        // print_r(['$sign2'=>$sign2,'$sign'=>$sign,'$sign2_s'=>$sign2_s]);
        if ($sign && 32 == strlen($sign) && $sign2 == $sign) {
            return succeed('ok');
        }
        return error("出错:校验出错");
    }

    /**
     * @param $data
     * @return string
     */
    public function encode($data)
    {
        $data_json = json_encode($data);
        return xxtea::encrypt($data_json, $this->key);
    }

    /**
     * @param string $data_json
     * @return mixed
     */
    public function decode(string $data_json)
    {
        $data_json2 = xxtea::decrypt($data_json, $this->key);
        return json_decode($data_json2, true);
    }
}
