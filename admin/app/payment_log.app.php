<?php

/**

 *    支付记录控制器

 *

 *    @author    Garbin

 *    @usage    none

 */



class Payment_logApp extends BackendApp

{

    /**

     *    管理

     *

     *    @author    Garbin

     *    @param    none

     *    @return    void

     */



    function index()



    {



        $search_options = array(



            'user_name'   => '姓名',



        );



        /* 默认搜索的字段是店铺名 */



        $field = 'mobile';







        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];



        $conditions = $this->_get_query_conditions(array(

            array(

                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索

                'equal' => 'LIKE',

                'name'  => 'search_name',

            ),array(

                'field' => 'add_time',

                'name'  => 'add_time_from',

                'equal' => '>=',

                'handler'=> 'gmstr2time',

            ),array(

                'field' => 'add_time',

                'name'  => 'add_time_to',

                'equal' => '<=',

                'handler'   => 'gmstr2time_end',

            ),array(

                'field' => 'money',

                'name'  => 'order_amount_from',

                'equal' => '>=',

                'type'  => 'numeric',

            ),array(

                'field' => 'money',

                'name'  => 'order_amount_to',

                'equal' => '<=',

                'type'  => 'numeric',

            ),array(

                'field' => 'payment_id',

                'name'  => 'payment_id',

                'equal' => '=',

                'type'  => 'numeric',

            ),array(

                'field' => 'province',

                'name'  => 'province',

                'equal' => '=',

                'type'  => 'numeric',

            ),array(

                'field' => 'city',

                'name'  => 'city',

                'equal' => '=',

                'type'  => 'numeric',

            ),array(

                'field' => 'area',

                'name'  => 'area',

                'equal' => '=',

                'type'  => 'numeric',

            ),



        ));



        $model = &m();

        $province = isset($_GET['province']) ? trim($_GET['province']) : null;

        $city = isset($_GET['city']) ? trim($_GET['city']) : null;

        $area = isset($_GET['area']) ? trim($_GET['area']) : null;



        $this->model = & m();

        $page   =   $this->_get_page(10);    //获取分页信息

        //更新排序

        if (isset($_GET['sort']) && isset($_GET['order']))

        {

            $sort  = strtolower(trim($_GET['sort']));

            $order = strtolower(trim($_GET['order']));

            if (!in_array($order,array('asc','desc'))){

             $sort  = 'add_time';

             $order = 'desc';

            }

        }else{

            $sort  = 'add_time';

            $order = 'desc';

        }

        $totalMoney = 0;

        if(!isset($_GET['payment_id'])){

            $conditions .= ' and payment_id in (5,6,9)';

        }

        $conditions = ' 1=1 '.$conditions;

        $count = $this->model->table('paymentlog')->where($conditions)->count();

        $totalMoney = $this->model->table('paymentlog')->where($conditions)->sum('money');

        $paylist = $this->model->table('paymentlog')->where($conditions)->order("$sort $order")->page($count)->select();

        $mypage = $this->model->getButton();

        /*$rs = $this->model->query('select sum(money) as totalmoney from `ecm_sgxt_deposit` where 1=1 '.$conditions);

        $orders = $this->model->table('sgxt_deposit')->find(array(

            'conditions'    => '1=1 ' . $conditions,

            'limit'         => $page['limit'],  //获取当前页的数据

            'order'         => "$sort $order",

            'count'         => true             //允许统计

        )); //找出所有商城的合作伙伴*/



        $plist = $clist = $alist = $provinceList = $cityList = $areaList = array();

        foreach($paylist as $key=>$val){

            if(!in_array($val['province'], $plist)){

                $plist[] = $val['province'];

            }

            if(!in_array($val['city'], $clist)){

                $clist[] = $val['city'];

            }

            if(!in_array($val['area'], $alist)){

                $alist[] = $val['area'];

            }

        }        

        $prolist = $this->model->table('sgxt_area')->where('id in ('.implode(',', $plist).')')->select();        

        $citlist = $this->model->table('sgxt_area')->where('id in ('.implode(',', $clist).')')->select();

        $arelist = $this->model->table('sgxt_area')->where('id in ('.implode(',', $alist).')')->select();

        foreach($prolist as $k=>$v){

            $provinceList[$v['id']] = $v;

        }

