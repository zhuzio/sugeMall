<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Order_offlineApp extends BackendApp
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
        $this->model = & m();
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'seller_id' => '卖家手机号',
			'buyer_id'   => '买家手机号',
            'buyer_name'   => Lang::get('buyer_name'),
//            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        $mobile = '';
		if($_GET['field']=='buyer_id'){
			$user_name=$this->model->table('member')->field('user_id')->where('user_name=\''.$_GET['search_name'].'\' or user_id='.$_GET['search_name'])->find1();
			$_GET['search_name']=$user_name['user_id'];
			$mobile=$_GET['search_name'];
		}
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        if($_GET['field'] == 'seller_id'){
            $mobile = $_GET['search_name'];
            $user = $this->model->table('member')->where('user_name=\''.$_GET['search_name'].'\' or user_id='.$_GET['search_name'])->find1();
            if($user){
                $_GET['search_name'] = $user['user_id'];
                //$field = 'seller_id';
            }
        }

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => '=',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'add_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'point',
                'name'  => 'point_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'point',
                'name'  => 'point_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'payment_id',
                'equal' => '=',
                'type'  => 'numeric',
            )

        ));
        if($field=='buyer_id' || $field=='seller_id')
        {
            $_GET['search_name'] = $mobile;
        }

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
        $orders = $this->model->table('order_offline')->find(array(
            'conditions'    => 'is_check in (0,1) ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        /*$orders_amount = $this->model->table('order_offline')->find(array(
            'conditions'    => '1=1 ' . $conditions,
            //'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        /*foreach($orders_amount as $key=>$val){
        	$total_amount += $val['order_amount'];
        }*/
        $total_amount = 0;
        $sgxt_area_arr=array();
        $sgxt_area=$this->model->table('sgxt_area')->field('id,name')->select();
        foreach($sgxt_area as $akey =>$avalue)
        {
            $sgxt_area_arr[$avalue['id']]=$avalue['name'];
        }
        foreach($orders as $key=>$val){
        	$total_amount += $val['order_amount'];
            $user = $this->model->table('member')->where('user_id='.$val['seller_id'])->find1();
            $store = $this->model->table('store')->where('store_id='.$val['seller_id'])->find1();
            $buyer = $this->model->table('member')->where('user_id='.$val['buyer_id'])->find1();
            $val['buyer_address']=$sgxt_area_arr[$buyer['province']].$sgxt_area_arr[$buyer['city']].$sgxt_area_arr[$buyer['area']];
            $val['buyer_mobile'] = $buyer['user_name'];
            $val['user_name'] = $user['user_name'];
            $val['region_name'] = $store['region_name'];
            $val['s_point'] = doubleval($val['point']) * 0.3;
            $val['s_point'] = sprintf("%.2f", $val['s_point']);
            $val['p_point'] = doubleval($val['point']) - $val['s_point'];
            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
			if(empty($val['check_time']))
            {
                $val['check_time'] = '未审核';
            }else
            {
                $val['check_time'] = date('Y-m-d H:i:s',$val['check_time']);
            }
            $val['pay_style'] = '';
            if($val['payment_id'] == 3){
                $val['pay_style'] = '购物积分支付';
            }else if($val['payment_id'] == 9){
                $val['pay_style'] = '商家发送积分';
            }
            $orders[$key] = $val;
        }
        $page['item_count'] = $this->model->table('order_offline')->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('total_amount',$total_amount);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', array(
            ORDER_PENDING => Lang::get('order_pending'),
            ORDER_SUBMITTED => Lang::get('order_submitted'),
            ORDER_ACCEPTED => Lang::get('order_accepted'),
            ORDER_SHIPPED => Lang::get('order_shipped'),
            ORDER_FINISHED => Lang::get('order_finished'),
            ORDER_CANCELED => Lang::get('order_canceled'),
        ));
        $this->assign('order_payment_list',array(
           3 => '购物积分支付',
            9 => '商家发送积分'
        ));
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('order_offline.index.html');
    }
    
