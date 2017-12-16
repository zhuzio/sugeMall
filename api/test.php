<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 13:50
 */
$https=$_SERVER['HTTPS'];
$protocol=$_SERVER['SERVER_PROTOCOL'];
$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
$host = $_SERVER['HTTP_HOST'];
$port = ':' . $_SERVER['SERVER_PORT'];
$host = $_SERVER['SERVER_NAME'] . $port;
$info='https='.$https.';SERVER_PROTOCOL='.$protocol.';HTTP_X_FORWARDED_HOST='.$_SERVER['HTTP_X_FORWARDED_HOST'].';HTTP_HOST='.$_SERVER['HTTP_HOST'].';SERVER_PORT='.$_SERVER['SERVER_PORT'].';SERVER_NAME='.$_SERVER['SERVER_NAME'];
echo $info;
/*$fp=fopen('test.txt','w');
fwrite($fp,$info);
fclose($fp);*/
phpinfo();