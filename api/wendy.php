<?php
define('__DIR__',conf('dir'));//dir仅限于autoload.php里面使用
include(ROOT_PATH . '/includes/qiniusdk/autoload.php');
use Qiniu\Auth;
//新品推荐列表
function newgoods()
{
    $model = new M();
    $page =isset($_POST['page']) ? $_POST['page'] :'1'; //当前页
    $pagecount=conf('pagecount2');//默认一页条数
    $startcount=($page-1)*$pagecount;
    $totalcount=$model->table('goods')->where(array('isnew'=>1))->count();
    $goodslist=$model->table('goods')->field('goods_id,goods_name,default_image,price,market_price')->where(array('isnew'=>1))->order('goods_id desc')->limit($startcount.','.$pagecount)->select();
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
        $goodcate_ad=null;
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

        //$path 		=	C('APP_HEADIMG_PATH'); //服务器相对路径
        $path='data/files/mall/portrait/'.$user['user_id'].'/';
        $rel_path 	=	ROOT_PATH.'/'.$path;//服务器绝对路径
        //$suffix 	=	c('suffix');
        $suffix=date('YmdHis').'.jpg';
        //$picname 	=	'uid_'.$this->param['user_id'];//图片名称
        $filename 	=	$rel_path.$suffix;//图片生成绝对路径
        //$server_path=	$_SERVER["HTTP_HOST"].'/'.$path.$suffix;
        if(!file_exists($rel_path)){
            mkdir($rel_path,0777,true);
        }

        //$info=file_get_contents("aa.rtf");
        $info=$_FILES['portrait'];
        $fp=fopen('test.txt','w');
        fwrite($fp,$info);
        fclose($fp);
        $info=$_POST['head'];
        $fp=fopen('test1.txt','w');
        fwrite($fp,$info);
        fclose($fp);
        //file_put_contents($filename,base64_decode($this->param['head']));//写出文件到服务器
        //file_put_contents($filename,base64_decode($info));

        header('Content-type: text/json; charset=UTF-8' );

        /**
         * $_FILES 文件上传变量，是一个二维数组，第一维保存上传的文件的数组，第二维保存文件的属性，包括类型、大小等
         * 要实现上传文件，必须修改权限为加入可写 chmod -R 777 目标目录
         */

// 文件类型限制
// "file"名字必须和iOS客户端上传的name一致
        if (($_FILES["portrait"]["type"] == "image/gif")
            || ($_FILES["portrait"]["type"] == "image/jpeg")
            || ($_FILES["portrait"]["type"] == "image/png")
            || ($_FILES["portrait"]["type"] == "image/pjpeg"))
// && ($_FILES["file"]["size"] < 20000)) // 小于20k
        {
            if ($_FILES["portrait"]["error"] > 0) {
                err('portrait error');
                //echo $_FILES["portrait"]["error"]; // 错误代码
            } else {
                $fillname = $_FILES['portrait']['name']; // 得到文件全名
                $dotArray = explode('.', $fillname); // 以.分割字符串，得到数组
                $type = end($dotArray); // 得到最后一个元素：文件后缀

                $path = ROOT_PATH.'/'.$path.md5(uniqid(rand())).'.'.$type; // 产生随机唯一的名字

                move_uploaded_file( // 从临时目录复制到目标目录
                    $_FILES["portrait"]["tmp_name"], // 存储在服务器的文件的临时副本的名称
                    $path);

                //echo "成功";
            }
        } else {
            //echo "文件类型不正确";
            err('file error');
        }


        $portrait=$path;
        $model = new M();
        $result=$model->table('member')->where(array('user_id'=>$user['user_id']))->update(array('portrait'=>$portrait));
        if($result>0)
        {
            fk('success');
        }
        else
        {
            err('fail');
        }
    }
    else
    {
        err('fail');
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
        if($result>0)
        {
            fk('success');
        }
        else
        {
            err('fail');
        }
    }
    else
    {
        err('fail');
    }
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
?>
