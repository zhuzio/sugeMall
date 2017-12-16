<?php
error_reporting(0);
//header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 


define('ROOT_PATH', "/home/ftp/1520/sugemall_com-20160528-Red/sugemall.com/");
include(ROOT_PATH . '/eccore/ecmall.php');
/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');
include(ROOT_PATH . '/eccore/model/model.base.php');
define('CHARSET', 'utf-8');
$settings = include(ROOT_PATH . '/data/settings.inc.php');

$no_order = $_REQUEST['order_no'];
$model = & m();
$order = array();
function getOrderinfo($dingdan){
     global $model;
     //第一位为0 默认为积分订单     
     if($dingdan[0] == 'P'){
          $orderinfo = $model->table('sgxt_order')->where(array('orderid'=>$dingdan))->find1();
          if(empty($orderinfo)){
               echo 'order error';die;
          }
          if($orderinfo['status']){
               echo '订单已支付1';
               die;
          }
          $order['userid'] = $orderinfo['userid'];
          $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find1();
          $order['order_id'] = $orderinfo['order_id'];
          $order['user'] = $user;
          $order['user_mobile'] = $user['user_name'];
          $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
          $order['userid'] = $orderinfo['userid'];
          $order['truename'] = $orderinfo['truename'];
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
          $order['order_id'] = $orderinfo['order_id'];
          $order['userid'] = $orderinfo['buyer_id'];
          $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find1();
          $order['user'] = $user;
          $order['user_mobile'] = $user['user_name'];
          $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
          $order['userid'] = $orderinfo['buyer_id'];
          $order['truename'] = $orderinfo['buyer_name'];
          $order['cz_money'] = $orderinfo['order_amount'];
     }
     return $order;
}
$order = getOrderinfo($no_order);

function getIp(){
     if (getenv('HTTP_CLIENT_IP')) { 
          $ip = getenv('HTTP_CLIENT_IP'); 
     } 
     elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
          $ip = getenv('HTTP_X_FORWARDED_FOR'); 
     } 
     elseif (getenv('HTTP_X_FORWARDED')) { 
          $ip = getenv('HTTP_X_FORWARDED'); 
     } 
     elseif (getenv('HTTP_FORWARDED_FOR')) { 
          $ip = getenv('HTTP_FORWARDED_FOR'); 
     } 
     elseif (getenv('HTTP_FORWARDED')) { 
          $ip = getenv('HTTP_FORWARDED'); 
     } 
     else { 
          $ip = $_SERVER['REMOTE_ADDR']; 
     } 
     return $ip; 
}



//参数数组
$paramArr = array(
     'merchant_id' => $merchant_id,
     'card_no' => $_REQUEST['card_no'],
     'owner' => $_REQUEST['owner'],
     'cert_type' => '01',
     'cert_no' => $_REQUEST['cert_no'],
     'phone'=> $_REQUEST['phone'],
     'order_no' =>$no_order,
     'transtime' => time(),
     'currency' => '156',
     'total_fee' => floor($order['cz_money']*100),
     'title' => '购买积分',
     'body' => '购买积分',
     'member_id' => $order['userid'],
     'terminal_type'=>'mobile',
     'terminal_info' => '554545',
     'member_ip' => getIp(),
     'seller_email' => '348887102@qq.com',
     'notify_url' => 'http://www.sugemall.com/app/reapal/notify.php',
	 'token_id' => '1234567890765463',
     'version' => '3.1.3'

);
//访问储蓄卡签约服务
$url = $apiUrl.'/fast/debit/portal';
$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo AESDecryptResponse($encryptkey,$response['data']);exit;
?>