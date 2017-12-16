<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta http-equiv="Content-Language" content="zh-CN"/>
	<meta http-equiv="Expires" CONTENT="0">        
	<meta http-equiv="Cache-Control" CONTENT="no-cache">        
	<meta http-equiv="Pragma" CONTENT="no-cache">
	<title>通联网上支付平台通联网上支付平台-商户接口范例-支付请求信息签名</title>
	<link href="css.css" rel="stylesheet" type="text/css">
</head>
<body>	
<center> <font size=16><strong>订单支付请求</strong></font></center>
<?php
//如果需要用证书加密，使用phpseclib包
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
require("File/X509.php"); 
require("Crypt/RSA.php");

//如果不用证书加密，使用php_rsa.php函数
require_once("./php_rsa.php"); 


//页面编码要与参数inputCharset一致，否则服务器收到参数值中的汉字为乱码而导致验证签名失败。	
$serverUrl=$_POST["serverUrl"];
$inputCharset=$_POST["inputCharset"];
$pickupUrl=$_POST["pickupUrl"];
$receiveUrl=$_POST["receiveUrl"];
$version=$_POST["version"];
$language=$_POST["language"];
$signType=$_POST["signType"];
$merchantId=$_POST["merchantId"];
$payerName=$_POST["payerName"];
$payerEmail=$_POST["payerEmail"];	
$payerTelephone=$_POST["payerTelephone"];
$payerIDCard=$_POST["payerIDCard"];
$pid=$_POST["pid"];
$orderNo=$_POST["orderNo"];
$orderAmount=$_POST["orderAmount"];
$orderDatetime=$_POST["orderDatetime"];
$orderCurrency=$_POST["orderCurrency"];
$orderExpireDatetime=$_POST["orderExpireDatetime"];
$productName=$_POST["productName"];
$productId=$_POST["productId"];
$productPrice=$_POST["productPrice"];
$productNum=$_POST["productNum"];
$productDesc=$_POST["productDesc"];
$ext1=$_POST["ext1"];
$ext2=$_POST["ext2"];
$extTL=$_POST["extTL"];
$payType=$_POST["payType"]; //payType   不能为空，必须放在表单中提交。
$issuerId=$_POST["issuerId"]; //issueId 直联时不为空，必须放在表单中提交。
$pan=$_POST["pan"];	
$tradeNature=$_POST["tradeNature"];
$customsExt=$_POST["customsExt"];
$key=$_POST["key"]; 


//报文参数有消息校验
//if(preg_match("/\d/",$pickupUrl)){
//echo "<script>alert('pickupUrl有误！！');history.back();</script>";
//}

// 生成签名字符串。
$bufSignSrc=""; 
if($inputCharset != "")
	$bufSignSrc=$bufSignSrc."inputCharset=".$inputCharset."&";		
if($pickupUrl != "")
	$bufSignSrc=$bufSignSrc."pickupUrl=".$pickupUrl."&";		
if($receiveUrl != "")
	$bufSignSrc=$bufSignSrc."receiveUrl=".$receiveUrl."&";		
if($version != "")
	$bufSignSrc=$bufSignSrc."version=".$version."&";		
if($language != "")
	$bufSignSrc=$bufSignSrc."language=".$language."&";		
if($signType != "")
	$bufSignSrc=$bufSignSrc."signType=".$signType."&";		
if($merchantId != "")
	$bufSignSrc=$bufSignSrc."merchantId=".$merchantId."&";		
if($payerName != "")
	$bufSignSrc=$bufSignSrc."payerName=".$payerName."&";		
if($payerEmail != "")
	$bufSignSrc=$bufSignSrc."payerEmail=".$payerEmail."&";		
if($payerTelephone != "")
	$bufSignSrc=$bufSignSrc."payerTelephone=".$payerTelephone."&";	

//需要加密付款人身份证信息
if($payerIDCard != "")
{
	/*
	//测身份证信息认证使用商户号：20150513442 
	//加密函数从php_rsa.php 调用

	
	$publickeyfile = './publickey.txt';
	$publickeycontent = file_get_contents($publickeyfile);

	$publickeyarray = explode(PHP_EOL, $publickeycontent);
	$publickey_arr = explode('=',$publickeyarray[0]);
	$modulus_arr = explode('=',$publickeyarray[1]);
	$publickey = trim($publickey_arr[1]);
	$modulus = trim($modulus_arr[1]);
	$keylength = 1024;
	$ciphertext = base64_encode(rsa_encrypt($payerIDCard, $publickey, $modulus, $keylength));
	*/
	
	
	//测身份证信息认证使用商户号：20150513442 
	//加密函数从phpseclib调用
	$certfile = file_get_contents('TLCert-test.cer');
	$x509 = new File_X509();
	$cert = $x509->loadX509($certfile);
	$pubkey = $x509->getPublicKey();
	
	$rsa = new Crypt_RSA();
	$rsa->loadKey($pubkey);
	$rsa->setPublicKey();
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	$ciphertext = $rsa->encrypt($payerIDCard);
	$ciphertext = base64_encode($ciphertext);
	
	
	$payerIDCard = $ciphertext;
	$bufSignSrc=$bufSignSrc."payerIDCard=".$payerIDCard."&";
	
}
				
