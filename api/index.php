<?php
header("Content-type:text/html;charset=utf-8");
header('Access-Control-Allow-Origin:*');
define('IMAGE_FILE_TYPE', 'gif|jpg|jpeg|png'); // 图片类型，上传图片时使用

define('SIZE_GOODS_IMAGE', '2097152');   // 商品大小限制2M
define('SIZE_STORE_LOGO', '1048576');      // 店铺LOGO大小限制2OK
define('SIZE_STORE_BANNER', '1048576');  // 店铺BANNER大小限制1M
define('SIZE_STORE_CERT', '1048576');     // 店铺证件执照大小限制400K
define('SIZE_STORE_PARTNER', '102400');  // 店铺合作伙伴图片大小限制100K
define('SIZE_CSV_TAOBAO', '2097152');     // 淘宝助理CSV大小限制2M

/* 店铺状态 */
define('STORE_APPLYING', 0); // 申请中
define('STORE_OPEN',     1); // 开启
define('STORE_CLOSED',   2); // 关闭

/* 积分类型 */
define('INTEGRAL_REG', 1);    // 注册赠送积分
define('INTEGRAL_LOGIN', 2);  // 登录赠送积分
define('INTEGRAL_RECOM', 3);  // 推荐赠送积分
define('INTEGRAL_BUY', 4);    // 购买赠送积分
define('INTEGRAL_SELLER', 5); // 抵扣删减积分
define('INTEGRAL_ADD', 6);    // 管理员增加积分
define('INTEGRAL_SUB', 7);    // 管理员减少积分
define('INTEGRAL_EGG', 8);    // 砸金蛋删减积分
define('INTEGRAL_GOODS', 9);  // 兑换礼品删减积分


/* 资金管理类型 */
define('EPAY_ADMIN', 10);    // 管理员手工操作
define('EPAY_BUY', 20);    // 购买商品
define('EPAY_SELLER', 30);    // 出售商品
define('EPAY_IN', 40);    // 账户转入
define('EPAY_OUT', 50);    // 账户转出
define('EPAY_CZ', 60);    // 账户充值
define('EPAY_TX', 70);    // 账户提现
define('EPAY_REFUND_IN', 80); // 账户退款收入,通常为买家退款成功 得到退款
define('EPAY_REFUND_OUT',90); // 账户退款收入,通常为卖家退款成功 扣除退款
define('EPAY_TUIJIAN_BUYER',100); // 用户推荐注册,注册者购买产品，推荐人会获得佣金，店铺会损失佣金。
define('EPAY_TUIJIAN_SELLER',110); // 用户推荐注册,注册者成为店主，卖出产品推荐人会获得佣金，店主会损失佣金。
define('EPAY_TRADE_CHARGES',120); // 交易成功扣除佣金
define('EPAY_BEAN_RETURN',130); // 受赠权每日定反佣金
define('EPAY_AGENT_RETURN',140); // 代理费每日定反
define('EPAY_UNFREEZE',150); //余额解冻

/* 意见留言类型 */
define('CUSTOMER_MESSAGE_SUGGESTION', 1);    // 给网站留言
define('CUSTOMER_MESSAGE_STORE', 2);    // 投诉店铺
define('CUSTOMER_MESSAGE_GOODS', 3);    // 投诉商品
define('CUSTOMER_EPAYOFFLINE', 4);    // 线下汇款 提交


/* 订单状态 */
define('ORDER_SUBMITTED', 10);                 // 针对货到付款而言，他的下一个状态是卖家已发货
define('ORDER_PENDING', 11);                   // 等待买家付款
define('ORDER_ACCEPTED', 20);                  // 买家已付款，等待卖家发货
define('ORDER_SHIPPED', 30);                   // 卖家已发货
define('ORDER_FINISHED', 40);                  // 交易成功
define('ORDER_CANCELED', 0);                   // 交易已取消
define('ORDER_REFUND', 50);                   // 订单退款

