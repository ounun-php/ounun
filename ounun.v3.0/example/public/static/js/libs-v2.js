var $sc   = $sc || {};
var $__m_g_com = $__m_g_com || {};
// 常用JS库
var $webp = {
    /**
     * 是否支持 webp
     * @returns {boolean} */
    check:function (){
        if(this._check === false || this._check === true)
        {
            return this._check;
        }else
        {
            if($cookie('_sp_webp') == 'ok')
            {
                this._check = true;
            }else
            {
                try
                {
                    this._check = (document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') == 0);
                    $cookie('_sp_webp','ok');
                }catch(err)
                {
                    this._check = false;
                }
            }
            return this._check;
        }
    },
    _check:-1,

    /**
     * 全站 对不支持的 图片src进行替换
     */
    replace:function()
    {
        if(!this.check())
        {
            $('img').each(function() {
                var src = $(this).attr('src');
                if(typeof src != 'undefined')  { $(this).attr('src',   src.replace(/(\!.*)w$/, '$1'));}
                //针对用了懒加载的情况
                var osrc = $(this).attr('osrc');
                if(typeof osrc != 'undefined') { $(this).attr('osrc', osrc.replace(/(\!.*)w$/, '$1'));}
            })
        }
    },

    /**
     * 对不支持的 图片src进行替换
     * @param url
     * @returns {string}
     */
    src2jpg:function(url)
    {
        try{
            if(!this.check()) {
                return url.replace(/(\!.*)w$/, '$1');
            }else {
                return url;
            }
        }catch(err) {
            return url;
        }
    }
};
var $url    = {
    time : function() {
        return Math.floor((new Date()).getTime() / 1000);
    },
    rand : function(begin, end) {
        if (typeof begin != 'undefined') {
            end = end ? end : 2147483648;
            return Math.floor(Math.random() * (end - begin) + begin);
        } else {
            return (new Date()).getTime();
        }
    },
    get : function(name) {
        var get = [ location.search, location.hash ].join('&');
        var start = get.indexOf(name + '=');
        if (start == -1)
            return '';
        var len = start + name.length + 1;
        var end = get.indexOf('&', len);
        if (end == -1)
            end = get.length;
        return decodeURIComponent(get.substring(len, end));
    },
    encode : function(datas, url) {
        var rs = [];
        for ( var k in datas)
            rs.push(k + '=' + encodeURIComponent(o[k]));
        return (url ? url + (url.indexOf('?') == -1 ? '?' : '&') : '') + rs.join('&');
    },
    decode : function(str) {
        str = (str.indexOf('?') == -1 ? str : str.split("?")[1]).split("&");
        var rs = {}, a, c = str.length;
        for ( var i = 0; i < c; i++) {
            a = str[i].split("=");
            rs[a[0]] = decodeURIComponent(a[1]);
        }
        return rs;
    },
    favorite: function (sTitle,sURL) {
        try {
            window.external.addFavorite(sURL, sTitle);
        } catch (e) {
            try {
                window.sidebar.addPanel(sTitle, sURL, "");
            } catch (e) {
                alert("加入收藏失败，请使用Ctrl+D进行添加");
            }
        }
    }
};
var $cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set cookie
        options         = options         || {};
        options.expires = options.expires || 1;
        options.path    = options.path    || "\/";
        options.domain  = options.domain  || document.domain;
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toGMTString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime()
                    + (options.expires * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toGMTString(); // use expires
            // attribute,
            // max-age is not
            // supported by IE
        }
        var path   = options.path   ? '; path=' + options.path : '';
        var domain = options.domain ? '; domain=' + options.domain : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [ name, '=', encodeURIComponent(value), expires,
            path, domain, secure ].join('');
    } else { // only name given, get cookie
        var cookieValue = '';
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for ( var i = 0; i < cookies.length; i++) {
                var cookie = typeof jQuery != 'undefined' ? jQuery.trim(cookies[i]) : cookies[i].trim();
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
var $libs = {
    uaredirect:function (murl){
        try {
            if(document.getElementById("bdmark") != null){
                return;
            }
            var urlhash = window.location.hash;
            if (!urlhash.match("fromapp")){
                if ((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i))) {
                    location.replace(murl);
                }
            }
        } catch(err){}
    },
    adwrite:function (mode,size){
        var str = 'mode:'+mode+(size?' size:'+size:'');
        if($__m_g_com && $__m_g_com[mode]){
            str = $__m_g_com[mode];
        }
        document.write(str);
    },
    search:function (obj,input,len){
        input = input || 'q';
        len   = len   || 2;
        if(obj && obj[input])
        {
            return obj[input].value && obj[input].value.length && obj[input].value.length > len ? true :false;
        }
        return false;
    },
    qrcode:function (config){
        config           = config || {};
        config.width     = config.width || 120;
        config.height    = config.height || 120;
        config.maxScreen = config.maxScreen || 980;
        config.message   = config.message || "扫码手机访问";

        try
        {
            var mobile_url2 = $url.encode({"f":"qr"},$sc.url_wap);
        }catch (e)
        {
            var mobile_url2 = document.location.href;
        }
        // qrcode
        document.write('<style type="text/css">#qrocdeContainer{position: fixed;z-index: 999;bottom: 0;left: 0;}#qrcodeMessage{background:#fff;width:'+config.width+'px;padding:8px;text-align:center;float:right;}@media screen and (max-width: '+config.maxScreen+'px) {#qrocdeContainer{display:none;}}</style>');
        document.write('<div id="qrocdeContainer"><div id="qrcodeMessage"><div id="qrcode"></div><div>'+config.message+'</div></div></div>');
        window.jQuery('#qrcode').qrcode({width:config.width,height:config.height,correctLevel:0,text:mobile_url2});
    },
    // $webp
    eve_webp:function () {
        if(typeof $webp != "undefined"  && $webp['replace']){
            $webp.replace();
        }
    },
    // go_mobile
    eve_gomobile:function (){
        if(typeof $sc != "undefined"  && $sc['url_wap']){
            this.uaredirect($sc.url_wap);
        }
    },
    // 百度提交
    eve_pushbaidu:function (){
        var bp = document.createElement('script');
        var curProtocol = window.location.protocol.split(':')[0];
        if (curProtocol === 'https') {
            bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
        }
        else {
            bp.src = 'http://push.zhanzhang.baidu.com/push.js';
        }
        var s = document.getElementsByTagName("script")[0];
        s.parentNode.insertBefore(bp, s);
    },
    // 统计
    eve_stat:function (){
        // 百度统计
        if(typeof $sc != "undefined"  && $sc['stat_baidu']){
            var _hmt = _hmt || [];
            (function (){
                var hm = document.createElement("script");
                hm.src = "https://hm.baidu.com/hm.js?"+$sc['stat_baidu'];
                var s = document.getElementsByTagName("script")[0];
                s.parentNode.insertBefore(hm, s);
            })();
        }
        // cnzz
        if(typeof $sc != "undefined"  && $sc['stat_cnzz']){
            var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");
            document.write(unescape("%3Cspan id='cnzz_stat_icon_"+$sc['stat_cnzz']+"'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s11.cnzz.com/stat.php%3Fid%3D"+$sc['stat_cnzz']+"' type='text/javascript'%3E%3C/script%3E"));
        }
    },
    // 显示二维码
    eve_qrcode:function (){
        // qrcodeFloat
        if(typeof this.qrcode != "undefined" && this.qrcode){
            try{
                this.qrcode({width:120,height:120});
            }catch (e) { }
        }
    }
}




