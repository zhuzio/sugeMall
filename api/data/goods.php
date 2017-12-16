<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/27
 * Time: 17:41
 */
function recgoods(){
    $model=new M();
    $data=$model
        ->table('goods')
        ->field('goods_id,goods_name,price,default_image')
        ->where(array('isnew'=>'1','closed'=>'0'))
        ->select();
    if($data){
        fk("推荐商品信息",$data);
    }else{
        err("查询失败");
    }

}

/*

**更多推荐商品

*/

function mrecgoods(){
    $condition='mall_recommended=1 and closed = 0';
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount2');//默认一页条数
    $startcount=($page-1)*$pagecount;

    $model=new M();

    $totalcount=$model
        ->table('goods')
        ->field('goods_id,goods_name,price,default_image')

        ->where($condition)

        ->count();
    //print($model->getSql());die;
    $totalpage=ceil($totalcount/$pagecount);

    $data=$model

        ->table('goods')

        ->field('goods_id,goods_name,price,market_price,default_image')

        ->where($condition)->order('goods_id desc')->limit($startcount.','.$pagecount)

        ->select();
    //print($model->getSql());die;
    if($data){

        //fk("推荐商品信息",$data);
        pageJson('ok','msg',$data,$totalpage);

    }else{

        fk("数据获取失败");

    }

}



/*

**推荐分类信息*

*/

function classinfo(){

    $model=new M();

    $data=$model

        ->table('gcategory')

        ->field('cate_id,cate_name,sort_order,cate_logo')

        ->where(array('store_id'=>'0'))

        ->select();

    if($data){

        fk("分类信息",$data);

    }else{

        err("查询失败");

    }

}



/*

**乐购商品

*/

function tescogoods(){



    $model=new M();

    $data=$model

        ->table('goods')

        ->field('goods_id,goods_name,price,default_image')

        ->where(array('recommended'=>'0','closed'=>'0'))

        ->select();

    if($data){

        fk("推荐商品信息",$data);

    }else{

        err("查询失败");

    }

}



/*

**乐购分类信息

*/

function tescoclass(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model=new M();

        $data=$model

            ->table('gcategory')

            ->field('cate_id,cate_name,sort_order')

            ->where(array('if_show'=>'1'))

            ->select();

        if($data){

            fk("分类信息",$data);

        }else{

            err("查询失败");

        }

    }else{

        err('身份错误，请重新登录');

    }

}

/*

**首页搜索

*/



function search(){
    if($_POST["search"]==""){
        $search="";
    }else{
        $search=$_POST["search"];
        $hotsearch=new M();
        $hot=$hotsearch->table('hotsearch')
            ->field('content,count')
            ->where(array('content'=>$_POST["search"],'type'=>'1'))
            ->find();
        if($hot){
            $count=$hot['count']+1;
            $hotsearch->table('hotsearch')
                ->where(array('content'=>$_POST["search"]))
                ->update(array('count'=>$count));
        }else{
            $a= array(
                'type' =>'1',
                'content'=>$search,
                'addtime'=>time(),
                'count'=>'1'
            );
            $hotsearch -> table('hotsearch') -> insert($a);
        }
    }
    if($_POST["sales"]==""&&$_POST["price"]!=""){

        if($_POST["price"]=='DESC'){

            $desc="price desc";

        }else{

            $desc="price asc";

        }



    }else{

        $desc="sales desc";

    }

    if($_POST["page"]=="0"||$_POST["page"]==""){

        $page='1';

    }else{

        $page=$_POST["page"];

    }

    $pagecount= 10;

    $startpage=((int)$page-1)*10;

    $model=new M();



    $count=$model

        ->query("select count(ecm_goods.goods_id) as id from ecm_goods inner join ecm_store on ecm_store.store_id=ecm_goods.store_id inner join ecm_goods_statistics on ecm_goods.goods_id=ecm_goods_statistics.goods_id where ecm_store.state=1 and ecm_goods.closed = 0 and ecm_goods.goods_name like '%$search%' order by ".$desc);



    $count=$count[0]['id'];

    $totalpage=ceil($count/$pagecount);

    $data=$model

        ->table('goods')->query("select  ecm_goods.market_price,ecm_goods.goods_id,sales,goods_name,price,default_image,ecm_goods.description from ecm_goods inner join ecm_store on ecm_store.store_id=ecm_goods.store_id inner join ecm_goods_statistics on ecm_goods.goods_id=ecm_goods_statistics.goods_id where ecm_store.state=1 and ecm_goods.closed = 0 and ecm_goods.goods_name like '%$search%' order by ".$desc." limit ".$startpage.",".$pagecount);

    if($data){

        pageJson('ok',"商品信息",$data,$totalpage);

    }else{

        fk("商品信息查询失败");

    }



}



/*

**商品详情

*/

