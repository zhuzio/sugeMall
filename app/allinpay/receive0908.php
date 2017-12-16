<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta http-equiv="Content-Language" content="zh-CN"/>
		<meta http-equiv="Expires" content="0" />        
		<meta http-equiv="Cache-Control" content="no-cache" />        
		<meta http-equiv="Pragma" content="no-cache" />
		<title>通联网上支付平台-支付结果</title>
		<link href="css.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<center> <font size=16><strong>支付结果</strong></font></center>
<?php
error_reporting(0);
    define('ROOT_PATH', "/home/ftp/1520/sugemall_com-20160528-Red/sugemall.com/");
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
	
	//测试商户的key! 请修改。
	$md5key = "1234567890";
	
	$merchantId=$_POST["merchantId"];
	$version=$_POST['version'];
	$language=$_POST['language'];
	$signType=$_POST['signType'];
	$payType=$_POST['payType'];
	$issuerId=$_POST['issuerId'];
	$paymentOrderId=$_POST['paymentOrderId'];
	$orderNo=$_POST['orderNo'];
	$orderDatetime=$_POST['orderDatetime'];
	$orderAmount=$_POST['orderAmount'];
	$payDatetime=$_POST['payDatetime'];
	$payAmount=$_POST['payAmount'];
	$ext1=$_POST['ext1'];
	$ext2=$_POST['ext2'];
	$payResult=$_POST['payResult'];
	$errorCode=$_POST['errorCode'];
	$returnDatetime=$_POST['returnDatetime'];
	$signMsg=$_POST["signMsg"];
	file_put_contents('data.txt','merchantId='.$merchantId.'&version='.$version.'&signType='.$signType.'&paymentOrderId='.$paymentOrderId.'&orderNo='.$orderNo.'&orderDatetime='.$orderDatetime.'&payDatetime='.$payDatetime.'&payResult='.$payResult.'&returnDatetime='.$returnDatetime.'&signMsg='.$signMsg."\n",FILE_APPEND);
	
	$bufSignSrc="";
	if($merchantId != "")
	$bufSignSrc=$bufSignSrc."merchantId=".$merchantId."&";		
	if($version != "")
	$bufSignSrc=$bufSignSrc."version=".$version."&";		
	if($language != "")
	$bufSignSrc=$bufSignSrc."language=".$language."&";

	$bufSignSrc=$bufSignSrc."signType=".$signType."&";		
	if($payType != "")
	$bufSignSrc=$bufSignSrc."payType=".$payType."&";
	if($issuerId != "")
	$bufSignSrc=$bufSignSrc."issuerId=".$issuerId."&";
	if($paymentOrderId != "")
	$bufSignSrc=$bufSignSrc."paymentOrderId=".$paymentOrderId."&";
	if($orderNo != "")
	$bufSignSrc=$bufSignSrc."orderNo=".$orderNo."&";
	if($orderDatetime != "")
	$bufSignSrc=$bufSignSrc."orderDatetime=".$orderDatetime."&";
	if($orderAmount != "")
	$bufSignSrc=$bufSignSrc."orderAmount=".$orderAmount."&";
	if($payDatetime != "")
	$bufSignSrc=$bufSignSrc."payDatetime=".$payDatetime."&";
	if($payAmount != "")
	$bufSignSrc=$bufSignSrc."payAmount=".$payAmount."&";
	if($ext1 != "")
	$bufSignSrc=$bufSignSrc."ext1=".$ext1."&";
	if($ext2 != "")
	$bufSignSrc=$bufSignSrc."ext2=".$ext2."&";
	if($payResult != "")
	$bufSignSrc=$bufSignSrc."payResult=".$payResult."&";
	if($errorCode != "")
	$bufSignSrc=$bufSignSrc."errorCode=".$errorCode."&";
	if($returnDatetime != "")
	$bufSignSrc=$bufSignSrc."returnDatetime=".$returnDatetime;
    $verifyResult=0;
    if($signMsg == strtoupper(md5($bufSignSrc."&key=".$md5key)))
    {
        $value = "报文验签成功！";
        $verifyResult=1;
    }
    else
    {
        $value = "报文验签失败！";
        $verifyResult=0;
    }
    $log='allinpay_'.date('Ymd').'.txt';
    file_put_contents($log,$value.';'.$verifyResult.';orderNo='.$orderNo."\r\n",FILE_APPEND);
    file_put_contents('test.txt',$verifyResult.';'.$value.';signMsg='.$signMsg.';param='.strtoupper(md5($bufSignSrc."&key=".$md5key)).';str='.$bufSignSrc."&key=".$md5key."\n",FILE_APPEND);
	//验签成功，还需要判断订单状态，为"1"表示支付成功。
	$payvalue = null;
	$pay_result = false;
	if($verifyResult==1 && $payResult == 1){
		$pay_result = true;
		$payvalue = "报文验签成功，且订单支付成功";
        file_put_contents($log,$payvalue.';orderNo='.$orderNo."\r\n",FILE_APPEND);
		/**
		 * 支付成功后，开始处理逻辑
		 * Edit By Lixinchao
		 * AddTime : 2017-03-03 17:18
		 */
		$dingdan = $orderNo;
		$mod_epay = &m('epay');
		$mod_epaylog = &m('epaylog');
		$model = &m();
		$payment_log = &m('paymentlog');
		$info .= "初始化完毕-wap-\r\n";
		if ($dingdan[0] == 'P') {
			$info .= "【积分订单处理】\r\n";
			$sgxt_order = $model->table('sgxt_order')->where(array('orderid' => $dingdan))->find1();
			if (empty($sgxt_order)) {
				file_put_contents($log,"【逻辑处理失败】：".$dingdan."订单不存在\r\n",FILE_APPEND);
				exit;
			}
			if ($sgxt_order['status'] == 0) {
				//修改订单状态
				$model->setBegin();
				$update = array(
					'paytype' => 'allinpay',
					'status' => 1,
					'pay_createtime' => time(),
					'pay_sn' => date('YmdHis') . rand(100, 999) . rand(1000, 9999),
				);
				$info .= "\t修改订单状态为1\r\n";
				$pass1 = $model->table('sgxt_order')->where(array('orderid' => $dingdan))->save($update);
				//更新用户积分字段
				$info .= "\t更新member表中pay_point字段\r\n";
				$pass2 = $model->table('member')->where(array('user_id' => $sgxt_order['userid']))->setInc('pay_point', $sgxt_order['num']);
				if ($pass1 & $pass2) {
					$payment_log->paymentlog($sgxt_order['userid'], $sgxt_order['truename'], $sgxt_order['amount'], 18, '', '', '', $dingdan);
					$info .= "\t更新成功----修改订单内容【订单号：".$dingdan.",sgxt_order更新数据：".json_encode($update)."】--------更新用户信息【用户id：".$sgxt_order['userid'].",pay_point字段增加数值：".$sgxt_order['num']."】\r\n";
					$info .= "\t保存支付记录\r\n";
					$model->commit();
					file_put_contents($log,"【逻辑处理成功】：".$info."\r\n",FILE_APPEND);
					exit;
				}else
				{
					$model->rollBack();
					$info .= "\t更新失败-wap-pass1=".var_dump($pass1).";pass2=".$pass2."---修改订单内容【订单号：".$dingdan.",sgxt_order更新数据：".json_encode($update)."】--------更新用户信息【用户id：".$sgxt_order['userid'].",pay_point字段增加数值：".$sgxt_order['num']."】\r\n";
					file_put_contents($log,"【逻辑处理失败】：".$info."\r\n",FILE_APPEND);
					exit;
				}
			}else
			{
				$info .= "\t订单已完成，不用处理\r\n";
				file_put_contents($log,$info."\r\n",FILE_APPEND);
				exit;
			}
		}
		$mod_order = &m('order');
		//根据用户返回的 order_sn 判断是否为订单
		$order_info = $mod_order->get('order_sn=' . $dingdan);
		$info .= "\t获取订单信息\r\n";
		if (!empty($order_info)) {
		    if($order_info['status']>=20)
            {
                $info .= "\t订单".$dingdan."已支付，不用处理\r\n";
                file_put_contents($log,$info."\r\n",FILE_APPEND);
                exit;
            }
			//如果存在订单号  则自动付款
			$order_id = $order_info['order_id'];
			/*$row_epay = $mod_epay->get("user_id='$user_id'");
			$buyer_name = $row_epay['user_name']; //用户名
			$buyer_old_money = $row_epay['money']; //当前用户的原始金钱
			$info .= "\t买家姓名：$buyer_name,买家原始金钱：$buyer_old_money\r\n";*/
			//从定单中 读取卖家信息
			$row_order = $mod_order->get("order_id='$order_id'");
			$order_order_sn = $row_order['order_sn']; //定单号
			$order_seller_id = $row_order['seller_id']; //定单里的 卖家ID
			$order_money = $row_order['order_amount']; //定单里的 最后定单总价格
			//读取卖家SQL
			$seller_row = $mod_epay->get("user_id='$order_seller_id'");
			$seller_id = $seller_row['user_id']; //卖家ID
			$seller_name = $seller_row['user_name']; //卖家用户名
			$seller_money_dj = $seller_row['money_dj']; //卖家的原始冻结金钱
			$info .= "\t卖家姓名：$seller_name,卖家冻结金钱：$seller_money_dj\r\n";
			//更新卖家的冻结金钱
			$seller_array = array(
				'money_dj' => $seller_money_dj + $order_money,
			);
			$seller_edit = $mod_epay->edit('user_id=' . $seller_id, $seller_array);
			$info .= "\t更新卖家金额【更新数据：".json_encode($seller_array)."】\r\n";
			$time=time();
			//买家添加日志
			$buyer_log_text = '购买商品店铺' . $seller_name;
			$buyer_add_array = array(
				'user_id' => $order_info['buyer_id'],
				'user_name' => $buyer_name,
				'order_id' => $order_id,
				'order_sn ' => $order_order_sn,
				'to_id' => $seller_id,
				'to_name' => $seller_name,
				'add_time' => $time,
				'type' => 20,
				'money_flow' => 'outlay',
				'money' => $order_money,
				'log_text' => $buyer_log_text,
				'states' => 20,
			);
			$mod_epaylog->add($buyer_add_array);
			$info .= "\t添加买家日志" . json_encode($buyer_add_array) . "\r\n";
			//卖家添加日志
			$seller_log_text = '出售商品买家' . $buyer_name;
			$seller_add_array = array(
				'user_id' => $seller_id,
				'user_name' => $seller_name,
				'order_id' => $order_id,
				'order_sn ' => $order_order_sn,
				'to_id' => $order_info['buyer_id'],
				'to_name' => $buyer_name,
				'add_time' => $time,
				'type' => 30,
				'money_flow' => 'income',
				'money' => $order_money,
				'log_text' => $seller_log_text,
				'states' => 20,
			);
			$mod_epaylog->add($seller_add_array);
			$info .= "\t添加卖家日志" . json_encode($seller_add_array) . "\r\n";
			//改变定单为 已支付等待卖家确认  status10改为20
			//更新定单状态
			$order_edit_array = array(
                'payment_id' => 18,
				'payment_name' => '通联支付',
				'payment_code' => 'allinpay',
				'pay_time' => $time,
				'out_trade_sn' => $returnDatetime,
				'status' => 20, //20就是 待发货了
			);
			$mod_order->edit($order_id, $order_edit_array);
			$info .= "\t更新订单状态" . json_encode($order_edit_array) . "\r\n";
            file_put_contents($log,$info."\r\n",FILE_APPEND);
		}
	}else{
	  $payvalue = "报文验签成功，但订单支付失败";
	}
		
?>
	<div style="padding-left:40px;">			
			<div>验证结果：<?=$value?></div>
			<div>支付结果：<?=$payvalue?></div>
			<hr/>
			<div>商户号：<?=$merchantId ?> </div>
			<div>商户订单号：<?=$orderNo ?> </div>
			<div>商户订单金额：<?=$orderAmount ?></div>
			<div>商户订单时间：<?=$orderDatetime ?> </div>
			<div>网关支付金额：<?=$payAmount ?></div>
			<div>网关支付时间：<?=$payDatetime ?></div>

	</div>	
 </body>
</html>
	
	