$py_start=microtime_float(); //记录起始时间
include("config.db.php");
include("db.class.php");
define('IN_ECM', true);
include("../eccore/model/model.base.php");
session_start();
include("common.function.php");
/* 应用根目录 */
define('APP_ROOT', dirname(__FILE__));          //该常量只在后台使用
define('ROOT_PATH', dirname(APP_ROOT));   //该常量是ECCore要求的
$GLOBALS['ECMALL_CONFIG'] = include("../data/settings.inc.php");
include("paymentlog.php");
require_once '../app/reapal/util.php';
require_once '../app/reapal/config.php';
if(!isset($_GET["n"])){  //n代表文件名
    err("接口调用错误：未定义接口文件");
}else{
    if(!file_exists("./data/".$_GET["n"].".php")){
        err("接口调用错误：接口文件不存在");
    }else{
        include("./data/".$_GET["n"].".php");
    }
}
if(!isset($_GET["f"])){  //f代表函数名
    err("接口调用错误：未定义接口函数");
}else{
    if (!function_exists($_GET["f"])) {
        err("接口调用错误：未找到接口函数");
    }else{
        call_user_func($_GET["f"]);
    }
}

//接口耗时
function haoshi(){
    global $py_start;
    return number_format(((microtime_float()-$py_start)*1000),4)."ms";
}
function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
function err($msg){
    $return=array();
    $return["ret"]="err";
    $return["msg"]=$msg;
    $return["runtime"]=haoshi();
    echo json_encode($return);
    exit();
}


function out($msg){
    $return=array();
    $return["ret"]="out";
    $return["msg"]=$msg;
    $return["runtime"]=haoshi();
    echo json_encode($return);
    exit();
}


function fk($msg,$data=""){
    $return=array();
    $return["ret"]="ok";
    $return["msg"]=$msg;
    $return["data"]=$data;
    $return["runtime"]=haoshi();
    echo json_encode($return);
    exit();
}

function out_json($ret=0,$msg='',$data=array() ){
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data));
}


function outJson($ret='ok',$msg='',$data=array()){
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data));
}
function pageJson($ret='ok',$msg='',$data=array(),$totalpage=''){
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data,'totalpage'=>$totalpage));
}
function outputJson($ret='ok',$msg='',$data=array(),$totalpage='',$arr=array()){
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data,'totalpage'=>$totalpage,'arr'=>$arr));
}
function goodsJson($ret='ok',$msg='',$data=array(),$color=array(),$spec=array(),$image=array())
{
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data,'color'=>$color,'spec'=>$spec,'image'=>$image));
}
function outJsonpoint($ret='ok',$msg='',$data=array(),$totalpage='',$summoney='',$newpoint='',$totalmoney=''){
    echo json_encode(array('ret'=>$ret,'msg'=>$msg,'data'=>$data,'totalpage'=>$totalpage,'summoney'=>$summoney,'newpoint'=>$newpoint,'totalmoney'=>$totalmoney));
}
function checkToken($token){
    $token=@str_replace("%2B","+",$token);
    $token=@str_replace("%2F","/",$token);
    $user_id = _authcode($token,'DECODE');

    $model = new M();
    $user = $model->table('member')->where(array('user_id'=>$user_id,'status'=>1))->find();
    if($user){
        return $user;
    }else{
        return false;
    }
}

/**
 * token生成与解析
 * @param $string   加密字符串
 * @param string $operation 操作符，DECODE解密，ENCODE加密
 * @param string $key   混淆码
 * @param int $expiry
 * @return string
 */
function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;

    $key = md5($key ? $key : AUTH_KEY);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }

}


/**
 * 获取配置参数
 * @param string $val
 * @return mixed
 */
function conf($val=''){
    if(empty($val)){
        return $GLOBALS['ECMALL_CONFIG'];
    }
    if(is_string($val)){
        $val = trim($val,'/');
        if(strstr($val, '/')){

            $keys = explode('/', $val);
            $num = count($keys);
            $conf = $GLOBALS['ECMALL_CONFIG'];
            for($i = 0 ;$i<$num ; $i++ ){
                $conf = $conf[$keys[$i]];
            }
            return $conf;
        }else{
            return $GLOBALS['ECMALL_CONFIG'][$val];
        }
    }else if(is_array($val)){
        $conf = $GLOBALS['ECMALL_CONFIG'];
        foreach($val as $value){
            $conf = $conf[$value];
        }
        return $conf;
    }

}

/**
 * 获取参数并过滤
 * @param $name
 * @param string $default
 * @param null $filter
 * @return array
 */
function I($name,$default='',$filter=null){
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        return false;
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        default:
            $input =&  $_REQUIST;
    }
    if(empty($filter)){
        $filter='htmlspecialchars';
    }
    if(!empty($name)){
        return $filter($input[$name]);
    }else{
        return $input;
    }
}

function gmtime()
{
    //return (time() - date('Z'));
    return time();
}
/**
 *    导入一个类
 *
 *    @author    Garbin
 *    @return    void
 */
