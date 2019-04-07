<?php

namespace ounun\cache;


class html_base extends core
{
    private $_cache_time = -1;
    private $_cache_time_t = -1;
    private $_cache_size = -1;
    private $_cache_size_t = -1;

    private $_debug = false;

    /**
     * 构建函数
     * @param $cfg
     */
    public function __construct($cfg, $debug = false)
    {
        parent::__construct();
        $type_list = array(self::Type_File, self::Type_Memcache, self::Type_Redis);
        $type = in_array($cfg['type'], $type_list) ? $cfg['type'] : self::Type_File;
        if (self::Type_Redis == $type) {
            $cfg['type'] = $type;
            $cfg['format_string'] = false;
            $cfg['large_scale'] = true;
        } elseif (self::Type_Memcache == $type) {
            $cfg['type'] = $type;
            $cfg['format_string'] = false;
            $cfg['large_scale'] = true;
        } else//if(self::Type_File == $type)
        {
            $cfg['type'] = $type;
            $cfg['format_string'] = true;
            $cfg['large_scale'] = true;
        }
        $this->_debug = $debug;
        $this->config($cfg);
    }

    /**
     * 修改时间
     * @return int 修改时间
     */
    public function cache_time()
    {
        if (0 <= $this->_cache_time) {
            return $this->_cache_time;
        }
        //
        $this->_cache_time = 0;
        if (self::Type_File == $this->_type) {
            $filename = $this->filename();
            // \debug::header('filename',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if (file_exists($filename)) {
                $this->_cache_time = filemtime($filename);
                // \debug::header('cache_time',$this->_cache_time,$this->_debug,__FUNCTION__,__LINE__);
            }
        } else {
            $this->_cache_time = (int)$this->get('filemtime');
        }
        return $this->_cache_time;
    }

    /**
     * 文件生成时间(临时)
     * @return int 文件生成时间(临时)
     */
    public function cache_time_tmp()
    {
        if (0 <= $this->_cache_time_t) {
            return $this->_cache_time_t;
        }
        //
        $this->_cache_time_t = 0;
        if (self::Type_File == $this->_type) {
            $filename = $this->filename() . '.t';
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if (file_exists($filename)) {
                $this->_cache_time_t = filemtime($filename);
                $this->_cache_size_t = filesize($filename);
                // \debug::header('time',$this->_cache_time_t,$this->_debug,__FUNCTION__,__LINE__);
            }
        } else {
            $this->_cache_time_t = (int)$this->get('filemtime_t');
        }
        return $this->_cache_time_t;
    }

    /**
     * 文件大小(临时)
     * @return int
     */
    public function cache_size_tmp()
    {
        return $this->_cache_size_t;
    }

    /**
     * 标记(临时)
     */
    public function cache_time_tmp_set()
    {
        $this->_cache_time_t = time();
        if (self::Type_File == $this->_type) {
            $filename = $this->filename() . '.t';
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if (file_exists($filename)) {
                touch($filename);
            } else {
                $filedir = dirname($filename);
                if (!is_dir($filedir)) {
                    mkdir($filedir, 0777, true);
                }
                touch($filename);
            }
        } else {
            $this->set('filemtime_t', $this->_cache_time_t);
            $this->write();
        }
    }

    /**
     * 文件大小
     * @return int 文件大小
     */
    public function cache_size()
    {
        if (0 <= $this->_cache_size) {
            return $this->_cache_size;
        }
        if (self::Type_File == $this->_type) {
            $filename = $this->filename();
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if (file_exists($filename)) {
                $this->_cache_size = filesize($filename);
                // \debug::header('size',$this->_cache_size,$this->_debug,__FUNCTION__,__LINE__);
            }
            $this->_cache_size = 0;
        } else {
            $this->_cache_size = (int)$this->get('filesize');
        }
        return $this->_cache_size;
    }

    /**
     * 保存数据
     */
    public function cache_html($html)
    {
        $this->_cache_time = time();
        if (self::Type_File == $this->_type) {
            $this->val($html);
            $this->write();
            $filename = $this->filename() . '.t';
            // \debug::header('delfile',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if (file_exists($filename)) {
                unlink($filename);
            }
        } else {
            $this->val(array('filemtime' => $this->_cache_time, 'filesize' => strlen($html), 'html' => $html));
            $this->write();
        }
    }

    /**
     * 保存数据
     */
    public function cache_out($gzip)
    {
        // 输出
        if ($gzip) {// 输出 ( 支持 gzip )
            header('Content-Encoding: gzip');
            if (self::Type_File == $this->_type) {
                $filename = $this->filename();
                header('Content-Length: ' . filesize($filename));
                readfile($filename);
                exit;
            } else {
                header('Content-Length: ' . $this->get('filesize'));
                exit($this->get('html'));
            }
        } else {// 输出 ( 不支持 gzip )
            if (self::Type_File == $this->_type) {
                $content = $this->read();
            } else {
                $content = $this->get('html');
            }
            $content = gzdecode($content);
            $filesize = strlen($content);
            header('Content-Length: ' . $filesize);
            exit($content);
        }
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        $this->_cache_time = -1;
        $this->_cache_time_t = -1;
        $this->_cache_size = -1;
        $this->_cache_size_t = -1;

        $filename = $this->filename() . '.t';

        if (file_exists($filename)) {
            return unlink($filename);
        }
        return parent::delete();
    }
}