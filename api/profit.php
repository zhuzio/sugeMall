<?php

//按月份统计商家收益
function shopEaringMonth()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select user_id,sum(remain_money) as money,times,is_clearing from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type=4 group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        if($val['is_clearing'] == 1){
            $val['is_clearing_cn'] = '已转化';
        }else if($val['is_clearing'] == 0){
            $val['is_clearing_cn'] = '冻结中';
        }
        $new[$val['times']] = $val;
        $list[$key] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val,'is_clearing'=>1,'is_clearing_cn'=>'已转化');
        }
    }
    krsort($new);
    $i = 0;
    $total=0;
    foreach($new as $key=>$val){
        if($i==0){
            $val['is_clearing'] = 0;
            $val['is_clearing_cn'] = '冻结中';
        }
        $rs[] = $val;
        $i++;
        $total=$total+$val['money'];
    }
    outputJson('ok','商家收益月统计',$rs,0,$total);
}
//商家收益明细
function shopEaringMonth_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $time=$_POST['time'];
    $model = new M();
    $totalcount=$model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=4 and times='.$time)->count();
    //根据订单查询classname
    $list = $model->query('select from_username,remain_money as money,real_point,createtime,ecm_sgxt_infotpl.content from ecm_sgxt_profit INNER join ecm_sgxt_infotpl on ecm_sgxt_profit.source_type=ecm_sgxt_infotpl.id where user_id='.$user['user_id'].' and source_type=4 and ecm_sgxt_profit.times='.$time.' order by createtime desc ');
    foreach($list as $key=>$val){
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $rep['name'] = $val['from_username'];
        $rep['point'] = $val['real_point']*conf('PAY_INFO/shops_point');
        $rep['money'] = $val['money'];
        $list[$key]['content'] = replace_tpl($rep , $val['content']);
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"商家收益明细",$list,$totalpage);
}
//按月份统计代理收益
function commissionMonth()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select toid as user_id,sum(money) as money,FROM_UNIXTIME(createtime,\'%Y%m\') times from ecm_sgxt_commission where toid='.$user['user_id'].' group by times');
    $new = array();
    foreach($list as $key=>$val){
        $new[$val['times']] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val);
        }
    }
    krsort($new);
    $total = 0;
    foreach($new as $key=>$val){
        $rs[] = $val;
        $total=$total+$val['money'];
    }
    outputJson('ok','代理收益月统计',$rs,0,$total);
}
//代理收益明细
function commissionMonth_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $time=$_POST['time'];
    $year=substr($time,0,4);
    $month=substr($time,4,2);
    $day=$year.'-'.$month.'-01';
    $starttime=strtotime($day);
    $nextmonthtime=strtotime($day.'+1 month');
    $model = new M();
    $totalcount=$model->table('sgxt_commission')->where('toid='.$user['user_id'].'and createtime>='.$starttime.' and createtime<'.$nextmonthtime)->count();
    $user_type=conf('user_type');
    //根据订单查询classname
    $list = $model->query('select fromid,money,createtime from ecm_sgxt_commission where toid='.$user['user_id'].' and createtime>='.$starttime.' and createtime<'.$nextmonthtime.' order by createtime desc limit '.$startcount.','.$pagecount);
    foreach($list as $key=>$val){
        //手机号码
        $userinfo=$model->table('member')->field('type,user_name,real_name')->where(array('user_id'=>$val['fromid']))->find();
        $list[$key]['from_username']=$userinfo['user_name'];
        $list[$key]['from_real_name']=$userinfo['real_name'];
        $list[$key]['type']=$user_type[$userinfo['type']];
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"代理收益明细",$list,$totalpage);
}