function goodslist(){

    if(!isset($_POST["goods"]))err("请确认参数");
    $token = rawurlencode($_POST['token']);

    $user = checkToken($token);

    $model=new M();

    $array=$model

        ->table('collect')

        ->where(array('user_id'=>$user['user_id'],'item_id'=>$_POST["goods"]))

        ->find();

    $arr=$model

        ->table('goods_statistics')

        ->field('goods_id,views')

        ->where(array('goods_id'=>$_POST["goods"]))

        ->find();

    $model

        ->table('goods_statistics')

        ->where(array('goods_id'=>$_POST["goods"]))

        ->update(array('views'=>$arr["views"]+1));

    $data=$model

        ->table('goods inner join ecm_goods_spec on ecm_goods.goods_id = ecm_goods_spec.goods_id inner join ecm_store on ecm_goods.store_id = ecm_store.store_id')

        ->field('ecm_store.store_name,stock,ecm_goods.goods_id,market_price,point,goods_name,ecm_goods.description,ecm_goods.price,default_image,ecm_goods.store_id')

        ->where(array('ecm_goods.goods_id'=>$_POST["goods"],'ecm_goods.closed'=>0,'ecm_store.state'=>1))

        ->find();
    if(empty($data))
    {
        err('此商品不存在或已下架！');
    }
//echo $model->getsql();die;

    //print_r($data);die;

    $color=$model->table('goods_spec')

        ->field('spec_1')

        ->where(array('goods_id'=>$_POST['goods']))

        ->group('spec_1')->select();

    //echo $model->getsql();die;

    $spec=$model->table('goods_spec')

        ->field('spec_id,spec_1,spec_2,price,stock')

        ->where(array('goods_id'=>$_POST["goods"]))

        ->select();
    $image=$model->table('goods_image')

        ->field('image_url')

        ->where(array('goods_id'=>$_POST["goods"]))

        ->select();
    //echo $model->getsql();die;

    if($array){

        $c='1';

    }else{

        $c='0';

    }
    $data= array(
        'store_name'=>	$data['store_name'],
        'stock'=>	$data['stock'],
        'goods_id'=>	$data['goods_id'],
        'market_price'=>	$data['market_price'],
        'point'=>	$data['point'],
        'goods_name'=>	$data['goods_name'],
        'description'=>	$data['description'],
        'price'=>	$data['price'],
        'default_image'=>	$data['default_image'],
        'store_id'=>	$data['store_id'],
        'collection'=>	$c

    );
    if($data){

        goodsJson("ok","商品信息",$data,$color,$spec,$image);

    }else{

        fk("商品获取失败");

    }

}



/*

**购物车

*/

function goods_car(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $user = checkToken($token);

    if($user){

        $model=new M();
        $array=$model->query('select ecm_store.store_name,ecm_cart.store_id from ecm_cart inner join ecm_store on ecm_cart.store_id = ecm_store.store_id inner join ecm_goods on ecm_cart.goods_id = ecm_goods.goods_id where ecm_store.state=1 and ecm_cart.user_id = '.$user['user_id'].' and ecm_goods.closed=0 group by ecm_cart.store_id ');
        foreach($array as $a=>$key){
            $array[$a]['goods']=$model->query('select rec_id,store_name,ecm_goods_spec.stock,ecm_cart.goods_id,ecm_cart.spec_id,ecm_cart.store_id,specification,ecm_goods.goods_name,ecm_cart.price,ecm_cart.quantity,ecm_cart.goods_image from ecm_cart inner join ecm_store on ecm_cart.store_id = ecm_store.store_id inner join ecm_goods on ecm_cart.goods_id = ecm_goods.goods_id inner join ecm_goods_spec on ecm_cart.spec_id =ecm_goods_spec.spec_id  where ecm_store.state=1 and ecm_cart.store_id = '.$key['store_id'].' and ecm_cart.user_id = '.$user['user_id'].' and ecm_goods.closed=0 group by ecm_cart.rec_id');
        }
        if($array){

            fk("购物车信息",$array);

        }else{

            fk("购物车内没有商品",$array);

        }

    }else{

        err('身份错误，请重新登录');

    }



}

/*

**购物车总金额

*/

function cartmoney(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    $rec_id=$_POST["rec_id"];

    $user = checkToken($token);

    if($user){

        $model=new M();

        $data=$model

            ->table('cart')

            ->query('select price,quantity from ecm_cart where rec_id in ('.$rec_id.')');

        foreach($data as $key){

            $arr += (int)$key['price']*(int)$key['quantity'];

        }



        if($data){

            fk("成功",$arr);

        }else{



            fk("没有数据");

        }

    }else{

        err('身份错误，请重新登录');

    }

}

/*

**购物车编辑

*/



function delcat(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["rec_id"]))err("请选择要删除的商品");

    $rec_id=$_POST["rec_id"];

    $user = checkToken($token);

    if($user){

        $model=new M();

        $data=$model

            ->table('cart')

            ->query('delete from ecm_cart where rec_id in ('.$rec_id.')');
        if($data){

            fk("删除成功");

        }else{
            err("删除失败");

        }

    }else{

        err('身份错误，请重新登录');

    }

}



/*

**购物车商品数量

*/

function editor(){

    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["rec_id"]))err("请选择商品");//修改商品数量ＩＤ
    if(!isset($_POST["quantity"]))err("商品数量不能为空");
    $rec_id=$_POST["rec_id"];//购物车所选的商品ＩＤ
    $user = checkToken($token);
    if($user){
        $model=new M();

        if($data['quantity']=='1'){
            err('至少有一个商品');
        }else{
            $a=$model
                ->table('cart')
                ->where(array('rec_id'=>$_POST['rec_id']))
                ->update(array('quantity'=>$_POST['quantity']));
            $array=$model
                ->table('cart')
                ->query('select price,quantity from ecm_cart where rec_id in ('.$rec_id.')');
            $arr =array(
                'totalmoney'=>  $array[0]['price']*$array[0]['quantity']
            );
        }
        fk("成功",$arr);

    }else{

        err('身份错误，请重新登录');

    }



}

