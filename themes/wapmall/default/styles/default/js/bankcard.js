$(function(){
	//选取银行
	$.ajax({
		type: 'POST',
		dataType: 'JSON',
		async: false,
		url: 'index.php?app=bank&act=bank_list',
		success:function(result){
			// console.log(result);
			if(result){
				var list = result;
				$('#bankname').html('');
				$('#bankname').append('<option value="">请选择开户行</option>');
				var html;
				for(var i=0;i<list.length;i++){
					html = '<option value="'+list[i].bank_name+'">'+list[i].bank_name+'</option>' ; 
                    $("#bankname").append(html);   
				}
			}
		}
	});

	//根据银行列取省份
	$("#bankname").change(function(){
		var bank =$(this).val();
		// alert(bank);
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			async: false,
			url: 'index.php?app=bank&act=list_bank_province',
			data:{bank:bank},
			success:function(result){
				// console.log(result);
				if(result){
					var list =result;
					$('#province').html('');
					$('#province').append('<option value="">请选择开户省份</option>');
					var html;
					for(var i=0;i<list.province.length;i++){
						html ='<option value="'+list.province[i]+'" id="area_name">'+list.province[i]+'</option>';
						$('#province').append(html);
					}

				}
			}
		});

	});

	//根据省份获取城市
	$('#province').change(function(){
		var prv =$(this).val();
		var bank = $('#bankname').val();
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			async: false,
			url: 'index.php?app=bank&act=list_bank_area',
			data :{prv:prv,bank:bank},
			success:function(result){
				// console.log(result);
				if(result){
					var list =result;
					$('#area').html('');
					$('#area').append('<option value="">请选择开户城市</option>');
					var html;
					for(var i=0;i<list.area.length;i++){
						html ='<option value="'+list.area[i]+'">'+list.area[i]+'</option>';
						$('#area').append(html);
					}
				}
			}
		});

	});

	//根据城市获取支行
	$('#area').change(function(){
		var area =$(this).val();
		// var area_name =$(this).text()
		// alert(area);
		var bank =$('#bankname').val();
		var prv =$('#province').val();

		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			async: false,
			url: 'index.php?app=bank&act=list_bank_name',
			data:{bank:bank,prv:prv,area:area},
			success:function(result){
				// console.log(result);
				if(result){
					var list =result;
					var html;
					$('#code').html('');
					$('#code').append('<option value="">请选择开户支行</option>');
					for(var i=0;i<list.length;i++){
						html ='<option value="'+list[i].code+'" id="branch">'+list[i].name+'</option>';
						$('#code').append(html);

					}
				}
			}

		});
	});

	
	$('#code').change(function(){
		var code_name =$('#branch').text();
		// alert(code_name);
		$('input[name="bank_codename"]').val(code_name);

	});


	//验证持卡人 卡号 省份
	$('#next_step').click(function(){
		var username =$('input[name="account_name"]').val();
		var cardnum =$('input[name="bank_num"]').val();
		var bank =$('#bankname').val();
		var pro =$('#province').val();
		if(username.length <=0){
			alert('持卡人姓名不能为空');
			return false;
		}

		if(cardnum.length <=0){
			alert('银行卡号不能为空122');
			return false;
		}

		if(bank.length ==''){
			alert("开户行不能为空");
			return false;
		}

		if(pro.length ==''){
			alert("开户省份不能为空");
			return false;
		}

		$('#show_step').hide();
		$('#save').show();


	});





	// //输出默认银行卡
	// $('.bank-edit').click(function(){
	// 	$.ajax({
 //            type: 'POST',
 //            dataType:'JSON',
 //            async: false;
 //            url: 'index.php?app=bank&act=default_bank',
 //            success:function(result){
 //                $('input[name="b_number"]').val(result.data.bankcard);
 //                $('input[name="bankname"]').val(result.data.bankname);
 //            }
 //     });

	// });
	 



});