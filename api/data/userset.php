<?php

/*

**银行卡

*/

function card(){

	$token = urlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	$user = checkToken($token);

	if($user){

		$model=new M();

		$data=$model

			->table('epay_bank')

			->field('bank_id,bank_name,bank_num,status')

			->where(array('user_id'=>$user['user_id']))

			->select();	

		//echo $model->getsql();die;

		fk('银行卡信息',$data);

	}else{

		err('身份错误，请重新登录');

	}



}

/*

**设置默认银行卡

*/

function setcard(){

	$token = urlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["bank_id"]))err("请选择银行卡");

	$user = checkToken($token);

	if($user){

		$model=new M();

		$model

			->table('epay_bank')

			->where(array('user_id'=>$user['user_id']))

			->update(array('status'=>'1'));

		$data=$model

			->table('epay_bank')

			->where(array('bank_id'=>$_POST["bank_id"]))

			->update(array('status'=>'0'));

		//echo $model->getsql();die;

		fk('设置成功');

	}else{

		err('身份错误，请重新登录');

	}

}



/*

**删除银行卡

*/

function delcard(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["bank_id"]))err("请选择银行卡");

	$user = checkToken($token);

	if($user){

		$model= new M();

		$model->table('epay_bank')

			->where(array('bank_id'=>$_POST["bank_id"]))

			->delete();

		fk('删除成功');

	}else{

		err('身份错误，请重新登录');

	}

}



/*

**银行卡下拉

*/

function bank_list(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	$user = checkToken($token);

	if($user){

		$model= new M();

		$data=$model->table('sgxt_bank')

			->select();

		fk('银行卡信息',$data);

	}else{

		err('身份错误，请重新登录');

	}



}

/*

**添加银行卡

*/

function addcard(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["bank_name"]))err("请确认银行名称");

	if(!isset($_POST["bank_num"]))err("请确认卡号");

	if(!isset($_POST["user_name"]))err("请确认持卡人姓名");
	if(!isset($_POST["bank_code"]))err("参数错误");
	if(empty($_POST["id_card"]))err("请输入身份证号");
	$user = checkToken($token);

	if($user){

		$model= new M();

		$model

			->table('epay_bank')

			->where(array('user_id'=>$user['user_id']))

			->update(array('status'=>'1'));

		$data=array(

		'bank_name'	=>	$_POST["bank_name"],

		'bank_num'	=>	$_POST["bank_num"],
			'bank_code'	=>	$_POST["bank_code"],
			'id_card'	=>	$_POST["id_card"],
		'user_id'	=>	$user["user_id"],

		'account_name'	=>$_POST["user_name"],

		'status'	=>	$_POST["type"]

		);

		

		$model->table('epay_bank')

			->insert($data);

		fk('添加成功');

	}else{

		err('身份错误，请重新登录');

	}

}



/*

**反馈与意见

*/

function feedback(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["feedback"]))err("请确认反馈内容");

	if(!isset($_POST["user_qq"]))err("请确认qq");

	if(!isset($_POST["phone"]))err("请确认手机号");

	

	$user = checkToken($token);

	$data=array(

		'feedback'		=>	$_POST["feedback"],

		'user_qq'		=>	$_POST["user_qq"],	

		'user_id'		=>	$user["user_id"],

		'createtime'	=>	time(),

		'phone'			=>	$_POST["phone"]

		);

	if($user){

		$model= new M();

		$model->table('sgxt_feedback')

			->insert($data);

		fk('感谢您的反馈意见,我们会尽快做出调整.');

	}else{

		err('身份错误，请重新登录');

	}

}



/*

**联系我们

*/

function contactus(){

	$phone = '4006203777';

	$data = array(

		'tel'=>	$phone 

	);

	fk('添加成功',$data);

}



/*

**会员升级

*/

function upgrade(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["type"]))err("请先选择升级的类型");

	$user = checkToken($token);

	if($user){
		$model= new M();
		$req =$model->table('sgxt_req')->where(array('userid'=>$user['user_id'],'status'=>1))->find();
		
		if($req) err('不能重复提交申请!');

		if($_POST['type']=='5'||$_POST['type']=='6'||$_POST['type']=='7'){

			if($_POST['type']=='5'){

				$array=$model->table('member')->where(array('ahentarea'=>$_POST["region_id"],'type'=>$_POST['type']))->select();

				if($array){

					err('该地区已有县代');

				}else{

					$data=array(

					'type'=>$_POST["type"],	

					'userid'=>$user["user_id"],

					'createtime'=>time(),

					'status'=>'1',

					'areaid'=>$_POST["region_id"]

						);

					$model->table('sgxt_req')

					->insert($data);

					fk('已申请，等待审核');

				}

			}else if($_POST['type']=='6'){

				$array=$model->table('member')->where(array('ahentarea'=>$_POST["region_id"],'type'=>$_POST['type']))->select();

				if($array){

					err('该地区已有市代');

				}else{

					

					$data=array(

						'type'=>$_POST["type"],	

						'userid'=>$user["user_id"],

						'createtime'=>time(),

						'status'=>'1',

						'areaid'=>$_POST["region_id"]

						);
					$model->table('sgxt_req')

					->insert($data);

					fk('已申请，等待审核');

				}

			}else if($_POST['type']=='7'){

				$array=$model->table('member')->where(array('ahentarea'=>$_POST["region_id"],'type'=>$_POST['type']))->select();

				if($array){

					err('该地区已有省代');

				}else{

					$data=array(

					'type'=>$_POST["type"],	

					'userid'=>$user["user_id"],

					'createtime'=>time(),

					'status'=>'1',

					'areaid'=>$_POST["region_id"]

						);
					$model->table('sgxt_req')

					->insert($data);

					fk('已申请，等待审核');

				}

			}

		}else{

			$areaid =0;
			$data=array(
				'type'=>$_POST["type"],	
				'userid'=>$user["user_id"],
				'createtime'=>time(),
				'areaid'=>$areaid,
				'status'=>'1'
			);

		}

		$model->table('sgxt_req')->insert($data);

		fk('已申请,等待审核');

	}else{

		err('身份错误，请重新登录');

	}

}


/*

**上传图片

*/

function uplode(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	$user = checkToken($token);

	if($user){

		$model= new M();

		$model

			->table('epay_bank')

			->where(array('user_id'=>$user['user_id']))

			->update(array('portrait'=>$_POST['url']));

		fk('添加成功');

	}else{

		err('身份错误，请重新登录');

	}

}





?>