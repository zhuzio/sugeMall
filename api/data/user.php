<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/28
 * Time: 19:38
 */

function login(){


    if(!isset($_REQUEST["phone"]))err("手机号未提交!");


    if(!isset($_REQUEST["password"]))err("密码未提交!");


    if(!preg_match("/1[345678]{1}\d{9}$/",strval(@$_REQUEST["phone"])))err("手机号格式错误,请检查!");


    $model = new M();


    $user = $model->table('member')->where(array('user_name'=>$_REQUEST['phone']))->find();

    //print_r($user['portrait']);die;

    $province = $model->table('sgxt_area')->where(array('id'=>$user['province']))->find();

    //echo $model->getsql();die;

    $area = $model->table('sgxt_area')->where(array('id'=>$user['area']))->find();

    $city = $model->table('sgxt_area')->where(array('id'=>$user['city']))->find();


    if($user){


        if(md5($_REQUEST['password'])==$user['password']){

            $id=$user['user_id'];

            $http=conf('SITE_URL');

            //$recode=$http.'/api/index.php?n=user&f=recode&user_id='.base64_encode($id);
            $recode=$http.'/api/index.php?n=shop_center&f=register_qrc';
            if($user['type']=="2"){

                $type = $model
                    ->table('store')
                    ->field('o2o')
                    ->where(array('store_id'=>$user['user_id']))->find();
                $data=array(

                    'user_name'	=>$user['user_name'],

                    'birthday'	=>$user['birthday'],

                    'im_qq'		=>$user['im_qq'],

                    'email'		=>$user['email'],

                    'gender'	=>$user['gender'],

                    'real_name'	=>$user['real_name'],

                    'portrait'	=>$user['portrait'],

                    'area'		=>$area['name'],

                    'city'		=>$city['name'],

                    'province'	=>$province['name'],

                    'type'		=>$user['type'],
                    'pid'		=>$user['pid'],

                    'recode'	=>$recode,

                    'o2o'	=>$type['o2o'],

                    'token'		=>_authcode($user['user_id'],'ENCODE',AUTH_KEY)

                );
            }else{
                $data=array(

                    'user_name'	=>$user['user_name'],

                    'birthday'	=>$user['birthday'],

                    'im_qq'		=>$user['im_qq'],

                    'email'		=>$user['email'],

                    'gender'	=>$user['gender'],

                    'real_name'	=>$user['real_name'],

                    'portrait'	=>$user['portrait'],

                    'area'		=>$area['name'],

                    'city'		=>$city['name'],

                    'province'	=>$province['name'],

                    'type'		=>$user['type'],
                    'pid'		=>$user['pid'],
                    'recode'	=>$recode,


                    'token'		=>_authcode($user['user_id'],'ENCODE',AUTH_KEY)

                );
            }
            if(!empty($data['portrait']))
            {
                $data['portrait']=conf('QINIU_URL').$data['portrait'];
            }
            //修改会员的登录日志信息
            update_login_log($user['user_id'],$user['user_name'],$user['real_name']);
            //修改用户最后登录时间,最后登录ip，登录次数
            $logins=$user['logins']+1;
            $last_ip=getIP();
            $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('last_login'=>time(),'logins'=>$logins,'last_ip'=>$last_ip));
            outJson('ok','登录成功',$data);


        }else{


            err('密码错误');


        }


    }else{


        err('用户不存在');


    }
}

//检测用户是否存在
function check_user(){
    $phone =trim($_POST['phone']);
    $m =new M();
    $user =$m->table('member')->where(array('user_name'=>$phone,'status'=>'1'))->find();
    if($user){
        echo "1"; //账户存在
    }else{
        echo "0"; //账户不存在
    }
}

//登出
function logout()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user) {
        $model = new M();
        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('device_number'=>''));
        fk('success');
    }else
    {
        err('fail');
    }
}