//收益账单月统计
function profitMonth()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    //收益
    $list = $model->query('select user_id,sum(remain_money) as money,times from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type in (4,5,6,7) and is_clearing=1 group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        $new[$val['times']] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val);
        }
    }
    krsort($new);
    $total=0;
    foreach($new as $key=>$val){
        //添加每月的代理奖励
        $comlist = $model->query('select toid,sum(money) as money,FROM_UNIXTIME(createtime,\'%Y%m\') times from ecm_sgxt_commission where toid='.$user['user_id'].' group by times having(times='.$val['times'].')');
        $val['money']=floatval($val['money'])+floatval($comlist[0]['money']);
        //添加收益提现
        $delist = $model->query('select userid,sum(money) as money,FROM_UNIXTIME(operatortime,\'%Y%m\') times from ecm_sgxt_deposit where userid='.$user['user_id'].' and ispay=1 and type=2 group by times having(times='.$val['times'].')');
        $val['money']=floatval($val['money'])-floatval($delist[0]['money']);
        $rs[] = $val;
        $total=$total+floatval($val['money']);
    }
	//$total=round($total,2);
	$total=number_format($total, 2, '.', '');
	$fp=fopen('test0107.txt','w');
	fwrite($fp,$total);
	fclose($fp);
    outputJson('ok','收益账单月统计',$rs,0,(float)$total);
}
//收益明细：
function profitMonth_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $time=$_POST['time'];
    $model = new M();
    $list = $model->query('select from_username,order_sn,remain_money as money,createtime,source_type as type,ecm_sgxt_infotpl.title from ecm_sgxt_profit INNER join ecm_sgxt_infotpl on ecm_sgxt_profit.source_type=ecm_sgxt_infotpl.id where user_id='.$user['user_id'].' and is_clearing=1 and source_type in (4,5,6,7) and ecm_sgxt_profit.times='.$time.' order by createtime desc ');
    foreach($list as $key=>$val){
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
    }
    //代理奖励
    $user_type=conf('user_type');
    $comlist = $model->query('select fromid,money,createtime,FROM_UNIXTIME(createtime,\'%Y%m\') times from ecm_sgxt_commission where toid='.$user['user_id'].' having(times='.$time.')');
    foreach($comlist as $key=>$val){
        $userinfo =$model->table('member')->field('real_name,type')->where(array('user_id'=>$val['fromid']))->find();
        $val['from_username']='您推荐'.$userinfo['real_name'].'注册为'.$user_type[$userinfo['type']].'为您增加收益';
        $val['order_sn']='';
        $val['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $val['type']=8;
        $val['title']='代理奖励';
        unset($val['fromid']);
        unset($val['times']);
        $list[]=$val;
    }
    //收益提现
    $delist = $model->query('select userid,money,operatortime as createtime,FROM_UNIXTIME(operatortime,\'%Y%m\') times from ecm_sgxt_deposit where userid='.$user['user_id'].' and ispay=1 and type=2  having(times='.$time.')');
    foreach($delist as $key=>$val){
        $val['from_username']='';
        $val['order_sn']='';
        $val['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $val['type']=9;
        $val['title']='收益提现';
        unset($val['userid']);
        unset($val['times']);
        $list[]=$val;
    }
    fk('ok',$list);
}

//购物积分账单月统计
function billMonth()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    //转化的
    $list = $model->query('select user_id,sum(get_money) as money,times from ecm_sgxt_balance where user_id='.$user['user_id'].' and source_type in (1,2,3) and is_clearing=1 group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        $new[$val['times']] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val);
        }
    }
    krsort($new);
    $total=0;
    foreach($new as $key=>$val){
        //添加该月支付的
        $list = $model->query('select user_id,sum(money) as money,FROM_UNIXTIME(add_time,\'%Y%m\') times from ecm_paymentlog where payment_id=3 and user_id='.$user['user_id'].' group by times having(times='.$val['times'].')');
        $val['money']=floatval($val['money'])-floatval($list[0]['money']);
        $rs[] = $val;
        $total=$total+$val['money'];
    }
    outputJson('ok','购物积分月统计',$rs,0,$total);
}
//购物积分明细：
function billMonth_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $time=$_POST['time'];
    $model = new M();
    $list = $model->query('select from_username,order_sn,get_money as money,createtime,source_type as type,happiness,ecm_sgxt_infotpl.title from ecm_sgxt_balance INNER join ecm_sgxt_infotpl on ecm_sgxt_balance.source_type=ecm_sgxt_infotpl.id where user_id='.$user['user_id'].' and source_type in (1,2,3) and is_clearing=1 and ecm_sgxt_balance.times='.$time.' order by createtime desc ');
    foreach($list as $key=>$val){
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $list[$key]['ordertime']=$val['createtime'];
    }
    //支付记录
    $paylist = $model->query('select to_name as from_username,order_sn,money,add_time as createtime,FROM_UNIXTIME(add_time,\'%Y%m\') times from ecm_paymentlog where payment_id=3 and user_id='.$user['user_id'].' having(times='.$time.') order by add_time desc ');
    foreach($paylist as $key=>$val){
        $val['money']=$val['money'];
        $val['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $val['ordertime']=$val['createtime'];
        $val['type']=9;
        $val['happiness']=0;
        $val['title']='购物积分支付';
        $list[]=$val;
    }
    multi_array_sort($list,'ordertime');
    fk('ok',$list);
}
function multi_array_sort($multi_array,$sort_key,$sort=SORT_DESC){
    if(is_array($multi_array)){
        foreach ($multi_array as $row_array){
            if(is_array($row_array)){
                $key_array[] = $row_array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_array,$sort,$multi_array);
    return $multi_array;
}
//获赠积分月统计
function userMonthPoint()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select sum(point) as money,FROM_UNIXTIME(createtime,\'%Y%m\') times from ecm_sgxt_get_point where getid='.$user['user_id'].' and is_pass in (0,1) group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        $new[$val['times']] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('money'=>0,'times'=>$val);
        }
    }
    krsort($new);
    $total = 0;
    foreach($new as $key=>$val){
        $rs[] = $val;
        $total=$total+$val['money'];
    }
    outputJson('ok','获赠积分月统计',$rs,0,$total);
}
//获赠积分明细
function userMonthPoint_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $time=$_POST['time'];
    $year=substr($time,0,4);
    $month=substr($time,4,2);
    $day=$year.'-'.$month.'-01';
    $starttime=strtotime($day);
    $nextmonthtime=strtotime($day.'+1 month');
    $model = new M();
    $totalcount=$model->table('sgxt_get_point')->where('getid='.$user['user_id'].' and  createtime>='.$starttime.' and createtime<'.$nextmonthtime)->count();
    //根据订单查询classname
    $list = $model->query('select order_id,point,getid,sendid,sendname,createtime,oto from ecm_sgxt_get_point where getid='.$user['user_id'].' and is_pass in (0,1) and createtime>='.$starttime.' and createtime<'.$nextmonthtime.' order by createtime desc limit '.$startcount.','.$pagecount);
    $tablearray=array('online'=>'order','offline'=>'order_offline');
    foreach($list as $key=>$val){
        $order_sn =$model->table($tablearray[$val['oto']])->where(array('order_id'=>$val['order_id']))->getField('order_sn');
        $list[$key]['order_sn'] =$order_sn;
        //手机号码
        $user_name =$model->table('member')->where(array('user_id'=>$val['sendid']))->getField('user_name');
        $list[$key]['user_name'] =$user_name;
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"赠送积分明细",$list,$totalpage);
}

