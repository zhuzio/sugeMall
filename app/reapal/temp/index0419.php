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
<!-- uiView: undefined --><ui-view class="ng-scope"><div ng-controller="CardBinQueryCtrl" class="ng-scope">
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
                <td style="text-align: right">￥<span class="money ng-binding"><?php echo $order['cz_money']?></span></td>
            </tr>
            </tbody></table>
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 5px 0;" class="merchant">
            <tbody><tr>
                <td style="width:100px;vertical-align:top" valign="top" class="ng-binding">收款商户:<span ng-show="cache_data.planurl!=''" class="ng-hide"></span></td>
                <td><p class="ng-binding">南阳苏格实业有限公司</p></td>
            </tr>
            </tbody></table>
    </div>
    <form role="form" id="myForm" name="myForm" method="POST" action="/app/reapal/reapal.php" novalidate="" class="ng-pristine ng-valid">
        <input type="hidden" name="payform" value="<?php if($bind_bank){echo "3";}else {echo '1';}?>" />
        <input type="hidden" name="user_id" id="user_id" value="<?php echo $bind_bank['user_id']; ?>" />
        <input type="hidden" name="bindid" id="bindid" value="<?php echo $bind_bank['id'] ?>" />
        <input type="hidden" name="dingdan" id="dingdan" value="<?php echo $no_order ?>" />
        <input type="hidden" name="bank_name" id="bank_name" value='<?php echo $bind_bank['bank_name']; ?>' />
        <input type="hidden" name="bank_num" id="bank_num" value="<?php echo $bind_bank['bank_num'] ?>" />
        <input type='hidden' name="bank_type" id="bank_type" value="<?php echo $bind_bank['bank_type'] ?>" />
        <input type='hidden' name="bind_id" id="bind_id" value="<?php echo $bind_bank['bind_id'] ?>" />

        <div class="form_warp">
            <ul>
                <!--
                <li class="txt ng-hide" ng-show="acct_name!=''">请绑定持卡人本人的银行卡</li>
                <li class="cardholder ng-hide" ng-show="acct_name!=''"><span>持卡人</span>
                -->
                    <i class="iconfont" id="show_info"></i> <strong class="ng-binding"></strong></li>
                <li>
                    <div class="form_item">
                        <label for="name">卡号</label><input type="tel" id="card" value="<?php echo $bank_num; ?>" <?php if($bank_num!='') echo "readonly"; ?> <?php if($bank_num!=''){ echo "onclick='showDel()'";} ?> placeholder="请输入本人银行卡号" maxlength="22" name="cardno" class="ng-pristine ng-untouched ng-valid ng-valid-maxlength">
                    </div>
                    <p class="err_tips" style="display: none">
                        <i class="iconfont">错</i><span>银行卡不能为空</span>
                    </p>
                </li>
                <!--<li style="text-align: right"><a href="" ng-click="openSupportBankList()" class="support_bank ng-binding">支持银行160家&gt;&gt;</a></li>-->
                <li>
                    <button class="btn <?php if($bank_num ==''){ echo 'gray'; }?>" id="submit_btn" style="<?php if($bank_num ==''){ echo 'background:#fff;color:#999'; } ?>" type="button" <?php if($bank_num == ''){?> disabled="disabled" <?php } ?> >下一步</button>
                </li>
            </ul>
        </div>
    </form>
    <div class="footer">
        <span  style="text-indent: inherit;background:none;">本服务由融宝支付提供</span>
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
<script>
    $("#card").on('keyup',function(){
        if(this.value != ''){
            $("#submit_btn").removeClass('gray');
            $("#submit_btn").attr('style','background: #ff1010;color: #fff;');
            $("#submit_btn").removeAttr('disabled');
        }else{
            $("#submit_btn").addClass('gray');
            $("#submit_btn").attr('style','background: #fff;color: #999;');
            $("#submit_btn").attr('disabled','disabled');
        }
    })

    function showDel(){
        if(confirm('确定要解绑吗？')){
            bind_id = $("#bind_id").val();
            user_id = $("#user_id").val();
            $.ajax({
                url : '/app/reapal/canclebindcard/cannelCardResult.php',
                type : 'POST',
                dataType : 'JSON',
                data : {
                    'member_id' : user_id,
                    'bind_id' : bind_id
                },
                success:function(res){
                    if(res.result_code == '0000'){
                        alert('解绑成功');
                        $.ajax({
                            url : '/api/index.php?n=shop_center&f=canclebindcard',
                            type : 'POST',
                            dataType : 'JSON',
                            data : {'user_id':user_id,'bind_id':bind_id},
                            success:function(){
                                location.reload();
                            }
                        })                        
                    }else{
                        alert(res.result_msg);
                        location.reload();
                    }
                }
            });
        }
    }


    $("#submit_btn").on('click',function(){
        bindid = $("#bindid").val();
        if(bindid){
            $.ajax({
                url : '/app/reapal/bindcardportal/bindCardResult.php',
                type : 'POST',
                dataType : 'JSON',        
                data : {
                    'order_no':'<?php echo $no_order ?>',                 //订单号
                    'bind_id' : '<?php echo $bind_bank['bind_id'] ?>',               //银行卡号
                    'owner' : '<?php echo $bind_bank['real_name'] ?>',                //持卡人姓名
                    'cert_no' : '<?php echo $bind_bank['bank_num'] ?>',                   //证件号
                    'phone' : '<?php echo $bind_bank['mobile'] ?>',                   //手机号                    
                    'member_id' : '<?php echo $bind_bank['user_id'] ?>'
                },
                beforeSend : function(){
                    $("#mask").show();
                },
                success : function(res){
                    
                    if(res.result_code == '0000'){
                        $.ajax({
                            url : '/app/reapal/sms/reSendSmsResult.php',
                            type : 'POST',
                            dataType : 'JSON',
                            data : {
                                'order_no' : '<?php echo $no_order ?>'
                            },
                            success:function(r){
                                $("#mask").hide();
                                if(r.result_code == '0000'){                                    
                                    $("#myForm").submit();    
                                }else{
                                    $("#modal-body").html(res.result_msg);
                                    $("#dialog_mask").show();
                                }                                
                            }
                        });                        
                    }else{
                        $("#mask").hide();
                        $("#modal-body").html(res.result_msg);
                        $("#dialog_mask").show();
                    }
                }
            });
            
        }else{
            card_no = $("#card").val();            
            $.ajax({
                url : '/app/reapal/queryBankCard/queryBankCardResult.php',
                type : 'POST',
                dataType : 'JSON',
                data : {'card_no':card_no},
                beforeSend : function(){
                    $("#mask").show();
                },
                success : function(res){
                    $("#mask").hide();
                    if(res.result_code == '0000'){
                        $("#bank_name").val(res.bank_name);
                        $("#bank_type").val(res.bank_card_type)
                        $("#myForm").submit();
                    }else{                        
                        $("#modal-body").html(res.result_msg);
                        $("#dialog_mask").show();
                    }
                }
            })
        }
    });
</script>

</body></html>