<?php

use \app\adm\controller\adm;
use \app\adm\model\purview;
use \ounun\config;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>管理中心 - {$site_name}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta content="{$powered_studio_name} Inc." name="Copyright"/>
    <link type="image/x-icon" rel="icon" href="{$static}favicon.ico">
    <link type="text/css" rel="stylesheet" href="{$static_g}adm/admincp.css"/>
    <script type="text/javascript" src="{$static_g}adm/common.js"></script>
    <script type="text/javascript">



        var nav_hash = window.location.hash ? decodeURIComponent(window.location.hash.substr(1)) : '';
        nav_hash = nav_hash.split(",");
        var nav_key = nav_hash[1];
        nav_hash = nav_hash[0];

        var nav_default = nav_hash ? nav_hash :<?php echo json_encode(adm::$purview->purview[adm::$purview->purview_default]['default']);//$purview[$adm_purview_default]['default']);?>;
        var nav_default_key = nav_key ? nav_key : '<?php echo adm::$purview->purview_default;?>';
        var menukey = '';


        var scfg_caiji = <?php echo json_encode(config::$global['caiji'], JSON_UNESCAPED_UNICODE)?>;
        var scfg = <?php echo json_encode($scfg, JSON_UNESCAPED_UNICODE);?>;
        var json_curr_url = '';
        var json_nav = 0;

        /**
         * 初始化  默认第一页
         * @param title       选项名称
         * @param title_sub   页面名称
         * @param scfg        导航       0:无选择  1:平台  2:平台+服务器
         *                    平台cid
         *                    服务器sid
         *                    当前页面url
         */

        function init_page(title, title_sub, scfg) {
            json_nav = scfg.nav || 0;
            json_curr_url = scfg.curr_url || json_curr_url;

            var site_key = scfg.site_key || '';
            var zqun_key = scfg.zqun_key || '';
            var caiji_key = scfg.caiji_key || '';

            window.location.hash = encodeURIComponent(json_curr_url + ',' + menukey);
            document.title = title_sub + ' - ' + title + ' - 管理中心 - {$site_name}';
            var admincpnav = $('admincpnav');
            if (admincpnav) {
                admincpnav.innerHTML = title + '&nbsp;&raquo;&nbsp;' + title_sub;
            }
            document.getElementById('span_zqun').style.display = 'none';
            document.getElementById('span_site').style.display = 'none';
            document.getElementById('span_caiji').style.display = 'none';
            if (json_nav && (json_nav == 10)) {
                site_init(zqun_key, site_key);
            } else if (json_nav && (json_nav == 20)) {
                caiji_init(caiji_key);
            }
        }

        function site_init(zqun_key, site_key) {
            document.getElementById('span_zqun').style.display = '';
            var select = document.getElementById('select_zqun');
            while (select.length > 0) {
                select.remove(select.options[0]);
            }
            var option = document.createElement('option');
            option.text = "选择站群...";
            option.value = "0";
            select.options.add(option);

            var data = scfg['zqun'];
            for (var key in data) {
                var coop_name = data[key];
                option = document.createElement('option');
                option.text = '[' + key + ']' + coop_name;
                option.value = key;
                if (zqun_key && zqun_key == key) {
                    option.selected = true;
                }
                select.options.add(option);
            }
            site_zqun_change(zqun_key, site_key);
        }

        function site_zqun_change(zqun_key, site_key) {
            document.getElementById('span_site').style.display = '';
            var select = document.getElementById('select_site');
            while (select.length > 0) {
                select.remove(select.options[0]);
            }
            var option = document.createElement('option');
            option.text = "选择站点...";
            option.value = "0";
            select.options.add(option);

            var data = scfg['site'][zqun_key];
            for (var key in data) {
                var coop_name = data[key];
                option = document.createElement('option');
                option.text = '[' + key + '](' + coop_name['domain'] + ')' + coop_name['name'];
                option.value = coop_name['k'];
                if (site_key && site_key == coop_name['k']) {
                    option.selected = true;
                }
                select.options.add(option);
            }
        }

        function site_change(site_key) {
            var zqun_key = document.getElementById('select_zqun').value;
            if (site_key) {
                document.getElementById('main').src = '/select_set.html?<?php echo purview::adm_zqun_tag?>=' + zqun_key + '&<?php echo purview::adm_site_tag?>=' + site_key + '&uri=' + json_curr_url;
            }
        }

        function caiji_init(caiji_key) {
            document.getElementById('span_caiji').style.display = '';
            var select = document.getElementById('select_caiji');
            while (select.length > 0) {
                select.remove(select.options[0]);
            }
            var option = document.createElement('option');
            option.text = "选择资料库...";
            option.value = "0";
            select.options.add(option);

            var data = scfg_caiji;
            for (var key in data) {
                var _tmp_name = data[key];
                option = document.createElement('option');
                option.text = '[' + key + ']' + _tmp_name['name'];
                option.value = key;
                if (caiji_key && caiji_key == key) {
                    option.selected = true;
                }
                select.options.add(option);
            }
        }

        function caiji_change(caiji_key) {
            document.getElementById('main').src = '/select_set.html?<?php echo purview::adm_caiji_tag?>=' + caiji_key + '&uri=' + json_curr_url;
        }

        // function init_coop(cid)
        // {
        // 	document.getElementById('span_coop').style.display = '';
        // 	var select_coop 	= document.getElementById('select_coop');
        // 	while(select_coop.length>0)
        // 	{
        // 		select_coop.remove(select_coop.options[0]);
        // 	}
        // 	var option	 = document.createElement('option');
        // 	option.text  = "选择平台...";
        // 	option.value = "0";
        // 	select_coop.options.add(option);
        //
        // 	var select_serv 	= document.getElementById('select_serv');
        // 	while(select_serv.length>0)
        // 	{
        // 		select_serv.remove(select_serv.options[0]);
        // 	}
        // 	var option	 = document.createElement('option');
        // 	option.text  = "选择服务器...";
        // 	option.value = "0";
        // 	select_serv.options.add(option);
        //
        // 	for(var coop_id in json_coop_list)
        // 	{
        // 		var coop_name	= json_coop_list[coop_id]+"["+coop_id+"]";
        // 		option		 	= document.createElement('option');
        // 		option.text  	= coop_name;
        // 		option.value 	= coop_id;
        // 		if(cid && cid == coop_id)
        // 		{
        // 			option.selected = true;
        // 		}
        // 		select_coop.options.add(option);
        // 	};
        // }
        //
        // function init_coop_serv_list(cid,sid)
        // {
        // 	if(json_nav >= 2)
        // 	{
        // 		document.getElementById('span_serv').style.display = '';
        // 		var select_serv  = document.getElementById('select_serv');
        // 		while(select_serv.length>0)
        // 		{
        // 			select_serv.remove(select_serv.options[0]);
        // 		}
        // 		var option		 = document.createElement('option');
        // 		option.text  = "选择服务器...";
        // 		option.value = "0";
        // 		select_serv.options.add(option);
        //
        // 		for(var sid_id in json_coop_serv_list[cid])
        // 		{
        // 			var coop_name	= json_coop_serv_list[cid][sid_id];
        // 			option		 	= document.createElement('option');
        // 			option.text  	= coop_name;
        // 			option.value 	= sid_id;
        // 			if(sid && sid == sid_id)
        // 			{
        // 				option.selected = true;
        // 			}
        // 			select_serv.options.add(option);
        // 		};
        // 	}
        // 	else
        // 	{
        // 		change_coop(cid);
        // 	}
        // }
        // function change_coop(cid)
        // {
        // 	if(cid)
        // 	{
        // 		document.getElementById('main').src = '/select_set.html?cid='+cid+'&uri='+json_curr_url;
        // 	}
        // }
        // function change_coop_serv_list(sid)
        // {
        // 	var cid = document.getElementById('select_coop').value;
        // 	if(cid && sid)
        // 	{
        // 		document.getElementById('main').src = '/select_set.html?cid='+cid+'&sid='+sid+'&uri='+json_curr_url;
        // 	}
        // }
    </script>
    <style type="text/css">

        /*注释内容*/


        /*注释

        内容*/

        .logo_bg {
            background: url("{$static}{$site_logo_dir}logo.png") no-repeat 15px 13px;
        }

        .logo_bg:hover {
            background-image: url("{$static}{$site_logo_dir}logo_hover.png");
        }
    </style>