function update_login_log($user_id,$phone,$real_name)
{
    //查询最后一次登录信息
    $model = new M();
    $login_log=$model->table('login_log')->where(array('user_id'=>$user_id))->order('id desc')->limit('0,1')->select();
    $device=get_device_type();
    $data=array('user_id'=>$user_id,'login_ip'=>getIP(),'login_time'=>time(),'device_type'=>$device['type'],'version'=>$device['version'],'phone'=>$phone,'info'=>$device['info'],'real_name'=>$real_name);
    if(empty($login_log))
    {
        $data['count']=1;
        $model->table('login_log')->insert($data);
    }
    else
    {
        $data['count']=(int)$login_log[0]['count']+1;
        unset($data['user_id']);
        //修改登录信息
        $model->table('login_log')->where(array('user_id'=>$user_id))->update($data);
    }
}
function get_device_type()
{
    $serverinfo=$_SERVER['HTTP_USER_AGENT'];
    $agent=strtolower($serverinfo);
    $type = 'other';
    if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
    {
        $type='ios';
    }
    if(strpos($agent, 'android'))
    {
        $type='android';
    }
    //$agent='sugeOnlineMart/1.0 (iPhone; iOS 10.0.1; Scale/2.00)';
    preg_match("/iPhone; (([\s\S]+?));/",$serverinfo,$arr);
    $version=$arr[1];
    $brr=explode(' ',$version);
    $data['type']=$type;
    $data['version']=$brr[1];
    $data['info']=$serverinfo;
    return $data;
}
function getIP()
{
    $ip=getenv('REMOTE_ADDR');
    $ip_ = getenv('HTTP_X_FORWARDED_FOR');
    if (($ip_ != "") && ($ip_ != "unknown"))
    {
        $ip=$ip_;
    }
    return $ip;
}

/*

**密码修改

*/


function updatePwd(){


    $phone =trim($_POST['phone']);
    $model = new M();
    //查询用户
    $user = $model->table('member')->where(array('user_name'=>$phone))->find();


    if($user){


        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('password'=>md5($_POST['password'])));


        fk('修改成功');


    }else{


        err('身份错误，请重新登录');


    }





}
/*

**支付密码修改

*/


function updateZfpw(){

	$token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(empty($user))
    {
        err('身份错误，请重新登录');
    }
    $phone =trim($_POST['phone']);
    if($phone!=$user['user_name'])
    {
        err('只能修改登录用户的支付密码！');
    }
    $phone =trim($_POST['phone']);
    $model = new M();
    //查询用户
    // $user = $model->table('epay')->where(array('user_name'=>$phone))->find();

    if($user){

        $model->table('epay')->where(array('user_id'=>$user['user_id']))->update(array('zf_pass'=>md5($_POST['zf_pass'])));

        fk('修改成功');


    }else{


        err('身份错误，请重新登录1');


    }
}
/**
 * 发送验证码
 */
function send_code() {
    if (!conf('msg_enabled')) {
        return;
    }
    $mobile = empty($_POST['phone']) ? '' : trim($_POST['phone']);
    if (!$mobile) {
        echo ecm_json_encode(false);
        return;
    }
	 //验证码5分钟内不允许重发
    $code=checksmscode($mobile);
    if($code!='0')
    {
        fk("success",$code);
    }
    //发送短信的格式
    $type = $_POST['type'];
    if (!in_array($type, array('register', 'find', 'change'))) {
        echo ecm_json_encode(false);
        return;
    }
    //发送验证码
    import('mobile_msg.lib');
    $mobile_msg = new Mobile_msg();
    $result = $mobile_msg->send_msg_system($type, $mobile);
	fk("success",$result);
    //fk("success",ecm_json_encode($result));
}
/*验证验证码*/
/*function checkcode()
{
	$code=$_POST['code'];
	$phone=$_POST['phone'];
	$content=file_get_contents('code.txt');
	$param=$phone.'-'.$code;
	//验证验证码
	//if($_SESSION['MobileConfirmCode']==$code&&$_SESSION['MobileConfirmPhone']==$phone)
	//if($_COOKIE['MobileConfirmCode']==$code && $_COOKIE['MobileConfirmPhone']==$phone)
	if($content==$param)
	{
		fk("验证码输入正确！");
	}
	else
	{
		err('验证码输入错误！');
	}
}*/
function checkcode()
{
    $code=$_POST['code'];
    $phone=$_POST['phone'];
    if($code=='')
    {
        err('请输入验证码！');
    }
    $sms_code=checksmscode($phone);

    if((int)$sms_code!=(int)$code)
    {
        err('验证码输入错误！');
    }
    fk("验证码输入正确！");
}
function checksmscode($phone)
{
    //验证验证码
    $model=new M();
    $sms=$model->table('msglog')->where('to_mobile='.$phone.' and time>='.(time()-300))->order('time desc')->limit('0,1')->find();
    if(empty($sms))
    {
        return '0';
    }
    /*$arr=explode(":",$sms['content']);
    $brr=explode(".",$arr[1]);*/
    return $sms['code'];
}
//检查手机号码是否已注册
function checkphone()
{
    $phone=$_POST['phone'];
    if($phone =='' || strlen($phone) != 11 || !is_numeric($phone) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $phone)){
        err('手机号非法');exit;
    }
    $model = new M();
    $user=$model->table('member')->where(array('user_name'=>$phone))->find();
    if(!empty($user))
    {
        err('本号码已被注册!');
    }
    fk('检测手机号码');
}

