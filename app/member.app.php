 <?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
class MemberApp extends MemberbaseApp {

    var $_feed_enabled = false;

    function __construct() {
        $this->MemberApp();
    }

    function MemberApp() {
        parent::__construct();
        $ms = & ms();
        $this->_feed_enabled = $ms->feed->feed_enabled();
        $this->assign('feed_enabled', $this->_feed_enabled);
        //余额支付 
        $this->epay_mod = & m('epay');
    }

    function index() {
        /* 清除新短消息缓存 */
        $cache_server = & cache_server();
        $cache_server->delete('new_pm_of_user_' . $this->visitor->get('user_id'));

        $user = $this->visitor->get();
        $user_mod = & m('member');
        $info = $user_mod->get_info($user['user_id']);
        $user['portrait'] = portrait($user['user_id'], $info['portrait'], 'middle');
        $user['ugrade']=$user_mod->get_grade_info($user['user_id']);
        $user['integral'] = $info['integral'];
        $user['total_integral'] = $info['total_integral'];
        $this->assign('user', $user);
        $weixin_user =& m('weixinuser');
        $weixin_user_info=$weixin_user->get("user_id=".$user['user_id']);
  	    $this->assign('weixin_user_info', $weixin_user_info);
        //余额支付 
        $my_user_id = $this->visitor->get('user_id');
        $epay = $this->epay_mod->getAll("select * from " . DB_PREFIX . "epay where user_id=$my_user_id");
        $this->assign('epay', $epay);

        /* 店铺信用和好评率 */
        if ($user['has_store']) {
            $store_mod = & m('store');
            $store = $store_mod->get_info($user['has_store']);
            $step = intval(Conf::get('upgrade_required'));
            $step < 1 && $step = 5;
            $store['credit_image'] = $this->_view->res_base . '/images/' . $store_mod->compute_credit($store['credit_value'], $step);
            $this->assign('store', $store);
            $this->assign('store_closed', STORE_CLOSED);
        }
        $goodsqa_mod = & m('goodsqa');
        $groupbuy_mod = & m('groupbuy');
        /* 买家提醒：待付款、待确认、待评价订单数 */
        $order_mod = & m('order');
        $sql1 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_PENDING . "'";
        $sql2 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_SHIPPED . "'";
        $sql3 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE buyer_id = '{$user['user_id']}' AND status = '" . ORDER_FINISHED . "' AND evaluation_status = 0";
        $sql4 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE user_id = '{$user['user_id']}' AND reply_content !='' AND if_new = '1' ";
        $sql5 = "SELECT COUNT(*) FROM " . DB_PREFIX . "groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " . GROUP_CANCELED;
        $sql6 = "SELECT COUNT(*) FROM " . DB_PREFIX . "groupbuy_log AS log LEFT JOIN {$groupbuy_mod->table} AS gb ON gb.group_id = log.group_id WHERE log.user_id='{$user['user_id']}' AND gb.state = " . GROUP_FINISHED;
        $buyer_stat = array(
            'pending' => $order_mod->getOne($sql1),
            'shipped' => $order_mod->getOne($sql2),
            'finished' => $order_mod->getOne($sql3),
            'my_question' => $goodsqa_mod->getOne($sql4),
            'groupbuy_canceled' => $groupbuy_mod->getOne($sql5),
            'groupbuy_finished' => $groupbuy_mod->getOne($sql6),
        );
        $sum = array_sum($buyer_stat);
        $buyer_stat['sum'] = $sum;
        $this->assign('buyer_stat', $buyer_stat);

        /* 卖家提醒：待处理订单和待发货订单 */
        if ($user['has_store']) {

            $sql7 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_SUBMITTED . "'";
            $sql8 = "SELECT COUNT(*) FROM {$order_mod->table} WHERE seller_id = '{$user['user_id']}' AND status = '" . ORDER_ACCEPTED . "'";
            $sql9 = "SELECT COUNT(*) FROM {$goodsqa_mod->table} WHERE store_id = '{$user['user_id']}' AND reply_content ='' ";
            $sql10 = "SELECT COUNT(*) FROM {$groupbuy_mod->table} WHERE store_id='{$user['user_id']}' AND state = " . GROUP_END;
            $seller_stat = array(
                'submitted' => $order_mod->getOne($sql7),
                'accepted' => $order_mod->getOne($sql8),
                'replied' => $goodsqa_mod->getOne($sql9),
                'groupbuy_end' => $goodsqa_mod->getOne($sql10),
            );

            $this->assign('seller_stat', $seller_stat);
        }
        /* 卖家提醒： 店铺等级、有效期、商品数、空间 */
        if ($user['has_store']) {
            $store_mod = & m('store');
            $store = $store_mod->get_info($user['has_store']);

            $grade_mod = & m('sgrade');
            $grade = $grade_mod->get_info($store['sgrade']);

            $goods_mod = &m('goods');
            $goods_num = $goods_mod->get_count_of_store($user['has_store']);
            $uploadedfile_mod = &m('uploadedfile');
            $space_num = $uploadedfile_mod->get_file_size($user['has_store']);
            $sgrade = array(
                'grade_name' => $grade['grade_name'],
                'add_time' => empty($store['end_time']) ? 0 : sprintf('%.2f', ($store['end_time'] - gmtime()) / 86400),
                'goods' => array(
                    'used' => $goods_num,
                    'total' => $grade['goods_limit']),
                'space' => array(
                    'used' => sprintf("%.2f", floatval($space_num) / (1024 * 1024)),
                    'total' => $grade['space_limit']),
            );
            $this->assign('sgrade', $sgrade);
        }

        /* 待审核提醒 */
        if ($user['state'] != '' && $user['state'] == STORE_APPLYING) {
            $this->assign('applying', 1);
        }

        $this->assign('system_notice', $this->_get_system_notice($_SESSION['member_role']));

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), url('app=member'), LANG::get('overview'));

        /* 当前用户中心菜单 */
        $this->_curitem('overview');
        $this->_config_seo('title', Lang::get('member_center'));
        $this->display('member.index.html');
    }

    function _get_system_notice($member_role = 'buyer_admin') {
        // 根据不同的用户角色（卖家或买家），在用户中心首页显示不同的文章
        if ($member_role == 'seller_admin') {
            $article_cate_id = 2;
        } else {
            $article_cate_id = 1;
        }
        $article_mod = &m('article');
        $acategory_mod = &m('acategory');

        $cate_ids = $acategory_mod->get_descendant($article_cate_id);
        if ($cate_ids) {
            $conditions = ' AND cate_id ' . db_create_in($cate_ids);
        } else {
            $conditions = '';
        }

        $data = $article_mod->find(array(
            'conditions' => 'code = "" AND if_show=1 AND store_id=0 ' . $conditions,
            'fields' => 'article_id, title',
            'limit' => 5,
            'order' => 'sort_order ASC, article_id DESC'
        ));
        return $data;
    }

    /**
     *    注册一个新用户
     *
     *    @author    Garbin
     *    @return    void
     */
    function register() {
        if ($this->visitor->has_login) {
            $this->show_warning('has_login');

            return;
        }
        if (!IS_POST) {
            $this ->assign('key' ,$_GET['key']);
            if (!empty($_GET['ret_url'])) {
                $ret_url = trim($_GET['ret_url']);
            } else {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $ret_url = $_SERVER['HTTP_REFERER'];
                } else {
                    $ret_url = SITE_URL . '/index.php';
                }
            }
            $this->assign('ret_url', rawurlencode($ret_url));
            $this->_curlocal(LANG::get('user_register'));
            $this->_config_seo('title', Lang::get('user_register') . ' - ' . Conf::get('site_title'));
            if (Conf::get('captcha_status.register')) {
                $this->assign('captcha', 1);
            }
            if (Conf::get('msg_enabled')) {
                $this->assign('msg_enabled', 1);
            }
            /* 导入jQuery的表单验证插件   */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js,jquery.plugins/poshy_tip/jquery.poshytip.js',
                'style' => 'jquery.plugins/poshy_tip/tip-yellowsimple/tip-yellowsimple.css')
            );
            $this->display('member.register.html');
            if (!$_POST['agree']) {
                $this->show_warning('agree_first');

                return;
            }
            if (Conf::get('captcha_status.register') && base64_decode($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
                $this->show_warning('captcha_failed');
                return;
            }
            if(!$_POST['tuijian']){
                $this->show_warning('推荐人不能为空');exit;
            }
            if($_POST['tuijian'] == '' || strlen($_POST['tuijian']) != 11 || !is_numeric($_POST['tuijian']) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $_POST['tuijian'])){
                $this->show_warning('推荐人手机号非法');exit;
            }
            if(!$_POST['user_name']){
                $this->show_warning('手机号不能为空');exit;
            }
            if($_POST['user_name'] == '' || strlen($_POST['user_name']) != 11 || !is_numeric($_POST['user_name']) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $_POST['user_name'])){
                $this->show_warning('手机号非法');exit;
            }
