<?php
require_once("util.php");
require_once("config.php");

class RongpayService{
	
	public static function buildForm($paramarr, $reapalPublicKey, $apiKey,$url){
		
		$gateway = $url.'/web/portal';//访问地址
		$paramarr = RongpayService::argSort($paramarr);
		$mySign = RongpayService::buildMysign($paramarr, $apiKey);//生成签名结果
		$paramarr['sign'] = $mySign;
		$generateAESKey = generateAESKey();
		$encryptkey = RSAEncryptkey($generateAESKey,$reapalPublicKey);
		$data = AESEncryptRequest($generateAESKey,$paramarr);
		//post方式传递
		$sHtml = "<form id=\"rongpaysubmit\" name=\"rongpaysubmit\" action=\"".$gateway."\" method=\"post\">"
		."<input type=\"hidden\" name=\"merchant_id\" value=\"".$paramarr['merchant_id']."\"/>"
		."<input type=\"hidden\" name=\"data\" value=\"".$data."\"/>"
		."<input type=\"hidden\" name=\"encryptkey\" value=\"".$encryptkey."\"/>"
		//submit按钮控件请不要含有name属性
		."<input type=\"submit\" class=\"button_p2p\" value=\"融宝支付确认付款\"></form><script>document.getElementById('rongpaysubmit').submit();</script>";
		
		return $sHtml;
	}
	
	public static function buildMysign($paramarr, $apiKey){
		$prestr = RongpayService::createLinkString($paramarr);
		return md5($prestr.$apiKey);
	}

	private static function createLinkString($array){
		$paramarr = RongpayService::paraFilter($array);
		$arg  = "";
		while (list ($apiKey, $val) = each ($paramarr)) 
		{
			$arg.=$apiKey."=".$val."&";
		}
		$arg = substr($arg,0,count($arg)-2);		     //去掉最后一个&字符
		return $arg;
	}


	/**
		*除去数组中的空值和签名参数
		*$parameter 签名参数组
		*return 去掉空值与签名参数后的新签名参数组
	 */
	private static function paraFilter($parameter) 
	{
		$para = array();
		while (list ($key, $val) = each ($parameter)) 
		{
			if($key == "sign" || $key == "sign_type" || $val == "")
			{
				continue;
			}
			else
			{
				$para[$key] = $parameter[$key];
			}
		}
		return $para;
	}
	/********************************************************************************/

	/**对数组排序
		*$array 排序前的数组
		*return 排序后的数组
	 */
	private static function argSort($array) 
	{
		ksort($array);
		reset($array);
		return $array;
	}
}
?>