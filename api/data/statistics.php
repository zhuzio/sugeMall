<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/8
 * Time: 17:07
 */
//每天统计(收益账单明细、购物账单明细)
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
    //$userslist=$m->table('member')->where('type>1 and last_login>='.$starttime.' and last_login<'.$endtime)->select();
    $userslist=$m->table('member')->where('type>1 and last_login>=1475884800')->select();
    //$userslist=$m->table('member')->where('user_id=11')->select();
    foreach($userslist as $userkey=>$user)
    {
        //统计收益账单明细
        /*if($user['type']==2||$user['type']==3)//商家 销售经理
        {
            //经理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $commission =$m->query($sql);
            foreach($commission as $key=>$val){
                $commission[$key]['info'] = '经理奖励';
                $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $commission[$key]['user_id'] =$user['user_id'];
                $commission[$key]['type'] =$user['type'];
                $data[]=$commission[$key];
            }
            //代理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $agentReward =$m->query($sql);
            foreach($agentReward as $key=>$val){
                $agentReward[$key]['info'] = '代理奖励';
                $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $agentReward[$key]['user_id'] =$user['user_id'];
                $agentReward[$key]['type'] =$user['type'];
                $data[]=$agentReward[$key];
            }

            //商家收益   直推商家
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc ";
            $balance = $m->query($sql);
            if(empty($balance))$balance=array();
            foreach($balance as $key=>$band){
                $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
                $balance[$key]['info'] ='商家收益';
                $balance[$key]['user_id'] =$user['user_id'];
                $balance[$key]['type'] =$user['type'];
                $data[]=$balance[$key];
            }
            if($user['type'] =2){
                //提现  货款提现  收益提现
                $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1))->where('operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
            }
            if($user['type'] >2){
                //提现   收益提现
                $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->where('operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
            }
            foreach ($deposit as $key => $val) {
                $deposit[$key]['createtime'] =date('Y-m-d',$val['operatortime']);
                $deposit[$key]['info'] ='提现';
                unset($deposit[$key]['operatortime']);
                $deposit[$key]['user_id'] =$user['user_id'];
                $deposit[$key]['type'] =$user['type'];
                $data[]=$deposit[$key];
            }
        }else if($user['type']==4)//区域代理
        {
            //  经理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $commission =$m->query($sql);
            foreach($commission as $key=>$val){
                $commission[$key]['info'] = '经理奖励';
                $commission[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);
                $commission[$key]['user_id'] =$user['user_id'];
                $commission[$key]['type'] =$user['type'];
                $data[]=$commission[$key];
            }

            //代理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $agentReward =$m->query($sql);
            foreach($agentReward as $key=>$val){
                $agentReward[$key]['info'] = '代理奖励';
                $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $agentReward[$key]['user_id'] =$user['user_id'];
                $agentReward[$key]['type'] =$user['type'];
                $data[]=$agentReward[$key];
            }

            //商家收益   直推商家
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
            $balance = $m->query($sql);
            if(empty($balance))$balance=array();
            foreach($balance as $key=>$band){
                $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
                $balance[$key]['info'] ='商家收益';
                $balance[$key]['user_id'] =$user['user_id'];
                $balance[$key]['type'] =$user['type'];
                $data[]=$balance[$key];
            }

            //提现   收益提现
            $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->where('operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
            foreach ($deposit as $key => $val) {
                $deposit[$key]['createtime'] =date('Y-m-d',$val['operatortime']);
                $deposit[$key]['info'] ='提现';
                unset($deposit[$key]['operatortime']);
                $deposit[$key]['user_id'] =$user['user_id'];
                $deposit[$key]['type'] =$user['type'];
                $data[]=$deposit[$key];
            }

            //辖区会员收益
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 7 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
            $areaReward = $m->query($sql);
            if(empty($areaReward))$areaReward=array();
            foreach($areaReward as $key=>$band){
                $band['createtime'] = date('Y-m-d ',$band['createtime']);
                $band['info'] ='辖区会员收益';
                $areaReward[$key] =$band;
                $areaReward[$key]['user_id'] =$user['user_id'];
                $areaReward[$key]['type'] =$user['type'];
                $data[]=$areaReward[$key];
            }

            //辖区商家收益  type =5
            $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =5 and user_id=".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
            $shopReward =$m->query($sql);
            foreach ($shopReward as $k => $v) {
                $v['createtime'] =date('Y-m-d',$v['createtime']);
                $v['info'] ='辖区商家收益';
                $shopReward[$k] =$v;
                $shopReward[$k]['user_id'] =$user['user_id'];
                $shopReward[$k]['type'] =$user['type'];
                $data[]=$shopReward[$k];
            }
        }
        else if($user['type']==5)//县级代理
        {
            //  经理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $commission =$m->query($sql);
            foreach($commission as $key=>$val){
                $commission[$key]['info'] = '经理奖励';
                $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $commission[$key]['user_id'] =$user['user_id'];
                $commission[$key]['type'] =$user['type'];
                $data[]=$commission[$key];
            }

            //代理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type > 3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $agentReward =$m->query($sql);
            foreach($agentReward as $key=>$val){
                $agentReward[$key]['info'] = '代理奖励';
                $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $agentReward[$key]['user_id'] =$user['user_id'];
                $agentReward[$key]['type'] =$user['type'];
                $data[]=$agentReward[$key];
            }

            //商家收益   直推商家
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc ";
            $balance = $m->query($sql);
            if(empty($balance))$balance=array();
            foreach($balance as $key=>$band){
                $balance[$key]['createtime'] = date('Y-m-d H:i',$band['createtime']);
                $balance[$key]['info'] ='商家收益';
                $balance[$key]['user_id'] =$user['user_id'];
                $balance[$key]['type'] =$user['type'];
                $data[]=$balance[$key];
            }

            //提现   收益提现
            $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->where('operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
            foreach ($deposit as $key => $val) {
                $deposit[$key]['createtime'] =date('Y-m-d H:i:s',$val['operatortime']);
                $deposit[$key]['info'] ='提现';
                unset($deposit[$key]['operatortime']);
                $deposit[$key]['user_id'] =$user['user_id'];
                $deposit[$key]['type'] =$user['type'];
                $data[]=$deposit[$key];
            }

            //县区会员收益
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 7 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc ";
            $areaReward = $m->query($sql);
            if(empty($areaReward))$areaReward=array();
            foreach($areaReward as $key=>$band){
                $band['money'] = $band['remain_money'];
                $band['createtime'] = date('Y-m-d ',$band['createtime']);
                $band['info'] ='县区会员收益';
                $areaReward[$key] =$band;
                $areaReward[$key]['user_id'] =$user['user_id'];
                $areaReward[$key]['type'] =$user['type'];
                $data[]=$areaReward[$key];
            }

            //县区商家收益
            $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =5 and user_id=".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
            $shopReward =$m->query($sql);
            foreach ($shopReward as $k => $v) {
                $v['createtime'] =date('Y-m-d',$v['createtime']);
                $v['info'] ='县区商家收益';
                $shopReward[$k] =$v;
                $shopReward[$k]['user_id'] =$user['user_id'];
                $shopReward[$k]['type'] =$user['type'];
                $data[]=$shopReward[$k];
            }

            //县代收益
            $sql ="select $taba.remain_money as money,$taba.createtime from $taba left join $tabb on $taba.source_type = $tabb.id where $taba.source_type =6 and $taba.user_id =".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
            $countyReward =$m->query($sql);
            foreach($countyReward as $k=>$v){
                $v['createtime'] =date('Y-m-d ',$v['createtime']);
                $v['info'] ='县代收益';
                $countyReward[$k] =$v;
                $countyReward[$k]['user_id'] =$user['user_id'];
                $countyReward[$k]['type'] =$user['type'];
                $data[]=$countyReward[$k];
            }
        }else if($user['type']==6||$user['type']==7)//市级代理 省代
        {
            //  经理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where toid=".$user['user_id']." and $tabd.type =3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $commission =$m->query($sql);
            foreach($commission as $key=>$val){
                $commission[$key]['info'] = '经理奖励';
                $commission[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $commission[$key]['user_id'] =$user['user_id'];
                $commission[$key]['type'] =$user['type'];
                $data[]=$commission[$key];
            }

            //代理奖励
            $sql ="select $tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.toid=".$user['user_id']." and $tabd.type >3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
            $agentReward =$m->query($sql);
            foreach($agentReward as $key=>$val){
                $agentReward[$key]['info'] = '代理奖励';
                $agentReward[$key]['createtime'] = date('Y-m-d',$val['createtime']);
                $agentReward[$key]['user_id'] =$user['user_id'];
                $agentReward[$key]['type'] =$user['type'];
                $data[]=$agentReward[$key];
            }

            //商家收益   直推商家
            $sql = "select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type = 4 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc ";
            $balance = $m->query($sql);
            if(empty($balance))$balance=array();
            foreach($balance as $key=>$band){
                $balance[$key]['createtime'] = date('Y-m-d',$band['createtime']);
                $balance[$key]['money'] =$band['remain_money'];
                $balance[$key]['info'] ='商家收益';
                unset($balance[$key]['remain_money']);
                $balance[$key]['user_id'] =$user['user_id'];
                $balance[$key]['type'] =$user['type'];
                $data[]=$balance[$key];
            }

            //提现   收益提现
            $deposit =$m->table('sgxt_deposit')->field('money,operatortime')->where(array('userid'=>$user['user_id'],'ispay'=>1,'type'=>2))->where('operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
            foreach ($deposit as $key => $val) {
                $deposit[$key]['createtime'] =date('Y-m-d H:i:s',$val['operatortime']);
                $deposit[$key]['info'] ='收益提现';
                unset($deposit[$key]['operatortime']);
                $deposit[$key]['user_id'] =$user['user_id'];
                $deposit[$key]['type'] =$user['type'];
                $data[]=$deposit[$key];
            }

            //辖区会员收益
            $sql ="select $taba.remain_money as money,$taba.createtime from $taba join $tabb on $taba.source_type = $tabb.id where $taba.source_type =7 and user_id= ".$user['user_id']." and $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc ";
            $areaReward =$m->query($sql);
            if(empty($areaReward))$areaReward=array();
            foreach($areaReward as $key=>$band){
                $band['createtime'] = date('Y-m-d ',$band['createtime']);
                $band['info'] ='辖区会员收益';
                $areaReward[$key] =$band;
                $areaReward[$key]['user_id'] =$user['user_id'];
                $areaReward[$key]['type'] =$user['type'];
                $data[]=$areaReward[$key];
            }
        }*/
        //统计购物账单明细
        /*$shoppingdata=array();
        //会员奖励
        $tabe = DB_PREFIX."sgxt_balance";
        $tabf = DB_PREFIX."sgxt_infotpl";
        if($user['type']>2){
            //会员奖励   购物积分转化 市场补贴
            $sql = "select $tabe.from_username,$tabe.from_userid,$tabe.real_point,$tabe.createtime,$tabf.title from $tabe join $tabf on $tabe.source_type = $tabf.id where $tabe.source_type in (1,2,3) and user_id= ".$user['user_id']." and $tabe.createtime>= ".$starttime." and $tabe.createtime<".$endtime." order by $tabe.id desc ";
        }else{
            $sql = "select $tabe.from_username,$tabe.from_userid,$tabe.real_point,$tabe.createtime,$tabf.title from $tabe join $tabf on $tabe.source_type = $tabf.id where $tabe.source_type in (1,2) and user_id= ".$user['user_id']." and $tabe.createtime>= ".$starttime." and $tabe.createtime<".$endtime." order by $tabe.id desc ";
        }
        $balance = $m->query($sql);
        if(empty($balance))$balance=array();
        foreach($balance as $key=>$val){
            $result=array();
            $result['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $result['point'] = $val['real_point'];
            $result['name'] =$val['title'];
            $result['user_id']=$user['user_id'];
            $result['type']=$user['type'];
            $result['char']='+';
            $shoppingdata[]=$result;
        }
        //消费积分 --线上 线下 +
        $pointList =$m->table('sgxt_get_point')->field('sendid,point,createtime')->where('getid='.$user['user_id'].' and createtime>= '.$starttime.' and createtime<'.$endtime)->select();
        foreach($pointList as $k=>$v){
            $shopname =$m->table('store')->field('store_name')->where('store_id='.$v['sendid'])->find();
            $result=array();
            $result['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $result['point'] = $v['point'];
            $result['name'] =$shopname['store_name'];
            $result['user_id']=$user['user_id'];
            $result['type']=$user['type'];
            $result['char']='+';
            $shoppingdata[]=$result;
        }

//购物积分 消费 -
        $sql ="select money,to_id,add_time,payment_id from ecm_paymentlog where user_id=".$user['user_id']." and add_time>= ".$starttime." and add_time<".$endtime." and payment_id in (1,3) order by add_time desc ";
        $res =$m->query($sql);
        foreach ($res as $key => $value) {
            $shopname =$m->table('store')->field('store_name')->where('store_id='.$value['to_id'])->find();
            $result=array();
            $result['createtime'] = date('Y-m-d H:i:s',$value['add_time']);
            $result['point'] = $value['money'];
            $result['name'] =$shopname['store_name'];
            $result['user_id']=$user['user_id'];
            $result['type']=$user['type'];
            $result['char']='-';
            $shoppingdata[]=$result;
        }*/
        //辖区商家收益（区代县代）
        /*if($user['type']==4||$user['type']==5)
        {
            $shopincome=array();
            //当前商家数
            if($user['type']==4){
                $shopslist=$m->table('member')->field('user_id')->where(array('opid'=>$user['user_id'],'type'=>2))->select();
            }else{
                //当前商家数
                $shopslist=$m->table('member')->field('user_id')->where(array('area'=>$user['ahentarea'],'type'=>2))->select();
            }
            $areashoparray=conf('user_reward');
            $areashop=$areashoparray[$user['type']]['area_shops'];
            foreach($shopslist as $key=>$value){
                //每个商家的信息（根据商家查询其收益）
                $orderonline=$m->table('order')->field('order_id,order_sn,point,is_check,add_time')->where('seller_id='.$value['user_id'].' and add_time>='.$starttime.' and add_time<'.$endtime)->select();
                foreach($orderonline as $k => $v){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$v['point']*0.3*$areashop;
                    //显示每个订单的商家信息
                    $shopsinfo=getclassnamebyorderid($v['order_id'],$value['user_id']);
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['is_check']=$v['is_check'];
                    $shopsinfo['add_time']=$v['add_time'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopincome[]=$shopsinfo;
                }
                //线下
                $sql='select ecm_order_offline.order_id,ecm_order_offline.order_sn,ecm_order_offline.point,ecm_order_offline.is_check,ecm_order_offline.add_time,ecm_order_offline.classname,ecm_store.store_name from ecm_order_offline join ecm_store on ecm_store.store_id=ecm_order_offline.seller_id where ecm_order_offline.seller_id='.$value['user_id'].' and ecm_order_offline.add_time>='.$starttime.' and ecm_order_offline.add_time<'.$endtime;
                $orderoffline=$m->query($sql);
                foreach($orderoffline as $koffline => $voffline){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$voffline['point']*0.3*$areashop;
                    //显示每个订单的商家信息
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['is_check']=$voffline['is_check'];
                    $shopsinfo['add_time']=$voffline['add_time'];
                    $shopsinfo['classname']=$voffline['classname'];
                    $shopsinfo['store_name']=$voffline['store_name'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopincome[]=$shopsinfo;
                }
            }
        }*/
        //辖区会员收益（区代县代市代省代）
        if($user['type']>3)
        {
            $memberincome=array();
            //当前商家数
            if($user['type']==4){
                $userslist=$m->table('member')->field('user_id,user_name,real_name')->where(array('opid'=>$user['user_id']))->select();
            }else{
                //当前商家数
                $userslist=$m->table('member')->field('user_id,user_name,real_name')->where(array('area'=>$user['ahentarea']))->select();
            }
            $areauserarray=conf('user_reward');
            $areauser=$areauserarray[$user['type']]['area_users'];
            foreach($userslist as $key=>$value){
                //每个会员的信息（根据会员查询其收益）
                $orderonline=$m->table('order')->field('order_id,seller_id,order_sn,point,is_check,add_time')->where('buyer_id='.$value['user_id'].' and add_time>='.$starttime.' and add_time<'.$endtime)->select();
                foreach($orderonline as $k => $v){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$v['point']*$areauser;
                    //显示每个订单的会员信息
                    $shopsinfo=getclassnamebyorderid($v['order_id'],$v['seller_id']);
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['is_check']=$v['is_check'];
                    $shopsinfo['add_time']=$v['add_time'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopsinfo['user_name']=$value['real_name'];
                    $shopsinfo['phone']=$value['user_name'];
                    $memberincome[]=$shopsinfo;
                }
                //线下
                $sql='select ecm_order_offline.order_id,ecm_order_offline.order_sn,ecm_order_offline.point,ecm_order_offline.is_check,ecm_order_offline.add_time,ecm_order_offline.classname,ecm_store.store_name from ecm_order_offline join ecm_store on ecm_store.store_id=ecm_order_offline.seller_id where ecm_order_offline.buyer_id='.$value['user_id'].' and ecm_order_offline.add_time>='.$starttime.' and ecm_order_offline.add_time<'.$endtime;
                $orderoffline=$m->query($sql);
                foreach($orderoffline as $koffline => $voffline){
                    $shopsinfo=array();
                    //计算每个订单的收益
                    $earning=$voffline['point']*$areauser;
                    //显示每个订单的商家信息
                    $shopsinfo['earning']=$earning;
                    $shopsinfo['is_check']=$voffline['is_check'];
                    $shopsinfo['add_time']=$voffline['add_time'];
                    $shopsinfo['classname']=$voffline['classname'];
                    $shopsinfo['store_name']=$voffline['store_name'];
                    $shopsinfo['user_id']=$user['user_id'];
                    $shopsinfo['user_name']=$value['real_name'];
                    $shopsinfo['phone']=$value['user_name'];
                    $memberincome[]=$shopsinfo;
                }
            }
        }
    }
    //插入数据
    /*foreach($data as $k=>$v)
    {
        $m->table('account_profit')->insert(array('user_id'=>$v['user_id'],'type'=>$v['type'],'info'=>$v['info'],'createtime'=>$v['createtime'],'money'=>$v['money']));
    }
    foreach($shoppingdata as $k=>$v)
    {
        $m->table('bill_statistics')->insert(array('user_id'=>$v['user_id'],'type'=>$v['type'],'point'=>$v['point'],'createtime'=>$v['createtime'],'name'=>$v['name'],'char'=>$v['char']));
    }
    foreach($shopincome as $k=>$v)
    {
        $m->table('shopincome')->insert(array('user_id'=>$v['user_id'],'earning'=>$v['earning'],'is_check'=>$v['is_check'],'add_time'=>$v['add_time'],'classname'=>$v['classname'],'store_name'=>$v['store_name']));
    }*/
    foreach($memberincome as $k=>$v)
    {
        $m->table('memberincome')->insert(array('user_id'=>$v['user_id'],'earning'=>$v['earning'],'is_check'=>$v['is_check'],'add_time'=>$v['add_time'],'classname'=>$v['classname'],'user_name'=>$v['user_name'],'phone'=>$v['phone']));
    }
    fk('ok');
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