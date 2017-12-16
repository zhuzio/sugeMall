<?php
header("Content-type: text/html; charset=utf-8");
class OfflineApp extends MallbaseApp {

    function index() {
        $model = &m();
//        $cateList = array();
//        $childList = array();
//        foreach($cList as $key=>$val){
//            if($val['parent_id'] == 0){
//                $val['sub'] = array();
//                $cateList[$val['id']] = $val;
//            }else{
//                if(array_key_exists($val['parent_id'],$cateList)){
//                   $cateList[$val['parent_id']]['sub'] = $val;
//                }else{
//                    $childList[] = $val;
//                }
//            }
//        }
//        foreach($childList as $key=>$val){
//            if(array_key_exists($val['parent_id'],$cateList)){
//                $cateList[$val['parent_id']]['sub'] = $val;
//            }
//        }

        $lng = trim($_GET['lng']);
        $lat = trim($_GET['lat']);
        $cateid = intval($_GET['cateid']);
        
        // $condition = array();
        // $condition['o2o'] = 'offline';
        // $sids = array();
        // if($cateid){
        //     //$condition['']
        //     $csid = $model->table('category_store')->where('cate_id='.$cateid)->select();
        //     foreach($csid as $key=>$val){
        //         $sids[] = $val['store_id'];
        //     }
        // }
        // $count = $model->table('store')->where($condition)->count();
        // $shopList = $model->table('store')->where($condition)->page($count)->select();
        // if($sids){
        //     $count = $model->table('store')->where($condition)->where('store_id in ('.implode(',',$sids).')')->count();
        //     $shopList = $model->table('store')->where($condition)->where('store_id in ('.implode(',',$sids).')')->page($count)->select();
        // }
        $cateFirst = $model->table('scategory')->where('parent_id=0')->limit(8,0)->select();
        $cateSecond = $model->table('scategory')->where('parent_id=0')->limit(8,8)->select();
        $cateThird = $model ->table('scategory')->where('parent_id=0')->limit(8,16)->select();
        $this->assign('shopList',array());
        $this->assign('cateFirst',$cateFirst);
        $this->assign('cateSecond',$cateSecond);
        $this->assign('cateThird',$cateThird);
        $this->assign('cateid',$cateid);
        $this->assign('page_description', Conf::get('site_description'));
        $this->assign('page_keywords', Conf::get('site_keywords'));
        $this->display('newapp/index.offline.html');
    }

    function getAreaChild(){
        $id = intval($_POST['id']);
        $model = &m();
        $area = $model->table('sgxt_area')->where('parent_id='.$id)->select();
        if($area){
            echo json_encode(array('status'=>0,'data'=>$area));exit;
        }else{
            echo json_encode(array('status'=>1,'msg'=>'区域不存在'));exit;
        }
    }

    function getAreaById(){
        $id = intval($_POST['id']);
        $model = &m();
        $area = $model->table('sgxt_area')->where('id='.$id)->find1();
        if($area){
            echo json_encode(array('status'=>0,'data'=>$area));exit;
        }else{
            echo json_encode(array('status'=>1,'msg'=>'区域不存在'));exit;
        }
    }
    function getAreaIdByName(){
        $name = trim($_POST['name']);
        $model = &m();
        $area = $model->table('sgxt_area')->where("name='".$name."'")->find1();
        if($area){
            echo json_encode(array('status'=>0,'data'=>$area['id']));exit;
        }else{
            echo json_encode(array('status'=>1,'msg'=>'区域不存在'));exit;
        }
    }