/*

**加入购物车

*/
/**
 *
function addcar(){

$token = rawurlencode($_POST['token']);

if(!isset($_POST["token"]))out_json(1,"请先登录");
if(!isset($_POST["goods_id"]))out_json(2,"请选择要加入购物车的商品");
if(!isset($_POST["quantity"]))out_json(3,"请选择商品数量");
if(!isset($_POST["color"]))out_json(4,"请选择商品颜色");
if(!isset($_POST["size"]))out_json(5,"请选择商品尺码");
$rec_id=$_POST["goods_id"];
$color=$_POST["color"];
$size=$_POST["size"];
$specification= '颜色:'.$color.' 尺码:'.$size.'';
$rec_id=$_POST["goods_id"];
if($_POST["quantity"]=="0"){
out_json(6,'商品数量不能为0');
}else{
$quantity=$_POST["quantity"];
}
$user = checkToken($token);
if($user){
$model=new M();
$data=$model
->table('goods')
->field('goods_id,default_image,goods_name,store_id,description,price')
->where(array('goods_id'=>$rec_id))
->find();
$goods_list=$model
->table('cart')
->field('quantity,rec_id')
->where(array('user_id'=>$user['user_id'],'goods_id'=>$rec_id,'specification'=>$specification))
->find();
if($goods_list){
$aquantity=$goods_list['quantity']+$_POST["quantity"];
$model->table('cart')->where(array('rec_id'=>$goods_list['rec_id']))->update(array('quantity'=>$aquantity));
out_json(0,"加入购物车成功");
}else{
$arr=array(
'goods_id'		=>	$rec_id,
'goods_image'	=>	$data['default_image'],
'specification'		=>	$specification,
'goods_name'	=>	$data['goods_name'],
'store_id'		=>	$data['store_id'],
'price'			=>	$data['price'],
'quantity'		=>	$quantity,
'user_id'		=>	$user['user_id']
);
if($model->table('cart')->insert($arr)){
$arr=$model
->table('goods_statistics')
->field('goods_id,carts')
->where(array('goods_id'=>$_POST["goods"]))
->find();
$model
->table('goods_statistics')
->where(array('goods_id'=>$_POST["goods"]))
->update(array('carts'=>$arr["carts"]+1));
out_json(0,"加入购物车成功");
}else{
out_json(7,"加入失败");
}
}
}else{
out_json(8,'身份错误，请重新登录');
}
}

 **/
function addcar(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["goods_id"]))err("请选择要加入购物车的商品");
    if(!isset($_POST["quantity"]))err("请选择商品数量");
    if(!isset($_POST["color"]))err("请选择商品颜色");
    if(!isset($_POST["size"]))err("请选择商品尺码");
    $rec_id=$_POST["goods_id"];
    $spec_id=$_POST["spec_id"];
    if(empty($spec_id) || $spec_id<=0)
    {
        err('参数错误！');
    }
    $color=$_POST["color"];
    $size=$_POST["size"];
    $specification= '颜色:'.$color.' 尺码:'.$size.'';
    $rec_id=$_POST["goods_id"];
    if($_POST["quantity"]=="0"){
        err('商品数量不能为0');
    }else{
        $quantity=$_POST["quantity"];
    }


    $user = checkToken($token);

    if($user){

        $model=new M();

        $data=$model
            ->table('goods')
            ->field('goods_id,default_image,goods_name,store_id,description,price')
            ->where(array('goods_id'=>$rec_id))
            ->find();
        $goods_list=$model
            ->table('cart')
            ->field('quantity,rec_id')
            ->where(array('user_id'=>$user['user_id'],'goods_id'=>$rec_id,'spec_id'=>$spec_id,'specification'=>$specification))
            ->find();
        if($goods_list){
            $aquantity=$goods_list['quantity']+$_POST["quantity"];
            $model->table('cart')->where(array('rec_id'=>$goods_list['rec_id']))->update(array('quantity'=>$aquantity));
            fk("加入购物车成功");
        }else{
            //查询商品价格
            $goods_spec_price=$model->table('goods_spec')
                ->where(array('spec_id'=>$spec_id))->getField('price');
            $arr=array(
                'goods_id'		=>	$rec_id,
                'spec_id'		=>	$spec_id,
                'goods_image'	=>	$data['default_image'],
                'specification'		=>	$specification,
                'goods_name'	=>	$data['goods_name'],
                'store_id'		=>	$data['store_id'],
                'price'			=>$goods_spec_price,
                'quantity'		=>	$quantity,
                'user_id'		=>	$user['user_id']
            );
            if($model->table('cart')->insert($arr)){
                $arr=$model
                    ->table('goods_statistics')
                    ->field('goods_id,carts')
                    ->where(array('goods_id'=>$_POST["goods"]))
                    ->find();
                $model
                    ->table('goods_statistics')
                    ->where(array('goods_id'=>$_POST["goods"]))
                    ->update(array('carts'=>$arr["carts"]+1));
                fk("加入购物车成功");
            }else{
                err("加入失败");
            }
        }
    }else{
        err('身份错误，请重新登录');
    }
}



/*

**收藏商铺

*/



function shopcollect(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["store_id"]))err("请选择收藏的商铺");
    $rec_id=$_POST["store_id"];
    $type=$_POST["type"];
    $user = checkToken($token);
    if($user){
        $model=new M();
        $array=$model
            ->table('collect')
            ->field('item_id')
            ->where(array('user_id'=>$user['user_id'],'item_id'=>$_POST['store_id']))
            ->find();
        if($array){
            err("已收藏");
        }else{
            $arr=array(
                'item_id'		=>	$_POST['store_id'],
                'add_time'		=>	time(),
                'type'			=>	'store',
                'user_id'		=>	$user['user_id']
            );
            $model->table('collect')->insert($arr);
            fk("收藏成功");
        }
    }else{
        err('身份错误，请重新登录');
    }
}

/*

**收藏商品

*/

function goodscollect(){

    $token = rawurlencode($_POST['token']);

    if(!isset($_POST["token"]))err("请先登录");

    if(!isset($_POST["goods_id"]))err("请选择要收藏商品的商品");

    $rec_id=$_POST["goods_id"];

    $type=$_POST["type"];

    $user = checkToken($token);

    if($user){

        $model=new M();

        $array=$model

            ->table('collect')

            ->where(array('user_id'=>$user['user_id'],'item_id'=>$rec_id))

            ->find();

        //print_r($array);die;

        //echo $model->getsql();die;

        if($array){

            fk("已收藏");

        }else{



            $arr=array(



                'item_id'		=>	$rec_id,

                'add_time'		=>	time(),

                'type'			=>	'goods',

                'user_id'		=>	$user['user_id']

            );

            $model->table('collect')->insert($arr);


            $array=$model



                ->table('goods_statistics')

                ->field('goods_id,carts')

                ->where(array('goods_id'=>$_POST["goods"]))

                ->find();

            $model

                ->table('goods_statistics')

                ->where(array('goods_id'=>$_POST["goods"]))

                ->update(array('carts'=>$array["carts"]+1));

            fk("收藏成功");

        }

    }else{

        err('身份错误，请重新登录');

    }

}

