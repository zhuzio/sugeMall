<?php
/* 添加适合 BEGIN */
define('ROOT_PATH', dirname(__FILE__) . "/../..");
include(ROOT_PATH . '/eccore/ecmall.php');
/* 定义配置信息 */
ecm_define(ROOT_PATH . '/data/config.inc.php');
include(ROOT_PATH . '/eccore/model/model.base.php');
define('CHARSET', 'utf-8');
$settings = include(ROOT_PATH . '/data/settings.inc.php');
/* END */
define("APPID", $settings['epay_wx_appid']);
define("MCHID", $settings['epay_wx_mch_id']);
define("KEY", $settings['epay_wx_key']);
define("APPSECRET", $settings['epay_wx_secret']);
require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';
//  require_once 'log.php';
//初始化日志
$logHandler = new CLogFileHandler("logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify {
    //查询订单
    public function Queryorder($transaction_id) {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);
        Log::DEBUG("query:" . json_encode($result));
        if (array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return true;
        }
        return false;
    }
    //重写回调处理函数
    public function NotifyProcess($data, &$msg) {
        Log::DEBUG("call back:" . json_encode($data));
        $notfiyOutput = array();
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if (!$this->Queryorder($data["transaction_id"])) {
            $msg = "订单查询失败";
            return false;
        }
        return true;
    }
}
Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
//$xml = '<xml><appid><![CDATA[wxc3775cf2340d95b21213]]></appid>
//<attach><![CDATA[6020150419080428]]></attach>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[Y]]></is_subscribe>
//<mch_id><![CDATA[1224930902]]></mch_id>
//<nonce_str><![CDATA[d9hne9w1rstz1t4vkcurib99mmjo4dtl]]></nonce_str>
//<openid><![CDATA[od6X7t1pRxbXQkuSTPmXsowybuXM]]></openid>
//<out_trade_no><![CDATA[6020150419080428]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[AD3D9FAB01C6E30619A90E82C5F987D2]]></sign>
//<time_end><![CDATA[20150419200641]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[NATIVE]]></trade_type>
//<transaction_id><![CDATA[10015204642011504190075760847]]></transaction_id>
//</xml>';
$data = $notify->FromXml($xml);
if ($notify->NotifyProcess($data, $msg)) {
    Log::DEBUG("chenggong");
    
    $total_fee = $data["total_fee"]/100;
    $out_trade_no = $data["out_trade_no"];
    $time = time();
    $dingdan = $out_trade_no;
    $mod_epay = & m('epay');
    $mod_epaylog = & m('epaylog');
    $model = & m();
    $payment_log = & m('paymentlog');
      /************************************验证是否购买积分******************************************************/
   //验证是否为购物积分订单
    if($dingdan[0] == 'P'){
        Log::DEBUG("处理积分订单\r\n");
        $sgxt_order = $model -> table('sgxt_order')->where(array('orderid' => $out_trade_no))->find1();
        if(empty($sgxt_order)){
            return ;
        }     
        //订单未支付
        if($sgxt_order['status'] == 0){
            Log::DEBUG("\t积分订单未支付\r\n");
            //修改订单状态
            $model->setBegin();
            $update = array(
                'paytype' => 'wx',
                'status'  => 1,
                'pay_createtime' => time(),
                'pay_sn' => date('YmdHis').rand(100,999).rand(1000,9999),
                ); 
            $pass1 = $model->table('sgxt_order')->where(array('orderid' => $out_trade_no)) ->save($update);
            Log::DEBUG("\t修改订单信息（".json_encode($update)."）\r\n");
            //更新用户积分字段
            $pass2 = $model->table('member')->where(array('user_id' => $sgxt_order['userid'])) ->setInc('pay_point' , $sgxt_order['num']);
            Log::DEBUG("\t更新用户积分字段（pay_point=".$sgxt_order['num']."）\r\n");
            if($pass1 & $pass2){
                 $payment_log -> paymentlog($sgxt_order['userid'] , $sgxt_order['truename'] , $sgxt_order['amount'] , 5 , '' , '' , '' ,$out_trade_no);
                Log::DEBUG("\t更新成功，写入paymenglog日志，paymentid=5\r\n");
                $model->commit();
                die('success');
            }else{
                $model->rollBack();
                Log::DEBUG("\t更新失败，数据回滚\r\n");

                die;
            }
        }
    }

    /******************************************************************/
    //根据用户订单号，获取充值者的ID
    /*
    $row_epay_log = $mod_epaylog->get("order_sn='$dingdan'");
    if(!empty($row_epay_log) ){
            if ($row_epay_log['complete'] == '1') {
                return;
            }
            $user_id = $row_epay_log['user_id'];
            //获取用户的余额
            $row_epay = $mod_epay->get("user_id='$user_id'");
            //计算新的余额
            $old_money = $row_epay['money'];
            $new_money = $old_money + $total_fee;
            $edit_money = array(
                'money' => $new_money,
            );
            $mod_epay->edit('user_id=' . $user_id, $edit_money);
            //修改记录
            $edit_epaylog = array(
                'add_time' => $time,
                'money' => $total_fee,
                'complete' => 1,
                'states' => 61,
            );
            $mod_epaylog->edit('order_sn=' . '"' . $dingdan . '"', $edit_epaylog);
    }
    */
    Log::DEBUG("处理非积分订单\r\n");
    //---------------------  以下是判断  是否启用 自动付款----------------------
    $mod_order = & m('order');
    //根据用户返回的 order_sn 判断是否为订单
    $order_info = $mod_order->get('order_sn=' . $dingdan);
    if (!empty($order_info)) {
        Log::DEBUG("\t存在订单，开始处理\r\n");
        //如果存在订单号  则自动付款
        $order_id = $order_info['order_id'];
        $row_epay = $mod_epay->get("user_id='".$order_info['buyer_id']."'");
        $buyer_name = $row_epay['user_name']; //用户名
        $buyer_old_money = $row_epay['money']; //当前用户的原始金钱
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
        /*
        //检测余额是否足够
        if ($buyer_old_money < $order_money) {   //检测余额是否足够 开始
            Log::DEBUG("\t余额不足支付，退出\r\n");
            return;
        }
        //扣除买家的金钱
        $buyer_array = array(
            'money' => $buyer_old_money - $order_money,
        );
        $mod_epay->edit('user_id=' . $user_id, $buyer_array);
        */
        //更新卖家的冻结金钱
        $seller_array = array(
            'money_dj' => $seller_money_dj + $order_money,
        );
        $seller_edit = $mod_epay->edit('user_id=' . $seller_id, $seller_array);
        Log::DEBUG("\t更新商家的冻结金钱\r\n");
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
        Log::DEBUG("\t添加买家epaylog日志\r\n");
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
        Log::DEBUG("\t添加卖家epaylog日志\r\n");
        //改变定单为 已支付等待卖家确认  status10改为20
        $payment_code = "zjgl";
        $payment_name = '微信支付';
        //$o2o = 'offline';
        if($order_info['payment_id'] == 8){
            $payment_name = '微信支付';
        }else if($order_info['payment_id'] == 14){
            $payment_name = '移动微信支付';
        }else if($order_info['payment_id'] == 13){
            $payment_name = '微信扫码支付';
        }

        //更新定单状态
        $order_edit_array = array(
            'payment_name' => $payment_name,
            'payment_code' => $payment_code,
            'pay_time' => $time,
            'out_trade_sn' => $dingdan,
            'status' => 20, //20就是 待发货了
        );
        $mod_order->edit($order_id, $order_edit_array);
        Log::DEBUG("\t更新订单状态以及其他内容【".json_decode($order_edit_array)."】\r\n");
        if($order_info['point'] > 0){
			$point_mod = & m('point');
			$point_mod->sendPoint($row_epay['user_name'],$order_info['point'],$order_info['seller_id'],$order_info,'online');
            Log::DEBUG("\t订单赠送积分：".$order_info['point'].",开始执行发送积分流程\r\n");
		}
        Log::DEBUG("处理结束\r\n");
		die('success');
     }
  
   
 
}
?>