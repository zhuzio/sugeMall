/*
var ps="<p class='ulp'><img src=''><span></span></p>";
var ins="<input type='file' name='file'>";
var a=[];
function add(){
	$(".upload").append(ps);
	$(".fil").append(ins);
}
add(); 
// var tf=false;

$(".upload").on("click","span",function(){
	var n=$(this).parent().index();
	$(this).parent().remove();
	$(".fil input").eq(n).remove();
	if ($(".ulps").length==$(".ulp").length) {
				add();
			}
	}) ;
$(".upload").on("click","img",function(){
	var i=$(this).parent().index();
	// if (a[i]) {
		
	// }else{
	// 	a[i]=1;
	// }
	$(".fil input").eq(i).click();
	var othis=$(this);
	$(".fil input").eq(i).change(function(){
		// if (a[i]==1) {
		// 	a[i]=2;
		// 	if ($(".ulps").length<3) {
		// 		add();
		// 	}	
		// }
			var objUrl = getObjectURL(this.files[0]);
			if (objUrl) {
				othis.attr("src", objUrl) ;
				if (othis.parent().hasClass("ulps")) {

				}else{
					othis.parent().addClass("ulps");
					if ($(".ulps").length<4) {
						add(); 
					}
				}
			}
		
	}) ;
	//建立一個可存取到該file的url
	function getObjectURL(file) {
		var url = null ; 
		if (window.createObjectURL!=undefined) { // basic
			url = window.createObjectURL(file) ;
		} else if (window.URL!=undefined) { // mozilla(firefox)
			url = window.URL.createObjectURL(file) ;
		} else if (window.webkitURL!=undefined) { // webkit or chrome
			url = window.webkitURL.createObjectURL(file) ;
		}
		return url;
	}

	
});	
*/
// $(".ulp2111 img").click(function(){
// 	$(".fil input").eq(1).click();
// 	$(".upload div input").eq(1).change(function(){
// 		var objUrl = getObjectURL(this.files[0]) ;
// 		console.log("objUrl = "+objUrl) ;
// 		if (objUrl) {
// 			$(".ulp2").find("img").attr("src", objUrl) ;
			
// 			$(".ulp2").find("img").css("display","block")
// 		}
// 	}) ;
// 	//建立一個可存取到該file的url
// 	function getObjectURL(file) {
// 		var url = null ; 
// 		if (window.createObjectURL!=undefined) { // basic
// 			url = window.createObjectURL(file) ;
// 		} else if (window.URL!=undefined) { // mozilla(firefox)
// 			url = window.URL.createObjectURL(file) ;
// 		} else if (window.webkitURL!=undefined) { // webkit or chrome
// 			url = window.webkitURL.createObjectURL(file) ;
// 		}
// 		return url;
// 	}
// });
// $(".ulp1").on("click",function(){
	// $(".upload div input").eq(0).click();
// 	$(".upload div input").eq(0).change(function(){

// 		var objUrl = getObjectURL(this.files[0]);
// 		console.log("objUrl = "+objUrl) ;
// 		if (objUrl) {

// 			$("ulp1").find("img").attr("src", objUrl) ;alert($("ulp1").find("img").attr("src"));
// 			$("ulp1").find("img").css("display","block")
		
			
// 		}
		
// 	}) ;
	//建立一個可存取到該file的url
	// var a=[];
// 	function getObjectURL(file) {
// 		var url = null ; 
// 		if (window.createObjectURL!=undefined) { // basic
// 			url = window.createObjectURL(file) ;
// 		} else if (window.URL!=undefined) { // mozilla(firefox)
// 			url = window.URL.createObjectURL(file) ;
// 		} else if (window.webkitURL!=undefined) { // webkit or chrome
// 			url = window.webkitURL.createObjectURL(file) ;
// 		}
// 		return url;
// 	}
// });