    function getShopList(){
        $model = &m();
        $lng = trim($_POST['lng']);
        $lat = trim($_POST['lat']);
        $local = trim($_POST['local']);
        $cateid = intval($_POST['cateid']);
        $map_city_id = trim($_POST['map_city_id']);
        $condition = array();
        $condition['o2o'] = 'offline';
        $condition['state']='1';
        /*
        if($local){
            list($province,$city,$area) = explode(',',$local);
            $area = $model->table('sgxt_area')->where("name='".trim($area)."'")->find1();
            $condition['area'] = $area['id'];
            $count = $model->table('store')->where($condition)->count();
            if($count == 0){
                unset($condition['area']);
                $area = $model->table('sgxt_area')->where("id=".$area['parent_id'])->find1();
                $condition['city'] = $area['id'];
                $count = $model->table('store')->where($condition)->count();                
                if($count == 0){
                    unset($condition['city']);
                    $area = $model->table('sgxt_area')->where("id=".$area['parent_id'])->find1();
                    $condition['province'] = $area['id'];
                }
            }
        }
        */
        $sids = array();
        if($cateid){
            $csid = $model->table('category_store')->where('cate_id='.$cateid)->select();
            foreach($csid as $key=>$val){
                $sids[] = $val['store_id'];
            }
        }
        $count = $model->table('store')->where($condition)->count();
        $shopList = $model->table('store')->where($condition)->order('add_time desc')->select();
        if($sids){
            $count = $model->table('store')->where($condition)->where('store_id in ('.implode(',',$sids).')')->count();
            $shopList = $model->table('store')->where($condition)->where('store_id in ('.implode(',',$sids).')')->order('add_time desc')->select();
        }
        foreach($shopList as $key=>$val){
            //$user = $model->table('member')->where('user_id='.$val['store_id'])->find1();
//            $val['lng'] = $user['lng'];
//            $val['lat'] = $user['lat'];
            if($lng && $lat){
                $distance = $this->count_distance2($lng,$lat,$val['lng'],$val['lat']);
                $val['distance'] = $distance;
            }
            if(!$val['views']){
                $val['views'] =0;
            }
            if($val['store_banner'] == ''){
                if($val['image_1']){
                    $val['store_banner'] = $val['image_1'];
                }else if($val['image_2']){
                    $val['store_banner'] = $val['image_2'];
                }if($val['image_3']){
                    $val['store_banner'] = $val['image_3'];
                }

            }
            $shopList[$key] = $val;
        }
        $page = $_GET['p'] ? $_GET['p'] : 1;
        $mypage = $this->mypage($page,10,$count);
        $shopList = $this->mymArrsort($shopList,'distance');
        $result = array();
        foreach($shopList as $key=>$val){
            $val['distance'] = $this->formatDistance($val['distance']);
            if($key >= $mypage['start'] && $key < ($mypage['start']+$mypage['end'])){
                $result[] = $val;
            }
        }

        echo json_encode(array('status'=>0,'data'=>array('list'=>$result,'hasNext'=>$mypage['hasNext'])));
        return;

    }

    function mypage($page,$pageNum,$count){
        $totalPage = ceil($count/$pageNum);
        $start = $end = 0;
        $hasNext = false;
        $pre = 1;
        $next = 1;
        if($page == 1){
            $start = 0;
            $end = $pageNum;
            if($count <= $pageNum){
                $end = $count;

            }else{
                $next = $page + 1;
                $hasNext = true;
            }
        }else if($page < $totalPage){
            $start = $pageNum * ($page - 1);
            $end = $pageNum;
            $pre = $page - 1;
            $next = $page + 1;
            $hasNext = true;
        }else if($page == $totalPage){
            $start = $pageNum * ($page - 1);
            $end = $count % $pageNum == 0 ? $pageNum : $count % $pageNum;
            //$hasNext = false;
            $next = $page;
            $pre = $page - 1;
        }elseif($page > $totalPage){
            output(array('status' => 1,'msg' => '没有更多数据了'));
        }
        return array('start' => $start,'end' => $end,'hasNext' => $hasNext,'totalPage' => $totalPage,'pre' => $pre, 'next' => $next);
    }


