$cpt2_cat = '';
$cpt2_vendor = '';

jQuery(document).ready(function($) {
	
	$wcfm_cpt2_table = $('#wcfm-cpt2').DataTable( {
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
				d.controller = 'wcfm-cpt2',
				d.cpt2_cat      = $cpt2_cat,
				d.cpt2_vendor   = $cpt2_vendor,
				d.cpt2_status   = GetURLParameter( 'cpt2_status' )
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-cpt2 table refresh complete
				$( document.body ).trigger( 'updated_wcfm-cpt2' );
			}
		}
	} );
	
	if( $('.dropdown_cpt2_cat').length > 0 ) {
		$('.dropdown_cpt2_cat').on('change', function() {
			$cpt2_cat = $('.dropdown_cpt2_cat').val();
			$wcfm_cpt2_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$cpt2_vendor = $('#dropdown_vendor').val();
			$wcfm_cpt2_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Delete Cpt2
	$( document.body ).on( 'updated_wcfm-cpt2', function() {
		$('.wcfm_cpt2_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_cpt2_manage_messages.delete_confirm);
				if(rconfirm) deleteWCFMCpt2($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMCpt2(item) {
		jQuery('#wcfm-cpt2_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action    : 'delete_wcfm_cpt2',
			cpt2id : item.data('cpt2id')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_cpt2_table) $wcfm_cpt2_table.ajax.reload();
				jQuery('#wcfm-cpt2_wrapper').unblock();
			}
		});
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-cpt2', function() {
		$.each(wcfm_cpt2_screen_manage, function( column, column_val ) {
		  $wcfm_cpt2_table.column(column).visible( false );
		} );
	});
	
} );