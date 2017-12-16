 <?php
/*
**我的财富(普通会员)
*/
//购物积分头部
function pointTop(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user){
		$m =new M();
        $userPoint =$m->table('member')->field('point_peac,point,happiness')->where(array('user_id' =>$user['user_id']))->find();
        $current =$m->table('epay')->where(array('user_id' => $user['user_id']))->find();
        $data['point_peac'] =$userPoint['point_peac'];//权
        $data['point'] =$userPoint['point']; //结余积分
        $data['current'] =$current['money'];//当前可用
        $data['happiness'] =$userPoint['happiness']; //幸福积分

        fk('banner',$data);
	}else{
		err('身份错误,请重新登录');
	}

}

//转化记录  -购物积分  
function convertRecord(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user){
		$m =new M();
		$taba = DB_PREFIX."sgxt_balance";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $sql ="select count(user_id) as uid from $taba where user_id=".$user['user_id'];
        $count =$m->query($sql);
        $count =$count[0]['uid'];
        $totalpage=ceil($count/$pagecount);
        if($user["type"]>2){
        	//会员奖励  购物积分转化  市场补贴        
        	$sql = "select $taba.get_money,$taba.happiness,$tabb.title,$tabb.content,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type in (1,2,3) and is_clearing=1 and user_id= ".$user['user_id']." order by $taba.id desc limit $startcount,$pagecount";
             
        }else{
            //会员奖励  购物积分转化
        	 $sql = "select $taba.get_money,$taba.happiness,$tabb.title,$tabb.content,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type in (1,2) and is_clearing=1 and user_id= ".$user['user_id']." order by $taba.id desc limit $startcount,$pagecount";
        }
         $record =$m->query($sql);
        if(empty($record))$record=array();
        foreach($record as $key=>$val){
            $rep['money'] = $val['get_money'];
            $record[$key]['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $record[$key]['content'] = replace_tpl($rep , $val['content']);
        }
        
        pageJson('ok',"转化记录",$record,$totalpage);
       	// fk('转化记录',$record);
	}else{
		err('身份错误,请重新登录');
	}
}

//支付记录
function payRecord(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user){
		$m =new M();
        $sql ="select count(to_id) as uid from ecm_paymentlog where user_id=".$user['user_id']." and payment_id =3";
        $count =$m->query($sql); 
        $count =$count[0]['uid']; //总条数
        $totalpage=ceil($count/$pagecount);
        $sql ="select money,order_sn,to_id,add_time,payment_id from ecm_paymentlog where user_id=".$user['user_id']." and payment_id=3 order by add_time desc limit $startcount,$pagecount";
        $res =$m->query($sql);
		foreach ($res as $key => $value){
			$shopname =$m->table('store')->field('store_name')->where('store_id='.$value['to_id'])->find();
			$value['store_name'] =$shopname['store_name'];
			$value['add_time'] =date('Y-m-d H:i:s',$value['add_time']);
			$res[$key] =$value;
		}
        // $res['totalpage'] =$totalpage; 

        pageJson('ok',"支付记录",$res,$totalpage);

		// fk('支付记录',$res);
	}else{
		err('身份错误，请重新登录');
	}

}


//获赠积分(消费积分)  线上 线下购物赠送
function getPoint(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user){
		$m =new M();
        $taba =DB_PREFIX.'sgxt_get_point';
        $tabb =DB_PREFIX.'store';
        $sql ="select count(getid) as gid from $taba where getid=".$user['user_id'];
        $count =$m->query($sql);
        $count =$count[0]['gid'];   //总条数
        $totalpage =ceil($count/$pagecount);
        $sql ="select $taba.oto,$taba.point,$taba.createtime,$tabb.store_name,$taba.order_id,$taba.oto,is_pass from $taba left join $tabb on $taba.sendid =$tabb.store_id where is_pass in (0,1) and getid=".$user['user_id']."  order by $taba.createtime desc limit $startcount,$pagecount";
        $pointList =$m->query($sql);
        $tablearray=array('online'=>'order','offline'=>'order_offline');
        foreach($pointList as $k=>$v){
            $order_sn =$m->table($tablearray[$v['oto']])->where(array('order_id'=>$v['order_id']))->getField('order_sn');
            $v['order_sn'] =$order_sn;
            $v['createtime'] =date('Y-m-d H:i:s',$v['createtime']);
            $pointList[$k] =$v;

        }
        pageJson('ok',"获赠积分",$pointList,$totalpage);
	}else{
		err('身份错误,请重新登录');
	}
}

//获赠积分  测试
function testPoint(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount=conf('pagecount');//默认一页条数
    $startcount =((int)$page-1)*$pagecount;
    $user =checkToken($token);
    if($user){
        $m= new M();
        //线上  线下  
        $taba =DB_PREFIX.'order';
        $tabb =DB_PREFIX.'order_offline';
        //统计总条数
        $sql ="select * from 
        (select count($taba.buyer_id) as anum from $taba where $taba.status=40  and $taba.buyer_id=".$user['user_id'].") as numa ,
        (select count($tabb.buyer_id) as bnum from $tabb where $tabb.buyer_id =".$user['user_id'].") as numb ";
        $totalcount =$m->query($sql);
        $count =0;
        foreach ($totalcount as $k => $val) {
            $count +=array_sum($val); 
        }
        $totalpage =ceil($count/$pagecount);//总页数
        $online ='online';
        $offline ='offline';
        $sql ="(select $taba.seller_name,$taba.point,$taba.finished_time as time,'$online' as oto from $taba where $taba.status=40 and $taba.buyer_id=".$user['user_id'].") union (select $tabb.seller_name,$tabb.point,$tabb.pay_time as time,'$offline' as oto from $tabb where $tabb.status=40 and $tabb.payment_id=9 and $tabb.buyer_id=".$user['user_id'].") order by time desc limit $startcount,$pagecount" ;
        $pointList =$m->query($sql);
        // var_dump($pointList);die;
        while($rows =mysql_fetch_assoc($pointList)){
            $rows['time'] =date('Y-m-d H:i:s',$rows['time']);
            $data[]=$rows;
        }
        pageJson('ok',"获赠积分测试",$data,$totalpage);


    }else{
        err('身份错误，请重新登录');
    }
}



