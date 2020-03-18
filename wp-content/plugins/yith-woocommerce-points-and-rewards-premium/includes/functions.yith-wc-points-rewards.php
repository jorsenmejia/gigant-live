<?php
if ( !defined( 'ABSPATH' ) || ! defined( 'YITH_YWPAR_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements helper functions for YITH WooCommerce Points and Rewards
 *
 * @package YITH WooCommerce Points and Rewards
 * @since   1.0.0
 * @author  YITH
 */

global $yith_ywpar_db_version;

$yith_ywpar_db_version = '1.0.1';

if ( !function_exists( 'yith_ywpar_db_install' ) ) {
    /**
     * Install the table yith_ywpar_points_log
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywpar_db_install() {
        global $wpdb;
        global $yith_ywpar_db_version;

        $installed_ver = get_option( "yith_ywpar_db_version" );

        $table_name = $wpdb->prefix . 'yith_ywpar_points_log';

        $charset_collate = $wpdb->get_charset_collate();

        if( ! $installed_ver ){
                $sql = "CREATE TABLE $table_name (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `action` VARCHAR (255) NOT NULL,
            `order_id` int(11),
            `amount` int(11) NOT NULL,
            `date_earning` datetime NOT NULL,
            `cancelled` datetime,
            PRIMARY KEY (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'yith_ywpar_db_version', $yith_ywpar_db_version );
        }

        if ( $installed_ver == '1.0.0') {
            $sql = "SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='$table_name'";
            $cols = $wpdb->get_col($sql);

            if( is_array($cols) && !in_array('cancelled', $cols)){
                $sql = "ALTER TABLE $table_name ADD `cancelled` datetime";
                $wpdb->query( $sql);
            }
            update_option( 'yith_ywpar_db_version', $yith_ywpar_db_version );
        }
    }
}

if ( !function_exists( 'yith_ywpar_update_db_check' ) ) {
    /**
     * check if the function yith_ywpar_db_install must be installed or updated
     *
     * @return void
     * @since 1.0.0
     */
    function yith_ywpar_update_db_check() {
        global $yith_ywpar_db_version;

        if ( get_site_option( 'yith_ywpar_db_version' ) != $yith_ywpar_db_version ) {

            yith_ywpar_db_install();
        }
    }
}

if ( !function_exists( 'yith_ywpar_locate_template' ) ) {
    /**
     * Locate the templates and return the path of the file found
     *
     * @param string $path
     * @param array  $var
     *
     * @return string
     * @since 1.0.0
     */
    function yith_ywpar_locate_template( $path, $var = NULL ) {

        global $woocommerce;

        if ( function_exists( 'WC' ) ) {
            $woocommerce_base = WC()->template_path();
        }
        elseif ( defined( 'WC_TEMPLATE_PATH' ) ) {
            $woocommerce_base = WC_TEMPLATE_PATH;
        }
        else {
            $woocommerce_base = $woocommerce->plugin_path() . '/templates/';
        }

        $template_woocommerce_path = $woocommerce_base . $path;
        $template_path             = '/' . $path;
        $plugin_path               = YITH_YWPAR_DIR . 'templates/' . $path;

        $located = locate_template( array(
            $template_woocommerce_path, // Search in <theme>/woocommerce/
            $template_path,             // Search in <theme>/
            $plugin_path                // Search in <plugin>/templates/
        ) );

        if ( !$located && file_exists( $plugin_path ) ) {
            return apply_filters( 'yith_ywpar_locate_template', $plugin_path, $path );
        }

        return apply_filters( 'yith_ywpar_locate_template', $located, $path );
    }
}

if ( !function_exists( 'yith_ywpar_get_roles' ) ) {
    /**
     * Return the roles of users
     *
     * @return array
     * @since 1.0.0
     */
    function yith_ywpar_get_roles(){
        global $wp_roles;
        return $wp_roles->get_names();
    }
}

if ( ! function_exists( 'yith_ywpar_calculate_user_total_orders_amount' ) ) {
	/**
	 * Calculate the amount of all order completed and processed of a user
	 *
	 * @param $user_id
	 * @param int $order_id
	 *
	 * @return float
	 * @since 1.1.3
	 */

	function yith_ywpar_calculate_user_total_orders_amount( $user_id, $order_id = 0 ) {

		$orders = wc_get_orders( array( 'customer' => $user_id, 'status' => array( 'wc-completed', 'wc-processing' ) ) );
		$o = wc_get_order( $order_id );
		$total_amount = 0;

		if ( $orders ) {
			foreach ( $orders as $order ) {
				if( $order_id && $order_id == $order->id ){
					continue;
				}
				$total_amount += $order->get_subtotal();
			}
		}

		if( $o ){
			$total_amount += $o->get_subtotal();
		}

		return $total_amount;

	}
}

if ( ! function_exists( 'ywpar_get_customer_order_count' ) ) {
	/**
	 * Calculate the amount of all order completed and processed of a user
	 *
	 * @param $user_id
	 *
	 * @return float
	 * @internal param int $order_id
	 *
	 * @since    1.1.3
	 */

	function ywpar_get_customer_order_count( $user_id ) {

		$orders = wc_get_orders( array( 'customer' => $user_id, 'status' => array( 'wc-completed', 'wc-processing' ) , 'limit' => -1 ) );

		return count( $orders );

	}
}

/**
 * Provides functionality for array_column() to projects using PHP earlier than
 * version 5.5.
 * @copyright (c) 2015 WinterSilence (http://github.com/WinterSilence)
 * @license MIT
 */
if (!function_exists('array_column')) {
    /**
     * Returns an array of values representing a single column from the input
     * array.
     * @param array $array A multi-dimensional array from which to pull a
     *     column of values.
     * @param mixed $columnKey The column of values to return. This value may
     *     be the integer key of the column you wish to retrieve, or it may be
     *     the string key name for an associative array. It may also be NULL to
     *     return complete arrays (useful together with index_key to reindex
     *     the array).
     * @param mixed $indexKey The column to use as the index/keys for the
     *     returned array. This value may be the integer key of the column, or
     *     it may be the string key name.
     * @return array
     */
    function array_column(array $array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (!is_array($subArray)) {
                continue;
            } elseif (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $result[$subArray[$indexKey]] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $result[$subArray[$indexKey]] = $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

if ( ! function_exists( 'ywpar_get_price' ) ) {
	function ywpar_get_price( $product, $qty = 1, $price = '' ){

		if ( $price === '' ) {
			$price = $product->get_price();
		}

		$tax_display_mode = apply_filters('ywpar_get_price_tax_on_points', get_option( 'woocommerce_tax_display_shop' ) );
		$display_price = $tax_display_mode == 'incl' ? yit_get_price_including_tax( $product, $qty, $price ) : yit_get_price_excluding_tax( $product, $qty, $price );

		return $display_price;
	}
}

if ( ! function_exists( 'ywpar_get_subtotal_cart' ) ) {
	function ywpar_get_subtotal_cart(){

		$tax_display_mode = apply_filters('ywpar_get_price_tax_on_points', get_option( 'woocommerce_tax_display_shop' ) );
		if ( version_compare( WC()->version, '3.2.0', '>=' ) ) {
			$subtotal = ( $tax_display_mode == 'incl' ) ?   WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax() : WC()->cart->get_subtotal();
		}else{
			$subtotal = $tax_display_mode == 'incl' ? WC()->cart->subtotal : WC()->cart->subtotal_ex_tax;
		}

		return  apply_filters( 'ywpar_rewards_points_cart_subtotal', $subtotal );
	}
}

if ( function_exists( 'AW_Referrals' ) ) {
	add_filter( 'woocommerce_coupon_is_valid', 'validate_ywpar_coupon', 11, 2 );
	/**
	 * Compatibility with AutomateWoo - Referrals Add-on
	 * @param $valid
	 * @param $coupon
	 *
	 * @return bool
	 */
	function validate_ywpar_coupon( $valid, $coupon ) {
		if ( 'ywpar_discount' == $coupon->code ) {
			return true;
		}

		return $valid;
	}
}

if ( ! function_exists ( 'ywpar_coupon_is_valid' ) ) {

	/**
	 * Check if a coupon is valid
	 *
	 * @param $coupon
	 * @param array $object
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
	 */
	function ywpar_coupon_is_valid ( $coupon, $object = array() ) {
		if ( version_compare( WC()->version, '3.2.0', '>=' ) ) {
			$wc_discounts = new WC_Discounts( $object );
			$valid        = $wc_discounts->is_coupon_valid( $coupon );
			$valid        = is_wp_error( $valid ) ? false : $valid;
		}else{
			$valid = $coupon->is_valid();
		}

		return $valid;
	}

}

/**
 * WooCommerce Multilingual - MultiCurrency
 */
if ( function_exists( 'wcml_is_multi_currency_on' ) && wcml_is_multi_currency_on() ) {

	add_filter( 'ywpar_multi_currency_current_currency', 'ywpar_multi_currency_current_currency', 10 );
	if ( ! function_exists( 'ywpar_multi_currency_current_currency' ) ) {
		/**
		 * Get current currency.
		 *
		 * @param $currency
		 *
		 * @return mixed
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_multi_currency_current_currency( $currency ) {
			global $woocommerce_wpml;
			$client_currency = $woocommerce_wpml->multi_currency->get_client_currency();

			return ! empty( $client_currency ) ? $client_currency : $currency;
		}
	}

	add_filter( 'ywpar_get_active_currency_list', 'ywpar_get_active_currency_list' );
	if ( ! function_exists( 'ywpar_get_active_currency_list' ) ) {
		/**
		 * Return the list of active currencies.
		 *
		 * @param array $currencies
		 *
		 * @return array
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_get_active_currency_list( $currencies ) {
			global $woocommerce_wpml;
			$multi_currencies = $woocommerce_wpml->multi_currency->get_currencies( 'include_default = true' );
			if ( $multi_currencies ) {
				$currencies = array_keys( $multi_currencies );
			}

			return $currencies;
		}
	}

	add_action('woocommerce_coupon_loaded', 'remove_wcml_filter', 1);
	if ( ! function_exists( 'remove_wcml_filter' ) ) {

		/**
		 * Remove wcml filter when a coupon is loaded
		 * @param $coupon
		 *
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function remove_wcml_filter( $coupon ) {
			global $woocommerce_wpml;
			if ( YITH_WC_Points_Rewards_Redemption()->check_coupon_is_ywpar( $coupon ) ) {
				remove_action( 'woocommerce_coupon_loaded', array(
					$woocommerce_wpml->multi_currency->coupons,
					'filter_coupon_data'
				), 10 );
			}
		}
	}

}

if( class_exists( 'WC_Aelia_CurrencySwitcher') ){

	add_filter( 'ywpar_get_active_currency_list', 'ywpar_aelia_get_active_currency_list' );
	if ( ! function_exists( 'ywpar_aelia_get_active_currency_list' ) ) {

		/**
		 * Return the list of active currencies.
		 *
		 * @param array $currencies
		 *
		 * @return array
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */

		function ywpar_aelia_get_active_currency_list( $currencies ) {
			$settings_controller = WC_Aelia_CurrencySwitcher::settings();
			$enabled_currencies = $settings_controller->get_enabled_currencies();
			$currencies = !empty( $enabled_currencies ) ? $enabled_currencies : $currencies;

			return $currencies;
		}
	}

	add_action( 'woocommerce_coupon_get_amount', 'remove_aelia_filter_woocommerce_coupon_get_amount', 1, 2 );
	function remove_aelia_filter_woocommerce_coupon_get_amount( $amount, $coupon ) {
		$is_par = YITH_WC_Points_Rewards_Redemption()->check_coupon_is_ywpar( $coupon );
		if ( $is_par ) {
			remove_action( 'woocommerce_coupon_get_amount', array( WC_Aelia_CurrencyPrices_Manager::Instance(), 'woocommerce_coupon_get_amount' ), 5, 2 );
		}
		return $amount;
	}


}

if( class_exists( 'WOOCS_STARTER') ){

	add_filter( 'ywpar_get_active_currency_list', 'ywpar_woocommerce_currency_switcher_currency_list' );
	if ( ! function_exists( 'ywpar_woocommerce_currency_switcher_currency_list' ) ) {

		/**
		 * Return the list of active currencies.
		 *
		 * @param array $currencies
		 *
		 * @return array
		 * @since  1.5.3
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocommerce_currency_switcher_currency_list( $currencies ) {
			global $WOOCS;
			$enabled_currencies = array_keys( $WOOCS->get_currencies() );
			$currencies         = ! empty( $enabled_currencies ) ? $enabled_currencies : $currencies;

			return $currencies;
		}
	}

	add_action( 'ywpar_before_currency_loop', 'ywpar_woocommerce_currency_switcher_before_currency_loop' );
	if ( ! function_exists( 'ywpar_woocommerce_currency_switcher_before_currency_loop' ) ) {
		/**
		 * @since  1.5.3
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocommerce_currency_switcher_before_currency_loop() {
			global $WOOCS;
			remove_filter( 'woocommerce_currency_symbol', array( $WOOCS, 'woocommerce_currency_symbol' ), 9999 );
		}
	}

	//add_action( 'ywpar_before_rewards_message', 'ywpar_woocs_before_rewards_message' );
	if ( ! function_exists( 'ywpar_woocs_before_rewards_message' ) ) {
		/**
		 * @since 1.5.2
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocs_before_rewards_message() {
			global $WOOCS;
			remove_filter( 'wc_price_args', array( $WOOCS, 'wc_price_args' ), 9999 );

				remove_filter( 'raw_woocommerce_price', array( $WOOCS, 'raw_woocommerce_price' ), 9999 );

		}
	}

	add_action( 'ywpar_after_rewards_message', 'ywpar_woocs_after_rewards_message' );
	if ( ! function_exists( 'ywpar_woocs_after_rewards_message' ) ) {
		/**
		 * @since 1.5.2
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocs_after_rewards_message() {
			global $WOOCS;
			add_filter( 'wc_price_args', array( $WOOCS, 'wc_price_args' ), 9999 );
			if( !$WOOCS->is_multiple_allowed ){
				add_filter( 'raw_woocommerce_price', array( $WOOCS, 'raw_woocommerce_price' ), 9999 );
			}

		}
	}


	add_filter( 'ywpar_get_point_earned_price', 'ywpar_woocs_convert_price', 10, 2 );
	add_filter( 'ywpar_calculate_rewards_discount_max_discount_fixed', 'ywpar_woocs_convert_price', 10, 1 );
	add_filter( 'ywpar_calculate_rewards_discount_max_discount_percentual', 'ywpar_woocs_convert_price', 10, 1 );
	if ( ! function_exists( 'ywpar_woocs_convert_price' ) ) {
		/**
		 * @param $price
		 * @param string $currency
		 *
		 * @return float|int
		 * @since  1.5.3
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocs_convert_price( $price, $currency = '' ) {
			global $WOOCS;
			if( $WOOCS->is_multiple_allowed ){ return $price; }
			$currencies = $WOOCS->get_currencies();
			$currency   = empty( $currency ) ? $WOOCS->current_currency : $currency;
			if ( isset( $currencies[ $currency ] ) ) {
				$price = $price * $currencies[ $currency ]['rate'];
			}

			return $price;
		}
	}

	add_filter( 'ywpar_hide_value_for_max_discount', 'ywpar_woocs_hide_value_for_max_discount' );
	if ( ! function_exists( 'ywpar_woocs_hide_value_for_max_discount' ) ) {
		/**
		 * @return int
		 * @since  1.5.3
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocs_hide_value_for_max_discount( $discount ) {
			global $WOOCS;
			if( $WOOCS->is_multiple_allowed ){
				$currencies = $WOOCS->get_currencies();
				return $WOOCS->back_convert( $discount, $currencies[$WOOCS->current_currency]['rate'] ) ;
			}
			remove_all_filters( 'ywpar_calculate_rewards_discount_max_discount_fixed' );
			remove_all_filters( 'ywpar_calculate_rewards_discount_max_discount_percentual' );

			return YITH_WC_Points_Rewards_Redemption()->calculate_rewards_discount();
		}
	}

	add_filter( 'ywpar_adjust_discount_value', 'ywpar_woocs_adjust_discount_value' );
	if ( ! function_exists( 'ywpar_woocs_adjust_discount_value' ) ) {
		/**
		 * @return int
		 * @since  1.5.3
		 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
		 */
		function ywpar_woocs_adjust_discount_value( $discount ) {
			global $WOOCS;
			if( $WOOCS->is_multiple_allowed ){
				$currencies = $WOOCS->get_currencies();
				$discount=  $WOOCS->back_convert( $discount, $currencies[$WOOCS->current_currency]['rate'] ) ;
			}
			return $discount;
		}
	}

}

/**
 *
 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
 */
function ywpar_conversion_points_multilingual() {

	$old_conversion = get_option( 'yit_ywpar_multicurrency', false );
	if ( ! $old_conversion ) {
		$default_currency = get_woocommerce_currency();
		$roles            = yith_ywpar_get_roles();

		$options = array( 'earn_points_conversion_rate',
		                  'rewards_conversion_rate',
		                  'rewards_percentual_conversion_rate' );

		foreach ( $options as $option_name ) {
			$conversion_role = YITH_WC_Points_Rewards()->get_option( $option_name );
			//error_log( print_r( $conversion_role, true ) );
			$new_conversion_role = get_conversion_rate_with_default_currency( $conversion_role, $default_currency );
			//error_log( print_r( $conversion_role, true ) );
			YITH_WC_Points_Rewards()->set_option( $option_name, $new_conversion_role );
		}

		$options_by_roles = array( 'earn_points_role_', 'rewards_points_role_', 'rewards_points_percentual_role_' );
		foreach ( $options_by_roles as $option_name ) {
			foreach ( $roles as $role ) {
				$conversion_role = YITH_WC_Points_Rewards()->get_option( $option_name . $role );
				$new_conversion_role = get_conversion_rate_with_default_currency( $conversion_role, $default_currency );
				YITH_WC_Points_Rewards()->set_option( $option_name . $role, $new_conversion_role );
			}
		}

		update_option( 'yit_ywpar_multicurrency', true );
	}

}

/**
 * @param $options
 * @param $currency
 *
 * @return array
 * @author Emanuela Castorina <emanuela.castorina@yithemes.com>
 */
function get_conversion_rate_with_default_currency( $options, $currency ) {
	$new_option = array();
	if ( isset( $options['points'] ) ) {
		$new_option[ $currency ] = $options;
	} else {
		$new_option = $options;
	}

	return $new_option;
}