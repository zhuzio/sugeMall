<?php 
class PointApp extends MemberbaseApp {

    var $_feed_enabled = false;
    var $point_mod ;
    function __construct() {
        $this->MemberApp();
        
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        //余额支付 
        $this->epay_mod = & m('epay');
        //积分模型
        $this->point_mod = & m('point');
        //实例化一个空的model类
        $this->model = & m();
        //线下订单
        $this->order_offline = & m('order_offline');
        $this->userinfo = $this->model->table('member') -> where(array('user_id' => $_SESSION['user_info']['user_id'])) ->find1();

    }
 
    //商户发积分的方法
    function sendPoint(){
        if($this->userinfo['type'] != 2){
                $this->show_warning('非商家用户不能发积分');
                return ;
            }
        if(!IS_POST){
            $pay_point = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->getField('pay_point');
            $class_goods = $this->model->table('sgxt_class_goods')->where(array('store_id' => $this->userinfo['user_id']))->select();
            $hash = get_hash();
            $this->assign('_hash_',$hash);
            $this->assign('pay_point', $pay_point);
            $this->assign('class_goods' , $class_goods);
            $this->display('newapp/shop.topoint.html');
        }else{
            //商家发积分

            $mobile = trim($_POST['mobile']);
            $point = intval($_POST['point']);
            $passwd = trim($_POST['passwd']);
            $money = trim(I('post.money'));
            $classid = I('post.classid');
            $remark = I('post.remark');
            if(empty($mobile)){
                 $this->show_warning('手机号不能为空');
                 return ;
            }
            if(empty($point)){
                 $this->show_warning('积分不能为空');
                 return ;
            }
            if($point < 2){
                $this->show_warning('不能发1积分');
                 return ;
            }
            if(!is_int($point)){
                $this->show_warning('赠送积分必须为整数');
                 return ;
            }
            if(empty($passwd)){
                 $this->show_warning('密码不能为空');
                 return ;
            }
            $get_user_info = $this->model->table('member')->where(array('user_name' => $mobile)) ->find1();
            if(empty($get_user_info)){
                $this->show_warning('你输入的电话有误');
                 return ;
            }
            if(empty($classid)){
                $this->show_warning('请选择商品类型');
                 return ;
            }
            if(empty($money)){
                $this->show_warning('请输入商品价格');
                 return ;
            }

            //验证密码是否正确
            $sqlPassword = $this->model->table('epay') ->where(array('user_id' => $this->userinfo['user_id'])) ->getField('zf_pass'); 
            if(md5($passwd) != $sqlPassword){
                $this->show_warning('支付密码错误');
                 return ;
            }
            //验证商家积分是足够
            $pay_point = $this->model->table('member')-> where(array('user_id' => $this->userinfo['user_id'])) -> getField('pay_point');
            //执行发积分的方法
            $check_point = conf('PAY_INFO/shops_point')*$point;
            if($pay_point < $check_point){
                 $this->show_warning('积分不足，无法发送');
                  return ;
            }
            $order = $this->order_offline->createOrder($get_user_info['user_id'] ,$this->userinfo['user_id'] , 9 , $money , $point , $classid , $remark , 11);
            $pass =  $this->point_mod->sendPoint($mobile,$point,$this->userinfo['user_id']  ,$order , 'offline');

            if(!$pass){
                $this->show_warning('积分发送失败，请重新发送');
                 return ;
            }else{
                //积分发送成功后创建订单

                 $this->show_success('积分发送成功','index.php?app=point&act=shopSendPointList&action=send');

            }
        }
    }
    //验证返回用户真实姓名
    public function chechName(){
        echo $this->model->table('member')->where(array('user_name' => trim($_REQUEST['mobile'])))->getField('real_name');
    }
    //验证积分返回商家发送和平台发送
    public function checkPoint(){
        $array['allPoint'] = I('get.point');
        $array['shopPoint'] = round($array['allPoint']*conf('PAY_INFO/shops_point'));
        $array['pingPoint'] = round($array['allPoint']*conf('PAY_INFO/system_point'));
        echo json_encode($array);
    }
    //用户收积分明细
    public function userGetPoint(){
        $users = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->find1();
        $point['bean'] = $users['point_peac'];
        $point['point'] = $users['point'];
        $point['allPoint'] = $this->model->table('sgxt_get_point')->where(array('getid' => $this->userinfo['user_id']))->sum('point');
        $point['allPoint'] = $point['allPoint']?$point['allPoint']:0;
        $point['agent_money'] = $this->userinfo['agent_money'];
        $this->assign('point' , $point);
        $this->assign('user_type',$users);
        $action = I('get.action');
        $this->assign('action' , $action );
        if($action == 'bean'){
            $count = $this->model->table('sgxt_bean')->where(array('user_id' => $this->userinfo['user_id']))  ->count();
            $beanList = $this->model->table('sgxt_bean') ->where(array('user_id' => $this->userinfo['user_id'])) ->order('id desc')->page($count)->select();
            foreach($beanListas as $key=>$list){
                $beanList[$key]['createtime'] = date('Y-m-d H:i' ,$list['createtime']);
            }
            $showpage = $this->model->getButton();
            $this->assign('showpage' , $showpage);

            $this->assign('beanList' , $beanList);
        }elseif($action == 'point'){
           
            $count = $this->model->table('sgxt_get_point') -> where(array('getid' => $this->userinfo['user_id'])) ->count();
            $pointList = $this->model->table('sgxt_get_point') -> where(array('getid' => $this->userinfo['user_id']))->page($count) ->order('id desc')->select();

            foreach($pointList as $key=>$list){
                 $pointList[$key]['createtime'] = date('Y-m-d H:i' ,$list['createtime']);
            }
            
            $showpage = $this->model->getButton();
            $this->assign('pointList' , $pointList);
            $this->assign('showpage' , $showpage);
        }
        $this->display('newapp/user.get.point.html');
    }

