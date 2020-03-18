<?php
/**
*
* Woo Total Sales General Overview block 
*
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

$awts_backend = new Woo_Total_Sales();
$awts_wc_status = new Woo_Total_Sales_GO_WC_Reports();
//$awts_wc_reports = new WC_Admin_Report();
//set_current_screen('woo-total-sales' );

$screen = get_current_screen();
//print_pre($screen);

?>

<div id="woocommerce_dashboard_status" class="postbox ">
	<div class="inside">
		<?php $awts_wc_status->status_widget(); ?>
	</div>
</div>
