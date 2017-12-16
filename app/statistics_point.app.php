<?php
header("Content-type:text/html;charset=utf-8");
/* 会员 member */
ini_set('max_execution_time',0);
//ini_set(E_ALL);
class Statistics_pointApp extends FrontendApp{
    //购物积分统计
    public function statistics()
    {
        $m=& m();
        $userslist=$m->table('member')->where('type=2')->select();
        $fp=fopen('point.txt','w');
        $ycfp=fopen('point_yc.txt','w');
        $userinfo=fopen('user.txt','w');
        $orderfp=fopen('order_offline.txt','w');
        foreach($userslist as $userkey=>$user) {
            //收取购物积分货款
            $sql='select sum(order_amount) as total_offline_amount from ecm_order_offline where status=40 and payment_id=3 and seller_id='.$user['user_id'];
            $order_offline=$m->query($sql);
            $sql='select sum(order_amount) as total_online_amount from ecm_order where status=40 and payment_id=3 and seller_id='.$user['user_id'];
            $order_online=$m->query($sql);
            //提现和
            $sql='select sum(money) as total_money from ecm_sgxt_deposit where type=1 and ispay in (0,1)  and userid='.$user['user_id'];
            $tixian=$m->query($sql);
            //货款购积分和
            $sql='select sum(amount) as total_amount from ecm_sgxt_order where paytype=\'balance\'  and userid='.$user['user_id'];
            $huokuan=$m->query($sql);
            $num=(float)$order_online[0]['total_online_amount']+(float)$order_offline[0]['total_offline_amount']-(float)$tixian[0]['total_money']-(float)$huokuan[0]['total_amount'];
            if($num>=0)
            {
                if(((float)$order_online[0]['total_offline_amount']+(float)$order_offline[0]['total_offline_amount'])>0)
                {
                    fwrite($fp,'线上积分货款总和：'.$order_online[0]['total_online_amount'].'线下积分货款总和：'.$order_offline[0]['total_offline_amount'].';提现总和：'.$tixian[0]['total_money'].';货款购积分总和：'.$huokuan[0]['total_amount']."\n");
                }
            }
            else
            {
                //查询线上订单时间
                $sql='select * from ecm_order where status=40 and payment_id=3 and seller_id='.$user['user_id'];
                $order=$m->query($sql);
                $ordernum=0;
                if(empty($order))
                {
                    $order=array();
                }
                foreach($order as $key=>$v)
                {
                    $ordernum=$ordernum+$v['order_amount'];
                    fwrite($orderfp,'姓名：'.$user['real_name'].';手机号码：'.$user['user_name'].';用户id:'.$user['user_id'].'；线上；订单号：'.$v['order_sn'].';订单时间：'.date('Y-m-d H:i:s',$v['add_time']).';审核时间：'.date('Y-m-d H:i:s',$v['check_time'])."\n");
                }
                //查询线下订单时间
                $sql='select * from ecm_order_offline where status=40 and payment_id=3 and seller_id='.$user['user_id'];
                $order=$m->query($sql);
                if(empty($order))
                {
                    $order=array();
                }
                foreach($order as $key=>$v)
                {
                    $ordernum=$ordernum+$v['order_amount'];
                    fwrite($orderfp,'姓名：'.$user['real_name'].';手机号码：'.$user['user_name'].';用户id:'.$user['user_id'].'；线下；订单号：'.$v['order_sn'].';订单时间：'.date('Y-m-d H:i:s',$v['add_time']).';审核时间：'.date('Y-m-d H:i:s',$v['check_time'])."\n");
                }
                fwrite($userinfo,'姓名：'.$user['real_name'].';手机号码：'.$user['user_name'].';用户id:'.$user['user_id'].'；差价：'.$num.'订单金额：'.$ordernum."\n");
                fwrite($ycfp,'姓名：'.$user['real_name'].';手机号码：'.$user['user_name'].';线下积分货款总和：'.floatval($order_offline[0]['total_offline_amount']).';提现总和：'.floatval($tixian[0]['total_money']).';货款购积分总和：'.floatval($huokuan[0]['total_amount']).';差价：'.$num."\n");
            }
        }
        fclose($fp);
        fclose($ycfp);
        fclose($userinfo);
        fclose($orderfp);
        echo 'Ok';
    }
}
?>