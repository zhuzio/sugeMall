<?php
/* 会员控制器 */
class UserApp extends BackendApp {
    var $_admin_mod;
    var $_user_mod;
    var $weixin_user;
    function __construct() {
        $this->UserApp();
    }
    function UserApp() {
        parent::__construct();
        $this->_user_mod = & m('member');
        $this->_admin_mod = & m('userpriv');
        $this->weixin_user =& m('weixinuser');
        $this->epay_mod = & m('epay');
    }
    function index() {
        $search_options = array(
            'mobile'   => '手机号',
            'real_name' => '姓名'
        );
        if($_GET['field'] == 'mobile' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("phone_mob='".$_GET['search_name']."'")->find1();
            if($user){
                $_GET['userid'] = $user['user_id'];
            }else{
                $this->show_warning('手机号码不存在');
            }
        }
        if($_GET['field'] == 'pid' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("phone_mob='".$_GET['search_name']."'")->find1();
            if($user){
                $_GET['pid'] = $user['user_id'];
            }else{
                $this->show_warning('手机号码不存在');
            }
        }
        //$page   =   $this->_get_page(10);    //获取分页信息
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'pid',
                'equal' => '=',
                'name'  =>  'pid',
                'type'  => 'numeric',
            ),array(
                'field' => 'user_id',
                'equal' => '=',
                'name'  =>  'userid',
                'type'  => 'numeric',
            ),array(
                'field' => $_GET['field_name'],
                'name' => 'field_value',
                'equal' => 'like',
            ),array(
                'field' => 'type',
                'equal' => '=',
                'name'  =>  'type',
                'type'  => 'numeric',
            ),array(
                'field' => 'status',
                'equal' => '=',
                'name'  =>  'status',
                'type'  => 'numeric',
            ),array(
                'field' => 'reg_time',
                'name'  => 'add_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'reg_time',
                'name'  => 'add_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
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
        //var_dump($conditions);exit;
        //更新排序
        if (isset($_GET['sort']) && !empty($_GET['order'])) {
            $sort = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order, array('asc', 'desc'))) {
                $sort = 'user_id';
                $order = 'asc';
            }
        } else {
            if (isset($_GET['sort']) && empty($_GET['order'])) {
                $sort = strtolower(trim($_GET['sort']));
                $order = "";
            } else {
                $sort = 'user_id';
                $order = 'asc';
            }
        }
        $page = $this->_get_page();
        $users = $this->_user_mod->find(array(
            'join' => 'has_store,manage_mall',
            'fields' => 'this.*,store.store_id,userpriv.store_id as priv_store_id,userpriv.privs',
            'conditions' => '1=1' . $conditions,
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        $model = &m();
        $user_type = conf('user_type');
        $user_status = conf('user_status');
        foreach ($users as $key => $val) {
            $parent = array();
            if($val['pid'] > 0){
                $parent = $model->table('member')->where('user_id='.$val['pid'])->find1();
            }
            $val['parent'] = $parent;
            $val['type_cn'] = $user_type[$val['type']];
            $color = 'green';
            if($val['status'] == 0){
                $color = 'red';
            }
            $val['status_cn'] = '<span style="color:'.$color.';">' . $user_status[$val['status']] . '</span>';
            $local = '';
            if($val['province'] && $val['city'] && $val['area']){
                $arealist = $model->table('sgxt_area')->where('id in (' . $val['province'] . ',' . $val['city'] . ',' . $val['area'] . ')')->select();
                $local = $arealist[0]['name'] . ' ' . $arealist[1]['name'] . ' ' . $arealist[2]['name'];
            }
			$dlarea='';
            if($val['ahentarea']>0)
            {
                $dlarealist=$model->query('select * from ecm_sgxt_area where id='.$val['ahentarea']);
                if(!empty($dlarealist))
                {
                    $dlarea=$dlarealist[0]['name'];
                }
            }
            $val['dlarea'] = $dlarea;
            $val['local'] = $local;
            if ($val['priv_store_id'] == 0 && $val['privs'] != '') {
                $users[$key]['if_admin'] = true;
            }
            $val['reg_time'] = date('Y-m-d',$val['reg_time']);

            //剩余权
            $val['last_seed'] = $model->table('sgxt_bean')->where('user_id='.$val['user_id'].' and status=1')->count();
            //减少权
            $val['used_seed'] = $model->table('sgxt_bean')->where('user_id='.$val['user_id'].' and status=2')->count();
            //获取消费积分总额
            $val['all_point'] = $model->table('sgxt_get_point')->where('getid='.$val['user_id'])->sum('point');
            //定返总额
            $val['return_point'] = $model->table('epaylog')->where('user_id='.$val['user_id'].' and type in (140)')->sum('money');
            //未返还消费积分
            $payinfo = conf('PAY_INFO');
            $val['freeze_point'] = $val['last_seed'] * $payinfo['bean'] - $val['return_point'];
            //结余积分

            $users[$key] = $val;
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
        $this->assign('users', $users);
        $page['item_count'] = $this->_user_mod->getCount();
        $this->_format_page($page);
        $this->assign('filtered', $conditions ? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);
        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style' => 'res:style/jqtreetable.css'
        ));
        $this->assign('user_type_list',conf('user_type'));
        $this->assign('user_status_list',conf('user_status'));
        $this->assign('query_fields', array(
            'user_name' => LANG::get('user_name'),
            'real_name' => LANG::get('real_name'),
//            'phone_tel' => LANG::get('phone_tel'),
//            'phone_mob' => LANG::get('phone_mob'),
        ));
        $this->assign('sort_options', array(
            'reg_time DESC' => LANG::get('reg_time'),
            'last_login DESC' => LANG::get('last_login'),
            'logins DESC' => LANG::get('logins'),
        ));
        $this->assign('if_system_manager', $this->_admin_mod->check_system_manager($this->visitor->get('user_id')) ? 1 : 0);
        $this->display('user.index.html');
    }
    function getarea(){
        $model = &m();
        $pid = intval($_POST['pid']);
        $list = $model->table('sgxt_area')->where('parent_id='.$pid)->select();
        echo json_encode(array(
            'status' => 0,
            'msg' => '',
            'data' => $list
        ));
        exit;
    }
    //会员请求升级列表
    function req(){
        $this->model = &m();
        $search_options = array(
            'mobile'   => '手机号',
            'real_name' => '姓名'
        );
		
		 if($_GET['field'] == 'mobile' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("phone_mob='".$_GET['search_name']."'")->find1();
			
            if($user){
                $_GET['user_id'] = $user['user_id'];
				
            }else{
                $this->show_warning('手机号码不存在');
            }
        }
		if($_GET['field'] == 'real_name' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("real_name='".$_GET['search_name']."'")->find1();
            if($user){
                $_GET['user_id'] = $user['user_id'];
            }else{
                $this->show_warning('名字不存在');
            }
        }
        if($_GET['field'] == 'real_name' && $_GET['search_name'] != ''){
            $user = $this->model->table('member')->where("real_name='".$_GET['search_name']."'")->find1();            
            if($user){
                $_GET['user_id'] = $user['user_id'];
            }else{
                $this->show_warning('手机号码不存在');
            }
        }
        /* 默认搜索的字段是店铺名 */
        $field = 'mobile';
        array_key_exists($_GET['field'], $search_options) && $field = $_GET['field'];       
        //$page   =   $this->_get_page(10);    //获取分页信息
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => 'userid',
                'equal' => '=',
                'name'  =>  'user_id',
                'type'  => 'numeric',
            ),array(
                'field' => 'type',
                'equal' => '=',
                'name'  =>  'type',
                'type'  => 'numeric',
            ),array(
                'field' => 'status',
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
                'field' => 'updatetime',
                'name'  => 'update_time_from',
                'equal' => '>=',
                'handler'=> 'gmstr2time',
            ),array(
                'field' => 'updatetime',
                'name'  => 'update_time_to',
                'equal' => '<=',
                'handler'   => 'gmstr2time_end',
            )
        ));
        $conditions = ' 1=1 '.$conditions;
		
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
        $count = $this->model->table('sgxt_req')->where($conditions)->count();
        //$page['item_count'] = $count;
        $list = $this->model->table('sgxt_req')->where($conditions)->page($count)->order("$sort $order")->select();
        $mypage = $this->model->getButton(1);
        $user_type = conf('user_type');
        $req_status = conf('req_status');
        foreach($list as $key=>$val){
            $user = $this->model->table('member')->where('user_id='.$val['userid'])->find1();
            $val['user'] = $user;
            if($val['areaid']){
                $area = $this->model->table('sgxt_area')->where('id='.$val['areaid'])->find1();
            }
			
			if(!empty($area['parent_id'])){
				$xarea = $this->model->table('sgxt_area')->where('id='.$area['parent_id'])->find1();
			}
			if(!empty($xarea['parent_id'])){
				$sarea = $this->model->table('sgxt_area')->where('id='.$xarea['parent_id'])->find1();
			}
			$val['area'] = $area;
			$val['xarea'] = $xarea;
			$val['sarea'] = $sarea;
            if($val['opid']){
                $val['opor'] = $this->model->table('member')->where('user_id='.$val['opid'])->find1();
            }
            $val['type_cn'] = $user_type[$val['type']];
            $val['status_cn'] = $req_status[$val['status']];
            if($val['updatetime']){
                $val['updatetime'] = date("Y-m-d H:i:s",$val['updatetime']);
            }
            $val['createtime'] = date("Y-m-d H:i:s",$val['createtime']);
            $list[$key] = $val;
        }
        //$this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        //$this->assign('page_info', $page);          //将分页信息传递给视图，用于形成分页条
        $this->assign('search_options',$search_options);
        $this->assign('req_type_list',conf('user_type'));
        $this->assign('req_status_list',conf('req_status'));
        $this->assign('list', $list);
        $this->assign('mypage',$mypage);
        $this->display('userreq.index.html');
    }
    //通过会员升级请求
    public function req_accept(){
        $id = intval($_GET['id']);
        $this->model = &m();
        $req = $this->model->table('sgxt_req')->where('id='.$id)->find1();
        if($req){
            $user_type = conf('user_type');
            $area_retrun = conf('area_retrun');
            $grade_money = conf('grade_money');
            //获取请求用户信息
            $req_user = $this->model->table('member')->where('user_id='.$req['userid'])->find1();
            /*
            if($req['type'] == 2){
                $this->model->table('store')->where('store_id='.$req['userid'])->save(array('state'=>1,'apply_time'=>gmtime()));
            }else if($req['type'] == 3){
                $return = $area_retrun[$req['type']];
                //获取三级各级佣金配置
                $sjmoney = $grade_money[$return['money']];
                //获取上三级用户id数组
                $patharr = explode(',',$req_user['path']);
                foreach($patharr as $key=>$val){
                    $parent = $this->model->table('member')->where('user_id='.$val)->find1();
                    //获取当前上级所在层级
                    $level = count($patharr) - $key;
                    //获取对应层级的佣金
                    $money = $sjmoney['level'][$level];
                    //更新佣金记录
                    $this->model->table('epay')->where('user_id='.$val)->setInc('earnings',$money);
                    //添加获取佣金记录
                    $this->model->table('sgxt_commission')->add(array(
                        'fromid' => $req['userid'],
                        'from_name' => $req['real_name'],
                        'toid' => $val,
                        'to_name' => $parent['real_name'],
                        'money' => $money,
                        'info' => '开通销售经理,'.$req_user['real_name'].'('.$val.')获得第'.$level.'级佣金'.$money,
                        'createtime' => gmtime()
                    ));
                }
            }else if($req['type'] == 4){
                //县级代理返现金额
                $areaReturn = doubleval($area_retrun['4']['money'])  * doubleval($area_retrun['4']['parent']);
                //直推佣金
                $parentReturn = doubleval($area_retrun['4']['money'])  * doubleval($area_retrun['4']['reference']);
                //获取县级代理
                $agent = $this->model->table('member')->where('ahentarea='.$req_user['area'])->find1();
                //佣金对象
                $commissionData = array(
                    'fromid' => $req['userid'],
                    'from_name' => $req['from_name'],
                    'toid' => 0,
                    'to_name' => '',
                    'money' => $areaReturn,
                    'info' => '',
                    'createtime' => gmtime()
                );
                //如果有县级代理，发放县级代理返现
                if($agent){
                    $this->model->table('epay')->where('user_id='.$agent['user_id'])->setInc('earnings',$areaReturn);
                    $commissionData['toid'] = $agent['user_id'];
                    $commissionData['money'] = $areaReturn;
                    $commissionData['info'] = '开通区域代理,上级区域代理'.$agent['real_name'].'获得佣金'.$areaReturn;
                    $this->model->table('sgxt_commission')->add($commissionData);
                }else{
                    $commissionData['money'] = $areaReturn;
                    $commissionData['info'] = '开通区域代理,暂无区域代理，资金沉淀：'.$areaReturn;
                    $this->model->table('sgxt_commission')->add($commissionData);
                }
                //直推奖金
                $this->model->table('epay')->where('user_id='.$req_user['pid'])->setInc('earnings',$parentReturn);
                $zhitui = $this->model->table('member')->where('user_id='.$req_user['pid'])->find1();
                $commissionData['toid'] = $req_user['pid'];
                $commissionData['money'] = $parentReturn;
                $commissionData['info'] = '开通区域代理, 推荐人'.$zhitui['real_name'].'获的佣金'.$parentReturn;
                $this->model->table('sgxt_commission')->add($commissionData);
            }*/
            //记录管理员操作日志
            $this->model->table('sgxt_oplog')->add(array(
                'obj_id' => $req['userid'],
                'obj_type' => 'user',
                'opid' => $this->visitor->get('user_id'),
                'info' => $this->visitor->get('user_name') . '通过了升级请求(id:'.$req['userid'].')',
                'createtime' => gmtime()
            ));
            $this->model->table('sgxt_req')->where('id='.$id)->save(array('status'=>2,'updatetime'=>gmtime()));
            $this->model->table('member') -> where(array('user_id' => $req['userid'])) ->save(array('type' => $req['type'] , 'ahentarea' => $req['areaid']));
            $this->model->table('store')->where('store_id='.$req['userid'])->update(array('state'=>1,'apply_time'=>time()));
            $this->show_message('操作成功');
        }else{
            $this->show_warning('申请不存在');
        }
    }
    //驳回升级申请
    public function req_cancle(){
        $id = intval($_GET['id']);
        $this->model = &m();
        $req = $this->model->table('sgxt_req')->where('id='.$id)->find1();
        if($req){
            $this->model->table('sgxt_req')->where('id='.$id)->save(array('status'=>3));
            $this->model->table('sgxt_oplog')->add(array(
                'obj_id' => $req['userid'],
                'obj_type' => 'user',
                'opid' => $this->visitor->get('user_id'),
                'info' => $this->visitor->get('user_name') . '驳回了升级请求(id:'.$req['userid'].')',
                'createtime' => gmtime()
            ));
            $this->show_message('操作成功');
        }else{
            $this->show_warning('申请不存在');
        }
    }
    function send()
    {
        $id = empty($_GET['wxid']) ? 0 : $_GET['wxid'];
        if (!IS_POST){
            $wxmessage =& m('wxmessage');
            $wxmessage_list = $wxmessage->find(array(
                'conditions' => "1=1 AND wxid='$id'",
                'limit'=>'5',
                'order' => "id desc",
            ));
            $weixinuser=& m('weixinuser');
            $wxinfo= $weixinuser->get("wxid='$id'");
            $this->assign('wxinfo', $wxinfo);
            $this->assign('wxmessage_list', $wxmessage_list);
            //print_r($wxmessage_List);
            $this->display('wx.from.html');
        }else{
            $wxid = $_POST['wxid'];
            $content = $_POST['msg_content'];
            $uid = $_POST['uid'];
            $time = time();
            if(empty($content))
            {
                $this->show_warning('内容不能为空');
                return;
            }
            import('weixin.lib');
            $wxconfig=	& m('wxconfig');
            $config = $wxconfig->get_info_user(2);
            if (empty($config)) {
                exit;
            }
            $token = $config['token'];
            $ACCESS_LIST = Init_Weixin::curl($config['appid'], $config['appsecret']);
            $access_token= $ACCESS_LIST['access_token'];
            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;
            $post_msg = '{
            "touser":"'.$wxid.'",
            "msgtype":"text",
            "text":
            {
                 "content":"'.$content.'"
            }
        }';
            $ret_json = $this->curl_grab_page($url, $post_msg);
            $ret = json_decode($ret_json);
            if($ret->errcode == '40001')
            {
                $this->show_warning('您公众号不支持接口');
                return;
            }   elseif($ret->errcode == '45015')
            {
                $this->show_warning('回复时间超过限制');
                return;
            }     elseif($ret->errcode == '0')
            {
                $data=array(
                    'wxid'=>$wxid,
                    'w_message'=>$content,
                    'dateline'=>time(),
                );
                $wxmessage =& m('wxmessage');
                $wxmessage->add($data);
                $this->show_message('回复成功' );
            }
        }
    }
    function view()
    {
        $id = empty($_GET['wxid']) ? 0 : $_GET['wxid'];
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));
        $page = $this->_get_page();
        $wxmessage =& m('wxmessage');
        $wxmessage_list = $wxmessage->find(array(
            'conditions' => "1=1 AND wxid='$id' ".$conditions,
            'limit' => $page['limit'],
            'count' => true,
            'order' => "id desc",
        ));
        $page['item_count'] = $this->_user_mod->getCount();
        $this->_format_page($page);
        $weixinuser=& m('weixinuser');
        $wxinfo= $weixinuser->get("wxid='$id'");
        $this->assign('page_info', $page);
        $this->assign('id', $id);
        $this->assign('wxinfo', $wxinfo);
        $this->assign('wxmessage_list', $wxmessage_list);
        $this->assign('query_fields', array(
            'message' =>"发送内容",
            'w_message' =>"回复内容",
        ));
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->display('wx.view.html');
    }
    function curl_grab_page($url,$data,$proxy='',$proxystatus='',$ref_url='')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($proxystatus == 'true') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        if(!empty($ref_url)){
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_REFERER, $ref_url);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        ob_start();
        return curl_exec ($ch); // execute the curl command
        ob_end_clean();
        curl_close ($ch);
        unset($ch);
    }
    function weixin()
    {
        $conditions = $this->_get_query_conditions(array(
            array(
                'field' => $_GET['field_name'],
                'name'  => 'field_value',
                'equal' => 'like',
            ),
        ));
        //更新排序
        if (isset($_GET['sort']) && !empty($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
                $sort  = 'user_id';
                $order = 'asc';
            }
        }
        else
        {
            if (isset($_GET['sort']) && empty($_GET['order']))
            {
                $sort  = strtolower(trim($_GET['sort']));
                $order = "";
            }
            else
            {
                $sort  = 'uid';
                $order = 'DESC';
            }
        }
        $page = $this->_get_page(10);
        $users = $this->weixin_user->find(array(
            'join' => 'belongs_to_user',
            'conditions' => '1=1' . $conditions,
            'limit' => $page['limit'],
            'order' => "$sort $order",
            'count' => true,
        ));
        $this->assign('users', $users);
        $page['item_count'] = $this->weixin_user->getCount();
        $this->_format_page($page);
        $this->assign('filtered', $conditions? 1 : 0); //是否有查询条件
        $this->assign('page_info', $page);
        /* 导入jQuery的表单验证插件 */
        $this->import_resource(array(
            'script' => 'jqtreetable.js,inline_edit.js',
            'style'  => 'res:style/jqtreetable.css'
        ));
        $this->assign('query_fields', array(
            'nickname' =>"微信用户名",
            'user_name' =>"用户名",
        ));
        $this->assign('sort_options', array(
            'subscribe_time DESC'   => '关注时间',
        ));
        $this->assign('if_system_manager', $this->_admin_mod->check_system_manager($this->visitor->get('user_id')) ? 1 : 0);
        $this->display('wx.index.html');
    }
    function super_login() {
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $_SESSION['super_user_id'] = $user_id;
        $url = 'Location:'.SITE_URL.'/index.php?app=member';
        header($url);
    }
    function add() {
        if (!IS_POST) {
            $this->assign('user', array(
                'gender' => 0,
            ));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            //获取会员等级信息 by qufood
            $ugrade_mod = &m('ugrade');
            $member_mod = &m('member');
            $user = $user + $member_mod->get_grade_info($id);
            $ugrades = $ugrade_mod->get_option('grade_name');
            $this->assign('ugrades', $ugrades);
            //
            $ms = & ms();
            $this->assign('set_avatar', $ms->user->set_avatar());
            $this->display('user.form.html');
        } else {
            $user_name = trim($_POST['user_name']);
            $password = trim($_POST['password']);
            $tuijian = trim($_POST['tuijian']);
            $email = trim($_POST['email']);
            $real_name = trim($_POST['real_name']);
            $gender = trim($_POST['gender']);
            $im_qq = trim($_POST['im_qq']);
            $im_msn = trim($_POST['im_msn']);
            $model = &m();
            if($user_name == '' || strlen($user_name) != 11 || !is_numeric($user_name) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $user_name)){
                $this->show_warning('会员手机号非法');die;
            }else{
                $user = $model->table('member')->where(array('user_name'=>$user_name))->find1();
                if($user){
                    $this->show_warning('手机号已存在');die;
                }
            }
            if (strlen($password) < 6 || strlen($password) > 20) {
                $this->show_warning('password_length_error');
                return;
            }
            if($tuijian == '' || strlen($tuijian) != 11 || !is_numeric($tuijian) || !preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $tuijian)){
                $this->show_warning('推荐人手机号非法');die;
            }else{
                $user = $model->table('member')->where(array('user_name'=>$tuijian))->find1();
                if(!$user){
                    $this->show_warning('推荐人不存在');die;
                }
            }
            /*
            if (!is_email($email)) {
                $this->show_warning('email_error');
                return;
            }
            */
            /* 连接用户系统 */
            $ms = & ms();
            /* 检查名称是否已存在 */
            if (!$ms->user->check_username($user_name)) {
                $this->show_warning($ms->user->get_error());
                return;
            }
            /* 保存本地资料 */
            $data = array(
                'real_name' => $_POST['real_name'],
                'gender' => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq' => $_POST['im_qq'],
                'im_msn' => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
                'reg_time' => gmtime(),
            );
            /* 到用户系统中注册 */
            $user_id = $ms->user->register($user_name, $password, $email, $data);
            if (!$user_id) {
                $this->show_warning($ms->user->get_error());
                return;
            }
            if (!empty($_FILES['portrait'])) {
                $portrait = $this->_upload_portrait($user_id);
                if ($portrait === false) {
                    return;
                }
                $portrait && $this->_user_mod->edit($user_id, array('portrait' => $portrait));
            }
            $this->show_message('add_ok', 'back_list', 'index.php?app=user', 'continue_add', 'index.php?app=user&amp;act=add'
            );
        }
    }
    /* 检查会员名称的唯一性 */
    function check_user() {
        $user_name = empty($_GET['user_name']) ? null : trim($_GET['user_name']);
        if (!$user_name) {
            echo ecm_json_encode(false);
            return;
        }
        /* 连接到用户系统 */
        $ms = & ms();
        $model = &m();
        $id = intval($_GET['id']);
        if($id){
            $user = $model->table('member')->where("user_id=".$id)->find1();
            if($user){
                if($user['user_name'] == $_GET['user_name']){
                    echo true;
                }else{
                    echo ecm_json_encode($ms->user->check_username($user_name));
                }
            }
            //$user = $ms->table('member')->where("user_name='".$_GET['user_name']."'")->find1();
        }else{
            echo ecm_json_encode($ms->user->check_username($user_name));
        }
    }
    /* 检查推荐人是否存在 */
    function check_tuijian() {
        $tuijian = trim($_GET['tuijian']);
        $model = &m();
        $user = $model->table('member')->where(array('user_name'=>$tuijian))->find1();
        if(!$user){
            echo 'false';
        }else{
            echo 'true';
        }
    }
    //冻结用户
    function changeStatus(){
        $model = &m();
        $id = intval($_GET['id']);
        $status = intval($_GET['status']);
        if($status != 1 && $status != 0){
            $this->show_warning('操作失败');
        }
        $user = $model->table('member')->where('user_id='.$id)->find1();
        $str = '冻结';
        if($status == 1){
            $str = '恢复';
        }
        if($user){
            $model->table('member')->where('user_id='.$id)->save(array('status'=>$status));
            $model->table('sgxt_oplog')->add(array(
                'obj_id' => $id,
                'obj_type' => 'user',
                'opid' => $this->userinfo['user_id'],
                'info' => '管理员'.$str.'用户('.$this->userinfo['user_name'].')',
                'createtime' => gmtime()
            ));
            $this->show_message('操作成功');
        }else{
            $this->show_warning('用户不存在');
        }
    }
    //获取用户各种财富统计信息
    function userAccountsDetail($user){
        $user_type = conf('user_type');
        $model = &m();
        $return = array();
        //所有用户共有收益,TYPE=1
        //每日定返购物积分
        $eachDayReturn = $model->table('sgxt_balance')->where('user_id='.$user['user_id'])->sum('get_money');
        $return['each_day_return'] = $eachDayReturn ? $eachDayReturn : 0;
        //收到的积分
        $getPoint = $model->table('sgxt_get_point')->where('getid='.$user['user_id'])->sum('point');
        $return['get_point'] = $getPoint ? $getPoint : 0;
        //使用的购物积分
        $usedPoint = $model->table('sgxt_get_point')->where('sendid='.$user['user_id'])->sum('point');
        $return['use_point'] = $usedPoint ? $usedPoint : 0;
        if($user['type'] > 1){
            //直推佣金
            $selfCommission = $model->table('sgxt_commission')->where('toid='.$user['user_id'])->sum('money');
            $return['self_commission'] = $selfCommission ? $selfCommission : 0;
            //三级关系定返提成
            $teamCommission = $model->table('sgxt_balance')->where('user_id='.$user['user_id'] . ' and source_type=1')->sum('get_money');
            $return['team_commission'] = $teamCommission ? $teamCommission : 0;
        }
        //商户收益，TYPE=2
        if($user['type'] == 2){
            //货款购买的积分----------
            $buyPoint = $model->table('sgxt_order')->where('userid='.$user['user_id'] . ' and paytype=\'balance\' and status=1')->sum('num');
            $return['buy_point'] = $buyPoint ? $buyPoint : 0;
            //微信支付购买的积分
            $wxBuyPoint = $model->table('sgxt_order')->where('userid='.$user['user_id'] . ' and paytype=\'wx\' and status=1')->sum('num');
            $return['wx_buy_point'] = $wxBuyPoint ? $wxBuyPoint : 0;
            //发出的积分
            $sendPoint = $model->table('sgxt_get_point')->where('sendid='.$user['user_id'])->sum('point');
            $return['send_point'] = $sendPoint ? $sendPoint : 0;
            //收到用户支付的购物积分
            $payPoint = $model->table('sgxt_balance')->where('user_id='.$user['user_id'])->sum('get_money');
            $return['pay_point'] = $payPoint ? $payPoint : 0;
            //直推商家佣金
            $selfShopCommission = $model->table('sgxt_balance')->where('user_id='.$user['user_id'] . ' and source_type=4')->sum('get_money');
            $return['self_shop_commission'] = $selfShopCommission ? $selfShopCommission : 0;
        }elseif($user['type'] > 2){
            //代理费定返
            $eachDayAgentReturn = $model->table('sgxt_balance')->where('user_id='.$user['user_id'] . ' and source_type=3')->sum('get_money');
            $return['each_day_agent_return'] = $eachDayAgentReturn ? $eachDayAgentReturn : 0;
            if($user['type'] == 4){
                //区域商家发积分提成
                $areaShopSendPointReturn = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=5')->sum('remain_money');
                $return['area_shop_send_point_return'] = $areaShopSendPointReturn ? $areaShopSendPointReturn : 0;
                //直推商家佣金
                $selfShopProfit = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=4')->sum('remain_money');
                $return['self_shop_profit'] = $selfShopProfit ? $selfShopProfit : 0;
            }elseif($user['type'] == 5){
                //区域商家发积分提成
                $areaShopSendPointReturn = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=5')->sum('remain_money');
                $return['area_shop_send_point_return'] = $areaShopSendPointReturn ? $areaShopSendPointReturn : 0;
                //直推商家佣金
                $selfShopProfit = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=4')->sum('remain_money');
                $return['self_shop_profit'] = $selfShopProfit ? $selfShopProfit : 0;
                //区域会员收积分提成
                $selfShopProfit = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=7')->sum('remain_money');
                $return['self_shop_profit'] = $selfShopProfit ? $selfShopProfit : 0;
                //直推县级代理商家发积分提成
                $selfShopProfit = $model->table('sgxt_profit')->where('user_id='.$user['user_id'].' and source_type=6')->sum('remain_money');
                $return['self_shop_profit'] = $selfShopProfit ? $selfShopProfit : 0;
            }
        }
        return $return;
    }
    //用户财富，默认显示周月年的总收入、总支出、总提现信息
    function wealth(){
        $model = &m();
        $id = intval($_GET['id']);
        $user = $model->table('member')->where('user_id='.$id)->find1();
        $nowTime = gmtime();
        $add_time_from = $_GET['add_time_from'];
        $add_time_to = $_GET['add_time_to'];
        $conditions = array();
        $weekStart = $nowTime - 86400*7;
        $monthStart = $nowTime - 86400*30;
        $yearStart = $nowTime - 86400*365;
        $income = $pay = $cash = 0;
        $return = array();
        if($user){
            if($user['type'] == 1){
                $return['week']['pay'] = $model->table('payment_log')->where('add_time>'.$weekStart.' and add_time<'.$nowTime.' and payment_id in (1,2,3) and user_id='.$id)->sum('money');
                $return['week']['income'] = $model->table('sgxt_get_point')->where('createtime>'.$weekStart.' and createtime<'.$nowTime.' and source_type=2 and getid='.$id)->sum('get_money');
                $return['week']['cash'] = 0;
                $return['month']['pay'] = $model->table('payment_log')->where('add_time>'.$monthStart.' and add_time<'.$nowTime.' and payment_id in (1,2,3) and user_id='.$id)->sum('money');
                $return['month']['income'] = $model->table('sgxt_get_point')->where('createtime>'.$monthStart.' and createtime<'.$nowTime.' and source_type=2 and getid='.$id)->sum('get_money');
                $return['month']['cash'] = 0;
                $return['year']['pay'] = $model->table('payment_log')->where('add_time>'.$yearStart.' and add_time<'.$nowTime.' and payment_id in (1,2,3) and user_id='.$id)->sum('money');
                $return['year']['income'] = $model->table('sgxt_get_point')->where('createtime>'.$yearStart.' and createtime<'.$nowTime.' and source_type=2 and getid='.$id)->sum('get_money');
                $return['year']['cash'] = 0;
            }else{
                //非用户收入部分：定返购物积分+定返三级佣金+
            }
        }else{
            $this->show_warning('用户不存在');
        }
    }
    function edit() {
        $ugrade_mod=&m('ugrade');//by qufood
        $model = &m();
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        //判断是否是系统初始管理员，如果是系统管理员，必须是自己才能编辑，其他管理员不能编辑系统管理员
        if ($this->_admin_mod->check_system_manager($id) && !$this->_admin_mod->check_system_manager($this->visitor->get('user_id'))) {
            $this->show_warning('system_admin_edit');
            return;
        }
        if (!IS_POST) {
            /* 是否存在 */
            $user = $this->_user_mod->get_info($id);
            if (!$user) {
                $this->show_warning('user_empty');
                return;
            }
            //获取会员等级信息 by qufood
            $member_mod = &m('member');
            $user = $user + $member_mod->get_grade_info($id);
            $ugrades = $ugrade_mod->get_option('grade_name');
            $this->assign('ugrades', $ugrades);
            $user_type = conf('user_type');
            $income = $this->userAccountsDetail($user);
            $this->assign('income',$income);
            $user['type_cn'] = $user_type[$user['type']];
            $ms = & ms();
            $this->assign('set_avatar', $ms->user->set_avatar($id));
            $this->assign('user', $user);
            $this->assign('action','edit');
            $this->assign('phone_tel', explode('-', $user['phone_tel']));
            /* 导入jQuery的表单验证插件 */
            $this->import_resource(array(
                'script' => 'jquery.plugins/jquery.validate.js'
            ));
            $this->display('user.form.html');
        } else {
            $editUser = $this->_user_mod->get_info($id);
            $data = array(
                'user_name' => $_POST['user_name'],
                'real_name' => $_POST['real_name'],
                'phone_mob' => $_POST['phone_mob'],
                // 'gender' => $_POST['genderadmin/app/user.app.php:989'],
                'gender' => $_POST['gender'],
//                'phone_tel' => join('-', $_POST['phone_tel']),
//                'phone_mob' => $_POST['phone_mob'],
                'im_qq' => $_POST['im_qq'],
                'im_msn' => $_POST['im_msn'],
//                'im_skype'  => $_POST['im_skype'],
//                'im_yahoo'  => $_POST['im_yahoo'],
//                'im_aliww'  => $_POST['im_aliww'],
            );
            //当输入积分大于 0
            $point = intval($_POST['point']);
            if ($point > 0) {
                $user = $this->_user_mod->get_info($id);
                $model = &m();
                $model->table('member')->where('user_id='.$id)->setInc('agent_money',$point);
                $model->table('sgxt_subsidy')->add(array(
                    'user_id' => $user['user_id'],
                    'user_name' => $user['user_name'],
                    'point' => $point,
                    'province' => $user['province'],
                    'city' => $user['city'],
                    'area' => $user['area'],
                    'add_time' => gmtime(),
                    'opid' => $this->userinfo['user_id'],
                ));
                $model->table('sgxt_oplog')->add(array(
                    'obj_id' => $id,
                    'obj_type' => 'user',
                    'opid' => $this->userinfo['user_id'],
                    'info' => '管理员给用户['.$user['real_name'].']赠送了'.$point.'积分',
                    'createtime' => gmtime()
                ));
                /*
                //选项为 增加积分
                if ($_POST['point_change'] == 'inc_by') {
                    $data['integral'] = $user['integral'] + $point;
                    $data['total_integral'] = $user['total_integral'] + $point;
                    //操作记录入积分记录
                    $integral_log_mod = &m('integral_log');
                    $integral_log = array(
                        'user_id' => $user['user_id'],
                        'user_name' => $user['user_name'],
                        'point' => $point,
                        'add_time' => gmtime(),
                        'remark' => '管理员新增积分' . $point,
                        'integral_type' => INTEGRAL_ADD,
                    );
                    $integral_log_mod->add($integral_log);
                }
                //选项为 减少积分
                if ($_POST['point_change'] == 'dec_by') {
                    //如果当前的可用积分小于扣除的积分  则不做操作
                    if ($user['integral'] >= $point) {
                        $data['integral'] = $user['integral'] - $point;
                        //$data['total_integral'] = $user['total_integral'] - $point;
                        //操作记录入积分记录
                        $integral_log_mod = &m('integral_log');
                        $integral_log = array(
                            'user_id' => $user['user_id'],
                            'user_name' => $user['user_name'],
                            'point' => $point,
                            'add_time' => gmtime(),
                            'remark' => '管理员扣除积分' . $point,
                            'integral_type' => INTEGRAL_SUB,
                        );
                        $integral_log_mod->add($integral_log);
                    }
                }
                */
            }
            //管理员编辑会员等级后，也把该会员的会员积分修改为该会员等级所需要的最低积分 by qufood
            $ugrade_info = $ugrade_mod->get($_POST['grade_id']);
            $data['ugrade'] = $ugrade_info['grade'];
            $data['growth'] = $ugrade_info['floor_growth'];
            //end
            $changeUserName = false;
            if(!empty($_POST['user_name']) && $editUser['user_name'] != $_POST['user_name']){
                $changeUserName = true;
                $phone_mob = trim($_POST['user_name']);
                if (!preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $phone_mob)) {
                    $this->show_warning('手机号不正确');
                    return;
                }else{
                    $user = $model->table('member')->where(array('user_name'=>$phone_mob))->find1();
                    if($user){
                        $this->show_warning('手机号已被使用');
                        return;
                    }else{
                        $data['phone_mob'] = $phone_mob;
                        $data['user_name'] = $phone_mob;
                    }
                }
            }
            if (!empty($_POST['password'])) {
                $password = trim($_POST['password']);
                if (strlen($password) < 6 || strlen($password) > 20) {
                    $this->show_warning('password_length_error');
                    return;
                }
            }
            /*
            if (!empty($_POST['phone_mob'])) {
                $phone_mob = trim($_POST['phone_mob']);
                if (!preg_match('#^13[\d]{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18[\d]{9}$#', $phone_mob)) {
                    $this->show_warning('手机号不正确');
                    return;
                }else{
                    $user = $model->table('member')->where(array('user_name'=>$phone_mob))->find();
                    if($user){
                        $this->show_warning('手机号已被使用');
                        return;
                    }else{
                        $data['phone_mob'] = $phone_mob;
                        $data['user_name'] = $phone_mob;
                    }
                }
            }
            */
            /*
            if (!is_email(trim($_POST['email']))) {
                $this->show_warning('email_error');
                return;
            }
            */
            if (!empty($_FILES['portrait'])) {
                $portrait = $this->_upload_portrait($id);
                if ($portrait === false) {
                    return;
                }
                $data['portrait'] = $portrait;
            }
            if (!empty($_POST['cz_zfpass'])) {
                $zf_pass= array();
                $zf_pass['zf_pass']='';
                $this->epay_mod->edit($id,$zf_pass);
            }
            /* 修改本地数据 */
            $this->_user_mod->edit($id, $data);
            /* 修改用户系统数据 */
            $user_data = array();
            !empty($_POST['password']) && $user_data['password'] = trim($_POST['password']);
            !empty($_POST['email']) && $user_data['email'] = trim($_POST['email']);
            if (!empty($user_data)) {
                $ms = & ms();
                $ms->user->edit($id, '', $user_data, true);
            }
            //如果手机号变更则更改epay,epaylog,order,paymentlog,sgxt_deposit,sgxt_order表中的对应的记录数据
            if($changeUserName){
                //更改epay表数据
                $model->table('epay')->where('user_id='.$id)->save(array('user_name'=>$data['user_name']));
                //更改epaylog表数据
                $model->table('epaylog')->where("user_id=".$id." and user_name='".$_POST['user_name']."'")->save(array('user_name'=>$data['user_name']));
                $model->table('epaylog')->where('to_id='.$id.' and to_name='.$_POST['user_name'])->save(array('to_name'=>$data['user_name']));
                //更改order表数据
                $model->table('order')->where('buyer_id='.$id)->save(array('buyer_name'=>$data['user_name']));
                //更改paymentlog表数据
                $model->table('paymentlog')->where('to_id='.$id)->save(array('to_name'=>$data['user_name']));
                //更改sgxt_deposit表数据
                $model->table('sgxt_deposit')->where('userid='.$id)->save(array('mobile'=>$data['user_name']));
                //更改sgxt_order表数据
                $model->table('sgxt_order')->where('userid='.$id)->save(array('mobile'=>$data['user_name']));
            }
            $this->show_message('edit_ok', 'back_list', 'index.php?app=user', 'edit_again', 'index.php?app=user&amp;act=edit&amp;id=' . $id
            );
        }
    }
    function drop() {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id) {
            $this->show_warning('no_user_to_drop');
            return;
        }
        $admin_mod = & m('userpriv');
        if (!$admin_mod->check_admin($id)) {
            $this->show_message('cannot_drop_admin', 'drop_admin', 'index.php?app=admin');
            return;
        }
        if (!$this->check_store($id)) {
            $this->show_message('cannot_drop_store', 'drop_store', 'index.php?app=store');
            return;
        }
        $ids = explode(',', $id);
        /* 连接用户系统，从用户系统中删除会员 */
        $ms = & ms();
        if (!$ms->user->drop($ids)) {
            $this->show_warning($ms->user->get_error());
            return;
        }
        $this->show_message('drop_ok');
    }
    //检测删除的用户是否存在店铺  如果存在 需要先删除店铺 以便删除相关多余本地图片
    function check_store($user_id) {
        $conditions = "store_id in (" . $user_id . ")";
        $store_mod = & m('store');
        return count($store_mod->find(array('conditions' => $conditions))) == 0;
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
            $this->show_warning($uploader->get_error(), 'go_back', 'index.php?app=user&amp;act=edit&amp;id=' . $user_id);
            return false;
        }
        $uploader->root_dir(ROOT_PATH);
        return $uploader->save('data/files/mall/portrait/' . ceil($user_id / 500), $user_id);
    }
}
?>
