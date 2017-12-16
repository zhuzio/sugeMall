<?php
error_reporting(0);
require_once 'util.php'; 
require_once 'config.php';

define('ROOT_PATH', "/home/ftp/1520/shadouxing_net-20160710-oah/shadouxing.net/");
include(ROOT_PATH . '/eccore/ecmall.php');
/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');
include(ROOT_PATH . '/eccore/model/model.base.php');
define('CHARSET', 'utf-8');
$settings = include(ROOT_PATH . '/data/settings.inc.php');


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
	      $order['userid'] = $orderinfo['userid'];
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
	      $order['userid'] = $orderinfo['buyer_id'];
	      $order['truename'] = $orderinfo['buyer_name'];
	      $order['cz_money'] = $orderinfo['order_amount'];
	 }
	 return $order;
}




file_put_contents('peapal_log.txt',"融宝支付回调【".date('Y-m-d H:i:s',time())."】\r\n",FILE_APPEND);
$merchantId = $merchant_id;
$data = urldecode($_REQUEST['data']);
$data = str_replace(" ", "+",$data);
$encryptkey = $_REQUEST['encryptkey'];
//$data='GbZpznbdKpIgaiNGrDX3kjZcXBY8Wq8PpFTYl3xBnzaPyAFprIzaY9o+RLWZAIKcbZG9z1QCqwdnWrX46ZZHvPx0Semmw/qRDm/tkJcXD9Wje6IPyIFr8bWMZvvrS9PJwG74Cc/xQ8s4U8ABCO+XHo8duJQV+fbh4q+wdnF9TR9RuaM/n4ZCkdqspXf8c3+6U+avNS06fNIlT2CHq5ClqAoholhrMdq/JQc/a/dOotdISlGXvB4tiYQovqFwX/qs';
//$encryptkey ='c7gCy7fEUjrFxpUi+wXBaojD20dpEZl8AJw4dCc4G/7hFGOUM03wmQrpRzhxDoARHU19t9c1uz0gAdRyPecbgJk3UxhjJoPQvam7Hi/A7a2sIjAeWVvSfEheVxMZlx+55fLbOni3fTTwuhX0ZiMExI0riKn/3KKpLYrwieGjDIgKviagHjRNwemutr+NrAH0sxNv0oZOS80H4NH2Wa3vgrQVdZ07LD//CdngkJ1806cIxLM8JBKyHvlCouQTp5dM6EVUSWq3iq4xkm/SXG2ZbXWGtMW62j4d+H9wY9fmq6Uq7Psf20ycy+59hEDb5fc2e8ebnSQiTkiEZlaj0XML1A==';
$encryptkey = RSADecryptkey($encryptkey,$merchantPrivateKey);
file_put_contents('peapal_log.txt','【$data】' . $data."\r\n",FILE_APPEND);
file_put_contents('peapal_log.txt','【$merchantPrivateKey】' . $merchantPrivateKey."\r\n",FILE_APPEND);
file_put_contents('peapal_log.txt','【$_REQUEST】' . json_encode($_REQUEST)."\r\n",FILE_APPEND);
file_put_contents('peapal_log.txt','【$encryptkey】：' . $encryptkey."\r\n",FILE_APPEND);
$decryData = AESDecryptResponse($encryptkey, $data);

$jsonObject = json_decode($decryData,true);