//会员奖励(团队奖励) type=1
function memberReward(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user){
		$m =new M();
		$taba = DB_PREFIX."sgxt_balance";
        $tabb = DB_PREFIX."sgxt_infotpl";
        //分页
        $sql ="select count(from_userid) as fid from $taba where source_type=1 and user_id=".$user['user_id'];
        $count =$m->query($sql);
        $count =$count[0]['fid']; 
      	$totalpage =ceil($count/$pagecount);
        $sql = "select $taba.from_username,$taba.order_id,$taba.from_userid,$taba.get_money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 1 and user_id= ".$user['user_id']." order by $taba.id desc limit $startcount,$pagecount";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$val){
			$phone =$m->table('member')->field('user_name')->where('user_id='.$val['from_userid'])->find();
        	$val['phone'] =$phone['user_name']; //手机号
            $val['real_point'] =$val['get_money'];
            // if($type == 7){
            //   $rep['point'] = $val['real_point'];
            // }
            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $balance[$key] =$val;         
        }
        pageJson('ok',"会员奖励",$balance,$totalpage);

	}else{
		err('身份错误,请重新登录');
	}
}

/***********普通会员结束****************/

/*
***招商奖励
*/
//收益奖励公共头部
function profitTop(){
   $token =urlencode($_POST['token']);
   if(!isset($_POST['token'])) err('请先登录');
   $user =checkToken($token);
   if($user['type']>1){
    $m =new M();
    $mon =date('Y-m-d',mktime(0,0,0,date('m')-1,1,date('Y'))); //上个月开始时间
    $days =date('t',strtotime($mon)); //上个月天数
    $start=mktime(0,0,0,date('m')-1,1,date('Y'));  //上月开始时间
    $end =mktime(23,59,59,date('m')-1,$days,date('Y')); //上月结束时间
    $userProfit =$m->table('epay')->field('earnings,freeze_earnings')->where('user_id='.$user['user_id'])->find();
    $profit['current'] =$userProfit['earnings']; //当前可提
    $profit['freeze'] =$userProfit['freeze_earnings']; //冻结收益
    // $sql ="select sum(remain_money) as money from ecm_sgxt_profit where user_id=".$user['user_id']." and is_clearing =0"." and createtime < $end";
    $sql ="select sum(remain_money) as money from ecm_sgxt_profit where user_id=".$user['user_id'];
    $depositProfit=$m->query($sql);
    $profit['totalprofit'] =$depositProfit[0]['money']; //累计收益
    //统计代理收益
       $sql ="select sum(money) as money from ecm_sgxt_commission where toid=".$user['user_id'];
       $dlProfit=$m->query($sql);
       $profit['totalprofit'] =$profit['totalprofit']+$dlProfit[0]['money']; //累计收益
    $profit['totalprofit'] =$profit['totalprofit'] ? $profit['totalprofit'] : '0.00';
    fk('success',$profit);
   }
}

// 经理奖励 --推荐销售经理(佣金)
function managerReward(){ 
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user['type']>1){
		$m =new M();
		$taba = DB_PREFIX."sgxt_commission";
        $tabb = DB_PREFIX."member";
        //分页
        $sql ="select count(toid) as tid from $taba where toid=".$user['user_id'];
        $count =$m->query($sql);
        $count =$count[0]['tid'];
        $totalpage =ceil($count/$pagecount);
        //总收益
		$sql ="select sum(money) as totalcommission from $taba join $tabb on $taba.fromid =$tabb.user_id where toid=".$user['user_id']." and $tabb.type=3";
		$totalcommission = $m->query($sql);
		$totalcommission =$totalcommission[0]['totalcommission'];
		$totalcommission =$totalcommission ? $totalcommission : '0.00';
        $sql ="select $taba.toid,$taba.fromid,$taba.info,$taba.createtime,$tabb.real_name from $taba left join $tabb on $taba.fromid =$tabb.user_id where toid=".$user['user_id']." and $tabb.type=3 order by id desc limit $startcount,$pagecount";
        $commission =$m->query($sql);
       foreach($commission as $key=>$val){
          $commission[$key]['info'] = '推荐'.$val['real_name'].$val['info'];
          $commission[$key]['createtime'] = date('Y-m-d H:i' , $val['createtime']);
           unset($commission[$key]['real_name']);
       }

        $commission['totalcommission']=$totalcommission;
        pageJson('ok',"经理奖励",$commission,$totalpage);

	}else{
		err('访问错误');
	}
}

