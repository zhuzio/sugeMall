<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 9:20
 */
header("Content-Type: text/html; charset=utf-8");
$gtdir='D:\phpStudy\WWW\test.4gxt.com\includes\getuisdk';
require_once($gtdir. '/' . 'IGt.Push.php');
require_once($gtdir . '/' . 'igetui/IGt.AppMessage.php');
require_once($gtdir . '/' . 'igetui/IGt.APNPayload.php');
require_once($gtdir. '/' . 'igetui/template/IGt.BaseTemplate.php');
require_once($gtdir. '/' . 'IGt.Batch.php');
require_once($gtdir. '/' . 'igetui/utils/AppConditions.php');

require_once($gtdir . '/' . 'igetui/IGt.Target.php');

//http的域名
define('HOST','http://sdk.open.api.igexin.com/apiex.htm');


//定义常量, appId、appKey、masterSecret 采用本文档 "第二步 获取访问凭证 "中获得的应用配置
define('APPKEY','SuhHavEBFU8NZzrRwCcoF2');
define('APPID','vntp77JotV9l686SWFkNA3');
define('MASTERSECRET','Igr34fJqfB6y9zQrLVMyJ');

//define('BEGINTIME','2015-03-06 13:18:00');
//define('ENDTIME','2015-03-06 13:24:00');

