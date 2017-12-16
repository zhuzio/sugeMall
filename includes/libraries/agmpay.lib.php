<?php
/** 
 * 爱干嘛支付签名验证类
 *
 * 
 * @author: ruan 
 * @version: 1.0.0 
 * @date: 2016/4/5 
 */  
class agmPay{  
  
    private $pubKey = null;  
    private $priKey = null;  
  
    /** 
     * 自定义错误处理 
     */  
    private function _error($msg){  
        die('AGM Error:' . $msg); //TODO  
    }  
  
    /** 
     * 构造函数 
     * 
     * @param string 公钥文件（验签和加密时传入） 
     * @param string 私钥文件（签名和解密时传入） 
     */  
    public function __construct($public_key_file = '', $private_key_file = ''){  
        if ($public_key_file){  
            $this->_getPublicKey($public_key_file);  
        }  
        if ($private_key_file){  
            $this->_getPrivateKey($private_key_file);  
        }  
    }  
  
  
    /** 
     * 生成签名 
     * 
     * @param string 签名材料 
     * @param string 签名编码（base64/hex/bin） 
     * @return 签名值 
     */  
    public function sign($data, $code = 'hex'){  
        $ret = false;
        if(is_array($data)){
            $data = $this->createLinkstring($data);
        }  
        if (openssl_sign($data, $ret, $this->priKey)){  
            $ret = $this->_encode($ret, $code);  
        }  
        return $ret;  
    }  
  
    /** 
     * 验证签名 
     * 
     * @param string 签名材料 
     * @param string 签名值 
     * @param string 签名编码（base64/hex/bin） 
     * @return bool  
     */  
    public function verify($data, $sign, $code = 'hex'){  
        $ret = false;
        if(is_array($data)){
            $data = $this -> paraFilter($data);
            $data = $this -> createLinkstring($data);
        }

        $sign = $this->_decode($sign, $code);  
        if ($sign !== false) {  
            switch (openssl_verify($data, $sign, $this->pubKey)){  
                case 1: $ret = true; break;      
                case 0:      
                case -1:       
                default: $ret = false;       
            }  
        }  
        return $ret;  
    }  
  
    /** 
     * 加密 
     * 
     * @param string 明文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING） 
     * @return string 密文 
     */  
    public function encrypt($data, $code = 'hex', $padding = OPENSSL_PKCS1_PADDING){  
        $ret = false;   
        if(is_array($data)){
            $data = $this->createLinkstring($data);
        }    
        if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');  
        if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)){  
            $ret = $this->_encode($result, $code);  
        }  
        return $ret;  
    }  
  
    /** 
     * 解密 
     * 
     * @param string 密文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING） 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block） 
     * @return string 明文 
     */  
    public function decrypt($data, $code = 'hex', $padding = OPENSSL_PKCS1_PADDING, $rev = false){  
        $ret = false;  
        $data = $this->_decode($data, $code);  
        if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');  
        if ($data !== false){  
            if (openssl_private_decrypt($data, $result, $this->priKey, $padding)){  
                $ret = $rev ? rtrim(strrev($result), "\0") : ''.$result;  
            }   
        }

        return $this->stringToArray($ret);  
    }
    private function stringToArray($string){

        if(!is_string($string)){
            return $string;
        }

        if(!strstr( $string,'=')){
             return $string;
        }
        $array = explode('&', $string);
        if(empty($array)) return ;
        foreach($array as $key=> $val){
            list($keys , $value) = explode('=', $val);
            $rearray[$keys] = $value;
        }
        return $rearray;
    }  
    /**
     * 建立请求，以表单HTML形式构造（默认）
     * @param $para_temp 请求参数数组
     * @param $url 请求参数数组
     * @param $method 提交方式。两个值可选：post、get
     * @param $button_name 确认按钮显示文字
     * @return 提交表单HTML文本
     */
    public function buildRequestForm($para_temp, $url , $method = 'post', $button_name = '确认提交') {
        //待请求参数数组
        $para = $this->buildRequestPara($para_temp);
        
        $sHtml = "<form id='agmpaysubmit' name='agmpaysubmit' action='".$url."' method='".$method."'>";
        while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
        
        $sHtml = $sHtml."<script>document.forms['agmpaysubmit'].submit();</script>";
        
        return $sHtml;
    }
    /**
     * 远程获取数据，POST模式
     * 注意：
     * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
     * @param $url 指定URL完整路径地址
     * @param $para_temp 请求参数数组
     * return 远程输出的数据
     */
    public function getHttpPost ( $url , $para_temp = array ()){

        if (! is_array ( $para_temp )){
        throw new Exception ( "参数必须为array" );
        }
        //待请求参数数组
        $param = $this->buildRequestPara($para_temp);

        $httph = curl_init ( $url );
        curl_setopt ( $httph , CURLOPT_SSL_VERIFYPEER , 0 );
        curl_setopt ( $httph , CURLOPT_SSL_VERIFYHOST , 1 );
        curl_setopt ( $httph , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt ( $httph , CURLOPT_USERAGENT , "Mozilla/4.0(compatible;MSIE6.0;windowsNT5.0)" );
        curl_setopt ( $httph , CURLOPT_POST , 1 ); //设置为POST方式
        curl_setopt ( $httph , CURLOPT_POSTFIELDS , $param );
        curl_setopt ( $httph , CURLOPT_RETURNTRANSFER , 1 );
        //curl_setopt ( $httph , CURLOPT_HEADER , 1 );
        $rst = curl_exec ( $httph );
        curl_close ( $httph );
        return $rst ;
    }

    private function buildRequestPara($para_temp) {
        //除去待签名参数数组中的空值和签名参数
        $para_filter =$this -> paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort =$this -> argSort($para_filter);

        //生成签名结果
        $mysign = $this->sign($para_sort);
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        
        return $para_sort;
    }
  
    // 私有方法  
  
    /** 
     * 检测填充类型 
     * 加密只支持PKCS1_PADDING 
     * 解密支持PKCS1_PADDING和NO_PADDING 
     *  
     * @param int 填充模式 
     * @param string 加密en/解密de 
     * @return bool 
     */  
    private function _checkPadding($padding, $type){  
        if ($type == 'en'){  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        } else {  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                case OPENSSL_NO_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        }  
        return $ret;  
    }  
  
    private function _encode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_encode(''.$data);  
                break;  
            case 'hex':  
                $data = bin2hex($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _decode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_decode($data);  
                break;  
            case 'hex':  
                $data = $this->_hex2bin($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _getPublicKey($file){  
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->pubKey = openssl_get_publickey($key_content);  
        }  
    }  
  
    private function _getPrivateKey($file){  
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->priKey = openssl_get_privatekey($key_content);  
        }  
    }  
  
    private function _readFile($file){  
        $ret = false;  
        if (!file_exists($file)){  
            $this->_error("The file {$file} is not exists");  
        } else {  
            $ret = file_get_contents($file);  
        }  
        return $ret;  
    }  
  
  
    private function _hex2bin($hex = false){  
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;      
        return $ret;  
    }
    //把数据封装成key&value的形式
    private function createLinkstring($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para) {
        $para_filter = array();
        while (list ($key, $val) = each ($para)) {
            if($key == "sign" || $key == "sign_type" || $val == "")continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 写日志，方便测试（看网站需求，也可以改成把记录存入数据库）
     * 注意：服务器需要开通fopen配置
     * @param $word 要写入日志里的文本内容 默认值：空值
     */
    private function logResult($word='') {
        $fp = fopen("log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".strftime("%Y%m%d%H%M%S",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }  
      
  
  
}


?>