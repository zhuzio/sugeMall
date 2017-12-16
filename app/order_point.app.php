<?php
/**
 * @Author: ruan
 * @Date:   2016-06-17 21:02:11
 * @Last Modified time: 2016-07-19 10:08:45
 */
class Order_pointApp extends MemberbaseApp {


    function __construct() {
        parent::__construct();
        
        //余额支付 
        $this->epay_mod = & m('epay');
        $this->userinfo = $_SESSION['user_info'];
        $this->model = & m();
    }
    public function pointOrder(){
        if(!IS_POST){
            $this->display('newapp/card.order.html');
        }else{
            $num = trim(I('post.num'));
            if($num <= 0) {
                $this->show_warning('购买积分必须大于0');
                return;
            }
       
             $pay_info = conf('PAY_INFO');

            $user = $this->model->table('member')->where(array('user_id' => $this->userinfo[user_id]))->find1();
            if($user['type'] != '2') {
                $this->show_warning('只有商家才能买积分');
                return ;
            }
            
            $order = 'P'.substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
            $amount = $pay_info['point'] * $num;

            $orderData = array(
                    'orderid' => $order,
                    'userid'  => $user['user_id'],
                    'mobile'  => $user['user_name'],
                    'truename' => $user['real_name'],
                    'price'   => $pay_info['point'],
                    'num'     => $num,
                    'order_type' => 'point',
                    'amount'  => $amount,
                    'createtime' => time()
            );
            $insid = $this->model->table('sgxt_order') ->add($orderData); 
            header("Location:index.php?app=order_point&act=paymentWay&orderid={$insid}"); 
        }
        
        
    }
    public function paymentWay(){
        $orderid = I('get.orderid');
        if(empty($orderid)){
            $this->show_warning('网络延迟，请重试');
            return ;
        }
        $order = $this->model->table('sgxt_order')->where(array('id' => $orderid,'userid' => $this->userinfo['user_id']))->find1();
        if(empty($order)){
            $this->show_warning('网络延迟，请重试1');
            return ;
        }
        $hash = get_hash();
        $this->assign('_hash_',$hash);
        
        //$wxpay =
        $this->assign('order' ,$order);

        $this->display('newapp/payment.way.html');
    }
}
 ?>