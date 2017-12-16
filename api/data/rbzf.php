<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/8
 * Time: 13:46
 */
//解绑接口
function canclebank()
{
    $user_id=$_POST['user_id'];
    $bind_id=$_POST['bind_id'];
    global $merchant_id;
    global $apiUrl;
    global $apiKey;
    global $reapalPublicKey;
    global $merchantPrivateKey;
    //第三方接口
    $paramArr = array(
        'merchant_id' => $merchant_id,
        'member_id' => $user_id,
        'bind_id' => $bind_id,
        'version' => '3.1.2'
    );
    //访问储蓄卡签约服务
    $url = $apiUrl.'/fast/cancle/bindcard';
    $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
    $response = json_decode($result,true);
    $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
    $json=AESDecryptResponse($encryptkey,$response['data']);
    $result=json_decode($json, true);
    if($result['result_code'] == '0000')
    {
        $model = new M();
        $rs = $model->table('member_bind_bank')->where(array('user_id'=>$user_id,'bind_id'=>$bind_id))->update(array('status'=>2));
        fk('解绑成功');
    }else
    {
        err('解绑失败');
    }
}
//获取订单信息
function getOrderinfo($dingdan){
    $model=new M();
    if($dingdan[0] == 'P'){
        $orderinfo = $model->table('sgxt_order')->where(array('orderid'=>$dingdan))->find();
        if(empty($orderinfo)){
            echo 'sgxt order error';die;
        }
        if($orderinfo['status']){
            echo '订单已支付1';
            die;
        }
        $order['userid'] = $orderinfo['userid'];
        $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find();
        $order['order_id'] = $orderinfo['order_id'];
        $order['user'] = $user;
        $order['user_mobile'] = $user['user_name'];
        $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
        $order['userid'] = $orderinfo['userid'];
        $order['truename'] = $orderinfo['truename'];
        $order['cz_money'] = $orderinfo['amount'];
    }else{
        //商品订单支付
        $orderinfo = $model -> table('order')->where(array('order_sn'=>$dingdan))->find();
        if(empty($orderinfo)){
            echo 'goods order error';
            die;
        }
        if($orderinfo['status'] != '11'){
            echo '订单已支付';
            die;
        }
        $order['order_id'] = $orderinfo['order_id'];
        $order['userid'] = $orderinfo['buyer_id'];
        $user = $model->table('member')->where(array('user_id'=>$order['userid']))->find();
        $order['user'] = $user;
        $order['user_mobile'] = $user['user_name'];
        $order['user_reg_time'] = date('YmdHis',$user['reg_time']);
        $order['userid'] = $orderinfo['buyer_id'];
        $order['truename'] = $orderinfo['buyer_name'];
        $order['cz_money'] = $orderinfo['order_amount'];
    }
    return $order;
}
function getIp(){
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    }
    elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_X_FORWARDED')) {
        $ip = getenv('HTTP_X_FORWARDED');
    }
    elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip = getenv('HTTP_FORWARDED_FOR');
    }
    elseif (getenv('HTTP_FORWARDED')) {
        $ip = getenv('HTTP_FORWARDED');
    }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