    //我的积分
    public function my_point(){
        $users = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->find();
        $point['bean'] = $users['point_peac'];
        $point['point'] = $users['point'];
        $point['allPoint'] = $this->model->table('sgxt_get_point')->where(array('getid' => $this->userinfo['user_id']))->sum('point');
        $this->assign('point' , $point);
        $this->display('newapp/integral.html');
    }


    //商家发积分
    public function shopSendPointList(){
        $point['sendPoint'] = $this->model->table('sgxt_get_point')->where(array('sendid' => $this->userinfo['user_id']))->sum('shops_point');
        $point['currentPoint'] = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->getField('pay_point');
        $point['allPoint'] = $this->model->table('sgxt_order')->where(array('userid' =>$this->userinfo['user_id'] , 'status' => 1 )) ->sum('amount') ;
        $this->assign('point' , $point);

        $action = I('get.action');
        $this->assign('action' , $action );

        if($action == 'send'){
            $count = $this->model->table('sgxt_get_point') -> where(array('sendid' => $this->userinfo['user_id'])) ->count();
            $pointList = $this->model->table('sgxt_get_point') -> where(array('sendid' => $this->userinfo['user_id']))->page($count) ->order('id desc')->select();
            foreach($pointList as $key=>$val){
                $pointList[$key]['createtime'] = date('Y-m-d H:i' , $val['createtime']);
            }
            $showpage = $this->model->getButton(1);

            $this->assign('pointList' , $pointList);
            $this->assign('showpage' , $showpage);

        }elseif($action == 'pay'){
            $count = $this->model->table('sgxt_order') -> where(array('userid' => $this->userinfo['user_id'] , 'status' => 1)) ->count();
            $orderList = $this->model->table('sgxt_order') -> where(array('userid' => $this->userinfo['user_id'] , 'status' => 1))->order('id desc')->page($count) ->select();
            if(!empty($orderList)){
                foreach($orderList as $key=>$val){
                 $orderLis[$key]['createtime'] = date('Y-m-d H:i' , $val['createtime']);
                 $orderList[$key]['pay_createtime'] =date('Y-m-d H:i',$val['pay_createtime']);
                }
            }

            $showpage = $this->model->getButton(1);

            $this->assign('orderList' , $orderList);
            $this->assign('showpage' , $showpage);
        }
        $this->display('newapp/shop.send.point.list.html');
        
    }

}
 ?>