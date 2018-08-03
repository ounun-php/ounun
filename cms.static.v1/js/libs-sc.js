function uaredirect(murl){
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
}

if(typeof $sc != "undefined"  && $sc['url_mobile']){
    uaredirect($sc.url_mobile);
}
// 常用JS库
var $webp = {
    /**
     * 是否支持 webp
     * @returns {boolean} */
    check:function (){
        if(this._check === false || this._check === true)
        {
            return this._check;
        }else {
            if($cookie('_sp_webp') == 'ok'){
                this._check = true;
            }else{
                try{
                    this._check = (document.createElement('canvas').toDataURL('image/webp').indexOf('data:image/webp') == 0);
                    $cookie('_sp_webp','ok');
                }catch(err) {
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
            if(!this.check())
            {
                return url.replace(/(\!.*)w$/, '$1');
            }else
            {
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
        return (url ? url + (url.indexOf('?') == -1 ? '?' : '&') : '')
            + rs.join('&');
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

function qrcode_float(config){
    if(config==null){config={};}
    if(config.width==null){config.width=100;config.height=100;}
    if(config.height==null){config.height=100;}
    if(config.maxScreen==null){config.maxScreen=980;}
    if(config.message==null){config.message="扫码手机访问";}

    try
    {
        var mobile_url2 = $sc.url_mobile+'?f=qr';
    }catch (e)
    {
        var mobile_url2 = document.location.href;
    }
    //qrcode
    document.write('<style type="text/css">#qrocdeContainer{position: fixed;z-index: 999;bottom: 0;left: 0;width: 100%;width:100%;}#qrcodeMessage{background:#fff;width:'+config.width+'px;padding:8px;text-align:center;float:right;}@media screen and (max-width: '+config.maxScreen+'px) {#qrocdeContainer{display:none;}}</style>');
    document.write('<div id="qrocdeContainer"><div id="qrcodeMessage"><div id="qrcode"></div><div>'+config.message+'</div></div></div>');
    $(function(){
        var content=mobile_url2;
        $('#qrcode').qrcode({width:config.width,height:config.height,correctLevel:0,text:content});
    });
}