function import() {
    $c = func_get_args();
    if (empty($c)) {
        return;
    }
    array_walk($c, create_function('$item, $key', 'include_once(ROOT_PATH . \'/includes/libraries/\' . $item . \'.php\');'));
}

function ecm_json_decode($value, $type = 0) {
    if (CHARSET == 'utf-8' && function_exists('json_decode')) {
        return empty($type) ? json_decode($value) : get_object_vars_deep(json_decode($value));
    }

    if (!class_exists('JSON')) {
        import('json.lib');
    }
    $json = new JSON();
    return $json->decode($value, $type);
}

/**
 *    所有类的基础类
 *
 *    @author    Garbin
 *    @usage    none
 */
class Object {
    public $settings;
    var $_errors = array();
    var $_errnum = 0;

    function __construct() {
        //初始化加载配置文件

        $this->Object();

    }

    function Object() {
        #TODO
    }

    /**
     *    触发错误
     *
     *    @author    Garbin
     *    @param     string $errmsg
     *    @return    void
     */
    function _error($msg, $obj = '') {
        if (is_array($msg)) {
            $this->_errors = array_merge($this->_errors, $msg);
            $this->_errnum += count($msg);
        } else {
            $this->_errors[] = compact('msg', 'obj');
            $this->_errnum++;
        }
    }

    /**
     *    检查是否存在错误
     *
     *    @author    Garbin
     *    @return    int
     */
    function has_error() {
        return $this->_errnum;
    }

    /**
     *    获取错误列表
     *
     *    @author    Garbin
     *    @return    array
     */
    function get_error() {
        return $this->_errors;
    }

}
/**
 *    将default.abc类的字符串转为$default['abc']
 *
 *    @author    Garbin
 *    @param     string $str
 *    @return    string
 */
function strtokey($str, $owner = '') {
    if (!$str) {
        return '';
    }
    if ($owner) {
        return $owner . '[\'' . str_replace('.', '\'][\'', $str) . '\']';
    } else {
        $parts = explode('.', $str);
        $owner = '$' . $parts[0];
        unset($parts[0]);
        return strtokey(implode('.', $parts), $owner);
    }
}

/**
 * 创建MySQL数据库对象实例
 *
 * @author  wj
 * @return  object
 */
