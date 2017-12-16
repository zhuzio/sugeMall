<?php

//
function replace_tpl($array , $tpl){

    if(empty($array) || empty($tpl)) return ;

    foreach($array as $key => $val){

       $tpl = str_replace('{'.$key.'}' , $val , $tpl);

    }

    return $tpl;

}

//发送邮件
// function sendmail($email_subject,$email_content)
// {
//     import('mailer.lib');
//     /* 使用mailer类 */
//     $sender = Conf::get('site_name');
//     $from = Conf::get('email_addr');
//     $protocol = Conf::get('email_type');
//     $host = Conf::get('email_host');
//     $port = Conf::get('email_port');
//     $username = Conf::get('email_id');
//     $password = Conf::get('email_pass');
//     $email_test=Conf::get('email_test');
//     $mailer = new Mailer($sender, $from, $protocol, $host, $port, $username, $password);
//     $mail_result=$mailer->send($email_test, $email_subject, $email_content, CHARSET, 1);
    
// }
?>