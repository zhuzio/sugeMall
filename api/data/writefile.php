<?php
//填数据
function setrealmoney()
{
    $m=new M();
    $info=$m->table('sgxt_deposit')->where('real_money=0')->select();
    foreach($info as $key=>$value)
    {
        $m->query('update ecm_sgxt_deposit set real_money='.$value['money'].' where deid='.$value['deid']);
    }
    echo 'ok';
}
function saoma()
{
    include(ROOT_PATH."/includes/libraries/phpqrcode.php");
    include(ROOT_PATH."/includes/libraries/agmpay.lib.php");
    $link = conf('SITE_URL').'/wap/register1.html?mobile=17739500188';
    $errorCorrectionLevel = 'L';//容错级别
    $matrixPointSize = 6;//生成图片大小
    //生成二维码图片
    QRcode::png($link, false, $errorCorrectionLevel, $matrixPointSize, 2);
    exit;
}
function epaylog()
{
    $m=new M();
    $refund=$m->table('refund')->where('refund_id in (6,9)')->select();
    foreach($refund as $key=>$value)
    {
        $order_id=$value['order_id'];
        $order=$m->table('order')->where('order_id='.$order_id)->find();
        $money=$value['refund_goods_fee']+$value['refund_shipping_fee'];
        $buyer_log_text = $order['seller_name'].'同意给你购买的产品退款'.$money.'元，订单号为:'.$order['order_sn'].',退款编号为:'.$value['refund_sn'];
        $buyer_epay_log = array(
            'user_id'=>$order['buyer_id'],
            'user_name'=>$order['buyer_name'],
            'order_id'=>$order_id,
            'order_sn'=>$order['order_sn'],
            'to_id'=>$order['seller_id'],
            'to_name'=>$order['seller_name'],
            'type'=>80,
            'money_flow' => 'income',
            'money' => $money,
            'complete'=>1,
            'log_text'=>$buyer_log_text,
            'add_time'=>  time(),
        );
       $m->table('epaylog')->insert($buyer_epay_log);
    }
}
//检查上线以后货款购积分的支付记录是否存在，不存在则添加数据
function payment()
{
    //查询28号以后的货款购积分记录
    $m=new M();
    $sgxt_order=$m->table('sgxt_order')->where('status=1 and createtime>=1480262400')->select();
    $fp=fopen('paymentlog.txt','w');
    foreach($sgxt_order  as $k=> $v)
    {
        //查询支付记录
        $paymentlog=$m->table('paymentlog')->where(array('order_sn'=>$v['orderid']))->find();
        if(empty($paymentlog))
        {
            //记录并插入数据库
            $paymentlog=new paymentlogModel();
            $paymentlog->paymentlog($v['userid'],$v['truename'],$v['amount'],'6',0,0,$v['id'],$v['orderid']);
            //更新时间为购积分时间
            $m->table('paymentlog')->where(array('order_sn'=>$v['orderid']))->update(array('add_time'=>$v['pay_createtime']));
            fwrite($fp,$v['userid'].'-'.$v['truename'].'-'.$v['amount'].'-'.'6'.'-0-0-'.$v['id'].'-'.$v['orderid'].'-'.$v['pay_createtime']."\n");
        }
    }
    fclose($fp);
    fk('成功！');
}
//生成文件
function writearea()
{
    $model=new M();
    $model->table('message_type')->delete();
    $users=$model->table('member')->field('user_id')->select();
    foreach($users as $k=>$v)
    {
        $model->table('message_type')->insert(array('user_id'=>$v['user_id'],'message_type'=>1));
        $model->table('message_type')->insert(array('user_id'=>$v['user_id'],'message_type'=>2));
        $model->table('message_type')->insert(array('user_id'=>$v['user_id'],'message_type'=>3));
        $model->table('message_type')->insert(array('user_id'=>$v['user_id'],'message_type'=>4));
    }
    /*$file='area.plist';
    $fp=fopen($file,"w") or die("Unable to open file!");
    $content='<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">';
    fwrite($fp, $content."\n");
    $model = new M();
    $provices=$model->table('sgxt_area')->where(array('parent_id'=>1))->select();
    fwrite($fp,'<array>'."\n");
    foreach($provices as $key=>$provice)
    {
        $strprovince='	<dict>'."\n";
        $strprovince.='		<key>provinceName</key>'."\n";
        $strprovince.='		<string>'.$provice['name'].'</string>'."\n";
        $strprovince.='		<key>provinceId</key>'."\n";
        $strprovince.='		<string>'.$provice['id'].'</string>'."\n";
        $strprovince.='		<key>cityList</key>'."\n";
        fwrite($fp, $strprovince);
        $citys=$model->table('sgxt_area')->where(array('parent_id'=>$provice['id']))->select();
        fwrite($fp,'		<array>'."\n");
        foreach($citys as $k=> $city)
        {
            $strcity='			<dict>'."\n";
            $strcity.='				<key>cityName</key>'."\n";
            $strcity.='				<string>'.$city['name'].'</string>'."\n";
            $strcity.='				<key>cityId</key>'."\n";
            $strcity.='				<string>'.$city['id'].'</string>'."\n";
            $strcity.='				<key>countryList</key>'."\n";
            fwrite($fp, $strcity);
            $areas=$model->table('sgxt_area')->where(array('parent_id'=>$city['id']))->select();
            fwrite($fp,'				<array>'."\n");
            foreach($areas as $karea=>$area)
            {
                $strarea='					<dict>'."\n";
                $strarea.='						<key>countryName</key>'."\n";
                $strarea.='						<string>'.$area['name'].'</string>'."\n";
                $strarea.='						<key>countryId</key>'."\n";
                $strarea.='						<string>'.$area['id'].'</string>'."\n";
                fwrite($fp, $strarea);
                fwrite($fp, '					</dict>'."\n");
            }
            fwrite($fp,'				</array>'."\n");
            fwrite($fp, '			</dict>'."\n");
        }
        fwrite($fp,'		</array>'."\n");
        fwrite($fp, '	</dict>'."\n");
    }
    fwrite($fp,'</array>'."\n");
    $content="</plist>";
    fwrite($fp, $content);
    fclose($fp);
    fk('success');*/
}