    /**
     * 根据两点坐标获取两点之间的距离
     * @param $slng 起点经度
     * @param $slat 起点纬度
     * @param $elng 终点经度
     * @param $elat 终点纬度
     * @return int 返回距离，单位米
     */
    function count_distance2($slng,$slat,$elng,$elat){
        $pk = 180 / 3.14169;
        $a1 = (double)$slat / $pk;
        $a2 = (double)$slng / $pk;
        $b1 = (double)$elat / $pk;
        $b2 = (double)$elng / $pk;
        $t1 = cos($a1) * cos($a2) * cos($b1) * cos($b2);
        $t2 = cos($a1) * sin($a2) * cos($b1) * sin($b2);
        $t3 = sin($a1) * sin($b1);
        $tt = acos($t1 + $t2 + $t3);
        return 6366000 * $tt;
    }

    /**
     * 根据指定字段排序二维数组，保留原有键值(降序)
     * @param $arr @输入二维数组
     * @param $var @要排序的字段名
     * return array
     * @return array
     */
    function mymArrsort($arr, $var,$mode = true){
        $tmp=array();
        $rst=array();
        foreach($arr as $key=>$trim){
            $tmp[$key] = $trim[$var];
        }
        if($mode){
            asort($tmp);
        }else{
            arsort($tmp);
        }
        $i=0;
        foreach($tmp as $key1=>$trim1){
            $rst[$i] = $arr[$key1];
            $i++;
        }
        return $rst;
    }

    //格式化距离，单位米
    function formatDistance($distance){
        $km = sprintf("%.2f",$distance / 1000);
        if($km > 1){
            return $km."km";
        }else{
            return sprintf("%.2f",$distance)."m";
        }
    }

    function detail(){
        $id = intval($_GET['id']);
        $lng = trim($_GET['lng']);
        $lat = trim($_GET['lat']);
        $model = &m();
        $shop = $model->table('store')->where('store_id='.$id)->find1();
        //浏览量
        if($shop){
            $model->query("update `ecm_store` set `views` =`views`+1 where store_id=".$id." limit 1");
        }
        if($shop){
            $pics = array();
            if($shop['pic_slides']){
                $list = json_decode($shop['pic_slides']);
                foreach($list as $k=>$v){
                    $pics[] = $v->url;
                }
            }
            if($shop['city']){
                $area = $model->table('sgxt_area')->where('id='.$shop['city'])->find1();
                $shop['cityname'] = $area['name'];
            }
            $shop['pics'] = $pics;
            $distance = $this->count_distance2($lng,$lat,$shop['lng'],$shop['lat']);
            $shop['distance'] = $this->formatDistance($distance);
            $this->assign('store',$shop);
            $this->assign('lng',$lng);
            $this->assign('lat',$lat);
            $this->assign('page_description', Conf::get('site_description'));
            $this->assign('page_keywords', Conf::get('site_keywords'));
            $this->display('newapp/store_offline_detail.html');
        }else{
            $this->show_warning('店铺不存在');
        }
    }

    function driverout(){
        $lng = $_GET['lng'];
        $lat = $_GET['lat'];
        $to_lng = $_GET['to_lng'];
        $to_lat = $_GET['to_lat'];
        $ggPosition = $this->bd_decrypt($lng,$lat);
        $this->assign('lng',$ggPosition['lat']);
        $this->assign('lat',$ggPosition['lng']);
        $ggPosition = $this->bd_decrypt($to_lng,$to_lat);
        $this->assign('to_lng',$ggPosition['lat']);
        $this->assign('to_lat',$ggPosition['lng']);
        $this->display('newapp/drivingrout.html');
    }

    function bd_encrypt($gg_lat, $gg_lon){
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = doubleval($gg_lon);
        $y = doubleval($gg_lat);
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array('lng'=>$bd_lon,'lat'=>$bd_lat);
    }

    function bd_decrypt($bd_lat, $bd_lon){
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gg_lon = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return array('lng'=>$gg_lon,'lat'=>$gg_lat);
    }


}

?>