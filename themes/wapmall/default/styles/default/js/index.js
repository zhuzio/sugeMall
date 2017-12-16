$(".nav li a").eq(0).addClass("active");
$(".nav li a").click(
	function(){
		$(".nav li a").removeClass("active")
		$(this).addClass("active")
	}
);
/*头部搜索框*/
$(".search").children(".clear-icon").hide();
			$("#search").focus(function(){
				$(this).next(".clear-icon").show();
				$(this).prev(".search-icon").hide();
			});
			$("#search").blur(function(){
				$(this).children(".clear-icon").hide();
				$(this).prev(".search-icon").show();
			});
			$(".clear-icon").click(function(){
				$(this).prev("#search").attr("value","");
				$(this).hide();
				$(this).prev(".search-icon").show();
			});
			
/**/
mui.init({
				pullRefresh: {
					container: '#pullrefresh',
					down: {
						callback: pulldownRefresh
					},
					up: {
						contentrefresh: '正在加载...',
						callback: pullupRefresh
					}
				}
			});
			/**
			 * 下拉刷新具体业务实现
			 */
			function pulldownRefresh() {
				setTimeout(function() {
					var table = document.body.querySelector('.mui-table-views');
					var cells = document.body.querySelectorAll('.cat-list');
					for (var i = cells.length, len = i + 3; i < len; i++) {
						var li = document.createElement('li');
						li.className = 'cat-list';
						li.innerHTML = '<a href="#"><img src="images/index/pic.png"><div class="inf"><p class="name">苏格远航茶馆中达e区</p><p class="names">中达北路369号</p><p class="number"><span class="icon-yan"></span><span>浏览量</span><span>428</span></p></div><div class="distance"><span class="icon-dis"></span><span>5.89km</span></div></a>';
						//下拉刷新，新纪录插到最前面；
						table.insertBefore(li, table.firstChild);
					}
					mui('#pullrefresh').pullRefresh().endPulldownToRefresh(); //refresh completed
				}, 1500);
			}
			var count = 0;
			/**
			 * 上拉加载具体业务实现
			 */
			function pullupRefresh() {
				setTimeout(function() {
					mui('#pullrefresh').pullRefresh().endPullupToRefresh((++count > 2)); //参数为true代表没有更多数据了。
					var table = document.body.querySelector('.mui-table-views');
					var cells = document.body.querySelectorAll('.cat-list');
					for (var i = cells.length, len = i + 5; i < len; i++) {
						var li = document.createElement('li');
						li.className = 'cat-list';
						li.innerHTML = '<a href="#"><img src="images/index/pic.png"><div class="inf"><p class="name">苏格远航茶馆中达e区</p><p class="names">中达北路369号</p><p class="number"><span class="icon-yan"></span><span>浏览量</span><span>428</span></p></div><div class="distance"><span class="icon-dis"></span><span>5.89km</span></div></a>';
						
						table.appendChild(li);
					}
				}, 1500);
			}
			if (mui.os.plus) {
				mui.plusReady(function() {
					setTimeout(function() {
						mui('#pullrefresh').pullRefresh().pullupLoading();
					}, 1000);

				});
			} else {
				mui.ready(function() {
					mui('#pullrefresh').pullRefresh().pullupLoading();
				});
			}
		mui.ready(function() {
			var slider = document.getElementById('Gallery');
			var group = slider.querySelector('.mui-slider-group');
			var items = mui('.mui-slider-item', group);
			//克隆第一个节点
			var first = items[0].cloneNode(true);
			first.classList.add('mui-slider-item-duplicate');
			//克隆最后一个节点
			var last = items[items.length - 1].cloneNode(true);
			last.classList.add('mui-slider-item-duplicate');
			//处理是否循环逻辑，若支持循环，需支持两点：
			//1、在.mui-slider-group节点上增加.mui-slider-loop类
			//2、重复增加2个循环节点，图片顺序变为：N、1、2...N、1
			var sliderApi = mui(slider).slider();

			function toggleLoop(loop) {
					if (loop) {
						group.classList.add('mui-slider-loop');
						group.insertBefore(last, group.firstChild);
						group.appendChild(first);
						sliderApi.refresh();
						sliderApi.gotoItem(0);
					} else {
						group.classList.remove('mui-slider-loop');
						group.removeChild(first);
						group.removeChild(last);
						sliderApi.refresh();
						sliderApi.gotoItem(0);
					}
				}
				//按下“循环”按钮的处理逻辑；
			document.getElementById('Gallery_Toggle').addEventListener('toggle', function(e){
				var loop = e.detail.isActive;
				toggleLoop(loop);
			});
		});