<?php

namespace plugins\curl;

/**
 * @Author: Jaeger <hj.q@qq.com>
 * @Date:   2015-11-11 17:52:40
 * @Last Modified by:   Jaeger
 * @Last Modified time: 2015-12-28 12:55:45
 * @version         1.0
 * 多线程扩展
 */
class Multi
{
    /** @var MultiBase */
    private $curl;

    private $args;

    public function run(array $args)
    {
        $default = [
            'curl' => [
                'opt' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_AUTOREFERER    => true,
                ],
                'maxThread' => 100,
                'maxTry' => 3
            ],
            'list'    => [],
            'success' => function(){},
            'error'   => function(){},
            'start'   => true
        ];

        $this->args = array_merge($default,$args);
        $this->curl = new MultiBase();
        if(isset($this->args['curl']))
        {
            foreach ($this->args['curl'] as $k => $v)
            {
                $this->curl->$k = $v;
            }
        }
        $this->add($this->args['list']);

        return $this->args['start']?$this->start():$this;
    }

    public function add($urls,$success = false,$error = false)
    {
        if(!is_array($urls))
        {
            $urls = [$urls];
        }
        foreach ($urls as $url)
        {
            $this->curl->add(
                [
                    'url'  => $url,
                    'args' => $this,
                    'opt'  => [CURLOPT_REFERER => $url]
                ],
                $success?$success:$this->args['success'],
                $error  ?$error  :$this->args['error']
            );
        }
        return $this;
    }

    public function start()
    {
        $this->curl->start();
        return $this;
    }
}