/*注册身份接口*/
function usertype()
{
    $data=array();
    $usertype=conf('register_user_type');
    foreach($usertype as $k=> $v)
    {
        $tempdata=array();
        $tempdata['type']=$k;
        $tempdata['name']=$v;
        $data[]=$tempdata;
    }
    fk("用户注册身份",$data);
}


/*

**用户注册

*/
function register(){
    $tjphone=$_POST['tjphone'];
    $phone=$_POST['phone'];
    $code=$_POST['code'];
    $password=$_POST['password'];
    $zfpw=$_POST['zfpw'];
    $real_name=$_POST['real_name'];
    $user_type=$_POST['user_type'];
    $province=$_POST['province'];
    $city=$_POST['city'];
    $area=$_POST['area'];

    //根据推荐手机号查找推荐用户信息
    $model = new M();
    $tjuser=$model->table('member')->where(array('user_name'=>$tjphone,'status'=>1))->find();
    if(empty($tjuser))
    {
        err('推荐人不存在!');
    }
    if($tjuser['type']==4)
    {
        $opid=$tjuser['user_id'];
    }
    else{
        $opid=$tjuser['opid'];
        if($opid==0)
        {
            //报警发送邮件
            $email_subject= 'data error';
            $email_content='user:'.$tjuser['user_name'].' opid=0,please check!';
            sendmail($email_subject,$email_content);
        }
    }
    $path = '';
    if($tjuser['path']){
        $path_arr = explode(',',$tjuser['path']);
        if(count($path_arr) == 3){
            $path = implode(',',array($path_arr[1],$path_arr[2],$tjuser['user_id']));
        }else if(count($path_arr) < 3){
            $path = $tjuser['path'] . ',' . $tjuser['user_id'];
        }
    }else{
        $path = $tjuser['user_id'];
    }
    //验证信息
    if($phone =='' || strlen($phone) != 11 || !is_numeric($phone) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $phone)){
        err('手机号非法');exit;
    }

    $user=$model->table('member')->where(array('user_name'=>$phone))->find();
    if(!empty($user))
    {
        err('本号码已被注册!');
    }

    //验证验证码
    /*$content=file_get_contents('code.txt');
    $param=$phone.'-'.$code;
    if($content!=$param){
        err('验证码不正确！');
    }*/

    if($code=='')
    {
        err('请输入验证码');
    }
    $sms_code=checksmscode($phone);
    if((int)$sms_code!=(int)$code)
    {
        err('验证码不正确！');
    }
    if($real_name== ''){
        err('请填写真实姓名');exit;
    }
    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $real_name, $matches);
    $real_name = join('', $matches[0]);
    if($province == '' || $province == 0){
        err('请选择省份');exit;
    }
    if($city == '' || $city == 0){
        err('请选择城市');exit;
    }
    if($area == '' || $area == 0){
        err('请选择县区');exit;
    }
    if($password == ''){
        err('密码不能为空');exit;
    }
	if($zfpw == ''){
        err('支付密码不能为空');exit;
    }
    if(strlen($zfpw)!=6 || !is_numeric($zfpw))
    {
        err('支付密码6位且必须是数字');exit;
    }

    $reg_time=time();
    //插入member表
    $data=array(
        'user_name'=>$phone,
        'real_name'=>$real_name,
        'pid'=>$tjuser['user_id'],
        'type'=>$user_type,
        'password'=>md5($password),
        'province'=>$province,
        'city'=>$city,
        'area'=>$area,
        'opid'=>$opid,
        'path'=>$path,
        'reg_time'=>$reg_time,
        'phone_mob'=>$phone,
        'status'=>conf('site_status'),
    );
    $user_id=$model->table('member')->insert($data);
    if($user_id<=0)
    {
        err('注册失败！');
    }
    $parentList = $model->table('member')->where('user_id in ('.$path.')')->select();
    foreach($parentList as $key=>$val){
        $model->table('member')->where('user_id='.$val['user_id'])->save(array(
            'childrens' => $val['childrens'] ? $val['childrens'].','.$user_id : $user_id
        ));
    }
    //插入epay表
    $epaydata=array(
        'user_id'=>$user_id,
        'user_name'=>$phone,
        'zf_pass'=>md5($zfpw),
        'add_time'=>$reg_time,
    );
    $epayresult=$model->table('epay')->insert($epaydata);
    if($epayresult>0)
    {
        addmessagetype($user_id);
        fk("注册成功");
    }
    else
    {
        err('支付密码失败！');
    }
}
//添加消息设置
function addmessagetype($userid)
{
    $model=new M();
    $model->table('message_type')->insert(array('user_id'=>$userid,'message_type'=>1));
    $model->table('message_type')->insert(array('user_id'=>$userid,'message_type'=>2));
    $model->table('message_type')->insert(array('user_id'=>$userid,'message_type'=>3));
    $model->table('message_type')->insert(array('user_id'=>$userid,'message_type'=>4));
}
/*
 * 发送邮件
 * */