//群推接口案例
function pushMessageToApp(){
    $igt = new IGeTui(HOST,APPKEY,MASTERSECRET);
    //定义透传模板，设置透传内容，和收到消息是否立即启动启用
    $template = IGtNotificationTemplateDemo();
    //$template = IGtLinkTemplateDemo();
    // 定义"AppMessage"类型消息对象，设置消息内容模板、发送的目标App列表、是否支持离线发送、以及离线消息有效期(单位毫秒)
    $message = new IGtAppMessage();
    $message->set_isOffline(true);
    $message->set_offlineExpireTime(10 * 60 * 1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
    $message->set_data($template);

    $appIdList=array(APPID);
    $phoneTypeList=array('ANDROID');
    $provinceList=array('浙江');
    $tagList=array('haha');
    //用户属性
    //$age = array("0000", "0010");


    //$cdt = new AppConditions();
    // $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
    // $cdt->addCondition(AppConditions::REGION, $provinceList);
    //$cdt->addCondition(AppConditions::TAG, $tagList);
    //$cdt->addCondition("age", $age);

    $message->set_appIdList($appIdList);
    //$message->set_conditions($cdt->getCondition());

    $rep = $igt->pushMessageToApp($message,"任务组名");

    var_dump($rep);
    fk('ok',$rep);
}
function IGtNotificationTemplateDemo(){
    $template =  new IGtNotificationTemplate();
    $template->set_appId(APPID);                   //应用appid
    $template->set_appkey(APPKEY);                 //应用appkey
    $template->set_transmissionType(1);            //透传消息类型
    $template->set_transmissionContent("测试离线");//透传内容
    $template->set_title("个推");                  //通知栏标题
    $template->set_text("个推最新版点击下载");     //通知栏内容
    $template->set_logo("");                       //通知栏logo
    $template->set_logoURL("");                    //通知栏logo链接
    $template->set_isRing(true);                   //是否响铃
    $template->set_isVibrate(true);                //是否震动
    $template->set_isClearable(true);              //通知栏是否可清除

    return $template;
}

//个推测试
function getsinglegetui()
{
    $pushid='e6a7d7a79cb70b6589213a429ffec298';
    $data=array('id'=>1,'typeinfo'=>'sendpoint','title'=>'shopper sendpoint','content'=>'sendpoint 10 ok!');
    $host='http://sdk.open.api.igexin.com/apiex.htm';
    $title=$data['title'];
    $json=json_encode($data);
    $igt = new IGeTui($host,APPKEY,MASTERSECRET);

    $template = new IGtTransmissionTemplate();
    $template->set_appId(APPID);
    $template->set_appkey(APPKEY);
    $template->set_transmissionContent($json);
    $template->set_transmissionType(1);
    $template->set_pushInfo("actionLocKey", "badge", $json, "sound", "payload",$title, "locArgs", "launchImage");

    $message = new IGtSingleMessage();
    $message->set_isOffline(true);
    $message->set_offlineExpireTime(3600*12*1000);
    $message->set_data($template);
    $message->set_pushNetWorkType(0);

    $target = new IGtTarget();
    $target->set_appId(APPID);
    $target->set_clientId($pushid);

    try {
        $rep = $igt->pushMessageToSingle($message, $target);
        //return $rep;

    }catch(getuisdk\RequestException $e) {
        $requstId = $e->getRequestId();
        $rep = $igt->pushMessageToSingle($message, $target,$requstId);
        //return $rep;
        // var_dump($rep);
    }
    fk('ok',$rep);
}
//多推
/*public static function getlistgetui($uidarray,$data)
{
    $userpush=Userpush::find()->select(['companyid','pushid'])->where(['in','uid',$uidarray])->asArray()->all();
    $type=['1'=>'1','2'=>'3'];
    foreach($userpush as $k => $v)
    {
        if(!empty($v['pushid']))
        {
            $pushid[]=$v['pushid'];
            $pushtype=$type[$v['companyid']];
        }
    }
    //return $pushid;
    if(empty($pushid))
    {
        return 'pushid null';
    }
    $keytype=(string)$pushtype;
    $app=TokenHelper::get_Pushtypekey();
    $host='http://sdk.open.api.igexin.com/apiex.htm';
    $appkey=$app[$keytype]['appkey'];
    $mastersecret=$app[$keytype]['mastersecret'];
    $appid=$app[$keytype]['appid'];
    $json=json_encode($data,JSON_UNESCAPED_UNICODE);
    $igt = new getuisdk\IGeTui($host,$appkey,$mastersecret);

    $template = new getuisdk\IGtTransmissionTemplate();
    $template->setAppId($appid);
    $template->setAppkey($appkey);
    $template->setTransmissionContent($json);
    $template->setTransmissionType(1);
    $template->setPushInfo("actionLocKey", "badge", $json, "first.mp3", "payload",$data['title'], "locArgs", "launchImage");

    $message = new getuisdk\IGtListMessage();
    $message->setIsOffline(true);
    $message->setOfflineExpireTime(3600*12*1000);
    $message->setData($template);
    $message->setPushNetWorkType(0);
    $contentId = $igt->getContentId($message);

    foreach($pushid as $k => $v)
    {
        $target = new getuisdk\IGtTarget();
        $target->setAppId($appid);
        $target->setClientId($v);
        $targetList[] = $target;
    }
    try {
        //return $targetList;exit;
        $rep = $igt->pushMessageToList($contentId, $targetList);
        return $rep;
    }catch(getuisdk\RequestException $e) {
        return 'RequestException';
    }
}*/
    //单推
/*function getsinglegetui($uid,$data)
{
    $userpush=Userpush::findOne(['uid'=>$uid]);
    if(empty($userpush['pushid']))
    {
        return 'pushid null';
    }
    $keytype=$userpush['pushtype'];
    $app=TokenHelper::get_Pushtypekey();
    $host='http://sdk.open.api.igexin.com/apiex.htm';
    $appkey=$app[$keytype]['appkey'];
    $mastersecret=$app[$keytype]['mastersecret'];
    $appid=$app[$keytype]['appid'];
    $title=$data['title'];
    $json=json_encode($data,JSON_UNESCAPED_UNICODE);
    $igt = new getuisdk\IGeTui($host,$appkey,$mastersecret);

    $template = new getuisdk\IGtTransmissionTemplate();
    $template->setAppId($appid);
    $template->setAppkey($appkey);
    $template->setTransmissionContent($json);
    $template->setTransmissionType(1);
    $template->setPushInfo("actionLocKey", "badge", $json, "sound", "payload",$title, "locArgs", "launchImage");

    $message = new getuisdk\IGtSingleMessage();
    $message->setIsOffline(true);
    $message->setOfflineExpireTime(3600*12*1000);
    $message->setData($template);
    $message->setPushNetWorkType(0);

    $target = new getuisdk\IGtTarget();
    $target->setAppId($appid);
    $target->setClientId($userpush['pushid']);

    try {
        $rep = $igt->pushMessageToSingle($message, $target);
        return $rep;

    }catch(getuisdk\RequestException $e) {
        $requstId = $e->getRequestId();
        $rep = $igt->pushMessageToSingle($message, $target,$requstId);
        return $rep;
        // var_dump($rep);
    }
}*/
