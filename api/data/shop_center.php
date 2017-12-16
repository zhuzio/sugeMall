<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/28
 * Time: 19:38
 */
function register_qrc(){
    $token = urlencode($_GET['token']);
    if(!isset($_GET["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
		import('agmpay.lib');
        $link = conf('SITE_URL').'/wap/register1.html?mobile='.$user['user_name'];
        import('phpqrcode');
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        QRcode::png($link, false, $errorCorrectionLevel, $matrixPointSize, 2);
        exit;
    }else{
        err('身份错误，请重新登录');
    }
}

/*
 * 积分支付二维码接口
 */
function pay_barcode(){
    $token = urlencode($_GET['token']);    
    if(!isset($_GET["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        if($user['type'] != 2){
            err('只有商家才能收款，请重新登录');
        }
        $model = new M();
        $recode = $model-> table('epay_barcode') -> where(array('get_money_id' => $user['user_id'] , 'is_pay' => 0) ) -> order('create_time desc')->find();
        if(empty($recode)){
            $pay_sn = buildCountRand(1,15);
            $data['pay_sn'] = $pay_sn[0];
            $data['create_time']  = time();
            $data['sj_id'] = $user['user_id'];
            $insadd = array(
                'pay_sn'  => $data['pay_sn'],
                'get_money_id' => $user['user_id'],
                'get_money_name' => $user['real_name'],
                'create_time'    => $data['create_time'],
            );
            $model->table('epay_barcode')->insert($insadd);
        }else{
            $data['pay_sn'] = $recode['pay_sn'];
            $data['create_time']  = $recode['create_time'];
            $data['sj_id'] = $user['user_id'];
        }
        import('agmpay.lib');
        $agm = new agmPay(ROOT_PATH.'/'. conf('pubfile'), ROOT_PATH.'/'.conf('prifile'));
        $key = $agm->encrypt($data);
        $link = conf('SITE_URL').'/api/index.php?n=shop_center&f=pay_balance&key='.$key;
        import('phpqrcode');
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        //生成二维码图片
        QRcode::png($link, false, $errorCorrectionLevel, $matrixPointSize, 2);
        exit;
    }else{
        err('身份错误，请重新登录');
    }
}
/*
 * 解析收款二维码
 */
function pay_balance(){
    $model = new M();
    $key = rawurldecode(I('get.key')?I('get.key'):I('post.key'));
    if(empty($key)){
        err('二维码参数错误,请重新扫描');
    }
    import('agmpay.lib');
    $agm = new agmPay(ROOT_PATH.'/'. conf('pubfile'), ROOT_PATH.'/'.conf('prifile'));
    $to_user_info = $agm->decrypt($key);
    if(empty($to_user_info)){
        err('二维码参数错误,请重新扫描2');
        return ;
    }
    //验证该店铺是否存在并且审核通过
    $is_store = $model -> table('store') -> where(array('store_id' => $to_user_info['sj_id'] , 'state' => 1))->find();
    file_put_contents('0221.txt', $model->getSql());
    file_put_contents('0222.txt', json_encode($is_store));
    if(empty($is_store)){
        err('该店铺不存在，或暂时关闭');
        return ;
    }
    if($is_store['is_trade']!=1)
    {
        err('该店铺交易未开通，或交易已关闭');
    }
    $user = $model->table('member')->where('user_id='.$to_user_info['sj_id'])->find();
    $class_goods = $model -> table('sgxt_class_goods') -> where(array('store_id' => $to_user_info['sj_id'] , 'state' => 1))->select();
    if(empty($class_goods)){
        err('店家没有设置商品类型，暂时不能支付');
        return ;
    }
    fk('二维码验证成功',array('url'=>'saoyisao.html','class_goods'=>$class_goods,'store_name'=>$is_store['store_name'],'store_id'=>$is_store['store_id'],'store_mobile'=>$user['user_name'],'pay_sn'=>$to_user_info['pay_sn'],'sj_id'=>$to_user_info['sj_id']));
}
/*
 * 用户余额支付
 */
function user_balance_pay(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $model = new M();
        $stroe_id = I('post.store_id');
        if(empty($stroe_id)){
            err('请重新扫描商家二维码');
            return ;
        }
        $classid = I('post.classid');
        if(empty($classid)){
            err('请选择商品类型');
            return ;
        }
        $money = I('post.money');
        if(empty($money)){
            err('请输入支付金额');
            return ;
        }
        $pay_sn = I('post.pay_sn');
        if(empty($pay_sn)){
            err('请重新扫描商家二维码');
            return ;
        }
        $sj_id = I('post.sj_id');
        $pay_passwd = I('post.pay_passwd');
        if(!check_pay_pass($user['user_id'],$pay_passwd)){
            err('支付密码错误');
            return ;
        }
        //修改该二维码状态
        $truename = $model->table('member') -> where(array('user_id'=> $user['user_id']))->getField('real_name');
        $save = array(
            'pay_money_id' => $user['user_id'],
            'pay_money_name' => $truename,
            'is_pay'  => 1,
            'pay_time' => time(),
        );
        $model->table('epay_barcode')->where(array('pay_sn' => $pay_sn)) ->update($save);
        out_balance($user,$sj_id , I('post.money') , I('post.pay_passwd'));
    }else{
        err('身份错误，请重新登录');
    }
}
function out_balance($user,$to_user_id , $to_money , $pay_passwd){
    $model = new M();
    if (preg_match("/[^0.-9]/", $to_money)) {
        err('金额必须是数字');
        return;
    }
    $to_row = $model->table('epay')->where('user_id='.$to_user_id)->find();
    $to_user_id = $to_row['user_id'];
    $to_user_name = $to_row['user_name'];
    $to_user_money = $to_row['balance'];
    if ($to_user_id == $user['user_id']) {
        err('不能为自己转账');
        return;
    }
    if (empty($to_user_id)) {
        err('目标用户不存在');
        return;
    }
    $row_epay = $model->table('epay')->where('user_id='.$user['user_id'])->find();
    $user_money = $row_epay['money'];
    $user_zf_pass = $row_epay['zf_pass'];
    $zf_pass = md5(trim($pay_passwd));
    if ($user_zf_pass != $zf_pass) {
        err('支付密码验证失败');
        return;
    }
    $order_sn = "40" . date('YmdHis',time()+8*3600).rand(1000,9999);
    if ($user_money < $to_money) {
        err('账户余额不足');
        return;
    } else {
        //线下余额支付时创建已完成订单
        $order = createOrder($user['user_id'] ,$to_user_id , 3 , I('post.money') , 0 , I('post.classid') , '线下购物积分支付');
        //更新用户的余额
        $new_user_money = $user_money - $to_money;
        $add_jian = array(
            'money' => $new_user_money,
        );
        $model->table('epay')->where('user_id=' . $user['user_id'])->update($add_jian);
		//查询订单信息
		$order=$model->table('order_offline')->where(array('order_sn'=>$order['order_sn']))->find();

        $log_text = $user['user_name'] . '向' . $order['seller_name'] . '转入金额' . $to_money . '元';
        //添加日志
        $add_epaylog = array(
            'user_id' => $order['buyer_id'],
            'user_name' => $order['buyer_name'],
            'to_id' => $order['seller_id'],
            'to_name' => $order['seller_name'],
            'order_id' => $order['order_id'],
            'order_sn' => $order['order_sn'],
            'add_time' => $order['add_time'],
            'type' => EPAY_OUT, //转出
            'money_flow' => 'outlay',
            'money' => $to_money,
            'complete' => 1,
            'log_text' => $log_text,
            'states' => 40,
            'payment_id' => 3 //余额支付
        );
        $model->table('epaylog')->insert($add_epaylog);
        if($order){
            fk('支付成功',$order);
        }else{
            err('支付失败');
        }
        /*
        //更新商家余额
        $new_to_user_money = $to_user_money + $to_money;
        $add_jia = array(
                'balance' => $new_to_user_money,
        );
        $model->table('epay')->where('user_id=' . $to_user_id)->update($add_jia);
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
        */
    }
}
/*
 * 创建线下订单
 */
function createOrder($buyid , $sellerid ,$paymentid ,$money ,$point , $classid , $paymess = '' ,$status=40){
    $model = new M();
    //获取买家信息
    $buyinfo = $model ->table('member') -> where(array('user_id'=>$buyid)) -> find();
    //获取卖家信息
    $sellername = $model ->table('store') -> where(array('store_id'=>$sellerid , 'state' => 1)) -> getField('store_name');
    //获取支付类型
    $payment =  $model -> table('payment') -> where(array('payment_id' => $paymentid)) -> find();
    //获取分类信息
    $classname = $model -> table('sgxt_class_goods') -> where(array('class_id' => $classid)) -> getField('name');
    //封装生成数据
    $order_sn = substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
    $buyer_name=$buyinfo['real_name']?$buyinfo['real_name']:$buyinfo['user_name'];
    $orderdata = array(
        'order_sn'      =>  $order_sn,
        'seller_id'     =>  $sellerid,
        'seller_name'   =>  $sellername,
        'buyer_id'      =>  $buyid,
        'buyer_name'    =>  $buyer_name,
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
        'classname'     =>  $classname,
        'is_check'		=>	0
    );
    $insid = $model -> table('order_offline') -> insert($orderdata);
    if($insid){
        $order['orderid'] = $insid;
        $order['order_sn'] = $order_sn;
        //addMessage('order_offline','order_id',$insid,$buyer_name.'线下购物积分支付成功',$buyid,$buyer_name);
        return $order ;
    }
}
function check_pay_pass($user_id,$pay_passwd){
    $model = new M();
    $pass = $model->table('epay') -> where(array('user_id'=> $user_id)) ->getField('zf_pass');
    return $pass == md5($pay_passwd);
}

function shop_class_list(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $model=new M();
        if($user['type'] != 2){
            err('您不是商户');
        }
        $list = $model->table('sgxt_class_goods')
            ->where('store_id='.$user['user_id'])
            ->select();
        fk('验证通过',$list);

    }else{
        err('身份错误，请重新登录');
    }
}

//添加分类
function addClassName(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    if(empty($_POST['classname'])) err('分类名不能为空');
    $user =checkToken($token);
    if($user){
        if($user['type'] !=2) err('你不是商户');
        $m =new M();
        $data =array('name'=>$_POST['classname'],
            'store_id'=>$user['user_id'],
            'state'=>1
        );
        $classname =$m->table('sgxt_class_goods')->insert($data);
        if($classname){
            fk('添加成功');
        }else{
            err('添加失败');
        }

    }

}

//删除分类
function delClassName(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    if(empty($_POST['classid'])) err('分类ID不存在');
    $user =checkToken($token);
    if($user){
        if($user['type'] !=2) err('您不是商户');
        $m= new M();
        $delclass =$m->table('sgxt_class_goods')
            ->where(array('class_id'=>trim($_POST['classid'])))
            ->delete();
        if($delclass){
            fk('删除成功');
        }else{
            err('删除失败');
        }
    }else{
        err('请先登录');
    }
}


//可用积分
function use_point(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    if($user){
        if($user['type'] != 2) err('您不是商户');
        $m =new M();
        $usepoint = $m->table('member')-> where(array('user_id' =>$user['user_id']))->getField('pay_point');
        $point =$usepoint ? $usepoint : '0';
        fk('可用积分',$point);
    }else{
        err('身份错误,请重新登录');
    }
}



function send_point_step1(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $model=new M();
        if($user['type'] != 2){
            err('您不是商户');
        }
        $store_info=$model->table('store')->where(array('store_id'=>$user['user_id'],'state'=>1))->find();
        if(empty($store_info))
        {
            err('该店铺不存在，或暂时关闭');
        }
        if($store_info['is_trade']!=1)
        {
            err('该店铺交易未开通，或交易已关闭');
        }
        // $mobile = trim(I('mobile'));
        // $point = intval(I('point'));
        $mobile =trim($_POST['mobile']);
        $point =trim($_POST['point']);
        $point =(float)$point;
        $get_user_info = $model->table('member')->where(array('user_name' => $mobile)) ->find();
        if(empty($get_user_info)){
            err('你输入的电话有误');
        }
        if($get_user_info['user_id'] == $user['user_id']){
            err('不能为自己发送积分');
        }
        $pay_point = $model->table('member')-> where(array('user_id' => $user['user_id'])) -> getField('pay_point');
        //执行发积分的方法
        //$all_point = $point / conf('PAY_INFO/shops_point');
        $all_point = $point * 3.33;
        $all_point = round($all_point,2);
        if($pay_point < $point){
            err('积分不足，无法发送');
            return ;
        }
        fk('验证通过',array(
            'username' => $get_user_info['real_name']?$get_user_info['real_name']:$get_user_info['user_name'],
            'user_point' => $all_point,
            'system_point' => round($point * 2.33,2),
        ));


    }else{
        err('身份错误，请重新登录');
    }
}

