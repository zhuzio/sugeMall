
$(function(){
	$(".bank-input").children(".bank-clear").hide();
	$(".bank-input input").focus(function(){
		$(this).next(".bank-clear").show();
	});
	$(".bank-input").blur(function(){
		$(this).children(".bank-clear").hide();
	});
	$(".bank-clear").click(function(){
		$(this).prev("input").attr("value","");
		$(this).hide();
	});

	/*弹窗*/
	$("#bankim-Popup").hide();
	$("#bankimp").click(function(){	
		$("#bankim-Popup").show();
	});
	$(".pop-zdl").click(function(){
		$("#bankim-Popup").hide();
	});




});