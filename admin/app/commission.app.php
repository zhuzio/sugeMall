<?php

/**
 *    佣金记录控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class CommissionApp extends BackendApp
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
            'mobile'   => Lang::get('mobile'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'mobile';

        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];

        if($_GET['field'] == 'mobile' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("phone_mob='".$_GET['search_name']."'")->find1();
            if($user){
                $_GET['userid'] = $user['user_id'];
            }else{
                $this->show_warning('手机号码不存在');
            }
        }

        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'toid',
                'equal' => '=',
                'name'  =>  'userid',
                'type'  => 'numeric',
            ),array(
                'field' => 'type',
                'equal' => '=',
                'name'  =>  'type',
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
                'field' => 'money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
                'name'  => 'order_amount_to',
                'equal' => '<=',
                'type'  => 'numeric',
            ),
        ));
        //$model_order =& m();

        //var_dump($conditions);exit;
        $page   =   $this->_get_page(10);    //获取分页信息
        //更新排序
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'createtime';
             $order = 'desc';
            }
        }
        else
        {
            $sort  = 'createtime';
            $order = 'desc';
        }
        $totalMoney = 0;
        $rs = $this->model->query('select sum(money) as totalmoney from `ecm_sgxt_commission` where 1=1 '.$conditions);

        $orders = $this->model->table('sgxt_commission')->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        $uids = $userList = array();
        foreach($orders as $key=>$val){
            if(!in_array($val['fromid'],$uids)){
                $uids[] = $val['fromid'];
            }
            if(!in_array($val['toid'],$uids)){
                $uids[] = $val['toid'];
            }
        }
        if(count($uids) > 0){
            $users = $this->model->query('select * from ecm_member where user_id in ('.implode(',',$uids).')');
            foreach($users as $key=>$val){
                $userList[$val['user_id']] = $val;
            }
        }
        $commissionType = conf('commission_type');
        foreach($orders as $key=>$val){
            $val['ispay_cn'] = conf('deposit_status/'.$val['ispay']);
            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $user = $userList[$val['toid']];
            $val['mobile'] = $user['phone_mob'];
            $val['truename'] = $user['real_name'];
            $val['type_cn'] = $commissionType[$val['type']];
            $orders[$key] = $val;
        }

        $page['item_count'] = $this->model->table('sgxt_commission')->getCount();   //获取统计的数据
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('order_status_list', $commissionType);
        if($rs){
            $totalMoney = $rs[0]['totalmoney'];
        }
        $this->assign('totalMoney',$totalMoney);
        $this->assign('search_options', $search_options);
        $this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('orders', $orders);
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
                                      'style'=> 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->display('commission.index.html');
    }
    
    /**
     * 订单导出
     */
    function export()
    {
        $search_options = array(
            'mobile'   => Lang::get('mobile'),
            'truename'   => Lang::get('buyer_name'),
        );
        /* 默认搜索的字段是店铺名 */
        $field = 'seller_name';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $field,       //按用户名,店铺名,支付方式名称进行搜索
                'equal' => 'LIKE',
                'name'  => 'search_name',
            ),array(
                'field' => 'ispay',
                'equal' => '=',
                'name'  =>  'status',
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
                'field' => 'money',
                'name'  => 'order_amount_from',
                'equal' => '>=',
                'type'  => 'numeric',
            ),array(
                'field' => 'money',
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
        if (isset($_GET['sort']) && isset($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'createtime';
                $order = 'desc';
            }
        }
        else
        {
            $sort  = 'createtime';
            $order = 'desc';
        }
        $totalMoney = 0;
        $rs = $this->model->query('select sum(money) as totalmoney from `ecm_sgxt_deposit` where 1=1 '.$conditions);
        $orders = $this->model->table('sgxt_deposit')->find(array(
            'conditions'    => '1=1 ' . $conditions,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "$sort $order",
            'count'         => true             //允许统计
        )); //找出所有商城的合作伙伴
        foreach($orders as $key=>$val){
            $val['ispay_cn'] = conf('deposit_status/'.$val['ispay']);
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
        $cols_item[] = '编号';
        $cols_item[] = '姓名';
        $cols_item[] = '手机';
        $cols_item[] = '提现金额';
        $cols_item[] = '提现状态';
        $cols_item[] = '提现时间';

        $cols[] = $cols_item;

        if (is_array($orders) && count($orders) > 0) {
            foreach ($orders as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['deid'];
                $tmp_col[] = $v['truename'];
                $tmp_col[] = $v['mobile'];
                $tmp_col[] = $v['money'];
                $tmp_col[] = $v['ispay_cn'];
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