//商家收益  ---直推商家收益
function merchantReward(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user['type']>1){
		$m =new M();
		$taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        //分页
        $sql ="select count(user_id) as uid from $taba where user_id=".$user['user_id']." and source_type =4";
        $count =$m->query($sql);
        $count =$count[0]['uid'];
        $totalpage =ceil($count/$pagecount);
        $sql ="select sum(remain_money) as allmoney from $taba where user_id=".$user['user_id']." and source_type =4";//总收益  
        $allmoney =$m->query($sql);
        $allmoney =$allmoney[0]['allmoney'];    
        $allmoney=$allmoney ? $allmoney : '0.00';
        $sql = "select $taba.from_username,$taba.createtime,$taba.real_point,$taba.remain_money,$tabb.content from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." order by $taba.id desc limit $startcount,$pagecount";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
            $rep['name'] = $band['from_username'];
            $rep['point'] = $band['real_point']*conf('PAY_INFO/shops_point');
            $rep['money'] = $band['remain_money'];
           $balance[$key]['createtime'] = date('Y-m-d H:i',$band['createtime']);
           $balance[$key]['content'] = replace_tpl($rep , $band['content']);
        }
        $balance['allmoney'] =$allmoney;
        pageJson('ok',"商家收益",$balance,$totalpage);
    
	}else{
		err('访问错误');
	}
}

//代理奖励  直推代理   
function agentReward(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
	$user =checkToken($token);
	if($user['type']>1){
		$m =new M();
		$taba = DB_PREFIX."sgxt_commission";
        $tabb = DB_PREFIX."member";
         //分页
        $sql ="select count($taba.toid) as tid from $taba join $tabb on $taba.toid=$tabb.user_id where $taba.toid=".$user['user_id'];
        $count =$m->query($sql);
        $count =$count[0]['tid'];
        $totalpage =ceil($count/$pagecount);
        //总收益
        $sql ="select sum(money) as totalcommission from $taba join $tabb on $taba.toid=$tabb.user_id where toid=".$user['user_id'];
        $totalcommission =$m->query($sql);
        $totalcommission =$totalcommission[0]['totalcommission'];
        $totalcommission =$totalcommission ? $totalcommission : '0.00';
        $sql ="select $taba.money,$taba.createtime from $taba left join $tabb on $taba.fromid =$tabb.user_id where $taba.toid=".$user['user_id']." order by id desc";
        $agentList =$m->query($sql);
        foreach($agentList as $key=>$val){
          $agentList[$key]['info'] = '通过代理奖励为您增加收益';
          $agentList[$key]['createtime'] = date('Y-m-d H:i' , $val['createtime']);
       }
       $agentList['totalcommission'] =$totalcommission;
       // $agentList['totalpage'] =$totalpage;
       pageJson('ok',"代理奖励",$agentList,$totalpage);
  
	}else{
		err('访问错误');
	}
}

//县代收益
function countyReward(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
	$pagecount=conf('pagecount');//默认一页条数
	$startcount =((int)$page-1)*$pagecount;
    $user =checkToken($token);
    if($user['type'] =5){
        $m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        //分页 
        $sql ="select count(user_id) as uid from $taba where user_id=".$user['user_id']." and source_type =6";
        $count =$m->query($sql);
        $count =$count[0]['uid'];
        $totalpage =ceil($count/$pagecount);
        //总收益
        $sql ="select sum(remain_money) as totalmoney from $taba where user_id=".$user['user_id']." and source_type =6";
        $allMoney =$m->query($sql);
        $allMoney =$allMoney[0]['totalmoney'] ? $allMoney[0]['totalmoney'] : '0.00';
        //间接收益
        $sql ="select $taba.remain_money as money,$taba.createtime,$taba.from_username from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =6 and $taba.user_id =".$user['user_id']." order by $taba.id desc limit $startcount,$pagecount";
        $money =$m->query($sql);
        foreach($money as $k=>$v){
            $v['createtime'] =date('Y-m-d ',$v['createtime']);
            $v['info'] ='推荐县级代理下商家'.$v['from_username'].' 为您增加';
            $money[$k] =$v;
        }
        $money['allmoney'] =$allMoney;
        // $money['totalpage'] =$totalpage;
       pageJson('ok',"代理奖励",$money,$totalpage);

        // fk('success',$money);

    }else{
        err('访问错误');
    }
}
 //每天统计(收益账单明细、购物账单明细)  //辖区商家收益
