<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Ivole_StructuredData' ) ) :

	class Ivole_StructuredData {

	  public function __construct() {
			if( 'yes' == get_option( 'ivole_attach_image', 'no' ) ) {
				if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0.0' ) >= 0 ) {
					add_filter( 'woocommerce_structured_data_review', array( $this, 'filter_woocommerce_structured_data_review' ), 10, 2 );
				}
			}
	  }

		public function filter_woocommerce_structured_data_review( $markup, $comment ) {
			$pics = get_comment_meta( $comment->comment_ID, 'ivole_review_image' );
			$pics_n = count( $pics );
			if( $pics_n > 0 ) {
				//error_log( print_r( $comment, true ) );
				$markup['associatedMedia']  = array();
				for( $i = 0; $i < $pics_n; $i ++) {
					$markup['associatedMedia'][]  = array(
						'@type' => 'ImageObject',
						'name' => sprintf( __( 'Image #%1$d from ', IVOLE_TEXT_DOMAIN ), $i + 1 ) . $comment->comment_author,
						'contentUrl' => $pics[$i]['url']
					);
				}
			}
			return $markup;
		}

	}

endif;

?>