/**
 * 商家发积分
 */
function send_point(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $model=new M();
        $mobile = trim(I('post.mobile'));
        $point = floatval(I('post.point'));
        $passwd = trim(I('post.passwd'));
        $money = trim(I('post.money'));
        $classid = trim(I('post.classid'));
        $remark = trim(I('post.remark'));
        if(empty($mobile)){
            err('手机号不能为空');
        }
        if(empty($point)){
            err('积分不能为空');
        }
        if($point < 1){
            err('不能发少于1积分');
        }
        // if(!is_int($point)){
        // 	err('赠送积分必须为整数');
        // }
        if(empty($passwd)){
            err('密码不能为空');
        }
        $get_user_info = $model->table('member')->where(array('user_name' => $mobile)) ->find();
        if(empty($get_user_info)){
            err('你输入的电话有误');
        }
        if($get_user_info['user_id'] == $user['user_id']){
            err('不能为自己发送积分');
        }
        if(empty($classid)){
            err('请填写商品名称');
        }
        //获取分类信息

        $classname = $model -> table('sgxt_class_goods') -> where(array('class_id' => $classid)) -> getField('name');
        if(empty($classname)){
            err('商品名称不存在');
        }


        if(empty($money)){
            err('请输入消费金额');
        }
        
        if($money <= 0){
            err('消费金额必须大于0');
        }

        //验证密码是否正确
        $sqlPassword = $model->table('epay') ->where(array('user_id' => $user['user_id'])) ->getField('zf_pass');
        if(md5($passwd)!= $sqlPassword){
            err('支付密码错误');
            return ;
        }
        //验证商家积分是足够
        $pay_point = $model->table('member')-> where(array('user_id' => $user['user_id'])) -> getField('pay_point');
        //执行发积分的方法
        //$all_point = $point / conf('PAY_INFO/shops_point');
        $all_point = $point * 3.33;
        $all_point = sprintf("%.2f",$all_point);
        if($pay_point < $point){
            err('积分不足，无法发送');
            return ;
        }
        //创建线下订单
        //$order = $this->order_offline->createOrder($get_user_info['user_id'] ,$user['user_id'] , 9 , $money , $point , $classid , $remark , 11);
        //获取买家信息
        $buyinfo = $get_user_info;
        //获取卖家信息
        $sellername = $model ->table('store') -> where(array('store_id'=>$user['user_id'] , 'state' => 1)) -> getField('store_name');
        //获取支付类型
        $payment =  $model -> table('payment') -> where(array('payment_id' => 9)) -> find();
        //封装生成数据
        $order_sn = substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
        $orderdata = array(
            'order_sn'      =>  $order_sn,
            'seller_id'     =>  $user['user_id'],
            'seller_name'   =>  $sellername,
            'buyer_id'      =>  $get_user_info['user_id'],
            'buyer_name'    =>  $buyinfo['real_name']?$buyinfo['real_name']:$buyinfo['user_name'],
            'buyer_email'   =>  $buyinfo['email'],
            'status'        =>  40,
            'add_time'      =>  time(),
            'payment_id'    =>  9,
            'payment_name'  =>  $payment['payment_name'],
            'payment_code'  =>  $payment['payment_code'],
            'pay_time'      =>  time(),
            'pay_message'   =>  $remark,
            'goods_amount'  =>  $money,
            'order_amount'  =>  $money,
			'shop_point'    =>  $point,
            'point'         =>  $all_point,
            'classid'       =>  $classid,
            'classname'     =>  $classname,
            'is_check'		=>  0
        );
        $insid = $model -> table('order_offline') -> insert($orderdata);
        $order = array();
        if($insid){
            $order['orderid'] = $insid;
            $order['order_sn'] = $order_sn;
            $sendPass =  $model ->table('member') -> where('user_id='.$user['user_id'].' and pay_point>='.$point) -> setDec('pay_point' ,  $point);
            //添加商家发积分记录
            $data=array('sendid'=>$user['user_id'],'getid'=>$get_user_info['user_id'],'point'=>$all_point,'shops_point'=>$point,'system_point'=>($all_point-$point),'oto'=>'offline','createtime'=>time(),'sendname'=>$sellername,'getname'=>$orderdata['buyer_name'],'order_id'=>$insid);
            $id=$model->table('sgxt_get_point')->insert($data);
            addMessage('sgxt_get_point' , 'id' , $id,$sellername.'赠送您积分：'.$all_point,$get_user_info['user_id'] , $orderdata['buyer_name']);
            fk('积分发送成功，等待审核',$order);
        }else{
            err('积分发送失败，请重新发送');
        }
    }else{
        err('身份错误，请重新登录');
    }
}
/*
 * 县区代理审核订单
 */
