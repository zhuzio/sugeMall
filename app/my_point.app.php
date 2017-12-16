<?php

class My_pointApp extends MemberbaseApp {

    
    function My_pointApp() {
        parent::__construct();
        $this->mod_epay = & m('epay');
        $this->mod_epaylog = & m('epaylog');
        $this->mod_epay_bank = & m('epay_bank');
        $this->mod_order = & m('order');
        $this->mod_sgxt_order = & m('sgxt_order');
        $this->userinfo = $_SESSION['user_info'];
    }

    function exits() {
        //执行关闭页面	
        echo "<script language='javascript'>window.opener=null;window.close();</script>";
    }

    function index(){
        $user_id = $this->visitor->get('user_id');
        $this->_curitem('my_point');

        $this->assign('epay_alipay_enabled', Conf::get('epay_alipay_enabled'));
        $this->assign('epay_chinabank_enabled', Conf::get('epay_chinabank_enabled'));
        $this->assign('epay_tenpay_enabled', Conf::get('epay_tenpay_enabled'));
        $this->assign('epay_wxjs_enabled', Conf::get('epay_wxjs_enabled'));
        $this->assign('epay_wxnative_enabled', Conf::get('epay_wxnative_enabled'));

        $epay = $this->mod_epay->get("user_id=$user_id");
        $this->assign('epay', $epay);

        $this->assign('epay_offline_info', Conf::get('epay_offline_info'));

        $this->display('my_point.html');
    }
    //商户购买积分记录
    function buy_log()
    {
        $user_id = $this->visitor->get('user_id');
        /* 当前用户中心菜单 */
        $this->_curitem('epay');
        $this->_curmenu('epay_logall');

        $page = $this->_get_page(10);
        $sgxtorder_list = $this->mod_sgxt_order->find(array(
            'conditions' => ' status=1 and user_id=' .$user_id,
            'limit' => $page['limit'],
            'order' => "id desc",
            'count' => true));
        $page['item_count'] = $this->mod_sgxt_order->getCount();
        $this->_format_page($page);
        //$this->assign('filtered', $conditions ? 1 : 0); //是否有查询条件
        $typearr=array('wx'=>'微信支付','allinpay'=>'通联支付支付','reapal'=>'融宝支付','ll'=>'连连支付','llpay'=>'连连支付','balance'=>'货款支付');
        foreach($sgxtorder_list as $key => $value)
        {
            $sgxtorder_list[$key]['paytypecn']=$typearr[$value['paytype']];
        }
        $this->assign('page_info', $page);
        $this->assign('sgxtorder_list', $sgxtorder_list);

        $this->assign('epay_type_list', array(
            EPAY_ADMIN => Lang::get('epay_admin'), //手工操作
            EPAY_BUY => Lang::get('epay_buy'), //购买商品
            EPAY_SELLER => Lang::get('epay_seller'), //出售商品
            EPAY_IN => Lang::get('epay_in'), //账户转入
            EPAY_OUT => Lang::get('epay_out'), //账户转出
            EPAY_CZ => Lang::get('epay_cz'), //账户充值
            EPAY_TX => Lang::get('epay_tx'), //账户提现
            EPAY_REFUND_IN => Lang::get('epay_refund_in'), //账户退款收入,通常为买家退款成功 得到退款
            EPAY_REFUND_OUT => Lang::get('epay_refund_out'), //账户退款收入,通常为卖家退款成功 扣除退款
            EPAY_TUIJIAN_BUYER => Lang::get('epay_tuijian_buyer'),  // 用户推荐注册,注册者购买产品，推荐人会获得佣金，店铺会损失佣金。
            EPAY_TUIJIAN_SELLER=> Lang::get('epay_tuijian_seller'), // 用户推荐注册,注册者成为店主，卖出产品推荐人会获得佣金，店主会损失佣金。
            EPAY_TRADE_CHARGES=> Lang::get('epay_trade_charges'), // 扣除卖家交易佣金
        ));

        $this->assign('complete_list', array(
            0 => Lang::get('uncomplete'),
            1 => Lang::get('oncomplete'),
        ));


        $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
            ),
            'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('my_point.buylog.html');
    }
    function logall() {
        $user_id = $this->visitor->get('user_id');
        /* 当前用户中心菜单 */
        $this->_curitem('my_point');
        $this->_curmenu('my_point_logall');


        /*$conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'pay_createtime',
                'name' => 'add_time_from',
                'equal' => '>=',
                'handler' => 'gmstr2time',
            ), array(
                'field' => 'pay_createtime',
                'name' => 'add_time_to',
                'equal' => '<=',
                'handler' => 'gmstr2time_end',
            ),
            array(//按订单号
                'field' => 'orderid',
                'equal' => 'LIKE',
                'name' => 'orderid',
            ),
        ));*/
        $this->model=& m();
        $page = $this->_get_page(10);
        $sgxtorder_list = $this->model->table('sgxt_order')->find(array(
            'conditions'    => ' status=1 and userid=' .$user_id,
            'limit'         => $page['limit'],  //获取当前页的数据
            'order'         => "id desc",
            'count'         => true             //允许统计
        ));
        //print($this->model->getlastsql().'123456');
        $page['item_count'] = $this->model->table('sgxt_order')->getCount();
        $this->_format_page($page);
        //$this->assign('filtered', $conditions ? 1 : 0); //是否有查询条件
        $typearr=array('wx'=>'微信支付','allinpay'=>'通联支付支付','reapal'=>'融宝支付','ll'=>'连连支付','llpay'=>'连连支付','balance'=>'货款支付');
        foreach($sgxtorder_list as $key => $value)
        {
            $sgxtorder_list[$key]['paytypecn']=$typearr[$value['paytype']];
        }
        //print_r($sgxtorder_list);die;
        //$this->assign('filtered', $conditions ? 1 : 0); //是否有查询条件
        
        
        $this->assign('page_info', $page);
        $this->assign('sgxtorder_list', $sgxtorder_list);

        /*$this->assign('epay_type_list', array(
            EPAY_ADMIN => Lang::get('epay_admin'), //手工操作
            EPAY_BUY => Lang::get('epay_buy'), //购买商品
            EPAY_SELLER => Lang::get('epay_seller'), //出售商品
            EPAY_IN => Lang::get('epay_in'), //账户转入
            EPAY_OUT => Lang::get('epay_out'), //账户转出
            EPAY_CZ => Lang::get('epay_cz'), //账户充值
            EPAY_TX => Lang::get('epay_tx'), //账户提现
            EPAY_REFUND_IN => Lang::get('epay_refund_in'), //账户退款收入,通常为买家退款成功 得到退款
            EPAY_REFUND_OUT => Lang::get('epay_refund_out'), //账户退款收入,通常为卖家退款成功 扣除退款
            EPAY_TUIJIAN_BUYER => Lang::get('epay_tuijian_buyer'),  // 用户推荐注册,注册者购买产品，推荐人会获得佣金，店铺会损失佣金。
            EPAY_TUIJIAN_SELLER=> Lang::get('epay_tuijian_seller'), // 用户推荐注册,注册者成为店主，卖出产品推荐人会获得佣金，店主会损失佣金。
            EPAY_TRADE_CHARGES=> Lang::get('epay_trade_charges'), // 扣除卖家交易佣金
        ));
        
        $this->assign('complete_list', array(
            0 => Lang::get('uncomplete'), 
            1 => Lang::get('oncomplete'), 
        ));*/
        
        
       $this->import_resource(array(
            'script' => array(
                array(
                    'path' => 'jquery.ui/jquery.ui.js',
                    'attr' => '',
                ),
            ),
            'style' => 'jquery.ui/themes/ui-lightness/jquery.ui.css',
        ));
        $this->display('my_point.logall.html');
    }

    public function pointOrder(){
        $this->model = &m();
        $type = trim(I('post.czfs'));
        $num = trim(I('post.num'));
        if($num <= 0) {
            $this->show_warning('购买积分必须大于0');
            return;
        }
        if($type == 'wxnative')
        {
            if($num >100) {
                $this->show_warning('微信支付限额100');
                return;
            }
        }
        $pay_info = conf('PAY_INFO');
        $user = $this->model->table('member')->where(array('user_id' => $this->userinfo[user_id]))->find1();
        if($user['type'] != '2') {
            $this->show_warning('只有商家才能买积分');
            return ;
        }

        $order = 'P'.substr(time() , 3) . rand(100 , 999) . rand(1000 , 9999);
        $amount = $pay_info['point'] * $num;

        $orderData = array(
            'orderid' => $order,
            'userid'  => $user['user_id'],
            'mobile'  => $user['user_name'],
            'truename' => $user['real_name'],
            'price'   => $pay_info['point'],
            'num'     => $num,
            'order_type' => 'point',
            'amount'  => $amount,
            'createtime' => time()
        );
        $insid = $this->model->table('sgxt_order') ->add($orderData);

        if($type == 'wxnative'){
            //window.location.href='http://www.sugemall.com/app/wxpay/wxjs.php?dingdan=P66952471249035';
            header("Location:/app/wxpay/wxnative.php?dingdan={$order}");
        }else if($type == 'llpay'){
            //window.location.href='http://www.sugemall.com/app/wapllpay/llpayapi.php?dingdan=P66952471249035';
            header("Location:/app/webllpay/llpayapi.php?dingdan={$order}");
        }else if($type == 'reapal'){
            header("Location:/app/reapal/rongpayto.php?dingdan={$order}&cz_money=".$amount);
        }else if($type == 'allinpay'){
            header("Location:/app/allinpay/allinpay_pc.php?dingdan={$order}&cz_money=".$amount);
        }
        //header("Location:index.php?app=order_point&act=paymentWay&orderid={$insid}");
    }
    function _get_member_submenu()
    {
        $menus = array(

            array(
                'name'  => 'my_point',
                'url'   => 'index.php?app=my_point',
            ),
            array(
                'name'  => 'my_point_logall',
                'url'   => 'index.php?app=my_point&act=logall',
            ),
            /*
            array(
                'name'  => 'epay_czlist',
                'url'   => 'index.php?app=epay&act=czlist',
            ),
            array(
                'name'  => 'epay_out',
                'url'   => 'index.php?app=epay&act=out',
            ),
            */

        );
        return $menus;
    }


}
?>
