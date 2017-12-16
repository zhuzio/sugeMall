$(function(){
	var abs=true;
	var abs2=false;
	// alert($(".sec").text());
	// 条款判断
	$(".rule").on("touchend",function(){
		if (abs) {
			$(this).find("img").attr("src","images/rule2.png");
			abs=false;
		}else{
			$(this).find("img").attr("src","images/rule1.png");
			abs=true;
		}
	})
	// 线上线下判断
	$(".tp2").on("touchend","img",function(){
		// if (abs) {
		// 	$(this).attr("src","images/rule2.png");
		// 	abs=false;
		// }else{
		// 	$(this).attr("src","images/rule1.png");
		// 	abs=true;
		// }
		$(".tp2 img").attr("src","images/rule1.png");
		$(this).attr("src","images/tp2.png");
		abs2=ture;
	})
	$("form").submit(function(){
		if ($(".name").val()=="") {
			alert("请输入店主姓名");
				$(".name").focus();
				return false;
		}
		var id=/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/;
		if ($(".id").val()=="") {
			alert("请输入店主省份证号");
				$(".id").focus();
				return false;
		}else if(!id.test($(".id").val())){
			alert("请输入正确的省份证号");
			$(".id").focus();
				return false;
		}
		if ($(".shop").val()=="") {
			alert("请输入店铺名称");
				$(".shop").focus();
				return false;
		}
		if (!abs2) {
			alert("请选择线上或者线下店铺");
			return false;
		}
		if ($(".sec").text()=="选择所属分类") {
			alert("请选择店铺的所属分类");
			return false;
		}
		if ($(".addr").val()=="") {
			alert("请输您的详细地址");
				$(".addr").focus();
				return false;
		}
		var yb= /^[1-9][0-9]{5}$/
		if ($(".yb").val()=="") {
			alert("请输入邮政编码");
				$(".yb").focus();
				return false;
		}else if(!yb.test($(".yb").val())){
			alert("请输入正确的邮政编码");
			$(".yb").focus();
				return false;
		}
		var phone=/^(0[0-9]{2,3}\-)?([2-9][0-9]{6,7})+(\-[0-9]{1,4})?$|(^(13[0-9]|15[0|3|6|7|8|9]|18[8|9])\d{8}$)/;
		if ($(".phone").val()=="") {
			alert("请输入联系电话");
				$(".phone").focus();
				return false;
		}else if(!phone.test($(".phone").val())){
			alert("请输入正确的联系电话");
			$(".phone").focus();
				return false;
		}
		if (abs) {
			alert("请确认是否已阅读所有条款");
			return false;
		}

	})
})