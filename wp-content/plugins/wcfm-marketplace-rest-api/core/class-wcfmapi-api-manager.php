<?php
/**
 * API_Registrar class
 */
class WCFMapi_API_Manager {
    /**
     * Class dir and class name mapping
     *
     * @var array
     */
    protected $class_map;
    /**
     * Constructor
     */
    public function __construct() {
      global $WCFMapi;
      if ( ! class_exists( 'WP_REST_Server' ) ) {
          return;
      }
      $this->class_map = apply_filters( 'wcfmapi_class_map', array(
          'product' => 'WCFM_REST_Product_Controller',
          'order'   => 'WCFM_REST_Order_Controller',
          'settings'   => 'WCFM_REST_Settings_Controller',
          'capabilities' => 'WCFM_REST_Capabilities_Controller',
          'notification' => 'WCFM_REST_Notification_Controller',
          'booking' => 'WCFM_REST_Booking_Controller',
          'site_details' => 'WCFM_REST_Site_Details_Controller',
          'sales_stats' => 'WCFM_REST_Sales_Stats_Controller',
          'enquiry' => 'WCFM_REST_Enquiry_Controller',
          'review' => 'WCFM_REST_Review_Controller',
          'store_vendors' => 'WCFM_REST_Store_Vendors_Controller',
          'deliveries'  => 'WCFM_REST_Deliveries_Controller',
          'support' => 'WCFM_REST_Support_Controller'
      ) );
      // Init REST API routes.
      add_action( 'rest_api_init', array( $this, 'wcfmapi_register_rest_routes' ), 10 );
      add_filter( 'wcfmapi_rest_prepare_product_object', array( $this, 'prepeare_product_response' ), 30, 3 );
    }
    
    
 
    /**
     * Register REST API routes.
     *
     * @since 1.2.0
     */
    public function wcfmapi_register_rest_routes() {
        foreach ( $this->class_map as $file_name => $controller ) {
          $this->load_controller($file_name);
          $controller = new $controller();
          $controller->register_routes();
        }
    }
    
    public function load_controller($class_name = '') {
      global $WCFMapi;
      if ('' != $class_name) {
        require_once ($WCFMapi->plugin_path . '/includes/api/class-api-' . $class_name . '-controller.php' );
      } // End If Statement
    }
    /**
     * Prepare object for product response
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function prepeare_product_response( $response, $object, $request ) {
      global $WCFM;
      $data = $response->get_data();
      //print_r($data); die;
      $author_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $data['id'] );
      $data['store'] = array(
          'id'        => $author_id,
          'name'      => $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor($author_id),
          'shop_name' => $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor($author_id),
          'url'       => wcfmmp_get_store_url($author_id),
          'address'   => $WCFM->wcfm_vendor_support->wcfm_get_vendor_address_by_vendor($author_id)
      );
      $response->set_data( $data );
      return $response;
    }
}