function sendmail($email_subject,$email_content)
{
    import('mailer.lib');
    /* 使用mailer类 */
    $sender = Conf::get('site_name');
    $from = Conf::get('email_addr');
    $protocol = Conf::get('email_type');
    $host = Conf::get('email_host');
    $port = Conf::get('email_port');
    $username = Conf::get('email_id');
    $password = Conf::get('email_pass');
    $email_test=Conf::get('email_test');
    $mailer = new Mailer($sender, $from, $protocol, $host, $port, $username, $password);
    $mail_result=$mailer->send($email_test, $email_subject, $email_content, CHARSET, 1);
}
/*

**用户注册

*/

function registere(){

    if(!isset($_REQUEST["phone"]))err("手机号未提交!");

    if(!isset($_REQUEST["password"]))err("密码未提交!");

    if(!isset($_REQUEST["real_name"]))err("姓名未提交!");

    if(!isset($_REQUEST["province"]))err("省份未提交!");

    if(!isset($_REQUEST["city"]))err("城市未提交!");

    if(!isset($_REQUEST["area"]))err("县城未提交!");

    if(!preg_match("/1[345678]{1}\d{9}$/",strval(@$_REQUEST["phone"])))err("手机号格式错误,请检查!");

    $model = new M();

    $arr=array(

        'user_name'	=>	$_REQUEST["phone"],

        'password'	=>	MD5($_REQUEST["password"]),

        'real_name'	=>	$_REQUEST["real_name"],

        'province'	=>	$_REQUEST["province"],

        'city'		=>	$_REQUEST["city"],

        'area'		=>	$_REQUEST["area"],

        'type'=>'1'



    );

    $data=$model->table('member')->insert($arr);

    echo $model->getSql();

    if($data){

        fk("注册成功");

    }else{

        err("注册失败");

    }



}

/*

**普通会员用户信息

*/

function userinfo(){

    $token = rawurlencode($_POST['token']);



    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model=new M();

        $data=$model

            ->table('member')

            ->field('real_name,recode,portrait,type')

            ->where(array('user_id'=>$user['user_id']))

            ->find();

        if($data){

            fk("用户信息",$data);

        }else{

            err("查询失败");

        }

    }else{

        err('身份错误，请重新登录');

    }





}