/*

**分类 导航

*/

function classlist(){

    $model=new M();

    //热门推荐（不知道怎么查）

    //分类 导航

    $data=$model

        ->table('gcategory')

        ->field('store_id,cate_id,cate_name,parent_id')

        ->where(array('if_show'=>'1','store_id'=>'0','parent_id'=>'0'))
        ->order('sort_order asc')
        ->select();



    if($data){

        fk('分类信息',$data);

    }else{

        fk('分类获取失败');

    }

}

/*

**分类

*/

function classlistinfo(){

    $model=new M();

    $array=array();

    $cate_id=$_POST['cate_id'];

    //分类 导航

    $data=	$model

        ->table('gcategory')

        ->field('store_id,cate_id,cate_name,parent_id,cate_logo')

        ->where(array('if_show'=>'1','parent_id'=>$cate_id))

        ->order('sort_order asc')->select();



    if($data){

        fk('分类信息',$data);

    }else{

        fk('分类获取失败');

    }

}

/*

**分类搜索

*/

function classshop_wx(){

    if($_POST["page"]=="0"||$_POST["page"]==""){

        $page='1';

    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
    $cate_id=isset($_POST["cate_id"])?$_POST["cate_id"]:1;
    $model=new M();
    $count=$model
        ->field('count(ecm_goods.goods_id) as id')
        ->table('gcategory inner join ecm_goods on ecm_goods.cate_id=ecm_gcategory.cate_id inner join ecm_store on ecm_store.store_id=ecm_goods.store_id')
        ->where(array('ecm_goods.closed'=>'0','ecm_store.state'=>1,'ecm_gcategory.parent_id'=>$cate_id))
        ->select();



    $count=$count[0]['id'];

    $totalpage=ceil($count/$pagecount);

    $data=$model

        ->query('select ecm_goods.goods_id,goods_name,market_price,price,default_image from ecm_gcategory inner join ecm_goods on ecm_goods.cate_id=ecm_gcategory.cate_id inner join ecm_store on ecm_store.store_id=ecm_goods.store_id where ecm_store.state=1 and ecm_goods.closed = 0 and ecm_gcategory.parent_id = "'.$cate_id.'" order by ecm_goods.goods_id desc limit '.$startpage.','.$pagecount);
//        file_put_contents('mysql.txt',$model->getSql());
    if($data){

        pageJson('ok',"商品信息",$data,$totalpage);

    }else{

        fk("商品获取失败");

    }

}


/*

**分类搜索

*/

function classshop(){

    if($_POST["page"]=="0"||$_POST["page"]==""){

        $page='1';

    }else{
        $page=$_POST["page"];
    }
    $pagecount= 10;
    $startpage=((int)$page-1)*10;
    if($_POST["cate_name"]==""){
        $cate_name="跨境产品";
    }else{
        $cate_name=$_POST["cate_name"];
    }
    $model=new M();
    $count=$model
        ->field('count(ecm_goods.goods_id) as id')
        ->table('gcategory inner join ecm_goods on ecm_goods.cate_id=ecm_gcategory.cate_id inner join ecm_store on ecm_store.store_id=ecm_goods.store_id')
        ->where(array('ecm_goods.recommended'=>'1','ecm_goods.closed'=>'0','ecm_store.state'=>1,'ecm_gcategory.cate_name'=>$cate_name))
        ->select();



    $count=$count[0]['id'];

    $totalpage=ceil($count/$pagecount);

    $data=$model

        ->query('select ecm_goods.goods_id,goods_name,market_price,price,default_image from ecm_gcategory inner join ecm_goods on ecm_goods.cate_id=ecm_gcategory.cate_id inner join ecm_store on ecm_store.store_id=ecm_goods.store_id where ecm_store.state=1 and ecm_goods.recommended = "1" and ecm_goods.closed = 0 and ecm_gcategory.cate_name = "'.$cate_name.'" order by ecm_goods.goods_id desc limit '.$startpage.','.$pagecount);


    //echo $model->getsql();die;
    if($data){

        pageJson('ok',"商品信息",$data,$totalpage);

    }else{

        fk("商品获取失败");

    }

}

/*

**确认订单

*/



function orderlist(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["rec_id"]))err("请选择商品");
    $source = $_POST["rec_id"];//按逗号分离字符串
    // $_POST["rec_id"] = substr($source,0,-1);
    $user = checkToken($token);
    if($user){
        $model =new M();
        //收货地址
        $addresslist =$model
            ->table('address')
            ->field('addr_id,region_name,consignee,address,phone_tel')
            ->where(array('user_id'=>$user['user_id'],'type'=>'1'))
            ->find();
        //商品信息

        $goodslist=$model->query('select ecm_store.store_name,ecm_cart.goods_id,ecm_cart.spec_id,ecm_cart.store_id from ecm_cart inner join ecm_store on ecm_cart.store_id = ecm_store.store_id where ecm_cart.user_id = '.$user['user_id'].' and rec_id in ('.$_POST["rec_id"].') group by ecm_cart.store_id ');
        foreach($goodslist as $a=>$key){
            $goodslist[$a]['goods']=$model->query('select rec_id,store_name,ecm_goods.point,ecm_goods_spec.stock,ecm_cart.goods_id,ecm_cart.spec_id,ecm_cart.store_id,specification,ecm_cart.goods_name,ecm_cart.price,ecm_cart.quantity,ecm_cart.goods_image from ecm_cart inner join ecm_store on ecm_cart.store_id = ecm_store.store_id inner join ecm_goods_spec on ecm_cart.spec_id =ecm_goods_spec.spec_id inner join ecm_goods on ecm_cart.goods_id = ecm_goods.goods_id  where ecm_cart.store_id = '.$key['store_id'].' and ecm_cart.user_id = '.$user['user_id'].' and ecm_cart.rec_id in ('.$_POST["rec_id"].') group by ecm_cart.rec_id');
            foreach($goodslist[$a]['goods'] as $b=> $val){
                $goodslist[$a]['point']+=$val['point'];
                $goodslist[$a]['summoney']+=$val['price']*$val['quantity'];
            }
            $goodslist[$a]['shipping']=$model
                ->table('shipping')
                ->field('shipping_id,shipping_name,shipping_desc,first_price,step_price')
                ->where('store_id='.$key['store_id'])
                ->find();
            if($key['store_id']==$user['user_id'])
            {
                err('不允许购买自己的产品！');
            }
        }
        //可用积分
        $money=$model
            ->table('epay')
            ->field('money')
            ->where('user_id='.$user['user_id'])
            ->find();
        /*foreach($goodslist as $key=> $val){
            $point+=$val['goods'][$val]['point'];
        }*/
        //echo $model->getsql();die;
        //配送方式
        /*$shipping=$model
            ->table('shipping')
            ->field('shipping_name,shipping_id,shipping_desc,first_price,step_price')
            ->where('store_id='.$goodslist[0]['store_id'])
            ->find();
        //总计
        foreach ($goodslist as $key){
            $arr += (int)$val['goods'][0]['price']*(int)$val['goods'][0]['quantity'];
        }*/

        //echo $model->getsql()
        // $summoney=$goodsmoney[0]['price']+$shipping[0]['first_price'];
        $data[]=array(
            'addresslist'	=>	$addresslist,
            'goodslist'		=>	$goodslist,
            'money'			=>	$money['money']

        );
        if($data){
            fk("确认订单信息",$data);
        }else{
            err("数据获取失败");
        }
    }else{
        err('身份错误，请重新登录');
    }
}



