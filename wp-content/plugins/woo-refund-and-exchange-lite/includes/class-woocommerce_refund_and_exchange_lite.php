<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    woocommerce_refund_and_exchange_lite
 * @subpackage woocommerce_refund_and_exchange_lite/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class woocommerce_refund_and_exchange_lite {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      woocommerce_refund_and_exchange_lite_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $woocommerce_refund_and_exchange_lite    The string used to uniquely identify this plugin.
	 */
	protected $woocommerce_refund_and_exchange_lite;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->woocommerce_refund_and_exchange_lite = 'woocommerce_refund_and_exchange_lite';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - woocommerce_refund_and_exchange_lite_Loader. Orchestrates the hooks of the plugin.
	 * - woocommerce_refund_and_exchange_lite_i18n. Defines internationalization functionality.
	 * - woocommerce_refund_and_exchange_lite_Admin. Defines all hooks for the admin area.
	 * - woocommerce_refund_and_exchange_lite_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce_refund_and_exchange_lite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce_refund_and_exchange_lite-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce_refund_and_exchange_lite-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce_refund_and_exchange_lite-public.php';

		$this->loader = new woocommerce_refund_and_exchange_lite_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the woocommerce_refund_and_exchange_lite_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new woocommerce_refund_and_exchange_lite_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new woocommerce_refund_and_exchange_lite_Admin( $this->get_woocommerce_refund_and_exchange_lite(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menus' );
		$this->loader->add_action( 'init', $plugin_admin, 'ced_rnx_register_custom_order_status' );
		$this->loader->add_filter( 'wc_order_statuses', $plugin_admin, 'ced_rnx_add_custom_order_status');
		$this->loader->add_action( 'wp_ajax_ced_return_req_approve', $plugin_admin, 'ced_rnx_return_req_approve_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_ced_return_req_approve', $plugin_admin, 'ced_rnx_return_req_approve_callback');
		$this->loader->add_action( 'wp_ajax_ced_return_req_cancel',$plugin_admin, 'ced_rnx_return_req_cancel_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_ced_return_req_cancel', $plugin_admin, 'ced_rnx_return_req_cancel_callback');
		$this->loader->add_action('wp_ajax_ced_rnx_manage_stock' , $plugin_admin , 'ced_rnx_manage_stock' );
		$this->loader->add_action('wp_ajax_nopriv_ced_rnx_manage_stock' , $plugin_admin , 'ced_rnx_manage_stock' );
		$this->loader->add_action( 'woocommerce_refund_created', $plugin_admin, 'ced_rnx_action_woocommerce_order_refunded', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new woocommerce_refund_and_exchange_lite_Public( $this->get_woocommerce_refund_and_exchange_lite(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'template_include',  $plugin_public, 'ced_rnx_product_return_template');
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions',$plugin_public, 'ced_rnx_refund_exchange_button',10, 2 );
		$this->loader->add_action( 'wp_ajax_ced_rnx_return_upload_files',$plugin_public, 'ced_rnx_order_return_attach_files');
		$this->loader->add_action( 'wp_ajax_nopriv_ced_rnx_return_upload_files',$plugin_public, 'ced_rnx_order_return_attach_files');
		$this->loader->add_action( 'wp_ajax_ced_rnx_return_product_info', $plugin_public, 'ced_rnx_return_product_info_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_ced_rnx_return_product_info',$plugin_public, 'ced_rnx_return_product_info_callback');
		$this->loader->add_action( 'woocommerce_order_details_after_order_table',$plugin_public, 'ced_rnx_order_return_button');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_woocommerce_refund_and_exchange_lite() {
		return $this->woocommerce_refund_and_exchange_lite;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    woocommerce_refund_and_exchange_lite_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
