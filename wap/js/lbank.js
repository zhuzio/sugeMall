$(function(){
	$(".lp0 span").on("click",function(){
		$(".lp0 span img").attr("src","images/bkpass1.png");
		$(this).find("img").attr("src","images/bkpass2.png");
	})	
	$(".lp0 button").on("click",function(){
		$(".delete").css("display","block");
		// alert($(this).parent().parent().index());
		var count=$(this).parent().parent().index();
	})
	$(".delete button").on("click",function(){
		$(".delete").css("display","none");
	})	
})