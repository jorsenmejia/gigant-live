<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$image_src = '';
if ( isset( $_GET['section'] ) ) {
	$mwb_rnx_section = sanitize_text_field( wp_unslash($_GET['section'] ));
	if ( $mwb_rnx_section == 'exchange' ) {
		$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Order-Exchange-icon.png';
	} elseif ( $mwb_rnx_section == 'other' ) {
		$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Common-Setting-icon.png';
	} elseif ( $mwb_rnx_section == 'text_setting' ) {
		$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Text-Setting-icon.png';
	} elseif ( $mwb_rnx_section == 'cancel' ) {
		$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Order-Cancel-icon.png';
	} elseif ( $mwb_rnx_section == 'catalog_setting' ) {
		$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Product-Catalog-icon.png';
	}
} else {
	if ( isset( $_GET['tab'] ) ) {
		$mwb_rnx_tab = sanitize_text_field( wp_unslash($_GET['tab'] ));
		if ( $mwb_rnx_tab == 'exchange' ) {
			$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Order-Exchange-icon.png';
		} elseif ( $mwb_rnx_tab == 'return_ship_label_setting' ) {
			$image_src = MWB_REFUND_N_EXCHANGE_LITE_URL . '/admin/images/Return-Slip-Label-icon.png';
		}
	}
}

?>
<div class="ced_rnx_help_wrapper">
	 <div class="ced-purchase-main-plugin-wrap">
			<?php
			if ( $image_src != '' ) {
				?>
			 <img src="<?php esc_html_e(esc_url( $image_src)); ?>">
				<?php
			}
			?>
		 <h2 class="ced-purchase-heading"><?php esc_html_e( 'Please Purchase Our Premium version for this feature and also for some more exciting feature.', 'woocommerce-refund-and-exchange-lite' ); ?></h2>
		 <div class="ced-rnx-purchase-conetnt">
			 <a class="ced-rnx-news-button ced-prcchase-button" href="https://makewebbetter.com/product/woocommerce-rma-return-refund-exchange/" target="_blank"> <?php esc_html_e( 'Get it now', 'woocommerce-refund-and-exchange-lite' ); ?>
		 </a>
		 </div>
	 </div>
</div>
