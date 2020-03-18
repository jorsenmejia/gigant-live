<?php
/**
 * WCFM plugin views
 *
 * Plugin WC Vatiations Additional Image Products Manage Views
 *
 * @author 		WC Lovers
 * @package 	wcfmu/views/thirdparty
 * @version   3.0.2
 */

// WC Vatiations Additional Image Variaton Date Edit
add_filter( 'wcfm_variation_edit_data', 'wcfmu_thirdparty_wc_vatiation_gallery_product_data_variations', 10, 3 );

function wcfmu_thirdparty_wc_vatiation_gallery_product_data_variations( $variations, $variation_id, $variation_id_key ) {
	global $wp, $WCFM, $WCFMu, $wpdb;
	
	if( $variation_id  ) {
		
		$gallery_img_urls = array();
		$gallery_img_ids = get_post_meta( $variation_id, '_wc_additional_variation_images', true );
		if( $gallery_img_ids ) {
			$gallery_img_ids = explode( ',', $gallery_img_ids );
			if( is_array( $gallery_img_ids ) && !empty( $gallery_img_ids ) ) {
				foreach( $gallery_img_ids as $gallery_img_id ) {
					$gallery_img_urls[]['gallery_image'] = wp_get_attachment_url( $gallery_img_id );
				}
			}
		}
		
		$variations[$variation_id_key]['wc_additional_variation_images'] = $gallery_img_urls;
	}
	return $variations;
}

// WC Vatiations Additional Image View
add_filter( 'wcfm_product_manage_fields_variations', 'wcfmu_thirdparty_wc_vatiation_gallery_product_manage_fields_variations', 160, 4 );

function wcfmu_thirdparty_wc_vatiation_gallery_product_manage_fields_variations( $variation_fileds, $variations, $variation_shipping_option_array, $variation_tax_classes_options ) {
	global $wp, $WCFM, $WCFMu, $wpdb;
	
	if( $wcfm_is_allow_gallery = apply_filters( 'wcfm_is_allow_gallery', true ) ) {
		$gallerylimit = apply_filters( 'wcfm_gallerylimit', -1 );
		$variation_gallery_fields = array( "wc_additional_variation_images" => array( 'label' => __( 'Aditional Images', 'wc-frontend-manager-ultimate' ), 'type' => 'multiinput', 'class' => 'wcfm_additional_variation_images wcfm_ele variable', 'label_class' => 'wcfm_title', 'custom_attributes' => array( 'limit' => $gallerylimit ), 'options' => array(
																															"gallery_image" => array( 'type' => 'upload', 'prwidth' => 75),
																													) ) );
		
		$variation_fileds = array_slice($variation_fileds, 0, 6, true) +
																	$variation_gallery_fields +
																	array_slice($variation_fileds, 6, count($variation_fileds) - 1, true) ;
	}
	
	return $variation_fileds;
}
?>