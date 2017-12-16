var html="";
	$.ajax({
		type:"get",
		url:"js/city.json",
		async:true,
		success:function(data){
//			console.log(data);
			for(var i=0;i<data.citylist.length;i++){
				html+="<option>"+data.citylist[i].p+"</option>";
			}
			$(".city").html(html);
			$(".city").change(function(){
				var txt=$(".city").find("option:selected").text();
				//console.log(txt)
				for(var i=0;i<data.citylist.length;i++){
					if(txt==data.citylist[i].p){
						var chengp="";
						$.each(data.citylist[i].c,function(i,v){
							chengp+="<option>"+v.n+"</option>";
							$('.cheng').change(function(){
								var txt1=$('.cheng').find('option:selected').text();
								//console.log(txt1,data);							
								if(txt1==v.n){
									var xianp="";
//									console.log(c.a);
									if(v.a==null){
										xianp+='<option>直辖市没有县！！</option>'
									}else{
										$.each(v.a,function(index,a){
//											console.log(a.s);
											xianp+='<option>'+a.s+'</option>';
										})
									}								
//									console.log(xianp);
								}
								$('.xian').html(xianp);
							})
						})
					}
				}
				$(".cheng").html(chengp)
			})
		}
	})