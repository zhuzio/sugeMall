<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 9:20
 */
header("Content-Type: text/html; charset=utf-8");
$gtdir='D:\phpStudy\WWW\test.4gxt.com\includes\php\src';

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

define('TIMESTAMP',strval(time()));//
//define('DEVICE_TOKENS','6e1acde11f18c4dbfdd353d477048c8dba761b6d33821f908cb38046ce39d035');
define('IOS_DEVICE_TOKENS','47e624db244491bae8fca16cb0fd39284bab127c41bd318569f9261635e7f75b');
define('Android_DEVICE_TOKENS','Arg3zheS4VNdk_GNnTaUfnfD3xVyN5zPqc3AiQWZHmTb');

function sendAndroidBroadcast() {
    try {
        $brocast = new AndroidBroadcast();
        $brocast->setAppMasterSecret(Android_APPMASTERSECRET);
        $brocast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $brocast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        $brocast->setPredefinedKeyValue("ticker",           "Android broadcast ticker范新莹测试");
        $brocast->setPredefinedKeyValue("title",            "Android title范新莹测试");
        $brocast->setPredefinedKeyValue("text",             "Android broadcast text范新莹测试");
        $brocast->setPredefinedKeyValue("description",        '范新莹测试');
        $brocast->setPredefinedKeyValue("after_open",       "go_app");
        // Set 'production_mode' to 'false' if it's a test device.
        // For how to register a test device, please see the developer doc.
        $brocast->setPredefinedKeyValue("production_mode", "true");
        // [optional]Set extra fields
        $brocast->setExtraField("test", "helloworld");
        //print("Sending broadcast notification, please wait...\r\n");
        $brocast->send();
        fk('ok');
        //print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        //print("Caught exception: " . $e->getMessage());
        err('fail');
    }
}

function sendAndroidUnicast() {
    try {
        $unicast = new AndroidUnicast();
        $unicast->setAppMasterSecret(Android_APPMASTERSECRET);
        $unicast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $unicast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    Android_DEVICE_TOKENS);
        $unicast->setPredefinedKeyValue("ticker",           "Android unicast ticker范新莹测试");
        $unicast->setPredefinedKeyValue("title",            "安卓-fxy单播测试");
        $unicast->setPredefinedKeyValue("text",             "测试测试测试安卓");
        $unicast->setPredefinedKeyValue("description",        '范新莹测试');
        $unicast->setPredefinedKeyValue("after_open",       "go_app");
        // Set 'production_mode' to 'false' if it's a test device.
        // For how to register a test device, please see the developer doc.
        $unicast->setPredefinedKeyValue("production_mode", "true");
        // Set extra fields
        $unicast->setExtraField("test", "helloworld");
        //print("Sending unicast notification, please wait...\r\n");
        $unicast->send();
        print("Sent SUCCESS\r\n");
        fk('ok');
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
        //err('fail');
    }
}

