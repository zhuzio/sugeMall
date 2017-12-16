<?php
//检查每日定时执行是否异常
function moneyauto()
{
    $model=new M();
    $balance=$model->table('sgxt_balance')->field('createtime')->where(array('source_type'=>3))->order('id desc')->find();
    $check2=true;
    if(date('d')==15)
    {
        $month=intval(date('m'))-1;
        $year=intval(date('Y'));
        if($month==0)
        {
            $year=$year-1;
            $month=12;
        }
        $daytime=$year.$month;
        //检查购物积分解冻
        $balanceinfo=$model->table('sgxt_balance')->where(array('times'=>$daytime,'is_clearing'=>0))->select();
        if(empty($balanceinfo) || count($balanceinfo)<100)
        {
            file_put_contents('balance.txt',$balanceinfo['times'].'-15号解冻正常！'."\n",FILE_APPEND);
        }else
        {
            file_put_contents('balance.txt',$balanceinfo['times'].'-15号解冻异常，请检查！'."\n",FILE_APPEND);
            $check2=false;
        }
        //检查收益解冻
        $profitinfo=$model->table('sgxt_profit')->where(array('times'=>$daytime,'is_clearing'=>0))->select();
        if(empty($profitinfo) || count($profitinfo)<100)
        {
            file_put_contents('profit.txt',$profitinfo['times'].'-15号解冻正常！'."\n",FILE_APPEND);
        }else{
            file_put_contents('profit.txt',$profitinfo['times'].'-15号解冻异常！'."\n",FILE_APPEND);
            $check2=false;
        }
    }
    if(date('Y-m-d',$balance['createtime'])==date('Y-m-d') && $check2)
    {
        file_put_contents('moneyauto.txt',date('Y-m-d H:i:s',$balance['createtime']).'定时执行正常！'.date('Y-m-d H:i:s')."\n",FILE_APPEND);
        fk('ok');
    }
    else
    {
        file_put_contents('moneyauto.txt',date('Y-m-d H:i:s',$balance['createtime']).'定时执行异常，发送短信！'.date('Y-m-d H:i:s')."\n",FILE_APPEND);
        err('fail');
    }
}