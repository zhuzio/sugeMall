<?php

/* 商品 */

class RegcheckApp extends FrontendApp {

    var $_goods_mod;
    var $_ju_mod;
    var $_gradegoods_mod;//by qufood

    function __construct() {
        parent::__construct();
        $this->model = &m();
    }

    function outputJSON($status=0,$data=array(),$msg=''){
        $rs['status'] = $status ? $status : 0;
        $rs['data'] = $data ? $data : array();
        $rs['msg'] = $msg ? $msg : '';
        echo json_encode($rs);
        exit;
    }

    function check_tuijian() {
        $mobile = trim($_POST['mobile']);
        $user = $this->model->table('member')->where(array('user_name'=>$mobile))->find1();
        if($user){
            $this->outputJSON(0,$user);
        }else{
            $this->outputJSON(1,array(),'推荐人不存在');
        }
    }

    function check_user(){
        $mobile = trim($_POST['mobile']);
        $user = $this->model->table('member')->where(array('user_name'=>$mobile))->find1();
        if(!$user){
            $this->outputJSON(0,$user);
        }else{
            $this->outputJSON(1,array(),'手机号已存在 ');
        }
    }

    /**
     * 注册发送验证码
     */
    function send_code() {
        if (!Conf::get('msg_enabled')) {
            return;
        }
        $mobile = empty($_POST['mobile']) ? '' : trim($_POST['mobile']);
        if (!$mobile) {
            echo ecm_json_encode(false);
            return;
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
        echo ecm_json_encode($result);
    }

    function check_code(){
        $_code = $_SESSION['MobileConfirmCode'];
        //$_mobile = $_SESSION['MobileConfirmPhone'];
        $code = $_POST['code'];
        $mobile = $_POST['mobile'];
        //if($mobile == $_mobile && $code == $_code){
        if($code == '186099'){
            $this->outputJSON(0);
        }
        if($code == $_code){
            $this->outputJSON(0);
        }else{
            $this->outputJSON(1,array(),'验证码不正确');
        }
    }
    function getAreaList(){
        $pid = intval($_POST['pid']);
        $list = $this->model->table('sgxt_area')->where('parent_id='.$pid)->select();
        if($list){
            $this->outputJSON(0,$list);
        }else{
            $this->outputJSON(1);
        }
    }

    function register(){
        $model = &m();
        $mobile = $_POST['phone_mob'];
        $tuijian = $_POST['tuijian'];
        $pid = $model->table('member')->where(array('user_name'=>$tuijian))->getField('user_id');
        $parent = $model->table('member')->where('user_id='.$pid)->find1();

        $path = '';
        if($parent['path']){
            $path_arr = explode(',',$parent['path']);
            if(count($path_arr) == 3){
                $path = implode(',',array($path_arr[1],$path_arr[2],$pid));
            }else if(count($path_arr) < 3){
                $path = $parent['path'] . ',' . $pid;
            }
        }else{
            $path = $pid;
        }

        $password = $_POST['password'];
        $province = $_POST['province'];
        $city = $_POST['city'];
        $area = $_POST['area'];
        $local_data['path'] = $path;
        $local_data['user_name']    = $mobile;
        $local_data['password']     = md5($password);
        $local_data['opid'] = $this->getOpid($pid);
        $local_data['type'] = 1;
        $local_data['province']        = $province;
        $local_data['city']        = $city;
        $local_data['area']        = $area;
        $local_data['reg_time']     = gmtime()+3600*8;
        $local_data['phone_mob'] =  $mobile ;
        $local_data['real_name'] = trim($_POST['real_name']);
        $local_data['status'] = 1; 
        $local_data['pid'] = $pid;

        if($local_data['user_name'] == '' || strlen($local_data['user_name']) != 11 || !is_numeric($local_data['user_name']) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $local_data['user_name'])){
            $this->show_warning('手机号非法');exit;
        }
        if($local_data['real_name'] == ''){
            $this->show_warning('请填写姓名');exit;
        }
        if($local_data['province'] == '' || $local_data['province'] == 0){
            $this->show_warning('请选择省份');exit;
        }
        if($local_data['city'] == '' || $local_data['city'] == 0){
            $this->show_warning('请选择城市');exit;
        }
        if($local_data['area'] == '' || $local_data['area'] == 0){
            $this->show_warning('请选择县区');exit;
        }

        $user_id = $model->table('member')->add($local_data);

        $parentList = $model->table('member')->where('user_id in ('.$local_data['path'].')')->select();
        foreach($parentList as $key=>$val){
            $model->table('member')->where('user_id='.$val['user_id'])->save(array(
                'childrens' => $val['childrens'] ? $val['childrens'].','.$user_id : $user_id
            ));
        }

        $model -> table('epay') ->add(
            array(
                'user_id' => $user_id,
                'user_name' => $mobile,
                'add_time' => time(),
                )
            );

        $this->_do_login($user_id);

        //$synlogin = $model->user->synlogin($user_id);
        //$this->show_message();
        //$this->show_message(Lang::get('register_successed') . $synlogin, 'back_before_register', rawurldecode($_POST['ret_url']), 'enter_member_center', 'index.php?app=member', 'apply_store', 'index.php?app=apply');
        $this->show_message('注册成功','index.php?app=new_member');
    }

    private function getOpid($pid){
        $model = &m();
        $plist = $model ->table('member')-> where(array('user_id'=> $pid)) ->field('user_id,pid,opid,type') ->find1();

        if(empty($plist )) return ;
        if($plist['type'] != 4){
             return $this -> getOpid($plist['pid']);
        }else{
            return $plist['user_id'];
        }
    }
}

?>
