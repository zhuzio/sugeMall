<?php
//检查每日定时执行是否异常
function serviceinfo()
{
    $mobile='17739500188';
    $url='http://sugemall.com/api/index.php?n=check&f=moneyauto';
    $content=file_get_contents($url);
    file_put_contents('checkinfo.txt','苏格时代商城：'.date('Y-m-d H:i:s').$content."\n",FILE_APPEND);
    if(strstr($content,'err'))
    {
        //发送短信
        $sms_content=date('Y-m-d H:i:s').'苏格定时执行';
        import('mobile_msg.lib');
        $mobile_msg = new Mobile_msg();
        $result = $mobile_msg->send_msg_system_wendy($sms_content, $mobile);
        file_put_contents('checkinfo.txt',date('Y-m-d H:i:s').'短信结果：'.$result."\n",FILE_APPEND);
    }
    echo 'ok';
}