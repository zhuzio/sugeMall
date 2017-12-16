<?php
/**
 * @Author: anchen
 * @Date:   2016-06-15 16:50:23
 * @Last Modified by:   anchen
 * @Last Modified time: 2016-07-09 14:44:09
 */
header("Content-type:text/html;charset=utf-8");
class Move_userApp extends FrontendApp
{
    //迁移会员
    public function moveuser(){
        include ('userall.php');
        $usermode = & m('member');
        
        
        foreach($userall as $key=>$user){
            $data = array(
                'user_id'  => $user['id'],
                'user_code' => $user['userid'],
                'pid'       => $user['pid'],
                'type'      => $user['type'],
                'status'      => $user['status'],
                'user_name'      => $user['mobile'],
                'real_name'    => $user['truename'],
                'password'      => $user['password'],
                'phone_mob'      => $user['mobile'],
                'reg_time'      => $user['createtime'],
                'idcard'      => $user['idcard'],
                'path'      => $user['path'],
                'childrens'      => $user['childrens'],
                'balance'      => $user['shop_balance'],
                'city'      => $user['city'],
                'area'      => $user['area'],
                'opid'      => $user['opid'],
                'ahentarea'      => $user['ahentarea'],
                'recode'      => $user['recode'],
                'pay_point'      => $user['pay_point'],
                'brabchcode'      => $user['brabchcode'],
                'cardmoney'      => $user['cardmoney'],

                );
            $usermode ->add($data);
        }


    }
    public function userinfo(){
        $usermode = & m('member');
        $user = $usermode->limit(1)->find('user_id = 3');
        dump($user);
    }

    public function testToken(){
        $token = & m();
        $token->table('token')->setBegin();
        $data =array(
            'userid' => 1,
            'token'  => 'sssss',
            'createtime' =>time(),
            );
        $p1 = $token->table('token')->add($data);
        $data2 =array(
            'userid' => 2,
            'token'  => 'sssss',
            'createtime' =>time(),
            );
        $p2 = $token->table('token')->add($data2);
        if($p1 && $p2){
            $token->commit();
           
        }else{
            $token->rollBack(); 
        }

        
       
    }   

    public function pagetest(){
         $usermode = & m();
         $count = $usermode ->table('epaylog')->count();
         $info = $usermode ->table('epaylog') ->page($count , 10)->select();
         $button = $usermode->getButton(2);
        dump($button);
    }

    public function test(){
        echo date('Y-m-d H:i:s' , time());
        echo date('Y-m-d H:i:s' , gmtime());
    }

    public function test1(){
        $model = &m();
        $out_trade_no='P72969546525396';
            if($out_trade_no[0] == 'P'){
                $sgxt_order = $model -> table('sgxt_order')->where(array('orderid' => $out_trade_no))->find1();
                if(empty($sgxt_order)){
                    return ;
                }
                //订单未支付
                if($sgxt_order['status'] == 0){
                    //修改订单状态
                    $model->setBegin();
                    $update = array(
                        'paytype' => 'wx',
                        'status'  => 1,
                        'pay_createtime' => time(),
                        'pay_sn' => date('YmdHis').rand(100,999).rand(1000,9999),
                        ); 
                    
                    $pass1 = $model->table('sgxt_order')->where(array('orderid' => $out_trade_no)) ->save($update);
                    //更新用户积分字段
                    $pass2 = $model->table('member')->where(array('user_id' => $sgxt_order['userid'])) ->setInc('pay_point' , $sgxt_order['num']);
                    if($pass1 & $pass2){
                        $model->commit();
                        die('success');
                    }else{
                        $model->rollBack();
                    }
                }
            }
    }

    public function test2(){
        echo $this->getOpid(148);
    }
    private function getOpid($pid){
        $model = &m();
        $plist = $model ->table('member')-> where(array('user_id'=> $pid)) ->field('user_id,pid,opid,type') ->find1();

        if(empty($plist )) return ;
        if($plist['type'] != 4){
             return $this -> getOpid($plist['pid']);
        }else{
            return $plist['user_id'];
        }
    }

    public function testtpl(){
        $array = array(
            'name' => '张三' ,
            'point' => '50',
            'money' => '18.3',
            );
        $tpl = "你区域下会员{name}，消费了{point}，为你的收益增加{money}元。";
        echo replace_tpl($array , $tpl);
    }

