<?php
/**
 * @Author: ruan
 * @Date:   2016-06-17 21:02:11
 * @Last Modified time: 2016-07-25 17:14:10
 */
class New_memberApp extends MemberbaseApp {

    var $_feed_enabled = false;

    function __construct() {
        $this->MemberApp();
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        //余额支付 
        $this->model = & m();
        $this->epay_mod = & m('epay');
        $this->userinfo = $this->model->table('member') -> where(array('user_id' => $_SESSION['user_info']['user_id'])) ->find1();

//        if(!$this->userinfo){
        //        $_SESSION['user_info'] = array();
//            $this->show_warning('请重新登录','index.php?app=member&act=login');
//        }
        
    }

    
    /****新增会员模块**/
    // 个人中心
    public function index(){
        //调取微信扫一扫
        
        import('jssdk.lib');
        $jssdk = new JSSDK(conf('epay_wx_appid'), conf('epay_wx_secret'));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage' , $signPackage );
        //用户信息
        $userinfo = $this->model->table('member')->where(array('user_id' => $this->userinfo[user_id]))->find1();

        //计算当前用户购物积分
        $month= date('Ym');
        $shoping_point['current'] = $this->model->table('epay')-> where(array('user_id' => $this->userinfo[user_id]))->getField('money');
        //当月购物积分
        $shoping_point['month'] = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo['user_id'] , 'times' => $month , 'is_clearing' => 0))->sum('get_money');
        $shoping_point['month']=$shoping_point['month']?$shoping_point['month']:'0.00';
        //累计购物积分
        $shoping_point['all'] = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo['user_id']))->sum('get_money');
        //根据不同的配置文件显示
        $shoping_point['all'] = $shoping_point['all']?$shoping_point['all']:'0.00';
        $this->assign('shoping_point' , $shoping_point);

        $point['point'] = $userinfo['point'];
        $point['point_peac'] = $userinfo['point_peac'];
        $point['all_point'] = $this->model->table('sgxt_get_point')-> where(array('getid' => $this->userinfo['user_id'])) -> sum('point');
        $point['all_point']=$point['all_point']?$point['all_point']:0;
        $this -> assign('point' , $point);

        //收益
        $treasure = $this->model->table('epay')-> where(array('user_id' => $this->userinfo[user_id]))->find1();
        $earnings['current'] = $treasure['earnings'];
        $earnings['freeze'] = $treasure['freeze_earnings'];
        $this->assign('earnings' , $earnings);


        if($this->userinfo['type'] == 2){
            $top_baner = conf('shops_center_list');
            $this->assign('top_bander' , $top_baner);
        }

        $this->assign('public_bander' , conf('my_common_list'));
        $role = conf("user_type_center/".$this->userinfo['type']);
        $act = conf('my_center_list');
        if(!empty($role)){
            $role = explode(',', $role);
            foreach($role as $ro){
                $piv_baner[] = $act[$ro];
            }
            $this -> assign('piv_bander' , $piv_baner);
        }

        //显示推荐人
        /*if($this->userinfo['type'] == 2){
            $rs =$this->model->table('member')->field('sj_zhitui')->where(array('user_id'=>$this->userinfo['user_id']))->find1();
            //根据字段自推人id值查询用户名
            $rs_id =implode($rs);
            $zt_user =$this->model->table('member')->field('real_name')->where(array('user_id'=>$rs_id))->find1();
            $this->assign('tuijian',$rs);
            $this->assign('tuijian_u',$zt_user);
        }*/

        //显示升级请求


        //返回身份信息
       $type = array();
       $type['id'] = $this->userinfo['type'];
       $type['true_name'] = $this->userinfo['real_name']?$this->userinfo['real_name']:$this->userinfo['user_name'];
       $type['name'] = conf('user_type/'.$this->userinfo['type']);
       $type['img'] = './themes/wapmall/default/styles/default/images/member/'.$type['id'].'.png';
       if($this->userinfo['type'] ==1 || $this->userinfo['type']==3){
            $type['is_up'] = 1;
       }

       
       $this->assign('type',$type);
       $this->display('newapp/index.html');

    }


    //添加推荐人
    public function add_tuijian(){
        if(!IS_POST){
             $this->display('newapp/introducer.html');
        }   
    }


    public function select_intro(){   
        $phone_num =trim($_GET['tj_phone']);
        if(empty($phone_num)){
            return;
        }
        $res =$this->model->table('member')->field('user_id,sj_zhitui,real_name,phone_tel,phone_mob,user_name')->where(array('user_name'=>$phone_num))->find1();
        if($res){
            echo json_encode($res);
        }
  
    } 

    //插入推荐人
    public function insert_intro(){
        // var_dump($_GET['uname']);die;
        $id_uname =trim($_GET['uname']);
        // 根据手机号(用户名)拿到id
        $id =$this->model->table('member')->field('user_id')->where(array('user_name'=>$id_uname))->find1();
        $str_id =implode($id);
        $data =array('sj_zhitui'=>$str_id);
        //插入推荐人id
        $rs =$this->model->table('member')->where(array('user_id'=>$this->userinfo['user_id']))->save($data);
        if($rs){
            echo 1;  //成功
        }else{
            echo 0;  //失败
        }
    }

    //查询当前用户类型
    public function user_type(){
        if(!IS_POST){
            $rs =$this->model->table('member')->field('type')->where(array('user_id'=>$this->userinfo['user_id']))->find1(); 
            echo json_encode($rs);
        }
       
    }

    public function earnings(){
        //上月开始时间
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期
        $this->assign('endtime',date('Y-m-d H:i:s',$end));
        //获取用户当前收益
        
        $this->assign('real_name',$this->userinfo['real_name']);
        $userTreasure = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->find1();
        $earnings['current'] = $userTreasure['earnings']; //当前收益
        $earnings['freeze'] = $userTreasure['freeze_earnings']; //冻结收益
        //$earnings['thaw'] = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'< $end")->sum('remain_money');
        $earnings['thaw'] = $this->model->table('sgxt_profit')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->sum('remain_money');        
        
        $earnings['thaw']=$earnings['thaw']?$earnings['thaw']:'0.00';

        $this->assign('earnings' , $earnings);
        $rbac = conf("user_earnings_rbac/".$this->userinfo[type]);
        $rbac = explode(',', $rbac);
        foreach($rbac as $rb){
            
            $money = conf("user_earnings/".$rb);
            $money['money'] = $this->get_model_earnings($rb);
            $bander[] = $money;
        }

        $this->assign('bander' , $bander);
        $this->display('newapp/profit.sale.html');
    }
    //收益解冻
    public function earningsUnfreeze(){
        //判断当前是不是解冻时间
        if(!IS_POST){
           echo '非法访问';
            die; 
        }
        $today = date('d');
        if($today<15){
            echo '每月15号以后为解冻时间';
            die;
        }
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期 
        
        //$money = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'< $end")->sum('remain_money');
        $money = $this->model->table('sgxt_profit')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->sum('remain_money');

        if(empty($money)){
            echo '暂时没有可解冻的收益';
            die ;
        }
        $upid = $this->model->table('sgxt_profit')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->getField('id',true);
        if(count($upid) == 1){
            $upid = $upid[0];
        }else{
           $upid = implode(',',$upid ); 
        }
        
        //将收益增加到余额中
        $this->model->setBegin();
        $pass1 = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setInc('earnings' , $money);
        $pass2 = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setDec('freeze_earnings' , $money);
        if($pass1 && $pass2 ){
            $this->model->table('sgxt_profit')->where("id in ($upid) and createtime<".$end) ->save(array('is_clearing' => 1));
            $this->model->commit();
            
            //增加解冻记录
            $insdata = array(
                'userid' => $this->userinfo['user_id'],
                'user_name' => $this->userinfo['real_name'],
                'money' => $money,
                'type' => 2,
                'createtime' => time(),
                );
            $this->model->table('sgxt_unfreeze')->add($insdata);
        }else{
             $this->model->rollBack();
              echo '收益解冻失败';
              die ;
        }
        
       echo '1';
    }
    //余额解冻
    public function balanceUnfreeze(){
        //判断当前是不是解冻时间
        if(!IS_POST){
           echo '非法访问';
            die; 
        }
        $today = date('d');
        if($today<15){
            echo '每月15号以后为解冻时间';
            die;
        }
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期 

        //$money = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'< $end")->sum('get_money');
        $money = $this->model->table('sgxt_balance')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->sum('get_money');

        if(empty($money)){
            echo '暂时没有可解冻的购物积分';
            die ;
        }
        $upid = $this->model->table('sgxt_balance')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->getField('id',true);
        if(count($upid) == 1){
            $upid = $upid[0];
        }else{
           $upid = implode(',',$upid ); 
        }
        
        //将收益增加到余额中
        $this->model->setBegin();
        $pass1 = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setInc('money' , $money);
        $pass2 = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setDec('money_dj' , $money);
        if($pass1 && $pass2 ){
            $this->model->table('sgxt_balance')->where("id in ($upid) and createtime<".$end) ->save(array('is_clearing' => 1));
            $this->model->commit();
            
            //增加解冻记录
            $insdata = array(
                'userid' => $this->userinfo['user_id'],
                'user_name' => $this->userinfo['real_name'],
                'money' => $money,
                'type' => 1,
                'createtime' => time(),
                );
            $this->model->table('sgxt_unfreeze')->add($insdata);
        }else{
             $this->model->rollBack();
              echo '购物积分解冻失败';
              die ;
        }
        
       echo '1';
    }
    //购物积分点击进入
    public function balance(){
        //上月开始时间
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期
         //获取用户当前余额
         $this->assign('endtime',date('Y-m-d H:i:s',$end));
         $this->assign('real_name',$this->userinfo['real_name']);
        $userTreasure = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->find1();
        $user_balance = conf("user_balance");

        $balance['current'] = $userTreasure['money']; //当前收益
        //$balance['freeze'] = $userTreasure['money_dj']; //冻结收益
		$balance['freeze'] = $this->model->table('sgxt_balance')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0")->sum('get_money');
        //$balance['thaw'] = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'< $end")->sum('get_money');
        $balance['thaw'] = $this->model->table('sgxt_balance')->where("user_id=".$this->userinfo['user_id']." and is_clearing=0 and createtime<".$end)->sum('get_money');
        
        $balance['thaw']=$balance['thaw']?$balance['thaw']:'0.00';
        $this->assign('balance' , $balance);
        if($this->userinfo['type'] < 3){
            unset($user_balance['3']);
        }
        foreach($user_balance as $key=>$money){
            echo $key;
             $money['money'] = $this->get_model_balance($key);
            $bander[] = $money;
        }

        $this->assign('bander' , $bander);
        $this->display('newapp/balance.sales.html');
    }
    //返回每个栏目的收益
    private function get_model_earnings($val){
        $balance = 0.00;
        switch($val){
            case 1: $balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 5))->sum('remain_money');
                    break;
            case 2: $balance = $this->model->table('sgxt_commission') ->where(array('toid' => $this->userinfo[user_id]))->sum('money');break;
            case 3: $balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 4))->sum('remain_money');
                    break;
            case 4:$balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 6))->sum('remain_money');
                    break;
            case 5:$balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 7))->sum('remain_money');
                    break;
            case 6:$balance = $this->model->table('sgxt_deposit')->where(array('userid' => $this->userinfo[user_id] , 'ispay' => 1))->sum('money');
                    break;
        }
       return $balance=$balance?$balance:'0.00';
    }
    //商家中心

    //返回每个栏目的余额
    private function get_model_balance($val){
        $balance = 0.00;
        switch($val){
            case 1: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 1))->sum('get_money');
                    break;
            case 2: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 2))->sum('get_money');break;
            case 3: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 3))->sum('get_money');
                    break;
            case 4:$balance =$this->userinfo['happiness'];
                    break;
        }
        return $balance=$balance?$balance:'0.00';
    }

    //商家货款
    public function huokuan(){
        $money['current'] = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->getField('balance'); 
        $money['deposit'] = $this->model->table('sgxt_deposit')->where(array('userid' => $this->userinfo['user_id'] ,'type' => 1,'ispay'=>1))->sum('money');   
        $money['deposit'] = $money['deposit']?$money['deposit']:0.00;      
        //统计线上订单收益
        $order['online'] = $this->model->table('order')->where(array('seller_id'=>$this->userinfo['user_id'] , 'status'=>40))->sum('order_amount');
        $order['online'] = $order['online']?$order['online']:0;
        //统计线下订单货款
        
        $order['offline'] = $this->model->table('order_offline')->where(array('seller_id'=>$this->userinfo['user_id'] , 'status'=>40))->sum('order_amount');
        $order['offline']=$order['offline']?$order['offline']:0;
        $money['all'] = $order['online']  + $order['offline'];
        //货款购积分
        $money['pay_point'] = $this->model->table('sgxt_order')->where(array('userid' =>$this->userinfo['user_id'] , 'status' => 1,'paytype' => 'balance' )) ->sum('amount');
        //收到的积分
        $money['get_point'] = $this->model->table('order_offline') ->where(array('seller_id' =>$this->userinfo['user_id'],'status' =>40,'payment_id' =>3 )) ->sum('order_amount');
        $this->assign('money' , $money);
        $this->assign('order' , $order);
        $this->display('newapp/huokuan.html');
    }

    public function saveLocal(){
        $model = &m();
        $lng = $_POST['lng'];
        $lat = $_POST['lat'];
        $last = $model->table('member_login')->where('user_id='.$this->userinfo['user_id'])->order('add_time desc')->find1();
        $model->table('member_login')->where('id='.$last['id'])->save(array('lng'=>$lng,'lat'=>$lat));
    }



    
}