//var_dump($jsonObject);//打印出结果不成功
file_put_contents('peapal_log.txt','【$jsonObject】：' . $decryData . "\r\n",FILE_APPEND);
$merchant_id = $jsonObject['merchant_id'];
$trade_no = $jsonObject['trade_no'];
$order_no = $jsonObject['order_no'];
$total_fee = $jsonObject['total_fee'];
$status = $jsonObject['status'];
$result_code = $jsonObject['result_code'];
$result_msg = $jsonObject['result_msg'];
$sign = $jsonObject['sign'];
$notify_id = $jsonObject['notify_id'];
$paramarr = array(
	'merchant_id' => $merchant_id,
	'trade_no' => $trade_no,
	'order_no' => $order_no,
	'total_fee' => $total_fee,
	'status' => $status,	
	'notify_id' => $notify_id
);
if($result_code){
	$paramarr['result_code'] = $result_code;		
}
if($result_msg){
	$paramarr['result_msg'] = $result_msg;		
}
$mysign = createSign($paramarr, $key);
//echo "mysign:".$mysign;
//echo "sign:".$sign;	
file_put_contents('peapal_log.txt','【$mysign】：' . $mysign."\r\n",FILE_APPEND);
file_put_contents('peapal_log.txt','【$sign】：' . $sign."\r\n",FILE_APPEND);
if ($status === "TRADE_FINISHED"){	
	$verifyStatus = "success";
	/**
	 * 支付成功后，开始处理逻辑
	 * Edit By Lixinchao
	 * AddTime : 2017-03-03 17:18
	 */
	$no_order = $order_no;
	$model = & m();
	$dingdan = $no_order;
    $mod_epay = &m('epay');
    $mod_epaylog = &m('epaylog');
    $model = &m();
    $payment_log = &m('paymentlog');
    $info .= "初始化完毕-wap-\r\n";
    if ($dingdan[0] == 'P') {
        $info .= "【积分订单处理】\r\n";
        $sgxt_order = $model->table('sgxt_order')->where(array('orderid' => $dingdan))->find1();
        if (empty($sgxt_order)) {
            file_put_contents('peapal_log.txt',"【逻辑处理失败】：订单不存在\r\n",FILE_APPEND);
            echo 'success';
            exit;
        }
        if ($sgxt_order['status'] == 0) {
            //修改订单状态
            $model->setBegin();
            $update = array(
                'paytype' => 'reapal',
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
                $payment_log->paymentlog($sgxt_order['userid'], $sgxt_order['truename'], $sgxt_order['amount'], 9, '', '', '', $dingdan);
                $info .= "\t更新成功----修改订单内容【订单号：".$dingdan.",sgxt_order更新数据：".json_encode($update)."】--------更新用户信息【用户id：".$sgxt_order['userid'].",pay_point字段增加数值：".$sgxt_order['num']."】\r\n";
                $info .= "\t保存支付记录\r\n";
                $model->commit();
                file_put_contents('peapal_log.txt',"【逻辑处理成功】：".$info."\r\n",FILE_APPEND);
                echo 'success';
                exit;
            }else
            {
                $model->rollBack();
                $info .= "\t更新失败-wap-pass1=".var_dump($pass1).";pass2=".$pass2."---修改订单内容【订单号：".$dingdan.",sgxt_order更新数据：".json_encode($update)."】--------更新用户信息【用户id：".$sgxt_order['userid'].",pay_point字段增加数值：".$sgxt_order['num']."】\r\n";
                file_put_contents('peapal_log.txt',"【逻辑处理失败】：".$info."\r\n",FILE_APPEND);
                echo 'success';
                exit;
            }
        }else
        {
            $info .= "\t订单已完成，不用处理\r\n";
            file_put_contents('peapal_log.txt',"【逻辑处理失败】：".$info."\r\n",FILE_APPEND);
            echo 'success';
            exit;
        }
    }
    $mod_order = &m('order');
    //根据用户返回的 order_sn 判断是否为订单
    $order_info = $mod_order->get('order_sn=' . $dingdan);
    $info .= "\t获取订单信息\r\n";
    if (!empty($order_info)) {
        //如果存在订单号  则自动付款
        $order_id = $order_info['order_id'];
        $row_epay = $mod_epay->get("user_id='$user_id'");
        $buyer_name = $row_epay['user_name']; //用户名
        $buyer_old_money = $row_epay['money']; //当前用户的原始金钱
        $info .= "\t买家姓名：$buyer_name,买家原始金钱：$buyer_old_money\r\n";
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
            'payment_name' => '融宝支付',
            'payment_code' => 'reapal',
            'pay_time' => $time,
            'out_trade_sn' => $order_sn,
            'status' => 20, //20就是 待发货了
        );
        $mod_order->edit($order_id, $order_edit_array);
        $info .= "\t更新订单状态" . json_encode($order_edit_array) . "\r\n";
    }
    file_put_contents('peapal_log.txt',"【逻辑处理】：".$info."\r\n",FILE_APPEND);

}else {
	$verifyStatus = "fail";
}
file_put_contents('peapal_log.txt',"融宝支付回调结束【".date('Y-m-d H:i:s',time())."】：".$verifyStatus."\r\n",FILE_APPEND);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>支付成功客户端返回</title>
</head>
<body>
	<div>
		<?php echo $verifyStatus; ?>
	</div>
</body>
</html>
