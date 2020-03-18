$cpt1_cat = '';
$cpt1_vendor = '';

jQuery(document).ready(function($) {
	
	$wcfm_cpt1_table = $('#wcfm-cpt1').DataTable( {
		"processing": true,
		"serverSide": true,
		"responsive": true,
		"pageLength": dataTables_config.pageLength,
		"language"  : $.parseJSON(dataTables_language),
		"columns"   : [
			              { responsivePriority: 2 },
										{ responsivePriority: 1 },
										{ responsivePriority: 3 },
										{ responsivePriority: 4 },
										{ responsivePriority: 3 },
										{ responsivePriority: 2 },
										{ responsivePriority: 5 },
										{ responsivePriority: 3 },
								],
		"columnDefs": [ { "targets": 0, "orderable" : false }, 
									  { "targets": 1, "orderable" : false }, 
										{ "targets": 2, "orderable" : false }, 
										{ "targets": 3, "orderable" : false }, 
										{ "targets": 4, "orderable" : false }, 
										{ "targets": 5, "orderable" : false },
										{ "targets": 6, "orderable" : false },
										{ "targets": 7, "orderable" : false },
									],
		'ajax': {
			"type"   : "POST",
			"url"    : wcfm_params.ajax_url,
			"data"   : function( d ) {
				d.action     = 'wcfm_ajax_controller',
				d.controller = 'wcfm-cpt1',
				d.cpt1_cat      = $cpt1_cat,
				d.cpt1_vendor   = $cpt1_vendor,
				d.cpt1_status   = GetURLParameter( 'cpt1_status' )
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-cpt1 table refresh complete
				$( document.body ).trigger( 'updated_wcfm-cpt1' );
			}
		}
	} );
	
	if( $('.dropdown_cpt1_cat').length > 0 ) {
		$('.dropdown_cpt1_cat').on('change', function() {
			$cpt1_cat = $('.dropdown_cpt1_cat').val();
			$wcfm_cpt1_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$cpt1_vendor = $('#dropdown_vendor').val();
			$wcfm_cpt1_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Delete Cpt1
	$( document.body ).on( 'updated_wcfm-cpt1', function() {
		$('.wcfm_cpt1_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_cpt1_manage_messages.delete_confirm);
				if(rconfirm) deleteWCFMCpt1($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMCpt1(item) {
		jQuery('#wcfm-cpt1_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action    : 'delete_wcfm_cpt1',
			cpt1id : item.data('cpt1id')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_cpt1_table) $wcfm_cpt1_table.ajax.reload();
				jQuery('#wcfm-cpt1_wrapper').unblock();
			}
		});
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-cpt1', function() {
		$.each(wcfm_cpt1_screen_manage, function( column, column_val ) {
		  $wcfm_cpt1_table.column(column).visible( false );
		} );
	});
	
} );