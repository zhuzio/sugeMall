<?php

/*

**收货地址

*/



//收货地址列表

function addrList(){

	$token =urlencode($_POST['token']);	

	if(!isset($_POST['token'])) err('请先登录');

	//解析token

	$user =checkToken($token);

	if($user){

		$address =new M();

		$list =$address

			->field('addr_id,address,consignee,phone_mob,phone_tel,type,region_id,region_name')

			->table('address')

			->where('user_id='.$user['user_id'])

			->select();

		if($list){

			fk('收货地址',$list);

		}else{

		

			fk('收货地址为空，请添加收货地址');

		}

	}else{

		err('身份错误,请重新登录');

	}



} 



//三级联动 省

function province(){

	$address =new M();

	$list =$address

		->table('sgxt_area')

		->field('name,id')

		->where(array('parent_id'=>'1'))

		->select();

	if($list){

		fk('地区',$list);

	}



}

//三级联动 市



function city(){



	if(!isset($_POST['id'])) err('请先确认所选的省份');
	$address =new M();

	$list =$address

		->table('sgxt_area')

		->field('name,id')

		->where(array('parent_id'=>$_POST['id']))

		->select();

	if($list){

		fk('地区',$list);

	}



}



//三级联动 县

function area(){



	if(!isset($_POST['id'])) err('请先确认所选的省份');

	$address =new M();

		$list =$address

			->table('sgxt_area')

			->field('name,id')

			->where(array('parent_id'=>$_POST['id']))

			->select();

		if($list){

			fk('地区',$list);

		}



}


function getChildArea(){
	$id = intval($_GET['id']) ? intval($_GET['id']) : 1;
	$list = array();
	// $link = @mysql_connect('localhost','test_4gxt_com','test_4gxt_com1860');
	// mysql_select_db('test_4gxt_com',$link);
	
	// $query = mysql_query('select * from ecm_sgxt_area where parent_id=1',$link);	
	// var_dump($query);
	// while($row = mysql_fetch_array($query)){
	// 	var_dump($row);
	// }
	
	$address =new M();
	$list =$address
			->table('sgxt_area')
			->field('name,id')
			->where(array('parent_id'=>$id))
			->select();
	
	fk('ok',$list);
}



//添加收货地址

function add(){

	$token =urlencode($_POST['token']);

	if(!isset($_POST['token'])) err('请先登录');

	/*if(!isset($_REQUEST["consignee"]))err("请填写收货人姓名!");

	if(!isset($_REQUEST["region_id"]))err("区域id!");

	if(!isset($_REQUEST["region_name"]))err("请填写区域!");

	if(!isset($_REQUEST["address"]))err("请填写详细地址!");

	if(!preg_match("/1[345678]{1}\d{9}$/",strval(@$_REQUEST["phone_tel"])))err("手机号格式错误,请检查!");*/

	$user =checkToken($token);

	//var_dump($user);die;

	if($user){

		$data =array('user_id'		=>$user['user_id'],

					 'consignee'	=>$_REQUEST['consignee'],

					 'phone_tel'	=>$_REQUEST['phone_tel'],

					 'type'			=>$_REQUEST['type'],

					 'region_id'	=>$_REQUEST['region_id'],	//区域id

					 'region_name'	=>$_REQUEST['region_name'],

					 'address'		=>$_REQUEST['address']

		);

		$shouhuo =new M();

		$shouhuo

			->table('address')

			->where(array('user_id'=>$user['user_id']))

			->update(array('type'=>'0'));

		$add =$shouhuo->table('address')->insert($data);
		//echo $shouhuo->getsql();die;
		if($add){

			fk('添加成功');

		}else{

			err('收货地址添加失败');

		}

	}else{

		err('身份错误,请重新登录');

	}

}

/*

**编辑收货地址默认数据

*/

function eitlist(){

	$token =urlencode($_POST['token']);	

	if(!isset($_POST['token'])) err('请先登录');

	//if(!isset($_POST['addr_id'])&&) err('确认选择要编辑的数据');

	$user =checkToken($token);

	if($user){

		$address =new M();

		$list =$address->table('address')->where('addr_id='.$_POST['addr_id'])->find();

		if($list){

			fk('默认数据',$list);

		}else{

			err("数据获取失败");

		}

	}else{

		err('身份错误,请重新登录');

	}

}



/*

**编辑收货地址

*/

function eitaddress(){

	$token =urlencode($_POST['token']);	

	if(!isset($_POST['token'])) err('请先登录');

	if(!isset($_POST['addr_id'])) err('请先确认数据');

	if(!isset($_POST['consignee'])) err('请先确认数据');

	if(!isset($_POST['region_name'])) err('请先确认数据');

	if(!isset($_POST['phone_tel'])) err('请先确认数据');

	if(!isset($_POST['address'])) err('请先确认数据');

	if(!isset($_POST['type'])) err('请先确认数据');



	$data = array(

			'consignee'		=>	$_POST['consignee'],

			'region_name'	=>	$_POST['region_name'],

			'phone_tel'		=>	$_POST['phone_tel'],

			'address'		=>	$_POST['address'],

			'type'			=>	$_POST['type']

	);

	$user =checkToken($token);

	if($user){

		$address =new M();

		if($_POST['type']=='1'){

			$address

			->table('address')

			->where(array('user_id'=>$user['user_id']))

			->update(array('type'=>'0'));

		}

		$list =$address->table('address')->where('addr_id='.$_POST['addr_id'])->update($data);

		if($list){

			fk('修改成功');

		}else{

			err("修改失败");

		}

	}else{

		err('身份错误,请重新登录');

	}

}

/*

**删除收货地址

*/

function deladdress(){

	

	$token =urlencode($_POST['token']);	

	if(!isset($_POST['token'])) err('请先登录');

	//if(!isset($_POST['addr_id'])&&) err('确认选择要删除的数据');

	$user =checkToken($token);

	if($user){

		$address =new M();

		$list =$address->table('address')->where('addr_id='.$_POST['addr_id'])->delete();

		if($list){

			fk('删除成功');

		}else{

			err("删除失败");

		}

	}else{

		err('身份错误,请重新登录');

	}

	

}





//设置默认收货地址

function setDefaultAddr(){

	$token =urlencode($_POST['token']);

	if(!isset($_POST['token'])) err('请先登录');

	$user =checkToken($token);

	$aid =intval($_REQUEST['addr_id']);

	if($user){

	$model=new M();

		$model

			->table('address')

			->where(array('user_id'=>$user['user_id']))

			->update(array('type'=>'0'));

		//echo $model->getsql();

		$data=$model

			->table('address')

			->where(array('addr_id'=>$aid))

			->update(array('type'=>'1'));

		//echo $model->getsql();
		if($data){
			fk('设置成功');
		}else{
			fk('设置失败');
		}			

	}else{

		err('身份错误,请重新登录');

	}

}







//区域列表

function getRegions(){	

	if(!isset($_POST['token'])) err('请先登录');

	$pid =intval($_REQUEST['pid']);

	$model =new M();

	$list =$model->table('sgxt_area')->field('id,parent_id,name')->where('parent_id='.$pid)->select();

	if($list){

		fk('区域列表',$list);

	}else{

		err('数据请求失败');

	}

}









/*

**地区搜索

*/

function seracharea(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	$regionname=$_POST["regionname"];

	$user = checkToken($token);

	if($user){

		$model= new M();

		$data= $model->table('sgxt_area')

		->field('id,name,parent_id')

		->where(array('name'=>$regionname))

		->find();

		

		fk('地区信息',$regionname);

	}else{

		err('身份错误，请重新登录');

	}





}









?>