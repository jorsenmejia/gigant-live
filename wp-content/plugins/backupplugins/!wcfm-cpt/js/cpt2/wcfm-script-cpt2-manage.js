var cpt2_form_is_valid = true;
jQuery( document ).ready( function( $ ) {
		
	// Collapsible
  $('.wcfm-tabWrap .page_collapsible').collapsible({
		defaultOpen: 'wcfm_products_manage_form_inventory_head',
		speed: 'slow',
		loadOpen: function (elem) { //replace the standard open state with custom function
			//console.log(elem);
		  elem.next().show();
		},
		loadClose: function (elem, opts) { //replace the close state with custom function
			//console.log(elem);
			elem.next().hide();
		},
		animateOpen: function(elem, opts) {
			$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
			elem.addClass('collapse-open');
			$('.collapse-close').find('span').removeClass('fa-arrow-circle-o-right block-indicator');
			elem.find('span').addClass('fa-arrow-circle-o-right block-indicator');
			$('.wcfm-tabWrap').find('.wcfm-container').stop(true, true).slideUp(opts.speed);
			elem.next().stop(true, true).slideDown(opts.speed);
		},
		animateClose: function(elem, opts) {
			elem.find('span').removeClass('fa-arrow-circle-o-up block-indicator');
			elem.next().stop(true, true).slideUp(opts.speed);
		}
	});
	$('.wcfm-tabWrap .page_collapsible').each(function() {
		$(this).html('<div class="page_collapsible_content_holder">' + $(this).html() + '</div>');
		$(this).find('.page_collapsible_content_holder').after( $(this).find('span') );
	});
	$('.wcfm-tabWrap .page_collapsible').find('span').addClass('fa');
	$('.collapse-open').addClass('collapse-close').removeClass('collapse-open');
	$('.wcfm-tabWrap').find('.wcfm-container').hide();
	$('.wcfm-tabWrap').find('.page_collapsible:first').click();
	
	if( $('.cpt2_taxonomies').length > 0 ) {
		$('.cpt2_taxonomies').each(function() {
			$("#" + $(this).attr('id')).select2({
				placeholder: wcfm_dashboard_messages.choose_select2 + $(this).attr('id') + " ..."
			});
		});
	}
	
	if( $("#wcfm_associate_vendor").length > 0 ) {
		$("#wcfm_associate_vendor").select2({
			placeholder: wcfm_dashboard_messages.choose_vendor_select2
		});
	}
	
	if( $('#cpt2_cats_checklist').length > 0 ) {
		$('.sub_checklist_toggler').each(function() {
			if( $(this).parent().find('.cpt2_taxonomy_sub_checklist').length > 0 ) { $(this).css( 'visibility', 'visible' ); }
		  $(this).click(function() {
		    $(this).toggleClass('fa-arrow-circle-down');
		    $(this).parent().find('.cpt2_taxonomy_sub_checklist').toggleClass('cpt2_taxonomy_sub_checklist_visible');
		  });
		});
		$('.cpt2_cats_checklist_item_hide_by_cap').attr( 'disabled', true );
	}
	
	// Tag Cloud
	if( $('.wcfm_fetch_tag_cloud').length > 0 ) {
		$wcfm_tag_cloud_fetched = false;
		$('.wcfm_fetch_tag_cloud').click(function() {
		  if( !$wcfm_tag_cloud_fetched ) {
				var data = {
					action : 'get-tagcloud',
					tax    : 'post_tag'
				}	
				$.post(wcfm_params.ajax_url, data, function(response) {
					if(response) {
						$('.wcfm_fetch_tag_cloud').html(response);
						$wcfm_tag_cloud_fetched = true;
						
						$('.tag-cloud-link').each(function() {
						  $(this).click(function(event) {
						  	event.preventDefault();
						  	$tag = $(this).text();
						  	$tags = $('#cpt2_tags').val();
						  	if( $tags.length > 0 ) {
						  		$tags += ',' + $tag;
						  	} else {
						  		$tags = $tag;
						  	}
						  	$('#cpt2_tags').val($tags);
						  });
						});
					}
				});
			}
		});
	}
	

	
	function wcfm_cpt2_manage_form_validate() {
		cpt2_form_is_valid = true;
		$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
		var title = $.trim($('#wcfm_cpt2_manage_form').find('#title').val());
		$('#wcfm_cpt2_manage_form').find('#title').removeClass('wcfm_validation_failed').addClass('wcfm_validation_success');
		if(title.length == 0) {
			$('#wcfm_cpt2_manage_form').find('#title').removeClass('wcfm_validation_success').addClass('wcfm_validation_failed');
			cpt2_form_is_valid = false;
			$('#wcfm_cpt2_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + wcfm_cpt2_manage_messages.no_title).addClass('wcfm-error').slideDown();
			audio.play();
		}
		
		$( document.body ).trigger( 'wcfm_cpt2_manage_form_validate' );
		
		$wcfm_is_valid_form = cpt2_form_is_valid;
		$( document.body ).trigger( 'wcfm_form_validate', $('#wcfm_cpt2_manage_form') );
		cpt2_form_is_valid = $wcfm_is_valid_form;
		
		return cpt2_form_is_valid;
	}
	
	// Draft Cpt2
	$('#wcfm_cpt2_simple_draft_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
	  $is_valid = wcfm_cpt2_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var excerpt = getWCFMEditorContent( 'excerpt' );
			
			var description = getWCFMEditorContent( 'description' );
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-cpt2-manage', 
				wcfm_cpt2_manage_form : $('#wcfm_cpt2_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'draft'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-error').removeClass('wcfm-success').slideUp();
					if($response_json.status) {
						audio.play();
						$('#wcfm_cpt2_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
							if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						audio.play();
						$('#wcfm_cpt2_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#cpt2_id').val($response_json.id);
					$('#wcfm-content').unblock();
				}
			});	
		}
	});
	
	// Submit Cpt2
	$('#wcfm_cpt2_simple_submit_button').click(function(event) {
	  event.preventDefault();
	  
	  // Validations
	  $is_valid = wcfm_cpt2_manage_form_validate();
	  
	  if($is_valid) {
			$('#wcfm-content').block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
			
			var excerpt = getWCFMEditorContent( 'excerpt' );
			
			var description = getWCFMEditorContent( 'description' );
			
			var data = {
				action : 'wcfm_ajax_controller',
				controller : 'wcfm-cpt2-manage',
				wcfm_cpt2_manage_form : $('#wcfm_cpt2_manage_form').serialize(),
				excerpt     : excerpt,
				description : description,
				status : 'submit'
			}	
			$.post(wcfm_params.ajax_url, data, function(response) {
				if(response) {
					$response_json = $.parseJSON(response);
					$('.wcfm-message').html('').removeClass('wcfm-success').removeClass('wcfm-error').slideUp();
					wcfm_notification_sound.play();
					if($response_json.status) {
						$('#wcfm_cpt2_manage_form .wcfm-message').html('<span class="wcicon-status-completed"></span>' + $response_json.message).addClass('wcfm-success').slideDown( "slow", function() {
						  if( $response_json.redirect ) window.location = $response_json.redirect;	
						} );
					} else {
						$('#wcfm_cpt2_manage_form .wcfm-message').html('<span class="wcicon-status-cancelled"></span>' + $response_json.message).addClass('wcfm-error').slideDown();
					}
					if($response_json.id) $('#cpt2_id').val($response_json.id);
					wcfmMessageHide();
					$('#wcfm-content').unblock();
				}
			});
		}
	});
	
	function jsUcfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
} );