$(function(){

	$(".p2 button").on("click",function(){
		$(".btns").removeClass("btns");
		$(this).addClass("btns");
		num=$(this).index();
		if (num) {
			$(".part1").css("display","none");
			$(".part2").css("display","block");
			$(".account").css("display","none");
		}else{
			$(".part1").css("display","block");
			$(".part2").css("display","none");
			$(".account").css("display","block");
		}
	})
	$(".menu li").on("click",function(){
		$(".mlis").removeClass("mlis");
		$(this).addClass("mlis");
		
	})
	$(".left").on("click",function(){
		var src=$(this).find("img").attr("src");
		if(src=="images/circle1.png"){
			$(this).find("img").attr("src","images/circle2.png");
		}else{
			$(this).find("img").attr("src","images/circle1.png");
		}
		
		
	})	
})