function check_order(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user && $user['type'] == 5){
        $model = new M();
        $order_type = trim(I('post.order_type'));
        $orderSn = trim(I('post.order_sn'));
        if($orderSn == ''){
            err('订单不存在');exit;
        }
        $orderList = explode(',', $orderSn);
        $is_err = false;
        $msg = '未知错误';
        foreach($orderList as $key=>$order_sn){
            $model->startTrans(); //开启事务
            $is_check = intval(I('post.is_check'));
            if($order_sn[0] == 'P'){
                $sgxt_order = $model -> table('sgxt_order')->where(array('orderid' => $order_sn))->find();
                if($sgxt_order){
                    if($sgxt_order['status'] != 0){
                    }else{
                        err('订单未支付');exit;
                    }
                }else{
                    err('订单不存在');exit;
                }
            }
            if($order_type == 'offline'){
                $order = $model->table('order_offline')->where(array('order_sn'=>$order_sn))->find();
                if(empty($order))
                {
                    err('订单不存在');
                }
                $seller_id = $order['seller_id'];
                $buyer_id = $order['buyer_id'];
                $getUser = $model->table('member') -> where(array('user_id' => $order['buyer_id']))  ->find();
                $sendUser = $model->table('member') -> where(array('user_id' => $order['seller_id']))  ->find();
                if($order['is_check'] != 0){
                    err('订单已审核过');exit;
                }else{
                    if($order['payment_id'] == 9){
                        try{
                            if($is_check == 2){
                                //归还商家发送的积分
                                $gp = $model->table('sgxt_get_point')->where('sendid='.$seller_id.' and order_id='.$order['order_id'])->find();
                                $rs = $model->table('member')->where('user_id='.$seller_id)->setInc('pay_point',$gp['shops_point']);
                                $user = $model->table('member')->where('user_id='.$seller_id)->find();
                                //修改订单状态为驳回
                                $model->table('order_offline')->
                                where('order_id='.$order['order_id'])->
                                update(array(
                                        'is_check'=>2,
                                        'check_id'=>$user['user_id'],
                                        'check_time'=>time()
                                    )
                                );
                                $pointPass = $model -> table('sgxt_get_point')->where(array('order_id'=>$order['order_id'],'oto'=>'offline')) -> update(array('is_pass' => 2));
                                //fk('订单已驳回');
                                $msg = '订单已驳回';
                                $is_check = true;
                            }else if($is_check == 1){
                                if($order['point']>0)
                                {
                                    $shops_point = round($order['point']*conf('PAY_INFO/shops_point'));
                                    $system_point = round($order['point']*conf('PAY_INFO/system_point'));

                                    $shops_point = round($shops_point,2);

                                    //给用户添加消费积分

                                    $getPass = $model -> table('member') ->where(array('user_id' => $buyer_id)) ->setInc('point' ,$order['point']);
                                    $pointData = array(
                                        'sendid'  =>  $seller_id,
                                        'sendname' => $sendUser['real_name'],
                                        'getid'   => $getUser['user_id'],
                                        'getname' => $getUser['real_name']?$getUser['real_name']:$getUser['user_name'],
                                        'point'   => $order['point'],
                                        'is_pass' => 1,
                                        'createtime' => time(),
                                        'oto'   =>  'offline',
                                        'shops_point' => $shops_point,
                                        'system_point' => $system_point,
                                        'order_id' => $order['order_id'],
                                    );
                                    $pointPass = $model -> table('sgxt_get_point')->where(array('order_id'=>$order['order_id'],'oto'=>'offline')) -> update(array('is_pass' => 1));
                                    if(!$getPass || !$pointPass){
                                        throw new Exception($seller_id."积分发送失败".$getUser['user_name']."分".$order['point']);
                                    }
                                    autoPointBean($getUser['user_id']);
                                    logicEarnings($sendUser , $order['point'] , $getUser,$order['order_id'],$order_sn,$order_type);
                                }
                                //auto_complete_order($order);
                                $model->commit();
                                $model->table('order_offline')->where('order_id='.$order['order_id'])->update(array('is_check'=>1,'check_id'=>$user['user_id'],'check_time'=>time()));
                                //$model->commit();
                                //fk('审核成功');
                                $msg = '审核成功';
                                $is_check = true;
                            }
                        }catch (Exception $e) {
                            $model->rollback();
                            //$e->addLog('sendPoint.txt');
                            err('审核失败');exit;
                        }
                    }else if($order['payment_id'] == 3){
                        if($is_check == 2){
                            //驳回订单归还用户余额
                            $model->table('epay')->where('user_id='.$buyer_id)->setInc('money',$order['order_amount']);
                            //返回余额日志
                            $log_text_to = $order['buyer_name'] . '收到县代' .$user['user_name']. '驳回订单金额' . $order['order_amount'] . '元';
                            $add_epaylog_to = array(
                                'user_id' => $order['buyer_id'],
                                'user_name' => $order['buyer_name'],
                                'to_id' => $order['seller_id'],
                                'to_name' => $order['seller_name'],
                                'order_id' => $order['order_id'],
                                'order_sn' => $order_sn,
                                'add_time' => $order['add_time'],
                                'type' => EPAY_IN, //转入
                                'money_flow' => 'income',
                                'money' => $order['order_amount'],
                                'complete' => 1,
                                'log_text' => $log_text_to,
                                'states' => 40,
                            );
                            $model->table('epaylog')->insert($add_epaylog_to);
                            $model->table('order_offline')->where('order_id='.$order['order_id'])->update(array('is_check'=>2,'check_id'=>$user['user_id'],'check_time'=>time()));
                            //fk('订单已驳回');
                            $msg = '订单已驳回';
                            $is_check = true;
                        }else if($is_check == 1){
                            $to_user_name = $order['seller_name'];
                            $to_user_id = $order['seller_id'];
                            $to_money = $order['order_amount'];

                            $to_row = $model->table('epay')->where('user_id='.$to_user_id)->find();
                            $to_user_id = $to_row['user_id'];
                            $to_user_name = $to_row['user_name'];
                            $to_user_money = $to_row['balance'];
                            $new_to_user_money = $to_user_money + $to_money;
                            $add_jia = array(
                                'balance' => $new_to_user_money,
                            );
                            $model->table('epay')->where('user_id='.$to_user_id)->update($add_jia);

                            $log_text_to = $to_user_name . '收到' .$order['buyer_name']. '转入金额' . $to_money . '元';
                            $add_epaylog_to = array(
                                'user_id' => $to_user_id,
                                'user_name' => $to_user_name,
                                'to_id' => $order['buyer_id'],
                                'to_name' => $order['buyer_name'],
                                'order_id' => $order['order_id'],
                                'order_sn' => $order_sn,
                                'add_time' => $order['add_time'],
                                'type' => EPAY_IN, //转入
                                'money_flow' => 'income',
                                'money' => $to_money,
                                'complete' => 1,
                                'log_text' => $log_text_to,
                                'states' => 40,
                            );
                            $model->table('epaylog')->insert($add_epaylog_to);
                            //paymentlog($order['buyer_id'] , $order['buyer_name'] ,$order['order_amount'] , '3' , $to_user_id , $to_user_name , $order['order_id'], $order['order_sn']);
                            //addMessage('order_offline' , 'order_id' , $order['order_id'] , $order['buyer_name'].conf('push_message/1') ,$to_user_id , $to_user_name);
                            //支付完成过推送成功的消息模板
                            //$weixinModel =  & m('weixin_login');
                            /*$storename = $model -> table('store')->where(array('store_id' => $to_user_id)) ->getField('store_name');
                            $sendarr = array(
                                $storename.$to_user_name,
                                $order['order_sn'],
                                I('post.money'),
                                date('Y-m-d H:i'),
                            );*/
                            //$weixinModel -> pus_message($this->visitor->get('user_id') , 1 , '购物积分支付成功',$sendarr,'欢迎下次光临' );
                            $model->table('order_offline')->where('order_id='.$order['order_id'])->update(array('is_check'=>1,'check_id'=>$user['user_id'],'check_time'=>time()));
                            $msg = '订单审核通过';
                            $is_check = true;
                            //fk('订单审核通过');
                        }
                    }
                }
            }else if($order_type == 'online'){
                $order = $model->table('order')->where(array('order_sn'=>$order_sn))->find();
                if(empty($order))
                {
                    err('订单不存在');
                }
                if($order['is_check'] != 0){
                    $is_check = false;
                    err('订单已审核过');exit;
                }else{
                    if($order['status'] != 40){
                        $is_check = false;
                        err('订单还未完成');exit;
                    }else{
                        if($order['auto_finished_time'] > time()){
                            err('最后退款期限未到,暂不能审核');exit;
                        }
                        if($is_check==2)
                        {
                            err('暂不支持驳回请求');
                        }
                        if($is_check == 1){
                            try{
                                //余额支付给商家增加货款
                                if($order['payment_id']==3)
                                {
                                    //减去冻结的
                                    $model -> table('epay') ->where(array('user_id' => $order['seller_id']))->setDec('freeze_balance' ,$order['order_amount']);
                                    //增加商家货款
                                    $model -> table('epay') ->where(array('user_id' => $order['seller_id'])) ->setInc('balance' ,$order['order_amount']);
                                }
                                if($order['point']>0)
                                {
                                    $shops_point = round($order['point']*conf('PAY_INFO/shops_point'),2);
                                    $system_point = round($order['point']*conf('PAY_INFO/system_point'),2);
                                    //$shops_point = printf("%.2f",$shops_point);
                                    //$shops_point = round($shops_point,2);

                                    $sendUser = $model->table('member')->where('user_id='.$order['seller_id'])->find();
									
									//自动扣积分
									$sendpass=$model->table('member')->where('user_id='.$order['seller_id'])->setDec('pay_point',$shops_point);
									
                                    $getUser = $model->table('member')->where('user_id='.$order['buyer_id'])->find();
                                    //给用户添加消费积分
                                    $getPass = $model -> table('member') ->where(array('user_id' => $order['buyer_id'])) ->setInc('point' ,$order['point']);
                                    $pointData = array(
                                        'sendid'  =>  $order['seller_id'],
                                        'sendname' => $order['seller_name'],
                                        'getid'   => $order['buyer_id'],
                                        'getname' => $order['buyer_name'],
                                        'point'   => $order['point'],
                                        'is_pass' => 1,
                                        'createtime' => $order['add_time'],
                                        'oto'   =>  'online',
                                        'shops_point' => $shops_point,
                                        'system_point' => $system_point,
                                        'order_id' => $order['order_id'],
                                    );
                                    $pointPass = $model -> table('sgxt_get_point') -> insert($pointData);
                                    if(!$getPass || !$pointPass || !$sendpass){
                                        throw new MyException($order['seller_id']."积分发送失败".$order['buyer_name']."分".$order['point']);
                                    }
                                    autoPointBean($order['buyer_id']);
                                    logicEarnings($sendUser , $order['point'] , $getUser,$order['order_id'],$order_sn,$order_type);
                                }
                                $model->table('order')->where('order_id='.$order['order_id'])->update(array('is_check'=>1,'check_id'=>$user['user_id'],'check_time'=>time()));
                                //fk('审核成功');
                                $msg = '审核成功';
                                $is_check = true;
                            }catch (Exception $e){
                                $model->rollback();
                                err('审核失败');exit;
                                $is_check = false;
                            }
                        }else if($is_check==2){
                            /*if($order['point']>0)
                            {
                                //归还商家发送的积分
                                $model->table('member')->where('user_id='.$order['seller_id'])->setInc('pay_point',$order['point']*conf('PAY_INFO/shops_point'));
                            }*/
                            if($order['payment_id']==3)
                            {
                                $model->table('epay')->where('user_id='.$order['buyer_id'])->setInc('money',$order['order_amount']);
                                //返回余额日志
                                $log_text_to = $order['buyer_name'] . '收到县代' .$user['user_name']. '驳回订单金额' . $order['order_amount'] . '元';
                                $add_epaylog_to = array(
                                    'user_id' => $order['buyer_id'],
                                    'user_name' => $order['buyer_name'],
                                    'to_id' => $order['seller_id'],
                                    'to_name' => $order['seller_name'],
                                    'order_id' => $order['order_id'],
                                    'order_sn' => $order_sn,
                                    'add_time' => $order['add_time'],
                                    'type' => EPAY_IN, //转入
                                    'money_flow' => 'income',
                                    'money' => $order['order_amount'],
                                    'complete' => 1,
                                    'log_text' => $log_text_to,
                                    'states' => 40,
                                );
                                $model->table('epaylog')->insert($add_epaylog_to);
                                //更新商家冻结中的资金
                                $model -> table('epay') ->where(array('user_id' => $order['seller_id']))->setDec('freeze_balance' ,$order['order_amount']);
                                $log_text_to = $order['seller_name'] . '收到县代' .$user['user_name']. '驳回订单金额' . $order['order_amount'] . '元';
                                $add_epaylog_to = array(
                                    'user_id' => $order['seller_id'],
                                    'user_name' => $order['seller_name'],
                                    'to_id' => $order['buyer_id'],
                                    'to_name' => $order['buyer_name'],
                                    'order_id' => $order['order_id'],
                                    'order_sn' => $order_sn,
                                    'add_time' => $order['add_time'],
                                    'type' => EPAY_OUT, //转出
                                    'money_flow' => 'outlay',
                                    'money' => $order['order_amount'],
                                    'complete' => 1,
                                    'log_text' => $log_text_to,
                                    'states' => 40,
                                );
                                $model->table('epaylog')->insert($add_epaylog_to);
                            }
                            //修改订单状态为驳回
                            $model->table('order')->where('order_id='.$order['order_id'])->update(array('is_check'=>2,'check_id'=>$user['user_id'],'check_time'=>time()));
                            //fk('订单已驳回');
                            $msg = '订单已驳回';
                            $is_check = true;
                        }
                    }
                }
            }
        }
        if($is_check){
            $model->commit();
            fk($msg);
        }else{
			$model->rollback();
            err($msg);
        }
    }else{
        err('身份错误，请重新登录');
    }
}
function autoPointBean($userid){
    $model = new M();
    $userinfo = $model->table('member') -> where("user_id = $userid")->find();
    $payinfo = conf('PAY_INFO');
    if($userinfo['point'] < $payinfo['bean']){
        return;
    }
    $beanNum = floor($userinfo['point']/$payinfo['bean']);
    $delPoint = $beanNum  * $payinfo['bean'];
    $pass= $model -> table('member') -> where(array('user_id' => $userid)) -> setDec('point',$delPoint);
    //更新改用户的受赠权
    if($pass){
        $beanPass =$model ->table('member') -> where(array('user_id' => $userid)) -> setInc('point_peac' ,$beanNum);
        if($beanPass){
            addBean($userid , $beanNum);
        }else{
            throw new Exception("用户".$userid."受赠权增加".$beanNum."失败");
        }
    }else{
        throw new Exception("用户".$userid."积分减少".$delPoint."失败");
    }
}
function addBean($userid, $beanNum){
    $model = new M();
    if(empty($userid) || empty($beanNum))  return ;
    $addData = array();
    for($i = 0 ;$i< $beanNum ; $i++){
        $code = buildCountRand();
        $addData = array(
            'bean_number' => $code[0],
            'user_id'     => $userid,
            'bean_price'  => conf('PAY_INFO/bean'),
            'status'      => 1,
            'createtime'  => time(),
        );
        $ins = $model -> table('sgxt_bean') -> insert($addData);
        if(!$ins){
            throw new Exception("用户".$userid."受赠权增加".$beanNum."失败");
        }
    }
    //插入数据表
}
function logicEarnings($senduser , $point ,$getuser,$order_id='',$order_sn,$order_type){
    $model = new M();
    $logHandler = new CLogFileHandler(ROOT_PATH . '/logs/profit/' . date('Y-m-d') . '.log');
    $log = Log::Init($logHandler, 15);
    $logInfo = "[增加收益的逻辑关系处理--Start]\r\n发送用户：".$senduser['real_name'] . '('.$senduser['user_name'].'),接收用户：'.$getuser['real_name'].'('.$getuser['user_name'].')积分:'.$point."\r\n";
    $real_point = $point;
    if(empty($senduser) || empty($point) || empty($getuser)) {
        throw new Exception("发送者信息积分接收者信息为空");
    }
    //处理上三级关系的
    //获取到改用户的上三级人
    $path = $getuser['path'];
    $point = round($point*conf('PAY_INFO/shops_point')); //计算出所付出的钱数
    $money = '';
    $logInfo .= "[接收用户的三级关系处理--Start]\r\n";
    if($getuser['pid']){
        $user = $model->table('member')->where(array('user_id'=>$getuser['pid'])) -> find();
        $money = '';
        $money = $point * conf("user_reward/".$user['type']."/recommend_level3");
        $money = sprintf("%.2f",$money);
        $logInfo .= "\t上级用户：" . $user['real_name'].'('.$user['user_name'].'),获取收益：'.$point .'*'. conf("user_reward/".$user['type']."/recommend_level3").'='.$money ."\r\n";
        addBalance($user ,$getuser , $money , '1' ,$real_point,$order_id,$order_sn,$order_type);
    }
    /*
    if(!empty($path)){
        $pathinfo = $model->table('member')->where(" user_id in ( $path )") -> select();
        if(!empty($pathinfo)){
            foreach($pathinfo as $k => $user){
                $money = '';
                $money = $point * conf("user_reward/".$user['type']."/recommend_level3");
                $money = sprintf("%.2f",$money);
                $logInfo .= "\t上级用户：" . $user['real_name'].'('.$user['user_name'].'),获取收益：'.$point .'*'. conf("user_reward/".$user['type']."/recommend_level3").'='.$money ."\r\n";
                addBalance($user ,$getuser , $money , '1' ,$real_point,$order_id);
            }
        }
    }*/
    else{
        $logInfo .= "\t没有上级关系\r\n";
    }
    $logInfo .= "[接收用户的三级关系处理--End]\r\n";
    //商家的直推人收益
    $logInfo .= "[商家的直推人收益处理--Start]\r\n";
    if(!empty($senduser['pid'])){
        $money = 0;
        $ztuser = $model -> table('member') -> where(array('user_id' => $senduser['pid'])) ->find();
        $logInfo .= "\t直推人：".$ztuser['real_name'].'('.$ztuser['user_name'].")\r\n";
        //验证直推人权限 普通用户不享受该权限
        if($ztuser['type'] != 1) {
            $money = $point * conf("user_reward/".$ztuser['type']."/recommen_shops");
            $money = sprintf("%.2f",$money);
            $logInfo .= "\t获得收益为：".$point . '*' . conf("user_reward/".$ztuser['type']."/recommen_shops") . '='.$money."\r\n";
            addEarnings($ztuser , $senduser,$money , '4' , $real_point,$order_id,$order_sn,$order_type);
        }else{
            $logInfo .= "\t普通用户不享受该权限\r\n";
        }
    }else{
        $logInfo .= "\t没有直推人";
    }
    $logInfo .= "[商家的直推人收益处理--End]\r\n";
    //社区内的商家提成
    $logInfo .= "[社区内的商家提成处理--Start]\r\n";
    if(!empty($senduser['opid'])){
        $money = '';
        $opuser = $model -> table('member') -> where(array('user_id' => $senduser['opid'])) ->find();
        if($opuser['type'] == 4) {
            $logInfo .= "\t社区代理：".$opuser['real_name']."(".$opuser['user_name'].")";
            $money = $point * conf("user_reward/".$opuser['type']."/area_shops");
            $money = sprintf("%.2f",$money);
            $logInfo .= "获得收益为：".$point . '*' . conf("user_reward/".$opuser['type']."/area_shops") . '='.$money."\r\n";
            addEarnings($opuser , $senduser,$money , '5' , $real_point,$order_id,$order_sn,$order_type);
        }else{
            $logInfo .= "\t不是社区代理\r\n";
        }
    }else{
        $logInfo .= "\t没有社区代理\r\n";
    }
    $logInfo .= "[社区内的商家提成处理--End]\r\n";
    //县级内所有商家提成
    $logInfo .= "[县级内所有商家提成处理--Start]\r\n";
    if(!empty($senduser['area'])){
        $areaagent = $model -> table('member') -> where(array('ahentarea' => $senduser['area'])) ->find();
        $money = '';
        if(!empty($areaagent) && $areaagent['type'] == 5) {
            $money = $point * conf("user_reward/".$areaagent['type']."/area_shops");
            $money = sprintf("%.2f",$money);
            $logInfo .= "\t县级代理：".$areaagent['real_name'].'('.$areaagent['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$areaagent['type']."/area_shops") . '='.$money."\r\n";
            addEarnings($areaagent , $senduser,$money , '5' , $real_point,$order_id,$order_sn,$order_type);
            //检验县级上级是否为县级
            $logInfo .= "\t检测改县级代理的推荐人是否也为县级代理\r\n";
            if(!empty($areaagent['pid'])){
                $sjuser = $model -> table('member') -> where(array('user_id' => $senduser['pid'])) ->find();
                if($sjuser['type'] == 5){
                    $money = '';
                    $money = $point * conf("user_reward/".$sjuser['type']."/recommen_ounty_shops");
                    $money = sprintf("%.2f",$money);
                    $logInfo .= "\t改县级代理的推荐人：".$sjuser['real_name'].'('.$sjuser['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$sjuser['type']."/recommen_ounty_shops") . '='.$money."\r\n";
                    addEarnings($sjuser , $senduser,$money , '6' , $real_point,$order_id,$order_sn,$order_type);
                }else{
                    $logInfo .= "\t改县级代理的推荐人不是县级代理\r\n";
                }
            }else{
                $logInfo .= "\t改县级代理没有推荐人\r\n";
            }
        }else{
            $logInfo .= "\t所属代理不是县级代理\r\n";
        }
    }
    $logInfo .= "[县级内所有商家提成处理--End]\r\n";
    //查出该会员省市县及社区代理
    $logInfo .= "[获取收益会员的省、市、县、社区代理各级收益处理--Start]\r\n";
    $agent_province_id = $model -> table('member') -> where(array('ahentarea' => $getuser['province'])) ->getField('user_id');
    $agent_city_id = $model -> table('member') -> where(array('ahentarea' => $getuser['city'])) ->getField('user_id');
    $agent_area_id = $model -> table('member') -> where(array('ahentarea' => $getuser['area'])) ->getField('user_id');
    $agent_opid = $getuser['opid'];
    $idin = '';
    if(!empty($agent_province_id)){
        $idin .=  $agent_province_id . ',';
    }
    if(!empty($agent_city_id)){
        $idin .=  $agent_city_id . ',';
    }
    if(!empty($agent_area_id)){
        $idin .=  $agent_area_id . ',';
    }
    if(!empty($agent_opid)){
        $idin .=  $agent_opid;
    }
    $idin = trim($idin,',');
    //if(empty($idin)) return ;
    $agentuser = $model -> table('member') -> where(" user_id in ($idin)") -> select();
    //省代获得佣金
    foreach($agentuser as $user){
        $money = '';
        $agentuser = '';
        if($user['type'] > 3) {
            $money = $point * conf("user_reward/".$user['type']."/area_users");
            $money = sprintf("%.2f",$money);
            $type_cn = '';
            if($user['type'] == 4){
                $type_cn = '社区代理';
            }elseif($user['type'] == 5){
                $type_cn = '县级代理';
            }elseif($user['type'] == 6){
                $type_cn = '市级代理';
            }elseif($user['type'] == 7){
                $type_cn = '省级代理';
            }
            $logInfo .= "\t".$type_cn."：".$user['real_name'].'('.$user['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$user['type']."/area_users") . '='.$money."\r\n";
            addEarnings($user , $getuser,$money , '7' , $real_point,$order_id,$order_sn,$order_type);
        }
    }
    $logInfo .= "[获取收益会员的省、市、县、社区代理各级收益处理--End]\r\n";
    $logInfo .= "[增加收益的逻辑关系处理--End]\r\n";
    log::DEBUG($logInfo);
}
function addBalance($getuser , $formuser ,$money , $type ,$real_point,$order_id='',$order_sn='',$order_type=''){
    $model = new M();
    $money = (float)$money;
    if(empty($money)) return ;
    //增加用余额 属于冻结状态
    $pass = $model -> table('epay') -> where(array('user_id' => $getuser['user_id'])) ->setInc('money_dj' , sprintf("%.2f",$money*0.95));
    //增加幸福积分
    $model->table('member')->where(array('user_id' => $getuser['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
    $adddata = array(
        'user_id'  => $getuser['user_id'],
        'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
        'get_money' => sprintf("%.2f" ,$money*0.95),
        'real_point' => $real_point,
        'source_type' => $type,
        'from_username' => $formuser['real_name']?$formuser['real_name']:$formuser['user_name'],
        'from_userid'  => $formuser['user_id'],
        'createtime'  => time(),
        'times'       => date('Ym'),
        'area'   =>  $getuser['area'],
        'city'   =>  $getuser['city'],
        'province' =>  $getuser['province'],
        'opid'   =>  $getuser['opid'],
        'happiness' => sprintf("%.2f",$money*0.05),
        'order_id' => $order_id,
        'order_sn' => $order_sn,
        'order_type' => $order_type
    );
    $pass1 =  $model->table('sgxt_balance') -> insert($adddata);
    if(!$pass || !$pass1){
        throw new Exception("用户".$getuser['user_id']."增加余额".$money."失败");
    }
}
function addEarnings($getuser , $formuser ,$money , $type ,$real_point,$order_id='',$order_sn='',$order_type=''){
    $model = new M();
    $money = (float)$money;
    if(empty($money)) return ;
    //更新用户冻结收益
    $pass = $model -> table('epay') -> where(array('user_id' => $getuser['user_id'])) ->setInc('freeze_earnings' , $money);
    $adddata = array(
        'user_id'  => $getuser['user_id'],
        'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
        'remain_money' => $money,
        'real_point' => $real_point,
        'source_type' => $type,
        'from_username' => $formuser['real_name']?$formuser['real_name']:$formuser['user_name'],
        'from_userid'  => $formuser['user_id'],
        'createtime'  => time(),
        'times'       => date('Ym'),
        'area'   =>  $getuser['area'],
        'city'   =>  $getuser['city'],
        'province' =>  $getuser['province'],
        'opid'   =>  $getuser['opid'],
        'order_id' => $order_id,
        'order_sn' => $order_sn,
        'order_type' => $order_type
    );
    $pass1 = $model->table('sgxt_profit') -> insert($adddata);
    if(!$pass ||  !$pass1){
        throw new Exception("用户".$getuser['user_id']."增加收益".$money."失败");
    }
}
function auto_complete_order($orderid){
    $model = new M();
    $order = $model ->table('order_offline')->where(array('order_sn' => $orderid['order_sn'])) ->update(array('status' => 40));
    if($order === false){
        throw new Exception("订单".$orderid['order_sn']."状态修改失败");
    }
}
function paymentlog($userid , $username ,$money , $paymentid, $toid ='' ,$toname ='',$orderid='' ,$ordersn=''){
    $model = new M();
    $user = $model->table('member')->where('user_id='.$userid)->find();
    $log = logtext($paymentid,$money);
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
    return $model->table('paymentlog') -> insert($data);
}
function logtext($paymentid , $money){
    $log = '';
    switch($paymentid){
        case '1' : $log = '你在商城使用余额成功支付一笔订单，支付金额为'.$money.'元';break;
        case '2' : $log = '你在商城使用微信支付成功支付一笔订单，支付金额为'.$money.'元';break;
        case '3' : $log = '你在联盟商家使用购物积分成功支付一笔订单，支付金额为'.$money.'元';break;
        case '4' : $log = '你在联盟商家使用现成功支付一笔订单，支付金额为'.$money.'元';break;
        case '5' : $log = '你在啥都行联盟使用微信支付成功购买'.$money.'元的积分';break;
        case '6' : $log = '你在啥都行联盟使用货款成功购买'.$money.'元的积分';;break;
        case '7' : $log = '你在啥都行联盟成功提现收益'.$money.'元';break;
        case '8' : $log = '你在啥都行联盟成功提现货款'.$money.'元';break;
        case '9' : $log = '你在啥都行联盟使用连连支付成功购买'.$money.'元的积分';break;
        case '10' : $log = '你在商城使用银行打款成功支付一笔订单，支付金额为'.$money.'元';break;
    }
    return $log;
}
/*
function addMessage($table , $key ,$value ,$title ,$touserid = '', $touser = '' ,$checked = '1' , $pushtype = 'one'){
	$model = new M();
	$add = array(
			'table_name'  => $table,
			'table_key'    => $key,
			'table_value'  => $value,
			'title'  => $title,
			'checked' => $checked,
			'to_user' => $touser,
			'to_userid' => $touserid,
			'addtime'  => time(),
			'push_type' => $pushtype,
	);
	return  $model->table('push_message') -> add($add);
}
*/
interface ILogHandler{
    public function write($msg);
}
class CLogFileHandler implements ILogHandler{
    private $handle = null;
    public function __construct($file = '')	{
        $this->handle = fopen($file,'a');
    }
    public function write($msg)	{
        fwrite($this->handle, $msg, 4096);
    }
    public function __destruct()	{
        fclose($this->handle);
    }
}
class Log{
    private $handler = null;
    private $level = 15;
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}
    public static function Init($handler = null,$level = 15)	{
        if(!self::$instance instanceof self)		{
            self::$instance = new self();
            self::$instance->__setHandle($handler);
            self::$instance->__setLevel($level);
        }
        return self::$instance;
    }
    private function __setHandle($handler){
        $this->handler = $handler;
    }
    private function __setLevel($level)	{
        $this->level = $level;
    }
    public static function DEBUG($msg)	{
        self::$instance->write(1, $msg);
    }
    public static function WARN($msg)	{
        self::$instance->write(4, $msg);
    }
    public static function ERROR($msg)	{
        $debugInfo = debug_backtrace();
        $stack = "[";
        foreach($debugInfo as $key => $val){
            if(array_key_exists("file", $val)){
                $stack .= ",file:" . $val["file"];
            }
            if(array_key_exists("line", $val)){
                $stack .= ",line:" . $val["line"];
            }
            if(array_key_exists("function", $val)){
                $stack .= ",function:" . $val["function"];
            }
        }
        $stack .= "]";
        self::$instance->write(8, $stack . $msg);
    }
    public static function INFO($msg)	{
        self::$instance->write(2, $msg);
    }
    private function getLevelStr($level)	{
        switch ($level)		{
            case 1:
                return 'debug';
                break;
            case 2:
                return 'info';
                break;
            case 4:
                return 'warn';
                break;
            case 8:
                return 'error';
                break;
            default:
        }
    }
    protected function write($level,$msg)	{
        if(($level & $this->level) == $level )		{
            $msg = '['.date('Y-m-d H:i:s').']['.$this->getLevelStr($level).'] '.$msg."\n";
            $this->handler->write($msg);
        }
    }
}


function online_pay() {
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $order_id = isset($_POST['order_id']) ? trim($_POST['order_id']) : 0;
        $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
        if (!$order_id) {
            err('订单id不存在');
        }
        $order_model = new M();
        if($order_id[0] == 'P'){
            $order_info = $order_model->table('sgxt_order')->where("orderid='{$order_id}'")->find();
            $order_sn = $order_info['orderid'];
            $amount = $order_info['amount'];
            if (empty($order_info)) {
                err('订单不存在');
            }
            if($order_info['status']==1)
            {
                err('订单已支付');
            }
        }else
        {
            $order_info = $order_model->table('order')->where("order_sn='{$order_id}'")->find();
            $order_sn = $order_info['order_sn'];
            $amount = $order_info['order_amount'];
            if (empty($order_info)) {
                err('订单不存在');
            }
            if($order_info['status']>=20)
            {
                err('订单已支付');
            }
        }
        if(empty($amount)||$amount<0.0001)
        {
            err('金额错误');
        }
        /* 使用余额支付 */
        if (!$payment_id) {
            err('请选择支付方式');
        }
        if($payment_id == 16){
            $action = '/app/wapllpay/llpayapi.php';
            $edit_data = array(
                'payment_id' => $payment_id,
                'payment_code' => 'epaywapllpay',
                'payment_name' => '移动连连支付',
            );
        }else if($payment_id == 8){
            $action = '/app/wxpay/wxjs.php';
            $edit_data = array(
                'payment_id' => $payment_id,
                'payment_code' => 'epaywxjs',
                'payment_name' => '移动微信支付',
            );
        }else if($payment_id == 17){
            $paytype = 'reapal';
            $action = '/app/reapal/reapal.php';
            $edit_data = array(
                'payment_id' => $payment_id,
                'payment_code' => 'reapal',
                'payment_name' => '融宝快捷支付',
            );
        }else if($payment_id == 18){
            $paytype = 'allinpay';
            $action = '/app/allinpay/allinpay.php';
            $edit_data = array(
                'payment_id' => $payment_id,
                'payment_code' => 'allinpay',
                'payment_name' => '通联快捷支付',
            );
        }
        $order_model->table('order')->where("order_sn='{$order_id}'")->update($edit_data);
        echo '<html>'.
            '<body onLoad="javascript:document.WXNATIVE_FORM.submit()">'.
            '<form method="get" name="WXNATIVE_FORM" action="'.$action.'">'.
            '<input type="hidden" name="user_id" value="'.$user['user_id'].'">'.
            '<input type="hidden" name="user_name" value="'.$user['real_name'].'">'.
            '<input type="hidden" name="dingdan" value="'.$order_sn.'">'.
            '<input type="hidden" name="cz_money" value="'.$amount.'">'.
            '<input type="hidden" name="site_url" value="'.conf('SITE_URL').'">'.
            '</form>'.
            '</body></html>';
        exit;
    }else{
        err('身份错误，请重新登录');
    }
}
function buildCountRand ($number = 1,$length=10,$mode=1) {
    if($mode==1 && $length<strlen($number) ) {
        //不足以生成一定数量的不重复数字
        return false;
    }
    $rand   =  array();
    for($i=0; $i<$number; $i++) {
        $rand[] =   randString($length,$mode);
    }
    $unqiue = array_unique($rand);
    if(count($unqiue)==count($rand)) {
        return $rand;
    }
    $count   = count($rand)-count($unqiue);
    for($i=0; $i<$count*3; $i++) {
        $rand[] =   randString($length,$mode);
    }
    $rand = array_slice(array_unique ($rand),0,$number);
    return $rand;
}
function randString($len=6,$type='',$addChars='') {
    $str ='';
    switch($type) {
        case 0:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        case 1:
            $chars= str_repeat('0123456789',3);
            break;
        case 2:
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ'.$addChars;
            break;
        case 3:
            $chars='abcdefghijklmnopqrstuvwxyz'.$addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars='ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'.$addChars;
            break;
    }
    if($len>10 ) {//位数过长重复字符串一定次数
        $chars= $type==1? str_repeat($chars,$len) : str_repeat($chars,5);
    }
    if($type!=4) {
        $chars   =   str_shuffle($chars);
        $str     =   substr($chars,0,$len);
    }
    return $str;
}



class JSSDK {
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]".$_POST['url'];

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("jsapi_ticket.php"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("jsapi_ticket.php", json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    public function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("access_token.php"));
        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file("access_token.php", json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
}

function getSignPackge(){
    $jssdk = new JSSDK(conf('epay_wx_appid'), conf('epay_wx_secret'));
    $signPackage = $jssdk->GetSignPackage();
    fk('ok',$signPackage);
}

function checkStore(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        if($user['type'] != 5){
            err('只有县代才能操作，请重新登录');
        }
        $model = new M();
        $store_id = intval($_POST['store_id']);
        $state = intval($_POST['state']);
        $store = $model->table('store')->where('store_id='.$store_id)->find();
        if($store){
            $model->table('store')->where('store_id='.$store_id)->update(array('state'=>$state,'apply_time'=>time()));
            $str="驳回";
            $strstatus=3;
            if($state==1)
            {
                $model->table('member')->where('user_id='.$store_id)->update(array('type'=>2));
                $str="通过";
                $strstatus=2;
            }
            $model->table('sgxt_oplog')->insert(array(
                'obj_id' => $store_id,
                'obj_type' => 'user',
                'opid' => $user['user_id'],
                'info' => $user['user_name'] . $str.'了升级请求(id:'.$store_id.')',
                'createtime' => time()
            ));
            file_put_contents('text.txt',$model->getSql()."\n");
            $model->table('sgxt_req')->where('userid='.$store_id.' and type=2')->update(array('status'=>$strstatus,'updatetime'=>time()));
            file_put_contents('text.txt',$model->getSql()."\n",FILE_APPEND);
            fk('操作成功');
        }else{
            fk('记录不存在');
        }

    }else{
        err('身份错误，请重新登录');
    }
}

function showStore(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        if($user['type'] != 5){
            err('只有县代才能操作，请重新登录');
        }
        $model = new M();
        $store_id = intval($_POST['store_id']);
        $is_show = intval($_POST['is_show']);
        $store = $model->table('store')->where('store_id='.$store_id)->find();
        if($store){
            $model->table('store')->where('store_id='.$store_id)->update(array('is_show'=>$is_show));
            fk('操作成功');
        }else{
            fk('记录不存在');
        }

    }else{
        err('身份错误，请重新登录');
    }
}

function goodStore(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        if($user['type'] != 5){
            err('只有县代才能操作，请重新登录');
        }
        $model = new M();
        $store_id = intval($_POST['store_id']);
        $is_good = intval($_POST['is_good']);
        $store = $model->table('store')->where('store_id='.$store_id)->find();
        if($store){
            $model->table('store')->where('store_id='.$store_id)->update(array('is_good'=>$is_good));
            fk('操作成功');
        }else{
            fk('记录不存在');
        }

    }else{
        err('身份错误，请重新登录');
    }
}

function tradeStore(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        if($user['type'] != 5){
            err('只有县代才能操作，请重新登录');
        }
        $model = new M();
        $store_id = intval($_POST['store_id']);
        $is_trade = intval($_POST['is_trade']);
        $store = $model->table('store')->where('store_id='.$store_id)->find();
        if($store){
            $model->table('store')->where('store_id='.$store_id)->update(array('is_trade'=>$is_trade));
            fk('操作成功');
        }else{
            fk('记录不存在');
        }

    }else{
        err('身份错误，请重新登录');
    }
}

//审核列表
function checkStoreList(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $page =isset($_POST['page']) ? $_POST['page'] : '1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=((int)$page-1)*$pagecount;
    $user =checkToken($token);
    if($user){
        if($user['type'] != 5){
            err('只有县代才能查看商家');
        }
        if($_POST["starttime"]=="" && $_POST["endtime"]==""){
            $where = 'and 1=1';
        }else if($_POST["starttime"]!="" && $_POST["endtime"]==""){
            $where = 'and 1=1';
        }else if($_POST["starttime"]=="" && $_POST["endtime"]!=""){
            $where = 'and 1=1';
        }else if($_POST["starttime"]!="" && $_POST["endtime"]!=""){
            $where = 'and add_time >= '.strtotime($_POST["starttime"]).' and add_time <= '. strtotime($_POST["endtime"]).'';
        }
        $m =new M();
        $taba =DB_PREFIX.'store';
        $tabb =DB_PREFIX.'member';
        $count =$m->query("select count(*) as count from $taba join $tabb on $taba.store_id=$tabb.user_id where $tabb.area=".$user['ahentarea']." ".$where);
        $count =$count[0]['count'] ? $count[0]['count'] : '0';
        $totalpage =ceil($count/$pagecount);
        $list =$m->query("select $taba.region_name,$taba.address, $taba.store_id,$taba.is_trade,$taba.add_time,$taba.apply_time,$taba.store_name,$taba.image_2,$taba.is_show,$taba.state,$taba.is_good,$tabb.user_name,$tabb.province,$tabb.city,$tabb.area from $taba join $tabb on $taba.store_id=$tabb.user_id where $tabb.area=".$user['ahentarea']." ".$where." order by $taba.state asc,$taba.add_time desc limit $startcount,$pagecount");
        //print($m->getSql());die;
        foreach ($list as $k => $v) {
            $v['add_time'] =date('Y-m-d',$v['add_time']);
            $v['apply_time'] =date('Y-m-d',$v['apply_time']);
            if($v['image_2'] ==''){
                $v['image_2'] ='0';
            }
            if($v['province'] && $v['city'] && $v['area']) {
                $arealist = $m->table('sgxt_area')->where('id in (' . $v['province'] . ',' . $v['city'] . ',' . $v['area'] . ')')->select();
                $v['region_name'] = $arealist[0]['name'] . $arealist[1]['name'] . $arealist[2]['name'];
            }
            $list[$k] =$v;
        }
        pageJson('ok',"商家审核列表",$list,$totalpage);

    }else{
        err('身份错误，请重新登录');
    }
}

function getUserInfo(){
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if($user){
        $model = new M();
        $provice = $model->table('sgxt_area')->where('id='.$user['province'])->find();
        $city = $model->table('sgxt_area')->where('id='.$user['city'])->find();
        $area = $model->table('sgxt_area')->where('id='.$user['area'])->find();
        $user['province_cn'] = $provice['name'];
        $user['city_cn'] = $city['name'];
        $user['area_cn'] = $area['name'];
        fk('ok',$user);
        //pageJson('ok',"商家审核列表",$list,$totalpage);

    }else{
        err('身份错误，请重新登录');
    }
}
function getShopInfo(){
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if($user){
        $model = new M();
        $shop = $model->table('store')->where('store_id='.$user['user_id'])->find();
        $cate = $model->table('category_store')->where('store_id='.$shop['store_id'])->find();
        $shop['cate'] = $cate;
        $provice = $model->table('sgxt_area')->where('id='.$user['province'])->find();
        $city = $model->table('sgxt_area')->where('id='.$user['city'])->find();
        $area = $model->table('sgxt_area')->where('id='.$user['area'])->find();
        $shop['province_cn'] = $provice['name'];
        $shop['city_cn'] = $city['name'];
        $shop['area_cn'] = $area['name'];
        fk('ok',$shop);
        //pageJson('ok',"商家审核列表",$list,$totalpage);

    }else{
        err('身份错误，请重新登录');
    }
}

function storeupimage(){

}

function getOrderinfo($dingdan){
    $model = new M();
    //第一位为0 默认为积分订单
    if($dingdan[0] == 'P'){
        $orderinfo = $model->table('sgxt_order')->where(array('orderid'=>$dingdan))->find();
        if(empty($orderinfo)){
            err('订单不存在');
        }
        if($orderinfo['status']){
            err('订单已支付');
        }
        $order['userid'] = $orderinfo['userid'];
        $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find();
        $order['order_id'] = $orderinfo['order_id'];
        $order['user'] = $user;
        $order['user_mobile'] = $user['user_name'];
        $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
        $order['truename'] = $orderinfo['truename'];
        $order['cz_money'] = $orderinfo['amount'];
    }else{
        //商品订单支付
        $orderinfo = $model -> table('order')->where(array('order_sn'=>$dingdan))->find();
        if(empty($orderinfo)){
            err('订单不存在');
        }

        if($orderinfo['status'] != '11'){
            err('订单已支付');
        }
        $order['order_id'] = $orderinfo['order_id'];
        $order['userid'] = $orderinfo['buyer_id'];
        $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find();
        $order['user'] = $user;
        $order['user_mobile'] = $user['user_name'];
        $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
        $order['truename'] = $orderinfo['buyer_name'];
        $order['cz_money'] = $orderinfo['order_amount'];
    }
    return $order;
}

function llpaySign(){

    require_once ("../app/webllpay/llpay.config.php");
    require_once ("../app/webllpay/lib/llpay_submit.class.php");


    $token = urlencode($_POST['token']);
    $user =checkToken($token);

    if($user){

        //商户订单号
        $no_order = $_POST['order_sn'];
        //商户网站订单系统中唯一订单号，必填
        $model = new M();
        $order = array();
        //获取订单信息
        $order = getOrderinfo($no_order);
        //商户用户唯一编号
        $user_id = $order['userid'];
        //支付类型
        $busi_partner = '109001';
        //付款金额
        $money_order = $order['cz_money'];
        //必填
        //商品名称
        $name_goods = '';
        //订单地址
        $url_order = '';
        //订单描述
        $info_order = '连连支付APP交易';
        //银行网银编码
        $bank_code = '';
        //支付方式
        $pay_type = '';
        //卡号
        $card_no = '';
        //姓名
        $acct_name = $order['truename'];
        //身份证号
        $id_no = '';
        //协议号
        $no_agree = '';
        //修改标记
        $flag_modify = 0;
        $orderGood = $model->table('order_goods')->where(array('order_id'=>$order['order_id']))->find();
        if($orderGood){
            $name_goods = $orderGood['goods_name'];
        }
        if($no_order[0] == 'P'){
            $name_goods = '啥都行商城';
        }
        $orderExtm = $model->table('order_extm')->where('order_id='.$order['order_id'])->find();
        $region = $orderExtm['region_id'];
        if($orderExtm['region_id'] == 2){
            $pid = $order['user']['province'];
            $cid = $order['user']['city'];
            $aid = $order['user']['area'];
        }else{
            $pid = substr($region,0,2).'0000';
            $cid = substr($region,0,4).'00';
            $aid = $region;
        }
        $data = array(
            'frms_ware_category'=> '4001',
            'logistics_mode' => '2',
            'user_info_mercht_userno' => $order['userid'],
            'user_info_bind_phone' => $order['user_mobile'],
            'user_info_dt_register' => $order['user_reg_time'],
            'delivery_addr_province' => $pid,
            'delivery_addr_city' => $cid,
            'delivery_phone' => $orderExtm['phone_mob'] ? $orderExtm['phone_mob'] : $orderExtm['phone_tel'],
            'delivery_cycle' => '24h'
        );
        //风险控制参数
        $risk_item = json_encode($data);
        //分账信息数据
        $shareing_data = '';
        //返回修改信息地址
        $back_url = $_POST['back_url'];
        //订单有效期
        $valid_order = $_POST['valid_order'];
        //服务器异步通知页面路径
        $notify_url = conf('SITE_URL')."/app/webllpay/notify_url.php";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        //页面跳转同步通知页面路径
        $return_url = conf('SITE_URL')."/app/webllpay/return_url.php";
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
        /************************************************************/
        date_default_timezone_set('PRC');
        //构造要请求的参数数组，无需改动
        $parameter = array (
            //"version" => trim($llpay_config['version']),
            "oid_partner" => trim($llpay_config['oid_partner']),
            "sign_type" => trim($llpay_config['sign_type']),
            //"userreq_ip" => trim($llpay_config['userreq_ip']),
            //"id_type" => trim($llpay_config['id_type']),
            "valid_order" => trim($llpay_config['valid_order']),
            //"user_id" => $user_id,
            //"timestamp" => local_date('YmdHis', time()),
            "busi_partner" => $busi_partner,
            "no_order" => $no_order,
            "dt_order" => local_date('YmdHis', time()),
            "name_goods" => $name_goods,
            "info_order" => $info_order,
            "money_order" => $money_order,
            "notify_url" => $notify_url,
            //"url_return" => $return_url,
            //"url_order" => $url_order,
            //"bank_code" => $bank_code,
            //"pay_type" => $pay_type,
            //"no_agree" => $no_agree,
            //"shareing_data" => $shareing_data,
            "risk_item" => $risk_item,
            //"id_no" => $id_no,
            //"acct_name" => $acct_name,
            //"flag_modify" => $flag_modify,
            //"card_no" => $card_no,
            //"back_url" => $back_url
        );
        //print_r($parameter);exit;
        $llpaySubmit = new LLpaySubmit($llpay_config);
        $arr = $llpaySubmit->buildRequestPara($parameter);
        fk('ok',$arr);
        //fk('ok',array('aaa'=>'bbb'));
        //echo json_encode(array('aaa'=>'bbb'));exit;
    }else{
        err('身份错误，请重新登录');
    }
}
//按月份统计会员奖励
function userMonthBalance(){
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if($user){
        $model = new M();
        $list = $model->query('select user_id,sum(get_money),times,is_clearing from ecm_sgxt_balance where user_id='.$user['user_id'].' and source_type=1 group by times order by times desc');
        foreach($list as $key=>$val){
            if($val['is_clearing'] == 1){
                $val['is_clearing_cn'] = '已转化';
            }else if($val['is_clearing'] == 0){
                $val['is_clearing_cn'] = '冻结中';
            }
            $list[$key] = $val;
        }
        fk('ok',$list);
        //pageJson('ok',"商家审核列表",$list,$totalpage);

    }else{
        err('身份错误，请重新登录');
    }
}

function bind_bank(){
    $model = new M();
    $bank_name = $_POST['bank_name'];
    $bank_num = $_POST['bank_num'];
    $real_name = $_POST['owner'];
    $idcard_num = $_POST['cert_no'];
    $mobile = $_POST['phone'];
    $ordersn = $_POST['order_no'];
    $bank_type = $_POST['bank_type'];
    $end_time = $_POST['validate'];
    $bind_id = $_POST['bind_id'];
    $cvv2 = $_POST['cvv2'];
    if($ordersn[0] == 'P'){
        $order = $model->table('sgxt_order')->where(array('orderid'=>$ordersn))->find();
        $user_id = $order['userid'];
    }else{
        $order = $model->table('order')->where(array('order_sn'=>$ordersn))->find();
        $user_id = $order['buyer_id'];
    }
    $data['user_id'] = $user_id;
    $data['bank_num'] = $bank_num;
    $data['bank_name'] = $bank_name;
    $data['real_name'] = $real_name;
    $data['idcard_num'] = $idcard_num;
    $data['mobile'] = $mobile;
    $data['type'] = $bank_type;
    $data['end_time'] = $end_time;
    $data['cvv2'] = $cvv2;
    $data['status'] = 1;
    $data['is_default'] = 1;
    $data['bind_id'] = $bind_id;
    $data['add_time'] = time();
    $bank = $model->table('member_bind_bank')->where(array('user_id'=>$user_id,'bank_num'=>$bank_num))->find();
    if(!empty($bank))
    {
        $model->table('member_bind_bank')->where('user_id='.$user_id)->update(array('is_default'=>2));
    }
    $model->table('member_bind_bank')->insert($data);
}

function canclebindcard(){
    file_put_contents('cancle.txt','testcanclebindcard');
    $user_id = $_POST['user_id'];
    $bind_id = $_POST['bind_id'];
    $model = new M();
    $rs = $model->table('member_bind_bank')->where(array('user_id'=>$user_id,'bind_id'=>$bind_id))->update(array('status'=>2));
    file_put_contents('sqlcancle.txt',$model->getSql().';'.$rs."\r\n",FILE_APPEND);
}