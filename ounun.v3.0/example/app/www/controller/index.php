<?php

namespace app\www\controller;

class index extends \v
{
    /** 开始init */
    public function index($mod)
    {
        $this->init_page('/', false, true, '', 0, false);

        require \v::tpl_fixed('login.html.php');
    }
}
