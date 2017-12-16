<?php
header("Content-type:text/html;charset=utf-8");
/* 会员 member */
ini_set('max_execution_time',0);
class StatisticsinfoApp extends FrontendApp{
    //给用户添加手机号码
    public function setphone()
    {
        $model = & m();
        $sql="select * from ecm_member where phone_mob is null or phone_mob=''";
        $list = $model->query($sql);
        foreach($list as $key=>$value)
        {
            $sql='update ecm_member set phone_mob='.$value['user_name'].' where user_id='.$value['user_id'];
            $model->query($sql);
        }
    }
    //查询数据，检测有异常的商户
    public function check()
    {
        $model = & m();
        $list = $model->query('select * from ecm_store');
        $info=$info1=$info2='';
        $str=$str1=$str2='';
        foreach($list as $key=>$value)
        {
            if($value['o2o']=='online')
            {
                $sql='select sum(order_amount) as total_money from ecm_order where seller_id='.$value['store_id'].' and status>=20;';
            }
            else
            {
                $sql='select sum(order_amount) as total_money from ecm_order_offline where seller_id='.$value['store_id'].' and status=40 and payment_id=3;';
            }
            $order=$model->query($sql);
            //查询商户的货款
            $total_money=$order[0]['total_money'];
            //查询商户发积分量
            $point=$model->query('select sum(shops_point) as total_point from ecm_sgxt_get_point where sendid='.$value['store_id'].' and is_pass in (0,1);');
            $total_point=$point[0]['total_point'];
            $num=$total_point-$total_money;
            if($num>=10000)
            {
                $str.=$value['store_id'].',';
                $info.=$value['store_name'].','.$value['owner_name'].','.'多发积分：'.$num.'(总货款：'.$total_money.'总积分：'.$total_point.")\r\n";
            }else if($num>=1000&$num<10000)
            {
                $str1.=$value['store_id'].',';
                $info1.=$value['store_name'].','.$value['owner_name'].','.'多发积分：'.$num.'(总货款：'.$total_money.'总积分：'.$total_point.")\r\n";
            }else if($num>0)
            {
                $str2.=$value['store_id'].',';
                $info2.=$value['store_name'].','.$value['owner_name'].','.'多发积分：'.$num.'(总货款：'.$total_money.'总积分：'.$total_point.")\r\n";
            }
        }
        file_put_contents('checkshops.txt',$info."\n\n\n\n".$info1."\n\n\n\n".$info2);
        file_put_contents('store_id.txt',$str."\n\n\n\n".$str1."\n\n\n\n".$str2);
        echo $str;
    }
    //查询安阳市代会员
    public function selectuser()
    {
        $model = & m();
        $list = $model->query('select user_name,real_name,type,reg_time from ecm_member where city=410500 and status=1;');
        $totalcount=0;
        foreach($list as $key => $value)
        {
            $totalcount++;
            $userlist[$key]['reg_time']=date('Y-m-d H:i:s',$value['reg_time']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $type=conf('user_type');
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*市区';
        $cols_item1[] = '*总人数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = '安阳市';
        $tmp_col1[] = $totalcount;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '手机号码';
        $cols_item[] = '用户姓名';
        $cols_item[] = '用户类型';
        $cols_item[] = '注册时间';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['user_name'];
                $tmp_col[] = $v['real_name'];
                $tmp_col[] = $type[$v['type']];
                $tmp_col[] = date('Y-m-d H:i:s',$v['reg_time']);
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //查询分类
    public function selectstore()
    {
        $model = & m();
        $store_id_str='';
        $storelist = $model->table('store')->field('store_id')->where('state=1 and o2o=\'online\'')->select();
        foreach($storelist as $key => $value)
        {
            $store_id_str.=$value['store_id'].",";
        }
        $store_id_str=substr($store_id_str,0,strlen($store_id_str)-1);
        $cate_list=$model->table('scategory')->select();
        $catelist_arr=array();
        foreach($cate_list as $k1=> $v1)
        {
            $catelist_arr[$v1['cate_id']]=$v1['cate_name'];
        }
        $sql='select cate_id,count(*) as count from ecm_category_store where store_id in ('.$store_id_str.') group by cate_id ';
        $list=$model->query($sql);
        $fp=fopen('cate_count.txt','w');
        $totalcount=0;
        foreach($list as $k2 => $v2)
        {
            $totalcount=$totalcount+$v2['count'];
            echo $info=$catelist_arr[$v2['cate_id']].' '.$v2['count']."\n";
            fwrite($fp,$info);
        }
        fwrite($fp,'总商家：'.$totalcount);
        fclose($fp);
    }
    function storechecktime()
    {
        $model = & m();
        $fp=fopen('txcheck.txt','w');
        $sql='select * from ecm_store where state=1 order by store_id desc';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            if($value['apply_time']<=$value['add_time'])
            {
                fwrite($fp,$value['store_id'].';申请时间：'.date('Y-m-d H:i:s',$value['add_time']).';审核时间：'.date('Y-m-d H:i:s',$value['apply_time'])."\n");
                $sql='update ecm_store set apply_time=1487901600 where store_id='.$value['store_id'];
                //$model->query($sql);
            }
        }
        fclose($fp);
        echo 'ok';
    }
    function deposit()
    {
        $model = & m();
        /*$fp=fopen('deposit.txt','w');
        $sql='select * from ecm_sgxt_deposit where ispay>0';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            fwrite($fp,$value['deid'].','.$value['ispay'].','.$value['operatortime'].','.$value['operatorid'].','."\n");
        }
        fclose($fp);*/
        /*$fp=fopen('deposit.txt','r');
        while ($info= fgets($fp, 4096))
        {
           $arr=explode(',',$info);
            $sql='update ecm_sgxt_deposit set ispay='.$arr[1].',operatortime='.$arr[2].',operatorid='.$arr[3].' where deid='.$arr[0];
            $model->query($sql);
        }
        fclose($fp);*/
        /*$fp=fopen('tx.txt','w');
        $sql='select * from ecm_sgxt_deposit where ispay=0';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            $ispay=0;
            if($value['payment']==-1 || $value['payment']==3)
            {
                $ispay=2;
            }
            else if($value['payment']==1 || $value['payment']==2)
            {
                $ispay=1;
            }
            $sql='update ecm_sgxt_deposit set ispay='.$ispay.' where deid='.$value['deid'];
            $model->query($sql);
        }
        fclose($fp);*/
        /*$sql='select * from ecm_sgxt_deposit where ispay=0 and deid<=754 order by deid desc';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            $ispay=0;
            $sql='select * from ecm_sgxt_oplog where obj_id='.$value['deid'];
            $infolist=$model->query($sql);
            $info=$infolist[0]['info'];
            if(strstr($infolist[0]['info'],"批准")!==false)
            {
                $ispay=1;
            }
            if(strstr($infolist[0]['info'],"驳回")!==false)
            {
                $ispay=2;
            }
            $sql='update ecm_sgxt_deposit set ispay='.$ispay.' where deid='.$value['deid'];
            $model->query($sql);
        }*/
        /*$sql='select * from ecm_sgxt_deposit where operatortime=0 order by deid desc';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            $operatortime=0;
            $sql='select * from ecm_sgxt_oplog where obj_id='.$value['deid'];
            $infolist=$model->query($sql);
            if(!empty($infolist))
            {
                $operatortime=$infolist[0]['createtime'];
                $sql='update ecm_sgxt_deposit set operatortime='.$operatortime.' where deid='.$value['deid'];
                $model->query($sql);
            }
        }*/
        /*$fp=fopen('txcheck.txt','w');
        $sql='select * from ecm_sgxt_deposit where ispay>0 order by deid desc';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            $ispayvalue=$value['ispay'];
            $ispay=0;
            $sql='select * from ecm_sgxt_oplog where obj_type=\'deposit\' and obj_id='.$value['deid'].' order by id desc limit 1';
            $infolist=$model->query($sql);
            $info=$infolist[0]['info'];
            if(strstr($infolist[0]['info'],"批准")!==false)
            {
                $ispay=1;
            }
            if(strstr($infolist[0]['info'],"驳回")!==false)
            {
                $ispay=2;
            }
            if($ispayvalue!=$ispay)
            {
                fwrite($fp,$value['deid'].';ispay='.$ispayvalue.';'.$infolist[0]['info']."\n");
            }
        }
        fclose($fp);*/
        $fp=fopen('txcheck.txt','w');
        $sql='select * from ecm_sgxt_deposit where ispay>0 order by deid desc';
        $userlist=$model->query($sql);
        foreach($userlist as $key => $value)
        {
            if($value['createtime']>=$value['operatortime'])
            {
                $sql='select * from ecm_sgxt_oplog where obj_type=\'deposit\' and obj_id='.$value['deid'].' order by id desc limit 1';
                $infolist=$model->query($sql);
                $operatortime=$infolist[0]['createtime'];
                fwrite($fp,$value['deid'].';ispay='.$value['ispay']."\n");
                //$sql='update ecm_sgxt_deposit set operatortime='.$operatortime.' where deid='.$value['deid'];
                //$model->query($sql);
            }
        }
        fclose($fp);
        echo 'ok';
    }
    //市代补会员收益（会员设置省市县后）
    function setearning()
    {
        $model = & m();
        $user_id=867;
        $sql='select * from ecm_member where user_id='.$user_id;
        $userlist=$model->query($sql);
        $getuser=$userlist[0];
        $totalmoney=0;
        $fp=fopen('log20170214.txt','w');
        fwrite($fp,'给市代增加区域下会员收益：'."\n");
        //查询这些用户所获得的积分，然后给市代提成
        $sql='select * from ecm_sgxt_get_point where getid in (15953,15919,16070,15916,16047,16089,15852,16028,15938,16053,15842,15741,16019,15832) order by createtime desc;';
        $list=$model->query($sql);
        foreach($list as $key=> $value)
        {
            $real_point=$value['point'];
            $shops_point=$value['shops_point'];
            $money = $shops_point * conf("user_reward/".$getuser['type']."/area_users");
            $money = sprintf("%.2f",$money);
            $pass = $model -> table('epay') -> where(array('user_id' => $getuser['user_id'])) ->setInc('freeze_earnings' , $money);
            $adddata = array(
                'user_id'  => $getuser['user_id'],
                'user_name' => $getuser['real_name']?$getuser['real_name']:$getuser['user_name'],
                'remain_money' => $money,
                'real_point' => $real_point,
                'source_type' => 7,
                'from_username' => $value['getname'],
                'from_userid'  => $value['getid'],
                'createtime'  => $value['createtime'],
                'times'       => date('Ym',$value['createtime']),
                'area'   =>  $getuser['area'],
                'city'   =>  $getuser['city'],
                'province' =>  $getuser['province'],
                'opid'   =>  $getuser['opid'],
            );

            $pass1 = $model->table('sgxt_profit') -> add($adddata);
            if(!$pass ||  !$pass1){
                throw new MyException("用户".$getuser['user_id']."增加收益".$money."失败");
            }
            $totalmoney=$totalmoney+$money;
            $type_cn='市代';
            $logInfo =$type_cn."：".$getuser['real_name'].'('.$getuser['user_name'].')从 '.$value['getname'].'获得收益为：'.$shops_point . '*' . conf("user_reward/".$getuser['type']."/area_users") . '='.$money.' 时间：'.date('Y-m-d H:i:s',$value['createtime'])."\r\n";
            fwrite($fp,$logInfo);
        }
        fwrite($fp,'总额：'.$totalmoney);
        fclose($fp);
    }
    //发积分单子
    function sendpoint()
    {
        $m=& m();
        $user_id=7727;
        $type=array('0'=>'未审核','1'=>'已通过');
        //购积分
        $sql='select * from ecm_sgxt_get_point where sendid='.$user_id.' and is_pass in (0,1);';
        $list=$m->query($sql);
        $i=0;
        $totalMoney=0;
        foreach($list as $key=> $value)
        {
            $i++;
            $totalMoney=$totalMoney+floatval($value['shops_point']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*总金额';
        $cols_item1[] = '*总笔数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = $totalMoney;
        $tmp_col1[] = $i;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '店铺名称';
        $cols_item[] = '积分';
        $cols_item[] = '获赠会员名';
        $cols_item[] = '时间';
        $cols_item[] = '状态';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['sendname'];
                $tmp_col[] = $v['shops_point'];
                $tmp_col[] = $v['getname'];
                $tmp_col[] = date('Y-m-d H:i:s',$v['createtime']);
                $tmp_col[] = $type[$v['is_pass']];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //转换记录
    function zhuanha()
    {
        $m=& m();
        $user_id=5;
        $type=array('0'=>'未审核','1'=>'已通过');
        $sql='select * from ecm_sgxt_balance where user_id=5 and source_type=2 order by id desc;';
        $list=$m->query($sql);
        $i=0;
        $totalMoney=0;
        foreach($list as $key=> $value)
        {
            $i++;
            $totalMoney=$totalMoney+floatval($value['get_money'])+floatval($value['happiness']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*总返积分';
        $cols_item1[] = '*总笔数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = $totalMoney;
        $tmp_col1[] = $i;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '已返积分';
        $cols_item[] = '幸福积分';
        $cols_item[] = '定返时间时间';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['get_money'];
                $tmp_col[] = $v['happiness'];
                $tmp_col[] = date('Y-m-d H:i:s',$v['createtime']);
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //获赠积分
    function getpoint()
    {
        $m=& m();
        $user_id=5;
        $type=array('0'=>'未审核','1'=>'已通过');
        $sql='select * from ecm_sgxt_get_point where getid='.$user_id.' and is_pass in (0,1) order by id desc;';
        $list=$m->query($sql);
        $i=0;
        $totalMoney=0;
        foreach($list as $key=> $value)
        {
            $i++;
            $totalMoney=$totalMoney+floatval($value['point']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*总积分';
        $cols_item1[] = '*总笔数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = $totalMoney;
        $tmp_col1[] = $i;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '店铺名称';
        $cols_item[] = '获赠积分';
        $cols_item[] = '时间';
        $cols_item[] = '状态';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['sendname'];
                $tmp_col[] = $v['point'];
                $tmp_col[] = date('Y-m-d H:i:s',$v['createtime']);
                $tmp_col[] = $type[$v['is_pass']];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //购积分单子
    function buypoint()
    {
        $m=& m();
        $user_id=1159;
        $type=array('wx'=>'微信支付','llpay'=>'连连支付','ll'=>'连连支付','balance'=>'货款支付','reapal'=>'融宝支付','bank'=>'网银支付');
        //购积分
        $sql='select * from ecm_sgxt_order where userid='.$user_id.' and status=1 and paytype not like \'%balance%\';';
        $list=$m->query($sql);
        $i=0;
        $totalMoney=0;
        foreach($list as $key=> $value)
        {
            $i++;
            $totalMoney=$totalMoney+floatval($value['amount']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*总金额';
        $cols_item1[] = '*总笔数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = $totalMoney;
        $tmp_col1[] = $i;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '订单号';
        $cols_item[] = '姓名';
        $cols_item[] = '金额';
        $cols_item[] = '购买时间';
        $cols_item[] = '支付类型';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['orderid'];
                $tmp_col[] = $v['truename'];
                $tmp_col[] = $v['amount'];
                $tmp_col[] = date('Y-m-d H:i:s',$v['pay_createtime']);
                $tmp_col[] = $type[$v['paytype']];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //整理县代收益
    function earning()
    {
        $m=& m();
        $type=array('5'=>'区域下商家提成','6'=>'推荐县级下商家提成','7'=>'区域下会员提成');
        //$user_id=5966;
        //$user_name='温泉';
        $user_id=6283;
        $user_name='罗勇';
        $sql="select user_name,remain_money,source_type,from_username,FROM_UNIXTIME(createtime,'%Y-%m-%d %H:%i:%s') times  from ecm_sgxt_profit where user_id=".$user_id." and user_name='".$user_name."' and createtime<1485878400;";
        $list=$m->query($sql);
        $i=0;
        $totalMoney=0;
        foreach($list as $key=> $value)
        {
            $i++;
            $totalMoney=$totalMoney+floatval($value['remain_money']);
        }
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$list) {
            $this->show_warning('无数据');
            return;
        }
        $cols = array();
        $cols_item1 = array();
        $cols_item1[] = '*日期';
        $cols_item1[] = '*总金额';
        $cols_item1[] = '*总笔数';
        $cols[] = $cols_item1;
        $tmp_col1 = array();
        $tmp_col1[] = date('Ymd');
        //$tmp_col1[] = $rs[0]['totalmoney'];
        $tmp_col1[] = $totalMoney;
        $tmp_col1[] = $i;
        $cols[] = $tmp_col1;
        $cols_item = array();
        $cols_item[] = '姓名';
        $cols_item[] = '收益';
        $cols_item[] = '来源用户姓名';
        $cols_item[] = '收益类型';
        $cols_item[] = '时间';

        $cols[] = $cols_item;

        if (is_array($list) && count($list) > 0) {
            foreach ($list as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['user_name'];
                $tmp_col[] = $v['remain_money'];
                $tmp_col[] = $v['from_username'];
                $tmp_col[] = $type[$v['source_type']];
                $tmp_col[] = $v['times'];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
    }
    //查询suge地址有问题的用户
    function checkadress()
    {
        $fp=fopen('checkuser.txt','w');
        fwrite($fp,"以下是省市县有误的用户\n");
        $m=& m();
        $areaarray=array();
        $areanamearray=array();
        $usertype=conf('user_type');
        $sql="select id,name from ecm_sgxt_area;";
        $arealist=$m->query($sql);
        foreach($arealist as $key=>$value)
        {
            $areaarray[$value['id']]=$value['name'];
            $areanamearray[$value['name']]=$value['id'];
        }
        $sql="select * from ecm_member where (province=city or area=province or city=area) and (province!='' or city!='' or area!='' or province!=0 or city!=0 or area!=0) and type!=0;";
        $list1=$m->query($sql);
        foreach($list1 as $k1 => $v1)
        {
            //查询店铺地址
            $sql='select region_name from ecm_store where store_id='.$v1['user_id'];
            $storelist=$m->query($sql);
            $reg=$storelist[0]['region_name'];
            $info1=$usertype[$v1['type']].$v1['real_name'].':'.$v1['user_name'].';'.$areaarray[$v1['province']].$areaarray[$v1['city']].$areaarray[$v1['area']].';';
            //更新省市县
            $arr=explode('	',$reg);
            if(count($arr)>=3)
            {
                $info1.=$arr[1].$arr[2].$arr[3];
                $sql='update ecm_member set province='.$areanamearray[$arr[1]].',city='.$areanamearray[$arr[2]].' where user_id='.$v1['user_id'];
                //$m->query($sql);
            }
            fwrite($fp,$info1."\n");
        }
        fwrite($fp,"以下是没有省市县的用户\n");
        $sql='select * from ecm_member where (province=\'\' or city=\'\' or area=\'\' or province=0 or city=0 or area=0 ) and type!=0;';
        $list=$m->query($sql);
        foreach($list as $k => $v)
        {
            //查询店铺地址
            $sql='select region_name from ecm_store where store_id='.$v['user_id'];
            $storelist1=$m->query($sql);
            $info=$usertype[$v['type']].$v['real_name'].':'.$v['user_name'].';'.$storelist1[0]['region_name'];
            fwrite($fp,$info."\n");
        }
        echo 'ok!';
    }
    //更改苏格的商铺注册地址
    function adress()
    {
        $file_path='sguser.txt';
        if(file_exists($file_path)) {
            $str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中
            $str = str_replace("\r\n", ",", $str);
            //echo $str;
        }
        $arr = explode(',',$str);
        $m=& m();
        foreach($arr as $k => $v)
        {
            //查询该用户并更改注册地
            $sql='update ecm_member set province=\'810000\',city=\'810200\',area=\'810201\' where user_name='.$v;
            $m->query($sql);
            echo $v.',';
        }
        echo 'ok';
    }
}
?>