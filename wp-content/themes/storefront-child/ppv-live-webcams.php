<?php
/*
Plugin Name: Paid Videochat Turnkey Site - HTML5 PPV Live Webcams
Plugin URI: https://paidvideochat.com
Description: <strong>Paid Videochat Turnkey Site - HTML5 PPV Live Webcams</strong> solution can be used to build pay per minute (PPM) videochat sites.  Includes custom registration (performers, clients, studios), live webcams list, public lobby for each performer with live stream, mult-performer in show checkin, custom tips with notification, pay per minute private 2 way calls and group videochat shows, HTML5 WebRTC live video streaming, adaptive interface for mobile browsers, snapshot and video archiving, video conference mode with split view streaming, presentation/collaboration mode with file sharing and image/video display.  <a href='https://videowhisper.com/tickets_submit.php?topic=PPV-Live-Webcams'>Contact Us</a>
Version: 4.9.7
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com, PaidVideoChat.com
Text Domain: ppv-live-webcams
Domain Path: /languages/
*/


defined( 'ABSPATH' ) or exit;

require_once(plugin_dir_path( __FILE__ ) .'/inc/options.php');
require_once(plugin_dir_path( __FILE__ ) .'/inc/requirements.php');
require_once(plugin_dir_path( __FILE__ ) .'/inc/h5videochat.php');
require_once(plugin_dir_path( __FILE__ ) .'/inc/shortcodes.php');


use VideoWhisper\LiveWebcams;

if (!class_exists("VWliveWebcams"))
{
	class VWliveWebcams {

		use VideoWhisper\LiveWebcams\Options;
		use VideoWhisper\LiveWebcams\Requirements;
		use VideoWhisper\LiveWebcams\H5Videochat;
		use VideoWhisper\LiveWebcams\Shortcodes;

		public function __construct()
		{
		}


		public function VWliveWebcams() { //constructor
			self::__construct();

		}


		//! Plugin Hooks

		function init()
		{
			//setup post
			VWliveWebcams::webcam_post();

			//prevent wp from adding <p> that breaks JS
			remove_filter ('the_content',  'wpautop');

			//move wpautop filter to BEFORE shortcode is processed
			add_filter( 'the_content', 'wpautop' , 1);

			//then clean AFTER shortcode
			add_filter( 'the_content', 'shortcode_unautop', 100 );


			//cors
			//add_filter('allowed_http_origins', array('VWliveWebcams','allowed_http_origins') );

			$options = get_option('VWliveWebcamsOptions');

			if ($options['corsACLO'])
			{
				$http_origin = get_http_origin();

				$found = 0;
				$domains = explode(',', $options['corsACLO']);
				foreach ($domains as $domain) if ($http_origin == trim($domain)) $found = 1;

					if ($found)
					{
						header("Access-Control-Allow-Origin: " . $http_origin);
						header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, HEAD"); //POST, GET, OPTIONS, PUT, DELETE, HEAD
						header("Access-Control-Allow-Credentials: true");
						header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"); //Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With

						if ( 'OPTIONS' == $_SERVER['REQUEST_METHOD'] )  // CORS preflight request
							{
							status_header(200);
							exit();
						}

					}

			}

			//use a cookie for visitor username persistence
			if (!is_user_logged_in())
				if (!$_COOKIE['htmlchat_username'])
				{
					$userName =  'G_' . base_convert(time()%36 * rand(0,36*36),10,36);
					setcookie('htmlchat_username', $userName);
				}



		}


		/*
		function allowed_http_origins($origins)
		{

			$options = get_option('VWliveWebcamsOptions');

			if ($options['corsACLO'])
			{
				$domains = explode(',', $options['corsACLO']);
				foreach ($domains as $domain) $origins[] = trim($domain);
			}

			return $origins;

		}

*/
		function activation()
		{
			VWliveWebcams::webcam_post();
			flush_rewrite_rules();

			if (! wp_next_scheduled ( 'vwlw_hourly' ))
				wp_schedule_event(time(), 'hourly', 'vwlw_hourly');
		}


		function vwlw_hourly()
		{
			VWliveWebcams::processTimeout(); //clears stalling ffmpeg
		}


		function plugins_loaded()
		{
			$options = get_option('VWliveWebcamsOptions');

			//translations
			load_plugin_textdomain('ppv-live-webcams', false, dirname(plugin_basename(__FILE__)) .'/languages');

			//settings link in plugins view
			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWliveWebcams','settings_link') );

			//webcam post handling
			add_filter("the_content", array('VWliveWebcams','the_content'));
			add_filter('pre_get_posts', array('VWliveWebcams','pre_get_posts'));

			//admin webcam posts
			add_filter('manage_' . $options['custom_post'] . '_posts_columns', array( 'VWliveWebcams', 'columns_head_webcam') , 10);
			add_filter( 'manage_edit-' . $options['custom_post'] . '_sortable_columns', array('VWliveWebcams', 'columns_register_sortable') );
			add_action('manage_' . $options['custom_post'] . '_posts_custom_column', array( 'VWliveWebcams', 'columns_content_webcam') , 10, 2);
			add_filter( 'request', array('VWliveWebcams', 'duration_column_orderby') );

			add_action( 'quick_edit_custom_box', array( 'VWliveWebcams', 'quick_edit_custom_box'), 10, 2 );
			add_action( 'save_post', array( 'VWliveWebcams', 'save_post') );

			//custom content template
			add_action('add_meta_boxes', array($this,'add_meta_boxes') );

			//crons
			add_action('vwlw_hourly', array( 'VWliveWebcams', 'vwlw_hourly'));

			//notify admin about requirements
			if( current_user_can( 'administrator' ) ) self::requirements_plugins_loaded();

			//admin users
			add_filter('manage_users_columns', array( 'VWliveWebcams', 'manage_users_columns'));
			add_action('manage_users_custom_column',  array( 'VWliveWebcams', 'manage_users_custom_column'), 10, 3);
			add_filter( 'manage_users_sortable_columns', array( 'VWliveWebcams', 'manage_users_sortable_columns') );
			add_action('pre_user_query', array( 'VWliveWebcams', 'pre_user_query'));

			//!shortcode definitions


			//h5v
			add_shortcode('videowhisper_cam_calls', array( $this, 'videowhisper_cam_calls'));

			add_shortcode('videowhisper_cam_instant', array( $this, 'videowhisper_cam_instant'));
			add_shortcode('videowhisper_cam_random', array( 'VWliveWebcams', 'videowhisper_cam_random'));
			
			add_shortcode('videowhisper_cam_app', array( 'VWliveWebcams', 'videowhisper_cam_app'));

			//
			add_shortcode('videowhisper_webcams', array( 'VWliveWebcams', 'videowhisper_webcams'));
			add_shortcode('videowhisper_webcams_performer', array( 'VWliveWebcams', 'videowhisper_webcams_performer'));
			add_shortcode('videowhisper_webcams_studio', array( 'VWliveWebcams', 'videowhisper_webcams_studio'));

			add_shortcode('videowhisper_account_records', array( 'VWliveWebcams', 'videowhisper_account_records'));

			add_shortcode('videowhisper_webcams_logout', array( 'VWliveWebcams', 'videowhisper_webcams_logout'));

			add_shortcode('videowhisper_follow', array( 'VWliveWebcams', 'videowhisper_follow'));
			add_shortcode('videowhisper_follow_list', array( 'VWliveWebcams', 'videowhisper_follow_list'));

			add_shortcode('videowhisper_videochat', array( 'VWliveWebcams', 'videowhisper_videochat'));


			add_shortcode('videowhisper_camvideo', array( 'VWliveWebcams', 'videowhisper_camvideo'));
			add_shortcode('videowhisper_campreview', array( 'VWliveWebcams', 'videowhisper_campreview'));
			add_shortcode('videowhisper_caminfo', array( 'VWliveWebcams', 'videowhisper_caminfo'));

			add_shortcode('videowhisper_camprofile', array( 'VWliveWebcams', 'videowhisper_camprofile'));



			//html5
			add_shortcode('videowhisper_camhls', array( 'VWliveWebcams', 'videowhisper_camhls'));
			add_shortcode('videowhisper_cammpeg', array( 'VWliveWebcams', 'videowhisper_cammpeg'));

			add_shortcode('videowhisper_htmlchat', array( 'VWliveWebcams', 'videowhisper_htmlchat'));

			add_shortcode('videowhisper_cam_webrtc_broadcast', array( 'VWliveWebcams', 'videowhisper_cam_webrtc_broadcast'));
			add_shortcode('videowhisper_cam_webrtc_playback', array( 'VWliveWebcams', 'videowhisper_cam_webrtc_playback')); //only video


			//buddypress: disable redirect BP registration (without roles)
			if ($options['registrationFormRole'])
			{
				remove_action( 'bp_init', 'bp_core_wpsignup_redirect');
				remove_filter( 'register_url', 'bp_get_signup_page' );
				add_filter( 'bp_get_signup_slug', array( 'VWliveWebcams', 'bp_get_signup_slug') );
			}

			//web app ajax calls
			add_action( 'wp_ajax_vmls_app', array('VWliveWebcams','vmls_app') );
			add_action( 'wp_ajax_nopriv_vmls_app', array('VWliveWebcams','vmls_app') );


			add_action( 'wp_ajax_vmls', array('VWliveWebcams','vmls_callback') );
			add_action( 'wp_ajax_nopriv_vmls', array('VWliveWebcams','vmls_callback') );

			add_action( 'wp_ajax_vmls_cams', array('VWliveWebcams','vmls_cams_callback') );
			add_action( 'wp_ajax_nopriv_vmls_cams', array('VWliveWebcams','vmls_cams_callback') );

			add_action( 'wp_ajax_vmls_htmlchat', array('VWliveWebcams','vmls_htmlchat_callback') );
			add_action( 'wp_ajax_nopriv_vmls_htmlchat', array('VWliveWebcams','vmls_htmlchat_callback') );

			add_action( 'wp_ajax_vmls_playlist', array($this,'vmls_playlist') );
			add_action( 'wp_ajax_nopriv_vmls_playlist', array($this,'vmls_playlist'));

			//sql fast session processing tables
			//check db
			$vmls_db_version = "4.9.3";


			$installed_ver = get_option( "vmls_db_version" );

			if( $installed_ver != $vmls_db_version )
			{
				global $wpdb;

				$table_sessions = $wpdb->prefix . "vw_vmls_sessions";
				$table_private = $wpdb->prefix . "vw_vmls_private";
				$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";
				$table_follow = $wpdb->prefix . "vw_vmls_follow";
				$table_actions = $wpdb->prefix . "vw_vmls_actions";

				$wpdb->flush();

				$sql = "DROP TABLE IF EXISTS `$table_sessions`;
		CREATE TABLE `$table_sessions` (
		  `id` int(11) NOT NULL auto_increment,
		  `session` varchar(64) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  `uid` int(11) NOT NULL,
		  `broadcaster` tinyint(4) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `rid` int(11) NOT NULL,
		  `rsdate` int(11) NOT NULL,
		  `redate` int(11) NOT NULL,
		  `roptions` text NOT NULL,
		  `meta` text NOT NULL,
		  `rmode` tinyint(4) NOT NULL,
		  `message` text NOT NULL,
		  `ip` text NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `broadcaster` (`broadcaster`),
		  KEY `type` (`type`),
		  KEY `rid` (`rid`),
		  KEY `uid` (`uid`),
		  KEY `rmode` (`rmode`),
		  KEY `rsdate` (`rsdate`),
		  KEY `redate` (`redate`),
		  KEY `sdate` (`sdate`),
		  KEY `edate` (`edate`),
		  KEY `room` (`room`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Sessions 2015-2019@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_private`;
		CREATE TABLE `$table_private` (
		  `id` int(11) NOT NULL auto_increment,
  		  `room` varchar(64) NOT NULL,
		  `performer` varchar(64) NOT NULL,
		  `client` varchar(64) NOT NULL,
		  `pid` int(11) NOT NULL,
		  `cid` int(11) NOT NULL,
		  `rid` int(11) NOT NULL,
		  `psdate` int(11) NOT NULL,
		  `pedate` int(11) NOT NULL,
		  `csdate` int(11) NOT NULL,
		  `cedate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `call` tinyint(4) NOT NULL,
		  `meta` text NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `room` (`room`),
		  KEY `performer` (`performer`),
		  KEY `client` (`client`),
		  KEY `rid` (`rid`),
		  KEY `pid` (`pid`),
		  KEY `psdate` (`psdate`),
		  KEY `pedate` (`pedate`),
		  KEY `csdate` (`csdate`),
		  KEY `cedate` (`cedate`),
		  KEY `call` (`call`),		  
		  KEY `status` (`status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Private Sessions 2015-2019@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_chatlog`;
		CREATE TABLE `$table_chatlog` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `username` varchar(64) NOT NULL,
		  `user_id` int(11) unsigned NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `room_id` int(11) unsigned NOT NULL,
		  `message` text NOT NULL,
		  `mdate` int(11) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `private_uid` int(11) unsigned NOT NULL,
		  `meta` TEXT,
		  PRIMARY KEY  (`id`),
		  KEY `room` (`room`),
		  KEY `mdate` (`mdate`),
		  KEY `type` (`type`),
		  KEY `private_uid` (`private_uid`),
		  KEY `user_id` (`user_id`),
		  KEY `room_id` (`room_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Chat Logs 2018-2019@videowhisper.com' AUTO_INCREMENT=1;

		DROP TABLE IF EXISTS `$table_actions`;
		CREATE TABLE `$table_actions` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `user_id` int(11) unsigned NOT NULL,
		  `room_id` int(11) unsigned NOT NULL,
		  `target_id` int(11) unsigned NOT NULL,
		  `action` varchar(64) NOT NULL,
		  `mdate` int(11) NOT NULL,
		  `meta` TEXT,
		  `status` tinyint(4) NOT NULL,
		  `answer` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `mdate` (`mdate`),
		  KEY `user_id` (`user_id`),
		  KEY `room_id` (`room_id`),
		  KEY `target_id` (`target_id`),
		  KEY `action` (`action`),
		  KEY `status` (`status`),
		  KEY `answer` (`status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Actions 2019@videowhisper.com' AUTO_INCREMENT=1;

		";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

				if (!$installed_ver) add_option("vmls_db_version", $vmls_db_version);
				else update_option( "vmls_db_version", $vmls_db_version );

				$wpdb->flush();

			}


		}


		//! Backend: Full Page App Template
		function add_meta_boxes()
		{

			$options = self::getOptions();

			$postTypes = explode(',', $options['templateTypes']);

			$showFor = array();
			foreach ($postTypes as $postType)
				if (post_type_exists( trim($postType) )) $showFor[] = trim($postType);

				if (count($showFor))
				{
					add_meta_box(
						'videowhisper_live_webcams',           // Unique ID
						'Videochat Template for PPV Live Webcams',  // Box title
						array($this,'meta_box_html'),  // Content callback, must be of type callable
						$showFor,                   // Post types
						'side' //Context
					);
				}
		}


		function meta_box_html($post)
		{
			$postTemplate = get_post_meta( $post->ID, 'videowhisper_template', true );
?>

<h4>Videochat Template</h4>
<select name="videowhisper_template" id="videowhisper_template">
  <option value="" <?php echo !$postTemplate?"selected":""?>>Default</option>
  <option value="+app" <?php echo $postTemplate=='+app'?"selected":""?>>Full Page (App)</option>
  <option value="+plugin" <?php echo $postTemplate=='+plugin'?"selected":""?>>Minimal with Theme Header & Footer</option>
</select> <?php echo $postTemplate ?>
	<br>Use content Update button to save changes. Full page app template is recommended for <A href="admin.php?page=live-webcams&tab=app">HTML5 Videochat App</a>.
		<?php
		}


		//!backend listings
		function manage_users_columns($columns) {
			$columns['vwUpdated'] = 'Cam Records';
			return $columns;
		}


		function manage_users_sortable_columns( $columns ) {
			$columns['vwUpdated'] = 'vwUpdated';
			return $columns;
		}


		static function pre_user_query($user_search)
		{
			global $wpdb, $current_screen;

			if (!$current_screen) return;
			if ( 'users' != $current_screen->id )
				return;

			$vars = $user_search->query_vars;

			if('vwUpdated' == $vars['orderby'])
			{
				$user_search->query_from .= " LEFT JOIN {$wpdb->usermeta} m1 ON {$wpdb->users}.ID=m1.user_id AND (m1.meta_key='vwUpdated')";
				$user_search->query_orderby = ' ORDER BY UPPER(m1.meta_value) '. $vars['order'];
			}

		}


		function manage_users_custom_column($value, $column_name, $user_id)
		{
			if ($column_name == 'vwUpdated')
			{
				$verified = get_user_meta( $user_id, 'vwVerified', true);
				$vwUpdated = get_user_meta( $user_id, 'vwUpdated', true);

				$studioID = get_user_meta($user_id, 'studioID', true);


				$htmlCode = $verified?'Verified':'Not Verified';
				$htmlCode .= '<small style="display:block;">Updated: ' .($vwUpdated?date("F j, Y, g:i a", $vwUpdated):'Never'). '</small>';
				if ($studioID) $htmlCode .= '<small style="display:block;">Studio ID: '. $studioID . '</small>';

				$htmlCode .= '<div class="row-actions"><span><a href="admin.php?page=live-webcams-records&user_id=' . $user_id . '">Review Records</a></span></div>';
				$htmlCode .= '<div class="row-actions"><span><a href="admin.php?page=live-webcams-studio&user_id=' . $user_id . '">Assign to Studio</a></span></div>';
				return $htmlCode;
			} else return $value;
		}



		/*
	 //remove widgets
		function sidebars_widgets( $sidebars_widgets )
		{
			if (!is_single()) return $sidebars_widgets;

			$options = get_option('VWliveWebcamsOptions');

			$postID = get_the_ID();
			if (! get_post_type( $postID ) == $options['custom_post']) return $sidebars_widgets;

			// foreach ($sidebars_widgets as $key=>$value) unset($sidebars_widgets[$key]);
			$sidebars_widgets = array( false );

			return $sidebars_widgets;
		}

	 //remove sidebar
		function get_sidebar( $name )
		{
			if (!is_single())  return $name;

			$options = get_option('VWliveWebcamsOptions');

			$postID = get_the_ID();
			if (! get_post_type( $postID ) == $options['custom_post']) return $name;

			// Avoid recurrsion: remove itself
			remove_filter( current_filter(), __FUNCTION__ );
			return get_sidebar( 'webcams' );
		}
*/

		function bp_get_signup_slug($slug)
		{
			return 'wp-login.php?action=register';
		}


		//! Webcam Post Type

		function webcam_post() {

			$options = get_option('VWliveWebcamsOptions');

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			$labels = array(
				'name'                => _x( 'Webcams', 'Post Type General Name', 'live_webcams' ),
				'singular_name'       => _x( 'Webcam', 'Post Type Singular Name', 'live_webcams' ),
				'menu_name'           => __( 'Webcams', 'live_webcams' ),
				'parent_item_colon'   => __( 'Parent Webcam:', 'live_webcams' ),
				'all_items'           => __( 'All Webcams', 'live_webcams' ),
				'view_item'           => __( 'View Webcam', 'live_webcams' ),
				'add_new_item'        => __( 'Add New Webcam', 'live_webcams' ),
				'add_new'             => __( 'New Webcam', 'live_webcams' ),
				'edit_item'           => __( 'Edit Webcam', 'live_webcams' ),
				'update_item'         => __( 'Update Webcam', 'live_webcams' ),
				'search_items'        => __( 'Search Webcams', 'live_webcams' ),
				'not_found'           => __( 'No webcams found', 'live_webcams' ),
				'not_found_in_trash'  => __( 'No webcams found in Trash', 'live_webcams' ),
			);

			$args = array(
				'label'               => __( 'webcam', 'live_webcams' ),
				'description'         => __( 'Live Webcams', 'live_webcams' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'menu_icon' => 'dashicons-video-alt2',
				'capability_type'     => 'post',
				'capabilities' => array(
					'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
				),
				'map_meta_cap' => true, // Set to `false`, if users are not allowed to edit/delete existing posts
			);

			register_post_type( $options['custom_post'], $args );
		}


		static function the_title( $title, $id = null )
		{
			$options = get_option('VWliveWebcamsOptions');

			if (get_post_type($id) != $options['custom_post']) return $title;

			$label = get_post_meta($postID, 'vw_roomLabel', true);
			if ($label) return $label;

			//disable room name breakers
			$findthese = array(
				'#Protected:#',
				'#Private:#',
			);
			$replacewith = array(
				'', // What to replace "Protected:" with
				'' // What to replace "Private:" with
			);
			$title = preg_replace($findthese, $replacewith, $title);

			return $title;
		}


		function the_content($content)   //! webcam post page
			{

			$options = get_option('VWliveWebcamsOptions');


			//global geo blocking: applies to all content
			if ($options['geoBlocking'])
			{
				$clientLocation = self::detectLocation('all'); //array
				if ($clientLocation) if (!empty($clientLocation)) //only if geoip works
						{
						$banLocations = explode(',', $options['geoBlocking']);
						if (is_array($banLocations))
						{
							foreach ($banLocations as $key => $value) $banLocations[$key] = trim($value);


							$matches = array_intersect($clientLocation, $banLocations);

							if ($clientLocation) if (is_array($matches)) if (!empty($matches))
										return '<h3 class="ui header">' . __('Access Forbidden!', 'ppv-live-webcams') . '</h3>' . htmlspecialchars($options['geoBlockingMessage']) . ' (' . count($matches) . ')';
						}
					}
			}

			if (!is_single()) return $content; //other listings

			$postID = get_the_ID() ;
			if (get_post_type($postID) != $options['custom_post']) return $content; //othery post types
			$room = sanitize_file_name(get_the_title($postID));


			//password protected
			if (post_password_required($postID)) return $content;

			// webcam post geo blocking
			//! banCountries if not owner or admin
			$banLocations = get_post_meta( $postID, 'vw_banCountries', false );

			if ($banLocations) if (is_array($banLocations))
				{
					$current_user = wp_get_current_user();
					$post_author_id = get_post_field( 'post_author', $postID );

					if ($post_author_id != $current_user->ID && !in_array('administrator', $current_user->roles))
					{
						foreach ($banLocations as $key => $value) $banLocations[$key] = trim($value);

						$clientLocation = VWliveWebcams::detectLocation('all'); //array
						// $banLocations = str_getcsv($vw_banCountries, ',');

						$matches = array_intersect($clientLocation, $banLocations);

						if ($clientLocation) if (is_array($matches)) if (!empty($matches))
									return '<h3 class="ui header">This content is not available in your location!</h3>' . ' (' . count($matches) . ')';
					}
				}


			$view = sanitize_file_name( $_GET['view'] );

			if ( in_array($view, array('profile', 'content')) )
			{
				self::enqueueUI();


				if ($view == 'profile')  $addCode .= self::webcamProfile($postID, $options);
				if ($view == 'content')  $addCode .= self::webcamContent($postID, $room, $options, $content);

				$addCode .= self::webcamLinks($postID);

				return $addCode;

			}


			//! render webcam post page: room = sanitized post title
			$stream = get_post_meta( $postID, 'performer', true);
			if (!$stream) $stream = $room; //use room name


			$hideApp = false;


			//fake performer
			$playlistActive = get_post_meta( $postID, 'vw_playlistActive', true );

			//detect if performer online
			if ($options['performerOffline'] != 'show' && !$playlistActive)
				if (!VWliveWebcams::isAuthor($postID) )
				{
					if (!VWliveWebcams::webcamOnline($postID))
					{
						$addCode = '<div id="performerStatus" class="ui ' . $options['interfaceClass'] .' segment">' . $options['performerOfflineMessage'] . '</div>';
						if ($options['performerOffline'] == 'hide') $hideApp = true;

					}
				}

			//paid room
			$groupCPM = get_post_meta($postID, 'groupCPM', true);
			if ($groupCPM)
			{
				$userID = get_current_user_id();
				$balance = self::balance($userID); //use complete balance to avoid double amount checking

				$ppvMinInShow =  self::balanceLimit($groupCPM, 2,  $options['ppvMinInShow'], $options);

				if ($groupCPM>0)
					if ( $groupCPM + $ppvMinInShow > $balance)
					{
						$addCode = '<div id="warnGroupCPM" class="ui ' . $options['interfaceClass'] .' segment">' . __('This is a paid room. A minimum balance required to access.', 'ppv-live-webcams') . '<br>' . ($groupCPM + $ppvMinInShow) . '/'  . $balance . '<br><a class="ui button primary qbutton" href="' . get_permalink( $options['balancePage'] ) . '">' . __('Wallet', 'ppv-live-webcams') . '</a>' .'</div>' ;
						$hideApp = true;
					}

				if (!$userID)  {
					$addCode = '<div id="warnGroupCPM" class="ui ' . $options['interfaceClass'] .' segment">' . __('This is a paid room. Login is required to access a paid room.', 'ppv-live-webcams') . '<br><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a></div>';
					$hideApp = true;

				}


			}

			//show app

			if (!$hideApp)
			{
				$addCode .= '[videowhisper_videochat room="' . $room . '"]';

				//ip camera or playlist: update snapshot
				if (get_post_meta( $postID, 'vw_ipCamera', true ) || get_post_meta( $postID, 'vw_playlistActive', true ))
				{
					VWliveWebcams::streamSnapshot($stream, true, $postID);
				}


				//update thumbnail if missing
				$dir = $options['uploadsPath']. "/_snapshots";
				$thumbFilename = "$dir/$stream.jpg";

				//only if snapshot exists but missing post thumb (not uploaded or generated previously)
				if ( file_exists($thumbFilename) && !get_post_thumbnail_id( $postID ))
				{

					VWliveWebcams::delete_associated_media($postID, false);

					$wp_filetype = wp_check_filetype(basename($thumbFilename), null );

					$attachment = array(
						'guid' => $thumbFilename,
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => $stream,
						'post_content' => '',
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
					set_post_thumbnail($postID, $attach_id);

					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
					wp_update_attachment_metadata( $attach_id, $attach_data );
				}
			}

			//under videochat

			//semantic ui : profile
			VWliveWebcams::enqueueUI();

			$addCode .= self::webcamContent($postID, $room, $options, $content);
			$addCode .= self::webcamLinks($postID);

			return $addCode;
		}


		static function webcamLinks($postID)
		{

			$addCode .= '<a class="ui button tiny compact" href="'. get_permalink( $postID) .'">' .'Webcam Room Page'. '</a> ' ;
			$addCode .= '<a class="ui button tiny compact" href="'. add_query_arg('view', 'content',get_permalink( $postID)) .'">' .'Profile Content'. '</a> ' ;
			$addCode .= '<a class="ui button tiny compact" href="'. add_query_arg('view', 'profile',get_permalink( $postID)) .'">' .'Profile Info'. '</a> ' ;

			return $addCode;
		}


		static function webcamContent($postID, $room, $options, $content)
		{
			$addCode = '';

			$addCode .= '
	<script>
jQuery(document).ready(function(){

	jQuery(".tabular.menu .item").tab();

});
</script>';

			//tab header
			$addCode .= '<a name="profile"></a> <div class="ui ' . $options['interfaceClass'] . ' top attached tabular menu">';

			$addCode .= '<a class="item active" data-tab="profile">' . __('Profile', 'ppv-live-webcams') . '</a>';

			if ($options['videosharevod']) $addCode .= '<a class="item" data-tab="videos">' . __('Videos', 'ppv-live-webcams') . '</a>';
			if ($options['picturegallery']) $addCode .= '<a class="item" data-tab="pictures">' . __('Pictures', 'ppv-live-webcams') . '</a>';
			if ($options['rateStarReview']) $addCode .= '<a class="item" data-tab="reviews">' . __('Reviews', 'ppv-live-webcams') . '</a>';

			$addCode .= '</div>';

			//tab : profile
			$addCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment active" data-tab="profile"> <div class="ui grid">';

			if ($options['videosharevod'])
			{
				$video_teaser = get_post_meta($postID, 'video_teaser', true);

				if ($video_teaser) $addCode .= '<div class="item"><h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Teaser', 'ppv-live-webcams') . '</h3> <div class="ui ' . $options['interfaceClass'] .' segment" style="min-width:320px">' . do_shortcode('[videowhisper_player video="' .$video_teaser. '"]' . '</div></div>');
			}


			$addCode .= '<div class="item">';
			//! show viewers

			if ($options['viewersCount']||$options['salesCount'])
			{
				$addCode .= '<h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Meta', 'ppv-live-webcams') . '</h3><div class="ui ' . $options['interfaceClass'] .' segment">';

				if ($options['viewersCount'])
				{

					$maxViewers =  get_post_meta($postID, 'maxViewers', true);
					if (!is_array($maxViewers))
						if ($maxViewers>0)
						{
							$maxDate = (int) get_post_meta($postID, 'maxDate', true);
							$addCode .= __('Maximum viewers','ppv-live-webcams') . ': ' . $maxViewers;
							if ($maxDate) $addCode .= ' on ' . date("F j, Y, g:i a", $maxDate);
						}
				}

				//! show clients
				if ($options['salesCount'])
				{
					$clientsP = get_post_meta($postID, 'paidSessionsPrivate', true);
					$clientsG = get_post_meta($postID, 'paidSessionsGroup', true);

					$addCode .= '<div id="vwPaidSessions">' . __('Paid sessions', 'ppv-live-webcams') . ': ' . $clientsP . ' ' . __('private', 'ppv-live-webcams') . ', ' . $clientsG . ' ' . __('group', 'ppv-live-webcams') . '. ' . __('Logged days', 'ppv-live-webcams') . ': ' . round($options['ppvKeepLogs'] / 3600 / 24,2) . ' </div>';
				}

				$addCode .= '</div>';
			}

			//! show profile
			$profileCode = VWliveWebcams::webcamProfile($postID, $options);
			if ($profileCode) $addCode .= '<h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Profile Info', 'ppv-live-webcams') . '</h3>' . $profileCode;

			if ($content) $addCode .= '<h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Description', 'ppv-live-webcams') . '</h3><div class="ui ' . $options['interfaceClass'] .' segment">' . $content . '</div>';



			$addCode .= '</div></div></div>';

			//! show videos
			if ($options['videosharevod'])
			{
				//tab : videos
				$addCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment" data-tab="videos">';

				$vw_videos = get_post_meta($postID, 'vw_videos',true);

				if ($vw_videos) if (shortcode_exists("videowhisper_videos"))
						$addCode .= '<h3 class="header">' . __('Videos', 'ppv-live-webcams') . '</h3>' . do_shortcode('[videowhisper_videos playlist="' . $room . '" include_css="1"]' );
					else $addCode .= 'Warning: shortcodes missing. Plugin <a href="https://videosharevod.com">Video Share VOD</A> should be installed and enabled or feature disabled.';

					$addCode .= '</div>';
			}

			//! show pictures
			if ($options['picturegallery'])
			{
				//tab : pictures
				$addCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment" data-tab="pictures">';

				$vw_pictures = get_post_meta($postID, 'vw_pictures',true);
				if ($vw_pictures)
					if (shortcode_exists("videowhisper_pictures"))
						$addCode .= '<h3 class="ui header">' . __('Pictures', 'ppv-live-webcams') . '</h3>' . do_shortcode('[videowhisper_pictures perpage="12" gallery="' . $room . '" include_css="1"]' );
					else $addCode .= 'Warning: shortcodes missing. Plugin <a href="https://wordpress.org/plugins/picture-gallery/">Picture Gallery</A> should be installed and enabled or feature disabled.';

					$addCode .= '</div>';
			}

			//! show reviews
			if ($options['rateStarReview'])
			{
				//tab : reviews
				$addCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment" data-tab="reviews">';

				if (shortcode_exists("videowhisper_review"))
					$addCode .= '<h3 class="ui header">' . __('My Review', 'ppv-live-webcams') . '</h3>' . do_shortcode('[videowhisper_review content_type="webcam" post_id="' . $postID . '" content_id="' . $postID . '"]' );
				else $addCode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';

				if (shortcode_exists("videowhisper_reviews"))
					$addCode .= '<h3 class="ui header">' . __('Reviews', 'ppv-live-webcams') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );

				$addCode .= '</div>';
			}


			return $addCode;

		}


		static function webcamProfile($postID, $options)
		{
			if (!$options) $options = get_option('VWliveWebcamsOptions');

			//allowed tags
					$allowedtags = array(
						'a' => array(
							'href' => true,
							'title' => true,
						),
						'abbr' => array(
							'title' => true,
						),
						'acronym' => array(
							'title' => true,
						),
						'b' => array(),
						'blockquote' => array(
							'cite' => true,
						),
						'cite' => array(),
						'code' => array(),
						'del' => array(
							'datetime' => true,
						),
						'em' => array(),
						'i' => array(),
						'q' => array(
							'cite' => true,
						),
						'strike' => array(),
						'strong' => array(),

						'ul' => array(),
						'ol' => array(),
						'li' => array(),
						
						'span' => array(
							'style' => array()
						),
						
						'p' => array(
							'style' => array()
						),
					);


			$profileCode .= '<div>';

			if (is_array($options['profileFields']))
				foreach ($options['profileFields'] as $field => $parameters)
				{
					$fieldName = sanitize_title(trim($field));

					//retrieve value
					if ($parameters['type'] == 'checkboxes')
					{
						$fieldValue = get_post_meta( $postID, 'vwf_' . $fieldName, true);
						if (!$fieldValue) $fieldValue = array();
						if (!is_array($fieldValue)) $fieldValue = array($fieldValue);
					}
					else $fieldValue = wp_kses(get_post_meta( $postID, 'vwf_' . $fieldName, true ), $allowedtags);


					if ($fieldValue) if (!is_array($fieldValue))
						{
							$profileCode .= '<div class="ui ' . $options['interfaceClass'] .' segment">
						<h4 class="ui ' . $options['interfaceClass'] . ' header">'.$field.'</h4>' . $fieldValue . '</div>';
						}
					else
					{
						$fieldValues = '';
						foreach ($fieldValue as $fieldItem) $fieldValues .= ($fieldValues?', ':'') . $fieldItem;

						$profileCode .= '<div class="ui ' . $options['interfaceClass'] .' segment">
						<h4 class="ui ' . $options['interfaceClass'] . ' header">'.$field.'</h4>' . $fieldValues . '</div>';
					}
				}
			$profileCode .= '</div>';
			return $profileCode;

		}


		static function query_vars( $vars ){
			$vars[] = "page";
			return $vars;
		}


		public static function pre_get_posts($query)
		{

			//add webcams to post listings
			if(is_category() || is_tag())
			{
				$query_type = get_query_var('post_type');

				$options = get_option('VWliveWebcamsOptions');


				if($query_type)
				{
					if (is_array($query_type))
						if (in_array('post', $query_type) && !in_array($options['custom_post'], $query_type))
							$query_type[] = $options['custom_post'];

				}
				else  //default
					{
					$query_type = array('post', $options['custom_post']);
				}

				$query->set('post_type', $query_type);
			}

			return $query;
		}


		function columns_head_webcam($defaults) {
			$defaults['featured_image'] = 'Snapshot';
			$defaults['edate'] = 'Last Online';
			$defaults['vw_costPerMinute'] = 'Custom CPM';
			$defaults['vw_costPerMinuteGroup'] = 'Custom Group CPM';
			$defaults['vw_earningRatio'] = 'Custom Earning Ratio';
			$defaults['customRoomLink'] = 'Custom Link';

			$defaults['vw_featured'] = 'Featured';

			return $defaults;
		}


		function columns_register_sortable( $columns ) {
			$columns['edate'] = 'edate';
			$columns['vw_costPerMinute'] = 'vw_costPerMinute';
			$columns['vw_costPerMinute'] = 'vw_costPerMinuteGroup';
			$columns['vw_earningRatio'] = 'vw_earningRatio';
			$columns['vw_featured'] = 'vw_featured';

			return $columns;
		}


		function columns_content_webcam($column_name, $post_id)
		{

			if ($column_name == 'featured_image')
			{

				$options = get_option('VWliveWebcamsOptions');

				global $wpdb;
				$postName = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE ID = '" . $post_id . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

				if ($postName)
				{
					$options = get_option('VWliveWebcamsOptions');
					$dir = $options['uploadsPath']. "/_thumbs";
					$thumbFilename = "$dir/" . $postName . ".jpg";

					$url = VWliveWebcams::roomURL($postName);

					if (file_exists($thumbFilename)) echo '<a href="' . $url . '"><IMG src="' . VWliveWebcams::path2url($thumbFilename) .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';

				}



			}

			if ($column_name == 'edate')
			{
				$edate = get_post_meta($post_id, 'edate', true);
				if ($edate)
				{
					echo ' ' . VWliveWebcams::format_age(time() - $edate);

				}


			}

			if ($column_name == 'vw_costPerMinute')
			{
				echo  get_post_meta($post_id, 'vw_costPerMinute', true);
			}

			if ($column_name == 'vw_costPerMinuteGroup')
			{
				echo  get_post_meta($post_id, 'vw_costPerMinuteGroup', true);
			}


			if ($column_name == 'customRoomLink')
			{
				echo  get_post_meta($post_id, 'customRoomLink', true);
			}

			if ($column_name == 'vw_earningRatio')
			{
				echo  get_post_meta($post_id, 'vw_earningRatio', true);
			}

			if ($column_name == 'vw_featured')
			{
				$featured = get_post_meta($post_id, 'vw_featured', true);
				if (empty($featured)) update_post_meta($post_id, 'vw_featured', 0);

				echo ($featured?__('Yes','ppv-live-webcams').' ('.$featured.')':__('No','ppv-live-webcams'));
			}
		}


		public static function duration_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'edate' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
						'meta_key' => 'edate',
						'orderby' => 'meta_value_num'
					) );
			}

			return $vars;
		}


		function quick_edit_custom_box( $column_name, $post_type ) {

			$options = get_option('VWliveWebcamsOptions');

			static $printNonce = TRUE;
			if ( $printNonce ) {
				$printNonce = FALSE;
				wp_nonce_field( plugin_basename( __FILE__ ),  $options['custom_post'] . '_edit_nonce' );
			}

?>
    <fieldset class="inline-edit-col-right inline-edit-book">
      <div class="inline-edit-col column-<?php echo $column_name; ?>">
        <label class="inline-edit-group">
        <?php
			switch ( $column_name ) {
			case 'vw_costPerMinute':
				?><span class="title">New Custom CPM</span><input name="vw_costPerMinute" />
				<BR>Custom cost per minute for private shows. Ex: 0.5
				<?php
				break;
			case 'vw_costPerMinuteGroup':
				?><span class="title">New Custom Group CPM</span><input name="vw_costPerMinuteGroup" />
				<BR>Custom cost per minute for group shows. Replaces paid group CPM. Ex: 0.5
				<?php
				break;
			case 'vw_earningRatio':
				?><span class="title">New Custom Earning Ratio</span><input name="vw_earningRatio"/>
				<BR>Fraction earned by performer. Ex: 0.80 Min: 0 Max: 1<?php
				break;
			case 'vw_featured':
				?><span class="title">New Featured Level</span><input name="vw_featured"/>
				<BR>Higher featured show first in listings. Ex: 1 Default: 0 (not featured)
				<?php
				break;
			case 'customRoomLink':
				?><span class="title">New Custom Room Link</span><input name="customRoomLink"/>
				<BR>Define a custom link for room Enter button in listings.
				<?php
				break;

			}
?>
        </label>
      </div>
    </fieldset>
    <?php
		}


		function save_post( $post_id ) {


			if ( isset( $_REQUEST['videowhisper_template'] ) )
			{
				$postTemplate = sanitize_text_field($_REQUEST['videowhisper_template']);
				update_post_meta( $post_id, 'videowhisper_template', $postTemplate);
			}

			$options = get_option('VWliveWebcamsOptions');

			$slug = $options['custom_post'];

			if ( $slug !== $_POST['post_type'] ) {
				return;
			}
			if ( !current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			$_POST += array("{$slug}_edit_nonce" => '');
			if ( !wp_verify_nonce( $_POST["{$slug}_edit_nonce"],
					plugin_basename( __FILE__ ) ) )
			{
				return;
			}

			if ( isset( $_REQUEST['vw_costPerMinute'] ) ) {
				update_post_meta( $post_id, 'vw_costPerMinute', $_REQUEST['vw_costPerMinute'] );
			}

			if ( isset( $_REQUEST['vw_costPerMinuteGroup'] ) ) {
				update_post_meta( $post_id, 'vw_costPerMinuteGroup', $_REQUEST['vw_costPerMinuteGroup'] );
			}

			if ( isset( $_REQUEST['vw_earningRatio'] ) ) {
				update_post_meta( $post_id, 'vw_earningRatio', $_REQUEST['vw_earningRatio'] );
			}

			if ( isset( $_REQUEST['vw_featured'] ) ) {
				update_post_meta( $post_id, 'vw_featured', $_REQUEST['vw_featured'] );
			}

			if ( isset( $_REQUEST['customRoomLink'] ) ) {
				update_post_meta( $post_id, 'customRoomLink', $_REQUEST['customRoomLink'] );
			}

		}


		//
		static function webcamPost($name = '', $performer = '', $performerID=0, $studioID = 0)
		{
			// retrieves default performer webcam listing or creates it if necessary

			if (!is_user_logged_in()) return;

			$options = get_option('VWliveWebcamsOptions');

			$current_user = wp_get_current_user();

			if (!$name)
			{
				$post_title = VWliveWebcams::performerName($current_user, $options);

			}
			else $post_title = sanitize_file_name($name);

			global $wpdb;
			$pid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = '%s' AND post_type='" . $options['custom_post'] . "'", $post_title ));

			if (!$pid) //creating
				{
				$post = array(
					'post_name'      => sanitize_title_with_dashes($post_title),
					'post_title'     => $post_title,
					'post_author'    => $current_user->ID,
					'post_type'      => $options['custom_post'],
					'post_status'    => 'publish',
				);

				$pid = wp_insert_post($post);
				//update_post_meta($pid, 'rdate', time());
				//update_post_meta($pid, 'viewers', 0);



				if (!$performer) $performer = $post_title;
				if ($performer)
				{
					update_post_meta($pid, 'performer', $performer);
				}

				if (!$performerID) $performerID = $current_user->ID;
				if ($performerID)
				{
					//no need to assign if already owner
					if ($current_user->ID != $performerID) update_post_meta($pid, 'performerID', $performerID);

					//set as selected webcam for this performer if he has no cam
					$selectWebcam = get_user_meta($performerID, 'currentWebcam', true);
					if (!$selectWebcam) update_user_meta($performerID, 'currentWebcam', $pid);
				}

				if ($studioID)
				{
					update_post_meta($pid, 'studioID', $studioID);
				}

			}

			return $pid;
		}


		function single_template($single_template)
		{

			if (!is_singular())  return $single_template; //not single page/post

			$postID = get_the_ID();

			//custom template
			$postTemplate = get_post_meta( $postID, 'videowhisper_template', true );
			if ($postTemplate == '+app')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-app.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}
			if ($postTemplate == '+plugin')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-webcam.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}

			$options = get_option('VWliveWebcamsOptions');
			//webcam post template
			if (get_post_type( $postID ) != $options['custom_post']) return $single_template;

			if ($options['postTemplate'] == '+app')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-app.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}
			if ($options['postTemplate'] == '+plugin')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-webcam.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}

			$single_template_new = get_template_directory() . '/' . $options['postTemplate'];

			if (file_exists($single_template_new)) return $single_template_new;
			else return $single_template;
		}


		function importArchives($postID)
		{
			//import recorded archived sessions with VSV

			$options = get_option('VWliveWebcamsOptions');

			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if (!is_plugin_active('video-share-vod/video-share-vod.php')) return; //requires VideoShareVOD.com plugin installed and active

			$archivedSessions = get_post_meta($postID, 'archivedSessions', true);
			if (!$archivedSessions) return;

			$post = get_post( $postID);

			//$archivedSession = array('performer' =>$performerName, 'sessionStart' => time(), 'groupMode'=>$groupMode);

			$ignored = array('.', '..', '.svn', '.htaccess');
			$extensions = array('flv', 'mp4', 'f4v', 'm4v');

			foreach ($archivedSessions as $key => $archivedSession)
			{
				$fileList = scandir($options['streamsPath']);

				$prefix = $archivedSession['performer'];
				$prefixL=strlen($prefix);

				foreach ($fileList as $fileName)
				{

					if (in_array($fileName, $ignored)) continue;
					if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;
					if ($prefixL) if (substr($fileName,0,$prefixL) != $prefix) continue;

						$filepath = $options['streamsPath'] . '/' . $fileName;

					//found!

					$modified = filemtime($filepath);

					$playlists = array($archivedSession['performer'], $post->post_title, $archivedSession['groupMode'], $post->post_title .' ' . $archivedSession['groupMode'], $post->post_title .' ' . $archivedSession['groupMode'] .' ' . date('j M Y', $modified));

					$tags = array($archivedSession['performer'], $post->post_title, $archivedSession['groupMode'], date('j M Y', $modified));

					$title = $post->post_title . ' ' . $archivedSession['groupMode'] . ' ' .$archivedSession['performer'] . ' ' . date('G:i j M Y', $modified);

					$description = $title . ' ' . $filepath ;

					//import
					VWvideoShare::importFile($filepath, $title, $post->post_author, $playlists, $category, $tags, $description);

					//clean
					unlink($filepath);

					//remove from list
					unset($archivedSessions['key']);
					update_post_meta($postID, 'archivedSessions', $archivedSessions);


					//import one file only
					break;
				}

			}

			//end: reset list
			update_post_meta($postID, 'archivedSessions', array());


		}


		//! FFMPEG

		static function streamSnapshot($stream, $standalone = false, $postID ='')
		{

			//updates snapshot and thumbs for room $postID from $stream

			//$standalone = independent streams/scheduler - not used
			//handles rtmp/rtsp snapshots depending on source type

			$stream = sanitize_file_name($stream);

			if (strstr($stream,'.php')) return;
			if (!$stream) return;

			$options = get_option('VWliveWebcamsOptions');

			global $wpdb;

			if (!$postID) //use as listing name if not id
				{
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $stream . "' and post_type='".$options['custom_post']. "' LIMIT 0,1" );
			}

			if (!$postID) return; //did not identify

			if (!$room)
			{
				$room = $wpdb->get_var( "SELECT post_title  FROM $wpdb->posts WHERE ID = '" . $postID . "'" );
			}

			//rest time
			$rtmpSnapshotRest = $options['rtmpSnapshotRest'];
			if ($rtmpSnapshotRest < 10) $rtmpSnapshotRest = 30;

			$snapshotDate = get_post_meta($postID, 'snapshotDate', true);
			if (time() - $snapshotDate < $rtmpSnapshotRest) return; // fresh

			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);
			$dir .= "/_snapshots";
			if (!file_exists($dir)) mkdir($dir);

			if (!file_exists($dir))
			{
				$error = error_get_last();
				//echo 'Error - Folder does not exist and could not be created: ' . $dir . ' - '.  $error['message'];

			}

			$filename = "$dir/$room.jpg";
			if (file_exists($filename)) if (time()-filemtime($filename) < $rtmpSnapshotRest) return; //do not update if file is fresh (15s)

				$log_file = $filename . '.txt';


			//get primary stream source (rtmp/rtsp)
			$streamProtocol = get_post_meta($postID, 'stream-protocol', true);
			$streamType = get_post_meta($postID, 'stream-type', true);
			$streamAddress = get_post_meta($postID, 'vw_ipCamera', true);

			$roomRTMPserver = VWliveWebcams::rtmpServer($postID, $options);

			if ($streamType == 'restream' && $streamAddress)
			{
				//retrieve from main source
				$cmdP = '';
				if ($streamProtocol == 'rtsp') $cmdP = '-rtsp_transport tcp'; //use tcp for rtsp
				$cmd = 'timeout -s KILL 10 ' . $options['ffmpegPath'] ." -y -frames 1 \"$filename\" $cmdP -i \"" . $streamAddress . "\" >&$log_file  ";
			}
			elseif ($streamProtocol == 'rtsp')
			{
				$streamQuery = VWliveWebcams::webrtcStreamQuery($userID, $postID, 0, $stream, $options, 1, $room);

				//usually webrtc
				$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] . " -f image2 -vframes 1 \"$filename\" -y -i \"" . $options['rtsp_server'] ."/". $streamQuery . "\" >&$log_file & ";
			}
			else
			{
				if ($options['externalKeysTranscoder'])
				{
					$keyView = md5('vw' . $options['webKey']. $postID);
					$rtmpAddressView = $roomRTMPserver . '?'. urlencode('ffmpegSnap_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';
				}
				else $rtmpAddressView = $roomRTMPserver;

				$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] . " -f image2 -vframes 1 \"$filename\" -y -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";
			}

			//start and log snapshot process
			VWliveWebcams::startProcess($cmd, $log_file, $postID, $stream, 'snapshot', $options);

			//failed
			if (!file_exists($filename)) return;

			//if snapshot successful update time
			update_post_meta($postID, 'edate', time()); //always update (snapshot retrieved = stream is live)
			update_post_meta($postID, 'snapshotDate', time());
			update_post_meta($postID, 'vw_lastSnapshot', $filename);

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$src = imagecreatefromjpeg($filename);
			list($width, $height) = getimagesize($filename);
			$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

			$dir = $options['uploadsPath']. "/_thumbs";
			if (!file_exists($dir)) mkdir($dir);

			$thumbFilename = "$dir/$room.jpg";
			imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagejpeg($tmp, $thumbFilename, 95);

			//detect tiny images without info
			if (filesize($thumbFilename)>5000) $picType = 1;
			else $picType = 2;

			//update post meta
			if ($postID) update_post_meta($postID, 'hasSnapshot', $picType);
		}


		//!  FFMPEG Transcoding
		static function transcodeStreamWebRTC($stream, $postID, $options = null, $detect=2)
		{
			//not used

			//transcode for WebRTC usage: RTMP/RTSP as necessary
			if (!$stream) return;
			if (!$options)  $options = get_option('VWliveWebcamsOptions');

			if (!$options['webrtc']) return;
			// check every 59s
			$tooSoon = 0;
			if (!VWliveWebcams::timeTo($stream . '/transcodeCheckWebRTC', 29, $options)) $tooSoon = 1;

			global $wpdb;

			if (!$postID)
			{
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}

			if (!$postID) return '';

			if (!$room)
			{
				$room = $wpdb->get_var( "SELECT post_title  FROM $wpdb->posts WHERE ID = '" . $postID . "'" );
			}

			$stream_webrtc = get_post_meta($postID, 'stream-webrtc', true);
			if (!VWliveWebcams::timeTo($stream . '/transcodeCheckWebRTC-Flood', 3, $options)) return $stream_webrtc; //prevent duplicate checks

			//room metas
			$transcodeEnabled = get_post_meta($postID, 'vw_transcode', true);
			$videoCodec = get_post_meta($postID, 'stream-codec-video', true);
			$privateShow = get_post_meta($postID, 'privateShow', true);
			$performer = get_post_meta($postID, 'performer', true);
			$performerUserID = get_post_meta($postID, 'performerUserID', true);

			if ($performer) $stream = $performer; //always transcode room performer stream as source


			$streamProtocol = get_post_meta($postID, 'stream-protocol', true);
			$streamType = get_post_meta($postID, 'stream-type', true); //restream/webrtc/external
			$streamMode = get_post_meta($postID, 'stream-mode', true); //direct/safari_pc

			$roomRTMPserver = VWliveWebcams::rtmpServer($postID, $options);


			if (!$streamProtocol) $streamProtocol = 'rtmp'; //assuming plain wowza stream

			/*
	//not needed as rtmp streams play as HLS
			if ($streamProtocol == 'rtmp') //source available as RTMP (flash, external)
				{

				//RTMP to RTSP (h264/opus)
				$stream_webrtc = $stream . '_webrtc';

				if ($tooSoon) return $stream_webrtc;

				//detect transcoding process - cancel if already started
				$cmd = "ps aux | grep '/$stream_webrtc -i rtmp'";
				exec($cmd, $output, $returnvalue);

				$transcoding = 0;
				foreach ($output as $line)
					if (strstr($line, "ffmpeg"))
					{
						$transcoding = 1;
						break;
					}

				//rtmp keys: input
				if ($options['externalKeysTranscoder'])
				{
					$current_user = wp_get_current_user();
					$keyView = md5('vw' . $options['webKey']. $postID);
					$rtmpAddressView = $roomRTMPserver . '?'. urlencode('ffmpegWebRTC_' . $stream_webrtc) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';

				}
				else
				{
					$rtmpAddress = $roomRTMPserver;
					$rtmpAddressView = $roomRTMPserver;
				}

				$streamQuery = VWliveWebcams::webrtcStreamQuery($performerUserID, $postID, 1, $stream_webrtc, $options, 1, $room);


				//paths
				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$upath = $uploadsPath . "/$stream/";
				if (!file_exists($upath)) mkdir($upath);


				if (!$transcoding) //transcode to rtsp
					{

					//start transcoding process
					$log_file =  $upath . "transcode_rtmp-webrtc.log";

					$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscodeRTC'] .
						" -threads 1 -f rtsp \"" . $options['rtsp_server_publish'] . '/'. $streamQuery .
						"\" -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";


					//echo $cmd;
					exec($cmd, $output, $returnvalue);
					exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

					update_post_meta( $postID, 'stream-webrtc',  $stream_webrtc );
				}
				else update_post_meta($postID, 'transcoding-webrtc', time());
			}
*/

			//safari_pc
			if ($streamProtocol == 'rtsp' && $streamMode == 'safari_pc') //source available as RTSP (WebRTC) but not correc profile in h264
				{

				if (!$options['transcodeRTC']) return $stream;
				//RTSP to RTSP (correct profile transcoding)

				//RTMP to RTSP (h264/opus)
				$stream_webrtc = $stream . '_webrtc';

				if ($tooSoon) return $stream_webrtc;

				$streamQuery = VWliveWebcams::webrtcStreamQuery($performerUserID, $postID, 1, $stream_webrtc, $options, 1, $room);

				//detect transcoding process - cancel if already started
				$cmd = "ps aux | grep '/$streamQuery '";
				exec($cmd, $output, $returnvalue);

				$transcoding = 0;
				foreach ($output as $line)
					if (strstr($line, "ffmpeg"))
					{
						$transcoding = 1;
						break;
					}


				//paths for logs
				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$upath = $uploadsPath . "/$stream/";
				if (!file_exists($upath)) mkdir($upath);


				if (!$transcoding) //transcode
					{

					//detect
					if ($detect == 2 || ($detect == 1 && !$videoCodec))
					{

						//detect webrtc stream info
						$log_file =  $upath . "streaminfo-webrtc.log";

						$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] .' -y -i "' . $options['rtsp_server'] . '/' . $stream . '" 2>&1 ';
						$info = shell_exec($cmd);

						//video
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
						list($videoCodec) = explode(' ',$matches[1]);
						if ($videoCodec && $postID) update_post_meta( $postID, 'stream-codec-video', strtolower($videoCodec) );

						//audio
						$matches = array();
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

						list($audioCodec) = explode(' ',$matches[1]);
						$audioCodec = trim($audioCodec, " ,.\t\n\r\0\x0B");
						if ($audioCodec && $postID) update_post_meta( $postID, 'stream-codec-audio', strtolower($audioCodec) );
						if (($videoCodec || $audioCodec) && $postID) update_post_meta( $postID, 'stream-codec-detect', time() );

						exec("echo '". "$stream|$stream_hls|$stream_webrtc|$transcodeEnabled|$detect|$videoCodec|$audioCodec" ."' >> $log_file", $output, $returnvalue);
						exec("echo \"". addslashes($info)."\" >> $log_file", $output, $returnvalue);

						//
					}


					//start transcoding process
					$log_file =  $upath . "transcode_webrtc-webrtc.log";

					if ($videoCodec && $audioCodec) //if incomplete, transcode later
						{
						$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscodeRTC'] .
							" -threads 1 -f rtsp \"" . $options['rtsp_server_publish'] . '/'. $streamQuery .
							"\" -i \"" . $options['rtsp_server'] ."/". $stream . "\" >&$log_file & ";


						//echo $cmd;
						exec($cmd, $output, $returnvalue);
						exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

						update_post_meta( $postID, 'stream-webrtc',  $stream_webrtc );
					}
					else exec("echo 'RTSP stream incomplete: " . $options['rtsp_server'] ."/". $stream ." Will check again later... ' >> $log_file", $output, $returnvalue);

				}
				else update_post_meta($postID, 'transcoding-webrtc', time()); //last time process detected
			}

			// rtsp to hls not required as showing directly
			/*
			if ($streamProtocol == 'rtsp' && $streamType != 'restream') //source available as RTSP (WebRTC) and is not a restream (handled from rtmp)
				{
				//RTSP to HLS/RTMP (h264/aac)
				$stream_hls = 'i_' . $stream;

				if ($tooSoon) return $stream_hls;


				//detect transcoding process - cancel if already started
				$cmd = "ps aux | grep '/$stream_hls -i rtsp'";
				exec($cmd, $output, $returnvalue);

				$transcoding = 0;
				foreach ($output as $line)
					if (strstr($line, "ffmpeg"))
					{
						$transcoding = 1;
						break;
					}

				//rtmp keys:output
				if ($options['externalKeysTranscoder'])
				{
					$key = md5('vw' . $options['webKey'] . $performerUserID . $postID);
					$rtmpAddress = $roomRTMPserver . '?'. urlencode($stream_hls) .'&'. urlencode($stream) .'&'. $key . '&1&' . $performerUserID . '&videowhisper';
				}
				else
				{
					$rtmpAddress = $roomRTMPserver;
					$rtmpAddressView = $roomRTMPserver;
				}

				//paths
				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$upath = $uploadsPath . "/$stream/";
				if (!file_exists($upath)) mkdir($upath);

				if (!$transcoding) //transcode to rtmp
					{

					if ($detect == 2 || ($detect == 1 && !$videoCodec))
					{

						//detect webrtc stream info
						$log_file =  $upath . "streaminfo-webrtc.log";

						$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] .' -y -i "' . $options['rtsp_server'] . '/' . $stream . '" 2>&1 ';
						$info = shell_exec($cmd);

						//video
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
						list($videoCodec) = explode(' ',$matches[1]);
						if ($videoCodec && $postID) update_post_meta( $postID, 'stream-codec-video', strtolower($videoCodec) );

						//audio
						$matches = array();
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

						list($audioCodec) = explode(' ',$matches[1]);
						$audioCodec = trim($audioCodec, " ,.\t\n\r\0\x0B");
						if ($audioCodec && $postID) update_post_meta( $postID, 'stream-codec-audio', strtolower($audioCodec) );
						if (($videoCodec || $audioCodec) && $postID) update_post_meta( $postID, 'stream-codec-detect', time() );

						exec("echo '". "$stream|$stream_hls|$stream_webrtc|$transcodeEnabled|$detect|$videoCodec|$audioCodec" ."' >> $log_file", $output, $returnvalue);
						exec("echo \"". addslashes($info)."\" >> $log_file", $output, $returnvalue);

						//
					}


					//start transcoding process
					$log_file =  $upath . "transcode_webrtc-hls.log";

					if ($videoCodec && $audioCodec) //if incomplete, transcode later
						{
						//convert command
						$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -f flv \"" .
							$rtmpAddress . "/". $stream_hls . "\" -i \"" . $options['rtsp_server'] ."/". $stream . "\" >&$log_file & ";

						//echo $cmd;
						exec($cmd, $output, $returnvalue);
						exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

						update_post_meta( $postID, 'stream-hls',  $stream_hls );
					}
					else exec("echo 'Stream incomplete. Will check again later... ' >> $log_file", $output, $returnvalue);

				}
				else update_post_meta($postID, 'transcoding-hls', time());

			}
			*/

			//otherwise return existing stream (and update)
			if (!$stream_webrtc) $stream_webrtc = $stream;
			update_post_meta( $postID, 'stream-webrtc',  $stream_webrtc );
			return $stream_webrtc;
		}


		static function responsiveStream($default, $postID, $player = 'flash')
		{
			if (!$postID) return $default;

			$streamProtocol = get_post_meta($postID, 'stream-protocol', true);

			if ($player == 'flash')
				if ($streamProtocol == 'rtsp') //may require transcoding
					{
					$transcode = 0;

					$videoCodec = get_post_meta($postID, 'stream-codec-video', true);
					$audioCodec = get_post_meta($postID, 'stream-codec-audio', true);

					if (!in_array($videoCodec, array('h264')) ) $transcode =1;
					if (!in_array($audioCodec, array('aac','speex')) ) $transcode =1;

					if (!$transcode) return $default;

					$stream_hls = get_post_meta( $postID, 'stream-hls',  true );
					if ($stream_hls) return $stream_hls;
				}

			return $default;
		}



		function transcodeStream($stream, $required=0, $room='', $detect=2, $convert=1, $options = null, $postID = 0)
		{

			//$detect: 0 = no, 1 = auto, 2 = always (update)
			//$convert: 0 = no, 1 = auto , 2 = always

			//VWliveWebcams

			if (!$stream) return;
			if (!$room) $room = $stream;

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			if (!$options['transcoding']) return $stream; //functionality is disabled


			//is it a post channel?
			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}

			//echo "transcodeStream($stream, $required, $detect, $convert) $transcoding $postID ".$options['transcoding'];


			//is feature enabled?
			if ($postID)
			{
				$transcodeEnabled = get_post_meta($postID, 'vw_transcode', true);
				$videoCodec = get_post_meta($postID, 'stream-codec-video', true);
				$privateShow = get_post_meta($postID, 'privateShow', true);
				$performer = get_post_meta($postID, 'performer', true);

				if ($performer) $stream = $performer; //always transcode room performer stream as source

				$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);
				$sourceType = get_post_meta($postID, 'stream-type', true); // stream-type: flash/external/webrtc/restream/playlist
				$stream_hls = get_post_meta($postID, 'stream-hls', true);
				$reStream = get_post_meta( $postID, 'vw_ipCamera', true );

			}
			else return $stream;

			if (in_array($sourceProtocol, array('http', 'https'))) $stream_hls = $stream; // as is for http streams

			if ( !$options['transcodingAuto'] && $convert != 2) return $stream_hls; //disabled

			//direct delivery for restream/external/playlist : do not transcode
			if (($reStream && !$options['transcodeReStreams']) || ($sourceType == 'external' && !$options['transcodeExternal']) || $sourceType == 'playlist')
			{
				update_post_meta( $postID, 'stream-hls', $stream );

				return $stream;
			}



			// do not check more often than 60s if not required
			if (!$required)
				if (!VWliveWebcams::timeTo('transcoder-' . $stream, 60, $options)) return '';

				//detect transcoding process
				$cmd = "ps aux | grep '/i_$stream -i rtmp'";
			exec($cmd, $output, $returnvalue);
			//var_dump($output);

			$transcoding = 0;
			foreach ($output as $line) if (strstr($line, "ffmpeg"))
				{
					$transcoding = 1;
					break; //break foreach loop
				}

			//stop transcoding if not permitted during show
			if ($transcoding)
				if ($privateShow) if (!$options['transcodeShows'])
					{
						//close transcoding
						$cmd = "ps aux | grep '/i_$stream -i rtmp'";
						exec($cmd, $output, $returnvalue);
						//var_dump($output);

						$transcoderClosed = 0;
						foreach ($output as $line) if (strstr($line, "ffmpeg"))
							{
								$columns = preg_split('/\s+/',$line);
								$cmd = "kill -9 " . $columns[1];
								exec($cmd, $output, $returnvalue);
								//echo "<BR>Closing #".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3];
								$transcoderClosed++;
							}

						return '';
					}

				//no further action required if already transcoding
				if ($transcoding) return "i_". $stream; //already transcoding - use that

				//rtmp keys: required for connecting to stream
				if ($options['externalKeysTranscoder'])
				{
					$current_user = wp_get_current_user();

					$key = md5('vw' . $options['webKey'] . $current_user->ID . $postID);

					$keyView = md5('vw' . $options['webKey']. $postID);

					//?session&room&key&broadcaster&broadcasterid
					$rtmpAddress = $options['rtmp_server_hls'] . '?'. urlencode('i_' . $stream) .'&'. urlencode($stream) .'&'. $key . '&1&' . $current_user->ID . '&videowhisper';
					$rtmpAddressView = VWliveWebcams::rtmpServer($postID, $options) . '?'. urlencode('ffmpeg_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';
					$rtmpAddressViewI = VWliveWebcams::rtmpServer($postID, $options) . '?'. urlencode('ffmpegInfo_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';

					//VWliveWebcams::webSessionSave("/i_". $stream, 1);
				}
			else
			{
				$rtmpAddress = $options['rtmp_server_hls'];
				$rtmpAddressViewI = $rtmpAddressView = VWliveWebcams::rtmpServer($postID, $options);
			}

			//paths
			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$upath = $uploadsPath . "/$room/";
			if (!file_exists($upath)) mkdir($upath);


			//detect codecs - do transcoding only if necessary
			if ($detect == 2 || ($detect == 1 && !$videoCodec))
			{

				$log_file =  $upath . $stream . "_streaminfo.log";

				//$swfurl = plugin_dir_url(__FILE__) . 'videowhisper/live_video.swf';

				//$cmd = $options['ffmpegPath'] .' -y -rtmp_pageurl "http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . '" -rtmp_swfurl "' . $swfurl .'" -rtmp_swfverify "' . $swfurl .'" -i "' . $rtmpAddressViewI .'/'. $stream . '" 2>&1 ';
				$cmd = $options['ffmpegPath'] .' -y -i ' . escapeshellarg($rtmpAddressViewI .'/'. $stream) . '2>&1 ';
				$info = shell_exec($cmd);

				//video
				if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
					preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
				list($videoCodec) = explode(' ',$matches[1]);
				if ($videoCodec && $postID) update_post_meta( $postID, 'stream-codec-video', strtolower($videoCodec) );

				//audio
				$matches = array();
				if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
					preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

				list($audioCodec) = explode(' ',$matches[1]);
				if ($audioCodec && $postID) update_post_meta( $postID, 'stream-codec-audio', strtolower($audioCodec) );

				if (($videoCodec || $audioCodec) && $postID) update_post_meta( $postID, 'stream-codec-detect', time() );

				exec("echo '".addslashes($info)."' >> $log_file", $output, $returnvalue);
				exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

			}

			//do any conversions after detection
			if ($convert)
			{
				if (!$videoCodec && $postID) $videoCodec = get_post_meta($postID, 'stream-codec-video', true);
				if (!$audioCodec && $postID) $audioCodec = get_post_meta($postID, 'stream-codec-audio', true);


				//valid mp4 for html5 playback?
				if (($sourceExt == 'mp4') && ($videoCodec == 'h264') && ($audioCodec = 'aac')) $isMP4 =1;
				else $isMP4 = 0;


				if ($isMP4 && $convert == 1) return $stream; //present format is fine - no conversion required

				if (!$transcodeEnabled) return ''; //transcoding disabled

				//start transcoding process
				$log_file =  $upath . $stream . "_transcode.log";

				//$swfurl = plugin_dir_url(__FILE__) . 'videowhisper/live_video.swf';

				//$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -rtmp_pageurl \"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] . '" -rtmp_swfurl "' . $swfurl .'" -rtmp_swfverify "' . $swfurl ."\" -f flv \"" . $rtmpAddress . "/i_". $room . "\" -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";

				//input/output depends based on performer name, not room name
				$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -f flv " . escapeshellarg($rtmpAddress . "/i_". $stream) . " -i "  . escapeshellarg($rtmpAddressView ."/". $stream) . " >&$log_file 2>&1 & ";

				//start and log transcoding process
				VWliveWebcams::startProcess($cmd, $log_file, $postID, $stream, 'transcode', $options);

				return "i_". $room;
			}

		}


		function startProcess($cmd = '', $log_file = '', $postID = '', $stream ='', $type = '', $options ='')
		{
			//start and log a process
			//$cmd must end in &

			if (!$options)  $options = get_option('VWliveWebcamsOptions');

			//release timeout slots before starting new process
			VWliveWebcams::processTimeout();

			$processId = exec($cmd . ' echo $!;', $output, $returnvalue);

			exec("echo '$cmd' >> ".$log_file."-cmd.txt", $output, $returnvalue);

			$uploadsPath = $options['uploadsPath'];

			$processPath = $uploadsPath . "/_process/";
			if (!file_exists($processPath)) mkdir($processPath);

			if ($processId)
			{


				$info = array(
					'postID' => $postID,
					'stream' => $stream,
					'type' => $type,
					'time' => time()
				);

				VWliveWebcams::varSave($processPath . $processId, $info);
			}
		}


		function processTimeout($search = 'ffmpeg', $force = false, $verbose = false)
		{
			//clear processes for listings that are not online
			$options = get_option('VWliveWebcamsOptions');

			if (!$force && !VWliveWebcams::timeTo('processTimeout', 300, $options)) return;

			if ($verbose) echo '<BR>Checking timeout processes (associated with offline listings) ...';

			$processTimeout = $options['processTimeout'];
			if ($processTimeout < 10) $processTimeout = 90;

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$processPath = $uploadsPath . "/_process/";
			if (!file_exists($processPath)) mkdir($processPath);

			$processUser = get_current_user();

			$cmd = "ps aux | grep '$search'";
			exec($cmd, $output, $returnvalue);
			//var_dump($output);

			$transcoders = 0;
			$kills = 0;

			foreach ($output as $line)
				if (strstr($line, $search))
				{
					$columns = preg_split('/\s+/',$line);
					if ($processUser == $columns[0] && (!in_array($columns[10],array('sh','grep'))))
					{
						$transcoders++;

						$killThis = false;

						$info = VWliveWebcams::varLoad($processPath . $columns[1]);

						if ($info === false)
						{
							//not found: kill it
							//$killThis = true;

							if ($verbose) echo '<br>Warning: No info found for process #'.$columns[1];
						}
						else
						{
							if ($info['postID'])
							{
								$edate = (int) get_post_meta($info['postID'], 'edate', true);
								if (time() - $edate > $processTimeout) $killThis = true; //kill if not online last $processTimeout s
							}

						}

						if ($killThis)
						{
							$cmd = "kill -9 " . $columns[1];
							exec($cmd, $output, $returnvalue);

							$kills++;
							if ($verbose) echo '<br>processTimeout (item offline) Killed #'.$columns[1];
						}
					}
				}

			if ($verbose) echo '<br>' . $transcoders . ' processes found, ' . $kills . ' cleared';


		}


		static function vsvVideoURL($video_teaser, $options = null)
		{
			if (!$video_teaser) return '';

			if (!$options) $options = get_option('VWliveWebcamsOptions');
			$streamPath = '';

			//use conversion if available
			$videoAdaptive = get_post_meta($video_teaser, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();

			foreach (array('high', 'mobile') as $frm)
				if (array_key_exists($frm, $videoAlts))
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
							if ($options['hls_vod']) $streamPath = VWliveWebcams::path2stream($alt['file']);
							else $streamPath = self::path2url($alt['file']);
							break;
						};

				//user original
				if (!$streamPath)
				{
					$videoPath = get_post_meta($video_teaser, 'video-source-file', true);
					$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

					if (in_array($ext, array('flv','mp4','m4v')))
					{
						//use source if compatible
						if ($options['hls_vod']) $streamPath = self::path2stream($videoPath);
						else $streamPath = self::path2url($videoPath);
					}
				}

			if ($options['hls_vod']) $streamURL = $options['hls_vod'] . '_definst_/' . $streamPath .'/manifest.mpd';
			else $streamURL = $streamPath;


			return $streamURL;
		}


		function webcamStreamName($webcamName ='', $postID = 0, $options = '')
		{
			//current stream name (performer) for webcam

			$webcam = sanitize_file_name( $webcamName );

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $webcam . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

			}

			$stream = get_post_meta( $postID, 'performer', true);
			if (!$stream) $stream = $webcamName;

			return sanitize_file_name($stream);
		}


		//! WebRTC
		static function webrtcStreamQuery($userID, $postID, $broadcaster, $stream_webrtc, $options = null, $transcoding = 0, $room ='', $privateUID = 0)
		{

			if (!$options) $options = get_option('VWliveWebcamsOptions');
			$clientIP = VWliveWebcams::get_ip_address();

			if (!$room) $room = $stream_webrtc; //same as stream name

			if ($broadcaster)
			{
				$key = md5('vw' . $options['webKey'] . $userID . $postID);

			}
			else
			{
				$key = md5('vw' . $options['webKey']. $postID );
			}

			$streamQuery = $stream_webrtc . '?channel_id=' . $postID . '&userID=' . urlencode($userID) . '&key=' . urlencode($key) . '&ip=' . urlencode($clientIP) . '&transcoding=' . $transcoding . '&room=' . $room. '&privateUID=' . $privateUID;
			return $streamQuery;

		}


		static function balanceLimit($cpm, $minutes, $default = 0, $options = null)
		{
			if (!$options) $options = get_option('VWliveWebcamsOptions');
			if (!$options['autoBalanceLimits']) return $default;

			$limit = $cpm * $minutes;

			return max($limit, $default); //maximum balance minimum required
		}


		//! user sessions vw_vmls_sessions
		static function sessionValid($sessionID, $userID)
		{
			//returns true if session is valid

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$sqlS = "SELECT * FROM $table_sessions WHERE id='$sessionID' AND uid='$userID' AND status=0 LIMIT 0,1";
			$session = $wpdb->get_row($sqlS);

			if ($session) return $session;
			else return false;
		}


		static function updateOnlineBroadcaster($sessionID)
		{
			$ztime = time();

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$sqlS = "SELECT * FROM $table_sessions WHERE id='$sessionID' AND broadcaster='1' AND status='0' LIMIT 0,1";
			$session = $wpdb->get_row($sqlS);

			if (!$session) return 'Broadcaster session missing or closed.';

			$sql = "UPDATE `$table_sessions` set edate=$ztime WHERE id ='" . $sessionID . "'";
			$wpdb->query($sql);

			$postID = $session->rid;
			update_post_meta($postID, 'edate', $ztime);


			/*
				$table_private = $wpdb->prefix . "vw_vmls_private";
				//update perfomer private show status (present in private chats)
				$shows =  $wpdb->get_var("SELECT count(id) as no FROM `$table_private` where status='0' and room='" . $session->room . "' and pid='" . $session->uid . "'");
				if ($shows) $shows =1; else $shows = 0;
				update_post_meta($postID, 'privateShow', $shows);
			*/

			VWliveWebcams::billSessions();

		}


		// self::privateSessionUpdate($session, $post, $isPerformer, $privateUID, $options, $end);

		static function privateSessionUpdate($session, $post, $isPerformer, $privateUID, $options, $end = 0)
		{

			//called by app/session control to update private session

			//$end : request to end this session

			//note: if private session is detected, public session is swiched to a free one if paid (from updateOnlineViewer)

			$ztime = time();

			global $wpdb;
			$table_private = $wpdb->prefix . "vw_vmls_private";
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions"; //to update meta


			$sqlEnd = ''; $sqlEndC = '';

			if (!$end) $end = 0;
			else
			{
				$sqlEnd = ', status=1';
				$sqlEndC = "OR status='1'";
			}

			//make sure: detect
			$isPerformer2 = self::isPerformer($session->uid, $post->ID);

			if ($isPerformer2)
			{
				//performer

				//retrieve or create session
				$sqlS = "SELECT * FROM $table_private where rid='" . $post->ID . "' AND pid='" . $session->uid . "' AND cid='$privateUID' AND (status='0' $sqlEndC) ORDER BY status ASC, id DESC";
				$pSession = $wpdb->get_row($sqlS);

				if (!$pSession)
				{
					if ($end) $disconnect = 'No session found to end!';

					$user = get_userdata($privateUID);
					$clientUsername = self::clientName($user, $options);

					$sql="INSERT INTO `$table_private` ( `performer`, `pid`, `rid`, `client`, `cid`, `room`, `psdate`, `pedate`, `status` ) VALUES ( '" .$session->username . "', '" . $session->uid . "', '" .$post->ID . "', '" . $clientUsername . "', '" . $privateUID . "', '" .$post->post_title . "', $ztime, $ztime, 0 )";
					$wpdb->query($sql);
					$pSession = $wpdb->get_row($sqlS);
				}

				//update id and time
				$sdate = $pSession->psdate;
				if (!$sdate) $sdate = $ztime; //first time = start time


				$sql="UPDATE `$table_private` SET pid = " . $session->uid . ", psdate=$sdate, pedate = $ztime $sqlEnd WHERE id = " . $pSession->id;
				$wpdb->query($sql);

				//info
				$timeUsed = $ztime - $sdate;

				$cost = self::performerCost($pSession);
				$balance = self::balance($session->uid);
				$ppvMinInShow =  self::balanceLimit($options['ppvPerformerPPM'], 2,  $options['ppvMinInShow'], $options);

				if ($cost>0) if ( $cost + $ppvMinInShow > $balance) $disconnect = "Not enough funds left for performer to continue session. Minimum extra required: " . $ppvMinInShow . " Session Cost:$cost Balance:$balance";

			}
			else
			{
				//client

				//retrieve or create session
				$sqlS = "SELECT * FROM $table_private where rid='" . $post->ID . "' AND cid='" . $session->uid . "' AND pid='$privateUID' AND (status='0' $sqlEndC) ORDER BY status ASC, id DESC";
				$pSession = $wpdb->get_row($sqlS);

				if (!$pSession)
				{
					if ($end) $disconnect = 'No session found to end!';

					$user = get_userdata($privateUID);
					$performerUsername = self::performerName($user, $options);

					$sql="INSERT INTO `$table_private` ( `rid`, `client`, `cid`, `performer`, `pid`,  `room`, `csdate`, `cedate`, `status` ) VALUES ( '" . $post->ID . "', '" .$session->username . "', '" . $session->uid . "', '$performerUsername', '$privateUID', '" . $post->post_title . "', $ztime , $ztime, 0 )";

					$wpdb->query($sql);
					$pSession = $wpdb->get_row($sqlS);
				}

				//update id and time

				$sdate = $pSession->csdate;
				if (!$sdate) $sdate = $ztime; //first time = start time

				$sql="UPDATE `$table_private` SET cid = " . $session->uid . ", csdate = $sdate, cedate = $ztime $sqlEnd WHERE id = " . $pSession->id;
				$wpdb->query($sql);

				//info
				$timeUsed = $ztime - $sdate;

				$cost = self::clientCost($pSession);
				$balance = self::balance($session->uid);
				$CPM = self::clientCPM($post->post_title, $options, $post->ID);
				$ppvMinInShow =  self::balanceLimit($CPM, 2,  $options['ppvMinInShow'], $options);

				if ($cost>0) if ( $cost + $ppvMinInShow > $balance) $disconnect = __("Not enough funds left for client to continue session. Minimum required: ","ppv-live-webcams") . $ppvMinInShow;

				//estimate pending balance
				$balancePending = self::balance($session->uid, true);
			}

			if ($pSession->status>0) $disconnect = __("Session was already ended.", 'ppv-live-webcams');

			if ($cost>0) $credits_info .=  $cost . htmlspecialchars($options['currency']) . '/';
			$credits_info .=  $balance . htmlspecialchars($options['currency']);

			if ($balancePending) $credits_info .=  ' ('. $balancePending . htmlspecialchars($options['currency']) .')';

			if ($disconnect) self::notificationMessage($disconnect, $session, 0); //send to public where user will be returned
			
			
			//update session meta
			if ($session->meta) if (!is_array($userMeta = unserialize($session->meta))) $userMeta = array();
			$userMeta['privateUpdate'] = $ztime;
			$userMetaS = serialize($userMeta);
			$sql = "UPDATE `$table_sessions` set meta='$userMetaS' WHERE id ='" . $session->id . "'";
			$wpdb->query($sql);


			return $disconnect;
		}


		static function sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1, $clean=1, $options=null, $userID = 0, $postID=0, $ip = '')
		{

			//called by rtmp session control, app ajax
			//return $session vw_vmls_sessions

			//strict = create new if not that type
			//updated = return updated session unless missing (otherwise return old for delta calculations)


			if (!$username) return;
			$ztime = time();

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			if (!$broadcaster)
			{
				//viewer (client)

				if (!$userID)
				{
					$user = get_user_by($options['userName'], $username);
					if ($user) $userID = $user->ID;
				}

				if (!$userID) $userID = 0;

				//supports visitors
				return self::updateOnlineViewer($username, $room, $postID , $type , '', $options, $userID, $strict, $ip, 1 ); //for viewer/client

			}
			else
			{
				//performer
				global $wpdb;
				$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

				$cnd = " AND broadcaster='1'";
				if ($strict) $cnd .= " AND `type`='$type'";
				if ($userID) $cnd .= " AND `uid` = '$userID'"; //if $userID provided, strict check on it

				if (!$userID)
				{
					$user = get_user_by($options['performerName'], $username);
					if ($user) $userID = $user->ID;
				}


				if (!$postID) $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='" .$options['custom_post'] . "' LIMIT 0,1" );

				//online broadcasting session
				$sqlS = "SELECT * FROM $table_sessions where session='$username' and status='0' $cnd ORDER BY edate DESC LIMIT 0,1";
				$session = $wpdb->get_row($sqlS);

				if (!$session)
					$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`, `uid`, `rid`, `ip`,`broadcaster`) VALUES ('$username', '$username', '$room', '', $ztime, $ztime, 0, $type, $userID, $postID, '$ip', $broadcaster)";
				else $sql="UPDATE `$table_sessions` set edate=$ztime, room='$room', username='$username' where id ='".$session->id."'";
				$wpdb->query($sql);


				if ($postID) update_post_meta($postID, 'edate', $ztime);

				//update private shows
				if ($userID)
				{
					self::updatePrivateShow($postID, $userID);
				}

				if ($updated || !$session) $session = $wpdb->get_row($sqlS);

			}

			if ($clean) VWliveWebcams::billSessions();


			return $session;
		}
		
		static function updatePrivateShow($postID, $userID)
		{
					global $wpdb ;
					$table_private = $wpdb->prefix . "vw_vmls_private";

					//update perfomer private show status (present in private chats)
					$shows =  $wpdb->get_var("SELECT count(id) as no FROM `$table_private` where status='0' AND psdate > 0 AND csdate > 0 AND rid='" . $postID . "' and pid='" . $userID . "'");
					if ($shows) $shows =1; else $shows = 0;
					update_post_meta($postID, 'privateShow', $shows);
		}

		//! Watcher Online Status for App, AJAX chat, session control
		static function updateOnlineViewer($username, $room, $postID = 0, $type = 2, $current_user = '', $options ='', $userID = 0, $strict = 1, $ip = '', $returnSession = 0 )
		{
			//this should only be called for viewer (client) not performer!
			//$type: 1 = flash full, 2 = html5 chat, 3 = flash video, 4 = html5 video, 5 = voyeur flash, 6 = voyeur html5
			//7=webrtc performer, 8=webrtc viewer
			//9=external broadcaster, 10=external viewer
			//11 app

			//returns $disconnect string unless $returnSession

			if (!$room && !$postID) return; //no room, no update

			$s = $u = $username;
			$r = $room;
			$ztime = time();

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			if (!$userID) //called by own user web session
				{
				if (!$current_user) $current_user = wp_get_current_user();

				$uid = 0;
				if ($current_user) $uid = $current_user->ID;
			}
			else $uid = $userID;


			global $wpdb ;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			if (!$postID)
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

			//retrieve room info

			$groupCPM = get_post_meta($postID, 'groupCPM', true);
			$performer = get_post_meta($postID, 'performer', true);
			$sessionStart = get_post_meta($postID, 'sessionStart', true);
			$checkin = get_post_meta($postID, 'checkin', true);
			$privateShow = get_post_meta($postID, 'privateShow', true);

			$groupMode =  get_post_meta($postID, 'groupMode', true);
			if (!$groupMode) $groupMode = 'Free';
					
			//special user mode
			$userMode = 'chat';

			$groupParameters =  get_post_meta($postID, 'groupParameters', true);

			//2way
			if ($groupParameters['2way'])
			{
				$mode2way = get_post_meta( $postID, 'mode2way', true );

				if (is_array($mode2way)) //present
					{
					$m2update = false;

					if (array_key_exists($uid,  $mode2way)) //update time and select cpm
						{
						$mode2way[$uid] = $ztime;
						$groupCPM = $groupParameters['cpm2'];
						$m2update = true;
						$userMode = '2way';
					}

					foreach ($mode2way as $key=>$value) if ($ztime - $value > $options['onlineTimeout']) //clean any that went offline
							{
							unset($mode2way[$key]);
							$m2update = true;
						}
					if ($m2update)  update_post_meta( $postID, 'mode2way', $mode2way);

				}
			}

			//voyeur
			//is in voyeur mode (list in room meta)
			if ($groupParameters) if (is_array($groupParameters))
					if (array_key_exists('voyeur', $groupParameters))
						if ($groupParameters['voyeur'])
						{
							$modeVoyeur = get_post_meta( $postID, 'modeVoyeur', true );
							if (!is_array($modeVoyeur)) $modeVoyeur = array();

							if (array_key_exists($uid, $modeVoyeur))
							{
								$isVoyeur = 1;
								$userMode = 'voyeur';
								$groupCPM = $groupParameters['cpmv'];
								$modeVoyeur[$uid] = $ztime;
								$mvupdate = true;
							}

							foreach ($modeVoyeur as $key=>$value)
								if ($ztime - $value > $options['onlineTimeout']) //clean any that went offline
									{
									unset($modeVoyeur[$key]);
									$mvupdate = true;
								}

							if ($mvupdate)  update_post_meta( $postID, 'modeVoyeur', $modeVoyeur); //update voyeur list in room meta

						}


					//room options for this session type
					if ($groupCPM) $rmode = 1;
					else $rmode = 0;

					$roomOptions = array(
						'performer' => $performer,
						'cpm' => $groupCPM,
						'userMode' => $userMode,
						'groupMode' => $groupMode,
						'sessionStart' => $sessionStart,
						'checkin' => $checkin
					);

				$roptions = esc_sql(serialize($roomOptions));

			$redate = get_post_meta($postID, 'edate', true);


			//strict mode
			$cnd = '';
			if ($strict) $cnd = " AND `type`='$type'";


			//if performer is in private: switch to free group session (except for voyeur mode)
			if ($rmode && $privateShow && !$isVoyeur)
			{
				$roomOptions['cpm'] = 0;
				$rmode = 0;

				//close previous paid session if any
				$sqlU = "UPDATE `$table_sessions` SET status='1' WHERE session='$s' AND status='0' AND room='$room' $cnd LIMIT 1";
				$wpdb->query($sqlU);
			}

			//to consider: exit if new group session with different mode was started
			//if ($rmode != $session->rmode) $disconnect = __("Performer started a different room type: current session type ended. Please re-enter!","ppv-live-webcams");

			//create or update session
			//status: 0 current, 1 closed, 2 billed

			$sqlS = "SELECT * FROM `$table_sessions` WHERE session='$s' AND status='0' AND room='$room' $cnd AND rmode='$rmode' LIMIT 1";
			$session = $wpdb->get_row($sqlS);

			if (!$ip) $clientIP = VWliveWebcams::get_ip_address(); //detect ip if not provided ($ip required on session control)
			else $clientIP = $ip;

			if (!$session)
			{

				if ($ztime - $redate >$options['onlineTimeout']) $rsdate = 0; //performer offline
				else $rsdate = $redate; //performer online: mark room start date

				$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `uid`, `room`, `rid`, `roptions`, `rsdate`, `redate`, `rmode`, `message`, `sdate`, `edate`, `status`, `type`, `ip`, `broadcaster`) VALUES ('$s', '$u', '$uid', '$r', '$postID', '$roptions', '$rsdate', '$redate', '$rmode', '$m', '$ztime', '$ztime', 0, $type, '$clientIP', 0)";
				$wpdb->query($sql);

				$session = $wpdb->get_row($sqlS);
			}
			else
			{
				$id = $session->id;

				//performer was offline and came online: update room start time (rsdate)
				if ($session->rsdate == 0 && $redate > $session->sdate) $rsdate = $redate;
				else $rsdate = $session->rsdate; //keep unchanged (0 or start time)

				$sql="UPDATE `$table_sessions` set edate='$ztime', rsdate='$rsdate', redate='$redate', roptions = '$roptions' WHERE id='$id' LIMIT 1";
				$wpdb->query($sql);
			}


			//echo '&onlineDebug='.$sql;



			//check if client banned
			$bans = get_post_meta($postID, 'bans',true);
			if ($bans) if (is_array($bans))
				{

					//clean expired bans
					foreach ($bans as $key=>$ban) if ($ban['expires']<time())
						{
							unset($bans[$key]);
							$bansUpdate =1;
						}
					if ($bansUpdate) update_post_meta($postID, 'bans', $bans);

					foreach ($bans as $ban)
						if ($clientIP == $ban['ip'] || ($uid && $uid == $ban['uid']))
						{
							$disconnect = urlencode( __('You are banned from accessing this room! By', 'ppv-live-webcams') . ' ' . $ban['by'] . '. ');
						}
				}



			//billing and limitations
			if ($groupCPM) //paid group mode
				{

				$cost = VWliveWebcams::clientGroupCost($session, $groupCPM, $sessionStart);
				$balance = VWliveWebcams::balance($uid); //use complete balance to avoid double amount checking

				$ppvMinInShow =  VWliveWebcams::balanceLimit($groupCPM, 2,  $options['ppvMinInShow'], $options);

				if ($cost>0) if ( $cost + $ppvMinInShow > $balance) $disconnect = __("Not enough funds left for client to continue group chat session. Minimum required: ","ppv-live-webcams") . $ppvMinInShow;

					if (!$uid) $disconnect = __("Only registered and logged in users can access paid sessions.","ppv-live-webcams");

			}
			else //free mode limits
				{
				if ($uid) $cnd = "uid='$uid'";
				else
				{
					if (!$clientIP) $clientIP = VWliveWebcams::get_ip_address();
					$cnd = "ip='$clientIP' AND uid='0'";
				}

				$h24 = time() - 86400;
				$sqlC = "SELECT SUM(edate-sdate) FROM `$table_sessions` WHERE $cnd AND sdate > $h24";
				$freeTime = $wpdb->get_var($sqlC);

				if ($uid) if ($freeTime > $options['freeTimeLimit']) $disconnect = __("Free chat daily time limit reached: You can only access paid group rooms today!", "ppv-live-webcams");
					if (!$uid) if ($freeTime > $options['freeTimeLimitVisitor']) $disconnect = __("Free chat daily visitor time limit reached: Register and login for more chat time today!", "ppv-live-webcams");

			}

			if ($returnSession)
			{
				$session = $wpdb->get_row($sqlS = "SELECT * FROM `$table_sessions` WHERE id = '" . $session->id . "'");
				return $session;
			}

			return $disconnect;

		}


		//! AJAX HTML Chat
		function vmls_htmlchat_callback()
		{

			$options = get_option('VWliveWebcamsOptions');
			//output clean
			ob_clean();

			// Handling the supported tasks:

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";
			$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";

			$room = sanitize_file_name($_GET['room']);
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			if (!$postID) throw new Exception('HTML Chat: Room not found: ' . $room);

			//user
			$username = '';
			$user_id = 0;
			$isPerformer = 0;

			if (is_user_logged_in())
			{
				$current_user = wp_get_current_user();

				$isPerformer = VWliveWebcams::isAuthor($postID); //is current user performer?


				if (isset($current_user) )
				{
					$user_id = $current_user->ID;

					$userName =  $options['userName'];
					if (!$userName) $userName='user_nicename';
					if ($current_user->$userName) $username = urlencode(sanitize_file_name($current_user->$userName));
				}
			}else
			{
				if ($_COOKIE['htmlchat_username']) $username = $_COOKIE['htmlchat_username'];
				else
				{
					$username =  'H_' . base_convert(time()%36 * rand(0,36*36),10,36);
					setcookie('htmlchat_username', $username);
				}
			}

			$ztime = time();


			switch($_GET['task']){

				//! tips ajax
			case 'getBalance':

				$balance = 0;
				if (is_user_logged_in()) $balance = VWliveWebcams::balance($current_user->ID, true, $options); //get live balance (preview)

				$response = array(
					'balance' => $balance
				);

				break;


			case 'sendTip':

				$error = '';
				if (!isset($current_user)) $error = 'Login is required to access balance and tip!';

				if ($options['tipCooldown'])
				{
					$lastTip = intval(get_user_meta($current_user->ID, 'vwTipLast', true));
					if ($lastTip + $options['tipCooldown'] > time()) $error = 'Already sent tip recently!';
				}

				$message = sanitize_text_field($_POST['label']);
				$amount = intval($_POST['amount']);
				$note = sanitize_text_field($_POST['note']);
				$sound = sanitize_text_field($_POST['sound']);
				$image = sanitize_text_field($_POST['image']);

				$meta = array();
				$meta['sound'] = $sound;
				$meta['image'] = $image;
				$metaS = serialize($meta);

				if (!$message) $error = 'No message!';

				if ($error)
				{
					$response = array(
						'status'    => 0,
						'insertID'    => 'error',
						'success' => 0,
						'error' => $error
					);

				}
				else
				{
					$message = preg_replace('/([^\s]{12})(?=[^\s])/', '$1'.'<wbr>', $message); //break long words <wbr>:Word Break Opportunity
					$message = "<I>$message</I>"; //mark system message for tip

					$private = 0;

					//msg type: 1 flash, 2 web ext, 3 notification (own)
					$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`, `meta`, `user_id`, `private_uid`) VALUES ('$username', '$room', '$message', $ztime, '2', '$metaS', '$user_id', '$private')";
					$wpdb->query($sql);

					$response = array(
						'status'    => 1,
						'insertID'    => $wpdb->insert_id
					);

					//also update chat log file
					if ($message)
					{

						$message = strip_tags($message,'<p><a><img><font><b><i><u>');

						$message = date("F j, Y, g:i a", $ztime) . " <b>$username</b>: $message";

						//generate same private room folder for both users
						if ($private)
						{
							if ($private > $session) $proom=$session ."_". $private;
							else $proom=$private ."_". $session;
						}

						$dir=$options['uploadsPath'];
						if (!file_exists($dir)) mkdir($dir);

						$dir.="/$room";
						if (!file_exists($dir)) mkdir($dir);

						if ($proom)
						{
							$dir.="/$proom";
							if (!file_exists($dir)) mkdir($dir);
						}

						$day=date("y-M-j",time());

						$dfile = fopen($dir."/Log$day.html","a");
						fputs($dfile,$message."<BR>");
						fclose($dfile);
					}

					//tip

					$balance = VWliveWebcams::balance($current_user->ID, true, $options);

					$response['success'] = 1;
					$response['balancePrevious'] = $balance;
					$response['postID'] = $postID;
					$response['userID'] = $current_user->ID;
					$response['amount'] = $amount;


					if ($amount > $balance)
					{
						$response['success'] = 0;
						$response['error'] = 'Tip amount greater than balance!';
						$response['balance'] = $balance;
					}
					else
					{

						$ztime = time();

						//client cost
						$paid = number_format($amount, 2, '.', '');
						VWliveWebcams::transaction('ppv_tip', $current_user->ID, - $paid, 'Tip for <a href="' . VWliveWebcams::roomURL($room) . '">' . $room.'</a>. (' .$label.')' , $ztime);
						$response['paid'] = $paid;

						//performer earning
						$post = get_post( $postID );
						$received = number_format($amount * $options['tipRatio'], 2, '.', '');
						VWliveWebcams::transaction('ppv_tip_earning', $post->post_author, $received , 'Tip from ' . $username .' ('.$label.')', $ztime);

						//save last tip time
						update_user_meta($current_user->ID, 'vwTipLast', time());

						$response['broadcaster'] = $post->post_author;
						$response['received'] = $received;

						//update balance and report
						$response['balance'] = VWliveWebcams::balance($current_user->ID, true, $options);

					}
				}

				break;


				//htmlchat
			case 'checkLogged':
				$response = array('logged' => false);

				if (isset($current_user) )
				{
					$response['logged'] = true;

					$response['loggedAs'] = array(
						'name'        => $username,
						'avatar'    => get_avatar_url($current_user->ID),
						'userID' => $current_user->ID
					);

				}

				if (!$isPerformer) $disconnected = VWliveWebcams::updateOnlineViewer($username, $room);

				if ($disconnected)
				{
					$response['disconnect'] = $disconnected;
					$response['logged'] = false;
				}

				break;

			case 'submitChat':
				//$response = Chat::submitChat();

				if (!isset($current_user) ) throw new Exception('You are not logged in!');

				$message = sanitize_text_field($_POST['chatText']);
				$message = preg_replace('/([^\s]{12})(?=[^\s])/', '$1'.'<wbr>', $message); //break long words <wbr>:Word Break Opportunity

				$private = 0; //htmlchat only public mode
				$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`, `user_id`, `private_uid`) VALUES ('$username', '$room', '$message', $ztime, '2', '$user_id', '$private')";
				$wpdb->query($sql);

				$response = array(
					'status'    => 1,
					'insertID'    => $wpdb->insert_id
				);

				//also update chat log file
				if ($message)
				{

					$message = strip_tags($message,'<p><a><img><font><b><i><u>');

					$message = date("F j, Y, g:i a", $ztime) . " <b>$username</b>: $message";

					//generate same private room folder for both users
					if ($private)
					{
						if ($private > $session) $proom=$session ."_". $private;
						else $proom=$private ."_". $session;
					}

					$dir=$options['uploadsPath'];
					if (!file_exists($dir)) mkdir($dir);

					$dir.="/$room";
					if (!file_exists($dir)) mkdir($dir);

					if ($proom)
					{
						$dir.="/$proom";
						if (!file_exists($dir)) mkdir($dir);
					}

					$day=date("y-M-j",time());

					$dfile = fopen($dir."/Log$day.html","a");
					fputs($dfile,$message."<BR>");
					fclose($dfile);
				}

				break;

			case 'getUsers':

				//old session cleanup

				//close sessions
				$closeTime = time() - intval($options['ppvCloseAfter']); // > client statusInterval
				$sql="UPDATE `$table_sessions` SET status = 1 WHERE status = 0 AND edate < $closeTime";
				$wpdb->query($sql);


				// $response = Chat::getUsers();

				$users = array();

				//type 5,6 voyeur: do not show
				$sql = "SELECT * FROM `$table_sessions` where room='$room' and status='0' AND type < 5";
				$userRows = $wpdb->get_results($sql);

				if ($wpdb->num_rows>0)
					foreach ($userRows as $userRow)
					{
						$user = [];
						$user['name'] = $userRow->session;

						//avatar
						$uid  = $userRow->user_id;
						if (!$uid)
						{
							$wpUser = get_user_by($userName, $userRow->session);
							if (!$wpUser) $wpUser = get_user_by('login', $chatRow->username);
							$uid = $wpUser->ID;
						}

						$user['avatar'] = get_avatar_url($uid);

						$users [] = $user;
					}
				$response = array(
					'users' => $users,
					'total' => count($userRows)
				);

				break;

			case 'getChats':

				if (!$isPerformer) $disconnect = VWliveWebcams::updateOnlineViewer($username, $room);

				if (!$disconnect)
				{
					//clean old chat logs
					$closeTime = time() - 900; //only keep for 15min
					$sql="DELETE FROM `$table_chatlog` WHERE mdate < $closeTime";
					$wpdb->query($sql);

					//retrieve only messages since user came online
					$sdate = 0;
					if ($session) $sdate = $session->sdate;


					$chats = array();

					$lastID = (int) $_GET['lastID'];

					$cndNotification = "AND (type < 3 OR (type=3 AND user_id='" . $session->uid . "' AND username='" . $session->username. "'))"; //chat message or own notification (type 3)

					$sql = "SELECT * FROM `$table_chatlog` WHERE room='$room' $cndNotification AND private_uid = '0' AND id > $lastID AND mdate > $sdate ORDER BY mdate DESC LIMIT 0,20";
					$sql = "SELECT * FROM ($sql) items ORDER BY mdate ASC";

					$chatRows = $wpdb->get_results($sql);


					if ($wpdb->num_rows>0) foreach ($chatRows as $chatRow)
						{
							$chat = [];

							if ($chatRow->meta)
							{
								$meta = unserialize($chatRow->meta);

								if ($meta['sound']) $chat['sound'] = $meta['sound'];
								if ($meta['image']) $chat['image'] = $meta['image'];

							}

							$chat['id'] = $chatRow->id;
							$chat['author'] = $chatRow->username;
							$chat['text'] = $chatRow->message;

							$chat['time'] =  array(
								'hours'        => gmdate('H',$chatRow->mdate),
								'minutes'    => gmdate('i',$chatRow->mdate)
							);

							//avatar
							$uid  = $chatRow->user_id;
							if (!$uid)
							{
								$wpUser = get_user_by($userName, $chatRow->username);
								if (!$wpUser) $wpUser = get_user_by('login', $chatRow->username);
								$uid = $wpUser->ID;
							}

							$chat['avatar'] = get_avatar_url($uid);

							$chats[] = $chat;
						}

					$response = array('chats' => $chats);
				}
				else
				{
					$response = array('chats' => array(), 'disconnect' => $disconnect);

				}

				break;

			default:
				throw new Exception('HTML Chat: Wrong task');
			}

			echo json_encode($response);

			die();
		}


		//! tools
		static function fixPath($p) {

			//adds ending slash if missing

			//    $p=str_replace('\\','/',trim($p));
			return (substr($p,-1)!='/') ? $p.='/' : $p;
		}


		static function path2stream($path, $withExtension=true, $withPrefix=true)
		{
			$options = get_option( 'VWliveWebcamsOptions' );

			$stream = substr($path, strlen($options['streamsPath']));
			if ($stream[0]=='/') $stream = substr($stream, 1);

			if ($withPrefix)
			{
				$ext = pathinfo($stream, PATHINFO_EXTENSION);
				$prefix = $ext . ':';
			}else $prefix = '';

			if (!file_exists($options['streamsPath'] . '/' . $stream)) return '';
			elseif ($withExtension) return $prefix.$stream;
			else return $prefix.pathinfo($stream, PATHINFO_FILENAME);
		}


		static function stream2path($stream)
		{

			$options = get_option( 'VWliveWebcamsOptions' );

			//mp4:
			if (strstr($stream, ':')) $stream = substr($stream, strpos($stream, ':') + 1);
			$path = $options['streamsPath'] .'/'. $stream;

			return $path;
		}


		static function varSave($path, $var)
		{
			file_put_contents($path, serialize($var));
		}


		static function varLoad($path)
		{
			if (!file_exists($path)) return false;

			return unserialize(file_get_contents($path));
		}


		static function stringSave($path, $var)
		{
			file_put_contents($path, $var);
		}


		static function stringLoad($path)
		{
			if (!file_exists($path)) return false;

			return file_get_contents($path);
		}


		//! Playlist AJAX handler

		static function updatePlaylist($stream, $active = true)
		{
			//updates playlist for channel $stream in global playlist
			if (!$stream) return;

			$options = get_option('VWliveWebcamsOptions');

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);
			$playlistPathGlobal = $uploadsPath . '/playlist_global.txt';
			if (!file_exists($playlistPathGlobal)) VWliveWebcams::varSave($playlistPathGlobal, array());

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);
			$playlistPath = $upath . 'playlist.txt';
			if (!file_exists($playlistPath)) VWliveWebcams::varSave($playlistPath, array());

			$playlistGlobal = VWliveWebcams::varLoad($playlistPathGlobal);
			$playlist = VWliveWebcams::varLoad($playlistPath);

			if ($active) $playlistGlobal[$stream] = $playlist;
			else unset($playlistGlobal[$stream]);

			VWliveWebcams::varSave($playlistPathGlobal, $playlistGlobal);

			VWliveWebcams::updatePlaylistSMIL();
		}


		static function updatePlaylistSMIL()
		{
			$options = get_option('VWliveWebcamsOptions');

			//! update Playlist SMIL
			$streamsPath =VWliveWebcams::fixPath($options['streamsPath']);
			$smilPath = $streamsPath . 'playlist.smil';

			$smilCode .= <<<HTMLCODE
<smil>
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body>

HTMLCODE;

			if ($options['playlists'])
			{

				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$playlistPathGlobal = $uploadsPath . '/playlist_global.txt';
				if (!file_exists($playlistPathGlobal))VWliveWebcams::varSave($playlistPathGlobal, array());
				$playlistGlobal =VWliveWebcams::varLoad($playlistPathGlobal);


				$streams = array_keys($playlistGlobal);
				foreach ($streams as $stream)
					$smilCode .= '<stream name="' . $stream . '"></stream>
				';

				foreach ($streams as $stream)
					foreach ($playlistGlobal[$stream] as $item)
					{
						$vids = 0;

						$smilCodeV = '';
						if ($item['Videos']) if (is_array($item['Videos'])) foreach ($item['Videos'] as $video)
									if (file_exists(VWliveWebcams::stream2path($video['Video'])))
									{
										$smilCodeV .= '
		<video src="'. $video['Video'] . '" start="' . $video['Start'] . '" length="' . $video['Length'] . '"/>';
										$vids++;
									};

							if ($vids)
							{
								$smilCode .= '
        <playlist name="' . $stream . $item['Id'] . '" playOnStream="' . $stream . '" repeat="'. ($item['Repeat']?'true':'false') .'" scheduled="' . $item['Scheduled']. '">';
								$smilCode .= $smilCodeV;
								$smilCode .= '
		</playlist>';
							}
					}
			}
			$smilCode .= <<<HTMLCODE

    </body>
</smil>
HTMLCODE;

			file_put_contents($smilPath, $smilCode);
		}


		static function playlistsTroubleshoot($verbose=false, $save=false)
		{

			$options = get_option('VWliveWebcamsOptions');

			if (!$options['playlists']) return;

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);
			$playlistPathGlobal = $uploadsPath . '/playlist_global.txt';
			if (!file_exists($playlistPathGlobal))VWliveWebcams::varSave($playlistPathGlobal, array());
			$playlistGlobal = VWliveWebcams::varLoad($playlistPathGlobal);

			$streams = array_keys($playlistGlobal);

			foreach ($streams as $stream)
				foreach ($playlistGlobal[$stream] as $item)
				{
					$vids = 0;
					if ($item['Videos']) if (is_array($item['Videos']))
							foreach ($item['Videos'] as $video)
								if (!file_exists(VWliveWebcams::stream2path($video['Video'])))
								{
									if ($verbose) echo '<br>Video missing for '.$stream.' - Stream: '. $video['Video'] . ' Path: ' . VWliveWebcams::stream2path($video['Video']);
								} else $vids++;

							if (!$vids) if ($verbose) echo '<br>No videos found for ' . $stream . ' : playlist will not be added.';

				}
		}


		function vmls_playlist()
		{
			ob_clean();

			$postID = (int) $_GET['webcam'];

			if (!$postID)
			{
				echo "No webcam post ID provided!";
				die;
			}

			$channel = get_post( $postID );
			if (!$channel)
			{
				echo "Webcam post not found!";
				die;
			}

			$current_user = wp_get_current_user();

			//requires owner of performer
			if ( !VWliveWebcams::isAuthor($postID) )
			{
				echo "Access not permitted (different webcam owner)!";
				die;
			}

			$stream = sanitize_file_name($channel->post_title);

			$options = get_option('VWliveWebcamsOptions');

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);

			$playlistPath = $upath . 'playlist.txt';

			if (!file_exists($playlistPath)) VWliveWebcams::varSave($playlistPath, array());

			switch ($_GET['task'])
			{
			case 'list':
				$rows = VWliveWebcams::varLoad($playlistPath);

				//sort rows by order
				if (count($rows))
				{
					//sort
					function cmp_by_order($a, $b) {

						if ($a['Order'] == $b['Order']) return 0;
						return ($a['Order'] < $b['Order']) ? -1 : 1;
					}


					usort($rows,  'cmp_by_order'); //sort

					//update Ids to match keys (order)
					$updated = 0;
					foreach ($rows as $key => $value)
						if ($rows[$key]['Id'] != $key)
						{
							$rows[$key]['Id'] = $key;
							$updated = 1;
						}
					if ($updated) VWliveWebcams::varSave($playlistPath, $rows);

				}

				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['Records'] = $rows;
				print json_encode($jTableResult);

				break;

			case 'videolist':
				$ItemId = (int) $_GET['item'];
				$jTableResult = array();

				$playlist = VWliveWebcams::varLoad($playlistPath);

				if ($schedule = $playlist[$ItemId])
				{
					if (!$schedule['Videos']) $schedule['Videos'] = array();

					//sort videos



					//sort rows by order
					if (count($schedule['Videos']))
					{

						//sort
						function cmp_by_order($a, $b) {

							if ($a['Order'] == $b['Order']) return 0;
							return ($a['Order'] < $b['Order']) ? -1 : 1;
						}


						usort($schedule['Videos'],  'cmp_by_order'); //sort

						//update Ids to match keys (order)
						$updated = 0;
						foreach ($schedule['Videos'] as $key => $value)
							if ($schedule['Videos'][$key]['Id'] != $key)
							{
								$schedule['Videos'][$key]['Id'] = $key;
								$updated = 1;
							}

						$playlist[$ItemId] = $schedule;
						if ($updated) VWliveWebcams::varSave($playlistPath, $playlist);

					}

					$jTableResult['Records'] = $schedule['Videos'];
					$jTableResult['Result'] = "OK";
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				print json_encode($jTableResult);
				break;

			case 'videoupdate':
				//delete then add new

				$playlist = VWliveWebcams::varLoad($playlistPath);
				$ItemId = (int) $_POST['ItemId'];
				$Id = (int) $_POST['Id'];

				$jTableResult = array();
				if ($playlist[$ItemId])
				{

					//find and remove record with that Id
					foreach ($playlist[$ItemId]['Videos'] as $key => $value)
						if ($value['Id'] == $Id)
						{
							unset($playlist[$ItemId]['Videos'][$key]);
							break;
						}

					VWliveWebcams::varSave($playlistPath,$playlist);
				}

			case 'videoadd':
				$playlist = VWliveWebcams::varLoad($playlistPath);
				$ItemId = (int) $_POST['ItemId'];

				$jTableResult = array();
				if ($schedule = $playlist[$ItemId])
				{
					if (!$schedule['Videos']) $schedule['Videos'] = array();

					$maxOrder = 0; $maxId = 0;
					foreach ($schedule['Videos'] as $item)
					{
						if ($item['Order'] > $maxOrder) $maxOrder = $item['Order'];
						if ($item['Id'] > $maxId) $maxId = $item['Id'];
					}

					$item = array();
					$item['Video'] = sanitize_text_field($_POST['Video']);
					$item['Id'] = (int) $_POST['Id'];
					$item['Order'] = (int) $_POST['Order'];
					$item['Start'] = (int) $_POST['Start'];
					$item['Length'] = (int) $_POST['Length'];

					if (!$item['Order']) $item['Order'] = $maxOrder + 1;
					if (!$item['Id']) $item['Id'] = $maxId + 1;

					$playlist[$ItemId]['Videos'][] = $item;

					VWliveWebcams::varSave($playlistPath,$playlist);

					$jTableResult['Result'] = "OK";
					$jTableResult['Record'] = $item;
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				//Return result to jTable
				print json_encode($jTableResult);

				break;

			case 'videoremove':
				$playlist = VWliveWebcams::varLoad($playlistPath);
				$ItemId = (int) $_GET['item'];
				$Id = (int) $_POST['Id'];

				$jTableResult = array();
				if ($schedule = $playlist[$ItemId])
				{

					//find and remove record with that Id
					foreach ($playlist[$ItemId]['Videos'] as $key => $value)
						if ($value['Id'] == $Id)
						{
							unset($playlist[$ItemId]['Videos'][$key]);
							break;
						}

					VWliveWebcams::varSave($playlistPath,$playlist);

					$jTableResult['Result'] = "OK";
					$jTableResult['Remaining'] = $playlist[$ItemId]['Videos'];
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				//Return result to jTable
				print json_encode($jTableResult);

				break;

			case 'source':

				//error_reporting(E_ALL);
				//ini_set('display_errors', 'On');

				//retrieve videos owned by user (from all channels)


				if (is_plugin_active('video-share-vod/video-share-vod.php'))
				{
					$optionsVSV = get_option('VWvideoShareOptions');
					$custom_post_video = $optionsVSV['custom_post'];
					if (!$custom_post_video) $custom_post_video = 'video';
				} else $custom_post_video = 'video';

				//query
				$args=array(
					'post_type' =>  $custom_post_video,
					'author'        =>  $current_user->ID,
					'orderby'       =>  'post_date',
					'order'            => 'DESC',
				);

				$postslist = get_posts( $args );
				$rows = array();

				$jTableResult = array();

				if (count($postslist)>0)
				{
					foreach ( $postslist as $item )
					{
						$row = array();
						$row['DisplayText'] = $item->post_title;

						$video_id = $item->ID;

						//retrieve video stream
						$streamPath = '';
						$videoPath = get_post_meta($video_id, 'video-source-file', true);
						$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

						//use conversion if available
						$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
						if ($videoAdaptive) $videoAlts = $videoAdaptive;
						else $videoAlts = array();

						foreach (array('high', 'mobile') as $frm)
							if (array_key_exists($frm, $videoAlts))
								if ($alt = $videoAlts[$frm])
									if (file_exists($alt['file']))
									{
										$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
										$streamPath = VWliveWebcams::path2stream($alt['file']);
										break;
									};

							//user original
							if (!$streamPath)
								if (in_array($ext, array('flv','mp4','m4v')))
								{
									//use source if compatible
									$streamPath = VWliveWebcams::path2stream($videoPath);
								}

							$row['Value'] = $streamPath;
						$rows[] = $row;
					}

					$jTableResult['Result'] = "OK";
					$jTableResult['Options'] = $rows;
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "No video posts (type:$custom_post_video) found for current user. Add some videos first!";
				}

				//Return result to jTable

				print json_encode($jTableResult);

				break;

			case 'update':
				//delete then create new
				$Id = (int) $_POST['Id'];

				$playlist = VWliveWebcams::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				foreach ($playlist as $key => $value)
					if ($value['Id'] == $Id)
					{
						unset($playlist[$key]);
						break;
					}

				VWliveWebcams::varSave($playlistPath,$playlist);

			case 'create':

				$playlist = VWliveWebcams::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				$maxOrder = 0; $maxId = 0;
				foreach ($playlist as $item)
				{
					if ($item['Order'] > $maxOrder) $maxOrder = $item['Order'];
					if ($item['Id'] > $maxId) $maxId = $item['Id'];
				}

				$item = array();
				$item['Id'] = (int) $_POST['Id'];
				$item['Video'] = sanitize_text_field($_POST['Video']);
				$item['Repeat'] = (int) $_POST['Repeat'];
				$item['Scheduled'] = sanitize_text_field($_POST['Scheduled']);
				$item['Order'] = (int) $_POST['Order'];
				if (!$item['Order']) $item['Order'] = $maxOrder + 1;
				if (!$item['Id']) $item['Id'] = $maxId + 1;
				if (!$item['Scheduled']) $item['Scheduled']  = date('Y-m-j h:i:s');

				$playlist[$item['Id']] = $item;

				VWliveWebcams::varSave($playlistPath, $playlist);

				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['Record'] = $item;
				print json_encode($jTableResult);
				break;

			case 'delete':
				$Id = (int) $_POST['Id'];

				$playlist = VWliveWebcams::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				foreach ($playlist as $key => $value)
					if ($value['Id'] == $Id)
					{
						unset($playlist[$key]);
						break;
					}

				VWliveWebcams::varSave($playlistPath, $playlist);

				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				print json_encode($jTableResult);
				break;

			default:
				echo 'Action not supported!';
			}

			die;

		}



		static function webcamThumbSrc($postID, $name, $options, $age='')
		{
			//returns webcam thumbnail url: from snapshot or picture as configured

			$snapshot ='';
			$showImage=get_post_meta( $postID, 'showImage', true );

			if ($showImage == 'picture')
			{
				//get post thumb
				$attach_id = get_post_thumbnail_id($postID);
				if ($attach_id) $thumbFilename = get_attached_file($attach_id);
			}

			//no thumb? get live snapshot thumb
			if (!$thumbFilename || $showImage == 'snapshot' || !$showImage)
			{
				//$debug .= 'PostThumbNotFoundId:' . $attach_id .'-'. $thumbFilename;

				$dir = $options['uploadsPath']. "/_thumbs";
				$thumbFilename = "$dir/" . $name . ".jpg";
			}


			$noCache = '';
			if ($age == __('LIVE', 'ppv-live-webcams')) $noCache = '?'.((time()/10)%100);

			if (file_exists($thumbFilename)) $snapshot = VWliveWebcams::path2url($thumbFilename) . $noCache;
			else
			{
				$snapshot = plugin_dir_url(__FILE__). 'no-picture.png';
				//$debug .= 'ThumbNotFoundPath:' . $thumbFilename;
			}

			return $snapshot;
		}


		static function webcamThumbCode($postID, $name, $options, $snapshot, $optionsVSV, $isMobile, $showBig, $previewMuted)
		{
			if ($showBig) $ci='2'; else $ci='';

			$previewCode = '<IMG src="' . $snapshot . '" class="videowhisperPreview'.$ci.'">';

			if (!$options['videosharevod'] || $isMobile) return $previewCode;

			$video_teaser = get_post_meta($postID, 'video_teaser', true);
			if (!$video_teaser) return $previewCode;

			$previewVideo = '';
			$videoAdaptive = get_post_meta($video_teaser, 'video-adaptive', true);

			if (is_array($videoAdaptive))
				if (array_key_exists('preview', $videoAdaptive))
					if ($videoAdaptive['preview'])
						if ($videoAdaptive['preview']['file'])
							if (file_exists($videoAdaptive['preview']['file']))
								$previewVideo = $videoAdaptive['preview']['file'];

							$previewCode = '<video class="videowhisperPreview'.$ci.'" ' . $previewMuted . ' poster="' . $snapshot . '" preload="none"><source src="' . VWliveWebcams::path2url($previewVideo) . '" type="video/mp4">' . $previewCode . '</video>';

						return $previewCode ;
		}



		static function label($key, $default, $options)
		{
			if (!$options) $options = get_option('VWliveWebcamsOptions');

			if (!$options['labels']) return $default;
			if (!is_array($options['labels'])) return $default;
			if (!array_key_exists($key, $options['labels'])) return $default;

			return $options['labels'][$key];
		}


		//! AJAX Webcams List
		function vmls_cams_callback()
		{
			//ajax called

			//cam meta:
			//edate s
			//viewers n
			//maxViewers n
			//maxDate s
			//hasSnapshot 1
			//privateShow 0

			$options = get_option('VWliveWebcamsOptions');

			$isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );

			//output clean (clear 0)
			ob_clean();

			//cache : do not generate more often than 14s (client refresh each 15s)
			//client still updates but less load on high volume (many users online)

			$cacheQuery = $_SERVER['QUERY_STRING'].'&mob='.$isMobile;
			$cacheKey = sha1($cacheQuery);

			//$cachePath
			$cachePath = $options['uploadsPath'];
			if (!file_exists($cachePath)) mkdir($cachePath);
			$cachePath .= "/_cache/";
			if (!file_exists($cachePath)) mkdir($cachePath);
			$cachePath .= $cacheKey;


			if (!VWliveWebcams::timeTo('cl_' . $cacheKey, 9, $options))
				if (file_exists($cachePath))
				{
					echo VWliveWebcams::stringLoad($cachePath);
					exit;
				}

			//
			if (!$isMobile && $options['videosharevod'])  $optionsVSV = get_option('VWvideoShareOptions');

			//safari requires muted for autoplay
			$isSafari = (bool) (strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') && strpos($_SERVER['HTTP_USER_AGENT'], 'Safari'));
			if ($isSafari) $previewMuted = 'muted'; else $previewMuted ='';

			$isLoggedin = is_user_logged_in();

			$debugMode = $options['debugMode'];

			//widget id
			$id = sanitize_file_name($_GET['id']);

			//pagination
			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$page = (int) $_GET['p'];
			$offset = $page * $perPage;

			$perRow = (int) $_GET['pr'];

			//admin side
			$ban = (int) $_GET['ban'];

			//
			$category = (int) $_GET['cat'];
			$pstatus =  sanitize_file_name($_GET['st']);

			//order
			$order_by = sanitize_file_name($_GET['ob']);
			if (!$order_by) $order_by = 'default';

			//options
			$selectCategory = (int) $_GET['sc'];
			$selectOrder = (int) $_GET['so'];
			$selectPage = (int) $_GET['sp'];

			$selectStatus = (int) $_GET['ss'];

			$selectName = (int) $_GET['sn'];
			$selectTags = (int) $_GET['sg'];

			//studio
			$studioID = (int) $_GET['studioID'];

			//tags,name search
			$tags = sanitize_text_field($_GET['tags']);
			$name = sanitize_file_name($_GET['name']);
			if ($name == 'undefined') $name = '';
			if ($tags == 'undefined') $tags = '';

			//layout
			$layout = sanitize_file_name($_GET['layout']);
			if (!$layout) $layout = $options['layoutDefault'];
			if (!$layout) $layout = 'grid';

			//thumbs dir
			$dir = $options['uploadsPath']. "/_thumbs";

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_cams&pp=' . $perPage .  '&pr=' . $perRow . '&ss=' . $selectStatus . '&sc=' . $selectCategory . '&so=' . $selectOrder . '&sn=' . $selectName .  '&sg=' . $selectTags. '&sp=' . $selectPage .  '&id=' . $id . '&tags=' . urlencode($tags) . '&name=' . urlencode($name);

			if ($atts['studioID']) $ajaxurl .= '&studioID=' . $studioID;
			if ($ban) $ajaxurl .= '&ban=' . $ban; //admin side



			$ajaxurlP = $ajaxurl . '&p=' . $page . '&layout=' . $layout;
			$ajaxurlC = $ajaxurl . '&cat=' . $category . '&layout=' . $layout;
			$ajaxurlO = $ajaxurl . '&ob='. $order_by . '&layout=' . $layout;
			$ajaxurlCO = $ajaxurl . '&cat=' . $category . '&ob='.$order_by . '&layout=' . $layout;
			$ajaxurlCOP = $ajaxurl . '&cat=' . $category . '&ob='.$order_by . '&p='.$page; //layout select

			//$htmlCode .= '<div class="videowhisperListOptions">';


			//! header option controls

			$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' tiny equal width form"><div class="inline fields">';

			if ($selectStatus)
			{
				$htmlCode .= '<div class="field"><select class="ui dropdown" id="pstatus' . $id . '" name="pstatus' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlCO .'&st=\'+ this.value; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Filtering Webcams...</div>\')">';

				$htmlCode .= '<option value="all"' . ($pstatus == ''?' selected':'') . '>' . VWliveWebcams::label('All',__('All', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="paid"' . ($pstatus == 'paid'?' selected':'') . '>' . VWliveWebcams::label('Paid', __('All, Paid', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="free"' . ($pstatus == 'free'?' selected':'') . '>' . VWliveWebcams::label('Free', __('All, Free', 'ppv-live-webcams'), $options) . '</option>';


				$htmlCode .= '<option value="online"' . ($pstatus == 'online'?' selected':'') . '>' . VWliveWebcams::label('Online', __('Online', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="public"' . ($pstatus == 'public'?' selected':'') . '>' . VWliveWebcams::label('Available', __('Online, Available', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="available_paid"' . ($pstatus == 'available_paid'?' selected':'') . '>' . VWliveWebcams::label('Available Paid', __('Available, Paid', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="available_free"' . ($pstatus == 'available_free'?' selected':'') . '>' . VWliveWebcams::label('Available Free', __('Available, Free', 'ppv-live-webcams'), $options) . '</option>';


				$htmlCode .= '<option value="private"' . ($pstatus == 'private'?' selected':'') . '>' . VWliveWebcams::label('In Private', __('Online, In Private', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '<option value="offline"' . ($pstatus == 'offline'?' selected':'') . '>' . VWliveWebcams::label('Offline', __('Offline', 'ppv-live-webcams'), $options) . '</option>';

				$htmlCode .= '</select></div>';

			}

			if ($selectCategory)
			{
				$htmlCode .= '<div class="field">' . wp_dropdown_categories('echo=0&name=category' . $id . '&hide_empty=1&class=ui+dropdown&show_option_all=' . VWliveWebcams::label('All Categories',__('All Categories', 'ppv-live-webcams'), $options) . '&selected=' . $category).'</div>';
				$htmlCode .= '<script>var category' . $id . ' = document.getElementById("category' . $id . '"); 			category' . $id . '.onchange = function(){aurl' . $id . '=\'' . $ajaxurlO.'&cat=\'+ this.value; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading category...</div>\')}
			</script>';
			}

			if ($selectOrder)
			{
				$htmlCode .= '<div class="field"><select class="ui dropdown" id="order_by' . $id . '" name="order_by' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlC.'&ob=\'+ this.value; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Ordering webcams...</div>\')">';
				$htmlCode .= '<option value="">' . __('Order By', 'ppv-live-webcams') . ':</option>';

				$htmlCode .= '<option value="default"' . ($order_by == 'default'?' selected':'') . '>' . __('Default Order', 'ppv-live-webcams') . '</option>';

				$htmlCode .= '<option value="viewers"' . ($order_by == 'viewers'?' selected':'') . '>' . __('Current Viewers', 'ppv-live-webcams') . '</option>';

				$htmlCode .= '<option value="edate"' . ($order_by == 'edate'?' selected':'') . '>' . __('Broadcast Recently', 'ppv-live-webcams') . '</option>';
				$htmlCode .= '<option value="post_date"' . ($order_by == 'post_date'?' selected':'') . '>' . __('Created Recently', 'ppv-live-webcams') . '</option>';

				$htmlCode .= '<option value="maxViewers"' . ($order_by == 'maxViewers'?' selected':'') . '>' . __('Maximum Viewers', 'ppv-live-webcams') . '</option>';

				$htmlCode .= '<option value="rand"' . ($order_by == 'rand'?' selected':'') . '>' . __('Random', 'ppv-live-webcams') . '</option>';

				if ($options['rateStarReview'])
				{

					$htmlCode .= '<option value="rateStarReview_rating"' . ($order_by == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'ppv-live-webcams') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingNumber"' . ($order_by == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Ratings Number', 'ppv-live-webcams') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingPoints"' . ($order_by == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'ppv-live-webcams') . '</option>';

					if ($category)
					{
						$htmlCode .= '<option value="rateStarReview_rating_category' . $category . '"' . ($order_by == 'rateStarReview_rating_category' . $category ?' selected':'') . '>' . __('Rating', 'ppv-live-webcams') . ' ' . __('in Category', 'ppv-live-webcams') . '</option>';
						$htmlCode .= '<option value="rateStarReview_ratingNumber_category' . $category . '"' . ($order_by == 'rateStarReview_ratingNumber_category' . $category ?' selected':'') . '>' . __('Ratings Number', 'ppv-live-webcams'). ' ' . __('in Category', 'ppv-live-webcams') . '</option>';
						$htmlCode .= '<option value="rateStarReview_ratingPoints_category' . $category . '"' . ($order_by == 'rateStarReview_ratingPoints_category' . $category?' selected':'') . '>' . __('Rate Popularity', 'ppv-live-webcams'). ' ' . __('in Category', 'ppv-live-webcams') . '</option>';
					}

				}

				$htmlCode .= '</select></div>';

			}



			if ($selectTags || $selectName)
			{
				$htmlCode .= '<div class="field"></div>'; //separator

				if ($selectTags)
				{
					$htmlCode .= '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __('Tags', 'ppv-live-webcams')  . '" value="' .htmlspecialchars($tags). '">
					</div></div>';
				}

				if ($selectName)
				{
					$htmlCode .= '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __('Name', 'ppv-live-webcams')  . '" value="' .htmlspecialchars($name). '">
  <div class="ui left corner label">
    <i class="asterisk icon"></i>
  </div>
					</div></div>';
				}

				//search button
				$htmlCode .= '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui fluid icon button" type="submit" name="submit" id="submit" value="' . __('Search', 'ppv-live-webcams') . '" onclick="aurl' . $id . '=\'' . $ajaxurlCO .'&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching webcams...</div>\')"><i class="search icon"></i></button></div>';


			}

			$htmlCode .= '</div></div>';


			//! meta query
			$meta_query = array(
				'relation'    => 'AND',
				'snapshot' => array(
					'key' => 'hasSnapshot',
					'value' => '1'
				),
				'vw_featured'    => array(
					'key'     => 'vw_featured',
					'compare' => 'EXISTS',
				),
				'edate'    => array(
					'key'     => 'edate',
					'compare' => 'EXISTS',
				),
			);

			//hide private rooms
			$meta_query['room_private'] = array(
				'relation' => 'OR',
				array(
					'key' => 'room_private',
					'value' => 'false',
				),
				array(
					'key' => 'room_private',
					'compare' => 'NOT EXISTS',
				)
			);


			//location filter
			$clientLocation = VWliveWebcams::detectLocation('all'); //array

			if ($clientLocation) if (is_array($clientLocation)) if (!empty($clientLocation))
					{
						$meta_query['location'] = array(
							'relation' => 'OR',
							array(
								'key' => 'vw_banCountries',
								'value' => $clientLocation,
								'compare' => 'NOT IN',
							),
							array(
								'key' => 'vw_banCountries',
								'compare' => 'NOT EXISTS',
							)
						);

					}

				//! query args
				$args=array(
					'post_type' => $options['custom_post'],
					'post_status' => 'publish',
					'posts_per_page' => $perPage,
					'offset'           => $offset,
					'order'            => 'DESC',
					'meta_query' => $meta_query
				);

			if (!$pstatus) $pstatus = '';

			if ($studioID)  $args['meta_query'][] = array('key' => 'studioID', 'value' => $studioID);

			switch ($pstatus)
			{
			case 'free':
				$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');
				$args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0');
				break;
			case 'paid':
				$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');
				$args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0', 'compare' => '>');
				break;
			case 'available_free':
				$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');
				$args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');
				$args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0');
				break;
			case 'available_paid':
				$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');
				$args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');
				$args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0', 'compare' => '>');
				break;

			case 'private':
				$args['meta_query']['private'] = array('key' => 'privateShow', 'value' => '1');
				$args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');
				break;

			case 'online':
				$args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');
				break;

			case 'public':
				$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');
				$args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');
				break;

			case 'offline':
				$args['meta_query']['offline'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '<');
				break;
			}

			switch ($order_by)
			{
			case 'default':
				$args['orderby'] =  array(
					'vw_featured' => 'DESC',
					'edate' => 'DESC',
				);
				break;

			case 'post_date':
				$args['orderby'] = 'post_date';
				break;

			case 'rand':
				$args['orderby'] = 'rand';
				break;

			default:
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = $order_by;
				break;
			}

			if ($category)  $args['category'] = $category;

			if ($tags)
			{
				$tagList = explode(',', $tags);
				foreach ($tagList as $key=>$value) $tagList[$key] = trim($tagList[$key] );

				$args['tax_query'] = array(
					array(
						'taxonomy'  => 'post_tag',
						'field'     => 'slug',
						'operator' => 'AND',
						'terms'     => $tagList
					)
				);
			}

			if ($name)
			{
				$args['s'] = $name;
			}

			$postslist = get_posts( $args );


			$htmlCode .= '<div class="">';

			//! list cams
			if (count($postslist)>0)
			{

				if ($layout == 'list') $listingTemplate = $listingTemplate2 = $listingTemplate1 = html_entity_decode(stripslashes($options['listingTemplateList']));
				else
				{
					$listingTemplate1 = html_entity_decode(stripslashes($options['listingTemplate']));
					$listingTemplate2 = html_entity_decode(stripslashes($options['listingTemplate2']));
					$listingTemplate = $listingTemplate2;
				}


				$templateSearch = array('#name#', '#age#', '#clientCPM#', '#roomBrief#', '#roomTags#', '#url#', '#snapshot#','#thumbWidth#', '#thumbHeight#', '#banLink#', '#groupMode#', '#groupCPM#', '#performers#', '#currency#', '#preview#', '#enter#', '#paidSessionsPrivate#', '#paidSessionsGroup#', '#rating#', '#performerStatus#', '#roomCategory#', '#featuredReview#', '#roomDescription#', '#featured#');
				//$templateReplace  = array($name, $age, $clientCPM, $roomBrief, $roomTags, $url, $snapshot, $thumbWidth, $thumbHeight, $banLink);

				$k = 0;
				foreach ( $postslist as $item )
				{
					if ($layout == 'grid')
						if ($perRow) if ($k) if ($k % $perRow == 0) $htmlCode .= '<br>';

								$k++;

							$listingTemplate = $listingTemplate1;


						$showBig = 0;

					if ($options['listingBig'])
					{
						if ($options['listingBig']==1 && $k == 1 ) $listingTemplate = $showBig=1;
						if ($options['listingBig']>1) if ($k % $options['listingBig'] == 1) $showBig=1;

							if ($showBig) $listingTemplate = $listingTemplate2;
					}
					//else $listingTemplate = $listingTemplate1;


					$k++;

					$name = sanitize_file_name($item->post_title);

					if ($ban) $banLink = '<a class = "button" href="admin.php?page=live-webcams&ban=' . urlencode( $name ) . '">' . __('Ban This Webcam', 'ppv-live-webcams') . '</a><br>';

					$edate =  get_post_meta($item->ID, 'edate', true);
					$age = VWliveWebcams::format_age(time() -  $edate);

					$privateShow = get_post_meta($item->ID, 'privateShow', true);

					//performer status
					if (time() -  $edate > 40) $performerStatus = 'offline';
					elseif ($privateShow) $performerStatus = 'private';
					else $performerStatus = 'public';

					$clientCPM = VWliveWebcams::clientCPM($name, $options, $item->ID);


					//ip camera or playlist : update snapshot when listing
					if (get_post_meta( $item->ID, 'vw_ipCamera', true ) || (get_post_meta( $item->ID, 'vw_playlistActive', true ) && $options['playlists']))
					{
						VWliveWebcams::streamSnapshot($name, true, $item->ID);
						//$htmlCode .= 'Updating IP Cam Snapshot: ' . $stream;
					}

					switch ($options['webcamLink']) //custom Enter links
						{
					case '':
					case 'room':
						$url = VWliveWebcams::roomURL($name);
						break;

					case 'custom':
						$url = get_post_meta( $item->ID, 'customRoomLink', true );
						if (!$url)
						{
							$url = $options['webcamLinkDefault'] . $name;
							update_post_meta( $item->ID, 'customRoomLink', $url );
						}
						break;

					case 'auto':
						$url = get_post_meta( $item->ID, 'customRoomLink', true );
						if (!$url) $url = VWliveWebcams::roomURL($name);
						break;

					}

					$thumbWidth = $options['thumbWidth'];
					$thumbHeight = $options['thumbHeight'];

					$snapshot = VWliveWebcams::webcamThumbSrc($item->ID, $name, $options, $age);

					$previewCode = VWliveWebcams::webcamThumbCode($item->ID, $name, $options, $snapshot, $optionsVSV, $isMobile, $showBig, $previewMuted );

					$roomBrief =  get_post_meta($item->ID, 'vw_roomBrief', true);

					$tags = wp_get_post_tags($item->ID, array( 'fields' => 'names' ));
					$roomTags = '';
					if ( ! empty( $tags ) ) if ( ! is_wp_error( $tags ) )
							foreach( $tags as $tag )  $roomTags .= ($roomTags?', ':'') . $tag;

							$roomLabel =  get_post_meta($item->ID, 'vw_roomLabel', true);
						if (!$roomLabel) $roomLabel = $name;

						$roomCategory = '';
					$cats = wp_get_post_categories( $item->ID, array('fields'=>'names'));
					if (!empty($cats)) foreach ($cats as $category) $roomCategory .= ($roomCategory?', ':'') . $category;

						$roomDescription = $item ->post_content;

					$groupMode =  get_post_meta($item->ID, 'groupMode', true);
					if (!$groupMode) $groupMode = 'Free';

					$groupCPM =  get_post_meta($item->ID, 'groupCPM', true);
					if (!$groupCPM) $groupCPM = 0;

					//extras
					$groupParameters =  get_post_meta($item->ID, 'groupParameters', true);

					//sales counters
					$paidSessionsPrivate =  get_post_meta($item->ID, 'paidSessionsPrivate', true);
					$paidSessionsGroup =  get_post_meta($item->ID, 'paidSessionsGroup', true);

					$ratingCode = '';
					if ($options['rateStarReview'])
					{
						$rating = get_post_meta($item->ID, 'rateStarReview_rating', true);
						$max = 5;
						if ($rating > 0) $ratingCode = '<div class="ui star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
					}

					$featuredReview = '<div class="ui stackable cards">' . do_shortcode('[videowhisper_review_featured post_id="' . $item->ID . '"]' ) . '</div>';

					//featured webcam
					$featuredCode = ''; 
					$vw_featured = get_post_meta($item->ID, 'vw_featured', true);
					if ($vw_featured) $featuredCode = '<div class="videowhisperWrap"><span class="videowhisperFeatured">' . __('Featured', 'ppv-live-webcams') . ($vw_featured>1?' x' . $vw_featured:'') . '</span></div>';

					//#performers#
					$checkin = get_post_meta($item->ID, 'checkin', true);
					if ($checkin) if (!is_array($checkin))  $checkin = array($checkin);

						$performersCode = '';

					if ($checkin) foreach ($checkin as $performerID) $performersCode .= ($performersCode ?',' :'' ) .  VWliveWebcams::performerLink($performerID, $options);

						//#enter#
						$enterCode = '<div class="videowhisperEnterDropdown"><a href="'.$url.'"><button class="videowhisperEnterButton">' . __('Enter', 'ppv-live-webcams') . '</button></a><div class="videowhisperEnterDropdown-content">';


					if (!$isLoggedin){

						if (!$groupCPM) $enterCode .= '<a href="'.$url.'">' .$groupMode. '</a>';
						$enterCode .= '<a href="'.wp_login_url().'">' . __('Login for More', 'ppv-live-webcams') . ' ...'. '</a>';

					}
					else
					{
						$enterCode .= '<a href="'.$url.'">' .$groupMode. ($groupCPM?' ' .$groupCPM . $options['currencypm']:''). '</a>';

						//special modes
						$enterCode .= '<a href="'  .add_query_arg('vwsm', 'private', $url) . '">' .__('Private', 'ppv-live-webcams') . ' ' . ($clientCPM ? $clientCPM . $options['currencypm'] : '').'</a>';


						if (is_array($groupParameters))
							if ($groupParameters['2way']>0)
							{
								$c2way = 0;

								$mode2way = get_post_meta( $postID, 'mode2way', true );
								if (is_array($mode2way)) $c2way = count($mode2way);

								$enterCode .= '<a href="'.add_query_arg('vwsm', '2way', $url).'">' .__('2 Way', 'ppv-live-webcams'). ($groupParameters['cpm2']?' ' .$groupParameters['cpm2'] . $options['currencypm']:''). ' ('.$c2way.'/' . $groupParameters['2way'].')</a>';
							}

						if ($options['voyeurAvailable'] == 'always' || ($options['voyeurAvailable'] == 'private' && $privateShow) || ($options['voyeurAvailable'] == 'public' && !$privateShow))
							if (is_array($groupParameters))
								if (array_key_exists('voyeur', $groupParameters))
									if ($groupParameters['voyeur'])
									{
										$enterCode .= '<a href="'.add_query_arg('vwsm', 'voyeur', $url).'">' .__('Voyeur', 'ppv-live-webcams'). ($groupParameters['cpmv']?' ' .$groupParameters['cpmv'] . $options['currencypm']:'').'</a>';

									}

					}
					
					$enterCode .= '<a href="'.add_query_arg('view', 'content', $url).'">' . __('Profile', 'ppv-live-webcams') .'</a>';

					$enterCode .= '</div></div>';
					
					

					//replace
					$templateReplace  = array($roomLabel, $age, $clientCPM, $roomBrief, $roomTags, $url, $snapshot, $thumbWidth, $thumbHeight, $banLink, $groupMode, $groupCPM, $performersCode, $options['currency'], $previewCode, $enterCode, $paidSessionsPrivate, $paidSessionsGroup, $ratingCode, $performerStatus, $roomCategory, $featuredReview, $roomDescription, $featuredCode);

					if ($debug) $debugCode = '<!--' . $debug . '-->';
					$htmlCode .=  str_replace($templateSearch, $templateReplace, $listingTemplate) . $debugCode;
				}
			}
			else
			{
				$htmlCode .=  "No webcams match current selection.";
				if ($debugMode)
				{
					$htmlCode .=  '<!--';
					var_dump($args);
					$htmlCode .=  '-->';
				}
			}

			$htmlCode .= '</div>'; //end

			//footer start
			$htmlCode .= '<br style="clear:both"><br> <div class="ui' . $options['interfaceClass'] .' equal width grid">';


			//pagination
			if ($selectPage)
			{
				$htmlCode .= '<div class="column"><div class="ui form"><div class="inline fields">';

				if ($page>0) $htmlCode .=  ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page-1). '\'; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading previous page...</div>\');"><i class="left arrow icon"></i> ' . __('Previous', 'ppv-live-webcams') . '</a> ';

				$htmlCode .= '<a class="ui secondary button" href="#"> ' . __('Page', 'ppv-live-webcams') . ' ' . ($page+1) . ' </a>' ;

				if (count($postslist) == $perPage) $htmlCode .=  ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page+1). '\'; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading next page...</div>\');">' . __('Next', 'ppv-live-webcams') . ' <i class="right arrow icon"></i></a> ';

				$htmlCode .= '</div></div></div>';
			}

			$htmlCode .= '
<div class="right floated column">
<div class="ui icon buttons right floated">
  <a class="ui button ' . ($layout=='grid'?'active':'') . '" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCOP.'&layout=grid\'; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading grid layout...</div>\');">
    <i class="th icon"></i>
  </a>
  <a class="ui button ' . ($layout=='list'?'active':''). '" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCOP.'&layout=list\'; loadWebcams' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading list layout...</div>\');">
    <i class="th list icon"></i>
  </a>
</div>
</div>';

			$htmlCode .= '</div>';
			//footer end

			if (!$isMobile && $options['videosharevod'])
			{

				$htmlCode .= '
<SCRIPT language="JavaScript">

var $jQnC1 = jQuery.noConflict();
$jQnC1(document).ready(function()
{

var hHandlers = $jQnC1(".videowhisperWebcam").hover( hoverVideoWhisper, outVideoWhisper );
var hHandlers2 = $jQnC1(".videowhisperWebcam2").hover( hoverVideoWhisper, outVideoWhisper );

function hoverVideoWhisper(e) {
   var vid = $jQnC1(\'video\', this).get(0);
   if (vid) vid.play();
}

function outVideoWhisper(e) {
     var vid = $jQnC1(\'video\', this).get(0);
     if (vid) vid.pause();
}
});
</SCRIPT>
';
			}

			$htmlCode .= '<!-- Generated '. date(DATE_RFC2822) . ' ' . $cacheQuery . ' ' . $cacheKey . ' -->';

			echo $htmlCode . '<!-- Generated this request -->';

			VWliveWebcams::stringSave($cachePath, $htmlCode);

			die();
		}


		//string contains any term for list (ie. banning)
		static function containsAny($name, $list)
		{
			$items = explode(',', $list);
			foreach ($items as $item) if (stristr($name, trim($item))) return $item;

				return 0;
		}


		//if any element from array1 in array2
		static function any_in_array($array1, $array2)
		{
			foreach ($array1 as $value) if (in_array($value,$array2)) return true;
				return false;
		}


		//if any key matches any listing (csv); for
		static function inList($keys, $data)
		{
			if (!$keys) return 0;
			if (!$data) return 0;
			if (strtolower(trim($data)) == 'all') return 1;
			if (strtolower(trim($data)) == 'none') return 0;

			$list=explode(",", strtolower(trim($data)));
			if (in_array('all', $list)) return 1;

			foreach ($keys as $key)
				foreach ($list as $listing)
					if ( strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}


		static function getCurrentURL()
		{
			$currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			$currentURL .= $_SERVER["SERVER_NAME"];

			if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
			{
				$currentURL .= ":".$_SERVER["SERVER_PORT"];
			}

			$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

			$currentURL .= $uri_parts[0];
			return $currentURL;
		}


		/**
		 * Retrieves the best guess of the client's actual IP address.
		 * Takes into account numerous HTTP proxy headers due to variations
		 * in how different ISPs handle IP addresses in headers between hops.
		 */
		static function get_ip_address() {
			$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
			foreach ($ip_keys as $key) {
				if (array_key_exists($key, $_SERVER) === true) {
					foreach (explode(',', $_SERVER[$key]) as $ip) {
						// trim for safety measures
						$ip = trim($ip);
						// attempt to validate IP
						if (VWliveWebcams::validate_ip($ip)) {
							return $ip;
						}
					}
				}
			}
			return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		}


		/**
		 * Ensures an ip address is both a valid IP and does not fall within
		 * a private network range.
		 */
		static function validate_ip($ip)
		{
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				return false;
			}
			return true;
		}


		//! room features
		static function roomFeatures()
		{
			return array(
				'videos' => array(
					'name'=>'Videos',
					'description' =>'Can upload and import videos. Requires Video Share VOD plugin.',
					'installed' => 1,
					'default' => 'All'),
				'pictures' => array(
					'name'=>'Pictures',
					'description' =>'Can upload and import pictures. Requires Picture Gallery plugin.',
					'installed' => 1,
					'default' => 'All'),
				'costPerMinute' => array(
					'name'=>'Cost per Minute',
					'description' =>'Can specify cost per minute for private shows. Replaces default show CPM.',
					'installed' => 1,
					'default' => 'All'),
				'costPerMinuteGroup' => array(
					'name'=>'Group Cost per Minute',
					'description' =>'Can specify cost per minute for group shows. Replaces default paid group CPM.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'slots2way' => array(
					'name'=>'2 Way Slots',
					'description' =>'Can specify 2 way slots for other participants to start webcam. Replaces default group 2 way slots.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'uploadPicture' => array(
					'name'=>'Upload Room Picture',
					'description' =>'Can upload custom room picture.',
					'installed' => 1,
					'default' => 'All'),
				'roomDescription' => array(
					'name'=>'Room Description',
					'description' =>'Can write description for room (profile, bio, schedule). Shows on cam page.',
					'installed' => 1,
					'default' => 'All'),
				'roomLabel' => array(
					'name'=>'Room Label',
					'description' =>'Can define a room label. Shows in cam listings instead of name.',
					'installed' => 1,
					'default' => 'All'),
				'roomBrief' => array(
					'name'=>'Room Brief',
					'description' =>'Can write brief for room. Shows in cam listings.',
					'installed' => 1,
					'default' => 'All'),
				'banCountries' => array(
					'name'=>'Ban Countries',
					'description' =>'Can restrict access based on user country.',
					'installed' => 1,
					'default' => 'All'),
				'roomTags' => array(
					'name'=>'Room Tags',
					'description' =>'Can write tags for room. Shows in cam listings.',
					'installed' => 1,
					'default' => 'All'),
				'roomCategory' => array(
					'name'=>'Room Category',
					'description' =>'Can select a category.',
					'installed' => 1,
					'default' => 'All'),
				'accessList' => array(
					'name'=>'Access List',
					'description' =>'Can specify list of user logins, roles, emails that can access the room (public chat). If disabled, users can access as configured in Client section.',
					'installed' => 1,
					'default' => 'None'),
				'accessPrice' => array(
					'name'=>'Access Price',
					'description' =>'Can setup a price for public room access. Uses myCRED Sell Content addon to control page access.',
					'type' => 'number',
					'installed' => 1,
					'default' => 'None'),
				'accessPassword' => array(
					'name'=>'Access Password',
					'description' =>'Can specify a password to protect room page access.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'transcode' => array(
					'name'=>'Transcode',
					'description' =>'Shows transcoding interface with web broadcasting interface.',
					'installed' => 1,
					'default' => 'All'),
				'logoHide' => array(
					'name'=>'Hide Logo',
					'description' =>'Hides logo from room video.',
					'installed' => 1,
					'default' => 'None'),
				'logoCustom' => array(
					'name'=>'Custom Logo',
					'description' =>'Can setup a custom logo. Overrides hide logo feature.',
					'installed' => 1,
					'default' => 'None'),
				'schedulePlaylists' => array(
					'name'=>'Video Scheduler',
					'description' =>'Can schedule existing videos to play as if performer was live.',
					'installed' => 1,
					'default' => 'None'),
				'private2way' => array(
					'name'=>'2 Way Private Videochat',
					'description' =>'Can toggle 2 way videochat.',
					'installed' => 1,
					'default' => 'All'),
				'multicam' => array(
					'name'=>'Multiple Cameras',
					'description' =>'Can start multiple cameras.',
					'installed' => 1,
					'default' => 'All'),
				'presentationMode' => array(
					'name'=>'Presentation Mode',
					'description' =>'Can toggle presentation mode from room setup.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
			);
		}


		function delete_associated_media($id, $unlink=false) {

			$htmlCode .= "Removing... ";

			$media = get_children(array(
					'post_parent' => $id,
					'post_type' => 'attachment'
				));
			if (empty($media)) return $htmlCode;

			foreach ($media as $file) {

				if ($unlink)
				{
					$filename = get_attached_file($file->ID);
					$htmlCode .=  " Removing $filename #" . $file->ID;
					if (file_exists($filename)) unlink($filename);
				}

				wp_delete_attachment($file->ID);
			}

			return $htmlCode;
		}


		function videowhisper_webcams_logout($atts)
		{
			//$pid = $options['p_videowhisper_webcams_logout'];

			$room = sanitize_file_name( $_GET['room'] );
			$message = sanitize_textarea_field( $_GET['message'] );

			$options = get_option('VWliveWebcamsOptions');

			$htmlCode = '<h3 class="ui header">' . __('You Were Disconnected from Chat Room', 'ppv-live-webcams') . '</H3>';

			switch($message)
			{
			case __('You have been disconnected from server.', 'ppv-live-webcams'):
			case __('Free daily time limit reached: Check paid rooms!', 'ppv-live-webcams'):
			case __('Free daily visitor time limit reached: Register for more!', 'ppv-live-webcams'):

			default:
				$htmlCode .= '<p>'.$message.'</p>';
			}

			return $htmlCode;
		}


		//! usernames and meta in chat
		static function performerName($user, $options)
		{
			//returns performer name in room, for $user

			$webcamName =  $options['webcamName'];
			if (!$webcamName) $webcamName='user_nicename';

			if ($user->$webcamName) $name = $user->$webcamName;
			if (!$name) $name = $user->user_login;

			return sanitize_file_name($name);

		}


		static function performerNameID($id, $options)
		{
			//returns performer name in room, for user $id

			$user = get_user_by('id', $id);
			if (!$user) return;

			$webcamName =  $options['webcamName'];
			if (!$webcamName) $webcamName='user_nicename';

			if ($user->$webcamName) $name = $user->$webcamName;
			if (!$name) $name = $user->user_login;

			return sanitize_file_name($name);

		}


		static function clientName($user, $options)
		{
			//returns client name in room, for $user

			$userName =  $options['userName'];
			if (!$userName) $userName='user_nicename';

			return $user->$userName;
		}


		static function performerLink($id, $options)
		{

			$name = VWliveWebcams::performerNameID($id, $options);;

			if (!$options['performerProfile']) return $name;

			$user = get_userdata($id);
			if (!$user) return '';

			return '<a href="'.$options['performerProfile'].$user->user_nicename.'">'.$name.'</a>';
		}


		//! Dashboards



		//! Studio Dashboard
		function videowhisper_webcams_studio($atts)
		{
			if (!is_user_logged_in()) return __('Login to manage studio!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'include_css' => '1',
				), $atts, 'videowhisper_webcams_studio');

			$options = get_option('VWliveWebcamsOptions');

			$current_user = wp_get_current_user();

			if (!$current_user->ID) return __('Login is required to access this section!','ppv-live-webcams');

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;

			$roleS = implode(',', $current_user->roles);
			if ( ! VWliveWebcams::any_in_array( array( $options['roleStudio'], 'administrator', 'super admin'), $current_user->roles))
				return __('User role does not allow managing studio!','ppv-live-webcams') . ' (' . $roleS.')';

			$this_page    =  VWliveWebcams::getCurrentURL();
			$tab = sanitize_file_name($_GET['tab']);

			if (!$tab) $htmlCode .= html_entity_decode(stripslashes($options['dashboardMessageStudio']));

			$htmlCode .= do_shortcode('[videowhisper_account_records]');
			if (!VWliveWebcams::userEnabled($current_user, $options, 'Studio')) return $htmlCode .  __('Your account is not currently enabled. Update your account records and wait for site admins to approve your account.', 'ppv-live-webcams');

			//updating records
			if ($_GET['updateRecords']) if ($_GET['updateRecords']!='update') return $htmlCode;

				//! Dashboard Tabs
				$htmlCode .= '<div class="vwtabs">';

			//! performers tab
			$checked = '';
			if ( $tab == 'performers' || ! $tab) $checked = 'checked';
			if ($checked) $checked1 = true;

			$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-performers" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-performers">' . __('Performers', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">';
			$htmlCode .= '<h3 class="ui header">' . __('Performers', 'ppv-live-webcams') . '</h3> ' . __('Manage studio performer accounts.', 'ppv-live-webcams') ;



			if  ($tab == 'performers')
			{
				//Transactions Log
				if ($transactionsID = (int) $_GET['transactions'])
				{


					$studioID = get_user_meta($transactionsID, 'studioID', true);

					if ($studioID == $current_user->ID || $transactionsID == $current_user->ID)
					{

						$user_info = get_userdata($transactionsID);
						$htmlCode .= '<h4>' . __('Performer Transactions', 'ppv-live-webcams') .': '. $user_info->user_login . '</h4>';
						$htmlCode .= do_shortcode( '[mycred_history user_id=' . $transactionsID . ']');

						$htmlCode .= '<style type="text/css">
.pagination, .pagination > LI {
display: inline !important;
padding: 5px;
}
</style>';

					}
					else $htmlCode .= '<BR>' . __('You are not allowed to view transactions for this user!', 'ppv-live-webcams');



				}
				switch ($_GET['view'])
				{
				case 'form':

					$htmlCode .= '<h4>' . __('Create Performer Account', 'ppv-live-webcams') . '</h4>';

					$action = add_query_arg( array('tab'=>'performers', 'view'=>'insert'), $this_page);

					$clarificationsMsg = __('After setting up performer account you will also receive password to communicate to performer for quick access. Account password should be changed by performer. Warning: Make sure you fill details correctly. Only administrator can delete incorrect accounts.', 'ppv-live-webcams');
					$saveMsg = __('Save', 'ppv-live-webcams');

					$htmlCode .= <<<HTMLCODE
<form method="post" enctype="multipart/form-data" action="$action" name="adminForm" class="w-actionbox">
<table class="g-input" width="500px">
<tr><td>Username</td><td><input size="32" type="text" name="performer_username" id="performer_username" value=""></td></tr>
<tr><td>Email</td><td><input size="32" type="text" name="performer_email" id="performer_email" value=""></td></tr>
<tr><td></td><td><input class="button videowhisperButton" type="submit" name="save" id="save" value="$saveMsg" /></td></tr>
</table>
$clarificationsMsg
</form>
HTMLCODE;
					break;

				case 'insert':

					$htmlCode .= '<h4>' . __('Adding Performer Account', 'ppv-live-webcams') . '</h4>';

					$error = '';

					$performer_username = sanitize_file_name( $_POST['performer_username'] );
					$performer_email = sanitize_file_name( $_POST['performer_email'] );

					if (username_exists( $performer_username )) $error = __('Username already in use!', 'ppv-live-webcams');
					if (email_exists( $performer_email )) $error = __('Email already in use!', 'ppv-live-webcams');


					$args = array(
						'blog_id'      => $GLOBALS['blog_id'],
						'meta_key'     => 'studioID',
						'meta_value'   => $current_user->ID,
						'meta_compare' => '=',
					);
					$performers = get_users( $args );
					$performersCount = count($performers);
					if ($options['studioPerformers'] && $performersCount >= $options['studioPerformers']) $error = __('Performers limit reached!', 'ppv-live-webcams');

					if ($error) $htmlCode .= __('Could not create performer account: ', 'ppv-live-webcams') . $error;
					else
					{
						$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
						$user_id = wp_create_user( $performer_username, $random_password, $performer_email );
						if ($user_id > 0)
						{
							$htmlCode .= __('Performer account was created', 'ppv-live-webcams') . ': <BR>Username: ' . $performer_username . '<BR>' . __('Password', 'ppv-live-webcams') . ': ' . $random_password . '<BR>' . __('Email', 'ppv-live-webcams') . ': ' . $performer_email;

							//assign to studio
							update_user_meta($user_id, 'studioID', $current_user->ID);
							update_user_meta($user_id, 'studioLogin', $current_user->user_login);
							update_user_meta($user_id, 'studioPassword', $random_password);
							update_user_meta($user_id, 'studioDisabled', 0);

							//set as performer
							wp_update_user( array( 'ID' => $user_id, 'role' => $options['rolePerformer'] ) );

							//also create a webcam listing
							$newPerformer = get_userdata($user_id);
							$name = VWliveWebcams::performerName($newPerformer, $options);

							$webcamID = VWliveWebcams::webcamPost($name, $name, $user_id, $current_user->ID);
							update_user_meta($user_id, 'currentWebcam', $webcamID);

						}
					}

					break;

				default:
					$performersList = 1;
				}

				if ($user_id = (int) $_GET['disable'])
				{
					$studioID = get_user_meta($user_id, 'studioID', true);

					if ($studioID == $current_user->ID)
					{
						update_user_meta($user_id, 'studioDisabled', (int) $_GET['disabled']);
						//wp_update_user( array( 'ID' => $user_id, 'role' => $options['rolePerformer'] ) );
					}
					else $htmlCode .= __('That performer is not assigned to this studio!', 'ppv-live-webcams');

				}

			} else $performersList = 1; //no special action


			if ($performersList)
			{

				$args = array(
					'blog_id'      => $GLOBALS['blog_id'],
					'meta_key'     => 'studioID',
					'meta_value'   => $current_user->ID,
					'meta_compare' => '=',
					'orderby'      => 'registered',
					'order'        => 'DESC',
					'fields'       => 'all',
				);

				$performers = get_users( $args );

				$performersCount = count($performers);

				$htmlCode .= '<h4>' . __('Performers List', 'ppv-live-webcams') . ' (' . $performersCount . '/' . ($options['studioPerformers']?$options['studioPerformers']:'&infin;') . ')</h4>';

				if ($performersCount)
				{
					$htmlCode .= '<table><thead><tr><th>' . __('Login', 'ppv-live-webcams') . '</th><th>' . __('Email', 'ppv-live-webcams') . '</th><th>' . __('Balance', 'ppv-live-webcams') . '</th><th>' . __('Status', 'ppv-live-webcams') . '</th><th>Info</th><th>' . __('Current Cam', 'ppv-live-webcams') . '</th></tr></thead>';

					foreach ( $performers as $performer )
					{

						$htmlCode .= '<tr><td> <b>' .$performer->user_login . '</b> </td><td>'. $performer->user_email;

						$htmlCode .= '</td><td>';
						$htmlCode .= '<a href="'. add_query_arg( array('tab'=>'performers', 'transactions'=>$performer->ID), $this_page).'">' . VWliveWebcams::balance($performer->ID) . '</a>';
						$htmlCode .= '</td><td>';

						$disabled = get_user_meta($performer->ID, 'studioDisabled', true);
						if (!$disabled) $htmlCode .= '<a href="'.add_query_arg( array('tab'=>'performers', 'disable'=>$performer->ID, 'disabled'=> 1), $this_page).'">' . __('Enabled', 'ppv-live-webcams') . '</A>';
						else $htmlCode .= '<a href="'.add_query_arg( array('tab'=>'performers', 'disable'=>$performer->ID, 'disabled'=> 0), $this_page).'">' . __('Disabled', 'ppv-live-webcams') . '</A>';

						$htmlCode .= '</td><td>';

						$password = get_user_meta($performer->ID, 'studioPassword', true);
						if ($password) $htmlCode .=  __('Original Password', 'ppv-live-webcams') . ': ' . $password . ' ';

						$htmlCode .= '</td><td>';

						$selectWebcam = get_user_meta($performer->ID, 'currentWebcam', true);
						if ($selectWebcam) $htmlCode .= '<a href="'.get_permalink( $selectWebcam ).'">'.get_the_title( $selectWebcam ).'</a>';
						$htmlCode .= '</td></tr>';
					}

					$htmlCode .= '</table>';
				} else $htmlCode .=  __('Studio has no performer accounts, yet.', 'ppv-live-webcams');

				if (!$options['studioPerformers'] || $performersCount < $options['studioPerformers'])
					$htmlCode .= '<p><a href="'. add_query_arg( array('tab'=>'performers', 'view'=>'form'), $this_page).'" class="videowhisperButton">' . __('Add Performer', 'ppv-live-webcams') . '</a> ' . __('Create a new performer account.', 'ppv-live-webcams') . '</p>';

			}else $htmlCode .= '<p><a href="'. add_query_arg( array('tab'=>'performers'), $this_page).'">' . __('View Your Performers List', 'ppv-live-webcams') . '</a></p>';




			$htmlCode .= '
			</div>
       </div>
   </div>';


			//! webcams tab

			$checked = '';
			if ( $tab == 'webcams') $checked = 'checked';
			if ($checked) $checked1 = true;

			$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-webcams" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-webcams">Webcams</label>

<div class="vwpanel">
       <div class="vwcontent">';
			$htmlCode .= '<h3 class="ui header">' . __('Webcams', 'ppv-live-webcams') . '</h3> ' . __('Manage webcam listings for studio performers.', 'ppv-live-webcams')  ;

			if  ($tab == 'webcams')
			{

				switch ($_GET['view'])
				{
				case 'add':

					$htmlCode .= '<h4>' . __('Create Webcam Listing', 'ppv-live-webcams') . '</h4>';

					//performers list
					if (!$performers)
					{
						$args = array(
							'blog_id'      => $GLOBALS['blog_id'],
							'meta_key'     => 'studioID',
							'meta_value'   => $current_user->ID,
							'meta_compare' => '=',
							'orderby'      => 'registered',
							'order'        => 'DESC',
							'fields'       => 'all',
						);

						$performers = get_users( $args );
					}

					if (count($performers))
					{
						foreach ( $performers as $performer )
							$performersCode .= '<input type="checkbox" name="selectedPerformers[]" value="' . $performer->ID . '">' .$performer->user_login. '<br>' ;

					} else $performersCode .= 'Studio has no performer accounts to select from.';

					$action = add_query_arg( array('tab'=>'webcams', 'view'=>'insert'), $this_page);

					$infoMsg = __('Warning: Make sure webcam listing name is correct. Only administrator can delete webcams.', 'ppv-live-webcams');
					$saveMsg = __('Save', 'ppv-live-webcams');

					$htmlCode .= <<<HTMLCODE
<form method="post" enctype="multipart/form-data" action="$action" name="adminForm" class="w-actionbox">
<table class="g-input" width="500px">
<tr><td>Webcam Listing Name</td><td><input size="32" type="text" name="webcam_name" id="webcam_name" value=""></td></tr>
<tr><td>Performers</td><td align="left">$performersCode</td></tr>
<tr><td></td><td><input class="button videowhisperButton" type="submit" name="save" id="save" value="$saveMsg" /></td></tr>
</table>
$infoMsg
</form>
HTMLCODE;
					break;

				case 'insert':

					$htmlCode .= '<h4>Adding Webcam Listing</h4>';

					$error = '';

					$webcam_name = sanitize_file_name( $_POST['webcam_name'] );
					$selectedPerformers = $_POST['selectedPerformers'];

					global $wpdb;
					$pid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = '%s' AND post_type='" . $options['custom_post'] . "'", $webcam_name ));

					if (!$pid)
					{
						$post = array(
							'post_name'      => sanitize_title_with_dashes($webcam_name),
							'post_title'     => $webcam_name,
							'post_author'    => $current_user->ID,
							'post_type'      => $options['custom_post'],
							'post_status'    => 'publish',
						);

						$pid = wp_insert_post($post);

						//assign to studio
						update_post_meta($pid, 'studioID', $current_user->ID);


						//assign to performers
						if (count($selectedPerformers)) foreach ($selectedPerformers as $performer) add_post_meta($pid, 'performerID', (int) $performer, false);

					} else $error = __('Webcam listing with that name already exists.', 'ppv-live-webcams');


					if ($error) $htmlCode .= 'Could not create webcam listing: ' . $error;
					else $htmlCode .= __('Webcam listing was successfully created!', 'ppv-live-webcams');


					break;

				default:
					$webcamsList = 1;
				}

				// webcam actions in list view

			} else $webcamsList = 1; //no special action

			if ($webcamsList)
			{

				$args = array(
					'post_type'        => $options['custom_post'],
					'meta_key'     => 'studioID',
					'meta_value'   => $current_user->ID,
					'meta_compare' => '=',
					'orderby'      => 'date',
					'order'        => 'DESC',
				);

				$webcams = get_posts( $args );

				$webcamsCount = count($webcams);

				$htmlCode .= '<h4>Webcams List (' . $webcamsCount . '/' . ($options['studioWebcams']?$options['studioWebcams']:'&infin;') . ')</h4>';


				if ($webcamsCount)
				{
					$htmlCode .= '<table><thead><tr><td>Webcam</td><td>Status</td><td>Performer(s)</td></tr></thead>';

					foreach ( $webcams as $webcam )
					{

						$htmlCode .= '<tr><td><b>' .$webcam->post_title . '</b><td>'.$webcam->post_status. '</td>' ;

						$htmlCode .= ' </td><td>';
						$performerIDs = get_post_meta($webcam->ID, 'performerID', false);
						if ($performerIDs) if (count($performerIDs)) foreach ($performerIDs as $performerID)
								{
									$performer = get_userdata($performerID);
									$htmlCode .= $performer->user_login . ' ';
								}

							$htmlCode .= '</td>';
						$htmlCode .= '</tr>';
					}

					$htmlCode .= '</table>';
				} else $htmlCode .=  'Studio has no webcam listings, yet.';

				if (!$options['studioWebcams'] || $webcamsCount < $options['studioWebcams'])
					$htmlCode .= '<p><a href="'. add_query_arg( array('tab'=>'webcams', 'view'=>'add'), $this_page).'" 	class="ui button secondary">Add Webcam</a> Create a new webcam listing.</p>';

			}else $htmlCode .= '<p><a href="'. add_query_arg( array('tab'=>'webcams'), $this_page).'" class="ui button secondary">View Your Webcams List</a></p>';

			$htmlCode .= '
			</div>
       </div>
   </div>';

			$htmlCode .= '
</div>';

			if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['dashboardCSS']));

			return $htmlCode;
		}


		//! Follow
		function videowhisper_follow($atts)
		{
			//AJAX button to follow webcam, saves follower as webcam listing meta

		}


		function videowhisper_follow_list($atts)
		{
			//list followed channels based on videowhisper_webcams

		}


		//! Account Status and Records
		function videowhisper_account_records($atts)
		{

			$current_user = wp_get_current_user();
			if (!$current_user->ID) return __('Login is required to access this section!','ppv-live-webcams');

			$options = get_option('VWliveWebcamsOptions');

			$this_page    =  VWliveWebcams::getCurrentURL();

			//! update account records

			$updateRecords = $_GET['updateRecords'];

			$verified = get_user_meta($current_user->ID,'vwVerified', true);
			$adminSuspended = get_user_meta($current_user->ID,'vwSuspended', true);

			$htmlCode .=  '<div id="performerStatus" class="ui ' . $options['interfaceClass'] .' segment"><h4 class="ui header">' . $current_user->user_login . ' (#'.$current_user->ID.')</h4> ' . __('Account Status', 'ppv-live-webcams') . ': ' .  ($verified?__('Verified','ppv-live-webcams'):__('Not Verified','ppv-live-webcams')) . ($adminSuspended?' ' . __('Suspended by Admin', 'ppv-live-webcams'):'');
			if ($updateRecords != 'form') $htmlCode .= ' <small><a class="ui compact tiny button" href='. add_query_arg( array('updateRecords'=>'form'), $this_page).'>' . __('Update Account Records', 'ppv-live-webcams') . '</a></small>';

			$balance = VWliveWebcams::balance($current_user->ID);
			if ($balance) $htmlCode .=  '<p>'. __('Your current balance', 'ppv-live-webcams') . ': ' .  $balance . VWliveWebcams::balances($current_user->ID) . '</p>';

			$htmlCode .= '</div>';


			switch ($updateRecords)
			{
			case 'form':
				$htmlCode .='<div class="ui ' . $options['interfaceClass'] .' form segment"><form method="post" enctype="multipart/form-data" action="'. add_query_arg( array('updateRecords'=>'update'), $this_page) .'" name="accountForm" class="w-actionbox">';

				$htmlCode .= '<H4 class="ui ' . $options['interfaceClass'] .' header">' . __('Account Administrative Records', 'ppv-live-webcams') . ': ' . $current_user->user_login . '</H4> ' . __('These details are required by administrators for account approval and administrative operations.', 'ppv-live-webcams');

				if (!is_array($options['recordFields']))
				{
					$htmlCode .= '<p>' . __('No record fields defined by administrator!', 'ppv-live-webcams') . '</p>';
				}
				else
					foreach ($options['recordFields'] as $field => $parameters)
					{

						$fieldName = sanitize_title(trim($field));

						if ($parameters['instructions']) $htmlInstructions = ' data-tooltip="' . htmlspecialchars($parameters['instructions']). '"';
						else $htmlInstructions ='';

						$htmlCode .= '<div class="field"' . $htmlInstructions . '><label for="'.$fieldName.'">'.$field.'</label>';

						if (isset($_POST[$fieldName]))
							if ($current_user->ID == $_POST['profileFor'])
								update_user_meta($current_user->ID, 'vwf_' . $fieldName, sanitize_text_field($_POST[$fieldName]));

							$fieldValue = htmlspecialchars(get_user_meta( $current_user->ID, 'vwf_' . $fieldName, true ));

						switch ($parameters['type'])
						{
						case 'text';
							$htmlCode .= '<INPUT type="text" size="72" name="'. $fieldName . '" id="'. $fieldName . '" value="' . $fieldValue . '">';
							break;

						case 'textarea';
							$htmlCode .= '<TEXTAREA type="text" rows="3" cols="70" name="'. $fieldName . '" id="'. $fieldName . '">' .$fieldValue. '</TEXTAREA>';
							break;

						case 'select';
							$htmlCode .= '<SELECT name="'. $fieldName . '" id="'. $fieldName . '" class="ui dropdown">';
							$fieldOptions = explode('/', $parameters['options']);

							$htmlCode .= '<OPTION value="" '. (!$fieldValue ? 'selected':'') . '> - </OPTION>';


							foreach ($fieldOptions as $fieldOption)
								$htmlCode .= '<OPTION value="'. htmlspecialchars($fieldOption) . '" '. ($fieldOption == $fieldValue ? 'selected':'') . '>'.htmlspecialchars($fieldOption).'</OPTION>';


							$htmlCode .='</SELECT>';
							break;
						}
						$htmlCode .= '</div>';

					}
				$htmlCode .='<input type="hidden" name="profileFor" id="profileFor" value="'.$current_user->ID.'">
	<BR><input class="ui primary button" type="submit" name="updateRecords" id="updateRecords" value="' . __('Save', 'ppv-live-webcams') . '" />
	</form></div>';

				break;

			case 'update':

				if (is_array($options['recordFields'])) if ($current_user->ID == $_POST['profileFor'])
					{
						foreach ($options['recordFields'] as $field => $parameters)
						{

							$fieldName = sanitize_title(trim($field));

							if (isset($_POST[$fieldName]))
								update_user_meta($current_user->ID, 'vwf_' . $fieldName, sanitize_text_field($_POST[$fieldName]));
						}
						update_user_meta($current_user->ID, 'vwUpdated',time());

					}
				break;



			}

			return $htmlCode;
		}


		static function userEnabled($user, $options, $role='Performer')
		{
			if (!$user) return;
			if (!$user->ID) return;

			$verified = get_user_meta($user->ID,'vwVerified', true);
			$adminSuspended = get_user_meta($user->ID,'vwSuspended', true);

			if ($adminSuspended) return 0;
			if ($verified || $options['unverified'.$role]) return 1;
		}


		static function detectLocation($resolution = 'country', $ip='')
		{
			//GeoIP2

			if (!$ip) $ip = self::get_ip_address();

			try
			{
				switch ($resolution)
				{

				case 'all': //array
					$res = array();

					if (function_exists('geoip_record_by_name'))
					{
						$inf =  geoip_record_by_name($ip);
						if ($inf)
						{
							if ($ev =  $inf['continent_code']) $res[] = esc_sql(htmlspecialchars($ev));
							if ($ev =  $inf['country_name']) $res[] = esc_sql(htmlspecialchars($ev));
							if ($ev =  $inf['region']) $res[] = esc_sql(htmlspecialchars($ev));
							if ($ev =  $inf['city']) $res[] = esc_sql(htmlspecialchars($ev));
							if (!empty($res)) return $res;
						}
					}


					if ($ev = getenv('GEOIP_CONTINENT_CODE')) $res[] = esc_sql(htmlspecialchars($ev));
					if ($ev = getenv('GEOIP_COUNTRY_NAME')) $res[] = esc_sql(htmlspecialchars($ev));
					if ($ev = getenv('GEOIP_REGION_NAME')) $res[] = esc_sql(htmlspecialchars($ev));
					if ($ev = getenv('GEOIP_CITY')) $res[] = esc_sql(htmlspecialchars($ev));
					return $res;

					break;

				case 'continent':
					if (function_exists('geoip_record_by_name'))
					{
						$inf =  geoip_record_by_name($ip);
						if ($inf) return $inf['continent_code'];
					}
					if ($ev = getenv('GEOIP_CONTINENT_CODE')) return $ev;
					break;

				case 'country':
					if (function_exists('geoip_country_name_by_name'))
						$c = esc_sql(geoip_country_name_by_name($ip));
					if ($c) return $c;

					if ($ev = esc_sql(getenv('GEOIP_COUNTRY_NAME'))) return $ev;
					break;

				case 'region':

					if ($ev = getenv('GEOIP_REGION_NAME'))  return $ev;

					if (function_exists('geoip_record_by_name'))
					{
						$inf =  geoip_record_by_name($ip);
						if ($inf) if ($ev =  $inf['region']) return $ev;
					}

					break;

				case 'city':
					if (function_exists('geoip_record_by_name'))
					{
						$inf =  geoip_record_by_name($ip);
						if ($inf) return esc_sql(htmlspecialchars($inf['city']));
					}

					if ($ev = esc_sql(htmlspecialchars(getenv('GEOIP_CITY')))) return $ev;
					break;
				}

			} catch (Exception $e) {
				echo 'Exception: ' .  $e->getMessage();
				return false;
			}

			return false;
		}


		//! Performer Dashboard
		function videowhisper_webcams_performer($atts)
		{
			if (!is_user_logged_in()) return __('Login to manage webcams!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'include_css' => '1',
				), $atts, 'videowhisper_webcams_performer');

			$options = get_option('VWliveWebcamsOptions');

			$current_user = wp_get_current_user();

			if (!$current_user->ID) return __('Login is required to access this section!','ppv-live-webcams');

			$uid = $current_user->ID;

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;

			$roleS = implode(',', $current_user->roles);
			if ( ! VWliveWebcams::any_in_array( array( $options['rolePerformer'], 'administrator', 'super admin'), $current_user->roles))
				return __('User role does not allow publishing webcams!','ppv-live-webcams') . ' (' . $roleS.')';

			//process user's sessions to show updated balance
			VWliveWebcams::billSessions($current_user->ID);


			//semantic ui : performer dashboard
			VWliveWebcams::enqueueUI();

			if ($options['dashboardMessage']) $htmlCode .= '<div id="performerDashboardMessage" class="ui ' . $options['interfaceClass'] .' segment">'. html_entity_decode(stripslashes($options['dashboardMessage'])) .'</div>';


			$this_page    =  VWliveWebcams::getCurrentURL();

			$htmlCode .= do_shortcode('[videowhisper_account_records]');
			if (!VWliveWebcams::userEnabled($current_user, $options, 'Performer')) return $htmlCode . '<div id="performerDashboardMessage" class="ui yellow segment">' .  __('Your account is not currently enabled. Update your account records and wait for site admins to approve your account.','ppv-live-webcams') . '</div>';


			//disabled by studio: no access
			$disabled = get_user_meta($current_user->ID, 'studioDisabled', true);
			if ($disabled) return $htmlCode . '<div id="performerDashboardMessage" class="ui red segment">' . __('Studio disabled your account: dashboard access is forbidden. Contact studio or site administrator!','ppv-live-webcams') . '</div>';


			//updating records
			if ($_GET['updateRecords']) if ($_GET['updateRecords']!='update') return $htmlCode;


				// display dashboard info

				//! webcams manage: shows when managing

				//! select webcam listing

				if ($selectWebcam = (int) $_GET['selectWebcam'])
				{

					//verify if valid (owner or assigned)
					$webcamSelected = get_post( $selectWebcam );


					if (!$webcamSelected) $selectWebcam = '';
					else
					{
						$performerIDs = get_post_meta($webcam->ID, 'performerID', false);
						if (!$performerIDs) $performerIDs = array();
						if ($webcamSelected->post_author != $current_user->ID && !in_array($current_user->ID, $performerIDs) ) $selectWebcam ='';
					}

					if ($selectWebcam)
					{
						update_user_meta($current_user->ID, 'currentWebcam', $selectWebcam);
						$postID = $selectWebcam;
					}
					else $htmlCode .= '<BR>' . __('Selected webcam listing is invalid.', 'ppv-live-webcams') . '' . $webcamSelected->author  ;

				}

			if (!$postID)
			{
				//get or setup webcam post for this user
				if (!$options['performerWebcams']) $postID = VWliveWebcams::webcamPost(); //default cam
				else
				{
					$postID = get_user_meta($current_user->ID, 'currentWebcam', true);
					if (!$postID) $selectWebcamList = 1;
				}
			}



			//! manage webcams
			if ($selectWebcamList || $_GET['changeWebcam'])
			{
				$htmlCode .= '<div id="performerWebcamsManage" class="ui ' . $options['interfaceClass'] .' segment">';

				$postID = ''; //select or manage


				switch ($_GET['view'])
				{
				case 'add':

					$htmlCode .= '<h4>' . __('Create Webcam Listing', 'ppv-live-webcams') . '</h4>';

					$action = add_query_arg( array('changeWebcam'=>'manage', 'view'=>'insert'), $this_page);

					$performersCode = '<input size="48" type="text" name="performersCSV" id="performersCSV" value=""><br>' . __('Partner performers that can also go live in this room: Comma separated list of performer usernames or emails.', 'ppv-live-webcams');

					$msg1 = __('Webcam Listing Name', 'ppv-live-webcams');
					$msg2 = __('Performers', 'ppv-live-webcams');
					$msg3 = __('Save', 'ppv-live-webcams');
					$msg4 = __('Warning: Make sure webcam listing name is correct. Only administrator can delete webcams.', 'ppv-live-webcams');

					$htmlCode .= <<<HTMLCODE
<form method="post" enctype="multipart/form-data" action="$action" name="adminForm" class="w-actionbox">
<table class="g-input" width="500px">
<tr><td>$msg1</td><td><input size="32" type="text" name="webcam_name" id="webcam_name" value=""></td></tr>
<tr><td>$msg2</td><td align="left">$performersCode</td></tr>
<tr><td></td><td><input class="ui button videowhisperButton" type="submit" name="save" id="save" value="$msg3" /></td></tr>
</table>
$msg4
</form>
HTMLCODE;
					break;

				case 'insert':

					$htmlCode .= '<h4>' . __('Adding Webcam Listing', 'ppv-live-webcams') . '</h4>';

					$error = '';

					$webcam_name = sanitize_file_name( $_POST['webcam_name'] );

					global $wpdb;
					$pid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = '%s' AND post_type='" . $options['custom_post'] . "'", $webcam_name ));

					if (!$pid)
					{
						$post = array(
							'post_name'      => sanitize_title_with_dashes($webcam_name),
							'post_title'     => $webcam_name,
							'post_author'    => $current_user->ID,
							'post_type'      => $options['custom_post'],
							'post_status'    => 'publish',
						);

						$pid = wp_insert_post($post);

						//assign to studio
						update_post_meta($pid, 'studioID', $current_user->ID);

						//assign to performers
						if ($performersCSV = sanitize_text_field( $_POST['performersCSV']))
						{
							$selectedPerformers = array();

							$performers = explode(',', $performersCSV);
							if (count($performers)) foreach ($performers as $performerV)
								{
									$performerID = '';
									$value = trim($performerV);
									if (is_email($value))
									{
										if ($performerID = get_user_by('email', $value)) $selectedPerformers[] = $performerID;
									}
									elseif ($performerID = get_user_by('login', $value)) $selectedPerformers[] = $performerID;

									if ($value && !$performerID) $htmlCode .= '"'.$value.'" ' . __('Warning: Performer not found: use a valid username (login) or email!', 'ppv-live-webcams') . '<BR>';
								}

							if (count($selectedPerformers)) foreach ($selectedPerformers as $performerID) add_post_meta($pid, 'performerID', (int) $performerID, false);
						}

					} else $error = __('Webcam listing with that name already exists.','ppv-live-webcams');


					if ($error) $htmlCode .= __('Could not create webcam listing:', 'ppv-live-webcams') . $error;
					else $htmlCode .= __('Webcam listing was successfully created!', 'ppv-live-webcams');

					break;

				default:
					$webcamsList = 1;
				}


				if ($webcamsList)
				{

					$args = array(
						'post_type'        => $options['custom_post'],
						'author'   => $current_user->ID,
						'orderby'      => 'date',
						'order'        => 'DESC',
						'posts_per_page'   => -1,
					);

					$webcamsOwned = get_posts( $args );

					$args = array(
						'post_type'        => $options['custom_post'],
						'meta_key'     => 'performerID',
						'meta_value'   => $current_user->ID,
						'meta_compare' => '=',
						'orderby'      => 'date',
						'order'        => 'DESC',
						'posts_per_page'   => -1,

					);

					$webcamsAssigned = get_posts( $args );

					$webcams = array_merge($webcamsOwned, $webcamsAssigned);
					$webcams = array_unique($webcams, SORT_REGULAR);


					$webcamsCount = count($webcams);

					$htmlCode .= '<h4>' . __('Select Webcam Listing', 'ppv-live-webcams') . ' (' . $webcamsCount . '/' . ($options['performerWebcams']?$options['performerWebcams']:'&infin;') . ')</h4>';


					$webcamCreated = 0;
					if ($webcamsCount)
					{
						$htmlCode .= __('Webcam listings you created or were granted access to broadcast:', 'ppv-live-webcams');

						foreach ( $webcams as $webcam )
						{
							$htmlCode .= '<br><a href="' .add_query_arg(array('selectWebcam' => $webcam->ID), $this_page). '"><b>' .$webcam->post_title . '</b></a> ';

							$performerIDs = get_post_meta($webcam->ID, 'performerID', false);
							if ($performerIDs) if (count($performerIDs)) foreach ($performerIDs as $performerID)
									{
										$performer = get_userdata($performerID);
										$htmlCode .= ' ' . $performer->user_login;
									}
						}

					} else
					{
						$htmlCode .=  __('You do not have any active webcam listings: one will be setup for you.', 'ppv-live-webcams');
						$postID = VWliveWebcams::webcamPost();
						
						if ($postID) $post = get_post($postID); 
						if (!$post) $postID = 0;
						
						if (!$postID) $htmlCode .=  'Error: Could not setup a webcam post!';
						else 
						{ 
							$htmlCode .= '<b>' . $post->post_title . '</b> #' . $postID;
							$webcamCreated = 1;
						}

					}


					if (!$options['performerWebcams'] || $webcamsCount < $options['performerWebcams'])
						if (!$webcamCreated)
							$htmlCode .= '<p><a href="'. add_query_arg( array('changeWebcam'=>'manage', 'view'=>'add'), $this_page).'" 	class="ui button secondary">' . __('Add Webcam', 'ppv-live-webcams') . '</a> ' . __('Create a new webcam listing.', 'ppv-live-webcams') . '</p>';

				}
				else $htmlCode .= '<p><a class="ui button secondary" href="'. add_query_arg( array('changeWebcam'=>'select'), $this_page).'">' . __('View Your Webcams List', 'ppv-live-webcams') . '</a></p>';

				$htmlCode .= '</div>';
			}
			//! end webcams manage


			if (!$postID)
			{
				if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['dashboardCSS']));
				return $htmlCode;
			}

			//!room updates

			//FEATURES
			//! edit room
			if ($_POST['setup'])
			{

				if (!$webcamPost) $webcamPost = get_post( $postID );


				//costPerMinute
				if (VWliveWebcams::inList($userkeys, $options['costPerMinute']))
				{
					$costPerMinute = round($_POST['costPerMinute'],2);

					//check range
					if ($costPerMinute >0)
						if ($options['ppvPPMmin'] > $costPerMinute ||  $options['ppvPPMmax'] < $costPerMinute)
						{
							$costPerMinute = 0;
							$htmlCode .='<div class="warning">' . __('Custom CPM out of range: removed.', 'ppv-live-webcams') .' (' . $options['ppvPPMmin'] . '- ' . $options['ppvPPMmax'] . ')</div>';
						}

					if ($costPerMinute) update_post_meta($postID, 'vw_costPerMinute', $costPerMinute);
					else delete_post_meta($postID, 'vw_costPerMinute');

				}

				//costPerMinute
				if (VWliveWebcams::inList($userkeys, $options['costPerMinuteGroup']))
				{
					$costPerMinute = round($_POST['costPerMinuteGroup'],2);

					//check range
					if ($costPerMinute >0)
						if ($options['ppvPPMmin'] > $costPerMinute ||  $options['ppvPPMmax'] < $costPerMinute)
						{
							$costPerMinute = 0;
							$htmlCode .='<div class="warning">' . __('Custom group CPM out of range: removed.', 'ppv-live-webcams') . '</div>';
						}

					if ($costPerMinute) update_post_meta($postID, 'vw_costPerMinuteGroup', $costPerMinute);
					else delete_post_meta($postID, 'vw_costPerMinuteGroup');
				}

				//slots 2way
				if (VWliveWebcams::inList($userkeys, $options['slots2way']))
				{
					$slots2way = round($_POST['slots2way']);

					if ($slots2way) update_post_meta($postID, 'vw_slots2way', $slots2way);
					else delete_post_meta($postID, 'vw_slots2way');
				}


				//$htmlCode .= 'Upload?'. $_FILES['uploadPicture']['tmp_name'];

				//uploadPicture
				if (VWliveWebcams::inList($userkeys, $options['uploadPicture']))
				{
					if ($filename = $_FILES['uploadPicture1']['tmp_name'])
					{
						$htmlCode .= 'Processing picture upload... ';

						$ext = strtolower(pathinfo($_FILES['uploadPicture1']['name'], PATHINFO_EXTENSION));
						$allowed = array('jpg','jpeg','png','gif');
						if (!in_array($ext,$allowed)) return 'Unsupported file extension!';

						list($width, $height) = getimagesize($filename);

						if ($width && $height)
						{

							//delete previous image(s)
							VWliveWebcams::delete_associated_media($postID);

							//$htmlCode .= 'Generating thumb... ';
							$thumbWidth = $options['thumbWidth'];
							$thumbHeight = $options['thumbHeight'];


							if (file_exists($filename))
							{
								$imageData = file_get_contents($filename);

								if ($imageData)
								{
									$src = imagecreatefromstring($imageData);

									unset($imageData);//prevent memory leaks

									$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);
									if ($tmp)
									{
										$dir = $options['uploadsPath'];
										if (!file_exists($dir)) mkdir($dir);

										$dir .= "/_pictures";
										if (!file_exists($dir)) mkdir($dir);

										$room_name = sanitize_file_name($webcamPost->post_title);
										$thumbFilename = "$dir/$room_name.jpg";
										imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

										imagejpeg($tmp, $thumbFilename, 95);

										//prevent memory leaks in case of loops (due to filters on other plugins)
										if ($src) imagedestroy($src);
										if ($tmp) imagedestroy($tmp);

										if (file_exists($thumbFilename))
										{
											//detect tiny images without info
											if (filesize($thumbFilename)>5000) $picType = 1;
											else $picType = 2;

											//update post meta
											if ($postID) update_post_meta($postID, 'hasPicture', $picType);

											//$htmlCode .= ' Updating picture... ' . $thumbFilename;

											//update post image
											if (!function_exists('wp_generate_attachment_metadata')) require ( ABSPATH . 'wp-admin/includes/image.php' );

											$wp_filetype = wp_check_filetype(basename($thumbFilename), null );

											$attachment = array(
												'guid' => $thumbFilename,
												'post_mime_type' => $wp_filetype['type'],
												'post_title' => $room_name,
												'post_content' => '',
												'post_status' => 'inherit'
											);

											$attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
											set_post_thumbnail($postID, $attach_id);

											//update post imaga data
											$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
											wp_update_attachment_metadata( $attach_id, $attach_data );

											$htmlCode .= __('Picture Updated.', 'ppv-live-webcams');
										}
										else $htmlCode .= __('ERROR: Cound not create JPG thumb file ', 'ppv-live-webcams') . $thumbFilename;

									}
									else $htmlCode .= __('ERROR: Cound not create temporary image!', 'ppv-live-webcams');

								}
								else $htmlCode .= __('ERROR: Failed loading image data ', 'ppv-live-webcams');
							}
							else $htmlCode .= __('ERROR: File does not exist ', 'ppv-live-webcams'). $filename;
						}
						else $htmlCode .= __('ERROR: Could not retrieve image size for ', 'ppv-live-webcams'). $filename;
					}

					$showImage = sanitize_file_name($_POST['showImage']);
					update_post_meta($postID, 'showImage', $showImage);

				}

				//category
				if (VWliveWebcams::inList($userkeys, $options['roomCategory']))
				{
					$category = (int) $_POST['newcategory'];
					wp_set_post_categories($postID, array($category));
				}

				//roomDescription
				if (VWliveWebcams::inList($userkeys, $options['roomDescription']))
				{

					$roomDescription = sanitize_text_field($_POST['roomDescription']);
					wp_update_post( array(
							'ID'           => $postID,
							'post_content' => $roomDescription,
						) );
				}

				//roomLabel
				if (VWliveWebcams::inList($userkeys, $options['roomLabel']))
				{
					$roomLabel = sanitize_file_name($_POST['roomLabel']);
					update_post_meta($postID, 'vw_roomLabel', $roomLabel);
				}

				//roomBrief
				if (VWliveWebcams::inList($userkeys, $options['roomBrief']))
				{
					$roomBrief = sanitize_text_field($_POST['roomBrief']);
					update_post_meta($postID, 'vw_roomBrief', $roomBrief);
				}

				//roomTags
				if (VWliveWebcams::inList($userkeys, $options['roomTags']))
				{
					$roomTags = sanitize_text_field($_POST['roomTags']);
					wp_set_post_tags( $postID, $roomTags, false);
				}


				//banCountries
				if (VWliveWebcams::inList($userkeys, $options['banCountries']))
				{
					$banCountries = sanitize_text_field($_POST['banCountries']);

					$banLocations = str_getcsv($banCountries, ',');

					delete_post_meta($postID, 'vw_banCountries' );

					if (is_array($banLocations)) if (!empty($banLocations))
							foreach ($banLocations as $value) if (trim($value))
									add_post_meta($postID, 'vw_banCountries', trim($value));
				}

				//private2way
				if (VWliveWebcams::inList($userkeys, $options['private2way']))
				{
					$private2way = sanitize_text_field($_POST['private2way']);
					update_post_meta($postID, 'vw_private2way', $private2way);
				}

				//accessList
				if (VWliveWebcams::inList($userkeys, $options['accessList']))
				{
					$accessList = sanitize_text_field($_POST['accessList']);
					update_post_meta($postID, 'vw_accessList', $accessList);
				}

				//accessPrice
				if (VWliveWebcams::inList($userkeys, $options['accessPrice']))
				{
					$accessPrice = round($_POST['accessPrice'],2);
					update_post_meta($postID, 'vw_accessPrice', $accessPrice);

					$mCa = array(
						'status'       => 'enabled',
						'price'        => $accessPrice,
						'button_label' => 'Buy Access Now', // default button label
						'expire'       => 0 // default no expire
					);

					if ($accessPrice) update_post_meta($postID, 'myCRED_sell_content', $mCa);
					else delete_post_meta($postID, 'myCRED_sell_content');
				}

				//accessPassword
				$accessPassword =''; //remove if not enabled
				if (VWliveWebcams::inList($userkeys, $options['accessPassword']))
					$accessPassword = sanitize_text_field($_POST['accessPassword']);

				//if ($accessPassword) $htmlCode .= 'Webcam was password protected.';

				wp_update_post( array(
						'ID'           => $postID,
						'post_password' => $accessPassword
					) );

				//logoCustom
				if (VWliveWebcams::inList($userkeys, $options['logoCustom']))
				{
					$logoImage = sanitize_text_field($_POST['logoImage']);
					update_post_meta($postID, 'vw_logoImage', $logoImage);

					$logoLink = sanitize_text_field($_POST['logoLink']);
					update_post_meta($postID, 'vw_logoLink', $logoLink);

					update_post_meta($postID, 'vw_logo', 'custom');
				}

				//presentationMode
				if (VWliveWebcams::inList($userkeys, $options['presentationMode']))
				{
					$roomLabel = sanitize_file_name($_POST['presentationMode']);
					update_post_meta($postID, 'vw_presentationMode', $roomLabel);
				}
				else update_post_meta($postID, 'vw_presentationMode', '');


			}

			//feature updates

			//transcode
			if (VWliveWebcams::inList($userkeys, $options['multicam']))
				update_post_meta($postID, 'vw_multicam', $options['multicamMax']);
			else update_post_meta($postID, 'vw_multicam', '0');

			//transcode
			if (VWliveWebcams::inList($userkeys, $options['transcode']))
				update_post_meta($postID, 'vw_transcode', '1');
			else update_post_meta($postID, 'vw_transcode', '0');


			//logoHide
			if (VWliveWebcams::inList($userkeys, $options['logoHide']))
				update_post_meta($postID, 'vw_logo', 'hide');
			else update_post_meta($postID, 'vw_logo', 'global');


			if ($options['videosharevod'])
				if (VWliveWebcams::inList($userkeys, $options['videos']))
					update_post_meta($postID, 'vw_videos', '1');
				else update_post_meta($postID, 'vw_videos', '0');

				if ($options['picturegallery'])
					if (VWliveWebcams::inList($userkeys, $options['pictures']))
						update_post_meta($postID, 'vw_pictures', '1');
					else update_post_meta($postID, 'vw_pictures', '0');

					//schedulePlaylists
					if (!$options['playlists'] || !VWliveWebcams::inList($userkeys, $options['schedulePlaylists']))
						update_post_meta($postID, 'vw_playlistActive', '');

					//end room update


					//! Go Live
					if ($_POST['go-live'])
					{


						//set current as room performer
						$performerName = VWliveWebcams::performerName($current_user, $options);

						update_post_meta($postID, 'performer', $performerName);
						update_post_meta($postID, 'performerUserID', $current_user->ID );

						update_post_meta($postID, 'sessionStart', time());

						$link = get_permalink($postID);

						$archive = 0;

						$htmlCode .= '<BR>' . __('Room ID', 'ppv-live-webcams') . ': ' . $postID;


						//mode & parameters
						if (is_array($options['groupModes']) && $mode = sanitize_text_field($_POST['groupMode']))
						{
							foreach ($options['groupModes'] as $groupMode => $modeParameters)
								if ($mode == $groupMode)
								{
									//default group CPM if not set

									if ($modeParameters['cpm']) //only paid groups can have custom cpm: free remain free
										{
										$CPMg = get_post_meta( $postID, 'vw_costPerMinuteGroup', true );
										if ($CPMg) $modeParameters['cpm'] = $CPMg;
										else $CPMg = $modeParameters['cpm'];
									}

									//custom 2 way slots if configured
									$slots2way = get_post_meta( $postID, 'vw_slots2way', true );
									if ($slots2way) $modeParameters['2way'] = $slots2way;

									$htmlCode .= '<BR>' . __('Setting webcam group mode to', 'ppv-live-webcams') . ': ' . $groupMode;
									update_post_meta($postID, 'groupCPM', $CPMg);
									update_post_meta($postID, 'groupMode', $groupMode);
									update_post_meta($postID, 'groupParameters', $modeParameters);

									if ($modeParameters['archive']) $archive=1;
									if ($modeParameters['archiveImport']) $archiveImport=1;
									else $archiveImport = 0;
								}

						}
						else
						{
							update_post_meta($postID, 'groupCPM', 0);
							update_post_meta($postID, 'groupMode', 'Free');
						}

						//room interface: defaults
						$roomInterface = 'flash';
						if ($options['webrtc'])
						{
							$agent = $_SERVER['HTTP_USER_AGENT'];
							$Android = stripos($agent,"Android");
							$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
							if ($iOS || $Android) $roomInterface = 'html5app'; //on mobiles only html5/html5app available
						}

						//or selected
						if (isset($_POST['roomInterface'])) $roomInterface = sanitize_file_name( $_POST['roomInterface'] );

						//set
						update_post_meta($postID, 'roomInterface', $roomInterface);

						//import previous archives to prevent confusion
						VWliveWebcams::importArchives($postID);

						//archiving
						if ($archive)
						{
							update_post_meta($postID, 'rtmp_server', $options['rtmp_server_archive']);

							if ($archiveImport)
							{
								//update info about archived sessions for automated import
								$archivedSessions = get_post_meta($postID, 'archivedSessions', true);
								if (!$archivedSessions) $archivedSessions = array();
								$archivedSession = array('performer' =>$performerName, 'sessionStart' => time(), 'groupMode'=>$mode);
								$archivedSessions[] = $archivedSession;
								update_post_meta($postID, 'archivedSessions', $archivedSessions);
							}

						}
						else update_post_meta($postID, 'rtmp_server', $options['rtmp_server']);

						//Checkins

						//sanitize
						$selectedPerformers = $_POST['selectedPerformers'];

						if (!$selectedPerformers) $selectedPerformers = array();

						if (count($selectedPerformers)) foreach ($selectedPerformers as $key => $performerID) $selectedPerformers[$key] = (int) $performerID;

							//checkin current performer
							if (!in_array($current_user->ID, $selectedPerformers)) $selectedPerformers[] = $current_user->ID;


							if ($performersCSV = sanitize_text_field( $_POST['performersCSV']))
							{

								$performers = explode(',', $performersCSV);
								if (count($performers)) foreach ($performers as $performerV)
									{
										$performerID = '';
										$value = trim($performerV);

										if (is_email($value)) $performerID = get_user_by('email', $value);
										else $performerID = get_user_by('login', $value);

										if ($performerID) if (!in_array($performerID, $selectedPerformers)) $selectedPerformers[] = $performerID;

											if ($value && !$performerID) $htmlCode .= '<BR>' . __('Warning: Performer', 'ppv-live-webcams') . ' "'.$value.'" ' . __('not found: use a valid username (login) or email!', 'ppv-live-webcams') . '<BR>';
									}


							}

						update_post_meta($postID, 'checkin', $selectedPerformers);

						$htmlCode .= '<BR>' . __('Room Interface', 'ppv-live-webcams') . ': ' . $roomInterface;

						$htmlCode .= '<BR>' . __('Checked in performers', 'ppv-live-webcams') . ': ' . count($selectedPerformers);

						$htmlCode .= '<BR>' . __('Assigning you', 'ppv-live-webcams') . ' ('.$performerName.') ' . __('as room performer (streamer) and redirecting to webcam room page. Please wait...', 'ppv-live-webcams') . '';




						$htmlCode .= '<SCRIPT>window.location="'.$link.'";</SCRIPT>
				<p><a href="'.$link.'"><b' . __('>Click here', 'ppv-live-webcams') . '</b> ' . __('to access your webcam room if you do not get automatically redirected', 'ppv-live-webcams') . '</a></p>';

						return $htmlCode;
					}

				//! Current Webcam
				$webcamPost = get_post( $postID );

			$htmlCode .=  '<div class="ui ' . $options['interfaceClass'] . ' red segment"><h4>' . __('Webcam Room', 'ppv-live-webcams') . ': ' . $webcamPost->post_title . '</h4>';

			//! form: group mode

			$CPM = get_post_meta( $postID, 'vw_costPerMinute', true );
			$CPMg = get_post_meta( $postID, 'vw_costPerMinuteGroup', true );
			$cpmCode = '';
			//if ($CPM) $cpmCode.=  __('Custom cost per minute in private show:', 'ppv-live-webcams') . ' '. $CPM . ' ';
			if ($CPMg) $cpmCode .=  '<br>* '. __('Custom cost per minute in paid group chat:', 'ppv-live-webcams') . ' '. $CPMg . ' ' . htmlspecialchars($options['currency']);



			if (is_array($options['groupModes']))
			{
				$groupModeCrt = get_post_meta($postID, 'groupMode', true);

				$modeCode .= '<div id="groupModeSelect"><SELECT class="ui dropdown" name="groupMode" id="groupMode">';
				foreach ($options['groupModes'] as $groupMode => $modeParameters)
				{
					if ($modeParameters['cpm'])
						if ($CPMg) $costCode = '*' . $CPMg . htmlspecialchars($options['currencypm']);
						else $costCode = $modeParameters['cpm'] . htmlspecialchars($options['currencypm']);
						else $costCode = __('Free', 'ppv-live-webcams');


						$modeCode .= '<OPTION value="' . $groupMode . '" ' . ($groupModeCrt==$groupMode?'selected':'') . '>' . $groupMode .' (' . $costCode. ')</OPTION>';
				}
				$modeCode .= '</SELECT> Room Mode: ' . __('Select group mode for your new session in this room, before going live.', 'ppv-live-webcams') . $cpmCode .  '</div>';
			}

			if (!$options['presentation'] && self::collaborationMode($postID, $options)) $modeCode .=  '<br>* '. __('This room is in presentation/collaboration mode.', 'ppv-live-webcams') ;





			//! flash/html5 interface select
			$roomInterface = get_post_meta($postID, 'roomInterface', true);

			if ($options['webrtc'])
			{
				if (!$roomInterface) $roomInterface = 'html5app';

				$agent = $_SERVER['HTTP_USER_AGENT'];
				$Android = stripos($agent,"Android");
				$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));


				$interfaceCode .= '<div id="roomInterfaceSelect"><SELECT class="ui dropdown" name="roomInterface" id="roomInterface">';

				//html5 videochat app
				$interfaceCode .= '<OPTION value="html5app" ' . ($roomInterface=='html5app'?'selected':'') . '>HTML5 Videochat</OPTION>';
				$interfaceInfo .= 'HTML5 Videochat works in mobile and PC browsers, providing streaming with group chat, private 2 way videochat, tips.';

				//html5 live streaming
				if ($options['webrtc'] < 6)
				{
					$interfaceCode .= '<OPTION value="html5" ' . ($roomInterface=='html5'?'selected':'') . '>HTML5 Streaming</OPTION>';
					$interfaceInfo .= '<br>HTML5 Streaming interface provides simple 1 way video streaming with group chat and tips.';
				}

			}

			//Flash
			if ($options['webrtc'] < 5)
				if (!$iOS && !$Android)
				{
					if (!$roomInterface) $roomInterface = 'flash';

					$interfaceCode .= '<OPTION value="flash" ' . ($roomInterface=='flash'?'selected':'') . '>Advanced PC</OPTION>';
					$interfaceInfo .= '<br>Advanced PC interface is Flash based and supports presentation mode, streaming with group chat, 2 way videochat, tips. Mobile users will get the HTML5 Streaming interface if transcoding is available.';
				}
			else $interfaceInfo .= '<br>Advanced PC interface is Flash based and not supported in mobile brosers.';


			$interfaceCode .= '</SELECT> Room Interface: ' . __('Select room interface before going live. Also applies to clients.', 'ppv-live-webcams');
			$interfaceCode .= '<div class="ui small basic pointing grey label">' . $interfaceInfo . '</div></div>' ;


			//! form: checkin
			$checkinCode .= '<A class="ui icon right labeled button" id="hideshow" >' . __('Checkin Options', 'ppv-live-webcams') . ' <i class="down arrow icon"></i> </a> Tag other performers present in session.';

			$checkinCode .= '<div id="checkinPerformers"><div class="ui ' . $options['interfaceClass'] .' segment">
<H4 class="ui header">' . __('Checkin Performers', 'ppv-live-webcams') . '</H4>' . __('You can select partner performers or specify other performers that are present in session.', 'ppv-live-webcams') . '
<BR>' . __('If paid group chat is enabled, earnings will be shared equally between checked in performers.', 'ppv-live-webcams');

			//partner performers
			$performerIDs = get_post_meta($postID, 'performerID', false);
			if ($performerIDs) if (count($performerIDs))
				{
					$checkinCode .= '<BR> <label>' . __('Partner Performers', 'ppv-live-webcams') . '</label>';
					foreach ($performerIDs as $performerID)
					{
						$performer = get_user_by('id', $performerID);
						if ($performer)
							$checkinCode .= '<input type="checkbox" name="selectedPerformers[]" value="' . $performer->ID . '">' .$performer->user_login. '<br>' ;

					}
				}

			$checkinCode .=  '<BR> <label>' . __('Checkin Performers', 'ppv-live-webcams') . '</label> <input size="48" type="text" name="performersCSV" id="performersCSV" value=""><BR>' . __('Comma separated list of performer usernames or emails.', 'ppv-live-webcams') . '';


			$checkinCode .= '</div></div>';

			$checkinCode .= "<SCRIPT>jQuery(document).ready(function(){

		jQuery('#checkinPerformers').toggle('fast');

        jQuery('#hideshow').on('click', function(event) {
             jQuery('#checkinPerformers').toggle('slow');

			 jQuery(\".ui.dropdown\").dropdown();

        });
    });</SCRIPT>";


			if ($options['performerWebcams']) $webcamCode .= '<a class="ui icon right labeled button" href="' .add_query_arg(array('changeWebcam' => 1), $this_page). '"> <i class="sync icon"></i> ' . __('Select Different Webcam Listing', 'ppv-live-webcams') . '</a> Switch to different webcam listing. Your account can setup and manage multiple webcam profiles.';

			$htmlCode .='
<form method="post" enctype="multipart/form-data" action="' . $this_page . '" name="goLiveForm" class="">
<div class="ui form">
<div class="ui field"><button class="ui icon right labeled button big green" type="submit" name="go-live" id="go-live" value="go-live" > ' . __('Go Live', 'ppv-live-webcams') . ' <i class="play icon"></i> </button> </div>
<div class="ui field">' . $modeCode .'</div>
<div class="ui field">' . $interfaceCode .'</div>
<div class="ui field">' . $checkinCode .'</div>
<div class="ui field">' . $webcamCode .'</div>
</div>
</form>
<br style="clear:both">
';




			$htmlCode .=  '</div>';



			//! Dashboard Tabs
			//$htmlCode .= '<div class="vwtabs">';


			//tab header
			$headerCode .= '<div class="ui ' . $options['interfaceClass'] . ' top attached tabular menu">';


			$htmlCode .= '
	<script>
jQuery(document).ready(function(){
	jQuery(".tabular.menu .item").tab();
});
</script>';


//! calls tab
			$checked = '';
			if ($_GET['calls']) $checked = 'active';
			if ($checked) $checked1 = true;

			$headerCode .= '<a class="item ' . $checked .'" data-tab="calls">' . __('Calls', 'ppv-live-webcams') . '</a>';

			$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="calls">';

			$contentCode .= do_shortcode('[videowhisper_cam_calls post_id="' . $postID . '"]');
			
			$contentCode .= '<br style="clear:both"></div>';


			/*

			$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-2" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-2">' . __('Setup', 'ppv-live-webcams') . '</label>

	   <div class="vwpanel">
       <div class="vwcontent">';
 */
			//! setup tab

			$checked = '';
			if ($_POST['setup']) $checked = 'active';
			if ($checked) $checked1 = true;

			$headerCode .= '<a class="item ' . $checked .'" data-tab="setup">' . __('Setup', 'ppv-live-webcams') . '</a>';

			$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="setup">';


			$featuresCode = '';

			//costPerMinute
			if (VWliveWebcams::inList($userkeys, $options['costPerMinute']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_costPerMinute', true );
				if ($value == '') $value = $options['ppvPPM'];

				if ($value < $options['ppvPPMmin']) $value = $options['ppvPPMmin'];
				if ($options['ppvPPMmax']) if ($value > $options['ppvPPMmax']) $value = $options['ppvPPMmax'];

					$cpmRange =  '' . __('Default', 'ppv-live-webcams') . ': ' . $options['ppvPPM'] .  ' Min: ' . $options['ppvPPMmin'] .  ' Max: ' . $options['ppvPPMmax'];

				$featuresCode .= '<tr><td>' . __('Cost Per Minute', 'ppv-live-webcams') . '</td><td><input size=5 name="costPerMinute" id="costPerMinute" value="' . $value . '"><BR>' . __('Cost per minute for private shows (set 0 to use default).', 'ppv-live-webcams') . ' '.$cpmRange.'</td></tr>';
			}

			//costPerMinuteGroup
			if (VWliveWebcams::inList($userkeys, $options['costPerMinuteGroup']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_costPerMinuteGroup', true );
				if ($value == '') $value = $options['ppvPPM'];

				if ($value < $options['ppvPPMmin']) $value = $options['ppvPPMmin'];
				if ($options['ppvPPMmax']) if ($value > $options['ppvPPMmax']) $value = $options['ppvPPMmax'];

					$cpmRange =  '' . __('Default', 'ppv-live-webcams') . ': ' . $options['ppvPPM'] .  ' Min: ' . $options['ppvPPMmin'] .  ' Max: ' . $options['ppvPPMmax'];

				$featuresCode .= '<tr><td>' . __('Cost Per Minute in Group Mode', 'ppv-live-webcams') . '</td><td><input size=5 name="costPerMinuteGroup" id="costPerMinuteGroup" value="' . $value . '"><BR>' . __('Cost per minute for group shows. Replaces paid group CPM (set 0 to use default).', 'ppv-live-webcams') . ' '.$cpmRange.'</td></tr>';
			}

			//slots2way
			if (VWliveWebcams::inList($userkeys, $options['slots2way']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_slots2way', true );
				$featuresCode .= '<tr><td>' . __('2 Way Slots', 'ppv-live-webcams') . '</td><td><input size=5 name="slots2way" id="slots2way" value="' . $value . '"><BR>' . __('2 way slots in room. Replaces default group mode slots (set 0 to use default).', 'ppv-live-webcams') .'</td></tr>';
			}

			//uploadPicture
			if (VWliveWebcams::inList($userkeys, $options['uploadPicture']))
			{

				$featuresCode .= '<tr><td>' . __('Picture', 'ppv-live-webcams') . '</td><td><input type="file" name="uploadPicture1" id="uploadPicture1" class="ui button"><BR>' . __('Update room picture to use instead of live snapshot.', 'ppv-live-webcams') . '</td></tr>';


				$value=get_post_meta( $postID, 'showImage', true );

				$featuresCode .= '<tr><td>' . __('Show', 'ppv-live-webcams') . '</td><td><select name="showImage" id="showImage" class="ui dropdown">';
				$featuresCode .= '<option value="picture" '.($value=='picture'?'selected':'').'>' . __('Picture', 'ppv-live-webcams') . '</option>';
				$featuresCode .= '<option value="snapshot" '.($value=='snapshot'?'selected':'').'>' . __('Live Snapshot', 'ppv-live-webcams') . '</option>';
				$featuresCode .= '</select><BR>' . __('Select what to show in webcam listings.', 'ppv-live-webcams') . '</td></tr>';

			}

			//roomLabel
			if (VWliveWebcams::inList($userkeys, $options['roomLabel']))
			{
				if ($postID) $vw_roomLabel = get_post_meta( $postID, 'vw_roomLabel', true );
				else $vw_roomLabel = '';

				$featuresCode .= '<tr><td>' . __('Label', 'ppv-live-webcams') . '</td><td><input size=24 name="roomLabel" id="roomLabel" value="' . $vw_roomLabel . '"><BR>' . __('Shows in room listings instead of name.', 'ppv-live-webcams') . '</td></tr>';
			}

			//roomCategory
			if (VWliveWebcams::inList($userkeys, $options['roomCategory']))
			{
				$cats = wp_get_post_categories( $postID);
				if (count($cats)) $newCat = array_pop($cats);

				$featuresCode .= '<tr><td>' . __('Category', 'ppv-live-webcams') . '</td><td>' . wp_dropdown_categories('show_count=0&echo=0&name=newcategory&hide_empty=0&class=ui+dropdown&selected=' . $newCat) . '<BR>' . __('Webcam category.', 'ppv-live-webcams') . '</td></tr>';
			}

			//roomDescription
			if (VWliveWebcams::inList($userkeys, $options['roomDescription']))
			{
				$featuresCode .= '<tr><td>' . __('Description', 'ppv-live-webcams') . '</td><td><textarea rows="4" cols="80" name="roomDescription" id="roomDescription">' . htmlspecialchars($webcamPost->post_content) . '</textarea><BR>' . __('Room description: profile, schedule. Shows on room page and full row list layout.', 'ppv-live-webcams') . '</td></tr>';
			}

			//roomBrief
			if (VWliveWebcams::inList($userkeys, $options['roomBrief']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_roomBrief', true );
				else $value = '';

				$featuresCode .= '<tr><td>' . __('Brief', 'ppv-live-webcams') . '</td><td><textarea rows="2" cols="80" name="roomBrief" id="roomBrief">' . htmlspecialchars($value) . '</textarea><BR>' . __('Room brief info: profile, schedule. Shows in room listings.', 'ppv-live-webcams') . '</td></tr>';
			}

			//private2way
			if (VWliveWebcams::inList($userkeys, $options['private2way']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_private2way', true );
				else $value = '';

				$featuresCode .= '<tr><td>' . __('Private Videochat Mode', 'ppv-live-webcams') . '</td><td><select name="private2way" id="private2way" class="ui dropdown">';
				$featuresCode .= '<option value="" '.($value==''?'selected':'').'>' . __('Default', 'ppv-live-webcams') . '</option>';
				$featuresCode .= '<option value="1way" '.($value=='1way'?'selected':'').'>' . __('1 Way', 'ppv-live-webcams') . '</option>';
				$featuresCode .= '<option value="2way" '.($value=='2way'?'selected':'').'>' . __('2 Way', 'ppv-live-webcams') . '</option>';
				// $featuresCode .= '<option value="both" '.($value=='both'?'selected':'').'>Both Modes</option>';
				$featuresCode .= '</select><BR>' . __('Allow clients to start their cams for 2 way videochat.', 'ppv-live-webcams') . '</td></tr>';
			}


			//banCountries
			if (VWliveWebcams::inList($userkeys, $options['banCountries']))
			{
				if ($postID) $banLocations = get_post_meta( $postID, 'vw_banCountries', false );
				else $banLocations = '';

				$value = implode(', ', $banLocations);

				$info = '';

				//$banLocations = str_getcsv($value, ',');
				$info .= ' ('. count($banLocations) . ') ';

				$clientIP = VWliveWebcams::get_ip_address();
				$info .= '<br>' . __('Detected IP', 'ppv-live-webcams') . ': ' . $clientIP;

				$info .= '<br>' . __('Detected Continent, Country, Region, City', 'ppv-live-webcams') . ': ' . VWliveWebcams::detectLocation('continent', $clientIP) . ', ' . VWliveWebcams::detectLocation('country', $clientIP) . ', ' . VWliveWebcams::detectLocation('region', $clientIP) . ', ' . VWliveWebcams::detectLocation('city', $clientIP);

				if (VWliveWebcams::detectLocation() === false)
					$info .= '<BR>' . __('ERROR: GeoIP extension is required on host for this functionality.', 'ppv-live-webcams');

				$featuresCode .= '<tr><td>' . __('Ban Countries', 'ppv-live-webcams') . '</td><td><textarea rows="2" cols="80" name="banCountries" id="banCountries">' . htmlspecialchars($value) . '</textarea><BR>' . __('You may want to ban your location for privacy reasons. Or countries and regions where your content is not legal or well received. List of locations (country, region, city or continent code, separated by comma) that can not access webcam room.', 'ppv-live-webcams') . $info. '</td></tr>';

			}


			//roomTags
			if (VWliveWebcams::inList($userkeys, $options['roomTags']))
			{
				$tags = wp_get_post_tags($postID, array( 'fields' => 'names' ));
				//var_dump($tags);
				$value = '';

				if ( ! empty( $tags ) ) if ( ! is_wp_error( $tags ) )
						foreach( $tags as $tag )  $value .= ($value?', ':'') . $tag;

						$featuresCode .= '<tr><td>' . __('Tags', 'ppv-live-webcams') . '</td><td><textarea rows=2 cols="80" name="roomTags" id="roomTags">' . $value . '</textarea><BR>' . __('Tags separated by comma. Show in room listings.', 'ppv-live-webcams') . '</td></tr>';
			}

			//presentationMode
			if (VWliveWebcams::inList($userkeys, $options['presentationMode']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_presentationMode', true );
				else $value = '';

				$featuresCode .= '<tr><td>' . __('Presentation Mode', 'ppv-live-webcams') . '</td><td><select name="presentationMode" id="presentationMode" class="ui dropdown">';
				$featuresCode .= '<option value="" '.($value==''?'selected':'').'>' . __('Default', 'ppv-live-webcams') .' : '. ($options['presentation']?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')). '</option>';
				$featuresCode .= '<option value="1" '.($value=='1'?'selected':'').'>' . __('Enabled', 'ppv-live-webcams') . '</option>';
				$featuresCode .= '<option value="0" '.($value=='0'?'selected':'').'>' . __('Disabled', 'ppv-live-webcams') . '</option>';
				// $featuresCode .= '<option value="both" '.($value=='both'?'selected':'').'>Both Modes</option>';
				$featuresCode .= '</select><BR>' . __('Enables presentations mode with presentation screen, annotations, whiteboard, file sharing.', 'ppv-live-webcams') . '</td></tr>';
			}

			//logoCustom
			if (VWliveWebcams::inList($userkeys, $options['logoCustom']))
			{
				$value = get_post_meta( $editPost, 'vw_logoImage', true );
				$featuresCode .= '<tr><td>' . __('Logo Image', 'ppv-live-webcams') . '</td><td><input size=64 name="logoImage" id="logoImage" value="' . $value . '"><BR>' . __('Floating logo URL (preferably a transparent PNG image). Leave blank to hide.', 'ppv-live-webcams') . '</td></tr>';
				$value = get_post_meta( $editPost, 'vw_logoLink', true );
				$featuresCode .= '<tr><td>' . __('Logo Link', 'ppv-live-webcams') . '</td><td><input size=64 name="logoLink" id="logoImage" value="' . $value . '"><BR>' . __('URL to open on logo click.', 'ppv-live-webcams') . '</td></tr>';
			}


			//accessList
			if (VWliveWebcams::inList($userkeys, $options['accessList']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_accessList', true );
				else $value = '';

				$featuresCode .= '<tr><td>' . __('Access List', 'ppv-live-webcams') . '</td><td><textarea rows=2 cols="80" name="accessList" id="accessList">' . $value . '</textarea><BR>' . __('User roles, logins, emails separated by comma. Leave empty to allow everybody to access.', 'ppv-live-webcams') . '</td></tr>';
			}

			//accessPrice
			if (VWliveWebcams::inList($userkeys, $options['accessPrice']))
			{
				if ($postID) $value = get_post_meta( $postID, 'vw_accessPrice', true );
				else $value = '';

				$featuresCode .= '<tr><td>Access Price</td><td><input size=5 name="accessPrice" id="accessPrice" value="' . $value . '"><BR>' . __('Webcam room access price. Leave 0 for free access.', 'ppv-live-webcams') . '</td></tr>';
			}

			//accessPassword
			if (VWliveWebcams::inList($userkeys, $options['accessPassword']))
			{
				$value = $webcamPost->post_password;

				$featuresCode .= '<tr><td>Access Password</td><td><input size=16 name="accessPassword" id="accessPassword" value="' . $value . '"><BR>Password to protect room page access. Leave blank to not require password.</td></tr>';
			}

			$this_page    =  VWliveWebcams::getCurrentURL();

			$msg1 = __('Webcam Profile Setup', 'ppv-live-webcams');
			$msg2 = __('Setup', 'ppv-live-webcams');

			$interfaceClass =  $options['interfaceClass'];

			if ($featuresCode) $contentCode .= <<<HTMLCODE
<form method="post" enctype="multipart/form-data" action="$this_page" name="adminForm" class="ui $interfaceClass form w-actionbox">
<h3 class="ui $interfaceClass header">$msg1</h3>
<table class="ui $interfaceClass selectable striped table form">
$featuresCode
<tr><td></td><td><input class="ui button primary" type="submit" name="setup" id="setup" value="$msg2" /></td></tr>
</table>
</form>

HTMLCODE;

			$contentCode .= '
			<br style="clear:both">
   </div>';



			//! profile tab

			if (is_array($options['profileFields']))
			{


				$checked = '';
				if ($_POST['save']) $checked = 'active';
				if ($checked) $checked1 = true;

				$headerCode .= '<a class="item ' . $checked .'" data-tab="profile">' . __('Profile', 'ppv-live-webcams') . '</a>';

				$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="profile">';

				/*
				$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-profile" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-profile">' . __('Profile', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">';
*/


				$contentCode .='<div class="ui ' . $options['interfaceClass'] . ' form"><form method="post" enctype="multipart/form-data" action="'. $this_page .'" name="profileForm" class="w-actionbox">';

				$contentCode .= '<h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Webcam Room Listing Profile', 'ppv-live-webcams') . '</H3> ' . __('These details will show on webcam room page (under videochat interface).', 'ppv-live-webcams') ;
				
				$contentCode .= '<div class="ui divider" /></div>';

					//allowed tags
					$allowedtags = array(
						'a' => array(
							'href' => true,
							'title' => true,
						),
						'abbr' => array(
							'title' => true,
						),
						'acronym' => array(
							'title' => true,
						),
						'b' => array(),
						'blockquote' => array(
							'cite' => true,
						),
						'cite' => array(),
						'code' => array(),
						'del' => array(
							'datetime' => true,
						),
						'em' => array(),
						'i' => array(),
						'q' => array(
							'cite' => true,
						),
						'strike' => array(),
						'strong' => array(),

						'ul' => array(),
						'ol' => array(),
						'li' => array(),
						
						'span' => array(
							'style' => array()
						),
						
						'p' => array(
							'style' => array()
						),
					);

					$tinymce_options = array(
						'plugins' => "lists,link,textcolor,hr",
						'toolbar1' => "cut,copy,paste,|,undo,redo,|,fontsizeselect,forecolor,backcolor,bold,italic,underline,strikethrough",
						'toolbar2' => "alignleft,aligncenter,alignright,alignjustify,blockquote,hr,bullist,numlist,link,unlink",
						'fontsize_formats' => '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt' );


				foreach ($options['profileFields'] as $field => $parameters)
				{

					$fieldName = sanitize_title(trim($field));

					if ($parameters['instructions']) $htmlInstructions = ' data-tooltip="' . htmlspecialchars(stripslashes($parameters['instructions'])). '"';
					else $htmlInstructions ='';

					$contentCode .= '<div class="field"' . $htmlInstructions . '><label for="'.$fieldName.'">'.$field.'</label>';
					//$contentCode .= '<span>' . htmlspecialchars(stripslashes($parameters['instructions'])) . '</span>';

					//save data
					if (isset($_POST[$fieldName]))
						if ($postID == $_POST['profileFor'])
							if ($parameters['type'] == 'checkboxes')
							{
								$tags = (array) $_POST[$fieldName];
								if (is_array($tags)) {
									foreach ($tags as &$tag) {
										$tag = esc_attr($tag);
									}
									unset($tag );
								} else {
									$tags = esc_attr($tags);
								}

								update_post_meta($postID, 'vwf_' . $fieldName, $tags);
							}
						else update_post_meta($postID, 'vwf_' . $fieldName, wp_kses($_POST[$fieldName], $allowedtags));

						//get data
						if ($parameters['type'] == 'checkboxes')
						{
							$fieldValue = get_post_meta( $postID, 'vwf_' . $fieldName, true);
							if (!$fieldValue) $fieldValue = array();
							if (!is_array($fieldValue)) $fieldValue = array($fieldValue);
						}
					else $fieldValue = get_post_meta( $postID, 'vwf_' . $fieldName, true );
					//$fieldValue = htmlspecialchars(stripslashes(get_post_meta( $postID, 'vwf_' . $fieldName, true )));


					// form
					switch ($parameters['type'])
					{
					case 'text';
						$contentCode .= '<INPUT type="text" size="72" name="'. $fieldName . '" id="'. $fieldName . '" value="' . $fieldValue . '">';
						break;

					case 'textarea';
						//$contentCode .= '<TEXTAREA type="text" rows="3" cols="70" name="'. $fieldName . '" id="'. $fieldName . '">' .$fieldValue. '</TEXTAREA>';


						ob_start();
						wp_editor( $fieldValue, $fieldName, $settings = array(
								'textarea_rows'=>3, 'media_buttons' => false, 'teeny'=>true,  'wpautop'=>false,
								'tinymce' => $tinymce_options,) );
						$contentCode .= ob_get_clean();

						break;

					case 'select';
						$contentCode .= '<SELECT class="ui dropdown" name="'. $fieldName . '" id="'. $fieldName . '">';
						$fieldOptions = explode('/', $parameters['options']);

						$contentCode .= '<OPTION value="" '. (!$fieldValue ? 'selected':'') . '> - </OPTION>';


						foreach ($fieldOptions as $fieldOption)
							$contentCode .= '<OPTION value="'. htmlspecialchars(stripslashes($fieldOption)) . '" '. ($fieldOption == $fieldValue ? 'selected':'') . '>'.htmlspecialchars($fieldOption).'</OPTION>';


						$contentCode .='</SELECT>';
						break;

					case 'checkboxes';
						$fieldOptions = explode('/', $parameters['options']);

						foreach ($fieldOptions as $fieldOption)
							$contentCode .= '<div class="field"><div class="ui toggle checkbox">
  <input type="checkbox" name="'. $fieldName . '[]" value="'. htmlspecialchars(stripslashes($fieldOption)) . '" '. (in_array($fieldOption, $fieldValue) ? 'checked':'') . '>
  <label>'.htmlspecialchars($fieldOption).'</label></div></div>';

						break;

					}

					//if ($parameters['instructions']) $contentCode .= '<BR>' . htmlspecialchars($parameters['instructions']);

					$contentCode .= '<div class="ui divider" /></div> </div>';
				}
				
				$contentCode .='<input type="hidden" name="profileFor" id="profileFor" value="'.$postID.'">
	<BR><input class="ui button primary" type="submit" name="save" id="save" value="' . __('Save', 'ppv-live-webcams') . '" />
	</form></div>';

				$contentCode .= '
		<br style="clear:both">
   </div>
   ';
			}



			//! videos tab
			if ($options['videosharevod']) if (shortcode_exists('videowhisper_postvideos'))
					if (VWliveWebcams::inList($userkeys, $options['videos']))
					{

						//import saved archives
						VWliveWebcams::importArchives($postID);

						$checked = '';
						if ($_GET['playlist_upload'] || $_GET['playlist_import']) $checked = 'active';
						if ($checked) $checked1 = true;

						$headerCode .= '<a class="item ' . $checked .'" data-tab="videos">' . __('Videos', 'ppv-live-webcams') . '</a>';

						$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="videos">';

						/*
						$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-vid" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-vid">' . __('Videos', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">';
*/


						$contentCode .=  do_shortcode("[videowhisper_postvideos_process post=\"$postID\"]");
						$contentCode .=  do_shortcode("[videowhisper_postvideos post=\"$postID\"]");

						$contentCode .= '<p>' . __('Multiple profile videos can show on webcam listing page. Each listing has own videos.', 'ppv-live-webcams') . '</p>';

						$contentCode .= '
	    <br style="clear: both">
   </div>
   ';
					}

				//!  tab
				if ($options['videosharevod']) if (shortcode_exists('videowhisper_postvideo_assign'))
						if (VWliveWebcams::inList($userkeys, $options['videos']))
						{
							$checked = '';
							if ($_GET['assignVideo']) $checked = 'active';
							if ($checked) $checked1 = true;

							$headerCode .= '<a class="item ' . $checked .'" data-tab="teaser">' . __('Teaser', 'ppv-live-webcams') . '</a>';
							$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="teaser">';

							/*
							$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-teaser" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-teaser">' . __('Teaser', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">
 */
							$contentCode .=        '<h3 class="ui header">' . __('Teaser Video', 'ppv-live-webcams') . '</H3>';
							$contentCode .= do_shortcode("[videowhisper_postvideo_assign post_id=\"$postID\" meta=\"video_teaser\"]");

							$contentCode .= '<p>' . __('Teaser video (featured video) plays shortly as preview in listings, in room when performer is offline in HTML5 Videochat interface and shows on profile page. Can be selected from all videos of user.', 'ppv-live-webcams') . '</p';

							$contentCode .= '
	    <br style="clear: both">
   </div>
   ';
						}

					//! playlists scheduler tab

					if ($options['playlists'])
						if (VWliveWebcams::inList($userkeys, $options['schedulePlaylists']))
						{

							//activity
							$editPlaylist = $postID;

							$playlistActive = (int) $_POST['playlistActive'];

							wp_enqueue_script( 'jquery');
							wp_enqueue_script( 'jquery-ui-core');
							wp_enqueue_script( 'jquery-ui-widget');
							wp_enqueue_script( 'jquery-ui-dialog');

							//css
							wp_enqueue_style( 'jtable-green', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/themes/lightcolor/green/jtable.min.css');

							wp_enqueue_style( 'jtable-flick', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/themes/flick/jquery-ui.min.css');

							//js
							wp_enqueue_script( 'jquery-ui-jtable', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/jquery.jtable.min.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog'));

							$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_playlist&webcam=' . $postID;


							$checked = '';
							if ($_POST['$editPlaylist'] ||  $_POST['playlistActive']) $checked = 'active'; //activity on this tab
							if ($checked) $checked1 = true;

							$headerCode .= '<a class="item ' . $checked .'" data-tab="playlist">' . __('Playlist', 'ppv-live-webcams') . '</a>';
							$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="playlist">';

							/*							$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-scheduler" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-scheduler">' . __('Scheduler', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">
       ';
*/

							//OS timezone : Wowza time
							$timezone = 'UTC';
							if (is_link('/etc/localtime')) {
								// Mac OS X (and older Linuxes)
								// /etc/localtime is a symlink to the
								// timezone in /usr/share/zoneinfo.
								$filename = readlink('/etc/localtime');
								if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
									$timezone = substr($filename, 20);
								}
							} elseif (file_exists('/etc/timezone')) {
								// Ubuntu / Debian.
								$data = file_get_contents('/etc/timezone');
								if ($data) {
									$timezone = $data;
								}
							} elseif (file_exists('/etc/sysconfig/clock')) {
								// RHEL / CentOS
								$data = parse_ini_file('/etc/sysconfig/clock');
								if (!empty($data['ZONE'])) {
									$timezone = $data['ZONE'];
								}
							}

							$defaultTimezone = date_default_timezone_get();
							if ($timezone) date_default_timezone_set($timezone);


							//$webcamPost;

							$stream = sanitize_file_name($webcamPost->post_title);

							//! quick loop setup - save loop

							if ($loop = sanitize_text_field($_POST['loop']))
							{

								$playlist = array();


								$item = array();
								$item['Id'] = 1;
								$item['Video'] = $loop;
								$item['Repeat'] = 1;
								$item['Scheduled'] = date('Y-m-j h:i:s');
								$item['Order'] = 1;

								$playlist[1] = $item;


								$playlist[1]['Videos'] = array();

								$item = array();
								$item['Video'] = $loop;
								$item['Start'] = 0;
								$item['Length'] = -1;
								$item['Order'] =  1;
								$item['Id'] =  1;

								$playlist[1]['Videos'][] = $item;


								//$playlistPath
								$uploadsPath = $options['uploadsPath'];
								if (!file_exists($uploadsPath)) mkdir($uploadsPath);
								$upath = $uploadsPath . "/$stream/";
								if (!file_exists($upath)) mkdir($upath);
								$playlistPath = $upath . 'playlist.txt';


								VWliveWebcams::varSave($playlistPath,$playlist);

								//$contentCode .= '<p>' . __('Video Loop', 'ppv-live-webcams') . ': '.$loop.'</p>';
								update_post_meta($postID, 'videoLoop', $loop);

							}

							$currentDate = date('Y-m-j h:i:s');

							//activate playslit
							if ($_POST['updatePlaylist'])
							{
								update_post_meta( $postID, 'vw_playlistActive', $playlistActive);
								VWliveWebcams::updatePlaylist($stream, $playlistActive);
								update_post_meta( $postID, 'vw_playlistUpdated', time());

								if ($playlistActive)
								{
									$contentCode .= '<p>' . __('Playlist is enabled and room stream updated to', 'ppv-live-webcams') . ' '.$stream.'.</p>';
									update_post_meta($postID, 'performer', $stream);
									update_post_meta($postID, 'performerUserID', $current_user->ID );

									update_post_meta($postID, 'edate', time());

									//stream is from rtmp server
									update_post_meta($postID, 'stream-protocol', 'rtmp');
									update_post_meta($postID, 'stream-type', 'playlist');

								}

							}


							//retrieve video list
							$optionsVSV = get_option('VWvideoShareOptions');
							$custom_post_video = $optionsVSV['custom_post'];
							if (!$custom_post_video) $custom_post_video = 'video';

							//query
							$args=array(
								'post_type' =>  $custom_post_video,
								'author'        =>  $current_user->ID,
								'orderby'       =>  'post_date',
								'order'            => 'DESC',
							);

							$postslist = get_posts( $args );
							if (count($postslist)>0)
							{
								$videoLoop = get_post_meta($postID, 'videoLoop', true); //previous video loop

								$quickCode .= '<SELECT id="loop" name="loop" class="ui dropdown">';
								foreach ( $postslist as $item )
								{
									$video_id = $item->ID;

									//retrieve video stream
									$streamPath = '';
									$videoPath = get_post_meta($video_id, 'video-source-file', true);
									$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

									//use conversion if available
									$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
									if ($videoAdaptive) $videoAlts = $videoAdaptive;
									else $videoAlts = array();

									foreach (array('high', 'mobile') as $frm)
										if (array_key_exists($frm, $videoAlts))
											if ($alt = $videoAlts[$frm])
												if (file_exists($alt['file']))
												{
													$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
													$streamPath = VWliveWebcams::path2stream($alt['file']);
													break;
												};

										//user original
										if (!$streamPath)
											if (in_array($ext, array('flv','mp4','m4v')))
											{
												//use source if compatible
												$streamPath = VWliveWebcams::path2stream($videoPath);
											}

										$quickCode .='<option value="'.htmlspecialchars($streamPath).'" ' .($videoLoop==$streamPath?'selected':''). '>'. $video_id .' ' .$item->post_title.'</option>';

								}
								$quickCode .='</SELECT>';
							}
							else  $quickCode = __('No videos found! Please add some videos first.', 'ppv-live-webcams');


							$playlistPage = add_query_arg(array('editPlaylist'=>$editPlaylist), $this_page);

							$msg1 = __('Quick Loop Setup', 'ppv-live-webcams');
							$msg2 = __('Video will play in loop while playlist is active. Warning: Going live (with real webcam) will stop video loop (needs to be setup again). Can also be disabled by setting Playlist Status as Inactive.', 'ppv-live-webcams');
							$msg3 = __('Set Video Loop', 'ppv-live-webcams');

							$contentCode .= <<<HTMLCODE
					<h3 class="ui header">$msg1</H3>
<form method="post" action="$playlistPage" name="adminForm" class="w-actionbox">
<label>Select Video</label> $quickCode <input class="ui button" type="submit" name="button" id="button" value="$msg3" />
<input type="hidden" name="playlistActive" id="playlistActive" value="1" />
<input type="hidden" name="updatePlaylist" id="updatePlaylist" value="$editPlaylist" />
<BR>$msg2
<br style="clear:both">
</form>
HTMLCODE;


							//playlistActive
							$value = get_post_meta( $editPlaylist, 'vw_playlistActive', true );

							$activeCode .= '<select id="playlistActive" name="playlistActive" class="ui dropdown">';
							$activeCode .= '<option value="0" ' . (!$value ? 'selected' : '') . '>' . __('Inactive', 'ppv-live-webcams') . '</option>';
							$activeCode .= '<option value="1" ' . ($value ? 'selected' : '') . '>' . __('Active', 'ppv-live-webcams') . '</option>';
							$activeCode .= '</select>';

							$value = get_post_meta( $editPlaylist, 'vw_playlistUpdated', true );
							$playlistUpdated = date('Y-m-j h:i:s', (int) $value);

							$value = get_post_meta( $editPlaylist, 'vw_playlistLoaded', true );
							$playlistLoaded = date('Y-m-j h:i:s', (int) $value);



							$videosImg =  plugin_dir_url( __FILE__ ) . 'scripts/jtable/themes/lightcolor/edit.png';

							$channelURL = get_permalink($postID);

							$msg1 = __('Advanced Playlist Editor', 'ppv-live-webcams');
							$msg2 = __('After editing playlist contents below, update it to apply changes. Last Updated:', 'ppv-live-webcams');
							$msg3 = __('Playlist is loaded with web application (on access) and reloaded if necessary when users access', 'ppv-live-webcams');
							$msg4 = __('First create a Schedule (Add new record), then Edit Videos (Add new record under Videos):', 'ppv-live-webcams');
							$msg5 = __('videochat interface', 'ppv-live-webcams');
							$msg6 = __('last time reloaded', 'ppv-live-webcams');

							//! jTable
							$contentCode .= <<<HTMLCODE
					<h3 class="ui header">$msg1</H3>
<form method="post" action="$playlistPage" name="adminForm" class="w-actionbox">
<label>Playlist Status</label> $activeCode <input class="ui button" type="submit" name="button" id="button" value="Update" />
<input type="hidden" name="updatePlaylist" id="updatePlaylist" value="$editPlaylist" />
<BR>$msg2 $playlistUpdated $timezone
<BR>$msg3 <a href='$channelURL'>$msg5</a> ($msg6:  $playlistLoaded $timezone).
</form>
<BR>
$msg4
	<div id="PlaylistTableContainer" style="width: 600px;"></div>
	<script type="text/javascript">

		jQuery(document).ready(function () {

		    //Prepare jTable
			jQuery('#PlaylistTableContainer').jtable({
				title: 'Playlist Contents',
				defaultSorting: 'Order ASC',
				toolbar: {hoverAnimation: false},
				actions: {
					listAction: '$ajaxurl&task=list',
					createAction: '$ajaxurl&task=create',
					updateAction: '$ajaxurl&task=update',
					deleteAction: '$ajaxurl&task=delete'
				},
				fields: {
					Id: {
						key: true,
						create: false,
						edit: false,
						list: false,
					},
					//CHILD TABLE DEFINITION
					Videos: {
                    title: 'Videos',
                    sorting: false,
                    edit: false,
                    create: false,
                    display: function (playlist) {
                        //Create an image that will be used to open child table
                        var vButton = jQuery('<IMG src="$videosImg" /><I>Edit Videos</I>');
                        //Open child table when user clicks the image
                        vButton.click(function () {
                            jQuery('#PlaylistTableContainer').jtable('openChildTable',
                                    vButton.closest('tr'),
                                    {
                                        title: 'Videos for Schedule ' + playlist.record.Scheduled,
                                        actions: {
                                            listAction: '$ajaxurl&task=videolist&item=' + playlist.record.Id,
                                            deleteAction: '$ajaxurl&task=videoremove&item=' + playlist.record.Id,
                                            updateAction: '$ajaxurl&task=videoupdate',
                                            createAction: '$ajaxurl&task=videoadd'
                                        },
                                        fields: {
                                            ItemId: {
                                                type: 'hidden',
                                                defaultValue: playlist.record.Id
                                            },
                                            Id: {
                                                key: true,
                                                create: false,
                                                edit: false,
                                                list: false
                                            },
											Video: {
												title: 'Video',
												options: '$ajaxurl&task=source',
												sorting: false
											},
											Start: {
												title: 'Start',
												defaultValue: '0',
											},
											Length: {
												title: 'Length',
												defaultValue: '-1',
											},
											Order: {
												title: 'Order',
												defaultValue: '0',
											},
	                                    }
                                    }, function (data) { //opened handler
                                        data.childTable.jtable('load');
                                    });
                        });
                        //Return image to show on the person row
                        return vButton;
                    }

                    },
					Scheduled: {
						title: 'Scheduled',
						defaultValue: '$currentDate',
						sorting: false
					},
					Repeat: {
						title: 'Repeat',
						type: 'checkbox',
						defaultValue: '0',
						values: { '0' : 'Disabled', '1' : 'Enabled' },
						sorting: false
					},
					Order: {
						title: 'Order',
						defaultValue: '0',
					}
				}
			});

			//Load item list from server
			jQuery('#PlaylistTableContainer').jtable('load');
		});
	</script>
	<STYLE>
	.ui-widget
	{
		z-index: 1000 !important;
	}
	</STYLE>

HTMLCODE;

							$contentCode .= '<BR>' . __('Schedule playlist items as: Year-Month-Day Hours:Minutes:Seconds in scheduling server timezone. In example, current server OS time', 'ppv-live-webcams') . ': ' . date('y-m-j h:i:s') . ' ' . __('in timezone', 'ppv-live-webcams') . ' ' .date_default_timezone_get() . ' (' . __('default timezone was', 'ppv-live-webcams') . ' '. $defaultTimezone.').';

							if (date_default_timezone_get()) $contentCode .= '<BR>' . __('If the schedule time is in the past, each video is loaded in order and immediately replaces the previous video for the stream. Repeat will cause that videos to repeat in loop.', 'ppv-live-webcams');


							//restore default timezone
							if ($defaultTimezone) date_default_timezone_set($defaultTimezone);


							$contentCode .= '<p>' . __('Scheduled videos play instead of performer live stream in videochat interface. Can be selected from all videos of user.', 'ppv-live-webcams') . '</p>';

							$contentCode .= '
						<br style="clear:both">
   </div>
   ';


						}





					//! pictures tab
					if ($options['picturegallery']) if (shortcode_exists('videowhisper_postpictures'))

							if (VWliveWebcams::inList($userkeys, $options['pictures']))
							{

								$checked = '';
								if ($_GET['gallery_upload'] || $_GET['gallery_import']) $checked = 'active';
								if ($checked) $checked1 = true;

								$headerCode .= '<a class="item ' . $checked .'" data-tab="pictures">' . __('Pictures', 'ppv-live-webcams') . '</a>';
								$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="pictures">';


								/*								$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-pic" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-pic">Pictures</label>

<div class="vwpanel">
       <div class="vwcontent">';
*/
								$contentCode .= do_shortcode("[videowhisper_postpictures_process post=\"$postID\"]");
								$contentCode .= do_shortcode("[videowhisper_postpictures perpage=\"8\" post=\"$postID\"]");

								$contentCode .= '<p>' . __('Multiple profile pictures can show on webcam listing page. Each listing has own pictures.', 'ppv-live-webcams') . '</p>';




								$contentCode .= '
						<br style="clear:both">
   </div>
   ';
							}

						$htmlCode .= apply_filters("vw_plw_dashboard", '', $postID)  ;

					$htmlCode .= apply_filters("vw_plw_dashboard", '', $postID)  ;

				if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['dashboardCSS']));

				$htmlCode .= '<style type="text/css">
.pagination, .pagination > LI {
display: inline !important;
padding: 5px;
}
</style>';

			//! bans tab


			$checked = '';
			if ($banRemove = sanitize_textarea_field($_GET['banRemove'])) $checked = 'active';
			if ($checked) $checked1 = true;

			$headerCode .= '<a class="item ' . $checked .'" data-tab="bans">' . __('Bans', 'ppv-live-webcams') . '</a>';
			$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="bans">';

			$bans = get_post_meta($postID, 'bans',true);

			if ($banRemove && $bans)
			{
				if (is_array($bans))
					foreach ($bans as $key=>$ban)
						if ( $banRemove == base64_encode($ban['user'].$ban['ip'].$ban['expires']) )
						{
							unset($bans[$key]);
							$bansUpdate =1;
							$banInfo .= '<br>' . __('Cleared ban for', 'ppv-live-webcams') . ': '. $ban['user'];
						}

					if (!$bansUpdate) $banInfo .= __('Not found!', 'ppv-live-webcams') ;
			}

			//clean bans
			if ($bans) foreach ($bans as $key=>$ban) if ($ban['expires']<time())
					{
						unset($bans[$key]);
						$bansUpdate =1;
					}
				if ($bansUpdate) update_post_meta($postID, 'bans', $bans);
				/*
				$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-bans" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-bans">' . __('Bans', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">';
       */
				$contentCode .= '<h3 class="ui header">' . __('Bans', 'ppv-live-webcams') . '</h3>' . $banInfo;
			if ($bans)
			{
				foreach ($bans as $ban)
					$contentCode .= '- ' . $ban['user'] . ' ' . $ban['ip'] .' Expires: ' .VWliveWebcams::format_age($ban['expires'] - time()) . ' <a href="'.add_query_arg(array('banRemove'=>urlencode(base64_encode($ban['user'].$ban['ip'].$ban['expires']))), $this_page) .'">remove</a><br>';
			}
			else $contentCode .= __('There are no bans for this room.', 'ppv-live-webcams');

			$contentCode .= '<p>' . __('Bans occur when performer kicks or bans users from videochat interface. Kicks result in short bans (15 minutes) and full bans have longer cooldown (1 week). Bans prevent viewers from accessing or remaining in chat room.', 'ppv-live-webcams') . '</p>
		<br style="clear:both">
   </div>';


			//! credits tab

			//update clients count
			$uid = get_current_user_id();
			VWliveWebcams::billCount($uid, $options) ;

			VWliveWebcams::billCountRoom($postID, $options);
			VWliveWebcams::billSessions(0, $postID);

			//WooWallet Tab
			if ($options['wallet'] == 'WooWallet' || $options['walletMulti']>=1)
				if (shortcode_exists('woo-wallet'))
				{
					$checked = '';
					if ($_GET['wallet_action'] || get_query_var('wallet_action')) $checked = 'active';
					if ($checked) $checked1 = true;

					$headerCode .= '<a class="item ' . $checked .'" data-tab="woowallet">' . __('TeraWallet', 'ppv-live-webcams') . '</a>';
					$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="woowallet">';

					$contentCode .= '<h3 class="ui header">' . __('TeraWallet (WooCommerce)', 'ppv-live-webcams') . '</h3>' . do_shortcode('[woo-wallet]') ;


					$clientsP = get_user_meta($uid, 'paidSessionsPrivate', true);
					$clientsG = get_user_meta($uid, 'paidSessionsGroup', true);
					$contentCode .= '<br>' . __('Logged days', 'ppv-live-webcams') . ': ' . round($options['ppvKeepLogs'] / 3600 / 24,2);
					$contentCode .= '<br>' . __('Logged private sessions billed', 'ppv-live-webcams') . ': ' . $clientsP;
					$contentCode .= '<br>' . __('Logged group sessions billed', 'ppv-live-webcams') . ': ' . $clientsG;
					$contentCode .= '<br>* ' . __('Session logs are kept for limited time and transaction logs forever.', 'ppv-live-webcams');

					$contentCode .= '
	<br style="clear:both">
   </div>';
				}


			//MyCred Transactions
			if ($options['wallet'] == 'MyCred' || $options['walletMulti']>=1)
				if (shortcode_exists('mycred_history'))
				{
					$checked = '';
					if ($_GET['page'] || get_query_var('page')) $checked = 'active';
					if ($checked) $checked1 = true;

					$headerCode .= '<a class="item ' . $checked .'" data-tab="transactions">' . __('My Credits', 'ppv-live-webcams') . '</a>';
					$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="transactions">';

					$contentCode .= '<h3 class="ui header">' . __('My Credits', 'ppv-live-webcams') . '</h3>' . do_shortcode('[mycred_history user_id="current"]') ;

					$clientsP = get_user_meta($uid, 'paidSessionsPrivate', true);
					$clientsG = get_user_meta($uid, 'paidSessionsGroup', true);
					$contentCode .= '<br>' . __('Logged days', 'ppv-live-webcams') . ': ' . round($options['ppvKeepLogs'] / 3600 / 24,2);
					$contentCode .= '<br>' . __('Logged private sessions billed', 'ppv-live-webcams') . ': ' . $clientsP;
					$contentCode .= '<br>' . __('Logged group sessions billed', 'ppv-live-webcams') . ': ' . $clientsG;
					$contentCode .= '<br>* ' . __('Session logs are kept for limited time and transaction logs forever.', 'ppv-live-webcams');

					$contentCode .= '
	<br style="clear:both">
   </div>';
				}


			//! overview tab

			$checked = '';
			if (!$checked1) $checked = 'active';

			$headerCode .= '<a class="item ' . $checked .'" data-tab="overview">' . __('Overview', 'ppv-live-webcams') . '</a>';
			$contentCode .= '<div class="ui ' . $options['interfaceClass'] . ' bottom attached tab segment ' . $checked .'" data-tab="overview">';

			/*
			$htmlCode .= '<div class="vwtab">
       <input type="radio" id="tab-1" name="tab-group-1" '.$checked.'>
       <label class="vwlabel" for="tab-1">' . __('Overview', 'ppv-live-webcams') . '</label>

<div class="vwpanel">
       <div class="vwcontent">
';
*/
			$attach_id = get_post_thumbnail_id($postID);
			if ($attach_id) $thumbFilename = get_attached_file($attach_id);
			$url = get_permalink($postID);

			$noCache='?'.((time()/10)%100);


			$contentCode .= '<h3 class="ui header">' . __('Webcam Room Listing Overview', 'ppv-live-webcams') . '</h3>';

			//$contentCode .= $webcamPost->post_content;

			//get/set performer stream name
			$stream = get_post_meta( $postID, 'performer', true);
			$performerName = VWliveWebcams::performerName($current_user, $options);

			if (!$stream)
			{
				update_post_meta($postID, 'performer', $performerName);
				update_post_meta($postID, 'performerUserID', $current_user->ID );

			}


			if (file_exists($thumbFilename)) $imageCode .=  '<a href="' . $url . '"><IMG class="ui small rounded bordered image right floated" src="' . VWliveWebcams::path2url($thumbFilename) . $noCache .'"></a>';
			else $imageCode .=  '<a href="' . $url . '"><IMG class="ui small rounded bordered image right floated" SRC="' . plugin_dir_url(__FILE__). 'no-picture.png"></a>';


			$contentCode .= '<table class="ui ' . $options['interfaceClass'] . ' selectable striped table two">';

			$contentCode .=  '<tr><td>' . __('Listing (Room) Name', 'ppv-live-webcams') . ': <b>' . $webcamPost->post_title . '</b>' . $imageCode . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Current Room Performer', 'ppv-live-webcams') . ': ' . $stream . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Current Room Performer ID', 'ppv-live-webcams') . ': ' . get_post_meta($postID, 'performerUserID', true) . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Your Performer Name', 'ppv-live-webcams') . ': ' . $performerName . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Listing Label', 'ppv-live-webcams') . ': '.$vw_roomLabel . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Listing Status', 'ppv-live-webcams') . ': ' . $webcamPost->post_status . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Listing URL', 'ppv-live-webcams') . ': ' . $url . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Listing Slug', 'ppv-live-webcams') . ': ' . $webcamPost->post_name . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Listing ID', 'ppv-live-webcams') . ': ' . $webcamPost->ID . '</td></tr>';


			$featured = get_post_meta($postID, 'vw_featured', true);
			if (empty($featured)) update_post_meta($postID, 'vw_featured', 0);
			$contentCode .= '<tr><td>' . __('Featured', 'ppv-live-webcams') . ': ' . ($featured?__('Yes','ppv-live-webcams'):__('No','ppv-live-webcams')) . '</td></tr>';



			$contentCode .=  '<tr><td>' . __('Client CPM', 'ppv-live-webcams') . ': '. VWliveWebcams::clientCPM($webcamPost->post_title, $options, $postID) . '</td></tr>';
			$contentCode .=  '<tr><td>' . __('Earning Ratio', 'ppv-live-webcams') . ': '. VWliveWebcams::performerRatio($webcamPost->post_title, $options, $postID)*100 . '%' . '</td></tr>';


			if ($options['videosharevod']) if (shortcode_exists("videowhisper_videos"))
				{
					$vw_videos = get_post_meta($postID, 'vw_videos',true);
					$contentCode .= '<tr><td>' . __('Videos', 'ppv-live-webcams') . ': ' . ($vw_videos?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')) . '</td></tr>';
				}

			if ($options['picturegallery']) if (shortcode_exists("videowhisper_pictures"))
				{
					$vw_pictures = get_post_meta($postID, 'vw_pictures',true);
					$contentCode .= '<tr><td>' . __('Pictures', 'ppv-live-webcams') . ': ' . ($vw_pictures?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')) . '</td></tr>';
				}

			if ($options['transcoding'])
			{
				$vw_transcode = get_post_meta($postID, 'vw_transcode',true);
				$contentCode .= '<tr><td>' . __('HTML5 Transcoding', 'ppv-live-webcams') . ': ' . ($vw_transcode?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')) . '</td></tr>';
			}

			if ($options['multicamMax'])
			{
				$vw_multicam = get_post_meta($postID, 'vw_multicam', true);
				$contentCode .= '<tr><td>' . __('Multiple Cameras', 'ppv-live-webcams') . ': ' . ($vw_multicam?"Yes ($vw_multicam extra)":'No') . '</td></tr>';
			}


			if ($options['playlists'])
				if (VWliveWebcams::inList($userkeys, $options['schedulePlaylists']))
				{
					$vw_playlistActive = get_post_meta( $postID, 'vw_playlistActive', true);
					$contentCode .= '<tr><td>' . __('Scheduled Playlist', 'ppv-live-webcams') . ': ' . ($vw_playlistActive?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')) . '</td></tr>';
				}


			$contentCode .=  '<tr><td>' . __('Description', 'ppv-live-webcams') . ': '.$webcamPost->post_content . '</td></tr>';

			$contentCode .= '</table>';


			$contentCode .= '
		<br style="clear:both">
   </div>
   ';


			//build tabs
			$headerCode .= '</div>';
			$htmlCode .= $headerCode . $contentCode ;

			if ($options['dashboardMessageBottom']) $htmlCode .= '<div id="performerDashboardMessageBottom" class="ui ' . $options['interfaceClass'] .' segment">'. html_entity_decode(stripslashes($options['dashboardMessageBottom'])) .'</div>';

			$htmlCode .= '<br style="clear:both">';

			return $htmlCode;
		}


		//! Performer Registration and Login

		static function register_form()
		{

			$options = get_option('VWliveWebcamsOptions');

			if (!$options['registrationFormRole']) return;

			$roles = array($options['roleClient'], $options['rolePerformer']);

			if ($options['studios']) $roles[] = $options['roleStudio']; //add studio if enabled

			echo '<label for="role"> ' . __('Role', 'ppv-live-webcams') . '<br><select id="role" name="role" class="ui dropdown">';
			foreach ($roles as $role)
			{
				//create role if missing
				if (!$oRole = get_role($role))
				{
					add_role($role, ucwords($role), array('read' => true) );
					$oRole = get_role($role);
				}

				echo '<option value="' . $role . '">' . ucwords($oRole->name) . '</option>';

			}
			echo '</select></label>';
		}


		static function user_register($user_id, $password="", $meta=array())
		{
			$options = get_option('VWliveWebcamsOptions');
			if (!$options['registrationFormRole']) return;

			$userdata = array();
			$userdata['ID'] = (int) $user_id;
			$userdata['role'] = sanitize_file_name($_POST['role']);

			//restrict registration roles
			$roles = array($options['roleClient'], $options['rolePerformer']);

			if (in_array( $userdata['role'], $roles ))
				wp_update_user($userdata);
		}


		static function login_logo() {

			$options = get_option('VWliveWebcamsOptions');

			if ($options['loginLogo'])
			{
?>
    <style type="text/css">
         #login h1 a, .login h1 a  {
            background-image: url(<?php echo $options['loginLogo']; ?>);
			background-size: 240px 80px;
			width: 240px;
			height: 80px;
        }
    </style>
  	<?php
			}
			/*			else
			{
?>
	    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png);
            padding-bottom: 30px;
        }
    </style>
	    <?php

			}*/
		}


		function login_redirect( $redirect_to, $request, $user ) {

			global $user;

			//wp_users & wp_usermeta
			//$user = get_userdata(get_current_user_id());

			if ( isset( $user->roles ) && is_array( $user->roles ) ) {
				//check for admins
				if ( in_array( 'administrator', $user->roles ) ) {
					// redirect them to the default place
					return $redirect_to;
				} else {

					$options = get_option('VWliveWebcamsOptions');

					//performer to dashboard
					if ( in_array(  $options['rolePerformer'], $user->roles ) )
					{
						$pid = $options['p_videowhisper_webcams_performer'];
						if ($pid) return get_permalink($pid);
						else return $redirect_to;
					}

					//studio to dashboard
					if ( in_array(  $options['roleStudio'], $user->roles ) )
					{
						$pid = $options['p_videowhisper_webcams_studio'];
						if ($pid) return get_permalink($pid);
						else return $redirect_to;
					}

					//client to webcams list
					if ( in_array(  $options['roleClient'], $user->roles ) )
					{
						$pid = $options['p_videowhisper_webcams'];
						if ($pid) return get_permalink($pid);
						else return $redirect_to;
					}


				}
			} else {
				return $redirect_to;
			}
		}


		//! Billing Integration: MyCred, WooWallet

		static function balances($userID, $options = null)
		{
			//get html code listing balances
			if (!$options) $options = get_option('VWliveWebcamsOptions');
			if (!$options['walletMulti']) return ''; //disabled

			$balances = VWliveWebcams::walletBalances($userID, '', $options);

			$walletTransfer = sanitize_text_field( $_GET['walletTransfer'] );

			global $wp;
			foreach ($balances as $key=>$value)
			{
				$htmlCode .= '<br>'. $key . ': ' . $value;

				if ($options['walletMulti'] == 2 && $walletTransfer != $key && $options['wallet'] != $key && $value>0) $htmlCode .= ' <a class="ui button compact tiny" href=' . add_query_arg(array('walletTransfer'=>$key),$wp->request) . ' data-tooltip="Transfer to Active Balance">Transfer</a>';

				if ($walletTransfer == $key || ($value>0 && $options['walletMulti'] == 3 && $options['wallet'] != $key))
				{
					VWliveWebcams::walletTransfer($key, $options['wallet'], get_current_user_id(), $options);
					$htmlCode .= ' Transferred to active balance.';
				}

			}


			return $htmlCode;
		}


		static function walletBalances($userID, $view = 'view', $options = null)
		{
			$balances = array();
			if (!$userID) return $balances;

			//woowallet
			if ($GLOBALS['woo_wallet'])
			{
				$wooWallet = $GLOBALS['woo_wallet'];
				$balances['WooWallet'] = $wooWallet->wallet->get_wallet_balance( $userID, $view);
			}

			//mycred
			if (function_exists( 'mycred_get_users_balance')) $balances['MyCred'] = mycred_get_users_balance($userID);

			return  $balances;
		}


		static function walletTransfer($source, $destination, $userID, $options = null)
		{
			//transfer balance from a wallet to another wallet

			if ($source == $destination) return;

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			$balances = VWliveWebcams::walletBalances($userID, '', $options);

			if ($balances[$source] > 0)
			{
				VWliveWebcams::walletTransaction($destination, $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
				VWliveWebcams::walletTransaction($source, - $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
			}

		}


		static function walletTransaction($wallet, $amount, $user_id, $entry, $ref, $ref_id = null, $data = null)
		{
			//transactions on all supported wallets
			//$wallet : MyCred/WooWallet

			if ($amount == 0) return; //no transaction

			//mycred
			if ($wallet == 'MyCred')
				if ($amount>0)
				{
					if (function_exists('mycred_add')) mycred_add($ref, $user_id, $amount, $entry, $ref_id, $data);
				}
			else
			{
				if (function_exists('mycred_subtract')) mycred_subtract( $ref, $user_id, $amount, $entry, $ref_id, $data );
			}

			//woowallet
			if ($wallet == 'WooWallet')
				if ($GLOBALS['woo_wallet'])
				{
					$wooWallet = $GLOBALS['woo_wallet'];

					if ($amount>0)
					{
						$wooWallet->wallet->credit( $user_id, $amount, $entry );
					}
					else
					{
						$wooWallet->wallet->debit( $user_id, -$amount, $entry );
					}

				}

		}


		static function balance($userID, $live = false, $options = null)
		{
			//get current user balance (as value)
			// $live also estimates active (incomplete) session costs for client

			if (!$userID) return 0;

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			$balance = 0;

			$balances = VWliveWebcams::walletBalances($userID, '', $options);

			if ($options['wallet'])
				if (array_key_exists($options['wallet'], $balances)) $balance = $balances[$options['wallet']];

				if ($live)
				{
					$updated = get_user_meta($userID, 'vw_ppv_tempt', true);

					if (time() - $updated < 15) //updated recently: use that estimation
						$temp = get_user_meta($userID, 'vw_ppv_temp', true);
					else $temp = VWliveWebcams::billSessions($userID, 0, false); //estimate charges for current sessions

					$balance = $balance - $temp; //deduct temporary charge
				}

			return $balance;
		}


		static function transaction($ref = "ppv_live_webcams", $user_id = 1, $amount = 0, $entry = "PPV Live Webcams transaction.", $ref_id = null, $data = null, $options = null)
		{
			//ref = explanation ex. ppv_client_payment
			//entry = explanation ex. PPV client payment in room.
			//utils: ref_id (int|string|array) , data (int|string|array|object)

			if ($amount == 0) return; //nothing


			if (!$options) $options = get_option('VWliveWebcamsOptions');

			//active wallet
			if ($options['wallet']) $wallet = $options['wallet'];
			if (!$wallet) $wallet = 'MyCred';
			if (!function_exists('mycred_add')) if ($GLOBALS['woo_wallet']) $wallet = 'WooWallet';


				VWliveWebcams::walletTransaction($wallet, $amount, $user_id, $entry, $ref, $ref_id, $data);
		}


		//! PPV Calculations

		static function billCount($uid, $options = null)
		{
			// counts number of paid sessions (billed)
			// $uid = performer id

			if (!$uid) return;
			if (!$options) $options = get_option('VWliveWebcamsOptions');

			global $wpdb;
			$table_private = $wpdb->prefix . "vw_vmls_private";
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$sql = "SELECT COUNT(id) as no FROM $table_private WHERE status = 2 AND pid=$uid";
			$clients = $wpdb->get_row($sql);
			update_user_meta($uid, 'paidSessionsPrivate', $clients->no);

			$sql = "SELECT COUNT(id) as no FROM $table_sessions WHERE status = 2 AND uid=$uid";
			$clients = $wpdb->get_row($sql);
			update_user_meta($uid, 'paidSessionsGroup', $clients->no);

		}


		static function billCountRoom($rid, $options = null)
		{
			// counts number of paid sessions (billed)
			// $rid = room id

			if (!$rid) return;
			if (!$options) $options = get_option('VWliveWebcamsOptions');

			global $wpdb;
			$table_private = $wpdb->prefix . "vw_vmls_private";
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$sql = "SELECT COUNT(id) as no FROM $table_private WHERE status = 2 AND rid=$rid";
			$clients = $wpdb->get_row($sql);
			update_post_meta($rid, 'paidSessionsPrivate', $clients->no);

			$sql = "SELECT COUNT(id) as no FROM $table_sessions WHERE status = 2 AND rid=$rid";
			$clients = $wpdb->get_row($sql);
			update_post_meta($rid, 'paidSessionsGroup', $clients->no);
		}


		static function billSessions($uid=0, $rid=0, $complete = true)
		{
			//$uid = process for user_id
			//$rid = process for postID (wecam/room)
			//$complete = if not, just estimate temp charge for client ($uid required)

			$options = get_option('VWliveWebcamsOptions');

			$closeTime = time() - intval($options['ppvCloseAfter']);
			$billTime = time() - intval($options['ppvBillAfter']);
			$logTime = time() - intval($options['ppvKeepLogs']);

			global $wpdb;
			$table_private = $wpdb->prefix . "vw_vmls_private";
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$cStatus = "status=1";

			// temporary charge per account (!$complete)
			$temp = 0;
			if (!$complete)
			{
				$cStatus = '(status=0 OR status=1)'; //process open, closed
				$billTime = time()+1; //process all including recent, !! +1 because is updated on same request
			}

			//! bill Private videochat sessions

			if ($complete)
			{
				//force clean and close sessions terminated abruptly

				//delete where only 1 entered (other could have accepted and quit) or stayed 0s, except calls
				$sql="DELETE FROM `$table_private` WHERE call = '0' AND (status=0 OR status=1) AND ((cedate=0 AND pedate < $closeTime) OR (pedate=0 AND cedate < $closeTime) OR (pedate=psdate AND pedate < $closeTime) OR (cedate=csdate AND cedate < $closeTime))";
				$wpdb->query($sql);

				//close rest, where both entered
				$sql="UPDATE `$table_private` SET status='1' WHERE psdate > 0 AND csdate > 0 AND status='0' AND pedate < $closeTime AND cedate < $closeTime";
				$wpdb->query($sql);
			}


			//bill private sessions
			if ($uid) $cnd = "AND (pid=$uid OR cid=$uid)";
			else $cnd = '';

			$sql = "SELECT * FROM $table_private WHERE $cStatus AND pedate < $billTime AND cedate < $billTime $cnd";
			$sessions = $wpdb->get_results($sql);
			if ($wpdb->num_rows>0) foreach ($sessions as $session)
					$temp += VWliveWebcams::billSession($session, $complete);

				if ($complete)
				{
					// clean private session logs, except calls
					$sql="DELETE FROM `$table_private` WHERE call = '0' AND pedate < $logTime AND cedate < $logTime AND status ='2' $cnd";
					$wpdb->query($sql);
				}

			//! bill Group videochat sessions
			if ($uid) $cnd = "AND uid=$uid";
			else $cnd = '';
			if ($rid) $cnd .=" AND rid=$rid";

			if ($complete)
			{
				//delete where performer did not enter (redate) until user left (edate)
				$sql="DELETE FROM `$table_sessions` WHERE (status=0 OR status=1) AND (rsdate = 0 AND edate < $closeTime)";
				$wpdb->query($sql);

				//update rest to status = 1
				$sql="UPDATE `$table_sessions` SET status='1' WHERE status='0' AND edate < $closeTime";
				$wpdb->query($sql);
			}

			$sql = "SELECT * FROM $table_sessions WHERE $cStatus AND edate < $billTime $cnd";
			$sessions = $wpdb->get_results($sql);
			if ($wpdb->num_rows>0) foreach ($sessions as $session)
					$temp += VWliveWebcams::billGroupSession($session, $complete);

				if ($complete)
				{
					// clean group session logs
					$sql="DELETE FROM `$table_sessions` WHERE edate < $logTime AND status ='2' $cnd";
					$wpdb->query($sql);
				}

			//update temp charge
			if ($uid)
			{
				if (!$complete)
				{
					update_user_meta($uid, 'vw_ppv_temp', $temp);
					update_user_meta($uid, 'vw_ppv_tempt', time());
				}
				else
				{
					update_user_meta($uid, 'vw_ppv_temp', 0);
					update_user_meta($uid, 'vw_ppv_tempt', time());
				}
			}

			return $temp;

		}


		static function clientCPM($room_name, $options='', $postID = 0)
		{
			if (!$options) $options = get_option('VWliveWebcamsOptions');

			//custom room cost per minute
			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
			}

			if ($postID) $CPM = get_post_meta( $postID, 'vw_costPerMinute', true );

			if ($CPM == '') $CPM = $options['ppvPPM'];

			if ($options['ppvPPMmin']) if ($CPM < $options['ppvPPMmin']) $CPM = $options['ppvPPMmin'];
				if ($options['ppvPPMmax']) if ($CPM > $options['ppvPPMmax']) $CPM = $options['ppvPPMmax'];

					return $CPM;
		}


		static function performerRatio($room_name, $options='', $postID = null)
		{

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			//custom performer ratio
			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
			}

			if ($postID) $Ratio = get_post_meta( $postID, 'vw_earningRatio', true );

			if ($Ratio == '') $Ratio = $options['ppvRatio'];

			return $Ratio;
		}


		static function billGroupSession($session, $complete = true)
		{
			//bills group session
			//if not $complete just returns temporary charge estimation

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			//performer (broadcaster) should not get billed in group session
			if ($session->broadcaster) return 0;

			$options = get_option('VWliveWebcamsOptions');

			$roomOptions = unserialize($session->roptions);
			$clientCPM = $roomOptions['cpm'];
			$sessionStart = $roomOptions['sessionStart'];

			$checkin = $roomOptions['checkin'];


			if (!$clientCPM) return 0;

			if (!$complete) return VWliveWebcams::clientGroupCost($session, $clientCPM, $sessionStart);


			$end = min($session->edate, $session->redate); //when first left
			$start = max($session->sdate, $session->rsdate); //when last entered

			$totalDuration = $end - $start;
			$duration = $totalDuration - $options['ppvGraceTime'];
			if ($duration < 0) return ; //graced - nothing to bill


			$timeStamp = date("M j G:i", $start) . ' ' . ceil($duration / 60) . 'm';

			$charge = number_format($duration * $clientCPM / 60, 2, '.', '');

			if (!$complete) return $charge;

			//client cost
			if ($clientCPM>0) VWliveWebcams::transaction('ppm_group', $session->uid, - $charge, __(__('PPM group', 'ppv-live-webcams'), 'ppv-live-webcams') . ' ' . $roomOptions['groupMode'] . ' ' . $roomOptions['userMode'] . ' ' . __('session in', 'ppv-live-webcams') . ' <a href="' . VWliveWebcams::roomURL($session->room) . '">' . $session->room .'</a> ' . $timeStamp , $session->id);

			//checkin perfomer payments

			if ($checkin)
			{

				if (!is_array($checkin)) $checkin = array($checkin);

				$divider = count($checkin);
				if (!$divider) return;

				$share = number_format($duration * $clientCPM / ($divider * 60) , 2, '.', '');

				$performerRatio = VWliveWebcams::performerRatio($session->room, $options);

				//$percent = round(100/$divider);

				foreach ($checkin as $performerID)
				{
					VWliveWebcams::transaction('ppm_group_earn', $performerID, $share * $performerRatio , __('Earning from PPM group', 'ppv-live-webcams') . ' ' . $roomOptions['groupMode'] . ' ' . $roomOptions['userMode'] . __(' session with ', 'ppv-live-webcams') . $session->username .' ' . $timeStamp , $session->id);
				}

			}

			//mark group session as billed

			$sql="UPDATE `$table_sessions` SET status='2' WHERE id=" . $session->id;
			$wpdb->query($sql);

			return 0; // billed: no temp charge
		}


		static function billSession($session, $complete = true)
		{
			if (!$complete) return VWliveWebcams::clientCost($session);

			//bills private session
			$options = get_option('VWliveWebcamsOptions');

			$clientCPM = VWliveWebcams::clientCPM($session->room, $options);
			$performerRatio = VWliveWebcams::performerRatio($session->room, $options);

			if (!$options['ppvPerformerPPM'] && !$clientCPM) return ;
			if (!$session) return 0;
			if ($session->pedate == 0 || $session->cedate == 0 ) return 0; //did not enter both

			$end = min($session->pedate, $session->cedate);
			$start = max($session->psdate, $session->csdate);
			$totalDuration = $end - $start;
			$duration = $totalDuration - $options['ppvGraceTime'];
			if ($duration < 0) return 0; //graced - nothing to bill

			$startDate = ' ' . date(DATE_RFC2822, $start);

			$timeStamp = date("M j G:i", $start) . ' ' . ceil($duration / 60) . 'm';

			$charge = number_format($duration * $clientCPM / 60, 2, '.', '');
			if (!$complete) return $charge; //just return estimate

			//client cost
			if ($clientCPM>0) VWliveWebcams::transaction('ppm_private', $session->cid, - $charge, 'PPM private session with <a href="' . VWliveWebcams::roomURL($session->room) . '">' . $session->performer.'</a> ' . $timeStamp, $session->id);

			//performer earning
			if ($clientCPM>0 && $performerRatio>0) VWliveWebcams::transaction('ppm_private_earn', $session->pid, number_format($duration * $clientCPM * $performerRatio / 60, 2, '.', ''), __('Earning from PPM private session with ', 'ppv-live-webcams') . $session->client . ' '. $timeStamp, $session->id);

			//performer cost
			if ($options['ppvPerformerPPM']>0) VWliveWebcams::transaction('ppm_private', $session->pid, - number_format($duration * $options['ppvPerformerPPM'] / 60, 2, '.', ''), __('Performer cost for PPM private session with ', 'ppv-live-webcams') . $session->performer . ' '. $timeStamp , $session->id);


			//mark private session as billed
			global $wpdb;
			$table_private = $wpdb->prefix . "vw_vmls_private";

			$sql="UPDATE `$table_private` SET status='2' WHERE id=" . $session->id;
			$wpdb->query($sql);

			return 0;
		}


		//calculate current cost (no processing)
		static function clientCost($session)
		{
			$options = get_option('VWliveWebcamsOptions');

			$clientCPM = VWliveWebcams::clientCPM($session->room, $options);

			if (!$clientCPM) return 0;
			if (!$session) return 0;
			if ($session->pedate == 0 || $session->cedate == 0 ) return 0; //did not enter both

			//duration when both online: max(psdate,csdate)->min(pedate,cedate)
			$duration = min($session->pedate, $session->cedate) - max($session->psdate, $session->csdate) - $options['ppvGraceTime'];
			if ($duration < 0) return 0; //grace

			return number_format($duration * $clientCPM / 60, 2, '.', '');
		}


		//calculate current group cost (no processing)
		static function clientGroupCost($session, $clientCPM, $sessionStart = 0)
		{

			//$roomOptions['sessionStart']

			$options = get_option('VWliveWebcamsOptions');

			if (!$clientCPM) return 0;
			if (!$session) return 0;
			if ($session->edate == 0) return 0; //did not enter

			//duration when both online: max(psdate,csdate)->min(pedate,cedate)
			$duration = min($session->edate, $session->redate) - max($sessionStart, $session->sdate) - $options['groupGraceTime'];
			if ($duration < 0) return 0; //grace

			return number_format($duration * $clientCPM / 60, 2, '.', '');
		}


		static function performerCost($session)
		{
			//$session = private session

			$options = get_option('VWliveWebcamsOptions');

			if (!$options['ppvPerformerPPM']) return 0;
			if (!$session) return 0;
			if ($session->pedate == 0 || $session->cedate == 0 ) return 0; //did not enter both

			//duration when both online: max(psdate,csdate)->min(pedate,cedate)
			$duration = min($session->pedate, $session->cedate) - max($session->psdate, $session->csdate) - $options['ppvGraceTime'];
			if ($duration < 0) return 0; //grace

			return number_format($duration*$options['ppvPerformerPPM']/60, 2, '.', '');
		}


		//! Online user functions

		static function currentUserSession($room)
		{
			if (!is_user_logged_in()) return 0;

			$current_user = wp_get_current_user();

			$options = get_option('VWliveWebcamsOptions');

			$username1 = $current_user->${$options['userName']};
			$username2 = $current_user->${$options['webcamName']};

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			$sql = "SELECT * FROM `$table_sessions` WHERE (session='$username1' OR session='$username2') AND room='$room' AND status='1' LIMIT 1";
			$session = $wpdb->get_row($sql);

			return $session;
		}


		static function webcamOnline($postID)
		{
			$last = time() - (int) get_post_meta($postID, 'edate', true);

			$options = get_option('VWliveWebcamsOptions');

			if ($last < $options['onlineTimeout']) return true;
			else return false;
		}


		static function isPerformer($userID, $postID)
		{
			//is specified user a performer for this room
			if (!$userID) return 0;
			if (!$postID) return 0;

			$current_user = get_userdata($userID);
			if (!$current_user) return 0;
			if (!$current_user->ID) return 0;


			$post = get_post( $postID );
			if (!$post) return 0;

			//owner
			if ($post->post_author == $current_user->ID) return 1;

			//performer (post owner is studio)
			if (get_post_meta($postID, 'performerID', true) == $current_user->ID) return 1;

			//multi performer posts
			$performerIDs = get_post_meta($postID, 'performerID', false);
			if ($performerIDs) if (is_array($performerIDs))
					if (in_array($current_user->ID, $performerIDs)) return 1;

					return 0;

		}


		static function isAuthor($postID)
		{
			//is current user author (owner or assigned perfomer)
			//includes post author and assigned by studio (single or multi perfomer room as performerID)

			if (!$postID) return 0;

			$current_user = wp_get_current_user();
			if (!isset($current_user)) return 0;
			if (!$current_user) return 0;
			if (!$current_user->ID) return 0;


			$post = get_post( $postID );
			if (!$post) return 0;

			//owner
			if ($post->post_author == $current_user->ID) return 1;

			//performer (post owner is studio)
			if (get_post_meta($postID, 'performerID', true) == $current_user->ID) return 1;

			//multi performer posts
			$performerIDs = get_post_meta($postID, 'performerID', false);
			if ($performerIDs) if (is_array($performerIDs))
					if (in_array($current_user->ID, $performerIDs)) return 1;

					return 0;
		}


		static function updateViewers($postID, $room, $options)
		{
			if (!VWliveWebcams::timeTo($room . '-updateViewers', 30, $options)) return;

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

			//close sessions
			$closeTime = time() - intval($options['ppvCloseAfter']); // > client statusInterval
			$sql="UPDATE `$table_sessions` SET status = 1 WHERE status = 0 AND edate < $closeTime";
			$wpdb->query($sql);

			//update viewers
			$viewers =  $wpdb->get_var("SELECT count(id) as no FROM `$table_sessions` where status='0' and room='" . $room . "'");
			update_post_meta($postID, 'viewers', $viewers);

			$maxViewers = get_post_meta($postID, 'maxViewers', true);

			if ($viewers >= $maxViewers)
			{
				update_post_meta($postID, 'maxViewers', $viewers);
				update_post_meta($postID, 'maxDate', time());
			}

		}


		static function timeTo($action, $expire = 60, $options='')
		{
			//if $action was already done in last $expire, return false

			if (!$options) $options = get_option('VWliveWebcamsOptions');

			$cleanNow = false;


			$ztime = time();


			$lastClean = 0;

			//saves in specific folder
			$timersPath = $options['uploadsPath'];
			if (!file_exists($timersPath)) mkdir($timersPath);
			$timersPath .= '/_timers/';
			if (!file_exists($timersPath)) mkdir($timersPath);

			$lastCleanFile = $timersPath . $action . '.txt';

			if (!file_exists($dir = dirname($lastCleanFile))) mkdir($dir);
			elseif (file_exists($lastCleanFile)) $lastClean = file_get_contents($lastCleanFile);

			if (!$lastClean) $cleanNow = true;
			else if ($ztime - $lastClean > $expire) $cleanNow = true;

				if ($cleanNow)
					file_put_contents($lastCleanFile, $ztime);


				return $cleanNow;

		}


		//! App Calls

		static function editParameters($default = '', $update = array(), $remove = array())
		{
			//adjust parameters string by update(add)/remove

			parse_str(substr($default,1), $params);

			//remove

			if (count($update)) foreach ($params as $key => $value)
					if (in_array($key, $update)) unset($params[$key]);

					if (count($remove)) foreach ($params as $key => $value)
							if (in_array($key, $remove)) unset($params[$key]);


							//add updated
							if (count($update)) foreach ($update as $key => $value) $params[$key] = $value;

								return '&' . http_build_query($params);
		}


		static function rexit($output)
		{
			echo $output;
			exit;

		}


		static function isRoomPerformer($post, $current_user)
		{

			if ($post->post_author == $current_user->ID) return 1;

			//assigned performer
			$performerIDs = get_post_meta($post->ID, 'performerID', false);
			if ($performerIDs) if (is_array($performerIDs))
					if (in_array($current_user->ID, $performerIDs)) return 1;

					return 0;
		}


		static function rtmpServer($postID, $options ='')
		{

			$rtmp_server = '';
			if ($postID) $rtmp_server = get_post_meta( $postID, 'rtmp_server', true);

			if ($rtmp_server) return $rtmp_server;
			else
			{
				if (!$options) $options = get_option('VWliveWebcamsOptions');
				return $options['rtmp_server'];
			}

		}


		static function webSessionSave($username, $canKick=0, $debug = '0', $ip = '')
		{
			//generates a session file record on web server for rtmp login check
			//means: this user was allowed by web server (previous web login or key), for more advanced control during session use rtmp seasion control

			$username = sanitize_file_name($username);

			if ($username)
			{
				$options = get_option('VWliveWebcamsOptions');
				$webKey = $options['webKey'];
				$ztime = time();

				$ztime=time();
				$info = 'VideoWhisper=1&login=1&webKey='. urlencode($webKey) . '&start=' . $ztime . '&ip=' . urlencode($ip) . '&canKick=' . $canKick . '&debug=' . urlencode($debug);

				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				//@chmod($dir, 0777);
				$dir.="/_sessions";
				if (!file_exists($dir)) mkdir($dir);
				//@chmod($dir, 0777);

				$dfile = fopen($dir."/$username","w");
				fputs($dfile,$info);
				fclose($dfile);
			}

		}


		function vmls_callback()
		{

			//error_reporting(E_ALL);
			ini_set('display_errors', 'On');

			global $wpdb;
			$options = get_option('VWliveWebcamsOptions');

			ob_clean();

			switch ($_GET['task'])
			{

				//! WebLogin ajax calls
				//! rtmp_logout
			case 'rtmp_logout':

				//rtmp server notifies client disconnect here
				$session = $_GET['s'];

				$session = sanitize_file_name( $session );
				if (!$session) exit;

				$options = get_option('VWliveWebcamsOptions');
				$dir=$options['uploadsPath'];

				echo "logout=";
				$filename1 = $dir ."/_sessions/$session";

				if (file_exists($filename1))
				{
					echo unlink($filename1); //remove session file
				}
				?><?php
				break;
				//! rtmp_login
			case 'rtmp_login':

				//when external app connects to streaming server, it will call this to confirm and then accept/reject
				//rtmp server should check login like rtmp_login.php?s=$session&p[]=$username&p[]=$room&p[]=$key&p[]=$broadcaster&p[]=$broadcasterID&p[]=$IP
				//p[] = params sent with rtmp address (key, channel)

				$session = $_GET['s'];
				$session = sanitize_file_name( $session );
				if (!$session) exit;

				$p =  $_GET['p'];

				if (count($p))
				{
					$username = $p[0]; //or sessionID
					$room = $p[1]; //room, webcam listing post name
					$key = $p[2];
					$performer = $broadcaster = ($p[3] === 'true' || $p[3] === '1'); //performer
					$userID = $broadcasterID = $p[4]; //userID
				}

				$ip = '';
				if (count($p)>=5)  $ip = $p[5]; //ip detected from streaming server


				$postID = 0;
				$ztime = time();

				$options = get_option('VWliveWebcamsOptions');

				global $wpdb;
				$wpdb->flush();
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post']. "' LIMIT 0,1" );

				//verify if performer when trying to access as performer user (prevent hijacking)
				$invalid = 0;
				//only invalidate if trying to access as performer username as clients can broadcast in 2w
				if ($broadcaster && $username == get_post_meta($postID, 'performer'))
					if (!VWliveWebcams::isPerformer($userID, $postID)) $invalid = 1;

					//verify
					$verified = 0;
				//rtmp key login for external apps: only for external apps is validated based on secret key, local app sessions should be already validated
				if (!$invalid)
					if ($broadcaster=='1') //external broadcaster
						{
						$validKey = md5('vw' . $options['webKey'] . $userID . $postID);

						if ($key == $validKey)
						{
							$verified = 1;

							VWliveWebcams::webSessionSave($session, 1, 'rtmp_login_broadcaster', $ip);

							//detect transcoding to not alter source info
							$transcoding = 0;
							$stream_webrtc = $room . '_webrtc';
							$stream_hls = 'i_' . $room;
							if ($username == $stream_hls || $username == $stream_webrtc) $transcoding = 1;

							if ($postID && !$transcoding)
							{
								update_post_meta($postID, 'stream-protocol', 'rtmp');
								update_post_meta($postID, 'stream-type', 'external');
								update_post_meta($postID, 'stream-updated', $ztime);
							}

						}

					}
				elseif ($broadcaster=='0') //external watcher
					{
					$validKeyView = md5('vw' . $options['webKey']. $postID);
					if ($key == $validKeyView)
					{
						$verified = 1;

						VWliveWebcams::webSessionSave($session, 0, 'rtmp_login_viewer', $ip);
					}

				}

				//after previously validaded session (above or by local apps login), returning result that was saved above

				//validate web login to streaming server
				$dir = $options['uploadsPath'];
				$filename1 = $dir ."/_sessions/$session";
				if (file_exists($filename1)) //web login present
					{
					echo implode('', file($filename1));
					if ($broadcaster) echo '&role=' . $broadcaster;
				}
				else
				{
					//VideoWhisper=1&login=0&nsf=1&v=0&i=1&s=1482012032&p=5540&u=1
					echo "VideoWhisper=1&login=0&nsf=1&v=$verified&i=$invalid&s=$session&p=$postID&u=$userID";
				}

				//also update RTMP server IP in settings after authentication
				if ($verified)
				{


					if (in_array($options['webStatus'], array('auto','enabled'))) //in strict mode does not add IPs
						{
						$ip = VWliveWebcams::get_ip_address();;
						if (!strstr($options['rtmp_restrict_ip'], $ip))  //add ip only if missing
							{
							$options['rtmp_restrict_ip'] .= ($options['rtmp_restrict_ip']?',':'') . $ip;
							$updateOptions=1;
							echo '&rtmp_restrict_ip=' . $options['rtmp_restrict_ip'];
						}
					}

					//also enable webStatus if on auto (now secure with IP restriction enabled)
					if ($options['webStatus'] == 'auto')
					{
						$options['webStatus'] = 'enabled';
						$updateOptions=1;
						echo '&webStatus=' . $options['webStatus'];
					}

					if ($updateOptions) update_option('VWliveWebcamsOptions', $options);

				}


				?><?php
				break;


				//! rtmp_status : call from rtmp server, session control for rtmp, webrtc streams
			case 'rtmp_status':

				$options = get_option('VWliveWebcamsOptions');

				//allow such requests only if feature is enabled (by default is not)
				if (!in_array($options['webStatus'], array('enabled', 'strict'))) VWliveWebcams::rexit('denied=webStatusNotEnabled-' . $options['webStatus']);

				//allow only status updates from configured server IP
				if ($options['rtmp_restrict_ip'])
				{
					$allowedIPs = explode(',', $options['rtmp_restrict_ip']);
					$requestIP = VWliveWebcams::get_ip_address();

					$found = 0;
					foreach ($allowedIPs as $allowedIP)
						if ($requestIP == trim($allowedIP)) $found = 1;

						if (!$found) VWliveWebcams::rexit('denied=NotFromAllowedIP-' . $requestIP);

				} else VWliveWebcams::rexit('denied=StatusServerIPnotConfigured');

				self::requirementMet('rtmp_status');

				//start logging
				$dir = $options['uploadsPath'];
				$filename1 = $dir ."/_rtmpStatus.txt";
				$dfile = fopen($filename1,"w");

				fputs($dfile,'VideoWhisper Log for RTMP Session Control'. "\r\n");
				fputs($dfile,"Server Date: ". "\r\n" . date("D M j G:i:s T Y"). "\r\n" );
				fputs($dfile, '$_POST:'. "\r\n" . serialize($_POST));

				$debugInfo = '';

				$ztime=time();

				$controlUsers = array();
				$controlSessions = array();

				//! RTP (WebRTC) sessions
				$rtpsessiondata = stripslashes($_POST['rtpsessions']);

				if (version_compare(phpversion(), '7.0', '<')) $rtpsessions = unserialize($rtpsessiondata);  //request is from trusted server
				else $rtpsessions = unserialize($rtpsessiondata, array());


				$webrtc_test = 0;
				if (is_array($rtpsessions))
					foreach ($rtpsessions as $rtpsession)
					{

						$disconnect = "";

						if (!$options['webrtc']) $disconnect = 'WebRTC is disabled.';

						$stream = $rtpsession['streamName'];
						$streamQuery = array();

						if ($rtpsession['streamQuery'])
						{

							parse_str($rtpsession['streamQuery'], $streamQuery);

							if ($userID = (int) $streamQuery['userID'] )
							{
								$user = get_userdata($userID);

								$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
								if ($user->$userName) $username = urlencode($user->$userName);
							}


						}

						if (!$username) $username = $stream; //fallback (external stream?)

						$postID = 0;
						if ($channel_id = intval($streamQuery['channel_id']))
						{
							$postID = $channel_id;
							$post = get_post($channel_id);

						} else $disconnect = 'No channel ID.';

						$transcoding = intval($streamQuery['transcoding']); // just a transcoding

						//WebRTC session vars

						$r = $streamQuery['room'];
						if (!$r) $r = $stream;
						$u = $username;

						if ($rtpsession['streamPublish'] == 'true' && $userID && !$disconnect && !$transcoding) //WebRTC broadcaster session
							{
							$s = $username;
							$m = 'WebRTC Broadcaster';


							//webrtc broadcast test
							if (!$webrtc_test)
							{
								self::requirementMet('webrtc_test');
								$webrtc_test = 1;
							}

							$keyBroadcast = md5('vw' . $options['webKey'] . $userID  . $channel_id);
							if ($streamQuery['key'] != $keyBroadcast) $disconnect = 'WebRTC broadcast key mismatch.';

							if (!$post) $disconnect = 'Channel post not found.';
							// elseif (!VWliveWebcams::isPerformer($userID,$postID)) $disconnect = 'Only channel performers can broadcast.';

							if ($options['bannedNames']) if ($ban =  VWliveWebcams::containsAny($r, $options['bannedNames'])) $disconnect = "Room banned ($ban)!";

								if (!$disconnect)
								{
									//clients can also broadcast in 2way (app)
									$isPerformer = self::isPerformer($userID, $postID);

									//update public session
									$session = self::sessionUpdate($s, $r, $isPerformer, 7, 0, 0, 0, $options, $userID);

									//update private session if in private mode
									$privateUID = intval($streamQuery['privateUID']);
									if ($privateUID) $disconnect = self::privateSessionUpdate($session, $post, $isPerformer, $privateUID, $options);

									//generate external snapshot for external broadcaster
									if ($isPerformer)
									{
										VWliveWebcams::streamSnapshot($session->session, false, $postID);

										if ($postID)
										{
											update_post_meta($postID, 'edate', $ztime);
											update_post_meta($postID, 'btime', $btime);

											update_post_meta($postID, 'stream-protocol', 'rtsp');
											update_post_meta($postID, 'stream-type', 'webrtc');

											VWliveWebcams::updateViewers($postID, $r, $options);
										}

										$streamMode = get_post_meta($postID, 'stream-mode', true); //safari on pc encoding profile issues

										//transcode stream (from RTSP) if safari_pc (incorrect profile for h264)
										/*
										if (!$disconnect) if ($options['transcodingAuto']>=2)
												if ($streamMode == 'safari_pc')
													VWliveWebcams::transcodeStreamWebRTC($stream, $postID, $options);
													*/
									}

								}

							///end WebRTC broadcaster session
						}

						if ($rtpsession['streamPlay'] == 'true' && !$disconnect)  //webRTC playback session (public) from rtmp_status
							{

							//$s = $username .'_'. $stream;

							// sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1, $clean=1, $options=null, $userID = 0, $postID=0, $ip = '')
							$session = VWliveWebcams::sessionUpdate($username, $r, 0, 8, 0, 0, 0, $options, $userID, $postID);


							///end WebRTC playback
						}


						$controlSession['disconnect'] = $disconnect;

						$controlSession['session'] = $s;
						$controlSession['dS'] = $dS;
						$controlSession['type'] = $session->type;
						$controlSession['room'] = $r;
						$controlSession['username'] = $u;

						//$controlSession['query'] = $rtpsession['streamQuery'];

						$controlSessions[$rtpsession['sessionId']] = $controlSession;

						//end  foreach ($rtpsessions as $rtpsession)
					}

				$controlSessionsS = serialize($controlSessions);

				//debug update
				fputs($dfile,"\r\nControl RTP Sessions: " . "\r\n" . $controlSessionsS);

				//users - RTMP clients
				$userdata = stripslashes($_POST['users']);

				if (version_compare(phpversion(), '7.0', '<'))
					$users = unserialize($userdata);  //request is from trusted server
				else $users = unserialize($userdata, array());


				$rtmp_test = 0;

				if (is_array($users))
					foreach ($users as $user)
					{

						//$rooms = explode(',',$user['rooms']); $r = $rooms[0];
						$r = $user['rooms'];
						$s = $user['session'];
						$u = $user['username'];

						$ztime=time();
						$disconnect = "";

						if ($options['bannedNames']) if ($ban =  VWliveWebcams::containsAny($s, $options['bannedNames'])) $disconnect = "Name banned ($s,$ban)!";

							//kill snap/info sessions
							if ($options['ffmpegTimeout'])
								if ($user['runSeconds']) if ($user['runSeconds']>$options['ffmpegTimeout'])
										if ( in_array(substr($user['session'],0,11), array('ffmpegSnap_', 'ffmpegInfo_')) )
										{
											$disconnect = 'FFMPEG timeout.';
										}

									if ($user['role'] == '1') //channel broadcaster
										{

										//an user is connected on rtmp: works
										if (!$rtmp_test)
										{
											self::requirementMet('rtmp_test');
											$rtmp_test = 1;
										}

										if (!$r) $r = $s; //use session as room if missing in older rtmp side

										//sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1);
										$session = VWliveWebcams::sessionUpdate($s, $r, 1, 9, 0, 0, 0, $options); //not strict in case this is existing flash user

										if ($session->type >= 2) //external broadcaster: update here, otherwise updates on calls from flash app
											{

											//update post
											$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $session->room . "' and post_type='" . $options['custom_post']. "' LIMIT 0,1" );

											//generate external snapshot for external broadcaster
											VWliveWebcams::streamSnapshot($session->session, true, $postID);


											if ($options['bannedNames'])
												if ($ban =  VWliveWebcams::containsAny($r,$options['bannedNames'])) $disconnect = "Room banned ($ban)!";

												//detect transcoding to avoid altering source info
												$transcoding = 0;
											$stream_webrtc = $session->room . '_webrtc';
											$stream_hls = 'i_' . $session->room;
											if ($s == $stream_hls || $s == $stream_webrtc) $transcoding = 1;

											if ($postID && !$transcoding)
											{
												update_post_meta($postID, 'edate', $ztime);
												update_post_meta($postID, 'btime', $channel->btime);

												update_post_meta($postID, 'stream-protocol', 'rtmp');
												update_post_meta($postID, 'stream-type', 'external');
												update_post_meta($postID, 'stream-updated', $ztime);

												VWliveWebcams::updateViewers($postID, $r, $options);
											}

											//transcode stream (from RTMP)
											if (!$disconnect) if ($options['transcodingAuto']>=2) VWliveWebcams::transcodeStream($session->room);
										}

									}
								else //subscriber viewer
									{


									$session = VWliveWebcams::sessionUpdate($s, $r, 0, 10, 0, 0, 0, $options); //not strict in case this is existing flash user

									if ($session->type >= '2') //external viewer session: update here
										{

										//update post
										$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $r . "' and post_type='channel' LIMIT 0,1" );
										if ($postID)
										{
											update_post_meta($postID, 'wtime', $channel->wtime);
										}

									}

								}

							$controlUser['disconnect'] = $disconnect;

						$controlUser['session'] = $s;
						$controlUser['dS'] = $dS;
						$controlUser['type'] = $session->type;
						$controlUser['room'] = $session->room;
						$controlUser['username'] = $session->username;

						$controlUsers[$user['session']] = $controlUser;

					}

				$controlUsersS = serialize($controlUsers);

				fputs($dfile,"\r\nControl RTMP Users: ". "\r\n" . $controlUsersS);

				fputs($dfile,"\r\n" . $debugInfo);
				fclose($dfile);

				echo 'VideoWhisper=1&usersCount='.count($users)."&controlUsers=$controlUsersS&controlSessions=$controlSessionsS";

				//clean sessions in db
				VWliveWebcams::billSessions();
				// rtmp_status end
				break;



				//! Flash app ajax calls
				//! login
			case 'm_login':

				$room_name = sanitize_file_name($_GET['room_name']);

				//does room exist?
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
				if (!$postID)  VWliveWebcams::rexit('loggedin=0&msg=' . urlencode('Room does not exist: ' . $room_name)) ;

				$post = get_post( $postID );

				$rtmp_server = VWliveWebcams::rtmpServer($postID, $options);

				$rtmp_amf = $options['rtmp_amf'];

				$tokenKey = $options['tokenKey'];
				$webKey = $options['webKey'];

				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];

				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaysRTMP = $options['alwaysRTMP'];
				$alwaysP2P = $options['alwaysP2P'];

				$disableBandwidthDetection = $options['disableBandwidthDetection'];

				$camRes = explode('x',$options['camResolution']);

				$canWatch = $options['canWatch'];
				$watchList = $options['watchList'];

				$current_user = wp_get_current_user();

				$loggedin=0;
				$msg="";
				$performer=0;
				$balance=0;
				$uid = 0;

				$multicam = 0;

				//user info
				if ($current_user) if ($current_user->ID >0)
					{

						//identify if user can be room performer
						if (VWliveWebcams::isRoomPerformer($post, $current_user)) $performer =1;
						else $performer = 0;

						$performerName = VWliveWebcams::performerName($current_user, $options);
						$stream = get_post_meta( $postID, 'performer', true);

						//$debug .= "p_a:".$post->post_author .".uID:".$current_user->ID.".pName:$performerName.s:$stream";

						//username
						if ($performer) $username =  $performerName;
						else $userName =  $options['userName'];

						if (!$userName) $userName='user_nicename';

						if (!$username) //non performer
							if ($current_user->$userName) $username = urlencode(sanitize_file_name($current_user->$userName));

							$uid = $current_user->ID;

						if ($uid)
						{
							VWliveWebcams::billSessions($uid);
							$balance = VWliveWebcams::balance($uid);
							$balancePending = $balance;
						}

					}

				//common room info
				$groupCPM = get_post_meta($postID, 'groupCPM', true);

				$presentationMode = get_post_meta($postID, 'vw_presentationMode', true);
				if ($presentationMode == '' || !$presentationMode) $presentation = $options['presentation'];
				if ($presentationMode == '0')  $presentation = 0;
				if ($presentationMode == '1')  $presentation = 1;



				if (!$performer)
				{
					//client
					$parameters = html_entity_decode(stripslashes($options['parametersClient']));
					$layoutCode = html_entity_decode(stripslashes($options['layoutCodeClient']));
					$layoutCodePrivate = html_entity_decode(stripslashes($options['layoutCodePrivateClient']));

					$welcome = html_entity_decode(stripslashes($options['welcomeClient']));

					if ($presentation)
					{
						$parameters = html_entity_decode($options['parametersClientPresentation']);
						$layoutCode = html_entity_decode(stripslashes($options['layoutCodeClientPresentation']));
					}

					$requestShow = 1;
					$sendTip = $options['tips'];

					//access keys
					if ($current_user)
					{
						$userkeys = $current_user->roles;
						$userkeys[] = $current_user->user_login;
						$userkeys[] = $current_user->ID;
						$userkeys[] = $current_user->user_email;
						$userkeys[] = $current_user->display_name;
					}

					//check if banned
					$bans = get_post_meta($postID, 'bans',true);
					if ($bans)
					{

						//clean expired bans
						foreach ($bans as $key=>$ban) if ($ban['expires']<time())
							{
								unset($bans[$key]);
								$bansUpdate =1;
							}
						if ($bansUpdate) update_post_meta($postID, 'bans', $bans);

						$clientIP = VWliveWebcams::get_ip_address();

						foreach ($bans as $ban)
							if ($clientIP == $ban['ip'] || ($uid && $uid == $ban['uid']))
							{
								$msg = urlencode( __('You are banned from accessing this room! By', 'ppv-live-webcams') . ' ' . $ban['by'] . '. ');
								VWliveWebcams::rexit('loggedin=0&msg=' . $msg);
							}
					}

					switch ($canWatch)
					{
					case "all":
						$loggedin=1;
						if (!$username)
						{
							$username="G_".base_convert((time()-1224350000).rand(0,10),10,36);
							$visitor=1; //ask for username

							$requestShow = 0;
							$sendTip = 0;

							$welcome .= '<BR>' . __('You are NOT logged in. Only registered users can request private show or send tips!', 'ppv-live-webcams');

						}
						break;

					case "members":
						if ($username) $loggedin=1;
						else $msg=urlencode('<a href="\">' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>') . $msgp;
						break;

					case "list";
						if ($username)
							if (VWliveWebcams::inList($userkeys, $watchList)) $loggedin=1;
							else $msg = urlencode('<a href="/">' . $username .', ' . __('you are not in the allowed watchers list.', 'ppv-live-webcams') . '</a>') . $msgp;
							else $msg = urlencode('<a href="/">' . __('Please login first or register an account if you do not have one!', 'ppv-live-webcams') . '</a>') . $msgp;
							break;

					}

					//estimated balance including incomplete paid sessions
					$balancePending = VWliveWebcams::balance($uid, true);

					if ($groupCPM) //check if client can access
						{

						$ppvMinimum =  VWliveWebcams::balanceLimit($groupCPM, 5,  $options['ppvMinimum'], $options);

						if (!$uid)
						{
							$loggedin=0;
							$msg .= urlencode(__("Paid group session: login to access!", 'ppv-live-webcams'));
						}
						elseif ( $balancePending < $ppvMinimum)
						{
							$loggedin=0;
							$msg .= urlencode(__('Minimum balance required for paid session:', 'ppv-live-webcams') .' '. $ppvMinimum );
						}
					}
					else //free mode limits
						{

						if ($uid) $cnd = "uid='$uid'";
						else
						{
							if (!$clientIP) $clientIP = VWliveWebcams::get_ip_address();
							$cnd = "ip='$clientIP'";
						}

						$table_sessions = $wpdb->prefix . "vw_vmls_sessions";


						$h24 = time() - 86400;
						$sqlC = "SELECT SUM(edate-sdate) FROM `$table_sessions` WHERE $cnd AND sdate > $h24";
						$freeTime = $wpdb->get_var($sqlC);

						if ($freeTime)
						{
							$welcome .= '<BR>Free time last 24h: ' . $freeTime .'s';

							if ($uid) if ($freeTime > $options['freeTimeLimit']) $disconnect = __("Free chat daily time limit reached: You can only access paid group rooms today!", 'ppv-live-webcams');
								if (!$uid) if ($freeTime > $options['freeTimeLimitVisitor']) $disconnect = __("Free chat daily visitor time limit reached: Register and login for more chat time today!", 'ppv-live-webcams');

						}

						if ($disconnect)
						{
							$loggedin=0;
							$msg .= urlencode($disconnect);
						}


					}


					$accessList = get_post_meta($postID, 'vw_accessList', true);
					if ($accessList) if (!VWliveWebcams::inList($userkeys, $accessList))
						{
							$loggedin=0;
							$msg .= urlencode(__("Your are not in room access list.", 'ppv-live-webcams'));
						}


					//client side only
					if (post_password_required($postID))
					{
						$loggedin=0;
						$msg .= urlencode(__("Password Protected: Room can be accessed after filling password from room page.", 'ppv-live-webcams'));
					}

					if (!$loggedin) VWliveWebcams::rexit('loggedin=0&msg=' . $msg) ;

					$videoDefault = get_post_meta($postID, 'performer', true);

					//cost per minute in private shows
					$clientCPM = VWliveWebcams::clientCPM($room_name, $options, $postID);

					if ($uid)
					{
						$welcome .= '<BR>' . __('Your current balance:', 'ppv-live-webcams') . ' ' . $balance . ' ' . htmlspecialchars($options['currency']);

						if ($balance != $balancePending) $welcome .= '<BR>' . __('Pending balance', 'ppv-live-webcams') . ': ' . $balancePending . ' ' . htmlspecialchars($options['currency']);

						if ($clientCPM) $welcome .= '<BR>' . __('Private show cost per minute:', 'ppv-live-webcams') . ' ' . $clientCPM . ' ' . htmlspecialchars($options['currencypm']);
					}

					$groupParameters =  get_post_meta($postID, 'groupParameters', true);

					$in2way = false;
					if (is_array($groupParameters))
						if ($groupParameters['2way']>0)
						{
							$c2way = 0;
							$mode2way = get_post_meta( $postID, 'mode2way', true );
							if (is_array($mode2way)) $c2way = count($mode2way);

							$welcome .= '<BR>' . __('2 way slots:', 'ppv-live-webcams') . ' (' . $c2way .'/'.$groupParameters['2way'] . ') ' . $groupParameters['cpm2']. ' ' . htmlspecialchars($options['currencypm']);

							if (is_array($mode2way))
								if (array_key_exists($uid,  $mode2way)) //current user is in 2way mode
									{

									$mode2way[$uid] = $ztime;
									update_post_meta( $postID, 'mode2way', $mode2way);
									$in2way = true;
								}
						}

					if ($clientCPM) $ppvMinimum =  VWliveWebcams::balanceLimit($clientCPM, 5,  $options['ppvMinimum'], $options);

					if ($uid)
						if ($ppvMinimum && $clientCPM)
							if ( $balancePending < $ppvMinimum)
							{
								$requestShow = 0;
								$welcome .= '<BR>' . __('You do not have enough credits to request a private show.', 'ppv-live-webcams') . " ($ppvMinimum)";
							}
						else
						{
							if ($options['ppvGraceTime']) $welcome .= '<BR>' . __('Charging starts after a grace time:', 'ppv-live-webcams') . ' ' . $options['ppvGraceTime'] . 's';
						}

					//disable shows if cpm=0
					if (!$clientCPM) $requestShow = 0;

					$snap='client.png';

					//private2way
					$private2way = get_post_meta( $postID, 'vw_private2way', true );

					$update = array();

					if ($private2way == '2way') $update['webcamOnPrivate']='1';
					if ($private2way == '1way') $update['webcamOnPrivate']='0';

					if ($in2way)
					{
						$update['webcamSupported']='1';
						$update['webcamEnabled']='1';
						$update['webcamOnPrivate']='1';

						$layoutCode = html_entity_decode(stripslashes($options['layoutCodeClient2way']));
						$layoutCodePrivate = html_entity_decode(stripslashes($options['layoutCodePrivateClient2way']));

					}

					if (count($update)) $parameters = VWliveWebcams::editParameters($parameters, $update);

				}
				else
				{
					//performer
					$parameters = html_entity_decode($options['parametersPerformer']);
					$layoutCode = html_entity_decode(stripslashes($options['layoutCodePerformer']));
					$layoutCodePrivate = html_entity_decode(stripslashes($options['layoutCodePrivatePerformer']));
					$welcome = html_entity_decode(stripslashes($options['welcomePerformer']));

					if ($presentation)
					{
						$parameters = html_entity_decode($options['parametersPerformerPresentation']);
						$layoutCode = html_entity_decode(stripslashes($options['layoutCodePerformerPresentation']));

					}

					$loggedin=1;

					//performer is current user: update room stream
					$videoDefault = $performerName;
					update_post_meta($postID, 'performer', $performerName);
					update_post_meta($postID, 'performerUserID', $current_user->ID );

					$requestShow = 0;
					$sendTip = 0;


					$welcome .= '<BR>' . __('Your current balance:', 'ppv-live-webcams') . ' ' . $balance . ' ' . htmlspecialchars($options['currency']);


					if ($options['ppvPerformerPPM']>0) $welcome .= '<BR>' . __('Private show cost per minute for performer is:', 'ppv-live-webcams') . ' ' . $options['ppvPerformerPPM'];


					$clientCPM = VWliveWebcams::clientCPM($room_name, $options, $postID);
					$performerRatio = VWliveWebcams::performerRatio($room_name, $options, $postID);

					if ($clientCPM) $welcome .= '<BR>' . __('Private show cost per minute for client:', 'ppv-live-webcams') . ' ' . $clientCPM . ' ' . htmlspecialchars($options['currencypm']);

					if ($options['ppvGraceTime']) $welcome .= '<BR>' . __('Charging starts after a grace time:', 'ppv-live-webcams') . ' ' . $options['ppvGraceTime'] . 's';

					if ($clientCPM && $performerRatio) $welcome .= '<BR>' . __('Private show earning per minute for performer:', 'ppv-live-webcams') . ' ' . number_format($clientCPM*$performerRatio, 2, '.','') . ' ' . htmlspecialchars($options['currencypm']);


					$snap='performer.png';

					$multicam = get_post_meta( $postID, 'vw_multicam', true );


				}

				//common for performer/client

				$welcome .= '<BR>' .  __('You are in room', 'ppv-live-webcams') . ': ' . ' ' . $room_name;

				if ($videoDefault) $welcome .= '<BR>' . __('Active performer (stream):', 'ppv-live-webcams') . ' ' . $videoDefault;

				if ($groupCPM) //show group mode details
					{
					$groupMode = get_post_meta($postID, 'groupMode', true);

					$welcome .= '<BR>' .  __('Group Mode', 'ppv-live-webcams') . ': ' . '<B>'.$groupMode.'</B>';
					$welcome .= '<BR>' .  __('Group session cost', 'ppv-live-webcams') . ': ' . ' ' . $groupCPM . ' ' . htmlspecialchars($options['currencypm']);
				}

				//vw logo
				$vw_logo = get_post_meta( $postID, 'vw_logo', true );
				if (!$vw_logo) $vw_logo = 'global';

				switch ($vw_logo)
				{
				case 'global':
				case '':
					$overLogo = $options['overLogo'];
					$overLink = $options['overLink'];
					break;

				case 'hide':
					$overLogo = '';
					$overLink = '';
					break;

				case 'custom':
					$overLogo = get_post_meta( $postID, 'vw_logoImage', true );
					$overLink = get_post_meta( $postID, 'vw_logoLink', true );
					break;
				}

				$videoOffline = urlencode(plugins_url('videowhisper/offline.jpg', __FILE__ )); //when goes offline
				$videoBusy = urlencode(plugins_url('videowhisper/busy.jpeg', __FILE__ )); //when hides or until access

				//common parameters
				$update = array();

				$update['webfilter'] = $options['webfilter'];
				$parameters = VWliveWebcams::editParameters($parameters, $update);

				//reload playlist if updated
				$reloadPlaylist = 0;
				$forceOnline = 0;

				if ($loggedin)
				{
					$playlistActive = get_post_meta( $postID, 'vw_playlistActive', true );
					$playlistLoaded = get_post_meta( $postID, 'vw_playlistLoaded', true );

					if ($playlistActive) $forceOnline = 1; //show stream

					//activated or loaded and inactive
					if ($playlistActive || $playlistLoaded)
					{


						$streamsPath = VWliveWebcams::fixPath($options['streamsPath']);
						$smilPath = $streamsPath . 'playlist.smil';

						if (filemtime($smilPath) > $playlistLoaded)
							if (VWliveWebcams::timeTo($roomName . '-playlistReload', 5, $options))
							{
								$reloadPlaylist = 1;
								update_post_meta( $postID, 'vw_playlistLoaded', time() );

							}
					}
				}
				//$debug .= ".performer:$performer";


				if ($loggedin)
				{
					//static function sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1, $clean=1, $options=null, $userID = 0)
					VWliveWebcams::sessionUpdate($username, $room_name, $performer, 1, 1, 1, 0, $options, $uid);

					//web Session Save
					$clientIP = VWliveWebcams::get_ip_address();
					VWliveWebcams::webSessionSave($username, $performer, 'm_login', $clientIP); //approve session for rtmp check

					if ($performer) //update room type
						{
						update_post_meta($postID, 'stream-protocol', 'rtmp');
						update_post_meta($postID, 'stream-type', 'flash');
						update_post_meta($postID, 'roomInterface', 'flash');
					}

				}

				//warn if HTTPS missing
				if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
					$welcome.= '<br><B>' . __('Warning: HTTPS not detected. Some browsers like Chrome will not permit webcam access when accessing without SSL!', 'ppv-live-webcams') . '</B>';


				?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
				$p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
				$disableBandwidthDetection?>&room=<?php echo $room_name?>&username=<?php echo $username?>&administrator=<?php echo $performer?>&psnap=<?php echo urlencode($snap)?>&loggedin=<?php echo $loggedin?>&welcome=<?php echo urlencode($welcome)?>&filterRegex=<?php echo urlencode($options['filterRegex'])?>&filterReplace=<?php echo urlencode($options['filterReplace'])?>&friendsList=<?php echo $friendsList?>&requestShow=<?php echo $requestShow?>&sendTip=<?php echo $sendTip?>&videoDefault=<?php echo $videoDefault?>&reloadPlaylist=<?php echo $reloadPlaylist; ?>&forceOnline=<?php echo $forceOnline; ?>&videoOffline=<?php echo $videoOffline ?>&videoBusy=<?php echo $videoBusy ?>&layoutCode=<?php echo urlencode($layoutCode)?>&layoutCodePrivate=<?php echo urlencode($layoutCodePrivate)?>&loadstatus=1&multicam=<?php echo $multicam?>&loaderGIF=<?php echo urlencode($options['loaderGIF'])?>&uploadsURL=<?php echo urlencode(VWliveWebcams::path2url($options['uploadsPath'].'/'))?>&serverRecord=<?php echo urlencode($options['rtmp_server_record'])?>&overLogo=<?php echo urlencode($overLogo)?>&overLink=<?php echo urlencode($overLink); echo $parameters; echo '&debug='.$debug;

				//end m_login
				break;

			case 'ban':

				$room = sanitize_file_name( $_POST['room']);

				$username = sanitize_file_name( $_POST['username']);
				$mode = $_POST['mode'];


				$current_user = wp_get_current_user();


				if (!$current_user) VWliveWebcams::rexit('error=login');
				if (!$current_user->ID) VWliveWebcams::rexit('error=login');


				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
				if (!$postID)  VWliveWebcams::rexit('error=1&msg=roomNotFound-' . $room) ;

				//identify if admin
				$performerName = VWliveWebcams::performerName($current_user, $options);
				$stream = get_post_meta( $postID, 'performer', true);
				if ($stream == $performerName) $administrator = 1;
				if (in_array('administrator', $current_user->roles)) $administrator = 1;

				if (!$administrator) VWliveWebcams::rexit('error=notAdmin');

				$duration = 900; //15 min
				if ($mode =='ban') $duration = 604800; //1 week


				global $wpdb ;
				$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

				$sqlS = "SELECT * FROM `$table_sessions` WHERE session='$username' AND status='0' AND room='$room' LIMIT 1";
				$session = $wpdb->get_row($sqlS);

				$bans = get_post_meta($postID, 'bans',true);
				if (!is_array($bans)) $bans = array();

				$ban = array('user'=> $username, 'uid' => $session->uid, 'ip'=> $session->ip, 'expires' => time() + $duration, 'by' => $current_user->user_login);
				$bans[] = $ban;

				update_post_meta($postID, 'bans', $bans);

				?>ban=success&uid=<?php echo $session->uid; ?>&ip=<?php echo $session->ip; ?>&loadstatus=1<?php
				break;

			case 'a_login':
				//! a_login videowhisperAdmin.swf



				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

				$current_user = wp_get_current_user();

				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

				$username = '_A_' . $username;

				//web Session Save
				$clientIP = VWliveWebcams::get_ip_address();
				if ($loggedin) VWliveWebcams::webSessionSave($username, 1, 'a_login', $clientIP); //approve session for rtmp check


				//get first room to use for session control
				$args = array(
					'post_type' => $options['custom_post'],
					'post_status' => 'publish',
					'posts_per_page' => 1,
					'offset'           => 0,
					'orderby' => 'post_date',
					'order'            => 'ASC',
				);

				$postslist = get_posts($args);
				if (count($postslist)>0)
					foreach ( $postslist as $item )
					{
						$keyView = md5('vw' . $options['webKey']. $item->ID);
						$rtmp_server = $options['rtmp_server_admin'] . '?'. urlencode($username) .'&'. urlencode($item->post_title) .'&'. $keyView . '&0&videowhisper';
					}


				?>server=<?php echo urlencode($rtmp_server)?>&serverAMF=<?php echo $rtmp_amf?>&room=<?php echo $username?>&demo_mode=0&username=<?php echo $username?>&webserver=&msg=&loggedin=1&loadstatus=1<?php
				break;


			case 'vv_login':
				//! vv_login - live_video.swf
				//live_video.swf - plain video interface login

				$room_name = sanitize_file_name($_GET['room_name']);
				if (!$room_name)  VWliveWebcams::rexit('loggedin=0&msg=' . urlencode('Room name not provided.' . $room_name));

				/*
				$stream_name = sanitize_file_name($_GET['stream_name']);
				if (!$stream_name)  VWliveWebcams::rexit('loggedin=0&msg=' . urlencode('Stream name not provided.' . $stream_name));
*/

				//does room exist?
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
				if (!$postID)  VWliveWebcams::rexit('loggedin=0&msg=' . urlencode('Room does not exist: ' . $room_name)) ;

				//$post = get_post( $postID );

				$rtmp_server = VWliveWebcams::rtmpServer($postID, $options);
				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';


				$tokenKey = $options['tokenKey'];
				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];
				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaysRTMP = $options['alwaysRTMP'];
				$alwaysP2P = $options['alwaysP2P'];

				$disableBandwidthDetection = $options['disableBandwidthDetection'];

				//identify user
				$current_user = wp_get_current_user();
				$uid = $current_user->ID;

				$loggedin=0;
				$msg="";
				$visitor=0;

				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

				//access keys
				/*
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
					$userkeys[] = $current_user->display_name;
				}
				*/

				$loggedin=1;
				if (!$username)
				{
					$username=base_convert((time()-1224350000).rand(0,10),10,36);

					$visitor=1; //video only interface
				}

				//if ($username==$room_name) $username.="_".rand(10,99);//allow viewing own room - session names must be different

				$username =  $username. '-' . base_convert(rand(0,36*36),10,36) . '-'.$room_name;



				//voyeur mode?
				$isVoyeur = 0;
				$groupParameters =  get_post_meta($postID, 'groupParameters', true);

				if ($groupParameters['voyeur'])
				{
					$modeVoyeur = get_post_meta( $postID, 'modeVoyeur', true );
					if (is_array($modeVoyeur))
						if (array_key_exists($uid, $modeVoyeur))
						{
							$isVoyeur = 1;
							$username = 'Voy' . base_convert(rand(0,36*36*36),10,36) . '-'.$room_name;
						}

					//$debug .= var_export($groupParameters);
				}


				//web Session Save
				$clientIP = VWliveWebcams::get_ip_address();
				if ($loggedin) VWliveWebcams::webSessionSave($username, 0, 'vv_login', $clientIP); //approve session for rtmp check


				$overLogo = $options['overLogo'];
				$overLink = $options['overLink'];

				$s = $username;
				$u = $username;
				$r = $room_name;
				$m = '';

				$parameters = html_entity_decode($options['parametersVideo']);

				?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
				$p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
				$disableBandwidthDetection?>&username=<?php echo $username?>&userType=<?php echo $userType?>&msg=<?php echo $msg?>&loggedin=<?php echo
				$loggedin?>&visitor=<?php echo $visitor?>&overLogo=<?php echo urlencode($overLogo)?>&overLink=<?php echo
				urlencode($overLink); echo $parameters; ?>&voyeur=<?php echo $isVoyeur;?>&room=<?php echo $room_name;?>&loadstatus=1&debug=<?php echo $debug;  ?><?php
				break;

			case 'v_status':
				//! v_status - live_video.swf
				// live_video.swf - plain video interface status

				/*
POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
*/

				$cam = (int) $_POST['cam'];
				$mic = (int) $_POST['mic'];

				$timeUsed = $currentTime = (int) $_POST['ct'];
				$lastTime = (int) $_POST['lt'];

				$s = sanitize_file_name($_POST['s']);
				$u = sanitize_file_name($_POST['u']);
				$room_name = $r = sanitize_file_name($_POST['r']);

				//exit if no valid session name or room name
				if (!$s) exit;
				if (!$r) exit;

				//does room exist?
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''. $options['custom_post'] . '\' LIMIT 0,1' );
				if (!$postID)  $disconnect = 'Room does not exist: ' . $room_name;

				$isVoyeur = 0;
				$type = 3; //flash video

				if (is_user_logged_in() && !$disconnect)
				{
					$current_user = wp_get_current_user();
					$uid = $current_user->ID;

					//voyeur mode?
					$groupParameters =  get_post_meta($postID, 'groupParameters', true);

					if ($groupParameters['voyeur'])
					{

						$modeVoyeur = get_post_meta( $postID, 'modeVoyeur', true );
						if (is_array($modeVoyeur))
							if (array_key_exists($uid, $modeVoyeur)) $isVoyeur = 1;

					}

					if ($isVoyeur) $type = 5; //flash video - voyeur

					$disconnect = VWliveWebcams::updateOnlineViewer($s, $r, $postID, $type, $current_user, $options);
				}

				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo urlencode($disconnect)?>&voyeur=<?php echo $isVoyeur?>&postID=<?php echo $postID?>&loadstatus=1<?php
				break;

				//! m_status
			case 'm_status':
				$cam = (int) $_POST['cam'];
				$mic = (int) $_POST['mic'];

				$timeUsed = $currentTime = (int) $_POST['ct'];
				$lastTime = (int) $_POST['lt'];

				$s = sanitize_file_name($_POST['s']);
				$u = sanitize_file_name($_POST['u']);
				$room_name = $r = sanitize_file_name($_POST['r']);
				//$m=$_POST['m'];

				$ztime=time();


				//exit if no valid session name or room name
				if (!$s) VWliveWebcams::rexit('noSession=1');
				if (!$r) VWliveWebcams::rexit('noRoom=1');

				//does room exist?
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
				if (!$postID)  VWliveWebcams::rexit('disconnect=' . urlencode('Room does not exist: ' . $room_name)) ;

				$post = get_post( $postID );


				$table_sessions = $wpdb->prefix . "vw_vmls_sessions";
				$table_private = $wpdb->prefix . "vw_vmls_private";


				//flash calls web status after rtmp connection
				self::requirementMet('rtmp_test');

				//user info
				$current_user = wp_get_current_user();

				$performer=0;

				if ($current_user) if ($current_user->ID>0)
					{
						$performerName = VWliveWebcams::performerName($current_user, $options);
						$stream = get_post_meta( $postID, 'performer', true);
						//performer ?

						if ($stream == $performerName) $performer = 1;
						if (!$stream) if (VWliveWebcams::isAuthor($postID)) $performer = 1;


							$balance= VWliveWebcams::balance($current_user->ID, !$performer);
						$statusInfo .=  $balance ;
						$statusInfo .=  htmlspecialchars($options['currency']);

					}

				//flash calls web status after rtmp connection
				if ($performer)
				{
					self::requirementMet('rtmp_test');
				}

				$debug = '-perf:' . $performer . '-stream:'. $stream . '-pN:'. $performerName;

				if ($performer)
				{
					//update cam stats
					update_post_meta($postID, 'edate', $ztime);

					$onlineTime = get_post_meta($postID, 'onlineTime', true);
					$dS = floor(($currentTime-$lastTime)/1000);
					update_post_meta($postID, 'onlineTime', $dS + $onlineTime);

					//update perfomer private show status (present in private chats)
					$shows =  $wpdb->get_var("SELECT count(id) as no FROM `$table_private` where status='0' and room='" . $r . "' and pid='" . $current_user->ID . "'");
					if ($shows) $shows =1; else $shows = 0;
					update_post_meta($postID, 'privateShow', $shows);

					//transcoding per settings
					if ($cam) if ($options['transcodingAuto']>=2) $tr = VWliveWebcams::transcodeStream($stream, 0, $room_name);

						//var_dump($shows);
						$debug .= '-edate' . $ztime . '-transcode_'.$options['transcodingAuto'].'_'.$stream .'_' .$room_name .'_C'. $cam .  $tr;

				}
				else
					{ //client

					$disconnect = VWliveWebcams::updateOnlineViewer($s, $r, $postID, 1, $current_user, $options);

					//notify low balance, warn 1 amount > warn 2 amount
					if ($balance > 0 && ($balance < $options['balanceWarn1Amount'] || $balance < $options['balanceWarn2Amount']) )
					{

						//low balance
						if ($balance < $options['balanceWarn1Amount'] )
						{
							$notify = 'BalanceLow';
							$notifyMessage = $options['balanceWarn1Message'];
							$notifySound = $options['balanceWarn1Sound'];
						}

						//critical balance
						if ($balance < $options['balanceWarn2Amount'] )
						{
							$notify = 'BalanceCritical';
							$notifyMessage = $options['balanceWarn2Message'];
							$notifySound = $options['balanceWarn2Sound'];
						}

						$notifyCode = '&notify=' . $notify . '&notifyMessage=' . urlencode($notifyMessage) . '&notifySound=' . urlencode($notifySound);
					}

					//check if client banned
					$bans = get_post_meta($postID, 'bans',true);
					if ($bans) if (is_array($bans))
						{

							//clean expired bans
							foreach ($bans as $key=>$ban) if ($ban['expires']<time())
								{
									unset($bans[$key]);
									$bansUpdate =1;
								}
							if ($bansUpdate) update_post_meta($postID, 'bans', $bans);

							$clientIP = VWliveWebcams::get_ip_address();

							foreach ($bans as $ban)
								if ($clientIP == $ban['ip'] || ($uid && $uid == $ban['uid']))
								{
									$disconnect = urlencode( __('You are banned from accessing this room! By', 'ppv-live-webcams') . ' ' . $ban['by'] . '. ');
								}
						}


				}

				VWliveWebcams::updateViewers($postID, $r, $options);




				$maximumSessionTime = 0;


				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $currentTime?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&statusInfo=<?php echo urlencode($statusInfo)?>&d=<?php echo $debug . $notifyCode?>&loadstatus=1<?php

				break;

			case 'tips':
				echo html_entity_decode(stripslashes($options['tipOptions']));
				break;

			case 'tip':

				if (!is_user_logged_in()) VWliveWebcams::rexit('success=0&failed=LoginRequired');

				$room_name = sanitize_file_name($_POST['r']);
				$caller = sanitize_file_name($_POST['s']);
				$target = sanitize_file_name($_POST['t']);

				$username = sanitize_file_name($_POST['u']);
				$private = sanitize_file_name($_POST['p']);
				$amount = floatval($_POST['a']);
				$label = sanitize_text_field($_POST['l']);
				$message = sanitize_text_field($_POST['m']);
				$color = sanitize_hex_color($_POST['c']);
				if (!$color) $color = '#11EE11';

				$sound = sanitize_file_name($_POST['snd']);

				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

				if (!$postID) VWliveWebcams::rexit('success=0&failed=RoomNotFound-' . urlencode($room_name));
				$post = get_post( $postID );

				$current_user = wp_get_current_user();

				if ($options['tipCooldown'])
				{
					$lastTip = intval(get_user_meta($current_user->ID, 'vwTipLast', true));
					if ($lastTip + $options['tipCooldown'] > time()) VWliveWebcams::rexit('success=0&failed=Cooldown-' . $lastTip);
				}

				$balance = VWliveWebcams::balance($current_user->ID, true); //live balance with estimate for active sessions

				$CPM = VWliveWebcams::clientCPM($room_name, $options, $postID);
				$ppvMinInShow =  VWliveWebcams::balanceLimit($CPM, 2,  $options['ppvMinInShow'], $options);

				if ($amount + $ppvMinInShow > $balance) VWliveWebcams::rexit('success=0&failed=NotEnoughFunds-balance' . $balance . '-minInShow' . $ppvMinInShow);

				$ztime = time();

				//client cost
				$paid = number_format($amount, 2, '.', '');
				VWliveWebcams::transaction('ppv_tip', $current_user->ID, - $paid, 'Tip for <a href="' . VWliveWebcams::roomURL($room_name) . '">' . $room_name.'</a>. (' .$label.')' , $ztime);

				//performer earning (by post owner, can be studio - todo: update to split to checked in performers or assigned)
				$received = number_format($amount * $options['tipRatio'] , 2, '.', '');
				VWliveWebcams::transaction('ppv_tip_earning', $post->post_author, $received , __('Tip from ', 'ppv-live-webcams') . $caller .' ('.$label.')', $ztime);


				//save last tip time
				update_user_meta($current_user->ID, 'vwTipLast', time());

				//update balance and report
				$balance = VWliveWebcams::balance($current_user->ID);

				$ownMessage = __('After tip, your balance is', 'ppv-live-webcams') . ': ' . $balance . '. ';

				$balancePending = VWliveWebcams::balance($current_user->ID, true);
				if ($balance != $balancePending) $ownMessage .= __('Pending balance', 'ppv-live-webcams') . ': ' . $balancePending . '.';

				if ($sound) $soundCode = "sound://$sound;;";
				$publicMessage = $soundCode. '<font color="'.$color.'"><B>' . __('Tip from', 'ppv-live-webcams') . ' ' . $username . '</B>: ' . $label . " ($paid" . htmlspecialchars($options['currency']) . ")</font>";

				$privateMessage = '<B>' . $username . ' (' . __('Tip', 'ppv-live-webcams') . ' '.$paid.')</B>: ' . $message;

				echo 'success=1&amount=' . $paid . '&balance=' . $balance. '&sound=' .urlencode($sound) . '&privateMessage=' .urlencode($privateMessage). '&publicMessage=' .urlencode($publicMessage) . '&ownMessage=' .urlencode($ownMessage);

				break;

				//! private status
			case 'm_pend':
			case 'm_pstatus':

				$room_name = sanitize_file_name($_POST['r']);
				$caller = sanitize_file_name($_POST['s']);
				$username = sanitize_file_name($_POST['u']);
				$private = sanitize_file_name($_POST['p']);

				$currentTime = (int) $_POST['ct'];
				$lastTime = (int) $_POST['lt'];

				$end = (int) $_POST['e'];

				$sqlEnd = ''; $sqlEndC = '';

				if (!$end) $end = 0;
				else
				{
					$sqlEnd = ', status=1';
					$sqlEndC = "OR status='1'";
				}

				$ztime = time();
				$maximumSessionTime = 0;
				$disconnect = "";


				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

				if (!$postID) VWliveWebcams::rexit('disconnect=RoomNotFound-' . urlencode($room_name));
				$post = get_post( $postID );

				$current_user = wp_get_current_user();
				$performer=0;


				if (VWliveWebcams::isAuthor($postID)) $performer = 1;

				$table_private = $wpdb->prefix . "vw_vmls_private";
				//status: 0 = current, 1 = ended,  2 = charged

				//discard expired sessions:
				//$closeTime = time() - $options['ppvCloseAfter'];
				//AND (pedate > $closeTime OR cedate > $closeTime)

				if ($performer)
					{ //performer

					//retrieve or create session
					$sqlS = "SELECT * FROM $table_private where room='$room_name' AND performer='$caller' AND client='$private' AND (status='0' $sqlEndC) ORDER BY status ASC, id DESC";
					$session = $wpdb->get_row($sqlS);

					if (!$session)
					{
						if ($end) VWliveWebcams::rexit('disconnect=NoSessionToEnd');

						$dt = min($currentTime/1000, intval($options['ppvCloseAfter']));

						$sql="INSERT INTO `$table_private` ( `performer`, `pid`, `rid`, `client`, `room`, `psdate`, `pedate`, `status` ) VALUES ( '$caller', '" . $current_user->ID . "', '$postID', '$private', '$room_name', " . ($ztime - $dt) . ", $ztime, 0 )";
						$wpdb->query($sql);
						$wpdb->flush();
						$session = $wpdb->get_row($sqlS);
					}

					//update id and time
					$sdate = $session->psdate;
					if (!$sdate) $sdate = $ztime; //first time = start time

					//$debug = $sql;
					//$debug = '--' . $session->psdate . '--' . $session->id . '--' . $ztime . '--' . $sdate;

					$sql="UPDATE `$table_private` SET pid = " . $current_user->ID . ", psdate=$sdate, pedate = $ztime $sqlEnd WHERE id = " . $session->id;
					$wpdb->query($sql);

					//info
					$timeUsed = $ztime - $sdate;

					$cost = VWliveWebcams::performerCost($session);
					$balance = VWliveWebcams::balance($current_user->ID);
					$ppvMinInShow =  VWliveWebcams::balanceLimit($options['ppvPerformerPPM'], 2,  $options['ppvMinInShow'], $options);

					if ($cost>0) if ( $cost + $ppvMinInShow > $balance) $disconnect = "Not enough funds left for performer to continue session. Minimum extra required: " . $ppvMinInShow . " Session Cost:$cost Balance:$balance";

				}
				else
					{ //client

					//retrieve or create session
					$sqlS = "SELECT * FROM $table_private where room='$room_name' AND client='$caller' AND performer='$private' AND (status='0' $sqlEndC) ORDER BY status ASC, id DESC";
					$session = $wpdb->get_row($sqlS);

					if (!$session)
					{
						if ($end) VWliveWebcams::rexit('disconnect=NoSessionToEnd');

						$dt = min($currentTime/1000, intval($options['ppvCloseAfter']));

						$sql="INSERT INTO `$table_private` ( `rid`, `client`, `cid`, `performer`, `room`, `csdate`, `cedate`, `status` ) VALUES ( '$postID', '$caller', $current_user->ID, $private, '$room_name', " . ($ztime - $dt) . ", $ztime, 0 )";
						$wpdb->query($sql);
						$session = $wpdb->get_row($sqlS);
					}

					//update id and time

					$sdate = $session->csdate;
					if (!$sdate) $sdate = $ztime; //first time = start time

					$sql="UPDATE `$table_private` SET cid = " . $current_user->ID . ", csdate=$sdate, cedate = $ztime $sqlEnd WHERE id = " . $session->id;
					$wpdb->query($sql);

					//info
					$timeUsed = $ztime - $sdate;

					$cost = VWliveWebcams::clientCost($session);
					$balance = VWliveWebcams::balance($current_user->ID);
					$CPM = VWliveWebcams::clientCPM($room_name, $options, $postID);
					$ppvMinInShow =  VWliveWebcams::balanceLimit($CPM, 2,  $options['ppvMinInShow'], $options);

					if ($cost>0) if ( $cost + $ppvMinInShow > $balance) $disconnect = __("Not enough funds left for client to continue session. Minimum required: ","ppv-live-webcams") . $ppvMinInShow;

						//estimate pending balance
						$balancePending = VWliveWebcams::balance($current_user->ID, true);
				}

				if ($session->status>0) $disconnect = __("Session was already ended.", 'ppv-live-webcams');

				if ($cost>0) $credits_info .=  $cost . htmlspecialchars($options['currency']) . '/';
				$credits_info .=  $balance . htmlspecialchars($options['currency']);

				if ($balancePending) $credits_info .=  ' ('. $balancePending . htmlspecialchars($options['currency']) .')';

				//server session time to app ms
				$timeUsed=$timeUsed * 1000;


				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo urlencode($disconnect)?>&statusInfo=<?php echo urlencode($credits_info)?>&loadstatus=1&debug=<?php echo $debug?><?php
				break;


				//! snapshots
			case 'vw_snapshots':


				$stream = sanitize_file_name($_GET['name']);
				$room_name = sanitize_file_name($_GET['room']);

				if (strstr($stream,'.php')) VWliveWebcams::rexit('badStreamExtension=1');
				if (!$stream) VWliveWebcams::rexit('missingStreamArgument=1');
				if (!$room_name) VWliveWebcams::rexit('missingRoomArgument=1');

				//get jpg bytearray
				$jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
				if (!$jpg) $jpg = file_get_contents("php://input");

				if (!$jpg) VWliveWebcams::rexit('missingJpgData=1');

				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room_name . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
				$post = get_post( $postID );

				$current_user = wp_get_current_user();

				$performer=0;
				if (isset($current_user) )
				{
					if (VWliveWebcams::isAuthor($postID)) $performer = 1;
				}

				if (!$performer) VWliveWebcams::rexit('noPerformer=1'); //only performer updates snapshot

				//
				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir .= "/_snapshots";
				if (!file_exists($dir)) mkdir($dir);

				//save file
				$filename = "$dir/$room_name.jpg";
				$fp=fopen($filename ,"w");
				if ($fp)
				{
					fwrite($fp,$jpg);
					fclose($fp);
				}

				//generate thumb
				$thumbWidth = $options['thumbWidth'];
				$thumbHeight = $options['thumbHeight'];

				$src = imagecreatefromjpeg($filename);
				list($width, $height) = getimagesize($filename);
				$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

				$dir = $options['uploadsPath']. "/_thumbs";
				if (!file_exists($dir)) mkdir($dir);

				$thumbFilename = "$dir/$room_name.jpg";
				imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
				imagejpeg($tmp, $thumbFilename, 95);

				//detect tiny images without info
				if (filesize($thumbFilename)>1000) $picType = 1;
				else $picType = 2;

				$debug .= 'picType' . $picType.'-Jpg'.strlen($jpg);

				//update post meta
				if ($postID) update_post_meta($postID, 'hasSnapshot', $picType);

				//import snapshot

				if (is_plugin_active('picture-gallery/picture-gallery.php'))
				{
					$groupParameters =  get_post_meta($postID, 'groupParameters', true);
					$groupMode = get_post_meta($postID, 'groupMode', true);
					$privateShow = get_post_meta($postID, 'privateShow', true);

					if (is_array($groupParameters))
					{
						if ($groupParameters['snapshots'] && !$privateShow)
						{
							$snapshotsInterval = $groupParameters['snapshotsInterval'];
							$saveSnapshot = 1;
						}

						if ($options['privateSnapshots'] && $privateShow)
						{
							$snapshotsInterval = $options['privateSnapshotsInterval'];
							$saveSnapshot = 1;
							$groupMode = 'Private';
						}
					}

					if ($saveSnapshot)
					{

						$skip = 0;
						if ($snapshotsInterval) // too soon to save?
							{
							$lastPicture =  get_post_meta($postID, '$lastPicture', true);
							if ( (time() - $lastPicture) < $snapshotsInterval) $skip = 1;
						}

						if (!$skip)
						{
							$name = $stream . ' ' . date('G:i:s j M Y') . ' ' . $groupMode;
							$galleries = array($stream, $room_name, $groupMode, $room_name .' ' . $groupMode, $room_name .' ' . $groupMode .' ' . date('j M Y'));
							$tags = array($stream, $room_name, $groupMode, date('j M Y'));
							VWpictureGallery::importFile($filename, $name, $current_user->ID, $galleries, $tags);
							update_post_meta($postID, '$lastPicture', time());

							$debug .= '-pictureImported' . $snapshotsInterval .'_' . $lastPicture;
						} else  $debug .= '-pictureImportSkipped' . $snapshotsInterval .'_' . $lastPicture;


					}

				}

				//$debug = urlencode($thumbWidth.'x'.$thumbHeight.'--'.$filename .'--'. $thumbFilename);
				echo 'loadstatus=1&debug=' . $debug;
				break;


			case 'm_logout':
				$room = sanitize_file_name( $_GET['room'] );
				$message = sanitize_textarea_field( $_GET['message'] );

				$pid = $options['p_videowhisper_webcams_logout'];
				if ($pid)
					$logoutPage = get_permalink($pid);
				else $logoutPage = home_url();

				wp_redirect(add_query_arg(array('room'=>$room, 'message'=>$message), $logoutPage));
				break;


			case 'translation':
				echo html_entity_decode(stripslashes($options['translationCode']));
				break;

			case 'vw_extchat':

				$updated = (int) $_POST['t'];
				$room = sanitize_file_name($_POST['r']);

				$session =  VWliveWebcams::currentUserSession($room);
				if ($session) $updated = max($session->sdate, $updated); //since user session started

				$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";

				//clean old chat logs
				$closeTime = time() - 900; //keep for 15min
				$sql="DELETE FROM `$table_chatlog` WHERE mdate < $closeTime";
				$wpdb->query($sql);

				$chatText ='';


				$cndNotification = "AND (type < 3 OR (type=3 AND user_id='" . $session->uid . "' AND username='" . $session->username. "'))"; //chat message or own notification (type 3)

				$sql = "SELECT * FROM `$table_chatlog` WHERE room='$room' $cndNotification AND private_uid = '0' AND type ='2' AND mdate > $updated ORDER BY mdate DESC LIMIT 0,20";
				$sql = "SELECT * FROM ($sql) items ORDER BY mdate ASC";

				$chatRows = $wpdb->get_results($sql);

				if ($wpdb->num_rows>0) foreach ($chatRows as $chatRow)
						$chatText .= ($chatText?'<BR>':'') .'<font color="#77777">'. date('H:i:s',$chatRow->mdate) . '</font> <B>' . $chatRow->username .'</B>: '. $chatRow->message;

					?>chatText=<?php echo urlencode($chatText)?>&updateTime=<?php echo time()?><?php


					break;
			case 'chatfilter':
				//for filtering, translations of text before sending to chat, enabled with chatfilter=1

				$username = sanitize_file_name($_POST['u']);
				$session = sanitize_file_name($_POST['s']);
				$room = sanitize_file_name($_POST['r']);
				$message = sanitize_text_field( $_POST['m'] );
				$private = sanitize_file_name( $_POST['private']);

				if (!$room || ! $session)
				{
					echo 'm=&error=NoParams';
					exit;
				}

				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

				echo 'f=VW&m='.urlencode($message);
				break;

				//! vc_chatlog
			case 'vc_chatlog':

				//Public and private chat logs
				$private = sanitize_file_name( $_POST['private']); //private chat username, blank if public chat
				$username = sanitize_file_name($_POST['u']);
				$session = sanitize_file_name($_POST['s']);
				$room = sanitize_file_name($_POST['r']);
				$message = sanitize_text_field( $_POST['msg'] );
				$time = (int) ($_POST['msgtime']);

				//do not allow uploads to other folders

				if (!$room)
				{
					echo 'error=NoRoom';
					exit;
				}

				$message = strip_tags($message,'<p><a><img><font><b><i><u>');

				//generate same private room folder for both users
				if ($private)
				{
					if ($private > $session) $proom=$session ."_". $private;
					else $proom=$private ."_". $session;
				}

				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);

				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);

				if ($proom)
				{
					$dir.="/$proom";
					if (!file_exists($dir)) mkdir($dir);
				}

				$day=date("y-M-j",time());

				$dfile = fopen($dir."/Log$day.html","a");
				fputs($dfile,$message."<BR>");
				fclose($dfile);

				//update html chat log

				$pos = strpos($message,': ')+1;
				$message = substr($message, $pos); //message without username

				if ($message)
				{
					$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";
					$ztime = time();


					if ($private)
					{
						$user = get_user_by('login', $private);
						$private_uid = $user->ID;
					}

					//msg type: 1 flash, 2 web ext
					$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`, `private_uid`) VALUES ('$username', '$room', '$message', $ztime, '1', '$private_uid')";
					$wpdb->query($sql);
				}

				?>loadstatus=1<?php
				break;
				//! v4 Presentation AJAX calls
				//! vw_files
			case 'vw_files':
				if ($_GET["room"]) $room=sanitize_file_name($_GET["room"]);
				if ($_POST["room"]) $room=sanitize_file_name($_POST["room"]);

				if (!$room) exit;

				echo '<files>';

				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);

				$handle=opendir($dir);
				while
				(($file = readdir($handle))!==false)
				{
					if (($file != ".") && ($file != "..") && (!is_dir("$dir/".$file)))
						echo "<file file_name=\"".$file."\" file_size=\"".filesize("$dir/".$file)."\" />";
				}
				closedir($handle);

				echo '</files>';
				break;

				//! vw_upload
			case 'vw_upload':
				if (!is_user_logged_in()) exit;

				if ($_GET["room"]) $room=sanitize_file_name($_GET["room"]);
				if ($_POST["room"]) $room=sanitize_file_name($_POST["room"]);

				$slides = sanitize_file_name($_GET["slides"]);
				$addSlide = sanitize_file_name($_GET["addSlide"]);

				$filename=sanitize_file_name($_FILES['vw_file']['name']);

				if (!$room) exit;
				if (strstr($filename,".php")) $filename = ""; //duplicate extension not allowed
				$filename = preg_replace(array('#[\\s]+#', '#[^A-Za-z0-9\. -]+#'), array('_', ''), $filename);

				if (!$filename) exit;

				$destination = $options['uploadsPath'] . "/$room/";
				if (!file_exists($destination)) mkdir($destination);

				if ($slides)
				{
					$destination .= "slides/";
					if (!file_exists($destination)) mkdir($destination);
				}

				//verify extension
				$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

				$allowed = array('swf','jpg','jpeg','png','gif','txt','doc','docx','pdf', 'mp4', 'flv', 'avi', 'mpg', 'mpeg', 'ppt','pptx', 'pps', 'ppsx', 'doc', 'docx', 'odt', 'odf', 'rtf', 'xls', 'xlsx');

				if (in_array($ext,$allowed))
				{
					move_uploaded_file($_FILES['vw_file']['tmp_name'], $destination . $filename);

					if ($slides && $addSlide)
					{

						$source = VWliveWebcams::path2url($destination . $filename);
						$root_url = VWliveWebcams::path2url($options['uploadsPath'] . '/');
						$label = basename($filename, strrchr($filename, '.'));
						$type = 'Graphic';

						if ( in_array($ext, array('ppt', 'pptx', 'pps', 'ppsx', 'txt', 'doc', 'docx', 'odt', 'odf', 'rtf', 'xls', 'xlsx')) )  VWliveWebcams::importPPT($room, $label, $destination . $filename, $root_url);
						if ( in_array($ext, array('pdf')) )  VWliveWebcams::importPDF($room, $label, $destination . $filename, $root_url);
						if ( in_array($ext, array('png', 'jpg', 'swf', 'jpeg')) )  VWliveWebcams::addSlide($room, $label, $source, $type);
					}

					$debug = $destination . $filename;

					echo 'debug='.urlencode($debug). '&';

				}else echo 'uploadFailed=badExtension&';

				echo 'loadstatus=1';

				break;

			case 'vw_fdelete':

				if (!is_user_logged_in()) exit;

				$room = sanitize_file_name($_GET["room"]);
				$filename = sanitize_file_name($_GET["filename"]);

				if (!$room) exit;
				if (!$filename) exit;

				unlink($options['uploadsPath'] . "/$room/$filename");

				break;

				//! vw_slides
			case 'vw_slides':
				$room=sanitize_file_name($_POST['room']);
				if (!$room) exit;

				$dir = $options['uploadsPath'] ."/$room";
				if (!file_exists($dir)) @mkdir($dir);
				$dir .= "/slides";
				if (!file_exists($dir)) @mkdir($dir);

				if (file_exists($dir . "/slideshow.xml")) echo file_get_contents($dir . "/slideshow.xml");
				else echo "<SLIDES></SLIDES>";
				break;

			case 'vw_slidesa':
				$room = sanitize_file_name($_POST['room']);


				if (!$room) exit;

				$label = $_POST['label'];
				$source = $_POST['source'];
				$type = $_POST['type'];

				VWliveWebcams::addSlide($room, $label, $source, $type);


				echo 'loadstatus=1';

				break;

			case 'vw_slidecam':
				$room=sanitize_file_name($_POST['room']);

				if (!$room) exit;

				$stream=$_POST['stream'];
				$recording=$_POST['recording'];
				$rectime=$_POST['rectime'];

				VWliveWebcams::addData($room, $stream, "stream=$stream&duration=$rectime", 'Stream');


				echo 'loadstatus=1';
				break;

			case 'vw_slidesd':
				if (!is_user_logged_in())
				{
					echo 'success=0&msg=' . urlencode('login-required');
					exit;
				}

				$room = sanitize_file_name($_POST['room']);
				if (!$room) exit;

				$label = sanitize_file_name($_POST['label']);
				$index = sanitize_file_name($_POST['ix']);
				$slide = sanitize_file_name($_POST['id']);


				$filename = $options['uploadsPath']  . "/$room/slides/slideshow.xml";
				if (file_exists($filename)) $txt = implode(file($filename)); else exit;

				$txt=preg_replace("/<SLIDE index=\"$index\" label=\"$label\" [^>]+ \/>\r/","",$txt);

				//some cleanup
				$txt=str_replace("  "," ",$txt);
				$txt=str_replace("\r \r","\r",$txt);
				$txt=str_replace("\r\r","\r",$txt);

				//assign good order numbers
				preg_match_all("|<SLIDE (.*) />|U",  $txt, $out, PREG_SET_ORDER);
				$k=1;
				for ($i=0;$i<count($out);$i++)
				{
					$repl=preg_replace('/index="(\d+)"/','index="'.sprintf("%02d",$k++).'"',$out[$i][0]);
					$txt=str_replace($out[$i][0],$repl,$txt);
				}

				// save file
				$fp=fopen($filename,"w");
				if ($fp)
				{
					fwrite($fp, $txt);
					fclose($fp);
				}

				//delete slide files

				if ($slide != '')
				{
					$dir = $options['uploadsPath'];
					$dir.="/$room";
					$dir.='/slides';
					$dir.="/$slide";

					if (file_exists($dir))
					{
						$files = glob($dir . '/*'); // get all file names
						foreach($files as $file){ // iterate files
							if(is_file($file))
								unlink($file); // delete file
						}
					}
				}

				echo "&slide=$slide&loadstatus=1";

				break;


				//! comments
			case 'comments':
				echo '<comments>';

				foreach (array('_session', 'room', 'slide') as $vname)
				{
					${$vname} = sanitize_file_name($_POST[$vname]);
				}

				if (!$room) exit;

				$slide = (int) $slide;
				if (!$slide) $slide='0';

				$destination = $options['uploadsPath']  . '/' . $room .'/';
				if (!file_exists($destination)) mkdir($destination);

				$destination .= 'slides/';
				if (!file_exists($destination)) mkdir($destination);

				$destination .= $slide . '/';
				if (!file_exists($destination)) mkdir($destination);

				$comments = VWliveWebcams::varLoad($destination . '_comments');

				if (is_array($comments))
				{
					//comment types
					$types['Text'] = 1;
					$types['Video'] = 2;
					$types['Audio'] = 3;
					$types['File'] = 4;
					$types['Whiteboard'] = 5;
					$code_types = array_flip($types);

					$i=0;
					foreach ($comments as $id => $comment)
					{
						echo '<comment index="'.(++$i).'" id="' . $id . '" data="' . htmlspecialchars($comment['data']) . '" start="' .  $comment['start']  . '" duration="' .  $comment['duration']  . '" order="' .  $comment['order'] .'" created="'.date("F j, Y, g:i a", $comment['rdate']). '" email="' .  $comment['aid']  .'" type="'.$code_types[$comment['type']].'" />';

						if ($comment['order'] != $i) $comments[$id]['order'] = $i; //update order if not set right

					}

				}
				echo '</comments>';

				break;

			case 'comment-edit':

				foreach (array( '_session', 'room', 'slide', 'type', 'data', 'add', 'start', 'duration', 'del', 'id', 'ID', 'Slideshow', 'Slide', 'Type', 'Author', 'Start', 'Duration', 'Data') as $vname)
				{
					${$vname} = sanitize_text_field($_POST[$vname]);
				}

				$room = sanitize_file_name($room);
				if (!$room) exit;

				$tid = $room;

				$sid = $slide = (int) $slide;
				$ID = (int) $ID;

				//path to slide contents
				$dir = $options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				$dir.='/slides';
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$slide";
				if (!file_exists($dir)) mkdir($dir);


				//add
				if ($add)
				{
					//comment types
					$types['Text'] = 1;
					$types['Video'] = 2;
					$types['Audio'] = 3;
					$types['File'] = 4;
					$types['Whiteboard'] = 5;
					$code_types = array_flip($types);


					$status = 1;

					$id = VWliveWebcams::commentAdd($dir, $room, $sid, $data, $types[$type], $start, $duration, $_uid, $status);
					echo 'success=1&id='.$id.'&msg='. urlencode(__('Comment added successfully!'));
					exit;
				}

				//edit
				if ($ID)
				{
					echo 'success=1&id='.$ID.'&msg='. urlencode(__('Not implemented, yet!'));
					exit;
				}

				//delete
				if ($del)
				{
					$id = (int) $id;

					//Check owner
					//only owner can delete thread comments

					if ($tid)
					{

						$comments = VWliveWebcams::varLoad($dir . '/_comments');
						$whiteboard = VWliveWebcams::varLoad($dir . '/_whiteboard');

						$c0=count($comments);

						$cd=$wd = 0;

						if (is_array($comments))
						{
							$comment = $comments[$id];

							$commentStart = $comment['start']*1000; //in ms wb time
							$commentEnd = ($comment['start']+$comment['duration'])*1000; //in ms wb time

							//delete  whiteboard elements occuring at same time from same author
							if (is_array($whiteboard))
								foreach ($whiteboard as $idw => $element)
								{
									if ($element['start'] >= $commentStart && $element['end'] <= $commentEnd && ($element['aid'] == $comment['aid'] || !$element['aid']))
									{
										unset($whiteboard[$idw]);
										$wd++;
									}
								}

							//delete comment
							unset($comments[$id]);
							$cd++;
						}

						//save updated data
						if (is_array($whiteboard)) $whiteboard2 = array_values($whiteboard);
						else $whiteboard2 = array();
						VWliveWebcams::varSave($dir . '/_whiteboard', $whiteboard2);

						if (is_array($comments))  $comments2 = array_values($comments);
						else $comments2 = array();
						VWliveWebcams::varSave($dir . '/_comments', $comments2);

						$aff .= "&whiteboard=" . $wd;
						$aff .= "&comments=" . $cd;

						echo 'success=1&id='.$id.$aff.'&dbg='.urlencode($c0).'&msg='. urlencode(__('Comment deleted successfully!'));
					}
					else echo 'success=0&msg=' . urlencode('wrong-thread-not-moderator');

					exit;
				}
				break;

			case 'comment-upload':
				foreach (array('room', 'slide', '_session', 'start', 'duration') as $vname)
				{
					${$vname} = sanitize_text_field($_GET[$vname]);
				}

				$tid = $room;

				$sid = $slide = (int) $slide;
				if (!$tid) exit;

				if ($tid)
				{

					$filename=sanitize_file_name($_FILES['vw_file']['name']);

					if (strstr($filename,'.php')) $filename = '';
					if (preg_replace('([^\w\s\d\-_~,;:\[\]\(\).])', '', $filename) != $filename) $filename = '' ;
					if (preg_replace('([\.]{2,})', '', $filename) != $filename) $filename = '' ;

					if (!$filename)
					{
						echo 'success=0&msg=' . urlencode('bad-filename');
						exit;
					}

					$dir = $options['uploadsPath'];
					if (!file_exists($dir)) mkdir($dir);
					$dir.="/$room";
					if (!file_exists($dir)) mkdir($dir);
					$dir.='/slides';
					if (!file_exists($dir)) mkdir($dir);
					$dir.="/$slide";
					if (!file_exists($dir)) mkdir($dir);

					$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

					$allowed = array('swf','jpg','jpeg','png','gif', 'pdf', 'mp3','wav', 'aac', 'ogg','3gp', '3g2', 'avi', 'f4v', 'flv', 'm2v', 'm4p', 'm4v', 'mp2', 'mkv', 'mov', 'mp4', 'mpg', 'mpe', 'mpeg', 'mpv', 'mwv', 'ogv', 'rm', 'rmvb', 'svi','ts', 'qt', 'vob', 'webm', 'wmv','ppt','pptx', 'pps', 'ppsx', 'txt', 'doc', 'docx', 'odt', 'odf', 'rtf', 'xls', 'xlsx');

					if (in_array($ext, $allowed))
					{
						move_uploaded_file($_FILES['vw_file']['tmp_name'], $dir .'/'. $filename);

						$source = $rootURL. $dir .'/'. $filename;
						$label = basename($filename, ".".$ext);
						$prefix = $tid.'-'.$sid.'-';
						$status = 1;

						$imported = 0;

						//comment types
						$types['Text'] = 1;
						$types['Video'] = 2;
						$types['Audio'] = 3;
						$types['File'] = 4;
						$types['Whiteboard'] = 5;
						$code_types = array_flip($types);

						//  if ( in_array($ext, array('pdf', 'png', 'jpg', 'swf', 'jpeg', 'ppt', 'pptx', 'pps', 'ppsx', 'txt', 'doc', 'docx', 'odt', 'odf', 'rtf', 'xls', 'xlsx')) )

						//video
						if (in_array($ext, array('3gp', '3g2', 'avi', 'f4v', 'flv', 'm2v', 'm4p', 'm4v', 'mp2', 'mkv', 'mov', 'mp4', 'mpg', 'mpe', 'mpeg', 'mpv', 'mwv', 'ogv', 'rm', 'rmvb', 'svi','ts', 'qt', 'vob', 'webm', 'wmv')) )
						{
							$type = $types['Video'];
							$data = 'stream=mp4:' . urlencode($prefix . $label) . '.mp4';
							VWliveWebcams::commentImportVideo($dir, $tid, $sid, $data, $type, $start, $_uid, $status, $dir, $filename, $prefix . $label );
							$imported = 1;
						}

						//audio
						if (in_array($ext, array('mp3', 'wav', 'aac', 'ogg')) )
						{
							$type = $types['Audio'];
							$data = 'stream=mp3:' . urlencode($prefix . $label) . '.mp3';
							VWliveWebcams::commentImportAudio($dir, $tid, $sid, $data, $type, $start, $_uid, $status, $dir, $filename, $prefix . $label );
							$imported = 1;
						}

						//add file download
						if (!$imported)
						{
							$type = $types['Text'];
							$data = 'text=' . urlencode("File: <U><A HREF=\"$source\">$label</A></U>");

							VWliveWebcams::commentAdd($dir, $tid, $sid, $data, $type, $start, $duration, $_uid, $status );
						}


					}

					echo 'success=1&name='. urlencode($filename);
				}
				else echo 'success=0&msg=' . urlencode('no-thread');

				break;

			case 'comment-webcam':

				if (!is_user_logged_in())
				{
					echo 'success=0&msg=' . urlencode('login-required');
					exit;
				}

				foreach (array( '_session', 'room', 'stream', 'recording', 'rectime') as $vname)
				{
					${$vname} = sanitize_text_field($_POST[$vname]);
				}

				foreach (array( 'slide', 'start', 'content') as $vname)
				{
					${$vname} = sanitize_text_field($_GET[$vname]);
				}

				$sid = (int) $slide;
				$tid = $room;

				if (!$room) exit;


				//path to slide contents
				$dir = $options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				$dir.='/slides';
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$slide";
				if (!file_exists($dir)) mkdir($dir);

				if ($tid)
				{
					//comment types
					$types['Text'] = 1;
					$types['Video'] = 2;
					$types['Audio'] = 3;
					$types['File'] = 4;
					$types['Whiteboard'] = 5;
					$code_types = array_flip($types);

					if ($content == 'audio') $type = $types['Audio'];
					else $type = $types['Video'];

					$data = 'stream=' . urlencode($stream);
					$status = 1;

					VWliveWebcams::commentAdd($dir, $tid, $sid, $data, $type, $start, $rectime + 1, $_uid, $status );

				}
				else echo 'success=0&msg=' . urlencode('nope');

				break;


				//! whiteboard

			case 'whiteboard':

				foreach (array('_session', 'room', 'slide') as $vname)
				{
					${$vname} = sanitize_text_field($_POST[$vname]);
				}

				$slide = (int) $slide;

				$room = sanitize_file_name($room);
				if (!$room) exit;

				echo '<whiteboard>';

				//path to slide contents
				$dir = $options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				$dir.='/slides';
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$slide";
				if (!file_exists($dir)) mkdir($dir);

				$whiteboard = VWliveWebcams::varLoad($dir . '/_whiteboard');

				if (is_array($whiteboard))
				{
					$i=0;
					foreach ($whiteboard as $id => $element)
					{
						echo '<item index="'.(++$i).'" id="' . $id . '" data="' . htmlspecialchars($element['data']) . '" start="' .  $element['start']  . '" end="' .  $element['end']  . '"  created="'.date("F j, Y, g:i a", $element['rdate']). '" />';

					}
				}

				echo '</whiteboard>';

				break;

			case 'whiteboard-add':

				foreach (array( '_session', 'room', 'slide', 'start', 'duration', 'recordings') as $vname)
				{
					${$vname} = sanitize_text_field($_POST[$vname]);
				}

				$tid = $room;
				$sid = $slide = (int) $slide;


				if (!is_user_logged_in())
				{
					echo 'success=0&msg=' . urlencode('login-required');
					exit;
				}


				$current_user = wp_get_current_user();
				$aid = $current_user->display_name;
				//$aid = $_uid;

				$end = $start + $duration;
				$rdate = time();
				$status = 1;

				//path to slide contents
				$dir = $options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				$dir.='/slides';
				if (!file_exists($dir)) mkdir($dir);
				$dir.="/$slide";
				if (!file_exists($dir)) mkdir($dir);

				$whiteboard = VWliveWebcams::varLoad($dir . '/_whiteboard');

				if (!is_array($whiteboard)) $whiteboard = array();

				$id = count($whiteboard);

				if ($recordings>0)
				{
					for ($i=0; $i<$recordings; $i++)
					{
						$data = $_POST['r'.$i];
						$start1 = $start + $_POST['r'.$i.'_time'];

						//'tid' =>$tid, 'sid' => $sid,
						$element = array('data' => $data, 'start' => $start1, 'end' => $end, 'rdate' => $rdate, 'aid' => $aid, 'status' => $status);
						$whiteboard[] = $element;

					}

					VWliveWebcams::varSave($dir . '/_whiteboard', $whiteboard);

					echo 'success=1&msg='. urlencode(__('Whiteboard elements added successfully!'));
					exit;
				}

				echo 'success=0&msg='. urlencode(__('No whiteboard elements to add!'));

				break;

				// - Presentation

			default:
				echo 'task=' . $_GET['task'] . '&status=notImplemented';
			}

			//end vwcns_callback
			die();
		}


		//! Presentation Functions
		static function addSlide($room, $label, $source, $type)
		{
			VWliveWebcams::addData($room, $label, "src=$source", $type);
		}


		static function addData($room, $label, $data, $type)
		{

			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}

			$options = get_option('VWliveWebcamsOptions');

			$filename = $options['uploadsPath'] . "/$room/slides/slideshow.xml";
			if (file_exists($filename)) $txt = implode(file($filename));
			if (!$txt) $txt="<SLIDES>\r</SLIDES>";

			$txt = str_ireplace("</SLIDES>"," <SLIDE index=\"00\" label=\"$label\" type=\"$type\" data=\"$data\" />\r</SLIDES>",$txt);

			//assign good order numbers
			preg_match_all("|<SLIDE (.*) />|U",  $txt, $out, PREG_SET_ORDER);
			$k=1;
			for ($i=0;$i<count($out);$i++)
			{
				$repl=preg_replace('/index="(\d+)"/','index="'.sprintf("%02d",$k++).'"',$out[$i][0]);
				$txt=str_replace($out[$i][0],$repl,$txt);
			}

			// save file
			$fp=fopen($filename,"w");
			if ($fp)
			{
				fwrite($fp, $txt);
				fclose($fp);
			}
		}


		/*
PPT, PDF conversion requires:
1. Apache_OpenOffice
2. unoconv
3. ImageMagick
*/

		static function importPPT($room, $label, $filename, $root_url)
		{
			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}

			$filepath =  $filename;

			$options = get_option('VWliveWebcamsOptions');

			$folder = $options['uploadsPath'] .  "/$room/slides/";
			$outpath = $folder;

			$newFolder = $outpath . $label . '/';


			if (!file_exists($newFolder)) mkdir($newFolder);

			$debug = $outpath;

			/*
	Paths:
	[~]# which unoconv
	/usr/bin/unoconv
	[~]# which convert
	/usr/bin/convert
	*/

			//convert to pdf
			$cmd = $options['unoconvPath'] . ' -f pdf -o \'' . $outpath . $label . '.pdf\' \'' . $filepath . '\'';
			exec($cmd, $output, $returnvalue);

			//$debug = $cmd;

			//convert to png
			$cmd = $options['convertPath'] . ' \'' . $outpath . $label . '.pdf\' \'' . $newFolder . '%03d.png\'';
			exec($cmd, $output, $returnvalue);

			$files = scandir($newFolder);
			foreach ($files as $file)
			{
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				$no = basename($file, strrchr($file, '.'));
				if ($ext == 'png') VWliveWebcams::addSlide($room, $label . ' #' . $no, $root_url . $folder . $label .'/'. $file, 'Graphic');
			}

			echo 'importDebug=' . $debug . '&';

		}


		static function importPDF($room, $label, $filename, $root_url)
		{
			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}

			$filepath = $filename;

			$options = get_option('VWliveWebcamsOptions');

			$folder =  $options['uploadsPath'] . "/$room/slides/";
			$outpath = $folder;

			$newFolder = $outpath . $label . '/';

			if (!file_exists($newFolder)) mkdir($newFolder);

			//convert to png
			$cmd = $options['convertPath'] . ' \'' . $filepath . '\' \'' . $newFolder . '%03d.png\'';
			exec($cmd, $output, $returnvalue);

			$files = scandir($newFolder);
			foreach ($files as $file)
			{
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				$no = basename($file, strrchr($file, '.'));
				if ($ext == 'png') VWliveWebcams::addSlide($room, $label . ' #' . $no, $root_url . $folder . $label .'/'. $file, 'Graphic');
			}


			echo 'importDebug=' . $debug . '&';

		}


		static function commentAdd($dir, $tid, $sid, $data, $type, $start, $duration, $aid, $status )
		{
			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}

			$current_user = wp_get_current_user();

			$comments = VWliveWebcams::varLoad($dir . '/_comments');
			if (!is_array($comments)) $comments = array();
			$id = count($comments);

			$comment = array(
				'tid'=>$tid,
				'sid' => $sid,
				'data' => $data,
				'type'=> $type,
				'start'=> $start,
				'duration' => $duration,
				'order' => $id,
				'rdate' => time(),
				'aid' => $current_user->display_name,
				'uid' => $current_user->ID,
				'status' => $status
			);

			$comments[$id] =  $comment;

			VWliveWebcams::varSave($dir . '/_comments', $comments);

			echo 'success=1&id='.$id.'&msg='. urlencode(__('Comment added successfully!'));

			return $id;
		}


		static function commentImportVideo($dir, $tid, $sid, $data, $type, $start, $_uid, $status, $path, $filename, $label )
		{
			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}


			$options = get_option('VWliveWebcamsOptions');

			//rtmp video streams folder
			$streams_path = $options['streamsPath'];

			if (!file_exists($streams_path))
			{
				echo 'importError=StreamsPathMissing';
				exit;
			}

			$stream = $label;

			//ffmpeg
			$ffmpegcall = $options['ffmpegPath'] . " -y -vb 512k -vcodec libx264 -coder 0 -bf 0 -level 3.1 -g 30 -maxrate 768k -acodec libfaac -ac 2 -ar 22050 -ab 96k -x264opts vbv-maxrate=364:qpmin=4:ref=4";

			//mp4
			$output_file = $streams_path . $stream . ".mp4";
			$log_file =  $dir . '/' . $stream  . ".txt";
			$filepath =  $dir . '/' .  $filename;

			$cmd = $ffmpegcall . " '$output_file' -i '$filepath' >&'$log_file' &";
			exec($cmd, $output, $returnvalue);

			//get duration
			$cmd = $options['ffmpegPath'] . ' -y -i "'. $filepath . '" 2>&1';
			$info = shell_exec($cmd);
			preg_match('/Duration: (.*?),/', $info, $matches);
			$duration = explode(':', $matches[1]);
			$videoDuration = intval($duration[0]) * 3600 + intval($duration[1]) * 60 + intval($duration[2]);

			if (!$videoDuration) $videoDuration = 30;

			$duration = $videoDuration + 3;

			VWliveWebcams::commentAdd($dir, $tid, $sid, $data, $type, $start, $duration, $_uid, $status );

			//$debug = "$filepath++$output_file";

			echo 'importDebug=' . $debug . '&';
		}


		function commentImportAudio($dir, $tid, $sid, $data, $type, $start, $_uid, $status, $path, $filename, $label )
		{
			if (!is_user_logged_in())
			{
				echo 'success=0&msg=' . urlencode('login-required');
				exit;
			}

			$options = get_option('VWliveWebcamsOptions');

			//rtmp video streams folder
			$streams_path = $options['streamsPath'];

			if (!file_exists($streams_path))
			{
				echo 'importError=StreamsPathMissing';
				exit;
			}

			$stream = $label;

			//ffmpeg
			$ffmpegcall = $options['ffmpegPath'] . " -y -acodec libmp3lame";

			//mp3
			$output_file = $streams_path . $stream . ".mp3";
			$log_file =  $dir . '/' . $stream  . ".txt";
			$filepath =  $dir . '/' .  $filename;

			$cmd = $ffmpegcall . " '$output_file' -i '$filepath' >&'$log_file' &";
			exec($cmd, $output, $returnvalue);

			//get duration
			$cmd = $options['ffmpegPath'] . ' -y -i "'. $filepath . '" 2>&1';
			$info = shell_exec($cmd);
			preg_match('/Duration: (.*?),/', $info, $matches);
			$duration = explode(':', $matches[1]);
			$videoDuration = intval($duration[0]) * 3600 + intval($duration[1]) * 60 + intval($duration[2]);

			if (!$videoDuration) $videoDuration = 30;

			$duration = $videoDuration + 3;

			VWliveWebcams::commentAdd($dir, $tid, $sid, $data, $type, $start, $duration, $_uid, $status );

			//$debug = "$filepath++$output_file";

			echo 'importDebug=' . $debug . '&';
		}




		//! Utility Functions

		static function roomURL($room)
		{

			$options = get_option('VWliveWebcamsOptions');

			global $wpdb;

			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

			if ($postID) return get_post_permalink($postID);
		}


		static function path2url($file)
		{
			if (!function_exists('get_home_path')) require_once( ABSPATH . '/wp-admin/includes/file.php' );

			return get_site_url() . '/' . str_replace( get_home_path(), '', $file);
		}


		static function format_time($t,$f=':') // t = seconds, f = separator
			{
			return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		}


		static function format_age($t)
		{
			if ($t<41) return __('LIVE', 'ppv-live-webcams');
			return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
		}


		//! Admin Side

		function admin_bar_menu($wp_admin_bar)
		{
			if (!is_user_logged_in()) return;

			$options = get_option('VWliveWebcamsOptions');

			if( current_user_can('editor') || current_user_can('administrator') ) {

				$menu_id = 'videowhisper-ppvlivewebcams';

				$wp_admin_bar->add_node( array(
						'id'     => $menu_id,
						'title' => 'PaidVideochat',
						'href'  => admin_url('admin.php?page=live-webcams')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-records',
						'title' => __('Approve Users', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams-records')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-sessions',
						'title' => __('Session Logs', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams-sessions')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-admin',
						'title' => __('RTMP Admin', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams-admin')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-posts',
						'title' => __('Webcam Posts', 'ppv-live-webcams'),
						'href'  => admin_url('edit.php?post_type=' . $options['custom_post'])
					) );


				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-settings',
						'title' => __('Settings', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-bill',
						'title' => __('Billing', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams&tab=billing')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-docs',
						'title' => __('Documentation', 'ppv-live-webcams'),
						'href'  => admin_url('admin.php?page=live-webcams-doc')
					) );
			}

			$current_user = wp_get_current_user();

			if ($options['p_videowhisper_webcams_performer'])
				//if (get_post_status( $options['p_videowhisper_webcams_performer'])) //exists
				if (VWliveWebcams::any_in_array( array( $options['rolePerformer'], 'administrator', 'super admin'), $current_user->roles))
					$wp_admin_bar->add_node(array(
							'parent' => 'my-account-with-avatar',
							'id'     => 'videowhisper_performer_dashboard',
							'title' => __('Performer Dashboard', 'ppv-live-webcams') ,
							'href'  =>  get_permalink($options['p_videowhisper_webcams_performer']),
						));

				if ($options['p_videowhisper_webcams_studio'])
					if (VWliveWebcams::any_in_array( array( $options['roleStudio'], 'administrator', 'super admin'), $current_user->roles))
						$wp_admin_bar->add_node(array(
								'parent' => 'my-account-with-avatar',
								'id'     => 'videowhisper_studio_dashboard',
								'title' => __('Studio Dashboard', 'ppv-live-webcams') ,
								'href'  =>  get_permalink($options['p_videowhisper_webcams_studio']),
							));
		}


		function admin_menu() {

			add_menu_page('Live Webcams', 'Live Webcams', 'manage_options', 'live-webcams', array('VWliveWebcams', 'adminOptions'), 'dashicons-video-alt2',83);

			add_submenu_page("live-webcams", "Settings for Live Webcams", "Settings", 'manage_options', "live-webcams", array('VWliveWebcams', 'adminOptions'));
			add_submenu_page("live-webcams", "User Approve", "User Approve", 'promote_users', "live-webcams-records", array('VWliveWebcams', 'adminRecords'));
			add_submenu_page("live-webcams", "Admin for Live Webcams", "Live Admin", 'edit_users', "live-webcams-admin", array('VWliveWebcams', 'adminLive'));
			add_submenu_page("live-webcams", "Session Logs", "Session Logs", 'list_users', "live-webcams-sessions", array('VWliveWebcams', 'adminSessions'));
			add_submenu_page("live-webcams", "Assign Performers to Studios", "Studio Assign", 'promote_users', "live-webcams-studio", array('VWliveWebcams', 'adminStudio'));

			add_submenu_page("live-webcams", "Documentation for Live Webcams", "Documentation", 'manage_options', "live-webcams-doc", array('VWliveWebcams', 'adminDocs'));

			//hide add submenu
			$options = get_option('VWliveWebcamsOptions');
			global $submenu;
			unset($submenu['edit.php?post_type=' . $options['custom_post']][10]);
		}


		function admin_head() {

			$options = get_option('VWliveWebcamsOptions');
			if( get_post_type() != $options['custom_post']) return;

			//hide add button
			echo '<style type="text/css">
    #favorite-actions {display:none;}
    .add-new-h2{display:none;}
    .tablenav{display:none;}
    </style>';
		}


		function settings_link($links) {
			$settings_link = '<a href="admin.php?page=live-webcams">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}


		function adminStudio()
		{
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Assign Performers to Studios</h2>
Assigns existing user to studio (as performer): assigns performer role to user if necessary, creates a webcam listings and selects it as default for user, assigns performer and listing to studio to show in studio dashboard.
</div>
<?php
			$options = get_option('VWliveWebcamsOptions');


			//input
			$user_id = intval($_GET['user_id']);
			$user = sanitize_text_field( $_POST['user'] );
			$studio = sanitize_text_field( $_POST['studio']);

			//output
			$htmlCode = '';

			if ($user_id || $user)
			{
				if ($user)
				{
					if (filter_var($user, FILTER_VALIDATE_EMAIL)) $member = get_user_by('email', $user);
					else $member = get_user_by('login', $user);

					if (!$member) $htmlCode .=  __('User not found by email or login: ', 'ppv-live-webcams') . $user;
				}

				if ($user_id>0)
				{
					$member = get_userdata( $user_id);

					if (!$member) $htmlCode .=  __('User not found by ID: ', 'ppv-live-webcams') . $user_id ;
				}

				if ($member)
					if ($studio)
					{

						if (filter_var($studio, FILTER_VALIDATE_EMAIL)) $memberStudio = get_user_by('email', $studio);
						else $memberStudio = get_user_by('login', $studio);


						if (!$memberStudio) $htmlCode .=  __('Studio not found: ', 'ppv-live-webcams') . $studio;
						else
						{

							//assign to studio
							update_user_meta($member->ID, 'studioID', $memberStudio->ID);
							update_user_meta($member->ID, 'studioLogin', $memberStudio->user_login);
							update_user_meta($member->ID, 'studioDisabled', 0);

							//set performer role
							wp_update_user( array( 'ID' => $member->ID, 'role' => $options['rolePerformer'] ) );

							//also create a webcam listing
							//$newPerformer = get_userdata($member->ID);
							$name = VWliveWebcams::performerName($member, $options);
							$webcamID = VWliveWebcams::webcamPost($name, $name, $user_id, $memberStudio->ID);
							update_user_meta($member->ID, 'currentWebcam', $webcamID);


							$htmlCode .= '<div class="notice">Assigned Performer to Studio';
							$htmlCode .= '<br>Performer ID: ' . $member->ID;
							$htmlCode .= '<br>Studio ID: ' . $memberStudio->ID;
							$htmlCode .= '<br>Webcam ID: ' . $webcamID . ' Webcam Listing: <a href="'.get_permalink($webcamID).'">' . $name . '</a>';
							$htmlCode .= '<br> <a class="button" href="admin.php?page=live-webcams-studio">Assign New</a>';
							$htmlCode .= '</div>';
						}



					}
				else
				{
					//pick studio
					$htmlCode .= '<div class=""><form action="admin.php?page=live-webcams-studio" method="post">';
					$htmlCode .= '<h3 class="ui header">Assign user to studio, as performer:</h3>';
					$htmlCode .= '<label>Studio<label><BR><input size="16" maxlength="32" type="text" name="studio" id="studio" value=""/> Studio member email or login.';
					$htmlCode .= '<BR><label>User Login<label><BR><input type="hidden" name="user" id="user" value="' . $member->user_login . '"/> '. $member->user_login;
					$htmlCode .= ': This user will become performer and get assigned to studio. Warning: Do not test assigning admin user as performer because this will change role and remove admin access. <BR><INPUT class="button primary" TYPE="submit" name="Assign" id="assign" value="Assign">';
					$htmlCode .= '</form></div>';

				}
			}
			else
			{
				//pick user & studio
				$htmlCode .= '<div class=""><form action="admin.php?page=live-webcams-studio" method="post">';
				$htmlCode .= '<h3 class="ui header">Assign user to studio, as performer:</h3>';
				$htmlCode .= '<label>Studio<label><BR><input size="16" maxlength="32" type="text" name="studio" id="studio" value=""/><BR>Studio member email or login. Performer and webcam listing will show in studio dashboard for this member.';
				$htmlCode .= '<BR><label>User<label><BR><input size="16" maxlength="32" type="text" name="user" id="user" value=""/><BR>User email or login. This user will become performer and get assigned to studio. Warning: Do not test assigning admin user as performer because this will change role and remove admin access.';
				$htmlCode .= '<BR><INPUT class="button primary" TYPE="submit" name="Assign" id="assign" value="Assign">';
				$htmlCode .= '</form></div>';
			}

			echo $htmlCode;

		}


		function adminRecords()
		{
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Records Review and User Approval</h2>
Review administrative records submitted by providers (performers/studios) and approve accounts.
</div>
<?php
			$options = get_option('VWliveWebcamsOptions');

			if ($user_id = $_GET['user_id'])
			{

				$user = get_userdata( $user_id);
				if (!$user) echo __('User not found: ', 'ppv-live-webcams') . $user_id;
				else
				{


					echo '<div class="notice notice-info">
<H3>Reviewing User Account: ' . $user->user_login . '</H3>';

					$verified = get_user_meta($user_id,'vwVerified', true);
					$verifiedTime = get_user_meta($user_id,'vwVerifiedTime', true);

					$adminSuspended = get_user_meta($user_id,'vwSuspended', true);
					$vwUpdated = get_user_meta($user_id,'vwUpdated', true);

					if ($_GET['verify'])
					{
						$verified = !$verified;
						update_user_meta( $user_id, 'vwVerified', $verified);
						update_user_meta( $user_id, 'vwVerifiedTime', time());

					}

					if ($_GET['suspend'])
					{
						$adminSuspended = !$adminSuspended;
						update_user_meta( $user_id, 'adminSuspended', $adminSuspended);
					}

					echo '<H4>' . __('Status', 'ppv-live-webcams') . '</H4>';
					echo '<B>' . __('Verified', 'ppv-live-webcams') . ': ' .($verified?__('Yes', 'ppv-live-webcams'):__('No', 'ppv-live-webcams')). '</B> ';
					echo '<a href="admin.php?page=live-webcams-records&verify=1&user_id='.$user_id. '">' . __('Toggle Verified', 'ppv-live-webcams') . '</a>';

					echo '<BR><B>' . __('Suspended', 'ppv-live-webcams') . ': ' .($adminSuspended?'Yes':'No'). '</B> ';
					echo '<a href="admin.php?page=live-webcams-records&suspend=1&user_id='.$user_id. '">' . __('Toggle Suspended', 'ppv-live-webcams') . '</a>';

					echo '<BR>' . __('Records Updated', 'ppv-live-webcams') . ': ' .($vwUpdated?date("F j, Y, g:i a", $vwUpdated):'Never'). '';
					echo '<BR>' . __('Last Verified', 'ppv-live-webcams') . ': ' .($verifiedTime?date("F j, Y, g:i a", $verifiedTime):'Never'). '';


					if (!is_array($options['recordFields']))
					{
						$htmlCode .= '<p>No record fields defined by administrator!</p>';
					}
					else
						foreach ($options['recordFields'] as $field => $parameters)
						{
							$htmlCode .= '<h4>'.$field.'</h4>';

							$fieldName = sanitize_title(trim($field));

							$fieldValue = htmlspecialchars(get_user_meta( $user->ID, 'vwf_' . $fieldName, true ));
							$htmlCode .= $fieldValue;
						}

					echo $htmlCode;

					echo '</div>';
				}
			}
			else
			{

				echo '<h3>User Records Pending Review</h3>';


				$args = array(
					// 'role__in'     => array($options['rolePerformer'], $options['roleStudio'], 'administrator'),
					'meta_query'   => array(
						array(
							'relation' => 'AND',
							array(
								'key' => 'vwUpdated',
								'value' => '0',
								'compare' => '!=',
							),
							array(
								'key' => 'vwVerified',
								'compare' => 'NOT EXISTS',
							)
						)
					)
				);



				$users = get_users( $args );
				if (count($users))
				{
					foreach ($users as $user)
						echo '- <a href="admin.php?page=live-webcams-records&user_id=' . $user->ID . '">' . $user->user_login  .'</a><br>';

				} else echo 'No records pending review found. Pending list includes only users that updated their records but were never verified.';
			}
?>
	<BR>For more options:
	<BR>+ <a class="button" href="users.php?orderby=vwUpdated&order=desc">Browse Users that Recently Updated Records</a> (All Users)
	<BR>+ <a class="button" href="admin.php?page=live-webcams-records">Browse Users Pending Review</a> (Never Verified Users)
	<BR>+ <a class="button" href="admin.php?page=live-webcams&tab=record">Configure Administrative Fields</a>

<p>Administrative records refers to custom fields defined by administrators that users can fill. These are only accessible by administrators and can be used for identity verification, collecting payout info.
</p>
	</div>
	<?php
		}


		function adminLive()
		{
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Live Admin for VideoWhisper PPV Live Webcams - Paid Videochat</h2>
</div>
<?php
			$swfurl = plugin_dir_url(__FILE__) . "videowhisper/videowhisperAdmin.swf?ssl=1";
			$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vmls&task=');
			$swfurl .= '&extension='.urlencode('_none_');
			$swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'videowhisper/');

			$bgcolor="#333333";

			$htmlCode = <<<HTMLCODE
<div id="videowhisper_container" style="width:100%; height:800px">
<object id="videowhisper_admin" width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>
<br style="clear:both">
HTMLCODE;
			echo $htmlCode;
?>
This tool allows monitoring all connected users, identifying their usage, IP, spying on the publishing webcam/microphone streams.
Select rtmp side app to connect to from <a href="admin.php?page=live-webcams&tab=server
">server settings</a>.
<br>To work with session control, application will join first created room to generate access keys.
<?php

			$options = get_option('VWliveWebcamsOptions');

			//get first room to use for session control
			$args = array(
				'post_type' => $options['custom_post'],
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'offset'           => 0,
				'orderby' => 'post_date',
				'order'            => 'ASC',
			);

			$postslist = get_posts($args);
			if (count($postslist)>0)
				foreach ( $postslist as $item )
				{
					$keyView = md5('vw' . $options['webKey']. $item->ID);
					$rtmp_server = $options['rtmp_server_admin'] . '?'. urlencode($username) .'&'. urlencode($item->post_title) .'&'. $keyView . '&0&videowhisper';

					echo 'Using room #' . $item->ID . ' (' . $item->post_title . ') with address keys ' . $rtmp_server . ' to access RTMP streaming app.';
				}


		}


		function adminSessions()
		{

			$filterMode = sanitize_file_name($_GET['filterMode']);

			if (!$filterMode) $filterMode = 'paid';
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Client Session Logs: <?php echo $filterMode ?></h2>
</div>

<a href="admin.php?page=live-webcams-sessions&filterMode=all">All</a> |
<a href="admin.php?page=live-webcams-sessions&filterMode=paid">Paid</a> |
<a href="admin.php?page=live-webcams-sessions&filterMode=private">Private Calls</a>

<?php

			self::billSessions();


			global $wpdb ;

			if ($filterMode == 'private')
			{

				$cnd ='';

				$table_sessions = $wpdb->prefix . "vw_vmls_private";

				$sql = "SELECT * FROM $table_sessions $cnd ORDER by cedate DESC";
				$sessions = $wpdb->get_results($sql);

				if ($wpdb->num_rows>0)
				{
					echo '<table class="widefat fixed">';
					echo '<thead><tr>';
					echo '<td>Client<BR># CID</td>';
					echo '<td>Performer<BR># PID</td>';
					echo '<td>Client Start<br>Client End</td>';
					echo '<td>Performer Start<br>Performer End</td>';
					echo '<td>Room<BR># ID</td>';
					echo '<td>Status</td>';
					echo '</tr></thead>';

					foreach ($sessions as $session)
					{
						echo '<tr>';
						echo '<td>' . $session->client .  '<BR># '. $session->cid . '</td>';
						echo '<td>' . $session->performer . '<BR># '. $session->pid .'</td>';
						echo '<td>' . date("M j H:i:s", $session->csdate) . '<BR>'. date("M j H:i:s", $session->cedate)  . '<BR>'. ($session->cedate - $session->csdate) . 's</td>';
						echo '<td>' . ($session->psdate > 0 ? date("M j H:i:s", $session->psdate):'--') . '<BR>'. date("M j H:i:s", $session->pedate). '<BR>'. ($session->psdate > 0 ? ($session->pedate - $session->psdate):'--') . 's</td>';
						echo '<td>' . $session->room . '<br># ' . $session->rid . '</td>';

						$statusLabel = '';
						switch ($session->status)
						{
						case '0':
							$statusLabel = 'Active';
							break;
						case '1':
							$statusLabel = 'Ended';
							break;
						case '2':
							$statusLabel = 'Billed';
							break;
						}
						echo '<td>' . $session->status .'<BR>' . $statusLabel . '</td>';
						echo '</tr>';
					}

					echo '</table>';
				}



			}
			else {
				//group mode
				if ($filterMode =='paid') $cnd = 'WHERE rmode<>0';
				if ($filterMode == 'all') $cnd ='';


				$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

				$sql = "SELECT * FROM $table_sessions $cnd ORDER by edate DESC";
				$sessions = $wpdb->get_results($sql);



				if ($wpdb->num_rows>0)
				{
					echo '<table class="widefat fixed">';
					echo '<thead><tr>';
					echo '<td>User<BR>IP</td>';
					echo '<td>Room<BR>Room ID</td>';
					echo '<td>Start<br>End</td>';
					echo '<td>Performer Active<br>With User</td>';
					echo '<td>Paid Mode</td>';
					echo '<td>Status</td>';
					echo '<td>Type</td>';
					echo '<td>Meta</td>';
					echo '</tr></thead>';

					foreach ($sessions as $session)
					{
						echo '<tr>';
						echo '<td>' . $session->session .  '<BR>'. $session->ip .  '<BR>'. ($session->broadcaster?'Broadcaster':'Client') . '</td>';
						echo '<td>' . $session->room . '<BR>'. $session->rid .'</td>';
						echo '<td>' . date("M j H:i:s", $session->sdate) . '<BR>'. date("M j H:i:s", $session->edate)  . '<BR>'. ($session->edate - $session->sdate) . 's</td>';
						echo '<td>' . ($session->rsdate > 0 ? date("M j H:i:s", $session->rsdate):'--') . '<BR>'. ($session->rsdate > 0 ? ($session->redate - $session->rsdate):'--') . 's</td>';
						echo '<td>' . $session->rmode . '</td>';

						$statusLabel = '';
						switch ($session->status)
						{
						case '0':
							$statusLabel = 'Live';
							break;
						case '1':
							$statusLabel = 'Ended';
							break;
						case '2':
							$statusLabel = 'Billed';
							break;
						}
						echo '<td>' . $session->status .'<BR>' . $statusLabel . '</td>';
						echo '<td>' . $session->type . '</td>';
						echo '<td>' . print_r(unserialize($session->roptions), true) . '</td>';
						echo '</tr>';
					}

					echo '</table>';
				}

			}
?>
			* This section also updates billing.
			<?php
		}


		static function adminDocs()
		{

			$options = get_option('VWliveWebcamsOptions');
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Documentation for VideoWhisper PPV Live Webcams - Paid Videochat</h2>
</div>

<h3>Quick Setup Tutorial</h3>
For a complete project setup tutorial see <a href="https://paidvideochat.com/features/quick-setup-tutorial/">Paid Video Chat : Quick Setup Tutorial</a> .
<h3>Plugin Setup Tutorial</h3>
<ol>
<li>Install and activate the PPV Live Webcams plugin by VideoWhisper</li>
<li>From <a href="admin.php?page=live-webcams&tab=server">Live Webcams > Settings : Server</a> in WP backend and configure settings (it's compulsory to fill a valid RTMP hosting address)</li>
<li>From <a href="options-permalink.php">Settings > Permalinks</a> enable a SEO friendly structure (ex. Post name)</li>
<li>From <a href="options-general.php">Settings > General</a> enable Membership: Anyone can register. Plugin will create and show roles on the default <a href="../wp-login.php?action=register">registration page</a>. You may need to add a menu/link/widget for users to easily find registration page.
<br>Warning: To prevent spam bot registrations, configure captcha & email verification plugins. Also WordFence. Get a <a href="https://www.google.com/recaptcha/admin/create#list">reCaptcha key from Google</a>.</li>
	<li>Install and enable a <a href="admin.php?page=live-webcams&tab=billing">billing plugin</a>. Make sure you setup a payment option (by configuring a supported billing site you register with) and a page for users to buy credits/tokens. Use Paid Membership & Content plugin to setup My Wallet page and paid videos/pictures.</li>
<li><a href="admin.php?page=live-webcams&tab=pages">Setup main feature pages</a>. From <a href="nav-menus.php">Appearance > Menus</a> add Webcams and optionally the Performer Dashboard pages to main site menu.
</li>
<li>Setup <a href="edit-tags.php?taxonomy=category&post_type=webcam">webcam categories</a>, common to site content.</li>
<li>Optional: From <a href="options-reading.php">Settings > Reading</a> setup Front page: Webcams if this is the main functionality you want to emphasize.</li>
<li>Recommended: Next step is to review <a href="<?php echo get_admin_url(); ?>admin.php?page=live-webcams&tab=support#plugins">suggested plugins</a>. Install bot protection plugins: WP Super Cache (configured to not cache for known users or GET parameters), WordFence. Setup a reliable mailing account and configure a WP SMTP plugin, add User Verification. Install and activate a theme with wide content area (preferably full page width) so videochat application interface fits.</li>
</ol>

Most of these links/features will only be available after proper setup and connfiguration:
<h3>PaidVideochat Installation Overview</h3>

	- Users can register with performer and client roles from:
	<br><?php bloginfo('wpurl'); ?>/wp-login.php?action=register
	<br>Warning: To prevent spam bot registrations, configure captcha & email verification plugins. Get a free reCaptcha key from Google.  

	<br><br>- Performers and admins (for testing) can setup their webcam page and Go Live from:
	<br><?php echo get_permalink($options['p_videowhisper_webcams_performer'])?>

	<br><br>- After broadcasting, webcam shows in Webcams list:
	<br><?php echo get_permalink($options['p_videowhisper_webcams'])?>

	<br><br>- After login, clients can buy credits/tokens (to spend in private shows and for tips):
	<br><?php echo get_permalink($options['balancePage']?$options['balancePage']:$options['p_mycred_buy_form'])?>

	<br><br> - Billing features are accessible from:
	<br><?php echo get_admin_url(); ?>admin.php?page=live-webcams&tab=billing

	<br><br> - Setup site billing details for MyCred and/or WooWallet from:
	<br><?php echo get_admin_url(); ?>admin.php?page=mycred-gateways
	<br><?php echo get_admin_url(); ?>admin.php?page=wc-settings&tab=checkout

	<br><br> - After uploading your own logos, update logo addresses from:
	<br><?php echo get_admin_url(); ?>admin.php?page=live-webcams&tab=appearance

	<br><br> - See suggested plugins to improve site reliability, security, performance and features:
	<br><?php echo get_admin_url(); ?>admin.php?page=live-webcams&tab=support#plugins
	<br>Recommended: Harden site security by adding WordFence plugin with site Firewall. Also WP Super Cache to speed up site and reduce load from aggressive crawlers and bots.
	
	<br><br> - Setup can be customized as described at:
	<br>https://paidvideochat.com/features/quick-setup-tutorial/#customize

	<br><br> - Contact VideoWhisper anytime for clarifications, custom development evaluation:
	<br>https://videowhisper.com/tickets_submit.php


<h3>Shortcodes</h3>

<h4>[videowhisper_webcams perPage="6" perrow="0" pstatus="" order_by= "default" category_id="" select_status="1" select_order="1" select_category="1" select_page="1" select_tags="1" select_name="1" include_css="1" url_vars="1" url_vars_fixed="1" studioID=""]
</h4>
Lists and updates webcams using AJAX. Allows filtering and toggling filter controls.
<br>
/ order_by: edate = last time online
/ default = features then online
/ post_date = registration
/ viewers = currently in room
/ maxViewers = maximum viewers ever
/ rand = Random order
<br>pstatus: "" = all performers (default)
/ online = online (in public or private chat)
/ public = in public chat (online and not in private)
/ private = in private shows
/ offline = currently offline
<br>select_ .. : 0/1 (enables interface to select that control)
<br>perPage : number of listings to show per page (if select_page="0" that's maximum that will show)
<br>studioID = filter based on studio account ID

<h4>[videowhisper_cam_instant]</h4>
Instantly setup and access own webcam room.

<h4>[videowhisper_cam_random]</h4>
Random videochat room as configured in backend.

<h4>[videowhisper_campreview status="online" order_by="rand" category="" perPage="1" perRow="2" width="480px" height="360px"]</h4>
Show webcam previews (video).

<h4>[videowhisper_videochat room="Room Name" webcam_id="post id"]</h4>
Shows videochat application. Automatically detects room if shown on webcam post. Room name is listed in Performer Dashboard Overview. Can also use post ID as webcam_id.


<h4>[videowhisper_webcams_performer include_css="1"]</h4>
Shows performer dashboard with balance, webcam listing management, tabs.

<h4>[videowhisper_webcams_studio include_css="1"]</h4>
Shows studio dashboard.

<h4>[videowhisper_account_records]</h4>
Shows account status and allows updating administrative records for current user. Administrative records refers to custom fields defined by administrators that users can fill. These are only accessible by adminstrators and can be used for identity verification, collecting payout info.

<h4>[videowhisper_camcontent cam="Room Name" post_id="0"]</h4>
Shows webcam content (tabs). Webcam listing name or post_id must be provided.

<h4>[videowhisper_camprofile cam="Room Name" post_id="0"]</h4>
Shows webcam listing profile (fields). Webcam listing name or post_id must be provided.

<h4>[videowhisper_caminfo cam="Room Name" info="cpm" format="csv"]</h4>
Shows info about a cam.
<br>info:
cpm = cost per minute for private show /
online = last time online /
brief = brief info /
tags = room tags /
performers = checked in performers (links) /
groupMode = group mode /
groupCPM = group CPM
<br>format:
csv = comma separated values /
serialized = php serialized string

<h4>[videowhisper_camvideo cam =  "Room (Webcam) Name" width="480px" height="360px" html5="auto" post_id=""]</h4>
Shows plain video from a room.
<br> html5 = auto/always

<h4>[videowhisper_cammpeg webcam="webcam name" width="480px" height="360px"]</h4>
Plain video as HTML5 MPEG Dash (if supported & available).

<h4>[videowhisper_camhls webcam="webcam name" width="480px" height="360px"]</h4>
Plain video as HTML5 HLS (if supported & available).

<h4>[videowhisper_htmlchat room="webcam name" width="480px" height="360px"]</h4>
HTML ajax based simplified chat with HTML5 video playback (if supported & available).

<h4>[videowhisper_cam_app room="webcam name" webcam_id="post id"]</h4>
HTML5 Videochat app interface for webcam room.


<h3>Filters</h3>
Filters allow adding content to plugin sections.

<h4>apply_filters("vw_plw_dashboard", '', $postID)</h4>
Under performer dashboard.

<h4>apply_filters("vw_plw_videochat", '', $postID)</h4>
Under videochat app.

<h3>Cookies</h3>
These cookies can be registered in a cookie manager / GDPR plugin:
<h4>htmlchat_username</h4> Required for persistence of visitor usernames in html chat and app.
<?php
		}



	}


}

//instantiate
if (class_exists("VWliveWebcams"))
{
	$liveWebcams = new VWliveWebcams();
}

//Actions and Filters
if (isset($liveWebcams))
{
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	register_activation_hook( __FILE__, array(&$liveWebcams, 'activation'));

	add_action('init', array(&$liveWebcams, 'init'));

	add_action("plugins_loaded", array(&$liveWebcams, 'plugins_loaded'));

	//admin
	add_action('admin_menu', array(&$liveWebcams, 'admin_menu'));
	add_action( 'admin_bar_menu', array(&$liveWebcams, 'admin_bar_menu'),90 );

	add_action('admin_head', array(&$liveWebcams, 'admin_head'));

	//register
	add_action('register_form', array(&$liveWebcams,'register_form'));
	add_action('user_register', array(&$liveWebcams,'user_register'));

	//login
	add_action( 'login_enqueue_scripts', array('VWliveWebcams','login_logo') );
	//add_filter( 'login_headertitle', array('VWliveWebcams','login_headertitle') );
	//add_filter( 'login_headerurl', array('VWliveWebcams','login_headerurl') );
	add_filter( 'login_redirect', array('VWliveWebcams','login_redirect'), 10, 3 );

	add_filter( 'the_title', array('VWliveWebcams','the_title'), 10, 2 );
	add_filter( 'query_vars', array('VWliveWebcams','query_vars') );


	//add_filter( 'sidebars_widgets', array(&$liveWebcams,'sidebars_widgets') );
	//add_action( 'get_sidebar', array(&$liveWebcams,'get_sidebar') );

	//page template
	add_filter( "single_template", array(&$liveWebcams,'single_template'));
	add_filter( "page_template", array(&$liveWebcams,'single_template'));

}



?>
