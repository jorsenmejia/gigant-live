<?php
/**
*
* Woo Total Sales General Overview block 
*
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Don't allow direct access

$awts_backend = new Woo_Total_Sales_backend();
$awts_backend_go = new Woo_Total_Sales_GO();

 ?>

<div id="awts-monthly-sales-summary">

<?php $awts_backend_go->awts_montly_sales_dashboard_widget(); ?>
	
</div>