/*

**普通会员点击二维码信息

*/



function recodeinfo(){

    //$token = rawurlencode($_POST['token']);

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model=new M();
        $province = $model->table('sgxt_area')->where(array('id'=>$user['province']))->find();

        //echo $model->getsql();die;

        $area = $model->table('sgxt_area')->where(array('id'=>$user['area']))->find();

        $city = $model->table('sgxt_area')->where(array('id'=>$user['city']))->find();
        $array=$model

            ->table('member')

            ->field('real_name,recode,portrait,province,city,area,gender')

            ->where(array('user_id'=>$user['user_id']))

            ->find();
        $data=array(

            'real_name'	=>$array['real_name'],
            'portrait'		=>$array['portrait'],
            'gender'		=>$array['gender'],
            'area'		=>$area['name'],
            'city'		=>$city['name'],
            'province'	=>$province['name']

        );


        if($data){

            fk("用户信息",$data);

        }else{

            err("查询失败");

        }

    }else{

        err('身份错误，请重新登录');

    }





}



/*

**用户详细信息

*/

function information(){

    //$token = rawurlencode($_POST['token']);

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model=new M();
        $province = $model->table('sgxt_area')->where(array('id'=>$user['province']))->find();

        //echo $model->getsql();die;

        $area = $model->table('sgxt_area')->where(array('id'=>$user['area']))->find();

        $city = $model->table('sgxt_area')->where(array('id'=>$user['city']))->find();
        $array=$model

            ->table('member')

            ->field('real_name,user_name,recode,portrait,province,city,area,gender,birthday,im_qq,email')

            ->where(array('user_id'=>$user['user_id']))

            ->find();
        $data=array(

            'real_name'	=>$array['real_name'],

            'user_name'	=>$array['user_name'],

            'portrait'		=>$array['portrait'],

            'email'		=>$array['email'],

            'im_qq'	=>$array['im_qq'],

            'birthday'	=>$array['birthday'],

            'gender'	=>$array['gender'],

            'area'		=>$area['name'],

            'city'		=>$city['name'],

            'province'	=>$province['name']

        );

        if($data){

            fk("用户信息",$data);

        }else{

            err("查询失败");

        }

    }else{

        err('身份错误，请重新登录');

    }





}

/*

**性别设置

*/

function setsex(){

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["gender"]))err("请设置性别");

    $user = checkToken($token);

    if($user){

        $model=new M();

        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('gender'=>$_POST['gender']));

        fk('修改成功');

    }else{

        err('身份错误，请重新登录');

    }



}



/*

**生日设置

*/

function setbirthday(){

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["birthday"]))err("请设置生日");

    $user = checkToken($token);

    if($user){

        $model=new M();

        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('birthday'=>$_POST['birthday']));

        fk('修改成功');

    }else{

        err('身份错误，请重新登录');

    }



}



/*

**邮箱设置

*/

function setemail(){

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["email"]))err("请设置邮箱");

    $user = checkToken($token);

    if($user){

        $model=new M();

        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('email'=>$_POST['email']));

        fk('修改成功');

    }else{

        err('身份错误，请重新登录');

    }



}



/*

**qq设置

*/

function setqq(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["setqq"]))err("请设置qq");
    $user = checkToken($token);
    if($user){

        $model=new M();

        $model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('im_qq'=>$_POST['setqq']));
        fk('修改成功');

    }else{

        err('身份错误，请重新登录');

    }

}



/*

**我的团队（我的会员,商家）

*/

