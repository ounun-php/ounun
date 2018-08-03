// $webp
if(typeof $webp != "undefined"  && $webp['replace']){
    $webp.replace();
}
// 百度提交
(function(){
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
})();
// 百度统计
if(typeof $sc != "undefined"  && $sc['stat_baidu'])
{
    var _hmt = _hmt || [];
    (function ()
    {
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

// qrcodeFloat
if(typeof qrcode_float != "undefined" && qrcode_float){
    try{
        qrcode_float({width:120,height:120});
    }catch (e) { }
}


