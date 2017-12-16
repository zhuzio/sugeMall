<?php
header("Content-type:text/html;charset=utf-8");
/* 会员 member */
ini_set('max_execution_time',0);
class MonthStatisticsApp extends FrontendApp{
    //执行全部数据
    public function all()
    {
        echo '开始时间：'.date('Y-m-d H:i:s')."\n";
        $m = & m();
        $sql='delete from ecm_sgxt_report;';
        $m->query($sql);
        $sttime=strtotime('2016-05-01');
        $edtime=strtotime(date('Y-m-d '));
        $month[]='2016-05-01';
        $i=$sttime;
        while($i<=$edtime) {
            $day=date('Y-m-01',$i);
            $nextmonthtime=strtotime($day.'+1 month');
            $i=$nextmonthtime;
            $month[]=date('Y-m-01', $nextmonthtime);
        }
        foreach($month as $k=>$v)
        {
            $starttime=strtotime(date('Y-m-d', strtotime($v.'-1 month')));
            $endtime=strtotime(date($v));
            $this->searchdata($m,$starttime,$endtime);
        }
        echo 'ok'."\n";
        echo '结束时间：'.date('Y-m-d H:i:s');
    }

    //统计报表（每月统计一次【月份，区域，总赠送积分，总货款积分，总商家数、活跃商家、总会员数、总活跃会员】）
    public function reportStatistics()
    {
        echo '开始时间：'.date('Y-m-d H:i:s')."\n";
        $m = & m();
        $day=isset($_REQUEST['time']) ? $_REQUEST['time'] :date('Y-m-d');
        $time=date('Y-m-01',strtotime($day));
        //统计当月
		$starttime=strtotime($time);
        //$starttime=strtotime(date('Y-m-01', strtotime('-1 month')));
        $endtime=strtotime(date('Y-m-01',strtotime($time.'+1 month')));
        //删除数据
        $sql='delete from ecm_sgxt_report where times>='.date('Ym',$starttime);
        $m->query($sql);
        $this->searchdata($m,$starttime,$endtime);
        echo 'ok'."\n";
        echo '结束时间：'.date('Y-m-d H:i:s');
    }
    public function searchdata($m,$starttime,$endtime)
    {
        //县代区域
        $query=$m->table('member')->where(array('type'=>5))->select();
        foreach($query as $key=>$user)
        {
            //区域
            $area='';
            $condition="id in ({$user['province']},{$user['city']},{$user['area']})";
            $query=$m->table('sgxt_area')->field('name')->where($condition)->select();
            foreach($query as $k=>$v)
            {
                $area.=$v['name'];
            }
            $data=array();
            $data['times']=date('Ym',$starttime);
            $data['area']=$area;
            $data['user_id']=$user['user_id'];
            $data['ahentarea']=$user['ahentarea'];
            //统计当月
            //县代商家总赠送积分(查询县代下所有商家，然后查询所有商家赠送的积分)
            $useridstring='';
            $idlist=$m->table('member')->field('user_id')->where('area='.$user['ahentarea'].' and type=2 and last_login>='.$starttime.' and last_login<'.$endtime)->select();
            if(empty($idlist))$idlist=array();
            foreach($idlist as $key =>$value)
            {
                $useridstring.=$value['user_id'].',';
            }
            $useridstring=substr($useridstring , 0 , strlen($useridstring)-1);

            if(!empty($useridstring))
            {
                //总赠送积分
                $sql="select sum(shops_point) as totalshopspoint from ecm_sgxt_get_point where sendid in ({$useridstring}) and createtime>={$starttime} and createtime<{$endtime}";
                $totalshopspoint =$m->query($sql);
                if(!empty($totalshopspoint[0]['totalshopspoint']))
                {
                    $data['totalgivepoint']=$totalshopspoint[0]['totalshopspoint'];
                }
                else
                {
                    $data['totalgivepoint']=0;
                }

                //总货款积分(货款提现)
                $sql="select sum(money) as totalmoney from ecm_sgxt_deposit where ispay=1 and type=1 and operatortime>={$starttime} and pay_time<{$endtime} and userid in ({$useridstring})";
                $totalpoint =$m->query($sql);
                if(!empty($totalpoint[0]['totalmoney']))
                {
                    $data['totalpaymentpoint']=$totalpoint[0]['totalmoney'];
                }
                else
                {
                    $data['totalpaymentpoint']=0;
                }


                //活跃商家
                $sql="select count(DISTINCT sendid) as activeshopscount from ecm_sgxt_get_point where sendid in ({$useridstring}) and createtime>={$starttime} and createtime<{$endtime}";
                $activeshops =$m->query($sql);
                if(!empty($activeshops[0]['activeshopscount']))
                {
                    $data['activeshopscount']=$activeshops[0]['activeshopscount'];
                }
                else
                {
                    $data['activeshopscount']=0;
                }

            }
            else
            {
                $data['totalgivepoint']=0;
                $data['totalpaymentpoint']=0;
                $data['activeshopscount']=0;
            }


            //总商家数
            $data['totalshopscount']=count($idlist);

            //总会员数
            $userscount=$m->table('member')->where('area='.$user['ahentarea'].' and status=1 and last_login>='.$starttime.' and last_login<'.$endtime)->count('distinct user_id');
            $data['usercount']=$userscount;
            //总活跃会员
            $sql ='select count(distinct ecm_sgxt_profit.from_userid) as activeusercount from ecm_sgxt_profit left join ecm_member on ecm_sgxt_profit.from_userid = ecm_member.user_id where ecm_member.area='.$user['ahentarea'].' and ecm_member.status=1 and ecm_sgxt_profit.source_type = 7 and createtime>='.$starttime.' and createtime<'.$endtime;
            $activeusercount =$m->query($sql);
            if(!empty($activeusercount[0]['activeusercount']))
            {
                $data['activeusercount']=$activeusercount[0]['activeusercount'];
            }else
            {
                $data['activeusercount']=0;
            }

            //总辖区收益
            $sql='select sum(remain_money) as total_earning from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type in(5,7) and createtime>='.$starttime.' and createtime<'.$endtime;
            $total_earning =$m->query($sql);
            if(!empty($total_earning[0]['total_earning']))
            {
                $data['total_earning']=$total_earning[0]['total_earning'];
            }else
            {
                $data['total_earning']=0;
            }
            //添加到记录表
            if(!empty($data))
            {
                $m->table('sgxt_report')->add($data);
            }
        }
    }
}




?>