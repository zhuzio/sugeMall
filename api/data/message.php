<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/18
 * Time: 13:55 优惠促销 我的财富 系统通知 订单信息    每个消息推送的详细内容
 */
//提醒发货接口
function reminder_delivery()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $order_id=$_POST["order_id"];
    if(empty($order_id))
    {
        err('请上传订单id！');
    }
    $model=new M();
    $order=$model->table('order')->where(array('order_id'=>$order_id))->find();
    //给商家发消息
    addMessage('order','order_id',$order_id,'用户提醒您发货！',$order['seller_id'],$order['seller_name']);
    fk('提醒发货成功！');
}
//查询推送消息接口
function mlist()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $model=new M();
    $totalcount=$model->table('push_message')->where(array('to_userid'=>$user['user_id']))->count();
    $messagelist=$model->table('push_message')->field('id,table_name,table_key,table_value,title,addtime,is_read,status')->where(array('to_userid'=>$user['user_id']))->order('id desc')->limit($startcount.','.$pagecount)->select();
    //$messagelist=$model->query('select ecm_push_message.id,ecm_push_message.table_name,ecm_push_message.table_key,ecm_push_message.table_value,ecm_push_message.title,ecm_push_message.addtime,ecm_push_message.ms_type,ecm_push_message.is_read,ecm_push_ms_type_img.img,ecm_push_ms_type_img.name,ecm_push_message.status from ecm_push_message left join ecm_push_ms_type_img on ecm_push_ms_type_img.id=ecm_push_message.ms_type where to_userid='.$user['user_id'].' order by id desc limit '.$startcount.','.$pagecount);
    getmsurl($user['user_id'],$messagelist);
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"消息列表",$messagelist,$totalpage);
}

function getmsurl($userid,&$messagelist)
{
    $model=new M();
    $ms_type_list=conf('ms_type');
    foreach($messagelist as $key=>$value)
    {
        $user_type=1;
        $url='';
        if($value['table_key']=='id')
        {
            $messagelist[$key]['table_key']=$value['table_key']='order_id';
        }
        $messagelist[$key][$value['table_key']]=$value['table_value'];
        $ms_type=$ms_type_list[$value['table_name']];
        if($ms_type==1)
        {
            //查询是用户是卖家买家
            $info=$model->table($value['table_name'])->where(array($value['table_key']=>$value['table_value']))->find();
            if($userid==$info['buyer_id'])
            {
                $url='order_on.html';
            }
            else
            {
                $user_type=2;
                $tablearray=array('order'=>'z-union-shop-online-order.html','order_offline'=>'l-order1.html');
                $url=$tablearray[$value['table_name']];
            }
        }else if($ms_type==2)
        {
            $url='transrorm2.html';
        }
        else if($ms_type==3)
        {
            $user_type=2;
            $url='buymingxi.html';
        }else if($ms_type==4)
        {
            $statusarray=array('WAIT_SELLER_AGREE'=>'wdtksq.html','SELLER_REFUSE_BUYER'=>'mjjj.html','SUCCESS'=>'mjcg.html','CLOSED'=>'tkxq.html');
            $url=$statusarray[$value['status']];
        }else if($ms_type==6)
        {
            $url='index.html';
        }else{
            $url='index.html';
        }
        unset($messagelist[$key]['table_key']);
        unset($messagelist[$key]['table_value']);
        unset($messagelist[$key]['table_name']);
        $messagelist[$key]['ms_type']=$ms_type;
        $ms_type_img=conf('ms_type_img');
        $ms_type_name=conf('ms_type_name');
        $messagelist[$key]['img']=$ms_type_img[$ms_type];
        $messagelist[$key]['name']=$ms_type_name[$ms_type];
        $messagelist[$key]['url']=$url;
        $messagelist[$key]['user_type']=$user_type;
    }
}
//消息更新为已读状态
function read()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $msid=$_POST["messageid"];
    $model=new M();
    $model->table('push_message')->where(array('id'=>$msid))->update(array('is_read'=>1));
    fk('消息更新为已读');
}