function myteam(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["type"]))err("请确认会员类型");
    $type=$_POST['type'];
    $user = checkToken($token);
    $where='';
    $selectvalue=$_POST['user_value'];
    if(!empty($_POST['user_value']))
    {
        if(is_numeric($selectvalue))
        {
            $where='and phone_mob='.$_POST['user_value'];
        }else
        {
            $where='and real_name=\''.$_POST['user_value'].'\'';
        }
    }
    if($_POST["page"]=="0"||$_POST["page"]==""){
        $page='1';
    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
    if($user){
        $model=new M();
        $count=$model->query('select count(user_id) as id from ecm_member where pid = '.$user['user_id'].' and type = '.$type.' '.$where.' limit '.$startpage.','.$pagecount.'');
        $count=$count[0]['id'];
        $totalpage=ceil($count/$pagecount);
        $data=$model
            ->query('select user_id,user_name,real_name,type,reg_time,type,portrait from ecm_member where pid = '.$user['user_id'].' and type = '.$type.'  '.$where.' limit '.$startpage.','.$pagecount.'');     
        outputJson('ok','用户信息',$data,$totalpage,$count);
    }else{
        err('身份错误，请重新登录');
    }
}
/*
** 我的会员（商家）
*/
function myteamshop(){
	$token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    $where='';
    $selectvalue=$_POST['user_value'];
    if(!empty($_POST['user_value']))
    {
        if(is_numeric($selectvalue))
        {
            $where='and ecm_store.tel='.$_POST['user_value'];
        }else
        {
            $where='and ecm_store.store_name=\''.$_POST['user_value'].'\'';
        }
    }
    if($_POST["page"]=="0"||$_POST["page"]==""){
        $page='1';
    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
    if($user){
        $model=new M();
        $count=$model->query('select count(user_id) as id from ecm_member inner join ecm_store on ecm_member.user_id = ecm_store.store_id where pid = '.$user['user_id'].' and type = 2 '.$where.' order by reg_time desc limit '.$startpage.','.$pagecount.'');
        $count=$count[0]['id'];
        $totalpage=ceil($count/$pagecount);
        $data=$model
            ->query('select store_name,store_logo,owner_name,tel,add_time from ecm_member inner join ecm_store on ecm_member.user_id = ecm_store.store_id where pid = '.$user['user_id'].' and type = 2  '.$where.' order by reg_time desc limit '.$startpage.','.$pagecount.'');
        outputJson('ok','用户信息',$data,$totalpage,$count);
    }else{
        err('身份错误，请重新登录');
    }
}


/*
** 我的会员 （代理）
*/
function myteamagent(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    $where = 'and type in(3,4,5,6,7)';
    //搜索
    $select_value=$_POST["select_value"];
    if(!empty($select_value))
    {
        if(is_numeric($select_value))
        {
            $where.='and phone_mob='.$select_value;
        }else
        {
            $where.='and real_name=\''.$select_value.'\'';
        }
    }
    if($user){
        $model=new M();
        $count=$model
            ->query('select count(user_id) as id from ecm_member where pid = '.$user['user_id'].' '.$where.'');
        $count=$count[0]['id'];
        $data=$model
            ->query('select type from ecm_member where pid = '.$user['user_id'].' '.$where.' group by type');
        //echo $model->getsql();die;
        foreach ($data as $b=> $val){
            $data[$b]['user']=$model->query('select user_id,user_name,real_name,type,reg_time,type,portrait from ecm_member where pid = '.$user['user_id'].' and type= '.$val['type'].'');

        }
        pageJson('ok',"订单信息",$data,$count);
    }else{
        err('身份错误，请重新登录');
    }
}

/*
**辖区会员
*/
function jurisdiction(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["type"]))err("请确认会员类型");
    $type=$_POST['type'];

    $user = checkToken($token);
    if($_POST['name']=="username"){
        if($_POST['user_value']==""){
            $where = 'and 1=1';
        }else{
            $where = 'and  real_name= "'.$_POST['user_value'].'"';
        }
    }else if($_POST['name']=="phone"){
        if($_POST['user_value']==""){
            $where = 'and 1=1';
        }else{
            $where = 'and phone_mob = "'.$_POST['user_value'].'"';
        }
    }else if($_POST['name']==""){
        $where = 'and 1=1';
    }
    if($_POST["page"]=="0"||$_POST["page"]==""){
        $page='1';
    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
    if($user){
        $model=new M();
        $array=$model
            ->query('select user_id,ahentarea,type,province,city,area from ecm_member where user_id = '.$user['user_id'].'');
        /***fzq
        2016/11/24

         ***/
        if($array[0]['type'] =='4'){
            $whe ='and opid= '.$user['user_id'].'';
        }
        if($array[0]['type']=='5'){
            $whe ='and area= '.$array[0]['ahentarea'].'';
        }else if($array[0]['type']=='6'){
            $whe ='and city= '.$array[0]['ahentarea'].'';
        }else if($array[0]['type']=='7'){
            $whe ='and province= '.$array[0]['ahentarea'].'';
        }

        $count=$model->query('select count(user_id) as id from ecm_member where type = '.$type.' '.$whe.' '.$where.' limit '.$startpage.','.$pagecount.'');
        $count=$count[0]['id'];
        $totalpage=ceil($count/$pagecount);
        $data=$model
            ->query('select user_id,user_name,real_name,type,reg_time,type,portrait from ecm_member where type = '.$type.' '.$whe.' '.$where.' order by reg_time desc limit '.$startpage.','.$pagecount.'');


        outputJson('ok','用户信息',$data,$totalpage,$count);


    }else{

        err('身份错误，请重新登录');

    }

}
//辖区商家



