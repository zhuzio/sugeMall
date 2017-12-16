<?php
/**
 * @Author: lxc
 * @Date:   2016-06-27 16:18
 * @Last Modified time: 2016-06-27 16:18
 */
header('content-type:text/html;charset=utf-8');
class New_member_recordApp extends MemberbaseApp {

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
        $this->userinfo = $_SESSION['user_info'];
        $this->epay_mod = & m('epay');
        $this->model = & m();
        $this->userinfo = $this->model->table('member')->where(array('user_id'=>$this->userinfo['user_id']))->find1();
    }

    /**
     * 直推销售经理列表
     */
    public function selfSellerManager(){
        $count =  $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>3))->count();
        $sellerManagerList = $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>3))->page($count)->select();
        $uids = $userList = array();
        foreach($sellerManagerList as $key=>$val){

            $totalPoint = $this->model->table('sgxt_get_point')->where('getid='.$val['user_id'])->sum('point');
            $totalPoint = $totalPoint ? $totalPoint : 0;
            $val['totalPoint'] = $totalPoint;
            $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            $sellerManagerList[$key] = $val;
        }
        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.sellermanager.html');
    }


    /**
     * 直推县级代理列表
     */
    public function selfAreaAgent(){
        $count =  $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>5))->count();
        $sellerManagerList = $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>5))->page($count)->select();
        $uids = $userList = array();

        foreach($sellerManagerList as $key=>$val){
            $storelist = $this->model->table('member')->where('area='.$this->userinfo['ahentarea'])->select();
            $sids = array();
            foreach($storelist as $k=>$v){
                //print_r($v);
                $sids[] = $v['user_id'];
            }
            $totalPoint = 0;
            if(count($sids)>0){
                $totalPoint = $this->model->table('sgxt_get_point')->where('getid in ('.implode(',',$sids).')')->sum('point');
            }
            $val['totalPoint'] = $totalPoint;
            $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            $sellerManagerList[$key] = $val;
        }
        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.selfareaagent.html');
    }

    /**
     * 推荐商家列表
     */
    public function tjBusiness(){

        $count =  $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>2))->count();
        $sellerManagerList = $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>2))->page($count)->select();
        $uids = $userList = array();
        foreach($sellerManagerList as $key=>$val){

        $totalPoint = $this->model->table('sgxt_get_point')->where('sendid='.$val['user_id'])->sum('point');
        $totalPoint = $totalPoint ? $totalPoint : 0;
        $val['totalPoint'] = $totalPoint;
        $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
        $sellerManagerList[$key] = $val;
        }
        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.business.html');
    }

    /**
     * 直推区域代理列表
     */
    public function selfAreaManager(){
        $count =  $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>4))->count();
        $sellerManagerList = $this->model->table('member')->where(array('pid'=>$this->userinfo['user_id'],'type'=>4))->page($count)->select();
        $uids = $userList = array();
        foreach($sellerManagerList as $key=>$val){
            $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            $sellerManagerList[$key] = $val;
        }
        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.areamanager.html');
    }

    /**
     * 区域下商家列表
     */
    public function areaStore(){
        $condition = array();
        if($this->userinfo['type'] == 4){
            $condition = array('opid'=>$this->userinfo['user_id'],'type'=>2);
        }else if($this->userinfo['type'] == 5){
            $condition = array('area'=>$this->userinfo['ahentarea'],'type'=>2);
        }
        $count =  $this->model->table('member')->where($condition)->count();
        $sellerManagerList = $this->model->table('member')->where($condition)->page($count)->select();
        $uids = $userList = array();
        foreach($sellerManagerList as $key=>$val){            
            $totalPoint = $this->model->table('sgxt_get_point')->where('sendid='.$val['user_id'])->sum('point');
            $totalPoint = $totalPoint ? $totalPoint : 0;
            $val['totalPoint'] = $totalPoint;
            //店铺名称
            $shop_name =$this->model->table('store')->where('store_id='.$val['user_id'])->field('store_name')->find1();   
            $shop_name['store_name'] =$shop_name['store_name'] ? $shop_name['store_name'] : '';
            $val['shop_name'] =$shop_name['store_name'];
            $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            $sellerManagerList[$key] = $val;
        }

        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.areastore.html');
    }

    /**
     * 区域下会员列表
     */
    public function areaMember(){
        $condition = array();
        if($this->userinfo['type'] > 3){
            $condition = array('opid'=>$this->userinfo['user_id']);
        }
        $count =  $this->model->table('member')->where($condition)->count();
        $sellerManagerList = $this->model->table('member')->where($condition)->page($count)->select();
        $uids = $userList = array();
        foreach($sellerManagerList as $key=>$val){
            $totalPoint = $this->model->table('sgxt_get_point')->where('getid='.$val['user_id'])->sum('point');
            $totalPoint = $totalPoint ? $totalPoint : 0;
            $val['totalPoint'] = $totalPoint;
            $val['reg_time'] = date('Y-m-d H:i:s',$val['reg_time']);
            $sellerManagerList[$key] = $val;
        }
        $mypage = $this->model->getButton(1);
        $this->assign('mypage',$mypage);
        $this->assign('list',$sellerManagerList);
        $this->display('newapp/agent.recommend.areamember.html');
    }


    /****新增会员模块**/
    // 个人中心
    public function index(){
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

        $point['point_peac'] = $userinfo['point_peac'];
        $point['point'] = $userinfo['point_peac'];
        $point['all_point'] = $this->model->table('sgxt_get_point')-> where(array('getid' => $this->userinfo['user_id'])) -> sum('point');

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



       $this->display('newapp/index.html');

    }
    public function earnings(){
        //上月开始时间
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期
        //获取用户当前收益
        $userTreasure = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->find1();
        $earnings['current'] = $userTreasure['earnings']; //当前收益
        $earnings['freeze'] = $userTreasure['freeze_earnings']; //冻结收益
        $earnings['thaw'] = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'> $end")->sum('remain_money');

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
    //购物积分点击进入
    public function balance(){
        //上月开始时间
        $m = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月的开始日期
        $t = date('t',strtotime($m)); //上个月共多少天
        $start = mktime(0,0,0,date('m')-1,1,date('Y')); //上个月的开始日期
        $end = mktime(23,59,59,date('m')-1,$t,date('Y')); //上个月的结束日期
         //获取用户当前余额
        $userTreasure = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->find1();
        $user_balance = conf("user_balance");

        $balance['current'] = $userTreasure['earnings']; //当前收益
        $balance['freeze'] = $userTreasure['freeze_earnings']; //冻结收益
        $balance['thaw'] = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo['user_id'] ,'is_clearing' => 0))->where(" 'createtime'> $end")->sum('get_money');

        $balance['thaw']=$balance['thaw']?$balance['thaw']:'0.00';
        $this->assign('balance' , $balance);
        if($this->userinfo['type'] < 3){
            unset($user_balance['3']);
        }
        foreach($user_balance as $key=>$money){
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
            case 4:$balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 8))->sum('remain_money');
                    break;
            case 5:$balance = $this->model->table('sgxt_profit')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 5))->sum('remain_money');
                    break;
            case 6:$balance = $this->model->table('sgxt_deposit')->where(array('userid' => $this->userinfo[user_id] , 'ispay' => 1))->sum('money');
                    break;
        }
       return $balance=$balance?$balance:'0.00';
    }

    //返回每个栏目的余额
    private function get_model_balance(){
        $balance = 0.00;
        switch($val){
            case 1: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 1))->sum('get_money');
                    break;
            case 2: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 2))->sum('get_money');break;
            case 3: $balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'source_type' => 3))->sum('get_money');
                    break;
            case 4:$balance = $this->model->table('sgxt_balance')->where(array('user_id' => $this->userinfo[user_id] , 'payment_id' => 3 ,'money_flow' => 'outlay'))->sum('epaylog');
                    break;
        }
        return $balance=$balance?$balance:'0.00';
    }






}