    public function createSql(){
        //查询出所有会员
        //将type > 3的数据购物积分迁移到agent_money中
        //所有减掉 幸福积分
        ini_set("max_execution_time", "1000");
        $model = &m();
        $sql = "SELECT * from ecm_member left join ecm_epay on ecm_member.user_id = ecm_epay.user_id";
        $userall =  $model->query($sql);
        $sql = '';
        foreach($userall as $user){
            if($user['type'] > 3 & $user['type']<9){
               $sql .= "UPDATE ecm_member set `point` = `point` - $user[point] , agent_money = agent_money +  $user[point] where user_id = $user[user_id];\r\n";
            }
            if($user['money'] > 0){
                $sql .="UPDATE ecm_epay set `money` = `money` - $user[happiness] where user_id = $user[user_id];\r\n";
            }
        }
        file_put_contents('new_member.sql', $sql);
    }

 //自动兑换赠送器啊UN的方法
    public function autoPointBean($userid){
        $model = & m();
        $userinfo = $model->table('member') -> where("user_id = $userid")->find1();

        $payinfo = conf('PAY_INFO');
        
        if($userinfo['point'] < $payinfo['bean']) return;

        $beanNum = floor($userinfo['point']/$payinfo['bean']);

        $delPoint = $beanNum  * $payinfo['bean'];

        $pass= $model -> table('member') -> where(array('user_id' => $userid)) -> setDec('point',$delPoint);
        //更新改用户的受赠权
        if($pass){
            $beanPass =$model ->table('member') -> where(array('user_id' => $userid)) -> setInc('point_peac' ,$beanNum);
            if($beanPass){
                $this -> addBean($userid , $beanNum);
            }else{
                //throw new MyException("用户".$userid."受赠权增加".$beanNum."失败");   
            }
        }else{
           // throw new MyException("用户".$userid."积分减少".$delPoint."失败"); 
        }
       
    }
    
    /**
     *    增加受赠权
     *
     *    @author    ruan
     *    @param     int $userid      增加受赠权人id
     *    @param     int $beanNum      增加数量
     *    @return    void
     */
    public function addBean($userid, $beanNum){
        $model = & m();
        if(empty($userid) || empty($beanNum))  return ;

        for($i = 0 ;$i< $beanNum ; $i++){
            $code = buildCountRand();
            $addData[$i] = array(
                'bean_number' => $code[0],
                'user_id'     => $userid,
                'bean_price'  => conf('PAY_INFO/bean'),
                'status'      => 1,
                'createtime'  => time(),

                );
        }
        var_dump($addData);
        //插入数据表
        $ins = $model -> table('sgxt_bean') -> add($addData);
        if(!$ins){
            //throw new MyException("用户".$userid."受赠权增加".$beanNum."失败");   
        }
    }
    public function auto_quan(){
        //查询出数据库所有积分大于300
        $model = & m();
        $allList = $model -> table('member')->where('point >= 300 ') ->select();
        $allOutNum = 0;
        foreach($allList as $list){

            $this->autoPointBean($list['user_id']);
              
        }

       
    }


    //幸福积分处理
    public function  up_happiness(){
        //先查balance表拿出所有数据
        //计算出幸福积分
        //处理balanct表中数据
        //关联epay表
        //关联member表
        $model = & m();
        $balance = $model->table('sgxt_balance')->select();
        $sql = '';
        foreach($balance as $key=> $value ){
            $happiness =  sprintf("%.2f",$value['get_money'] * 0.05);
            $get_money =  $value['get_money']-$happiness;
            $sql .= "UPDATE ecm_sgxt_balance SET get_money = $get_money , happiness=$happiness WHERE id=$value[id] ;\r\n";
            if($value['source_type'] == 1){
                $field = 'money_dj';
            }else{
                $field = 'money';
            }
            
            $sql .= "UPDATE ecm_epay SET $field = $field - $happiness WHERE user_id = $value[user_id] ;\r\n";
            $sql .= "UPDATE ecm_member SET happiness = happiness + $happiness WHERE user_id = $value[user_id] ;\r\n";
        }

        file_put_contents('happiness.sql' , $sql);
        
    }
    public function testshow(){
       $this->show_success('积分发送成功','index.php');
    }

}

 