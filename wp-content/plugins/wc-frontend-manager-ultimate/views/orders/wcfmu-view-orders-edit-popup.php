<?php
/**
 * WCFM plugin view
 *
 * WCfM Edit Order popup View
 *
 * @author 		WC Lovers
 * @package 	wcfmu/views/orders
 * @version   5.2.1
 */
 
global $wp, $WCFM, $WCFMmp, $_POST, $wpdb;

if( !$order_id ) return;

$order                  = wc_get_order( $order_id );
$line_items             = $order->get_items( 'line_item' );
$line_items             = apply_filters( 'wcfm_valid_line_items', $line_items, $order_id );

?>

<div class="wcfm-clearfix"></div><br />
<div id="wcfm_order_edit_form_wrapper">
	<form action="" method="post" id="wcfm_order_edit_form" class="order_edit-form wcfm_popup_wrapper" novalidate="">
		<div style="margin-bottom: 15px;"><h2 style="float: none;"><?php echo __( 'Order Edit', 'wc-frontend-manager-ultimate' ) . ' #' . $order_id; ?></h2></div>
		
		<?php foreach( $line_items as $order_item_id => $item ) { ?>
			<p class="wcfm-order_edit-form-request-amount wcfm_popup_label">
				<strong for="wcfm_order_edit_request_amount"><?php echo $item->get_name() . ' ' . __( 'Quantity', 'wc-frontend-manager-ultimate' ); ?> <span class="required">*</span></strong> 
			</p>
			<?php $WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_order_edit_quantity" => array( 'type' => 'number', 'name' => 'wcfm_order_edit_quantity['.$order_item_id.']', 'attributes' => array( 'min' => '1', 'step' => '1' ), 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm-order_edit-form-quantity wcfm_popup_input', 'label_class' => 'wcfm_title', 'value' => ( $item->get_quantity() ? esc_html( $item->get_quantity() ) : '1' ) ) ) ); ?>
		<?php } ?>
		
		<?php if( wc_coupons_enabled() && apply_filters( 'wcfm_orders_manage_discount', true ) ) { ?>
			<p class="wcfm-order_edit-form-request-amount wcfm_popup_label">
				<strong for="wcfm_order_edit_discount_amount"><?php _e( 'Apply Discount', 'wc-frontend-manager-ultimate' ); ?></strong> 
			</p>
			<?php $WCFM->wcfm_fields->wcfm_generate_form_field( array( "wcfm_om_discount" => array( 'type' => 'number', 'attributes' => array( 'min' => '1', 'step' => '1' ), 'class' => 'wcfm-text wcfm_ele wcfm_non_negative_input wcfm-order_edit-form-discount-amount wcfm_popup_input', 'label_class' => 'wcfm_title', 'value' => '' ) ) ); ?>
		<?php } ?>
		
		<p class="wcfm-order_edit-form-reason wcfm_popup_label">
			<strong for="comment"><?php _e( 'Note to Customer', 'wc-frontend-manager-ultimate' ); ?></strong>
		</p>
		<textarea id="wcfm_om_comments" name="wcfm_om_comments" class="wcfm_popup_input wcfm_popup_textarea"></textarea>
	
		<div class="wcfm_clearfix"></div>
		<div class="wcfm-message" tabindex="-1"></div>
		<div class="wcfm_clearfix"></div><br />
		
		<p class="form-submit">
			<input name="submit" type="submit" id="wcfm_order_edit_submit_button" class="submit wcfm_popup_button" value="<?php _e( 'Submit', 'wc-frontend-manager-ultimate' ); ?>"> 
			<input type="hidden" name="wcfm_order_edit_id" value="<?php echo $order_id; ?>" id="wcfm_order_edit_order_id">
		</p>	
	</form>
</div>
<div class="wcfm-clearfix"></div>