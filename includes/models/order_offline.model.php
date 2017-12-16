<?php
class Order_offlineModel extends BaseModel
{
    //创建线下订单
    public function createOrder($buyid , $sellerid ,$paymentid ,$money ,$point , $classid , $paymess = '' ,$status=40){
        $model = & m();
        //获取买家信息
        $buyinfo = $model ->table('member') -> where(array('user_id'=>$buyid)) -> find1();
        //获取卖家信息
        $sellername = $model ->table('store') -> where(array('store_id'=>$sellerid , 'state' => 1)) -> getField('store_name');
        //获取支付类型
        $payment =  $model -> table('payment') -> where(array('payment_id' => $paymentid)) -> find1();

        //获取分类信息
        $classname = $model -> table('sgxt_class_goods') -> where(array('class_id' => $classid)) -> getField('name');

        //封装生成数据
        $order_sn = substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);


        $orderdata = array(
            'order_sn'      =>  $order_sn,
            'seller_id'     =>  $sellerid,
            'seller_name'   =>  $sellername,
            'buyer_id'      =>  $buyid,
            'buyer_name'    =>  $buyinfo['real_name']?$buyinfo['real_name']:$buyinfo['user_name'],
            'buyer_email'   =>  $buyinfo['email'],
            'status'        =>  $status,
            'add_time'      =>  time(),
            'payment_id'    =>  $paymentid,
            'payment_name'  =>  $payment['payment_name'],
            'payment_code'  =>  $payment['payment_code'],
            'pay_time'      =>  time(),
            'pay_message'   =>  $paymess,
            'goods_amount'  =>  $money,
            'order_amount'  =>  $money,
            'point'         =>  $point,
            'classid'       =>  $classid,
            'classname'     =>  $classname

            );
       $insid = $model -> table('order_offline') -> add($orderdata);
       if($insid){
            $order['orderid'] = $insid;
            $order['order_sn'] = $order_sn;
            return $order ;
       }
    }

}






?>