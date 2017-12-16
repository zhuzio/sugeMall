 /*! jQuery v1.8.3 jquery.com | jquery.org/license */         

	$("#on").addClass("activ");
	$("#off").removeClass("activ");
	$("#bean").show();
	$("#integral").hide();
	$("#off").click(function(){
		$("#on").removeClass("activ");
		$("#off").addClass("activ");
		$("#bean").hide();
		$("#integral").show();
	});
	$("#on").click(function(){
		$("#on").addClass("activ");
		$("#off").removeClass("activ")
		$("#bean").show();
		$("#integral").hide();
	});
	/*商城列表*/
	$(".Mall ul").hide();
	$(".Mall").toggle(
			function(){
				$(this).children("ul").show();
				$(this).children(".mall-list").children("em").addClass("active");
			},
			function(){
				$(this).children("ul").hide();
				$(this).children(".mall-list").children("em").removeClass("active");
			}
		);