//银行卡号码接口
function default_bank()
{
    $ordersn=$_POST['order_sn'];
    $order=getOrderinfo($ordersn);
    if(!$order){
        err('订单不存在');
    }    
    $model = new M();
    $bind_bank = $model->table('member_bind_bank')->where('user_id='.$order['userid'].' and status=1 and is_default=1')->find();
    if(!empty($bind_bank))
    {
        $bank_type_arr=array('0'=>'借记卡','1'=>'信用卡');
        $real_bank_num=$bank_num=$bind_bank['bank_num'];
        $bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
        $bind_bank['real_bank_num'] = $real_bank_num;
        $bind_bank['bank_num'] = $bank_num;
        $bind_bank['bank_card_type'] = $bank_type_arr[$bind_bank['type']];
        fk('ok',array(
        	'user_id'		=>$bind_bank['user_id'],
        	'bind_id'		=>$bind_bank['bind_id'],
        	'real_bank_num'	=>$real_bank_num,
        	'bank_num'		=>$bank_num,
        	'bank_name'		=>$bind_bank['bank_name'],
        	'bank_card_type'=>$bank_type_arr[$bind_bank['type']],
        	'type'			=>$bind_bank['type'],
        	'real_name'		=>$bind_bank['real_name'],
        	'mobile'		=>$bind_bank['mobile'],
            'idcard_num'    =>$bind_bank['idcard_num']
        ));
    }else
    {
        fk('ok',array(
            'user_id'       =>'',
            'bind_id'       =>'',
            'real_bank_num' =>'',
            'bank_num'      =>'',
            'bank_name'     =>'',
            'bank_card_type'=>'',
            'type'          =>'',
            'real_name'     =>'',
            'mobile'        =>'',
            'idcard_num'    =>''
        ));
    }
}
//第一步，输入银行卡号
function step1()
{
    global $merchant_id;
    global $apiUrl;
    global $apiKey;
    global $reapalPublicKey;
    global $merchantPrivateKey;
    $ordersn=$_POST['order_sn'];
    $order=getOrderinfo($ordersn);
    //判断银行是否已绑定
    $model = new M();
    $bank_num = $_POST['bank_num'];
    if($bank_num){
        $bind_bank = $model->table('member_bind_bank')->where('bank_num='.$bank_num.' and status=1')->find();
    }
    $bank_type_arr=array('0'=>'借记卡','1'=>'信用卡');
    if(!empty($bind_bank))
    {
        $real_bank_num=$bank_num;
        $bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
        fk('ok',array('real_bank_num'=>$real_bank_num,'bank_num'=>$bank_num,'bank_name'=>$bind_bank['bank_name'],'bank_card_type'=>$bank_type_arr[$bind_bank['type']],'bank_card_type_id'=>$bind_bank['type'],'is_card'=>1,'real_name'=>$bind_bank['real_name'],'idcard_num'=>$bind_bank['idcard_num'],'phone'=>$bind_bank['mobile'],'cvv2'=>$bind_bank['cvv2'],'validthru'=>$bind_bank['end_time']));
    }
    else
    {
        //调用卡信息查询接口
        $paramArr = array(
            'merchant_id' => $merchant_id,
            'card_no' => $bank_num,
            'version' => '3.1.2'
        );
        $url = $apiUrl.'/fast/bankcard/list';
        $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
        $response = json_decode($result,true);
        $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
        $json = AESDecryptResponse($encryptkey,$response['data']);
        $rs=json_decode($json, true);
        if($rs['result_code']=='0000')
        {
            $real_bank_num=$bank_num;
            $bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
            fk('ok',array('real_bank_num'=>$real_bank_num,'bank_num'=>$bank_num,'bank_name'=>$rs['bank_name'],'bank_card_type'=>$bank_type_arr[$rs['bank_card_type']],'bank_card_type_id'=>$rs['bank_card_type'],'is_card'=>0,'real_name'=>'','idcard_num'=>'','phone'=>'','cvv2'=>'','validthru'=>''));
        }
        else
        {
            err($rs['result_msg']);
        }
    }
}

