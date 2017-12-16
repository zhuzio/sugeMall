<?php





/*

**商铺分类

*/

function storeclass(){

	$model=new M();

	$data=$model

		->table('scategory')

		->field('cate_id,cate_name,thumb,parent_id,thumb')

		->select();

	if($data){

		fk("商铺信息",$data);

	}else{

		err("数据获取失败");

	}

}



/*

**附近商家32.9839880000,112.5182660000

*/

function nearbyshops(){
	if($_POST['type']==""){
		$type='ecm_store.is_show=1 and ecm_store.state=1 and ecm_store.is_trade=1 and ecm_store.o2o= "offline"';
	}else{
		$type='ecm_store.is_show=1 and ecm_store.state=1 and ecm_store.is_trade=1 and ecm_store.o2o= "offline" and ecm_scategory.cate_name="'.$_POST['type'].'"';
		$hotsearch=new M();
			$hot=$hotsearch->table('hotsearch')
				->field('content,count,type')
                ->where(array('content'=>$_POST["type"],'type'=>'2'))
                ->find();
			if($hot['content']==$_POST["type"]){
				$count=$hot['count']+1;
				$hotsearch->table('hotsearch')
                ->where(array('content'=>$_POST["type"]))
              ->update(array('count'=>$count));
			}else{
				$a= array(
					'type' =>'2',
					'content'=>$_POST["type"],	
					'addtime'=>time(),
					'count'=>'1',
				);

				$hotsearch -> table('hotsearch') -> insert($a);

			}

	}

	if($_POST["lat"]==""){

		$lat='39.9151754663074';

	}else{

		$lat=$_POST['lat'];

	}

	if($_POST["lng"]==""){

		$lng='116.40390583019587';

	}else{

		$lng=$_POST['lng'];

	}

	$page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
	$pagecount=conf('pagecount');//默认一页10条
	$startcount=($page-1)*$pagecount;

	//print_r($lat);print_r($lng);die;

	$model=new M();

	/*$totalcount=$model

		->query('select count(ecm_store.store_id) as count from ecm_store inner join ecm_category_store on ecm_store.store_id = ecm_category_store.store_id inner join ecm_scategory on ecm_category_store.cate_id = ecm_scategory.cate_id where '.$type.' and  lat > '.$lat.'-1 and lat < '.$lat.'+1 and lng > '.$lng.'-1 and lng < '.$lng.'+1 order by ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 asc');

	$count=$totalcount[0]['count'];

	$totalpage=ceil($count/$pagecount);*/
	$totalpage=1;
	$data=$model

		->query('select lat,lng,ecm_store.is_trade,ecm_store.is_good,ecm_store.store_logo,ecm_store.lat,ecm_store.lng,ecm_store.	store_name,ecm_store.store_id,ecm_scategory.cate_name,ecm_store.address from ecm_store inner join ecm_category_store on ecm_store.store_id = ecm_category_store.store_id inner join ecm_scategory on ecm_category_store.cate_id = ecm_scategory.cate_id where '.$type.' and  lat > '.$lat.'-1 and lat < '.$lat.'+1 and lng > '.$lng.'-1 and lng < '.$lng.'+1 order by ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 asc');


		foreach($data as $key){

			$earthRadius = 6367000;

			$lat1 = ($lat * pi() ) / 180; 

			$lng1 = ($lng * pi() ) / 180; 

			$lat2 = ($key['lat'] * pi() ) / 180; 

			$lng2 = ($key['lng'] * pi() ) / 180; 

			$calcLongitude = $lng2 - $lng1; 

			$calcLatitude = $lat2 - $lat1; 

			$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2); 

			$stepTwo = 2 * asin(min(1, sqrt($stepOne))); 

			$calculatedDistance = $earthRadius * $stepTwo;

			$arr[]=array(

				'store_name'=> $key['store_name'],

				'store_logo'=> $key['store_logo'],

				'store_id'	=> $key['store_id'],

				'cate_name' => $key['cate_name'],

				'is_trade'		=> $key['is_trade'],

				'is_good'		=> $key['is_good'],

				'lat'		=> $key['lat'],

				'lng'		=> $key['lng'],

				'address'	=> $key['address'],

				'activity'	=> $key['activity'],

				'distance'	=> $calculatedDistance

			

			);

		}

		

	if($data){

		pageJson('ok','商铺信息',$arr,$totalpage);
	}else{

		fk("附近没有加盟商家");

	}

}

