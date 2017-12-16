<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/28
 * Time: 15:29
 */
//查询热搜
function search()
{
    $type=$_POST['type'];
    $model=new M();
    $list=$model->table('hotsearch')->where(array('type'=>$type))->order('count desc,addtime desc')->limit('0,4')->select();
    fk('热搜',$list);
}