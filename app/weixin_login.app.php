<?php 
class weixin_loginApp extends FrontendApp{

    public function getUnionid(){
        $model = &m();
        if (ECMALL_WAP != 1) {
            return;
        }

        if ($this->visitor->has_login) {
            return;
        }
        if(!empty($_COOKIE['wx_access_token'])){
            return;
        }
       $weixinModel =  & m('weixin_login');
       //拿到微信返回的参数 包括access_token openid unionid
       $weixininfo = $weixinModel -> GetOpenid(SITE_URL.'/index.php?app=weixin_login&act=getUnionid');
       //将拿到的参数写入cookies
       
       if(!empty($weixininfo['access_token'])){
           setcookie('wx_access_token' , $weixininfo['access_token']);
           setcookie('wx_openid' , $weixininfo['openid']);
           setcookie('wx_unionid' , $weixininfo['unionid']);
       }
       //检查用户是否已经绑定过绑定过自动登录
       
       if(isset($_GET['code'])){
            //跳转到来源链接
            $weixininfo = $model ->table('weixin_user')-> where(array('unionid' => $weixininfo['unionid'])) -> find1();
            if(empty($weixininfo)){
                setcookie('wx_not_binding' , 1);
            }else{
                //完成自动登录
                $this->_do_login($weixininfo['user_id']);
            }
             $url = urldecode($_GET[formurl]);
            header("Location:$url");
       }
    }

    public function test(){
        $weixinModel =  & m('weixin_login');
        $a = $weixinModel -> pus_message('486' , 1 , '恭喜你消费成功',array('海底捞','20153620','58' ,'2016.3.12'),'欢迎下次光临' );
        dump($a);
    }
}


 ?>