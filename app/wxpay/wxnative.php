<?php
error_reporting(0);
/* 添加适合 BEGIN */
define('ROOT_PATH', dirname(__FILE__) . "/../..");
include(ROOT_PATH . '/eccore/ecmall.php');
/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');
include(ROOT_PATH . '/eccore/model/model.base.php');
define('CHARSET', 'utf-8');
$settings = include(ROOT_PATH . '/data/settings.inc.php');
/* END */
define("APPID", $settings['epay_wx_appid']);
define("MCHID", $settings['epay_wx_mch_id']);
define("KEY", $settings['epay_wx_key']);
define("APPSECRET", $settings['epay_wx_secret']);
$model = & m();
$order = array();
//获取订单信息
function getOrderinfo($dingdan){
    global $model;
    $order = array();
    //第一位为0 默认为积分订单
    if($dingdan[0] == 'P'){
        $orderinfo = $model -> table('sgxt_order')->where(array('orderid'=>$dingdan))->find1();
        if(empty($orderinfo)){
            echo 'order error';
            die;
        }
        if($orderinfo['status']){
            echo '订单已支付1';
            die;
        }
        $order['user_id'] = $orderinfo['userid'];
        $order['user_name'] = $orderinfo['truename'];
        $order['cz_money'] = $orderinfo['amount'];
    }else{
        //商品订单支付
        $orderinfo = $model -> table('order')->where(array('order_sn'=>$dingdan))->find1();
        if(empty($orderinfo)){
            echo 'order error';
            die;
        }
        if($orderinfo['status'] != '11'){
            echo '订单已支付';
            die;
        }
        $order['user_id'] = $orderinfo['buyer_id'];
        $order['user_name'] = $orderinfo['buyer_name'];
        $order['cz_money'] = $orderinfo['order_amount'];
    }
    return $order;
}
$dingdan = $_REQUEST['dingdan'];
//$site_url = $_POST['site_url'];
$site_url = 'http://sugemall.com';
$order = getOrderinfo($dingdan);
$user_id = $order['user_id'];
$user_name = $order['user_name'];
$cz_money = $order['cz_money'];
if($cz_money>$settings['weixin_limit'])
{
    echo '微信支付限额'.$settings['weixin_limit'];
    die;
}

//$site_url = $_POST['site_url'];
//$dingdan = $_POST['dingdan'];
//$user_id = $_POST['user_id'];
//$user_name = $_POST['user_name'];
//$cz_money = $_POST['cz_money'];
require_once "WxPay.NativePay.php";
//require_once 'log.php';
$notify = new NativePay();
$input = new WxPayUnifiedOrder();
//商品描述
$input->SetBody("微信扫码支付");
//附加数据
$input->SetAttach("$dingdan");
//商户订单号
$input->SetOut_trade_no($dingdan);
//总金额
$input->SetTotal_fee(intval($cz_money * 100));
//交易起始时间
$input->SetTime_start(date("YmdHis"));
//交易结束时间
$input->SetTime_expire(date("YmdHis", time() + 3600*24*7));
//商品标记
$input->SetGoods_tag("微信扫码支付");
$input->SetNotify_url($site_url . "/app/wxpay/notify_url.php");
//交易类型
$input->SetTrade_type("NATIVE");
//商品ID
$input->SetProduct_id($dingdan);
$result = $notify->GetPayUrl($input);
$url2 = $result["code_url"];
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script type="text/javascript" src="/includes/libraries/javascript/jquery.js" charset="utf-8"></script>
    <title>微信扫码支付</title>
</head>
<script>
    setInterval("check_payment()",2000);
    function check_payment()
    {

        var url = '/index.php?app=default&act=check_payment';
        $.get(url, {'dingdan':<?php echo $dingdan ?>}, function(result){
            if(result=='1'){
                self.location="/index.php?app=member";
            }else if(result=='2'){
                self.location="/index.php?app=buyer_order";
            }else{
//            alert('失败');
            }
        });
    }
</script>

<body>
<div style="text-align: center">
    <div style="color:#556B2F;font-size:30px;font-weight: bolder;">微信扫码支付</div>
    <img alt="模式二扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=<?php echo urlencode($url2); ?>" style="width:300px;height:300px;"/>
</div>
</body>
</html>