/**
     * 订单导出
     */
    function export()
    {
        $this->model = & m();
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'seller_id' => '卖家手机号',
            'buyer_id'   => '买家手机号',
            'buyer_name'   => Lang::get('buyer_name'),
//            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        $mobile = '';
        if($_GET['field']=='buyer_id'){
            $user_name=$this->model->table('member')->field('user_id')->where('user_name=\''.$_GET['search_name'].'\' or user_id='.$_GET['search_name'])->find1();
            $_GET['search_name']=$user_name['user_id'];
            $mobile=$_GET['search_name'];
        }
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        if($_GET['field'] == 'seller_id'){
            $mobile = $_GET['search_name'];
            $user = $this->model->table('member')->where('user_name=\''.$_GET['search_name'].'\' or user_id='.$_GET['search_name'])->find1();
            if($user){
                $_GET['search_name'] = $user['user_id'];
                //$field = 'seller_id';
            }
        }

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => '=',
                'name'  => 'search_name',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'type'  => 'numeric',
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
                'field' => 'order_amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'order_amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'point',
                'name'  => 'point_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'point',
                'name'  => 'point_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'payment_id',
                'equal' => '=',
                'type'  => 'numeric',
            )

        ));
        $_GET['search_name'] = $mobile;
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
        $orders = $this->model->table('order_offline')->find(array(
            'conditions'    => 'is_check in (0,1) ' . $conditions,
            //'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        ));
        $total_amount = 0;
        foreach($orders as $key=>$val){
            $total_amount += $val['order_amount'];
            $user = $this->model->table('member')->where('user_id='.$val['seller_id'])->find1();
            $store = $this->model->table('store')->where('store_id='.$val['seller_id'])->find1();
            $buyer = $this->model->table('member')->where('user_id='.$val['buyer_id'])->find1();
            $val['buyer_name'] = $buyer['real_name'];
            $val['buyer_mobile'] = $buyer['user_name'];
            $val['user_name'] = $user['user_name'];
            $val['region_name'] = $store['region_name'];
            $val['s_point'] = doubleval($val['point']) * 0.3;
            $val['s_point'] = sprintf("%.2f", $val['s_point']);
            $val['p_point'] = doubleval($val['point']) - $val['s_point'];
            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
            if(empty($val['check_time']))
            {
                $val['check_time'] = '未审核';
            }else
            {
                $val['check_time'] = date('Y-m-d H:i:s',$val['check_time']);
            }
            $val['pay_style'] = '';
            if($val['payment_id'] == 3){
                $val['pay_style'] = '购物积分支付';
            }else if($val['payment_id'] == 9){
                $val['pay_style'] = '商家发送积分';
            }
            $orders[$key] = $val;
        }
        
        
        
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$orders) {
            $this->show_warning('无数据');
            return;
        }

        $cols = array();
        $cols_item = array();
        $cols_item[] = '店铺名称';
        $cols_item[] = '所属区域';
        $cols_item[] = '商户手机';
        $cols_item[] = '订单号';
        $cols_item[] = '下单时间';
        $cols_item[] = '买家名称';
        $cols_item[] = '买家手机';
        $cols_item[] = '交易明细';
        $cols_item[] = '订单总价';
        $cols_item[] = '赠送积分';
        $cols_item[] = '商家赠送';
        $cols_item[] = '平台赠送';
        $cols_item[] = '支付方式';
        $cols_item[] = '订单状态';
        $cols_item[] = '审核时间';

        $cols[] = $cols_item;

        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['seller_name'];
                $tmp_col[] = $v['region_name'];
                $tmp_col[] = $v['user_name'];
                $tmp_col[] = $v['order_sn'];
                $tmp_col[] = $v['add_time'];
                $tmp_col[] = $v['buyer_name'];
                $tmp_col[] = $v['buyer_mobile'];
                $tmp_col[] = $v['pay_message'];
                $tmp_col[] = $v['order_amount'];
                $tmp_col[] = $v['point'];
                $tmp_col[] = $v['s_point'];
                $tmp_col[] = $v['p_point'];
                $tmp_col[] = $v['payment_name'];
                $tmp_col[] = $this->get_status($v['status']);
                $tmp_col[] = $v['check_time'];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();
        
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
        $this->model = &m();
        $order_info = $this->model->table('order_offline')->where('order_id='.$order_id)->find1();

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

    public function updateShopRegion(){
        $model = &m();
        $shopList = $model->table('store')->where('province=city and province>1')->select();        
        $uids = $userList = $plist = $clist = $alist = $provinceList = $cityList = $areaList = array();
        foreach($shopList as $key=>$val){
            $uids[] = $val['store_id'];
        }

        $ulist = $model->table('member')->where('user_id in ('.implode(',', $uids).')')->select();

        foreach ($ulist as $key => $value) {
            $userList[$value['user_id']] = $value;
        }       
        
        foreach($userList as $key=>$val){
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
        
        $prolist = $model->table('sgxt_area')->where('id in ('.implode(',', $plist).')')->select();
        $citlist = $model->table('sgxt_area')->where('id in ('.implode(',', $clist).')')->select();
        $arelist = $model->table('sgxt_area')->where('id in ('.implode(',', $alist).')')->select();        

        foreach($prolist as $k=>$v){
            $provinceList[$v['id']] = $v;
        }
        foreach($citlist as $k=>$v){
            $cityList[$v['id']] = $v;
        }
        foreach($arelist as $k=>$v){
            $areaList[$v['id']] = $v;
        }

        foreach($shopList as $key=>$val){            
            $user = $userList[$val['store_id']];
            $region_name = $provinceList[$user['province']]['name'] . ' ' . $cityList[$user['city']]['name'] . ' ' . $areaList[$user['area']]['name'];
            $model->table('store')->where('store_id='.$val['store_id'])->save(array(
                'province' => $user['province'],
                'city' => $user['city'],
                'area' => $user['area'],
                'region_name' => $region_name,
            ));
            //$model->table('store')->where('store_id='.$val['store_id'])->save(array('region_name'=>$region_name));
        }
    }
}
?>
