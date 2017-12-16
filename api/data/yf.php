<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/8
 * Time: 14:05
 */
function qd_storeimage()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $name=$_POST['typename'];
    $user = checkToken($token);
    if(!$user)
    {
        err('身份错误，请重新登录');
    }
    $path1='data/files/store_'.$user["user_id"].'/other/';
    $path2='data/files/mall/application/';
    $patharray=array('store_banner'=>$path1,'store_logo'=>$path1,'image_1'=>$path2,'image_2'=>$path2,'image_3'=>$path2);
    $namearray=array('store_banner'=>'store_banner','store_logo'=>'store_logo','image_1'=>'store_'.$user['user_id'].'_1','image_2'=>'store_'.$user['user_id'].'_2','image_3'=>'store_'.$user['user_id'].'_3');
    //上传图片
    //$result=uploadimg($name,$patharray[$name],$namearray[$name]);
    preg_match('/(?<=base64,)[\S|\s]+/',$_POST['imagefile'],$streamForw);
    $filename=$patharray[$name].$namearray[$name].'.jpg';
    $dir=ROOT_PATH . '/'. $patharray[$name];
    if (! file_exists ($dir )) {
        mkdir ( "$dir", 0777, true );
    }
    //$img = base64_decode(substr($_POST['imagefile'],22));
    if (file_put_contents(ROOT_PATH . '/' .$filename,base64_decode($streamForw[0]))===false)
    {
        err("文件写入失败!");
    }
    $model=new M();
    $model->table('store')->where(array('store_id'=>$user['user_id']))->update(array($name=>$filename));
    file_put_contents('store_image.txt',$model->getSql());
    fk('success',$filename);
}