        foreach($citlist as $k=>$v){

            $cityList[$v['id']] = $v;

        }

        foreach($arelist as $k=>$v){

            $areaList[$v['id']] = $v;

        }

        foreach($paylist as $key=>$val){

            $user = $this->model->table('member')->where('user_id='.$val['user_id'])->find1();

            $store = $this->model->table('store')->where('store_id='.$val['user_id'])->find1();

            $val['user_name'] = $user['real_name'] ? $user['real_name'] : $store['owner_name'];

            $val['store_name'] = $store['store_name'];

            $val['user_mob'] = $user['user_name'];

            $val['region_name'] = $store['region_name'];

            if($user['province'] && $user['city'] && $user['area']){

                $val['region_name'] = $provinceList[$user['province']]['name'].' '.$cityList[$user['city']]['name'].' '.$areaList[$user['area']]['name'];

            }            

            $type = '微信支付购买积分';

            if($val['payment_id'] == 6){

                $type = '货款支付购买积分';

            }

            if($val['payment_id'] == 9){

                $type = '连连支付购买积分';

            }

            $val['type_cn'] = $type;

            $val['order_sn'] = substr($val['order_sn'],1);

            $val['order_sn'] ='P'.$val['order_sn'];

            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);

            $paylist[$key] = $val;

        }

        $plist = $model->table('sgxt_area')->where('parent_id=1')->select();

        $cityList = $areaList = $provinceList = array();

        if($this->searchQuery['city']){

            $city = $model->table('sgxt_area')->where('id='.$this->searchQuery['city'])->find1();

            $clist = $model->table('sgxt_area')->where('parent_id='.$city['parent_id'])->select();

            foreach($clist as $key=>$val){

                $cityList[$val['id']] = $val['name'];

            }

            $this->assign('cityList',$cityList);

        }

        if($this->searchQuery['area']){

            $area = $model->table('sgxt_area')->where('id='.$this->searchQuery['area'])->find1();

            $alist = $model->table('sgxt_area')->where('parent_id='.$area['parent_id'])->select();

            foreach($alist as $key=>$val){

                $areaList[$val['id']] = $val['name'];

            }

            $this->assign('areaList',$areaList);

        }



        foreach($plist as $key=>$val){

            $provinceList[$val['id']] = $val['name'];

        }

        $this->assign('provinceList',$provinceList);

        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件

        $this->assign('order_status_list', array(

            5 => '微信支付购买积分',

            6 => '货款支付购买积分',
            9 => '连连支付购买积分'

        ));

        $this->assign('mypage',$mypage);

        $this->assign('totalMoney',$totalMoney);

        $this->assign('search_options', $search_options);

        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条

        $this->assign('paylist', $paylist);

        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',

                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));

        $this->display('paymentlog.index.html');

    }



    



    /**

     * 订单导出

     */



    function export()



    {



        $search_options = array(



            'user_name'   => '姓名',



        );



        /* 默认搜索的字段是店铺名 */



        $field = 'mobile';







        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];



        $conditions = $this->_get_query_conditions(array(



            array(



                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索



                'equal' => 'LIKE',



                'name'  => 'search_name',



            ),array(



                'field' => 'add_time',



                'name'  => 'add_time_from',



                'equal' => '>=',



                'handler'=> 'gmstr2time',



            ),array(



                'field' => 'add_time',



                'name'  => 'add_time_to',



                'equal' => '<=',



                'handler'   => 'gmstr2time_end',



            ),array(



                'field' => 'money',



                'name'  => 'order_amount_from',



                'equal' => '>=',



                'type'  => 'numeric',



            ),array(



                'field' => 'money',



                'name'  => 'order_amount_to',



                'equal' => '<=',



                'type'  => 'numeric',



            ),array(



                'field' => 'payment_id',



                'name'  => 'payment_id',



                'equal' => '=',



                'type'  => 'numeric',



            ),array(

                'field' => 'province',

                'name'  => 'province',

                'equal' => '=',

                'type'  => 'numeric',

            ),array(

                'field' => 'city',

                'name'  => 'city',

                'equal' => '=',

                'type'  => 'numeric',

            ),array(

                'field' => 'area',

                'name'  => 'area',

                'equal' => '=',

                'type'  => 'numeric',

            ),



        ));



        $this->model = & m();



        $page   =   $this->_get_page(10);    //获取分页信息



        //更新排序



        if (isset($_GET['sort']) && isset($_GET['order']))



        {



            $sort  = strtolower(trim($_GET['sort']));



            $order = strtolower(trim($_GET['order']));



            if (!in_array($order,array('asc','desc')))



            {



                $sort  = 'add_time';



                $order = 'desc';



            }



        }



        else



        {



            $sort  = 'add_time';



            $order = 'desc';



        }



        $totalMoney = 0;







        if(!isset($_GET['payment_id']) || $_GET['payment_id'] == ''){



            $conditions .= ' and payment_id in (5,6)';



        }



        $conditions = ' 1=1 '.$conditions;







        $count = $this->model->table('paymentlog')->where($conditions)->count();







        $totalMoney = $this->model->table('paymentlog')->where($conditions)->sum('money');







        $paylist = $this->model->table('paymentlog')->where($conditions)->order("$sort $order")->select();











        $mypage = $this->model->getButton();











        /*$rs = $this->model->query('select sum(money) as totalmoney from `ecm_sgxt_deposit` where 1=1 '.$conditions);



        $orders = $this->model->table('sgxt_deposit')->find(array(



            'conditions'    => '1=1 ' . $conditions,



            'limit'         => $page['limit'],  //获取当前页的数据



            'order'         => "$sort $order",



            'count'         => true             //允许统计



        )); //找出所有商城的合作伙伴*/



        foreach($paylist as $key=>$val){



            $user = $this->model->table('member')->where('user_id='.$val['user_id'])->find1();



            $bank = $this->model->table('epay_bank')->where('user_id='.$val['user_id']. ' and status=0')->find1();



            $store = $this->model->table('store')->where('store_id='.$val['user_id'])->find1();



            $val['user_name'] = $user['real_name'] ? $user['real_name'] : $store['owner_name'];



            $val['store_name'] = $store['store_name'];



            $val['user_mob'] = $user['user_name'];



            $val['bank_name'] = $bank['bank_name'];



            $val['open_bank'] = $bank['open_bank'];



            $val['bank_num'] = $bank['bank_num'];



            $val['bank_code'] = $bank['bank_code'];



            $val['region_name'] = $store['region_name'];



            $type = '微信支付购买积分';



            if($val['payment_id'] == 6){



                $type = '货款支付购买积分';



            }



            $val['type_cn'] = $type;



            $val['order_sn'] = substr($val['order_sn'],1);



            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);



            $paylist[$key] = $val;



        }



        import('excelwriter.lib');



        $excel = new ExcelWriter('utf8', 'toexcel');



        if (!$paylist) {



            $this->show_warning('无数据');



            return;



        }







        $cols = array();



        $cols_item = array();



        $cols_item[] = '编号';



        $cols_item[] = '店铺名';



        $cols_item[] = '用户名';



        $cols_item[] = '所属区域';



        $cols_item[] = '订单号';



        $cols_item[] = '购买积分';



        $cols_item[] = '购买方式';



        $cols_item[] = '支行名';



        $cols_item[] = '开户账号';



        $cols_item[] = '开户行号';



        $cols_item[] = '购买时间';







        $cols[] = $cols_item;







        if (is_array($paylist) && count($paylist) > 0) {



            foreach ($paylist as $k => $v) {







                $tmp_col = array();



                $tmp_col[] = $v['id'];



                $tmp_col[] = $v['store_name'];



                $tmp_col[] = $v['user_name'];



                $tmp_col[] = $v['region_name'];



                $tmp_col[] = $v['order_sn'];



                $tmp_col[] = $v['money'];



                $tmp_col[] = $v['type_cn'];



                $tmp_col[] = $v['open_bank'];



                $tmp_col[] = $v['bank_num'];



                $tmp_col[] = $v['bank_code'];



                $tmp_col[] = $v['add_time'];



                $cols[] = $tmp_col;



            }



        }



        $excel->add_array($cols);



        $excel->output();



        



    }







    function accept(){



        $user = $this->visitor->info;



        $id = intval($_GET['id']);



        $this->model = & m();



        $deposit = $this->model->table('sgxt_deposit')->where('deid='.$id)->find1();



        if(!$deposit){



            $this->show_warning('提现记录不存在');



            exit;



        }



        $sql = "update ecm_sgxt_deposit set ispay=1,operatortime='".time()."',operatorid=".$user['user_id'].",operatorname='".$user['real_name']."' where deid={$id}";



        $rs = $this->model->query($sql);



        if($rs){



            $this->model->table('sgxt_oplog')->add(array(



                'obj_id' => $id,



                'obj_type' => 'deposit',



                'opid' => $user['user_id'],



                'info' => '管理员'.$user['true_name'].'批准了id为:'.$id.'的提现请求',



                //'sql' =>  mysql_escape_string($sql),



                'createtime' => time()



            ));



            $this->show_message('操作成功');



        }else{



            $this->show_warning('操作失败');



        }



    }







    function cancle(){



        $user = $this->visitor->info;



        $id = intval($_GET['id']);



        $this->model = & m();



        $deposit = $this->model->table('sgxt_deposit')->where('deid='.$id)->find1();



        if(!$deposit){



            $this->show_warning('提现记录不存在');



            exit;



        }



        $sql = "update ecm_sgxt_deposit set ispay=2,operatortime='".time()."',operatorid=".$user['user_id'].",operatorname='".$user['real_name']."' where deid={$id}";



        $rs = $this->model->query($sql);



        if($rs){



            if($deposit['type'] == 1){



                $sql = "update ecm_epay set balance=balance+".$deposit['money']." where user_id=".$deposit['userid'];



            }else if($deposit['type'] == 2){



                $sql = "update ecm_epay set earnings=earnings+".$deposit['money']." where user_id=".$deposit['userid'];



            }



            $this->model->query($sql);



            $this->model->table('sgxt_oplog')->add(array(



                'obj_id' => $id,



                'obj_type' => 'deposit',



                'opid' => $user['user_id'],



                'info' => '管理员'.$user['true_name'].'驳回了id为:'.$id.'的提现请求',



                //'sql' =>  mysql_escape_string($sql),



                'createtime' => time()



            ));



            $this->show_message('操作成功');



        }else{



            $this->show_warning('操作失败');



        }



    }







    



    function get_status($status) {



        switch ($status) {



            case 0:



                $msg = '已取消';



                break;



            case 10:



                $msg = '发货中';



                break;



            case 11:



                $msg = '待付款';



                break;



            case 20:



                $msg = '待发货';



                break;



            case 30:



                $msg = '已发货';



                break;



            case 40:



                $msg = '交易成功';



                break;



            default:



                break;



        }



        return $msg;



    }







    /**



     *    查看



     *



     *    @author    Garbin



     *    @param    none



     *    @return    void



     */



    function view()



    {



        $order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;



        if (!$order_id)



        {



            $this->show_warning('no_such_order');







            return;



        }







        /* 获取订单信息 */



        $model_order =& m('order');



        $order_info = $model_order->get(array(



            'conditions'    => $order_id,



            'join'          => 'has_orderextm',



            'include'       => array(



                'has_ordergoods',   //取出订单商品



            ),



        ));







        if (!$order_info)



        {



            $this->show_warning('no_such_order');



            return;



        }



        $order_type =& ot($order_info['extension']);



        $order_detail = $order_type->get_order_detail($order_id, $order_info);



        $order_info['group_id'] = 0;



        if ($order_info['extension'] == 'groupbuy')



        {



            $groupbuy_mod =& m('groupbuy');



            $groupbuy = $groupbuy_mod->get(array(



                'fields' => 'groupbuy.group_id',



                'join' => 'be_join',



                'conditions' => "order_id = {$order_info['order_id']} ",



                )



            );



            $order_info['group_id'] = $groupbuy['group_id'];



        }



        foreach ($order_detail['data']['goods_list'] as $key => $goods)



        {



            if (substr($goods['goods_image'], 0, 7) != 'http://')



            {



                $order_detail['data']['goods_list'][$key]['goods_image'] = SITE_URL . '/' . $goods['goods_image'];



            }



        }



        $this->assign('order', $order_info);



        $this->assign($order_detail['data']);



        $this->display('order.view.html');



    }



}



?>



