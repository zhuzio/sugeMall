<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $merchant_id,
     'member_id' => $_REQUEST['member_id'],
	 'bind_id' => $_REQUEST['bind_id'],
     'version' => '3.1.2'
   );

//访问储蓄卡签约服务
$url = $apiUrl.'/fast/cancle/bindcard';
$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo AESDecryptResponse($encryptkey,$response['data']);exit;
?>