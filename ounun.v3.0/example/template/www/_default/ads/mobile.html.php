<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            background-color: #2c3035;
        }

        #weixinStyle {
            width: 100%;
            display: none;
            text-align: center;
            font-size: 46px;
            padding-top: 50px;
            color: white;
        }
    </style>
</head>

<body>
<div id="weixinStyle">
    <p>
        <img src="{$static_g}wap/live_weixin.png" alt="提示：请点击右上角，选择“用浏览器打开"/>
    </p>
    提示：请点击右上角，选择“用浏览器打开
</div>

<script type="text/javascript">
    var weixin = document.getElementById("weixinStyle");
    window.onload = function () {
        if (isWeixin() || isQQ()) {
            weixin.style.display = "block";
        } else {
            location.replace("/api/mobile_go/?ref=<?php echo urlencode($_GET['ref'])?>&t=" + Math.round(Math.random() * 10));
        }
    }

    function isWeixin() {
        var WxObj = window.navigator.userAgent.toLowerCase();
        if (WxObj.match(/microMessenger/i) == 'micromessenger') {
            return true;
        } else {
            return false;
        }
    }

    function isQQ() {
        var ua = navigator.userAgent;
        if (ua.match(/QQ\//i) == 'QQ/') {
            return true;
        } else {
            return false;
        }
    }
</script>
</body>
</html>