/*

**生成订单

*/

function addorder(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["rec_id"]))err("请确认商品");
    if(!isset($_POST["store_id"]))err("请选择商铺");
    if(!isset($_POST["invoice"]));
    if(!isset($_POST["adr_id"]))err("请选择收货地址");
    if(!isset($_POST["shipping_id"]))err("请选择快递方式");
    $source = $_POST["rec_id"];//按逗号分离字符串
    $goodsid = explode(',',$source);
    $store = $_POST["store_id"];
    $storeid = explode(',',$store);
    $model =new M();
    $user = checkToken($token);
    if($user){
        foreach($storeid as $key){

            //收货地址
            $addresslist =$model
                ->table('address')
                ->field('addr_id,region_name,consignee,address,phone_tel')
                ->where(array('addr_id'=>$_POST["adr_id"]))
                ->find();
            if(empty($addresslist))
            {
                err('收货地址错误');
            }
            //
            //配送方式
            $shipping=$model
                ->table('shipping')
                ->field('shipping_name,shipping_desc,first_price,step_price')
                ->where('shipping_id='.$_POST["shipping_id"])
                ->find();
            //商家信息
            $storelist =$model
                ->table('store')
                ->field('store_id,store_name')
                ->where(array('store_id'=>$key))
                ->find();
            //买家信息
            $buylist =$model
                ->table('member')
                ->field('user_id,user_name,real_name,email')
                ->where(array('user_id'=>$user['user_id']))
                ->find();
            //总金额
            $money=$model
                ->query('select sum(price) as price from ecm_cart where store_id='.$key.' and goods_id in ('.$_POST["goods_id"].')');
            $money=$money[0]['price'];
            //获取支付类型
            $payment =  $model -> table('payment') -> where(array('payment_id' => 9)) -> find();
            $remark="";
            //封装生成数据
            $order_sn = substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
            $orderdata = array(
                'order_sn'      =>  $order_sn,
                'type'     =>  'material',
                'extension'   =>  'normal',
                'seller_id'      =>  $storelist['store_id'],
                'seller_name'      =>  $storelist['store_name'],
                'buyer_id'      =>  $buylist['user_id'],
                'buyer_name'    =>  $buylist['real_name']?$buylist['real_name']:$buylist['user_name'],
                'buyer_email'   =>  $buylist['email'],
                'status'        =>  11,
                'add_time'      =>  time(),
                'payment_id'    =>  9,
                // 'payment_name'  =>  $payment['payment_name'],
                // 'payment_code'  =>  $payment['payment_code'],
                // 'pay_time'      =>  time(),
                // 'pay_message'   =>  $remark,
                'goods_amount'  =>  $money,
                'postscript'	=>  $_POST["postscript"],//买家留言
                'invoice_no'	=>  $_POST["invoice"],
            );
            $insid = $model -> table('order') -> insert($orderdata);
            $address = array(
                'order_id'	 =>$insid,
                'consignee'	 =>$addresslist['consignee'],
                'region_id'	 =>$addresslist['region_id'],
                'region_name'	 =>$addresslist['region_name'],
                'address'	 =>$addresslist['address'],
                'phone_tel'	 =>$addresslist['phone_tel'],
                'shipping_id'	 =>$shipping['shipping_id'],
                'shipping_name'	 =>$shipping['shipping_name'],
                'shipping_fee'	 =>$shipping['first_price']
            );
            $model -> table('order_extm') -> insert($address);
        }
        foreach($goodsid as $val){
            $goods=$model
                ->table('cart')
                ->field('goods_id,spec_id,goods_name,price,specification,quantity,goods_image')
                ->where(array('rec_id'=>$val))
                ->find();
            $model->table('cart')
                ->where(array('rec_id'=>$val))
                ->delete();
            $goodsdata = array(
                'order_id'      =>  $order_sn,
                'spec_id'     =>  $goods['spec_id'],
                'goods_id'     =>  $goods['goods_id'],
                'goods_name'   =>  $goods['goods_name'],
                'price'      =>  $goods['price'],
                'quantity'      =>  $goods['quantity'],
                'specification'      =>  $goods['specification'],
                'goods_image'      => $goods['goods_image']
            );
            $model -> table('order_goods') -> insert($goodsdata);
        }
        addMessage('order' , 'order_id' ,$insid ,'您有一条订单消息' ,$storelist['store_id'],$storelist['store_name']);
        fk('订单生成');
    }else{
        err('身份错误，请重新登录');
    }
}

