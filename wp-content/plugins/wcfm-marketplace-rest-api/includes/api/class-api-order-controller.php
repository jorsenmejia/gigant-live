<?php
class WCFM_REST_Order_Controller extends WCFM_REST_Controller {
/**
   * Endpoint namespace
   *
   * @var string
   */
  protected $namespace = 'wcfmmp/v1';

  /**
    * Route name
    *
    * @var string
    */
  protected $base = 'orders';

  /**
    * Post type
    *
    * @var string
    */
  protected $post_type = 'shop_order';
  
   /**
     * Post status
     */
    protected $post_status = array();

    /**
     * Stores the request.
     * @var array
     */
    protected $request = array();

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function __construct() {
        $this->post_status = array_keys( wc_get_order_statuses() );

//        add_filter( 'woocommerce_new_order_data', array( $this, 'set_order_vendor_id' ) );
//        add_action( 'woocommerce_rest_insert_shop_order_object', array( $this, 'after_order_create' ), 10, 2 );
    }
    
  /**
   * Register the routes for orders.
   */
  public function register_routes() {
      register_rest_route( $this->namespace, '/' . $this->base, array(
          array(
              'methods'             => WP_REST_Server::READABLE,
              'callback'            => array( $this, 'get_items' ),
              'permission_callback' => array( $this, 'get_orders_permissions_check' ),
              'args'                => $this->get_collection_params(),
          ),
          'schema' => array( $this, 'get_public_item_schema' ),
      ) );
      
      register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'wcfm-marketplace-rest-api' ),
                    'type'        => 'integer',
                )
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'args'                => $this->get_collection_params(),
                'permission_callback' => array( $this, 'get_single_order_permissions_check' ),
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_order_status' ),
                'args'                => array(
                    'status' => array(
                        'type'        => 'string',
                        'description' => __( 'Order Status', 'wcfm-marketplace-rest-api' ),
                        'required'    => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    )
                ),
                'permission_callback' => array( $this, 'update_order_permissions_check' ),
            ),
      ));
  }
  
  /**
     * Get object.
     *
     * @since  1.0.0
     * @param  int $id Object ID.
     * @return WC_Data
     */
    public function get_object( $id ) {
      if(!wc_get_order($id))
        return new WP_Error( "wcfmapi_rest_invalid_{$this->post_type}_id", sprintf( __( "Invalid ID", 'wcfm-marketplace-rest-api' ), __METHOD__ ), array( 'status' => 404 ) );
      return wc_get_order( $id );
    }
  
  
  /**
     * Checking if have any permission to view orders
     *
     * @since 1.0.0
     *
     * @return boolean
     */
  public function get_orders_permissions_check() {
    if( !is_user_logged_in() )
      return false;
    if( apply_filters( 'wcfm_is_allow_orders', true ) )
      return true;
    return false;
  }
    
  public function update_order_permissions_check() {
    if( !is_user_logged_in() )
      return false;
    if( apply_filters( 'wcfm_is_allow_order_status_update', true ) )
      return true;
    return false;
  }
    
  public function get_single_order_permissions_check() {
    if( !is_user_logged_in() )
      return false;
    if( apply_filters( 'wcfm_is_allow_orders', true ) )
      return true;
    return false;
  }
    
  
  public function get_post_type_items( $request ) {
    global $WCFM;
      
    $orders = $this->get_objects_from_database($request);
    $order_return_obj = array();
    foreach ($orders as $each_order ) {
      
      if($each_order->vendor_id) {
        $order_object = $this->get_object( $each_order->order_id );
        $formated_order_data = $this->get_formatted_item_data($order_object, $each_order->vendor_id );
        $formated_order_data['vendor_order_details'] = $each_order;
        $order_return_obj[] =  $formated_order_data;
      } else {
        $order_object = $this->get_object($each_order->ID);
        
        $order_return_obj[] = $this->get_formatted_item_data($order_object, 0 );
      }
    }
    $response = rest_ensure_response($order_return_obj);
    return apply_filters( "wcfmapi_rest_prepare_{$this->post_type}_objects", $response, $orders, $request );
  }
  
  protected function get_objects_from_database( $request ) {
    global $WCFM;
    $_POST["controller"] = 'wcfm-orders';
    $_POST['length'] = !empty($request['per_page']) ? intval($request['per_page']) : 10;
    $_POST['start'] = !empty($request['page']) ? ( intval($request['page']) - 1 ) * $_POST['length'] : 0;
//    if(empty($request['page'])){
//      $_POST['start'] = !empty($request['offset']) ? intval($request['offset']) : 0;
//    }
    $_POST['filter_date_form'] = !empty($request['after']) ? $request['after'] : '';
    $_POST['filter_date_to'] = !empty($request['before']) ? $request['before'] : '';
    $_POST['search']['value'] = !empty($request['search']) ? $request['search'] : '';    
    $_POST['orderby'] = !empty($request['orderby']) ? $request['orderby'] : '';
    $_POST['order'] = !empty($request['order']) ? $request['order'] : '';
    
    define('WCFM_REST_API_CALL', TRUE);
    $WCFM->init();
    $orders = $WCFM->ajax->wcfm_ajax_controller();
    return $orders;
  }
  
  
  public function get_post_type_item( $request , $id ) {
    global $WCFM;
    $order_return_obj = array();
    if( wcfm_is_vendor() ) {
      
      $is_order_for_vendor = $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $id );
      if( $is_order_for_vendor ) {
        $current_vendor   = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
        $order_object = $this->get_object( $id );
        $order_return_obj[] = $this->get_formatted_item_data($order_object, $current_vendor );
      } else {
        return new WP_Error( "wcfmapi_rest_invalid_vendor", sprintf( __( "Invalid Vendor - Order Id do not belong to the loggedin vendor", 'wcfm-marketplace-rest-api' ), __METHOD__ ), array( 'status' => 404 ) );
      }
      
    } else {
      $order_object = $this->get_object( $id );
      $order_return_obj[] = $this->get_formatted_item_data($order_object, 0 );
    }
    $response = rest_ensure_response($order_return_obj);
    return apply_filters( "wcfmapi_rest_prepare_{$this->post_type}_object", $response, $order_object, $request );
  }
  
  
  protected function get_formatted_item_data( $object, $each_order_vendor_id ) {
    $data = $object->get_data();
    $format_date       = array( 'date_created', 'date_modified', 'date_completed', 'date_paid' );
    $format_line_items = array( 'line_items', 'tax_lines', 'shipping_lines', 'fee_lines', 'coupon_lines' );
    // Format date values.
    foreach ( $format_date as $key ) {
      $datetime              = $data[ $key ];
      $data[ $key ]          = wc_rest_prepare_date_response( $datetime, false );
      $data[ $key . '_gmt' ] = wc_rest_prepare_date_response( $datetime );
    }
    
    // Format line items.
    
    foreach ( $format_line_items as $key ) {
      if( $each_order_vendor_id ) {
        $line_item_datas = array_values( array_map( array( $this, 'get_order_item_data' ),  $data[ $key ] ) );
        $line_item_datas_final = array();
        //print_r($line_item_datas);
        if($key == 'line_items') {
          //print_r($line_item_datas);
          foreach( $line_item_datas as $item_key => $line_item ) {
            $order_item_product = new WC_Order_Item_Product($line_item['id']);
            $order_product_vendor_id = $order_item_product->get_meta('_vendor_id', true);
            if( $order_product_vendor_id && $order_product_vendor_id  == $each_order_vendor_id ) {
              $line_item_datas_final[] = $line_item;
            }
            // $line_item_datas_final[] = $line_item;
          } 
        } 
        else if( $key == 'shipping_lines' ) {
          foreach( $line_item_datas as $item_key => $line_item ) {
            //var_dump($line_item['id']);
            $order_item_shipping = new WC_Order_Item_Shipping($line_item['id']);
            $shipping_vendor_id = $order_item_shipping->get_meta('vendor_id', true);
            if( $shipping_vendor_id && $shipping_vendor_id  == $each_order_vendor_id ) {
              $line_item_datas_final[] = $line_item;
            }
            // $line_item_datas_final[] = $line_item;
          }
        } 
        else {
          $line_item_datas_final = $line_item_datas;
        }
        $data[ $key ] = $line_item_datas_final;
        
      } else {
        $data[ $key ] = array_values( array_map( array( $this, 'get_order_item_data' ),  $data[ $key ] ) );
      }
    }
    
    // Format the order status.
    $data['status'] = 'wc-' === substr( $data['status'], 0, 3 ) ? substr( $data['status'], 3 ) : $data['status'];
    return $data;
  }
  
  public function update_order_status( $request ) {

    global $WCFM;

      $id             = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
      $status         = isset( $request['status'] ) ? $request['status'] : '';
      
      if(substr($status, 0, 2) !== 'wc-'){
        $status = 'wc-' . $status;
      }
      $order_statuses = wc_get_order_statuses();

      if ( empty( $id ) ) {
          return new WP_Error( "wcfmapi_rest_invalid_{$this->post_type}_id", __( 'Invalid order ID', 'wcfm-marketplace-rest-api' ), array(
              'status' => 404,
          ) );
      }

      if ( empty( $status ) ) {
          return new WP_Error( "wcfmapi_rest_empty_{$this->post_type}_status", __( 'Order status must me required', 'wcfm-marketplace-rest-api' ), array(
              'status' => 404,
          ) );
      }

      if ( ! in_array( $status, array_keys( $order_statuses ) ) ) {
          return new WP_Error( "wcfmapi_rest_invalid_{$this->post_type}_status", __( 'Order status not valid', 'wcfm-marketplace-rest-api' ), array(
              'status' => 404,
          ) );
      }

      // $order = $this->get_object( $id );
      // $order->set_status( $status );
      // $order =  apply_filters( "wcfmapi_rest_pre_insert_{$this->post_type}_object", $order, $request );
      // $order->save();
      $_POST['order_id'] = $id;
      $_POST['order_status'] = $status;
      define('WCFM_REST_API_CALL', TRUE);
      $WCFM->init();
      $order_status_change = $WCFM->ajax->wcfm_modify_order_status();
      return $this->get_post_type_item($request, $id);
  }
  
  
  /**
    * Expands an order item to get its data.
    *
    * @param WC_Order_item $item
    *
    * @return array
    */
  protected function get_order_item_data( $item ) {
    
      $data           = $item->get_data();
      $format_decimal = array( 'subtotal', 'subtotal_tax', 'total', 'total_tax', 'tax_total', 'shipping_tax_total' );

      // Format decimal values.
      foreach ( $format_decimal as $key ) {
          if ( isset( $data[ $key ] ) ) {
              $data[ $key ] = wc_format_decimal( $data[ $key ], ( isset($this->request['dp']) ) ? $this->request['dp'] : false );
          }
      }

      // Add SKU and PRICE to products.
      if ( is_callable( array( $item, 'get_product' ) ) ) {
          $data['sku']   = $item->get_product() ? $item->get_product()->get_sku(): null;
          $data['price'] = (float)( $item->get_total() / max( 1, $item->get_quantity() ) );
      }

      // Format taxes.
      if ( ! empty( $data['taxes']['total'] ) ) {
          $taxes = array();

          foreach ( $data['taxes']['total'] as $tax_rate_id => $tax ) {
              $taxes[] = array(
                  'id'       => $tax_rate_id,
                  'total'    => $tax,
                  'subtotal' => isset( $data['taxes']['subtotal'][ $tax_rate_id ] ) ? $data['taxes']['subtotal'][ $tax_rate_id ] : '',
              );
          }
          $data['taxes'] = $taxes;
      } elseif ( isset( $data['taxes'] ) ) {
          $data['taxes'] = array();
      }

      // Remove names for coupons, taxes and shipping.
      if ( isset( $data['code'] ) || isset( $data['rate_code'] ) || isset( $data['method_title'] ) ) {
          unset( $data['name'] );
      }

      // Remove props we don't want to expose.
      unset( $data['order_id'] );
      unset( $data['type'] );

      return $data;
  }
}