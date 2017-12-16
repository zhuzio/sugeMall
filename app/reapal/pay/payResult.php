<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $merchant_id,
     'order_no' => $_REQUEST['order_no'],
     'check_code' => $_REQUEST['check_code']
   );

//访问储蓄卡签约服务
$url = $apiUrl.'/fast/pay';
$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo AESDecryptResponse($encryptkey,$response['data']);
?>