function &db() {
    include_once(ROOT_PATH . '/eccore/model/mysql.php');
    static $db = null;
    if ($db === null) {
        $cfg = parse_url(DB_CONFIG);

        if ($cfg['scheme'] == 'mysql') {
            if (empty($cfg['pass'])) {
                $cfg['pass'] = '';
            } else {
                $cfg['pass'] = urldecode($cfg['pass']);
            }
            $cfg ['user'] = urldecode($cfg['user']);

            if (empty($cfg['path'])) {
                trigger_error('Invalid database name.', E_USER_ERROR);
            } else {
                $cfg['path'] = str_replace('/', '', $cfg['path']);
            }

            $charset = (CHARSET == 'utf-8') ? 'utf8' : CHARSET;
            $db = new cls_mysql();
            $db->cache_dir = ROOT_PATH . '/temp/query_caches/';
            $db->connect($cfg['host'] . ':' . $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['path'], $charset);
        } else {
            trigger_error('Unkown database type.', E_USER_ERROR);
        }
    }

    return $db;
}
function &m($model_name='', $params = array(), $is_new = false){
    static $models = array();
    //小阮自定义修改
    if(empty($model_name)){
        $model_name = 'model';
        $model_file = ROOT_PATH . '/eccore/model/' . $model_name . '.base.php';
    }
    $model_hash = md5($model_name . var_export($params, true));
    if ($is_new || !isset($models[$model_hash])) {
        if(empty($model_file)){
            $model_file = ROOT_PATH . '/includes/models/' . $model_name . '.model.php';
        }
        if (!is_file($model_file)) {
            /* 不存在该文件，则无法获取模型 */
            return false;
        }
        include_once($model_file);
        $model_name = $model_name=='model'?'Base':$model_name;
        $model_name = ucfirst($model_name) . 'Model';
        $db=new M();
        if ($is_new) {
            return new $model_name($params, $db);
        }
        $models[$model_hash] = new $model_name($params,$db);
    }

    return $models[$model_hash];
}
/**
 *    配置管理器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Conf {

    /**
     *    加载配置项
     *
     *    @author    Garbin
     *    @param     mixed $conf
     *    @return    bool
     */
    function load($conf) {
        $old_conf = isset($GLOBALS['ECMALL_CONFIG']) ? $GLOBALS['ECMALL_CONFIG'] : array();
        if (is_string($conf)) {
            $conf = include($conf);
        }
        if (is_array($old_conf)) {
            $GLOBALS['ECMALL_CONFIG'] = array_merge($old_conf, $conf);
        } else {
            $GLOBALS['ECMALL_CONFIG'] = $conf;
        }
    }

    /**
     *    获取配置项
     *
     *    @author    Garbin
     *    @param     string $k
     *    @return    mixed
     */
    static function get($key = '') {
        $vkey = $key ? strtokey("{$key}", '$GLOBALS[\'ECMALL_CONFIG\']') : '$GLOBALS[\'ECMALL_CONFIG\']';

        return eval('if(isset(' . $vkey . '))return ' . $vkey . ';else{ return null; }');
    }

}
function ecm_json_encode($value) {
    if (CHARSET == 'utf-8' && function_exists('json_encode')) {
        return json_encode($value);
    }

    $props = '';
    if (is_object($value)) {
        foreach (get_object_vars($value) as $name => $propValue) {
            if (isset($propValue)) {
                $props .= $props ? ',' . ecm_json_encode($name) : ecm_json_encode($name);
                $props .= ':' . ecm_json_encode($propValue);
            }
        }
        return '{' . $props . '}';
    } elseif (is_array($value)) {
        $keys = array_keys($value);
        if (!empty($value) && !empty($value) && ($keys[0] != '0' || $keys != range(0, count($value) - 1))) {
            foreach ($value as $key => $val) {
                $key = (string) $key;
                $props .= $props ? ',' . ecm_json_encode($key) : ecm_json_encode($key);
                $props .= ':' . ecm_json_encode($val);
            }
            return '{' . $props . '}';
        } else {
            $length = count($value);
            for ($i = 0; $i < $length; $i++) {
                $props .= ($props != '') ? ',' . ecm_json_encode($value[$i]) : ecm_json_encode($value[$i]);
            }
            return '[' . $props . ']';
        }
    } elseif (is_string($value)) {
        //$value = stripslashes($value);
        $replace = array('\\' => '\\\\', "\n" => '\n', "\t" => '\t', '/' => '\/',
            "\r" => '\r', "\b" => '\b', "\f" => '\f',
            '"' => '\"', chr(0x08) => '\b', chr(0x0C) => '\f'
        );
        $value = strtr($value, $replace);
        if (CHARSET == 'big5' && $value{strlen($value) - 1} == '\\') {
            $value = substr($value, 0, strlen($value) - 1);
        }
        return '"' . $value . '"';
    } elseif (is_numeric($value)) {
        return $value;
    } elseif (is_bool($value)) {
        return $value ? 'true' : 'false';
    } elseif (empty($value)) {
        return '""';
    } else {
        return $value;
    }
}

/*
 * 上传图片
 */
