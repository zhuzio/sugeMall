<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta http-equiv="Content-Language" content="zh-CN"/>
	<meta http-equiv="Expires" CONTENT="0">        
	<meta http-equiv="Cache-Control" CONTENT="no-cache">        
	<meta http-equiv="Pragma" CONTENT="no-cache">
	<title>通联网上支付平台通联网上支付平台</title>
	<link href="css.css" rel="stylesheet" type="text/css">
</head>
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

//如果需要用证书加密，使用phpseclib包
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
require("File/X509.php"); 
require("Crypt/RSA.php");

//如果不用证书加密，使用php_rsa.php函数
require_once("./php_rsa.php");
require_once("./config.php");

//页面编码要与参数inputCharset一致，否则服务器收到参数值中的汉字为乱码而导致验证签名失败。
/*$serverUrl='https://cashier.allinpay.com/mobilepayment/mobile/SaveMchtOrderServlet.action';
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

$pid=$_POST["pid"];*/
$model = & m();
$orderNo=$_REQUEST['dingdan'];
function getOrderinfo($dingdan){
	global $model;
	global $regUrl;
	global $merchantId;
    global $key;
    global $signType;
    global $pc_goods_pickupUrl;
    global $pc_point_pickupUrl;
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
		$order['truename'] = $orderinfo['truename'];
		$order['cz_money'] = $orderinfo['amount'];
		$order['order_time'] = date('YmdHis',$orderinfo['createtime']);
        $order['pickupUrl']=$pc_point_pickupUrl;
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
		$order['truename'] = $orderinfo['buyer_name'];
		$order['cz_money'] = $orderinfo['order_amount'];
		$order['order_time'] = date('YmdHis',$orderinfo['add_time']);
        $order['pickupUrl']=$pc_goods_pickupUrl;
	}
	//查询有无通联的id
    $allinpay_user=$model->table('allinpay')->where(array('user_id'=>$order['userid']))->find1();
	if(empty($allinpay_user)||empty($allinpay_user['allinpay_userid']))
    {
        //注册
        $str='&signType='.$signType.'&merchantId='.$merchantId.'&partnerUserId='.$order['userid'];
        $reg_sign_str=$str.'&key='.$key.'&';
        $reg_signMsg=strtoupper(md5($reg_sign_str));
        $str='signMsg='.$reg_signMsg.$str;
        file_put_contents('str.txt',$str.';'.$regUrl);
        //通联注册用户id
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL,$regUrl.'?'.$str);
        //设置头文件的信息作为数据流输出
        //curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        file_put_contents('regjosn.txt',$data);
        //显示获得的数据
        $arr=json_decode($data,true);
        if($arr['resultCode']=='0000' || $arr['resultCode']=='0006')
        {
            $order['allinpay_userid']=$arr['userId'];
            //将userid的值插入到数据库
            $info=array('user_id'=>$order['userid'],'allinpay_userid'=>$arr['userId'],'allinpay_time'=>$arr['returnDatetime'],'createtime'=>time());
            $model->table('allinpay')->add($info);
        }
    }
    else
    {
        $order['allinpay_userid']=$allinpay_user['allinpay_userid'];
    }
	return $order;
}
$order = getOrderinfo($orderNo);

$orderAmount=$order["cz_money"]*100;
$orderDatetime=$order['order_time'];
$orderCurrency=0;
$ext1='<USER>'.$order['allinpay_userid'].'</USER>';
$pickupUrl=$order['pickupUrl'];
/*$orderExpireDatetime=$_POST["orderExpireDatetime"];
$productName=$_POST["productName"];
$productId=$_POST["productId"];
$productPrice=$_POST["productPrice"];
$productNum=$_POST["productNum"];
$productDesc=$_POST["productDesc"];
$ext1=$_POST["ext1"];
$ext2=$_POST["ext2"];
$extTL=$_POST["extTL"];
$issuerId=$_POST["issuerId"]; //issueId 直联时不为空，必须放在表单中提交。
$pan=$_POST["pan"];	
$tradeNature=$_POST["tradeNature"];
$customsExt=$_POST["customsExt"];*/

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
<body onLoad="javascript:document.PC_ALLINPAY_FORM.submit()">
<form name="PC_ALLINPAY_FORM" action="<?php echo $pc_serverUrl ?>" method="post">
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
<!--================= post 方式提交支付请求 end =====================-->
</form>
</body>
</html>