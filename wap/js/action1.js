$(function(){
	
	re = /^[a-zA-Z0-9]{6,15}$/;
	$(".div1").on("focus","input",function(){
		$(this).on("blur",function(){
			if (re.test($(".div1 input").val())) {
			
		}else{
			alert("请输入6到15位数字或字母");
		}
		})
	})
	// $(".div1 button").on("touchend",function(){
	// 	var val=$(".div1 input").val();
		
	// 	if (re.test(val)) {
	// 		$("form").submit();
	// 	}else{
	// 		alert("错");
	// 	}
	// })
	$(".div3 button").on("touchend",function(){
		if ($(".div1 input").val()==$(".div2 input").val()) {
			$("form").submit();
		}else{
			alert("两次输入的密码不一样");
			
			$(".div1 input").focus();
			return false;
		}
		
	})
})
