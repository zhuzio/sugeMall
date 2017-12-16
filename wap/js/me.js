$('.main_four button').on('click',function(){
		//遮罩层
		  jQuery(document).ready(function($){
            event.preventDefault();
            $('.tan').addClass('is-visible');
        //关闭窗口
        	$('.tan').on('click', function(event){
	            if( $(event.target).is('.tan_close') || $(event.target).is('.tan') ) {
	                event.preventDefault();
	                $(this).removeClass('is-visible');
	            }
	        });
   		});
	})
	
	