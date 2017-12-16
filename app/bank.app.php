<?php
class bankApp extends MemberbaseApp{

  	var $_feed_enabled = false;
  	var $db; 
  	var $banklist_mod;
    function __construct() {
    	$this->db = new PDO('sqlite:'.'./data/db/bank.db');
    	$this->db->beginTransaction();
      $this->MemberApp();
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        $this->userinfo =$_SESSION['user_info'];
    		$this->member_mod = &m('member');
        $this->model = & m();
        $this->userinfo = $this->model->table('member')->where(array('user_id'=>$_SESSION['user_info']['user_id']))->find1();
    }


    // 绑定银行卡
    public function bind_bankcard(){
        
      $this->display('newapp/bind.bankcard.html');
    }


    //银行卡列表
    public function bank_list(){
    	$banklist =$this->model->table('sgxt_bank_list')->select();
    	$data =json_encode($banklist);
    	echo $data;
    }

    //根据银行列出省份
    public function list_bank_province(){
    	$bank = $_REQUEST['bank'];
		$sth = $this->db->prepare("select province from xbft_bank_mng where bank = '$bank' group by province");		
		$sth->execute();
		$res = $sth->fetchAll();
		unset($res[0]);
		foreach($res as $v){
			$arr[]=$v[0];
		}
		$data =json_encode(array('province'=>$arr));
		echo $data;
    }

   	//根据省份列出城市
   	public function list_bank_area(){
   		$bank =$_REQUEST['bank'];
   		$province =$_REQUEST['prv'];
   		$sth =$this->db->prepare("select area from xbft_bank_mng where province = '$province' and bank = '$bank' group by area");
   		$sth->execute();
   		$res =$sth->fetchAll();
   		// var_dump($res);die;
   		unset($res[0]);
   		foreach($res as $v){
   			$arr[] =$v[0];
   		}
   		$data2 =json_encode(array('area'=>$arr));
   		echo $data2;
   	}


   	//根据城市列出支行
   	public function list_bank_name(){
   		$bank =$_REQUEST['bank'];
   		$prv =$_REQUEST['prv'];
   		$area =$_REQUEST['area'];
		  $sth = $this->db->prepare("select name,code from xbft_bank_mng where province = '$prv' and bank = '$bank'  and area = '$area'");   		
   		$sth->execute();
   		$res =$sth->fetchAll();
   		foreach($res as $re){
			$arr[]=array('name'=>$re['name'],'code'=>$re['code']);
		}
   		$data3 =json_encode($arr);
   		echo $data3;

   	}

    //提交银行卡号---保存
    public function commit_bankcard(){
        if(IS_POST){
              // var_dump($_POST);die;
              $account_name =trim($_POST['account_name']); //户名
              $bank_num =trim($_POST['bank_num']); //银行卡号
              $bank_name =$_POST['bank_name'];   //银行名称
              $bank_code =$_POST['bank_code']; //支行编号
              $bank_codename =$_POST['bank_codename']; //支行名称
              $id_card =trim($_POST['id_card']);//身份证号
              $id =$this->userinfo['user_id'];
              if($bank_num ==''){
                //  echo '<script>alert("银行卡号不能为空");location="index.php?app=bank&act=bind_bankcard";</script>';
              }else{
                    $data =array(
                      'account_name'=>$account_name,
                      'bank_num'=>$bank_num,
                      'bank_name' =>$bank_name,
                      'bank_code'=>$bank_code,
                      'open_bank'=>$bank_codename,
                      'id_card'=>$id_card,
                      'user_id'=>$id
                      );
                    //添加银行卡     
                    $rs =$this->model->table('epay_bank')->where(array('status'=>0,'user_id'=>$this->userinfo['user_id']))->find1();
                    if($rs){
                          $bid =$this->model->table('epay_bank')->where(array('user_id'=>$this->userinfo['user_id']))->add($data);
                          if($bid){
                            $this->show_success('银行卡添加成功','index.php?app=bank&act=manage_bankcard');
                          }else{
                            $this->show_error('银行卡添加失败','index.php?app=bank&act=bind_bankcard');   
                          }
                      }else{
                        //如果没有银行卡 执行添加操作后 再修改默认状态
                        $bid =$this->model->table('epay_bank')->where(array('user_id'=>$this->userinfo['user_id']))->add($data);
                        $status =array('status'=>0);
                        $cid =$this->model->table('epay_bank')->where(array('user_id'=>$this->userinfo['user_id']))->save($status);
                           if($cid){
                              $this->show_success('银行卡添加成功','index.php?app=bank&act=manage_bankcard');

                           }else{
                              $this->show_error('银行卡添加失败','index.php?app=bank&act=bind_bankcard');   

                           }
                      }       

              }
           } 
    }

    //管理银行卡
    public function manage_bankcard(){
      $id =$this->userinfo['user_id'];
      var_dump($id);
         $res = $this->model->query("select ecm_epay_bank.user_id,ecm_epay_bank.bank_name,ecm_epay_bank.bank_id,ecm_epay_bank.status,ecm_epay_bank.bank_num,ecm_sgxt_bank_list.bank_logo from ecm_epay_bank join ecm_sgxt_bank_list on ecm_epay_bank.bank_name=ecm_sgxt_bank_list.bank_name where ecm_epay_bank.user_id={$id}");
         if($res){
	         foreach($res as $k=>$v){
		          $a1 =substr($v['bank_num'],0,4);
		          $a2 =substr($v['bank_num'],-4,4);
		          $v['bank_num'] =$a1.'********'.$a2;
	              $res[$k] =$v;
       			 }
        }else{
          $this->show_error('您还没有绑定银行卡','index.php?app=bank&act=bind_bankcard');
        
         }
      
         $this->assign('bankinfo',$res);
         $this->display('newapp/manage.bankcard.html');
    }


    //删除银行卡
    public function delte_bankcard(){
      $id =$_GET['id'];
      $uid=$this->userinfo['user_id'];

      $res =$this->model->table('epay_bank')->where(array('bank_id'=>$id,'user_id'=>$uid))->delete();
      // var_dump($res);die;
      if($res){
        $this->show_success('删除成功','index.php?app=bank&act=manage_bankcard');

      }else{
        $this->show_error('删除失败','index.php?app=bank&act=manage_bankcard');
      }
    }

    //设置默认银行卡
    public function default_bank(){
      $id =$_GET['id'];
      //进行默认设置之前先修改状态为零的为1
      $rs =array('status'=>1);
      $info =$this->model->table('epay_bank')->where(array('status'=>0,'user_id'=>$this->userinfo['user_id']))->save($rs);
      // 执行修改操作
      $data =array('status'=>0);
      $default =$this->model->table('epay_bank')->where(array('bank_id'=>$id,'user_id'=>$this->userinfo['user_id']))->save($data);
      if($default){
        //修改其它银行卡状态为1
        $this->show_success('设置成功','index.php?app=bank&act=manage_bankcard');

      }else{
        $this->show_error('设置失败','index.php?app=bank&act=manage_bankcard');

      }
    }




}


?>