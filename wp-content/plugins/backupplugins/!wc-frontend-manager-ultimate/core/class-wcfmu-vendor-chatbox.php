<?php
/**
 * WCFM plugin core
 *
 * Chatbox board core
 *
 * @author 		WC Lovers
 * @package 	wcfmu/core
 * @version   5.1.5
 */
 
class WCFMu_Vendor_Chatbox {
	
	public $wcfm_myaccount_view_chatbox_endpoint = 'chatbox';
	
	/**
	 * API endpoint url
	 *
	 * @var string
	 */
	protected $api_endpoint;

	/**
	 * Hold the app_id
	 *
	 * @var string
	 */
	public $app_id;

	/**
	 * Hold the app_secret
	 *
	 * @var string
	 */
	public $app_secret;
		

	public function __construct() {
		global $WCFM, $WCFMu, $WCFMmp;
		
		if ( !function_exists( 'wcfm_is_store_page' ) ) return;
		
		$wcfm_chatbox_setting = get_option( 'wcfm_chatbox_setting', array() );
		$this->app_id       = !empty( $wcfm_chatbox_setting['app_id'] ) ? $wcfm_chatbox_setting['app_id'] : '';
		$this->app_secret   = !empty( $wcfm_chatbox_setting['secret'] ) ? $wcfm_chatbox_setting['secret'] : '';
		$this->api_endpoint = 'https://api.talkjs.com/';
		
		$wcfm_myac_modified_endpoints = wcfm_get_option( 'wcfm_myac_endpoints', array() );
		$this->wcfm_myaccount_chatbox_endpoint = ! empty( $wcfm_myac_modified_endpoints['chatbox'] ) ? $wcfm_myac_modified_endpoints['chatbox'] : 'chatbox';
		
		add_filter( 'wcfm_query_vars', array( &$this, 'wcfm_chatbox_query_vars' ), 20 );
		add_filter( 'wcfm_endpoint_title', array( &$this, 'wcfm_chatbox_endpoint_title' ), 20, 2 );
		add_action( 'init', array( &$this, 'wcfm_chatbox_init' ), 20 );
		
		// Chatbox Endpoint Edit
		add_filter( 'wcfm_endpoints_slug', array( $this, 'chatbox_wcfm_endpoints_slug' ) );
		
		if( $this->app_id && $this->app_secret ) {
		
			// Chatbox menu on WCfM dashboard
			if( apply_filters( 'wcfm_is_allow_chatbox', true ) ) {
				add_filter( 'wcfm_menus', array( &$this, 'wcfm_chatbox_menus' ), 30 );
			}
		
			// Chat Now Shortcode
			add_shortcode( 'wcfm_chat_now', array(&$this, 'wcfm_chatnow_shortcode') );
			
			// Single Product page chat now button
			if( apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfm_is_pref_enquiry_button', true ) ) {
				add_action( 'wcfm_after_product_catalog_enquiry_button',	array( &$this, 'wcfm_chatbox_button' ), 35 );
			} else {
				add_action( 'woocommerce_single_product_summary',	array( &$this, 'wcfm_chatbox_button' ), 35 );
			}
			
			// WCFM Marketplace Store chat noe button
			add_action( 'wcfmmp_store_before_enquiry',	array( &$this, 'wcfm_store_chatbox_button' ), 50 );
		
			// Chatbox Load views
			add_action( 'wcfm_load_views', array( &$this, 'load_views' ), 30 );
		
			// My Account Chatbox End Point
			add_action( 'init', array( &$this, 'wcfm_chatbox_my_account_endpoints' ) );
			
			// My Account Chatbox Query Vars
			add_filter( 'query_vars', array( &$this, 'wcfm_chatbox_my_account_query_vars' ), 0 );
			
			// My Account Chatbox Rule Flush
			register_activation_hook( $WCFMu->file, array( &$this,'wcfm_chatbox_my_account_flush_rewrite_rules' ) );
			register_deactivation_hook( $WCFMu->file, array( &$this, 'wcfm_chatbox_my_account_flush_rewrite_rules' ) );
			
			// My Account Chatbox Menu
			add_filter( 'woocommerce_account_menu_items', array( &$this, 'wcfm_chatbox_my_account_menu_items' ), 195 );
			
			// My Account Chatbox End Point Title
			add_filter( 'the_title', array( &$this, 'wcfm_chatbox_my_account_endpoint_title' ) );
			
			// My Account Chatbox End Point Content
			add_action( 'woocommerce_account_'.$this->wcfm_myaccount_chatbox_endpoint.'_endpoint', array( &$this, 'wcfm_chatbox_my_account_endpoint_content' ) );
			
			// Enqueue scripts
			add_action( 'wp_head', array(&$this, 'wcfm_chatbox_scripts'), 999 );
		}
		
		// Talk JS Setting
		add_action( 'end_wcfm_settings_form_menu_manager', array( &$this, 'wcfm_chatbox_setting' ), 12 );
		
		// Talk JS Setting Save
		add_action( 'wcfm_settings_update', array( &$this, 'wcfm_chatbox_setting_save' ), 50 );
	}
	
