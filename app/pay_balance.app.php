<?php 
class Pay_balanceApp extends MemberbaseApp {
    var $_feed_enabled = false;
    var $point_mod ;
    function __construct() {
        parent::__construct();
        $this->userinfo = $_SESSION['user_info'];
        $this->mod_epay = & m('epay');
        $this->mod_epaylog = & m('epaylog');
        $this->order_offline = & m('order_offline');
        $this->model = & m();
        $this->mod_paymentlog = & m('paymentlog');
        $this->push_message = & m('push_message');
        import('agmpay.lib');
        $this->agm = new agmPay(conf('pubfile'), conf('prifile'));  
    }
    function index() {
        echo 'ss';
    }
    //生成收款二维码
    public function pay_barcode(){
        //验证该用户是否是商家
        
        if($this->userinfo['type'] != 2){
            $this->show_warning('cuowu_nishurudebushishuzilei');
            return ;
        }
        //生成带参数的二维码
        
        //判断是否有为使用的二维码
        $recode = $this->model-> table('epay_barcode') -> where(array('get_money_id' => $this->userinfo['user_id'] , 'is_pay' => 0) ) -> order('create_time desc')->find1();
        if(empty($recode)){
            $pay_sn = buildCountRand(1,15);
            $data['pay_sn'] = $pay_sn[0];
            $data['createtime']  = time();
            $data['sj_id'] = $this->userinfo['user_id'];
            $insadd = array(
                'pay_sn'  => $data['pay_sn'],
                'get_money_id' => $this->userinfo['user_id'],
                'get_money_name' => $this->userinfo['real_name'],
                'create_time'    => $data['createtime'],
                );

            $this->model->table('epay_barcode')->add($insadd);
        }else{
            $data['pay_sn'] = $recode['pay_sn'];
            $data['createtime']  = $recode['createtime'];
            $data['sj_id'] = $this->userinfo['user_id'];
        }


        $key = $this->agm -> encrypt($data);
        $link = SITE_URL.'/index.php?app=qrcode&url='.urlencode(SITE_URL.'/index.php?app=pay_balance&act=pay_balance&key='.$key);
        $this->assign('links' , $link);
        $this->display('newapp/business.qrcode.html');
    }
    public function pay_balance(){
        //检查用户是否登录，将用户的key写入cookis中
        
        if(!$_COOKIE['key_user_id']){
            $this->show_warning('你还未登录');
            return ;
        }
        $userid = $this->agm->decrypt($_COOKIE['key_user_id']);

        if(empty($userid)){
            $this->show_warning('你还未登录');
            return ;
        }

        //解析二维码中参数
        $key = rawurldecode(I('get.key')?I('get.key'):I('post.key'));

        if(empty($key)){
            $this->show_warning('二维码参数错误,请重新扫描1');
            return ;
        }
       
        $to_user_info = $this->agm->decrypt($key);
        
        if(empty($to_user_info)){
            $this->show_warning('二维码参数错误,请重新扫描2');
            return ;
        }
        //验证二维码已期
        /*$is_not_pay = $this->model->table('epay_barcode')->where(array('pay_sn' => $to_user_info['pay_sn'])) ->getField('is_pay');
        if($is_not_pay){
             $this->show_warning('二维码已过期，请重新扫描');
             return ;
        }*/

        //验证该店铺是否存在并且审核通过
        $is_store = $this->model -> table('store') -> where(array('store_id' => $to_user_info['sj_id'] , 'state' => 1))->find1();
        if(empty($is_store)){
            $this->show_warning('该店铺不存在，或暂时关闭');
             return ;
        }


        if(!IS_POST){
            $class_goods = $this->model -> table('sgxt_class_goods') -> where(array('store_id' => $to_user_info['sj_id'] , 'state' => 1))->select();
            if(empty($class_goods)){
                 $this->show_warning('店家没有设置商品类型，暂时不能支付');
                 return ;
            }
            //拿到商家信息
            $hash = get_hash();
           $this->assign('_hash_',$hash);
           $this->assign('class_goods',$class_goods);
           $this->assign('truename' , $is_store['store_name']);
           $this->display('newapp/user.balance.pay.html');  
        }else{
            //验证有没有选择classid
            $classid = I('post.classid');
            if(empty($classid)){
                $this->show_warning('请选择商品类型');
                 return ;
            }
            $money = I('post.money');
            if(empty($money)){
                $this->show_warning('请输入支付金额');
                 return ;
            }
            //修改该二维码状态
            $truename = $this->model->table('member') -> where(array('user_id'=> $userid))->getField('real_name');
            $save = array(
                'pay_money_id' => $userid,
                'pay_money_name' => $truename,
                'is_pay'  => 1,
                'pay_time' => time(),
                );
            $this->model->table('epay_barcode')->where(array('pay_sn' => $to_user_info['pay_sn'])) ->save($save);

            $this->out_balance($to_user_info['sj_id'] , I('post.money') , I('post.pay_passwd'));
            
            
        }
    }
    //余额线下支付的方法
    public function out_balance($to_user_id , $to_money , $pay_passwd){
        $user_id = $this->visitor->get('user_id');
      
            if (preg_match("/[^0.-9]/", $to_money)) {
                $this->show_warning('cuowu_nishurudebushishuzilei');
                return;
            }


            $to_row = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id='$to_user_id'");
            $to_user_id = $to_row['user_id'];
            $to_user_name = $to_row['user_name'];
            $to_user_money = $to_row['balance'];

            if ($to_user_id == $user_id) {
                $this->show_warning('cuowu_bunenggeizijizhuanzhang');
                return;
            }

            if (empty($to_user_id)) {
                $this->show_warning('cuowu_mubiaoyonghubucunzai');
                return;
            }
            $row_epay = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id='$user_id'");
            $user_money = $row_epay['money'];
            $user_zf_pass = $row_epay['zf_pass'];
            $zf_pass = md5(trim($pay_passwd));
            if ($user_zf_pass != $zf_pass) {
                $this->show_warning('cuowu_zhifumimayanzhengshibai');
                return;
            }
            $order_sn = "40" . date('YmdHis',gmtime()+8*3600).rand(1000,9999);
            if ($user_money < $to_money) {
                $this->show_warning('cuowu_zhanghuyuebuzu');
                return;
            } else {
                

                //添加日志
                $log_text = $this->visitor->get('user_name') . Lang::get('gei') . $to_user . Lang::get('zhuanchujine') . $to_money . Lang::get('yuan');

                $add_epaylog = array(
                    'user_id' => $this->visitor->get('user_id'),
                    'user_name' => $this->visitor->get('user_name'),
                    'to_id' => $to_user_id,
                    'to_name' => $to_user_name,
                    'order_sn' => $order_sn,
                    'add_time' => gmtime(),
                    'type' => EPAY_OUT, //转出    
                    'money_flow' => 'outlay',
                    'money' => $to_money,
                    'complete' => 1,
                    'log_text' => $log_text,
                    'states' => 40,
                    'payment_id' => 3 //余额支付
                );
                $this->mod_epaylog->add($add_epaylog);
                $log_text_to = $this->visitor->get('user_name') . Lang::get('gei') . $to_user_name . Lang::get('zhuanrujine') . $to_money . Lang::get('yuan');
                $add_epaylog_to = array(
                    'user_id' => $to_user_id,
                    'user_name' => $to_user_name,
                    'to_id' => $this->visitor->get('user_id'),
                    'to_name' => $this->visitor->get('user_name'),
                    'order_sn ' => $order_sn,
                    'add_time' => gmtime(),
                    'type' => EPAY_IN, //转入 
                    'money_flow' => 'income',
                    'money' => $to_money,
                    'complete' => 1,
                    'log_text' => $log_text_to,
                    'states' => 40,
                );
                $this->mod_epaylog->add($add_epaylog_to);

                $new_user_money = $user_money - $to_money;
                $new_to_user_money = $to_user_money + $to_money;

                $add_jia = array(
                    'balance' => $new_to_user_money,
                );
                $this->mod_epay->edit('user_id=' . $to_user_id, $add_jia);
                $add_jian = array(
                    'money' => $new_user_money,
                );
                $this->mod_epay->edit('user_id=' . $user_id, $add_jian);

                //线下余额支付时创建已完成订单
                $order = $this->order_offline->createOrder($user_id ,$to_user_id , 3 , I('post.money') , 0 , I('post.classid') , I('post.paymess'));
                //添加支付日志
                $this->mod_paymentlog->paymentlog($user_id , $this->userinfo['real_name'],I('post.money') , '3' , $to_user_id , $to_user_name , $order['orderid'], $order['order_sn']);
                //添加推送记录
                $this->push_message->addMessage('order_offline' , 'order_id' , $order['orderid'] , $this->userinfo['real_name'].conf('push_message/1') ,$to_user_id , $to_user_name );

                //支付完成过推送成功的消息模板
                $weixinModel =  & m('weixin_login');
                $model = & m();
                $storename = $model -> table('store')->where(array('store_id' => $to_user_id)) ->getField('store_name');
                $sendarr = array(
                    $storename.$to_user_name,
                    $order['order_sn'],
                    I('post.money'),
                    date('Y-m-d H:i'),
                    );
                $weixinModel -> pus_message($this->visitor->get('user_id') , 1 , '购物积分支付成功',$sendarr,'欢迎下次光临' );                
                $this->show_success('购物积分支付成功','index.php?app=offline_order');
                return;
            }
        
    }
    //检查支付密码
    public function check_pay_pass(){
       $pass = $this->model->table('epay') -> where(array('user_id'=> $this->userinfo['user_id'])) ->getField('zf_pass');
       //$pay_passwd = I('get.pay_passwd');       
       $pay_passwd = $_REQUEST['pay_passwd'];       
        if($pass != md5($pay_passwd)){
            echo 'error';
        }else{
            echo 'ok';
        }
    }

