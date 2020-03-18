<?php
/**
 * WCFM plugin view
 *
 * WCFM CPT1 Manage Tabs view
 *
 * @author 		WC Lovers
 * @package 	wcfmcpt/views/cpt1
 * @version   1.0.0
 */
 
global $wp, $WCFM, $WCFMcpt;


$custom_1 = '';
$custom_2 = '';
$custom_3 = '';
$custom_4 = '';

if( $cpt1_id ) {
	// Custom Meta Fields
	$custom_1 = get_post_meta( $cpt1_id, 'custom_1', true);
	$custom_2 = get_post_meta( $cpt1_id, 'custom_2', true);
	$custom_3 = get_post_meta( $cpt1_id, 'custom_3', true);
	$custom_4 = get_post_meta( $cpt1_id, 'custom_4', true);
}

?>
<!-- collapsible 1 -->
<div class="page_collapsible cpt1_manager_custom" id="wcfm_cpt1_manager_form_custom_head"><label class="fa fa-database"></label><?php _e('Custom', 'wcfm-cpt'); ?><span></span></div>
<div class="wcfm-container">
	<div id="wcfm_cpt1_manager_form_custom_expander" class="wcfm-content">
		<?php
		$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_cpt1_fields_custom', array(
																																														"custom_1" => array('label' => __('Custom 1', 'wcfm-cpt') , 'type' => 'text', 'class' => 'wcfm-text', 'label_class' => 'wcfm_title', 'value' => $custom_1, 'hints' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'wcfm-cpt' )),
																																														"custom_2" => array('label' => __('Custom 2', 'wcfm-cpt') , 'type' => 'checkbox', 'class' => 'wcfm-checkbox wcfm_ele', 'value' => 'enable', 'label_class' => 'wcfm_title wcfm_ele', 'hints' => __('Enable stock management at product level', 'wcfm-cpt'), 'dfvalue' => $custom_2 ),
																																														"custom_3" => array('label' => __('Custom 3', 'wcfm-cpt') , 'type' => 'number', 'class' => 'wcfm-text wcfm_ele simple variable non_manage_stock_ele', 'label_class' => 'wcfm_title wcfm_ele', 'value' => $custom_3, 'hints' => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'wcfm-cpt' ), 'attributes' => array( 'min' => '1', 'step'=> '1' ) ),
																																														"custom_4" => array('label' => __('Custom 4', 'wcfm-cpt') , 'type' => 'select', 'options' => array('instock' => __('In stock', 'wcfm-cpt'), 'outofstock' => __('Out of stock', 'wcfm-cpt'), 'onbackorder' => __( 'On backorder', 'wcfm-cpt' ) ), 'class' => 'wcfm-select wcfm_eleg', 'label_class' => 'wcfm_ele wcfm_title stock_status_ele', 'value' => $custom_4, 'hints' => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'wcfm-cpt' )),
																																										), $cpt1_id ) );
		?>
	</div>
</div>
<!-- end collapsible -->
<div class="wcfm_clearfix"></div>