//消息类型
function type()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $type=conf('message_type');
    $model=new M();
    $messagetype=$model->table('message_type')->where(array('user_id'=>$user['user_id']))->select();
    foreach($messagetype as $k => $v)
    {
        $v['message_type']=$type[$v['message_type']];
        $messagetype[$k]=$v;
    }
    fk('消息类型',$messagetype);

}

//消息类型
function status()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $model=new M();
    $model->table('message_type')->where(array('id'=>$_POST['id'],'user_id'=>$user['user_id']))->update(array('status'=>$_POST['status']));
    fk('设置消息类型状态');
}

//物流类型
function logistics()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $logistics=conf('logistics');
    fk('物流类型',$logistics);
}
//添加物流信息
function add_logistics()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $model=new M();
    $model->table('logistics')->insert(array('user_id'=>$user['user_id'],'type'=>$_POST['type'],'logistics_sn'=>$_POST['logistics_sn'],'add_time'=>time()));
    fk('提交物流信息');
}

//消息详情
function info()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    //根据消息参数然后返回对应的详情信息
    $messageid=$_POST['messageid'];
    $model=new M();
    $message=$model->table('push_message')->where(array('id'=>$messageid))->find();
    if(empty($message)){
        err('消息不存在！');
    }
    //查询详情
    $info=$model->table($message['table_name'])->where(array($message['table_key']=>$message['table_value']))->find();
    fk('消息详情',$info);
}
//卖家同意退款消息详情
function agree_info()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $refund_id=$_POST['refund_id'];
    //查询订单号，根据订单号查询到地址
    $model=new M();
    $refundinfo=$model->table('refund')->where(array('refund_id'=>$refund_id))->find();
    if(empty($refundinfo))
    {
        err('退款记录不存在！');
    }
    //根据卖家查询地址
    $store=$model->table('store')->field('region_name,address')->where(array('store_id'=>$refundinfo['seller_id']))->find();
    $data['refund_adress']=$store['region_name'].$store['address'];
    $data['seller_desc']=$refundinfo['seller_desc'];
    $data['refund_sn']=$refundinfo['refund_sn'];
    $data['times']=$refundinfo['end_time'];
    fk('同意退款消息详情',$data);
}
//退款成功,完成退款
function succ_info()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $refund_id=$_POST['refund_id'];
    $model=new M();
    $refundinfo=$model->table('refund')->where(array('refund_id'=>$refund_id))->find();
    if(empty($refundinfo))
    {
        err('退款记录不存在！');
    }
    $data['money']=$refundinfo['refund_goods_fee'] + $refundinfo['refund_shipping_fee'];
    $data['times']=$refundinfo['end_time'];
    fk('退款完成',$data);
}
//卖家拒绝退款
function refuse_info()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $refund_id=$_POST['refund_id'];
    $model=new M();
    $refundinfo=$model->table('refund')->where(array('refund_id'=>$refund_id))->find();
    if(empty($refundinfo))
    {
        err('退款记录不存在！');
    }
    $data['money']=$refundinfo['refund_goods_fee'] + $refundinfo['refund_shipping_fee'];
    $data['refund_sn']=$refundinfo['refund_sn'];
    $data['seller_desc']=$refundinfo['seller_desc'];
    $data['refund_reason']=$refundinfo['refuse_reason'];
    //凭证
    $refund_message=$model->table('refund_message')->field('content,pic_url')->where(array('refund_id'=>$refund_id,'owner_role'=>'seller'))->find();
    $content=$refund_message['content'];
    $arr=explode('：',$content);
    if($arr[1]!='')
    {
        $data['refund_reason']=$arr[1];
    }
    if($refund_message['pic_url']!='')
    {
        $data['seller_desc']=$refund_message['pic_url'];
    }
    $data['times']=$refundinfo['created'];
    fk('拒绝退款详情',$data);
}
//联系我们
function contact_us()
{
    $mobile=conf('contact_us');
    fk('联系我们',$mobile);
}
//退款状态
function refund_status()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user)) {
        err('身份错误，请重新登录');
    }
    $refund_id=$_POST['refund_id'];
    $model=new M();
    $refundinfo=$model->table('refund')->where(array('refund_id'=>$refund_id))->find();
    if(empty($refundinfo))
    {
        err('退款记录不存在！');
    }
    $data['refund_status']=$refundinfo['status'];
    fk('退款状态',$data);
}


