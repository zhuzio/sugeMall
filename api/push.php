<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/26
 * Time: 17:14
 */
header("Content-Type: text/html; charset=utf-8");
//$gtdir='D:\phpStudy\WWW\test.4gxt.com\includes\php\src';
$gtdir=conf('umengsdkdir');

require_once($gtdir . '/' . 'notification/android/AndroidBroadcast.php');
require_once($gtdir. '/' . 'notification/android/AndroidFilecast.php');
require_once($gtdir. '/' . 'notification/android/AndroidGroupcast.php');
require_once($gtdir. '/' . 'notification/android/AndroidUnicast.php');
require_once($gtdir. '/' . 'notification/android/AndroidCustomizedcast.php');
require_once($gtdir. '/' . 'notification/ios/IOSBroadcast.php');
require_once($gtdir. '/' . 'notification/ios/IOSFilecast.php');
require_once($gtdir. '/' . 'notification/ios/IOSGroupcast.php');
require_once($gtdir. '/' . 'notification/ios/IOSUnicast.php');
require_once($gtdir. '/' . 'notification/ios/IOSCustomizedcast.php');

//定义常量, appId、appKey、masterSecret 采用本文档 "第二步 获取访问凭证 "中获得的应用配置
define('IOS_APPKEY','57ff2f72e0f55a2078000257');
define('IOS_APPMASTERSECRET','umqnfkhfqmiskd2rur8apca5ouhhvxqk');

define('Android_APPKEY','580ad124b27b0a1a9d0018cc');
define('Android_APPMASTERSECRET','tcgwtyxjgzjq5kxyzhvtgmplvru72i7c');

define('TIMESTAMP',strval(time()));


function sendAndroidUnicast($device_tokens,$content,$url) {
    try {
        $unicast = new AndroidUnicast();
        $unicast->setAppMasterSecret(Android_APPMASTERSECRET);
        $unicast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $unicast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);
        $unicast->setPredefinedKeyValue("ticker",           "Android unicast ticker");
        $unicast->setPredefinedKeyValue("title",            $content);
        $unicast->setPredefinedKeyValue("text",             $content);
        $unicast->setPredefinedKeyValue("after_open",       "go_app");
        // Set 'production_mode' to 'false' if it's a test device.
        // For how to register a test device, please see the developer doc.
        $unicast->setPredefinedKeyValue("production_mode", "true");
        // Set extra fields
        $unicast->setExtraField("url",$url);
        $unicast->send();

    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}
function sendIOSUnicast($device_tokens,$content,$url) {
    try {
        $unicast = new IOSUnicast();
        $unicast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $unicast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $unicast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);
        $unicast->setPredefinedKeyValue("description",        $content);
        $unicast->setPredefinedKeyValue("alert", $content);
        $unicast->setPredefinedKeyValue("badge", 0);
        //$unicast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $unicast->setPredefinedKeyValue("production_mode", "false");
        // Set customized fields
        $unicast->setCustomizedField("url",$url);
        $unicast->send();
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}