<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class WealthApp extends BackendApp
{
    /**
     *    平台收益统计管理
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function index()
    {
        ini_set('max-execution-time',1000);
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'province',
                'equal' => '=',
                'name'  =>  'province',
            ),array(
                'field' => 'city',
                'equal' => '=',
                'name'  =>  'city',
            ),array(
                'field' => 'area',
                'equal' => '=',
                'name'  =>  'area',
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
            )
        ));

        $conditions = array();
        $model = &m();
        //获取购买积分收益
        $income = $model->table('paymentlog')->where('payment_id in (2,5)')->sum('money');
        $return['income'] = $income ? $income : 0;
        //获取定返支出信息
        $r = $model->table('sgxt_balance')->where('source_type in (2,3)')->sum('get_money');
        $return['return'] = $r ? $r : 0;
        //获取提现
        $cash = $model->table('paymentlog')->where('payment_id in (7,8)')->sum('money');
        $return['cash'] = $cash ? $cash : 0;

        $province = isset($_GET['province']) ? trim($_GET['province']) : null;
        $city = isset($_GET['city']) ? trim($_GET['city']) : null;
        $area = isset($_GET['area']) ? trim($_GET['area']) : null;
        $from_time = isset($_GET['from_time']) ? strtotime(trim($_GET['from_time'])) : 0;
        $to_time = trim($_GET['to_time']) ? strtotime(trim($_GET['to_time'])) : 0;
        $shops = $allUsers = array();
        //区域下商家总购买积分收益
        $areaIncome = 0;
        $areaPay = 0;
        $areaCash = 0;
        if($area){
            $shops = $model->table('member')->where('area='.$area.' and type=2')->select();
            $allUsers = $model->table('member')->where('area='.$area.' and type<3')->select();
        }else{
            if($city){
                $shops = $model->table('member')->where('city='.$city.' and type=2')->select();
                $allUsers = $model->table('member')->where('city='.$city.' and type<3')->select();
            }elseif($province){
                $shops = $model->table('member')->field('user_id')->where('province='.$province.' and type=2')->select();
                $allUsers = $model->table('member')->field('user_id')->where('province='.$province.' and type<3')->select();
            }
        }
        $shopids = $uids = array();
        foreach($shops as $key=>$val){
            if(!in_array($val['user_id'],$shopids)){
                $shopids[] = $val['user_id'];
            }
        }
        foreach($allUsers as $key=>$val){
            if(!in_array($val['user_id'],$uids)){
                $uids[] = $val['user_id'];
            }
        }
        $cdn = '';
        $ccdn = '';
        if($from_time > 0){
            $cdn .= ' and add_time>'.$from_time;
            $ccdn .= ' and createtime>'.$from_time;
        }
        if($to_time > 0){
            $cdn .= ' and add_time<'.$to_time;
            $ccdn .= ' and createtime>'.$to_time;
        }
        if(count($shopids) > 0){
            $areaIncome = $model->table('paymentlog')->where('user_id in ('.implode(',',$shopids).') and payment_id in (2,5)'.$cdn )->sum('money');
            $areaCash = $model->table('paymentlog')->where('user_id in ('.implode(',',$shopids).') and payment_id in (7,8)'.$cdn)->sum('money');
        }
        print_r(count($uids));
        if(count($uids) > 0){
            $areaPay = $model->table('sgxt_balance')->where('user_id in ('.implode(',',$uids).') and source_type=2'.$ccdn )->sum('get_money');
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

        $return['area_income'] = $areaIncome;
        $return['area_pay'] = $areaPay;
        $return['area_cash'] = $areaCash;
        $this->assign('statistics',$return);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('wealth.index.html');
    }
    
/**
     * 订单导出
     */
    function export()
    {
        $search_options = array(
            'seller_name'   => Lang::get('store_name'),
            'buyer_name'   => Lang::get('buyer_name'),
            'payment_name'   => Lang::get('payment_name'),
            'order_sn'   => Lang::get('order_sn'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
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
            ),
        ));
        
        $model_order =& m('order');
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
        $orders = $model_order->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'order'         => "$sort $order",
            'join'=>'has_orderextm',
        )); //找出所有商城的合作伙伴
        
        
        
        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$orders) {
            $this->show_warning('无数据');
            return;
        }

        $cols = array();
        $cols_item = array();
        $cols_item[] = '订单编号';
        $cols_item[] = '店铺名称';
        $cols_item[] = '消费者名称';
        $cols_item[] = '消费者邮箱';
        $cols_item[] = '订单状态';
        $cols_item[] = '下单时间';
        $cols_item[] = '支付方式';
        $cols_item[] = '付款时间';
        $cols_item[] = '发货时间';
        $cols_item[] = '快递单号';
        $cols_item[] = '完成时间';
        $cols_item[] = '商品总价';
        $cols_item[] = '折扣';
        $cols_item[] = '订单总价';
        $cols_item[] = '付款留言';
        $cols_item[] = '收货地区';
        $cols_item[] = '收货地址';
        $cols_item[] = '邮编';
        $cols_item[] = '电话';
        $cols_item[] = '手机';
        $cols_item[] = '快递方式';
        $cols_item[] = '快递费用';

        $cols[] = $cols_item;

        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['order_sn'];
                $tmp_col[] = $v['seller_name'];
                $tmp_col[] = $v['buyer_name'];
                $tmp_col[] = $v['buyer_email'];
                $tmp_col[] = $this->get_status($v['status']);
                $tmp_col[] = local_date('Y-m-d H:i:s', $v['add_time']);
                $tmp_col[] = $v['payment_name'];
                $tmp_col[] = local_date('Y-m-d H:i:s', $v['pay_time']);
                $tmp_col[] = local_date('Y-m-d H:i:s', $v['ship_time']);
                $tmp_col[] = $v['invoice_no'];
                $tmp_col[] = local_date('Y-m-d H:i:s', $v['finished_time']);
                $tmp_col[] = $v['goods_amount'];
                $tmp_col[] = $v['discount'];
                $tmp_col[] = $v['order_amount'];
                $tmp_col[] = $v['postscript'];
                $tmp_col[] = $v['region_name'];
                $tmp_col[] = $v['address'];
                $tmp_col[] = $v['zipcode'];
                $tmp_col[] = $v['phone_tel'];
                $tmp_col[] = $v['phone_mob'];
                $tmp_col[] = $v['shipping_name'];
                $tmp_col[] = $v['shipping_fee'];
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