//按月份统计支付记录
function userMonthPayment()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select user_id,sum(money) as money,FROM_UNIXTIME(add_time,\'%Y%m\') times from ecm_paymentlog where payment_id=3 and user_id='.$user['user_id'].' group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        $new[$val['times']] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val);
        }
    }
    krsort($new);
    $total = 0;
    foreach($new as $key=>$val){
        $rs[] = $val;
        $total=$total+$val['money'];
    }
    outputJson('ok','支付记录月统计',$rs,0,$total);
}
//支付记录月明细
function userMonthPayment_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $time=$_POST['time'];
    $year=substr($time,0,4);
    $month=substr($time,4,2);
    $day=$year.'-'.$month.'-01';
    $starttime=strtotime($day);
    $nextmonthtime=strtotime($day.'+1 month');
    $model = new M();
    $totalcount=$model->table('paymentlog')->where('user_id='.$user['user_id'].' and payment_id=3 and add_time>='.$starttime.' and add_time<'.$nextmonthtime)->count();
    //根据订单查询classname
    $list = $model->query('select money,to_name,order_sn,add_time from ecm_paymentlog where payment_id=3 and user_id='.$user['user_id'].' and add_time>='.$starttime.' and add_time<'.$nextmonthtime.' order by add_time desc limit '.$startcount.','.$pagecount);
    foreach($list as $key=>$val){
        $list[$key]['add_time']=date('Y-m-d H:i:s',$val['add_time']);
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"支付明细",$list,$totalpage);
}

