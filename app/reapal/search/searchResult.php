<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $_REQUEST['merchant_id'],
     'order_no' => $_REQUEST['order_no'],
     'version' => '3.1.2'
   );

//访问储蓄卡签约服务
$url = $apiUrl.'/fast/search';
echo $url,"\n";

$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);


$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo $encryptkey,"\n";
echo AESDecryptResponse($encryptkey,$response['data']);
?>