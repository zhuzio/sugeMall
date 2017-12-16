// $("#introducer").hide();
// $(".submint").click(function(){
// 	$("#introducer").show();
// });
// $("#intr").click(function(){
// 	$("#introducer").hide();
// });

// 通过手机号查询推荐人 id = sj_zhitui

$(function(){
	$("#tuijian_cell").hide();
	$('#tuijian').click(function(){
		var tj_phone =$('input[name="tuijian_phone"]').val();
		$.ajax({
			type: 'GET',
			dataType: 'JSON',
			async: false,
			data:{tj_phone:tj_phone},
			url: 'index.php?app=new_member&act=select_intro',
			success:function(result){
				console.log(result);
				var list =result;
				if(list){
					$('#queding').text(list.user_name);
					$('#rea_name').text('('+list.real_name+')');
					$("#tuijian_cell").show();
					//当点击确定时将上一步数据赋值
					$("#is_ok").click(function(){
					var user_name =list.user_name;
					$.ajax({
						type: 'GET',
						dataType: 'JSON',
						data: {uname:user_name},
						url: 'index.php?app=new_member&act=insert_intro',
						async: false,
						success:function(abc){
							if(abc == '1'){
								document.location ="index.php?app=new_member";
							}
						}
					});				
				});



				}else{
					alert('请正确输入推荐人手机号');
				}
			}
		});
		
		$("#intro").click(function(){
			$("#tuijian_cell").hide();

		});

		//检测如果不是商家隐藏div







	});
	

	
















});