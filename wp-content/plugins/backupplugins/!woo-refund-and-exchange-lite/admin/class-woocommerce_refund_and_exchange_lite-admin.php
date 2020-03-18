<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class woocommerce_refund_and_exchange_lite_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $woocommerce_refund_and_exchange_lite    The ID of this plugin.
	 */
	private $woocommerce_refund_and_exchange_lite;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $woocommerce_refund_and_exchange_lite       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $woocommerce_refund_and_exchange_lite, $version ) {

		$this->woocommerce_refund_and_exchange_lite = $woocommerce_refund_and_exchange_lite;
		$this->version = $version;

		if ( ! defined( 'MWB_RNX_LITE_ADMIN_PATH' ) ) {
			define( 'MWB_RNX_LITE_ADMIN_PATH', plugin_dir_path( __FILE__ ) );
		}

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_refund_and_exchange_lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_refund_and_exchange_lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->woocommerce_refund_and_exchange_lite, plugin_dir_url( __FILE__ ) . 'css/woocommerce_refund_and_exchange_lite-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'ced-rnx-slick-css', plugin_dir_url( __FILE__ ) . 'css/slick.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'ced-rnx-slick-theme-css', plugin_dir_url( __FILE__ ) . 'css/slick-theme.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in woocommerce_refund_and_exchange_lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The woocommerce_refund_and_exchange_lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->woocommerce_refund_and_exchange_lite, plugin_dir_url( __FILE__ ) . 'js/woocommerce_refund_and_exchange_lite-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'ced-rnx-slick-js', plugin_dir_url( __FILE__ ) . 'js/slick.min.js', array( 'jquery' ), $this->version, false );

		$ajax_nonce = wp_create_nonce( 'ced-rnx-ajax-seurity-string' );
		$translation_array = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'ced_rnx_nonce' => $ajax_nonce,
		);
		wp_localize_script( $this->woocommerce_refund_and_exchange_lite, 'global_rnx', $translation_array );
		wp_enqueue_script( $this->woocommerce_refund_and_exchange_lite );

	}

	/**
	 * Register the menu and submenus for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_menus() {

		require_once 'settings/class-woocommerce_refund_and_exchange_lite-settings.php';
		$tabs = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';
		$sections = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : '';
		$admin_obj = new MwbBasicframeworkAdminSettings();
		if ( 'ced_rnx_setting' == $tabs ) {
			if ( 'refund' == $sections || '' == $sections ) {
				$admin_obj->ced_rnx_setting_save();
			}
		}
	}

	public function ced_rnx_register_custom_order_status() {
		register_post_status(
			'wc-refund-requested',
			array(
				'label'                     => 'Refund Requested',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: 1: seconds  2: time */
				'label_count'               => _n_noop( 'Refund Requested <span class="count">(%s)</span>', 'Refund Requested <span class="count">(%s)</span>' ),
			)
		);

		register_post_status(
			'wc-refund-approved',
			array(
				'label'                     => 'Refund Approved',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: 1: seconds  2: time */
				'label_count'               => _n_noop( 'Refund Approved <span class="count">(%s)</span>', 'Refund Approved <span class="count">(%s)</span>' ),
			)
		);

		register_post_status(
			'wc-refund-cancelled',
			array(
				'label'                     => 'Refund Cancelled',
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: 1: seconds  2: time */
				'label_count'               => _n_noop( 'Refund Cancelled <span class="count">(%s)</span>', 'Refund Cancelled <span class="count">(%s)</span>' ),
			)
		);
	}

	public function ced_rnx_add_custom_order_status( $ced_rnx_order_statuses ) {
		$ced_rnx_new_order_statuses = array();
		foreach ( $ced_rnx_order_statuses as $ced_rnx_key => $ced_rnx_status ) {

			$ced_rnx_new_order_statuses[ $ced_rnx_key ] = $ced_rnx_status;

			if ( 'wc-completed' === $ced_rnx_key ) {
				$ced_rnx_new_order_statuses['wc-refund-requested'] = __( 'Refund Requested', 'woocommerce-refund-and-exchange-lite' );
				$ced_rnx_new_order_statuses['wc-refund-approved']  = __( 'Refund Approved', 'woocommerce-refund-and-exchange-lite' );
				$ced_rnx_new_order_statuses['wc-refund-cancelled'] = __( 'Refund Cancelled', 'woocommerce-refund-and-exchange-lite' );
			}
		}
		return $ced_rnx_new_order_statuses;
	}

	/**
	 * This function is approve return request and decrease product quantity from order
	 *
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function ced_rnx_return_req_approve_callback() {
		$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );

		if ( $check_ajax ) {
			if ( current_user_can( 'ced-rnx-refund-approve' ) ) {
				$orderid = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				$products = get_post_meta( $orderid, 'ced_rnx_return_product', true );

				// Fetch the return request product
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( $product['status'] == 'pending' ) {
							$product_datas = $product['products'];
							$products[ $date ]['status'] = 'complete';
							$approvdate = date_i18n(wc_date_format(), time());
							$products[ $date ]['approve_date'] = $approvdate;
							break;
						}
					}
				}

				// Update the status
				update_post_meta( $orderid, 'ced_rnx_return_product', $products );

				$request_files = get_post_meta( $orderid, 'ced_rnx_return_attachment', true );

				if ( isset( $request_files ) && ! empty( $request_files ) ) {
					foreach ( $request_files as $date => $request_file ) {
						if ( $request_file['status'] == 'pending' ) {
							$request_files[ $date ]['status'] = 'complete';
							break;
						}
					}
				}

				// Update the status
				update_post_meta( $orderid, 'ced_rnx_return_attachment', $request_files );

				$order = new WC_Order( $orderid );
				$fname = get_option( 'ced_rnx_notification_from_name' );
				$fmail = get_option( 'ced_rnx_notification_from_mail' );

				$headers = array();
				$headers[] = "From: $fname <$fmail>";
				$headers[] = 'Content-Type: text/html; charset=UTF-8';

				$to = get_post_meta( $orderid, '_billing_email', true );

				$subject = get_option( 'ced_rnx_notification_return_approve_subject', false );
				$approve = get_option( 'ced_rnx_notification_return_approve', false );

				$fname = get_post_meta( $orderid, '_billing_first_name', true );
				$lname = get_post_meta( $orderid, '_billing_last_name', true );

				$fullname = $fname . ' ' . $lname;

				$approve = str_replace( '[username]', $fullname, $approve );
				$approve = str_replace( '[order]', '#' . $orderid, $approve );
				$approve = str_replace( '[siteurl]', home_url(), $approve );

				$mail_header = stripslashes( get_option( 'ced_rnx_notification_mail_header', false ) );
				$mail_footer = stripslashes( get_option( 'ced_rnx_notification_mail_footer', false ) );

				$message = '<html>
				<body>
					<style>
						body {
							box-shadow: 2px 2px 10px #ccc;
							color: #767676;
							font-family: Arial,sans-serif;
							margin: 80px auto;
							max-width: 700px;
							padding-bottom: 30px;
							width: 100%;
						}

						h2 {
							font-size: 30px;
							margin-top: 0;
							color: #fff;
							padding: 40px;
							background-color: #557da1;
						}

						h4 {
							color: #557da1;
							font-size: 20px;
							margin-bottom: 10px;
						}

						.content {
							padding: 0 40px;
						}

						.Customer-detail ul li p {
							margin: 0;
						}

						.details .Shipping-detail {
							width: 40%;
							float: right;
						}

						.details .Billing-detail {
							width: 60%;
							float: left;
						}

						.details .Shipping-detail ul li,.details .Billing-detail ul li {
							list-style-type: none;
							margin: 0;
						}

						.details .Billing-detail ul,.details .Shipping-detail ul {
							margin: 0;
							padding: 0;
						}

						.clear {
							clear: both;
						}

						table,td,th {
							border: 2px solid #ccc;
							padding: 15px;
							text-align: left;
						}

						table {
							border-collapse: collapse;
							width: 100%;
						}

						.info {
							display: inline-block;
						}

						.bold {
							font-weight: bold;
						}

						.footer {
							margin-top: 30px;
							text-align: center;
							color: #99B1D8;
							font-size: 12px;
						}
						dl.variation dd {
							font-size: 12px;
							margin: 0;
						}
					</style>

					<div style="text-align: center; padding: 10px;" class="header">
						' . $mail_header . '
					</div>		
					
					<div class="header">
						<h2>' . __( 'Your Refund Request is Approved', 'woocommerce-refund-and-exchange-lite' ) . '</h2>
					</div>
					<div class="content">
						<div class="reason">
							<p>' . $approve . '</p>
						</div>
						<div class="Order">
							<h4>Order #' . $orderid . '</h4>
							<table>
								<tbody>
									<tr>
										<th>' . __( 'Product', 'woocommerce-refund-and-exchange-lite' ) . '</th>
										<th>' . __( 'Quantity', 'woocommerce-refund-and-exchange-lite' ) . '</th>
										<th>' . __( 'Price', 'woocommerce-refund-and-exchange-lite' ) . '</th>
									</tr>';
									$order = wc_get_order( $orderid );
									$requested_products = $products[ $date ]['products'];

				if ( isset( $requested_products ) && ! empty( $requested_products ) ) {
					$total = 0;
					$mwb_get_refnd = get_post_meta( $orderid, 'ced_rnx_return_product', true );
					if ( ! empty( $mwb_get_refnd ) ) {
						foreach ( $mwb_get_refnd as $key => $value ) {
							if ( isset( $value['amount'] ) ) {
								$total_price = $value['amount'];
								break;
							}
						}
					}
					foreach ( $order->get_items() as $item_id => $item ) {
						$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
						foreach ( $requested_products as $requested_product ) {
							if ( $item_id == $requested_product['item_id'] ) {

								if ( isset( $requested_product['variation_id'] ) && $requested_product['variation_id'] > 0 ) {
									$prod = wc_get_product( $requested_product['variation_id'] );

								} else {
									$prod = wc_get_product( $requested_product['product_id'] );
								}

								$prod_price = wc_get_price_excluding_tax( $prod, array( 'qty' => 1 ) );
								$subtotal = $prod_price * $requested_product['qty'];
								$total += $subtotal;
								if ( WC()->version < '3.1.0' ) {
									$item_meta      = new WC_Order_Item_Meta( $item, $_product );
									$item_meta_html = $item_meta->display( true, true );
								} else {
									$item_meta      = new WC_Order_Item_Product( $item, $_product );
									$item_meta_html = wc_display_item_meta( $item_meta, array( 'echo' => false ) );
								}
								$message .= '<tr>
													<td>' . $item['name'] . '<br>';
									$message .= '<small>' . $item_meta_html . '</small>
														<td>' . $requested_product['qty'] . '</td>
														<td>' . wc_price( $requested_product['price'] * $requested_product['qty'] ) . '</td>
													</tr>';

							}
						}
					}
					$message .= '<tr>
										<th colspan="2">Total:</th>
										<td>' . wc_price( $total_price ) . '</td>
									</tr>
									<tr>
										<th colspan="3">Extra:</th>
									</tr>';
				}
				if ( WC()->version < '3.0.0' ) {
					$order_id = $order->id;
				} else {
					$order_id = $order->get_id();
				}
								$added_fees = get_post_meta( $order_id, 'ced_rnx_return_added_fee', true );
				if ( isset( $added_fees ) && ! empty( $added_fees ) ) {
					foreach ( $added_fees as $da => $added_fee ) {
						if ( $date == $da ) {
							foreach ( $added_fee as $fee ) {
								$total -= $fee['val'];

								$message .= ' <tr>
												<th colspan="2">' . $fee['text'] . ':</th>
												<td>' . wc_price( $fee['val'] ) . '</td>
											</tr>';
							}
						}
					}
				}
				if ( WC()->version < '3.0.0' ) {
					$order_id = $order->id;
				} else {
					$order_id = $order->get_id();
				}
							$message .= ' <tr>
										<th colspan="2">' . __( 'Refund Total', 'woocommerce-refund-and-exchange-lite' ) . ':</th>
										<td>' . wc_price( $total_price ) . '</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="Customer-detail">
							<h4>' . __( 'Customer details', 'woocommerce-refund-and-exchange-lite' ) . '</h4>
							<ul>
								<li>
									<p class="info">
										<span class="bold">' . __( 'Email', 'woocommerce-refund-and-exchange-lite' ) . ': </span>' . get_post_meta( $order_id, '_billing_email', true ) . '
									</p>
								</li>
								<li>
									<p class="info">
										<span class="bold">' . __( 'Tel', 'woocommerce-refund-and-exchange-lite' ) . ': </span>' . get_post_meta( $order_id, '_billing_phone', true ) . '
									</p>
								</li>
							</ul>
						</div>
						<div class="details">
							<div class="Shipping-detail">
								<h4>' . __( 'Shipping Address', 'woocommerce-refund-and-exchange-lite' ) . '</h4>
								' . $order->get_formatted_shipping_address() . '
							</div>
							<div class="Billing-detail">
								<h4>' . __( 'Billing Address', 'woocommerce-refund-and-exchange-lite' ) . '</h4>
								' . $order->get_formatted_billing_address() . '
							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div style="text-align: center; padding: 10px;" class="footer">
						' . $mail_footer . '
					</div>
				</body>
				</html>';

				$fullname = $fname . ' ' . $lname;

				$message = str_replace( '[username]', $fullname, $message );
				$message = str_replace( '[order]', '#' . $order_id, $message );
				$message = str_replace( '[siteurl]', home_url(), $message );

				$html_content = $message;

				wc_mail( $to, $subject, $html_content, $headers );

				$final_stotal = 0;
				$lastElement = end( $order->get_items() );
				foreach ( $order->get_items() as $item_id => $item ) {
					if ( $item != $lastElement ) {
						$final_stotal += $item['subtotal'];
					}
				}

				update_post_meta( $orderid, 'discount', 0 );

				if ( $final_stotal > 0 ) {
					$mwb_rnx_obj = wc_get_order( $orderid );
					$tax_rate = 0;
					$tax = new WC_Tax();
					$country_code = WC()->countries->countries[ $mwb_rnx_obj->billing_country ]; // or populate from order to get applicable rates
					$rates = $tax->find_rates( array( 'country' => $country_code ) );
					foreach ( $rates as $rate ) {
						$tax_rate = $rate['rate'];
					}

					$total_ptax = $final_stotal * $tax_rate / 100;
					$orderval = $final_stotal + $total_ptax;
					$orderval = round( $orderval, 2 );

					// Coupons used in the order LOOP (as they can be multiple)
					foreach ( $mwb_rnx_obj->get_used_coupons() as $coupon_name ) {
						$coupon_post_obj = get_page_by_title( $coupon_name, OBJECT, 'shop_coupon' );
						$coupon_id = $coupon_post_obj->ID;
						$coupons_obj = new WC_Coupon( $coupon_id );

						$coupons_amount = $coupons_obj->get_amount();
						$coupons_type = $coupons_obj->get_discount_type();
						if ( $coupons_type == 'percent' ) {
							$finaldiscount = $orderval * $coupons_amount / 100;
						}
					}

					$discount = $finaldiscount * 100 / ( 100 + $tax_rate );

					if ( $discount > 0 ) {
						update_post_meta( $orderid, 'discount', $discount );
					} else {
						update_post_meta( $orderid, '_cart_discount_tax', 0.00 );
						update_post_meta( $orderid, 'discount', 0.00 );
					}
				}
				$new_fee_old            = new stdClass();
				$new_fee_old->name      = esc_attr( 'Refundable Amount' );
				$new_fee_old->amount    = (float) esc_attr( $total_price );
				$new_fee_old->tax_class = '';
				$new_fee_old->taxable   = false;
				$new_fee->tax       = $totalProducttax;
				$new_fee_old->tax_data  = array();
				$item_id = $order->add_fee( $new_fee_old );

				$order->update_status( 'wc-refund-approved', __( 'User Request of Refund Product is approved', 'woocommerce-refund-and-exchange-lite' ) );
				//$order->calculate_totals();
				$response['response'] = 'success';
				echo wp_json_encode( $response );
				die;
			}
		}
	}

	/**
	 * This function is process cancel Refund request
	 *
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function ced_rnx_return_req_cancel_callback() {
		 $check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'ced-rnx-refund-cancel' ) ) {
				$orderid = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

				$products = get_post_meta( $orderid, 'ced_rnx_return_product', true );

				// Fetch the return request product
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( $product['status'] == 'pending' ) {
							$product_datas = $product['products'];
							$products[ $date ]['status'] = 'cancel';
							$approvdate = date_i18n(wc_date_format(), time());
							$products[ $date ]['cancel_date'] = $approvdate;
							break;
						}
					}
				}

				// Update the status
				update_post_meta( $orderid, 'ced_rnx_return_product', $products );

				$request_files = get_post_meta( $orderid, 'ced_rnx_return_attachment', true );
				if ( isset( $request_files ) && ! empty( $request_files ) ) {
					foreach ( $request_files as $date => $request_file ) {
						if ( $request_file['status'] == 'pending' ) {
							$request_files[ $date ]['status'] = 'cancel';
						}
					}
				}

				// Update the status
				update_post_meta( $orderid, 'ced_rnx_return_attachment', $request_files );

				$order = wc_get_order( $orderid );
				$fname = get_option( 'ced_rnx_notification_from_name' );
				$fmail = get_option( 'ced_rnx_notification_from_mail' );

				$headers[] = "From: $fname <$fmail>";
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$to = get_post_meta( $orderid, '_billing_email', true );
				$subject = get_option( 'ced_rnx_notification_return_cancel_subject', false );
				$message = stripslashes( get_option( 'ced_rnx_notification_return_cancel', false ) );
				$order_id = $orderid;
				$fname = get_post_meta( $orderid, '_billing_first_name', true );
				$lname = get_post_meta( $orderid, '_billing_last_name', true );

				$fullname = $fname . ' ' . $lname;
				$message = str_replace( '[username]', $fullname, $message );
				$message = str_replace( '[order]', '#' . $order_id, $message );
				$message = str_replace( '[siteurl]', home_url(), $message );

				$mail_header = stripslashes( get_option( 'ced_rnx_notification_mail_header', false ) );
				$mail_footer = stripslashes( get_option( 'ced_rnx_notification_mail_footer', false ) );
				$html_content = '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
				</head>
				<body>
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td style="text-align: center; margin-top: 30px; padding: 10px; color: #99B1D8; font-size: 12px;">
								' . $mail_header . '
							</td>
						</tr>
						<tr>
							<td>
								<table align="center" cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-family:Open Sans; max-width: 600px; width: 100%;">
									<tr>
										<td style="padding: 36px 48px; width: 100%; background-color:#557DA1;color: #fff; font-size: 30px; font-weight: 300; font-family:helvetica;">' . $subject . '</td>
									</tr>
									<tr>
										<td style="width:100%; padding: 36px 48px 10px; background-color:#fdfdfd; font-size: 14px; color: #737373;">' . $message . '</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td style="text-align: center; margin-top: 30px; color: #99B1D8; font-size: 12px;">
								' . $mail_footer . '
							</td>
						</tr>
					</table>
				</body>
				</html>';

				wc_mail( $to, $subject, $html_content, $headers );

				$order->update_status( 'wc-refund-cancelled', __( 'User Request of Refund Product is approevd', 'woocommerce-refund-and-exchange-lite' ) );
				$response['response'] = 'success';
				echo wp_json_encode( $response );
				die;
			}
		}
	}

	/**
	 * Manage stock when product is actually back in stock.
	 *
	 * @name ced_rnx_manage_stock
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function ced_rnx_manage_stock() {
		$check_ajax = check_ajax_referer( 'ced-rnx-ajax-seurity-string', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'ced-rnx-refund-manage-stock' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
				$order_id = sanitize_text_field( $order_id );
				if ( $order_id > 0 ) {
					$ced_rnx_type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

					if ( $ced_rnx_type != '' ) {
						if ( $ced_rnx_type == 'ced_rnx_return' ) {
							$manage_stock = get_option( 'mwb_wrma_return_request_manage_stock' );
							if ( $manage_stock == 'yes' ) {
								$ced_rnx_return_data = get_post_meta( $order_id, 'ced_rnx_return_product', true );
								if ( is_array( $ced_rnx_return_data ) && ! empty( $ced_rnx_return_data ) ) {
									foreach ( $ced_rnx_return_data as $date => $requested_data ) {
										$ced_rnx_returned_products = $requested_data['products'];
										if ( is_array( $ced_rnx_returned_products ) && ! empty( $ced_rnx_returned_products ) ) {
											foreach ( $ced_rnx_returned_products as $key => $product_data ) {
												if ( $product_data['variation_id'] > 0 ) {
													$product = wc_get_product( $product_data['variation_id'] );
												} else {
													$product = wc_get_product( $product_data['product_id'] );
												}
												if ( $product->managing_stock() ) {
													$avaliable_qty = $product_data['qty'];
													if ( WC()->version < '3.0.0' ) {
														$product->set_stock( $avaliable_qty, 'add' );
													} else {
														if ( $product_data['variation_id'] > 0 ) {
															$total_stock = get_post_meta( $product_data['variation_id'], '_stock', true );
															$total_stock = $total_stock + $avaliable_qty;
															wc_update_product_stock( $product_data['variation_id'], $total_stock, 'set' );
														} else {
															$total_stock = get_post_meta( $product_data['product_id'], '_stock', true );
															$total_stock = $total_stock + $avaliable_qty;
															wc_update_product_stock( $product_data['product_id'], $total_stock, 'set' );
														}
													}
													update_post_meta( $order_id, 'ced_rnx_manage_stock_for_return', 'no' );
													$response['result'] = 'success';
													$response['msg'] = __( 'Product Stock is updated Succesfully.', 'woocommerce-refund-and-exchange-lite' );
												} else {
													$response['result'] = false;
													$response['msg'] = __( 'Product Stock is not updated as manage stock setting of product is disable.', 'woocommerce-refund-and-exchange-lite' );
												}
											}
										}
									}
								}
							}
						}
					}
				}
				echo wp_json_encode( $response );
				die();
			}
		}
	}

	/**
	 * update left amount because amount is refunded.
	 *
	 * @name ced_rnx_action_woocommerce_order_refunded
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function ced_rnx_action_woocommerce_order_refunded( $order_get_id, $refund_get_id ) {
		update_post_meta( $refund_get_id['order_id'], 'ced_rnx_left_amount_done', 'yes' );
	}

}
