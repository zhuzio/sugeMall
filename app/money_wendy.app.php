<?php 
/* 会员 member */
ini_set('max_execution_time',0);
class money_wendyApp extends FrontendApp{
    public function wendyMoney(){
        $m =& m();
        $sql='select user_name,earnings from ecm_epay where earnings>=200 order by earnings desc';
        $users_earnings=$m->query($sql);
        $fp=fopen('kt.txt','w');
        $total=0;
        foreach($users_earnings as $key=>$value)
        {
            $total=$total+$value['earnings'];
            fwrite($fp,$value['user_name'].'可提现收益：'.$value['earnings']."\n");
        }
        fwrite($fp,'可提现收益累计：'.$total."\n");
        fclose($fp);
        echo 'ok';
        //查询12月之前所有用户的总收益
        /*$sql='select user_id,sum(remain_money) as money from ecm_sgxt_profit where times<201612 group by user_id;';
        $users_earnings=$m->query($sql);
        foreach($users_earnings as $key=>$value)
        {
            //总收益
            $totalmoney=0;
            if(!empty($value['money']))
            {
                $totalmoney=$value['money'];
            }
            //佣金
            $sql='select sum(money) as money from ecm_sgxt_commission where toid='.$value['user_id'];
            $commission=$m->query($sql);
            if(!empty($commission))
            {
                $totalmoney=$totalmoney+$commission[0]['money'];
            }
            //查询已提现的收益
            $sql='select sum(money) as money from ecm_sgxt_deposit where userid='.$value['user_id'].' and type=2 and ispay=1;';
            $yitixian=$m->query($sql);
            $yitixian_money=0;
            if(!empty($yitixian))
            {
                $yitixian_money=$yitixian[0]['money'];
            }
            //当前可提现的收益
            $ketixian_money=$totalmoney-$yitixian_money;
            //查询12月中冻结中的收益
            $sql='select sum(remain_money) as money from ecm_sgxt_profit where times=201612 and user_id='.$value['user_id'];
            $freeze=$m->query($sql);
            $freeze_money=0;
            if(!empty($freeze))
            {
                $freeze_money=$freeze[0]['money'];
            }
            //更新收益、冻结收益
            $sql='update ecm_epay set earnings='.$ketixian_money.',freeze_earnings='.$freeze_money.' where user_id='.$value['user_id'];
            $m->query($sql);
        }
        //更新12月之前的所有的未解冻的未已解冻
        $sql='update ecm_sgxt_profit set is_clearing=1 where is_clearing=0 and times<201612 ';
        $m->query($sql);
        echo 'ok';*/
    }
}
 ?>