function sendAndroidFilecast() {
    try {
        $filecast = new AndroidFilecast();
        $filecast->setAppMasterSecret(Android_APPMASTERSECRET);
        $filecast->setPredefinedKeyValue("appkey",           100);
        $filecast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        $filecast->setPredefinedKeyValue("ticker",           "Android filecast ticker");
        $filecast->setPredefinedKeyValue("title",            "Android filecast title");
        $filecast->setPredefinedKeyValue("text",             "Android filecast text");
        $filecast->setPredefinedKeyValue("after_open",       "go_app");  //go to app
        print("Uploading file contents, please wait...\r\n");
        // Upload your device tokens, and use '\n' to split them if there are multiple tokens
        $filecast->uploadContents("aa"."\n"."bb");
        print("Sending filecast notification, please wait...\r\n");
        $filecast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendAndroidGroupcast() {
    try {
        /*
          *  Construct the filter condition:
          *  "where":
          *	{
          *		"and":
          *		[
            *			{"tag":"test"},
            *			{"tag":"Test"}
          *		]
          *	}
          */
        $filter = 	array(
            "where" => 	array(
                "and" 	=>  array(
                    array(
                        "tag" => "test"
                    ),
                    array(
                        "tag" => "Test"
                    )
                )
            )
        );

        $groupcast = new AndroidGroupcast();
        $groupcast->setAppMasterSecret(Android_APPMASTERSECRET);
        $groupcast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $groupcast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set the filter condition
        $groupcast->setPredefinedKeyValue("filter",           $filter);
        $groupcast->setPredefinedKeyValue("ticker",           "Android groupcast ticker");
        $groupcast->setPredefinedKeyValue("title",            "Android groupcast title");
        $groupcast->setPredefinedKeyValue("text",             "Android groupcast text");
        $groupcast->setPredefinedKeyValue("after_open",       "go_app");
        // Set 'production_mode' to 'false' if it's a test device.
        // For how to register a test device, please see the developer doc.
        $groupcast->setPredefinedKeyValue("production_mode", "true");
        print("Sending groupcast notification, please wait...\r\n");
        $groupcast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendAndroidCustomizedcast() {
    try {
        $customizedcast = new AndroidCustomizedcast();
        $customizedcast->setAppMasterSecret(Android_APPMASTERSECRET);
        $customizedcast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $customizedcast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set your alias here, and use comma to split them if there are multiple alias.
        // And if you have many alias, you can also upload a file containing these alias, then
        // use file_id to send customized notification.
        $customizedcast->setPredefinedKeyValue("alias",            "xx");
        // Set your alias_type here
        $customizedcast->setPredefinedKeyValue("alias_type",       "xx");
        $customizedcast->setPredefinedKeyValue("ticker",           "Android customizedcast ticker");
        $customizedcast->setPredefinedKeyValue("title",            "Android customizedcast title");
        $customizedcast->setPredefinedKeyValue("text",             "Android customizedcast text");
        $customizedcast->setPredefinedKeyValue("after_open",       "go_app");
        print("Sending customizedcast notification, please wait...\r\n");
        $customizedcast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendAndroidCustomizedcastFileId() {
    try {
        $customizedcast = new AndroidCustomizedcast();
        $customizedcast->setAppMasterSecret(Android_APPMASTERSECRET);
        $customizedcast->setPredefinedKeyValue("appkey",           Android_APPKEY);
        $customizedcast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // if you have many alias, you can also upload a file containing these alias, then
        // use file_id to send customized notification.
        $customizedcast->uploadContents("aa"."\n"."bb");
        // Set your alias_type here
        $customizedcast->setPredefinedKeyValue("alias_type",       "xx");
        $customizedcast->setPredefinedKeyValue("ticker",           "Android customizedcast ticker");
        $customizedcast->setPredefinedKeyValue("title",            "Android customizedcast title");
        $customizedcast->setPredefinedKeyValue("text",             "Android customizedcast text");
        $customizedcast->setPredefinedKeyValue("after_open",       "go_app");
        print("Sending customizedcast notification, please wait...\r\n");
        $customizedcast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendIOSBroadcast() {
    try {
        $brocast = new IOSBroadcast();
        $brocast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $brocast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $brocast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        $brocast->setPredefinedKeyValue("description",        '范新莹测试');
        $brocast->setPredefinedKeyValue("alert", '测试测试测试哈哈哈……');//内容

        $brocast->setPredefinedKeyValue("badge", 0);
        //$brocast->setPredefinedKeyValue("sound", "chime");//音频文件
        // Set 'production_mode' to 'true' if your app is under production mode
        $brocast->setPredefinedKeyValue("production_mode", "false");
        // Set customized fields
        $brocast->setCustomizedField("url", "http://www.baidu.com");//参数

        //print("Sending broadcast notification, please wait...\r\n");
        $brocast->send();
        //print("Sent SUCCESS\r\n");
        fk('ok');
    } catch (Exception $e) {
        //print("Caught exception: " . $e->getMessage());
        err('fail');
    }
}

function sendIOSUnicast() {
    try {
        $unicast = new IOSUnicast();
        $unicast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $unicast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $unicast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set your device tokens here
        $unicast->setPredefinedKeyValue("device_tokens",    IOS_DEVICE_TOKENS);
        $unicast->setPredefinedKeyValue("description",        '范新莹测试');
        $unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
        $unicast->setPredefinedKeyValue("badge", 0);
        //$unicast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $unicast->setPredefinedKeyValue("production_mode", "false");
        // Set customized fields
        $unicast->setCustomizedField("url", "http://www.baidu.com");

        $unicast->send();
        fk('ok');
    } catch (Exception $e) {
        err('fail');
    }
}

function sendIOSFilecast() {
    try {
        $filecast = new IOSFilecast();
        $filecast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $filecast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $filecast->setPredefinedKeyValue("timestamp",        TIMESTAMP);

        $filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
        $filecast->setPredefinedKeyValue("badge", 0);
        $filecast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $filecast->setPredefinedKeyValue("production_mode", "false");
        print("Uploading file contents, please wait...\r\n");
        // Upload your device tokens, and use '\n' to split them if there are multiple tokens
        $filecast->uploadContents("aa"."\n"."bb");
        print("Sending filecast notification, please wait...\r\n");
        $filecast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendIOSGroupcast() {
    try {
        /*
          *  Construct the filter condition:
          *  "where":
          *	{
          *		"and":
          *		[
            *			{"tag":"iostest"}
          *		]
          *	}
          */
        $filter = 	array(
            "where" => 	array(
                "and" 	=>  array(
                    array(
                        "tag" => "iostest"
                    )
                )
            )
        );

        $groupcast = new IOSGroupcast();
        $groupcast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $groupcast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $groupcast->setPredefinedKeyValue("timestamp",        TIMESTAMP);
        // Set the filter condition
        $groupcast->setPredefinedKeyValue("filter",           $filter);
        $groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
        $groupcast->setPredefinedKeyValue("badge", 0);
        $groupcast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $groupcast->setPredefinedKeyValue("production_mode", "false");
        print("Sending groupcast notification, please wait...\r\n");
        $groupcast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}

function sendIOSCustomizedcast() {
    try {
        $customizedcast = new IOSCustomizedcast();
        $customizedcast->setAppMasterSecret(IOS_APPMASTERSECRET);
        $customizedcast->setPredefinedKeyValue("appkey",           IOS_APPKEY);
        $customizedcast->setPredefinedKeyValue("timestamp",        TIMESTAMP);

        // Set your alias here, and use comma to split them if there are multiple alias.
        // And if you have many alias, you can also upload a file containing these alias, then
        // use file_id to send customized notification.
        $customizedcast->setPredefinedKeyValue("alias", "xx");
        // Set your alias_type here
        $customizedcast->setPredefinedKeyValue("alias_type", "xx");
        $customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
        $customizedcast->setPredefinedKeyValue("badge", 0);
        $customizedcast->setPredefinedKeyValue("sound", "chime");
        // Set 'production_mode' to 'true' if your app is under production mode
        $customizedcast->setPredefinedKeyValue("production_mode", "false");
        print("Sending customizedcast notification, please wait...\r\n");
        $customizedcast->send();
        print("Sent SUCCESS\r\n");
    } catch (Exception $e) {
        print("Caught exception: " . $e->getMessage());
    }
}
