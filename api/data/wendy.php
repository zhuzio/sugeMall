<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/19
 * Time: 17:30
 */
define('__DIR__',conf('dir'));//dir仅限于autoload.php里面使用
//include(ROOT_PATH . '/includes/qiniusdk/autoload.php');
//use Qiniu\Auth;
//新品推荐列表
function newgoods()
{
    $model = new M();
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount2');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $totalcount=$model->table('goods')->where(array('isnew'=>1,'closed'=>0))->count();
    $goodslist=$model->table('goods')->where(array('isnew'=>1,'closed'=>0))->order('goods_id desc')->limit($startcount.','.$pagecount)->select();
    $totalpage=ceil($totalcount/$pagecount);
    pageJson('ok',"新品列表",$goodslist,$totalpage);
}
//首页轮播图
function cate_ad()
{
    $model = new M();
    $goodslist=$model->table('ad')->field('ad_id,ad_logo,ad_name,ad_link')->where(array('ad_type'=>1,'if_show'=>1))->select();
    fk('success',$goodslist);
}
//商品分类中广告
function goodcate_ad()
{
    $cate_id =isset($_POST['cate_id']) ? $_POST['cate_id'] :'1';
    $model = new M();
    $goodcate_ad=$model->table('sgxt_cate_ad')->field('ad_id,ad_logo,ad_name,ad_link')->where(array('cate_id'=>$cate_id,'if_show'=>1))->find();
    if(empty($goodcate_ad))
    {
        $goodcate_ad='';
    }
    fk('success',$goodcate_ad);
}
//更新用户图像
function update_portrait()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user)
    {
        $path='data/files/mall/portrait/'.$user['user_id'].'/';
        $name=date('YmdHis');
        $fp=fopen('upload1.txt','w');
        fwrite($fp,$path);
        fclose($fp);
        $portrait=uploadimg('portrait',$path,$name);
        if(empty($portrait))
        {
            err('头像上传失败');
        }
        $model = new M();
        $result=$model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('portrait'=>$portrait));
        $data['portrait']=$portrait;
        fk('更新用户图像',$portrait);
    }
    else
    {
        err('fail');
    }
}
//店铺升级上传图片
function storeupimage()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $name=$_POST['typename'];
    $user = checkToken($token);
    if($user)
    {
        $path1='data/files/store_'.$user["user_id"].'/other/';
        $path2='data/files/mall/application/';
        $patharray=array('store_banner'=>$path1,'store_logo'=>$path1,'image_1'=>$path2,'image_2'=>$path2,'image_3'=>$path2);
        $namearray=array('store_banner'=>'store_banner','store_logo'=>'store_logo','image_1'=>'store_'.$user['user_id'].'_1','image_2'=>'store_'.$user['user_id'].'_2','image_3'=>'store_'.$user['user_id'].'_3');
        //上传图片
        $result=uploadimg($name,$patharray[$name],$namearray[$name]);
        if(empty($result))
        {
            err('上传失败');
        }
        /*$model=new M();
        //更新
        $model->table('store')->where(array('store_id'=>$user["user_id"]))->update(array($name=>$result));*/
        fk('success',$result);
    }
    else
    {
        err('身份错误，请重新登录');
    }
}
/**
 * 上传头像
 *
 * @param int $user_id
 * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
 */
function _upload_portrait($user_id) {
    $file = $_FILES['portrait'];
    if ($file['error'] != UPLOAD_ERR_OK) {
        return '';
    }
    import('uploader.lib');
    $uploader = new Uploader();
    $uploader->allowed_type(IMAGE_FILE_TYPE);
    $uploader->addFile($file);
    if ($uploader->file_info() === false) {
        return false;
    }
    $uploader->root_dir(ROOT_PATH);
    return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
}
//更新用户设备编号
function device_number()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user)
    {
        $model = new M();
        $result=$model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('device_number'=>$_POST['device_number']));
        fk('更新设备编号'+$result);
    }
    else
    {
        err('fail');
    }
}
//判断是否是微信打开
function is_weixin()
{
    $is_weixin=0;
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($user_agent, 'MicroMessenger') === false) {
        $is_weixin=0;
    } else {
        $is_weixin=1;
    }
    fk('判断是否是微信打开',$is_weixin);
}
//下载地址
function download()
{
    $serverinfo=$_SERVER['HTTP_USER_AGENT'];
    $agent=strtolower($serverinfo);
    $download = conf('SITE_URL').'/data/app-release.apk';
    if(strpos($agent, 'android'))
    {
        $download=conf('SITE_URL').'/data/app-release.apk';
    }
    fk('ok',$download);
}
//返回线上商城订单金额，当前可用积分
function order_money()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if(!$user)
    {
        err('身份错误，请重新登录');
    }
    $order_sn=$_POST["order_sn"];
    $model=new M();
    $order=$model->table('order')->where(array('order_sn'=>$order_sn))->find();
    if(empty($order))
    {
        err('订单不存在！');
    }
    $data['total_money']=$order['order_amount'];
    //当前可用积分
    $userpay=$model->table('epay')->where(array('user_id'=>$user['user_id']))->find();
    $data['user_money']=0;
    if(!empty($userpay['money']))
    {
        $data['user_money']=$userpay['money'];
    }
    fk('ok',$data);
}
//七牛token
function qntoken()
{
    $token = rawurlencode($_POST['token']);
    if(!isset($_POST["token"]))err("请先登录");
    $user = checkToken($token);
    if($user)
    {
        $accessKey = 'G_Pl6tQUFYF9b6PSHVnUkRs1RWu9oXYgc1rkj5Ey';
        $secretKey = '7-KvUt6ILKf8O4ZAiSebjT4m6yuIdIb_Qa6NvRXQ';
        $auth = new Auth($accessKey, $secretKey);
        // 空间名  http://developer.qiniu.com/docs/v6/api/overview/concepts.html#bucket
        $bucket = 'ecmall';
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        if (empty($token))
        {
            err('fail');
        }
        $data['qiniu_token']=$token;
        $data['qinniu_com']=conf('QINIU_URL');
        fk('success',$data);
    }
    else
    {
        err('fail');
    }
}