/*description

**商铺详情

*/

function storeinfo(){

	if(!isset($_POST["store_id"]))err("请确认要看的商铺");
	 $token = rawurlencode($_POST['token']);   
    $user = checkToken($token);
	$model=new M();
		$data=$model

		->table('store')

		->field('store_id,owner_name,region_name,image_1,image_2,image_3,store_name,address,tel,tel1,tel2,store_logo,description,activity')

		->where(array('store_id'=>$_POST['store_id']))

		->select();
		$arr= $model->query('select * from ecm_collect where item_id ='.$_POST["store_id"].' and user_id = '.@$user['user_id']);

		if($arr){

			$c='1';

		}else{

			$c='0';

		}
		
	if($data){
		pageJson('ok',"商铺信息",$data,$c);
		

	}else{

		fk("数据查询失败");

	}
	
	}
	

	

/*

**商铺搜索

*/

function searchstore(){

	if($_POST["lat"]==""){

		$lat='32.9839880000';

	}else{

		$lat=$_POST['lat'];

	}

	if($_POST["lng"]==""){

		$lng='112.5182660000';

	}else{

		$lng=$_POST['lng'];

	}

	$store_name	=$_POST['store_name'];

	

	if($_POST['store_name']!=""){

		$where = 'ecm_store.store_name like "%'.$_POST['store_name'].'%"';

		$hotsearch=new M();
			$hot=$hotsearch->table('hotsearch')
				->field('content,count,type')
                ->where(array('content'=>$_POST["store_name"],'type'=>'2'))
                ->find();
			if($hot['content']==$_POST["store_name"]){
				$count=$hot['count']+1;
				$hotsearch->table('hotsearch')
                ->where(array('content'=>$_POST["store_name"]))
               ->update(array('count'=>$count));
			}else{
				$a= array(
					'type' =>'2',
					'content'=>$_POST["store_name"],	
					'addtime'=>time(),
					'count'=>'1',
				);
				$hotsearch -> table('hotsearch') -> insert($a);
			}

	}else{

		$where = '1=1';

	}

	$model=new M();

	$data=$model

		->query('select lat,lng,ecm_store.store_name,ecm_store.store_id,ecm_store.store_logo,ecm_scategory.cate_name,ecm_store.address from ecm_store inner join ecm_category_store on ecm_store.store_id = ecm_category_store.store_id inner join ecm_scategory on ecm_category_store.cate_id = ecm_scategory.cate_id where '.$where.' and ecm_store.o2o= "offline" and  lat > '.$lat.'-1 and lat < '.$lat.'+1 and lng > '.$lng.'-1 and lng < '.$lng.'+1 order by ACOS(SIN(('.$lat.' * 3.1415) / 180 ) *SIN((lat * 3.1415) / 180 ) +COS(('.$lat.' * 3.1415) / 180 ) * COS((lat * 3.1415) / 180 ) *COS(('.$lng.'* 3.1415) / 180 - (lng * 3.1415) / 180 ) ) * 6380 asc ');

		
		foreach($data as $key){

			$earthRadius = 6367000;

			$lat1 = ($lat * pi() ) / 180; 

			$lng1 = ($lng * pi() ) / 180; 

			$lat2 = ($key['lat'] * pi() ) / 180; 

			$lng2 = ($key['lng'] * pi() ) / 180; 

			$calcLongitude = $lng2 - $lng1; 

			$calcLatitude = $lat2 - $lat1; 

			$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2); 

			$stepTwo = 2 * asin(min(1, sqrt($stepOne))); 

			$calculatedDistance = $earthRadius * $stepTwo;

			$arr[]=array(

				'store_name'=> $key['store_name'],

				'store_logo'=> $key['store_logo'],

				'store_id'	=> $key['store_id'],

				'cate_name' => $key['cate_name'],

				'is_trade'		=> $key['is_trade'],

				'is_good'		=> $key['is_good'],

				'lat'		=> $key['lat'],

				'lng'		=> $key['lng'],

				'address'	=> $key['address'],

				'activity'	=> $key['activity'],

				'distance'	=> $calculatedDistance

			

			);

		}


	if($data){

		fk("商铺信息",$data);

	}else{

		fk("数据获取失败");

	}

}