function store_shipping(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $model =new M();
    $user = checkToken($token);
    if($user){
        if($user['type'] != 2) err('身份错误，请重新登录');
        $list = $model->table('shipping')->where('store_id')->select();
        fk('读取成功',$list);
    }else{
        err('身份错误，请重新登录');
    }

}

/*
**直接购买订单
*/
function directlyorder(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["order_info"]))err("请选择商品");
    if(!isset($_POST["adr_id"]))err("请选择收货地址");
    if(!isset($_POST["shipping_id"]))err("请选择快递方式");
    file_put_contents('orderinfo.txt',$_POST['order_info'],FILE_APPEND);
    $order_info = json_decode(stripslashes($_POST['order_info']),true);
    $model =new M();
    $user = checkToken($token);
    if($user){
        $orderDatas = $goodsData = array();
        $shipping_id = $_POST["shipping_id"];
        $addresslist =$model
            ->table('address')
            ->field('addr_id,region_name,consignee,address,phone_tel')
            ->where(array('user_id'=>$user['user_id'],'addr_id'=>$_POST["adr_id"]))
            ->find();
        if(empty($addresslist))
        {
            err('收货地址错误');
        }
        $goods_names = array();
        foreach($order_info as $key=>$val){
            $store_id = $val['store_id'];
            $shipping = $model->table('shipping')->where('shipping_id='.$shipping_id.' and store_id='.$store_id)->find();
            $store = $model->table('store')->where('store_id='.$store_id)->find();
            $amount = 0;
            $goods_amount = 0;
            $goods_count = 0;
            $order_sn = substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
            $point = 0;
            foreach($val['good_list'] as $v){
                $goods_id = $v['goods_id'];
                $color_id = $v['color_id'];
                $size_id = $v['size_id'];
                $spec_id = $v['spec_id'];
                if(empty($spec_id) || $spec_id<=0)
                {
                    err('参数错误！');
                }
                $quantity = $v['quantity'];
                $goods=$model->query('select * from ecm_goods where goods_id='.$goods_id);
                $spec = $model->table('goods_spec')->where('goods_id='.$goods_id.' and spec_id='.$spec_id)->find();
                //$money=$goods[0]['price'];
                //$goods_amount += $goods[0]['price'];
                $money=$spec['price'];
                $goods_amount += $spec['price'];
                $goods_count += $quantity;
                $point += $goods[0]['point'] * $quantity;
                $amount += $quantity * $money;
                if(empty($amount)||$amount<0.0001)
                {
                    err('金额错误');
                }

                $goodsData[] = array(

                    'order_id'          =>  '',
                    'goods_id'          =>  $goods[0]['goods_id'],
                    'goods_name'        =>  $goods[0]['goods_name'],
                    //'price'             =>  $goods[0]['price'],
                    'price'             =>  $spec['price'],
                    'quantity'          =>  $quantity,
                    'spec_id'           =>  $spec['spec_id'],
                    'specification'     =>  '颜色：'.$color_id.' '.';尺寸：'.$size_id,
                    'goods_image'       => $goods[0]['default_image']
                );
                $goods_names[] = $goods[0]['goods_name'];

                $model->query('delete from ecm_cart where goods_id = '.$goods_id.' and user_id = '.$user['user_id']);
            }

            $orderdata = array(
                'order_sn'      =>  $order_sn,
                'type'          =>  'material',
                'extension'     =>  'normal',
                'seller_id'     =>  $store['store_id'],
                'seller_name'   =>  $store['store_name'],
                'buyer_id'      =>  $user['user_id'],
                'buyer_name'    =>  $user['real_name']?$user['real_name']:$user['user_name'],
                'buyer_email'   =>  $user['email'],
                'status'        =>  11,
                'add_time'      =>  time(),
                'payment_id'    =>  '',
                'goods_amount'  =>  $goods_amount,
                'discount'      =>  $amount,
                'order_amount'  =>  $amount+$shipping['first_price'],
                'point'         =>  $point,
                'postscript'    =>  $val["postscript"],//买家留言
                'invoice_no'    =>  $_POST["invoice"],
            );

            $orderDatas[] = $orderdata;


        }

        foreach($orderDatas as $key=>$val){
            $insid = $model -> table('order') -> insert($val);
            //产生订单直接扣除商家要发送的积分
            //$model -> table('member') ->where(array('user_id' => $val['seller_id'])) ->setDec('pay_point',$val['point']*conf('PAY_INFO/shops_point'));

            foreach($goodsData as $k=>$v){
                $v['order_id'] = $insid;
                $model -> table('order_goods') -> insert($v);
            }

            $shipping = $model->table('shipping')->where('shipping_id='.$shipping_id.' and store_id='.$store_id)->find();

            $address = array(
                'order_id'   =>$insid,
                'consignee'  =>$addresslist['consignee'],
                'region_id'  =>$addresslist['region_id'],
                'region_name'    =>$addresslist['region_name'],
                'address'    =>$addresslist['address'],
                'phone_tel'  =>$addresslist['phone_tel'],
                'shipping_id'    =>$shipping['shipping_id'],
                'shipping_name'  =>$shipping['shipping_name'],
                'shipping_fee'   =>$shipping['first_price']
            );
            $model -> table('order_extm') -> insert($address);
            $orderDatas[$key] = $val;
        }

        $orderlist =array(
            'orderid'       =>$insid,
            'order_id'       =>  $orderDatas[0]['order_sn'],
            'goods_name'     =>  implode(',', $goods_names),
            'goods_amount'   =>  $orderDatas[0]['order_amount'],
        );
        addMessage('order' , 'order_id' ,$insid ,'您有一条订单消息' ,$store['store_id'],$store['store_name']);

        fk('订单生成',$orderlist);
    }else{
        err('身份错误，请重新登录');
    }
}


