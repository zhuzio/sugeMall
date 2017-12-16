<?php

/**
 *    Desc
 *
 *    @author    Garbin
 *    @usage    none
 */
class TeamApp extends MemberbaseApp {

    var $user_id;
    var $_member_mod;
    var $model;

    function __construct() {
        $this->ReferApp();
    }

    function ReferApp() {

        parent::__construct();
        $this->user_id = $this->visitor->get('user_id');
        $this->_member_mod = & m('member');
        $this->model = & m();
    }

    function index() {

        //获取当前用户的信息
        $user = $this->model->table('member')->where('user_id='.$this->user_id)->find1();
        $this->assign('member_info', $user);
        $childrens = array();
        $data = array(
            '1' => array('num'=>0,'list'=>array()),
            '2' => array('num'=>0,'list'=>array()),
            '3' => array('num'=>0,'list'=>array()),
        );
        $team1 = $team2 = $team3 = array();
        if($user['childrens']){
            $childrens = $this->model->table('member')->where('user_id in ('.$user['childrens'].')')->select();
            $pids = $parentlist = $list = array();
            foreach($childrens as $key=>$val){
                if(!in_array($val['pid'],$pids)){
                    $pids[] = $val['pid'];
                }
            }
            if(count($pids) > 0){
                $list = $this->model->table('member')->where('user_id in ('.implode(',',$pids).')')->select();
            }

            foreach($list as $key=>$val){
                $parentlist[$val['id']] = $val;
            }
            $usertype = conf('user_type');
            foreach($childrens as $key=>$val){
                $parent = $this->model->table('member')->where('user_id='.$val['pid'])->find1();
                $paths = explode(',',$val['path']);
                $teamer['type'] = $usertype[$val['type']];
                $teamer['add_time'] = date('Y-m-d H:i:s',$val['reg_time']);
                $teamer['pname'] = $parent['real_name'];
                $teamer['pid'] = $val['pid'];
                $teamer['p_mobile'] = $parent['user_name'];
                $teamer['real_name'] = $val['real_name'];
                $teamer['mobile'] = $val['user_name'];
                $teamer['points'] = $this ->getPoints($val['user_id']);
                $index = array_keys($paths,$user['user_id'],true);
                $index = strval(count($paths) - $index[0]);
                $num = $data[$index]['num'];
                $data[$index]['list'][] = $teamer;
                $data[$index]['num'] = $num+1;
            }

//            $url = SITE_URL . '/index.php?app=member%26act=register%26referid=' . $this->user_id;
//            $scan_code = '<img src=' . SITE_URL . '/index.php?app=qrcode&url=' . $url . ' />';
//            $this->assign('scan_code', $scan_code);
            $this->assign('data',$data);
            $this->assign('team1',$data[1]['list']);
            $this->assign('team2',$data[2]['list']);
            $this->assign('team3',$data[3]['list']);
            //print_r($data);
        }
        $this->display('newapp/user.team.html');
    }

    function getPoints($id){
        $point = $this->model->table('sgxt_get_point')->where('sendid='.$id)->sum('point');
        return $point;
    }

    function all_refer() {
        //获取相关子孙推荐人
        $all_refers = $this->_get_all_refers($this->user_id);
        $this->assign('all_refers', $all_refers);

        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('refer'));
        /* 当前用户中心菜单 */
        $this->_curitem('refer');
        $this->_curmenu('all_refer');

        $this->display('refer.all_refer.html');
    }

    function _get_all_refers($user_id) {

        //获取所有用户 包含子孙
        $members = $this->_member_mod->find();

        import('tree.lib');
        $tree = new Tree();
        $tree->setTree($members, 'user_id', 'referid', 'user_name');


        return $tree->getOptions(0, $user_id, NULL, '<img src="' . site_url() . '/themes/mall/jd2015/styles/default/images/treetable/tv-item-last.gif" class="ttimage">');
//        return $tree->getArrayList(0);
    }

    function _get_member_submenu() {
        $array = array(
            array(
                'name' => 'refer',
                'url' => 'index.php?app=refer',
            ),
            array(
                'name' => 'all_refer',
                'url' => 'index.php?app=refer&act=all_refer',
            ),
            array(
                'name' => 'refer_user1',
                'url' => 'index.php?app=refer&act=refer_user1',
            ),
            array(
                'name' => 'refer_user2',
                'url' => 'index.php?app=refer&act=refer_user2',
            ),
            array(
                'name' => 'refer_user3',
                'url' => 'index.php?app=refer&act=refer_user3',
            ),
        );
        return $array;
    }

    /**
     * 一级推荐人
     */
    function refer_user1() {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('refer_user1'));
        /* 当前用户中心菜单 */
        $this->_curitem('refer');
        $this->_curmenu('refer_user1');


        $page = $this->_get_page();
        $refers1 = $this->_member_mod->findAll(
                array(
                    'conditions' => 'referid=' . $this->user_id,
                    'count' => true,
                    'limit' => $page['limit'],
                )
        );
        $page['item_count'] = $this->_member_mod->getCount();
        $this->_format_page($page);
        $this->assign('page_info', $page);
        $this->assign('refers', $refers1);
        $this->display('refer.refer.html');
    }

    function refer_user2() {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('refer_user2'));
        /* 当前用户中心菜单 */
        $this->_curitem('refer');
        $this->_curmenu('refer_user2');

        //首先获得一级的推荐人列表
        $refers1 = $this->_member_mod->findAll(
                array(
                    'fields' => 'referid',
                    'conditions' => 'referid=' . $this->user_id,
                )
        );

        //如果有推荐人
        if (!empty($refers1)) {
            $ids = array();
            foreach ($refers1 as $key => $refer) {
                $ids[] = $refer['user_id'];
            }
            $page = $this->_get_page();
            $refers2 = $this->_member_mod->findAll(
                    array(
                        'conditions' => 'referid ' . db_create_in($ids),
                        'count' => true,
                        'limit' => $page['limit'],
                    )
            );
            $page['item_count'] = $this->_member_mod->getCount();
            $this->_format_page($page);
            $this->assign('page_info', $page);
            $this->assign('refers', $refers2);
        }
        $this->display('refer.refer.html');
    }

    function refer_user3() {
        /* 当前位置 */
        $this->_curlocal(LANG::get('member_center'), 'index.php?app=member', LANG::get('refer_user2'));
        /* 当前用户中心菜单 */
        $this->_curitem('refer');
        $this->_curmenu('refer_user3');

        //首先获得一级的推荐人列表
        $refers1 = $this->_member_mod->findAll(
                array(
                    'fields' => 'referid',
                    'conditions' => 'referid=' . $this->user_id,
                )
        );

        //如果有推荐人
        if (!empty($refers1)) {
            $ids = array();
            foreach ($refers1 as $key => $refer) {
                $ids[] = $refer['user_id'];
            }

            $refers2 = $this->_member_mod->findAll(
                    array(
                        'conditions' => 'referid ' . db_create_in($ids),
                    )
            );

            if (!empty($refers2)) {
                $ids = array();
                foreach ($refers2 as $key => $refer) {
                    $ids[] = $refer['user_id'];
                }

                $page = $this->_get_page();
                $refers3 = $this->_member_mod->findAll(
                        array(
                            'conditions' => 'referid ' . db_create_in($ids),
                            'count' => true,
                            'limit' => $page['limit'],
                        )
                );
                $page['item_count'] = $this->_member_mod->getCount();
                $this->_format_page($page);
                $this->assign('page_info', $page);
                $this->assign('refers', $refers3);
            }
        }
        $this->display('refer.refer.html');
    }

}

?>