function jurisdictionshop(){
    $token = urlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
   
    $user = checkToken($token);
     $where='';
    $selectvalue=$_POST['user_value'];
    if(!empty($_POST['user_value']))
    {
        if(is_numeric($selectvalue))
        {
            $where='and ecm_store.tel='.$_POST['user_value'];
        }else
        {
            $where='and ecm_store.store_name=\''.$_POST['user_value'].'\'';
        }
    }
    if($_POST["page"]=="0"||$_POST["page"]==""){
        $page='1';
    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
	
    if($user){
        $model=new M();
        $array=$model
            ->query('select user_id,ahentarea,type,province,city,area from  ecm_member where user_id = '.$user['user_id'].'');
		
        if($array[0]['type'] =='4'){
            $whe ='and opid= '.$user['user_id'].'';
        }
        if($array[0]['type']=='5'){
            $whe ='and ecm_member.area= '.$array[0]['ahentarea'].'';
        }else if($array[0]['type']=='6'){
            $whe ='and ecm_member.city= '.$array[0]['ahentarea'].'';
        }else if($array[0]['type']=='7'){
            $whe ='and ecm_member.province= '.$array[0]['ahentarea'].'';
        }

        $count=$model->query('select count(user_id) as id from ecm_member where type = 2 '.$whe.' '.$where.' limit '.$startpage.','.$pagecount.'');
        $count=$count[0]['id'];
        $totalpage=ceil($count/$pagecount);
        $data=$model
            ->query('select store_name,store_logo,owner_name,tel,add_time from ecm_member inner join ecm_store on ecm_member.user_id = ecm_store.store_id where type = 2 '.$whe.' '.$where.' order by ecm_member.reg_time desc limit '.$startpage.','.$pagecount.'');

		
        outputJson('ok','用户信息',$data,$totalpage,$count);
    }else{

        err('身份错误，请重新登录');

    }

}

/*

**二维码

*/

function recode(){
    $token = urlencode($_GET['token']);
    if(!isset($_GET["token"]))err("请先登录");
    $user = checkToken($token);
    if($user){
        $model=new M();
        $data=$model->query('select user_id,phone_mob from ecm_member where user_id = '.$user['user_id'].'');

        $phone=$data[0]['phone_mob'];
        $link = conf('SITE_URL').'/api/index.php?n=user&f=register&key='.$phone;
        include 'phpqrcode.php';



        //$value= 'http://www.baidu.com'; //二维码内容

        $errorCorrectionLevel = 'L';//容错级别

        $matrixPointSize = 6;//生成图片大小

        //生成二维码图片

        QRcode::png($link, false, $errorCorrectionLevel, $matrixPointSize, 2);

        exit;
    }else{
        err('身份错误，请重新登录');
    }
}






/*

**

*/



function gettoken(){

    $token = urlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $id=$user['user_id'];

        $http=conf('SITE_URL');

        $data=$http.'/api/index.php?n=user&f=recode&user_id='.base64_encode($id);

        fk('会员信息',$data);

    }else{

        err('身份错误，请重新登录');

    }

}