/*

**收藏商铺详情

*/

function onlinstore(){

	if(!isset($_POST["store_id"]))err("请确认要看的商铺");

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	$user = checkToken($token);

	if($user){

		$model=new M();

		$data=$model->query('select ecm_store.store_id,ecm_store.store_name,ecm_store.store_logo,ecm_store.add_time,ecm_store.region_name,ecm_store.address,ecm_store.tel,ecm_store.owner_name from ecm_store where store_id='.$_POST["store_id"].'');

		//echo $model->getsql();die;

		$arr= $model->query('select id from ecm_collect where item_id ='.$_POST["store_id"].' and user_id = '.$user['user_id'].'');

		if($array){

			$c='1';

		}else{

			$c='0';

		}

		if($data){

			pageJson("ok","商铺信息",$data,$c);

		}else{

			fk("数据查询失败");

		}

	}else{

		err('身份错误，请重新登录');

	}

}

/*

**商铺商品分类

*/

function classinfo(){

    $model=new M();

	$store_id=$_POST['store_id'];
	if(empty($store_id))
	{
		err('请上传店铺id');
	}

    $data=$model->query('select cate_id,cate_name from ecm_gcategory where  store_id = '.$store_id.' order by sort_order asc');

    if($data){

        fk("分类信息",$data);

    }else{

       fk("数据获取失败",$data);

    }

}

/*

**商铺商品详情

*/

function storegoods(){

	if(!isset($_POST["store_id"]))err("请确认要看的商铺");

	$token = rawurlencode($_POST['token']);

	

	if($_POST["cate_name"]==""){

		$where = 'and 1=1';

	}else{

		$where = 'and goods_name like "%'.$_POST["cate_name"].'%"';	

	}

	$user = checkToken($token);



	if($_POST["page"]=="0"||$_POST["page"]==""){

		$page='1';

	}else{

		$page=$_POST["page"];

	}

	$pagecount= 10;

	$startpage=((int)$page-1)*10;	
		$model=new M();

		$data=$model->query('select ecm_store.store_id,ecm_store.store_name,ecm_store.store_logo,ecm_store.add_time,ecm_store.region_name,ecm_store.address,ecm_store.tel,ecm_store.owner_name from ecm_store where store_id='.$_POST["store_id"].'');

		foreach ($data as $b=> $val){



			$count=$model

					->query('select count(store_id) as id from ecm_goods where store_id = '.$val['store_id'].' '.$where.'');

			$count=$count[0]['id'];		

			$totalpage=ceil($count/$pagecount);

			$data[$b]['goods']=$model

					->query('select goods_id,market_price,default_image,goods_name,price from ecm_goods where store_id = '.$val['store_id'].' '.$where.' order by goods_id desc limit '.$startpage.','.$pagecount.'');

                

		}

		$arr= $model->query('select * from ecm_collect where item_id ='.$_POST["store_id"].' and user_id = '.$user['user_id'].'');

		if($arr){

			$c='1';

		}else{

			$c='0';

		}

		if($data){

			outputJson("ok","商铺信息",$data,$totalpage,$c);		
	}else{

		err('身份错误，请重新登录');

	}

}



/*

**商家购积分

*/

