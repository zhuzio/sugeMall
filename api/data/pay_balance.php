<?php
/*
**提现
*/
// 商家货款数据显示
function shopDepositShow(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user['type']=2){
        $m =new M();
        $money['allMoney'] =$m->table('epay')->where('user_id='.$user['user_id'])->getField('balance');//货款
        $money['debtMoney'] =$m->table('member')->where('user_id='.$user['user_id'])->geiField('pay_point');//欠款积分
        $money['debtMoney'] =$money['debtMoney']<0 ? $money['debtMoney'] : 0;
        // $money['debtMoney'] =$money['debtMoney']*0.3;
        $money['maxMoney'] =$money['allMoney'] + $money['debtMoney']; //最高可提现
        // 线上
        $sql ="select sum(order_amount) as onlinemoney from ecm_order where seller_id=".$user['user_id']." and status =40";
        $onlinemoney =$m->query($sql);
        // var_dump($onlinemoney);die;
        $onlinemoney =$onlinemoney[0]['onlinemoney'] ? $onlinemoney[0]['onlinemoney'] :'0';
        //线下
        $sql ="select sum(order_amount) as offlinemoney from ecm_order_offline where seller_id=".$user['user_id']." and status =40 and payment_id=3";
        $offlinemoney =$m->query($sql);
        $offlinemoney =$offlinemoney[0]['offlinemoney'] ? $offlinemoney[0]['offlinemoney'] :'0';
        //累计货款
        $money['totalmoney'] =$offlinemoney + $onlinemoney;
        //输出默认银行卡
        $taba = DB_PREFIX."epay_bank";
        $tabb = DB_PREFIX."sgxt_bank";
        $sql ="select $taba.*,$tabb.bank_logo from $taba join $tabb on $taba.bank_name = $tabb.bank_name where user_id=".$user['user_id']." and $taba.status=0";
        $card=$m->query($sql);
        $money['defaultCard'] =$card[0];
        unset($money['debtMoney']);
        unset($money['allMoney']);
        fk('success',$money);
	
	}else{
		err('身份错误,请重新登录');
	}

}

//商家提现操作	
function shopDeposit(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user){
		if($user['type']!=2) err('非商户不能货款提现');
		// 金额 密码
		$money =$_REQUEST['money'];
		$pwd =md5(trim($_REQUEST['pay_password']));
		if(preg_match("/[^0.-9]/",$money)) err('您输入的不是数字');
		$m =new M();
		$defaultCard =$m->table('epay_bank')->where(array('user_id'=>$user['user_id'],'status'=>0))->find();
		if(empty($defaultCard)) err('请选择默认银行卡');
		$payPoint = $m->table('member')->where(array('user_id' => $user['user_id']))->getField('pay_point');
        $debtMoney = $payPoint<0?$payPoint:0;
        // $debtMoney = $debtMoney*0.3;
        $balance =$m->table('epay')->field('balance,zf_pass')->where('user_id='.$user['user_id'])->find();
        $userMoney =$balance['balance'];
        $zfPass =$balance['zf_pass'];
        $maxMoney =$userMoney + $debtMoney;
        if($pwd != $zfPass) err('支付密码错误');
        if($money > $maxMoney) err('余额不足');
        if($money < 200) err('提现金额不能小于200');
        if(floor($money)!=$money)
        {
            err('提现金额必须是整数');
        }
        if($money%100!=0)
        {
            err('提现金额必须是100的倍数');
        }
        //欠款归零
        if($payPoint < 0){
            $upuser = $m->table('member')->where(array('user_id' =>$user['user_id']))->update(array('pay_point' => 0));
        }
        
        //获取数组中balance的值
        $earnings =$m->table('epay')->where('user_id='.$user['user_id'])->getField('balance');
        $newbalance =$earnings-$money;
        $sql ="update ecm_epay set balance=$newbalance where user_id=".$user['user_id'];
        $hkMoney =$m->query($sql);
        //拿到货款
        if(!$hkMoney) err('提现申请失败,请重试');
        //提现记录
        $deposit = array(
                'userid'  =>  $user['user_id'],
                'truename' => $user['real_name'],
                'mobile'   => $user['user_name'],
                'money'    => $money,
				'real_money'    => $money,
                'createtime' => time(),
                'type'     => 1,
                'bank_name' =>$defaultCard['bank_name'],
                'open_bank' =>$defaultCard['open_bank'],
                'bank_code' =>$defaultCard['bank_num'],
                'bank_num'  =>$defaultCard['bank_code'],
                'bank_user_name' =>$defaultCard['account_name'],
                 );
       $addmoney =$m->table('sgxt_deposit')->insert($deposit); 
       if(!$addmoney) err('提现申请失败,请重试1');    
       //添加提现日志
       $pay =new paymentlogModel();
       $res =$pay->paymentlog($user['user_id'] , $user['real_name'],$money , '8');
       fk('提现申请已提交');

	}else{
		err('身份错误,请重新登录');
	}
}