if($pid != "")
	$bufSignSrc=$bufSignSrc."pid=".$pid."&";		
if($orderNo != "")
	$bufSignSrc=$bufSignSrc."orderNo=".$orderNo."&";
if($orderAmount != "")
	$bufSignSrc=$bufSignSrc."orderAmount=".$orderAmount."&";
if($orderCurrency != "")
	$bufSignSrc=$bufSignSrc."orderCurrency=".$orderCurrency."&";
if($orderDatetime != "")
	$bufSignSrc=$bufSignSrc."orderDatetime=".$orderDatetime."&";
if($orderExpireDatetime != "")
	$bufSignSrc=$bufSignSrc."orderExpireDatetime=".$orderExpireDatetime."&";
if($productName != "")
	$bufSignSrc=$bufSignSrc."productName=".$productName."&";
if($productPrice != "")
	$bufSignSrc=$bufSignSrc."productPrice=".$productPrice."&";
if($productNum != "")
	$bufSignSrc=$bufSignSrc."productNum=".$productNum."&";
if($productId != "")
	$bufSignSrc=$bufSignSrc."productId=".$productId."&";
if($productDesc != "")
	$bufSignSrc=$bufSignSrc."productDesc=".$productDesc."&";
if($ext1 != "")
	$bufSignSrc=$bufSignSrc."ext1=".$ext1."&";

//如果海关扩展字段不为空，需要做个MD5填写到ext2里
if($ext2 == "" && $customsExt != "")
{
	$ext2 = strtoupper(md5($customsExt));
	$bufSignSrc=$bufSignSrc."ext2=".$ext2."&";
}
else if($ext2 != "")
{
	$bufSignSrc=$bufSignSrc."ext2=".$ext2."&";
}
	
if($extTL != "")
	$bufSignSrc=$bufSignSrc."extTL".$extTL."&";
if($payType != "")
	$bufSignSrc=$bufSignSrc."payType=".$payType."&";		
if($issuerId != "")
	$bufSignSrc=$bufSignSrc."issuerId=".$issuerId."&";
if($pan != "")
	$bufSignSrc=$bufSignSrc."pan=".$pan."&";	
if($tradeNature != "")
	$bufSignSrc=$bufSignSrc."tradeNature=".$tradeNature."&";
	$bufSignSrc=$bufSignSrc."key=".$key; //key为MD5密钥，密钥是在通联支付网关商户服务网站上设置。

//签名，设为signMsg字段值。
$signMsg = strtoupper(md5($bufSignSrc));	

?>

<!--
	1、订单可以通过post方式或get方式提交，建议使用post方式；
	   提交支付请求可以使用http或https方式，建议使用https方式。
	2、通联支付网关地址、商户号及key值，在接入测试时由通联提供；
	   通联支付网关地址、商户号，在接入生产时由通联提供，key值在通联支付网关会员服务网站上设置。
-->
<!--================= post 方式提交支付请求 start =====================-->
<!--================= 测试地址为 http://ceshi.allinpay.com/gateway/index.do =====================-->
<!--================= 生产地址请在测试环境下通过后从业务人员获取 =====================-->
<form name="form2" action="<?php echo $serverUrl ?>" method="post">
	<input type="hidden" name="inputCharset" id="inputCharset" value="<?php echo $inputCharset ?>" />
	<input type="hidden" name="pickupUrl" id="pickupUrl" value="<?php echo $pickupUrl?>"/>
	<input type="hidden" name="receiveUrl" id="receiveUrl" value="<?php echo $receiveUrl?>" />
	<input type="hidden" name="version" id="version" value="<?php echo $version?>"/>
	<input type="hidden" name="language" id="language" value="<?php echo $language?>" />
	<input type="hidden" name="signType" id="signType" value="<?php echo $signType?>"/>
	<input type="hidden" name="merchantId" id="merchantId" value="<?php echo $merchantId?>" />
	<input type="hidden" name="payerName" id="payerName" value="<?php echo $payerName?>"/>
	<input type="hidden" name="payerEmail" id="payerEmail" value="<?php echo $payerEmail?>" />
	<input type="hidden" name="payerTelephone" id="payerTelephone" value="<?php echo $payerTelephone ?>" />
	<input type="hidden" name="payerIDCard" id="payerIDCard" value="<?php echo $payerIDCard ?>" />
	<input type="hidden" name="pid" id="pid" value="<?php echo $pid?>"/>
	<input type="hidden" name="orderNo" id="orderNo" value="<?php echo $orderNo?>" />
	<input type="hidden" name="orderAmount" id="orderAmount" value="<?php echo $orderAmount ?>"/>
	<input type="hidden" name="orderCurrency" id="orderCurrency" value="<?php echo $orderCurrency?>" />
	<input type="hidden" name="orderDatetime" id="orderDatetime" value="<?php echo $orderDatetime?>" />
	<input type="hidden" name="orderExpireDatetime" id="orderExpireDatetime" value="<?php echo $orderExpireDatetime ?>"/>
	<input type="hidden" name="productName" id="productName" value="<?php echo $productName?>" />
	<input type="hidden" name="productPrice" id="productPrice" value="<?php echo $productPrice?>" />
	<input type="hidden" name="productNum" id="productNum" value="<?php echo $productNum?>"/>
	<input type="hidden" name="productId" id="productId" value="<?php echo $productId?>" />
	<input type="hidden" name="productDesc" id="productDesc" value="<?php echo $productDesc?>" />
	<input type="hidden" name="ext1" id="ext1" value="<?php echo $ext1?>" />
	<input type="hidden" name="ext2" id="ext2" value="<?php echo $ext2?>" />
	<input type="hidden" name="extTL" id="extTL" value="<?php echo $extTL?>" />
	<input type="hidden" name="payType" value="<?php echo $payType?>" />
	<input type="hidden" name="issuerId" value="<?php echo $issuerId?>" />
	<input type="hidden" name="pan" value="<?php echo $pan?>" />
	<input type="hidden" name="tradeNature" value="<?php echo $tradeNature?>" />
	<input type="hidden" name="customsExt" value="<?php echo $customsExt?>" />
	<input type="hidden" name="signMsg" id="signMsg" value="<?php echo $signMsg?>" />
	<div align="center"><input type="submit" value="确认付款，到通联支付去啦" align=center/></div>
