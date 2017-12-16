<?php
/*
**个人订单信息  线上
*/
function persononline(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	//if(!isset($_POST["status"]))err("请确认订单状态");
	 if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	if($_POST["status"]==""){
		$where = 'ecm_order.status >= 0';
	}else{
		$where= 'ecm_order.status= '.$_POST["status"].'';
	}
	$pagecount= 5;
	$startpage=((int)$page-1)*5;
	$user = checkToken($token);
	if($user){
		$model=new M();
		//$user['user_id']='5539';
		$count=$model
			->query('select count(ecm_order.order_id) as id from ecm_order where ecm_order.buyer_id = '.$user['user_id'].' and '.$where.'');
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->table('order')
		->query('select order_sn,invoice_no,ecm_order.payment_name,ecm_order.payment_code,ecm_order.order_id,ecm_order_extm.shipping_name,ecm_order_extm.shipping_fee,order_amount,ecm_member.real_name,ecm_order.seller_id,ecm_order.status,seller_name from ecm_order inner join ecm_member on ecm_order.buyer_id=ecm_member.user_id inner join ecm_order_extm on ecm_order.order_id = ecm_order_extm.order_id where ecm_order.buyer_id = '.$user['user_id'].' and '.$where.' order by ecm_order.add_time desc limit '.$startpage.','.$pagecount.'');	
		
            foreach ($data as $b=> $val){
				$data[$b]['goods']=$model
						->query('select order_id,goods_id,specification,quantity,goods_name,price,goods_image from ecm_order_goods where order_id = '.$val['order_id'].'');
						$refund_status_arr=conf('refund_status');
						$refund_status_info="";
						if($_POST["status"]=="40"||$_POST["status"]=="")
						{
							foreach($data[$b]['goods'] as $rekey => $revalue)
							{
								$refund=$model->table('refund')->where(array('order_id'=>$revalue['order_id'],'goods_id'=>$revalue['goods_id']))->find();
								if(!empty($refund))
								{
									$refund_status_info=$refund['status'];
								}
								$revalue['refund_status']=$refund_status_arr[$refund_status_info];
								$data[$b]['goods'][$rekey]=$revalue;
							}
						}
                }
		if($data){
			pageJson('ok',"订单信息",$data,$totalpage);
		}else{
			pageJson('ok',"订单信息",$data,$totalpage);
		}
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**个人订单信息  线下
*/
function personoffline(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	//if(!isset($_POST["status"]))err("请确认订单状态");
	if($_POST["type"]=="goods"){
		$where = '1=1';
		if($_POST["classname"]==""){
			$where = '1=1';
		}else{
			$where = 'ecm_order_offline.classname like "%'.$_POST['classname'].'%"';
		}
	}else if($_POST["type"]=="store"){
		$where = '1=1';
		if($_POST["classname"]==""){
			$where = '1=1';
		}else{
			$where = 'ecm_store.store_name like "%'.$_POST['classname'].'%"';
		}
	}else{
		$where = '1=1';
		}
	
	 if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	$user = checkToken($token);
	if($user){
		$model=new M();
		//$user['user_id']='316';
		$count=$model
			->field('count(ecm_order_offline.order_id) as id')
			->query('select count(ecm_order_offline.order_id) as id from ecm_order_offline ecm_order_offline  inner join ecm_store on ecm_order_offline.seller_id=ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id = ecm_member.user_id where ecm_order_offline.buyer_id = '.$user['user_id'].' and '.$where.'');
		//,'ecm_order_offline.status'=>$_POST["status"]
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->query('select order_sn,ecm_order_offline.point,ecm_order_offline.is_check,goods_amount,pay_time,classname,ecm_order_offline.order_id,tel,ecm_member.real_name,ecm_order_offline.status,seller_name from ecm_order_offline   inner join ecm_store on ecm_order_offline.seller_id=ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id = ecm_member.user_id where ecm_order_offline.buyer_id = '.$user['user_id'].' and '.$where.' order by ecm_order_offline.order_id  desc limit '.$startpage.','.$pagecount.'');
		//$data['totalpage']=$totalpage;ecm_order_offline.status = '.$_POST["status"].'
		//echo $model->getsql();die;
		if($data){
			pageJson('ok',"订单信息",$data,$totalpage);
		}else{
			pageJson('ok',"订单信息",$data,$totalpage);
		}
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**商家订单 线上 按类型返回商家订单
*/
function storeorderonline(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	//if(!isset($_POST["status"]))err("请确认订单状态");
if($_POST["status"]==""){
		$where = 'ecm_order.status != 0';
	}else{
		$where= 'ecm_order.status= '.$_POST["status"].'';
	}
	 if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	$user = checkToken($token);
	if($user){
		$model=new M();
		//$user['user_id']='5539';
		$count=$model
			->query('select count(ecm_order.order_id) as id from ecm_order  inner join ecm_member on ecm_order.buyer_id=ecm_member.user_id where ecm_order.seller_id = '.$user['user_id'].' and '.$where.'');
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->query('select order_sn,ecm_order.order_id,ecm_member.real_name,ecm_order.status,seller_name from ecm_order inner join ecm_member on ecm_order.buyer_id=ecm_member.user_id where ecm_order.seller_id = '.$user['user_id'].' and '.$where.' limit '.$startpage.','.$pagecount.'');
		foreach ($data as $b=> $val){
				$data[$b]['goods']=$model
						->query('select order_id,goods_id,goods_name,quantity,price,goods_image,specification from ecm_order_goods where order_id = '.$val['order_id'].'');
						$refund_status_arr=conf('refund_status');
						$refund_status_info="";
						if($_POST["status"]=="40"||$_POST["status"]=="")
						{
							foreach($data[$b]['goods'] as $rekey => $revalue)
							{
								$refund=$model->table('refund')->where(array('order_id'=>$revalue['order_id'],'goods_id'=>$revalue['goods_id']))->find();
								if(!empty($refund))
								{
									$refund_status_info=$refund['status'];
								}
								$revalue['refund_status']=$refund_status_arr[$refund_status_info];
								$data[$b]['goods'][$rekey]=$revalue;
							}
						}
                }
		if($data){
			pageJson('ok',"订单信息",$data,$totalpage);
		}else{
			pageJson('ok',"订单信息",$data,$totalpage);
		}
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**商家订单 线下 按类型返回商家订单
*/
function storeorderoffline(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	//if(!isset($_POST["status"]))err("请确认订单状态");
	 if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	$user = checkToken($token);
	if($user){
		$model=new M();
		//$user['user_id']='316';
		$count=$model
			->field('count(ecm_order_offline.order_id) as id')
			->table('order_offline  join ecm_store on ecm_order_offline.seller_id = ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id=ecm_member.user_id')
			->where(array('ecm_order_offline.seller_id'=>$user['user_id']))
			->select();
		$count=$count[0]['id'];
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->query('select order_sn,pay_time,ecm_order_offline.point,ecm_order_offline.is_check,ecm_order_offline.order_id,ecm_member.phone_mob,ecm_order_offline.status,seller_name,classname,goods_amount,payment_name from ecm_order_offline  inner join ecm_store on ecm_order_offline.seller_id=ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id=ecm_member.user_id where ecm_order_offline.seller_id = '.$user['user_id'].'  order by ecm_order_offline.pay_time desc limit '.$startpage.','.$pagecount.'');
		//$data['totalpage']=$totalpage;
		//echo  $model->getsql();die;
		if($data){
			pageJson('ok',"订单信息",$data,$totalpage);
		}else{
			pageJson('ok',"订单信息",$data,$totalpage);
		}
	}else{
		err('身份错误，请重新登录');
	}
}

/*
**发积分明细(按月份总计)
*/
function pointsendmonth(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	$user = checkToken($token);
	$model= new M();	
	if($user){
		$newpoint=$model->query('select pay_point from ecm_member where user_id = '.$user['user_id'].'');
		$summoney=$model->query('select sum(shops_point) as money from ecm_sgxt_get_point where sendid = '.$user['user_id'].' and is_pass in (0,1)');
		$summoney=$summoney[0]['money'];
		$newpoint=$newpoint[0]['pay_point'];
		$totalmoney= $model->query('select sum(amount) as money  from ecm_sgxt_order where userid = '.$user['user_id'].' and status=1');
		$totalmoney=$totalmoney[0]['money'];
		if(empty($summoney)){
			$summoney='0.00';
		}
		if(empty($newpoint)){
			$newpoint='0.00';
		}
		if(empty($totalmoney)){
			$totalmoney='0.00';
		}
		$data=$model->query('select FROM_UNIXTIME(createtime,"%Y-%m") months, sum(shops_point) as point from ecm_sgxt_get_point inner join ecm_member on ecm_sgxt_get_point.getid = ecm_member.user_id inner join ecm_order_offline on ecm_sgxt_get_point.order_id = ecm_order_offline.order_id where  is_pass in (0,1) and sendid = '.$user['user_id'].' group by months order by createtime desc');
		$totalpage=0;
		outJsonpoint('ok','发积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**发积分明细(每个月的明细)
*/
function pointsendmonthlist(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	if(!isset($_POST["month"]))err("请选择要看的月份");
	$user = checkToken($token);
	$model= new M();	
	if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	if($user){
		$firstday = date('Y-m-01',strtotime($_POST["month"]));
		$lastday = date('Y-m-d',strtotime("$firstday +1 month "));				
		$where = ' createtime>= '.strtotime($firstday).' and createtime <= '.strtotime($lastday).' and';
		$count=$model
			->query('select count(id) as id from ecm_sgxt_get_point inner join ecm_member on ecm_sgxt_get_point.getid = ecm_member.user_id inner join ecm_order_offline on ecm_sgxt_get_point.order_id = ecm_order_offline.order_id where '.$where.' ecm_sgxt_get_point.is_pass in (0,1) and ecm_sgxt_get_point.sendid = '.$user['user_id'].'' );
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		$data=$model
			->query('select shops_point,system_point,createtime,order_sn,ecm_member.user_name,ecm_member.real_name,ecm_sgxt_get_point.is_pass from ecm_sgxt_get_point inner join ecm_member on ecm_sgxt_get_point.getid = ecm_member.user_id inner join ecm_order_offline on ecm_sgxt_get_point.order_id = ecm_order_offline.order_id where '.$where.' ecm_sgxt_get_point.sendid = '.$user['user_id'].' and ecm_sgxt_get_point.is_pass in (0,1) order by createtime desc limit '.$startpage.','.$pagecount.' ');
			foreach ($data as $k => $v) {
				$v['createtime'] =date('Y-m-d H:i:s',$v['createtime']);
				$data[$k]=$v;
			}
outJsonpoint('ok','发积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}
}




/*
**发积分 发积分明细
*/
function shopSendPointList(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	$user = checkToken($token);
	$model= new M();
	if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	if($user){
		
		//$user['user_id']='13037';
		//$newpoint=$model->query('select money from ecm_epay where user_id = '.$user['user_id'].'');
		$newpoint=$model->query('select pay_point from ecm_member where user_id = '.$user['user_id'].'');
		//echo $model->getsql();die;
		$summoney=$model->query('select sum(shops_point) as money from ecm_sgxt_get_point where sendid = '.$user['user_id'].' and is_pass in (0,1)');
		$summoney=$summoney[0]['money'];
		$newpoint=$newpoint[0]['pay_point'];
		$totalmoney= (int)$summoney+(int)$newpoint;
		$count=$model
			->query('select count(id) as id from ecm_sgxt_get_point inner join ecm_member on ecm_sgxt_get_point.getid = ecm_member.user_id inner join ecm_order_offline on ecm_sgxt_get_point.order_id = ecm_order_offline.order_id where ecm_sgxt_get_point.is_pass in (0,1) and ecm_sgxt_get_point.sendid = '.$user['user_id']);
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		$data=$model
			->query('select shops_point,system_point,createtime,order_sn,ecm_member.user_name,ecm_member.real_name,ecm_sgxt_get_point.is_pass from ecm_sgxt_get_point inner join ecm_member on ecm_sgxt_get_point.getid = ecm_member.user_id inner join ecm_order_offline on ecm_sgxt_get_point.order_id = ecm_order_offline.order_id where ecm_sgxt_get_point.is_pass in (0,1) and ecm_sgxt_get_point.sendid = '.$user['user_id'].' order by createtime desc limit '.$startpage.','.$pagecount.' ');
			//echo $model->getsql();die;
			if(empty($summoney)){
				$summoney='0.00';
			}
			if(empty($newpoint)){
				$newpoint='0.00';
			}
			if(empty($totalmoney)){
				$totalmoney='0.00';
			}
			outJsonpoint('ok','发积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}
}

/*
**购积分明细 （按月份）
*/
function shopPointmonth(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	$user = checkToken($token);
	$model= new M();	
	if($user){
		$summoney=$model->query('select sum(shops_point) as money from ecm_sgxt_get_point where sendid = '.$user['user_id'].' and is_pass in (0,1)');
		
		//发积分总和
		$summoney=$summoney[0]['money'];
		//当前积分
		$newpoint=$model->table('member')->where(array('user_id'=>$user['user_id']))->getField('pay_point');
		//print_r($model->getsql());die;
		//累计积分
		$totalmoney= $model->query('select sum(amount) as money  from ecm_sgxt_order where userid = '.$user['user_id'].' and status=1');
		$totalmoney=$totalmoney[0]['money'];
		if(empty($totalmoney)){
			$totalmoney='0.00';
		}
		if(empty($newpoint)){
			$newpoint='0.00';
		}
		if(empty($summoney)){
			$summoney='0.00';
		}
		$data=$model->query('select FROM_UNIXTIME(createtime,"%Y-%m") months, sum(amount) as point from ecm_sgxt_order where userid = '.$user['user_id'].'  and status=1 group by months order by createtime desc');
		
		$totalpage=0;
		outJsonpoint('ok','购积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**购积分明细（每个月的）
*/
function shopPointmonthlist(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	if(!isset($_POST["month"]))err("请选择要看的月份");
	$user = checkToken($token);
	$model= new M();	
	if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	if($user){
		$firstday = date('Y-m-01',strtotime($_POST["month"]));
		$lastday = date('Y-m-d',strtotime("$firstday +1 month "));				
		$where = ' createtime>= '.strtotime($firstday).' and createtime <= '.strtotime($lastday).' and';

		$count=$model
			->query('select count(id) as id from ecm_sgxt_order where '.$where.' userid = '.$user['user_id'].'  and status=1');
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		$data=$model
			->query('select orderid,paytype,num,amount,price,pay_createtime from ecm_sgxt_order where '.$where.' userid = '.$user['user_id'].' and status=1 order by createtime desc  limit '.$startpage.','.$pagecount.'');

		foreach ($data as $k => $v) {
				$v['pay_createtime'] =date('Y-m-d H:i:s',$v['pay_createtime']);
				$data[$k]=$v;
			}
		outJsonpoint('ok','购积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}

}

/*
**购积分明细
*/
function shopPointList(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	$user = checkToken($token);
	$model= new M();
	if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	if($user){
		
		
		//$newpoint=$model->query('select money from ecm_epay where user_id = '.$user['user_id'].'');
		//echo $model->getsql();die;
		$summoney=$model->query('select sum(shops_point) as money from ecm_sgxt_get_point where sendid = '.$user['user_id'].' and is_pass in (0,1)');
		//发积分总和
		$summoney=$summoney[0]['money'];
		//当前积分
		$newpoint=$model->table('member')->where(array('user_id'=>$user['user_id']))->getField('pay_point');
		//累计积分
		$totalmoney= (int)$summoney+(int)$newpoint;

		$count=$model
			->query('select count(id) as id from ecm_sgxt_order where userid = '.$user['user_id'].' and status=1');
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		$data=$model
			->query('select orderid,paytype,num,amount,pay_createtime from ecm_sgxt_order where userid = '.$user['user_id'].' and status=1 order by createtime desc  limit '.$startpage.','.$pagecount.'');
		if(empty($totalmoney)){
			$totalmoney='0.00';
		}
		if(empty($newpoint)){
			$newpoint='0.00';
		}
		if(empty($summoney)){
			$summoney='0.00';
		}
		//echo $model->getsql();die;
		outJsonpoint('ok','发积分明细',$data,$totalpage,$summoney,$newpoint,$totalmoney);
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**联盟订单搜索
*/
function searchoffline(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	
	$user = checkToken($token);
	if($user){
		$model=new M();
		//$user['user_id']='316';
		//商城ID 
		$data=$model->query('select order_sn,ecm_order_offline.point,goods_amount,pay_time,classname,ecm_order_offline.order_id,tel,ecm_member.real_name,ecm_order_offline.status,seller_name from ecm_order_offline ecm_order_offline  inner join ecm_store on ecm_order_offline.seller_id=ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id = ecm_member.user_id where ecm_order_offline.buyer_id = '.$user['user_id'].' and '.$where.'');
		//$data['totalpage']=$totalpage;ecm_order_offline.status = '.$_POST["status"].'
	//echo $model->getsql();die;
		if($data){
			pageJson('ok',"订单信息",$data,$totalpage);
		}else{
			fk("数据获取失败");
		}
	}else{
		err('身份错误，请重新登录');
	}
}
/*
**取消订单
*/
function orderstate(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	if(!isset($_POST["order_id"]))err("请先选择要操作的订单");
	$user = checkToken($token);
	if($user){
		$model=new M();
		 $model
                ->table('order')
                ->where(array('order_id'=>$_POST["order_id"],'buyer_id'=>$user["user_id"]))
                ->update(array('status'=>'0'));
		 fk("订单取消成功");
	}else{
		err('身份错误，请重新登录');
	}
}

/**
 * 确认收货
 */
function order_confirm(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");	
	if(!intval($_POST['order_id'])) err("请先选择要操作的订单");
	$user = checkToken($token);
	if($user){
		$model=new M();
        $model_order = $model->table('order');
        $order_id = intval($_POST['order_id']);
        /* 只有已发货的订单可以确认 */
        $order_info = $model_order->where("order_id={$order_id} AND buyer_id=" . $user['user_id'] . " AND status=30")->find();
        if (empty($order_info)) {
            err('订单不存在或订单未发货');
        }
        $model_order->where('order_id='.$order_id.' and buyer_id='.$user['user_id'])->update(array('status' => 40, 'finished_time' => time(),'auto_finished_time'=>(time()+7*24*3600)));
        /* 记录订单操作日志 */            
        $order_log = $model->table('order_log');
        $order_log->add(array(
            'order_id' => $order_id,
            'operator' => addslashes($user['real_name']),
            'order_status' => 30,
            'changed_status' => 40,
            'remark' => '用户确认收货',
            'log_time' => time(),
            'operator_type'=>'buyer',
        ));

        /* 更新定单状态 开始***************************************************** */
        $mod_epay = $model->table('epay');
        $mod_epaylog = $model->table('epaylog');
        $epaylog_row = $mod_epaylog->where('order_id='.$order_id.' and type=20')->find();
        $money = $epaylog_row['money']; //定单价格
        $sell_user_id = $epaylog_row['to_id']; //卖家ID
        $buyer_user_id = $epaylog_row['user_id']; //买家ID
        if ($epaylog_row['order_id'] == $order_id) {
            /*$sell_money_row = $mod_epay->where('user_id='.$order_info['seller_id'])->find();
            $sell_money = $sell_money_row['balance']; //卖家的资金
            $sell_money_dj = $sell_money_row['freeze_balance']; //卖家的冻结资金
            $new_money = $sell_money + $money;
            $new_money_dj = $sell_money_dj - $money;
            //更新数据
            $new_money_array = array(
                'balance' => $new_money,
                'freeze_balance' => $new_money_dj,
            );*/
            $new_buyer_epaylog = array(
                'money'=>$money,
                'complete' => 1,
                'states' => 40,
            );
            $new_seller_epaylog = array(
                'money'=>$money,
                'complete' => 1,
                'states' => 40,
            );
            //$mod_epay->where('user_id='.$sell_user_id)->update($new_money_array);
            $mod_epaylog->where("order_id={$order_id} AND user_id={$sell_user_id}")->update($new_seller_epaylog);
            $mod_epaylog->where("order_id={$order_id} AND user_id={$buyer_user_id}")->update($new_buyer_epaylog);
        }
        /* 更新定单状态 结束***************************************************** */

        /*用户确认收货后 扣除商城佣金*/
        /*
        import('epay.lib');
        $epay=new epay();
        $epay->trade_charges($order_info);
        */
        
        /* 发送给卖家买家确认收货邮件，交易完成 */
        /*
        $model_member = & m('member');
        $seller_info = $model_member->get($order_info['seller_id']);
        $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
        $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

        $new_data = array(
            'status' => Lang::get('order_finished'),
            'actions' => array('evaluate'),
        );
        */

        /* 更新累计销售件数 */
        $model_goodsstatistics = $model->table('goods_statistics');
        $model_ordergoods = $model->table('order_goods');
        $order_goods = $model_ordergoods->where("order_id={$order_id}")->find();
        foreach ($order_goods as $goods) {
            $model_goodsstatistics->where('goods_id='.$goods['goods_id'])->setInc('sales',$goods['quantity']);
        }

        /* 如果赠送积分，则开始计算各种收益 */
        /*
        if($order_info['point'] > 0){
            $point_mod = & m('point');
            $point_mod->sendPoint($this->visitor->get('user_name'),$order_info['point'],$order_info['seller_id'],$order_info,'online');
        }
        */


        /*用户确认收货后 获得积分*/
        /*
        import('integral.lib');
        $integral=new Integral();
        $integral->change_integral_buy($order_info['buyer_id'],$order_info['goods_amount']);
        */
        
        /*交易成功后,推荐者可以获得佣金  BEGIN*/
        /*
        import('tuijian.lib');
        $tuijian=new tuijian();
        $tuijian->do_tuijian($order_info);
        */
        /*交易成功后,推荐者可以获得佣金  END*/
        
        
        //卖家确认收货 发送短信给卖家
        /*
        import('mobile_msg.lib');
        $mobile_msg = new Mobile_msg();
        $mobile_msg->send_msg_order($order_info,'check');
        */           
        
        //$this->pop_warning('ok','','index.php?app=buyer_order&act=evaluate&order_id='.$order_id);;\
        fk('确认成功');
	}else{
		err('身份错误，请重新登录');
	}	
}

/*
**订单详情
*/
function orderlist(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	if(!isset($_POST["order_id"]))err("请先选择要操作的订单");
	$user = checkToken($token);
	if($user){
		//订单内容
		$model=new M();
		$order=	 $model->table('order')
				->field('order_id,order_sn,order_amount,seller_id,status,add_time,is_check')
                ->where(array('order_id'=>$_POST["order_id"]))
                ->find();
		 //商铺信息
		$store = $model->table('store')
				->field('store_name,tel,store_logo')
                ->where(array('store_id'=>$order["seller_id"]))
                ->find();
		//商品信息
		$goods = $model->table('order_goods')
				->field('rec_id,spec_id,quantity,goods_id,goods_name,goods_image,price,specification')
                ->where(array('order_id'=>$_POST["order_id"]))
                ->select();
		foreach($goods as $k=>$v)
		{
			//查询商品是否退款，如果退款，退款状态
			$refund=$model->table('refund')->where('order_id='.$_POST['order_id'].' and goods_id='.$v['goods_id'].' and (spec_id='.$v['spec_id'].' or rec_id='.$v['rec_id'].') and (status=\'SUCCESS\' or status=\'WAIT_SELLER_AGREE\')')->find();
			$is_refund=0;
			$refund_id=0;
			$refund_status='';
			if(!empty($refund))
			{
				$is_refund=1;
				$refund_id=$refund['refund_id'];
				$refund_status=$refund['status'];
			}
			$goods[$k]['is_refund']=$is_refund;
			$goods[$k]['refund_id']=$refund_id;
			$goods[$k]['refund_status']=$refund_status;
			unset($refund);
		}
		//收货地址
		$address = $model->table('order_extm')
				->field('consignee,region_name,address,phone_tel,shipping_name,shipping_fee')
                ->where(array('order_id'=>$_POST["order_id"]))
                ->find();

		$data = array(
			'order_id'		=>$order['order_id'],
			'order_sn'		=>$order['order_sn'],
			'goods_amount'	=>$order['order_amount'],
			'seller_id'		=>$order['seller_id'],
			'status'		=>$order['status'],
			'add_time'		=>$order['add_time'],
            'is_check'		=>$order['is_check'],
			'store_name'	=>$store['store_name'],
			'tel'			=>$store['tel'],
			'store_logo'	=>$store['store_logo'],
			'goods'			=>$goods,
			'consignee'		=>$address['consignee'],
			'region_name'		=>$address['region_name'],
			'address'		=>$address['address'],
			'phone_tel'		=>$address['phone_tel'],
			'shipping_name'		=>$address['shipping_name'],
			'shipping_fee'		=>$address['shipping_fee']
		);
		fk("订单信息",$data);
	}else{
		err('身份错误，请重新登录');
	}
}
//审核订单列表
function auditorder(){
	$time11=time();
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	if(!isset($_POST["order_type"]))err("请选择订单所属类型");
	$order_type=$_POST["order_type"];
	/*if($_POST["starttime"]==""&&$_POST["endtime"]==""){
		$where = 'and 1=1';
	}else if($_POST["starttime"]!=""&&$_POST["endtime"]==""){
		$where = 'and 1=1';
	}else if($_POST["starttime"]==""&&$_POST["endtime"]!=""){
		$where = 'and 1=1';
	}else if($_POST["starttime"]!=""&&$_POST["endtime"]!=""){
		$where = 'and add_time >= '.$_POST["starttime"].' and add_time <= '.$_POST["endtime"].'';
	}*/
	$where='1';
	if(!empty($_POST["starttime"]))
	{
		$where.=' and add_time >='.strtotime($_POST["starttime"]);
	}
	if(!empty($_POST["endtime"]))
	{
		$where.=' and add_time >='.strtotime($_POST["starttime"]);
	}
	if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$pagecount= 10;
	$startpage=((int)$page-1)*$pagecount;
	$user = checkToken($token);
	$model= new M();
	if($user){
		$userlist = $model->table('member')
				->field('ahentarea')
                ->where(array('user_id'=>$user["user_id"]))
                ->find();
		//联盟订单审核列表
		if($order_type=="offline"){
			
			$count=$model
			->query('select count(ecm_order_offline.order_id) as id from ecm_order_offline inner join ecm_member on ecm_order_offline.seller_id = ecm_member.user_id where ecm_member.area = '.$userlist['ahentarea'].' and '.$where.' and ecm_order_offline.status=40');
			$count=$count[0]['id'];		
			$totalpage=ceil($count/$pagecount);
			$data=$model
			->query('select check_time,payment_name,ecm_order_offline.point,order_amount,order_sn,is_check,seller_name,buyer_id,buyer_name from ecm_order_offline inner join ecm_member on ecm_order_offline.seller_id = ecm_member.user_id where ecm_member.area = '.$userlist['ahentarea'].' and '.$where.' and ecm_order_offline.status=40 order by ecm_order_offline.order_id desc limit '.$startpage.','.$pagecount);
			foreach($data as $key => $value)
			{
				$data[$key]['user_name'] = $model->table('member')-> where(array('user_id' => $value['buyer_id'])) -> getField('user_name');
			}
            file_put_contents('time01.txt',(time()-$time11)."\n",FILE_APPEND);
			pageJson('ok',"联盟订单信息",$data,$totalpage);
		}else if($order_type=="online"){
			$where .= ' and ecm_order.status=40 and ecm_order.order_amount>0 ';
			$count=$model
			->query('select count(ecm_order.order_id) as id from ecm_order inner join ecm_member on ecm_order.seller_id = ecm_member.user_id where ecm_member.area = '.$userlist['ahentarea'].' and '.$where);
			$count=$count[0]['id'];		
			$totalpage=ceil($count/$pagecount);
			$data=$model
			->query('select check_time,payment_name,ecm_order.point,order_amount,order_sn,is_check,seller_name,buyer_id,buyer_name from ecm_order inner join ecm_member on ecm_order.seller_id = ecm_member.user_id where ecm_member.area = '.$userlist['ahentarea'].' and '.$where.' order by ecm_order.order_id desc limit '.$startpage.','.$pagecount);
			foreach($data as $key => $value)
			{
				$data[$key]['user_name'] = $model->table('member')-> where(array('user_id' => $value['buyer_id'])) -> getField('user_name');
			}
            file_put_contents('time01.txt',(time()-$time11)."\n",FILE_APPEND);
			pageJson('ok',"商城订单信息",$data,$totalpage);
		}	
	}else{
		err('身份错误，请重新登录');
	}
}

function offlinepayment(){
	$token = rawurlencode($_POST['token']);
	if(!isset($_POST["token"]))err("请先登录");
	//if(!isset($_POST["status"]))err("请确认订单状态");
	 if($_POST["page"]=="0"||$_POST["page"]==""){
		$page='1';
	}else{
		$page=$_POST["page"];
	}
	$model=new M();
	$pagecount= 10;
	$startpage=((int)$page-1)*10;
	$user = checkToken($token);
	if($user){
		$o2o = $model
			->field('o2o')
			->table('store')
			->where(array('store_id'=>$user['user_id']))
			->select();
	if($o2o[0]['o2o']=="online"){
		$where='is_check=0 and ecm_order.status=40';
		$count=$model
			->query('select count(ecm_order.order_id) as id from ecm_order  inner join ecm_member on ecm_order.buyer_id=ecm_member.user_id where ecm_order.seller_id = '.$user['user_id'].' and '.$where.'');
		$count=$count[0]['id'];		
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->query('select order_sn,ecm_order.status,order_amount,ecm_order.point,pay_time,payment_name from ecm_order inner join ecm_member on ecm_order.buyer_id=ecm_member.user_id where ecm_order.seller_id = '.$user['user_id'].' and '.$where.' limit '.$startpage.','.$pagecount.'');
		if($data){
			pageJson('ok',"未审核货款",$data,$totalpage);
		}else{
			pageJson('ok',"未审核货款",$data,$totalpage);
		}
	
	}else if($o2o[0]['o2o']=="offline"){
		
		//$user['user_id']='316';
		$count=$model
			->field('count(ecm_order_offline.order_id) as id')
			->table('order_offline  inner join ecm_member on ecm_order_offline.buyer_id=ecm_member.user_id')
			->where(array('ecm_order_offline.seller_id'=>$user['user_id'],'payment_id'=>3,'is_check'=>0,'ecm_order_offline.status'=>40))
			->select();
		$count=$count[0]['id'];	
		
		$totalpage=ceil($count/$pagecount);
		//商城ID 
		$data=$model->query('select payment_name,order_sn,pay_time,ecm_order_offline.point,ecm_order_offline.status,order_amount from ecm_order_offline  inner join ecm_store on ecm_order_offline.seller_id=ecm_store.store_id inner join ecm_member on ecm_order_offline.buyer_id=ecm_member.user_id where ecm_order_offline.seller_id = '.$user['user_id'].' and is_check=0 and payment_id = 3 and ecm_order_offline.status=40 order by ecm_order_offline.pay_time desc limit '.$startpage.','.$pagecount.'');
		//$data['totalpage']=$totalpage;
		//echo  $model->getsql();die;
		if($data){
			pageJson('ok',"未审核货款",$data,$totalpage);
		}else{
			pageJson('ok',"未审核货款");
		}

	}else{
		pageJson('ok',"未审核货款");
	}
	}else{
		err('身份错误，请重新登录');
	}
}
?>

