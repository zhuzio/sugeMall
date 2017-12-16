<?php

/*

**我的收藏(默认是商品)

*/
function gcollection(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
    if($_POST['type']==''){
        $type='goods';
    }else{
        $type=$_POST['type'];
    }
    $user =checkToken($token);
    if($user){
        $model =new M();
        if($type=="goods"){
            $list =$model
                ->field('store_id,ecm_goods.goods_id,ecm_goods.price,ecm_goods.default_image,ecm_goods.goods_name')
                ->table('goods inner join ecm_collect on ecm_goods.goods_id = ecm_collect.item_id')
                ->where(array('ecm_collect.user_id'=>$user['user_id'],'ecm_collect.type'=>$type))
                ->select();
            //echo $model->getsql();die;
            $data =$model
                ->field('count(goods_id) as id')
                ->table('goods inner join ecm_collect on ecm_goods.goods_id = ecm_collect.item_id')
                ->where(array('ecm_collect.user_id'=>$user['user_id'],'ecm_collect.type'=>$type))
                ->select();
            $tatol=$data[0]['id'];
            //echo $model->getsql();die;
            if($list){
                pageJson('ok',"商品信息",$list,$tatol);
            }else{
                fk("数据获取失败");
            }
        }else{
            $list =$model
                ->field('store_id,o2o,store_logo,store_name')
                ->table('store inner join ecm_collect on ecm_store.store_id = ecm_collect.item_id')
                ->where(array('ecm_collect.user_id'=>$user['user_id'],'ecm_collect.type'=>$type))
                ->select();
            $data =$model
                ->field('count(store_id) as id')
                ->table('store inner join ecm_collect on ecm_store.store_id = ecm_collect.item_id')
                ->where(array('ecm_collect.user_id'=>$user['user_id'],'ecm_collect.type'=>$type))
                ->select();
            $tatol=$data[0]['id'];
            if($list){
                pageJson('ok',"商铺信息",$list,$tatol);
            }else{
                fk("数据获取失败");
            }
        }
    }else{
        err('身份错误,请重新登录');
    }
}

/*

**收藏商品搜索

*/

function searchgoods(){
    $token =urlencode($_POST['token']);
    if(!isset($_POST['token'])) err('请先登录');
   $search=$_POST['search'];
    if($_POST['type']==''){
        $type='goods';
    }else{
        $type=$_POST['type'];
    }
    $user =checkToken($token);
    if($user){
        $model =new M();
        if($type=="goods"){
            $list =$model->query("select store_id,goods_id,price,default_image,goods_name from ecm_goods inner join ecm_collect on ecm_goods.goods_id = ecm_collect.item_id where ecm_collect.user_id= ".$user['user_id']." and ecm_collect.type= '$type' and ecm_goods.goods_name like '%$search%'");
            if($list){
                fk('收藏商品信息',$list);
            }else{
                fk("数据获取失败");

            }

        }else{

            $list =$model->query("select store_id,o2o,store_logo,store_name from ecm_store inner join ecm_collect on ecm_store.store_id = ecm_collect.item_id where ecm_collect.user_id= ".$user['user_id']." and ecm_collect.type= '$type' and ecm_store.store_name like '%$search%'");

            //echo $model->getsql();die;

            if($list){

                fk('收藏商品信息',$list);

            }else{

                fk("数据获取失败");

            }

        }

    }else{

        err('身份错误,请重新登录');

    }

}

/*
**取消收藏商铺
*/
function uncollectstore(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    
	$type=$_POST["type"];
    $rec_id=$_POST["store_id"];
    $user = checkToken($token);
    if($user){
        $model= new M();
        $model->table('collect')
            ->where(array('item_id'=>$rec_id,'type'=>$type,'user_id'=>$user['user_id']))
            ->delete();

        fk('取消成功');
    }else{
        err('身份错误，请重新登录');
    }
}

/*
**取消收藏商品
*/
function uncollect(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["goods_id"]))err("请选择要取消收藏商品的商品");
    $rec_id=$_POST["goods_id"];
	$type=$_POST["type"];
    $user = checkToken($token);
    if($user){
        $model= new M();        
        $model->table('collect')
            ->where(array('item_id'=>$rec_id,'type'=>$type,'user_id'=>$user['user_id']))
            ->delete();
        fk('取消成功');
    }else{
        err('身份错误，请重新登录');
    }
}

/*

**取消收藏

*/

function deletec(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["goods_id"]))err("请选择要取消收藏商品的商铺");

    $rec_id=$_POST["goods_id"];

    $user = checkToken($token);

    if($user){

        $model= new M();

        $model

            ->query('delete from ecm_collect where user_id= '.$user['user_id'].' and item_id in ('.$rec_id.')');

        //echo $model->getsql();die;

        fk('取消成功');

    }else{

        err('身份错误，请重新登录');

    }

}



/*

**设置店铺分类下拉

*/



function shopclass(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model= new M();

        $data=$model

            ->field('cate_name,cate_id')

            ->table('scategory')            

            ->select();

        fk('分类信息',$data);

    }else{

        err('身份错误，请重新登录');

    }

}











/*

**店铺升级

*/



