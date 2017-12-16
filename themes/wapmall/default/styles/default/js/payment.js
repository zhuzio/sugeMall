/*input多选样式*/
$("#pay").hide();

$(".Payment li").click(
			function(){
				$(".Payment li").children(".Multiselect").children("span").removeClass("active");
				$(this).children(".Multiselect").children("span").addClass("active");
				$(".Payment li").find("input[type=radio]").attr('checked',false);
				$(this).find("input[type=radio]").attr('checked','checked');
			}
	);
/*显示隐藏*/
$(".next").click(
	function(){
		$("#pay").show();
	}
);
$("#ment").click(function(){
	$("#pay").hide();
}
);

// 确认升级
$("#buy-Popup").hide();
$("#determine").click(function(){
	$("#buy-Popup").show();
});
$("#no").click(function(){
	$("#buy-Popup").hide();
});



