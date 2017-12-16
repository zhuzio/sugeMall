<?php
header("Access-Control-Allow-Origin: *");
/* 多级选择：地区选择，分类选择 */

class MlselectionApp extends MallbaseApp {

    function index() {
        in_array($_GET['type'], array('region', 'gcategory', 'sdcategory','jucategory')) or $this->json_error('invalid type');        
        $pid = empty($_GET['pid']) ? 1 : $_GET['pid'];


        switch ($_GET['type']) {
            case 'region':
                $pid = (empty($_GET['pid'])||$_GET['pid']==2)  ? 1 : $_GET['pid'];
                $mod_region = & m('sgxt_area');
                $this->model = &m();
                $list = $this->model->table('sgxt_area')->where('parent_id='.$pid)->select();
                $regions = array();
                foreach($list as $key=>$val){
                    $item['region_name'] = htmlspecialchars($val['name']);
                    $item['parent_id'] = $val['parent_id'];
                    $item['region_id'] = $val['id'];
                    $item['sort_order'] = 255;
                    $regions[] = $item;
                }
                $this->json_result(array_values($regions));
                break;
            case 'gcategory':
                $mod_gcategory = & m('gcategory');
                $cates = $mod_gcategory->get_list($pid, true);
                foreach ($cates as $key => $cate) {
                    $cates[$key]['cate_name'] = htmlspecialchars($cate['cate_name']);
                }
                $this->json_result(array_values($cates));
                break;
            case 'sdcategory':
                $mod_sdcategory = & m('sdcategory');
                $sdcates = $mod_sdcategory->get_list($pid, true);
                foreach ($sdcates as $key => $sdcate) {
                    $sdcates[$key]['cate_name'] = htmlspecialchars($sdcate['cate_name']);
                }
                $this->json_result(array_values($sdcates));
                break;
            case 'jucategory':
                $mod_jucategory = & m('jucate');
                $jucates = $mod_jucategory->get_list($pid, true);
                foreach ($jucates as $key => $jucate) {
                    $jucates[$key]['cate_name'] = htmlspecialchars($jucate['cate_name']);
                }
                $this->json_result(array_values($jucates));
                break;
        }
    }

}

?>