//用户收益数据显示
function userDepositShow(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user){
		$m =new M();
	 	$money['allMoney'] =$m->table('epay')->where(array('user_id' => $user['user_id']))->getField('earnings');
        $money['maxMoney'] =floor($money['allMoney']);
        //累计收益提现
        /*$sql ="select sum(money) as totalmoney from ecm_sgxt_deposit where userid=".$user['user_id']." and type =2 and ispay=1";
        $totalmoney =$m->query($sql);
        $totalmoney =$totalmoney[0]['totalmoney'];*/
         //输出默认银行卡
        $taba = DB_PREFIX."epay_bank";
        $tabb = DB_PREFIX."sgxt_bank";
        $sql ="select $taba.*,$tabb.bank_logo from $taba join $tabb on $taba.bank_name = $tabb.bank_name where user_id=".$user['user_id']." and $taba.status=0";
        $card=$m->query($sql);
        $money['defaultCard'] =$card[0];
        unset($money['allMoney']);
        // $money['totalmoney'] =$totalmoney;
        fk('success',$money);

	}else{
		err('身份错误,请重新登录');
	}

}


//用户收益提现操作
function userDeposit(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user){
		if($user['type'] <2) err('普通用户不能提现');
		$money =$_REQUEST['money'];
		$pwd =md5(trim($_REQUEST['pay_password']));
		if(preg_match("/[^0.-9]/",$money)) err('您输入的不是数字');
		$m =new M();
		$defaultCard =$m->table('epay_bank')->where(array('user_id'=>$user['user_id'],'status'=>0))->find();
		if(empty($defaultCard)) err('请选择默认银行卡');
        $balance =$m->table('epay')->field('earnings,zf_pass')->where('user_id='.$user['user_id'])->find();
        $userMoney =$balance['earnings'];
        $zfPass =$balance['zf_pass'];
        $maxMoney =$userMoney;
        if($pwd != $zfPass) err('支付密码错误');
        if($money > $maxMoney) err('余额不足');
        if($money <200) err('提现金额不能小于200');
        if(floor($money)!=$money)
        {
            err('提现金额必须是整数');
        }
        if($money%100!=0)
        {
            err('提现金额必须是100的倍数');
        }
        //获取数组中earnings的值
        $earnings =$m->table('epay')->where('user_id='.$user['user_id'])->getField('earnings');
        $newbalance =$earnings-$money;
        $sql ="update ecm_epay set earnings =$newbalance where user_id=".$user['user_id'];
        $hkMoney =$m->query($sql);
        //拿到货款
        if(!$hkMoney) err('提现申请失败,请重试');
        //提现记录
        $deposit = array(
                'userid'  =>  $user['user_id'],
                'truename' => $user['real_name'],
                'mobile'   => $user['user_name'],
                'money'    => $money,
				'real_money'    => $money,
                'createtime' => time(),
                'type'     => 2,
                'bank_name' =>$defaultCard['bank_name'],
                'open_bank' =>$defaultCard['open_bank'],
                'bank_code' =>$defaultCard['bank_num'],
                'bank_num'  =>$defaultCard['bank_code'],
                'bank_user_name' =>$defaultCard['account_name'],
                 );
       $addmoney =$m->table('sgxt_deposit')->insert($deposit); 
       if(!$addmoney) err('提现申请失败,请重试1');
       //添加提现日志
       $pay =new paymentlogModel();
       $res =$pay->paymentlog($user['user_id'] , $user['real_name'],$money , '7');
       fk('提现申请已提交');

	}else{
        err('请重新登录');
	}
}

//货款提现明细
function shopDepositList(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token']))	err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount =10;
	$startcount =((int)$page-1)*10;
	$user =checkToken($token);
	if($user['type']=2){
		$m =new M();
		// 分页
		$sql ="select count(userid) as uid from ecm_sgxt_deposit where userid=".$user['user_id']." and type =1";
        $count =$m->query($sql);
        $count =$count[0]['uid']; 
      	$totalpage =ceil($count/$pagecount);
		$hkList =$m->table('sgxt_deposit')->field('money,createtime,bank_code,operatortime,ispay')->where(array('userid'=>$user['user_id'],'type'=>1))->order('deid desc')->limit("$startcount,$pagecount")->select();
		foreach($hkList as $k=>$val){
			$val['createtime'] =date('Y-m-d H:i:s',$val['createtime']);
			$val['operatortime'] =date('Y-m-d H:i:s',$val['operatortime']);
			/*$a1 =substr($val['bank_code'],0,4);
			$a2 =substr($val['bank_code'],-4,4);
			$a3 =$a1.'********'.$a2;
			$val['bank_code'] =$a3;*/
			$hkList[$k] =$val;
		} 
		pageJson('ok',"货款提现明细",$hkList,$totalpage);
	
	}else{
		err('身份错误,请重新登录');
	}

}

