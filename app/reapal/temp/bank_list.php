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
<!-- uiView: undefined --><ui-view class="ng-scope"><div ng-controller="CardBinQueryCtrl" class="ng-scope">
    <div class="header" style="background:#ff5001">
        <a href="" ng-click="openExit()"><i class="iconfont back" style="color:#fff"></i></a>
        <h1 class="logo ng-binding" style="color:#fff">快捷支付</h1>
        <a href="" ng-click="openAbout()"><i class="iconfont about" style="color:#fff"></i></a>
    </div>
    <!--<div id="mask" ng-show="loading" class="ng-hide"><span class="refreshing-loader">Loading…</span></div>-->
    <div class="info">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody><tr>
                <td class="ng-binding"></td>
                <td style="text-align: right">￥<span class="money ng-binding">1.00</span></td>
            </tr>
            </tbody></table>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 5px 0;" class="merchant">
            <tbody><tr>
                <td style="width:100px;vertical-align:top" valign="top" class="ng-binding">收款商户:<span ng-show="cache_data.planurl!=''" class="ng-hide"></span></td>
                <td><p class="ng-binding">北京啥都行生态科技有限公司</p></td>
            </tr>
            </tbody></table>
    </div>
    <form role="form" name="myForm" ng-submit="nextPay()" novalidate="" class="ng-pristine ng-valid">
        <div class="form_warp">
            <ul>
                <!--
                <li class="txt ng-hide" ng-show="acct_name!=''">请绑定持卡人本人的银行卡</li>
                <li class="cardholder ng-hide" ng-show="acct_name!=''"><span>持卡人</span>
                -->
                    <i class="iconfont" id="show_info"></i> <strong class="ng-binding"></strong></li>
                <li>
                    <div class="form_item">
                        <label for="name">卡号</label><input type="tel" placeholder="请输入本人银行卡号" maxlength="20" ng-model="cardno" class="ng-pristine ng-untouched ng-valid ng-valid-maxlength">
                    </div>
                    <p class="err_tips" style="display: none">
                        <i class="iconfont">㑕</i><span>银行卡不能为空</span>
                    </p>
                </li>
                <li style="text-align: right"><a href="" ng-click="openSupportBankList()" class="support_bank ng-binding">支持银行160家&gt;&gt;</a></li>
                <li>
                    <button class="btn gray" style="background:#fff;color:#999" type="submit" ng-disabled="btnclass!=''" disabled="disabled">下一步</button>
                </li>
            </ul>
        </div>
    </form>
    <div class="footer">
        <span>本服务由连连支付提供</span>
    </div>
    <div class="dialog_mask" id="dialog_mask" style="display: none">
        <div id="modal-dialog">
            <div class="modal-header">提示</div>
            <div class="modal-body" id="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="alert_btn confirm" style="background:#ff5001;color:#fff" onclick="document.getElementById('dialog_mask').style.display='none';">确定</button>
            </div>
        </div>
    </div>
    <div class="dialog_mask" id="exit_dialog" style="display: none">
        <div id="modal-dialog">
            <div class="modal-header">提示</div>
            <div class="modal-body">确认退出支付?</div>
            <div class="modal-footer">
                <button type="button" class="alert_btn cancel" ng-click="clearStorage()">确定</button>
                <button type="button" id="cancel" class="alert_btn confirm" onclick="document.getElementById('exit_dialog').style.display='none';">取消</button>
            </div>
        </div>
    </div>
</div>
</ui-view>

</body></html>