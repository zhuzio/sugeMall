$(function(){
    var selfLat = '';
    var selfLng = '';
    var hasnext = false;
    var page=1;
    var map_city = localStorage.getItem('map_city') || '';
    var map_city_id = localStorage.getItem('map_city_id') || '';
    var local = localStorage.getItem('local') || '';
    //var cateid = localStorage.getItem('cate_id') || '';

    //var addressid = GetQueryString('addressid')?GetQueryString('addressid'):'';
    getLocation();
    //if(map_city == ''){
    //
    //}else{
    //    getlocationname(map_city_id);
    //    $(".shop_list").html('');
    //    getShopList();
    //}
    //if(selfLng && selfLat){
    //    newurl = '&lat='+selfLat+'&lng='+selfLng;
    //    $(".cat-list a").each(function(index,obj){
    //        $(obj).attr('href',$(obj).attr('href')+newurl);
    //    });
    //}
    getShopList();

    function getlocalid(name){
        $.ajax({
            url : "index.php?app=offline&act=getAreaIdByName",
            type : 'post',
            data :{name:name},
            dataType:'json',
            success:function(result){
                if(result.status == '0'){
                    map_city_id = result.data;
                    localStorage.setItem('map_city_id',map_city_id);
                    //addressxarp(data[0].name);
                }else{
                    //alert('网络繁忙');
                    console.log('定位失败');
                }
            }
        });
    }

   function getlocationname(id){
        $.ajax({
            url : "index.php?app=offline&act=getAreaById",
            type : 'post',
            data :{id:id},
            dataType:'json',
            success:function(result){
               if(result.status == '0'){
                var data = result.data;
                addressxarp(data.name);
               }else{
                 alert('网络繁忙');
               }

            }

        });
   }
   //地址解析
   function addressxarp(name){
        var myGeo = new BMap.Geocoder();
        // 将地址解析结果显示在地图上,并调整地图视野
        myGeo.getPoint(name, function(point){
            if (point) {
               selfLat = point.lat;
               selfLng = point.lng;
                //localStorage.setItem('selfLat',selfLat);
                //localStorage.setItem('selfLng',selfLng);
               againstaddressxarp();
            }else{
                alert("您选择地址没有解析到结果!");
            }
        });
   }
   //返地址解析

   function againstaddressxarp(){
       var myGeo = new BMap.Geocoder();
       // 根据坐标得到地址描述
       myGeo.getLocation(new BMap.Point(selfLng,selfLat), function(result){
           var addComp = result.addressComponents;
           local = addComp.province + ", " + addComp.city + ", " + addComp.district;
           localStorage.setItem('local',local);
           map_city = addComp.city;
           getlocalid(map_city);
           localStorage.setItem('map_city',map_city);
           //console.log(addComp);
           // addComp.province + ", " + addComp.city + ", " + addComp.district + ", " + addComp.street + ", " + addComp.streetNumber
           $('.weizhi').html('<span></span>'+addComp.district);
           $(".shop_list").html('');
           getShopList();
       });

   }
    function getCateList(){
        $.ajax({
            url : apiUrl + "getCateList",
            type : 'POST',
            dataType : 'JSON',
            success: function(res){
                if(res.status == 0){
                    $(".cateList").html('');
                    for(var i in res.data.list){
                        $(".cateList").append('<li class="mui-table-view-cell mui-media mui-col-xs-4 mui-col-sm-3 lisize"><a href="index.html?cateid='+res.data.list[i].id+'"><img class="tu-size" src="http://sgxt.sugemall.cn'+res.data.list[i].thumb+'"><div class="mui-media-body ">'+res.data.list[i].name+'</div></a></li>');
                    }
                }
            }
        });
    }
    //根据数值返回距离
 function returnunit(data){

    if(data >= 1000){
       var local = data/1000;
       local = local.toFixed(2);
       return local+'km';
    }else{
        return data+'m';
    }
 }
    function getShopList(){
        //var dtd = $.Deferred();
        return $.ajax({
            url : "index.php?app=offline&act=getShopList&p="+page+"&cateid="+cateid,
            type : 'POST',
            dataType : 'JSON',
            data : {lng : selfLng,lat : selfLat,cateid:cateid,map_city:map_city,local:local,map_city_id:map_city_id},
            success: function(res){
                if(typeof res == 'string'){
                    res = JSON.parse(res);
                }
                if(res.status == 0){

                    if(res.data.list.length < 0){
                        alert('没有数据了'); return ;
                    }
                    hasnext = res.data.hasNext == 0 ? false : true;

                    for(var i in res.data.list){
                        if(!res.data.list[i].store_banner){
                            res.data.list[i].store_banner = '/themes/wapmall/default/styles/default/images/tit.png';
                        }
                        $(".shop_list").append('' +
                            '<li class="cat-list" data-href="index.php?app=offline&act=detail&id='+res.data.list[i].store_id+'&p='+page+'&lng='+selfLng+'&lat='+selfLat+'">' +
                                '<a href="index.php?app=offline&act=detail&id='+res.data.list[i].store_id+'&p='+page+'&lng='+selfLng+'&lat='+selfLat+'">' +
                                    '<img src="'+res.data.list[i].store_banner+'">' +
                                    '<div class="inf">' +
                                        '<p class="name">'+res.data.list[i].store_name+'</p>' +
                                        '<p class="names">'+res.data.list[i].address+'</p>' +
                                        '<p class="number">' +
                                            '<span class="icon-yan"></span>' +
                                            '<span>浏览量</span>' +
                                            '<span>'+res.data.list[i].views+'</span>' +
                                        '</p>' +
                                    '</div>' +
                                    '<div class="distance">' +
                                        '<span class="icon-dis"></span>' +
                                        '<span>'+res.data.list[i].distance+'</span>' +
                                    '</div>' +
                                '</a>' +
                            '</li>');
                        $(".cat-list").bind('tap',function(){
                            location.href = $(this).attr('data-href');
                        });
                    }
                }
            }
        });


    }
    function getLocation(){
        var geolocation = new BMap.Geolocation();
        geolocation.getCurrentPosition(function(r){
            if(this.getStatus() == BMAP_STATUS_SUCCESS){
                selfLng = r.point.lng;
                selfLat = r.point.lat;
                newurl = '&lat='+selfLat+'&lng='+selfLng;
                $(".cat-list a").each(function(index,obj){
                    $(obj).attr('href',$(obj).attr('href')+newurl);
                });
                againstaddressxarp();
            }
            else {
                //alert('failed'+this.getStatus());
            }
        },{enableHighAccuracy: true})
    }

    //退出登录
    $(".logout").click(function(){
        $.ajax({
            url:apiUrl+"logout",
            type:'post',
            data:{token:token},
            dataType:'json',
            success:function(result){

                if(result.status=="0"){
                    // localStorage.clear();
                    window.location.href="login.html";
                }
                else{
                    alert(result.data.msg);
                }
            }
        });
    });
   //mui
    mui.init({
        swipeBack: false,
        pullRefresh : {
            container:"#pullrefresh",//下拉刷新容器标识，querySelector能定位的css选择器均可，比如：id、.class等
            down : {
                contentdown : "下拉可以刷新",//可选，在下拉可刷新状态时，下拉刷新控件上显示的标题内容
                contentover : "释放立即刷新",//可选，在释放可刷新状态时，下拉刷新控件上显示的标题内容
                contentrefresh : "正在刷新...",//可选，正在刷新状态时，下拉刷新控件上显示的标题内容
                callback :pullDownRefresh //必选，刷新函数，根据具体业务来编写，比如通过ajax从服务器获取新数据；
            },
            up : {
                contentrefresh : "正在加载...",//可选，正在加载状态时，上拉加载控件上显示的标题内容
                contentnomore:'没有更多数据了',//可选，请求完毕若没有更多数据时显示的提醒内容；
                callback : pullupNext
            }
        }
    });

    //mui('.mui-table-view').on('tap', '.mui-table-view-cell', function () {
    //    var sId = this.getElementsByTagName('p')[1].id.substring(1, 2);
    //    console.log(this);
    //    mui.openWindow({
    //        url: "O2O" + (sId - 1) + ".html"
    //    })
    //})


    /**
     * 上拉加载具体业务实现
     */
    function pullupNext(){
        if(hasnext){
            page++;
            getShopList();
            //mui('#pullrefresh').pullRefresh().endPullupToRefresh();
        }
        setTimeout(function(){mui('#pullrefresh').pullRefresh().endPullupToRefresh()},500);
    }

    function pullDownRefresh(callback) {
        page=1;
        //cateid='';
        $(".shop_list").html('');
        getShopList();
        //mui('#pullrefresh').pullRefresh().endPulldownToRefresh();
        setTimeout(function(){mui('#pullrefresh').pullRefresh().endPulldownToRefresh()},500);
    }



//百度地图功能 根据坐标返回当前城市

// 创建地理编码实例

});
