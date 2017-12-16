<html ng-app="routerApp" class="ng-scope">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<title>融宝支付</title>
<link rel="stylesheet" href="css/common.css">
</head>
<body ng="">
    <!-- uiView: undefined --><ui-view class="ng-scope"><div class="header ng-scope" style="background:#ff5001">
    <a href="javascript:history.go(-1);"><i class="iconfont back" style="color:#fff"></i></a>
    <h1 class="logo ng-binding" style="color:#fff">快捷支付</h1>
    <a href="about.html"><i class="iconfont about" style="color:#fff"></i></a>
</div>
<!-- 交易处理中显示! -->
<div class="result fail">
    <h3>
        <span class="ng-binding">支付失败</span>
    </h3>
    <p id="errorMsg" class="ng-binding">卡余额不足，请使用其它银行卡。[6001]</p>
</div>
<div class="warp ng-scope">
    <a ng-show="hidden_btn!=''" id="back_href" href="http://www.shadouxing.net/app/wapllpay/return_url.php" class="btn color" style="background:#ff5001;color:#fff;display:none;">返回商户</a>
    <a ng-show="hidden_btn==''" id="back_href" href="javascript:history.go(-1);" class="btn color ng-hide" style="background:#ff5001;color:#fff">返回商户</a>
    <br>
    <a href="tel:4000188888" class="btn" style="background:#ff5001;color:#fff">客服热线：400-018-8888</a>
</div>
</ui-view>

</body></html>