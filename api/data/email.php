<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 9:20
 */


function send()
{
    header("Content-Type: text/html; charset=utf-8");
    import('mailer.lib');
    /* 使用mailer类 */
    $sender = Conf::get('site_name');
    $from = Conf::get('email_addr');
    $protocol = Conf::get('email_type');
    $host = Conf::get('email_host');
    $port = Conf::get('email_port');
    $username = Conf::get('email_id');
    $password = Conf::get('email_pass');
    $email_test=Conf::get('email_test');
    $email_subject ='邮件测试';
    $email_content = '这是一封测试邮件';
    /*$email_subject='报警';
    $email_content='用户XXX,区域不存在……';*/
    $mailer = new Mailer($sender, $from, $protocol, $host, $port, $username, $password);
    $mail_result = $mailer->send($email_test, $email_subject, $email_content, CHARSET, 1);
    if ($mail_result) {
        fk('ok', 'mail_send_succeed');
    } else {
        err('mail_send_failure', implode("\n", $mailer->errors));
    }
}