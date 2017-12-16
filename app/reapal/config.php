<?php
//商户ID
$merchant_id = '100000001301142';
//商户邮箱
$seller_email = '568345100@qq.com';
// 商户私钥
$merchantPrivateKey = dirname(__FILE__).'/'.'key/user-rsa.pem';
// 融宝公钥
$reapalPublicKey = dirname(__FILE__).'/'.'key/itrus001.pem';
// APIKEy
$apiKey = '3c6c05cf9a2a5905a1dd697c2b00c048e897cg9c32de8ef49gfb2gb0bc134411';
// APIUrl
$apiUrl = 'http://api.reapal.com';

//通知地址，由商户提供
$notify_url = "http://www.sugemall.com/app/reapal/notify.php";
//返回地址，由商户提供
$return_url = "http://www.sugemall.com/index.php?app=buyer_order";


//版本号
$version= "3.1.6";
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑	

$charset = "utf-8";// 字符编码格式 目前支持  utf-8

$sign_type = "MD5";// 签名方式 不需修改

$transport = "http";//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http



?>