//按月份统计转化记录
function userMonthConvert()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select user_id,sum(get_money) as money,times,is_clearing from ecm_sgxt_balance where user_id='.$user['user_id'].' and source_type in (1,2,3) and is_clearing=1 group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        if($val['is_clearing'] == 1){
            $val['is_clearing_cn'] = '已转化';
        }else if($val['is_clearing'] == 0){
            $val['is_clearing_cn'] = '冻结中';
        }
        $new[$val['times']] = $val;
        $list[$key] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val,'is_clearing'=>1,'is_clearing_cn'=>'已转化');
        }
    }
    krsort($new);
    $i = 0;
    $total=0;
    foreach($new as $key=>$val){
        /*if($i==0){
            $val['is_clearing'] = 0;
            $val['is_clearing_cn'] = '冻结中';
        }*/
        $rs[] = $val;
        //$i++;
        $total=$total+$val['money'];
    }
    outputJson('ok','转化记录月统计',$rs,0,$total);
}
//转化记录明细
function userMonthConvert_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $time=$_POST['time'];
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $model = new M();
    $totalcount=$model->table('sgxt_balance')->where('user_id='.$user['user_id'].' and source_type in (1,2,3) and is_clearing=1 and times='.$time)->count();
    //根据订单查询classname
    $list = $model->query('select from_username,order_sn,order_type,from_userid,get_money,happiness,createtime,ecm_sgxt_infotpl.title from ecm_sgxt_balance INNER join ecm_sgxt_infotpl on ecm_sgxt_balance.source_type=ecm_sgxt_infotpl.id where user_id='.$user['user_id'].' and source_type in (1,2,3) and is_clearing=1 and ecm_sgxt_balance.times='.$time.' order by createtime desc limit '.$startcount.','.$pagecount);
    foreach($list as $key=>$val){
        $phone =$model->table('member')->field('user_name')->where('user_id='.$val['from_userid'])->find();
        $list[$key]['phone'] =$phone['user_name']; //手机号
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"转化记录明细",$list,$totalpage);
}
//按月份统计会员奖励
function userMonthBalance()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);
    $model = new M();
    $list = $model->query('select user_id,sum(get_money) as money,times,is_clearing from ecm_sgxt_balance where user_id='.$user['user_id'].' and source_type=1 group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        if($val['is_clearing'] == 1){
            $val['is_clearing_cn'] = '已转化';
        }else if($val['is_clearing'] == 0){
            $val['is_clearing_cn'] = '冻结中';
        }
        $new[$val['times']] = $val;
        $list[$key] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val,'is_clearing'=>1,'is_clearing_cn'=>'已转化');
        }
    }
    krsort($new);
    $i = 0;
    $total=0;
    foreach($new as $key=>$val){
        if($i==0){
            $val['is_clearing'] = 0;
            $val['is_clearing_cn'] = '冻结中';
        }
        $rs[] = $val;
        $i++;
        $total=$total+$val['money'];
    }
    outputJson('ok','会员奖励月统计',$rs,0,$total);
}
//辖区收益统计
function xq_earning()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $dates = monthList(strtotime('2016-07-01'),time());
    $dates = array_reverse($dates);    
    $type=$_POST['type'];//type=5 or type=7
    $model = new M();
    $list = $model->query('select user_id,sum(remain_money) as money,times,is_clearing from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type='.$type.' group by times order by times desc');
    $new = array();
    foreach($list as $key=>$val){
        if($val['is_clearing'] == 1){
            $val['is_clearing_cn'] = '已转化';
        }else if($val['is_clearing'] == 0){
            $val['is_clearing_cn'] = '冻结中';
        }
        $new[$val['times']] = $val;
        $list[$key] = $val;
    }
    $rs = array();
    foreach($dates as $val){
        if(!array_key_exists($val, $new)){
            $new[$val] = array('user_id'=>$user['user_id'],'money'=>0,'times'=>$val,'is_clearing'=>1,'is_clearing_cn'=>'已转化');            
        }
    }
    krsort($new);
    $i = 0;
    $total=0;
    foreach($new as $key=>$val){
        if($i==0){
            $val['is_clearing'] = 0;
            $val['is_clearing_cn'] = '冻结中';
        }
        $rs[] = $val;
        $i++;
        $total=$total+$val['money'];
    }
    outputJson('ok','辖区收益月统计',$rs,0,$total);
}
function monthList($start,$end){
    if(!is_numeric($start)||!is_numeric($end)||($end<=$start)) return '';
    $start=date('Y-m',$start);
    $end=date('Y-m',$end);
    //转为时间戳
    $start=strtotime($start.'-01');
    $end=strtotime($end.'-01');
    $i=0;//http://www.phpernote.com/php-function/224.html
    $d=array();
    while($start<=$end){
        //这里累加每个月的的总秒数 计算公式：上一月1号的时间戳秒数减去当前月的时间戳秒数
        $d[$i]=trim(date('Ym',$start),' ');
        $start+=strtotime('+1 month',$start)-$start;
        $i++;
    } 
    return $d;
}
//辖区收益明细
function xq_earning_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $type=$_POST['type'];//type=5 or type=7
    $time=$_POST['time'];
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $model = new M();
    $totalcount=$model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type='.$type.' and times='.$time)->count();
    $tablearray=array('online'=>'order','offline'=>'order_offline');
    //根据订单查询classname
    $list = $model->query('select user_id,from_userid,from_username as xq_real_name,remain_money as money,order_sn,order_type,createtime from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type='.$type.' and times='.$time.' order by createtime desc limit '.$startcount.','.$pagecount);
    foreach($list as $key=>$val){
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
        $store_name=$model->table('store')->where(array('store_id'=>$val['from_userid']))->getField('store_name');
        $list[$key]['store_name']=$store_name;
        if(!empty($val['order_type']))
        {
            $orderinfo =$model->table($tablearray[$val['order_type']])->where(array('order_sn'=>$val['order_sn']))->find();
            if($val['order_type']=='offline')
            {
                $list[$key]['classname']=$orderinfo['classname'];
            }
            else
            {
                $goodsinfo =$model->table('order_goods')->where(array('order_id'=>$orderinfo['order_id']))->find();
                //根据goods_id查询商品类别名称
                $sql='select ecm_gcategory.cate_name from ecm_category_goods join ecm_gcategory on ecm_category_goods.cate_id=ecm_gcategory.cate_id where ecm_category_goods.goods_id='.$goodsinfo['goods_id'];
                $namelist =$model->query($sql);
                $list[$key]['classname']=$namelist[0]['cate_name'];
            }
        }else
        {
            $list[$key]['order_sn']='';
            $list[$key]['order_type']='';
            $list[$key]['classname']='';
        }
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"收益明细",$list,$totalpage);
}
//会员奖励详情
function userMonth_info()
{
    $token = urlencode($_POST['token']);
    $user =checkToken($token);
    if(!$user){
        err('身份错误，请重新登录');
    }
    $time=$_POST['time'];
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $model = new M();
    $totalcount=$model->table('sgxt_balance')->where('user_id='.$user['user_id'].' and source_type=1 and times='.$time)->count();
    //根据订单查询classname
    $list = $model->query('select from_username,order_sn,order_type,from_userid,get_money,createtime from ecm_sgxt_balance where user_id='.$user['user_id'].' and source_type=1 and times='.$time.' order by createtime desc limit '.$startcount.','.$pagecount);
    foreach($list as $key=>$val){
        $phone =$model->table('member')->field('user_name')->where('user_id='.$val['from_userid'])->find();
        $list[$key]['phone'] =$phone['user_name']; //手机号
        $list[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);		if(empty($val['order_sn']))        {            $list[$key]['order_sn']='';        }        if(empty($val['order_type']))        {            $list[$key]['order_type']='';        }
    }
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"会员奖励明细",$list,$totalpage);
}