<?php
//header("Content-Type:text/html;charset=UTF-8");
require_once '../util.php'; 
require_once '../config.php'; 

//参数数组
$paramArr = array(
     'merchant_id' => $merchant_id,
     'card_no' => $_REQUEST['card_no'],
	 'version' => '3.1.2'
   );
$url = $apiUrl.'/fast/bankcard/list';
$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
$rs = AESDecryptResponse($encryptkey,$response['data']);
echo $rs;exit;
?>