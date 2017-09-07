/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    $(function(){
        $('.filter_by_searchword').click(function(e){
            event.stopPropagation();
        }); 
        
        $('.product_delete').click(function(){
			var buttonState = $(this).attr('button-state'); 
			if(buttonState == 'off'){
				$(this).attr('button-state', 'on').removeClass('btn-primary').addClass('btn-danger');
				$(this).html('<span style="font-size: 12px;" class="glyphicon glyphicon-trash" ></span> 确定吗?');
			}
			else{
				$(this).attr('button-state', 'deleting').removeClass('btn-danger').addClass('disabled');
				
				var post_data = {};
				var id_2_delete = $(this).attr('data-product-id');
				if(id_2_delete == null || id_2_delete == undefined){
					post_data.searchcompo = $(this).attr('data-product-searchword');
				}else{
					post_data.product = id_2_delete;
				}
				console.log(post_data);
				
				     $.ajax({
						dataType: "html",
						type: "get",
						evalScripts: true,
						url: $.cookie("__BASEURL")+'/ajax/deletepotential',
						data: (post_data),
						success: function (data, textStatus){
						// Create an instance of Notyf
							var notyf = new Notyf();

							if(data.indexOf('delete ok') > -1){
								notyf.confirm('删除成功');
								$('#potential_'+id_2_delete).fadeOut(300);
							}else{
								console.log(data);
								notyf.alert('删除失败');
							}
							
						}
					});
			}
        }).mouseleave(function(){
			var buttonState = $(this).attr('button-state');
			if((buttonState == 'on')){
				$(this).removeClass('btn-danger').addClass('btn-primary').attr('button-state', 'off');
				$(this).html('<span style="font-size: 12px;" class="glyphicon glyphicon-trash" ></span> 删除');
			}
		});
		
    });
