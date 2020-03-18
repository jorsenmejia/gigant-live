(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write $ code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	 

	 var files = {};
		 $(document).ready(function(){

		//Add more files to attachment
		$(".ced_rnx_return_request_morefiles").click(function(){
			var html = '<br/><input type="file" class="input-text ced_rnx_return_request_files" name="ced_rnx_return_request_files[]">';
			$("#ced_rnx_return_request_files").append(html);
		});
		
		//Pick all attached files
		$("#ced_rnx_return_request_files").on('change',".ced_rnx_return_request_files",function(e){
			files = {};
			var file_type = e.target.files;
			if(typeof file_type[0]['type'] != 'undefined')
			{
				var type = file_type[0]['type'];
			}	
			if(type == 'image/png' || type == 'image/jpg' || type == 'image/jpeg')
			{
			}	
			else
			{
				$(this).val("");
			}	
			
			var count = 0;
			$(".ced_rnx_return_request_files").each(function(){
				var filename = $(this).val();
				files[count] = e.target.files;
				count++;
			});
			
		});
		
		//Submit Retun Request form
		$("#ced_rnx_return_request_form").on('submit', function(e){
			e.preventDefault();	
			var orderid = $(this).data('orderid');
			var refund_amount = $('.ced_rnx_total_refund_price').val();
			var alerthtml = '';
			var selected_product = {};
			var count = 0;
			
			var rr_subject = $("#ced_rnx_return_request_subject").val();

			if(rr_subject == '' || rr_subject == null)
			{
				rr_subject = $("#ced_rnx_return_request_subject_text").val();
				if(rr_subject == '' || rr_subject == null)
				{
					alerthtml += '<li>'+global_rnx.return_subject_msg+'</li>';
				}
			}
			var rr_reason = $(".ced_rnx_return_request_reason").val();
			
			if(rr_reason == '' || rr_reason == null)
			{
				alerthtml += '<li>'+global_rnx.return_reason_msg+'</li>';
			}
			else
			{
				var r_reason = '';
				r_reason = rr_reason.trim();
				if(r_reason == '' || r_reason == null)
				{
					alerthtml += '<li>'+global_rnx.return_reason_msg+'</li>';
				}
			}	
			
			if(alerthtml != '')
			{
				$("#ced-return-alert").show();
				$("#ced-return-alert").html(alerthtml);
				$('html, body').animate({
					scrollTop: $("#ced_rnx_return_request_container").offset().top
				}, 800);
				return false;
			}
			else
			{
				$("#ced-return-alert").hide();
				$("#ced-return-alert").html(alerthtml);
			}	

			$(".ced_rnx_return_column").each(function(){
				if($(this).find("td:eq(0)").children('.ced_rnx_return_product')){
					var product_info = {};
					var variation_id = $(this).data("variationid");
					var product_id = $(this).data("productid");
					var item_id = $(this).data("itemid");
					var product_price = $(this).find('.ced_rnx_product_amount').val();
					var product_qty = $(this).find("td:eq(1)").children('.ced_rnx_return_product_qty').val();
					product_info['product_id'] = product_id;
					product_info['variation_id'] = variation_id;
					product_info['item_id'] = item_id;
					product_info['price'] = product_price;
					product_info['qty'] = product_qty;
					selected_product[count] = product_info;
					count++;
				}
			});

			var data = {	
				action	:'ced_rnx_return_product_info',
				products: selected_product,
				amount	: refund_amount,
				subject	: rr_subject,
				reason	: rr_reason,
				orderid : orderid,
				security_check	:	global_rnx.ced_rnx_nonce	
			}
			
			$(".ced_rnx_return_notification").show();
			
			//Upload attached files

			var formData = new FormData(this);
			formData.append('action', 'ced_rnx_return_upload_files');
			formData.append('security_check', global_rnx.ced_rnx_nonce);
			$("body").css("cursor", "progress");
			$.ajax({
				url: global_rnx.ajaxurl, 
				type: "POST",             
				data: formData, 
				contentType: false,       
				cache: false,             
				processData:false,
				success: function(respond)   
				{
					//Send return request
					
					$.ajax({
						url: global_rnx.ajaxurl, 
						type: "POST",  
						data: data,
						dataType :'json',	
						success: function(response) 
						{
							$(".ced_rnx_return_notification").hide();
							
							$("#ced-return-alert").html(response.msg);
							$("#ced-return-alert").removeClass('woocommerce-error');
							$("#ced-return-alert").addClass("woocommerce-message");
							$("#ced-return-alert").css("color", "#8FAE1B");
							$("#ced-return-alert").show();
							$('html, body').animate({
								scrollTop: $("#ced_rnx_return_request_container").offset().top
							}, 800);
							
							if(typeof response.auto_accept != 'undefined')
							{
								if(global_rnx.auto_accept == 'yes' && response.auto_accept == true)
								{
									var fullDate = new Date()
									var twoDigitMonth = ((fullDate.getMonth().length+1) === 1)? (fullDate.getMonth()+1) : '0' + (fullDate.getMonth()+1);
									var date = fullDate.getDate() + "-" + twoDigitMonth + "-" + fullDate.getFullYear();
									var auto_accept_data = {
										action:'ced_return_req_approve',
										orderid:orderid,
										date:date,
										autoaccept:true,
										security_check	:	global_rnx.ced_rnx_nonce	
									}
									
									$.ajax({
										url: global_rnx.ajaxurl, 
										type: "POST",  
										data: auto_accept_data,
										dataType :'json',	
										success: function(response) 
										{
											window.setTimeout(function() {
												window.location.href = global_rnx.myaccount_url;
											}, 1000);
										}
									});
								}
								else
								{
									window.setTimeout(function() {
										window.location.href = global_rnx.myaccount_url;
									}, 1000);
								}	
							}
							else
							{
								window.setTimeout(function() {
									window.location.href = global_rnx.myaccount_url;
								}, 1000);
							}	
						}
					});
				}
			});
		});
	var ced_rnx_return_request_subject = $("#ced_rnx_return_request_subject").val();
	if(ced_rnx_return_request_subject == null || ced_rnx_return_request_subject == ''){
		$("#ced_rnx_return_request_subject_text").show();
	}else{
		$("#ced_rnx_return_request_subject_text").hide();
	}

	$("#ced_rnx_return_request_subject").change(function(){
		var reason = jQuery(this).val();
		if(reason == null || reason == ''){
			$("#ced_rnx_return_request_subject_text").show();
		}else{
			$("#ced_rnx_return_request_subject_text").hide();
		}
	});
	});
})( jQuery );
