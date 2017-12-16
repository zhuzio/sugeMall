<html ng-app="routerApp" class="ng-scope">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<title>融宝支付</title>
<script src="//cdn.bootcss.com/jquery/3.1.1/jquery.min.js"></script>
<link rel="stylesheet" href="temp/css/common.css">
</head>
<body ng="">
    <!-- uiView: undefined --><ui-view class="ng-scope"><div ng-controller="BankCardPayCtrl" class="ng-scope">
    <div class="header" style="background:#ff5001">
        <a href="" ng-click="openExit()"><i class="iconfont back" style="color:#fff"></i></a>
        <h1 class="logo ng-binding" style="color:#fff">快捷支付</h1>
        <a href="" ng-click="openAbout()"><i class="iconfont about" style="color:#fff"></i></a>
    </div>
    <div id="mask" ng-show="loading" class="ng-hide" style="display:none;"><span class="refreshing-loader">Loading…</span></div>
    <!--<div id="mask" ng-show="loading" class="ng-hide"><span class="refreshing-loader">Loading…</span></div>-->
    <div class="info">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody><tr>
                <td class="ng-binding"></td>
                <td style="text-align: right">￥<span class="money ng-binding"><?php echo $order['cz_money'] ?></span></td>
            </tr>
        </tbody></table>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 5px 0;" class="merchant">
            <tbody><tr>
                <td style="width:100px;vertical-align:top" valign="top" class="ng-binding">收款商户:<span ng-show="cache_data.planurl!=''" class="ng-hide"></span></td>
                <td><p class="ng-binding">南阳苏格实业有限公司</p></td>
            </tr>
        </tbody></table>
    </div>
    <div class="form_warp" style="padding-bottom: 50px">
        
        <input type="hidden" name="payform" value="3" />
        <input type="hidden" id="bindid" name="bindid" value="<?php echo $bind_bank['id'];?>" />
        <input type="hidden" name="dingdan" id="dingdan" value="<?php echo $no_order ?>" />
        <input type="hidden" name="bank_name" id="bank_name" value='<?php echo $bank_name; ?>' />
        <input type="hidden" name="bank_num" id="bank_num" value='<?php echo $real_bank_num; ?>' />
        <input type="hidden" name="bank_type" id="bank_type" value='<?php echo $bank_type; ?>' />
        <input type="hidden" name="bind_id" id="bind_id" value='<?php echo $bind_bank['bind_id']; ?>' />
        <ul>
            <li>
                <p class="card_num">
                    <span>卡号</span> <strong class="ng-binding"><?php echo $bank_num;?></strong>
                </p>
                <p class="card_type ng-binding"><?php echo $bank_name; ?>  <?php if($bank_type == 0){echo '借记卡';}else if($bank_type == 1){echo '信用卡';} ?> </p>
            </li>
            <!--
            <li ng-show="elements_flag[1]=='1'" class="">
                <div class="form_item">
                    <label for="name" class="w70 ng-binding">姓名</label> <input type="text" id="real_name" placeholder="请输入持卡人真实姓名" ng-model="custname" ng-disabled="acct_name_enable?true:false" class="ml70 ng-pristine ng-untouched ng-valid ng-valid-maxlength">
                </div>
            </li>
            <li ng-show="elements_flag[2]=='1'" class="ng-hide">                
                <div class="form_item" style="display:none;">
                    <label for="name" class="w90">证件类型</label>
                   <input type="text" id="id_type" placeholder="请选择证件类型" class="ml90 ng-pristine ng-untouched ng-valid" ng-model="id_type" readonly="true">
                </div>
                <ul id="dropmenu" style="position:absolute;z-index:9999">
                    <li ng-click="select_idtype('身份证');">身份证</li>
                    <li ng-click="select_idtype('护照');">护照</li>
                    <li ng-click="select_idtype('军官证');">军官证</li>
                    <li ng-click="select_idtype('港澳居民来往内地通行证');">港澳居民来往内地通行证</li>
                    <li ng-click="select_idtype('台湾同胞来往内地通行证');">台湾同胞来往内地通行证</li>
                    <li ng-click="select_idtype('警官证');">警官证</li>
                    <li ng-click="select_idtype('其他证件');">其他证件</li>
                </ul>
            </li>
            <li ng-show="elements_flag[3]=='1'" class="">
                <div class="form_item">
                    <label for="name" class="w70 ng-binding">身份证</label> <input type="text" id="idno" placeholder="请输入持卡人身份证号码" class="ml70 ng-pristine ng-untouched ng-valid ng-valid-maxlength" ng-model="idno" ng-disabled="id_no_enable?true:false" maxlength="18">
                </div>
            </li>
            <li ng-show="elements_flag[6]=='1'" class="ng-hide" <?php if($bank_type == 0) { ?> style="display:none;"<?php } ?> >
                <div class="form_item">
                    <label for="name" class="w120">信用卡有效期</label> <input type="tel" placeholder="请输入MMYY" class="ml125 ng-pristine ng-untouched ng-valid ng-valid-maxlength" name="validate" id="validate" maxlength="4">
                </div>
            </li>
            <li ng-show="elements_flag[5]=='1'" class="ng-hide" <?php if($bank_type == 0) { ?> style="display:none;"<?php } ?> >
                <div class="form_item">
                    <label for="name" class="w70">CVV2</label> <input type="password" placeholder="请输入卡背面后三位" class="ml70 ng-pristine ng-untouched ng-valid ng-valid-maxlength" name="cvv2" id="cvv2" maxlength="3">
                </div>
            </li>
            <li ng-show="elements_flag[4]=='1'" class="">
                <div class="form_item">
                    <label for="name" class="w70">手机号</label> <input type="tel" id="mobile" placeholder="请输入银行预留手机号" class="ml70 ng-pristine ng-untouched ng-valid ng-valid-maxlength" maxlength="11" ng-model="bind_mob">
                </div>
            </li>
            -->
            <li ng-show="elements_flag[4]=='1'" class="">
                <div class="form_item">
                    <label for="name" class="w70">验证码</label> <input type="tel" id="code" placeholder="请输入短信验证码" class="ml70 veri_text ng-pristine ng-untouched ng-valid ng-valid-maxlength" maxlength="6" ng-model="verify_code">
                    <button class="btn veri" style="" type="button" id="sms" >获取</button>
                </div>
            </li>
            <li style="display:none;">
                <p class="agreement">
                    同意<a href="" ng-click="openTemplate()">《支付服务协议》</a>
                </p>
            </li>
            <!--
            <li class="cb-box" ng-show="cache_data.title_txt=='快捷支付'"><input type="checkbox" checked="" id="checked" name="isrecord" value="yes" ng-model="isrecord" class="ng-pristine ng-untouched ng-valid"><label for="checked">添加为常用卡</label>
            </li>
            -->
            <li>
                <button class="btn gray" id="pay_submit" style="background:#fff;color:#999" type="button"  disabled="disabled">下一步</button>
            </li>
        </ul>
        
    </div>
    <div class="dialog_mask" id="dialog_mask" style="display: none">
        <div id="modal-dialog">
                <div class="modal-header">提示</div>
                <div class="modal-body" id="modal-body"></div>
                <div class="modal-footer">
                    <!--
                    <button id="cancel" type="button" class="alert_btn cancel" style="display:none" onclick="document.getElementById('dialog_mask').style.display='none';">重新输入</button>
                    <button id="event" type="button" class="alert_btn confirm" style="background:#ff5001;color:#fff;display:none" ng-click="modifymobile()">更换手机号</button>
                    -->
                    <button id="confirm" type="button" class="alert_btn confirm" style="background:#ff5001;color:#fff;" onclick="document.getElementById('dialog_mask').style.display='none';">确定</button>
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
</div></ui-view>
<script>
$(function(){
    var loading = true;
    var countdown = 60;
    var second = "秒";
    var btnclass = "gray";
    var req_ok = false;


    $("#sms").on('click',function(){
        sendverify();
    });
    $("#code").on('keyup',function(){
        if($(this).val().length == 6){
            $("#pay_submit").removeClass('gray');
            $("#pay_submit").attr('style','background: #ff1010;color: #fff;');
            $("#pay_submit").removeAttr('disabled');
        }else{
            $("#pay_submit").addClass('gray');
            $("#pay_submit").attr('style','background: #fff;color: #999;');
            $("#pay_submit").attr('disabled','disabled');
        }
    });

    $("#pay_submit").on('click',function(){
        var code = $("#code").val();
        var dingdan = $("#dingdan").val();
        $.ajax({
            url : '/app/reapal/pay/payResult.php',
            type : 'POST',
            dataType : 'JSON',        
            data : {
                'order_no':dingdan,
                'check_code' : code,
            },
            beforeSend : function(){
                $("#mask").show();
            },
            success : function(res){
                $("#mask").hide();
                if(res.result_code == '0000'){                                        
                    alert(res.result_msg);
                    location.href="/wap";
                }else{
                    $("#modal-body").html(res.result_msg);
                    $("#dialog_mask").show();
                }
            }
        });
    });

    function sendverify() {               
        var dingdan = $("#dingdan").val();        
        $.ajax({            
            url : '/app/reapal/sms/reSendSmsResult.php',
            type : 'POST',
            dataType : 'JSON',
            data : {
                'order_no':dingdan,                 //订单号             
            },
            beforeSend : function(){
                $("#mask").show();
            },
            success : function(res){
                $("#mask").hide();
                if(res.result_code != '0000'){
                    $("#modal-body").html(res.result_msg);
                    $("#dialog_mask").show();                    
                    return;
                }else{
                    req_ok = true;
                    $("#modal-body").html('请输入短信验证码');
                    $("#dialog_mask").show();
                    $("#bind_id").val(res.bind_id);
                    loading = false;
                    countdown = 60;
                    second = "秒";
                    btnclass = "gray";
                    $("#sms").attr('disabled','disabled');
                    $('#sms').attr('style','');
                    $('#sms').addClass('gray');
                    var myTime2 = setInterval(function() {
                        countdown--;
                        if (countdown == 0) {
                            clearInterval(myTime2);
                            countdown = "重新发送";
                            btnclass = "";
                            second = "";
                            $("#sms").removeAttr('disabled');
                            $('#sms').attr('style','background: #ff1010;color: #fff;');
                            $('#sms').addClass('gray');
                        }
                        $('#sms').html(countdown);
                    }, 1000);
                    return;                    
                }
            }
        });
    };
});
</script>

</body></html>