function incomeBill_statistics()
{
    $m =new M();
    $taba = DB_PREFIX."sgxt_profit";
    $tabb = DB_PREFIX."sgxt_infotpl";
    $tabc = DB_PREFIX.'sgxt_commission';
    $tabd = DB_PREFIX.'member';
    //$day=date('Y-m-d');
    $day='2016-07-30';
    $endtime=strtotime($day);
    $startdate=date('Y-m-d',strtotime($day.'-1 day'));
    $starttime=strtotime($startdate);
    //统计昨天一天的账单
    $data=array();
    //查询昨天登录用户信息
    $userslist=$m->table('member')->where('user_id=11')->select();
    foreach($userslist as $userkey=>$user)
    {

        //辖区商家收益（区代县代）
        if($user['type']==4||$user['type']==5)
        {
            $taba1 = DB_PREFIX."sgxt_profit";
            $tabc1 = DB_PREFIX."member";
            $shopincome=array();
            //当前商家数
            if($user['type']==4){
                $shopslist=$m->table('member')->field('user_id')->where(array('opid'=>$user['user_id'],'type'=>2))->select();
            }else{
                //当前商家数
                $shopslist=$m->table('member')->field('user_id')->where(array('area'=>$user['ahentarea'],'type'=>2))->select();
            }
            foreach($shopslist as $key=>$value){
                //每个商家的信息（根据商家查询其收益）
                $orderonline=$m->table('order')->field('order_id,order_sn,point,status,add_time')->where('seller_id='.$value['user_id'].' and add_time>='.$starttime.' and add_time<'.$endtime)->select();
                foreach($orderonline as $k => $v){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$v['point']*0.3*0.06;
                    //显示每个订单的商家信息
                    $shopsinfo=getclassnamebyorderid($v['order_id'],$value['user_id']);
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['status']=$v['status'];
                    $shopsinfo['add_time']=$v['add_time'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopincome[]=$shopsinfo;
                }
                //线下
                $sql='select ecm_order_offline.order_id,ecm_order_offline.order_sn,ecm_order_offline.point,ecm_order_offline.status,ecm_order_offline.add_time,ecm_order_offline.classname,ecm_store.store_name from ecm_order_offline join ecm_store on ecm_store.store_id=ecm_order_offline.seller_id where ecm_order_offline.seller_id='.$value['user_id'].' and ecm_order_offline.add_time>='.$starttime.' and ecm_order_offline.add_time<'.$endtime;
                $orderoffline=$m->query($sql);
                foreach($orderoffline as $koffline => $voffline){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$voffline['point']*0.3*0.06;
                    //显示每个订单的商家信息
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['status']=$voffline['status'];
                    $shopsinfo['add_time']=$voffline['add_time'];
                    $shopsinfo['classname']=$voffline['classname'];
                    $shopsinfo['store_name']=$voffline['store_name'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopincome[]=$shopsinfo;
                }
            }
        }
    }

    foreach($shopincome as $k=>$v)
    {
        $m->table('shopincome')->insert(array('user_id'=>$v['user_id'],'earning'=>$v['earning'],'status'=>$v['status'],'add_time'=>$v['add_time'],'classname'=>$v['classname'],'store_name'=>$v['store_name']));
    }
    fk('ok');
}
 //收益账单
 function profitBill(){
     $token =urlencode($_POST['token']);
     if(!isset($_POST['token'])) err('请先登录');
     $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
     $pagecount=conf('pagecount');//默认一页条数
     $startcount=($page-1)*$pagecount;
     $m =new M();
     $totalcount=$m->table('account_profit')->where(array('user_id'=>$user['user_id']))->count();
     $billlist=$m->table('account_profit')->field('user_id,type,info,money,createtime,user_name')->where(array('user_id'=>$user['user_id']))->order('createtime desc')->limit($startcount.','.$pagecount)->select();
     foreach($billlist as $key=>$val){
        $billlist[$key]['createtime'] =date('Y-m-d H:i:s',$val['createtime']);
     }
     $totalpage=ceil($totalcount/$pagecount);
     pageJson('ok',"收益账单",$billlist,$totalpage);
 }

 //购物积分账单   
 function pointBill(){
     $token =urlencode($_POST['token']);
     if(!isset($_POST['token'])) err('请先登录');
     $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
     $pagecount=conf('pagecount');//默认一页条数
     $startcount=($page-1)*$pagecount;
     if($user){
         $m =new M();
         $totalcount=$m->table('bill_statistics')->where(array('user_id'=>$user['user_id'],'is_clearing'=>1))->count();
         $bill=$m->table('bill_statistics')->where(array('user_id'=>$user['user_id'],'is_clearing'=>1))->order('createtime desc')->limit($startcount.','.$pagecount)->select();
		 foreach($bill as $key=>$value)
         {
             $bill[$key]['point']=$value['get_money'];
         }
         $totalpage=ceil($totalcount/$pagecount);
         pageJson('ok',"购物积分账单",$bill,$totalpage);
     }else{
         err('身份错误，请重新登录');
     }
 }


//账单  收益奖励  商家  销售经理

/*function shopBill(){
	$token =urlencode($_POST['token']);
	if(!isset($_POST['token'])) err('请先登录');
	$user =checkToken($token);
	if($user['type'] =2 || $user['type'] =3){
		$m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX.'sgxt_commission';
        $tabd = DB_PREFIX.'member';
		//经理奖励
		$sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 order by id desc";
        $commission =$m->query($sql);
        foreach($commission as $key=>$val){
           $commission[$key]['info'] = '经理奖励';
           $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //代理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 order by id desc";
        $agentReward =$m->query($sql);
        foreach($agentReward as $key=>$val){
           $agentReward[$key]['info'] = '代理奖励';
           $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

 		//商家收益   直推商家
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
           $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
           $balance[$key]['info'] ='商家收益';

        }
        if($user['type'] =2){
        	//提现  货款提现  收益提现
       	 	$deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1))->select();
        }
        if($user['type'] >2){
           //提现   收益提现
       		$deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->select();
    
        }
        foreach ($deposit as $key => $val) {
        	$deposit[$key]['operatortime'] =date('Y-m-d',$val['operatortime']);
            $deposit[$key]['info'] ='提现';
        }

        $data =array('saleReward'=>$commission,
                     'agentReward'=>$agentReward,
                     'profit'=>$balance,
                     'deposit'=>$deposit);

        fk('success',$data);
	}else{
		err('访问错误');
	}
}

//账单   区域代理   
function  areaBill(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    if($user['type'] =4){
        $m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX.'sgxt_commission';
        $tabd = DB_PREFIX.'member';
        //  经理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 order by id desc";
        $commission =$m->query($sql);
        foreach($commission as $key=>$val){
           $commission[$key]['info'] = '经理奖励';
           $commission[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);
        }

        //代理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 order by id desc";
        $agentReward =$m->query($sql);
        foreach($agentReward as $key=>$val){
           $agentReward[$key]['info'] = '代理奖励';
           $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //商家收益   直推商家
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
           $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
           $balance[$key]['info'] ='商家收益';
        }

        //提现   收益提现
        $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->select();
        foreach ($deposit as $key => $val) {
            $deposit[$key]['operatortime'] =date('Y-m-d',$val['operatortime']);
            $deposit[$key]['info'] ='提现';
        }

        //辖区会员收益
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 7 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $areaReward = $m->query($sql);
        if(empty($areaReward))$areaReward=array();
        foreach($areaReward as $key=>$band){
            $band['createtime'] = date('Y-m-d ',$band['createtime']);
            $band['info'] ='辖区会员收益';
            $areaReward[$key] =$band;
        }

        //辖区商家收益  type =5
        $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =5 and user_id=".$user['user_id']." order by $taba.id desc";
        $shopReward =$m->query($sql);
        foreach ($shopReward as $k => $v) {
            $v['createtime'] =date('Y-m-d',$v['createtime']);
            $v['info'] ='辖区商家收益';
            $shopReward[$k] =$v;
        }

        $data =array('saleReward'=>$commission,
                     'agentReward'=>$agentReward,
                     'profit'=>$balance,
                     'deposit'=>$deposit,
                     'areaReward'=>$areaReward,
                     'shopReward'=>$shopReward
                     );
         fk('success',$data);
    }else{
        err('访问错误');
   
 }
}

//账单  收益奖励  县代
function  countyBill(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    if($user['type'] =5){
        $m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX.'sgxt_commission';
        $tabd = DB_PREFIX.'member';
        //  经理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 order by id desc";
        $commission =$m->query($sql);
        foreach($commission as $key=>$val){
           $commission[$key]['info'] = '经理奖励';
           $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //代理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 order by id desc";
        $agentReward =$m->query($sql);
        foreach($agentReward as $key=>$val){
           $agentReward[$key]['info'] = '代理奖励';
           $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //商家收益   直推商家
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
           $balance[$key]['createtime'] = date('Y-m-d H:i',$band['createtime']);
           $balance[$key]['info'] ='商家收益';
        }

        //提现   收益提现
        $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->select();
        foreach ($deposit as $key => $val) {
            $deposit[$key]['operatortime'] =date('Y-m-d H:i:s',$val['operatortime']);
            $deposit[$key]['info'] ='提现';
        }

        //县区会员收益
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 7 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $areaReward = $m->query($sql);
        if(empty($areaReward))$areaReward=array();
        foreach($areaReward as $key=>$band){
            $band['money'] = $band['remain_money'];
            $band['createtime'] = date('Y-m-d ',$band['createtime']);
            $band['info'] ='县区会员收益';
            $areaReward[$key] =$band;
        }

        //县区商家收益
        $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =5 and user_id=".$user['user_id']." order by $taba.id desc";
        $shopReward =$m->query($sql);
        foreach ($shopReward as $k => $v) {
            $v['createtime'] =date('Y-m-d',$v['createtime']);
            $v['info'] ='县区商家收益';
            $shopReward[$k] =$v;
        }

        //县代收益
        $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =6 and $taba.user_id =".$user['user_id']." order by $taba.id desc";
        $countyReward =$m->query($sql);
        foreach($countyReward as $k=>$v){
            $v['createtime'] =date('Y-m-d ',$v['createtime']);
            $v['info'] ='县代收益';
            $countyReward[$k] =$v;
        }
         $data =array('saleReward'=>$commission,
                     'agentReward'=>$agentReward,
                     'profit'=>$balance,
                     'deposit'=>$deposit,
                     'areaReward'=>$areaReward,
                     'shopReward'=>$shopReward,
                     'countyReward'=>$countyReward
                     );

        fk('success',$data);

    }else{
        err('访问错误');
    }
}


//账单  收益奖励  市代 省代  
function  pcBill(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    if($user['type'] >5){
        $m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX.'sgxt_commission';
        $tabd = DB_PREFIX.'member';
        //  经理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 order by id desc";
        $commission =$m->query($sql);
        foreach($commission as $key=>$val){
           $commission[$key]['info'] = '经理奖励';
           $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //代理奖励
        $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type >3 order by id desc";
        $agentReward =$m->query($sql);
        foreach($agentReward as $key=>$val){
           $agentReward[$key]['info'] = '代理奖励';
           $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
        }

        //商家收益   直推商家
        $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$band){
           $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
           $balance[$key]['money'] =$band['remain_money'];
           $balance[$key]['info'] ='商家收益';
           unset($balance[$key]['remain_money']);
        }

        //提现   收益提现
        $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->select();
        foreach ($deposit as $key => $val) {
            $deposit[$key]['operatortime'] =date('Y-m-d H:i:s',$val['operatortime']);
            $deposit[$key]['info'] ='收益提现';
        }

        //辖区会员收益
        $sql ="select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type =7 and user_id= ".$user['user_id']." order by $taba.id desc ";
        $areaReward =$m->query($sql);
        if(empty($areaReward))$areaReward=array();
        foreach($areaReward as $key=>$band){
            $band['createtime'] = date('Y-m-d ',$band['createtime']);
            $band['info'] ='辖区会员收益';
            $areaReward[$key] =$band;
        }

         $data =array('saleReward'=>$commission,
                     'agentReward'=>$agentReward,
                     'profit'=>$balance,
                     'deposit'=>$deposit,
                     'areaReward'=>$areaReward
                     );
        fk('success',$data);
    }else{
        err('访问错误');
   
 }
}

*/

/*********2016/9/13****************/
//辖区会员收益
function areaMemberEarnings(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount=conf('pagecount');//默认一页条数
    $startcount =((int)$page-1)*$pagecount;
    $user =checkToken($token);
    if($user['type']>3){
        $m =new M();
        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX."member";
        $arr =array('4'=>'opid','5'=>'area','6'=>'city','7'=>'province');
        $arr2 =array('4'=>$user['user_id'],'5'=>$user['ahentarea'],'6'=>$user['ahentarea'],'7'=>$user['ahentarea']);
        $a=$arr[$user['type']];
        $b =$arr2[$user['type']];
        //当前会员数  
        $sql ="select count($tabc.user_id) as members from $tabc where $a =".$b;
        $members =$m->query($sql);
        $members =$members[0]['members'];
        //活跃会员  按消费
        $sql ="select count(distinct $taba.from_userid) as activeMember from $taba left join $tabc on $taba.from_userid = $tabc.user_id where $tabc.$a=".$b." and $taba.source_type = 7";
        $activeMember =$m->query($sql);
        $activeMember =$activeMember[0]['activeMember'];
        //辖区会员收益
        $totalcount=$m->table('memberincome')->where(array('user_id'=>$user['user_id']))->count();
        $totalpage=ceil($totalcount/$pagecount);
        $memberincome=$m->table('memberincome')->where(array('user_id'=>$user['user_id']))->order('add_time desc')->limit($startcount.','.$pagecount)->select();
        foreach ($memberincome as $k => $v) {
            $v['add_time'] =date('Y.m.d',$v['add_time']);
            $memberincome[$k] =$v;
        }
        $data =array('members'=>$members,'activeMember'=>$activeMember);
        outputJson('ok',"辖区会员收益",$memberincome,$totalpage,$data);
    }else{
        err('访问错误');
    }
}

//辖区会员查询
function memberSearch(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
     $pagecount=conf('pagecount');//默认一页条数
     $startcount=($page-1)*$pagecount;
    if($user['type']>3){
        $name =isset($_POST['user_name']) ? $_POST['user_name'] : ''; //用户姓名
        $phone =isset($_POST['phone']) ? $_POST['phone'] : '';  //手机号
        $startTime =isset($_POST['startTime']) ? $_POST['startTime'] : '';//开始时间
        if(empty($name) && empty($phone) && empty($startTime)) err('请输入搜素条件');
        $m =new M();
        $taba = DB_PREFIX."memberincome";
        $tabb =DB_PREFIX.'member';
        //查询条件
        $wherelist =array();
        if(!empty($name)){
            $wherelist[] ="$taba.user_name like '%{$name}%' and user_id=".$user['user_id'];
        }
        if(!empty($startTime)){
            $starttime=strtotime(date('Y-m-d', strtotime($startTime)));
            $start=date('Y-m-d', strtotime($startTime));
            $enddate=date('Y-m-d',strtotime($start.'+1 month'));
            $endtime=strtotime($enddate);
            $wherelist[] ="$taba.add_time >=$starttime and $taba.add_time <$endtime and user_id=".$user['user_id']; 
        }       
        if(!empty($phone)){
            $wherelist[] ="$taba.phone like '%{$phone}%' and user_id=".$user['user_id'];
        }  
        //组装查询条件
        if(count($wherelist)>0){
            $where = " where ".implode(' AND ',$wherelist); 
        }
        $sql ="select count(*) as num from $taba  {$where} ";
        $count =$m->query($sql);
        $totalcount =$count[0]['num'];
        $totalpage=ceil($totalcount/$pagecount);
        $sql ="select * from $taba {$where} limit {$startcount},{$pagecount}";
        $res =$m->query($sql);
        foreach ($res as $k => $v) {
            $v['add_time'] =date('Y.m.d',$v['add_time']);
            $res[$k] =$v;
        }
        pageJson('ok',"辖区会员查询",$res,$totalpage);
    }else{
        err('访问错误');
    }

}
 //辖区商家收益  社区$县代
 function areaShopEarnings(){
     $token =urlencode($_POST['token']);
     if(!isset($_POST['token'])) err('请先登录');
     $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
     $pagecount=conf('pagecount');//默认一页条数
     $startcount=($page-1)*$pagecount;
     if($user['type']>3 && $user['type']<6){
         $m =new M();
         $taba = DB_PREFIX."sgxt_profit";
         $tabc = DB_PREFIX."member";
         $arr =array('4'=>'opid','5'=>'area');
         $arr2 =array('4'=>$user['user_id'],'5'=>$user['ahentarea'],);
         $a =$arr[$user['type']];
         $b =$arr2[$user['type']];
         //当前商家数量
         $sql ="select count($tabc.user_id) as shops from $tabc where $tabc.type =2 and $tabc.$a=".$b;
         $shops =$m->query($sql);
         //活跃商家
         $sql ="select count(distinct $taba.from_userid) as activeShops from $taba left join $tabc on $taba.from_userid = $tabc.user_id where $tabc.$a=".$b." and $taba.source_type =5 and $tabc.type =2";
         $activeShops =$m->query($sql);
         //活跃商家数
         $data['activeshopscount']=$activeShops[0]['activeShops'];
         //辖区商家数量
         $data['shopscount']=$shops[0]['shops'];
         $totalcount=$m->table('account_profit')->where(array('user_id'=>$user['user_id'],'type'=>5))->count();
         $shopincome=$m->table('account_profit')->where(array('user_id'=>$user['user_id'],'type'=>5))->order('createtime desc')->limit($startcount.','.$pagecount)->select();
         $totalpage=ceil($totalcount/$pagecount);
         outputJson('ok',"辖区商家收益",$shopincome,$totalpage,$data);
     }else{
         err('身份错误，请重新登录');
     }
 }

 //辖区商家收益搜索
 function shopSearch(){
     $token =urlencode($_POST['token']);
     if(!isset($_POST['token'])) err('请先登录');
     $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
     $pagecount=conf('pagecount');//默认一页条数
     $startcount=($page-1)*$pagecount;
     if($user){
         $m =new M();
         $taba = DB_PREFIX."sgxt_profit";
         $tabc = DB_PREFIX."member";
         $storename = isset($_POST['storename']) ? $_POST['storename'] : ''; //商家姓名
         $startTime =$_POST['startTime'] ? $_POST['startTime'] : '';//开始时间
         $condition='user_id='.$user['user_id'];
         if(!empty($storename))
         {
             $condition.=' and store_name like \'%'.$storename.'%\'';
         }
         if(!empty($startTime))
         {
            //时间
             $starttime=strtotime(date('Y-m-d', strtotime($startTime)));
             $start=date('Y-m-d', strtotime($startTime));
             $enddate=date('Y-m-d',strtotime($start.'+1 month'));
             $endtime=strtotime($enddate);
             $condition.=' and add_time>='.$starttime.' and add_time<'.$endtime;
         }
         $totalcount=$m->table('shopincome')->where($condition)->count();
         $shopincome=$m->table('shopincome')->where($condition)->order('add_time desc')->limit($startcount.','.$pagecount)->select();
         $totalpage=ceil($totalcount/$pagecount);
         pageJson('ok',"辖区商家查询",$shopincome,$totalpage);
     }else{
         err('身份错误，请重新登录');
     }
 }


//根据线上订单id返回购买产品类别及商家名称
 function getclassnamebyorderid($order_id,$store_id){
     //根据orderid查找goodid
     $m =new M();
     //$goods =$m->table('order_goods')->field('goods_id')->where(array('order_id'=>$order_id))->select();
     $sql="select goods_id from ecm_order_goods where order_id={$order_id}";
     $goods =$m->query($sql);
     //根据goods_id查询商品类别名称
     $sql="select ecm_gcategory.cate_name,ecm_store.store_name from ecm_gcategory join ecm_category_goods on ecm_category_goods.cate_id=ecm_gcategory.cate_id join ecm_store on ecm_store.store_id=ecm_gcategory.store_id where ecm_category_goods.goods_id={$goods[0]['goods_id']} and ecm_gcategory.store_id={$store_id}";
     $namelist =$m->query($sql);
     $namelist['classname']=$namelist['cate_name'];
     unset($namelist['cate_name']);
     return $namelist;
 }

 function getclassnamebyorderidsearch($order_id,$store_id){
     //根据orderid查找goodid
     $m =new M();
     $sql="select goods_id from ecm_order_goods where order_id={$order_id}";
     $goods =$m->query($sql);
     //根据goods_id查询商品类别名称
     $sql="select ecm_gcategory.cate_name from ecm_gcategory join ecm_category_goods on ecm_category_goods.cate_id=ecm_gcategory.cate_id where ecm_category_goods.goods_id={$goods[0]['goods_id']} and ecm_gcategory.store_id={$store_id}";
     $namelist =$m->query($sql);
     $namelist['classname']=$namelist['cate_name'];
     unset($namelist['cate_name']);
     return $namelist;
 }

 //报表统计 （县代）
 function reportStatistics(){
     $token =urlencode($_POST['token']);
     if(!isset($_POST['token'])) err('请先登录');
     $user =checkToken($token);
     $page =isset($_POST['page']) ? $_POST['page'] : '1';
     $pagecount =conf('pagecount3'); //每页条数
     $startcount =($page-1)*$pagecount;
     $m =new M();
     if($user['type']==5) {
         //区域
         $area='';
         $condition="id in ({$user['province']},{$user['city']},{$user['area']})";
         $query=$m->table('sgxt_area')->field('name')->where($condition)->select();
         foreach($query as $k=>$v)
         {
             $area.=$v['name'];
         }
         $searchtime = $_POST['startTime'] ? $_POST['startTime'] : '';//开始时间
         $condition="user_id={$user['user_id']}";
         if(!empty($searchtime))
         {
             $condition.=" and times='{$searchtime}'";
         }
         //查询
         $report =$m->table('sgxt_report')->field('times,total_earning,activeshopscount,activeusercount,totalgivepoint,totalpaymentpoint,totalshopscount,usercount')->where($condition)->order('Id desc')->limit($startcount.','.$pagecount)->select();
         //count
         $totalcount=$m->table('sgxt_report')->where($condition)->count();
         $totalpage=ceil($totalcount/$pagecount);
         $arr['area']=$area;
         outputJson('ok',"报表统计",$report,$totalpage,$arr);
     }else
     {
         err('访问错误');
     }
 }

//商家活跃度
function shopActives(){
     $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =conf('pagecount'); //每页条数
    $startcount =($page-1)*$pagecount;
    if($user['type'] ==4 || $user['type'] ==5){
        $m =new M();
        $taba= DB_PREFIX."sgxt_get_point";
        $tabb =DB_PREFIX."member";
        $tabc =DB_PREFIX."store";
        $arr =array('4'=>'opid','5'=>'area');
        $arr2 =array('4'=>$user['user_id'],'5'=>$user['ahentarea']);
        $a =$arr[$user['type']];
        $b =$arr2[$user['type']];
        $sql ="select count(*) as count from $taba left join $tabb on $taba.sendid =$tabb.user_id where $a=$b and $tabb.type =2 group by $taba.sendid";
        $totalcount =$m->query($sql);
        $totalcount =$totalcount[0]['count'];
        $totalpage =ceil($totalcount/$pagecount); //总页数
        //积分总数
        $sql ="select sum(shops_point) as totalPoint,$taba.sendid,$taba.sendname,$tabb.user_name,$tabc.store_name from $taba left join $tabb on $taba.sendid =$tabb.user_id join $tabc on $tabb.user_id=$tabc.store_id where $tabb.$a=$b and $tabb.type =2 group by $taba.sendid order by totalPoint desc limit $startcount,$pagecount";
        $totalPoint =$m->query($sql);
        foreach ($totalPoint as $key => $val) {
            $totalPoint[$key]['info'] ='已发积分';
        }
        pageJson('ok',"商家活跃度",$totalPoint,$totalpage);

    }else{
        err('身份错误，请重新登录');
    }
}



//商家活跃度   区代  县代
function  activeShops(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =conf('pagecount'); //每页条数
    $startcount =($page-1)*$pagecount;
    if($user['type'] ==4 || $user['type'] ==5){
       $m= new M();
       $sql ="select count(*)  as count from ecm_shopincome where user_id=".$user['user_id']." group by shopper_id";
        $totalcount =$m->query($sql);
        $totalcount =$totalcount[0]['count'];
        $sql ="select sum(shoppoint) as totalPoint,ecm_shopincome.shopper_id,ecm_shopincome.shopper_name from ecm_shopincome where user_id=".$user['user_id']."  group by shopper_id order by totalPoint desc limit $startcount,$pagecount";
        $active =$m->query($sql);
        $totalpage =ceil($totalcount/$pagecount);
        foreach ($active as $key => $v) {
            $active[$key]['info'] ='发积分总额';
        }

        pageJson('ok',"商家活跃度",$active,$totalpage);
    }else{
        err('访问错误');
    }

}


//会员活跃度
function memberActive(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    $user =checkToken($token);
    $page =isset($_POST['page']) ? $_POST['page'] : '1';
    $pagecount =conf('pagecount'); //每页条数
    $startcount =($page-1)*$pagecount;
    if($user['type'] >3){
        $m =new M();
        // $taba =DB_PREFIX.'order';
        // $tabb =DB_PREFIX.'order_offline';
        $tabc =DB_PREFIX.'member';
        $tabd =DB_PREFIX.'sgxt_get_point';
        $arr =array('4'=>'opid',
                    '5'=>'area',
                    '6'=>'city',
                    '7'=>'province'
            );
        $arr2 =array('4'=>$user['user_id'],
                     '5'=>$user['ahentarea'],
                     '6'=>$user['ahentarea'],
                     '7'=>$user['ahentarea']
            );
        $a =$arr[$user['type']];
        $b =$arr2[$user['type']];
        // 统计总条数
        // $sql ="select * from 
        // (select count(distinct $taba.buyer_id) as anum from $taba join $tabc on $taba.buyer_id =$tabc.user_id where $taba.status=40 and $tabc.$a=$b ) as numa ,
        // (select count(distinct $tabb.buyer_id) as bnum from $tabb join $tabc on $tabb.buyer_id=$tabc.user_id  where $tabc.$a=$b and $tabb.status =40 ) as numb";
        // $num =0;
        // foreach($totalcount as $k => $v){
        //     $num +=array_sum($v);
        // }
        $sql ="select count(distinct $tabd.getid) as count from $tabd join $tabc on $tabd.getid=$tabc.user_id where $tabc.$a=$b and $tabd.is_pass =1";
        $totalcount =$m->query($sql);
        $totalcount =$totalcount[0]['count'] ? $totalcount[0]['count'] : '0';
        $sql ="select sum($tabd.point) as totalPoint,$tabd.getid,$tabd.getname,$tabc.user_name as phone from $tabd left join $tabc on $tabd.getid=$tabc.user_id where $tabc.$a=$b and $tabd.is_pass=1 group by $tabd.getid order by totalPoint desc limit $startcount,$pagecount";
        $point =$m->query($sql);
        $totalpage =ceil($totalcount/$pagecount);
        foreach ($point as $k => $val) {
            // $val['createtime'] =date('Y-m-d H:i:s', $val['createtime']);
            $val['info'] ='已获得积分';
            $point[$k] =$val;
        }
        
        pageJson('ok',"会员活跃度",$point,$totalpage);


    }
}


?>