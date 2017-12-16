$(function(){
	//选择省份
     getOption(1,'province');
	//根据省份列出城市
	$("select").change(function(){
		  var changeid	=$(this).attr('data');
		  getOption($(this).val(),changeid);
		
		});	

	function getOption(pid,changeid){
		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			async: false,
			url: 'index.php?app=req&act=province',
			data:{pid:pid},
			success:function(result){
				// console.log(result);
				if(result){
					var list = result;
					$("."+changeid+"").html('');
					$("."+changeid+"").append('<option value="">请选择</option>');
					var html;
					for(var i=0;i<list.length;i++){
						html = '<option value="'+list[i].id+'">'+list[i].name+'</option>'; 
	                    $("."+changeid+"").append(html);   
					}

				}
			}
		});
	}










});