function point(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["money"]))err("购买积分数量不能为空");

	if($_POST["money"]=='0'){

		err("购买积分数量不能为0");

	}

	

	$user = checkToken($token);

	if($user){
		if($user['type'] != 2) err('您不是商户');
		$model= new M();

		$data=$model->query('select user_id,user_name,real_name from ecm_member where  user_id= '.$user['user_id'].'');

		$order_sn = 'P'.substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);

		

		$status='0';

		$price='1.00';

		$amount=$price * intval($_POST["money"]);

		$arr= array(

			'orderid'			=>	$order_sn,

			'userid'			=>	$data[0]['user_id'],

			'mobile'			=>	$data[0]['user_name'],

			'truename'			=>	$data[0]['real_name'],

			'price'				=>	'1.00',

			'num'				=>	$_POST["money"],

			'amount'			=>	$amount,

			'order_type'		=>'point',

			'status'			=>$status,

			'createtime'		=>time()

			

		);
		
		$insid = $model -> table('sgxt_order') -> insert($arr);
	//echo $model->getlastsql();die;
		$array= array(

			'id'				=>$insid,

			'orderid'			=>	$order_sn,

			'amount'			=>	$amount,

			'name'				=>	'购买积分',

			

		);

		//echo $model->getsql();die;

		if($insid){
			//addMessage('sgxt_order','id',$insid,'您购买了'.$amount.'积分',$data[0]['user_id'],$data[0]['real_name'],2);
			fk("订单号",$order_sn);

		}

	}else{

		err('身份错误，请重新登录');
	}



}







/*

**购积分货款支付

*/



function pointpay(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["zf_pass"]))err("请填写支付密码");
	$order_sn=$_POST["order_sn"];
	$summoney=(int)$_POST["money"];

	$user = checkToken($token);

	if(empty($user))
	{
		err('身份错误，请重新登录');
	}

	$model =new M();

	//开启事务
	try{
		$model->startTrans();
		$money=$model

			->table('epay')

			->field('balance,zf_pass')

			->where('user_id='.$user['user_id'])

			->find();

		//验证支付密码

		if($money['zf_pass']==md5($_POST["zf_pass"])){

			//可用积分

			$newmoney=(int)$money["balance"];

			$a=$newmoney-$summoney;

			if($a>=0){
				//首先插入货款购积分记录
				$sgxt_order=array('orderid');
				$balance=$money["balance"]-$summoney;

				$data=$model

					->table('epay')

					->where('user_id='.$user['user_id'])

					->update(array('balance'=>$balance));

				$usepoint = $model->table('member')-> where(array('user_id' =>$user['user_id']))->getField('pay_point');

				$point =$usepoint ? $usepoint : '0';

				$pay_point=$point+$summoney;

				$model

					->table('member')

					->where('user_id='.$user['user_id'])

					->update(array('pay_point'=>$pay_point));
				$model->commit();
				fk("支付成功");

			}else{

				err('货款不足');

			}

		}else{

			err('支付密码输入错误');

		}

	}catch (Exception $e)
	{
		$model->rollback();
		err('货款支付失败！');exit;
	}


}





/*

**购积分支付后回调

*/

function payback(){

	$token = rawurlencode($_POST['token']);

	if(!isset($_POST["token"]))err("请先登录");

	if(!isset($_POST["order_sn"]))err("请填写订单号");

	if(!isset($_POST["type"]))err("请填写支付类型");

	$summoney=(int)$_POST["money"];

	$type=$_POST["type"];

	$order_sn=$_POST["order_sn"];

	$user = checkToken($token);

	if(empty($user))
	{
		err('身份错误，请重新登录');
	}
	$model =new M();
	//开启事务
	$model->startTrans();
	try{
		$array = array(

			'paytype'			=>$type,

			'pay_createtime'	=>time(),

			'status'			=>'1'

		);
		$model =new M();

		$orderinfo=$model->table('sgxt_order')->where('orderid=\''.$order_sn.'\' and userid='.$user['user_id'])->find();
		if(empty($orderinfo))
		{
			err('订单不存在！');
		}
		$money=$model

			->table('sgxt_order')
			->where(array('orderid'=>$order_sn))

			->update($array);
		$paymentlog=new paymentlogModel();
		$paymentlog->paymentlog($user['user_id'],$user['user_name'],$orderinfo['amount'] , '6',0,0,$orderinfo['id'],$order_sn);
		$model->commit();
		addMessage('sgxt_order','id',$orderinfo['id'],'您使用货款购积分成功！',$orderinfo['userid'],$orderinfo['truename']);
		fk('成功');
	}catch(Exception $e)
	{
		$model->rollback();
		err('货款支付失败！');exit;
	}
}

?>