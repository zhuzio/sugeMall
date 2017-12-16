<?php 
//支付日志记录表
class paymentlogModel extends BaseModel{

    public function paymentlog($userid , $username ,$money , $paymentid, $toid ='' ,$toname ='',$orderid='' ,$ordersn=''){
        $model = &m();
        $user = $model->table('member')->where('user_id='.$userid)->find1();
        $log = $this->logtext($paymentid,$money);
        $data = array(
            'user_id' => $userid,
            'user_name' => $username,
            'order_id'  => $orderid,
            'order_sn'   => $ordersn,
            'to_id'     =>  $toid,
            'to_name'    => $toname,
            'payment_id' => $paymentid,
            'payment_name' => conf('paymentlog/'.$paymentid),
            'money'     =>  $money,
            'log_text'   => $log,
            'add_time'   => time(),
            'province' => $user['province'],
            'city' => $user['city'],
            'area' => $user['area'],
            );

       return $model->table('paymentlog') -> add($data);
    }
    public function logtext($paymentid , $money){
        $log = '';
        switch($paymentid){
            case '1' : $log = '你在商城使用余额成功支付一笔订单，支付金额为'.$money.'元';break;
            case '2' : $log = '你在商城使用微信支付成功支付一笔订单，支付金额为'.$money.'元';break;
            case '3' : $log = '你在联盟商家使用购物积分成功支付一笔订单，支付金额为'.$money.'元';break;
            case '4' : $log = '你在联盟商家使用现成功支付一笔订单，支付金额为'.$money.'元';break;
            case '5' : $log = '你在苏格联盟使用微信支付成功购买'.$money.'元的积分';break;
            case '6' : $log = '你在苏格联盟使用货款成功购买'.$money.'元的积分';;break;
            case '7' : $log = '你在苏格联盟成功提现收益'.$money.'元';break;
            case '8' : $log = '你在苏格联盟成功提现货款'.$money.'元';break;
            case '9' : $log = '你在啥都行联盟使用连连支付成功购买'.$money.'元的积分';break;
        }
        return $log;
    }
}

 ?>