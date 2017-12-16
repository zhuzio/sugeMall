<?php
/* 会员 member */
error_reporting(E_ALL);
ini_set('display_errors', '1');
//将出错信息输出到一个文本文件
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
ini_set('max_execution_time',0);
class money_autoApp extends FrontendApp{
    public function autoMoney(){
        //查出上次最大执时间
        //查出当前执行时间
        //去取整数 求出前一天的时间戳
        //求出前两天的时间戳
        $logHandler = new CLogFileHandler(ROOT_PATH . '/logs/eachday/' . date('Y-m-d') . '.log');
        $log = Log::Init($logHandler, 15);
        $loginfo = "===================自动执".date('Y-m-d H:i:s')."==========================\r\n";
        echo "===================自动执".date('Y-m-d H:i:s')."==========================<br/>";

        $Model = & m();
        $maxtime =$Model-> table('sgxt_eachday') -> where('newstime > 0') -> order('newstime desc')  -> getField('newstime');

        if(empty($maxtime)) $maxtime = time()-86400;
        $nowtime = time();
        list($maxyear , $maxmonth , $maxday) =explode('-',  date('Y-m-d',$maxtime));

        $maxtime = mktime(0,0,0,$maxmonth,$maxday,$maxyear );

        list($nowyear , $nowmonth , $nowday) = explode('-', date('Y-m-d',$nowtime));

        $nowtime = mktime(0,0,0,$nowmonth,$nowday,$nowyear );
        $loginfo .= "\t判断当前时间是否为第二天\r\n";

        if($nowtime >= (86400 +  $maxtime)){
            $data = array(
                'newstime' => time(),
                'info' => '执行自动返钱方法',
            );
            $loginfo .= "\t写入数据库执行时间".time()."\r\n";
            $Model -> table('sgxt_eachday') -> add($data);

            $beforeOneDay  =  $maxtime + 86400;   //一天前
            //记录下总支出
            $allmoney = 0;
            //记录下总排除的受赠权
            $allOutNum = 0;
            //设置受赠权单价
            $beanPrice = 300;

            $price = 0.6;
            //查询出所有有受赠权的人
            $userBeanNum = $Model ->table('member') ->where('point_peac > 0 and status=1') ->select();
            $loginfo .= "\t查询出来所有拥有受赠权的用户(".count($userBeanNum)."位用户)\r\n";
            log::DEBUG($loginfo);
            foreach($userBeanNum as $key=>$user){                
                $userinfo = "";
                $userinfo .= "\t第{$key}位用户[".$user['user_id'].",".$user['user_name']."]定返----------开始----------\r\n";
                $money = 0;
                //计算出该用户要分多少钱
                $moneyOne = $user['point_peac']*$price;
                //计算出累计金额
                $allmoney += $moneyOne;
                $money = $user['beanmoney'] + $moneyOne;
                $userinfo .= "\t\t累计金额为:".$money."\r\n";
                if($money >= $beanPrice){
                    $num = floor($money/$beanPrice);
                    $allOutNum +=  $num;
                    $money = $money-($num*$beanPrice);
                    $userinfo .= "\t\t累计金额超过".$beanPrice."，进行减权操作\r\n";
                    $this -> popBean($user['user_id'] , $num);
                }
                $condition = array();
                $condition['beanmoney'] =sprintf("%.2f", $money );
                //$condition['balance'] = sprintf("%.2f", $condition['balance']+$moneyOne);
                $userinfo .= "\t\t更新用户beanmoney(".$condition['beanmoney'].")字段\r\n";
                $pass = $Model -> table('member') -> where("user_id = $user[user_id]") ->data($condition) ->save();

                //记录卡券收入日志
                $money = sprintf("%.2f",$price* $user['point_peac']);
                $ins = array(
                    'userid'    =>   $user['user_id'],
                    'username'  =>   $user['real_name'],
                    'beanprice' =>   $price ,
                    'num'       =>   $user['point_peac'],
                    'money'     =>   $money,
                    'createtime' => time()
                );
                $insdata[] = $ins;
                $userinfo .= "\t\t更新epay表，增加money字段值(".$money." * 0.95 = ".sprintf("%.2f",$money*0.95).")\r\n";
                $Model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('money' , sprintf("%.2f",$money*0.95));
                //增加幸福积分
                $userinfo .= "\t\t更新member表，增加幸福积分happiness字段值(".$money." * 0.05 = ".sprintf("%.2f",$money*0.05).")\r\n";
                $Model->table('member')->where(array('user_id' => $user['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
                $userinfo .= "\t\t写入epaylog记录表，\r\n";
                $this-> addEapy($user , $money , 1);
                $userinfo .= "\t\t写入balance记录表，\r\n";
                $this -> addBalance($user , $money , 2);
                $userinfo .= "\t第{$key}位用户[".$user['user_id'].",".$user['user_name']."]定返----------结束----------\r\n";
                log::DEBUG($userinfo);
            }            
            //代理费定反            

            $this->agentMoneyReturn();

            $loginfo = "\t写入earnings收益表\r\n";
            $Model ->table('sgxt_earnings') -> add($insdata);
            $loginfo .= "\t写入定时执行统计表[用户总数：".count($userBeanNum).",定返总金额：".$allmoney.",抵消总定返权数：".$allOutNum."]\r\n";
            $dutoData = array(
                'usernum'     => count($userBeanNum),
                'beanprice'   => $price,
                'allmoney'    => $allmoney,
                'outbean'         => $allOutNum,
                'createtime'  => time()
            );
            $Model ->table('sgxt_auto') -> add($dutoData);
            $loginfo .= "\t代理费定返----------结束----------\r\n";

            $loginfo .= "\t执行订单自动完成----------开始--------\r\n";
            $this->order_auto_end();
            $loginfo .= "\t执行订单自动完成----------结束--------\r\n";

            $loginfo .= "\t---报表统计开始----------\r\n";
            $this->reportStatistics();
            $loginfo .= "\t---报表统计结束----------\r\n";

            /*$loginfo .= "\t---收益转化开始----------\r\n";
            $this->earningsUnfreeze();
            $loginfo .= "\t---收益统计结束----------\r\n";

            $loginfo .= "\t---奖励转化开始----------\r\n";
            $this->balanceUnfreeze();
            $loginfo .= "\t---奖励转化开始----------\r\n";*/

            log::DEBUG($loginfo);
        }


    }
    //排除一张受赠权的方法
    public function popBean($userid , $num){
        if(empty($userid) || empty($num)) return ;
        $Model = & m();
        $userinfo = $Model ->table('member')->where("user_id = $userid") ->find1();
        $beannum = $userinfo['point_peac'] - $num;
        //更新用积分受赠权
        $save['point_peac'] = $beannum;
        $peac = $Model ->table('member')-> where("user_id = $userid") ->save($save);
        //$peac = $Model->table('member')->where("user_id=".$userid)->setDec('point_peac',$num);
        //echo $Model ->getLastSql();

        if($peac){
            $beanid = $Model ->table('sgxt_bean') -> where("user_id = $userid and status = 1") ->select();
            if(isset($beanid)){
                $bids = array();
                foreach($beanid as $key=>$val){
                    if($key < $num){
                        $bids[] = $val['id'];
                    }else{
                        break;
                    }
                }
                $Model ->table('sgxt_bean')->where(" id in (".implode(',',$bids).")") ->save(array('status' => 2));
            }

        }
    }
    //代理费定反
    public function agentMoneyReturn(){
        //拿出所有销售经理以上的用户
        $loginfo = "\t代理费定返----------开始----------\r\n";
        $model = & m();
        $userall = $model->table('member')->where("type > 2 and status=1 and  agent_money>0  ")->select();
        $loginfo .= "\t\t获取所有销售经理以上用户(".count($userall)."位用户)\r\n";
        log::DEBUG($loginfo);
        $usertype = conf('user_type');
        foreach($userall as $key=>$user){
            $userinfo = '';
            $userinfo .= "\t\t\t[".$usertype[$user['type']]."]".$user['real_name']."(".$user['user_name'].")代理费：".$user['agent_money'].";定返----------开始--------\r\n";
            if($user['agent_money'] <= 0){
                $userinfo .= "\t\t\t\t代理费为：".$user['agent_money']."，跳过\r\n";
                $userinfo .= "\t\t\t[".$usertype[$user['type']]."]".$user['real_name']."(".$user['user_name'].")定返----------结束--------\r\n";
                log::DEBUG($userinfo);
                continue;
            }
            if($user['type'] == 4){
                $money = conf('community_agent');
                $money = min($money , $user['agent_money']);
            }else{
                $money = conf('user_reward/'.$user['type'].'/return_balance') * $user['agent_money'];
            }
            $money = sprintf("%.2f", $money);
            $userinfo .= "\t\t\t\t定返金额为：".$money."\r\n";
            //增加余额记录
            $userinfo .= "\t\t\t\t增加余额记录balance表：".$money."\r\n";
            $this -> addBalance($user , $money , 3);
            $this-> addEapy($user , $money , 2);
            //将余额增加到财富表
            $userinfo .= "\t\t\t\t增加余额到epay表：".$money." * 0.95 = ".sprintf("%.2f", $money*0.95)."\r\n";
            $model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('money' , sprintf("%.2f", $money*0.95));
            //增加幸福积分
            $userinfo .= "\t\t\t\t增加幸福积分到member表：".$money." * 0.05 = ".sprintf("%.2f", $money*0.05)."\r\n";
            $model->table('member')->where(array('user_id' => $user['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
            //将返的钱从总余额中减掉
            $userinfo .= "\t\t\t\t从总余额中扣除定返金额：".$money."\r\n";
            $model->table('member')->where(array('user_id' => $user['user_id']))->setDec('agent_money' , $money);
            $userinfo .= "\t\t\t[".$usertype[$user['type']]."]".$user['real_name']."(".$user['user_name'].")定返----------结束--------\r\n";
            log::DEBUG($userinfo);
        }
        unset($user);
        $loginfo = "\t代理费定返----------结束----------\r\n";
        log::DEBUG($loginfo);
    }

    public function userReturn(){
        $Model = & m();
        $beanPrice = 300;
        $price = 0.6;
        $createtime = $_GET['createtime'] ? $_GET['createtime'] : time();
        $userBeanNum = $Model ->table('member') ->where('point_peac > 0') ->select();
        foreach($userBeanNum as $key=>$user){                            
            //计算出该用户要分多少钱
            $moneyOne = $user['point_peac']*$price;
            //计算出累计金额
            $allmoney += $moneyOne;
            $money = $user['beanmoney'] + $moneyOne;
            
            if($money >= $beanPrice){
                $num = floor($money/$beanPrice);
                $allOutNum +=  $num;
                $money = $money-($num*$beanPrice);
                //$this -> popBean($user['user_id'] , $num);
                
                $userinfo = $Model ->table('member')->where("user_id = ".$user['user_id']) ->find1();
                $beannum = $userinfo['point_peac'] - $num;

                //更新用积分受赠权
                $save['point_peac'] = $beannum;
                //$peac = $Model ->table('member')-> where("user_id = $userid") ->save($save);
                $peac = $Model->table('member')->where("user_id=".$user['user_id'])->setDec('point_peac',$num);
                //echo $Model ->getLastSql();

                if($peac){
                    $beanid = $Model ->table('sgxt_bean') -> where("user_id = ".$user['user_id']." and status = 1") ->select();
                    if(isset($beanid)){
                        $bids = array();
                        foreach($beanid as $key=>$val){
                            if($key < $num){
                                $bids[] = $val['id'];
                            }else{
                                break;
                            }
                        }
                        $Model ->table('sgxt_bean')->where(" id in (".implode(',',$bids).")") ->save(array('status' => 2));
                    }

                }
            }
            $condition = array();
            $condition['beanmoney'] =sprintf("%.2f", $money );
            $condition['balance'] = sprintf("%.2f", $condition['balance']+$moneyOne);
            $pass = $Model -> table('member') -> where("user_id = $user[user_id]") ->data($condition) ->save();

            //记录卡券收入日志
            $money = sprintf("%.2f",$price* $user['point_peac']);
            $ins = array(
                'userid'    =>   $user['user_id'],
                'username'  =>   $user['real_name'],
                'beanprice' =>   $price ,
                'num'       =>   $user['point_peac'],
                'money'     =>   $money,
                'createtime' =>  $createtime
            );
            $insdata[] = $ins;
            $Model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('money' , sprintf("%.2f",$money*0.95));
            //增加幸福积分
            $Model->table('member')->where(array('user_id' => $user['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
            //$this-> addEapy($user , $money , 1);
            $model = & m();
            $log_text_to ='每日定反为你的余额增加'.$money.'元';
            $type = EPAY_AGENT_RETURN;
            $add_epaylog_to = array(
                'user_id' => $user['user_id'],
                'user_name' => $user['real_name'],
                'to_id' => '2',
                'to_name' => '苏格财务',
                'order_sn ' => '',
                'add_time' => $createtime - 3600*8,
                'type' => $type, //转入
                'money_flow' => 'income',
                'money' => $money,
                'complete' => 1,
                'log_text' => $log_text_to,
                'states' => 40,
            );
            $model->table('epaylog')->add($add_epaylog_to);





            //$this -> addBalance($user , $money , 2);
            //增加用余额
            $adddata = array(
                'user_id'  => $user['user_id'],
                'user_name' => $user['real_name']?$user['real_name']:$user['user_name'],
                'get_money' => sprintf("%.2f", $money * 0.95),
                'real_point' => 0,
                'source_type' => 2,
                'from_username' => '苏格财务',
                'from_userid'  => '2',
                'createtime'  => $createtime,
                'times'       => date('Ym'),
                'is_clearing' => 1,
                'area'   =>  $user['area'],
                'city'   =>  $user['city'],
                'province' =>  $user['province'],
                'opid'   =>  $user['opid'],
                'happiness' => sprintf("%.2f",$money * 0.05),
            );
            $pass =  $model->table('sgxt_balance') -> add($adddata);
        } 
    }


    public function agentReturn(){
        $craetetime = $_GET['createtime'] ? $_GET['createtime'] : time();
        $model = & m();
        $userall = $model->table('member')->where("type > 2 and status=1 and  agent_money>0  ")->select();
        $usertype = conf('user_type');
        foreach($userall as $key=>$user){
            if($user['agent_money'] <= 0){
                continue;
            }
            if($user['type'] == 4){
                $money = conf('community_agent');
                $money = min($money , $user['agent_money']);
            }else{
                $money = conf('user_reward/'.$user['type'].'/return_balance') * $user['agent_money'];
            }
            $money = sprintf("%.2f", $money);
            //增加余额记录
            //$this -> addBalance($user , $money , 3);
            //增加用余额            
            $adddata = array(
                'user_id'  => $user['user_id'],
                'user_name' => $user['real_name']?$user['real_name']:$user['user_name'],
                'get_money' => sprintf("%.2f", $money * 0.95),
                'real_point' => 0,
                'source_type' => $type,
                'from_username' => '苏格财务',
                'from_userid'  => '2',
                'createtime'  => $craetetime,
                'times'       => date('Ym'),
                'is_clearing' => 1,
                'area'   =>  $user['area'],
                'city'   =>  $user['city'],
                'province' =>  $user['province'],
                'opid'   =>  $user['opid'],
                'happiness' => sprintf("%.2f",$money * 0.05),
            );

            $pass =  $model->table('sgxt_balance') -> add($adddata);


            //$this-> addEapy($user , $money , 2);
            $log_text_to ='代理费每日定反为你的余额增加'.$money.'元';
            $type = EPAY_BEAN_RETURN;
            $add_epaylog_to = array(
                'user_id' => $user['user_id'],
                'user_name' => $user['real_name'],
                'to_id' => '2',
                'to_name' => '苏格财务',
                'order_sn ' => '',
                'add_time' => $craetetime - 3600*8,
                'type' => 3, //转入
                'money_flow' => 'income',
                'money' => $money,
                'complete' => 1,
                'log_text' => $log_text_to,
                'states' => 40,
            );

            $model->table('epaylog')->add($add_epaylog_to);



            //将余额增加到财富表
            $model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('money' , sprintf("%.2f", $money*0.95));
            //增加幸福积分
            $model->table('member')->where(array('user_id' => $user['user_id']))->setInc('happiness' , sprintf("%.2f",$money*0.05));
            //将返的钱从总余额中减掉
            $model->table('member')->where(array('user_id' => $user['user_id']))->setDec('agent_money' , $money);
        }
    }  

    public function kouchu(){
        $model = &m();
        //$list = $model->table('epaylog')->where('id in (1887,1888,1889,1890,1891,1892,1893,1894,1895,1896,1897,1898,1899,1900,1901)')->select();
        $blist = $model->table('sgxt_balance')->where('id in (2202,2203,2204,2205,2206,2207,2208,2209,2210,2211,2212,2213,2214,2215,2216)')->select();
        foreach($blist as $key=>$val){
            $sql = 'update ecm_epay set money=money-'.$val['get_money'].' where user_id='.$val['user_id'].";<br/>";
            print_r($sql);
            $sql = 'update ecm_member set happiness=happiness-'.$val['happiness'].' where user_id='.$val['user_id'].";<br/>";
            print_r($sql);
        }
    }




    /**
     *    //增加余额的方法
     *
     *    @author    ruan
     *    @param     array $getuser
     *    @param     float $money     金额
     *    @param     array $type      类型
     *    @return    void
     */
    public function addBalance($getuser  ,$money , $type){
        $model = & m();
        //增加用余额

        $adddata = array(
            'user_id'  => $getuser['user_id'],
            'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
            'get_money' => sprintf("%.2f", $money * 0.95),
            'real_point' => 0,
            'source_type' => $type,
            'from_username' => '苏格财务',
            'from_userid'  => '2',
            'createtime'  => time(),
            'times'       => date('Ym'),
            'is_clearing' => 1,
            'area'   =>  $getuser['area'],
            'city'   =>  $getuser['city'],
            'province' =>  $getuser['province'],
            'opid'   =>  $getuser['opid'],
            'happiness' => sprintf("%.2f",$money * 0.05),
        );
        $pass =  $model->table('sgxt_balance') -> add($adddata);

    }

    public function addEapy($user , $moeny , $logto){
        $model = & m();
        if($logto == 1){
            $log_text_to ='每日定反为你的余额增加'.$moeny.'元';
            $type = EPAY_AGENT_RETURN;
        }else{
            $log_text_to ='代理费每日定反为你的余额增加'.$moeny.'元';
            $type = EPAY_BEAN_RETURN;
        }
        $add_epaylog_to = array(
            'user_id' => $user['user_id'],
            'user_name' => $user['real_name'],
            'to_id' => '2',
            'to_name' => '苏格财务',
            'order_sn ' => '',
            'add_time' => gmtime(),
            'type' => $type, //转入
            'money_flow' => 'income',
            'money' => $moeny,
            'complete' => 1,
            'log_text' => $log_text_to,
            'states' => 40,
        );

        $model->table('epaylog')->add($add_epaylog_to);
    }
    //订单自动完成的方法
    public function order_auto_end(){
        //查询未结束的订单
        $model = & m();
        $nowtime = time();
        $orderlist = $model ->table('order') -> where('auto_finished_time>0 and is_end=0 and status!=0') -> select();
        if(empty($orderlist)){
            return ;
        }
        foreach($orderlist as $list){
            if($list['auto_finished_time'] <= $nowtime && $list['status']==40){
                //$this -> update_order($list);
                $model ->table('order') ->where(array('order_id' => $list['order_id'])) ->save(array('is_end' => 1));
            }
            if($list['auto_finished_time'] <= $nowtime && $list['status']==30){
                $model ->table('order') ->where(array('order_id' => $list['order_id'])) ->save(array('status'=>40,'is_end' => 1));
            }
        }
    }
    private function update_order($order){
        $model = & m();
        //$point_model = & m('point.model');
        $pass =  $model ->table('order') ->where(array('order_id' => $order['order_id'])) ->save(array('is_end' => 1));
        //$point_model ->sendPoint($order['buyer_name'] , $order['point'] , $order['seller_id'] , $order['order_sn'] , 'online');
    }
    //报表统计
    public function reportStatistics()
    {
        echo '开始时间：'.date('Y-m-d H:i:s')."\n";
        $m = & m();
        $day=isset($_REQUEST['time']) ? $_REQUEST['time'] :date('Y-m-d');
        //判断是否是每月第一天，如果是，则统计上月数据
        if(intval(date('d',strtotime($day)))==1)
        {
            $time=date('Y-m-01',strtotime($day.'-1 month'));
        }else
        {
            $time=date('Y-m-01',strtotime($day));
        }
        //统计当月
        $starttime=strtotime($time);
        //$starttime=strtotime(date('Y-m-01', strtotime('-1 month')));
        //$endtime=strtotime(date('Y-m-01',strtotime($time.'+1 month')));
        $endtime=strtotime($day);
        //删除数据
        $sql='delete from ecm_sgxt_report where times>='.date('Ym',$starttime);
        $m->query($sql);
        $this->searchdata($m,$starttime,$endtime);
        echo 'ok'."\n";
        echo '结束时间：'.date('Y-m-d H:i:s');
    }
    public function searchdata($m,$starttime,$endtime)
    {
        //县代区域
        $query=$m->table('member')->where(array('type'=>5))->select();
        foreach($query as $key=>$user)
        {
            //区域
            $area='';
            $condition="id in ({$user['province']},{$user['city']},{$user['area']})";
            $query=$m->table('sgxt_area')->field('name')->where($condition)->select();
            foreach($query as $k=>$v)
            {
                $area.=$v['name'];
            }
            $data=array();
            $data['times']=date('Ym',$starttime);
            $data['area']=$area;
            $data['user_id']=$user['user_id'];
            $data['ahentarea']=$user['ahentarea'];
            //统计当月
            //县代商家总赠送积分(查询县代下所有商家，然后查询所有商家赠送的积分)
            $useridstring='';
            $idlist=$m->table('member')->field('user_id')->where('area='.$user['ahentarea'].' and type=2 and last_login>='.$starttime.' and last_login<'.$endtime)->select();
            if(empty($idlist))$idlist=array();
            foreach($idlist as $key =>$value)
            {
                $useridstring.=$value['user_id'].',';
            }
            $useridstring=substr($useridstring , 0 , strlen($useridstring)-1);

            if(!empty($useridstring))
            {
                //总赠送积分
                $sql="select sum(shops_point) as totalshopspoint from ecm_sgxt_get_point where sendid in ({$useridstring}) and createtime>={$starttime} and createtime<{$endtime}";
                $totalshopspoint =$m->query($sql);
                if(!empty($totalshopspoint[0]['totalshopspoint']))
                {
                    $data['totalgivepoint']=$totalshopspoint[0]['totalshopspoint'];
                }
                else
                {
                    $data['totalgivepoint']=0;
                }

                //总货款积分(货款提现)
                $sql="select sum(money) as totalmoney from ecm_sgxt_deposit where ispay=1 and type=1 and operatortime>={$starttime} and pay_time<{$endtime} and userid in ({$useridstring})";
                $totalpoint =$m->query($sql);
                if(!empty($totalpoint[0]['totalmoney']))
                {
                    $data['totalpaymentpoint']=$totalpoint[0]['totalmoney'];
                }
                else
                {
                    $data['totalpaymentpoint']=0;
                }


                //活跃商家
                $sql="select count(DISTINCT sendid) as activeshopscount from ecm_sgxt_get_point where sendid in ({$useridstring}) and createtime>={$starttime} and createtime<{$endtime}";
                $activeshops =$m->query($sql);
                if(!empty($activeshops[0]['activeshopscount']))
                {
                    $data['activeshopscount']=$activeshops[0]['activeshopscount'];
                }
                else
                {
                    $data['activeshopscount']=0;
                }

            }
            else
            {
                $data['totalgivepoint']=0;
                $data['totalpaymentpoint']=0;
                $data['activeshopscount']=0;
            }


            //总商家数
            $data['totalshopscount']=count($idlist);

            //总会员数
            $userscount=$m->table('member')->where('area='.$user['ahentarea'].' and status=1 and last_login>='.$starttime.' and last_login<'.$endtime)->count('distinct user_id');
            $data['usercount']=$userscount;
            //总活跃会员
            $sql ='select count(distinct ecm_sgxt_profit.from_userid) as activeusercount from ecm_sgxt_profit left join ecm_member on ecm_sgxt_profit.from_userid = ecm_member.user_id where ecm_member.area='.$user['ahentarea'].' and ecm_member.status=1 and ecm_sgxt_profit.source_type = 7 and createtime>='.$starttime.' and createtime<'.$endtime;
            $activeusercount =$m->query($sql);
            if(!empty($activeusercount[0]['activeusercount']))
            {
                $data['activeusercount']=$activeusercount[0]['activeusercount'];
            }else
            {
                $data['activeusercount']=0;
            }

            //总辖区收益
            $sql='select sum(remain_money) as total_earning from ecm_sgxt_profit where user_id='.$user['user_id'].' and source_type in(5,7) and createtime>='.$starttime.' and createtime<'.$endtime;
            $total_earning =$m->query($sql);
            if(!empty($total_earning[0]['total_earning']))
            {
                $data['total_earning']=$total_earning[0]['total_earning'];
            }else
            {
                $data['total_earning']=0;
            }
            //添加到记录表
            if(!empty($data))
            {
                $m->table('sgxt_report')->add($data);
            }
        }
    }
    //购物积分统计
    public function bill_statistics()
    {
        echo '开始时间：'.date('Y-m-d H:i:s')."\n";
        $type =isset($_REQUEST['type']) ? $_REQUEST['type'] :'1';
        $day=isset($_REQUEST['time']) ? $_REQUEST['time'] :date('Y-m-d 01:30:00');
        $endtime=strtotime($day);
        if($type=='all')
        {
            $startdate=date('Y-m-d 01:30:00',strtotime($day.'-300 day'));
        }
        else{
            $startdate=date('Y-m-d 01:30:00',strtotime($day.'-1 day'));
        }
        $starttime=strtotime($startdate);

        $m =& m();
        if($type=='all')
        {
            $sql='delete from ecm_bill_statistics;';
            $m->query($sql);
            $sql='alter table ecm_bill_statistics AUTO_INCREMENT=1;';
            $m->query($sql);
        }
        else
        {
            $sql='delete from ecm_bill_statistics where createtime>='.$startdate.' and createtime<'.$day;
            $m->query($sql);
        }
        $store_name_list=array();
        $storelist=$m->table('store')->field('store_id,store_name')->select();
        foreach($storelist as $keylist=>$vallist)
        {
            $store_name_list[$vallist['store_id']]=$vallist['store_name'];
        }
        //查询所有用户的id和手机号码
        $user_name_list=array();
        $real_name_list=array();
        $userslist_name=$m->table('member')->field('user_id,user_name,real_name')->select();
        foreach($userslist_name as $keylist=>$vallist)
        {
            $user_name_list[$vallist['user_id']]=$vallist['user_name'];
            $real_name_list[$vallist['user_id']]=$vallist['real_name'];
        }
        //统计昨天一天的账单
        $tabe = DB_PREFIX."sgxt_balance";
        $tabf = DB_PREFIX."sgxt_infotpl";
        //查询昨天登录用户信息
        if($type=='all')
        {
            $userslist=$m->table('member')->where('type>1')->select();
        }
        else
        {
            $userslist=$m->table('member')->where('type>1 and last_login>='.$starttime.' and last_login<'.$endtime)->select();
        }

        foreach($userslist as $userkey=>$user) {
            $shoppingdata=array();
            //统计购物账单明细
            //会员奖励

            if($user['type']>2){
                //会员奖励   购物积分转化 市场补贴
                $sql = "select $tabe.from_username,$tabe.from_userid,$tabe.get_money,$tabe.real_point,$tabe.createtime,$tabe.source_type,$tabe.order_sn,$tabe.order_type,$tabf.title from $tabe join $tabf on $tabe.source_type = $tabf.id where $tabe.source_type in (1,2,3) and $tabe.is_clearing=1 and user_id= ".$user['user_id']." and $tabe.createtime>= ".$starttime." and $tabe.createtime<".$endtime." order by $tabe.id desc ";
            }else{
                $sql = "select $tabe.from_username,$tabe.from_userid,$tabe.get_money,$tabe.real_point,$tabe.createtime,$tabe.source_type,$tabe.order_sn,$tabe.order_type,$tabf.title from $tabe join $tabf on $tabe.source_type = $tabf.id where  $tabe.source_type in (1,2) and $tabe.is_clearing=1 and user_id= ".$user['user_id']." and $tabe.createtime>= ".$starttime." and $tabe.createtime<".$endtime." order by $tabe.id desc ";
            }
            $balance = $m->query($sql);
            //print($sql."\n");
            if(empty($balance))$balance=array();
            foreach($balance as $key=>$val){
                $result=array();
                $result['user_name']=$user_name_list[$val['from_userid']];
                $result['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
                $result['point'] = $val['real_point'];
                $result['get_money'] = $val['get_money'];
                $result['name'] =$val['title'];
                $result['order_sn'] =$val['order_sn'];
                $result['oto'] =$val['order_type'];
                $result['user_id']=$user['user_id'];
                $result['type']=$val['source_type'];
                $result['from_userid']=$val['from_userid'];
                $result['char']='+';
                $shoppingdata[]=$result;
            }
            //购物积分 消费 -
            $sql ="select order_sn,money,to_id,add_time,payment_id from ecm_paymentlog where user_id=".$user['user_id']." and add_time>= ".$starttime." and add_time<".$endtime." and payment_id=3 order by add_time desc ";
            $res =$m->query($sql);
            if(!empty($res))
            {
                foreach ($res as $key => $value) {
                    $result=array();
                    $result['user_name'] =$user_name_list[$value['to_id']];
                    $result['createtime'] = date('Y-m-d H:i:s',$value['add_time']);
                    $result['point'] = $value['money'];
                    $result['get_money'] = $value['money'];
                    $result['oto'] = '';
                    $result['order_sn'] =$val['order_sn'];
                    $result['name'] =$store_name_list[$value['to_id']];
                    $result['user_id']=$user['user_id'];
                    $result['type']=0;
                    $result['from_userid']=$value['to_id'];
                    $result['char']='-';
                    $shoppingdata[]=$result;
                }
            }
            foreach($shoppingdata as $k=>$v)
            {
                $m->table('bill_statistics')->add(array('user_id'=>$v['user_id'],'type'=>$v['type'],'point'=>$v['point'],'get_money'=>$v['get_money'],'createtime'=>$v['createtime'],'oto'=>$v['oto'],'order_sn'=>$v['order_sn'],'name'=>$v['name'],'charinfo'=>$v['char'],'user_name'=>$v['user_name'],'from_userid'=>$v['from_userid']));
            }
        }
        echo 'ok'."\n";
        echo '结束时间：'.date('Y-m-d H:i:s');
    }

    //收益账单明细
    function profitinfo()
    {
        echo '开始时间：'.date('Y-m-d H:i:s')."\n";
        $type =isset($_REQUEST['type']) ? $_REQUEST['type'] :'1';
        $day=isset($_REQUEST['time']) ? $_REQUEST['time'] :date('Y-m-d 01:30:00');
        $endtime=strtotime($day);
        $m =& m();
        if($type=='all')
        {
            $startdate=date('Y-m-d 01:30:00',strtotime($day.'-300 day'));
            $starttime=strtotime($startdate);
            $sql='delete from ecm_account_profit';
            $m->query($sql);
            $sql='alter table ecm_account_profit AUTO_INCREMENT=1;';
            $m->query($sql);
        }
        else{
            $startdate=date('Y-m-d 01:30:00',strtotime($day.'-1 day'));
            $starttime=strtotime($startdate);
            $sql='delete from ecm_account_profit where createtime>='.$starttime.' and createtime<'.$endtime;
            $m->query($sql);
        }
        echo '要统计的开始时间：'.$startdate.';结束时间：'.$day."\n";

        $taba = DB_PREFIX."sgxt_profit";
        $tabb = DB_PREFIX."sgxt_infotpl";
        $tabc = DB_PREFIX.'sgxt_commission';
        $tabd = DB_PREFIX.'member';
        //查询所有用户的id和手机号码
        $user_name_list=array();
        $real_name_list=array();
        $userslist_name=$m->table('member')->field('user_id,user_name,real_name')->select();
        foreach($userslist_name as $keylist=>$vallist)
        {
            $user_name_list[$vallist['user_id']]=$vallist['user_name'];
            $real_name_list[$vallist['user_id']]=$vallist['real_name'];
        }
        $store_name_list=array();
        $storelist=$m->table('store')->field('store_id,store_name')->select();
        foreach($storelist as $keylist=>$vallist)
        {
            $store_name_list[$vallist['store_id']]=$vallist['store_name'];
        }
        $classname_list=array();
        $classslist=$m->table('order_offline')->field('order_sn,classname,seller_name')->select();
        foreach($classslist as $keylist=>$vallist)
        {
            $classname_list[$vallist['order_sn']]=$vallist['classname'];
        }
        //查询收益表记录
        $sql = "select $taba.user_id,$taba.source_type as type,$taba.from_username,$taba.is_clearing,$taba.from_userid as fromid,$taba.remain_money as money,$taba.times,$taba.createtime,$taba.order_sn,$taba.order_type,$tabb.title as info from $taba join $tabb on $taba.source_type = $tabb.id where $taba.createtime>= ".$starttime." and $taba.createtime<".$endtime." order by $taba.createtime desc";
        $data=$m->query($sql);
        foreach($data as $k=>$v)
        {
            $namelist=$this->getname($v['order_sn'],$v['order_type'],$classname_list[$v['order_sn']]);
            $m->table('account_profit')->add(array('user_id'=>$v['user_id'],'type'=>$v['type'],'info'=>$v['info'],'createtime'=>$v['createtime'],'money'=>$v['money'],'user_name'=>$user_name_list[$v['fromid']],'fromid'=>$v['fromid'],'xq_real_name'=>$real_name_list[$v['fromid']],'classname'=>$namelist,'order_sn'=>$v['order_sn'],'order_type'=>$v['order_type'],'store_name'=>$store_name_list[$v['fromid']],'is_clearing'=>$v['is_clearing'],'times'=>$v['times']));
        }
        //查询代理记录
        $sql ="select $tabc.toid,$tabc.from_name,$tabc.fromid,$tabc.money,$tabc.createtime from $tabc left join $tabd on $tabc.fromid =$tabd.user_id where $tabc.money>0 and $tabd.type > 3 and $tabc.createtime>= ".$starttime." and $tabc.createtime<".$endtime." order by $tabc.createtime desc";
        $agentReward =$m->query($sql);
        if(empty($agentReward))$agentReward=array();
        foreach($agentReward as $k=>$v)
        {
            $m->table('account_profit')->add(array('user_id'=>$v['toid'],'type'=>0,'info'=>'代理奖励','createtime'=>$v['createtime'],'money'=>$v['money'],'user_name'=>$user_name_list[$v['fromid']],'fromid'=>$v['fromid'],'xq_real_name'=>$real_name_list[$v['fromid']]));
        }
        //查询提现记录
        $deposit =$m->table('sgxt_deposit')->where(array('ispay'=>1,'type'=>2))->where('money>0 and operatortime>='.$starttime.' and operatortime<'.$endtime)->select();
        foreach($deposit as $key=> $v)
        {
            $m->table('account_profit')->add(array('user_id'=>$v['userid'],'type'=>0,'info'=>'收益提现','createtime'=>$v['createtime'],'money'=>$v['money'],'user_name'=>$v['mobile'],'fromid'=>0,'xq_real_name'=>''));
        }
        echo 'ok'."\n";
        echo '结束时间：'.date('Y-m-d H:i:s');
    }
    //分类名称
    function getname($order_sn,$order_type,$classname)
    {
        if($order_type=='')
        {
            return '';
        }
        else if($order_type=='offline')
        {
            return $classname;
        }else
        {
            //查询线上订单
            $m=& m();
            $order=$m->table('order')->where(array('order_sn'=>$order_sn))->find();
            if(empty($order))
            {
                return '';
            }
            //查询分类
            $result=$this->getclassnamebyorderid($order['order_id'],$order['seller_id']);
            return $result;
        }
    }
    function getclassnamebyorderid($order_id,$store_id){
        //根据orderid查找goodid
        $m =& m();
        $sql="select goods_id from ecm_order_goods where order_id={$order_id}";
        $goods =$m->query($sql);
        //根据goods_id查询商品类别名称
        $sql='select ecm_gcategory.cate_name from ecm_category_goods join ecm_gcategory on ecm_category_goods.cate_id=ecm_gcategory.cate_id where ecm_category_goods.goods_id='.$goods[0]['goods_id'];
        $namelist =$m->query($sql);
        return $namelist[0]['cate_name'];
    }

    //收益解冻
    public function earningsUnfreeze()
    {
        //判断当前是不是解冻时间
        $today = date('d');        
        if ($today < 15) {
            //echo '每月15号以后为解冻时间';
            die;
        }
        $m = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y'))); //上个月的开始日期
        $t = date('t', strtotime($m)); //上个月共多少天
        $start = mktime(0, 0, 0, date('m') - 1, 1, date('Y')); //上个月的开始日期
        $end = mktime(23, 59, 59, date('m') - 1, $t, date('Y')); //上个月的结束日期
        $this->model = &m();
        //查询未解冻的收益
        $userlist = $this->model->table('sgxt_profit')->where('is_clearing=0 and createtime<'.$end)->select();        
        foreach ($userlist as $key => $user) {        	
            $money = $this->model->table('sgxt_profit')->where('user_id='.$user['user_id']. ' and is_clearing=0 and createtime<'.$end)->sum('remain_money');
            if (empty($money)) {
                continue;
            }
            $upid = $this->model->table('sgxt_profit')->where('user_id='.$user['user_id']. ' and is_clearing=0 and createtime<'.$end)->getField('id', true);
            if (count($upid) == 1) {
                $upid = $upid[0];
            } else {
                $upid = implode(',', $upid);
            }

            //将收益增加到余额中
            $this->model->setBegin();
            $pass1 = $this->model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('earnings', $money);
            $pass2 = $this->model->table('epay')->where(array('user_id' => $user['user_id']))->setDec('freeze_earnings', $money);
            if ($pass1 && $pass2) {
                $this->model->table('sgxt_profit')->where("id in ($upid) and createtime<".$end)->save(array('is_clearing' => 1));
                $this->model->commit();
                $member = $this->model->table('member')->where(array('user_id' => $user['user_id']))->find1();
                //增加解冻记录
                $insdata = array(
                    'userid' => $user['user_id'],
                    'user_name' => $member['real_name'],
                    'money' => $money,
                    'type' => 2,
                    'createtime' => time(),
                );
                $this->model->table('sgxt_unfreeze')->add($insdata);
            } else {
                $this->model->rollBack();
                echo '收益解冻失败';
                file_put_contents('unfreeze.txt',$user['user_name'].'(user_id='.$user['user_id'].')收益解冻失败！'."\r\n",FILE_APPEND);
                //die;
            }
        }
        unset($user);
    }

    //余额解冻
    public function balanceUnfreeze()
    {
        //判断当前是不是解冻时间
        $today = date('d');
        
        if ($today < 15) {
            echo '每月15号以后为解冻时间';
            die;
        }
        
        $fp=fopen('balanceUnfreeze'.date('Ymd_His',time()).'.txt','w');

        $m = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y'))); //上个月的开始日期
        $t = date('t', strtotime($m)); //上个月共多少天
        $start = mktime(0, 0, 0, date('m') - 1, 1, date('Y')); //上个月的开始日期
        $end = mktime(23, 59, 59, date('m') - 1, $t, date('Y')); //上个月的结束日期
        $this->model =& m();
        //查询所有未解冻的用户
        $userlist = $this->model->table('sgxt_balance')->where('is_clearing=0 and createtime<'.$end)->select();                
        foreach ($userlist as $key => $user) {        	
            $money = $this->model->table('sgxt_balance')->where('user_id='.$user['user_id']. ' and is_clearing=0 and createtime<'.$end)->sum('get_money');
            $money = $money ? $money : 0;
            fwrite($fp,$user['user_id'].'='.$money."\r\n");
            if($money == 0){
                continue;
            }
            $upid = $this->model->table('sgxt_balance')->where('user_id='.$user['user_id']. ' and is_clearing=0 and createtime<'.$end)->getField('id',true);
            if(count($upid) == 1){
                $upid = $upid[0];
            }else{
                $upid = implode(',',$upid );
            }            
            //将收益增加到余额中
            $this->model->setBegin();
            $pass1 = $this->model->table('epay')->where(array('user_id' => $user['user_id']))->setInc('money' , $money);
            $pass2 = $this->model->table('epay')->where(array('user_id' => $user['user_id']))->setDec('money_dj' , $money);            
            if($pass1 && $pass2 ){            	
                $this->model->table('sgxt_balance')->where("id in ($upid) and createtime<".$end) ->save(array('is_clearing' => 1));
                $this->model->commit();
                $member = $this->model->table('member')->where(array('user_id' => $user['user_id']))->find1();
                //增加解冻记录
                $insdata = array(
                    'userid' => $user['user_id'],
                    'user_name' => $member['real_name'],
                    'money' => $money,
                    'type' => 1,
                    'createtime' => time(),
                );
                unset($member);
                $this->model->table('sgxt_unfreeze')->add($insdata);
            }else{
                $this->model->rollBack();
                echo '购物积分解冻失败';
                //die ;
                file_put_contents('unfreeze.txt',$user['user_name'].'(user_id='.$user['user_id'].')购物积分解冻失败！'."\r\n",FILE_APPEND);
            }
            unset($user);
        }
        fclose($fp);
    }

}




?>