//获取验证码
function send_sms()
{
    global $merchant_id;
    global $apiUrl;
    global $apiKey;
    global $reapalPublicKey;
    global $merchantPrivateKey;
    $ordersn=$_POST['order_sn'];
    $order=getOrderinfo($ordersn);
    //判断银行是否已绑定
    $model = new M();
    $bank_num = $_POST['bank_num'];
    $bind_bank = $model->table('member_bind_bank')->where('bank_num='.$bank_num.' and status=1')->find();
    if(empty($bind_bank))
    {
        $bank_name=$_POST['bank_name'];
        $bank_card_type_id=$_POST['bank_card_type_id'];
        //信用卡签约
        if($bank_card_type_id==1)
        {
            $paramArr = array(
                'merchant_id' => $merchant_id,
                'card_no' => $bank_num,
                'cvv2' => $_POST['cvv2'],
                'validthru' => $_POST['validthru'],
                'owner' => $_POST['real_name'],
                'cert_type' => '01',
                'cert_no' => $_POST['idcard_num'],
                'phone'=> $_POST['phone'],
                'order_no' =>$ordersn,
                'transtime' => time(),
                'currency' => '156',
                'total_fee' => floor($order['cz_money']*100),
                'title' => '购买积分',
                'body' => '购买积分',
                'member_id' => $order['userid'],
                'terminal_type'=>'mobile',
                'terminal_info' => '554545',
                'member_ip' => getIp(),
                'seller_email' => '348887102@qq.com',
                'notify_url' => 'http://www.shadouxing.net/app/reapal/notify.php',
                'token_id' => '1234567890765463',
                'version' => '3.1.3'
            );
            $url = $apiUrl.'/fast/credit/portal';
            $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
            $response = json_decode($result,true);
            $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
            $json=AESDecryptResponse($encryptkey,$response['data']);
        }
        else
        {
            //储蓄卡签约
            $paramArr = array(
                'merchant_id' => $merchant_id,
                'card_no' => $bank_num,
                'owner' => $_POST['real_name'],
                'cert_type' => '01',
                'cert_no' => $_POST['idcard_num'],
                'phone'=> $_POST['phone'],
                'order_no' =>$ordersn,
                'transtime' => time(),
                'currency' => '156',
                'total_fee' => floor($order['cz_money']*100),
                'title' => '购买积分',
                'body' => '购买积分',
                'member_id' => $order['userid'],
                'terminal_type'=>'mobile',
                'terminal_info' => '554545',
                'member_ip' => getIp(),
                'seller_email' => '348887102@qq.com',
                'notify_url' => 'http://www.shadouxing.net/app/reapal/notify.php',
                'token_id' => '1234567890765463',
                'version' => '3.1.3'
            );
            //签约
            $url = $apiUrl.'/fast/debit/portal';
            $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
            $response = json_decode($result,true);
            $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
            $json=AESDecryptResponse($encryptkey,$response['data']);
        }
        file_put_contents('03092.txt',$json);
        $rs=json_decode($json, true);
        if($rs['result_code']!='0000')
        {
            err($rs['result_msg']);
        }
        $band_id=$rs['bind_id'];
        fk($rs['result_msg'],array('band_id'=>$band_id));
    }
    else
    {
        $band_id=$bind_bank['band_id'];
        $paramArr = array(
            'merchant_id' => $merchant_id,
            'bind_id' => $band_id,
            'order_no' =>$ordersn,
            'transtime' => time(),
            'currency' => '156',
            'title' => $ordersn[0]=='P'?'购买积分':'商城购物',
            'body' => $ordersn[0]=='P'?'购买积分':'商城购物',
            'member_id' => $order['userid'],
            'terminal_type'=>'mobile',
            'terminal_info' => '554545',
            'member_ip' => getIp(),
            'seller_email' => '348887102@qq.com',
            'notify_url' => 'http://www.shadouxing.net/app/reapal/notify.php',
            'token_id' => '1234567890765463',
            'version' => '3.1.3',
            'total_fee' => floor($order['cz_money']*100),
        );
        $url = $apiUrl.'/fast/bindcard/portal';
        $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
        $response = json_decode($result,true);
        $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
        $json=AESDecryptResponse($encryptkey,$response['data']);
        file_put_contents('03094.txt',$json);
        $rs=json_decode($json, true);
        if($rs['result_code']!='0000')
        {
            err($rs['result_msg']);
        }
        //发送短信
        $paramArr = array(
            'merchant_id' => $merchant_id,
            'order_no' => $ordersn,
            'version' => '3.1.2'
        );
        $url = $apiUrl.'/fast/sms';
        $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
        $response = json_decode($result,true);
        $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
        $json=AESDecryptResponse($encryptkey,$response['data']);
        file_put_contents('03093.txt',$json);
        $smsinfo=json_decode($json, true);
        if($smsinfo['result_code']=='0000')
        {
            $real_bank_num=$bank_num;
            $bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
            fk('ok',array('band_id'=>$band_id));
        }
        else
        {
            err($smsinfo['result_msg']);
        }
    }
}
//确认支付
function comfirm()
{
    global $merchant_id;
    global $apiUrl;
    global $apiKey;
    global $reapalPublicKey;
    global $merchantPrivateKey;
    $ordersn=$_POST['order_sn'];
    $code=$_POST['code'];
    $paramArr = array(
        'merchant_id' => $merchant_id,
        'order_no' => $ordersn,
        'check_code' => $code
    );

    //访问储蓄卡签约服务
    $url = $apiUrl.'/fast/pay';
    $result = send($paramArr, $url, $apiKey, $reapalPublicKey, $merchant_id);
    $response = json_decode($result,true);
    $encryptkey = RSADecryptkey($response['encryptkey'],$merchantPrivateKey);
    $json=AESDecryptResponse($encryptkey,$response['data']);
    $rs=json_decode($json, true);
    if($rs['result_code']=='0000')
    {
        //将信息保存至数据库
        $model = new M();
        $bank_name = $_POST['bank_name'];
        $bank_num = $_POST['bank_num'];
        $real_name = $_POST['real_name'];
        $ordersn=$_POST['order_sn'];
        $idcard_num = $_POST['idcard_num'];
        $mobile = $_POST['phone'];
        $bank_type = $_POST['bank_card_type_id'];
        $end_time = $_POST['validthru'];
        $bind_id = $_POST['bind_id'];
        $cvv2 = $_POST['cvv2'];
        $order=getOrderinfo($ordersn);
        $user_id = $order['userid'];
        $data['user_id'] = $user_id;
        $data['bank_num'] = $bank_num;
        $data['bank_name'] = $bank_name;
        $data['real_name'] = $real_name;
        $data['idcard_num'] = $idcard_num;
        $data['mobile'] = $mobile;
        $data['type'] = $bank_type;
        $data['end_time'] = $end_time;
        $data['cvv2'] = $cvv2;
        $data['status'] = 1;
        $data['is_default'] = 1;
        $data['bind_id'] = $bind_id;
        $data['add_time'] = time();
        file_put_contents('data.txt',json_encode($data));
        $bank = $model->table('member_bind_bank')->where(array('user_id'=>$user_id,'bank_num'=>$bank_num))->find();
        if(!$bank){
            $model->table('member_bind_bank')->where('user_id='.$user_id)->update(array('is_default'=>2));
        }
        $model->table('member_bind_bank')->insert($data);
        fk($rs['result_msg']);
    }
    else
    {
        err($rs['result_msg']);
    }
}