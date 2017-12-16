var wxIsReady = false;
function getSignPackge(url){
    var dtd = $.Deferred();
    $.ajax({
        url : Url + '/api/index.php?n=shop_center&f=getSignPackge',
        type : 'POST',
        data : {'url':url},
        dataType : 'JSON',
        crossDomain : true,
        beforeSend : function() {},
        complete : function() {},
        success : function(res){dtd.resolve(res);},
        error : function(error) {dtd.reject(error);}
    });
    return dtd.promise();
}
function wxInity(url){
    var getSys;
    getSignPackge(url).done(function(res){       
//  console.log(res);
        wx.config({
            debug: false,
            appId: res.data.appId,
            timestamp: res.data.timestamp,
            nonceStr: res.data.nonceStr,
            signature: res.data.signature,
            jsApiList: [
                'checkJsApi',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'onMenuShareQZone',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'translateVoice',
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'onVoicePlayEnd',
                'pauseVoice',
                'stopVoice',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'getNetworkType',
                'openLocation',
                'getLocation',
                'hideOptionMenu',
                'showOptionMenu',
                'closeWindow',
                'scanQRCode',
                'chooseWXPay',
                'openProductSpecificView',
                'addCard',
                'chooseCard',
                'openCard'
            ]
        });
        wx.ready(function () {
            // 在这里调用 API
            wxIsReady = true;
        });        
    });
}
function saoyisao(){
    wx.scanQRCode({
        needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
        scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
        success: function (res) {
            if (res.errMsg === "scanQRCode:ok"){
                if(res.resultStr.indexOf('shop_center')<0 && res.resultStr.indexOf('pay_balance')>=0)
                {
                    var arr=res.resultStr.split('&');
                    res.resultStr=Url+'/api/index.php?n=shop_center&f=pay_balance&'+arr[2];
                }
                if(res.resultStr.indexOf('register1')<0 && res.resultStr.indexOf('member')>=0)
                {
                    var brr=res.resultStr.split('=');
                    res.resultStr=Url+'/wap/register1.html?mobile='+brr[4];
                }
                //{"resultStr":"", "errMsg":"scanQRCode:ok"}
                if(/^[https?:\/\/|tel:|mailto:]/i.test(res.resultStr)) {
                    $.ajax({
                        url : res.resultStr,
                        type:'GET',
                        success:function(res){
                            res = JSON.parse(res);
                            res.ret = $.trim(res.ret);
                            console.log("res.data");
                            if(res.ret == 'ok'){
                            	console.log("res.data");
                            	$(".z-tan").show();
                            	setTimeout(function() {
	   								$(".z-tan").hide();
								}, 1000);
                                localStorage.setItem('sys_data',JSON.stringify(res.data));                                
                                location.href = 'saoyisao.html';
                            }
                            if(res.ret == 'err'){
                            	$(".z-prompt6").show();
                            	$(".z-prompt6").html(res.msg);
                            	setTimeout(function() {
	   								$(".z-prompt6").hide();
								}, 3000);
                            }
                        }
                    })
                    return;
                }else if(res.resultStr.indexOf('register1') >=0){
                    location.href = res.resultStr;
                }
                // 二维码ajax验票 
                // checkPost(data);
            }
            /*
            var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
            if(result.data.url == 'saoyisao.html'){
                localStorage.setItem('sys_data',JSON.stringify(result.data));
                location.href = result.data.url;
            }
            */
        }
    });
}




    