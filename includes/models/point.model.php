<?php 
class PointModel extends BaseModel{
    public $model='';
    public function sendPoint($mobel , $point ,$sendid ,$order, $oto = 'offline'){
        $model = & m();
        //开启事务
        //增加减少积分
        //验证积分是否够300自动增加受赠权
        //根据商家关系返佣金
        //根据用户关系返余额
        //为了兼容ecmall 这里把username 就是电话
        if(empty($point) || empty($mobel)) return;
        $getUser = $model->table('member') -> where(array('user_name' => $mobel))  ->find1();
        $sendUser = $model->table('member') -> where(array('user_id' => $sendid))  ->find1();
        $model -> setBegin(); //开启事务
        //利用try .. carch 的方法 来用事务处理
        try {
            $shops_point = round($point*conf('PAY_INFO/shops_point'));
            $system_point = round($point*conf('PAY_INFO/system_point'));
            $sendPass =  $model ->table('member') -> where(array('user_id' => $sendid)) -> setDec('pay_point' ,  $shops_point);
            $getPass = $model -> table('member') ->where(array('user_id' => $getUser['user_id'])) ->setInc('point' ,$point);
            $pointData = array(
                        'sendid'  =>  $sendid,
                        'sendname' => $sendUser['real_name'],
                        'getid'   => $getUser['user_id'],
                        'getname' => $getUser['real_name']?$getUser['real_name']:$mobel,
                        'point'   => $point,
                        'is_pass' => 1,
                        'createtime' => time(),
                        'oto'   =>  $oto,
                        'shops_point' => $shops_point,
                        'system_point' => $system_point,
                        'order_id' => $order['orderid'],

                    );
            $pointPass = $model -> table('sgxt_get_point') -> add($pointData);
            if(!$sendPass || !$getPass || !$pointPass){
                throw new MyException($sendid."积分发送失败".$mobel."分".$point);   
            }


            $this -> autoPointBean($getUser['user_id']);
                //处理各级返佣金的逻辑方法
             
            $this ->logicEarnings($sendUser , $point , $getUser);
            //修改订单状态
            
            if($oto == 'offline'){
                $this -> auto_complete_order($order);
            }
            $model->commit();
            return true;
        }
        catch (MyException $e) {
            $model->rollback();
            $e->addLog('sendPoint.txt'); 
            return false; 
        }
       /* if($sendPass & $getPass & $pointPass){
                $model->commit();
               
                //执行自动换券的方法
                $this -> autoPointBean($getUser['user_id']);
                //处理各级返佣金的逻辑方法
                $this ->logicEarnings($sendUser , $point , $getUser);
                return true;
            }else{
                $model->rollback();
                return false;
            }*/

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
                throw new MyException("用户".$userid."受赠权增加".$beanNum."失败");   
            }
        }else{
            throw new MyException("用户".$userid."积分减少".$delPoint."失败"); 
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
        //插入数据表
        $ins = $model -> table('sgxt_bean') -> add($addData);
        if(!$ins){
            throw new MyException("用户".$userid."受赠权增加".$beanNum."失败");   
        }
    }
    
    /**
     *    //增加收益的逻辑关系处理
     *
     *    @author    ruan
     *    @param     array $senduser      发送者信息
     *    @param     string $point         积分
     *    @param     array $getuser     接收者信息
     *    @return    void
     */
    public function   logicEarnings($senduser , $point ,$getuser){
        $logHandler = new CLogFileHandler(ROOT_PATH . '/logs/profit/' . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
        $logInfo = "[增加收益的逻辑关系处理--Start]\r\n发送用户：".$senduser['real_name'] . '('.$senduser['user_name'].'),接收用户：'.$getuser['real_name'].'('.$getuser['user_name'].')积分:'.$point."\r\n";
        $model = & m();
        $real_point = $point;
        if(empty($senduser) || empty($point) || empty($getuser)) {
            throw new MyException("发送者信息积分接收者信息为空");  
            die;
        }
        //处理上三级关系的
        //获取到改用户的上三级人
        $path = $getuser['path'];
        $point = round($point*conf('PAY_INFO/shops_point')); //计算出所付出的钱数
        $money = '';
        $logInfo .= "[接收用户的三级关系处理--Start]\r\n";
        if(!empty($path)){
            $pathinfo = $model->table('member')->where(" user_id in ( $path )") -> select();
            if(!empty($pathinfo)){
                foreach($pathinfo as $user){
                    $money = '';
                    $money = $point * conf("user_reward/".$user['type']."/recommend_level3");
                    $money = sprintf("%.2f",$money);
                    $logInfo .= "\t上级用户：" . $user['real_name'].'('.$user['user_name'].'),获取收益：'.$point .'*'. conf("user_reward/".$user['type']."/recommend_level3").'='.$money ."\r\n";
                    $this->addBalance($user ,$getuser , $money , '1' ,$real_point);
                }
            }
        }else{
            $logInfo .= "\t没有上级关系\r\n";
        }
        $logInfo .= "[接收用户的三级关系处理--End]\r\n";
        //商家的直推人收益
        $logInfo .= "[商家的直推人收益处理--Start]\r\n";
        if(!empty($senduser['pid'])){
            $money = '';
            $ztuser = $model -> table('member') -> where(array('user_id' => $senduser['pid'])) ->find1();
            $logInfo .= "\t直推人：".$ztuser['real_name'].'('.$ztuser['user_name'].")\r\n";
            //验证直推人权限 普通用户不享受该权限
            if($ztuser['type'] != 1) {
                $money = $point * conf("user_reward/".$ztuser['type']."/recommen_shops");
                $money = sprintf("%.2f",$money);
                $logInfo .= "\t获得收益为：".$point . '*' . conf("user_reward/".$ztuser['type']."/recommen_shops") . '='.$money."\r\n";
                $this -> addEarnings($ztuser , $senduser,$money , '4' , $real_point);
            }else{
                $logInfo .= "\t普通用户不享受该权限\r\n";
            }
        }else{
            $logInfo .= "\t没有直推人";
        }
        $logInfo .= "[商家的直推人收益处理--End]\r\n";

        //社区内的商家提成
        $logInfo .= "[社区内的商家提成处理--Start]\r\n";
        if(!empty($senduser['opid'])){
            $money = '';
            $opuser = $model -> table('member') -> where(array('user_id' => $senduser['opid'])) ->find1();
            if($opuser['type'] == 4) {
                $logInfo .= "\t社区代理：".$opuser['real_name']."(".$opuser['user_name'].")";
                $money = $point * conf("user_reward/".$opuser['type']."/area_shops");
                $money = sprintf("%.2f",$money);
                $logInfo .= "获得收益为：".$point . '*' . conf("user_reward/".$opuser['type']."/area_shops") . '='.$money."\r\n";
                $this -> addEarnings($opuser , $senduser,$money , '5' , $real_point);
            }else{
                $logInfo .= "\t不是社区代理\r\n";
            }
        }else{
            $logInfo .= "\t没有社区代理\r\n";
        }
        $logInfo .= "[社区内的商家提成处理--End]\r\n";
        //县级内所有商家提成
        $logInfo .= "[县级内所有商家提成处理--Start]\r\n";
        if(!empty($senduser['area'])){
            $areaagent = $model -> table('member') -> where(array('ahentarea' => $senduser['area'])) ->find1();
            $money = '';
            if(!empty($areaagent) && $areaagent['type'] == 5) {
                $money = $point * conf("user_reward/".$areaagent['type']."/area_shops");
                $money = sprintf("%.2f",$money);
                $logInfo .= "\t县级代理：".$areaagent['real_name'].'('.$areaagent['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$areaagent['type']."/area_shops") . '='.$money."\r\n";
                $this -> addEarnings($areaagent , $senduser,$money , '5' , $real_point);
                //检验县级上级是否为县级
                $logInfo .= "\t检测改县级代理的推荐人是否也为县级代理\r\n";
                if(!empty($areaagent['pid'])){
                    $sjuser = $model -> table('member') -> where(array('user_id' => $senduser['pid'])) ->find1();
                    if($sjuser['type'] == 5){
                        $money = '';
                        $money = $point * conf("user_reward/".$sjuser['type']."/recommen_ounty_shops");
                        $money = sprintf("%.2f",$money);
                        $logInfo .= "\t改县级代理的推荐人：".$sjuser['real_name'].'('.$sjuser['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$sjuser['type']."/recommen_ounty_shops") . '='.$money."\r\n";
                        $this -> addEarnings($sjuser , $senduser,$money , '6' , $real_point);
                    }else{
                        $logInfo .= "\t改县级代理的推荐人不是县级代理\r\n";
                    }
                }else{
                    $logInfo .= "\t改县级代理没有推荐人\r\n";
                }
            }else{
                $logInfo .= "\t所属代理不是县级代理\r\n";
            }
        }
        $logInfo .= "[县级内所有商家提成处理--End]\r\n";
 
        //查出该会员省市县及社区代理
        $logInfo .= "[获取收益会员的省、市、县、社区代理各级收益处理--Start]\r\n";
        $agent_province_id = $model -> table('member') -> where(array('ahentarea' => $getuser['province'])) ->getField('user_id');
        $agent_city_id = $model -> table('member') -> where(array('ahentarea' => $getuser['city'])) ->getField('user_id');
        $agent_area_id = $model -> table('member') -> where(array('ahentarea' => $getuser['area'])) ->getField('user_id');
        $agent_opid = $getuser['opid'];
        $idin = '';
        if(!empty($agent_province_id)){
            $idin .=  $agent_province_id . ',';
        }
        if(!empty($agent_city_id)){
            $idin .=  $agent_city_id . ',';
        }
        if(!empty($agent_area_id)){
            $idin .=  $agent_area_id . ',';
        }
        if(!empty($agent_opid)){
            $idin .=  $agent_opid;
        }
        $idin = trim($idin,',');
        //if(empty($idin)) return ;

        $agentuser = $model -> table('member') -> where(" user_id in ($idin)") -> select();
        //省代获得佣金
        foreach($agentuser as $user){
            $money = '';
            $agentuser = '';
            if($user['type'] > 3) {
                $money = $point * conf("user_reward/".$user['type']."/area_users");
                $money = sprintf("%.2f",$money);
                $type_cn = '';
                if($user['type'] == 4){
                    $type_cn = '社区代理';
                }elseif($user['type'] == 5){
                    $type_cn = '县级代理';
                }elseif($user['type'] == 6){
                    $type_cn = '市级代理';
                }elseif($user['type'] == 7){
                    $type_cn = '省级代理';
                }
                $logInfo .= "\t".$type_cn."：".$user['real_name'].'('.$user['user_name'].')获得收益为：'.$point . '*' . conf("user_reward/".$user['type']."/area_users") . '='.$money."\r\n";
                $this -> addEarnings($user , $getuser,$money , '7' , $real_point);
            }
            
        }
        $logInfo .= "[获取收益会员的省、市、县、社区代理各级收益处理--End]\r\n";
        $logInfo .= "[增加收益的逻辑关系处理--End]";
        log::DEBUG($logInfo);
        

    }
    
    /**
     *    //增加余额的方法
     *
     *    @author    ruan
     *    @param     array $getuser      发送者信息
     *    @param     array $formuser         积分
     *    @param     float $money     金额
     *    @param     array $type      类型
     *    @param     int $real_point    积分数量
     *    @return    void
     */
    public function addBalance($getuser , $formuser ,$money , $type ,$real_point){
         
        $money = (float)$money;
        if(empty($money)) return ;
        $model = & m();
        //增加用余额 属于冻结状态
        $pass = $model -> table('epay') -> where(array('user_id' => $getuser['user_id'])) ->setInc('money_dj' , sprintf("%.2f",$money*0.95));
        //增加幸福积分
        $model->table('member')->where(array('user_id' => $getuser['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
        $adddata = array(
            'user_id'  => $getuser['user_id'],
            'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
            'get_money' => sprintf("%.2f" ,$money*0.95),
            'real_point' => $real_point,
            'source_type' => $type,
            'from_username' => $formuser['real_name']?$formuser['real_name']:$formuser['user_name'],
            'from_userid'  => $formuser['user_id'],
            'createtime'  => time(),
             'times'       => date('Ym'),
             'area'   =>  $getuser['area'],
             'city'   =>  $getuser['city'],
             'province' =>  $getuser['province'],
             'opid'   =>  $getuser['opid'],
             'happiness' => sprintf("%.2f",$money*0.05),
            );
       
       $pass1 =  $model->table('sgxt_balance') -> add($adddata);
       if(!$pass || !$pass1){
         
            throw new MyException("用户".$getuser['user_id']."增加余额".$money."失败");   
       }
    }
    //增加收益的方法
    public function addEarnings($getuser , $formuser ,$money , $type ,$real_point){
        $money = (float)$money;
        if(empty($money)) return ;
        $model = & m();
        $pass = $model -> table('epay') -> where(array('user_id' => $getuser['user_id'])) ->setInc('freeze_earnings' , $money);
        $adddata = array(
            'user_id'  => $getuser['user_id'],
            'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
            'remain_money' => $money,
            'real_point' => $real_point,
            'source_type' => $type,
            'from_username' => $formuser['real_name']?$formuser['real_name']:$formuser['user_name'],
            'from_userid'  => $formuser['user_id'],
            'createtime'  => time(),
             'times'       => date('Ym'),
            'area'   =>  $getuser['area'],
             'city'   =>  $getuser['city'],
             'province' =>  $getuser['province'],
             'opid'   =>  $getuser['opid'],
            );

        $pass1 = $model->table('sgxt_profit') -> add($adddata);
        if(!$pass ||  !$pass1){
            throw new MyException("用户".$getuser['user_id']."增加收益".$money."失败");   
       }
    }
    //修改订单状态自动完成订单
    public function auto_complete_order($orderid){
        $model = & m();
        $order = $model ->table('order_offline')->where(array('order_sn' => $orderid['order_sn'])) ->save(array('status' => 40));
        if(!$order){
             throw new MyException("订单".$orderid['order_sn']."状态修改失败");  
        }
    }

}


 ?>