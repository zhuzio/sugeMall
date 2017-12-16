<?php 
/**
 *    微信自动登录
 *
 *    @author    ruan
 *    @usage    none
 */
class weixin_loginModel extends BaseModel
{

    public function getOpenid1(){
        import('jssdk.lib.php');
        $acctokne = new JSSDK(conf('epay_wx_appid') , conf('epay_wx_secret'));
        $access_token = $acctokne->getAccessToken();

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
        if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
            && WxPayConfig::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
            curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
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
        $urlObj["scope"] = "snsapi_base";
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