/*

**线上购物积分支付（线上）

*/

function pointpay(){
    //echo md5(md5(123456));die;
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["zf_pass"]))err("请填写支付密码");
    if(!isset($_POST["order_sn"]))err("请确认订单");

    $summoney=(int)$_POST["money"];
    $user = checkToken($token);
    if($user){
        $model =new M();
        $order=$model->table('order')->where(array('order_sn'=>$_POST["order_sn"]))->find();
        if(empty($order))
        {
            err('订单不存在！');
            die;
        }
        if($order['status']>=20)
        {
            err('订单已支付！');
            die;
        }
        if(empty($order['order_amount'])||$order['order_amount']<0.0001)
        {
            err('金额错误');
        }
        $money=$model
            ->table('epay')
            ->field('money,zf_pass')
            ->where('user_id='.$user['user_id'])
            ->find();

        //验证支付密码
        if($money['zf_pass']==md5($_POST["zf_pass"])){
            //可用积分
            $newmoney=(int)$money["money"];
            $a=$newmoney-$summoney;
            if($a>=0){
                $balance=$money["money"]-$summoney;
                $data=$model
                    ->table('epay')
                    ->where('user_id='.$user['user_id'])
                    ->update(array('money'=>$balance));
                $log_text = $user['user_name'] . '向' . $order['seller_name'] . '转入金额' . $summoney . '元';
                $add_epaylog = array(
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'to_id' => $order['seller_id'],
                    'to_name' => $order['seller_name'],
                    'order_id' => $order['order_id'],
                    'order_sn' => $_POST['order_sn'],
                    'add_time' => $order['add_time'],
                    'type' => EPAY_BUY,
                    'money_flow' => 'outlay',
                    'money' => $summoney,
                    'log_text' => $log_text,
                    'states' => 20,
                    'payment_id' => 3 //余额支付
                );
                $model->table('epaylog')->insert($add_epaylog);
                //给商家增加冻结资金
                $sell_money_row = $model->table('epay')->where('user_id='.$order['seller_id'])->find();
                $sell_money_dj = $sell_money_row['freeze_balance']; //卖家的冻结资金
                $new_money_dj = $sell_money_dj + $summoney;
                //更新数据
                $new_money_array = array(
                    'freeze_balance' => $new_money_dj,
                );
                $model->table('epay')->where('user_id='.$order['seller_id'])->update($new_money_array);
                $log_text = $order['seller_name'].'收到'.$user['user_name']. '转入金额' . $summoney . '元';
                $add_epaylog = array(
                    'user_id' => $order['seller_id'],
                    'user_name' => $order['seller_name'],
                    'to_id' => $order['buyer_id'],
                    'to_name' => $order['buyer_name'],
                    'order_id' => $order['order_id'],
                    'order_sn' => $_POST['order_sn'],
                    'add_time' => $order['add_time'],
                    'type' => EPAY_SELLER,
                    'money_flow' => 'income',
                    'money' => $summoney,
                    'log_text' => $log_text,
                    'states' => 20,
                );
                $model->table('epaylog')->insert($add_epaylog);
                //更改订单的状态
                $array = array(
                    'payment_id'			=>3,
                    'payment_name'			=>'余额支付',
                    'payment_code'			=>'epay',
                    'pay_time'	=>time(),
                    'status'			=>20
                );
                $money=$model->table('order')->where('order_id='.$order['order_id'])->update($array);
                addMessage('order','order_id',$order['order_id'],$order['buyer_name'].'购物积分支付成功',$order['seller_id'],$order['seller_name']);
                fk("支付成功");
            }else{
                err('积分不足');
            }
        }else{
            err('支付密码输入错误');
        }

    }else{
        err('身份错误，请重新登录');
    }
}



/*
**支付后回调(线上购物积分支付后回调,app连连支付回调)
*/
function goodspayback(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["order_sn"]))err("请填写订单号");
    if(!isset($_POST["type"]))err("请填写支付类型");
    $summoney=(int)$_POST["money"];
    $user = checkToken($token);
    if($user){
        $model =new M();
        $orderinfo=$model->table('order')->where('order_sn='.$_POST['order_sn'])->find();
        if(empty($orderinfo))
        {
            err('订单不存在！');
        }
        if($_POST["type"]=='移动连连支付'||$_POST["type"]=='移动连连')
        {
            $payment_id=16;
        }
        if($_POST["type"]=='余额支付')
        {
            $payment_id=3;
        }
        $array = array(
            'payment_id'			=>$payment_id,
            'payment_name'			=>$_POST["type"],
            'pay_time'	=>time(),
            'status'			=>20
        );

        $money=$model
            ->table('order')
            ->where('order_sn='.$_POST['order_sn'])
            ->update($array);
        //支付记录
        $paymentlog=new paymentlogModel();
        $paymentlog->paymentlog($orderinfo['buyer_id'],$orderinfo['buyer_name'],$orderinfo['order_amount'],$payment_id,$orderinfo['seller_id'],$orderinfo['seller_name'],$orderinfo['order_id'],$_POST['order_sn']);
        //购物积分支付成功后发送短信
        addMessage('order','order_id',$orderinfo['order_id'],'您使用积分支付成功！',$orderinfo['buyer_id'],$orderinfo['buyer_name']);
        fk('成功');
    }else{
        err('身份错误，请重新登录');
    }
}



