<?php

use \app\adm\model\purview;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$page_title_sub} - {$page_title}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="x-ua-compatible" content="ie=7"/>
    <link type="image/x-icon" rel="icon" href="{$static}favicon.ico">
    <link type="text/css" rel="stylesheet" href="{$static_g}adm/admincp.css?t3p"/>
    <script type="text/javascript">
        var IN_ADMINCP = true,
            IS_FRAME = 1;
    </script>
    <script type="text/javascript" src="https://cdn.bootcss.com/jquery/1.12.2/jquery.min.js"></script>
    <script type="text/javascript" src="{$static_g}code/date.js?t3p"></script>
    <script type="text/javascript" src="{$static_g}adm/common.js?t3p"></script>
    <script type="text/javascript" src="{$static_g}adm/admincp.js?t3p"></script>
    <script type="text/javascript">
        var nav = parseInt('{$page_nav}');
        var title = '{$page_title}';
        var title_sub = '{$page_title_sub}';
        var site_key = '<?php echo oauth()->cookie_get(purview::adm_site_tag) ?>';
        var zqun_key = '<?php echo oauth()->cookie_get(purview::adm_zqun_tag) ?>';
        var caiji_key = '<?php echo oauth()->cookie_get(purview::adm_caiji_tag) ?>';
        var url = '<?php echo $_GET['uri'] ? $_GET['uri'] : $_SERVER['REQUEST_URI']?>';
        var scfg = {nav: nav, site_key: site_key, zqun_key: zqun_key, caiji_key: caiji_key, curr_url: url};
        parent_init_page(title, title_sub, scfg);
    </script>
</head>
<body>
