<?php
header("Access-Control-Allow-Origin: *");
/*
*  2016/06/29
*  fzq
*  升級請求
*/
header('Content-Type:text/html;charset=utf-8');
class reqApp extends MemberbaseApp{

    var $_feed_enabled = false;
    function __construct() {
        $this->MemberApp();
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        $this->userinfo = $_SESSION['user_info'];
        $this->model = & m();
    }

    //会员升级请求
    public function upgradeRequest(){
    	if(!IS_POST){
    		$this->display('newapp/upgrade.html');
    	}else{

    		if($this->userinfo['type']==1 || $this->userinfo['type']==3){
    			
	    		$type =trim($_POST['type']);
	    		$uid =$this->userinfo['user_id'];
	    		$areaid = 0;	
	    		if($type == 5){
	    			$areaid = I('post.county');
	    		}else if($type == 6){
	    			$areaid = I('post.city');
	    		}elseif($type == 7){
	    			$areaid = I('post.province');
	    		}
	    		$data =array(
	    			'type' => $type,
	    			'userid' => $uid,
	    			'areaid' => $areaid,
	    			'status' => 1,
	    			'createtime' => time(),
	    			);
	    		$rs =$this->model->table('sgxt_req')->where(array('userid'=>$uid,'status' => 1))->find1();
	    		if($rs){
	    			echo '<script>alert("您的申请已提交过啦");location="index.php?app=req&act=upgradeRequest"</script>';
	    			return;
	    		}else{
	    			$res =$this->model->table('sgxt_req')->add($data);
		    			if($res){
		    				echo '<script>alert("申请提交成功");location="index.php?app=req&act=upgradeRequest"</script>';
		    			}
	    		}
	    		
	    		}

    	}	
    }


    //升级区域
    public function upgradeRegion(){
    	$type = I('get.type');
    	if(empty($type) || $type<5){
    		$this->show_warning('选择类型错误');  
    		return ;
      	}
      	$this->assign('type' ,$type);
    	$this->display('newapp/upgrade.region.html');
    	
    }

    // 列出省份
    public function province(){
    	$pid = I('post.pid');
		$prolist =$this->model->table('sgxt_area')->where(array('parent_id'=>$pid))->select();
		foreach($prolist as $k=>$v){
			$prolist[$k] =$v; 
		}
		$data =json_encode($prolist);
		echo $data;
    }

}

?>