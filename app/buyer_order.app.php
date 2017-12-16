<?php

/**
 *    买家的订单管理控制器
 *
 *    @author    Garbin
 *    @usage    none
 */
class Buyer_orderApp extends MemberbaseApp {

    function __construct() {
        $this->Buyer_orderApp();
    }

    function Buyer_orderApp() {
        parent::__construct();
        $this->clear_buyer_logs();
    }
    
    //标识发货记录  清除operator_type='seller'
    function clear_buyer_logs()
    {
        $order_log_mod = & m('orderlog');
        $user_id = $this->visitor->get('user_id');
        $buyer_order_log = $order_log_mod->find(
                array(
                    'conditions' => "buyer_id = '$user_id' AND order_log_status = 0 AND operator_type='seller'",
                    'join' => 'belongs_to_order',
                )
        );
        if(!empty($buyer_order_log)){
            foreach ($buyer_order_log as $key => $order) {
                $data['order_log_status'] = 1;
                $order_log_mod->edit($key, $data);
            }
        }
    }
    
    function index() {
        /* 获取订单列表 */
        $this->_get_orders();

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('order_list'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');
        $this->_curmenu('order_list');
        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_order'));
        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'dialog/dialog.js',
                    'attr' => 'id="dialog_js"',
                ),
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.ui/i18n/' . i18n_code() . '.js',
                    'attr' => '',
                ),
                array(
                    'path' => 'jquery.plugins/jquery.validate.js',
                    'attr' => '',
                ),
            ),
            'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));


        /* 显示订单列表 */
        $this->display('buyer_order.index.html');
    }

    /**
     *    查看订单详情
     *
     *    @author    Garbin
     *    @return    void
     */
    function view() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order = & m('order');
        //$order_info  = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        $order_info = $model_order->get(array(
            'fields' => "*, order.add_time as order_add_time",
            'conditions' => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join' => 'belongs_to_store',
        ));
        if (!$order_info) {
            $this->show_warning('no_such_order');

            return;
        }

        /* 团购信息 */
        if ($order_info['extension'] == 'groupbuy') {
            $groupbuy_mod = &m('groupbuy');
            $group = $groupbuy_mod->get(array(
                'join' => 'be_join',
                'conditions' => 'order_id=' . $order_id,
                'fields' => 'gb.group_id',
            ));
            $this->assign('group_id', $group['group_id']);
        }

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('view_order'));

        /* 当前用户中心菜单 */
        $this->_curitem('my_order');

        $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('order_detail'));

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type = & ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods) {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('buyer_order.view.html');
    }
    
    
    function orderprint() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $model_order = & m('order');
        $order_info = $model_order->get(array(
            'fields' => "*, order.add_time as order_add_time",
            'conditions' => "order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'),
            'join' => 'belongs_to_store',
        ));
        if (!$order_info) {
            $this->show_warning('no_such_order');

            return;
        }

        /* 调用相应的订单类型，获取整个订单详情数据 */
        $order_type = & ot($order_info['extension']);
        $order_detail = $order_type->get_order_detail($order_id, $order_info);
        foreach ($order_detail['data']['goods_list'] as $key => $goods) {
            empty($goods['goods_image']) && $order_detail['data']['goods_list'][$key]['goods_image'] = Conf::get('default_goods_image');
        }
        $this->assign('order', $order_info);
        $this->assign($order_detail['data']);
        $this->display('buyer_order.orderprint.html');
    }
    

    /**
     *    取消订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function cancel_order() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id) {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order = & m('order');
        /* 只有待付款的订单可以取消 */
        $order_info = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status " . db_create_in(array(ORDER_PENDING, ORDER_SUBMITTED)));
        if (empty($order_info)) {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST) {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_order.cancel.html');
        } else {
            $model_order->edit($order_id, array('status' => ORDER_CANCELED));
            if ($model_order->has_error()) {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 加回商品库存 */
            $model_order->change_stock('+', $order_id);
            $cancel_reason = (!empty($_POST['remark'])) ? $_POST['remark'] : $_POST['cancel_reason'];
            /* 记录订单操作日志 */
            $order_log = & m('orderlog');
            $order_log->add(array(
                'order_id' => $order_id,
                'operator' => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_CANCELED),
                'remark' => $cancel_reason,
                'log_time' => gmtime(),
                'operator_type'=>'buyer',
            ));

            /* 发送给卖家订单取消通知 */
            $model_member = & m('member');
            $seller_info = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_cancel_order_notify', array('order' => $order_info, 'reason' => $_POST['remark']));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status' => Lang::get('order_canceled'),
                'actions' => array(), //取消订单后就不能做任何操作了
            );

            $this->pop_warning('ok');
        }
    }

    /**
     *    确认订单
     *
     *    @author    Garbin
     *    @return    void
     */
    function confirm_order() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id) {
            echo Lang::get('no_such_order');

            return;
        }
        $model_order = & m('order');
        /* 只有已发货的订单可以确认 */
        $order_info = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id') . " AND status=" . ORDER_SHIPPED);
        if (empty($order_info)) {
            echo Lang::get('no_such_order');

            return;
        }
        if (!IS_POST) {
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->assign('order', $order_info);
            $this->display('buyer_order.confirm.html');
        } else {
            $model_order->edit($order_id, array('status' => ORDER_FINISHED, 'finished_time' => gmtime()));
            if ($model_order->has_error()) {
                $this->pop_warning($model_order->get_error());

                return;
            }

            /* 记录订单操作日志 */
            $order_log = & m('orderlog');
            $order_log->add(array(
                'order_id' => $order_id,
                'operator' => addslashes($this->visitor->get('user_name')),
                'order_status' => order_status($order_info['status']),
                'changed_status' => order_status(ORDER_FINISHED),
                'remark' => Lang::get('buyer_confirm'),
                'log_time' => gmtime(),
                'operator_type'=>'buyer',
            ));

            /* 更新定单状态 开始***************************************************** */
            $this->mod_epay = & m('epay');
            $this->mod_epaylog = & m('epaylog');
            $epaylog_row = $this->mod_epaylog->getrow("select * from " . DB_PREFIX . "epaylog where order_id='$order_id' and type=".EPAY_BUY);
            $money = $epaylog_row['money']; //定单价格
            $sell_user_id = $epaylog_row['to_id']; //卖家ID
            $buyer_user_id = $epaylog_row['user_id']; //买家ID
            if ($epaylog_row['order_id'] == $order_id) {

                $sell_money_row = $this->mod_epay->getrow("select * from " . DB_PREFIX . "epay where user_id='$sell_user_id'");
                $sell_money = $sell_money_row['balance']; //卖家的资金
                $sell_money_dj = $sell_money_row['freeze_balance']; //卖家的冻结资金
                $new_money = $sell_money + $money;
                $new_money_dj = $sell_money_dj - $money;
                //更新数据
                $new_money_array = array(
                    'balance' => $new_money,
                    'freeze_balance' => $new_money_dj,
                );
                $new_buyer_epaylog = array(
                    'money'=>$money,
                    'complete' => 1,
                    'states' => 40,
                );
                $new_seller_epaylog = array(
                    'money'=>$money,
                    'complete' => 1,
                    'states' => 40,
                );
                $this->mod_epay->edit('user_id=' . $sell_user_id, $new_money_array);
                $this->mod_epaylog->edit("order_id={$order_id} AND user_id={$sell_user_id}", $new_seller_epaylog);
                $this->mod_epaylog->edit("order_id={$order_id} AND user_id={$buyer_user_id}", $new_buyer_epaylog);
            }
            /* 更新定单状态 结束***************************************************** */

            /*用户确认收货后 扣除商城佣金*/
            import('epay.lib');
            $epay=new epay();
            $epay->trade_charges($order_info);
            
            /* 发送给卖家买家确认收货邮件，交易完成 */
            $model_member = & m('member');
            $seller_info = $model_member->get($order_info['seller_id']);
            $mail = get_mail('toseller_finish_notify', array('order' => $order_info));
            $this->_mailto($seller_info['email'], addslashes($mail['subject']), addslashes($mail['message']));

            $new_data = array(
                'status' => Lang::get('order_finished'),
                'actions' => array('evaluate'),
            );

            /* 更新累计销售件数 */
            $model_goodsstatistics = & m('goodsstatistics');
            $model_ordergoods = & m('ordergoods');
            $order_goods = $model_ordergoods->find("order_id={$order_id}");
            foreach ($order_goods as $goods) {
                $model_goodsstatistics->edit($goods['goods_id'], "sales=sales+{$goods['quantity']}");
            }

            /*用户确认收货后 获得积分*/
            import('integral.lib');
            $integral=new Integral();
            $integral->change_integral_buy($order_info['buyer_id'],$order_info['goods_amount']);

            /* 如果赠送积分，则开始计算各种收益 */
            /*
            if($order_info['point'] > 0){
                $point_mod = & m('point');
                $point_mod->sendPoint($this->visitor->get('user_name'),$order_info['point'],$order_info['seller_id'],$order_info,'online');
            }
            */
            
            /*交易成功后,推荐者可以获得佣金  BEGIN*/
            import('tuijian.lib');
            $tuijian=new tuijian();
            $tuijian->do_tuijian($order_info);
            /*交易成功后,推荐者可以获得佣金  END*/
            
            
            //卖家确认收货 发送短信给卖家
            import('mobile_msg.lib');
            $mobile_msg = new Mobile_msg();
            $mobile_msg->send_msg_order($order_info,'check');
            
            
            $this->pop_warning('ok','','index.php?app=buyer_order&act=evaluate&order_id='.$order_id);;
            
        }
    }
    
    function delay_auto_finished_time()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id) {
            $this->show_warning('no_such_order');

            return;
        }
        /* 验证订单有效性 */
        $model_order = & m('order');
        $order_info = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (!$order_info) {
            $this->show_warning('no_such_order');
            return;
        }
        if ($order_info['status'] != ORDER_SHIPPED) {
            $this->show_warning('error');
            return;
        }
        //自动延长 7 天
        $data = array(
            'auto_finished_time'=>$order_info['auto_finished_time']+7*3600*24,
        );
        $model_order->edit($order_id,$data);
        $this->show_message('已延长收货7天', 'back_list', 'index.php?app=buyer_order');
    }
    

    /**
     *    给卖家评价
     *
     *    @author    Garbin
     *    @param    none
     *    @return    void
     */
    function evaluate() {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        if (!$order_id) {
            $this->show_warning('no_such_order');

            return;
        }

        /* 验证订单有效性 */
        $model_order = & m('order');
        $order_info = $model_order->get("order_id={$order_id} AND buyer_id=" . $this->visitor->get('user_id'));
        if (!$order_info) {
            $this->show_warning('no_such_order');

            return;
        }
        if ($order_info['status'] != ORDER_FINISHED) {
            /* 不是已完成的订单，无法评价 */
            $this->show_warning('cant_evaluate');

            return;
        }
        if ($order_info['evaluation_status'] != 0) {
            /* 已评价的订单 */
            $this->show_warning('already_evaluate');

            return;
        }
        $model_ordergoods = & m('ordergoods');

        if (!IS_POST) {
            /* 显示评价表单 */
            /* 获取订单商品 */
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($goods_list as $key => $goods) {
                empty($goods['goods_image']) && $goods_list[$key]['goods_image'] = Conf::get('default_goods_image');
            }
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('my_order'), 'index.php?app=buyer_order', LANG::get('evaluate'));
            $this->assign('goods_list', $goods_list);
            $this->assign('order', $order_info);

            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('credit_evaluate'));
            $this->display('buyer_order.evaluate.html');
        } else {
            $evaluations = array();
            /* 写入评价 */
            foreach ($_POST['evaluations'] as $rec_id => $evaluation) {
                if ($evaluation['evaluation'] <= 0 || $evaluation['evaluation'] > 3) {
                    $this->show_warning('evaluation_error');

                    return;
                }
                switch ($evaluation['evaluation']) {
                    case 3:
                        $credit_value = 1;
                        break;
                    case 1:
                        $credit_value = -1;
                        break;
                    default:
                        $credit_value = 0;
                        break;
                }
                $evaluations[intval($rec_id)] = array(
                    'evaluation' => $evaluation['evaluation'],
                    /*新增 店铺动态评分 begin*/
                    'evaluation_desc'    => in_array($evaluation['evaluation_desc'], array("1","2","3","4","5",))?$evaluation['evaluation_desc']:5,         #描述相符评分
                    'evaluation_service'    => in_array($evaluation['evaluation_service'], array("1","2","3","4","5",))?$evaluation['evaluation_service']:5,   #服务动态评分
                    'evaluation_speed'    => in_array($evaluation['evaluation_speed'], array("1","2","3","4","5",))?$evaluation['evaluation_speed']:5,       #发货速度评分
                    /*新增 店铺动态评分 end*/
                    'comment' => $evaluation['comment'],
                    'credit_value' => $credit_value
                );
            }
            $goods_list = $model_ordergoods->find("order_id={$order_id}");
            foreach ($evaluations as $rec_id => $evaluation) {
                $model_ordergoods->edit("rec_id={$rec_id} AND order_id={$order_id}", $evaluation);
                $goods_url = SITE_URL . '/' . url('app=goods&id=' . $goods_list[$rec_id]['goods_id']);
                $goods_name = $goods_list[$rec_id]['goods_name'];
                $this->send_feed('goods_evaluated', array(
                    'user_id' => $this->visitor->get('user_id'),
                    'user_name' => $this->visitor->get('user_name'),
                    'goods_url' => $goods_url,
                    'goods_name' => $goods_name,
                    'evaluation' => Lang::get('order_eval.' . $evaluation['evaluation']),
                    'comment' => $evaluation['comment'],
                    'images' => array(
                        array(
                            'url' => SITE_URL . '/' . $goods_list[$rec_id]['goods_image'],
                            'link' => $goods_url,
                        ),
                    ),
                ));
            }

            /* 更新订单评价状态 */
            $model_order->edit($order_id, array(
                'evaluation_status' => 1,
                'evaluation_time' => gmtime()
            ));

            /* 更新卖家信用度及好评率 */
            $model_store = & m('store');
            /*新增店铺动态评分 获取评分 begin */
            import('evaluation.lib');
            $evaluation = new Evaluation();
            $average_score = $evaluation->recount_evaluation_dss($order_info['seller_id']);  #获取的为数组
            $evaluation_desc = $average_score['evaluation_desc'];
            $evaluation_service = $average_score['evaluation_service'];
            $evaluation_speed = $average_score['evaluation_speed'];
            /*新增店铺动态评分 获取评分 end */
            
            $model_store->edit($order_info['seller_id'], array(
                'credit_value' => $model_store->recount_credit_value($order_info['seller_id']),
                /*新增店铺动态评分 获取评分 begin */
                'evaluation_desc'       =>  $evaluation_desc,
                'evaluation_service'    =>  $evaluation_service,
                'evaluation_speed'      =>  $evaluation_speed,
                /*新增店铺动态评分 获取评分 end */
                'praise_rate' => $model_store->recount_praise_rate($order_info['seller_id'])
            ));

            /* 更新商品评价数 */
            $model_goodsstatistics = & m('goodsstatistics');
            $goods_ids = array();
            foreach ($goods_list as $goods) {
                $goods_ids[] = $goods['goods_id'];
            }
            $model_goodsstatistics->edit($goods_ids, 'comments=comments+1');


            $this->show_message('evaluate_successed', 'back_list', 'index.php?app=buyer_order');
        }
    }

    /**
     *    获取订单列表
     *
     *    @author    Garbin
     *    @return    void
     */
    function _get_orders() {
        $page = $this->_get_page(10);
        $model_order = & m('order');
        !$_GET['type'] && $_GET['type'] = 'all_orders';
        $con = array(
            array(//按订单状态搜索
                'field' => 'status',
                'name' => 'type',
                'handler' => 'order_status_translator',
            ),
            array(//按店铺名称搜索
                'field' => 'seller_name',
                'equal' => 'LIKE',
            ),
            array(//按下单时间搜索,起始时间
                'field' => 'add_time',
                'name' => 'add_time_from',
                'equal' => '>=',
                'handler' => 'gmstr2time',
            ),
            array(//按下单时间搜索,结束时间
                'field' => 'add_time',
                'name' => 'add_time_to',
                'equal' => '<=',
                'handler' => 'gmstr2time_end',
            ),
            array(//按订单号
                'field' => 'order_sn',
            ),
        );
        $conditions = $this->_get_query_conditions($con);
        /* 查找订单 */
        $orders = $model_order->findAll(array(
            'conditions' => "buyer_id=" . $this->visitor->get('user_id') . "{$conditions}",
            'fields' => 'this.*',
            'count' => true,
            'limit' => $page['limit'],
            'order' => 'add_time DESC',
            'include' => array(
                'has_ordergoods', //取出商品
            ),
        ));

        $member_mod = & m('member');
        $refund_mod = &m('refund');
        foreach ($orders as $key1 => $order) {
            foreach ($order['order_goods'] as $key2 => $goods) {
                empty($goods['goods_image']) && $orders[$key1]['order_goods'][$key2]['goods_image'] = Conf::get('default_goods_image');
                /* 是否申请过退款 */
                $refund = $refund_mod->get(array('conditions' => 'order_id=' . $goods['order_id'] . ' and goods_id=' . $goods['goods_id'] . ' and spec_id=' . $goods['spec_id'], 'fields' => 'status,order_id'));
                if ($refund) {
                    $orders[$key1]['order_goods'][$key2]['refund_status'] = $refund['status'];
                    $orders[$key1]['order_goods'][$key2]['refund_id'] = $refund['refund_id'];
                }
            }
            // psmb
            $orders[$key1]['goods_quantities'] = count($order['order_goods']);
            $orders[$key1]['seller_info'] = $member_mod->get(array('conditions' => 'user_id=' . $order['seller_id'], 'fields' => 'real_name,im_qq,im_aliww,im_msn'));
        }

        $page['item_count'] = $model_order->getCount();
        $this->assign('types', array('all' => Lang::get('all_orders'),
            'pending' => Lang::get('pending_orders'),
            'submitted' => Lang::get('submitted_orders'),
            'accepted' => Lang::get('accepted_orders'),
            'shipped' => Lang::get('shipped_orders'),
            'finished' => Lang::get('finished_orders'),
            'canceled' => Lang::get('canceled_orders')));
        $this->assign('type', $_GET['type']);
        $this->assign('orders', $orders);
        $this->_format_page($page);
        $this->assign('page_info', $page);
    }

    function _get_member_submenu() {
        $menus = array(
            array(
                'name' => 'order_list',
                'url' => 'index.php?app=buyer_order',
            ),
        );
        return $menus;
    }

}

?>