    //商家提现操作
    public function shopDeposit(){
        if($this->userinfo['type'] != 2){
            $this->show_warning('只有商户才能货款提现');
                return;
        }
        if(!IS_POST){
            //查询出总收益
            //欠款积分
            //最高可提现
            //查询出默认银行卡
            $money['allMoney'] = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->getField('balance');
            $money['debtMoney'] = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->getField('pay_point');
            $money['debtMoney'] = $money['debtMoney']<0?$money['debtMoney']:0;
            $money['debtMoney'] = $money['debtMoney']*0.3;
            $money['maxMoney'] =floor($money['allMoney'] - $money['debtMoney']);
            $this->assign('money' ,$money);

            //查询出默认银行卡
            $defaultBank = $this->model->table('epay_bank')->where(array('user_id' => $this->userinfo['user_id'] , 'status' => 0))->find1();

            $this->assign('defaultBank' ,$defaultBank);
            $this->display('newapp/shop.deposit.html');
        }else{
            $money = I('post.money');
            $pay_passwd = I('post.pay_passwd');
            if (preg_match("/[^0.-9]/", $money)) {
                $this->show_warning('错误你输入的不是数字');
                return;
            }

            $defaultBank = $this->model->table('epay_bank')->where(array('user_id' => $this->userinfo['user_id'] , 'status' => 0))->find1();
            if(empty($defaultBank)){
                 $this->show_warning('请先选择默认银行卡');
                return;
            }
            $pay_point = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->getField('pay_point');
            $debtMoney = $pay_point<0?$pay_point:0;
            $debtMoney = $debtMoney*0.3;
            $row_epay = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id={$this->userinfo[user_id]}");
            $user_money = $row_epay['balance'];
            $user_zf_pass = $row_epay['zf_pass'];
            $maxMoney = $user_money - $debtMoney;
            $zf_pass = md5(trim($pay_passwd));
            if ($user_zf_pass != $zf_pass) {
                $this->show_warning('支付密码错误');
                return;
            }
            if($money > $maxMoney){
                $this->show_warning('亲，你的提现金额大于账号余额');
                return;
            }
            if($money < 200){
                 $this->show_warning('亲，提现金额太少了，攒攒再提吧');
                return;
            }
            //拿到货款
            $upmoney = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setDec('balance' ,$money );
            //生成提现记录
            if($pay_point < 0){
                $upuser = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->save(array('pay_point' => 0));
            }
            
            if(!$upmoney){
                $this->show_warning('提现失败请重新操作');
                return;
            }
            
            $deposit = array(
                'userid'  =>  $this->userinfo['user_id'],
                'truename' => $this->userinfo['real_name'],
                'mobile'   => $this->userinfo['user_name'],
                'money'    => $money,
                'createtime' => time(),
                'type'     => 1,
                'bank_name' =>$defaultBank['bank_name'],
                'open_bank' =>$defaultBank['open_bank'],
                'bank_code' =>$defaultBank['bank_num'],
                'bank_num'  =>$defaultBank['bank_code'],
                'bank_user_name' =>$defaultBank['account_name'],
                 );
            $addmoney = $this->model->table('sgxt_deposit')->add($deposit);
            if(!$addmoney){
                $this->show_warning('提现失败请重新操作');
                return;
            }
            //添加提现成功日志
            $this->mod_paymentlog->paymentlog($this->userinfo['user_id'] , $this->userinfo['real_name'],$money , '8'  );
            $this->show_message('提现成功');

        }
    }
    //用户提示
    public function userDeposit(){
        if($this->userinfo['user_id'] < 2){
            $this->show_warning('普通用户不能提现');
                return;
        }
        if(!IS_POST){
            //查询出总收益
            //欠款积分
            //最高可提现
            //查询出默认银行卡
            $money['maxMoney'] =$money['allMoney'] = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->getField('earnings');
            $money['maxMoneyValue'] =floor($money['maxMoney']);
            $this->assign('money' ,$money);

            //查询出默认银行卡
            $defaultBank = $this->model->table('epay_bank')->where(array('user_id' => $this->userinfo['user_id'] , 'status' => 0))->find1();

            $this->assign('defaultBank' ,$defaultBank);
            $this->display('newapp/user.deposit.html');
        }else{
            $userid= $this->userinfo[user_id];
            $money = I('post.money');
            $pay_passwd = I('post.pay_passwd');
            if (preg_match("/[^0.-9]/", $money)) {
                $this->show_warning('cuowu_nishurudebushishuzilei');
                return;
            }
            $defaultBank = $this->model->table('epay_bank')->where(array('user_id' => $this->userinfo['user_id'] , 'status' => 0))->find1();
            if(empty($defaultBank)){
                 $this->show_warning('请先选择默认银行卡');
                return;
            }
            $money=floatval($money);
            $row_epay = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id='$userid'");
            $user_money = floatval($row_epay['earnings']);
            $user_zf_pass = $row_epay['zf_pass'];
            $zf_pass = md5(trim($pay_passwd));
            if ($user_zf_pass != $zf_pass) {
                $this->show_warning('cuowu_zhifumimayanzhengshibai');
                return;
            }
            if($money > $user_money){
                
                $this->show_warning('你不能提现那么多');
                return;
            }
            //拿到货款
            $upmoney = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setDec('earnings' ,$money );
            //生成提现记录
            
            if(!$upmoney){
                $this->show_warning('提现失败请重新操作');
                return;
            }
            
            $deposit = array(
                'userid'  =>  $this->userinfo['user_id'],
                'truename' => $this->userinfo['real_name'],
                'mobile'   => $this->userinfo['user_name'],
                'money'    => $money,
                'createtime' => time(),
                'type'     => 2,
                'bank_name' =>$defaultBank['bank_name'],
                'open_bank' =>$defaultBank['open_bank'],
                'bank_code' =>$defaultBank['bank_num'],
                'bank_user_name' =>$defaultBank['account_name'],
                 );
            $addmoney = $this->model->table('sgxt_deposit')->add($deposit);
            if(!$addmoney){
                $this->show_warning('提现失败请重新操作');
                return;
            }

            $this->show_message('提现成功');

        }
    }
    //货款支付
    public function paymentShops(){

        if(IS_POST){
            $orderid = I('post.orderid');
            if(empty($orderid)){
                $this->show_warning('订单为空');
                return ; 
            }
            $order = $this->model->table('sgxt_order')->where(array('id' => $orderid,'userid' => $this->userinfo['user_id']))->find1();
            if(empty($order)){
                $this->show_warning('订单错误，支付失败');
                return ;
            }
            if($order['status'] == 1){
                $this->show_warning('该订单已经支付，支付失败');
                return ;
            }
            $userpay = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->find1();

            //用事务方式处理支付
            if($order['amount'] > $userpay['balance']){
                $this->show_warning('余额不足，支付失败');
                return ; 
            }
            $oldOrder = $order;
            $this->model->setBegin();
            //商家减少余额
            $pass1 = $this->model->table('epay')->where(array('user_id' => $this->userinfo['user_id']))->setDec('balance' , $order['amount']);
            $pass2 = $this->model->table('member')->where(array('user_id' => $this->userinfo['user_id']))->setInc('pay_point' , $order['num']);
            $pay_sn = $order = date('Ymd').substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
            $incdata = array(
                'paytype'  => 'balance',
                'status'   =>  1,
                'pay_createtime' => time()
                );
            $pass3 =$this->model->table('sgxt_order')->where(array('id' => $orderid,'userid' => $this->userinfo['user_id']))-> save($incdata);
            if($pass1 & $pass2 & $pass3){
                $this->model->commit();
                $this->mod_paymentlog->paymentlog($this->userinfo['user_id'] , $this->userinfo['real_name'],$oldOrder['amount'] , '6' , 0 , 0 , $oldOrder['orderid'], $oldOrder['orderid']);
                $this->show_message('积分购买成功');
                return ; 
            }else{
                $this->model->rollBack();
                $this->show_warning('积分购买失败');
            }
        }
    }

    public function orderToPayment(){
        $orderList = $this->model->table('sgxt_order')->where(array('paytype'=>'balance'))->select();        
        foreach($orderList as $key=>$val){
            if($key == count($orderList)-1){
                break;
            }
            $this->mod_paymentlog->paymentlog($val['userid'] , $val['truename'],$val['amount'] , '6' , 0 , 0 , 0, $val['orderid']);
        }
    }
}
 ?>