<?php
/** 命名空间 */

namespace plugins;


class shell
{
    protected $_debug = false;

    public function __construct($debug)
    {
        $this->_debug = $debug;
    }

    protected function _mkdir($dir)
    {
        if ($this->_debug) {
            echo "debug mkdir:{$dir}\n";
        } else {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    protected function _sh_file($msg, $cmd, $sudo = '')
    {
        $cmd = "#!/bin/sh\n" . $cmd;
        if ($sudo) {
            $cmd = str_replace($sudo, '', $cmd);
        }
        $filename = "/tmp/cmd_" . time() . ".sh";
        file_put_contents($filename, $cmd);
        $cmd_file = "chmod +x {$filename}\n{$sudo}{$filename}";
        echo "\$cmd_file:{$cmd_file}\n";
        $this->_sh($msg, $cmd_file);
        // unlink($filename);
    }

    protected function _sh($msg, $cmd)
    {
        if ($this->_debug) {
            echo "\n\ndebug no run ......\n\n\n";
            echo 'cmd:<pre>', $cmd, '</pre>';
        } else {
            $retval = '';
            echo '<pre>', "\n{$msg}:\n";
            // echo  $cmd;
            $last_line = \system($cmd, $retval);
            echo '</pre>';
        }
        return true;
    }
}
