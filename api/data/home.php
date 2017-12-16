<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/28
 * Time: 08:05
 */
//每日精选--今日推荐
function day_selected()
{
    $model=new M();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,point')->where(array('isday'=>1,'closed'=>0))->order('goods_id desc')->limit('0,4')->select();
    fk("今日推荐",$goodslist);
}
//每日精选--今日推荐
function day_selected_page()
{
    $model=new M();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,point')->where(array('isday'=>1,'closed'=>0))->order('goods_id desc')->select();
    fk("今日推荐",$goodslist);
}
//猜你喜欢
function islike()
{
    $model=new M();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,point')->where(array('islike'=>1,'closed'=>0))->order('goods_id desc')->limit('0,10')->select();
    fk("每日精选",$goodslist);
}
//首页分类
function cate()
{
    $model=new M();
    $data=$model->table('gcategory')->field('store_id,cate_id,cate_name,parent_id,cate_logo')->where(array('if_show'=>'1','store_id'=>'0','parent_id'=>'0'))->order('sort_order asc')->select();
    fk('分类信息',$data);
}
//精品推荐--首页
function boutique()
{
    $model=new M();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,point')->where(array('mall_recommended'=>1,'closed'=>0))->order('goods_id desc')->limit('0,10')->select();
    fk("精品推荐",$goodslist);
}
//精品推荐2 --首页
function boutique2()
{
    $model=new M();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,point')->where(array('mall_recommended2'=>1,'closed'=>0))->order('goods_id desc')->limit('0,10')->select();
    fk("精品推荐2",$goodslist);
}
//精品推荐页面
function mall_recommended(){
    $condition='(mall_recommended=1 or mall_recommended2=1) and closed = 0';
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount2');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $model=new M();
    $totalcount=$model
        ->table('goods')
        ->field('goods_id,goods_name,price,default_image')
        ->where($condition)
        ->count();
    $totalpage=ceil($totalcount/$pagecount);
    $data=$model
        ->table('goods')
        ->field('goods_id,goods_name,price,market_price,default_image')
        ->where($condition)->order('goods_id desc')->limit($startcount.','.$pagecount)
        ->select();
    if($data){
        pageJson('ok','msg',$data,$totalpage);
    }else{
        fk("数据获取失败");
    }
}
//精品推荐的图片
function image()
{
    $model = new M();
    $data=$model->table('ad')->field('ad_id,ad_logo,ad_name,ad_link')->where(array('ad_type'=>11,'if_show'=>1))->select();
    fk('success',$data);
}