// $(".submit3").click(function(){
// 	$("#file3[type=file]").click();
// 	$("#file3").change(function(){
// 		var objUrl = getObjectURL(this.files[0]) ;
// 		console.log("objUrl = "+objUrl) ;
// 		if (objUrl) {
// 			$("#img3").attr("src", objUrl) ;
// 		}
// 	}) ;
// 	//建立一個可存取到該file的url
// 	function getObjectURL(file) {
// 		var url = null ; 
// 		if (window.createObjectURL!=undefined) { // basic
// 			url = window.createObjectURL(file) ;
// 		} else if (window.URL!=undefined) { // mozilla(firefox)
// 			url = window.URL.createObjectURL(file) ;
// 		} else if (window.webkitURL!=undefined) { // webkit or chrome
// 			url = window.webkitURL.createObjectURL(file) ;
// 		}
// 		return url;
// 	}
// });

// $(".submit4").click(function(){
// 	$("#file4[type=file]").click();
// 	$("#file4").change(function(){
// 		var objUrl = getObjectURL(this.files[0]) ;
// 		console.log("objUrl = "+objUrl) ;
// 		if (objUrl) {
// 			$("#img4").attr("src", objUrl) ;
// 		}
// 	}) ;
// 	//建立一個可存取到該file的url
// 	function getObjectURL(file) {
// 		var url = null ; 
// 		if (window.createObjectURL!=undefined) { // basic
// 			url = window.createObjectURL(file) ;
// 		} else if (window.URL!=undefined) { // mozilla(firefox)
// 			url = window.URL.createObjectURL(file) ;
// 		} else if (window.webkitURL!=undefined) { // webkit or chrome
// 			url = window.webkitURL.createObjectURL(file) ;
// 		}
// 		return url;
// 	}
// });

// $(".submit5").click(function(){
// 	$("#file5[type=file]").click();
// 	$("#file5").change(function(){
// 		var objUrl = getObjectURL(this.files[0]) ;
// 		console.log("objUrl = "+objUrl) ;
// 		if (objUrl) {
// 			$("#img5").attr("src", objUrl) ;
// 		}
// 	}) ;
// 	//建立一個可存取到該file的url
// 	function getObjectURL(file) {
// 		var url = null ; 
// 		if (window.createObjectURL!=undefined) { // basic
// 			url = window.createObjectURL(file) ;
// 		} else if (window.URL!=undefined) { // mozilla(firefox)
// 			url = window.URL.createObjectURL(file) ;
// 		} else if (window.webkitURL!=undefined) { // webkit or chrome
// 			url = window.webkitURL.createObjectURL(file) ;
// 		}
// 		return url;
// 	}
// });
// 
// 
var imgList = [];
var index = 0;

$(function(){
	function addImg(index,img){
		$(".img_list").append('<p class="ulp ulps" id="img'+index+'"><img src="/'+img+'"><span data-index="'+index+'"></span></p>');
		$(".img_list").find('span').on('click',function(){
			var id = $(this).attr('data-index');
			imgList[id] = '';
			$(this).parent().remove();
		})
	}

	function getData(typename,id){
		var uploadData = {
			auto: true,
			fileTypeExts: '*.jpg;*.jpeg;*.png;*.JPG;*.JPEG;*.PNG;',
			multi: false,
			fileObjName: typename,
			formData: {'token':tokens},
			fileSizeLimit: 99999999999,
			showUploadedPercent: false,
			showUploadedSize: false,
			removeTimeout: 9999999,
			uploader: Url + "/api/index.php?n=refund&f=img",
			//uploader:"http://aapi.vipiao.com/client" + "/user/header",
			//uploader: api"' + Url + 'uploadFace",
			onUploadStart: function(file) {
				console.log(file.name + '开始上传');
			},
			onInit: function(obj) {
				console.log('初始化');
			},
			onUploadComplete: function(file, res) {
				res = JSON.parse(res);
				//$("#"+id).css("backgroundImage", "url('/"+res.data+"')");
				imgList[index] = res.data;				
				addImg(index,res.data);
				index++;
			},
			onCancel: function(file) {
				console.log(file.name + '删除成功');
			},
			onClearQueue: function(queueItemCount) {
				console.log('有' + queueItemCount + '个文件被删除了');
			},
			onDestroy: function() {
				console.log('destroyed!');
			},
			onSelect: function(file) {
				console.log(file.name + '加入上传队列');
			},
			onQueueComplete: function(queueData) {
				console.log('队列中的文件全部上传完成', queueData);
			}
		};
		return uploadData;
		}
		var logoUp = $("#avatorUpload").Huploadify(getData('file','avatorUpload'));

})