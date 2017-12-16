
		$(".register-input").children(".register-clear").hide();
			$(".register-input input").focus(function(){
				$(this).next(".register-clear").show();
			});
			$(".register-input").blur(function(){
				$(this).children(".register-clear").hide();
			});
			$(".register-clear").click(function(){
				$(this).prev("input").attr("value","");
				$(this).hide();
			});
	/*	$(".clause-box").children("input").click(
		function(){
			$(this).prev("span").addClass("activ");
		}
	);*/

$(".clause-box").children("input").toggle(
			function(){
				$(this).prev("span").addClass("activ");
			},
			function(){
				$(this).prev("span").removeClass("activ");
			}
		);