function shopupgrade(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["store_name"]))err("请确认店铺名");

    if(!isset($_POST["o2o"]))err("请确认店铺类型");

    if(!isset($_POST["cate_id"]))err("请确认店铺分类");

    //if(!isset($_POST["cate_name"]))err("请确认店铺分类名称");

    if(!isset($_POST["store_banner"]))err("请确认店铺执照");

    if(!isset($_POST["store_logo"]))err("请确认店铺LOGO");

    if(!isset($_POST["image_1"]))err("请确认店铺轮播图1");

    if(!isset($_POST["image_2"]))err("请确认店铺轮播图2");

    if(!isset($_POST["image_3"]))err("请确认店铺轮播图3");

    if(!isset($_POST["province"]))err("请确认店铺省份");

    if(!isset($_POST["city"]))err("请确认店铺城市");

    if(!isset($_POST["area"]))err("请确认店铺县城");

    if(!isset($_POST["address"]))err("请确认店铺地址");

    if(!isset($_POST["lat"]))err("请确认店铺纬度");

    if(!isset($_POST["lng"]))err("请确认店铺经度");

    if(!isset($_POST["tel1"]))err("请确认店铺联系方式1");

    if(!isset($_POST["tel2"]))err("请确认店铺联系方式2");

    $user = checkToken($token);

    if($user){

        $array=array(

            'store_id'=>$user["user_id"],

            'store_name'=>$_POST["store_name"],

            'o2o'=>$_POST["o2o"],

            'store_banner'=>$_POST["store_banner"],

            'store_logo'=>$_POST["store_logo"],

            'image_1'=>$_POST["image_1"],

            'image_2'=>$_POST["image_2"],

            'image_3'=>$_POST["image_3"],

            'province'=>$_POST["province"],

            'city'=>$_POST["city"],

            'area'=>$_POST["area"],

            'address'=>$_POST["address"],

            'lat'=>$_POST["lat"],

            'lng'=>$_POST["lng"],

            'tel1'=>$_POST["tel2"],

            'tel'=>$_POST["tel1"],

            'description'=>$_POST["description"],

            'activity'=>$_POST["activity"]

        );



        $model= new M();


        $store = $model->table('store')->where('store_id='.$user['user_id'])->find();

        if($store){
            $model -> table('store')->where('store_id='.$store['store_id']) ->update($array);
            $cate = $model->table('category_store')->where('store_id='.$store['store_id'])->find();
            if($cate){
                $model -> table('category_store')->where('cate_id='.$cate['cate_id'].' and store_id='.$store['store_id']) -> update(array('cate_id'=>$_POST['cate_id']));  
            }else{
                $model -> table('category_store')-> insert(array('cate_id'=>$_POST['cate_id']));                  
            }
        }else{
            //获取用户的信息
            $array['owner_name']=$user['real_name'];
            //$result=$model -> table('store') -> insert($array);
        
            /*if(!$result)
            {
                $fp=fopen('store.txt','w');
                fwrite($fp,$result."\n");
                fwrite($fp,$model->getSql());
                fclose($fp);
                err('升级失败！');
                exit;
            }*/
            $arr=array(
                'cate_id'=>$_POST["cate_id"],
                'store_id'=>$user["user_id"],
            );
            $model -> table('category_store') -> insert($arr);
            //插入升级表
            $data2 =array('userid'=>$user['user_id'],
                          'type'=>2, 
                           'areaid'=>0,
                           'createtime'=>time()
                ); 
            $model->table('sgxt_req')->insert($data2);        
        }
        fk('商铺设置成功');

    }else{

        err('身份错误，请重新登录');

    }



}
/*
 * *查询店铺详细信息
 */
function storeinfo()
{
    $token = rawurlencode($_POST['token']);   
    $user = checkToken($token);

    if($user){
        if($user['type']!=2)err("非商户");
        //查询店铺信息
        $model= new M();
        $sql='select store_id,store_name,region_name,o2o,store_banner,store_logo,image_1,image_2,image_3,province,city,area,address,lat,lng,tel1,tel,description,activity from ecm_store where store_id='.$user['user_id'];
        $stores=$model->query($sql);
        //分类id,name
        $sql='select ecm_scategory.cate_id,ecm_scategory.cate_name from ecm_scategory join ecm_category_store on ecm_category_store.cate_id=ecm_scategory.cate_id where ecm_category_store.store_id='.$user['user_id'];
        $cates=$model->query($sql);
        $stores[0]['cate_id']=$cates[0]['cate_id'];
        $stores[0]['cate_name']=$cates[0]['cate_name'];
		$arr= $model->query('select * from ecm_collect where item_id ='.$_POST["store_id"].' and user_id = '.$user['user_id'].'');

		if($arr){

			$c='1';

		}else{

			$c='0';

		}
		pageJson('ok',"商铺信息",$stores[0],$c);
       
    }
    else
    {
        $model= new M();
        $sql='select store_id,store_name,region_name,o2o,store_banner,store_logo,image_1,image_2,image_3,province,city,area,address,lat,lng,tel1,tel,description,activity from ecm_store where store_id='.$user['user_id'];
        $stores=$model->query($sql);
        //分类id,name
        $sql='select ecm_scategory.cate_id,ecm_scategory.cate_name from ecm_scategory join ecm_category_store on ecm_category_store.cate_id=ecm_scategory.cate_id where ecm_category_store.store_id='.$user['user_id'];
        $cates=$model->query($sql);
        $stores[0]['cate_id']=$cates[0]['cate_id'];
        $stores[0]['cate_name']=$cates[0]['cate_name'];
		$arr= $model->query('select * from ecm_collect where item_id ='.$_POST["store_id"].' and user_id = '.$user['user_id'].'');

		if($arr){

			$c='1';

		}else{

			$c='0';

		}
		pageJson('ok',"商铺信息",$stores[0],$c);
    }
}
?>