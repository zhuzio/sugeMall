	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="Content-Language" content="zh-CN"/>
		<meta http-equiv="Expires" content="0" />        
		<meta http-equiv="Cache-Control" content="no-cache" />        
		<meta http-equiv="Pragma" content="no-cache" />
		<title>通联网上支付平台-商户接口范例-批量订单查询确认</title>
		<link href="css.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<center> <font size=16><strong>批量订单查询确认</strong></font></center>
	<?PHP
	/**
		* ATTENTION: 
		* The sync query function ONLY support PHP(5.0+)
		* There're a bug in fsockopen method which was provieded by PHP(4.x)
		*
		* If you want to use sync query function ,please install OPENSSL with PHP first.
		* example: (if you use APPSERV enviorment)
		* [1] open %WIN%\PHP.ini file and delete ';' which was front of "extension=php_openssl.dll"
		* [2] copy libeay32.dll and ssleay32.dll to %WIN%\system32 folder.
		* [3] restart apache httpserver
		* 
		*/
	$serverUrl = $_POST["serverUrl"];//http://ceshi.allinpay.com/gateway/index.do?
	$serverIP = $_POST["myServerIp"];//ceshi.allinpay.com
	$key = $_POST["key"];
	$version = $_POST["version"];
	$merchantId = $_POST["merchantId"];
	$beginDateTime = $_POST["beginDateTime"];
	$endDateTime = $_POST["endDateTime"];
	$pageNo = $_POST["pageNo"];
	$signType = $_POST["signType"];
	
	

	//组签名原串
	$bufSignSrc = "";
	if($version != "")
	$bufSignSrc = $bufSignSrc."version=".$version."&";
	if($merchantId != "")
	$bufSignSrc = $bufSignSrc."merchantId=".$merchantId."&";	
	if($beginDateTime != "")
	$bufSignSrc = $bufSignSrc."beginDateTime=".$beginDateTime."&";
	if($endDateTime != "")
	$bufSignSrc = $bufSignSrc."endDateTime=".$endDateTime."&";
	if($pageNo != "")
	$bufSignSrc = $bufSignSrc."pageNo=".$pageNo."&";
	if($signType != "")
	$bufSignSrc = $bufSignSrc."signType=".$signType."&";
	if($key != "")
	$bufSignSrc = $bufSignSrc."key=".$key;

	//生成签名串
	$signMsg = strtoupper(md5($bufSignSrc));	
	?>

		<table class="table_box" width="90%" align=center>
		   <tr class="tit_bar">
		      <td colspan="2" class="tit_bar">提交的批量订单查询表单参数</td>
		   </tr>
		   <tr><td>1</td><td>接口版本号: <?=$version?></td></tr>
		   <tr><td>2</td><td>商户号: <?=$merchantId?></td></tr>  
		   <tr><td>3</td><td>查询起始时间: <?=$beginDateTime ?></td></tr>
		   <tr><td>4</td><td>查询结束时间: <?=$endDateTime?></td></tr> 
		   <tr><td>5</td><td>页码: <?=$pageNo?></td> </tr>
		   <tr><td>6</td><td>签名方式: <?=$signType?></td></tr>
		   <tr><td>7</td><td>签名串: <?=$signMsg?></td></tr>
		   <tr><td>签名原串：</td><td><?=$bufSignSrc?></td>
		   </tr>
		</table>
		
		<!-- 1. 页面方式提交查询请求 -->
		<div>
			<form name="form1" action="<?=$serverUrl?>" method="post">
			<input type="hidden" name="version" value="<?=$version?>" />
			<input type="hidden" name="merchantId" value="<?=$merchantId?>" />			
			<input type="hidden" name="beginDateTime" value="<?=$beginDateTime ?>" />
			<input type="hidden" name="endDateTime" value="<?=$endDateTime?>" />
			<input type="hidden" name="pageNo" value="<?=$pageNo?>" />
			<input type="hidden" name="signType" value="<?=$signType?>" />
			<input type="hidden" name="signMsg" value="<?=$signMsg?>" />
			</form>
		</div>

		<!-- 2. HTTPCLIENT方式提交查询请求 -->
		<hr>
		<center> <font size=16><strong>同步返回批量订单查询结果</strong></font></center>
		<?PHP

		require_once("./php_rsa.php");  //请修改参数为php_rsa.php文件的实际位置
			
		$argv = array(
		'version' => $version,
		'merchantId' => $merchantId,		
		'beginDateTime' => $beginDateTime,
		'endDateTime' => $endDateTime,
		'pageNo' => $pageNo,
		'signType' => $signType,	
		'signMsg' => $signMsg
		);

		$index = 0;	
		$params = "";	
		foreach($argv as $key=>$value){
			if($index != 0){
				$params .= '&'; 
			}
			$params .= $key.'=';
			$params .= urlencode($value);//对字符串进行编码转换
			$index += 1;
		}
		$length = strlen($params);
		
		$urlhost = $serverIP;
		$urlpath = '/mchtoq/index.do';
		//echo "urlhost=".$urlhost."<br>";
		//echo "urlpath=".$urlpath."<br>";
		
		$header = array();
		$header[] = 'POST '.$urlpath.' HTTP/1.0';
    $header[] = 'Host: '.$urlhost;
    $header[] = 'Accept: text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
		$header[] = 'Accept-encoding: gzip';
		$header[] = 'Accept-language: en-us';
    $header[] = 'Content-Type: application/x-www-form-urlencoded';
    $header[] = 'Content-Length: '.$length;
    
    $request = implode("\r\n", $header)."\r\n\r\n".$params;
    $pageContents = "";
    
		if(!$fp= pfsockopen($urlhost, 80, $errno, $errstr, 10)){ //测试环境请换用pfsockopen($urlhost, 80, $errno, $errstr, 10)
		//if(!$fp= pfsockopen($urlhost, 80, $errno, $errstr, 10)){ //生产环境请换用pfsockopen('ssl://'.$urlhost, 443, $errno, $errstr, 10)
			echo "can not connect to {$urlhost}. $errstr($errno) <br/>"; 
		  echo $fp; 
		}else{
			fwrite($fp, $request); 
			$inHeaders = true;//是否在返回头
			$atStart = true;//是否返回头第一行
			$ERROR = false;
			$responseStatus;//返回头状态 
		  while(!feof($fp)){ 
		  	$line = fgets($fp, 2048); 
		  	
		  	if($atStart){
		  			$atStart = false;
		  			preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m); 
	      		$responseStatus = $m[2];
	      		print_r("<div style='padding-left:40px;'>");
	      		print_r("<div>".$line."</div>" );
	      		print_r("</div>");
	      		continue;
		  	}
		  	
		  	if($inHeaders ){
	  			  if(strLen(trim($line)) == 0 ){
		  					$inHeaders = false;		
		  			}
		  			continue;
		  	}
		  	
		  	if(!$inHeaders and $responseStatus == 200){
			  	//获得参数串
			  	$pageContents .= $line;
		  	}
		  	
		  } 
		  fclose($fp);
		}

		//echo "pageContents=".$pageContents."<br>";
		 /**
       * 返回的批量查询响应报文解析流程
       *  1、对报文base64解码,并转换成utf8编码
       *  2、解析响应报文，将对账明细进行MD5摘要作为签名原串
       *  3、用通联公钥进行验签
       * */
         
    //1、对报文base64解码,并转换成utf8编码     
    $base64PageContents = base64_decode($pageContents);
		//echo "base64PageContents=".$base64PageContents."<br>";
		
		//2、解析响应报文，将对账明细进行MD5摘要作为签名原串
		$lastIndex = strrpos($base64PageContents,PHP_EOL);//查找字符串在另一个字符串中最后一次出现的位置
		$details = substr($base64PageContents,0,$lastIndex);//对账明细包括空行
		//echo "<br>对账明细=".$details;
		$signVerifyMsg = substr($base64PageContents,$lastIndex+1);
		//echo "<br>签名串=".$signVerifyMsg;
		
		//3、对明细进行Md5作为签名原串进行验签，用通联公钥进行验签
		$detailsMd5 = strtoupper(md5($details));	
		//echo "<br>detailsMd5=".$detailsMd5;
		//验签
		//initPublicKey("d:\wamp\www\demo\publickey.xml");
		
		//解析publickey.txt文本获取公钥信息
		$publickeyfile = './publickey.txt';
		$publickeycontent = file_get_contents($publickeyfile);
		//echo "<br>".$content;
		$publickeyarray = explode(PHP_EOL, $publickeycontent);
		$publickey = explode('=',$publickeyarray[0]);
		$modulus = explode('=',$publickeyarray[1]);
		//echo "<br>publickey=".$publickey[1];
		//echo "<br>modulus=".$modulus[1];
		
		//请把参数修改为你的publickey.xml文件的存放位置，此处使用文件绝对路径
		//测试环境请用测试证书文件，生产环境请用生产证书文件
		$keylength = 1024;
		$verifyResult = 0;
 		$verify_result = rsa_verify($detailsMd5,$signVerifyMsg, $publickey[1], $modulus[1], $keylength,"sha1");
 		if($verify_result ){
 			$verifyResult = 1;
 		}else{
 			$verifyResult = 0;
 		}
		?>

		<div style="padding-left:40px;">
			<div>验签是否成功：<?=$verifyResult ?>(0：失败，1：成功)</div>
			<div>对账明细：<textarea  rows="20" cols="120"><?=$details ?></textarea></div>
			<div>签名串： <textarea  rows="4" cols="120"><?=$signVerifyMsg ?></textarea></div>
		</div>

  </body>
	
</html>









