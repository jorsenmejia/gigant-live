<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CR_Checkout' ) ) :

	class CR_Checkout {
		public $consent_text;

	  public function __construct() {
			if( 'yes' === get_option( 'ivole_customer_consent', 'no' ) ) {
				if( 'yes' === get_option( 'ivole_coupon_enable', 'no' ) ) {
					$this->consent_text = __( 'Check here to receive an invitation from CR (an independent third-party organization) to review your order. Once the review is published, you will receive a coupon to use for your next purchase.', IVOLE_TEXT_DOMAIN );
				} else {
					$this->consent_text = __( 'Check here to receive an invitation from CR (an independent third-party organization) to review your order', IVOLE_TEXT_DOMAIN );
				}
				add_action( 'woocommerce_checkout_terms_and_conditions', array( $this, 'display_cr_checkbox' ), 40 );
				add_action('woocommerce_checkout_update_order_meta', array( $this, 'cr_checkbox_meta' ) );
			}
	  }

		public function display_cr_checkbox() {
			echo '<p class="form-row validate-required">';
			echo '<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">';
			echo '<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="cr_customer_consent" id="cr_customer_consent" />';
			echo '<span class="woocommerce-terms-and-conditions-checkbox-text">' . $this->consent_text . '</span>';
			echo '</label>';
			echo '<input type="hidden" name="cr_customer_consent_field" value="1" />';
			echo '</p>';
		}

		public function cr_checkbox_meta( $order_id ) {
			if( isset( $_POST['cr_customer_consent_field'] ) && $_POST['cr_customer_consent_field'] ) {
				if( isset( $_POST['cr_customer_consent'] ) && $_POST['cr_customer_consent'] ) {
					update_post_meta( $order_id, '_ivole_cr_consent', 'yes' );
				} else {
					update_post_meta( $order_id, '_ivole_cr_consent', 'no' );
				}
			}
		}
	}

endif;

?>