</head>
<body style="margin: 0px" scroll="no">
<div id="append_parent"></div>
<table id="frametable" cellpadding="0" cellspacing="0" width="100%" height="100%">
    <tr>
        <td colspan="2" height="90">
            <div class="mainhd">
                <a href="<?php echo config::url_page('/') ?>" class="logo logo_bg"
                   target="_blank">《{$site_name}》管理中心</a>
                <div class="uinfo" id="frameuinfo">
                    <p>
                        你好, <?php echo adm::$purview->purview_group[adm::$auth->session_get(purview::session_type)]; ?>
                        <em><?php echo adm::$auth->session_get(purview::session_account); ?></em>
                        [<a href="/out.html" target="_top">退出</a>]
                    </p>
                    <p class="btnlink">
                        <a href="{$powered_studio_url}" target="_blank">官方网址</a>
                    </p>
                </div>
                <div class="navbg"></div>
                <?php
                $purview_uuid = 0;
                $purview = adm::$purview->purview;
                $purview_keys = array_keys($purview);
                ?>
                <div class="nav">
                    <ul id="topmenu">
                        <?php foreach ($purview as $key1 => $data1) { ?>
                            <li><em><a href="<?php echo $data1['default'] ?>" id="header_<?php echo $key1 ?>"
                                       hidefocus="true"
                                       onClick="toggleMenu('<?php echo $key1 ?>', '<?php echo $data1['default'] ?>');doane(event);"><?php echo $data1['name'] ?></a></em>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="currentloca">
                        <p id="admincpnav"></p>
                    </div>
                    <div class="navbd"></div>
                    <div style="line-height:100%; position:absolute;right:275px;top:50px;">
                        <span id="span_caiji">
                            <b>资料库:</b>
                            <select id="select_caiji" onChange="caiji_change(this.value);">
                                <option value="1">选择资料库...</option>
                            </select>
                        </span>
                        <span id="span_zqun">
                            <b>站群:</b>
                            <select id="select_zqun" onChange="site_zqun_change(this.value,0)">
                                <option value="1">选择站群...</option>
                            </select>
                        </span>
                        <span id="span_site">
                            <b>站点:</b>
                            <select id="select_site" onChange="site_change(this.value)">
                                <option value="1">选择站点...</option>
                            </select>
                        </span>
                    </div>
                    <div class="sitemapbtn">
                        <a href="#" id="cpmap" onClick="showMap();return false;"><img
                                    src="{$static_g}adm/img/btn_map.gif" title="管理中心导航(ESC键)" width="46"
                                    height="18"/></a>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td valign="top" width="160" class="menutd">
            <div id="leftmenu" class="menu">
                <?php
                foreach ($purview as $key1 => $data1) { ?>
                    <ul id="menu_<?php echo $key1 ?>" style="display: none">
                        <?php
                        foreach ($data1['sub'] as $key2 => $data2) { ?>
                            <?php
                            if ($data2['url']) {
                                ?>
                                <li><a href="<?php echo $data2['url'] ?>" hidefocus="true" target="main"><em
                                                onClick="menuNewwin(this)"
                                                title="<?php echo $data2['name'] ?>"></em><?php echo $data2['name'] ?>
                                    </a></li>
                                <?php
                            } else {
                                $purview_uuid++;
                                ?>
                                <li class="s">
                                    <div class="lsub desc" subid="Masb<?php echo $purview_uuid ?>">
                                        <div onClick="lsub('Masb<?php echo $purview_uuid ?>', this.parentNode)"><?php echo $data2['name'] ?></div>
                                        <ol style="display:" id="Masb<?php echo $purview_uuid ?>">
                                            <?php
                                            foreach ($data2['data'] as $key3 => $data3) {
                                                if ($data3) {
                                                    ?>
                                                    <li><a href="<?php echo $data3['url'] ?>" hidefocus="true"
                                                           target="main"><em onClick="menuNewwin(this)"
                                                                             title="<?php echo $data3['name'] ?>"></em><?php echo $data3['name'] ?>
                                                        </a></li>
                                                    <?php
                                                }
                                            } ?>
                                        </ol>
                                    </div>
                                </li>
                            <?php } // end if
                            ?>
                        <?php } // end foreach
                        ?>
                    </ul>
                <?php } ?>
            </div>
        </td>
        <td valign="top" width="100%" class="mask">
            <script type="text/javascript">
                window.document.write('<if' + 'rame src="' + nav_default + '" id="main" name="main" width="100%" height="100%" frameborder="0" scrolling="yes" style="overflow: visible;display:"></ifr' + 'ame>');
            </script>
        </td>
    </tr>
</table>
<div id="scrolllink" style="display: none">
    <span onClick="menuScroll(1)"><img src="{$static_g}adm/img/scrollu.gif"></span>
    <span onClick="menuScroll(2)"><img src="{$static_g}adm/img/scrolld.gif"></span>
</div>
<div class="copyright">
    <p>Powered by <a href="{$powered_studio_url}" target="_blank">{$powered_studio_name}</a></p>
    <p>&copy;2010-<?php echo date('Y') ?> <a href="{$powered_corp_url}" target="_blank">{$powered_corp_name_mini}</a>
        Inc.</p>
</div>
<div id="cpmap_menu" class="custom" style="display: none">
    <div class="cmain" id="cmain"></div>
    <div class="cfixbd"></div>
</div>
<script type="text/JavaScript">
    function switchheader(key) {
        if (!key || !$('header_' + key)) {
            return;
        }
        ;
        for (var k in top.headers) {
            if ($('menu_' + headers[k])) {
                $('menu_' + headers[k]).style.display = headers[k] == key ? '' : 'none';
            }
        }
        ;
        var lis = $('topmenu').getElementsByTagName('li');
        for (var i = 0; i < lis.length; i++) {
            if (lis[i].className == 'navon') lis[i].className = '';
        }
        ;
        $('header_' + key).parentNode.parentNode.className = 'navon';
    }

    var headerST = null;

    function previewheader(key) {
        if (key) {
            headerST = setTimeout(function () {
                for (var k in top.headers) {
                    if ($('menu_' + headers[k])) {
                        $('menu_' + headers[k]).style.display = headers[k] == key ? '' : 'none';
                    }
                }
                var hrefs = $('menu_' + key).getElementsByTagName('a');
                for (var j = 0; j < hrefs.length; j++) {
                    hrefs[j].className = '';
                }
            }, 1000);
        }
        else {
            clearTimeout(headerST);
        }
    }

    function toggleMenu(key, url) {
        menukey = key;
        switchheader(key);
        if (url) {
            parent.main.location = url;
            var hrefs = $('menu_' + key).getElementsByTagName('a');
            for (var j = 0; j < hrefs.length; j++) {
                hrefs[j].className = j == (key == 'plugin' ? 1 : 0) ? 'tabon' : '';
            }
        }
        setMenuScroll();
    }

    function setMenuScroll() {
        $('frametable').style.width = document.body.offsetWidth < 1000 ? '1000px' : '100%';
        var obj = $('menu_' + menukey);
        if (!obj) {
            return;
        }
        var scrollh = document.body.offsetHeight - 160;
        obj.style.overflow = 'visible';
        obj.style.height = '';
        $('scrolllink').style.display = 'none';
        if (obj.offsetHeight + 150 > document.body.offsetHeight && scrollh > 0) {
            obj.style.overflow = 'hidden';
            obj.style.height = scrollh + 'px';
            $('scrolllink').style.display = '';
        }
    }

    function resizeHeadermenu() {
        var lis = $('topmenu').getElementsByTagName('li');

        var maxsize = $('frameuinfo').offsetLeft - 160, widths = 0, moi = -1, mof = '';
        if ($('menu_mof')) {
            $('topmenu').removeChild($('menu_mof'));
        }
        if ($('menu_mof_menu')) {
            $('append_parent').removeChild($('menu_mof_menu'));
        }
        for (var i = 0; i < lis.length; i++) {
            widths += lis[i].offsetWidth;
            if (widths > maxsize) {
                lis[i].style.visibility = 'hidden';
                var sobj = lis[i].childNodes[0].childNodes[0];
                if (sobj) {
                    mof += '<a href="' + sobj.getAttribute('href') + '" onclick="$(\'' + sobj.id + '\').onclick()">&rsaquo; ' + sobj.innerHTML + '</a><br style="clear:both" />';
                }
            }
            else {
                lis[i].style.visibility = 'visible';
            }
        }
        if (mof) {
            for (var i = 0; i < lis.length; i++) {
                if (lis[i].style.visibility == 'hidden') {
                    moi = i;
                    break;
                }
            }
            ;
            mofli = document.createElement('li');
            mofli.innerHTML = '<em><a href="javascript:;">&raquo;</a></em>';
            mofli.onmouseover = function () {
                showMenu({'ctrlid': 'menu_mof', 'pos': '43'});
            };
            mofli.id = 'menu_mof';

            $('topmenu').insertBefore(mofli, lis[moi]);
            mofmli = document.createElement('li');
            mofmli.className = 'popupmenu_popup';
            mofmli.style.width = '150px';
            mofmli.innerHTML = mof;
            mofmli.id = 'menu_mof_menu';
            mofmli.style.display = 'none';
            $('append_parent').appendChild(mofmli);
        }
    }

    function menuScroll(op, e) {
        var obj = $('menu_' + menukey);
        var scrollh = document.body.offsetHeight - 160;
        if (op == 1) {
            obj.scrollTop = obj.scrollTop - scrollh;
        }
        else if (op == 2) {
            obj.scrollTop = obj.scrollTop + scrollh;
        }
        else if (op == 3) {
            if (!e) {
                e = window.event;
            }
            if (e.wheelDelta <= 0 || e.detail > 0) {
                obj.scrollTop = obj.scrollTop + 20;
            }
            else {
                obj.scrollTop = obj.scrollTop - 20;
            }
        }
    }

    function menuNewwin(obj) {
        window.open(obj.parentNode.href);
        doane();
    }

    function initCpMenus(menuContainerid) {
        var key = '', lasttabon1 = null, lasttabon2 = null, hrefs = $(menuContainerid).getElementsByTagName('a');
        for (var i = 0; i < hrefs.length; i++) {
            if (menuContainerid == 'leftmenu' && 'action=index'.indexOf(hrefs[i].href.substr(hrefs[i].href.indexOf(admincpfilename + '?') + admincpfilename.length + 1)) != -1) {
                if (lasttabon1) {
                    lasttabon1.className = '';
                }
                if (hrefs[i].parentNode.parentNode.tagName == 'OL') {
                    hrefs[i].parentNode.parentNode.style.display = '';
                    hrefs[i].parentNode.parentNode.parentNode.className = 'lsub desc';
                    key = hrefs[i].parentNode.parentNode.parentNode.parentNode.parentNode.id.substr(5);
                }
                else {
                    key = hrefs[i].parentNode.parentNode.id.substr(5);
                }
                hrefs[i].className = 'tabon';
                lasttabon1 = hrefs[i];
            }
            if (!hrefs[i].getAttribute('ajaxtarget')) hrefs[i].onclick = function () {
                if (menuContainerid != 'custommenu') {
                    var lis = $(menuContainerid).getElementsByTagName('li');
                    for (var k = 0; k < lis.length; k++) {
                        if (lis[k].firstChild && lis[k].firstChild.className != 'menulink') {
                            if (lis[k].firstChild.tagName != 'DIV') {
                                lis[k].firstChild.className = '';
                            }
                            else {
                                var subid = lis[k].firstChild.getAttribute('sid');
                                if (subid) {
                                    var sublis = $(subid).getElementsByTagName('li');
                                    for (var ki = 0; ki < sublis.length; ki++) {
                                        if (sublis[ki].firstChild && sublis[ki].firstChild.className != 'menulink') {
                                            sublis[ki].firstChild.className = '';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (this.className == '') this.className = menuContainerid == 'leftmenu' ? 'tabon' : '';
                }
                if (menuContainerid != 'leftmenu') {
                    var hk, currentkey;
                    var leftmenus = $('leftmenu').getElementsByTagName('a');
                    for (var j = 0; j < leftmenus.length; j++) {
                        if (leftmenus[j].parentNode.parentNode.tagName == 'OL') {
                            hk = leftmenus[j].parentNode.parentNode.parentNode.parentNode.parentNode.id.substr(5);
                        }
                        else {
                            hk = leftmenus[j].parentNode.parentNode.id.substr(5);
                        }
                        if (this.href.indexOf(leftmenus[j].href) != -1) {
                            if (lasttabon2) {
                                lasttabon2.className = '';
                            }
                            leftmenus[j].className = 'tabon';
                            if (leftmenus[j].parentNode.parentNode.tagName == 'OL') {
                                leftmenus[j].parentNode.parentNode.style.display = '';
                                leftmenus[j].parentNode.parentNode.parentNode.className = 'lsub desc';
                            }
                            lasttabon2 = leftmenus[j];
                            if (hk != '<?php echo $adm_purview_default;?>') currentkey = hk;
                        }
                        else {
                            leftmenus[j].className = '';
                        }
                    }
                    if (currentkey) toggleMenu(currentkey);
                    hideMenu();
                }
            }
        }
        return key;
    }

    function lsub(id, obj) {
        display(id);
        obj.className = obj.className != 'lsub' ? 'lsub' : 'lsub desc';
        if (obj.className != 'lsub') {
            setcookie('cpmenu_' + id, '');
        }
        else {
            setcookie('cpmenu_' + id, 1, 31536000);
        }
        setMenuScroll();
    }

    function initCpMap() {
        var ul, hrefs, s = '', count = 0;
        for (var k in headers) {
            s += '<td valign="top"><ul class="cmblock"><li><h4>' + $('header_' + headers[k]).innerHTML + '</h4></li>';
            ul = $('menu_' + headers[k]);
            if (!ul) {
                continue;
            }
            hrefs = ul.getElementsByTagName('a');
            for (var i = 0; i < hrefs.length; i++) {
                s += '<li><a href="' + hrefs[i].href + '" target="' + hrefs[i].target + '" k="' + headers[k] + '">' + hrefs[i].innerHTML + '</a></li>';
            }
            s += '<li></li></ul></td>';
            count++;
        }
        var width = (count > 11 ? 11 : count) * 80;
        s = '<div class="cnote" style="width:' + width + 'px"><span class="right"><a href="#" class="flbc" onclick="hideMenu();return false;"></a></span><h3>管理中心导航</h3></div>' +
            '<div class="cmlist" style="width:' + width + 'px"><table id="mapmenu" cellspacing="0" cellpadding="0" ><tr>' + s +
            '</tr></table></div>';
        $('cmain').innerHTML = s;
        $('cmain').style.width = (width > 1000 ? 1000 : width) + 'px';
    }


    function showMap() {
        showMenu({'ctrlid': 'cpmap', 'evt': 'click', 'duration': 3, 'pos': '00'});
    }

    function resetEscAndF5(e) {
        e = e ? e : window.event;
        actualCode = e.keyCode ? e.keyCode : e.charCode;
        if (actualCode == 27) {
            if ($('cpmap_menu').style.display == 'none') {
                showMap();
            }
            else {
                hideMenu();
            }
        }
        if (actualCode == 116 && parent.main) {
            parent.main.location.reload();
            if (document.all) {
                e.keyCode = 0;
                e.returnValue = false;
            }
            else {
                e.cancelBubble = true;
                e.preventDefault();
            }
        }
    }

    _attachEvent(document.documentElement, 'keydown', resetEscAndF5);
    _attachEvent(window, 'resize', setMenuScroll, document);
    _attachEvent(window, 'resize', resizeHeadermenu, document);
    if (BROWSER.ie) {
        $('leftmenu').onmousewheel = function (e) {
            menuScroll(3, e)
        };
    }
    else {
        $('leftmenu').addEventListener("DOMMouseScroll", function (e) {
            menuScroll(3, e)
        }, false);
    }

    var cookiepre = 'adm_',
        cookiedomain = '',
        cookiepath = '/';
    var headers = <?php echo json_encode($purview_keys)?>,
        admincpfilename = '/';

    resizeHeadermenu();

    initCpMap();
    initCpMenus('mapmenu');
    var cmcache = false;
    var header_key = initCpMenus('leftmenu');
    toggleMenu(nav_default_key);
</script>
</body>
</html>