//*/
function uploadimg($file,$path,$name)
{
    $fp=fopen('upload.txt','w');
    $resultname='';
    $rel_path 	=	ROOT_PATH.'/'.$path;//服务器绝对路径
    if(!file_exists($rel_path)){
        mkdir($rel_path,0777,true);
    }
    fwrite($fp,'1111111'."\n");
    fwrite($fp,$_POST['mimeType']."\n");
    fwrite($fp,$file.'=='.$_FILES[$file]["type"]."\n");
    header('Content-type: text/json; charset=UTF-8' );
    $imgtype=array('image/gif','image/jpg','image/jpeg','image/png','image/pjpeg');
    if(in_array($_FILES[$file]["type"],$imgtype)||in_array($_POST['mimeType'],$imgtype))
    {
        if ($_FILES[$file]["error"] > 0) {
            err($file.' error');
        } else {
            fwrite($fp,'33333333333333'."\n");
            $fillname = $_FILES[$file]['name']; // 得到文件全名
            if(!empty($_POST[$file]))
            {
                $fillname=$_POST[$file];
            }
            fwrite($fp,$fillname."\n");
            $dotArray = explode('.', $fillname); // 以.分割字符串，得到数组
            $type = end($dotArray); // 得到最后一个元素：文件后缀
            $path = $path .$name. '.' . $type; // 产生随机唯一的名字
            fwrite($fp,$_FILES[$file]["tmp_name"]."\n");
            fwrite($fp,ROOT_PATH . '/' . $path."\n");
            $result=move_uploaded_file( // 从临时目录复制到目标目录
                $_FILES[$file]["tmp_name"], // 存储在服务器的文件的临时副本的名称
                ROOT_PATH . '/' . $path);
            $resultname=$path;
            fwrite($fp,$result."\n");
            fwrite($fp,$path);
            fclose($fp);
        }
    }
    return $resultname;
}
//上传多张图片，返回多张图片名称，以“|”隔开
function uploadimglist($file,$path)
{
    $fp=fopen('uploadlist.txt','w');
    $resultname='';
    $rel_path 	=	ROOT_PATH.'/'.$path;//服务器绝对路径
    if(!file_exists($rel_path)){
        mkdir($rel_path,0777,true);
    }
    fwrite($fp,'1111111'."\n");
    fwrite($fp,$file.'=='.$_FILES[$file]["type"]."\n");
    for ($i=0; $i<count($_FILES[$file]['error']); $i++) {
        //if ($_FILES[$file]['error'][$i] == 0) {
        $fillname = $_FILES[$file]['name']; // 得到文件全名
        fwrite($fp,$fillname."\n");
        $dotArray = explode('.', $fillname); // 以.分割字符串，得到数组
        $type = end($dotArray); // 得到最后一个元素：文件后缀
        $name=date('YmdHis').rand(1000,9999);
        $path = $path .$name. '.' . $type; // 产生随机唯一的名字
        move_uploaded_file( // 从临时目录复制到目标目录
            $_FILES[$file]["tmp_name"][$i], // 存储在服务器的文件的临时副本的名称
            ROOT_PATH . '/' . $path);
        $resultname.=$path."|";
        //}
    }
    $resultname=substr($resultname, 0,strlen($resultname)-1);
    fwrite($fp,$resultname."\n");
    fclose($fp);
    return $resultname;
}

function addMessage($table , $key ,$value ,$title ,$touserid = '', $touser = '',$status='',$checked = '1' , $pushtype = 'one'){
    $model = new M();
    $ms_type=conf('ms_type');
    $add = array(
        'table_name'  => $table,
        'table_key'    => $key,
        'table_value'  => $value,
        'title'  => $title,
        'checked' => $checked,
        'to_user' => $touser,
        'to_userid' => $touserid,
        'addtime'  => time(),
        'ms_type' => $ms_type[$table],
        'push_type' => $pushtype,
        'status' => $status,
    );
    $messageid=$model->table('push_message') -> insert($add);
    //发送推送消息(查询用户的umeng设备编号)
    $info=$model->table('member')->field('device_number')->where(array('user_id'=>$touserid))->find();
    /*if(empty($info)|| empty($info['device_number']))
    {
        err('umeng设备号不存在');
    }*/
    $content=$title;
    $url=geturl($touserid,$table,$key,$value,$status);
    if(!empty($url))
    {
        $url=conf('SITE_URL').'/wap/'.$url;
    }
    /*if(!empty($info))
    {
        if(!empty($info['device_number']))
        {
            include("push.php");
            $len=strlen($info['device_number']);
            if($len==44)
            {
                //android
                sendAndroidUnicast($info['device_number'],$content,$url);
            }
            else
            {
                //ios
                sendIOSUnicast($info['device_number'],$content,$url);
            }
        }
    }*/
    return  $messageid;
}
function geturl($userid,$table,$tableid,$tablevalue,$status)
{
    $model=new M();
    $ms_type_list=conf('ms_type');
    $ms_type=$ms_type_list[$table];
    $url='';
    if($ms_type==1)
    {
        //查询是用户是卖家买家
        $info=$model->table($table)->where(array($tableid=>$tablevalue))->find();
        if($userid==$info['buyer_id'])
        {
            $url='order_on.html';
        }
        else
        {
            $user_type=2;
            $tablearray=array('order'=>'z-union-shop-online-order.html','order_offline'=>'l-order1.html');
            $url=$tablearray[$table];
        }
    }else if($ms_type==2)
    {
        $url='transrorm2.html';
    }else if($ms_type==3)
    {
        $user_type=2;
        $url='buymingxi.html';
    }else if($ms_type==4)
    {
        $statusarray=array('WAIT_SELLER_AGREE'=>'wdtksq.html','SELLER_REFUSE_BUYER'=>'mjjj.html','SUCCESS'=>'mjcg.html','CLOSED'=>'tkxq.html');
        $url=$statusarray[$status];
    }else if($ms_type==6)
    {
        $url='index.html';
    }else{
        $url='index.html';
    }
    return $url;
}