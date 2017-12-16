<?php
header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $_REQUEST['merchant_id'],
     'card_no' => $_REQUEST['card_no'],
     'owner' => $_REQUEST['owner'],
     'cert_type' => '01',
     'cert_no' => $_REQUEST['cert_no'],
     'phone'=> $_REQUEST['phone'], 
	 'cvv2' => $_REQUEST['cvv2'],
     'validthru' => $_REQUEST['validthru'],
     'member_id' => $_REQUEST['member_id'],
	 'bind_id' => $_REQUEST['bind_id'],
     'version' => '3.1.3'	

);

//访问储蓄卡签约服务
$url = $apiUrl.'/fast/identify';
echo $url,"\n";

$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo $encryptkey,"\n";
echo AESDecryptResponse($encryptkey,$response['data']);

?>