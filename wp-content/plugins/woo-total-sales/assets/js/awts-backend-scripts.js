jQuery(document).ready(function($){
	
	$(function () {
    	$("#awts-filter-by-date").on('change', function () {
        	var current_date = $(this).val();
        	var datastring = 'current_date=' + current_date + '&action=get_orders_archive';
            $.ajax({
            	type: 'POST',
            	url: ajax_var.url,
            	data: datastring,
            	dataType: "json",
            	success: function (response) {

                	if (response.type == 'success') {
                    	$('#awts-monthly-sales-summary > p').remove();
                        $('.awts-products-list').remove();
                        $('#awts-monthly-sales-summary').append(response.html);
                    }
                    else if (response.type == 'error') {
                    	$('#awts-monthly-sales-summary > p').remove();
                        $('.awts-products-list').remove();
                        $('#awts-monthly-sales-summary').append(response.html);
                	} },
});   });   });
});