	/**
   * Chatbox Query Var
   */
  function wcfm_chatbox_query_vars( $query_vars ) {
  	$wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );
  	
		$query_chatbox_vars = array(
			'wcfm-chatbox'                 => ! empty( $wcfm_modified_endpoints['wcfm-chatbox'] ) ? $wcfm_modified_endpoints['wcfm-chatbox'] : 'chatbox',
		);
		
		$query_vars = array_merge( $query_vars, $query_chatbox_vars );
		
		return $query_vars;
  }
  
  /**
   * Chatbox End Point Title
   */
  function wcfm_chatbox_endpoint_title( $title, $endpoint ) {
  	global $wp;
  	switch ( $endpoint ) {
  		case 'wcfm-chatbox' :
				$title = __( 'Chat Box', 'wc-frontend-manager-ultimate' );
			break;
  	}
  	
  	return $title;
  }
  
  /**
   * Chatbox Endpoint Intialize
   */
  function wcfm_chatbox_init() {
  	global $WCFM_Query;
	
		// Intialize WCFM End points
		$WCFM_Query->init_query_vars();
		$WCFM_Query->add_endpoints();
		
		add_rewrite_endpoint( $this->wcfm_myaccount_chatbox_endpoint, EP_ROOT | EP_PAGES );
		
		if( !get_option( 'wcfm_updated_end_point_chatbox' ) ) {
			// Flush rules after endpoint update
			flush_rewrite_rules();
			update_option( 'wcfm_updated_end_point_chatbox', 1 );
		}
  }
  
  /**
	 * Chatbox Endpoiint Edit
	 */
	function chatbox_wcfm_endpoints_slug( $endpoints ) {
		
		$chatbox_endpoints = array(
													'wcfm-chatbox'          => 'chatbox',
													);
		
		$endpoints = array_merge( $endpoints, $chatbox_endpoints );
		
		return $endpoints;
	}
	
	/**
   * WCFM Chatbox Menu
   */
  function wcfm_chatbox_menus( $menus ) {
  	global $WCFM;
  		
  	if( wcfm_is_vendor() ) {
			$menus = array_slice($menus, 0, 3, true) +
													array( 'wcfm-chatbox' => array( 'label'  => __( 'Chatbox', 'wc-frontend-manager-ultimate' ),
																											 'url'        => wcfm_chatbox_url(),
																											 'icon'       => 'comments',
																											 'menu_for'   => 'vendor',
																											 'priority'   => 69.2
																											) )	 +
														array_slice($menus, 3, count($menus) - 3, true) ;
		}
  	return $menus;
  } 
  
  function wcfm_chatnow_shortcode( $attr ) {
   	 global $WCFM, $WCFMu;
   	 
   	 if( !is_product() && ( function_exists( 'wcfmmp_is_store_page' ) && !wcfmmp_is_store_page() ) ) return;
   	 
   	 ob_start();
   	 $this->wcfm_chatbox_button();
   	 return ob_get_clean();
   }
  
  /**
   * Chat now Button on Single Product Page
   *
   * @since 5.1.5
   */
	function wcfm_chatbox_button() {
		global $WCFM, $WCFMu, $product;
		
		if( wcfm_is_vendor() ) return;
		
		$store_id = '';
		if( is_product() && $product ) {
			$product_id = $product->get_id();
			$store_id   = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $product_id );
		} elseif (  wcfm_is_store_page() ) {
			$wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			$store_id  = 0;
			if ( !empty( $store_name ) ) {
				$store_user = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store_user->ID;
		}
		
		if( !$store_id ) return;
		if( !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_id, 'chatbox' ) ) return; 
			
		$button_style = '';
		$wcfm_chatbox_setting = get_option( 'wcfm_chatbox_setting', array() );
		
		$background = !empty( $wcfm_chatbox_setting['background'] ) ? $wcfm_chatbox_setting['background'] : '#1C2B36';
		$hover      = !empty( $wcfm_chatbox_setting['hover'] ) ? $wcfm_chatbox_setting['hover'] : '#00798b';
		$text       = !empty( $wcfm_chatbox_setting['text'] ) ? $wcfm_chatbox_setting['text'] : '#b0bec5';
		$text_hover = !empty( $wcfm_chatbox_setting['text_hover'] ) ? $wcfm_chatbox_setting['text_hover'] : '#b0bec5';
		
		
		$button_style .= 'position:relative;padding:5px 10px;background: ' . $background . ';border-bottom-color: ' . $background . ';';
		$button_style .= 'color: ' . $text . ';';
		
		$wcfm_chatnow_label  = !empty( $wcfm_chatbox_setting['label'] ) ? $wcfm_chatbox_setting['label'] : __( 'Chat Now', 'wc-frontend-manager-ultimate' );
		
		$button_class = '';
		if( !is_user_logged_in() ) { $button_class = ' wcfm_login_popup'; }
		
		?>
		<?php if( !apply_filters( 'wcfm_is_pref_enquiry', true ) || !apply_filters( 'wcfm_is_pref_enquiry_button', true ) || !apply_filters( 'wcfm_is_allow_product_enquiry_bubtton', true ) ) { ?>
		  <div class="wcfm_ele_wrapper wcfm_chat_now_button_wrapper">
			<div class="wcfm-clearfix"></div>
		  <?php } ?>
		  
				<a href="#" onclick="return false;" class="wcfm-chat-now wcfm_chat_now_button <?php echo $button_class; ?>" style="<?php echo $button_style; ?>"><i class="wcfmfa fa-comments"></i>&nbsp;&nbsp;<span class="chat_now_label"><?php _e( $wcfm_chatnow_label, 'wc-frontend-manager' ); ?></span></a>
				<style>a.wcfm-chat-now:hover{background: <?php echo $hover; ?> !important;border-bottom-color: <?php echo $hover; ?> !important;color: <?php echo $text_hover; ?> !important;}</style>
		
		  <?php if( !apply_filters( 'wcfm_is_pref_enquiry', true ) || !apply_filters( 'wcfm_is_pref_enquiry_button', true ) || !apply_filters( 'wcfm_is_allow_product_enquiry_bubtton', true ) ) { ?>
			<div class="wcfm-clearfix"></div>
			</div>
		<?php } ?>
		<?php
		
		if ( !$this->wcfm_is_store_vendor_online( $store_id ) ) {
			return;
		}

		$this->make_store_vendor_online();
	}
	
	/**
	 * Chat Now Button at Store Page
	 */
	function wcfm_store_chatbox_button( $store_id ) {
		global $WCFM, $WCFMu;
		
		if( wcfm_is_vendor() ) return;
		
		if( !$WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_id, 'chatbox' ) ) return;
			
		$button_style = '';
		$wcfm_chatbox_setting = get_option( 'wcfm_chatbox_setting', array() );
		$wcfm_chatnow_label  = !empty( $wcfm_chatbox_setting['label'] ) ? $wcfm_chatbox_setting['label'] : __( 'Chat Now', 'wc-frontend-manager-ultimate' );
		
		$button_class = '';
		if( !is_user_logged_in() ) { $button_class = ' wcfm_login_popup'; }
		?>
		<div class="lft bd_icon_box"><a onclick="return false;" class="wcfm_store_chatnow wcfm-chat-now <?php echo $button_class; ?>" href="#"><i class="wcfmfa fa-comments" aria-hidden="true"></i><span><?php _e( $wcfm_chatnow_label, 'wc-frontend-manager' ); ?></span></a></div>
		<?php
		
		if ( !$this->wcfm_is_store_vendor_online( $store_id ) ) {
			return;
		}

		$this->make_store_vendor_online();
	}
	
	public function wcfm_is_store_vendor_online( $store_id ) {
		if ( empty( $store_id ) ) {
			return false;
		}
		
		if ( get_transient( 'wcfm_is_store_vendor_online_'.$store_id ) == 'maybe' ) {
			return false;
		}

		if ( get_transient( 'wcfm_is_store_vendor_online_'.$store_id ) == 'yes' ) {
			return true;
		}

		$url = $this->api_endpoint . 'v1/' . $this->app_id . '/users/' . $store_id . '/sessions' ;

		$response = wp_remote_get( $url, array(
				'sslverify' => false,
				'headers' => array(
						'Authorization' => 'Bearer '. $this->app_secret
				)
		) );

		set_transient( 'wcfm_is_store_vendor_online_'.$store_id, 'maybe', 10 );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'chatbox-error', __( 'Something went wrong', 'wc-frontend-manager-ultimate' ) );
		}

		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $api_response ) || empty( $api_response ) ) {
			return false;
		}

		// currentConversationId exists means user is online
		if ( ! array_key_exists( 'currentConversationId', $api_response[0] ) ) {
			return false;
		}

		set_transient( 'wcfm_is_store_vendor_online_'.$store_id, 'yes', 15 );

		return true;
	}
	
	public function make_store_vendor_online() {
		?>
		<script type="text/javascript">
		
			var wcfm_chats = document.querySelectorAll( '.wcfm-chat-now' );
			
			wcfm_chats.forEach(function(wcfm_chat) {
				var span = document.createElement( 'label' );
		
				wcfm_chat.appendChild( span );
				wcfm_chat.style.paddingLeft = '23px';
			});
		
			var wcfm_chat_bts = document.querySelectorAll( '.wcfm-chat-now label' );
			
			wcfm_chat_bts.forEach(function(wcfm_chat_bt) {
				wcfm_chat_bt.style.position = 'absolute';
				wcfm_chat_bt.style.top = '9px';
				wcfm_chat_bt.style.left = '7px';
				wcfm_chat_bt.style.width = '9px';
				wcfm_chat_bt.style.height = '9px';
				wcfm_chat_bt.style.borderRadius = '50%';
				wcfm_chat_bt.style.background = '#79e379';
				wcfm_chat_bt.style.zIndex = '999';
			});
		</script>
		<?php
	}
	
	/**
   * Chatbox Views
   */
  public function load_views( $end_point ) {
	  global $WCFM, $WCFMu;
	  
	  switch( $end_point ) {
	  	case 'wcfm-chatbox':
        $WCFMu->template->get_template( 'chatbox/wcfmu-view-chatbox.php' );
      break;
	  }
	}
	
  function wcfm_chatbox_my_account_endpoints() {
		add_rewrite_endpoint( $this->wcfm_myaccount_chatbox_endpoint, EP_ROOT | EP_PAGES );
	}
	
	function wcfm_chatbox_my_account_query_vars( $vars ) {
		$vars[] = $this->wcfm_myaccount_chatbox_endpoint;
	
		return $vars;
	}
	
	function wcfm_chatbox_my_account_flush_rewrite_rules() {
		add_rewrite_endpoint( $this->wcfm_myaccount_chatbox_endpoint, EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}
	
	function wcfm_chatbox_my_account_menu_items( $items ) {
		
		if( !wcfm_is_vendor() ) {
			$items = array_slice($items, 0, count($items) - 3, true) +
																		array(
																					$this->wcfm_myaccount_chatbox_endpoint => __( 'Chat Box', 'wc-frontend-manager-ultimate' )
																					) +
																		array_slice($items, count($items) - 3, count($items) - 1, true) ;
		}
		
		return $items;
	}
	
	function wcfm_chatbox_my_account_endpoint_title( $title ) {
		global $wp_query;
	
		$is_endpoint = isset( $wp_query->query_vars[$this->wcfm_myaccount_chatbox_endpoint] );
	
		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Chat Box', 'wc-frontend-manager-ultimate' );
			remove_filter( 'the_title', array( $this, 'wcfm_chatbox_my_account_endpoint_title' ) );
		}
		
		return $title;
	}
	
	function wcfm_chatbox_my_account_endpoint_content() {
		global $WCFM, $WCFMu, $wpdb;
		$WCFMu->template->get_template( 'chatbox/wcfmu-view-my-account-chatbox.php' );
	}
	
	public function get_unread_message_count() {
		?>
		<script type="text/javascript">
		Talk.ready.then( function() {
			var unreadMessage = document.createElement( 'span' );

			window.talkSession.unreads.on( 'change', function ( conversationId ) {
				var unreadCount = conversationId.length;

				if ( unreadCount > 0) {
					var inboxMenu = document.querySelector( '#wcfm_menu .wcfm_menu_wcfm-chatbox a span.text' );

					inboxMenu.appendChild( unreadMessage );
					unreadMessage.innerText = unreadCount;

					var inboxCount = document.querySelector( '#wcfm_menu .wcfm_menu_wcfm-chatbox a span.text span' );

					inboxCount.style.position       = 'absolute';
					inboxCount.style.top            = '14px';
					inboxCount.style.right          = '23px';
					inboxCount.style.color          = 'white';
					inboxCount.style.fontSize       = '12px';
					inboxCount.style.background     = 'rgb(242, 5, 37) none repeat scroll 0% 0%';
					inboxCount.style.borderRadius   = '50%';
					inboxCount.style.width          = '18px';
					inboxCount.style.height         = '18px';
					inboxCount.style.textAlign      = 'center';
					inboxCount.style.lineHeight     = '17px';
					inboxCount.style.fontWeight     = 'bold';
				}
			} );
		} );
		</script>
		<?php
	}
	
	/**
	 * WCFM Store Chat JS
	 */
	public function load_store_chatjs( $store ) {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = str_replace( '_', '-', $locale );
		$locale = apply_filters( 'wcfm_is_allow_chat_locale', $locale );
		?>
		<script type="text/javascript">
		Talk.ready.then( function() {
			var customer = new Talk.User({
				id       : "<?php echo $store->ID ?>",
				name     : "<?php echo $store->display_name ?>",
				email    : "<?php echo $store->user_email ?>",
				photoUrl : "<?php echo esc_url( get_avatar_url( $store->ID ) ) ?>",
				locale   : "<?php echo $locale; ?>"
			});

			window.talkSession = new Talk.Session( {
				appId: "<?php echo esc_attr( $this->app_id ); ?>",
				me: customer
			} );

			var inbox = window.talkSession.createInbox();

			/*window.talkSession.unreads.on('change', function (conversation) {
					var unreadCount = conversation.length;
					
					//console.log(unreadCount);

					if (unreadCount > 0) {
						var popup = talkSession.createPopup();
					}

					if (popup != '') {
						if (unreadCount > 0) {
							popup.mount();
						}
					}
				});*/

		} );
		</script>
		<?php

		//$this->get_unread_message_count();
  }
  
  /**
   * WCFM Custmer Chat JS
   */
  public function load_customer_chatjs( $customer ) {
  	global $WCFM, $WCFMu;
  	
  	$store_id  = 0;
  	$store_user = '';
		if ( wcfm_is_store_page() ) {
			$wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
			$store_name = get_query_var( $wcfm_store_url );
			if ( !empty( $store_name ) ) {
				$store = get_user_by( 'slug', $store_name );
			}
			$store_id   		= $store->ID;
		} elseif( is_product() ) {
			$store_id = get_post_field( 'post_author', get_the_ID() );
		}
		
		$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
		if( $store_id ) {
			$store_user = wcfmmp_get_store( $store_id );
			$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $store_id );
			if( !$store_logo ) {
				$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
			}
		}
		
		//if( !$store_user || !$store_id ) return;
		
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = str_replace( '_', '-', $locale );
		$locale = apply_filters( 'wcfm_is_allow_chat_locale', $locale );

		?>
		<script type="text/javascript">
		Talk.ready.then( function() {
			var customer = new Talk.User( {
					id             : "<?php echo $customer->ID ?>",
					name           : "<?php echo $customer->display_name ?>",
					email          : "<?php echo ! empty( $customer->user_email ) ? $customer->user_email : 'fake@email.com'; ?>",
					configuration  : "vendor",
					photoUrl       : "<?php echo esc_url( get_avatar_url( $customer->ID ) ) ?>",
					locale         : "<?php echo $locale; ?>"
			} );

			window.talkSession = new Talk.Session( {
					appId: "<?php echo esc_attr( $this->app_id ); ?>",
					me: customer
			} );
			
			<?php if (  wcfm_is_store_page() || is_product() ) { ?>
				<?php if( $store_user && $store_id && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_id, 'chatbox' ) ) { ?>

					var seller = new Talk.User( {
							id              : "<?php echo $store_user->get_id(); ?>",
							name            : "<?php echo ! empty( $store_user->get_shop_name() ) ? $store_user->get_shop_name() : 'fakename'; ?>",
							email           : "<?php echo ! empty( $store_user->get_email() ) ? $store_user->get_email() : 'fake@email.com'; ?>",
							configuration   : "vendor",
							photoUrl        : "<?php echo esc_url( $store_logo ) ?>",
							welcomeMessage  : "<?php _e( 'How may I help you?', 'wc-frontend-manager-ultimate' ) ?>",
							locale          : "<?php echo $locale; ?>"
					} );
		
					/*window.talkSession.unreads.on( 'change', function ( conversation ) {
						var unreadCount = conversation.length;
						console.log(unreadCount);
		
						if ( unreadCount > 0 ) {
							var popup = talkSession.createPopup();
						}
		
						if ( popup != '' ) {
							if ( unreadCount > 0 ) {
								popup.mount();
							}
						}
		
					} );*/
		
					setTimeout(function() {
						var chat_btns = document.querySelectorAll( '.wcfm-chat-now' );
			
						chat_btns.forEach(function(chat_btn) {
							if ( chat_btn !== null ) {
								chat_btn.addEventListener( 'click', function( e ) {
									e.preventDefault();
									
									var conversation = talkSession.getOrCreateConversation(Talk.oneOnOneId(customer, seller));
									conversation.setParticipant(customer);
									conversation.setParticipant(seller);
									var inbox = talkSession.createInbox({selected: conversation});
									var popup = talkSession.createPopup(conversation);
									popup.mount();
								});
							}
						});
					}, 2000 );
				<?php } ?>
			<?php } ?>
		} );
		</script>
		<?php
  }
	
	/**
	 * WCFM Chatbox JS
	 */
	function wcfm_chatbox_scripts() {
 		global $WCFM, $WCFMu, $wp, $WCFM_Query;
 		
 		if( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			
			// Load Talk JS Lib
			$WCFMu->library->load_talkjs_lib();
			
			if( wcfm_is_vendor())  {
				if( apply_filters( 'wcfm_is_allow_chatbox', true ) ) {
					$this->load_store_chatjs( $current_user );
				}
			} else {
				$this->load_customer_chatjs( $current_user );
			}
			
			$this->chatbox_responsive();
		} else {
			$WCFM->library->load_wcfm_login_popup_lib();
		}
 	}
 	
 	function chatbox_responsive() {
		?>
		<style type="text/css">
			@media only screen and (max-width: 600px) {
				.__talkjs_popup {
					top: 100px !important;
					height: 80% !important;
				}
			}
		</style>
		<?php
   }
   
   /**
    * Chat Box Admin Setting
    */
   function wcfm_chatbox_setting( $wcfm_options ) {
		global $WCFM, $WCFMu;
		
		$wcfm_chatbox_setting = get_option( 'wcfm_chatbox_setting', array() );
		$app_id = !empty( $wcfm_chatbox_setting['app_id'] ) ? $wcfm_chatbox_setting['app_id'] : '';
		$secret = !empty( $wcfm_chatbox_setting['secret'] ) ? $wcfm_chatbox_setting['secret'] : '';
		$label  = !empty( $wcfm_chatbox_setting['label'] ) ? $wcfm_chatbox_setting['label'] : __( 'Chat Now', 'wc-frontend-manager-ultimate' );
		
		$background = !empty( $wcfm_chatbox_setting['background'] ) ? $wcfm_chatbox_setting['background'] : '#1C2B36';
		$hover      = !empty( $wcfm_chatbox_setting['hover'] ) ? $wcfm_chatbox_setting['hover'] : '#00798b';
		$text       = !empty( $wcfm_chatbox_setting['text'] ) ? $wcfm_chatbox_setting['text'] : '#b0bec5';
		$text_hover = !empty( $wcfm_chatbox_setting['text_hover'] ) ? $wcfm_chatbox_setting['text_hover'] : '#b0bec5';
		
		?>
		<!-- collapsible -->
		<div class="page_collapsible" id="wcfm_settings_form_chatbox_head">
			<label class="wcfmfa fa-comments"></label>
			<?php _e('Chat Box', 'wc-frontend-manager-ultimate'); ?><span></span>
		</div>
		<div class="wcfm-container">
			<div id="wcfm_settings_form_chatbox_expander" class="wcfm-content">
				<h2><?php _e('Live Chat Setting', 'wc-frontend-manager-ultimate'); ?></h2>
				<div class="wcfm_clearfix"></div>
				
				<?php
					$WCFM->wcfm_fields->wcfm_generate_form_field( apply_filters( 'wcfm_settings_fields_chatbox', array(
																																														"wcfm_chatbox_setting_app_id" => array('label' => __('App ID', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[app_id]','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $app_id, 'label_class' => 'wcfm_title', 'desc_class' => 'wcfm_page_options_desc', 'desc' => sprintf( __( 'Get your Talk JS %sAPP ID%s', 'wc-frontend-manager-ultimate' ), '<a target="_blank" href="https://talkjs.com/dashboard/signup/standard/">', '</a>' ) ),
																																														"wcfm_chatbox_setting_secret" => array('label' => __('Secret Key', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[secret]','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $secret, 'label_class' => 'wcfm_title', 'desc_class' => 'wcfm_page_options_desc', 'desc' => sprintf( __( 'Get your Talk JS %sSecret Key%s', 'wc-frontend-manager-ultimate' ), '<a target="_blank" href="https://talkjs.com/dashboard/signup/standard/">', '</a>' )  ),
																																														"wcfm_chatnow_button_label" => array('label' => __('Chat Now Button Label', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[label]','type' => 'text', 'class' => 'wcfm-text wcfm_ele', 'value' => $label, 'label_class' => 'wcfm_title' ),
																																														"wcfm_chatnow_button_background" => array('label' => __( 'Chat Now Button Background', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[background]','type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'value' => $background, 'label_class' => 'wcfm_title' ),
																																														"wcfm_chatnow_button_hover" => array('label' => __( 'Chat Now Button Hover', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[hover]','type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'value' => $hover, 'label_class' => 'wcfm_title' ),
																																														"wcfm_chatnow_button_text" => array('label' => __( 'Chat Now Button Text', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[text]','type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'value' => $text, 'label_class' => 'wcfm_title' ),
																																														"wcfm_chatnow_button_text_hover" => array('label' => __( 'Chat Now Button Text Hover', 'wc-frontend-manager-ultimate') , 'name' => 'wcfm_chatbox_setting[text_hover]','type' => 'colorpicker', 'class' => 'wcfm-text wcfm_ele colorpicker', 'value' => $text_hover, 'label_class' => 'wcfm_title' ),
																																														) ) );
				?>
			</div>
		</div>
		<div class="wcfm_clearfix"></div>
		<!-- end collapsible -->
		<?php
	}
	
	function wcfm_chatbox_setting_save( $wcfm_settings_form ) {
		global $WCFM, $WCFMu, $_POST;
		
		if( isset( $wcfm_settings_form['wcfm_chatbox_setting'] ) ) {
			update_option( 'wcfm_chatbox_setting', $wcfm_settings_form['wcfm_chatbox_setting'] );
		}
	}
}