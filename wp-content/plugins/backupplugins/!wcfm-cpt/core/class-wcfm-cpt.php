<?php

/**
 * WCFM CPT plugin core
 *
 * Plugin intiate
 *
 * @author 		WC Lovers
 * @package 	wcfm-cpt
 * @version   1.0.0
 */
 
class WCFM_CPT {
	
	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	
	public $wcfmc_cpt_capability;
	
	public $wcfm_cpt_1;
	public $wcfm_cpt_2;
	public $wcfm_cpt_3;
	
	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFMcpt_TOKEN;
		$this->text_domain = WCFMcpt_TEXT_DOMAIN;
		$this->version = WCFMcpt_VERSION;
		
		add_action( 'wcfm_init', array( &$this, 'init' ), 20 );
	}
	
	function init() {
		global $WCFM, $WCFMcpt;
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		if ( !is_admin() || defined('DOING_AJAX')) {	
			$this->load_class( 'capability' );
			$this->wcfmc_cpt_capability = new WCFM_CPT_Capability();
		}
		
		// Load CPT 1 Module
		if( apply_filters( 'wcfm_is_pref_cpt_1', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'cpt1' );
				$this->wcfm_cpt_1 = new WCFM_CPT1();
			}
		}
		
		// Load CPT 2 Module
		/*if( apply_filters( 'wcfm_is_pref_cpt_1', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'cpt2' );
				$this->wcfm_cpt_2 = new WCFM_CPT2();
			}
		}*/
		
		// Load CPT 3 Module
		/*if( apply_filters( 'wcfm_is_pref_cpt_3', true ) ) {
			if (!is_admin() || defined('DOING_AJAX')) {
				$this->load_class( 'cpt3' );
				$this->wcfm_cpt_3 = new WCFM_CPT3();
			}
		}*/
		
	}
	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wcfm-cpt' );
		
		//load_plugin_textdomain( 'wcfm-tuneer-orders' );
		//load_textdomain( 'wcfm-cpt', WP_LANG_DIR . "/wcfm-cpt/wcfm-cpt-$locale.mo");
		load_textdomain( 'wcfm-cpt', $this->plugin_path . "lang/wcfm-cpt-$locale.mo");
		load_textdomain( 'wcfm-cpt', ABSPATH . "wp-content/languages/plugins/wcfm-cpt-$locale.mo");
	}
	
	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}
}