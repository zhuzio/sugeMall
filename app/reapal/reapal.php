<?php
error_reporting(0);
/* ����ʺ� BEGIN */
define('ROOT_PATH', dirname(__FILE__) . "/../..");
include(ROOT_PATH . '/eccore/ecmall.php');
/* ����������Ϣ */
ecm_define(ROOT_PATH . '/data/config.inc.php');
include(ROOT_PATH . '/eccore/model/model.base.php');
define('CHARSET', 'utf-8');
$settings = include(ROOT_PATH . '/data/settings.inc.php');
require_once ("config.php");



/**
 * 1����ȡ������Ϣ
 */
$no_order = $_REQUEST['dingdan'];
$model = & m();
$order = array();
function getOrderinfo($dingdan){
	global $model;
	//��һλΪ0 Ĭ��Ϊ���ֶ���

	if($dingdan[0] == 'P'){
		$orderinfo = $model->table('sgxt_order')->where(array('orderid'=>$dingdan))->find1();
		if(empty($orderinfo)){
			echo 'order error';die;
		}
		if($orderinfo['status']){
			echo '������֧��1';
			die;
		}
		$order['userid'] = $orderinfo['userid'];
		$user = $model->table('member')->where(array('user_id'=>$order['userid']))->find1();
		$order['order_id'] = $orderinfo['order_id'];
		$order['user'] = $user;
		$order['user_mobile'] = $user['user_name'];
		$order['user_reg_time'] = date('YmdHis',$user['reg_time']);
		$order['userid'] = $orderinfo['userid'];
		$order['truename'] = $orderinfo['truename'];
		$order['cz_money'] = $orderinfo['amount'];
	}else{
		//��Ʒ����֧��
		$orderinfo = $model -> table('order')->where(array('order_sn'=>$dingdan))->find1();
		if(empty($orderinfo)){
			echo 'order error';
			die;
		}

		if($orderinfo['status'] != '11'){
			echo '������֧��';
			die;
		}
		$order['order_id'] = $orderinfo['order_id'];
		$order['userid'] = $orderinfo['buyer_id'];
		$user = $model->table('member')->where(array('user_id'=>$order['userid']))->find1();
		$order['user'] = $user;
		$order['user_mobile'] = $user['user_name'];
		$order['user_reg_time'] = date('YmdHis',$user['reg_time']);
		$order['userid'] = $orderinfo['buyer_id'];
		$order['truename'] = $orderinfo['buyer_name'];
		$order['cz_money'] = $orderinfo['order_amount'];
	}
	return $order;
}
$order = getOrderinfo($no_order);
/**
 * 2����ȡ��ǰ�û������п�
 */
if($_POST['cardno']){
	$bind_bank = $model->table('member_bind_bank')->where('bank_num='.$_POST['cardno'].' and status=1')->find1();
}else{
	$bind_bank = $model->table('member_bind_bank')->where('user_id='.$order['userid'].' and status=1 and is_default=1')->find1();	
}
if(isset($_POST['payform'])){
	if($_POST['payform'] == 1){
		$bank_name = $_REQUEST['bank_name'];
		$bank_type = $_REQUEST['bank_type'];
		$real_bank_num = $bank_num = $_REQUEST['cardno'];
		$bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
		include 'temp/carddetail.php';
	}else if($_POST['payform'] == 3){
		$bank_name = $_REQUEST['bank_name'];
		$bank_type = $_REQUEST['bank_type'];
		$real_bank_num = $bank_num = $_REQUEST['cardno'];
		$bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);
		include 'temp/bindcarddetail.php';
	}
}else{	
	if($bind_bank){
		$bank_num = $bind_bank['bank_num'];
		$bank_num = substr($bank_num, 0,6).'********'.substr($bank_num, -4);	
	}else{
		$bank_num = '';
	}	
	include 'temp/index.php';
}
