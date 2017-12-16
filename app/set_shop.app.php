<?php
/**
 * @Author: fzq
 * @Date:   2016-06-27 22:40
 */
header('Content-type:text/html;charset=utf-8');
class set_shopApp extends MemberbaseApp{
	var $_feed_enabled = false;
    function __construct() {
        $this->MemberApp();
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        //余额支付
        $this->epay_mod = & m('epay');
        
        $this->model = & m();
        $this->userinfo = $this->model->table('member')->where(array('user_id' => $_SESSION['user_info']['user_id']))->find1();
    }

    //商品分类
    function goods_cate(){
		if($this->userinfo['type'] == 2){
			$uid =$this->userinfo['user_id'];
			$cate_list =$this->model->table('sgxt_class_goods')->where(array('store_id'=>$uid,'state'=>1))->select();
			$this->assign('list',$cate_list);
            $hash = get_hash();
            $this->assign('_hash_',$hash);
			$this->display('newapp/goods_cate.html');
		} 	

    }

    //删除商品分类
    function delete_goodscate(){
    	$cate_id =trim($_GET['c_id']);
    	$did =$this->model->table('sgxt_class_goods')->where(array('class_id'=>$cate_id))->delete();
    	if($did){
            $this->show_success('删除分类成功','index.php?app=set_shop&act=goods_cate');
    	 }else{
            $this->show_error('删除分类失败','index.php?app=set_shop&act=goods_cate');
    	}
    }
    function delete_goodscate_ajax(){
        $cate_id =trim($_GET['c_id']);
        $did =$this->model->table('sgxt_class_goods')->where(array('class_id'=>$cate_id))->delete();
        if($did){
            echo json_encode(array('status'=>0));exit;
            //$this->show_success('删除分类成功','index.php?app=set_shop&act=goods_cate');
         }else{
            echo json_encode(array('status'=>1,'msg'=>'删除分类失败'));exit;
            //$this->show_error('删除分类失败','index.php?app=set_shop&act=goods_cate');
        }
    }

    //添加商品分类
    function insert_goodscate(){
    	$cate_name =trim($_POST['goodscate']);
        if(empty($cate_name)){
             $this->show_warning('商品分类不能为空');
             return;
        }
    	$store_id =$this->userinfo['user_id'];
    	$data =array('name'=>$cate_name,'store_id'=>$store_id);
    	// var_dump($cate_name);die;
    	$aid =$this->model->table('sgxt_class_goods')->add($data);
    	// var_dump($aid);
    	if($aid){
            $this->show_success('分类添加成功','index.php?app=set_shop&act=goods_cate');
    	 }else{
            $this->show_error('分类添加失败','index.php?app=set_shop&act=goods_cate');
    	}


    }


















}



?>