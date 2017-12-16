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