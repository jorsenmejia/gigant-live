(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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

	 $(document).ready(function(){


	 	$("#ced_rnx_accept_return").click(function(){

	 		$("#ced_rnx_return_package").hide();
	 		$(".ced_rnx_return_loader").show();
	 		var orderid = $(this).data('orderid');
	 		var date = $(this).data('date');
	 		var data = {
	 			action:'ced_return_req_approve',
	 			orderid:orderid,
	 			date:date,
	 			security_check	: global_rnx.ced_rnx_nonce	
	 		};
	 		$.ajax({
	 			url: global_rnx.ajaxurl, 
	 			type: "POST",  
	 			data: data,
	 			dataType :'json',	
	 			success: function(response) 
	 			{
	 				$(".ced_rnx_return_loader").hide();
	 				$(".refund-actions .cancel-action").hide();

	 				window.location.reload(true);

	 			}
	 		});
	 	});	

	 	$("#ced_rnx_left_amount").click(function(){
	 		$(this).attr('disabled','disabled');

	 		var order_id = $(this).data('orderid');
	 		var refund_amount = $(".ced_rnx_total_amount_for_refund").val();
	 		$('html, body').animate({
	 			scrollTop: $("#order_shipping_line_items").offset().top
	 		}, 2000);

	 		$( 'div.wc-order-refund-items' ).slideDown();
	 		$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-order-refund-items' ).slideUp();
	 		$( 'div.wc-order-totals-items' ).slideUp();
	 		$( '#woocommerce-order-items' ).find( 'div.refund' ).show();
	 		$( '.wc-order-edit-line-item .wc-order-edit-line-item-actions' ).hide();
	 		var refund_amount = $("#ced_rnx_refund_amount").val();
	 		var refund_reason = $("#ced_rnx_refund_reason").val();
	 		$("#refund_amount").val(refund_amount);
	 		$("#refund_reason").val(refund_reason);

	 		var total = accounting.unformat( refund_amount, woocommerce_admin.mon_decimal_point );

	 		$( 'button .wc-order-refund-amount .amount' ).text( accounting.formatMoney( total, {
	 			symbol:    woocommerce_admin_meta_boxes.currency_format_symbol,
	 			decimal:   woocommerce_admin_meta_boxes.currency_format_decimal_sep,
	 			thousand:  woocommerce_admin_meta_boxes.currency_format_thousand_sep,
	 			precision: woocommerce_admin_meta_boxes.currency_format_num_decimals,
	 			format:    woocommerce_admin_meta_boxes.currency_format
	 		} ) );

	 	});	


	 	$("#ced_rnx_cancel_return").click(function(){
	 		$(".ced_rnx_return_loader").show();
	 		var orderid = $(this).data('orderid');
	 		var date = $(this).data('date');
	 		var data = {
	 			action:'ced_return_req_cancel',
	 			orderid:orderid,
	 			date:date,
	 			security_check	:	global_rnx.ced_rnx_nonce	
	 		};
	 		$.ajax({
	 			url: global_rnx.ajaxurl, 
	 			type: "POST",  
	 			data: data,
	 			dataType :'json',	
	 			success: function(response) 
	 			{
	 				$(".ced_rnx_return_loader").hide();
	 				location.reload(true);
	 			}
	 		});
	 	});	
	 	jQuery(document).on('click','#ced_rnx_stock_back',function(){
	 		jQuery(this).attr('disabled','disabled');
	 		var order_id = jQuery(this).data('orderid');
	 		var type = jQuery(this).data('type');
	 		var data = { 
	 			action   : 'ced_rnx_manage_stock' ,
	 			order_id : order_id ,
	 			type     : type,
	 			security_check : global_rnx.ced_rnx_nonce
	 		};
	 		jQuery.ajax({
	 			url: global_rnx.ajaxurl, 
	 			type: "POST",             
	 			data: data,
	 			dataType :'json',
	 			success: function(response)   
	 			{
	 				jQuery(this).removeAttr('disabled');
	 				if(response.result)
	 				{
	 					jQuery("#post").prepend('<div class="updated notice notice-success is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
	 					jQuery('html, body').animate({
	 						scrollTop: jQuery("body").offset().top
	 					}, 2000, "linear", function(){
	 						window.setTimeout(function() {
	 							window.location.reload();
	 						}, 1000);
	 					});
	 				}
	 				else
	 				{
	 					jQuery("#post").prepend('<div id="messege" class="notice notice-error is-dismissible" id="message"><p>'+response.msg+'</p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div>'); 
	 					jQuery('html, body').animate({
	 						scrollTop: jQuery("body").offset().top
	 					}, 2000, "linear", function(){
	 					});
	 				}
	 			}
	 		});
	 	});

	 	$("#ced_rnx_return_predefined_reason_add").click(function(){
	 		var html = '';
	 		html += '<input type="text" name="ced_rnx_return_predefined_reason[]" value="" class="input-text">';
	 		$("#ced_rnx_return_predefined_reason_wrapper").append(html);
	 	});
	 	$('#ced_rnx_accordion h2').on('click',function(){
	 		if($(this).next('.ced_rnx_content_sec').is(":visible"))
	 		{ 
	 			$(this).removeClass('ced_rnx_slide_active');
	 		}     
	 		else
	 		{
	 			$(this).addClass('ced_rnx_slide_active');
	 		}
	 		$(this).next('.ced_rnx_content_sec').slideToggle('slow');

	 	});
	 	$("#rnx_mail_setting").click(function(){
	 		if($("#rnx_mail_setting_wrapper").is(":visible"))
	 		{ 
	 			$(this).removeClass('ced_rnx_slide_active');
	 		}     
	 		else
	 		{
	 			$(this).addClass('ced_rnx_slide_active');
	 		}
	 		$("#rnx_mail_setting_wrapper").slideToggle('slow');
	 	});
	 	$("#rnx_return_reason").click(function(){
	 		if($("#rnx_return_reason_wrapper").is(":visible"))
	 		{ 
	 			$(this).removeClass('ced_rnx_slide_active');
	 		}     
	 		else
	 		{
	 			$(this).addClass('ced_rnx_slide_active');
	 		}
	 		$("#rnx_return_reason_wrapper").slideToggle('slow');
	 	});
	 	$("#rnx_exchange_reason").click(function(){
	 		if($("#rnx_exchange_reason_wrapper").is(":visible"))
	 		{ 
	 			$(this).removeClass('ced_rnx_slide_active');
	 		}     
	 		else
	 		{
	 			$(this).addClass('ced_rnx_slide_active');
	 		}
	 		$("#rnx_exchange_reason_wrapper").slideToggle('slow');
	 	});
	 	$("#rnx_refund_rules").click(function(){
	 		if($("#rnx_refund_rules_wrapper").is(":visible"))
	 		{ 
	 			$(this).removeClass('ced_rnx_slide_active');
	 		}     
	 		else
	 		{
	 			$(this).addClass('ced_rnx_slide_active');
	 		}
	 		$("#rnx_refund_rules_wrapper").slideToggle('slow');
	 	});

	 	$(document).ready(function(){
	 		$(".ced-news-wrap").slick({
	 			prevArrow: '<span class="ced-rnx-arrow left"><i class="dashicons dashicons-arrow-left-alt2"></i></span>',
	 			nextArrow: '<span class="ced-rnx-arrow right"><i class="dashicons dashicons-arrow-right-alt2"></i></span>',
	 			slidesToShow: 1,
	 			slidesToScroll: 1,
	 			autoplay: true,
	 			autoplaySpeed: 4000,
	 		});
	 		$(document).find('#wpbody-content').addClass('mwb_custom_wrap');

	 	});


	 });

})( jQuery );
