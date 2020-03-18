$cpt3_cat = '';
$cpt3_vendor = '';

jQuery(document).ready(function($) {
	
	$wcfm_cpt3_table = $('#wcfm-cpt3').DataTable( {
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
				d.controller = 'wcfm-cpt3',
				d.cpt3_cat      = $cpt3_cat,
				d.cpt3_vendor   = $cpt3_vendor,
				d.cpt3_status   = GetURLParameter( 'cpt3_status' )
			},
			"complete" : function () {
				initiateTip();
				
				// Fire wcfm-cpt3 table refresh complete
				$( document.body ).trigger( 'updated_wcfm-cpt3' );
			}
		}
	} );
	
	if( $('.dropdown_cpt3_cat').length > 0 ) {
		$('.dropdown_cpt3_cat').on('change', function() {
			$cpt3_cat = $('.dropdown_cpt3_cat').val();
			$wcfm_cpt3_table.ajax.reload();
		});
	}
	
	if( $('#dropdown_vendor').length > 0 ) {
		$('#dropdown_vendor').on('change', function() {
			$cpt3_vendor = $('#dropdown_vendor').val();
			$wcfm_cpt3_table.ajax.reload();
		}).select2( $wcfm_vendor_select_args );
	}
	
	// Delete Cpt3
	$( document.body ).on( 'updated_wcfm-cpt3', function() {
		$('.wcfm_cpt3_delete').each(function() {
			$(this).click(function(event) {
				event.preventDefault();
				var rconfirm = confirm(wcfm_cpt3_manage_messages.delete_confirm);
				if(rconfirm) deleteWCFMCpt3($(this));
				return false;
			});
		});
	});
	
	function deleteWCFMCpt3(item) {
		jQuery('#wcfm-cpt3_wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		var data = {
			action    : 'delete_wcfm_cpt3',
			cpt3id : item.data('cpt3id')
		}	
		jQuery.ajax({
			type:		'POST',
			url: wcfm_params.ajax_url,
			data: data,
			success:	function(response) {
				if($wcfm_cpt3_table) $wcfm_cpt3_table.ajax.reload();
				jQuery('#wcfm-cpt3_wrapper').unblock();
			}
		});
	}
	
	// Dashboard FIlter
	if( $('.wcfm_filters_wrap').length > 0 ) {
		$('.dataTable').before( $('.wcfm_filters_wrap') );
		$('.wcfm_filters_wrap').css( 'display', 'inline-block' );
	}
	
	// Screen Manager
	$( document.body ).on( 'updated_wcfm-cpt3', function() {
		$.each(wcfm_cpt3_screen_manage, function( column, column_val ) {
		  $wcfm_cpt3_table.column(column).visible( false );
		} );
	});
	
} );