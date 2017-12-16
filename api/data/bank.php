<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/3
 * Time: 10:55
 */
//开户行列表
function w_bank_list()
{
    //BANK_DB->
    $model=new M();
    $banklist=$model->table('sgxt_bank_list')->select();
    fk('开户行列表',$banklist);
}
//开户省份
function w_bank_province()
{
    $bank=$_POST['bank'];//银行名称
    if(empty($bank))
    {
        err('请选择开户行！');
    }
    file_put_contents('bank.txt',$bank."\n",FILE_APPEND);
    $bankdb = new PDO('sqlite:'.'./bank.db');
    $sth=$bankdb->prepare("select province from xbft_bank_mng where bank = '$bank' group by province");
    $sth->execute();
    $res = $sth->fetchAll();
    unset($res[0]);
    foreach($res as $v){
        $arr[]=$v[0];
    }
    fk('开户省份',$arr);
}
//开户城市
function w_bank_city()
{
    $bank=$_POST['bank'];//银行名称
    if(empty($bank))
    {
        err('请选择开户行！');
    }
    $province=$_POST['province'];//开户省份
    if(empty($province))
    {
        err('请选择开户省份！');
    }
    $bankdb = new PDO('sqlite:'.'./bank.db');
    $sth=$bankdb->prepare("select area from xbft_bank_mng where province = '$province' and bank = '$bank' group by area");
    $sth->execute();
    $res = $sth->fetchAll();
    foreach($res as $v){
        $arr[]=$v[0];
    }
    fk('开户城市',$arr);
}
//开户支行
function w_bank_code_list()
{
    $bank=$_POST['bank'];//银行名称
    if(empty($bank))
    {
        err('请选择开户行！');
    }
    $province=$_POST['province'];//开户省份
    if(empty($province))
    {
        err('请选择开户省份！');
    }
    $area=$_POST['area'];//开户城市
    if(empty($area))
    {
        err('请选择开户城市！');
    }
    $bankdb = new PDO('sqlite:'.'./bank.db');
    $sth=$bankdb->prepare("select name,code from xbft_bank_mng where province = '$province' and bank = '$bank'  and area = '$area'");
    $sth->execute();
    $res = $sth->fetchAll();
    foreach($res as $re){
        $arr[]=array('name'=>$re['name'],'code'=>$re['code']);
    }
    fk('开户支行',$arr);
}