/*
**支付后回调(扫码购物积分支付后回调)
*/
function goodspayback_offline(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["order_sn"]))err("请填写订单号");
    if(!isset($_POST["type"]))err("请填写支付类型");
    $summoney=(int)$_POST["money"];
    $user = checkToken($token);
    if($user){
        $model =new M();
        $orderinfo=$model->table('order_offline')->where('order_sn='.$_POST['order_sn'])->find();
        if(empty($orderinfo))
        {
            err('订单不存在！');
            exit();
        }
        $array = array(
//            'payment_code'			=>'zjgl',
            'payment_id'			=>3,
            'payment_name'			=>$_POST["type"],
            'pay_time'	=>time(),
            'status'			=>40
        );
        $model =new M();
        $money=$model
            ->table('order_offline')
            ->where('order_sn='.$_POST['order_sn'])
            ->update($array);
        //支付记录
        $paymentlog=new paymentlogModel();
        $paymentlog->paymentlog($orderinfo['buyer_id'],$orderinfo['buyer_name'],$orderinfo['order_amount'], '3',$orderinfo['seller_id'],$orderinfo['seller_name'],$orderinfo['order_id'],$_POST['order_sn']);
        //购物积分支付成功后发送短信
        addMessage('order_offline','order_id',$orderinfo['order_id'],'您使用积分支付成功！',$orderinfo['buyer_id'],$orderinfo['buyer_name']);
        fk('成功');
    }else{
        err('身份错误，请重新登录');
    }
}

/*
**直接购买确认订单
*/
function directlylist(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["goods_id"]))err("请选择要购买的商品");
    if(!isset($_POST["quantity"]))err("请选择商品数量");
    if(!isset($_POST["color"]))err("请选择商品颜色");
    if(!isset($_POST["size"]))err("请选择商品尺码");
    $rec_id=$_POST["goods_id"];
    $spec_id=$_POST["spec_id"];
    if(empty($spec_id) || $spec_id<=0)
    {
        err('参数错误！');
    }
    $color=$_POST["color"];
    $size=$_POST["size"];
    $specification= '颜色:'.$color.' 尺码:'.$size.'';

    if($_POST["quantity"]=="0"){
        err('商品数量不能为0');
    }else{
        $quantity=$_POST["quantity"];
    }
    $user = checkToken($token);
    if($user){
        $model =new M();
        //收货地址
        $addresslist =$model
            ->table('address')
            ->field('addr_id,consignee,region_name,address,phone_tel')
            ->where(array('user_id'=>$user['user_id'],'type'=>'1'))
            ->find();
        /*if(empty($addresslist))
        {
            err('收货地址错误');
        }*/
        //商品信息
        $goodslist =$model->query('select goods_id,goods_name,ecm_store.store_id,ecm_store.store_name,price,default_image  from ecm_goods inner join ecm_store on ecm_goods.store_id=ecm_store.store_id where goods_id in ('.$_POST["goods_id"].')');
        //查询规格
        $goods_spec=$model->table('goods_spec')->where(array('spec_id'=>$spec_id))->find();
        $goodslist[0]['price']=$goods_spec['price'];
        $store=$model
            ->table('store')
            ->field('store_id,store_name')
            ->where(array('store_id'=>$goodslist[0]["store_id"]))
            ->find();
        if($goodslist[0]["store_id"]==$user['user_id'])
        {
            err('自己不能购买自己的产品！');
        }
        //可用积分
        $money=$model
            ->table('epay')
            ->field('money')
            ->where('user_id='.$user['user_id'])
            ->find();
        $point=$model
            ->table('goods')
            ->field('point')
            ->where('goods_id='.$_POST['goods_id'])
            ->find();
        //配送方式
        $shipping=$model
            ->table('shipping')
            ->field('shipping_id,shipping_name,shipping_desc,first_price,step_price')
            ->where('store_id='.$goodslist[0]['store_id'])
            ->find();
        //总计
        $summoney=$goodslist[0]['price']*$_POST["quantity"]+$shipping['first_price'];
        $data[]=array(
            'addresslist'	=>	$addresslist,
            'goodslist'		=>	$goodslist,
            'store_name'	=>	$store['store_name'],
            'money'			=>	$money['money'],
            'point'			=>	$point['point'],
            'specification'			=>	$specification,
            'shipping'		=>	$shipping,
            'quantity'		=>	$_POST['quantity'],
            'summoney'		=>	$summoney
        );
        if($data){
            fk("确认订单信息",$data);
        }else{
            err("数据获取失败");
        }
    }else{
        err('身份错误，请重新登录');
    }
}



/*
**添加发票信息
*/
function invoice(){
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    if(!isset($_POST["type"]))err("请选择发票类型");
    if(!isset($_POST["head"]))err("选择发票抬头");
    if($_POST["head"]=='company'){
        if(empty($_POST["company"]))err("单位信息不能为空");
    }
    if(!isset($_POST["phone"]))err("收票人手机号");
    //if(!isset($_POST["emil"]))err("收票人邮箱");
    $user = checkToken($token);
    if($user) {
        $model= new M();
        $data =array(
            'type'		=>	$_POST["type"],
            'head'		=>	$_POST["head"],
            'company'	=>	$_POST["company"],
            'phone'		=>	$_POST["phone"],
            'emil'		=>	$_POST["emil"]
        );
        $arr=$model->table('sgxt_invoice')->insert($data);
        $k=array(
            'invoice'=>	$arr
        );
        fk("发票添加成功",$arr);
    }else{
        err('身份错误，请重新登录');
    }
}