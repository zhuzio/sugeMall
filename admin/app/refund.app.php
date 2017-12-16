<?php

/**
 *    退款维权管理员控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class RefundApp extends BackendApp {

    var $_order_mod;
    var $_order_log_mod;
    var $_order_extm_mod;
    var $_goods_mod;
    var $_ordergoods_mod;
    var $_refund_mod;
    var $_store_mod;
    var $_member_mod;
    var $_refund_message_mod;

    function __construct() {
        $this->RefundApp();
    }

    function RefundApp() {
        parent::__construct();
        $this->_order_mod = &m('order');
        $this->_order_log_mod = &m('orderlog');
        $this->_order_extm_mod = &m('orderextm');
        $this->_goods_mod = &m('goods');
        $this->_ordergoods_mod = &m('ordergoods');
        $this->_refund_mod = &m('refund');
        $this->_store_mod = &m('store');
        $this->_member_mod = &m('member');
        $this->_refund_message_mod = &m('refund_message');
    }

    function index() {
        $sort_order = str_replace('_', ' ', trim($_GET['sort_order']));
        if (!empty($sort_order)) {
            $order = $sort_order . ',created desc';
        } else {
            $order = 'created desc';
        }
        $conditions='1=1';
        $ask_customer = trim($_GET['ask_customer']);
        if ($ask_customer == 'yes' || $ask_customer == 'no') {
            $ask_customer = $ask_customer == 'yes' ? 1 : 0;
            $conditions .= ' and ask_customer=' . $ask_customer;
        } elseif ($ask_customer == 'all') {
            $conditions .= '';
        } else {
            //$conditions = 'and ask_customer=1';
            $conditions .= '';
        }
        //订单号：
        $order_sn = trim($_GET['order_sn']);
        if(!empty($order_sn))
        {
            //根据订单号查询订单id
            $orderinfo=$this->_order_mod->get(array('conditions' => 'order_sn=' . $order_sn, 'fields' => 'order_id'));
            $conditions.=' and order_id='.$orderinfo['order_id'];
        }
        //时间
        $add_time_from = trim($_GET['add_time_from']);
        $add_time_to = trim($_GET['add_time_to']);
        if(!empty($add_time_from))
        {
            $conditions.=' and created>='.strtotime($add_time_from);
        }
        if(!empty($add_time_to))
        {
            $conditions.=' and created<'.strtotime($add_time_to);
        }
        //echo $conditions;die;
        $page = $this->_get_page(10);   //获取分页信息
        $refunds = $this->_refund_mod->find(array(
            'conditions' => $conditions,
            'limit' => $page['limit'],
            'order' => $order,
            'count' => true
        ));
        //var_dump($refunds);die;
        $page['item_count'] = $this->_refund_mod->getCount();

        foreach ($refunds as $key => $refund) {
            $store = $this->_store_mod->get(array('conditions' => 'store_id=' . $refund['seller_id'], 'fields' => 'store_name,owner_name'));
            $refunds[$key]['store_name'] = $store['store_name'];
            $refunds[$key]['seller_name'] = $store['owner_name'];

            $member = $this->_member_mod->get(array('conditions' => 'user_id=' . $refund['buyer_id'], 'fields' => 'user_name'));
            $refunds[$key]['buyer_name'] = $member['user_name'];
            $goods = $this->_goods_mod->get(array('conditions' => 'goods_id=' . $refund['goods_id'], 'fields' => 'goods_name'));
            $refunds[$key]['goods_name'] = $goods['goods_name'];

            $order = $this->_order_mod->get(array('conditions' => 'order_id=' . $refund['order_id'], 'fields' => 'order_sn,payment_id,payment_name'));
            $refunds[$key]['order_sn'] = $order['order_sn'];
            $refunds[$key]['payment_id'] = $order['payment_id'];
            $refunds[$key]['payment_name'] = $order['payment_name'];
            $refunds[$key]['created']=date('Y-m-d H:i:s',$refund['created']);
        }
        $this->import_resource(array('script' => 'inline_edit.js,jquery.ui/jquery.ui.js,jquery.ui/i18n/' . i18n_code() . '.js',
            'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css'));
        $this->_format_page($page);
        $this->assign('page_info', $page);

        $this->assign('refunds', $refunds);
        $this->display('refund.index.html');
    }

    /**
     * 退款记录导出
     */
    function export()
    {
        $sort_order = str_replace('_', ' ', trim($_GET['sort_order']));
        if (!empty($sort_order)) {
            $order = $sort_order . ',created desc';
        } else {
            $order = 'created desc';
        }
        $conditions='1=1';
        $ask_customer = trim($_GET['ask_customer']);
        if ($ask_customer == 'yes' || $ask_customer == 'no') {
            $ask_customer = $ask_customer == 'yes' ? 1 : 0;
            $conditions .= ' and ask_customer=' . $ask_customer;
        } elseif ($ask_customer == 'all') {
            $conditions .= '';
        } else {
            //$conditions = 'and ask_customer=1';
            $conditions .= '';
        }
        //订单号：
        $order_sn = trim($_GET['order_sn']);
        if(!empty($order_sn))
        {
            //根据订单号查询订单id
            $orderinfo=$this->_order_mod->get(array('conditions' => 'order_sn=' . $order_sn, 'fields' => 'order_id'));
            $conditions.=' and order_id='.$orderinfo['order_id'];
        }
        //时间
        $add_time_from = trim($_GET['add_time_from']);
        $add_time_to = trim($_GET['add_time_to']);
        if(!empty($add_time_from))
        {
            $conditions.=' and created>='.strtotime($add_time_from);
        }
        if(!empty($add_time_to))
        {
            $conditions.=' and created<'.strtotime($add_time_to);
        }
        //echo $conditions;die;
        //$page = $this->_get_page(10);   //获取分页信息
        $refunds = $this->_refund_mod->find(array(
            'conditions' => $conditions,
           // 'limit' => $page['limit'],
            'order' => $order,
            'count' => true
        ));
        //var_dump($refunds);die;
        //$page['item_count'] = $this->_refund_mod->getCount();
        $refund_status_array=array('WAIT_SELLER_AGREE'=>'买家已经申请退款，等待卖家同意','SELLER_REFUSE_BUYER'=>'买家申请退款，卖家拒绝退款','CLOSED'=>'买家撤销退款，退款完成','SUCCESS'=>'卖家同意退款，退款完成');
        foreach ($refunds as $key => $refund) {
            $store = $this->_store_mod->get(array('conditions' => 'store_id=' . $refund['seller_id'], 'fields' => 'store_name,owner_name'));
            $refunds[$key]['store_name'] = $store['store_name'];
            $refunds[$key]['seller_name'] = $store['owner_name'];

            $member = $this->_member_mod->get(array('conditions' => 'user_id=' . $refund['buyer_id'], 'fields' => 'user_name'));
            $refunds[$key]['buyer_name'] = $member['user_name'];
            $goods = $this->_goods_mod->get(array('conditions' => 'goods_id=' . $refund['goods_id'], 'fields' => 'goods_name'));
            $refunds[$key]['goods_name'] = $goods['goods_name'];

            $order = $this->_order_mod->get(array('conditions' => 'order_id=' . $refund['order_id'], 'fields' => 'order_sn,payment_name,pay_time'));
            $refunds[$key]['order_sn'] = $order['order_sn'];
            $refunds[$key]['payment_name'] = $order['payment_name'];
            $refunds[$key]['pay_time'] = date('Y-m-d H:i:s',$order['pay_time']);
            $refunds[$key]['created']=date('Y-m-d H:i:s',$refund['created']);
            if(!empty($refund['end_time']))
            {
                $refunds[$key]['end_time']=date('Y-m-d H:i:s',$refund['end_time']);
            }
            else
            {
                $refunds[$key]['end_time']='';
            }
            $refunds[$key]['status']=$refund_status_array[$refund['status']];
        }

        import('excelwriter.lib');
        $excel = new ExcelWriter('utf8', 'toexcel');
        if (!$refunds) {
            $this->show_warning('无数据');
            return;
        }
        $ask_customer_array=array('0'=>'否','1'=>'是');
        $cols = array();
        $cols_item = array();
        $cols_item[] = '退款编号';
        $cols_item[] = '订单编号';
        $cols_item[] = '宝贝信息';
        $cols_item[] = '买家';
        $cols_item[] = '卖家';
        $cols_item[] = '交易金额';
        $cols_item[] = '退款金额';
        $cols_item[] = '退运费';
        $cols_item[] = '申请时间';
        $cols_item[] = '退款状态';
        $cols_item[] = '客服介入';
        $cols_item[] = '支付方式';
        $cols_item[] = '下单时间';
        $cols_item[] = '退款处理时间';
        $cols[] = $cols_item;

        if (is_array($refunds) && count($refunds) > 0) {
            foreach ($refunds as $k => $v) {

                $tmp_col = array();
                $tmp_col[] = $v['refund_sn'];
                $tmp_col[] = $v['order_sn'];
                $tmp_col[] = $v['goods_name'];
                $tmp_col[] = $v['buyer_name'];
                $tmp_col[] = $v['seller_name'];
                $tmp_col[] = '¥'.$v['total_fee'];
                $tmp_col[] = '¥'.$v['refund_goods_fee'];
                $tmp_col[] = '¥'.$v['refund_shipping_fee'];
                $tmp_col[] = $v['created'];
                $tmp_col[] = $v['status'];
                $tmp_col[] = $ask_customer_array[$v['ask_customer']];
                $tmp_col[] = $v['payment_name'];
                $tmp_col[] = $v['pay_time'];
                $tmp_col[] = $v['end_time'];
                $cols[] = $tmp_col;
            }
        }
        $excel->add_array($cols);
        $excel->output();

    }

    function view() {
        $refund_id = empty($_GET['refund_id']) ? 0 : intval($_GET['refund_id']);
        if (!$refund_id) {
            $this->show_warning('refund_id_miss', 'back_list', 'index.php?app=refund&act=view&refund_id=' . $refund_id);
            return;
        }

        //  读取退款信息
        $refund = $this->_refund_mod->get($refund_id);

        if (!$refund) {
            $this->show_warning('refund_not_find', 'back_list', 'index.php?app=refund&act=view&refund_id=' . $refund_id);
            return;
        }

        if (!IS_POST) {
            $refund['shipped_text'] = Lang::get('shipped_' . $refund['shipped']);

            $order = $this->_order_mod->get($refund['order_id']);
            $order['items'] = $this->_ordergoods_mod->find(array(
                'conditions' => 'order_id=' . $refund['order_id'],
            ));
            $order['shipping'] = $this->_order_extm_mod->get($refund['order_id']);

            $page = $this->_get_page(10);   //获取分页信息
            $refund['message'] = $this->_refund_message_mod->find(array(
                'conditions' => 'refund_id=' . $refund_id,
                'order' => 'created desc',
                'limit' => $page['limit'],
                'count' => true
            ));
            $page['item_count'] = $this->_refund_message_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('refund', $refund);
            $this->assign('order', $order);
            $this->display('refund.view.html');
        } else {

            $this->_check_post_data($refund);



            $order_id = $refund['order_id'];

            $order_info = $this->_order_mod->get($order_id);
            $seller_info = $this->_member_mod->get($order_info['seller_id']);
            $buyer_info = $this->_member_mod->get($order_info['buyer_id']);


            if ($order_info['payment_code'] == 'zjgl'||$order_info['payment_id']==3) {

                //判断是否已完成,如果未完成先结束订单
                $this->auto_refund_confirm_order($order_info);

                
                // 对 退货进行处理 BEGUN
                $this->_refund_mod->edit($refund_id, array('status' => 'SUCCESS', 'end_time' => gmtime()));
                $content = sprintf(Lang::get('admin_agree_refund_content_change'), Lang::get('system_customer'), $_POST['refund_goods_fee'], $_POST['refund_shipping_fee'], $_POST['content']);
                $refund_shipping_fee = $_POST['refund_shipping_fee'] ? $_POST['refund_shipping_fee'] : 0;
                $this->_refund_mod->edit($refund_id, array('refund_goods_fee' => $_POST['refund_goods_fee'], 'refund_shipping_fee' => $refund_shipping_fee, 'ask_customer' => 1));
                
                //重新获取 信息
                $refund = $this->_refund_mod->get($refund_id);
                
                $data = array(
                    'owner_id' => $order_info['seller_id'],
                    'owner_role' => 'seller',
                    'refund_id' => $refund_id,
                    'content' => $content,
                    'created' => gmtime()
                );
                $this->_refund_message_mod->add($data);
                // 对 退货进行处理 END



                $this->mod_epay = & m('epay');
                $this->mod_epaylog = & m('epaylog');
                //退款金额
                $money = $refund['refund_goods_fee'] + $refund['refund_shipping_fee'];
                $seller_user_id = $order_info['seller_id'];
                $buyer_user_id = $order_info['buyer_id'];

                $seller_epay = $this->mod_epay->get('user_id=' . $seller_user_id);
                $buyer_epay = $this->mod_epay->get('user_id=' . $buyer_user_id);

                if (empty($seller_info) || empty($buyer_info)) {
                    $this->show_warning('seller_agree_refund_error');
                }

                //没有可用的退款余额
                if ($seller_epay['money'] < $money) {
                    $this->show_warning(sprintf(Lang::get('seller_agree_refund_money'), $money));
                    return;
                }

                $seller_log_text = '您同意给买家' . $buyer_info['user_name'] . '购买的产品退款' . $money . '元，订单号为:' . $order_info['order_sn'] . ',退款编号为:' . $refund_id;
                $seller_epay_log = array(
                    'user_id' => $seller_user_id,
                    'user_name' => $seller_info['user_name'],
                    'order_id' => $order_info['order_id'],
                    'order_sn' => $order_info['order_sn'],
                    'to_id' => $buyer_info['user_id'],
                    'to_name' => $buyer_info['user_name'],
                    'type' => EPAY_REFUND_OUT,
                    'money_flow' => 'outlay',
                    'money' => $money,
                    'complete' => 1,
                    'log_text' => $seller_log_text,
                    'add_time' => gmtime(),
                );
                $this->mod_epaylog->add($seller_epay_log);

                $buyer_log_text = $seller_info['user_name'] . '同意给你购买的产品退款' . $money . '元，订单号为:' . $order_info['order_sn'] . ',退款编号为:' . $refund_id;
                $buyer_epay_log = array(
                    'user_id' => $buyer_user_id,
                    'user_name' => $buyer_info['user_name'],
                    'order_id' => $order_info['order_id'],
                    'order_sn' => $order_info['order_sn'],
                    'to_id' => $seller_info['user_id'],
                    'to_name' => $seller_info['user_name'],
                    'type' => EPAY_REFUND_IN,
                    'money_flow' => 'income',
                    'money' => $money,
                    'complete' => 1,
                    'log_text' => $buyer_log_text,
                    'add_time' => gmtime(),
                );
                $this->mod_epaylog->add($buyer_epay_log);


                $buyer_new_money = array(
                    'money' => $buyer_epay['money'] + $money,
                );
                $this->mod_epay->edit('user_id=' . $buyer_user_id, $buyer_new_money);

                $seller_new_money = array(
                    'money' => $seller_epay['money'] - $money,
                );
                $this->mod_epay->edit('user_id=' . $seller_user_id, $seller_new_money);





                $this->show_message('system_handle_refund_ok', 'back_list', 'index.php?app=refund&act=view&refund_id=' . $refund_id);
            } else {

                $this->show_warning('payment_not_support_refund', 'back_list', 'index.php?app=refund&act=view&refund_id=' . $refund_id);
            }
        }
    }

    //当卖家同意退款 订单自动确认收货
    function auto_refund_confirm_order($order_info) {

        $order_id = $order_info['order_id'];

        //判断订单是否已完成，未完成订单则自动完成
        if ($order_info['status'] != ORDER_FINISHED) {
            $this->_order_mod->edit($order_info['order_id'], array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));

            /* 记录订单操作日志 */
            $order_log = & m('orderlog');
            $order_log->add(array(
                'order_id' => $order_id,
                'operator' => 0,
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark' => Lang::get('admin_agree_refund_order_status_change'),
                'log_time' => gmtime(),
                'operator_type' => 'seller',
            ));


            /* 更新定单状态 开始***************************************************** */
            $this->mod_epay = & m('epay');
            $this->mod_epaylog = & m('epaylog');
            $epaylog_row = $this->mod_epaylog->getrow("select * from " . DB_PREFIX . "epaylog where order_id='$order_id' and type=" . EPAY_BUY);
            $money = $epaylog_row['money']; //定单价格
            $sell_user_id = $epaylog_row['to_id']; //卖家ID
            $buyer_user_id = $epaylog_row['user_id']; //买家ID
            if ($epaylog_row['order_id'] == $order_id) {

                $sell_money_row = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id='$sell_user_id'");
                $sell_money = $sell_money_row['money']; //卖家的资金
                $sell_money_dj = $sell_money_row['money_dj']; //卖家的冻结资金
                $new_money = $sell_money + $money;
                $new_money_dj = $sell_money_dj - $money;
                //更新数据
                $new_money_array = array(
                    'money' => $new_money,
                    'money_dj' => $new_money_dj,
                );
                $new_buyer_epaylog = array(
                    'money' => $money,
                    'complete' => 1,
                    'states' => 40,
                );
                $new_seller_epaylog = array(
                    'money' => $money,
                    'complete' => 1,
                    'states' => 40,
                );
                $this->mod_epay->edit('user_id=' . $sell_user_id, $new_money_array);
                $this->mod_epaylog->edit("order_id={$order_id} AND user_id={$sell_user_id}", $new_seller_epaylog);
                $this->mod_epaylog->edit("order_id={$order_id} AND user_id={$buyer_user_id}", $new_buyer_epaylog);
            }
            /* 更新定单状态 结束***************************************************** */
        }
    }

    function _check_post_data($refund = array()) {

        if ($refund['status'] == 'SUCCESS' || $refund['CLOSED']) {
            $this->show_warning('add_refund_message_not_allow');
            exit;
        }
        if (empty($_POST['refund_goods_fee']) || floatval($_POST['refund_goods_fee']) < 0) {
            $this->show_warning('refund_fee_ge0');
            exit;
        } elseif (floatval($_POST['refund_goods_fee']) > $refund['goods_fee']) {
            $this->show_warning('refund_fee_error');
            exit;
        }
        if ($_POST['refund_shipping_fee'] != '' && floatval($_POST['refund_shipping_fee']) < 0) {
            $this->show_warning('refund_shipping_fee_ge0');
            exit;
        }
        if (floatval($_POST['refund_shipping_fee']) > $refund['shipping_fee']) {
            $this->show_warning('refund_shipping_fee_error');
            exit;
        }
    }

}

?>