//用户提现明细
function userDepositList(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token']))	err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount =10;
	$startcount =((int)$page-1)*10;
	$user =checkToken($token);
	if($user['type']>1){
		$m =new M();
		// 分页
		$sql ="select count(userid) as uid from ecm_sgxt_deposit where userid=".$user['user_id']." and type =2";
        $count =$m->query($sql);
        $count =$count[0]['uid']; 
      	$totalpage =ceil($count/$pagecount);
		$syList =$m->table('sgxt_deposit')->field('money,createtime,bank_code,operatortime,ispay')->where(array('userid'=>$user['user_id'],'type'=>2))->order('deid desc')->limit("$startcount,$pagecount")->select();
		foreach ($syList as $key => $v) {
			$v['createtime'] =date('Y-m-d H:i:s',$v['createtime']);
			$v['operatortime'] =date('Y-m-d H:i:s',$v['operatortime']);
			/*$a1 =substr($v['bank_code'],0,4);
			$a2 =substr($v['bank_code'],-4,4);
			$a3 =$a1.'********'.$a2;
			$v['bank_code'] =$a3;*/
			$syList[$key] =$v;
		}
        pageJson('ok',"收益提现明细",$syList,$totalpage);
		
	}else{
		err('身份错误,请重新登录');
	}
}

//货款  收入
/*function income(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =10;
    $startcount =((int)$page-1)*10;
    $user =checkToken($token);
    if($user){
        if($user['type'] != 2) err('您不是商户');
        $m =new M();
        $sql ="select count(userid) as uid from ecm_sgxt_deposit where userid=".$user['user_id']." and type =1 and ispay =1";
        $count =$m->query($sql);
        $count =$count[0]['uid'] ? $count[0]['uid'] : '0';
        $totalpage =ceil($count/$pagecount);
        $hkIncome =$m->table('sgxt_deposit')->field('money,createtime')->where(array('userid'=>$user['user_id'],'type'=>1,'ispay'=>'1'))->order('deid desc')->limit("$startcount,$pagecount")->select();
        $hkIncome =$hkIncome ? $hkIncome : '0';
        foreach ($hkIncome as $k => $v) {
            $v['createtime'] =date('Y-m-d H:i:s',$v['createtime']);
            $v['money'] ='+ '.$v['money'];
            $v['info'] ='货款积分到账';
            $hkIncome[$k] =$v;
        }

        pageJson('收入','ok',$hkIncome,$totalpage);
    }else{
        err('身份错误,请重新登录');
    }
}*/

//商家货款  收入 
function income(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =10;
    $startcount =((int)$page-1)*10;
    $user =checkToken($token);
    if($user){
        if($user['type'] !=2)err('您不是商户!');
        $m= new M();
        //统计总条数
        $count =$m->table('order_offline')->where(array('seller_id'=>$user['user_id'],'status'=>40))->count();
        $totalpage =ceil($count/$pagecount);
        $hkIncome=$m->table('order_offline')->field('order_id,buyer_name,status,pay_time,order_amount,is_check')->where(array('payment_id'=>3,'seller_id'=>$user['user_id'],'status'=>40,'is_check'=>1))->order('order_id desc')->limit("$startcount,$pagecount")->select();
        foreach ($hkIncome as $k => $v) {
            $v['pay_time'] =date('Y-m-d H:i:s',$v['pay_time']);
            $v['money'] ='+ '.$v['order_amount'];
            $v['info'] ='货款积分到账';
            $hkIncome[$k]=$v;

        }

        pageJson('ok','收入',$hkIncome,$totalpage);

        
    } 
}


//货款  支出
function outcome(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =10;
    $startcount =((int)$page-1)*10;
    $user =checkToken($token); 
    if($user){
        if($user['type'] != 2) err('您不是商户');
        $tab =DB_PREFIX.'sgxt_order';
        $m =new M();
        $count =$m->query("select count(*) as count from $tab where userid=".$user['user_id']." and status=1");
        $count =$count[0]['count'] ? $count[0]['count'] : '0';
        $totalpage =ceil($count/$pagecount);
        $outcome =$m->query("select $tab.amount,$tab.num,$tab.price,$tab.paytype,$tab.pay_createtime from $tab where userid=".$user['user_id']." and status=1 order by $tab.pay_createtime desc limit $startcount,$pagecount");
        $outcome =$outcome ? $outcome : '0';
        foreach ($outcome as $key => $value) {
            $value['pay_createtime'] =date('Y-m-d H:i:s',$value['pay_createtime']);
            $outcome[$key] =$value;
        } 
        pageJson('ok','支出',$outcome,$totalpage);
    }else{
        err('身份错误,请重新登录');
    } 
}

//获取用户银行卡列表
function bank_list(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    if(!isset($_REQUEST["bank_id"]))err("请输入银行卡id!");
    $user =checkToken($token);
    $bank_id =$_REQUEST['bank_id'];  
    if($user){
        $m =new M();
        $bank =$m->table('epay_bank')->where(array('user_id'=>$user['user_id'],'bank_id'=>$bank_id))->find();
        if($bank){
            fk('银行卡信息',$bank);
        }else{
            err('该银行卡不存在');
        }

    } 
}



?>