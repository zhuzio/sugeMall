<?php 
/*
* 购物积分控制器
 */
header('Content-type:text/html;charset=utf-8');
class balanceApp extends MemberbaseApp {

    public function __construct() {
        parent::__construct();
        $this->userinfo = $_SESSION['user_info'];
        $this->model = & m();
        import('agmpay.lib');
        $this->agm = new agmPay(conf('pubfile'), conf('prifile'));  
    }
    //购物积分收入明细
    public   function index(){
        $type = intval(I('get.type'));

        $model_name = $this->model->table('sgxt_infotpl')->where(array('id' => $type)) ->getField('title');
        $taba = DB_PREFIX."sgxt_balance";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $sql = "select * from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = $type and   user_id= ".$this->userinfo['user_id']." order by $taba.id desc ";
        $balance = $this->model->query($sql);
        if(empty($balance))$balance=array();
        $allbalance = 0;
        foreach($balance as $key=>$band){
            $allbalance += $band['get_money'];
            $rep['name'] = $band['from_username'];
            //$rep['point'] = $band['real_point'];
            $rep['point'] = round(intval($band['real_point'])*conf('PAY_INFO/shops_point'));
            if($type == 7){
              $rep['point'] = $band['real_point'];
            }
            $rep['money'] = $band['get_money'];
            $balance[$key]['createtime'] = date('Y-m-d H:i',$band['createtime']);
            $balance[$key]['content'] = replace_tpl($rep , $band['content']);
        }
        $this->assign('model_name' , $model_name);
        $this->assign('allbalance' , $allbalance);
        $this->assign('balance',$balance);
        $this->display('newapp/detailed.balance.html');
    }
    //收益明细
    public function earning(){
        $type = intval(I('get.type'));
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $model_name = $this->model->table('sgxt_infotpl')->where(array('id' => $type)) ->getField('title');
        $allmoney = $this->model->table('sgxt_profit') -> where(array('user_id' => $this->userinfo['user_id'] , 'source_type' => $type))->sum('remain_money');
        $allmoney=$allmoney?$allmoney:'0.00';
        $this -> assign('model_name' ,$model_name);
        $this -> assign('allmoney' ,$allmoney);
        $sql = "select * from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = $type and   user_id= ".$this->userinfo['user_id']." order by $taba.id desc ";
        $balance = $this->model->query($sql);

        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
            $rep['name'] = $band['from_username'];
            $rep['point'] = $band['real_point'];
            $rep['money'] = $band['remain_money'];
           $balance[$key]['createtime'] = date('Y-m-d H:i',$band['createtime']);
           $balance[$key]['content'] = replace_tpl($rep , $band['content']);
        }
        $this->assign('balance',$balance);
        $this->display('newapp/detailed.earnings.html');
    }
    public function commission(){
       
       $model_name = '推荐奖励';
       $allmoney = $this->model->table('sgxt_commission')->where(array('toid' => $this->userinfo['user_id']))->sum('money');
       $count = $this->model->table('sgxt_commission')->where(array('toid' => $this->userinfo['user_id']))->count();
       $commission = $this->model->table('sgxt_commission')->where(array('toid' => $this->userinfo['user_id']))->page($count)->order('id desc')->select();
        
       foreach($commission as $key=>$val){
          $formname = $this->model->table('member')->where(array('user_id' => $val['fromid']))->getField('real_name');
          $commission[$key]['info'] = '推荐'.$formname.$val['info'];
          $commission[$key]['createtime'] = date('Y-m-d H:i' , $val['createtime']);
       }
       $allmoney=$allmoney?$allmoney:'0.00';
       $this -> assign('commission' ,$commission);
       $this -> assign('model_name' ,$model_name);
       $this -> assign('allmoney' ,$allmoney);
       $showpage = $this->model->getButton();
       $this -> assign('showpage' ,$showpage);
       $this->display('newapp/commission.html');
    }
    //消费流水
    public function consume(){
        
        $bakance = $this->model->table('epaylog')->where(array('user_id' => $this->userinfo['user_id'] , 'money_flow' => 'outlay' ,'payment_id' => 3))->select();
        dump($bakance );
    }

    //货款提现明细(商家)
    public function money_withdrawal(){
        
          $user_id =$this->userinfo['user_id'];
          //货款提现
          $tx_detail =$this->model->table('sgxt_deposit')->field('user_id,money,createtime,bank_code,operatortime,ispay')->where(array('userid'=>$user_id,'type'=>1))->order('deid desc')->select(); 
           //已提现总额
          $tx_total =$this->model->table('sgxt_deposit')->where(array('userid'=>$user_id,'ispay'=>1,'type'=>1))->sum('money');
          foreach ($tx_detail as $k => $v) {
                $v['createtime'] =date('Y-m-d H:i',$v['createtime']);
                $v['operatortime'] =date('Y-m-d H:i',$v['operatortime']);
                $a1=substr($v['bank_code'],0,4);
                $a2=substr($v['bank_code'],-4,4);
                $v['bank_code'] =$a1.'********'.$a2;
                $tx_detail[$k] =$v;
          }
          // var_dump($tx_detail);die;
          $tx_total=$tx_total?$tx_total:'0.00';
          $this->assign('total',$tx_total);
          $this->assign('detail',$tx_detail);
          $this->display('newapp/detailed.profit.business.html');
        
    }

    //收益提现明细(商家)
    public function earnings_detail(){
       
            $user_id =$this->userinfo['user_id'];
            //收益提现
            $tx_detail =$this->model->table('sgxt_deposit')->field('user_id,money,createtime,bank_code,operatortime,ispay')->where(array('userid'=>$user_id,'type'=>2))->order('deid desc')->select();
             //已提现总额
            $tx_total =$this->model->table('sgxt_deposit')->where(array('userid'=>$user_id,'ispay'=>1,'type'=>2))->sum('money');
            $tx_total = $tx_total?$tx_total:0.00;
            foreach ($tx_detail as $k => $v) {
                $v['createtime'] =date('Y-m-d  H:i',$v['createtime']);
                $v['operatortime'] =date('Y-m-d  H:i',$v['operatortime']);
                $a1=substr($v['bank_code'],0,4);
                $a2=substr($v['bank_code'],-4,4);
                $v['bank_code'] =$a1.'********'.$a2;
                $tx_detail[$k] =$v;
          }

          $this->assign('sum',$tx_total);
          $this->assign('details',$tx_detail);
          $this->display('newapp/detailed.profit.user.html');

        
    }

    //余额解冻明细
    public function balance_unfreeze(){
        if(!IS_POST){
           $user_id =$this->userinfo['user_id'];
           $jd_detail =$this->model->table('sgxt_unfreeze')->field('user_name,money,createtime')->where(array('userid'=>$user_id,'type'=>1))->order('od desc')->select();
           //解冻总额
           $jd_total =$this->model->table('sgxt_unfreeze')->where(array('userid'=>$user_id,'type'=>1))->sum('money');
           foreach ($jd_detail as $k=>$v){
                $v['createtime'] =date('Y-m-d H:i',$v['createtime']);
                $jd_detail[$k] =$v;
           }
           // var_dump($unfreeze_detail);die;
           $this->assign('sums',$jd_total);
           $this->assign('detail',$jd_detail);
           $this->display('newapp/balance.unfreeze.html');
        }


    }


    //收益解冻明细
    public function earning_unfreeze(){
        if(!IS_POST){
           $user_id =$this->userinfo['user_id'];
           $sy_detail =$this->model->table('sgxt_unfreeze')->field('user_name,money,createtime')->where(array('userid'=>$user_id,'type'=>2))->select();
           //解冻总额
           $sy_total =$this->model->table('sgxt_unfreeze')->where(array('userid'=>$user_id,'type'=>2))->sum('money');
           foreach ($sy_detail as $k=>$v){
                $v['createtime'] =date('Y-m-d H:i',$v['createtime']);
                $sy_detail[$k] =$v;
           }

           // var_dump($unfreeze_detail);die;
           $this->assign('zonghe',$sy_total);
           $this->assign('mingxi',$sy_detail);
           $this->display('newapp/earning.unfreeze.html');
        }



    }
















}    
  


?>