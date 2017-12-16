<?php 
	require_once("config.php");
	require_once("RongpayService.php");
	header("Content-Type:text/html;charset=UTF-8");
	error_reporting(0);
	/* 添加适合 BEGIN */
	define('ROOT_PATH', dirname(__FILE__) . "/../..");
	include(ROOT_PATH . '/eccore/ecmall.php');
	/* 定义配置信息 */
	ecm_define(ROOT_PATH . '/data/config.inc.php');
	include(ROOT_PATH . '/eccore/model/model.base.php');
	define('CHARSET', 'utf-8');
	$settings = include(ROOT_PATH . '/data/settings.inc.php');
	$model = & m();

	function getOrderinfo($dingdan){
	    global $model;
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
	    }
	    return $order;
	}
	function getIp(){
	     if (getenv('HTTP_CLIENT_IP')) { 
	          $ip = getenv('HTTP_CLIENT_IP'); 
	     } 
	     elseif (getenv('HTTP_X_FORWARDED_FOR')) { 
	          $ip = getenv('HTTP_X_FORWARDED_FOR'); 
	     } 
	     elseif (getenv('HTTP_X_FORWARDED')) { 
	          $ip = getenv('HTTP_X_FORWARDED'); 
	     } 
	     elseif (getenv('HTTP_FORWARDED_FOR')) { 
	          $ip = getenv('HTTP_FORWARDED_FOR'); 
	     } 
	     elseif (getenv('HTTP_FORWARDED')) { 
	          $ip = getenv('HTTP_FORWARDED'); 
	     } 
	     else { 
	          $ip = $_SERVER['REMOTE_ADDR']; 
	     } 
	     return $ip; 
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head> 
		<title>付款</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<?php
		$order_no = $_REQUEST['dingdan'];
		$order = getOrderinfo($order_no);
		$total_fee = floatval($_REQUEST['cz_money']) * 100;
		if($_REQUEST['cz_money'] != $order['cz_money']){
			echo "<script>alert('订单数据异常，请重新提交');history.back();</script>";exit;
		}
		$default_bank='NO';
		//$eee = iconv("gbk","UTF-8",$_POST['rongtitle']);
		//$aaa = iconv("gbk","UTF-8",$_POST['rongbody']);
		$eee = '融宝支付WEB交易';
		$aaa = '融宝支付WEB交易';
		 
		if ($default_bank == "NO"){
			$paymethod    = "bankPay";				//支付方式，默认网关
			$default_bank  = "";
		}else{
			$paymethod="directPay";  //支付方式，银行直连			
			$default_bank=$default_bank;
		}
			
		$parameter = array(
			'seller_email'=> $seller_email,
			'merchant_id' => $merchant_id,
			'notify_url' => $notify_url,
			'return_url' => $return_url,
			'transtime' => time(),
			'currency' => '156',
			'member_ip' => getIp(),
			'terminal_type' => 'web',
			'terminal_info' => 'terminal_info',
			'sign_type' => $sign_type,
			'order_no' => $order_no,
			'total_fee' => $total_fee,
			'title' => $eee,
			'body' => $aaa,
			'pay_method'=>$paymethod,
			'default_bank'=>$default_bank, 
			'payment_type'=>'1'
        );	
		$url = $apiUrl;
		////构造函数，生成请求URL
		$sHtmlText = rongpayService::buildForm($parameter, $reapalPublicKey, $apiKey,$url);
	?>
	<body>
		<div style="text-align: center; width: auto">
		  <section>
		   <div>订单确认</div>
		     <div>订单号：<span><?php echo $order_no; ?></span></div>
		     <div>订单金额：<span><?php echo $total_fee; ?></span></div>
		     <div><span><?php echo $sHtmlText; ?></span></div>
		 	</section>
			<footer>
				<div>Copyright &copy; 2017 融宝支付</div>	
			</footer>
		 </div>
	</body>
</html>