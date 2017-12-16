$(function(){
	re = /^1\d{10}$/;
	$(".div1 button").on("touchend",function(){
		var val=$(".div1 input").val();
		
		if (re.test(val)) {
			$("form").submit();
		}else{
			alert("请输入正确的手机号");
		}
	})
	$(".div3 button").on("touchend",function(){
		$("form").submit();
	})
})
