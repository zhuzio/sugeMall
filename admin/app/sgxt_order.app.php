<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Sgxt_orderApp extends BackendApp
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
            'mobile' => '手机号码',
			'truename'   => '真实姓名',
            'orderid'   => '订单号',
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'mobile';
        $mobile = '';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'sgxt_order.'.$field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'paytype',
                'equal' => 'LIKE',
                'name'  => 'paytype',
            ),array(
                'field' => 'pay_createtime',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'pay_createtime',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time',
            ),array(
                'field' => 'amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'member.province',
                'name'  => 'province',
                'equal' => '=',
            ),array(
                'field' => 'member.city',
                'name'  => 'city',
                'equal' => '=',
            ),array(
                'field' => 'member.area',
                'name'  => 'area',
                'equal' => '=',
            )
        ));
        //$_GET['search_name'] = $mobile;
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'pay_createtime';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'pay_createtime';
            $order = 'desc';
        }
        //找出所有商城的合作伙伴
        $sgxt_order_mod =& m('sgxt_order');
        $orders = $sgxt_order_mod->find(array(
            'fields'   => 'sgxt_order.*,member.province as province,member.city as city,member.area as area',
            'conditions'    => 'sgxt_order.status=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'join'    => 'belongs_to_user',
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        ));
        $total_amount = 0;
        $paytypearr=array('wx'=>'微信支付','ll'=>'连连支付','llpay'=>'连连支付','balance'=>'货款支付','balance2'=>'货款支付','reapal'=>'融宝支付','allinpay'=>'通联支付');
        foreach($orders as $key=>$val){
            //添加店铺
            $storeinfo=$this->model->table('store')->where('store_id='.$val['userid'])->find1();
            $val['store_name']=$storeinfo['store_name'];
            //地区
            $province = $this->model->table('sgxt_area')->where('id='.$val['province'])->find1();
            $city = $this->model->table('sgxt_area')->where('id='.$val['city'])->find1();
            $area = $this->model->table('sgxt_area')->where('id='.$val['area'])->find1();
            $val['region_name'] = $province['name'] . ' ' . $city['name'] . ' ' . $area['name'];
            $val['pay_createtime']=date('Y-m-d H:i:s',$val['pay_createtime']);
            $val['pay_style'] =$paytypearr[$val['paytype']];
            $orders[$key] = $val;
        }
        $sql='select sum(amount) as total_amount from ecm_sgxt_order sgxt_order join ecm_member member on member.user_id=sgxt_order.userid where sgxt_order.status=1 '.$conditions;
        $totalinfo=$sgxt_order_mod->query($sql);
        $total_amount=$totalinfo[0]['total_amount'];
        //echo $this->model->getLastSql();
        $page['item_count'] = $sgxt_order_mod->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('total_amount',$total_amount);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_payment_list',array(
           'wx' => '微信支付',
            'll' => '连连支付',
            'balance'=>'货款支付',
            'reapal' => '融宝支付',
            'allinpay' => '通联支付'
        ));
        $plist = $this->model->table('sgxt_area')->where('parent_id=1')->select();
        $cityList = $areaList = $provinceList = array();
        if($this->searchQuery['city']){
            $city = $this->model->table('sgxt_area')->where('id='.$this->searchQuery['city'])->find1();
            $clist = $this->model->table('sgxt_area')->where('parent_id='.$city['parent_id'])->select();
            foreach($clist as $key=>$val){
                $cityList[$val['id']] = $val['name'];
            }
            $this->assign('cityList',$cityList);
        }
        if($this->searchQuery['area']){
            $area = $this->model->table('sgxt_area')->where('id='.$this->searchQuery['area'])->find1();
            $alist = $this->model->table('sgxt_area')->where('parent_id='.$area['parent_id'])->select();
            foreach($alist as $key=>$val){
                $areaList[$val['id']] = $val['name'];
            }
            $this->assign('areaList',$areaList);
        }
        foreach($plist as $key=>$val){
            $provinceList[$val['id']] = $val['name'];
        }
        $this->assign('provinceList',$provinceList);
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('sgxt_order.index.html');
    }
    
/**
     * 订单导出
     */
    function export()
    {
        $this->model = & m();
        $search_options = array(
            'mobile' => '手机号码',
            'truename'   => '真实姓名',
            'orderid'   => '订单号',
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'mobile';
        $mobile = '';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'sgxt_order.'.$field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'paytype',
                'equal' => 'LIKE',
                'name'  => 'paytype',
            ),array(
                'field' => 'pay_createtime',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'pay_createtime',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time',
            ),array(
                'field' => 'amount',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'amount',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),array(
                'field' => 'member.province',
                'name'  => 'province',
                'equal' => '=',
            ),array(
                'field' => 'member.city',
                'name'  => 'city',
                'equal' => '=',
            ),array(
                'field' => 'member.area',
                'name'  => 'area',
                'equal' => '=',
            )
        ));
        //$_GET['search_name'] = $mobile;
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'pay_createtime';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'pay_createtime';
            $order = 'desc';
        }
        $sgxt_order_mod =& m('sgxt_order');
        $orders = $sgxt_order_mod->find(array(
            'fields'   => 'sgxt_order.*,member.province as province,member.city as city,member.area as area',
            'conditions'    => 'sgxt_order.status=1 ' . $conditions,
            'join'    => 'belongs_to_user',
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        ));//找出所有商城的合作伙伴
        $total_amount = 0;
        $paytypearr=array('wx'=>'微信支付','ll'=>'连连支付','llpay'=>'连连支付','balance'=>'货款支付','balance2'=>'货款支付','reapal'=>'融宝支付','allinpay'=>'通联支付');
        foreach($orders as $key=>$val){
            $total_amount += $val['amount'];
            //添加店铺
            $storeinfo=$this->model->table('store')->where('store_id='.$val['userid'])->find1();
            $val['store_name']=$storeinfo['store_name'];
            //地区
            $province = $this->model->table('sgxt_area')->where('id='.$val['province'])->find1();
            $city = $this->model->table('sgxt_area')->where('id='.$val['city'])->find1();
            $area = $this->model->table('sgxt_area')->where('id='.$val['area'])->find1();
            $val['region_name'] = $province['name'] . ' ' . $city['name'] . ' ' . $area['name'];
            $val['pay_createtime']=date('Y-m-d H:i:s',$val['pay_createtime']);
            $val['pay_style'] =$paytypearr[$val['paytype']];
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
        $cols_item[] = '订单号';
        $cols_item[] = '店铺名称';
        $cols_item[] = '真实姓名';
        $cols_item[] = '手机号码';
        $cols_item[] = '购积分金额';
        $cols_item[] = '购买方式';
        $cols_item[] = '购买时间';
        $cols_item[] = '省市县';

        $cols[] = $cols_item;

        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['orderid'];
                $tmp_col[] = $v['store_name'];
                $tmp_col[] = $v['truename'];
                $tmp_col[] = $v['mobile'];
                $tmp_col[] = $v['amount'];
                $tmp_col[] = $v['pay_style'];
                $tmp_col[] = $v['pay_createtime'];
                $tmp_col[] = $v['region_name'];
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