<!--================= post 方式提交支付请求 end =====================-->
</form>
<table class="table_box" width="90%" align="center">
<tr><td colspan="2" class="tit_bar">提交支付订单请求参数</td></tr>
   <tr><td>inputCharset</td><td style="width:100px">字符集: <?php echo $inputCharset?> </td></tr>  
   <tr><td>pickupUrl</td><td>取货地址: <?php echo $pickupUrl ?></td></tr>
   <tr><td>receiveUrl</td><td>商户系统通知地址: <?php echo $receiveUrl ?></td></tr>
   <tr><td>version</td><td>接口版本号: <?php echo $version ?></td></tr>
   <tr><td>language</td><td>网关页面语言: <?php echo $language ?></td></tr>
   <tr><td>signType</td><td>签名类型: <?php echo $signType ?></td></tr> 
	<tr><td>merchantId</td><td>商户号: <?php echo $merchantId ?></td></tr>
	<tr><td>payerName</td><td>付款人名称: <?php echo $payerName ?></td></tr>	
	<tr><td>payerEmail</td><td>付款人联系email: <?php echo $payerEmail ?></td></tr>	
	<tr><td>payerTelephone</td><td>付款人电话: <?php echo $payerTelephone ?></td></tr>
	<tr><td>payerIDCard</td><td>付款人证件号: <?php echo $payerIDCard ?></td></tr>
	<tr><td>pid</td> <td>合作伙伴商户号: <?php echo $pid ?></td></tr>	
	<tr><td>orderNo</td> <td >商户订单号: <?php echo $orderNo ?></td></tr>	
	<tr><td>orderAmount</td>  <td>订单金额(单位分): <?php echo  $orderAmount ?></td></tr>
	<tr><td>orderCurrency</td>  <td>订单金额币种类型: <?php echo $orderCurrency ?></td></tr>
	<tr><td>orderDatetime</td>  <td>订单提交时间: <?php echo $orderDatetime ?></td></tr>
	<tr><td>orderExpireDatetime</td>  <td>订单过期时间: <?php echo $orderExpireDatetime ?></td></tr>
	<tr><td>productName</td>  <td>商品名称: <?php echo $productName ?></td></tr>
	<tr><td>productPrice</td>  <td>商品单价: <?php echo $productPrice ?></td></tr>
	<tr><td>productNum</td>  <td>商品数量: <?php echo $productNum ?></td></tr>	
	<tr><td>productId</td>  <td>商品代码: <?php echo $productId ?></td></tr>
	<tr><td>productDesc</td>  <td>商品描述: <?php echo $productDesc ?></td></tr>
	<tr><td>ext1</td>  <td>扩展字段1: <?php echo $ext1 ?></td></tr>
	<tr><td>ext2</td>  <td>扩展字段2: <?php echo $ext2 ?></td></tr>
	<tr><td>extTL</td>  <td>业务扩展字段: <?php echo $extTL ?></td></tr>
	<tr><td>payType</td>  <td>支付方式: <?php echo $payType ?></td></tr>
	<tr><td>issuerId</td>  <td>发卡行代码: <?php echo $issuerId ?></td></tr>
	<tr><td>pan</td>  <td>付款人支付卡号: <?php echo $pan ?></td></tr>
	<tr><td>tradeNature</td>  <td>贸易类型: <?php echo $tradeNature ?></td></tr>
	<tr><td>customsExt</td>  <td>海关扩展字段: <?php echo htmlentities($customsExt) ?></td></tr>
	<tr><td>组成签名原串的内容: </td><td><textarea  rows="4" cols="120"><?php echo $bufSignSrc?></textarea></td></tr>
	<tr><td>报文签名后内容（signMsg）: </td><td><?php echo $signMsg?></td></tr>	
	</tbody>
</table>
</body>
</html>