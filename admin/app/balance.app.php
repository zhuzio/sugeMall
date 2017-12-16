<?php

/**
 *    合作伙伴控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class BalanceApp extends BackendApp
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
            'user_id'   => '用户编号',
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'user_name';

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'source_type',
                'equal' => '=',
                'name'  =>  'source_type',
                'type'  => 'numeric',
            ),array(
                'field' => 'createtime',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'createtime',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'real_point',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'real_point',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        //$model_order =& m();
        $this->model = & m();
        //var_dump($model_order);exit;
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
             $sort  = 'createtime';
             $order = 'desc';
            }
        }else{
            $sort  = 'createtime';
            $order = 'desc';
        }

        $sourceType = array('1' => '团队奖励购物积分','2' => '购物积分奖励','3' => '市场补贴奖励购物积分',);
        $isClear = array(0 => '未处理',1 => '已处理',2 => '冻结',);
        $totalMoney = 0;
        $rs = $this->model->query('select sum(real_point) as totalmoney from `ecm_sgxt_balance` where 1=1 '.$conditions);
        $orders = $this->model->table('sgxt_balance')->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        foreach($orders as $key=>$val){
            $user = $this->model->table('member')->where('user_id='.$val['user_id'])->find1();
            $val['region_name'] = $this->getRegionName(array($user['province'],$user['city'],$user['area']));
            $val['user_mob'] = $user['user_name'];
            $val['source_type_cn'] = $sourceType[$val['source_type']];
            $val['is_clearing_cn'] = $isClear[$val['is_clearing']];
            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $orders[$key] = $val;
        }

        $page['item_count'] = $this->model->table('sgxt_deposit')->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', $isClear);
        $this->assign('order_type_list', $sourceType);
        if($rs){
            $totalMoney = $rs[0]['totalmoney'];
        }
        $this->assign('totalMoney',$totalMoney);
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('balance.index.html');
    }

    function getRegionName($region){
        $model = &m();
        $list = $model->table('sgxt_area')->where('id in ('.implode(',',$region).')')->select();
        $region_name = array();
        foreach($list as $key=>$val){
            $region_name[] = $val['name'];
        }
        return implode(' ',$region_name);
    }

    /**
     * 订单导出
     */
    function export()
    {
        $search_options = array(
            'user_name'   => '姓名',
            'user_id'   => '用户编号',
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'user_name';

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'source_type',
                'equal' => '=',
                'name'  =>  'source_type',
                'type'  => 'numeric',
            ),array(
                'field' => 'createtime',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'createtime',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            ),array(
                'field' => 'real_point',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'real_point',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        //$model_order =& m();
        $this->model = & m();
        //var_dump($model_order);exit;
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order'])){
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc'))){
                $sort  = 'createtime';
                $order = 'desc';
            }
        }else{
            $sort  = 'createtime';
            $order = 'desc';
        }

        $sourceType = array('1' => '团队奖励购物积分','2' => '购物积分奖励','3' => '市场补贴奖励购物积分',);
        $isClear = array(0 => '未处理',1 => '已处理',2 => '冻结',);
        $totalMoney = 0;
        $rs = $this->model->query('select sum(real_point) as totalmoney from `ecm_sgxt_balance` where 1=1 '.$conditions);
        $orders = $this->model->table('sgxt_balance')->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        foreach($orders as $key=>$val){
            $user = $this->model->table('member')->where('user_id='.$val['user_id'])->find1();
            $val['region_name'] = $this->getRegionName(array($user['province'],$user['city'],$user['area']));
            $val['user_mob'] = $user['user_name'];
            $val['source_type_cn'] = $sourceType[$val['source_type']];
            $val['is_clearing_cn'] = $isClear[$val['is_clearing']];
            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
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
        $cols_item[] = '序号';
        $cols_item[] = '姓名';
        $cols_item[] = '用户名';
        $cols_item[] = '实际积分';
        $cols_item[] = '购物积分';
        $cols_item[] = '幸福积分';
        $cols_item[] = '收益类型';
        $cols_item[] = '收益状态';
        $cols_item[] = '日期';

        $cols[] = $cols_item;

        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $k => $v) {
                $tmp_col = array();
                $tmp_col[] = $v['id'];
                $tmp_col[] = $v['user_name'];
                $tmp_col[] = $v['user_mob'];
                $tmp_col[] = $v['real_point'];
                $tmp_col[] = $v['get_money'];
                $tmp_col[] = $v['happiness'];
                $tmp_col[] = $v['source_type_cn'];
                $tmp_col[] = $v['is_clearing_cn'];
                $tmp_col[] = $v['createtime'];
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
        $pm = &m('paymentlog');
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
            $pmid = 7;
            if($deposit['type'] == 1){
                $pmid = 8;
            }
            $pm->paymentlog($deposit['userid'],$deposit['truename'],$deposit['money'],$pmid);

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
