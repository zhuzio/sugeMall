<?php 
/**
 *    微信自动登录
 *
 *    @author    ruan
 *    @usage    none
 */
class weixin_loginModel extends BaseModel
{

    public function getAccessToken(){
        import('jssdk.lib');
        $acctokne = new JSSDK(conf('epay_wx_appid') , conf('epay_wx_secret'));
        return $access_token = $acctokne->getAccessToken();

    }

    public function GetOpenid($url)
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode($url);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }
    /**
     * 
     *  获取用户详细信息
     * 
     * 
     * 
     */
    public function getUserWeixinInfo($accesstoken , $openid){
        $urlObj["access_token"] = $accesstoken;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = "zh_CH";
        $bizString = $this->ToUrlParams($urlObj);
        $user_url =  "https://api.weixin.qq.com/sns/userinfo?".$bizString;
        
        $userinfo = $this->httpGet($user_url);
        return $this->objectToArray(json_decode($userinfo ));
    }
    /*
    * 封装用户推送消息类型
    * $openid string   用户openid
    * $tempid string   推送模板id
    * $first array     标题头
    * $keynote array   内容
    * $remark array    备注
     */
    public function pus_message($userid , $tempid ,$first , $keynote ,$remark,$url=''){
        //封装推送消息的方法
        $openid = $this-> getUserOpenid($userid);
        if(empty($openid)) return ;

        $data['touser'] = $openid;
        $data['template_id'] = $this->gettempid($tempid);
        $data['url'] = $url;
        $data['data']['first'] = is_array($first)?$first:array('value' => $first,'color' => '#173177');

        $i = 1;
        foreach($keynote as $key=>$val){
            $notekey = 'keyword'.$i;
            $data['data'][$notekey] = is_array($val)?$val:array('value' => $val,'color' => '#173177');
            $i++ ;
        }
        $data['data']['remark'] = is_array($remark)?$remark:array('value' => $remark,'color' => '#173177');
        
        $senddata = json_encode($data);

        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->getAccessToken();
        
        return  $this -> httpPost($url ,  $senddata);
    }
    private function getUserOpenid($userid){
        $model = & m();
        $user = $model ->table('weixin_user') ->where(array('user_id' => $userid)) ->find1();
        return $user['openid'];
    }
    private function gettempid($tempid){
        switch($tempid){
            case 1: $temp = 'TmzskUTkRkuBnDhrus3zRo6oKjvouaENcsaqnwfHV2w';break;  //订单支付成功
            case 2: break;
            case 3: break;

        }
        return $temp;
    }
    /**
     * 
     * 
     * 封装对象转数组的方法
     * 
     * @return array
     */
    private function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)$this->objectToArray($v);
        }
        return $e;
    }
    /**
     * 
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * 
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $ch = curl_init();
        //设置超时
//      curl_setopt($ch, CURLOP_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data;
        return $openid;
    }
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
  }
  public function httpPost ( $url , $para_temp = array ()){

        $httph = curl_init ( $url );
        curl_setopt ( $httph , CURLOPT_SSL_VERIFYPEER , 0 );
        curl_setopt ( $httph , CURLOPT_SSL_VERIFYHOST , 1 );
        curl_setopt ( $httph , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt ( $httph , CURLOPT_USERAGENT , "Mozilla/4.0(compatible;MSIE6.0;windowsNT5.0)" );
        curl_setopt ( $httph , CURLOPT_POST , 1 ); //设置为POST方式
        curl_setopt ( $httph , CURLOPT_POSTFIELDS , $para_temp );
        curl_setopt ( $httph , CURLOPT_RETURNTRANSFER , 1 );
        //curl_setopt ( $httph , CURLOPT_HEADER , 1 );
        $rst = curl_exec ( $httph );
        curl_close ( $httph );
        return $rst ;
    }
    /**
     * 
     * 拼接签名字符串
     * @param array $urlObj
     * 
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * 
     * @return 返回构造好的url
     */
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = conf('epay_wx_appid');
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     * 
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * 
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {

        $urlObj["appid"] =conf('epay_wx_appid');
        $urlObj["secret"] = conf('epay_wx_secret');
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    
    
}

 ?>