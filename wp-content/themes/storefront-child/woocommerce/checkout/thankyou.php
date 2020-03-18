    <?php
    /**
     * Thankyou page
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce/Templates
     * @version 3.7.0
     */

    defined( 'ABSPATH' ) || exit;
    ?>
    <style>
        .elementor-element.elementor-element-4ea54b6.elementor-column.elementor-col-50.elementor-top-column {
    display: none;
}
    </style>
    <div class="woocommerce-order">

      <?php if ( $order ) :

        do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>

          <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

          <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
            <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
            <?php if ( is_user_logged_in() ) : ?>
              <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
            <?php endif; ?>
          </p>

        <?php else : ?>

          <div class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"> <i class="fas fa-check"></i><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank You. Your appointment request has been sent.', 'woocommerce' ), $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <br> <div><i style="float:left; padding-top:2px;"class="fas fa-info-circle"></i><?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?></div>
          </div>

            <div class="grid-container">
                <div class="grid-item1">

               
                <?php do_action( 'woocommerce_thankyou', $order->get_id() );
                
         
                ?></div>
                            
      <div class="grid-item2">
        <?php echo do_shortcode('[INSERT_ELEMENTOR id="77835"]');?>
      </div>
                
                
        <?php endif; ?></div>
              </div>


        

      <?php else : ?>

       <center><p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Change to ‘Thank You. Your appointment request has been sent.’
  ', 'woocommerce' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p></center>

      <?php endif; ?>

    </div>


    <style>
      tfoot {
        display: none;
    }

    section.woocommerce-customer-details {
        display: none;
    }

    .grid-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
  }


    </style>