<?php
/**
 * @Author: fzq
 * @Date:   2016-06-28  21:54
 * @desc:   线下订单
 */
header('Content-type:text/html;charset=utf-8');
class offline_orderApp extends MemberbaseApp{
    var $_feed_enabled = false;
    var $point_mod ;
    function __construct() {
        $this->MemberApp();
        $this->userinfo = $_SESSION['user_info'];
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        $this->epay_mod = & m('epay');
        //实例化一个空的model类
        $this->model = & m();
    }

    //用户线下订单
    public function index(){
    	
    		$user_id =$this->userinfo['user_id'];   

    		$order =$this->model->table('order_offline')->field('order_sn,pay_time,seller_id,seller_name,payment_name,classname,goods_amount,point,pay_message')->order('order_id desc')->where(array('buyer_id'=>$user_id))->select();
            
    		foreach ($order as $key=>$val){
    			$val['pay_time'] =date('Y-m-d H:i',$val['pay_time']);
    			$order[$key] =$val;
    		}

    		$this->assign('myorder',$order);
    		$this->display('newapp/offline.order.html');
    	
    }


    //商家线下订单
    public function business_order(){
    	
    		if($this->userinfo['type'] ==2){
                $user_id =$this->userinfo['user_id'];   
                $orders =$this->model->table('order_offline')->field('order_id,order_sn,status,add_time,pay_time,seller_id,seller_name,buyer_name,payment_name,classname,order_amount,point,pay_message')->where(array('seller_id'=>$user_id))->order('order_id desc')->select();
                foreach ($orders as $k=>$v){
                    $v['at'] = $v['add_time'];
                    $v['pt'] = $v['pay_time'];
                    $v['add_time'] =date('Y-m-d H:i',$v['add_time']);
                    $v['pay_time'] =date('Y-m-d H:i',$v['pay_time']);

                    $orders[$k] =$v;
                }
               $this->assign('orderlist',$orders);  
    		   $this->display('newapp/order.line.html');
    		}
    		
    }

    //订单详情
    public function order_detail(){
        $order_id =$_GET['id'];
        //查询积分分配
        $detail =$this->model->table('sgxt_get_point')->field('oto,shops_point,system_point')->where(array('order_id'=>$order_id))->find1();
        //用户电话
        $phone =$this->model->query("select o.buyer_id,o.order_id,m.user_id,m.user_name from ecm_order_offline as o join ecm_member as m on o.buyer_id =m.user_id where o.order_id ={$order_id}");
       foreach($phone as $key=>$val){
            if(is_array($val)){
            foreach ($val as $k => $v) {
                        $phone[$k] =$v;
                    }   
                }else{
                        $phone[$key] =$val;    
                }
                
       }
       //订单详情
        $detail2 =$this->model->table('order_offline')->field('order_id,order_sn,status,buyer_name,classname,payment_name,order_amount,pay_message,add_time,pay_time')->where(array('order_id'=>$order_id))->find1();
        $detail2['add_time'] = date('Y-m-d H:i',$detail2['add_time']);
        $this->assign('phone',$phone);
        $this->assign('detail',$detail);
        $this->assign('detail2',$detail2);
        $this->display('newapp/line.details.html');
    }

    //取消订单
    public function cancel_order(){
        $o_id =$_GET['id'];
        $data =array('status'=>0);
        $order_cancel =$this->model->table('order_offline')->where(array('order_id'=>$o_id))->save($data);
        if($order_cancel){
            $this->show_success('取消成功','index.php?app=offline_order&act=business_order');
            
        }else{
            $this->show_error('取消失败','index.php?app=offline_order&act=business_order');

        }

    }

    //二维码
    public function createQrcode(){
        import('phpqrcode');
        $value = $_GET['url'];
        $length = $_GET['length']?$_GET['length']:4;
        $errorCorrectionLevel = "L";
        $matrixPointSize = $length;
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);

    }
 

















} 

?>