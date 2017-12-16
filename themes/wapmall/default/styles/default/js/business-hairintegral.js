
		$("#dete-Popup").hide();
		$("#determine").click(function(){
            var money = $('input[name="money"]').val();
            $('#money').html(money);
			$("#dete-Popup").show();
		});
		$("#no").click(function(){
			$("#dete-Popup").hide();
		});