<?php
/*
*DESC   账户管理控制器
*
*DATE   2016/06/19
*
*@author  FENG  
*/
	header('Content-type:text/html;charset=utf-8');
	class account_ManageApp extends MemberbaseApp{
		var $_feed_enabled =false;
		var $manage_mod;
		function __construct(){
			$this->MemberApp();
			$this->userinfo =$_SESSION['user_info'];
		}

		function MemberApp(){
			parent::__construct();
			$ms = & ms();
			$this->_feed_enabled =$ms->feed->feed_enabled();
			$this->assign('feed_enabled',$this->_feed_enabled);	
			$this->manage_mod = &m('member');
			//实例化一个空类
			$this->model =&m();
		}

		//显示用户信息操作
		function showData(){
			$userinfo =$this->userinfo;
			$id =$userinfo['user_id'];
			$data =$this->manage_mod->where(array('user_id'=>$id))->field('user_id,type,status,user_name,email,password,real_name,gender,birthday,phone_tel,phone_mob,lastip,portrait,recode')->find1();
			$this->assign('userinfo',$data);
			$this->display('newapp/account.manage.html');
		}


		//修改页面显示1
		function save_pwdshow(){
			if(!IS_POST){
				$this->display('newapp/password.save.html');
			}
			
		}	

		//验证手机号是否存在
		function tel_phone(){
			$phone =trim($_GET['phone']);
			$res =$this->manage_mod->where(array('user_name'=>$phone,'user_id'=>$this->userinfo['user_id']))->field('user_id,user_name,phone_mob')->find1();
			if($res){
				echo '1'; //正确 
			}else{
				echo '2';  //错误
			}
		}

		//修改页面显示2
		function save_pwdshow2(){
			$vcode =trim($_GET['vcode']);
			
			if(empty($vcode)){
				return ;
			}

			if($vcode != $_SESSION['MobileConfirmCode']){
				echo '0'; //失败
			}else{
				
				$_SESSION['is_check'] = 1;
				echo '1';//成功
			}
			
		}

		//修改密码页面3
		function save_pwdshow3(){
			if(!$_SESSION['is_check']){
				echo '短信验证失败';
			}
			if(!IS_POST){
				$this->display('newapp/password.reset.html');
			}

		}

		//执行用户密码修改操作
		function save_pwd(){
			$pwd =trim($_POST['upwd']);
			$pd =md5($pwd);
			$id =$this->userinfo['user_id'];
			$data =array('password'=>$pd);
			$res =$this->manage_mod->where(array('user_id'=>$id))->save($data);
			if($res){
				 $this->show_success('修改成功','index.php?app=account_manage&act=showData');
			}else{
				 $this->show_error('修改失败','index.php?app=account_manage&act=save_pwdshow');
			}
		}
		
		//支付密码页面
		function show_paypass(){
			if(!IS_POST){
				$this->display('newapp/save_paypass.html');
			}
		}


		//支付密码2
		function show_paypass2(){
			$vcode =trim($_GET['vcode']);
			if(empty($vcode)){
				return ;
			}

			if($vcode != $_SESSION['MobileConfirmCode']){
				echo '0'; //失败
			}else{
				
				$_SESSION['is_check'] = 1;
				echo '1';//成功
			}
		}



		//支付密码页面3
		function show_paypass3(){
			if(!$_SESSION['is_check']){
				echo '短信验证失败';
			}
			if(!IS_POST){
				$this->display('newapp/pay_passreset.html');
			}

		}


		//执行修改支付密码操作
		public function save_paypass(){
			$pwd =trim($_POST['passwd']);
			// var_dump($pwd);
			$pass =md5($pwd);
			$id =$this->userinfo['user_id'];
			$data =array('zf_pass'=>$pass);
			$res =$this->model->table('epay')->where(array('user_id'=>$id))->save($data);
				if($res){
				 $this->show_success('修改成功','index.php?app=account_manage&act=showData');
			}else{
				 $this->show_error('修改失败','index.php?app=account_manage&act=show_paypass');
			}

			
		}

        public function my_qrcode(){
            $key = $this->userinfo['user_name'];
            $link = SITE_URL.'/index.php?app=qrcode&url='.urlencode(SITE_URL.'/index.php?app=member&act=register&key='.$key);
            $this->assign('links' , $link);
            $this->display('newapp/my.qrcode.html');
        }


        //微信解除绑定
        public function wx_delete(){

        	$id =trim($_GET['id']);

        	//检测是否已经绑定	
        	$rs =$this->model->table('weixin_user')->where(array('user_id'=>$id))->find1();
        	// var_dump($rs);die;
        	if(empty($rs)){
        		$this->show_error('你还没有绑定微信','index.php?app=account_manage&act=showData');
        		die;
        	}

        	//解除绑定
        	$res =$this->model->table('weixin_user')->where(array('user_id'=>$id))->delete();
        	if($res){
        		$this->show_success('解除成功','index.php?app=account_manage&act=showData');
        	}else{
        		$this->show_error('解除失败,请重试','index.php?app=account_manage&act=showData');
        	}




        }

	}	


?>