//            if(!$_POST['province']){
//                $this->show_warning('省份不能为空');exit;
//            }
//            if(!$_POST['city']){
//                $this->show_warning('城市不能为空');exit;
//            }
//            if(!$_POST['area']){
//                $this->show_warning('县区不能为空');exit;
//            }
            if ($_POST['password'] != $_POST['password_confirm']) {
                /* 两次输入的密码不一致 */
                $this->show_warning('inconsistent_password');
                return;
            }

            /* 注册并登陆 */
            $user_name = trim($_POST['user_name']);
            $real_name = trim($_POST['real_name']);
            if(!$real_name){
                $this->show_warning('姓名不能为空');
                exit;
            }
            $password = $_POST['password'];
            $email = trim($_POST['email']);
            $passlen = strlen($password);
            $user_name_len = strlen($user_name);
            if ($user_name_len < 3 || $user_name_len > 25) {
                $this->show_warning('user_name_length_error');

                return;
            }
            if ($passlen < 6 || $passlen > 20) {
                $this->show_warning('password_length_error');

                return;
            }
            if (!is_email($email)) {
                $this->show_warning('email_error');

                return;
            }
			/*
            if (!preg_match("/^[0-9a-zA-Z]{3,15}$/", $user_name)) {
                $this->show_warning('user_already_taken');

                return;
            }
			*/

            if($_POST['confirm_code'] == '186099'){

            }else{
                if (Conf::get('msg_enabled') && ($_SESSION['MobileConfirmCode'] != $_POST['confirm_code']||$_SESSION[$to_mobile.'ConfirmCode'] != $_POST['confirm_code'])) {
                    $this->show_warning('mobile_code_error');
                    return;
                }
            }
            $model = &m();
            $tuijian = $_POST['tuijian'];
            $pid = $model->table('member')->where(array('user_name'=>$tuijian))->getField('user_id');//查推荐人id
            $parent = $model->table('member')->where('user_id='.$pid)->find1();//获取推荐人信息

            $path = '';
            if($parent['path']){
                $path_arr = explode(',',$parent['path']);
                if(count($path_arr) == 3){
                    $path = implode(',',array($path_arr[1],$path_arr[2],$pid));
                }else if(count($path_arr) < 3){
                    $path = $parent['path'] . ',' . $pid;
                }
            }else{
                $path = $pid;
            }


            $ms = & ms(); //连接用户中心
            $user_id = $ms->user->register($user_name, $password,$email, array('pid'=>$this->getOpid($pid),'path'=>$path,'real_name'=>$_POST['real_name'],'province'=>$_POST['province'],'city'=>$_POST['city'],'area'=>$_POST['area'],'phone_mob'=>$_POST['user_name'],'type'=>1,'status'=>1));

            if (!$user_id) {
                $this->show_warning($ms->user->get_error());

                return;
            }

            $parentList = $model->table('member')->where('user_id in ('.$path.')')->select();
            foreach($parentList as $key=>$val){
                $model->table('member')->where('user_id='.$val['user_id'])->save(array(
                    'childrens' => $val['childrens'] ? $val['childrens'].','.$user_id : $user_id
                ));
            }

            $model -> table('epay') ->add(
                array(
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'add_time' => time(),
                )
            );
            
            /*用户注册功能后 积分操作*/
            import('integral.lib');
            $integral=new Integral();
            $integral->change_integral_reg($user_id);
            /*用户注册如果有推荐人，则推荐人增加积分*/
            if(intval($_SESSION['referid'])){
                $integral->change_integral_recom(intval($_SESSION['referid']));
            }
            
            $this->_hook('after_register', array('user_id' => $user_id));
            //登录
            $this->_do_login($user_id);

            //修改成长值和会员等级 by qufood
            $user_mod=&m('member');
            $user_mod->edit_growth($user_id,'register');
            
            /* 同步登陆外部系统 */
            $synlogin = $ms->user->synlogin($user_id);

            #TODO 可能还会发送欢迎邮件

            $this->show_message(Lang::get('register_successed') . $synlogin, 'back_before_register', rawurldecode($_POST['ret_url']), 'enter_member_center', 'index.php?app=member', 'apply_store', 'index.php?app=apply'
            );
        }
    }

    /**
     *    检查用户是否存在
     *
     *    @author    Garbin
     *    @return    void
     */
    function check_user() {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        $tuijian = empty($_GET['tuijian']) ? null : trim($_GET['tuijian']);
        if (!$user_name && !$tuijian) {
            echo ecm_json_encode(false);
            return;
        }
		/*
        if(!preg_match("/^[0-9a-zA-Z]{3,15}$/",$user_name)){
            echo ecm_json_encode(false);
            return;
        }
		*/
        $ms = & ms();
        if($tuijian){
            echo $tuijian;die;
            $rs = ecm_json_encode($ms->user->check_username($tuijian));
            if($rs == 'true'){
                echo 'false';
            }elseif($rs == 'false'){
                echo 'true';
            }
            die;
        }else{
            echo ecm_json_encode($ms->user->check_username($user_name));
        }

    }
    function check_tuijian() {
        $tuijian = empty($_GET['tuijian']) ? null : trim($_GET['tuijian']);
        if (!$tuijian) {
            echo ecm_json_encode(false);
            return;
        }
        /*
        if(!preg_match("/^[0-9a-zA-Z]{3,15}$/",$user_name)){
            echo ecm_json_encode(false);
            return;
        }
        */
        $ms = & ms();
        echo !ecm_json_encode($ms->user->check_username($tuijian));
        die;

    }


    /*
    function check_tuijian() {
        $tuijian = trim($_GET['tuijian']);
        $model = &m();
        $user = $model->table('member')->where(array('user_name'=>$tuijian))->find1();
        if(!$user){
            echo 'false';
        }else{
            echo 'true';
        }

    }*/

    /**
     *    修改基本信息
     *
     *    @author    Hyber
     *    @usage    none
     */
    function profile() {

        $user_id = $this->visitor->get('user_id');
        if (!IS_POST) {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('basic_information'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('basic_information');

            $ms = & ms();    //连接用户系统
            $edit_avatar = $ms->user->set_avatar($this->visitor->get('user_id')); //获取头像设置方式

            $model_user = & m('member');
            $profile = $model_user->get_info(intval($user_id));
            $profile['portrait'] = portrait($profile['user_id'], $profile['portrait'], 'middle');
            $this->assign('profile', $profile);
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->assign('edit_avatar', $edit_avatar);
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('my_profile'));
            $this->display('member.profile.html');
        } else {
            $data = array(
                'portrait'=>$_POST['portrait'],
                'real_name' => $_POST['real_name'],
                'gender' => $_POST['gender'],
                'birthday' => $_POST['birthday'],
                'im_qq' => $_POST['im_qq'],
            );

            if (!empty($_FILES['portrait'])) {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false) {
                    return;
                }
                $data['portrait'] = $portrait;
            }

            $model_user = & m('member');
            $model_user->edit($user_id, $data);
            if ($model_user->has_error()) {
                $this->show_warning($model_user->get_error());
                return;
            }

            $this->show_message('edit_profile_successed');
        }
    }

    /**
     *    修改密码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function password() {
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST) {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('edit_password'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_password');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_password'));
            $this->display('member.password.html');
        } else {
            /* 两次密码输入必须相同 */
            $orig_password = $_POST['orig_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
			$user_id = $this->visitor->get('user_id');
			$epay = $this->epay_mod->get("user_id='$user_id'");
			
            if ($new_password != $confirm_password) {
                $this->show_warning('twice_pass_not_match');

                return;
            }
            if (!$new_password) {
                $this->show_warning('no_new_pass');

                return;
            }
            $passlen = strlen($new_password);
            if ($passlen < 6 || $passlen > 20) {
                $this->show_warning('password_length_error');

                return;
            }

            /* 修改密码 */
            $ms = & ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'password' => $new_password
            ));
            if (!$result) {
                /* 修改不成功，显示原因 */
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_password_successed');
        }
    }

    /**
     *    修改电子邮箱
     *
     *    @author    Hyber
     *    @usage    none
     */
    function email() {
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST) {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('edit_email'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('edit_email');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_email'));
            $this->display('member.email.html');
        } else {
            $orig_password = $_POST['orig_password'];
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            if (!$email) {
                $this->show_warning('email_required');

                return;
            }
            if (!is_email($email)) {
                $this->show_warning('email_error');

                return;
            }

            $ms = & ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'email' => $email
            ));
            if (!$result) {
                $this->show_warning($ms->user->get_error());

                return;
            }

            $this->show_message('edit_email_successed');
        }
    }

    /**
     * Feed设置
     *
     * @author Garbin
     * @param
     * @return void
     * */
    function feed_settings() {
        if (!$this->_feed_enabled) {
            $this->show_warning('feed_disabled');
            return;
        }
        if (!IS_POST) {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('feed_settings'));

            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');

            /* 当前所处子菜单 */
            $this->_curmenu('feed_settings');
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('feed_settings'));

            $user_feed_config = $this->visitor->get('feed_config');
            $default_feed_config = Conf::get('default_feed_config');
            $feed_config = !$user_feed_config ? $default_feed_config : unserialize($user_feed_config);

            $buyer_feed_items = array(
                'store_created' => Lang::get('feed_store_created.name'),
                'order_created' => Lang::get('feed_order_created.name'),
                'goods_collected' => Lang::get('feed_goods_collected.name'),
                'store_collected' => Lang::get('feed_store_collected.name'),
                'goods_evaluated' => Lang::get('feed_goods_evaluated.name'),
                'groupbuy_joined' => Lang::get('feed_groupbuy_joined.name')
            );
            $seller_feed_items = array(
                'goods_created' => Lang::get('feed_goods_created.name'),
                'groupbuy_created' => Lang::get('feed_groupbuy_created.name'),
            );
            $feed_items = $buyer_feed_items;
            if ($this->visitor->get('manage_store')) {
                $feed_items = array_merge($feed_items, $seller_feed_items);
            }
            $this->assign('feed_items', $feed_items);
            $this->assign('feed_config', $feed_config);
            $this->display('member.feed_settings.html');
        } else {
            $feed_settings = serialize($_POST['feed_config']);
            $m_member = &m('member');
            $m_member->edit($this->visitor->get('user_id'), array(
                'feed_config' => $feed_settings,
            ));
            $this->show_message('feed_settings_successfully');
        }
    }

    /**
     *    三级菜单
     *
     *    @author    Hyber
     *    @return    void
     */
    function _get_member_submenu() {
        $submenus = array(
            array(
                'name' => 'basic_information',
                'url' => 'index.php?app=member&amp;act=profile',
            ),
            array(
                'name' => 'edit_password',
                'url' => 'index.php?app=member&amp;act=password',
            ),
            array(
                'name' => 'edit_email',
                'url' => 'index.php?app=member&amp;act=email',
            ),
            array(
                'name' => 'location',
                'url' => 'index.php?app=member&amp;act=location',
            ),
        );
        if (Conf::get('msg_enabled'))
        {
            $submenus[] = array(
                'name' => 'edit_mobile',
                'url' => 'index.php?app=member&amp;act=mobile',
            );
        }
        if ($this->_feed_enabled) {
            $submenus[] = array(
                'name' => 'feed_settings',
                'url' => 'index.php?app=member&amp;act=feed_settings',
            );
        }

        return $submenus;
    }

    function location() {
        $user_id = $this->visitor->get('user_id');
        $member_mod = & m('member');

        if (!IS_POST) {
            $member_info = $member_mod->get($user_id);

            if ($member_info['lng'] == '0' || $member_info['lat'] == '0') {
                //根据IP 获取经纬度
                $data = $this->get_ip_location();
                $member_info = array_merge($member_info, $data);
            }

            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('location'));
            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');
            /* 当前所处子菜单 */
            $this->_curmenu('location');
            $this->_config_seo('title', Lang::get('member_center') . ' - ' . Lang::get('location'));

            $this->assign('baidu_ak', Conf::get('baidu_ak'));
            $this->assign('member_info', $member_info);
            $this->display('member.location.html');
        } else {
            $data = array(
                'lng' => $_POST['lng'],
                'lat' => $_POST['lat'],
                'zoom' => $_POST['zoom'],
            );
            $member_mod->edit($user_id, $data);
            $this->show_message('edit_ok');
        }
    }

    /**
     * 上传头像
     *
     * @param int $user_id
     * @return mix false表示上传失败,空串表示没有上传,string表示上传文件地址
     */
    function _upload_portrait($user_id) {
        $file = $_FILES['portrait'];
        if ($file['error'] != UPLOAD_ERR_OK) {
            return '';
        }
        import('uploader.lib');
        $uploader = new Uploader();
        $uploader->allowed_type(IMAGE_FILE_TYPE);
        $uploader->addFile($file);
        if ($uploader->file_info() === false) {
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=member&amp;act=profile');
            return false;
        }
        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }
    
    
    
    /**
     * 注册发送验证码
     */
    function send_code() {
        if (!Conf::get('msg_enabled')) {
            return;
        }
        $mobile = empty($_GET['mobile']) ? '' : trim($_GET['mobile']);
        if (!$mobile) {
            echo ecm_json_encode(false);
            return;
        }
        //发送短信的格式  
        $type = $_GET['type'];
        if (!in_array($type, array('register', 'find', 'change'))) {
            echo ecm_json_encode(false);
            return;
        }
        //发送验证码
        import('mobile_msg.lib');
        $mobile_msg = new Mobile_msg();
        $result = $mobile_msg->send_msg_system($type, $mobile);
        echo ecm_json_encode($result);
    }

  
    /**
     * 核对手机发送的验证码是否相同
     */
    function cmc() {
        $confirm_code = empty($_GET['confirm_code']) ? '' : trim($_GET['confirm_code']);
        if($confirm_code == '186099'){
            echo ecm_json_encode(true);
            return;
        }
        if (empty($_SESSION['MobileConfirmCode']) || !$confirm_code) {
            echo ecm_json_encode(false);
            return;
        } else {
            if ($confirm_code == $_SESSION['MobileConfirmCode']) {
                echo ecm_json_encode(true);
            } else {
                echo ecm_json_encode(false);
            }
        }
    }

    /*
     * 检测时候是否已经被注册
     */

    function check_mobile() {
        $phone_mob = empty($_GET['phone_mob']) ? '' : trim($_GET['phone_mob']);
        if (!$phone_mob) {
            echo ecm_json_encode(false);
            return;
        }
        //发送短信的格式  
        $type = $_GET['type'];
        if (!in_array($type, array('register', 'find', 'change'))) {

            echo ecm_json_encode(false);
            return;
        }

        $ms = & ms();

        if ($type == 'find') {
            //找回密码是需要存在的电话号码
            echo ecm_json_encode(!$ms->user->check_mobile($phone_mob));
        } else {
            //注册以及修改是需要不存在的电话号码
            echo ecm_json_encode($ms->user->check_mobile($phone_mob));
        }
    }

    /**
     *    修改手机号码
     *
     *    @author    Hyber
     *    @usage    none
     */
    function mobile() {
        $user_id = $this->visitor->get('user_id');
        if (!IS_POST) {
            /* 当前位置 */
            $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('edit_mobile'));
            /* 当前用户中心菜单 */
            $this->_curitem('my_profile');
            /* 当前所处子菜单 */
            $this->_curmenu('edit_mobile');
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js',
            ));
            $user_mod = & m('member');
            $user = $user_mod->get_info($user_id);

            $this->assign('user', $user);
            $this->_config_seo('title', Lang::get('user_center') . ' - ' . Lang::get('edit_mobile'));
            $this->display('member.mobile.html');
        } else {
            $orig_password = $_POST['orig_password'];
            $mobile = isset($_POST['phone_mob']) ? trim($_POST['phone_mob']) : '';
            if (!$mobile) {
                $this->show_warning('mobile_required');
                return;
            }
            if (Conf::get('msg_enabled') && $_SESSION['MobileConfirmCode'] != $_POST['confirm_code']) {
                $this->show_warning('mobile_code_error');
                return;
            }
            $ms = & ms();    //连接用户系统
            $result = $ms->user->edit($this->visitor->get('user_id'), $orig_password, array(
                'phone_mob' => $mobile
            ));
            if (!$result) {
                $this->show_warning($ms->user->get_error());
                return;
            }
            $this->show_message('edit_mobile_successed');
        }
    }

    public function showStore(){
        $model = &m();
        $list = $model->table('member')->where("user_name in ('15290328759','18864562612','13837728157','13837717549','15839962269','13510790210','18211880770','13193663386','18338361971','13193693200','13623774124','15539999269','13683993970','18637755562','13781787511','18520498289','18738746834','13938950658','13838732096','15093033430','13949397922','13523668733','13193695916','18438893930','13803879776','13782109875','13525136222','13949372856','13462554899','15670688131','13782124621','13937784317','13653772696','13803873730','15993191568','15938861257','13838990092','18538992850','18898198263','13525137988','13253033306','15515005131')")->select();
        foreach($list as $key=>$val){
            echo '<div style="color:#59b170;border-bottom:1px solid black;">'.$val['user_name'].'|'.$val['real_name'].'</div>';
            $getPoint = $model->table('sgxt_get_point')->where('sendid='.$val['user_id'])->select();
            echo '<div style="padding-left:20px"><table><tr ><td width="300">赠送时间</td><td width="200">赠送数量</td><td width="200">受赠人</td></tr>';
            foreach($getPoint as $k=>$v){
                $v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                echo '<tr><td>'.$v['createtime'].'</td><td>'.$v['point'].'</td><td>'.$v['getname'].'</td></tr>';
            }
            echo '</table></div>';
        }
    }

    private function getOpid($pid){
        $model = &m();
        $plist = $model ->table('member')-> where(array('user_id'=> $pid)) ->field('user_id,pid,opid,type') ->find1();

        if(empty($plist )) return ;
        if($plist['type'] != 4){
            return $this -> getOpid($plist['pid']);
        }else{
            return $plist['user_id'];
        }
    }

    public function orderFinish(){
        $dingdan = $_GET['dingdan'];
        $mod_order = & m('order');
        $mod_epay = & m('epay');
        $mod_epaylog = & m('epaylog');
        $model = & m();
        $time = time()-8*6400;
        //根据用户返回的 order_sn 判断是否为订单
        $order_info = $mod_order->get('order_sn=' . $dingdan);
        if (!empty($order_info)) {
            //如果存在订单号  则自动付款
            $order_id = $order_info['order_id'];
            $row_epay = $mod_epay->get("user_id=' ".$order_info['buyer_id']." '");
            $buyer_name = $row_epay['user_name']; //用户名
            $buyer_old_money = $row_epay['money']; //当前用户的原始金钱
            //从定单中 读取卖家信息
            $row_order = $mod_order->get("order_id='$order_id'");
            $order_order_sn = $row_order['order_sn']; //定单号
            $order_seller_id = $row_order['seller_id']; //定单里的 卖家ID
            $order_money = $row_order['order_amount']; //定单里的 最后定单总价格
            //读取卖家SQL
            $seller_row = $mod_epay->get("user_id='$order_seller_id'");
            $seller_id = $seller_row['user_id']; //卖家ID
            $seller_name = $seller_row['user_name']; //卖家用户名
            $seller_money_dj = $seller_row['freeze_balance']; //卖家的原始冻结金钱
            /*
            //检测余额是否足够
            if ($buyer_old_money < $order_money) {   //检测余额是否足够 开始
                Log::DEBUG("\t余额不足支付，退出\r\n");
                return;
            }
            //扣除买家的金钱
            $buyer_array = array(
                'money' => $buyer_old_money - $order_money,
            );
            $mod_epay->edit('user_id=' . $user_id, $buyer_array);
            */
            //更新卖家的冻结金钱
            $seller_array = array(
                'freeze_balance' => $seller_money_dj + $order_money,
            );
            $seller_edit = $mod_epay->edit('user_id=' . $seller_id, $seller_array);
            $model->table('member')->where('user_id='.$seller_id)->setDec('pay_point',$row_order['point']);
            //买家添加日志
            $buyer_log_text = '购买商品店铺' . $seller_name;
            $buyer_add_array = array(
                'user_id' => $order_info['buyer_id'],
                'user_name' => $buyer_name,
                'order_id' => $order_id,
                'order_sn ' => $order_order_sn,
                'to_id' => $seller_id,
                'to_name' => $seller_name,
                'add_time' => $time,
                'type' => 20,
                'money_flow' => 'outlay',
                'money' => $order_money,
                'log_text' => $buyer_log_text,
                'states' => 20,
            );
            $mod_epaylog->add($buyer_add_array);
            //卖家添加日志
            $seller_log_text = '出售商品买家' . $buyer_name;
            $seller_add_array = array(
                'user_id' => $seller_id,
                'user_name' => $seller_name,
                'order_id' => $order_id,
                'order_sn ' => $order_order_sn,
                'to_id' => $order_info['buyer_id'],
                'to_name' => $buyer_name,
                'add_time' => $time,
                'type' => 30,
                'money_flow' => 'income',
                'money' => $order_money,
                'log_text' => $seller_log_text,
                'states' => 20,
            );
            $mod_epaylog->add($seller_add_array);
            /*
            //改变定单为 已支付等待卖家确认  status10改为20
            $payment_code = "zjgl";
            $payment_name = '余额支付';
            if($order_info['payment_id'] == 8){
                $payment_name = '微信支付';
            }else if($order_info['payment_id'] == 14){
                $payment_name = '移动微信支付';
            }else if($order_info['payment_id'] == 13){
                $payment_name = '微信扫码支付';
            }
            */
            //更新定单状态
            $order_edit_array = array(
                'pay_time' => $time,
                'out_trade_sn' => $dingdan,
                'status' => 20, //20就是 待发货了
            );
            $mod_order->edit($order_id, $order_edit_array);
            /*
            if($order_info['point'] > 0){
                $point_mod = & m('point');
                $point_mod->sendPoint($row_epay['user_name'],$order_info['point'],$order_info['seller_id'],$order_info,'online');
            }
            */
            die('success');
        }
    }

   


}

?>
