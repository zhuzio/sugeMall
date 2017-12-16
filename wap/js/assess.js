$(function(){
	$(".lxf_ul1 li").on("click",function(){
		$(".llis").removeClass("llis");
		$(this).addClass("llis");
		// alert($(this).index());
		var count=$(this).index()+1;
		$(".lxfuls").removeClass("lxfuls");
		$(".lxf_pj ul:eq("+count+")").addClass("lxfuls");
	})
})