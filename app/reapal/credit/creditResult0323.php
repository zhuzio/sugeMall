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
     'order_no' =>date('YmdHis'),
     'transtime' => '123456',
     'currency' => '156',
     'total_fee' => $_REQUEST['total_fee'],
     'title' => 'yyyyy',
     'body' => 'yyyy',
     'member_id' => $_REQUEST['member_id'],
     'terminal_type'=>'mobile',
     'terminal_info' => '554545',
     'member_ip' => '192.168.1.83',
     'seller_email' => '820061154@qq.com',
	 'notify_url' => 'www.12345.com',
     'token_id' => '1234568779',
     'version' => '3.1.3'	
);

//访问储蓄卡签约服务
$url = $apiUrl.'/fast/credit/portal';
echo $url,"\n";

$result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);


$response = json_decode($result,true);
$encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
echo $encryptkey,"\n";
echo AESDecryptResponse($encryptkey,$response['data']);
?>