<?php
/*
Plugin Name: Video Share VOD - Turnkey Video Site Builder
Plugin URI: https://videosharevod.com
Description: <strong>Video Share / Video on Demand (VOD) - Turnkey Video Site Builder</strong> plugin enables users to share videos and others to watch on demand. Allows publishing archived VideoWhisper Live Streaming broadcasts and recorded videochat streams.  <a href='https://videowhisper.com/tickets_submit.php?topic=Video-Share-VOD'>Contact Us</a>
Version: 2.3.19
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
Text Domain: video-share-vod
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("VWvideoShare"))
{
	class VWvideoShare {


		public function __construct()
		{
		}

		public function VWvideoShare() { //constructor
			self::__construct();

		}

		static function install() {
			// do not generate any output here
			VWvideoShare::setupOptions();
			VWvideoShare::video_post();
			flush_rewrite_rules();
		}

		function init()
		{
			VWvideoShare::video_post();
			VWvideoShare::register_widgets();

		}


		//! Supported extensions
		function extensions_video()
		{
			return array('3gp', '3g2', 'avi', 'f4v', 'flv', 'm2v', 'm4p', 'm4v', 'mp2', 'mkv', 'mov', 'mp4', 'mpg', 'mpe', 'mpeg', 'mpv', 'mwv', 'ogv', 'ogg', 'rm', 'rmvb', 'svi','ts', 'qt', 'vob', 'webm', 'wmv');
		}

		function extensions_flash()
		{
			return array('flv', 'mp4', 'f4v', 'm4v');
		}

		// Register Custom Post Type
		static function video_post() {


			$options = get_option('VWvideoShareOptions');

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			$labels = array(
				'name'                => _x( 'Videos', 'Post Type General Name', 'video-share-vod' ),
				'singular_name'       => _x( 'Video', 'Post Type Singular Name', 'video-share-vod' ),
				'menu_name'           => __( 'Videos', 'video-share-vod' ),
				'parent_item_colon'   => __( 'Parent Video:', 'video-share-vod' ),
				'all_items'           => __( 'All Videos', 'video-share-vod' ),
				'view_item'           => __( 'View Video', 'video-share-vod' ),
				'add_new_item'        => __( 'Add New Video', 'video-share-vod' ),
				'add_new'             => __( 'New Video', 'video-share-vod' ),
				'edit_item'           => __( 'Edit Video', 'video-share-vod' ),
				'update_item'         => __( 'Update Video', 'video-share-vod' ),
				'search_items'        => __( 'Search Videos', 'video-share-vod' ),
				'not_found'           => __( 'No Videos found', 'video-share-vod' ),
				'not_found_in_trash'  => __( 'No Videos found in Trash', 'video-share-vod' ),
			);

			$args = array(
				'label'               => __( 'video', 'video-share-vod' ),
				'description'         => __( 'Video Videos', 'video-share-vod' ),
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
				'map_meta_cap'        => true,
				'menu_icon' => 'dashicons-video-alt3',
				'capability_type'     => 'post',
				'capabilities' => array(
					'create_posts' => false
				)
			);
			register_post_type( $options['custom_post'], $args );

			// Add new taxonomy, make it hierarchical (like categories)
			$labels = array(
				'name'              => _x( 'Playlists', 'taxonomy general name' ),
				'singular_name'     => _x( 'Playlist', 'taxonomy singular name' ),
				'search_items'      => __( 'Search Playlists', 'video-share-vod' ),
				'all_items'         => __( 'All Playlists', 'video-share-vod' ),
				'parent_item'       => __( 'Parent Playlist' , 'video-share-vod'),
				'parent_item_colon' => __( 'Parent Playlist:', 'video-share-vod' ),
				'edit_item'         => __( 'Edit Playlist' , 'video-share-vod'),
				'update_item'       => __( 'Update Playlist', 'video-share-vod' ),
				'add_new_item'      => __( 'Add New Playlist' , 'video-share-vod'),
				'new_item_name'     => __( 'New Playlist Name' , 'video-share-vod'),
				'menu_name'         => __( 'Playlists' , 'video-share-vod'),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'         => true,
				'rewrite'           => array( 'slug' => $options['custom_taxonomy']),
			);
			register_taxonomy( $options['custom_taxonomy'], array( $options['custom_post'] ), $args );

			if ($options['tvshows'])
			{
				$labels = array(
					'name'                => _x( 'TV Shows', 'Post Type General Name', 'video-share-vod' ),
					'singular_name'       => _x( 'TV Show', 'Post Type Singular Name', 'video-share-vod' ),
					'menu_name'           => __( 'TV Shows', 'video-share-vod' ),
					'parent_item_colon'   => __( 'Parent TV Show:', 'video-share-vod' ),
					'all_items'           => __( 'All TV Shows', 'video-share-vod' ),
					'view_item'           => __( 'View TV Show', 'video-share-vod' ),
					'add_new_item'        => __( 'Add New TV Show', 'video-share-vod' ),
					'add_new'             => __( 'New TV Show', 'video-share-vod' ),
					'edit_item'           => __( 'Edit TV Show', 'video-share-vod' ),
					'update_item'         => __( 'Update TV Show', 'video-share-vod' ),
					'search_items'        => __( 'Search TV Show', 'video-share-vod' ),
					'not_found'           => __( 'No TV Shows found', 'video-share-vod' ),
					'not_found_in_trash'  => __( 'No TV Shows found in Trash', 'video-share-vod' ),
				);

				$args = array(
					'label'               => __( 'TV show', 'video-share-vod' ),
					'description'         => __( 'TV Shows', 'video-share-vod' ),
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
					'menu_icon' => 'dashicons-format-video',
					'capability_type'     => 'post',
				);
				register_post_type( $options['tvshows_slug'], $args );

			}

			//extra rules
			add_rewrite_rule( 'crossdomain.xml$', 'index.php?vsv_crossdomain=1', 'top' );

			//without index.php use $1 instead of $matches[1]
			add_rewrite_rule( '^mbr\/([a-z\-]+)\/([0-9]+)\.([0-9a-z]+)$', 'wp-admin/admin-ajax.php?action=vwvs_mbr&protocol=$1&type=$3&id=$2', 'top');

		}

		function query_vars( $query_vars ){

			// array of recognized query vars
			$query_vars[] = 'vsv_crossdomain';
			/*
			$query_vars[] = 'protocol';
			$query_vars[] = 'id';
			$query_vars[] = 'type';
			$query_vars[] = 'action';
*/
			return $query_vars;
		}

		function parse_request( &$wp )
		{

			if ( array_key_exists( 'vsv_crossdomain', $wp->query_vars ) ) {
				$options = get_option('VWvideoShareOptions');
				echo html_entity_decode(stripslashes($options['crossdomain_xml']));
				exit();
			}
		}

		function cleanVideo($post_id, $clean = 'source', $confirm = 0, $options = null)
		{
			//cleans certain files for video (source, hls)

			if (!$options) $options = get_option('VWvideoShareOptions');
			if (get_post_type( $post_id ) != $options['custom_post']) return 0;

			switch ($clean)
			{
			case 'source':
				//delete source video
				$videoPath = get_post_meta($post_id, 'video-source-file', true);

				if (!file_exists($videoPath)) return 0;

				$space += filesize($videoPath);

				if ($confirm) unlink($videoPath);

				break;

			case 'hls':
				//delete all generated video files
				$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach ($videoAlts as $alt)
				{

					//clean segmentation
					if ($alt['hls']) if (strstr($alt['hls'], $options['uploadsPath']))
						{
							$space += VWvideoShare::sizeTree($alt['hls']);
							if ($confirm) if (is_dir($alt['hls'])) VWvideoShare::delTree($alt['hls']);
						}
				}


				break;

			case 'logs':
				//delete all generated video files
				$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach ($videoAlts as $alt)
				{

					$logpath = dirname($alt['file']);
					$log = $logpath . '/' . $post_id . '-' . $alt['id'] . '.txt';
					$logc = $logpath . '/' . $post_id . '-' . $alt['id'] . '-cmd.txt';
					$spaceStatistics[$alt['id'].'_logs'] = 0;
					if (file_exists($log)) $space += filesize($log);
					if (file_exists($logc)) $space += filesize($logc);

					if ($confirm) unlink($log);
					if ($confirm) unlink($logc);
				}


				break;
			}

			//recalculate video space
			VWvideoShare::spaceVideo($post_id);

			return $space;


		}


		function delTree($dir) {
			$files = array_diff(scandir($dir), array('.','..'));
			foreach ($files as $file) {
				(is_dir("$dir/$file")) ? VWvideoShare::delTree("$dir/$file") : unlink("$dir/$file");
			}
			return rmdir($dir);
		}


		function video_delete($video_id)
		{
			$options = get_option('VWvideoShareOptions');
			if (get_post_type( $video_id ) != $options['custom_post']) return;

			//delete source video
			$videoPath = get_post_meta($video_id, 'video-source-file', true);
			if (file_exists($videoPath)) unlink($videoPath);

			//delete all generated video files
			$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();



			foreach ($videoAlts as $alt)
			{
				if (file_exists($alt['file'])) unlink($alt['file']);

				//clean segmentation
				if ($alt['hls']) if (strstr($alt['hls'], $options['uploadsPath']))
					{
						if (file_exists($alt['hls']))
						{
							$files = glob($alt['hls'] . '/*'); // get all file names
							foreach($files as $file)
								{ // iterate files
								if(is_file($file))
									unlink($file); // delete file
							}
						}

						if (is_dir($alt['hls'])) VWvideoShare::delTree($alt['hls']);
						else unlink($alt['hls']);
					}
			}

		}

		//! Feature Pages and Menus
		function setupPages()
		{
			$options = get_option('VWvideoShareOptions');
			if ($options['disableSetupPages']) return;

			$pages = array(
				'videowhisper_videos' => 'Videos',
				'videowhisper_upload' => 'Upload',
				'videowhisper_recorder' => 'Record',
			);

			//create a menu and add pages
			$menu_name = 'VideoWhisper';
			$menu_exists = wp_get_nav_menu_object( $menu_name );
			if (!$menu_exists) $menu_id = wp_create_nav_menu($menu_name);

			//create pages if not created or existant
			foreach ($pages as $key => $value)
			{
				$pid = $options['p_'.$key];
				$page = get_post($pid);
				if (!$page) $pid = 0;

				if (!$pid)
				{
					global $user_ID;
					$page = array();
					$page['post_type']    = 'page';
					$page['post_content'] = '['.$key.']';
					$page['post_parent']  = 0;
					$page['post_author']  = $user_ID;
					$page['post_status']  = 'publish';
					$page['post_title']   = $value;
					$page['comment_status'] = 'closed';

					$pid = wp_insert_post ($page);

					$options['p_'.$key] = $pid;
					$link = get_permalink( $pid);

					if ($menu_id) wp_update_nav_menu_item($menu_id, 0, array(
								'menu-item-title' =>  $value,
								'menu-item-url' => $link,
								'menu-item-status' => 'publish'));

				}

			}

			update_option('VWvideoShareOptions', $options);
		}


		function admin_bar_menu($wp_admin_bar)
		{
			if (!is_user_logged_in()) return;

			$options = get_option('VWvideoShareOptions');


			if( current_user_can('editor') || current_user_can('administrator') ) {

				$menu_id = 'video-share-vod';

				$wp_admin_bar->add_node( array(
						'id'     => $menu_id,
						'title' => 'VideoShareVOD',
						'href'  => admin_url('admin.php?page=video-share')
					) );


					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-upload',
						'title' => __('Upload Videos', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share-upload')
					) );
					

					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-import',
						'title' => __('Import Videos', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share-import')
					) );
					

					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-manage',
						'title' => __('Manage Videos', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-manage')
					) );

					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-posts',
						'title' => __('Video Posts', 'video-share-vod'),
						'href'  => admin_url('edit.php?post_type=' . $options['custom_post'])
					) );
								
					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-conversions',
						'title' => __('Conversions', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share-conversion')
					) );

					$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-export',
						'title' => __('Export Videos', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share-export')
					) );
					

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-space',
						'title' => __('Statistics & Space', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-stats')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-settings',
						'title' => __('Settings', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-docs',
						'title' => __('Documentation', 'video-share-vod'),
						'href'  => admin_url('admin.php?page=video-share-docs')
					) );
			}

			$user_id = get_current_user_id();

			
			if (!$options['disableSetupPages'])
			{
			 if ($options['p_videowhisper_videos'])
			 if (VWvideoShare::hasPriviledge($options['watchList'] || current_user_can('editor') || current_user_can('administrator') )) 
					$wp_admin_bar->add_node(array(
							'parent' => 'my-account-with-avatar',
							'id'     => 'videowhisper_videos',
							'title' => __('Browse Videos', 'video-share-vod') ,
							'href'  =>  get_permalink($options['p_videowhisper_videos']),
						));
						
			 if ($options['p_videowhisper_upload'])
			 if (VWvideoShare::hasPriviledge($options['publishList'] || current_user_can('editor') || current_user_can('administrator') )) 
					$wp_admin_bar->add_node(array(
							'parent' => 'my-account-with-avatar',
							'id'     => 'videowhisper_upload',
							'title' => __('Upload Videos', 'video-share-vod') ,
							'href'  =>  get_permalink($options['p_videowhisper_upload']),
						));
			}

		}



		function admin_menu()
		{
			$options = get_option('VWvideoShareOptions');

			add_menu_page('Video Share VOD', 'Video Share VOD', 'manage_options', 'video-share', array('VWvideoShare', 'adminOptions'), 'dashicons-video-alt3',81);
			add_submenu_page("video-share", "Video Share VOD", "Options", 'manage_options', "video-share", array('VWvideoShare', 'adminOptions'));
			add_submenu_page("video-share", "Conversions", "Conversions", 'manage_options', "video-share-conversion", array('VWvideoShare', 'adminConversion'));

			add_submenu_page("video-share", "Upload", "Upload", 'manage_options', "video-share-upload", array('VWvideoShare', 'adminUpload'));
			add_submenu_page("video-share", "Import", "Import", 'manage_options', "video-share-import", array('VWvideoShare', 'adminImport'));
			add_submenu_page("video-share", "Export", "Export", 'manage_options', "video-share-export", array('VWvideoShare', 'adminExport'));


			if (class_exists("VWliveStreaming")) add_submenu_page('video-share', 'Live Streaming', 'Live Streaming', 'manage_options', 'video-share-ls', array('VWvideoShare', 'adminLiveStreaming'));
			add_submenu_page("video-share", "Manage Videos", "Manage Videos", 'manage_options', "video-manage", array('VWvideoShare', 'adminManage'));
			add_submenu_page("video-share", "Statistics & Space", "Statistics & Space", 'manage_options', "video-stats", array('VWvideoShare', 'adminStats'));
			add_submenu_page("video-share", "Documentation", "Documentation", 'manage_options', "video-share-docs", array('VWvideoShare', 'adminDocs'));

		}


		//! cron

		function cron_schedules( $schedules ) {
			$schedules['min4'] = array(
				'interval' => 240,
				'display' => __( 'Once every four minutes' )
			);
			return $schedules;
		}


		function setup_schedule() {
			if ( ! wp_next_scheduled( 'cron_4min_event') )
			{
				wp_schedule_event( time(), 'min4', 'cron_4min_event');
			}

		}





		function plugins_loaded()
		{
			$options = get_option('VWvideoShareOptions');

			//translations
			load_plugin_textdomain('video-share-vod', false, dirname(plugin_basename(__FILE__)) .'/languages');

			add_action( 'wp_enqueue_scripts', array('VWvideoShare','scripts') );

			//prevent wp from adding <p> that breaks JS
			remove_filter ('the_content',  'wpautop');

			//move wpautop filter to BEFORE shortcode is processed
			add_filter( 'the_content', 'wpautop' , 1);

			//then clean AFTER shortcode
			add_filter( 'the_content', 'shortcode_unautop', 100 );

			/* Fire our meta box setup function on the post editor screen. */
			add_action( 'load-post.php', array('VWvideoShare', 'post_meta_boxes_setup' ) );
			add_action( 'load-post-new.php', array( 'VWvideoShare', 'post_meta_boxes_setup' ) );

			//admin listings
			add_filter('pre_get_posts', array('VWvideoShare','pre_get_posts'));

			add_filter('manage_' .$options['custom_post']. '_posts_columns', array( 'VWvideoShare', 'columns_head_video') , 10);
			add_filter( 'manage_edit-' .$options['custom_post']. '_sortable_columns', array('VWvideoShare', 'columns_register_sortable') );
			add_filter( 'request', array('VWvideoShare', 'duration_column_orderby') );
			add_action('manage_' .$options['custom_post']. '_posts_custom_column', array( 'VWvideoShare', 'columns_content_video') , 10, 2);

			add_action('admin_head', array( 'VWvideoShare', 'admin_head') );


			add_filter( 'parse_query', array( 'VWvideoShare', 'parse_query') );

			add_action( 'before_delete_post',  array( 'VWvideoShare','video_delete') );

			//add_filter( 'category_description', 'category_description' );

			//video post page
			add_filter( "the_content", array('VWvideoShare','video_page'));
			// add_filter( "the_content", array('VWvideoShare','playlist_page'));

			if (class_exists("VWliveStreaming"))  if ($options['vwls_channel']) add_filter( "the_content", array('VWvideoShare','channel_page'));

				add_filter( "the_content", array('VWvideoShare','tvshow_page'));

			//! shortcodes
			add_shortcode('videowhisper_player', array( 'VWvideoShare', 'videowhisper_player'));
			add_shortcode('videowhisper_videos', array( 'VWvideoShare', 'videowhisper_videos'));
			add_shortcode('videowhisper_upload', array( 'VWvideoShare', 'shortcode_upload'));
			add_shortcode('videowhisper_preview', array( 'VWvideoShare', 'shortcode_preview'));
			add_shortcode('videowhisper_player_html', array( 'VWvideoShare', 'videowhisper_player_html'));
			add_shortcode('videowhisper_import', array( 'VWvideoShare', 'videowhisper_import'));
			add_shortcode('videowhisper_playlist', array( 'VWvideoShare', 'shortcode_playlist'));

			add_shortcode('videowhisper_embed_code', array( 'VWvideoShare', 'shortcode_embed_code'));

			add_shortcode('videowhisper_postvideos', array( 'VWvideoShare', 'videowhisper_postvideos'));
			add_shortcode('videowhisper_postvideos_process', array( 'VWvideoShare', 'videowhisper_postvideos_process'));

			add_shortcode('videowhisper_postvideo_assign', array( 'VWvideoShare', 'videowhisper_postvideo_assign'));

			add_shortcode('videowhisper_embed',  array( 'VWvideoShare', 'videowhisper_embed'));

			//! ajax
			//ajax videos
			add_action( 'wp_ajax_vwvs_videos', array('VWvideoShare','vwvs_videos'));
			add_action( 'wp_ajax_nopriv_vwvs_videos', array('VWvideoShare','vwvs_videos'));

			//ajax tools
			add_action( 'wp_ajax_vwvs_playlist_m3u', array('VWvideoShare','vwvs_playlist_m3u'));
			add_action( 'wp_ajax_nopriv_vwvs_playlist_m3u', array('VWvideoShare','vwvs_playlist_m3u'));

			add_action( 'wp_ajax_vwvs_embed', array('VWvideoShare','vwvs_embed'));
			add_action( 'wp_ajax_nopriv_vwvs_embed', array('VWvideoShare','vwvs_embed'));

			add_action( 'wp_ajax_vwvs_mbr', array('VWvideoShare','vwvs_mbr'));
			add_action( 'wp_ajax_nopriv_vwvs_mbr', array('VWvideoShare','vwvs_mbr'));


			add_filter('query_vars', array('VWvideoShare','query_vars'));


			//upload videos
			add_action( 'wp_ajax_vwvs_upload', array('VWvideoShare','vwvs_upload'));


			//disable X-Frame-Options: SAMEORIGIN
			if ($options['disableXOrigin'])
			{
				if (!$options['disableXOriginRef'] ||  substr($_SERVER["HTTP_REFERER"], 0, strlen($options['disableXOriginRef'])) === $options['disableXOriginRef'] )
					remove_action( 'admin_init', 'send_frame_options_header' );
			}

			//Live Streaming support
			if (class_exists("VWliveStreaming")) if ($options['vwls_playlist'])
				{
					add_filter('vw_ls_manage_channel', array('VWvideoShare', 'vw_ls_manage_channel' ), 10, 2);
					add_filter('vw_ls_manage_channels_head', array('VWvideoShare', 'vw_ls_manage_channels_head' ));
				}

			//BP
			if (function_exists('bp_is_active'))
				if ( bp_is_active( 'activity' ) )
				{
					bp_activity_set_post_type_tracking_args( $options['custom_post'], array(
							'component_id'             => 'activity',
							'action_id'                => 'new_blog_'. $options['custom_post'],
							'bp_activity_admin_filter' => __( 'Published a new ' . $options['custom_post'], 'buddyforms' ),
							'bp_activity_front_filter' => __( $options['custom_post'], 'buddyforms' ),
							'contexts'                 => array( 'activity', 'member' ),
							'activity_comment'         => true,
							'bp_activity_new_post'     => __( '%1$s posted a new <a href="%2$s">'. $options['custom_post'] .'</a>', 'buddyforms' ),
							'bp_activity_new_post_ms'  => __( '%1$s posted a new <a href="%2$s">'. $options['custom_post'] .'</a>, on the site %3$s', 'buddyforms' ),
							'position'                 => 100,
						) );

					add_post_type_support( $options['custom_post'], 'buddypress-activity' );

				}
			//check db and update if necessary
			/*
			$vw_db_version = "0.0";

			$installed_ver = get_option( "vwvs_db_version" );
			if( $installed_ver != $vw_db_version )
			{
				$tab_formats = $wpdb->prefix . "vwvs_formats";
				$tab_process = $wpdb->prefix . "vwvs_process";

				global $wpdb;
				$wpdb->flush();
				$sql = "";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
				if (!$installed_ver) add_option("vwvs_db_version", $vw_db_version);
				else update_option( "vwvs_db_version", $vw_db_version );
			}
			*/


		}


		function archive_template( $archive_template ) {
			global $post;

			$options = get_option('VWvideoShareOptions');

			if ( get_query_var( 'taxonomy' ) != $options['custom_taxonomy'] ) return $archive_template;

			if ($options['playlistTemplate'] == '+plugin')
			{
				$archive_template_new = dirname( __FILE__ ) . '/taxonomy-playlist.php';
				if (file_exists($archive_template_new)) return $archive_template_new;
			}

			$archive_template_new = get_template_directory() . '/' . $options['playlistTemplate'];
			if (file_exists($archive_template_new)) return $archive_template_new;
			else return $archive_template;
		}


		/*
		function category_description( $desc, $cat_id )
		{
			  $desc = 'Description: ' . $desc;
			  return $desc;
		}
*/

		/*
		function playlist_page($content)
		{
			if (!is_post_type_archive('playlist')) return $content;

			$addCode = 'Playlist [videowhisper_playlist videos=""]' . post_type_archive_title();

			return $addCode . $content;
		}
*/

		//! Widgets

		static function register_widgets()
		{


			$prefix = 'videowhisper-videos'; // $id prefix
			$name = __('VSV Videos');

			$widget_ops = array('classname' => 'widget_videowhisper_videos', 'description' => __('List videos and updates using AJAX.'));
			$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);


			$options = get_option('widget_videowhisper_videos');
			if(isset($options[0])) unset($options[0]);

			if(!empty($options)){
				foreach(array_keys($options) as $widget_number){
					wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, array( 'VWvideoShare','widget_videowhisper_videos'), $widget_ops, array( 'number' => $widget_number ));
					wp_register_widget_control($prefix.'-'.$widget_number, $name, array( 'VWvideoShare','widget_videowhisper_videos_control'), $control_ops, array( 'number' => $widget_number ));
				}
			} else{
				$options = array();
				$widget_number = 1;
				wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, array( 'VWvideoShare','widget_videowhisper_videos'), $widget_ops, array( 'number' => $widget_number ));
				wp_register_widget_control($prefix.'-'.$widget_number, $name, array( 'VWvideoShare','widget_videowhisper_videos_control'), $control_ops, array( 'number' => $widget_number ));
			}

			//! widgets
			// wp_register_sidebar_widget( 'videowhisper_videos', 'Videos',  array( 'VWvideoShare', 'widget_videos'), array('description' => 'List videos and updates using AJAX.') );
			// wp_register_widget_control( 'videowhisper_videos', 'videowhisper_videos', array( 'VWvideoShare', 'widget_videos_options') );


		}


		function widgetDefaultOptions()
		{
			return array(
				'title' => 'Videos',
				'perpage'=> '6',
				'perrow' => '6',
				'playlist' => '',
				'order_by' => '',
				'category_id' => '',
				'select_category' => '1',
				'select_order' => '1',
				'select_tags' => '1',
				'select_name' => '1',
				'select_page' => '1',
				'list_id' => '',
				'include_css' => '0'
			);


		}

		function widget_videowhisper_videos_control($args=array(), $params=array())
		{

			
			$optionsPlugin = get_option('VWvideoShareOptions');

			$prefix = 'videowhisper-videos'; // $id prefix

			$optionsAll = get_option('widget_videowhisper_videos');
			if(empty($optionsAll)) $optionsAll = array();
			if(isset($optionsAll[0])) unset($optionsAll[0]);

			// update options array
			if(!empty($_POST[$prefix]) && is_array($_POST)){
				foreach($_POST[$prefix] as $widget_number => $values){
					if(empty($values) && isset($optionsAll[$widget_number])) // user clicked cancel
						continue;

					if(!isset($optionsAll[$widget_number]) && $args['number'] == -1){
						$args['number'] = $widget_number;
						$optionsAll['last_number'] = $widget_number;
					}
					$optionsAll[$widget_number] = $values;
				}

				// update number
				if($args['number'] == -1 && !empty($optionsAll['last_number'])){
					$args['number'] = $optionsAll['last_number'];
				}
				
				if (!array_key_exists($args['number'] ,$optionsAll))  $optionsAll[$args['number']] = VWvideoShare::widgetDefaultOptions();

				// clear unused options and update options in DB. return actual options array
				$optionsAll = VWvideoShare::multiwidget_update($prefix, $optionsAll, $_POST[$prefix], $_POST['sidebar'], 'widget_videowhisper_videos');
			}

			// $number - is dynamic number for multi widget, gived by WP
			// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
			// to allow WP generate number automatically
			$number = ($args['number'] == -1)? '%i%' : $args['number'];

			//use if exists or defaults
			if (array_key_exists($number,$optionsAll)) $options = $optionsAll[$number];
			else $options = VWvideoShare::widgetDefaultOptions();
			
			//list_id used for JS
			$options['list_id'] = intval($options['list_id']);
			if (!$options['list_id']) $options['list_id'] = $number;
			if (!$options['list_id']) $options['list_id'] = random_int( 100, 999);

			/*
			$options = VWvideoShare::widgetSetupOptions();

			$options['list_id'] = intval($options['list_id']);
			if (!$options['list_id']) $options['list_id'] = random_int( 100, 999);

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWvideoShareWidgetOptions', $options);
			}
*/

?>

	<?php _e('Title','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][title]" value="<?php echo stripslashes($options['title']); ?>" />
	<br /><br />

	<?php _e('Playlist','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][playlist]" value="<?php echo stripslashes($options['playlist']); ?>" />
	<br /><br />

	<?php _e('Category ID','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][category_id]" value="<?php echo stripslashes($options['category_id']); ?>" />
	<br /><br />

 <?php _e('Order By','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][order_by]" id="order_by">
  <option value="post_date" <?php echo $options['order_by']=='post_date'?"selected":""?>><?php _e('Video Date','video-share-vod'); ?></option>
    <option value="video-views" <?php echo $options['order_by']=='video-views'?"selected":""?>><?php _e('Views','video-share-vod'); ?></option>
    <option value="video-lastview" <?php echo $options['order_by']=='video-lastview'?"selected":""?>><?php _e('Recently Watched','video-share-vod'); ?></option>
<?php 
				if ($optionsPlugin['rateStarReview'])
				{
					echo '<option value="rateStarReview_rating"' . ($options['order_by'] == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'video-share-vod') . '</option>';
					echo '<option value="rateStarReview_ratingNumber"' . ($options['order_by'] == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Most Rated', 'video-share-vod') . '</option>';
					echo '<option value="rateStarReview_ratingPoints"' . ($options['order_by'] == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'video-share-vod') . '</option>';
				}
 ?>     
    <option value="rand" <?php echo $options['order_by']=='rand'?"selected":""?>><?php _e('Random','video-share-vod'); ?></option>
</select><br /><br />

	<?php _e('Videos per Page','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][perpage]" value="<?php echo stripslashes($options['perpage']); ?>" />
	<br /><br />

	<?php _e('Videos per Row','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][perrow]" value="<?php echo stripslashes($options['perrow']); ?>" />
	<br /><br />

 <?php _e('Category Selector','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][select_category]" id="select_category">
  <option value="1" <?php echo $options['select_category']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_category']?"":"selected"?>>No</option>
</select><br /><br />

<?php _e('Tags Selector','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][select_tags]" id="select_order">
  <option value="1" <?php echo $options['select_tags']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_tags']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Name Selector','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][select_name]" id="select_name">
  <option value="1" <?php echo $options['select_name']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_name']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Order Selector','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][select_order]" id="select_order">
  <option value="1" <?php echo $options['select_order']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_order']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Page Selector','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][select_page]" id="select_page">
  <option value="1" <?php echo $options['select_page']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_page']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Unique List ID','video-share-vod'); ?>:<br />
	<input type="text" class="widefat" name="<?php echo $prefix; ?>[<?php echo $number; ?>][list_id]" id="list_id" value="<?php echo stripslashes($options['list_id']); ?>" />
	<br /><br />

	<?php _e('Include CSS','video-share-vod'); ?>:<br />
	<select name="<?php echo $prefix; ?>[<?php echo $number; ?>][include_css]" id="include_css">
  <option value="1" <?php echo $options['include_css']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['include_css']?"":"selected"?>>No</option>
</select><br /><br />
	<?php
		}

		function widget_videowhisper_videos($args=array(), $params=array())
		{

			extract($args);

			// get widget saved options
			$widget_number = (int) str_replace('videowhisper-videos-', '', @$widget_id);
			
			$optionsAll = get_option('widget_videowhisper_videos');
			if(!empty($optionsAll[$widget_number])){
				$options = $optionsAll[$widget_number];
			}

			//$options = get_option('VWvideoShareWidgetOptions');
			echo stripslashes($args['before_widget']);

			echo stripslashes($args['before_title']);
			echo stripslashes($options['title']);
			echo stripslashes($args['after_title']);

			echo do_shortcode('[videowhisper_videos playlist="' . $options['playlist'] . '" category_id="' . $options['category_id'] . '" order_by="' . $options['order_by'] . '" perpage="' . $options['perpage'] . '" perrow="' . $options['perrow'] . '" select_category="' . $options['select_category'] . '" select_tags="' . $options['select_tags']  . '" select_name="' . $options['select_name']  . '" select_order="' . $options['select_order'] . '" select_page="' . $options['select_page'] . '" include_css="' . $options['include_css'] . '" id="' . $options['list_id'] . ']');

			echo stripslashes($args['after_widget']);
		}


		static function multiwidget_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
			global $wp_registered_widgets;
			static $updated = false;

			// get active sidebar
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar =& $sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			// search unused options
			foreach ( $this_sidebar as $_widget_id ) {
				if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
					$widget_number = $match[1];

					// $_POST['widget-id'] contain current widgets set for current sidebar
					// $this_sidebar is not updated yet, so we can determine which was deleted
					if(!in_array($match[0], $_POST['widget-id'])){
						unset($options[$widget_number]);
					}
				}
			}

			// update database
			if(!empty($option_name)){
				update_option($option_name, $options);
				$updated = true;
			}

			// return updated array
			return $options;
		}

		//! Post Listings
		public static function pre_get_posts($query)
		{
			/*
			//add channels to post listings
			if(is_category() || is_tag() || is_archive())
			{

				if (is_admin()) return $query;

				$query_type = get_query_var('post_type');

				if ($query_type)
				{
					if (!is_array($query_type)) $query_type = array($query_type);

					if (is_array($query_type))
						if (in_array('post', $query_type) && !in_array( $options['custom_post'], $query_type))
							$query_type[] =  $options['custom_post'];

				}
				else  //default
					{
					$query_type = array('post',  $options['custom_post']);
				}

				$query->set('post_type', $query_type);
			}
*/
			return $query;
		}


		//! AJAX implementation

		function scripts()
		{
			wp_enqueue_script("jquery");
		}


		function vwvs_mbr()
		{

			//mbr/$protocol/$id.$type

			$video_id = intval($_GET['id']);
			$type = sanitize_file_name($_GET['type']);

			$protocol = sanitize_file_name($_GET['protocol']);
			if (!$protocol) $protocol = 'http';

			if (!$video_id)
			{
				echo 'Missing video id!';
				var_dump($_GET);
				exit;
			}

			$video = get_post($video_id);
			if (!$video)
			{
				echo 'Missing video!';
				exit;
			}

			$options = get_option('VWvideoShareOptions');

			$mbr = array();


			//retrieve mp4 variants (conversions)
			$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);


			$hasHigh = 0;
			if ($videoAdaptive) if (is_array($videoAdaptive))
					foreach ($videoAdaptive as $alt)
					{
						if ($alt['extension'] == 'mp4') $mbr[] = $alt;
						if ($alt['id'] == 'high') $hasHigh = 1;
					}

				//add original if mp4 and high format is not available
				if (!count($mbr) && $options['originalBackup'])
				{
					$videoPath = get_post_meta($video_id, 'video-source-file', true);
					$ext = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION));
					if (in_array($ext, array('mp4')))
					{
						$src['file'] = $videoPath;
						$src['bitrate'] = get_post_meta($video_id, 'video-bitrate', true);
						$src['width'] = get_post_meta($video_id, 'video-width', true);
						$src['height'] = get_post_meta($video_id, 'video-height', true);
						$src['extension'] = $ext;
						$src['type'] = 'video/mp4';
						$mbr[] = $src;
					}
				}

			//high bitrate first
			function cmpmbr($a, $b)
			{
				if ($a['bitrate'] == $b['bitrate']) {
					return 0;
				}
				return ($a['bitrate'] > $b['bitrate']) ? -1 : 1;
			}

			usort($mbr, 'cmpmbr');

			//var_dump($mbr);


			//var_dump($mbr);

			$videoDuration = get_post_meta($video_id, 'video-duration', true);

			$nl = "\r\n";

			switch ($type)
			{
			case 'f4m':
				$htmlCode = '<?xml version="1.0" encoding="utf-8"?>
<manifest xmlns="http://ns.adobe.com/f4m/1.0">
     <mimeType>video/mp4</mimeType>
     <duration>' . $videoDuration . '</duration>' . $nl;


				switch ($protocol)
				{
				case 'http':
					$htmlCode .='<id>Progressive Download</id>
     <streamType>recorded</streamType>
	 <deliveryType>progressive</deliveryType>' . $nl;
					foreach ($mbr as $alt)
						$htmlCode .= '<media url="' . VWvideoShare::path2url($alt['file']) . '" bitrate="' . $alt['bitrate'] . '" width="' . $alt['width'] . '" height="' . $alt['height'] . '" />' . $nl;
					break;

				case 'rtmp':
					$htmlCode .='<id>Dynamic Streaming</id>
	<baseURL>' . $options['rtmpServer'] . '</baseURL>' . $nl;
					foreach ($mbr as $alt)
						$htmlCode .= '<media url="mp4:' . VWvideoShare::path2stream($alt['file']) . '" bitrate="' . $alt['bitrate'] . '" width="' . $alt['width'] . '" height="' . $alt['height'] . '" />
';
					break;

				}


				$htmlCode .= '</manifest>';
				break;

			case 'm3u8':
				switch ($protocol)
				{
				case 'hls':
					$htmlCode .='#EXTM3U
';
					foreach ($mbr as $alt)
					{
						$codecsCode ='';
						//$codecsCode = ',CODECS="avc1.42e00a,mp4a.40.2"';
						
						$htmlCode .= '#EXT-X-STREAM-INF:PROGRAM-ID=1,BANDWIDTH=' . $alt['bitrate'] .'000,RESOLUTION='.$alt['width'].'x'.$alt['height']. $codecsCode . $nl;

						$indexULR = '';
						if ($alt['hls'])
						{
							//static HLS conversion available
							$indexULR = VWvideoShare::path2url($alt['hls']) . '/index.m3u8';
						}
						elseif ($options['hlsServer'])
						{
							//use HLS server
							$stream = VWvideoShare::path2stream($alt['file']);
							$stream = 'mp4:' . $stream;
							$indexULR = $options['hlsServer'] . '_definst_/' . $stream . '/playlist.m3u8';
						}

						if ($indexULR) $htmlCode .= $indexULR . $nl;

					}
					break;
				}
				break;


			}

			ob_clean();
			echo $htmlCode;
			die;
		}



		function vwvs_embed()
		{

			header( "Content-Type: application/javascript" );

			$playlist = sanitize_file_name($_GET['playlist']);


			if ($playlist)
			{
				$htmlCode = VWvideoShare::shortcode_playlist(array('name'=> $playlist, 'embed'=>0));
				$htmlCode = preg_replace("/\r?\n/", "\\n", addslashes($htmlCode));
			}

			ob_clean();
			if ($htmlCode) echo 'document.write("'. $htmlCode . '");';
			die;


		}

		function vwvs_playlist_m3u()
		{
			$options = get_option('VWvideoShareOptions');

			$playlist = sanitize_file_name($_GET['playlist']);

			$listCode = '#EXTM3U';


			if ($playlist)
			{
				$args=array(
					'post_type' =>  $options['custom_post'],
					'post_status' => 'publish',
					'posts_per_page' => 100,
					'order'            => 'DESC',
					'orderby' => 'post_date',
					$options['custom_taxonomy'] => $playlist
				);

				$postslist = get_posts( $args );

				if (count($postslist)>0)
					foreach ($postslist as $item)
					{
						$listCode .= "\r\n" . VWvideoShare::path2url(VWvideoShare::videoPath($item->ID));
					}
			}

			ob_clean();
			echo $listCode;
			die;

		}


		//! Videos AJAX handler

		function vwvs_videos()
		{
			$options = get_option('VWvideoShareOptions');

			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$playlist = sanitize_file_name($_GET['playlist']);

			$id = sanitize_file_name($_GET['id']);

			$category = (int) $_GET['cat'];

			$page = (int) $_GET['p'];
			$offset = $page * $perPage;

			$perRow = (int) $_GET['pr'];
			if (!$perRow) $perRow = $options['perRow'];

			//order
			$order_by = sanitize_file_name($_GET['ob']);
			if (!$order_by) $order_by = $options['order_by'];
			if (!$order_by) $order_by = 'post_date';

			//options
			$selectCategory = (int) $_GET['sc'];
			$selectOrder = (int) $_GET['so'];
			$selectPage = (int) $_GET['sp'];

			$selectName = (int) $_GET['sn'];
			$selectTags = (int) $_GET['sg'];

			//tags,name search
			$tags = sanitize_text_field($_GET['tags']);
			$name = sanitize_file_name($_GET['name']);
			if ($name == 'undefined') $name = '';
			if ($tags == 'undefined') $tags = '';

			//query
			$args=array(
				'post_type' =>  $options['custom_post'],
				'post_status' => 'publish',
				'posts_per_page' => $perPage,
				'offset'           => $offset,
				'order'            => 'DESC',
			);

			switch ($order_by)
			{
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

			if ($playlist)  $args['playlist'] = $playlist;
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


			//user permissions
			if (is_user_logged_in())
			{
				$current_user = wp_get_current_user();
				if (in_array('administrator', $current_user->roles)) $isAdministrator=1;
				$isID = $current_user->ID;

				if (is_plugin_active('paid-membership/paid-membership.php')) $pmEnabled =1;
			}

			$isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );

			$isSafari = (bool) (strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') && strpos($_SERVER['HTTP_USER_AGENT'], 'Safari'));
			if ($isSafari) $previewMuted = 'muted'; else $previewMuted ='';

			$postslist = get_posts( $args );

			ob_clean();
			//output

			//var_dump ($args);
			//echo $order_by;
			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwvs_videos&pp=' . $perPage .  '&pr=' .$perRow. '&playlist=' . urlencode($playlist) . '&sc=' . $selectCategory . '&sn=' . $selectName .  '&sg=' . $selectTags . '&so=' . $selectOrder . '&sp=' . $selectPage .  '&id=' . $id ;

			//reset on change, selections persist
			
			$ajaxurlC = $ajaxurl . '&cat=' . $category . '&tags=' . urlencode($tags) . '&name=' . urlencode($name) ; //select order
			$ajaxurlO = $ajaxurl . '&ob='. $order_by . '&tags=' . urlencode($tags) . '&name=' . urlencode($name) ; //select cat
			$ajaxurlCO = $ajaxurl . '&cat=' . $category . '&ob='.$order_by ; //select name tag

			$ajaxurlA = $ajaxurl . '&cat=' . $category . '&ob='.$order_by . '&tags=' . urlencode($tags) . '&name=' . urlencode($name); //all persist: reload/page

			//options

			// echo '<div class="videowhisperListOptions">';
			//$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' form"><div class="inline fields">';
			$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' small equal width form" style="z-index: 20;"><div class="inline fields">';

			if ($selectCategory)
			{
				$htmlCode .= '<div class="field">' . wp_dropdown_categories('show_count=0&echo=0&name=category' . $id . '&hide_empty=1&class=ui+dropdown&show_option_all=' . __('All', 'video-share-vod') . '&selected=' . $category).'</div>';
				$htmlCode .= '<script>var category' . $id . ' = document.getElementById("category' . $id . '"); 			category' . $id . '.onchange = function(){aurl' . $id . '=\'' . $ajaxurlO .'&cat=\'+ this.value; loadVideos' . $id . '(\'<div class="ui active inline text large loader">Loading category...</div>\')}
			</script>';
			}

			if ($selectOrder)
			{
				$htmlCode .= '<div class="field"><select class="ui dropdown" id="order_by' . $id . '" name="order_by' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlC .'&ob=\'+ this.value; loadVideos' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Ordering videos...</div>\')">';
				$htmlCode .= '<option value="">' . __('Order By', 'video-share-vod') . ':</option>';
				$htmlCode .= '<option value="post_date"' . ($order_by == 'post_date'?' selected':'') . '>' . __('Video Date', 'video-share-vod') . '</option>';
				$htmlCode .= '<option value="video-views"' . ($order_by == 'video-views'?' selected':'') . '>' . __('Views', 'video-share-vod') . '</option>';
				$htmlCode .= '<option value="video-lastview"' . ($order_by == 'video-lastview'?' selected':'') . '>' . __('Watched Recently', 'video-share-vod') . '</option>';
				
				if ($options['rateStarReview'])
				{
					$htmlCode .= '<option value="rateStarReview_rating"' . ($order_by == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'video-share-vod') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingNumber"' . ($order_by == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Most Rated', 'video-share-vod') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingPoints"' . ($order_by == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'video-share-vod') . '</option>';
				}
				
				$htmlCode .= '<option value="rand"' . ($order_by == 'rand'?' selected':'') . '>' . __('Random', 'video-share-vod') . '</option>';
				$htmlCode .= '</select></div>';
			}


			if ($selectTags || $selectName)
			{

					$htmlCode .= '<div class="field"></div>'; //separator

				if ($selectTags)
				{
					$htmlCode .= '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __('Tags', 'video-share-vod')  . '" value="' .htmlspecialchars($tags). '">
					</div></div>';
				}

				if ($selectName)
				{
					$htmlCode .= '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __('Name', 'video-share-vod')  . '" value="' .htmlspecialchars($name). '">
  <div class="ui left corner label">
    <i class="asterisk icon"></i>
  </div>
					</div></div>';
				}

				//search button
				$htmlCode .= '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui icon button" type="submit" name="submit" id="submit" value="' . __('Search', 'video-share-vod') . '" onclick="aurl' . $id . '=\'' . $ajaxurlCO .'&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadVideos' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching Videos...</div>\')"><i class="search icon"></i></button></div>';

			}
			
			//reload button
			if ($selectCategory || $selectOrder || $selectTags || $selectName)	$htmlCode .= '<div class="field"></div> <div class="field" data-tooltip="Reload"><button class="ui icon button" type="submit" name="reload" id="reload" value="' . __('Reload', 'picture-gallery') . '" onclick="aurl' . $id . '=\'' . $ajaxurlA .'\'; loadVideos' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Reloading Videos List...</div>\')"><i class="sync icon"></i></button></div>';
			

			//echo '</div>';
			$htmlCode .= '</div></div>';


			//list
			if (count($postslist)>0)
			{
				$htmlCode .= '<div class="videowhisperVideos">';
				$k = 0;

				foreach ( $postslist as $item )
				{
					if ($perRow) if ($k) if ($k % $perRow == 0) $htmlCode .= '<br>';

							$videoDuration = get_post_meta($item->ID, 'video-duration', true);
						$imagePath = get_post_meta($item->ID, 'video-thumbnail', true);

					$views = get_post_meta($item->ID, 'video-views', true) ;
					if (!$views) $views = 0;

					//get preview video
					$previewVideo = '';
					$videoAdaptive = get_post_meta($item->ID, 'video-adaptive', true);
					
					if (is_array($videoAdaptive))
					if (array_key_exists('preview', $videoAdaptive))
					if ($videoAdaptive['preview'])
						if ($videoAdaptive['preview']['file'])
							if (file_exists($videoAdaptive['preview']['file']))
								$previewVideo = $videoAdaptive['preview']['file'];
							else;
							else;
							else if ($options['convertPreview']) VWvideoShare::convertVideo($item->ID); //add preview if enabled and missing (older)

								$duration = VWvideoShare::humanDuration($videoDuration);
							$age = VWvideoShare::humanAge(time() - strtotime($item->post_date));


						$height = get_post_meta($item->ID, 'video-height', true) ;

					$canEdit = 0;
					if ($options['editContent'])
						if ($isAdministrator || $item->post_author == $isID) $canEdit = 1;


						$info = '' . __('Title', 'video-share-vod') . ': ' . $item->post_title . "\r\n" . __('Duration', 'video-share-vod') . ': ' . $duration . "\r\n" . __('Added', 'video-share-vod') . ': ' . $age . "\r\n" . __('Views', 'video-share-vod') . ": " . $views;
					$views .= ' ' . __('views', 'video-share-vod');

					$htmlCode .= '<div class="videowhisperVideo">';
					$htmlCode .= '<a href="' . get_permalink($item->ID) . '" title="' . $info . '"><div class="videowhisperVideoTitle">' . $item->post_title. '</div></a>';
					$htmlCode .= '<div class="videowhisperVideoDuration">' . $duration . '</div>';
					$htmlCode .= '<div class="videowhisperVideoDate">' . $age . '</div>';
					$htmlCode .= '<div class="videowhisperVideoViews">' . $views . '</div>';
					$htmlCode .= '<div class="videowhisperVideoResolution">' . $height . 'p</div>';

					$ratingCode = '';
					if ($options['rateStarReview'])
					{
						$rating = get_post_meta($item->ID, 'rateStarReview_rating', true);
						$max = 5;
						if ($rating > 0) $ratingCode = '<div class="ui star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
						$htmlCode .= '<div class="videowhisperVideoRating">' . $ratingCode . '</div>';
					}


					if ($pmEnabled && $canEdit) $htmlCode .= '<a style="z-index:10" href="'.$options['editURL']. $item->ID .'"><span class="videowhisperVideoEdit">EDIT</span></a>';


					if (!$imagePath || !file_exists($imagePath)) //video thumbnail?
						{
						$imagePath = plugin_dir_path( __FILE__ ) . 'no_video.png';
						VWvideoShare::updatePostThumbnail($item->ID);
					}
					else //what about featured image?
						{
						$post_thumbnail_id = get_post_thumbnail_id($item->ID);
						if ($post_thumbnail_id) $post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview') ;

						if (!$post_featured_image) VWvideoShare::updatePostThumbnail($item->ID);
					}

					$thumbURL = VWvideoShare::path2url($imagePath);
					$previewCode = '<IMG src="' . $thumbURL . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px" ALT="' . $info . '">';


					if ($previewVideo && !$isMobile)
					{
						$previewCode = '<video class="videowhisperPreviewVideo" ' .$previewMuted. ' poster="' . $thumbURL . '" preload="none" width="' . $options['thumbWidth'] . '" height="' . $options['thumbHeight'] . '"><source src="' . VWvideoShare::path2url($previewVideo) . '" type="video/mp4">'.$previewCode.'</video>';

					}

					$previewCode =  '<a href="' . get_permalink($item->ID) . '" title="' . $info . '">'.$previewCode.'</a>';



					//<div class="videowhisperPreview" style="background-image: url(\'' . $thumbURL . '\'); width: ' . $options['thumbWidth'] . 'px; height: ' . $options['thumbHeight'] . 'px; padding: 0px; margin: 0px; overflow: hidden; display: block;"> </div>

					$htmlCode .= $previewCode;
					$htmlCode .= '</div>
					';

					$k++;
				}

				$htmlCode .= '</div>';

			} else $htmlCode .= __("No videos.",'video-share-vod');


			if (!$isMobile)
			{
				$htmlCode .= '
<SCRIPT language="JavaScript">

var $jQnC1 = jQuery.noConflict();
$jQnC1(document).ready(function()
{

var hHandlers = $jQnC1(".videowhisperVideo").hover( hoverVideoWhisper, outVideoWhisper );

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
			//pagination
			if ($selectPage)
			{
				$htmlCode .= "<BR>";
				$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' form"><div class="inline fields">';

				if ($page>0) $htmlCode .= ' <a class="ui labeled icon button black" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA .'&p='.($page-1). '\'; loadVideos' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading previous page...</div>\');"><i class="left arrow icon"></i> ' . __('Previous', 'video-share-vod') . '</a> ';

				$htmlCode .= '<a class="ui button black" href="#"> ' . __('Page', 'video-share-vod') . ' ' . ($page+1) . ' </a>' ;

				if (count($postslist) >= $perPage) $htmlCode .= ' <a class="ui right labeled icon button black" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA . '&p='.($page+1). '\'; loadVideos' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading next page...</div>\');">' . __('Next', 'video-share-vod') . ' <i class="right arrow icon"></i></a> ';

				echo '</div></div>';

			}
			
			echo $htmlCode;

			//output end
			die;

		}


		static function enqueueUI()
		{
			wp_enqueue_script("jquery");

			wp_enqueue_style( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.css');
			wp_enqueue_script( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.js', array('jquery'));
		}


		// !Shortcodes

		function videowhisper_videos($atts)
		{

			$options = get_option('VWvideoShareOptions');

			$atts = shortcode_atts(
				array(
					'perpage'=> $options['perPage'],
					'perrow' => '',
					'playlist' => '',
					'order_by' => '',
					'category_id' => '',
					'select_category' => '1',
					'select_tags' => '1',
					'select_name' => '1',
					'select_order' => '1',
					'select_page' => '1',
					'include_css' => '1',
					'tags' => '',
					'name' => '',
					'id' => ''
				),
				$atts, 'videowhisper_videos');


			$id = $atts['id'];
			if (!$id) $id = uniqid();

			VWvideoShare::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwvs_videos&pp=' . $atts['perpage'] . '&pr=' . $atts['perrow'] . '&playlist=' . urlencode($atts['playlist']) . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&sc=' . $atts['select_category'] . '&sn=' . $atts['select_name'] .  '&sg=' . $atts['select_tags'] . '&so=' . $atts['select_order'] . '&sp=' . $atts['select_page']. '&id=' .$id . '&tags=' . urlencode($atts['tags']) . '&name=' . urlencode($atts['name']);

			$htmlCode = <<<HTMLCODE
<script type="text/javascript">
var aurl$id = '$ajaxurl';
var \$j = jQuery.noConflict();
var loader$id;

	function loadVideos$id(message){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperVideos$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: aurl$id,
			success: function(data) {
				\$j("#videowhisperVideos$id").html(data);
				jQuery(".ui.dropdown").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");
			}
		});
	}


	jQuery(document).ready(function(){
			loadVideos$id();
			setInterval("loadVideos$id('')", 60000);
	});

</script>

<div id="videowhisperVideos$id">
	<div class="ui active inline text large loader">Loading videos...</div>
</div>

HTMLCODE;
			$htmlCode .= self::poweredBy();

			if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		function videowhisper_import($atts)
		{

			if (!is_user_logged_in())
			{
				return __('Login is required to import videos!', 'video-share-vod');

			}
			
			$options = get_option( 'VWvideoShareOptions' );

			$current_user = wp_get_current_user();
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
			$username = $current_user->$userName;
				
			if (!VWvideoShare::hasPriviledge($options['shareList'])) return __('You do not have permissions to share videos!', 'video-share-vod');

			$atts = shortcode_atts(array('category' => '', 'playlist' => '', 'owner' => '', 'path' => '', 'prefix' => '', 'tag' => '', 'description' => ''), $atts, 'videowhisper_import');

			if (!$atts['path']) return 'videowhisper_import: Path required!';

			if (!file_exists($atts['path'])) return 'videowhisper_import: Path not found!';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<label for="category">' . __('Category', 'video-share-vod') . ': </label><div class="videowhisperDropdown">' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=videowhisperSelect').'</div>';

			if ($atts['playlist']) $playlists = '<br><label for="playlist">' . __('Playlist', 'video-share-vod') . ': </label>' .$atts['playlist'] . '<input type="hidden" name="playlist" id="playlist" value="'.$atts['playlist'].'"/>';
			elseif ( current_user_can('edit_posts') ) $playlists = '<br><label for="playlist">Playlist(s): </label> <br> <input size="48" maxlength="64" type="text" name="playlist" id="playlist" value="' . $username .'"/> ' . __('(comma separated)', 'video-share-vod');
			else $playlists = '<br><label for="playlist">' . __('Playlist', 'video-share-vod') . ': </label> ' . $username .' <input type="hidden" name="playlist" id="playlist" value="' . $username .'"/> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else
				$owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<br><label for="playlist">' . __('Tags', 'video-share-vod') . ': </label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/>';
				else $tags = '<br><label for="tag">' . __('Tag(s)', 'video-share-vod') . ': </label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value=""/> (comma separated)';

				if ($atts['description'] != '_none' )
					if ($atts['description']) $descriptions = '<br><label for="description">' . __('Description', 'video-share-vod') . ': </label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/>';
					else $descriptions = '<br><label for="description">' . __('Description', 'video-share-vod') . ': </label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value=""/>';


					$url  =  get_permalink();

				$htmlCode .= '<h3>' . __('Import Videos', 'video-share-vod') . '</h3>' . $atts['path'] . $atts['prefix'];

			$htmlCode .=  '<form action="' . $url . '" method="post">';

			$htmlCode .= $categories;
			$htmlCode .= $playlists;
			$htmlCode .= $tags;
			$htmlCode .= $descriptions;
			$htmlCode .= $owners;

			$htmlCode .= '<br>' . VWvideoShare::importFilesSelect( $atts['prefix'], VWvideoShare::extensions_video(), $atts['path']);

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';

			$htmlCode .= ' <INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

			$htmlCode .= '</form>';

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		function shortcode_upload($atts)
		{

			$options = get_option( 'VWvideoShareOptions' );

			if (!is_user_logged_in())
			{
				return '<div class="ui ' . $options['interfaceClass'] .' segment orange">'. __('Login is required to upload videos!', 'video-share-vod') . '</div>';
			}


			$current_user = wp_get_current_user();
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
			$username = $current_user->$userName;
			
			if (!VWvideoShare::hasPriviledge($options['shareList'])) return __('You do not have permissions to share videos!', 'video-share-vod');


			$atts = shortcode_atts(array('category' => '', 'playlist' => '', 'owner' => '', 'tag' => '', 'description' => ''), $atts, 'videowhisper_upload');


			VWvideoShare::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwvs_upload';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<div class="field"><label for="category">' . __('Category', 'video-share-vod') . ' </label>' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown').'</div>';

			if ($atts['playlist']) $playlists = '<div class="field"><label for="playlist">' . __('Playlist', 'video-share-vod') . ' </label>' .$atts['playlist'] . '<input type="hidden" name="playlist" id="playlist" value="'.$atts['playlist'].'"/></div>';
			elseif ( current_user_can('edit_users') ) $playlists = '<div class="field"><label for="playlist">' . __('Playlist(s)', 'video-share-vod') . ': </label> <input size="48" maxlength="64" type="text" name="playlist" id="playlist" value="' . $username .'" class="text-input" placehoder="(comma separated)"/> ';
			else $playlists = '<div class="field"><label for="playlist">' . __('Playlist', 'video-share-vod') . ' </label> ' . $username .' <input type="hidden" name="playlist" id="playlist" value="' . $username .'"/></div> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else $owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<div class="field"><label for="playlist">' . __('Tags', 'video-share-vod') . ' </label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/></div>';
				else $tags = '<div class="field"><label for="tag">' . __('Tag(s)', 'video-share-vod') . ' </label><input size="48" maxlength="64" type="text" name="tag" id="tag" value="" class="text-input" placeholder="comma separated tags, for all videos that will be uploaded"/></div>';

				if ($atts['description'] != '_none' )
					if ($atts['description']) $descriptions = '<div class="field"><label for="description">' . __('Description', 'video-share-vod') . ' </label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/></div>';
					else $descriptions = '<div class="field"><label for="description">' . __('Description', 'video-share-vod') . ' </label><textarea rows="2" name="description" id="description" class="text-input" placeholder="description, for all videos that will be uploaded"/></textarea></div>';



					$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
				$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
			$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
			$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

			if ($iPhone || $iPad || $iPod || $Android) $mobile = true; else $mobile = false;

			if ($mobile)
			{
				$mobiles = 'capture="camcorder"';
				$accepts = 'accept="video/*;capture=camcorder"';
				$multiples = '';
				$filedrags = '';
			}
			else
			{
				$mobiles = '';
				$accepts = 'accept="video/mp4,video/x-m4v,video/*"';
				$multiples = 'multiple="multiple"';
				$filedrags = '<div id="filedrag">' . __('or Drag & Drop files to this upload area<br>(fill rest of form options first to apply for all uploads)', 'video-share-vod') . '</div>';
			}

			wp_enqueue_script( 'vwvs-upload', plugin_dir_url(  __FILE__ ) . 'upload.js');

			$submits = '<div id="submitbutton">
	<button class="ui button" type="submit" name="upload" id="upload">' . __('Upload Files', 'video-share-vod') . '</button>';


$interfaceClass = $options['interfaceClass'];

			$htmlCode .= <<<EOHTML
<div class="ui $interfaceClass form">
<form id="upload" action="$ajaxurl" method="POST" enctype="multipart/form-data">

<fieldset>
$categories
$playlists
$tags
$descriptions
$owners
<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="9000000000" />
EOHTML;

			$htmlCode .= '<legend><h3>' . __('Video Upload', 'video-share-vod') . '</h3></legend><div> <label for="fileselect">' . __('Videos to Upload', 'video-share-vod') . ' </label>';

			$htmlCode .= <<<EOHTML
	<br><input class="ui button" type="file" id="fileselect" name="fileselect[]" $mobiles $multiples $accepts />
$filedrags
$submits
</div>
EOHTML;

			$htmlCode .= <<<EOHTML
<div id="progress"></div>

</fieldset>
</form>
</div>

<script>
jQuery(document).ready(function(){
jQuery(".ui.dropdown").dropdown();
});
</script>

<STYLE>

#filedrag
{
 height: 100px;
 border: 1px solid #AAA;
 border-radius: 9px;
 color: #333;
 background: #eee;
 padding: 5px;
 margin-top: 5px;
 text-align:center;
}

#progress
{
padding: 4px;
margin: 4px;
}

#progress div {
	position: relative;
	background: #555;
	-moz-border-radius: 9px;
	-webkit-border-radius: 9px;
	border-radius: 9px;

	padding: 4px;
	margin: 4px;

	color: #DDD;

}

#progress div > span {
	display: block;
	height: 20px;

	   -webkit-border-top-right-radius: 4px;
	-webkit-border-bottom-right-radius: 4px;
	       -moz-border-radius-topright: 4px;
	    -moz-border-radius-bottomright: 4px;
	           border-top-right-radius: 4px;
	        border-bottom-right-radius: 4px;
	    -webkit-border-top-left-radius: 4px;
	 -webkit-border-bottom-left-radius: 4px;
	        -moz-border-radius-topleft: 4px;
	     -moz-border-radius-bottomleft: 4px;
	            border-top-left-radius: 4px;
	         border-bottom-left-radius: 4px;

	background-color: rgb(43,194,83);

	background-image:
	   -webkit-gradient(linear, 0 0, 100% 100%,
	      color-stop(.25, rgba(255, 255, 255, .2)),
	      color-stop(.25, transparent), color-stop(.5, transparent),
	      color-stop(.5, rgba(255, 255, 255, .2)),
	      color-stop(.75, rgba(255, 255, 255, .2)),
	      color-stop(.75, transparent), to(transparent)
	   );

	background-image:
		-webkit-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-moz-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-ms-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	background-image:
		-o-linear-gradient(
		  -45deg,
	      rgba(255, 255, 255, .2) 25%,
	      transparent 25%,
	      transparent 50%,
	      rgba(255, 255, 255, .2) 50%,
	      rgba(255, 255, 255, .2) 75%,
	      transparent 75%,
	      transparent
	   );

	position: relative;
	overflow: hidden;
}

#progress div.success
{
    color: #DDD;
	background: #3C6243 none 0 0 no-repeat;
}

#progress div.failed
{
 	color: #DDD;
	background: #682C38 none 0 0 no-repeat;
}
</STYLE>
EOHTML;

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;

		}

		function vwvs_upload()
		{

			global $current_user;
			get_currentuserinfo();

			if (!is_user_logged_in())
			{
				echo 'Login required!';
				exit;
			}

			$owner = $_SERVER['HTTP_X_OWNER'] ? intval($_SERVER['HTTP_X_OWNER']) : intval($_POST['owner']);

			if ($owner && ! current_user_can('edit_users') && $owner != $current_user->ID )
			{
				echo 'Only admin can upload for others!';
				exit;
			}
			if (!$owner) $owner = $current_user->ID;


			$playlist = $_SERVER['HTTP_X_PLAYLIST'] ? $_SERVER['HTTP_X_PLAYLIST'] :$_POST['playlist'];

			//if csv sanitize as array
			if (strpos($playlist, ',') !== FALSE)
			{
				$playlists = explode(',', $playlist);
				foreach ($playlists as $key => $value) $playlists[$key] = sanitize_file_name(trim($value));
				$playlist = $playlists;
			}

			if (!$playlist)
			{
				echo 'Playlist required!';
				exit;
			}

			$category = $_SERVER['HTTP_X_CATEGORY'] ? sanitize_file_name($_SERVER['HTTP_X_CATEGORY']) : sanitize_file_name($_POST['category']);


			$tag = $_SERVER['HTTP_X_TAG'] ? $_SERVER['HTTP_X_TAG'] :$_POST['tag'];

			//if csv sanitize as array
			if (strpos($tag, ',') !== FALSE)
			{
				$tags = explode(',', $tag);
				foreach ($tags as $key => $value) $tags[$key] = sanitize_file_name(trim($value));
				$tag = $tags;
			} else $tag = sanitize_file_name(trim($tag));


			$description = wp_encode_emoji(sanitize_textarea_field( $_SERVER['HTTP_X_DESCRIPTION'] ? $_SERVER['HTTP_X_DESCRIPTION'] :$_POST['description'] ));

			$options = get_option( 'VWvideoShareOptions' );

			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/uploads';
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/';


			ob_clean();
			$fn = (isset($_SERVER['HTTP_X_FILENAME']) ? $_SERVER['HTTP_X_FILENAME'] : false);

			function generateName($fn)
			{
				$ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));

				if (!in_array($ext, VWvideoShare::extensions_video() ))
				{
					echo 'Extension not allowed!';
					exit;
				}

				//unpredictable name
				return md5(uniqid($fn, true))  . '.' . $ext;
			}

			$path = '';

			if ($fn)
			{
				// AJAX call
				file_put_contents($path = $dir . generateName($fn), file_get_contents('php://input') );
				$el = array_shift(explode(".", $fn));
				$title = ucwords(str_replace('-', ' ', sanitize_file_name($el) ));

				echo VWvideoShare::importFile($path, $title, $owner, $playlist, $category, $tag, $description);

				//echo "Video was uploaded.";
			}
			else
			{
				// form submit
				$files = $_FILES['fileselect'];

				/*
			//get info from POST:
			$category =  sanitize_file_name($_POST['category']);
			$tag = $_POST['tag'];
			$description = sanitize_text_field( $_POST['description'] );

			//if csv sanitize as array
			if (strpos($tag, ',') !== FALSE)
			{
				$tags = explode(',', $tag);
				foreach ($tags as $key => $value) $tags[$key] = sanitize_file_name(trim($value));
				$tag = $tags;
			} else $tag = sanitize_file_name(trim($tag));
*/



				if ($files['error']) if (is_array($files['error']))
						foreach ($files['error'] as $id => $err)
						{
							if ($err == UPLOAD_ERR_OK) {
								$fn = $files['name'][$id];
								move_uploaded_file( $files['tmp_name'][$id], $path = $dir . generateName($fn) );
								$title = ucwords(str_replace('-', ' ', sanitize_file_name(array_shift(explode(".", $fn)))));

								echo VWvideoShare::importFile($path, $title, $owner, $playlist, $category, $tag, $description) . '<br>';

								echo "Video was uploaded using fallback method as HTML5 drag & drop uploader JavaScript did not load/work.";
							}
						}

			}


			die;
		}

		function shortcode_preview($atts)
		{
			$atts = shortcode_atts(array('video' => '0', 'type'=>'auto'), $atts, 'shortcode_preview');

			$video_id = intval($atts['video']);
			if (!$video_id) return 'shortcode_preview: Missing video id!';

			$video = get_post($video_id);
			if (!$video) return 'shortcode_preview: Video #'. $video_id . ' not found!';

			$options = get_option( 'VWvideoShareOptions' );

			//res
			$vWidth = get_post_meta($video_id, 'video-width', true);
			$vHeight = get_post_meta($video_id, 'video-height', true);
			if (!$vWidth) $vWidth = $options['thumbWidth'];
			if (!$vHeight) $vHeight = $options['thumbHeight'];

			//snap
			$imagePath = get_post_meta($video_id, 'video-snapshot', true);
			if ($imagePath)
				if (file_exists($imagePath))
					$imageURL = VWvideoShare::path2url($imagePath);
				else VWvideoShare::updatePostThumbnail($update_id);

				if (!$imagePath) $imageURL = VWvideoShare::path2url(plugin_dir_path( __FILE__ ) . 'no_video.png');
				$video_url = get_permalink($video_id);
			$htmlCode = "<a href='$video_url'><IMG SRC='$imageURL' width='$vWidth' height='$vHeight'></a>";

			return $htmlCode;
		}

		function shortcode_playlist($atts)
		{
			$atts = shortcode_atts(
				array(
					'name' => '',
					'videos' => '',
					'embed' => '1',
				), $atts, 'videowhisper_playlist');


			if (!$atts['name'] && !$atts['videos']) return 'No playlist or video list specified!';

			$options = get_option( 'VWvideoShareOptions' );

			if ($atts['embed'])
				if (VWvideoShare::hasPriviledge($options['embedList'])) $showEmbed=1;
				else $showEmbed = 0;
				else $showEmbed = 0;


				$player = $option['playlist_player'];
			if (!$player) $player = 'video-js';

			switch ($player)
			{
			case 'strobe':

				$playlist_m3u = admin_url() . 'admin-ajax.php?action=vwvs_playlist_m3u&playlist=' . urlencode($atts['name']);

				$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
				$flashvars ='src=' .$playlist_m3u. '&autoPlay=false';

				$htmlCode .= '<object class="videoPlayer" width="480" height="360" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';

				// $dfrt56 .= $htmlCode;
				$embedCode .= '<BR><a href="'.$playlist_m3u . '">Playlist M3U</a>';

				$htmlCode .= '<br><h5>Embed Flash Playlist HTML Code (Copy and Paste to your Page)</h5>';
				$htmlCode .= htmlspecialchars($embedCode);
				break;


			case 'video-js':

				if ($atts['name'] && !$atts['videos'])
				{
					if (!taxonomy_exists($options['custom_taxonomy'])) $htmlCode .= 'Error: Taxonomy does not exist: '. $options['custom_taxonomy'];

					$args = array(
						'post_type' => $options['custom_post'],
						'post_status' => 'publish',
						'posts_per_page' => 100,
						'order'            => 'DESC',
						'orderby' => 'post_date',
						$options['custom_taxonomy'] => strtolower($atts['name']),
						'tax_query' => array(
							'taxonomy' => $options['custom_taxonomy'],
							'field'    => 'name',
							'terms'    =>  $atts['name'],
						),

					);
					//var_dump($args);
					$id = preg_replace("/[^A-Za-z0-9]/", '', $atts['name']);

					$postslist = get_posts( $args );
					//var_dump($postslist);

$listCode = '';
					if (count($postslist)>0)
						foreach ($postslist as $item)
						{

							$listCode .= ($listCode?",\r\n":'');
							
							$listCode .= "{ ";
													
							$listCode .= 'sources: [{';							
  							$source = VWvideoShare::path2url(VWvideoShare::videoPath($item->ID));
							$listCode .= 'src: "' . $source . '", ';							
							$listCode .= 'type: "video/mp4" ';							
							$listCode .= "}],\r\n";
	
  
							$listCode .= 'name: "'.$item->post_title.'", ';
							$listCode .= 'description: "'. strip_tags( $item->post_content) .'", ';
		
							

							$poster =  VWvideoShare::path2url(get_post_meta($item->ID, 'video-thumbnail', true));
							$listCode .= ' thumbnail: [ {srcset: "'. $poster .'", type: "image/jpeg", media: "(min-width: 400px;)"}, {src: "'. $poster .'"}] ';
					
							$listCode .= ' }';
						}
					else $htmlCode .= 'No published videos found for playlist "' . $atts['name']. '", taxonomy "'.$options['custom_taxonomy'].'"';
				}

				//video-js
				wp_enqueue_style( 'video-js', plugin_dir_url(__FILE__) .'video-js/video-js.min.css');
				wp_enqueue_script('video-js', plugin_dir_url(__FILE__) .'video-js/video.min.js');
				
				//video-js playlist
				wp_enqueue_script('video-js-playlist', plugin_dir_url(__FILE__) .'video-js/playlist/videojs-playlist.min.js',  array( 'video-js'));
				wp_enqueue_script('video-js-playlist-ui', plugin_dir_url(__FILE__) .'video-js/playlist/videojs-playlist-ui.min.js',  array( 'video-js', 'video-js-playlist'));
				
				wp_enqueue_style( 'video-js-playlist-ui', plugin_dir_url(__FILE__) .'video-js/playlist/videojs-playlist-ui.css');
				//wp_enqueue_style( 'video-js-playlist-ui', plugin_dir_url(__FILE__) .'video-js/playlist/videojs-playlist-ui.vertical.css');
			

					
				$VideoWidth = $options['playlistVideoWidth'];
				$ListWidth = $options['playlistListWidth'];

				$htmlCode .= <<<EOCODE
 <div class="player-container">

        <div class="vjs-playlist"></div>

        <video id="video_$id" class="video-js" controls width="$VideoWidth" height="540" data-setup='{"fluid": true}' poster=""></video>
</div>

<script>
var \$jQnC = jQuery.noConflict();
\$jQnC(document).ready(function()
{

var player$id = videojs('video_$id');

player$id.ready(function() {
	
player$id.playlist([$listCode]);

player$id.playlistUi({horizontal: true});

player$id.playlist.autoadvance(0);

});

});
</script>
EOCODE;


				if ($showEmbed)
				{
					$embedCode .= '<link rel="stylesheet" type="text/css" href="'.plugin_dir_url(__FILE__) . 'video-js/video-js.min.css' . '">';
					$embedCode .= "\r\n" . '<script src="' . plugin_dir_url(__FILE__) .'video-js/video.min.js' . '" type="text/javascript"></script>';
					$embedCode .= "\r\n" . '<script src="' . plugin_dir_url(__FILE__) .'video-js/4/videojs-playlists.min.js' . '" type="text/javascript"></script>';

					$embedCode .= "\r\n\r\n" . '<script src="' . admin_url() .'admin-ajax.php?action=vwvs_embed&playlist=' . urlencode($atts['name']) . '" type="text/javascript"></script>';


					$embedCode .= "\r\n\r\n". '<BR><a href="'.admin_url() . 'admin-ajax.php?action=vwvs_playlist_m3u&playlist=' . urlencode($atts['name']) . '">Playlist (M3U)</a>';


					$htmlCode .= "\r\n\r\n" . VWvideoShare::embedCode($embedCode, 'Embed Playlist HTML Code', 'Copy and Paste to your Page');
				}

				break;
			}

			return $htmlCode;

		}

		function embedCode($embedCode, $title, $instructions)
		{
			$htmlCode .= '<br><h5>'.$title.'</h5>';
			$htmlCode .= '<textarea style="width:90%; height: 160px">';
			$htmlCode .= '<script src="'.includes_url().'js/jquery/jquery.js" type="text/javascript"></script>'. "\r\n\r\n";
			$htmlCode .= htmlspecialchars($embedCode);
			$htmlCode .= '</textarea>';
			$htmlCode .= '<br>'.$instructions;
			return  $htmlCode;
		}

		function adVAST($id)
		{

			$options = get_option( 'VWvideoShareOptions' );

			//Ads enabled?
			$showAds = $options['adsGlobal'];

			//video exception playlists
			if ($id)
			{
				$lists = wp_get_post_terms(  $id, $options['custom_taxonomy'], array( 'fields' => 'names' ) );
				if (is_array($lists))
					foreach ($lists as $playlist)
					{
						if (strtolower($playlist) == 'sponsored') $showAds= true;
						if (strtolower($playlist) == 'adfree') $showAds= false;
					}

			}

			//no ads for premium users
			if ($showAds) if (VWvideoShare::hasPriviledge($options['premiumList'])) $showAds= false;


				if (!$showAds) return '';
				else return $options['vast'];

		}

		function shortcode_embed_code($atts)
		{
			$options = get_option( 'VWvideoShareOptions' );

			$atts = shortcode_atts(
				array(
					'poster' => '',
					'width' => $options['thumbWidth'],
					'height' => $options['thumbHeight'],
					'poster' => $options['thumbHeight'],
					'source' => '',
					'source_type' => '',
					'id' => '0',
					'fallback' => 'You must have a HTML5 capable browser to watch this video. Read more about video sharing solutions and players on <a href="https://videosharevod.com/">Video Share VOD</a> website.'
				), $atts, 'videowhisper_embed_code');

			$player = $options['embed_player'];
			if (!$player) $player = 'native';


			switch ($player)
			{
			case 'native':

				if ($atts['poster']) $posterProp = ' poster="' . $atts['poster'] . '"';
				else $posterProp ='';

				$embedCode .= "\r\n" . '<video width="' . $atts['width'] . '" height="' . $atts['height'] . '"  preload="metadata" autobuffer controls="controls"' . $posterProp . '>';
				$embedCode .= "\r\n" . ' <source src="' . $atts['source'] . '" type="' . $atts['source_type'] . '">';
				$embedCode .= "\r\n" . '</video>';
				$embedCode .= "\r\n" . "\r\n" . '<br><a href="' . $atts['source'] . '">' . __('Download Video File', 'video-share-vod') . '</a> (' . __('right click and Save As..', 'video-share-vod') . ')';
				break;
			}

			return VWvideoShare::embedCode($embedCode, __('Embed Video HTML Code','video-share-vod'), __('Copy and Paste to your Page','video-share-vod'));
		}


		function videowhisper_player_html($atts)
		{
			//html5 video player
			$options = get_option( 'VWvideoShareOptions' );

			$atts = shortcode_atts(
				array(
					'poster' => '',
					'width' => $options['thumbWidth'],
					'height' => $options['thumbHeight'],
					'poster' => $options['thumbHeight'],
					'source_alt' => '',
					'source' => '',
					'source_type' => '',
					'source2' => '',
					'source_type2' => '',
					'source3' => '',
					'source_type3' => '',					
					'source_alt_type' => '',
					'player' => '',
					'id' => '0',
					'fallback_enabled' =>'1',
					'fallback' => 'You must have a HTML5 capable browser to watch this video. Read more about video sharing solutions and players on <a href="https://videosharevod.com/">Video Share VOD Script</a> website.'
				), $atts, 'videowhisper_player_html');

			if (!$atts['player']) $player = $options['html5_player'];
			else $player = $atts['player'];

			if ($_GET['player_html'] && $options['allowDebug']) $player = sanitize_file_name($_GET['player_html']);


			if (!$player) $player = 'video-js';

				$htmlCode .= "<!-- videowhisper_player_html: $player -->";


			switch ($player)
			{
			case 'native':

				if ($atts['poster']) $posterProp = ' poster="' . $atts['poster'] . '"';
				else $posterProp ='';

				$htmlCode .='<video width="' . $atts['width'] . '" height="' . $atts['height'] . '"  preload="metadata" autobuffer controls="controls"' . $posterProp . '>';

				$htmlCode .=' <source src="' . $atts['source'] . '" type="' . $atts['source_type'] . '">';
				
				if ($atts['source2']) $htmlCode .=' <source src="' . $atts['source2'] . '" type="' . $atts['source_type2'] . '">';
				if ($atts['source3']) $htmlCode .=' <source src="' . $atts['source3'] . '" type="' . $atts['source_type3'] . '">';

				if ($atts['fallback_enabled']) $htmlCode .=' <div class="fallback"> <p>' . $atts['fallback'] . '</p></div> </video>';

				break;

			case 'wordpress':
				$htmlCode .= do_shortcode('[video src="' . $atts['source'] . '" poster="' . $atts['poster'] . '" width="' . $atts['width'] . '" height="' . $atts['height'] . '"]');
				break;


			case 'video-js':

				wp_enqueue_script('video-js', plugin_dir_url(__FILE__) .'video-js/video.min.js');
				wp_enqueue_style( 'video-js', plugin_dir_url(__FILE__) .'video-js/video-js.min.css');

				
				wp_enqueue_script('video-js-quality', plugin_dir_url(__FILE__) .'video-js/quality/videojs-contrib-quality-levels.min.js', array( 'video-js') );
	
					
					
				$vast = VWvideoShare::adVAST($atts['id']);

				$id = 'vwVid' . $atts['id'];

				$videojsParams = '';
				$videojsCalls = '';

				$videojsCSS = 1;

				$htmlCode .= '<script>var $j = jQuery.noConflict();
				$j(document).ready(function(){ videojs.options.flash.swf = "' . plugin_dir_url(__FILE__) .'video-js/video-js.swf' . '";});</script>';

				//source alternatives (mbr)
				if ($atts['source_alt'])
				{
		
					/*
						
				//dash plugin included in videojs 7+
				wp_enqueue_script('video-dash', plugin_dir_url(__FILE__) .'video-js/dash/dash.all.min.js' );
				wp_enqueue_script('video-js-dash', plugin_dir_url(__FILE__) .'video-js/dash/videojs-dash.min.js', array( 'video-js', 'video-dash') );



					wp_enqueue_script('video-js6', plugin_dir_url(__FILE__) .'video-js/6/videojs-media-sources.js', array( 'video-js') );
					wp_enqueue_script('video-js7', plugin_dir_url(__FILE__) .'video-js/7/videojs-contrib-hls.min.js', array( 'video-js', 'video-js6') );

					// segment handling
					wp_enqueue_script('video-js7-1', plugin_dir_url(__FILE__) .'video-js/7/flv-tag.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-2', plugin_dir_url(__FILE__) .'video-js/7/exp-golomb.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-3', plugin_dir_url(__FILE__) .'video-js/7/h264-stream.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-4', plugin_dir_url(__FILE__) .'video-js/7/aac-stream.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-5', plugin_dir_url(__FILE__) .'video-js/7/segment-parser.js', array( 'video-js', 'video-js7') );

					//m3u8 handling
					wp_enqueue_script('video-js7-6', plugin_dir_url(__FILE__) .'video-js/7/stream.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-7', plugin_dir_url(__FILE__) .'video-js/7/m3u8/m3u8-parser.js', array( 'video-js', 'video-js7') );
					wp_enqueue_script('video-js7-8', plugin_dir_url(__FILE__) .'video-js/7/playlist-loader.js', array( 'video-js', 'video-js7') );
					

					//MBR plugin
					wp_enqueue_script('video-js8-1', plugin_dir_url(__FILE__) .'video-js/8/videojs-mbr-menu-button.js', array( 'video-js', 'video-js6', 'video-js7') );
					wp_enqueue_script('video-js8', plugin_dir_url(__FILE__) .'video-js/8/videojs-mbr.js', array( 'video-js', 'video-js6', 'video-js7') );

					wp_enqueue_style( 'video-js9', plugin_dir_url(__FILE__) .'video-js/8/videojs-mbr.css');
*/									
					$videojsCSS = 0;

				//	$videojsParams .= "techOrder: ['']";
				//	$videojsCalls .= $id . '.mbr({autoSwitch:false});';
					$videojsCalls .= $id . '.controlBar.show();';

					$atts['source'] = $atts['source_alt'];
					$atts['source_type'] = $atts['source_alt_type'];
				}



				$htmlCode .= '<script>
					(function($) {})( jQuery );
					$j(document).ready(function(){
					var ' . $id . ' = videojs("' . $id . '", {' . $videojsParams . '});
					
					var qualityLevels = ' . $id . '.qualityLevels();
					qualityLevels.selectedIndex_ = 0;
					qualityLevels.trigger({ type: \'change\', selectedIndex: 0 });							
					';






				if ($vast)
				{
					
						wp_enqueue_script('video-js-ads', plugin_dir_url(__FILE__) .'video-js/ads/videojs-contrib-ads.min.js', array( 'video-js') );
						wp_enqueue_style( 'video-js-ads', plugin_dir_url(__FILE__) .'video-js/ads/videojs-contrib-ads.css');



					if ($options['vastLib'] == 'vast')
					{
						wp_enqueue_script('video-js-vastclient', plugin_dir_url(__FILE__) .'video-js/ads/vast-client.min.js');

						wp_enqueue_script('video-js3', plugin_dir_url(__FILE__) .'video-js/3/videojs.vast.js', array( 'video-js', 'video-js-vastclient', 'video-js-ads') );
						wp_enqueue_style( 'video-js3', plugin_dir_url(__FILE__) .'video-js/3/videojs.vast.css');

						$videojsCalls .= $id . '.ads();';
						$videojsCalls .= $id . '.vast({ url: \'' . $options['vast'] . '\' })';
					}
				else
				{

					wp_enqueue_script('ima3', 'https://imasdk.googleapis.com/js/sdkloader/ima3.js');

					wp_enqueue_script('video-js-ima', plugin_dir_url(__FILE__) .'video-js/ads/videojs.ima.min.js', array( 'video-js', 'ima3'));
					wp_enqueue_style( 'video-js-ima', plugin_dir_url(__FILE__) .'video-js/ads/videojs.ima.css');


					$videojsCalls .=  $id . '.ima({ id: \'' .$id. '\', adTagUrl: \'' . $options['vast'] . '\' });';
					$videojsCalls .=  $id . '.ima.requestAds();';
				}
				
				}

				$htmlCode .= $videojsCalls;

				$htmlCode .= '});</script>';


				if ($atts['poster']) $posterProp = ' poster="' . $atts['poster'] . '"';
				else $posterProp ='';

				$htmlCode .= ' <video id="' . $id . '" class="video-js vjs-big-play-centered"  controls="controls" preload="metadata" width="' . $atts['width'] . '" height="' . $atts['height'] . '"' . $posterProp . ' data-setup=\'{"fluid": true}\' setup=\'{"fluid": true}\'>';

				$htmlCode .= "\r\n" . ' <source src="' . $atts['source'] . '" type="' . $atts['source_type'] . '">';

				if ($atts['source2']) $htmlCode .= "\r\n" .' <source src="' . $atts['source2'] . '" type="' . $atts['source_type2'] . '">';
				if ($atts['source3']) $htmlCode .="\r\n" . ' <source src="' . $atts['source3'] . '" type="' . $atts['source_type3'] . '">';

				if ($atts['fallback_enabled'])  $htmlCode .=' <div class="fallback"> <p>' . $atts['fallback'] . '</p></div>';
				$htmlCode .=' </video>';

				break;

			default:
				$htmlCode .= 'videowhisper_player_html: Player not found:' . $player;
			}

			return $htmlCode;
		}

		function videowhisper_postvideo_assign($atts)
		{
			$atts = shortcode_atts(
				array(
					'post_id' => '',
					'meta' => 'video_teaser',
					'content' => 'id', // id / video_path / preview_path
					'show' => '1',
					'showWidth' => '320',
				), $atts, 'videowhisper_postvideo_assign');

			$postID = (int) $atts['post_id'];
			$meta = sanitize_file_name($atts['meta']);
			$show = (int) $atts['show'];

			if (!$postID) return 'No postID was specified, to assign post associated videos.';
			if (!is_user_logged_in()) return  'Login required to assign post associated video.';

			$current_user = wp_get_current_user();

			$options = get_option( 'VWvideoShareOptions' );


			if ($_GET['assignVideo'] == $meta && $_POST['select'])
			{
				$value = sanitize_text_field( $_POST[$meta] );
				update_post_meta($postID, $meta, $value);
				$htmlCode .= '<p>Updated...</p>';
			}

			$currentValue = get_post_meta( $postID, $meta, true );

			//query
			$args=array(
				'post_type' =>  $options['custom_post'],
				'author'        =>  $current_user->ID,
				'orderby'       =>  'post_date',
				'order'            => 'DESC',
			);

			$postslist = get_posts( $args );
			if (count($postslist)>0)
			{
				$quickCode .= '<SELECT class="ui dropdown" id="' . $meta . '" name="' . $meta . '">';
				$quickCode .='<option value="" ' .(!$currentValue?'selected':''). '> - </option>';

				foreach ( $postslist as $item )
				{
					$video_id = $item->ID;
					$value = '';

					switch ($atts['content'])
					{
					case 'id':
						$value = $video_id;
						break;

					case 'video_path':
						//retrieve video stream
						$streamPath = '';
						$videoPath = get_post_meta($video_id, 'video-source-file', true);
						$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

						//use conversion if available
						$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
						if ($videoAdaptive) $videoAlts = $videoAdaptive;
						else $videoAlts = array();

						foreach (array('high', 'mobile') as $frm)
						if (is_array($videoAlts))
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

						$value = $streamPath;
						break;

					case 'preview_path':

						$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
						if ($videoAdaptive) $videoAlts = $videoAdaptive;
						else $videoAlts = array();

						if (is_array($videoAlts))
						if (array_key_exists('preview', $videoAlts)) $alt = $videoAlts['preview'];

						if ($alt) if ($alt['file'])
								if (file_exists($alt['file']))
									$streamPath = VWliveWebcams::path2stream($alt['file']);
								$value = $streamPath;

							break;
					}

					if ($value) $quickCode .='<option value="'.$value.'" ' .($currentValue==$value?'selected':''). '>'.$item->post_title.'</option>';

					if ($currentValue==$value) $showID = $video_id;

				}
				$quickCode .='</SELECT>';
			}
			else  $quickCode = 'No videos found! Please add some videos first.';

			$action = add_query_arg(array('assignVideo'=>$meta, 'postID' => $postID), VWvideoShare::getCurrentURL());


			$htmlCode .= <<<HTMLCODE
<form method="post" action="$action" name="adminForm" class="w-actionbox">
<div class="field"><label>Select Video</label> $quickCode <input class="ui button" type="submit" name="select" id="select" value="Select" /></div>
</form>
HTMLCODE;

			if ($show) if ($showID) $htmlCode .= '<br style="clear:both">' . do_shortcode('[videowhisper_player video="' .$showID. '" player="" width="'.$atts['showWidth'].'"]');

				return $htmlCode;

		}

		function videowhisper_postvideos($atts)
		{

			$options = get_option( 'VWvideoShareOptions' );

			$atts = shortcode_atts(
				array(
					'post' => '',
				), $atts, 'videowhisper_postvideos');

			if (!$atts['post']) return 'No post id was specified, to manage post associated videos.';

			$channel = get_post( intval($atts['post']) );

			if ($_GET['playlist_upload']) $htmlCode .= '<A class="ui button" href="'.remove_query_arg('playlist_upload').'">Done Uploading Videos</A>';
			else
			{

				$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' segment"><h3 class="ui header">Manage Videos</h3>';

				$htmlCode .= '<p>Available '.$channel->post_title.' videos: ' . VWvideoShare::importFilesCount( $channel->post_title, VWvideoShare::extensions_flash(), $options['vwls_archive_path']) .'</p>';

				$link  = add_query_arg( array( 'playlist_import' => $channel->post_title), get_permalink() );
				$link2  = add_query_arg( array( 'playlist_upload' => $channel->post_title), get_permalink() );

				$htmlCode .= ' <a class="ui button" href="' .$link.'">Import</a> ';
				$htmlCode .= ' <a class="ui button" href="' .$link2.'">Upload</a> ';
				$htmlCode .= '</div>';
			}

			$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' segment"><h4 class="ui header">'.$channel->post_name.' - Videos</h4>';

			$htmlCode .= do_shortcode('[videowhisper_videos perpage="4" playlist="'.$channel->post_name.'"]');
			$htmlCode .= '</div>';

			return $htmlCode;
		}

		function videowhisper_postvideos_process($atts)
		{

			$atts = shortcode_atts(
				array(
					'post' => '',
					'post_type' => '',
				), $atts, 'videowhisper_postvideos_process');

			VWvideoShare::importFilesClean();

			$htmlCode = '';

			if ($channel_upload = sanitize_file_name($_GET['playlist_upload']))
			{
				$htmlCode .= do_shortcode('[videowhisper_upload playlist="'.$channel_upload.'"]');
			}

			if ($channel_name = sanitize_file_name($_GET['playlist_import']))
			{

				$options = get_option( 'VWvideoShareOptions' );

				$url  = add_query_arg( array( 'playlist_import' => $channel_name), get_permalink() );


				$htmlCode .=  '<div class="ui ' . $options['interfaceClass'] .' form"><form id="videowhisperImport" name="videowhisperImport" action="' . $url . '" method="post">';

				$htmlCode .= "<h3>Import <b>" . $channel_name . "</b> Videos to Playlist</h3>";

				$htmlCode .= VWvideoShare::importFilesSelect( $channel_name, VWvideoShare::extensions_flash(), $options['vwls_archive_path']);

				$htmlCode .=  '<input type="hidden" name="playlist" id="playlist" value="' . $channel_name . '">';

				//same category as post
				if ($atts['post']) $postID = $atts['post'];
				else
					{ //search by name
					global $wpdb;
					if ($atts['post_type']) $cfilter = "AND post_type='" . $atts['post_type'] . "'";
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $channel_name . "' $cfilter LIMIT 0,1" );
				}

				if ($postID)
				{
					$cats = wp_get_post_categories( $postID);
					if (count($cats)) $category = array_pop($cats);
					$htmlCode .=  '<input type="hidden" name="category" id="category" value="' . $category . '">';
				}

				$htmlCode .=   '<INPUT class="ui button" TYPE="submit" name="import" id="import" value="Import">';

				$htmlCode .=  ' <INPUT class="ui button" TYPE="submit" name="delete" id="delete" value="Delete">';

				$htmlCode .=  '</form></div>';
			}

			return $htmlCode;
		}


		//!permission functions

		//if any key matches any listing
		public static function inList($keys, $data)
		{
			if (!$keys) return 0;

			$list = explode(",", strtolower(trim($data)));

			foreach ($keys as $key)
				foreach ($list as $listing)
					if ( strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}

		public static function hasPriviledge($csv)
		{
			//determines if user is in csv list (role, id, email)

			if (strpos($csv,'Guest') !== false) return 1;



			if (is_user_logged_in())
			{
				global $current_user;
				get_currentuserinfo();

				//access keys : roles, #id, email
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
				}

				if (VWvideoShare::inList($userkeys, $csv)) return 1;
			}

			return 0;
		}

		function hasRole($role)
		{
			if (!is_user_logged_in()) return false;

			global $current_user;
			get_currentuserinfo();

			$role = strtolower($role);

			if (in_array($role, $current_user->roles)) return true;
			else return false;
		}

		static function getRoles()
		{
			if (!is_user_logged_in()) return 'None';

			global $current_user;
			get_currentuserinfo();

			return implode(", ", $current_user->roles);
		}


		static function arrayRand($arrX)
		{
			$randIndex = array_rand($arrX);
			return $arrX[$randIndex];
		}
		
		static function poweredBy()
		{

			$options = get_option('VWvideoShareOptions');

			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';

			return '<div id="VideoWhisper" style="text-align: center; display: ' . $state . ';"><p>' . self::arrayRand(array('Developed with', 'Published with', 'Powered by', 'Added with', 'Managed by')) . ' VideoWhisper <a href="https://videosharevod.com/">Video Share VOD '.self::arrayRand(array('Turnkey Site Solution', 'Site Software', 'WordPress Plugin', 'Site Script', 'for WordPress', 'Turnkey Site Builder')) .'</a>.</p></div>';
		}

		//get video path
		function videoPath($video_id, $type = 'auto')
		{

			$options = get_option('VWvideoShareOptions');

			if ($type == 'auto')
			{
				$isMobile = (bool)preg_match('#\b(ip(hone|od|ad)|android|opera m(ob|in)i|windows (phone|ce)|blackberry|tablet|s(ymbian|eries60|amsung)|p(laybook|alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]|mobile|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );

				if ($isMobile) $type = 'html5-mobile';
				else $type='html5';
			}


			$videoPath = get_post_meta($video_id, 'video-source-file', true);
			$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

			switch ($type)
			{
			case 'html5':


				//use conversion - high first
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach (array('high', 'mobile') as $frm)
				if (is_array($videoAlts))				
				if (array_key_exists($frm, $videoAlts))				
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							return $alt['file'];

						}

					if ($options['originalBackup'])
						if (in_array($ext, array('mp4')))
						{
							return $videoPath;
						}

					break;

			case 'html5-mobile':

				//use conversion - mobile first
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach (array('mobile', 'high') as $frm)
				if (is_array($videoAlts))
				if (array_key_exists($frm, $videoAlts))								
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							return $alt['file'];

						}

					if ($options['originalBackup'])
						if (in_array($ext, array('mp4')))
						{
							return $videoPath;
						}

					break;

			case 'flash':

				//use conversion
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach (array('high', 'mobile') as $frm)
				if (is_array($videoAlts))				
				if (array_key_exists($frm, $videoAlts))								
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							return $alt['file'];

						}

				if ($options['originalBackup'])
					if (in_array($ext, VWvideoShare::extensions_flash()))
					{
						return $videoPath;
					}

				break;
			}

			return 'Missing-videoPath-' . $video_id;
		}

		//embed a video from another site
		function videowhisper_embed($atts)
		{
			$atts = shortcode_atts(array(
					'provider' => 'youtube',
					'width' => '640',
					'height '=> '390',
					'videoId'=>'oifAEZJYKvI'), $atts, 'videowhisper_embed');

			$width = intval($atts['width']);
			$height = intval($atts['height']);
			$videoId = sanitize_text_field($atts['videoId']);

			$htmlCode = '';

			switch ($atts['provider'])
			{
			case 'youtube':


				//$htmlCode .= '<iframe id="player" type="text/html" width="'.$width.'" height="'.$height.'" src="https://www.youtube.com/embed/'.$videoId.'?enablejsapi=1&origin='. site_url() .'" frameborder="0"></iframe>';

				$htmlCode .= '<div id="player"></div>';

				$jsCode .= <<<EOT
      var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

      var player;
      function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {
          width: '$width',
          height: '$height',
          videoId: '$videoId',
          events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
          }
        });
      }

      function onPlayerReady(event) {
        //event.target.playVideo();
      }

      var done = false;
      function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING ) {

        }
      }

EOT;

				wp_add_inline_script( 'ytp-embed-js', $jsCode );

				break;
			}

			return $htmlCode;
		}

		function videowhisper_player($atts)
		{

			$atts = shortcode_atts(array('video' => '0', 'embed' => '1', 'player' => '',  'width' => '', 'height' =>'', 'fallback_enabled'=>'1'), $atts, 'videowhisper_player');

			$video_id = intval($atts['video']);
			if (!$video_id) return 'videowhisper_player: Missing video id = ' . $atts['video'];

			$video = get_post($video_id);
			if (!$video) return 'videowhisper_player: Video #'. $video_id . ' not found!';

			$vWidth = $atts['width']; //string
			$pWidth = intval($atts['width']); //force width if numeric

			$options = get_option( 'VWvideoShareOptions' );

			//VOD
			$deny = '';

			//global
			if (!VWvideoShare::hasPriviledge($options['watchList'])) $deny = 'Your current membership does not allow watching videos.';

			//by playlists
			$lists = wp_get_post_terms( $video_id, $options['custom_taxonomy'], array( 'fields' => 'names' ) );

			if (!is_array($lists))
			{
				if (is_wp_error($lists)) echo 'Error: Can not retrieve "playlist" terms for video post: ' . $lists->get_error_message();

				$lists = array();
			}


			//playlist role required?
			if ($options['vod_role_playlist'])
				foreach ($lists as $key=>$playlist)
				{
					$lists[$key] = $playlist = strtolower(trim($playlist));

					//is role
					if (get_role($playlist)) //video defines access roles
						{
						$deny = 'This video requires special membership. Your current membership: ' .VWvideoShare::getRoles() .'.' ;
						if (VWvideoShare::hasRole($playlist)) //has required role
							{
							$deny = '';
							break;
						}
					}
				}

			//exceptions
			if (in_array('free', $lists)) $deny = '';

			if (in_array('registered', $lists))
				if (is_user_logged_in()) $deny = '';
				else $deny = 'Only registered users can watch this videos. Please login first.';

				if (in_array('unpublished', $lists)) $deny = 'This video has been unpublished.';

				if ($deny)
				{
					$htmlCode .= str_replace('#info#',$deny, html_entity_decode(stripslashes($options['accessDenied'])));
					$htmlCode .= '<br>';
					$htmlCode .= do_shortcode('[videowhisper_preview video="' . $video_id . '"]') . VWvideoShare::poweredBy();
					return $htmlCode;
				}

			//update stats
			$views = get_post_meta($video_id, 'video-views', true);
			if (!$views) $views = 0;
			$views++;
			update_post_meta($video_id, 'video-views', $views);
			update_post_meta($video_id, 'video-lastview', time());

			//snap
			$imagePath = get_post_meta($video_id, 'video-snapshot', true);
			if ($imagePath)
				if (file_exists($imagePath))
				{
					$imageURL = VWvideoShare::path2url($imagePath);
					$posterVar = '&poster=' . urlencode($imageURL);
					$posterProp = ' poster="' . $imageURL . '"';
				} else VWvideoShare::updatePostThumbnail($update_id);



			//embed code?
			if ($atts['embed'])
				if (VWvideoShare::hasPriviledge($options['embedList'])) $showEmbed=1;
				else $showEmbed = 0;
				else $showEmbed = 0;

				$player = $options['player_default'];

			//Detect special conditions browsers & devices
			$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
			$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
			$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
			$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

			$Safari  = (stripos($_SERVER['HTTP_USER_AGENT'],"Safari") && !stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome'));

			$Mac = stripos($_SERVER['HTTP_USER_AGENT'],"Mac OS");
			$Firefox = stripos($_SERVER['HTTP_USER_AGENT'],"Firefox");


			if ($Mac && $Firefox) $player = $options['player_firefox_mac'];

			if ($Safari) $player = $options['player_safari'];

			if ($Android) $player = $options['player_android'];

			if ($iPod || $iPhone || $iPad) $player = $options['player_ios'];

			//force a player from shortcode
			if ($atts['player']) $player = $atts['player'];

			if ($_GET['player'] && $options['allowDebug']) $player = sanitize_file_name($_GET['player']);


			if (!$player) $player = $options['player_default'];

			//res
			if (!$vWidth) $vWidth = get_post_meta($video_id, 'video-width', true);
			if (!$vHeight) $vHeight = get_post_meta($video_id, 'video-height', true);
		
			if (!$vWidth) $vWidth = $options['thumbWidth'];
			if (!$vHeight) $vHeight = $options['thumbHeight'];

			if (strstr($vWidth, '%'))
			{
			$vHeight = '62%';	
			}
			else
			if ($pWidth) //force width but keep aspect ratio if numeric provided
				{
				$pHeight = $pWidth*$vHeight/$vWidth;
				$vWidth = $pWidth;
				$vHeight = $pHeight;
				}
				
		

			$htmlCode .= "<!--videoPlayer:$player|Mac$Mac|Ff$Firefox|iPh$iPhone|iPa$iPad|An$Android|Sa$Safari|vw$vWidth|vh$vHeight|pw$pWidth|ph$pHeight-->";

			switch ($player)
			{
			case 'strobe':

				$videoPath = get_post_meta($video_id, 'video-source-file', true);
				$videoURL = VWvideoShare::path2url($videoPath);

				//$videoURLmbr =  site_url() . '/mbr/http/' . $video_id . '.f4m' ;

				$vast = VWvideoShare::adVAST($atts['video']);

				$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';

				$flashvars ='src=' . urlencode($videoURL) . '&autoPlay=false' . $posterVar;

				if ($vast)
				{
					//$flashvars .= '&plugin_mast=' .  urlencode(plugin_dir_url(__FILE__) . 'strobe/MASTPlugin.swf');
					//$flashvars .= '&src_mast_uri=' .  urlencode(plugin_dir_url(__FILE__) . 'strobe/mast_vast_2_wrapper.xml');
					//$flashvars .= 'src_namespace_mast=https://www.akamai.com/mast/1.0';

					//$flashvars .= '&src_namespace_mast=' .  urlencode(plugin_dir_url(__FILE__) . 'strobe/mast_vast_2_wrapper.xml');
				}

				$htmlCode .= '<object class="videoPlayer" width="' . $vWidth . '" height="' . $vHeight . '" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';

				if ($showEmbed)
				{
					$embedCode = htmlspecialchars($htmlCode);
					$embedCode .= htmlspecialchars('<br><a href="' . $videoURL . '">' . __('Download Video File', 'video-share-vod') . '</a> (' . __('right click and Save As..', 'video-share-vod') . ')');

					$htmlCode .= '<br><h5>' . __('Embed Flash Video Code (Copy & Paste to your Page)', 'video-share-vod') . '</h5>';
					$htmlCode .= $embedCode;

				}
				break;

			case 'strobe-rtmp':
				$videoPath = get_post_meta($video_id, 'video-source-file', true);
				$ext = pathinfo($videoPath, PATHINFO_EXTENSION);


				//use conversion if available
				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach (array('high', 'mobile') as $frm)
				if (is_array($videoAlts))				
				if (array_key_exists($frm, $videoAlts))								
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
							$stream = VWvideoShare::path2stream($alt['file']);
							break;
						};

				//user original
				if (!$stream)
					if (in_array($ext, array('flv','mp4','m4v')))
					{
						//use source if compatible
						$stream = VWvideoShare::path2stream($videoPath);
					}


				if (!$stream) $htmlCode .= 'Adaptive format required but missing for this video!';



				$videoRTMP = $options['rtmpServer'] . '/' . $stream;

				//mbr support
				$videoURLmbr =  site_url() . '/mbr/rtmp/' . $video_id . '.f4m' ;

				if ($stream)
				{

					if ($ext == 'mp4') $stream = 'mp4:' . $stream;

					$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
					$flashvars ='src=' . urlencode($videoURLmbr) . '&autoPlay=false' . $posterVar;

					$htmlCode .= '<object class="videoPlayer" width="' . $vWidth . '" height="' . $vHeight . '" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';
				}
				else $htmlCode .= 'Stream not found!';

				if ($showEmbed)
				{
					$embedCode = htmlspecialchars($htmlCode);
					$embedCode .= htmlspecialchars('<br><a href="' . $videoURL . '">Download Video File</a> (right click and Save As..)');

					$htmlCode .= '<br><h5>Embed Flash Video Code (Copy & Paste to your Page)</h5>';
					$htmlCode .= $embedCode;
				}

				break;

			case 'html5':



				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();

				foreach (array('high', 'mobile') as $frm)
					if (is_array($videoAlts))
					if (array_key_exists($frm, $videoAlts))							
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							$videoURL = VWvideoShare::path2url($alt['file']);
							$videoType = $alt['type'];
							$width = $alt['width'];
							$height = $alt['height'];
							break;
						};

				//backup: use original if mp4
				if (!$videoURL)
				{
					$videoPath = get_post_meta($video_id, 'video-source-file', true);
					$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

					if ($ext == 'mp4')
					{
						$videoURL = VWvideoShare::path2url($videoPath);
						$videoType = 'video/mp4';

						$width = $vWidth;
						$height = $vHeight;
					}
				}


				if (!$videoURL)
				{
					$htmlCode .= 'Mobile adaptive format required but missing for this video!';
					VWvideoShare::convertProcessQueue();
				}

				if (($videoURL))
				{
					$htmlCode .= do_shortcode('[videowhisper_player_html source="' . $videoURL . '" source_type="' . $videoType . '" poster="' . $imageURL . '" width="' . $vWidth . '" height="' . $vHeight . '" id="' . $video_id . '" fallback_enabled ="'.$atts['fallback_enabled'].'"]');

					if ($showEmbed) $htmlCode .= do_shortcode('[videowhisper_embed_code source="' . $videoURL . '" source_type="' . $videoType . '" poster="' . $imageURL . '" width="' . $vWidth . '" height="' . $vHeight . '" id="' . $video_id . '"]');

				}

				break;

			case 'html5-mobile':

				//only mobile sources

				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();
				
				if (is_array($videoAlts))
				if (array_key_exists('mobile', $videoAlts)) 						
				if ($alt = $videoAlts['mobile'])
					if (file_exists($alt['file']))
					{
						$videoURL = VWvideoShare::path2url($alt['file']);
						$videoType = $alt['type'];
						$width = $alt['width'];
						$height = $alt['height'];

					} else $htmlCode .= 'Mobile adaptive format file missing for this video!' . $alt['file'];
				else $htmlCode .= 'Mobile adaptive format missing for this video!';
				else $htmlCode .= 'Mobile adaptive format key missing for this video!';


				if (($videoURL)) $htmlCode .= do_shortcode('[videowhisper_player_html source="' . $videoURL . '" source_type="' . $videoType . '" poster="' . $imageURL . '" width="' . $vWidth . '" height="' . $vHeight . '" id="' . $video_id . '"]');

				break;


			case 'html5-hls':

				//use conversion

				$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
				if ($videoAdaptive) $videoAlts = $videoAdaptive;
				else $videoAlts = array();


				foreach (array('high', 'mobile') as $frm)
					if (is_array($videoAlts))
					if (array_key_exists($frm, $videoAlts))											
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							$stream = VWvideoShare::path2stream($alt['file']);
							$videoType = $alt['type'];
							$width = $alt['width'];
							$height = $alt['height'];
							break;

						}

					if (!$stream) $htmlCode .= 'HLS: Mobile adaptive format missing for this video!';

					if ($stream)
					{
						$stream = 'mp4:' . $stream;

						if ($options['hlsServer'])
						{
						//hls
						$streamURL2 = $options['hlsServer'] . '_definst_/' . $stream . '/playlist.m3u8';
						$source_type2 = 'application/x-mpegURL';
						
						//mpeg
						$streamURL3 = $options['hlsServer'] . '_definst_/' . $stream . '/manifest.mpd';
						$source_type3 = 'application/dash+xml';
						} $htmlCode .= 'HLS: No HLS server configured!';
						
						//use static .ts segments if available
						if ($options['convertHLS'])
						{
						$source = $videoURLmbr =  site_url() . '/mbr/hls/' . $video_id . '.m3u8';
						$source_type = $source_alt_type = 'application/x-mpegURL';
						}
						else 
						{
							$source = $streamURL2;
							$source_type = $source_type2;
						}


						
						$htmlCode .= do_shortcode('[videowhisper_player_html source_alt="' . $videoURLmbr . '" source_alt_type="' .$source_alt_type. '" source="' . $source . '" source_type="' . $source_type . '" source2="' . $streamURL2 . '" source_type2="' . $source_type2 . '" source3="' . $streamURL3 . '" source_type3="' . $source_type3 . '"  poster="' . $imageURL . '" width="' . $vWidth . '" height="' . $vHeight . '" id="' . $video_id . '"]');

					} else $htmlCode .= 'HLS: Stream not found!';

				break;

			default:
				$htmlCode .= 'Player not found:' . $player;

			}


			return $htmlCode . VWvideoShare::poweredBy();
		}

		//! Custom Post Pages

		function single_template($single_template)
		{

			if (!is_single())  return $single_template;

			$options = get_option('VWvideoShareOptions');

			$postID = get_the_ID();
			if (get_post_type( $postID ) != $options['custom_post']) return $single_template;

			if ($options['postTemplate'] == '+plugin')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-video.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}

			$single_template_new = get_template_directory() . '/' . $options['postTemplate'];

			if (file_exists($single_template_new)) return $single_template_new;
			else return $single_template;
		}



		function video_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			$options = get_option( 'VWvideoShareOptions' );

			if (get_post_type( $postID ) != $options['custom_post']) return $content;


			if ($options['videoWidth']) $wCode = ' width="' . trim($options['videoWidth']) . '"';
			else $wCode ='';
			
			$addCode .= '' . '<div class="videowhisperPlayerContainer"><!--video_page:'.$postID.$wCode.'-->[videowhisper_player video="' . $postID . '" embed="1" '.$wCode.']</div>';

			//playlist
			global $wpdb;

			$terms = get_the_terms( $postID, $options['custom_taxonomy'] );

			if ( $terms && ! is_wp_error( $terms ) )
			{


				$paymentRequired = '';
				$addCode .=  '<div class="w-actionbox">';
				foreach ( $terms as $term )
				{

					if (class_exists("VWliveStreaming"))  if ($options['vwls_channel'])
						{


							$channelID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $term->slug . "' and post_type='channel' LIMIT 0,1" );

							if ($channelID)	
								{
								$addCode .= ' <a title="' . __('Channel', 'video-share-vod') . ': '. $term->name .'" class="videowhisper_playlist_channel button g-btn type_red size_small mk-button dark-color  mk-shortcode two-dimension small" href="'. get_post_permalink( $channelID ) . '">' . $term->name . ' Channel</a> ' ;
								if (!VWliveStreaming::userPaidAccess($current_user->ID, $channelID)) 
								return '<h4>Paid Channel Video</h4><p>This video is only accessible after paying for channel: <a class="button" href="' . get_permalink( $channelID ) . '">' . $term->slug . '</a></p> <h5>Paid Video Preview</h5>' . do_shortcode( '[videowhisper_preview video="' . $postID . '"]' );
								}
						}


					$addCode .= ' <a title="' . __('Playlist', 'video-share-vod') . ': '. $term->name .'" class="videowhisper_playlist button g-btn type_secondary size_small mk-button dark-color  mk-shortcode two-dimension small" href="'. get_term_link( $term->slug, $options['custom_taxonomy']) . '">' . $term->name . '</a> ' ;


				}
				$addCode .=  '</div>';

			}


			$views = get_post_meta($postID, 'video-views', true);
			if (!$views) $views = 0;

			$addCode .= '<div class="videowhisper_views">' . __('Video Views', 'video-share-vod') . ': ' . $views . '</div>';

			//! show reviews
			if ($options['rateStarReview'])
			{
				//tab : reviews
				if (shortcode_exists("videowhisper_review"))
					$addCode .= '<h3>' . __('My Review', 'video-share-vod') . '</h3>' . do_shortcode('[videowhisper_review content_type="video" post_id="' . $postID . '" content_id="' . $postID . '"]' );
				else $addCode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';

				if (shortcode_exists("videowhisper_reviews"))
					$addCode .= '<h3>' . __('Reviews', 'video-share-vod') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );

			}

			$addCode .= html_entity_decode(stripslashes($options['containerCSS']));

			return $addCode . $content ;
		}


		function channel_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			if (get_post_type( $postID ) != 'channel') return $content;

			$channel = get_post( $postID );

			$addCode = '<div class="w-actionbox color_alternate"><h3>' . __('Channel Playlist', 'video-share-vod') . '</h3> ' . '[videowhisper_videos playlist="' . $channel->post_name . '"] </div>';

			return $addCode . $content;

		}

		function tvshow_page($content)
		{
			if (!is_single()) return $content;

			$options = get_option( 'VWvideoShareOptions' );
			$postID = get_the_ID();
			if (get_post_type( $postID ) != $options['tvshows_slug']) return $content;

			$tvshow = get_post( $postID );

			$imageCode = '';
			$post_thumbnail_id = get_post_thumbnail_id($postID);
			if ($post_thumbnail_id) $post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview') ;

			if ($post_featured_image)
			{
				$imageCode = '<IMG style="padding-bottom: 20px; padding-right:20px" SRC ="'.$post_featured_image[0].'" WIDTH="'.$post_featured_image[1].'" HEIGHT="'.$post_featured_image[2].'" ALIGN="LEFT">';
			}

			$addCode = '<br style="clear:both"><div class="w-actionbox color_alternate"><h3>' . __('Episodes', 'video-share-vod') . '</h3> ' . '[videowhisper_videos playlist="' . $tvshow->post_name . '" select_category="0"] </div>';

			return  $imageCode . $content . $addCode;

		}

		//! Conversions


		//if $action was already done in last $expire, return false
		function timeTo($action, $expire = 60, $options='')
		{
			if (!$options) $options = get_option('VWvideoShareOptions');

			$cleanNow = false;

			$ztime = time();

			$lastClean = 0;
			$lastCleanFile = $options['uploadsPath'] . '/' . $action . '.txt';

			if (!file_exists($dir = dirname($lastCleanFile))) mkdir($dir);
			elseif (file_exists($lastCleanFile)) $lastClean = file_get_contents($lastCleanFile);

			if (!$lastClean) $cleanNow = true;
			else if ($ztime - $lastClean > $expire) $cleanNow = true;

				if ($cleanNow)
					file_put_contents($lastCleanFile, $ztime);

				return $cleanNow;
		}

		function timeToGet($action, $options='')
		{
			if (!$options) $options = get_option('VWvideoShareOptions');

			$lastCleanFile = $options['uploadsPath'] . '/' . $action . '.txt';

			if (file_exists($lastCleanFile)) $lastClean = file_get_contents($lastCleanFile);

			return $lastClean;
		}

		static function optimumBitrate($width, $height, $options)
		{
			if (!$width) return 500;
			if (!$height) return 500;

			if (!$options) $options = get_option( 'VWvideoShareOptions' );

			$bitrateHD = $options['bitrateHD'];
			if (!$bitrateHD) $bitrateHD = 8192;

			$pixels = $width * $height;

			/*
			$bitrate = 500;
			if ($pixels >= 640*360) $bitrate = 1000;
			if ($pixels >= 854*480) $bitrate = 2500;
			if ($pixels >= 1280*720) $bitrate = 5000;
			if ($pixels >= 1920*1080) $bitrate = 8000;
			*/

			$bitrate = floor($pixels*$bitrateHD/2073600);

			return $bitrate;
		}

		static function convertVideo($post_id, $overwrite = false, $verbose = false)
		{

			if ($verbose) echo "<BR>convertVideo($post_id, $overwrite , $verbose)";
			
			if (!$post_id) return;

			$options = get_option( 'VWvideoShareOptions' );

			if (!$options['convertMobile'] && !$options['convertHigh'] && !$overwrite) return;

			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			$videoSize = filesize($videoPath);

			if ($verbose) echo "<BR>path: $videoPath size: $videoSize";
			
			
			if (!$videoPath) return;

			$sourceExt = pathinfo($videoPath, PATHINFO_EXTENSION);


			$videoWidthM = $videoWidth = get_post_meta($post_id, 'video-width', true);
			$videoHeightM = $videoHeight = get_post_meta($post_id, 'video-height', true);
			if ($verbose) echo "<BR>width, height: $videoWidth, $videoHeight";
			if ($verbose) if (!$videoWidth) echo " - Update Info";

			if (!$videoWidth) return; // no size detected yet

			$videoCodec = get_post_meta($post_id, 'video-codec-video', true);
			$audioCodec = get_post_meta($post_id, 'video-codec-audio', true);

			if ($verbose) echo "<BR>codecs: $videoCodec, $audioCodec";

			if (!$videoCodec) return; // no codec detected yet

			$videoBitrate = get_post_meta($post_id, 'video-bitrate', true);


			//valid mp4 for html5 playback?
			if (($sourceExt == 'mp4') && ($videoCodec == 'h264') && ($audioCodec = 'aac')) $isMP4 =1;
			else $isMP4 = 0;



			//convertWatermark
			$cmdW ='';
			if ($options['convertWatermark']) if (file_exists($options['convertWatermark']))
				{
					$cmdW = ' -i "' . $options['convertWatermark'] . '" -filter_complex "overlay=' .$options['convertWatermarkPosition']. '" ';
				}


			//retrieve current alternate videos
			$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);

			if ($videoAdaptive)
				if (is_array($videoAdaptive)) $videoAlts = $videoAdaptive;
				else $videoAlts = unserialize($videoAdaptive);
				else $videoAlts = array();


				//conversion formats
				$formats = array();

			//preview format
			if ($options['convertPreview'])
			{
				//crop input to fit thumb ratio
				$Aspect = $videoWidth/$videoHeight;
				$newAspect = $options['thumbWidth']/$options['thumbHeight'];

				if ($newAspect>$Aspect)  //cut height
					{
					$newWidth = $videoWidth;
					$newHeight = floor($videoWidth/$newAspect);

				}else //cut widht
					{
					$newWidth = floor($videoHeight * $newAspect);
					$newHeight = $videoHeight;
				}

				$cmdCrop = ' -vf "crop='.$newWidth.':'.$newHeight.'"';

				$videoWidthM = $options['thumbWidth'];
				$videoHeightM = $options['thumbHeight'];

				$newBitrate = VWvideoShare::optimumBitrate($videoWidthM, $videoHeightM, $options);

				//no need to use more than before
				if ($videoBitrate) if ($newBitrate > $videoBitrate - 50) $newBitrate = $videoBitrate - 50;

					$formats[] = array
					(
						//Mobile: MP4/H.264, Baseline profile, max 1024, for wide compatibility
						'id' => 'preview',
						'cmd' => $options['convertPreviewInput'] . $cmdCrop. ' -s '.$videoWidthM.'x'.$videoHeightM.' -vb ' . $newBitrate . 'k  ' . $options['codecVideoPreview'] . ' ' . $options['codecAudioPreview'] .' '. $options['convertPreviewOutput'],
						'width' => $videoWidthM,
						'height' => $videoHeightM,
						'bitrate' => $newBitrate + 64,
						'type' => 'video/mp4',
						'extension' => 'mp4',
						'noWatermark' =>1,
						'noHLS' => 1
					);
			} else
			{
				//delete old file if present
				$oldFile = $videoAlts['preview']['file'];
				if ($oldFile) if (file_exists($oldFile)) unlink($oldFile);

					unset($videoAlts['preview']);
			}



			// mobile format
			if ($options['convertMobile']==2 || (!$isMP4 && $options['convertMobile']==1) )
			{

				$videoWidthM = $videoWidth;
				$videoHeightM = $videoHeight;

				if ($videoWidthM * $videoHeightM > 1024*768)
				{
					$videoWidthM = 1024;
					$videoHeightM = ceil($videoHeight * 1024 / $videoWidth);
				}


				$newBitrate = 400;

				//no need to use more than before
				if ($videoBitrate) if ($newBitrate > $videoBitrate - 50) $newBitrate = $videoBitrate - 50;

					$formats[] = array
					(
						//Mobile: MP4/H.264, Baseline profile, max 1024, for wide compatibility
						'id' => 'mobile',
						'cmd' => '-vb ' . $newBitrate . 'k  ' . $options['codecVideoMobile'] . ' -s '.$videoWidthM.'x'.$videoHeightM. ' -pix_fmt yuv420p -force_key_frames "expr:gte(t,n_forced*5)"' . ' ' . $options['codecAudioMobile'] ,
						'width' => $videoWidthM,
						'height' => $videoHeightM,
						'bitrate' => $newBitrate + 64,
						'type' => 'video/mp4',
						'extension' => 'mp4'
					);
			} else
			{
				//delete old file if present
				$oldFile = $videoAlts['mobile']['file'];
				if ($oldFile) if (file_exists($oldFile)) unlink($oldFile);

					unset($videoAlts['mobile']);
			}

			//!high format

			//convertHigh
			// 0 = No
			// 1 = Auto
			// 2 = Auto + Bitrate
			// 3 = Always

			$newBitrate = VWvideoShare::optimumBitrate($videoWidth, $videoHeight, $options);
			if ($videoBitrate) if ($newBitrate > $videoBitrate-96) $newBitrate = $videoBitrate-96; //don't increase (also includes 96 sound)


				if ($options['convertHigh']==3 || (!$isMP4 && $options['convertHigh']>=1) || (($videoBitrate > $newBitrate) &&$options['convertHigh']>=2))
				{
					//high quality mp4
					//video
					//-force_key_frames "expr:gte(t,n_forced*5)"
					
					$cmdV =  $options['codecVideoHigh'] . ' -b:v '.$newBitrate.'k -maxrate ' . $newBitrate . 'k -bufsize ' . $newBitrate . 'k -pix_fmt yuv420p ';

					// if h264 copy for auto or autobitrate if lower
					if ($videoCodec == 'h264' && $options['convertHigh']==1 || ($videoCodec == 'h264' && ($videoBitrate <= $newBitrate) && $options['convertHigh']==2))
					{
						$cmdV = '-vcodec copy';
						$newBitrate = $videoBitrate;
					}

					//audio
					$cmdA = $options['codecAudioHigh'];
					if ($audioCodec == 'aac' && $options['convertHigh'] == 1) $cmdA = '-acodec copy';

					$formats[] = array
					(
						'id' => 'high',
						'cmd' => $cmdV . ' -movflags +faststart '. $cmdA,
						'width' => $videoWidth,
						'height' => $videoHeight,
						'bitrate' => $newBitrate + 96,
						'type' => 'video/mp4',
						'extension' => 'mp4'
					);

				}
			else
			{
				//delete old file if present
				$oldFile = $videoAlts['high']['file'];
				if ($oldFile) if (file_exists($oldFile)) unlink($oldFile);

					unset($videoAlts['high']);
			}

			//hook formats
			$formats = apply_filters('videosharevod_formats', $formats);


			$path =  dirname($videoPath);

			$cmdS = array();
			$cmdHS = array();


			// generate missing formats (or overwrite all)
			foreach ($formats as $format)
				if (!$videoAlts[$format['id']] || $overwrite)
				{
					$alt = $format;

					$newFile = $post_id .'_'.$alt['id']. '_' . md5(uniqid($post_id . $alt['id'], true))  . '.' . $alt['extension'];
					$alt['file'] = $path . '/' . $newFile;



					//delete old file
					$oldFile = $videoAlts[$format['id']]['file'];
					if ($oldFile) if ($oldFile != $alt['file']) if (file_exists($oldFile)) unlink($oldFile);

							$cmdS .= ' ' . $format['cmd'] . ' "' . $alt['file'].'"';

						unset($alt['cmd']);

					//a process for each output file
					if (!$options['convertSingleProcess'])
					{
						$logPath = $path . '/' . $post_id . '-' . $alt['id'] . '.txt';
						$cmdPath = $path . '/' . $post_id . '-' . $alt['id'] . '-cmd.txt';


						$cmd = 'ulimit -t 7200; nice ' . $options['ffmpegPath'] . ' -y -threads 1 -i "' . $videoPath .'"'. ($alt['noWatermark']?'':$cmdW) . ' ' . $format['cmd'] .' "' . $alt['file'] . '" &>' . $logPath . ' &';

						VWvideoShare::convertAdd($cmd);

						exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

						$alt['log'] = $logPath;
						$alt['cmd'] = $cmd;
					}

					//segment output for HLS
					if ($options['convertHLS'])
					{

						//clean  previous segmentation
						if ($alt['hls'])
							if (strstr($alt['hls'], $path))
							{
								if (file_exists($alt['hls']))
								{
									$files = glob($alt['hls'] . '/*'); // get all file names
									foreach($files as $file){ // iterate files
										if(is_file($file))
											unlink($file); // delete file
									}
								}

								unlink($alt['hls']);
							}

						if (!$alt['noHLS'])
						{
							$newF = $path . '/' . $post_id .'_'.$alt['id']. '_' . md5(uniqid($post_id . $alt['id'], true));
							if (!file_exists($newF)) mkdir($newF);


							$alt['hls'] = $newF;

							$logPath = $path . '/' . $post_id . '-' . $alt['id'] . '-hls.txt';
							$cmdPath = $path . '/' . $post_id . '-' . $alt['id'] . '-hls-cmd.txt';

							$cmdH = 'ulimit -t 7200; nice ' . $options['ffmpegPath'] . ' -y -threads 1 -i "' . $alt['file'] .'" -flags -global_header -map 0 -f segment -segment_list "' . $alt['hls'] . '/index.m3u8" -segment_time 2 -segment_list_type m3u8 '. $alt['hls'] .'/segment%05d.ts' . ' &>' . $logPath . ' &';

							//if input exists start now, otherwise start later
							if (!$options['convertSingleProcess'] && file_exists($alt['file'])) VWvideoShare::convertAdd($cmdH);
							else $cmdHS[] = $cmdH;

							exec("echo '$cmdH' >> $cmdPath", $output, $returnvalue);

							$alt['logHLS'] = $logPath;
							$alt['cmdHLS'] = $cmdH;
						}

					}

					//update alternatives info
					$videoAlts[$alt['id']] = $alt;

				}

			//run all conversions in a single process (one input, multiple outputs)
			if ($options['convertSingleProcess'] && $cmdS)
			{
				$logPath = $path . '/' . $post_id . '-convert.txt';
				$cmdPath = $path . '/' . $post_id . '-convert-cmd.txt';

				$cmd = 'ulimit -t 7200; nice ' . $options['ffmpegPath'] . ' -y -threads 1 -i ' . $videoPath . $cmdW . ' ' . $cmdS . ' &>' . $logPath . ' &';

				VWvideoShare::convertAdd($cmd);
				exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

			}

			//after conversions, do segmentations
			if ($options['convertHLS']) if ($cmdHS) if (count($cmdHS))
						foreach ($cmdHS as $cmdH) VWvideoShare::convertAdd($cmdH);


						//save adaptive formats records
						update_post_meta( $post_id, 'video-adaptive', $videoAlts );


					update_post_meta( $post_id, 'convert-queued', time() );

		}

		function convertAdd($cmd)
		{
			$options = get_option( 'VWvideoShareOptions' );

			if ($options['convertInstant']) exec($cmd, $output, $returnvalue);
			else
				if (!strstr($options['convertQueue'], $cmd))
				{
					$options['convertQueue'] .= ($options['convertQueue']?"\r\n":'') . $cmd;
					update_option('VWvideoShareOptions', $options);
					VWvideoShare::convertProcessQueue();
				}

		}

		function varSave($path, $var)
		{
			file_put_contents($path, serialize($var));
		}

		function varLoad($path)
		{
			if (!file_exists($path)) return false;

			return unserialize(file_get_contents($path));
		}

		function convertProcessQueue($verbose=0)
		{
			$options = get_option( 'VWvideoShareOptions' );

			$minTime = 12;
			//not more often than $minTime s
			if (!VWvideoShare::timeTo('processQueue', $minTime, $options))
			{
				if ($verbose) echo 'Too fast to check again right now! Wait between checks at least (seconds): ' . $minTime;
				return;
			}

			if (!$options['convertQueue'])
			{
				if ($verbose) echo 'Conversion queue is empty: No conversions need to be started!';
				return;

			}
			//detect if ffmpeg is running
			$cmd = "ps aux | grep '" . $options['ffmpegPath'] . ' -y -threads 1 -i'  .  "'";
			exec($cmd, $output, $returnvalue);

			$transcoding = 0;
			foreach ($output as $line)
				if (!strstr($line, 'grep'))
				{
					$columns = preg_split('/\s+/',$line);
					if ($verbose) echo ($transcoding?'':'<br>FFMPEG Active:') . '<br>' . $line . '';
					$transcoding = 1;
				}


			if (!$transcoding)
			{
				if ($verbose) echo '<BR>No conversion process detected. System is available to start new conversions.';

				//extract first command
				$cmds = explode("\r\n", trim($options['convertQueue']));
				$cmd = array_shift($cmds);

				//save new queue
				$options['convertQueue'] = implode("\r\n", $cmds);
				update_option('VWvideoShareOptions', $options);

				if ($cmd)
				{
					$output = '';
					exec($cmd, $output, $returnvalue);
					if ($verbose)
					{
						echo '<BR>Starting: '. $cmd;
						if (is_array($output)) foreach ($output as $line) echo '<br>' . $line;
					}

					$lastConversion = array('command'=>$cmd, 'time'=>time() );

					$uploadsPath = $options['uploadsPath'];
					if (!file_exists($uploadsPath)) mkdir($uploadsPath);
					$lastConversionPath = $uploadsPath . '/lastConversion.txt';

					VWvideoShare::varSave($lastConversionPath, $lastConversion);
				}
			}


		}

		//! Snapshots
		function generateSnapshots($post_id)
		{
			if (!$post_id) return;

			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (!$videoPath) return;

			$options = get_option( 'VWvideoShareOptions' );

			$path =  dirname($videoPath);
			$imagePath =  $path . '/' . $post_id . '.jpg';
			$thumbPath =  $path . '/' . $post_id . '_thumb.jpg';
			$logPath = $path . '/' . $post_id . '-snap.txt';
			$cmdPath = $path . '/' . $post_id . '-snap-cmd.txt';

			$snapTime = 9;
			$videoDuration = get_post_meta($post_id, 'video-duration', true);
			if ($videoDuration) if ($videoDuration < $snapTime) $snapTime = floor($videoDuration/2);

				$cmd = $options['ffmpegPath'] . ' -y -threads 1 -i "'.$videoPath.'" -ss 00:00:0' . $snapTime . '.000 -f image2 -vframes 1 "' . $imagePath . '" >& ' . $logPath .' &';

			exec($cmd, $output, $returnvalue);
			exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

			update_post_meta( $post_id, 'video-snapshot', $imagePath );

			//probably source snap not ready, yet
			update_post_meta( $post_id, 'video-thumbnail', $thumbPath );

			list($width, $height) = VWvideoShare::generateThumbnail($imagePath, $thumbPath);
			if ($width) update_post_meta( $post_id, 'video-width', $width );
			if ($height) update_post_meta( $post_id, 'video-height', $height );
		}


		function generateThumbnail($src, $dest)
		{
			if (!file_exists($src)) return;

			$options = get_option( 'VWvideoShareOptions' );

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$srcImage = @imagecreatefromjpeg($src);
			if (!$srcImage) return;

			list($width, $height) = getimagesize($src);

			$destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagejpeg($destImage, $dest, 95);

			//return source dimensions
			return array($width, $height);
		}


		function updatePostThumbnail($post_id, $overwrite = false, $verbose = false)
		{
			$imagePath = get_post_meta($post_id, 'video-snapshot', true);
			$thumbPath = get_post_meta($post_id, 'video-thumbnail', true);

			if ($verbose)  echo "<br>Updating thumbnail ($post_id, $imagePath,  $thumbPath)";

			if (!$imagePath) VWvideoShare::generateSnapshots($post_id);
			elseif (!file_exists($imagePath)) VWvideoShare::generateSnapshots($post_id);
			elseif ($overwrite) VWvideoShare::generateSnapshots($post_id);

			if (!$thumbPath) VWvideoShare::generateSnapshots($post_id);
			elseif (!file_exists($thumbPath)) list($width, $height) = VWvideoShare::generateThumbnail($imagePath, $thumbPath);
			else
			{
				if ($overwrite) list($width, $height) = VWvideoShare::generateThumbnail($imagePath, $thumbPath);

				if (!get_the_post_thumbnail($post_id)) //insert if missing
					{
					$wp_filetype = wp_check_filetype(basename($thumbPath), null );

					$attachment = array(
						'guid' => $thumbPath,
						'post_mime_type' => $wp_filetype['type'],
						'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbPath, ".jpg" ) ),
						'post_content' => '',
						'post_status' => 'inherit'
					);


					// Insert the attachment.
					$attach_id = wp_insert_attachment( $attachment, $thumbPath, $post_id );
					set_post_thumbnail($post_id, $attach_id);
				}
				else //just update
					{
					$attach_id = get_post_thumbnail_id($post_id );
					//$thumbPath = get_attached_file($attach_id);
				}

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );


				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbPath );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				if ($verbose) var_dump($attach_data);


			}

			if ($width) update_post_meta( $post_id, 'video-width', $width );
			if ($height) update_post_meta( $post_id, 'video-height', $height );

			//do any conversions after detection
			VWvideoShare::convertVideo($post_id);
		}

		function updateVideo($post_id, $overwrite = false, $verbose = false)
		{

			if ($verbose) echo "<BR>updateVideo($post_id, $overwrite, $verbose)";

			if (!$post_id) return;
			
			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (!$videoPath) return; //source missing

			$videoDuration = get_post_meta($post_id, 'video-duration', true);
			if ($videoDuration && !$overwrite) return;

			$options = get_option( 'VWvideoShareOptions' );

			$path =  dirname($videoPath);
			$logPath = $path . '/' . $post_id . '-dur.txt';
			$cmdPath = $path . '/' . $post_id . '-dur-cmd.txt';

			$cmd = $options['ffmpegPath'] . ' -y -threads 1 -analyzeduration 10000000 -probesize 10000000 -i "'.$videoPath.'" 2>&1';

			$info = shell_exec($cmd);
			exec("echo '$info' >> $logPath", $output, $returnvalue);
			exec("echo '$cmd' >> $cmdPath", $output, $returnvalue);

			if ($verbose) echo "<BR>info:<br><textarea rows='5' cols='100'>$info</textarea>";

			//duration
			preg_match('/Duration: (.*?),/', $info, $matches);
			$duration = explode(':', $matches[1]);

			$videoDuration = intval($duration[0]) * 3600 + intval($duration[1]) * 60 + intval($duration[2]);
			if ($videoDuration) update_post_meta( $post_id, 'video-duration', $videoDuration );
			if ($verbose) echo "<BR>videoDuration:$videoDuration";

			//bitrate
			preg_match('/bitrate:\s(?<bitrate>\d+)\skb\/s/', $info, $matches);
			$videoBitrate = $matches['bitrate'];
			if ($videoBitrate) update_post_meta( $post_id, 'video-bitrate', $videoBitrate );
			if ($verbose) echo "<BR>videoBitrate:$videoBitrate";

			$videoSize = filesize($videoPath);
			if ($videoSize) update_post_meta( $post_id, 'video-source-size', $videoSize );
			if ($verbose) echo "<BR>videoSize:$videoSize";

			//get resolution
			if(strpos($info, 'Video:') !== false)
			{
				preg_match('/\s(?<width>\d+)[x](?<height>\d+)\s\[/', $info, $matches);
				$width = $matches['width'];
				$height = $matches['height'];

				if ($width) update_post_meta( $post_id, 'video-width', $width );
				if ($height) update_post_meta( $post_id, 'video-height', $height );
			}

			//codecs

			//video
			if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
				preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
			list($videoCodec) = explode(' ',$matches[1]);
			if ($videoCodec) update_post_meta( $post_id, 'video-codec-video', strtolower($videoCodec) );
			if ($verbose) echo "<BR>videoCodec:$videoCodec";

			//audio
			$matches = array();
			if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
				preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

			//var_dump($matches);

			list($videoCodecAudio) = explode(' ',$matches[1]);
			if ($videoCodecAudio) update_post_meta( $post_id, 'video-codec-audio', strtolower($videoCodecAudio) );

			//do any conversions after detection
			VWvideoShare::convertVideo($post_id);

			return $videoDuration;
		}

		//! VideoWhisper Live Streaming integration filters
		function vw_ls_manage_channel($val, $cid)
		{
			return do_shortcode("[videowhisper_postvideos post=\"$cid\"]");
		}


		function vw_ls_manage_channels_head($val)
		{
			return do_shortcode("[videowhisper_postvideos_process post_type=\"channel\"]");
		}

		//! Utility Functions
		function humanDuration($t,$f=':') // t = seconds, f = separator
			{
			return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		}

		function humanAge($t)
		{
			if ($t<30) return "NOW";
			return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m') . ' ago';
		}


		function humanFilesize($bytes, $decimals = 2) {
			$sz = 'BKMGTP';
			$factor = floor((strlen($bytes) - 1) / 3);
			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		}

		function path2url($file, $Protocol='http://')
		{
			if (is_ssl() && $Protocol=='http://') $Protocol='https://';

			$url = $Protocol.$_SERVER['HTTP_HOST'];

			//on godaddy hosting uploads is in different folder like /var/www/clients/ ..
			$upload_dir = wp_upload_dir();
			if (strstr($file, $upload_dir['basedir']))
				return  $upload_dir['baseurl'] . str_replace($upload_dir['basedir'], '', $file);

			//folder under WP path
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			if (strstr($file, get_home_path()))
				return site_url() . str_replace(get_home_path(), '/', $file);

			//under document root
			if (strstr($file, $_SERVER['DOCUMENT_ROOT']))
				return  $url . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);

			return $url . $file;
		}

		function path2stream($path, $withExtension=true)
		{
			$options = get_option( 'VWvideoShareOptions' );

			$stream = substr($path, strlen($options['streamsPath']));
			if ($stream[0]=='/') $stream = substr($stream, 1);

			if (!file_exists($options['streamsPath'] . '/' . $stream)) return '';
			elseif ($withExtension) return $stream;
			else return pathinfo($stream, PATHINFO_FILENAME);
		}

		//! import
		function importFilesClean()
		{
			$options = get_option( 'VWvideoShareOptions' );

			if (!$options['importClean']) return;
			if (!file_exists($options['importPath'])) return;
			if (!file_exists($options['uploadsPath'])) return;

			//last cleanup
			$lastFile = $options['uploadsPath'] . '/importCleanLast.txt';
			if (file_exists($lastFile)) $lastClean = file_get_contents($lastFile);

			//cleaned recently
			if ($lastClean > time()-3600) return;

			//start clean

			//save time
			$myfile = fopen($lastFile, "w");
			if (!$myfile) return;
			fwrite($myfile, time());
			fclose($myfile);

			//scan files and clean
			$folder = $options['importPath'];
			$extensions = VWvideoShare::extensions_video();
			$ignored = array('.', '..', '.svn', '.htaccess');
			$expirationTime = time() - $options['importClean'] * 86400;

			$fileList = scandir($folder);
			foreach ($fileList as $fileName)
			{
				if (in_array($fileName, $ignored)) continue;
				if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;

				if (filemtime($folder . $fileName) < $expirationTime) unlink($folder . $fileName);
			}

		}

		function importFilesSelect($prefix, $extensions, $folder)
		{
			if (!file_exists($folder)) return "<div class='error segment'>Video folder not found: $folder !</div>";

			VWvideoShare::importFilesClean();


			$htmlCode .= '';

			//import files
			if ($_POST['import'])
			{

				if (is_array($importFiles = $_POST['importFiles']))
					if (count($importFiles))
					{

						$owner = (int) $_POST['owner'];

						global $current_user;
						get_currentuserinfo();

						if (!$owner) $owner = $current_user->ID;
						elseif ($owner != $current_user->ID && ! current_user_can('edit_users')) return "Only admin can import for others!";

						//handle one or many playlists
						$playlist = $_POST['playlist'];

						//if csv sanitize as array
						if (strpos($playlist, ',') !== FALSE)
						{
							$playlists = explode(',', $playlist);
							foreach ($playlists as $key => $value) $playlists[$key] = sanitize_file_name(trim($value));
							$playlist = $playlists;
						}

						if (!$playlist) return "Importing requires a playlist name!";

						//handle one or many tags
						$tag = $_POST['tag'];

						//if csv sanitize as array
						if (strpos($tag, ',') !== FALSE)
						{
							$tags = explode(',', $playlist);
							foreach ($tags as $key => $value) $tags[$key] = sanitize_file_name(trim($value));
							$tag = $tags;
						}

						$description = sanitize_text_field($_POST['description']);

						$category = sanitize_file_name($_POST['category']);

						foreach ($importFiles as $fileName)
						{
							//$fileName = sanitize_file_name($fileName);
							$ext = pathinfo($fileName, PATHINFO_EXTENSION);
							if (!$ztime = filemtime($folder . $fileName)) $ztime = time();
							$videoName = basename($fileName, '.' . $ext) .' '. date("M j", $ztime);

							$htmlCode .= VWvideoShare::importFile($folder . $fileName, $videoName, $owner, $playlist, $category, $tag, $description);
						}
					}else $htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' warning segment">No files selected to import!</div>';

			}

			//delete files
			if ($_POST['delete'])
			{

				if (count($importFiles = $_POST['importFiles']))
				{
					foreach ($importFiles as $fileName)
					{
						$htmlCode .= '<BR>Deleting '.$fileName.' ... ';
						$fileName = sanitize_file_name($fileName);
						if (!unlink($folder . $fileName)) $htmlCode .= 'Removing file failed!';
						else $htmlCode .= 'Success.';

					}
				}else $htmlCode .= '<div class="warning">No files selected to delete!</div>';
			}

			//preview file
			if ($preview_name = $_GET['import_preview'])
			{
				//$preview_name = sanitize_file_name($preview_name);
				$preview_url = VWvideoShare::path2url($folder . $preview_name);
				$player_url = plugin_dir_url(__FILE__) . 'strobe/StrobeMediaPlayback.swf';
				$flashvars ='src=' .urlencode($preview_url). '&autoPlay=true&verbose=true';

				$htmlCode .= '<h4>Preview '.$preview_name.'</h4>';

				$htmlCode .= '<object class="previewPlayer" width="480" height="360" type="application/x-shockwave-flash" data="' . $player_url . '"> <param name="movie" value="' . $player_url . '" /><param name="flashvars" value="' .$flashvars . '" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="wmode" value="direct" /></object>';
			}

			//list files
			$fileList = scandir($folder);

			$ignored = array('.', '..', '.svn', '.htaccess');

			$prefixL=strlen($prefix);

			//list by date
			$files = array();
			foreach ($fileList as $fileName)
			{

				if (in_array($fileName, $ignored)) continue;
				if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;
				if ($prefixL) if (substr($fileName,0,$prefixL) != $prefix) continue;

					$files[$fileName] = filemtime($folder . $fileName);
			}

			arsort($files);
			$fileList = array_keys($files);

			if (!$fileList) $htmlCode .=  "<div class='warning'>No matching videos found!</div>";
			else
			{
				$htmlCode .=
					'<script language="JavaScript">
function toggleImportBoxes(source, checkboxes_name) {
  var checkboxes = new Array();
  checkboxes = document.getElementsByName(checkboxes_name);
  for(var i=0, n=checkboxes.length; i<n; i++)
    checkboxes[i].checked = source.checked;
}
</script>';
				$htmlCode .=  "<table class='widefat videowhisperTable'>";
				$htmlCode .=  '<thead class=""><tr><th><input type="checkbox" onClick="toggleImportBoxes(this,\'importFiles[]\')" /></th><th>File Name</th><th>Preview</th><th>Size</th><th>Date</th></tr></thead>';

				$tN = 0;
				$tS = 0;

				foreach ($fileList as $fileName)
				{
					$fsize = filesize($folder . $fileName);
					$tN++;
					$tS += $fsize;

					$htmlCode .=  '<tr>';
					$htmlCode .= '<td><input type="checkbox" name="importFiles[]" value="' . $fileName .'"'. ($fileName==$preview_name?' checked':'').'></td>';
					$htmlCode .=  "<td>$fileName</td>";
					$htmlCode .=  '<td>';
					$link  = add_query_arg( array( 'playlist_import' => $prefix, 'import_preview' => $fileName), admin_url('admin.php?page=video-share-import') );

					$htmlCode .=  " <a class='ui small button' href='" . $link ."'>Play</a> ";
					echo '</td>';
					$htmlCode .=  '<td>' .  VWvideoShare::humanFilesize($fsize) . '</td>';
					$htmlCode .=  '<td>' .  date('jS F Y H:i:s', filemtime($folder  . $fileName)) . '</td>';
					$htmlCode .=  '</tr>';
				}
				$htmlCode .=  '<tr><td></td><td>'.$tN.' files</td><td></td><td>'.VWvideoShare::humanFilesize($tS).'</td><td></td></tr>';
				$htmlCode .=  "</table>";

			}
			return $htmlCode;

		}

		function importFilesCount($prefix, $extensions, $folder)
		{
			if (!file_exists($folder)) return '';

			$kS=$k=0;

			$fileList = scandir($folder);

			$ignored = array('.', '..', '.svn', '.htaccess');

			$prefixL=strlen($prefix);

			foreach ($fileList as $fileName)
			{

				if (in_array($fileName, $ignored)) continue;
				if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $extensions  )) continue;
				if ($prefixL) if (substr($fileName,0,$prefixL) != $prefix) continue;

					$k++;
				$kS+=filesize($folder . $fileName);
			}

			return $k . ' ('.VWvideoShare::humanFilesize($kS).')';
		}


		public static function importFile($path, $name, $owner, $playlists, $category = '', $tags = '', $description = '')
		{
			if ($owner=='') return "<br>Missing owner!";
			if (!$playlists) return "<br>Missing playlists!";

			$options = get_option( 'VWvideoShareOptions' );
			if (!VWvideoShare::hasPriviledge($options['shareList'])) return '<br>' . __('You do not have permissions to share videos!', 'video-share-vod');

			if (!file_exists($path)) return "<br>$name: File missing: $path";


			//handle one or many playlists
			if (is_array($playlists)) $playlist = sanitize_file_name(current($playlists));
			else $playlist = sanitize_file_name($playlists);

			if (!$playlist) return "<br>Missing playlist!";

			$htmlCode = '';

			//uploads/owner/playlist/src/file
			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $owner;
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $playlist;
			if (!file_exists($dir)) mkdir($dir);

			//$dir .= '/src';
			//if (!file_exists($dir)) mkdir($dir);

			if (!$ztime = filemtime($path)) $ztime = time();

			$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
			$newFile = md5(uniqid($owner, true))  . '.' . $ext;
			$newPath = $dir . '/' . $newFile;

			//$htmlCode .= "<br>Importing $name as $newFile ... ";

			if ($options['deleteOnImport'])
			{
				if (!rename($path, $newPath))
				{
					$htmlCode .= 'Rename failed. Trying copy ...';
					if (!copy($path, $newPath))
					{
						$htmlCode .= 'Copy also failed. Import failed!';
						return $htmlCode;
					}
					// else $htmlCode .= 'Copy success ...';

					if (!unlink($path)) $htmlCode .= 'Removing original file failed!';
				}
			}
			else
			{
				//just copy
				if (!copy($path, $newPath))
				{
					$htmlCode .= 'Copy failed. Import failed!';
					return $htmlCode;
				}
			}

			//$htmlCode .= 'Moved source file ...';
			$timeZone = get_option('gmt_offset') * 3600;
			$postdate = date("Y-m-d H:i:s", $ztime + $timeZone);

			$post = array(
				'post_name'      => $name,
				'post_title'     => $name,
				'post_author'    => $owner,
				'post_type'      => $options['custom_post'],
				'post_status'    => 'publish',
				'post_date'   => $postdate,
				'post_content'   => $description
			);

			if (!VWvideoShare::hasPriviledge($options['publishList']))
				$post['post_status'] = 'pending';

			$post_id = wp_insert_post( $post);
			if ($post_id)
			{
				update_post_meta( $post_id, 'video-source-file', $newPath );

				wp_set_object_terms($post_id, $playlists, $options['custom_taxonomy']);

				if ($tags) wp_set_object_terms($post_id, $tags, 'post_tag');

				if ($category) wp_set_post_categories($post_id, array($category));

				VWvideoShare::updateVideo($post_id, true);
				VWvideoShare::updatePostThumbnail($post_id, true);
				//VWvideoShare::convertVideo($post_id, true);

				if ($post['post_status'] == 'pending') $htmlCode .= __('Video was submitted and is pending approval.','video-share-vod');
				else
					$htmlCode .= '<br>' . __('Video was published', 'video-share-vod') . ': <a href='.get_post_permalink($post_id).'> #'.$post_id.' '.$name.'</a> <br>' . __('Snapshot, video info and thumbnail will be processed shortly.', 'video-share-vod') ;
			}
			else $htmlCode .= '<br>Video post creation failed!';

			return $htmlCode;
		}

		//! Admin Area
		/* Meta box setup function. */
		function post_meta_boxes_setup() {
			/* Add meta boxes on the 'add_meta_boxes' hook. */
			add_action( 'add_meta_boxes', array( 'VWvideoShare', 'add_post_meta_boxes' ) );

			/* Update post meta on the 'save_post' hook. */
			add_action( 'save_post', array( 'VWvideoShare', 'save_post_meta'), 10, 2);

		}


		/* Create one or more meta boxes to be displayed on the post editor screen. */
		function add_post_meta_boxes() {

			add_meta_box(
				'video-post',      // Unique ID
				esc_html__( 'Video Post' ),    // Title
				array( 'VWvideoShare', 'post_meta_box'),   // Callback function
				'video',         // Admin page (or post type)
				'normal',         // Context
				'high'         // Priority
			);


		}

		/* Display the post meta box. */
		function post_meta_box( $object, $box ) {
?>
 <p>This is a special post type: In backend, videos can be uploaded from Video Share VOD > Upload menu or imported from Video Share VOD > Import menu, if files are already on server.
	 <br>Videos can also be added (uploaded or imported) from frontend if sections are setup (see <a href="https://videosharevod.com/features/quick-start-tutorial/">Setup Tutorial</a> and <a href="admin.php?page=video-share-docs">Plugin Documentation</a>).
	 <br>Custom fields are automatically generated and updated by the plugin. Do not alter custom fields manually as this can result in unexpected behaviour.
  </p>
<?php

		}


		function save_post_meta( $post_id, $post )
		{

			$options = get_option( 'VWvideoShareOptions' );

			//tv show : setup seasons
			if ($post->post_type == $options['tvshows_slug'])
			{
				$meta_value = get_post_meta( $post_id, 'tvshow-seasons', true );
				if (!$meta_value)
				{
					update_post_meta( $post_id, 'tvshow-seasons', '1');
					$meta_value = 1;
				}

				if ($post->post_title)
				{
					if (!term_exists($post->post_title, $options['custom_taxonomy']))
					{
						$args = array( 'description' => 'TV Show: ' . $post->post_title);
						wp_insert_term($post->post_title, $options['custom_taxonomy']);
					}

					$term = get_term_by('name', $post->post_title, $options['custom_taxonomy']);

					if ($meta_value>1) for ($i=1; $i<=$meta_value; $i++)
						if (!term_exists($post->post_title . ' ' . $i, $options['custom_taxonomy']))
						{
							$args = array('parent' => $term->term_id, 'description' => 'TV Show: ' . $post->post_title);

							wp_insert_term($post->post_title . ' ' . $i, $options['custom_taxonomy'], $args);

						}
				}
			}

		}

		//! Admin Videos
		function columns_head_video($defaults) {
			$defaults['featured_image'] = 'Thumbnail';
			$defaults['duration'] = 'Duration &amp; Info';

			return $defaults;
		}

		function columns_register_sortable( $columns ) {
			$columns['duration'] = 'duration';

			return $columns;
		}

		function admin_head() {
			echo '<style type="text/css">
        .column-featured_image { text-align: left; width:240px !important; overflow:hidden }
        .column-duration { text-align: left; width:160px !important; overflow:hidden }
    </style>';
		}


		function columns_content_video($column_name, $post_id)
		{

			if ($column_name == 'featured_image')
			{

				$post_thumbnail_id = get_post_thumbnail_id($post_id);

				if ($post_thumbnail_id)
				{

					$post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');

					if ($post_featured_image)
					{
						//correct url

						$upload_dir = wp_upload_dir();
						$uploads_url = VWvideoShare::path2url($upload_dir['basedir']);

						$iurl = $post_featured_image[0];
						$relPath = substr($iurl,strlen($uploads_url));

						if (file_exists($relPath)) $rurl = VWvideoShare::path2url($relPath);
						else $rurl = $iurl;

						echo '<img src="' . $rurl . '" />';
					} else echo 'No image  for '.$post_thumbnail_id;


					$url  = add_query_arg( array( 'updateThumb'  => $post_id), admin_url('admin.php?page=video-manage') );
					echo '<br><a href="'.$url.'">' . __('Update Thumbnail', 'video-share-vod') . '</a>';


				}
				else
				{
					echo 'Generating ... ';
					VWvideoShare::updatePostThumbnail($post_id);

				}
			}

			if ($column_name == 'duration')
			{

				$videoDuration = get_post_meta($post_id, 'video-duration', true);
				if ($videoDuration)
				{
					echo 'Duration: ' . VWvideoShare::humanDuration($videoDuration);
					echo '<br>Resolution: ' . get_post_meta($post_id, 'video-width', true). 'x' . get_post_meta($post_id, 'video-height', true);
					echo '<br>Source Size: ' . VWvideoShare::humanFilesize(get_post_meta($post_id, 'video-source-size', true));
					echo '<br>Total Space: ' . VWvideoShare::humanFilesize(VWvideoShare::spaceVideo($post_id));
					echo '<br>Bitrate: '. get_post_meta($post_id, 'video-bitrate', true) . ' kbps';

					echo '<br>Codecs: ' . ($codec = get_post_meta($post_id, 'video-codec-video', true)) . ', ' . get_post_meta($post_id, 'video-codec-audio', true);

					if (!$codec) VWvideoShare::updateVideo($post_id, true);
					echo '<br>Files: ';

					$videoPath = get_post_meta($post_id, 'video-source-file', true);
					if (file_exists($videoPath)) echo ' <a href="' . VWvideoShare::path2url($videoPath) . '">source</a> ' ;

					$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);
					if ($videoAdaptive) $videoAlts = $videoAdaptive;
					else $videoAlts = array();

					foreach ($videoAlts as $alt)
						if (file_exists($alt['file'])) echo '<br> - <a href="' . VWvideoShare::path2url($alt['file']) . '">' . $alt['id'] . '</a> (' . $alt['bitrate'] . ' kbps)';
						else echo $alt['id'] . '.. ';

						$url  = add_query_arg( array( 'updateInfo'  => $post_id), admin_url('admin.php?page=video-manage') );
					$url2 = add_query_arg( array( 'convert'  => $post_id), admin_url('admin.php?page=video-manage') );
					$url3 = add_query_arg( array( 'troubleshoot'  => $post_id), admin_url('admin.php?page=video-manage') );

					echo '<br><a href="'.$url.'">' . __('Update', 'video-share-vod') . '</a> ';
					echo '| <a href="'.$url2.'">' . __('Convert Again', 'video-share-vod') . '</a> ';
					echo '| <a href="'.$url3.'">' . __('Troubleshoot', 'video-share-vod') . '</a>';

				}
				else
				{
					echo 'Retrieving Info...';

					$videoPath = get_post_meta($post_id, 'video-source-file', true);
					if (!$videoPath) echo '<BR>Error: This entry has no video-source-file!';
					elseif (!file_exists($videoPath)) echo '<BR>Error: Video file missing: ' . $videoPath;
					elseif (!filesize($videoPath)) echo '<BR>Error: Video file is empty (filesize 0): ' . $videoPath;

					VWvideoShare::updateVideo($post_id, true);
				}

			}

		}

		public static function parse_query($query)
		{
			/*
			global $pagenow;

			if (is_admin() && $pagenow=='edit.php' && isset($_GET['post_type']) && $_GET['post_type']=='video')
			{
			}
			*/
		}

		function duration_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'duration' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
						'meta_key' => 'video-duration',
						'orderby' => 'meta_value_num'
					) );
			}

			return $vars;
		}

		function adminConversion()
		{

			$options = get_option( 'VWvideoShareOptions' );

			if (isset($_GET['cancelConversions']))
			{
				$options['convertQueue'] = '';
				update_option('VWvideoShareOptions', $options);
			}
?>
				<div class="wrap">
<?php screen_icon(); ?>
		<h2>Conversions - Video Share / Video on Demand (VOD)</h2>

<h4><?php _e('Conversion Queue','video-share-vod'); ?></h4>
<textarea name="convertQueue_" id="convertQueue" readonly="readonly" cols="120" rows="12"><?php echo $options['convertQueue']?></textarea>
<BR><?php


			if ($options['convertQueue'])
			{
				$cmds = explode("\r\n", $options['convertQueue']);
				if (count($cmds)) echo 'Conversions in queue: '. (count($cmds));
				echo ' <a href="'. get_permalink() . 'admin.php?page=video-share-conversion&cancelConversions=1'.'">Cancel Conversions</a>' ;
			}
			else echo 'No conversions in queue.';

			VWvideoShare::convertProcessQueue(1);
			echo '<BR>Next automated check (WP Cron, 4 min or more depending on site activity): in ' . ( wp_next_scheduled( 'cron_4min_event') - time()) . 's';

			$lastCheck = VWvideoShare::timeToGet('processQueue', $options);
			if ($lastCheck)  echo '<BR>Last Check: ' . (time() - $lastCheck) . 's ago';

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);
			$lastConversionPath = $uploadsPath . '/lastConversion.txt';

			$lastConversion = VWvideoShare::varLoad($lastConversionPath);

			if ($lastConversion) echo '<p>Last Conversion Command:' . $lastConversion['command'] . '<BR>' . $lastConversion['time'].'</p>' ;

?>
<h3><?php _e('Troubleshooting'); ?></h3>
<br>For a quick, hassle free setup, see <a href="https://videosharevod.com/hosting/" target="_vsvhost">VideoShareVOD turnkey managed hosting plans</a> for business video hosting, from $20/mo, including plugin installation, configuration.</p>

This section should aid in troubleshooting conversion issues.
<?php
			$fexec=0;

			echo "<BR>exec: ";
			if(function_exists('exec'))
			{
				echo "function is enabled";

				if(exec('echo EXEC') == 'EXEC')
				{
					echo ' and works';
					$fexec =1;
				}
				else echo ' <b>but does not work</b>';

			}else echo '<b>function is not enabled</b><BR>PHP function "exec" is required to run FFMPEG. Current hosting settings are not compatible with this functionality.';

			if ($fexec)
			{

				echo "<BR>FFMPEG: ";
				$cmd =$options['ffmpegPath'] . ' -version';
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
				{
					echo "detected";
					echo '<BR>' . $output[0];
					echo '<BR>' . $output[1];
				}

				$cmd =$options['ffmpegPath'] . ' -codecs';
				exec($cmd, $output, $returnvalue);

				//detect codecs
				if ($output) if (count($output))
					{
						echo "<br>Codec libraries:";
						foreach (array('h264', 'vp6','vp8', 'vp9', 'speex', 'nellymoser', 'opus', 'h263', 'mpeg', 'mp3', 'fdk_aac', 'faac') as $cod)
						{
							$det=0; $outd="";
							echo "<BR>$cod : ";
							foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };
							if ($det) echo "detected ($outd)"; else echo "<b>missing: configure and install FFMPEG with lib$cod if you don't have another library for that codec and need it for input or output</b>";
						}
					}
?>
<BR>You need only 1 AAC codec. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).

<?php
			}
?>
<h4><?php _e('CloudLinux Shared Hosting Requirements'); ?></h4>
CPU Speed: FFMPEG will be called with "-threads 1" to use just 1 thread (meaning 100% of 1 cpu core). That means on cloud limited environments account will need at least 100% CPU speed (to use at least 1 full core) to run conversions.
<BR>Memory: Depending on settings, conversions can fail with "x264 [error]: malloc" error if memory limit does not permit doing conversion. While "mobile" conversion can usually be done with 512Mb memory limit, for "high" quality settings (HD) 768Mb or more would be needed.

<h4><?php _e('System Process Limitations'); ?></h4>
User limits can prevent conversions. Setting cpu limit to 7200 to prevent early termination:<br>
<?php
			if ($fexec)
			{
				$cmd = 'ulimit -t 7200; ulimit -a';
				$output ='';
				exec($cmd, $output, $returnvalue);
				foreach ($output as $outp) echo $outp.'<br>';
			}
			else echo "Not functional without exec.";
		}

		function adminUpload()
		{
?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Video Share / Video on Demand (VOD)</h2>
		<?php
			echo do_shortcode("[videowhisper_upload]");
?>
		Use this page to upload one or multiple videos to server. Configure category, playlists and then choose files or drag and drop files to upload area.
		<br>Playlist(s): Assign videos to multiple playlists, as comma separated values. Ex: subscriber, premium
		<p><a target="_blank" href="https://videosharevod.com/features/video-uploader/">About Video Uploader ...</a></p>
		</div>

<h3>Troubleshoot Uploads</h3>
PHP Limitations (for a request, script call):
<BR>post_max_size: <?php echo ini_get('post_max_size') ?>
<BR>upload_max_filesize: <?php echo ini_get('upload_max_filesize') ?> - The maximum size of an uploaded file.
<BR>memory_limit: <?php echo ini_get('memory_limit') ?> - This sets the maximum amount of memory in bytes that a script is allowed to allocate. This helps prevent poorly written scripts for eating up all available memory on a server.
<BR>max_execution_time: <?php echo ini_get('max_execution_time') ?> - This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser. This helps prevent poorly written scripts from tying up the server. The default setting is 90.
<BR>max_input_time: <?php echo ini_get('max_input_time') ?>  - This sets the maximum time in seconds a script is allowed to parse input data, like POST, GET and file uploads.

<p>mod_security: Uploads may be prevented by this rule (that can be disabled) - 920420: Request content type is not allowed by policy	</p>
<p>For adding big videos, best way is to upload by FTP and use <a href="admin.php?page=video-share-import">Import</a> feature. Trying to upload big files by HTTP (from web page) may result in failure due to request limitations, timeouts depending on client upload connection speed.</p>

		<?php
		}

		function adminStats()
		{
			$options = get_option( 'VWvideoShareOptions' );

?>
		<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Video Statistics</h2>
		<?php

			$post_count = wp_count_posts($options['custom_post']);

			echo '<h3>Video Count</h3>';
			foreach ($post_count as $key => $value)
				echo '<BR>'.$key.' : <a href="edit.php?post_type=' . $options['custom_post']. '&post_status=' .$key. '">' . $value . '</a>';

			echo '<h3>Video Space Usage</h3>';

			function get_meta_values( $key = '', $type = 'video') {
				global $wpdb;
				if( empty( $key ) ) return;
				$r = $wpdb->get_col( $wpdb->prepare( "
        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = '%s'
        AND p.post_type = '%s'
    ", $key, $type ) );
				return $r;
			}

			$metas = get_meta_values('space-statistics', $options['custom_post']);

			echo 'Stats available for: ' . count($metas) . ' videos';
			$totalStats = array();

			foreach ($metas as $meta)
			{
				$spaceStats = unserialize($meta);
				foreach ($spaceStats as $key => $value)
				{
					$totalStats['total'] += $value;
					$totalStats[$key] += $value;
				}
			}

			echo '<BR>Space used by videos: ';
			foreach ($totalStats as $key => $value) echo '<BR>'.$key .': '. VWvideoShare::humanFilesize($value);


			echo '<h3>Content Folder Space Usage</h3>';

			if (file_exists($options['uploadsPath']))
				echo 'VideoShareVOD (' .$options['uploadsPath']. '): ' . VWvideoShare::humanFilesize(VWvideoShare::sizeTree($options['uploadsPath']));


			if (file_exists($options['importPath']))
				echo '<BR>Import (' .$options['importPath']. '): ' . VWvideoShare::humanFilesize(VWvideoShare::sizeTree($options['importPath']));

			if (file_exists($options['streamsPath']))
				echo '<BR>Streams (' .$options['streamsPath']. '): ' . VWvideoShare::humanFilesize(VWvideoShare::sizeTree($options['streamsPath']));


?>
			<h3>Video Space Tools</h3>
			+ <a href="admin.php?page=video-manage&updateSpace=1">Calculate Current Space Usage for All Videos</a>

		<BR>+ <a href="admin.php?page=video-manage&clean=source">Delete Sources</a> (not recommended, required to generate/update conversions and snapshots)
		<BR> + <a href="admin.php?page=video-manage&clean=logs">Delete Logs</a> (required to troubleshoot)
		<BR> + <a href="admin.php?page=video-manage&clean=hls">Delete HLS Segments</a> (required for web HLS playback, can be re-generated from source)

		<?php
		}

		function sizeTree($dir) {
			$files = array_diff(scandir($dir), array('.','..'));

			$space = 0;
			foreach ($files as $file) {
				$space += (is_dir("$dir/$file")) ? VWvideoShare::sizeTree("$dir/$file") : filesize("$dir/$file");
			}
			return $space;
		}


		function spaceVideo($post_id)
		{
			//calculate statistics for video

			if (!$post_id) return;
			$options = get_option( 'VWvideoShareOptions' );

			$spaceStatistics = array();

			//source
			$space = 0;
			$videoPath = get_post_meta($post_id, 'video-source-file', true);
			if (file_exists($videoPath)) $space = filesize($videoPath);
			$spaceStatistics['source'] = $space;

			//all generated video files
			$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();

			$space = 0;
			foreach ($videoAlts as $alt)
			{
				if (file_exists($alt['file'])) $spaceStatistics[$alt['id']] = filesize($alt['file']);

				$logpath = dirname($alt['file']);
				$log = $logpath . '/' . $post_id . '-' . $alt['id'] . '.txt';
				$logc = $logpath . '/' . $post_id . '-' . $alt['id'] . '-cmd.txt';
				$spaceStatistics[$alt['id'].'_logs'] = 0;
				if (file_exists($log)) $spaceStatistics[$alt['id'].'_logs'] += filesize($log);
				if (file_exists($logc)) $spaceStatistics[$alt['id'].'_logs'] += filesize($logc);

				if (file_exists($alt['file']))     //hls space
					if ($alt['hls']) if (strstr($alt['hls'], $options['uploadsPath']))
						{
							if (file_exists($alt['hls'])) if (is_dir($alt['hls']))
								{
									$spaceStatistics[$alt['id'].'_hls'] =  VWvideoShare::sizeTree($alt['hls']);
								}
						}
			}

			update_post_meta( $post_id, 'space-statistics', $spaceStatistics );

			$spaceTotal = 0;
			foreach ($spaceStatistics as $value) $spaceTotal += $value;

			update_post_meta( $post_id, 'space-total', $spaceTotal );

			//var_dump($spaceStatistics);

			return $spaceTotal;
		}


		function troubleshootVideo($post_id)
		{
			$post = get_post($post_id);
			echo '<H4>Troubleshooting: '.$post->post_title.'</H4>';

			$videoAdaptive = get_post_meta($post_id, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();
			foreach ($videoAlts as $id => $alt)
			{
				echo '<BR><B>'.$id.'</B>';
				echo '<BR>+ Conversion file ';
				if (file_exists($alt['file'])) echo 'exists'; else echo 'does NOT exist';
				echo ': ' . $alt['file'];
				if (file_exists($alt['file'])) echo ' Size: ' . filesize($alt['file']);

				echo '<BR>+ Conversion log ';
				if (file_exists($alt['log'])) echo 'exists <br><textarea cols=100 rows=5>' .file_get_contents($alt['log']). '</textarea>' ;
				else echo 'does NOT exist';

				echo '<BR>+ Conversion command: ' . $alt['cmd'];
			}
		}

		function adminManage()
		{

			$options = get_option( 'VWvideoShareOptions' );

?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Manage Videos</h2>
		<?php

			if ( $clean = sanitize_file_name($_GET['clean']))
			{

				echo 'Cleaning video files. Finding posts older than 3 days (to avoid processing of unconverted posts) ...';

				global $wpdb;
				$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM  {$wpdb->posts} WHERE post_type = '%s'AND post_date < NOW() - INTERVAL 3 DAY",  $options['custom_post'] ) );

				echo '<BR>Videos to clean for: ' . count($ids);

				$confirm = $_GET['confirm'];

				echo "<BR>$confirm ";
				foreach ($ids as $post_id)
				{
					$value += VWvideoShare::cleanVideo($post_id, $clean, $confirm, $options);
					echo ' .';
				}

				echo '<BR>Total clean space: ' .  VWvideoShare::humanFilesize($value);

				if ($confirm) echo '<BR>Successfully cleaned!';
				else echo '<BR>Are you sure you want to delete these files?<BR>This is not reversible: <a class="button" href="admin.php?page=video-manage&clean='.$clean.'&confirm=1">Confirm Deletion</a>';
			}

			if ( $_GET['updateSpace'])
			{
				echo '<BR>Calculating space usage for all videos...';
				global $wpdb;
				$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM  {$wpdb->posts} WHERE post_type = '%s'",  $options['custom_post'] ) );

				echo '<BR>Videos to calculate for: ' . count($ids);

				foreach ($ids as $post_id)   $value += VWvideoShare::spaceVideo($post_id);

				echo '<BR>Total space usage calculated: ' .  VWvideoShare::humanFilesize($value);
				echo '<BR>See current <a href="admin.php?page=video-stats">Full Space Usage Statistics</a>';
			}

			if ( $update_id = (int) $_GET['updateInfo'])
			{
				echo '<BR>Updating Video #' .$update_id. '... <br>';
				VWvideoShare::updateVideo($update_id, true, true);
				unset($_GET['updateInfo']);

			}

			if ( $update_id = (int) $_GET['updateThumb'])
			{
				echo '<BR>Updating Thumbnail for Video #' .$update_id. '... <br>';
				VWvideoShare::updatePostThumbnail($update_id, true, true);
				unset($_GET['updateThumb']);
			}

			if ( $update_id = (int) $_GET['convert'])
			{
				echo '<BR>Converting Video #' .$update_id. ' - overwriting previous conversions... <br>';
				VWvideoShare::convertVideo($update_id, true, true);
				$url3 = add_query_arg( array( 'troubleshoot'  => $update_id), admin_url('admin.php?page=video-manage') );
				echo '<BR> - <a href="'.$url3.'">' . __('Troubleshoot Video Conversions', 'video-share-vod') . '</a>';

				unset($_GET['convert']);
			}

			if ( $troubleshoot_id = (int) $_GET['troubleshoot'])
			{
				echo '<BR>Troubleshooting Video #' .$troubleshoot_id. '... <br>';
				VWvideoShare::troubleshootVideo($troubleshoot_id);
				unset($_GET['troubleshoot']);
			}
?>
		<h3>Video Information</h3>

		<BR>+ Review how conversions progress in the <a href="admin.php?page=video-share-conversion">Conversions Queue</a>.

		<BR>+ Individual videos can be managed from <a href="edit.php?post_type=<?php echo $options['custom_post']; ?>">Videos</a> section (options to convert again, troubleshoot conversions available). Browsing videos updates space usage calculations.
		<BR>+ Current space usage statistics is available in <a href="admin.php?page=video-stats">Statistics</a> section.

		<h3>Clean Videos</h3>
		+ <a href="admin.php?page=video-manage&clean=source">Delete Sources</a> (not recommended, required to generate/update conversions and snapshots)
		<BR> + <a href="admin.php?page=video-manage&clean=logs">Delete Logs</a> (required to troubleshoot)
		<BR> + <a href="admin.php?page=video-manage&clean=hls">Delete HLS Segments</a> (required for web HLS playback, can be re-generated from source)

<?php
		}

		//! Documentation
		function adminDocs()
		{
			
				$options = get_option( 'VWvideoShareOptions' );

?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Video Share / Video on Demand (VOD)</h2>
		<h3>External Documentation</h3>
		 	  + <a href="https://videosharevod.com/features/quick-start-tutorial/">Setup Tutorial</a>
		 <BR> + <a href="https://videosharevod.com/hosting/">Hosting Requirements and Options</a>
		 <BR> + <a href="https://www.videowhisper.com/?p=VideoWhisper+Script+Installation">Paid Installation</a> (on compatible hosting)
		 <BR> + <a href="https://www.videowhisper.com/tickets_submit.php">Contact Support</a> (for clarifications, custom development)


<h3>Installation Overview</h3>

<PRE>	
- Site visitors can browse videos at:
<?php echo get_permalink($options['p_videowhisper_videos']);?>
 
- Site users can upload videos from:
<?php echo get_permalink($options['p_videowhisper_upload']);?>


- Setup site categories from:
<?php echo  admin_url(); ?>edit-tags.php?taxonomy=category&post_type=video

- Configure listings from:
<?php echo  admin_url(); ?>admin.php?page=video-share&tab=listings

- Customize further as described at:
https://videosharevod.com/features/quick-start-tutorial/#customize

- Upgrade to more advanced features (software and hosting capabilities) with higher plans from:
https://videosharevod.com/turnkey-site/
</PRE>

		<h3>Shortcodes</h3>

		<h4>[videowhisper_videos playlist="" category_id="" order_by="" perpage="" perrow="" select_category="1" select_order="1" select_page="1" include_css="1" id=""]</h4>
		Displays video list. Loads and updates by AJAX. Optional parameters: video playlist name, maximum videos per page, maximum videos per row.
		<br>order_by: post_date / video-views / video-lastview
		<br>select attributes enable controls to select category, order, page
		<br>include_css: includes the styles (disable if already loaded once on same page)
		<br>id is used to allow multiple instances on same page (leave blank to generate)

		<h4>[videowhisper_upload playlist="" category="" owner=""]</h4>
		Displays interface to upload videos.
		<br>playlist: If not defined owner name is used as playlist for regular users. Admins with edit_users capability can write any playlist name. Multiple playlists can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

	   <h4>[videowhisper_import path="" playlist="" category="" owner=""]</h4>
		Displays interface to import videos.
		<br>path: Path where to import from.
		<br>playlist: If not defined owner name is used as playlist for regular users. Admins with edit_users capability can write any playlist name. Multiple playlists can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

		<h4>[videowhisper_player video="0" player="" width=""]</h4>
		Displays video player. Video post ID is required.
		<br>Player: html5/html5-mobile/strobe/strobe-rtmp/html5-hls/ blank to use settings & detection
		<br>Width: Force a fixed width in pixels (ex: 640) and height will be adjusted to maintain aspect ratio. Leave blank to use video size.

		<h4>[videowhisper_preview video="0"]</h4>
		Displays video preview (snapshot) with link to video post. Video post ID is required.
		Used to display VOD inaccessible items.


		<h4>[videowhisper_playlist name="playlist-name"]</h4>
		Displays playlist player.


		<h4>[videowhisper_player_html source="" source_type="" source_alt="" source_alt_type="" poster="" width="" height="" player=""]</h4>
		Displays configured HTML5 player for a specified video source.
		<br>Player: native/wordpress/video-js leave blank to use settings & detection
		<br>source_alt, source_alt_type for multi bitrate source & type like m3u8 supported by videojs
		<br>Ex. [videowhisper_player_html source="http://test.com/test.mp4" type="video/mp4" poster="http://test.com/test.jpg"]

		<h4>[videowhisper_embed_code source="" source_type="" poster="" width="" height=""]</h4>
		Displays html5 embed code.

	<h4>[videowhisper_postvideos post="post id"]</h4>
		Manage post associated videos. Required: post

	<h4>[videowhisper_postvideos_process post="" post_type=""]</h4>
		Process post associated videos (needs to be on same page with [videowhisper_postvideos] for that to work).

	<h4>[videowhisper_postvideo_assign post_id="" meta="video_teaser" content="id" show="1" showWidth="320"]</h4>
	Displays a form to select a video for a post and also shows current setting (including video player if "show" enabled).
	<br>meta: meta name that will contain the video info
	<br>content: id / video_path / preview_path
	<br>show: 1 / 0
	<br>showWidth: width of player in pixels

		<h3>Troubleshooting</h3>
		+ Check FFMPEG installation and codecs in <a href="admin.php?page=video-share&tab=server">server tab</a>.
		<br>+ Troubleshoot conversions in <a href="admin.php?page=video-share-conversion">conversions tab</a>.
		<br>+ Configure conversions in <a href="admin.php?page=video-share&tab=convert">conversions settings tab</a>.

		<br>+ If playlists don't show up right on your theme, copy taxonomy-playlist.php from this plugin folder to your theme folder.
		<h3>More...</h3>
		Read more details about <a href="https://videosharevod.com/features/">available features</a> on <a href="https://videosharevod.com/">official plugin site</a> and <a href="https://videowhisper.com/tickets_submit.php">contact us</a> anytime for questions, clarifications.
		</div>
		<?php
		}

		//! Settings

		function adminOptionsDefault()
		{
			$root_url = get_bloginfo( "url" ) . "/";
			$upload_dir = wp_upload_dir();

			return array(

				'interfaceClass' => '',
				'userName' => 'user_nicename',

				'rateStarReview' => '1',
				'order_by' => 'post_date',

				'editURL' => $root_url . 'edit-content?editID=',
				'editContent' => 'all',
				'allowDebug' => '1',

				'disableSetupPages' => '0',
				'vwls_playlist' => '1',

				'vwls_archive_path' =>'/home/youraccount/public_html/streams/',
				'importPath' => '/home/youraccount/public_html/streams/',

				'exportPath' => '/home/youraccount/public_html/download/',
				'exportCount' => '500',
				'exportOffset' => '0',

				'importClean' => '45',
				'deleteOnImport' => '1',

				'vwls_channel' => '1',
				'ffmpegPath' => '/usr/local/bin/ffmpeg',
				'ffmpegConfiguration' => '1',

				'convertPreviewInput' => '-ss 5',
				'convertPreviewOutput' => '-t 10',

				'codecVideoPreview' => '-vcodec libx264 -movflags +faststart -profile:v baseline -level 3.1',
				'codecVideoMobile' => '-vcodec libx264 -movflags +faststart -profile:v baseline -level 3.1',
				'codecVideoHigh' => '-vcodec libx264 -profile:v main -level 3.1',

				'codecAudioPreview' => '-acodec libfaac -ac 2 -ab 64k',
				'codecAudioMobile' => '-acodec libfaac -ac 2 -ab 64k',
				'codecAudioHigh' => '-acodec libfaac -ac 2 -ab 128k',

				'bitrateHD' => '8192',
				'convertSingleProcess' => '0',
				'convertQueue' => '',
				'convertInstant' => '0',
				'convertMobile' => '1',
				'convertHigh' => '2',
				'convertHLS' => '0',
				'convertPreview' => '1',
				'convertWatermark' => '',
				'convertWatermarkPosition' => 'main_w-overlay_w-4:4',
				'originalBackup' => '1',

				'custom_post' => 'video',
				'custom_taxonomy' => 'playlist',

				'postTemplate' => '+plugin',
				'playlistTemplate' => '+plugin',

				'videoWidth' => '',

				'player_default' => 'html5',
				'html5_player' => 'video-js',
				'player_ios' => 'html5-mobile',
				'player_safari' => 'html5',
				'player_android' => 'html5-mobile',
				'player_firefox_mac' =>'html5',
				'playlist_player' => 'video-js',

				'thumbWidth' => '240',
				'thumbHeight' => '180',
				'perPage' =>'6',
				'perRow' =>'0',

				'playlistVideoWidth' => '960',
				'playlistListWidth' => '350',

				'shareList' => 'Super Admin, Administrator, Editor, Author, Contributor, Performer, Broadcaster',
				'publishList' => 'Super Admin, Administrator, Editor, Author, Performer, Broadcaster',
				'embedList' => 'None',

				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber, Performer, Broadcaster, Client, Guest',
				'accessDenied' => '<h3>Access Denied</h3>
<p>#info#</p>',
				'vod_role_playlist' => '1',
				'vastLib' => 'iab',
				'vast' => '',
				'adsGlobal' => '0',
				'premiumList' => '',
				'tvshows' => '1',
				'tvshows_slug' => 'tvshow',
				'uploadsPath' => $upload_dir['basedir'] . '/vw_videoshare',
				'rtmpServer' => 'rtmp://your-site.com/videowhisper-x2',
				'streamsPath' =>'/home/youraccount/public_html/streams/',
				'hlsServer' =>'http://your-site.com:1935/videowhisper-x2/',
				'containerCSS' => '<style type="text/css">
.videowhisperPlayerContainer
{
background-color: #000000;
-webkit-align-content: center;
align-content: center;
text-align: center;
margin-left: auto;
margin-right: auto;
display: block;
}
</style>',
				
				'customCSS' => <<<HTMLCODE
<style type="text/css">

.videowhisperVideoEdit
{
position: absolute;
top:34px;
right:0px;
margin:8px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
background: rgba(0, 100, 255, 0.7);
padding: 3px;
border-radius: 3px;
z-index: 10;
}

.videowhisperVideo
{
position: relative;
display:inline-block;

border:1px solid #aaa;
background-color:#777;
padding: 0px;
margin: 2px;

width: 240px;
height: 180px;
overflow: hidden;
z-index: 0;
}

.videowhisperVideo:hover {
	border:1px solid #fff;
}

.videowhisperVideo IMG
{
position: absolute;
left:0px;
top:0px;
padding: 0px;
margin: 0px;
border: 0px;
z-index: 1;
}

.videowhisperVideo VIDEO
{
position: absolute;
left:0px;
top:0px;
padding: 0px;
margin: 0px;
border: 0px;
z-index: 1;
}


.videowhisperVideoTitle
{
position: absolute;
top:0px;
left:0px;
right:40px;
margin:5px;
font-size: 12px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}

.videowhisperVideoRating
{
position: absolute;
bottom: 25px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}

.videowhisperVideoDuration
{
position: absolute;
bottom:0px;
left:0px;
margin:5px;
font-size: 12px;
color: #FFF;
text-shadow:1px 1px 1px #333;
background: rgba(30, 30, 30, 0.5);
padding: 2px;
border-radius: 4px;
z-index: 10;
}


.videowhisperVideoResolution
{
position: absolute;
top:0px;
right:0px;
margin:5px;
font-size: 12px;
color: #FFF;
text-shadow:1px 1px 1px #333;
background: rgba(255, 50, 0, 0.5);
padding: 2px;
border-radius: 4px;
z-index: 10;

}

.videowhisperVideoDate
{
position: absolute;
bottom:0px;
right:0px;
margin: 5px;
padding: 2px;
font-size: 10px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;

}

.videowhisperVideoViews
{
position: absolute;
bottom:10px;
right:0px;
margin: 5px;
padding: 2px;
font-size: 10px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}



</style>
HTMLCODE
				,
				'disableXOrigin' => '0',
				'disableXOriginRef' => '',
				'crossdomain_xml' =>'<cross-domain-policy>
<allow-access-from domain="*"/>
<site-control permitted-cross-domain-policies="master-only"/>
</cross-domain-policy>',

				'videowhisper' => '0'
			);

		}

		function setupOptions() {

			$adminOptions = VWvideoShare::adminOptionsDefault();

			$options = get_option('VWvideoShareOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWvideoShareOptions', $adminOptions);

			return $adminOptions;
		}



		function adminOptions()
		{
			$options = VWvideoShare::setupOptions();

			// if ($options['convertQueue']) $options['convertQueue'] = trim($options['convertQueue']);

			if (isset($_POST)) if (!empty($_POST))
			{
							
				$nonce = $_REQUEST['_wpnonce'];
				if ( ! wp_verify_nonce( $nonce, 'vwsec' ) ) 
				{
				echo 'Invalid nonce!';
				exit; 
				}	
					
					foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWvideoShareOptions', $options);
			}


			VWvideoShare::setupPages();

			$optionsDefault = VWvideoShare::adminOptionsDefault();

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'support';
?>


<div class="wrap">
<?php screen_icon(); ?>
<h2>Video Share / Video on Demand (VOD)</h2>
<h2 class="nav-tab-wrapper">
	<a href="admin.php?page=video-share&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>"><?php _e('Server','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=share" class="nav-tab <?php echo $active_tab=='share'?'nav-tab-active':'';?>"><?php _e('Video Share','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=convert" class="nav-tab <?php echo $active_tab=='convert'?'nav-tab-active':'';?>"><?php _e('Convert','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=listings" class="nav-tab <?php echo $active_tab=='listings'?'nav-tab-active':'';?>"><?php _e('Listings','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=players" class="nav-tab <?php echo $active_tab=='players'?'nav-tab-active':'';?>"><?php _e('Players','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=ls" class="nav-tab <?php echo $active_tab=='ls'?'nav-tab-active':'';?>"><?php _e('Live Streams','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=tvshows" class="nav-tab <?php echo $active_tab=='tvshows'?'nav-tab-active':'';?>"><?php _e('TV Shows','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=vod" class="nav-tab <?php echo $active_tab=='vod'?'nav-tab-active':'';?>"><?php _e('Membership VOD','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=vast" class="nav-tab <?php echo $active_tab=='vast'?'nav-tab-active':'';?>"><?php _e('VAST/IAB','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=reset" class="nav-tab <?php echo $active_tab=='reset'?'nav-tab-active':'';?>"><?php _e('Reset','video-share-vod'); ?></a>
	<a href="admin.php?page=video-share&tab=support" class="nav-tab <?php echo $active_tab=='support'?'nav-tab-active':'';?>"><?php _e('Support','video-share-vod'); ?></a>
</h2>

<form method="post" action="<?php echo wp_nonce_url($_SERVER["REQUEST_URI"], 'vwsec'); ?>">

<?php
			switch ($active_tab)
			{
			case 'support':
				//! Support
?>

<h3>Hosting Requirements</h3>
<UL>
<LI><a href="https://videosharevod.com/hosting/">Hosting Features Required</a> Video hosting specific feature required.
<LI><a href="http://videowhisper.com/?p=Video-Hosting-Business">Business Video Hosting</a> High volume hosting options.</LI>
</UL>

<h3>Solution Documentation</h3>
<UL>
<LI><a href="https://videosharevod.com/features/quick-start-tutorial/">VideoShareVOD Setup Tutorial</a> Tutorial to setup the Video Share VOD plugin.</LI>
<LI><a href="admin.php?page=video-share-docs">Backend Documentation</a> Includes documents shortcodes, external documentation links.</LI>
<LI><a href="https://videosharevod.com">VideoShareVOD Homepage</a> Solution site: features listing, snapshots, demos, downloads, suggestions.</LI>
<LI><a href="https://wordpress.org/plugins/video-share-vod/">WordPress Plugin</a> Plugin page on WordPress repository.</LI>
</UL>

<h3>Recommended Plugins</h3>
Here are some plugins that work in combination with VideShareVOD:
<UL>
<LI><a href="https://wordpress.org/plugins/video-posts-webcam-recorder/">Webcam Video Recorder</a> Site users can record videos from webcam. Can also be used to setup reaction recording: record webcam while playing an Youtube video.</LI>
<LI><a href="https://broadcastlivevideo.com">Broadcast Live Video</a> Broadcast live video channels from webcam, IP cameras, desktop/mobile encoder apps. Archive these videos, import and publish on site.</LI>
<LI><a href="https://paidvideochat.com">Paid Videochat</a> Run a turnkey pay per minute videochat site where performers can archive live shows or upload videos for their fans.</LI>
<LI><a href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content</a> Sell videos (per item) from frontend, sell membership subscriptions. Based on MyCred & TeraWallet/WooWallet (WooCommerce) tokens that can be purchased with real money gateways or earned on site.</LI>
</UL>


<h3>Premium Plugins / Addons</h3>
<ul>
	<LI><a href="http://themeforest.net/popular_item/by_category?category=wordpress&ref=videowhisper">Premium Themes</a> Professional WordPress themes.</LI>
	<LI><a href="https://woocommerce.com/?aff=18336&cid=1980980">WooCommerce</a> Free shopping cart plugin, supports multiple free and premium gateways with TeraWallet/WooWallet plugin and various premium eCommerce plugins.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&cid=1980980">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&cid=1980980">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&cid=1980980">WooCommerce Bookings</a> Let your customers book reservations, appointments on their own.</LI>

	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&cid=1980980">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>

	<LI><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> Automated WordPress backup plugin. Free for local storage. For production sites external backups are recommended (premium).</LI>
</ul>

<h3>Contact and Feedback</h3>
<a href="https://videowhisper.com/tickets_submit.php">Sumit a Ticket</a> with your questions, inquiries and VideoWhisper support staff will try to address these as soon as possible.
<br>Although the free license does not include any services (as installation and troubleshooting), VideoWhisper staff can clarify requirements, features, installation steps or suggest paid services like installations, customisations, hosting you may need for your project.

<h3>Review and Discuss</h3>
You can publicly <a href="https://wordpress.org/support/plugin/video-share-vod/reviews/#new-post">review this WP plugin</a> on the official WordPress site (after <a href="https://wordpress.org/support/register.php">registering</a>). You can describe how you use it and mention your site for visibility. You can also post on the <a href="https://wordpress.org/support/plugin/video-share-vod">WP support forums</a> - these are not monitored by support so use a <a href="https://videowhisper.com/tickets_submit.php">ticket</a> if you want to contact VideoWhisper.
<BR>If you like this plugin and decide to order a commercial license for VideoWhisper applications (Video Recorder, Live Streaming) or other services from <a href="http://videowhisper.com/">VideoWhisper</a>, use this coupon code for 5% discount: giveme5

<h3>News and Updates</h3>
You can also get connected with VideoWhisper and follow updates using <a href="https://twitter.com/videowhisper"> Twitter </a>, <a href="https://www.facebook.com/pages/VideoWhisper/121234178858"> Facebook </a>.


				<?php
				break;



			case 'reset':
?>
<h3><?php _e('Reset Options','video-share-vod'); ?></h3>
This resets some options to defaults. Useful when upgrading plugin and new defaults are available for new features and for fixing broken installations.
<?php

				$confirm = $_GET['confirm'];



				if ($confirm) echo '<h4>Resetting...</h4>';
				else echo '<p><A class="button" href="'.get_permalink().'admin.php?page=video-share&tab=reset&confirm=1">Yes, Reset These Settings!</A></p>';

				$resetOptions = array('customCSS', 'containerCSS', 'convertSingleProcess','convertInstant', 'custom_post', 'custom_taxonomy');

				foreach ($resetOptions as $opt)
				{
					echo '<BR> - ' . $opt;
					if ($confirm) $options[$opt] = $optionsDefault[$opt];
				}

				if ($confirm)  update_option('VWvideoShareOptions', $options);


				break;

			case 'convert':
				//! convert options
?>
<h3><?php _e('Video Conversions','video-share-vod'); ?></h3>
Configure video conversions. See current progress on <a href="admin.php?page=video-share-conversion">Conversions Page</a>.

<h4>Conversion Watermark</h4>
<input name="convertWatermark" type="text" id="convertWatermark" size="100" maxlength="256" value="<?php echo $options['convertWatermark']?>"/>
<BR>Add a floating watermark image over video (encoded in video when converting). Involves extra processing resources (CPU & memory) for conversions. Specify absolute path to image file on server (PNG recommended), not web URL. Leave blank to disable.
<br>
<?php
	echo 'Ex:' . plugin_dir_path(__FILE__) .'logo.png';
		
				if ($options['convertWatermark'])
				if (file_exists($options['convertWatermark'])) echo '<br>File found: ' . sanitize_text_field($options['convertWatermark']);
				else echo '<br>NOT Found: ' . sanitize_text_field($options['convertWatermark']);
?>
<h4>Conversion Watermark Position</h4>
<input name="convertWatermarkPosition" type="text" id="convertWatermarkPosition" size="100" maxlength="256" value="<?php echo $options['convertWatermarkPosition']?>"/>
<BR>Position for <a href="https://ffmpeg.org/ffmpeg-filters.html#overlay-1">overlay filter</a>. Default is 4px from top right corner: main_w-overlay_w-4:4


<h4><?php _e('Allow Original Video as Backup','video-share-vod'); ?></h4>
<select name="originalBackup" id="originalBackup">
  <option value="1" <?php echo ($options['originalBackup']=='1')?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['originalBackup']?"":"selected"?>>No</option>
</select>
<BR>Use original as backup playback solution if it's in appropriate format and no suitable conversion is available.
<BR>Viewer will see original video (without watermark).
<BR>This can be useful to make video accessible fast, before conversion is done, if available in suitable format.

<h4><?php _e('Convert to High HTML5 Format','video-share-vod'); ?></h4>
<select name="convertHigh" id="convertHigh">
  <option value="3" <?php echo ($options['convertHigh']=='3')?"selected":""?>>Always</option>
  <option value="2" <?php echo ($options['convertHigh']=='2')?"selected":""?>>Auto & Bitrate</option>
  <option value="1" <?php echo ($options['convertHigh']=='1')?"selected":""?>>Auto</option>
  <option value="0" <?php echo $options['convertHigh']?"":"selected"?>>No</option>
</select>
<BR>Convert video to high quality mp4 (h264,aac). This is required on most setups.
<BR><b>Auto</b> converts only if source is not mp4 and copies h264/aac tracks if available.
<BR><b>Auto & Bitrate</b>  converts if source is not mp4 and/or bitrate is higher that <a href="http://www.videochat-scripts.com/recommended-h264-video-bitrate-based-on-resolution/">recommended</a> (which could cause interruptions, buffering for users and high server bandwidth usage without major quality benefits).
<BR><b>Always</b> will convert anyway (and apply watermark if configured).

<h4>High HD Bitrate</h4>
<input name="bitrateHD" type="text" id="bitrateHD" size="12" maxlength="20" value="<?php echo $options['bitrateHD']?>"/>
<BR>Bitrate for 1920x1080 resolution. For other resolutions bitrate is adjusted proportional to number of pixels. Default: <?php echo $optionsDefault['bitrateHD']?>


<h4>High Video Codec</h4>
<input name="codecVideoHigh" type="text" id="codecVideoHigh" size="100" maxlength="256" value="<?php echo $options['codecVideoHigh']?>"/>
<BR>Bitrate is calculated depending on resolution (do not include in encoding parameters).
<BR>Ex: -vcodec libx264 -profile:v main -level 3.1


<h4>High Audio Codec</h4>
<input name="codecAudioHigh" type="text" id="codecAudioHigh" size="100" maxlength="256" value="<?php echo $options['codecAudioHigh']?>"/>
<BR>Ex.(latest FFMPEG with libfdk_aac): -c:a libfdk_aac -b:a 128k
<BR>Ex.(latest FFMPEG with native aac): -c:a aac -b:a 128k
<BR>Ex.(older FFMPEG with libfaac): -acodec libfaac -ac 2 -ar 44100 -ab 128k

<h4><?php _e('Convert to Mobile HTML5 Format','video-share-vod'); ?></h4>
<select name="convertMobile" id="convertMobile">
  <option value="2" <?php echo ($options['convertMobile']=='2')?"selected":""?>>Always</option>
  <option value="1" <?php echo ($options['convertMobile']=='1')?"selected":""?>>Auto</option>
  <option value="0" <?php echo $options['convertMobile']?"":"selected"?>>No</option>
</select>
<BR>Convert video to mobile quality mp4 (h264,aac). This is optional, for supporting older devices and low connection users.
<BR>Auto converts only if source is not mp4.
<BR>When targetting latest devices "high" format can be used for all players and "mobile" format disabled. When using multi bitrate (MBR) sources, mobile variant can be used on slow connections (like mobile connection with poor signal) to permit adaptive bitrate (ABR) playback.

<h4>Mobile Video Codec</h4>
<input name="codecVideoMobile" type="text" id="codecVideoMobile" size="100" maxlength="256" value="<?php echo $options['codecVideoMobile']?>"/>
<BR>Bitrate is calculated depending on resolution (do not include in encoding parameters).
<BR>Ex: -vcodec libx264 -movflags +faststart -profile:v baseline -level 3.1

<h4>Mobile Audio Codec</h4>
<input name="codecAudioMobile" type="text" id="codecAudioMobile" size="100" maxlength="256" value="<?php echo $options['codecAudioMobile']?>"/>
<BR>Ex.(latest FFMPEG with libfdk_aac): -c:a libfdk_aac -b:a 64k
<BR>Ex.(latest FFMPEG with native aac): -c:a aac -b:a 64k
<BR>Ex.(older FFMPEG with libfaac): -acodec libfaac -ac 2 -ar 22050 -ab 64k



<h4><?php _e('Convert to Preview Format','video-share-vod'); ?></h4>
<select name="convertPreview" id="convertPreview">
  <option value="1" <?php echo ($options['convertPreview']=='1')?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['convertPreview']?"":"selected"?>>No</option>
</select>
<BR>Generates a thumbnail resolution sized, short preview. Preview is cropped to match thumbnail aspect ratio.

<h4>Preview Input Parameters</h4>
<input name="convertPreviewInput" type="text" id="convertPreviewInput" size="100" maxlength="256" value="<?php echo $options['convertPreviewInput']?>"/>
<BR>Start from 5s: -ss 5

<h4>Preview Output Parameters</h4>
<input name="convertPreviewOutput" type="text" id="convertPreviewOutput" size="100" maxlength="256" value="<?php echo $options['convertPreviewOutput']?>"/>
<BR>Duration 10s: -t 10

<h4>Preview Video Codec</h4>
<input name="codecVideoPreview" type="text" id="codecVideoPreview" size="100" maxlength="256" value="<?php echo $options['codecVideoPreview']?>"/>
<BR>Bitrate is calculated depending on resolution (do not include in encoding parameters).
<BR>Ex: -vcodec libx264 -movflags +faststart -profile:v baseline -level 3.1

<h4>Preview Audio Codec</h4>
<input name="codecAudioPreview" type="text" id="codecAudioPreview" size="100" maxlength="256" value="<?php echo $options['codecAudioPreview']?>"/>
<BR>Ex.(latest FFMPEG with libfdk_aac): -c:a libfdk_aac -b:a 64k
<BR>Ex.(older FFMPEG with libfaac): -acodec libfaac -ac 2 -ar 22050 -ab 64k




<h4><?php _e('Generate HLS Segments','video-share-vod'); ?></h4>
<select name="convertHLS" id="convertHLS">
  <option value="1" <?php echo ($options['convertHLS']=='1')?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['convertHLS']?"":"selected"?>>No</option>
</select>
<BR>Generates static .ts segments and .m3u8 playlist for HLS playback of conversions. Accessible at /mbr/hls/[video-id].m3u8
<BR>Segmentation is triggered with conversion (no conversions results in no segmentation).
<BR>Space Warning: This more than doubles necessary storage space for videos. It's a faster alternative to using a HLS server that generates segments live. Uses space but considerably improves latency and server load.
Best performance for VOD is to deliver existing videos directly trough web server or pre-segmented as HLS. 
Playtime segmentation trough a streaming server involves higher latency and high server load (reduced viewer capacity) as video needs to be processed per viewer access (different viewers start watching at different times and need different position packets).
It's best to stream live sources trough streaming server (stream is packetized once during broadcast) and videos trough regular web server (that passes existing content without processing). 

<h4><?php _e('Multiple Formats in Single Process','video-share-vod'); ?></h4>
<select name="convertSingleProcess" id="convertSingleProcess">
  <option value="1" <?php echo $options['convertSingleProcess']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['convertSingleProcess']?"":"selected"?>>No</option>
</select>
<BR>Creates all required video formats (high, mobile) in a single conversion process. This can increase overall performance (source is only read once) but involves higher memory requirements. If disabled each format is created in a different process (recommended).

<h4><?php _e('Instant Conversion','video-share-vod'); ?></h4>
<select name="convertInstant" id="convertInstant">
  <option value="1" <?php echo $options['convertInstant']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['convertInstant']?"":"selected"?>>No</option>
</select>
<BR>Starts conversion instantly, without using a conversion queue. Not recommended as multiple conversion processes at same time could temporary freeze server and/or fail.


<h3><?php _e('Troubleshooting'); ?></h3>
<br>For a quick, hassle free setup, see <a href="https://videosharevod.com/hosting/" target="_vsvhost">VideoShareVOD turnkey managed hosting plans</a> for business video hosting, from $20/mo, including plugin installation, configuration.</p>


This section should aid in troubleshooting conversion issues.
<?php
				$fexec=0;

				echo "<BR>exec: ";
				if(function_exists('exec'))
				{
					echo "function is enabled";

					if(exec('echo EXEC') == 'EXEC')
					{
						echo ' and works';
						$fexec =1;
					}
					else echo ' <b>but does not work</b>';

				}else echo '<b>function is not enabled</b><BR>PHP function "exec" is required to run FFMPEG. Current hosting settings are not compatible with this functionality.';

				if ($fexec)
				{

					echo "<BR>FFMPEG: ";
					$cmd =$options['ffmpegPath'] . ' -version';
					exec($cmd, $output, $returnvalue);
					if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
					{
						echo "detected";
						echo '<BR>' . $output[0];
						echo '<BR>' . $output[1];
					}

					$cmd =$options['ffmpegPath'] . ' -codecs';
					exec($cmd, $output, $returnvalue);

					//detect codecs
					if ($output) if (count($output))
						{
							echo "<br>Codec libraries:";
							foreach (array('h264', 'vp6','vp8', 'vp9', 'speex', 'nellymoser', 'opus', 'h263', 'mpeg', 'mp3', 'fdk_aac', 'faac') as $cod)
							{
								$det=0; $outd="";
								echo "<BR>$cod : ";
								foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };
								if ($det) echo "detected ($outd)"; else echo "<b>missing: configure and install FFMPEG with lib$cod if you don't have another library for that codec and need it for input or output</b>";
							}
						}
?>
<BR>You need only 1 AAC codec. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).

<?php
				}
?>
<h4><?php _e('CloudLinux Shared Hosting Requirements'); ?></h4>
CPU Speed: FFMPEG will be called with "-threads 1" to use just 1 thread (meaning 100% of 1 cpu core). That means on cloud limited environments account will need at least 100% CPU speed (to use at least 1 full core) to run conversions.
<BR>Memory: Depending on settings, conversions can fail with "x264 [error]: malloc" error if memory limit does not permit doing conversion. While "mobile" conversion can usually be done with 512Mb memory limit, for "high" quality settings (HD) 768Mb or more would be needed.

<h4><?php _e('System Process Limitations'); ?></h4>
User limits can prevent conversions. Setting cpu limit to 7200 to prevent early termination:<br>
<?php
				if ($fexec)
				{
					$cmd = 'ulimit -t 7200; ulimit -a';
					$output ='';
					exec($cmd, $output, $returnvalue);
					foreach ($output as $outp) echo $outp.'<br>';
				}
				else echo "Not functional without exec.";

				break;


			case 'tvshows':
				//! tvshows options
?>
<h3><?php _e('TV Shows','video-share-vod'); ?></h3>

<h4><?php _e('Enable TV Shows Post Type','video-share-vod'); ?></h4>
Allows setting up TV Shows as custom post types. Plugin will automatically generate playlists for all TV shows so videos can be assigned to TV shows.
<br><select name="tvshows" id="tvshows">
  <option value="1" <?php echo $options['tvshows']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['tvshows']?"":"selected"?>>No</option>
</select>

<h4><?php _e('TV Shows Slug','video-share-vod'); ?></h4>
<input name="tvshows_slug" type="text" id="tvshows_slug" size="16" maxlength="32" value="<?php echo $options['tvshows_slug']?>"/>
<?php
				break;
				//! server options
			case 'server':
?>
<h3><?php _e('Server Configuration','video-share-vod'); ?></h3>
For best experience with implementing all plugin features and site performance, take a look at these <a href="https://videosharevod.com/hosting/">premium video streaming hosting plans and servers</a> we recommend. Installation and configuration is included with these plans.


<h4><?php _e('Uploads Path','video-share-vod'); ?></h4>
<?php
if ($options['uploadsPath'] == $optionsDefault['uploadsPath'])  
if (file_exists(ABSPATH . 'streams')) 
{
$options['uploadsPath'] =  ABSPATH . 'streams/_video-share-vod';
echo 'Save to apply! Suggested: ' . $options['uploadsPath'] . '<br>';
}
?>
<p><?php _e('Path where video files will be stored. Make sure you use a location outside plugin folder to avoid losing files on updates and plugin uninstallation.','video-share-vod'); ?></p>
<input name="uploadsPath" type="text" id="uploadsPath" size="100" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<br>Ex: /home/-your-account-/public_html/wp-content/uploads/vw_videoshare
<br>Ex: /home/-your-account-/public_html/streams/_vsv - with RTMP VOD in streams folder
<br>Ex: C:/Inetpub/vhosts/-your-account-/httpdocs/streams/_vsv - on Windows
<br>If you need to setup RTMP delivery, this needs to be inside the streams folder configured for VOD delivery with RTMP server/application.
<br>If you ever decide to change this, previous files must remain in old location.

<?php
				if (!file_exists($options['uploadsPath'])) echo '<br><b>Warning: Folder does not exist. If this warning persists after first access check path permissions:</b> ' . $options['uploadsPath'];
				if (!strstr($options['uploadsPath'], get_home_path() )) echo '<br><b>Warning: Uploaded files may not be accessible by web (path is not within WP installation path).</b>';

				echo '<br>WordPress Path: ' . get_home_path();
				echo '<br>WordPress URL: ' . get_site_url();
?>
<br>wp_upload_dir()['basedir'] : <?php $wud= wp_upload_dir(); echo $wud['basedir'] ?>
<br>$_SERVER['DOCUMENT_ROOT'] : <?php echo $_SERVER['DOCUMENT_ROOT'] ?>


<h4><?php _e('FFMPEG Path','video-share-vod'); ?></h4>
<p><?php _e('Path to latest FFMPEG. Compulsory requirement for extracting snapshots, info and converting videos.','video-share-vod'); ?>
<br>For a quick, hassle free setup, see <a href="https://videosharevod.com/hosting/" target="_vsvhost">VideoShareVOD turnkey managed hosting plans</a> for business video hosting, from $20/mo, including plugin installation, configuration.</p>
<input name="ffmpegPath" type="text" id="ffmpegPath" size="100" maxlength="256" value="<?php echo $options['ffmpegPath']?>"/>
<?php

				$fexec=0;

				echo "<BR>exec: ";
				if(function_exists('exec'))
				{
					echo "function is enabled";

					if(exec('echo EXEC') == 'EXEC')
					{
						echo ' and works';
						$fexec =1;
					}
					else echo ' <b>but does not work</b>';

				}else echo '<b>function is not enabled</b><BR>PHP function "exec" is required to run FFMPEG. Current hosting settings are not compatible with this functionality.';

				if ($fexec)
				{

					echo "<BR>FFMPEG: ";

					if (file_exists($options['ffmpegPath'])) echo '<br>File exists: ' . $options['ffmpegPath'];
					else echo '<br>File does not exist: ' . $options['ffmpegPath'];


					$cmd =$options['ffmpegPath'] . ' -version';
					exec($cmd, $output, $returnvalue);
					if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
					{
						echo '<br>exec ffmpeg returned: ' . $returnvalue;
						echo '<BR>' . $output[0];
						echo '<BR>' . $output[1];
					}

					$cmd =$options['ffmpegPath'] . ' -codecs';
					exec($cmd, $output, $returnvalue);

					//detect codecs
					$hlsAudioCodec = 'aac'; //hlsAudioCodec

					if ($output) if (count($output))
						{
							echo "<br>Codec libraries:";
							foreach (array('h264', 'vp6','vp8', 'vp9', 'speex', 'nellymoser', 'opus', 'h263', 'mpeg', 'mp3', 'aacplus', 'vo_aacenc', 'faac', 'fdk_aac') as $cod)
							{
							$det=0; $outd="";
							echo "<BR>$cod : ";
							foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };
							
							if ($det) echo "detected ($outd)";
							elseif (in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac'))) echo "lib$cod is missing but other aac codec may be available";
							else echo "<b>missing: configure and install FFMPEG with lib$cod if you don't have another library for that codec</b>";
							
							if ($det && in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac')))  $hlsAudioCodec = 'lib'. $cod;

							}
						}
?>
					<BR>You need at least 1 AAC codec. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).
					<BR>Detected AAC: <?php echo $hlsAudioCodec ?>.
					<?php
				}
?>

<h4>FFMPEG Codec Configuration</h4>
<select name="ffmpegConfiguration" id="ffmpegConfiguration">
  <option value="0" <?php echo $options['ffmpegConfiguration']?"":"selected"?>>Manual</option>
  <option value="1" <?php echo $options['ffmpegConfiguration'] == 1 ?"selected":""?>>Auto</option>
</select>
<BR>Auto will configure based on detected AAC codec libraries (recommended). Requires saving settings to apply.

<?php
$hlsAudioCodecReadOnly = '';

if ($options['ffmpegConfiguration'])
{
	if (!$hlsAudioCodec) $hlsAudioCodec = 'aac';
	
	$options['codecAudioHigh'] = " -c:a $hlsAudioCodec -b:a 128k ";
	$options['codecAudioMobile'] = " -c:a $hlsAudioCodec -b:a 64k ";
	$options['codecAudioPreview'] = " -c:a $hlsAudioCodec -b:a 64k ";
	
	$hlsAudioCodecReadOnly = 'readonly';
}
?>

<h4>High Audio Codec</h4>
<input name="codecAudioHigh" type="text" id="codecAudioHigh" <?php echo $hlsAudioCodecReadOnly ?> size="100" maxlength="256" value="<?php echo $options['codecAudioHigh']?>"/>


<h4>Mobile Audio Codec</h4>
<input name="codecAudioMobile" type="text" id="codecAudioMobile" <?php echo $hlsAudioCodecReadOnly ?> size="100" maxlength="256" value="<?php echo $options['codecAudioMobile']?>"/>


<h4>Preview Audio Codec</h4>
<input name="codecAudioPreview" type="text" id="codecAudioPreview" <?php echo $hlsAudioCodecReadOnly ?> size="100" maxlength="256" value="<?php echo $options['codecAudioPreview']?>"/>


<h4>RTMP Address</h4>
<p>Optional: Required only for RTMP playback. Recommended: <a href="https://videosharevod.com/advanced-hosting/" target="_blank">Wowza RTMP Hosting</a>.
<br>RTMP application address for playback.</p>
<input name="rtmpServer" type="text" id="rtmpServer" size="100" maxlength="256" value="<?php echo $options['rtmpServer']?>"/>
<br>Ex: rtmp://your-site.com/videowhisper-x2
<br>Do not use a rtmp address that requires some form of authentication or verification done by another web script as player will not be able to connect.
<br>Avoid using a shared rtmp address. Setup a special rtmp application for playback of videos. For Wowza configure &lt;StreamType&gt;file&lt;/StreamType&gt;.

<h4>RTMP Streams Path</h4>
<?php
if ($options['streamsPath'] == $optionsDefault['streamsPath'])  
if (file_exists(ABSPATH . 'streams')) 
{
$options['streamsPath'] =  ABSPATH . 'streams';
echo 'Save to apply! Detected: ' . $options['streamsPath'] . '<br>';
}
?>

<p>Optional: Required only for RTMP playback.
<br>Path where rtmp server is configured to stream videos from. Uploads path must be a subfolder of this path to allow rtmp access to videos. </p>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<br>This must be a substring of, or same as Uploads Path.
<br>Ex: /home/your-account/public_html/streams
<?php
				if (!strstr($options['uploadsPath'], $options['streamsPath']))
					echo '<br><b class="error">Current value seems wrong!</b>';
				else echo '<br>Current value seems fine.';
?>
<h4>HLS URL</h4>
<p>Optional: Required only for HLS playback.
<br>HTTPS address to access by HTTP Live Streaming (HLS).</p>
<input name="hlsServer" type="text" id="hlsServer" size="100" maxlength="256" value="<?php echo $options['hlsServer']?>"/>
<br>Ex: https://your-site.com:1935/videowhisper-x2/
<br>Streaming server needs to be configured with a SSL certificate for HTTPS delivery.
<br>For Wowza disable live packetizers: &lt;LiveStreamPacketizers&gt;&lt;/LiveStreamPacketizers&gt;.
<br>Performance Warning: Best performance for VOD is to deliver existing videos directly trough web server or pre-segmented as HLS. 
Playtime segmentation trough a streaming server involves higher latency and high server load (reduced viewer capacity) as video needs to be processed per viewer access (different viewers start watching at different times and need different position packets).
It's best to stream live sources trough streaming server (stream is packetized once during broadcast) and videos trough regular web server (that passes existing content without processing). 
<?php
				break;
			case 'ls':
				//! ls options
?>
<h3>Live Streams</h3>
Video Share VOD can import and manage videos generated by archiving live streams (from broadcasts and videochats). Multiple VideoWhisper video communication plugins can use this functionality for managing stream archives.

<h4>Path to Video Archive</h4>
<?php
if ($options['vwls_archive_path'] == $optionsDefault['vwls_archive_path'])  
if (file_exists(ABSPATH . 'streams')) 
{
$options['vwls_archive_path'] =  ABSPATH . 'streams';
echo 'Save to apply! Detected: ' . $options['vwls_archive_path'] . '<br>';
}
?>

<input name="vwls_archive_path" type="text" id="vwls_archive_path" size="100" maxlength="256" value="<?php echo $options['vwls_archive_path']; ?>"/>
<br>Ex: /home/your-account/public_html/streams/
<br>When using Wowza Streaming Engine configure [install-dir]/conf/Server.xml to save as FLV instead of MP4:
<br>&lt;DefaultStreamPrefix&gt;flv&lt;/DefaultStreamPrefix&gt;
<br>FLV includes support for web based flash audio codecs.


<h3>Broadcast Live Video - Live Streaming</h3>
<p>
Find more about <a target="_blank" href="https://videosharevod.com/features/live-streaming/">VideoShareVOD Live Streaming functionality</a> and <a href="http://broadcastlivevideo.com/">Broadcast Live Video turnkey live streaming site solution</a>. <br>
VideoWhisper Live Streaming is a plugin that allows users to broadcast live video channels.
<br>Detection:
<?php
				if (class_exists("VWliveStreaming")) echo 'Installed.';
				else
					echo 'Not detected. Please install and activate <a href="https://wordpress.org/plugins/videowhisper-live-streaming-integration/">WordPress Broadcast Live Video - Live Streaming plugin</a> to use this functionality.';
?>
</p>

<h4>Import Live Streaming Playlists</h4>
Enables Live Streaming channel owners to import archived streams. Videos must be archived locally.
<br><select name="vwls_playlist" id="vwls_playlist">
  <option value="1" <?php echo $options['vwls_playlist']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['vwls_playlist']?"":"selected"?>>No</option>
</select>

<h4>List Channel Videos</h4>
List videos on channel page.
<br><select name="vwls_channel" id="vwls_channel">
  <option value="1" <?php echo $options['vwls_channel']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['vwls_channel']?"":"selected"?>>No</option>
</select>
<br>Videos are associated to channel using a playlist with same name as channel. If channel requires payment (setup with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership & Content</a>), channel videos are only accessible if user paid for the channel.


<?php
				break;
			case 'players':
				$options['crossdomain_xml'] = htmlentities(stripslashes($options['crossdomain_xml']));
				$options['containerCSS'] = htmlentities(stripslashes($options['containerCSS']));

				$crossdomain_url = site_url() . '/crossdomain.xml';

?>
<h3><?php _e('Players','video-share-vod'); ?></h3>
<?php _e('Strobe RTMP supports multi bitrate sources provided by plugin as:','video-share-vod'); ?> /mbr/rtmp/[video-id].f4m
<br><?php _e('HTML playback is supported on most browsers and devices.','video-share-vod'); ?>

<h4><?php _e('HTML5 Player','video-share-vod'); ?></h4>
<select name="html5_player" id="html5_player">
  <option value="native" <?php echo $options['html5_player']=='native'?"selected":""?>><?php _e('Native HTML5 Tag','video-share-vod'); ?></option>
  <option value="wordpress" <?php echo $options['html5_player']=='wordpress'?"selected":""?>><?php _e('WordPress Player (MediaElement.js)','video-share-vod'); ?></option>
  <option value="video-js" <?php echo $options['html5_player']=='video-js'?"selected":""?>><?php _e('Video.js','video-share-vod'); ?></option>
 </select>

<h3><?php _e('Player Compatibility','video-share-vod'); ?></h3>
<?php _e('Setup appropriate player type and video source depending on OS and browser.','video-share-vod'); ?>
<h4><?php _e('Default Player Type','video-share-vod'); ?></h4>
<select name="player_default" id="player_default">
  <option value="strobe" <?php echo $options['player_default']=='strobe'?"selected":""?>><?php _e('Strobe (Flash)','video-share-vod'); ?></option>
  <option value="html5" <?php echo $options['player_default']=='html5'?"selected":""?>><?php _e('HTML5','video-share-vod'); ?></option>
  <option value="html5-mobile" <?php echo $options['player_default']=='html5-mobile'?"selected":""?>><?php _e('HTML5 Mobile','video-share-vod'); ?></option>
    <option value="html5-hls" <?php echo $options['player_default']=='html5-hls'?"selected":""?>><?php _e('HTML5 HLS','video-share-vod'); ?></option>

   <option value="strobe-rtmp" <?php echo $options['player_default']=='strobe-rtmp'?"selected":""?>><?php _e('Strobe RTMP','video-share-vod'); ?></option>
</select>
<BR><?php _e('HTML5 Mobile plays lower profile converted video, for mobile support, even if source video is MP4.','video-share-vod'); ?>

<h4><?php _e('Player on iOS','video-share-vod'); ?></h4>
<select name="player_ios" id="player_ios">
  <option value="html5" <?php echo $options['player_ios']=='html5'?"selected":""?>><?php _e('HTML5','video-share-vod'); ?></option>	
  <option value="html5-mobile" <?php echo $options['player_ios']=='html5-mobile'?"selected":""?>><?php _e('HTML5 Mobile','video-share-vod'); ?></option>
   <option value="html5-hls" <?php echo $options['player_ios']=='html5-hls'?"selected":""?>><?php _e('HTML5 HLS','video-share-vod'); ?></option>
</select>
<br><?php _e('If enabled, use HTML5 mobile for lower bitrate conversion that loads faster in mobile networks.','video-share-vod'); ?>

<h4><?php _e('Player on Safari','video-share-vod'); ?></h4>
<select name="player_safari" id="player_safari">
  <option value="strobe" <?php echo $options['player_safari']=='strobe'?"selected":""?>>Strobe</option>
  <option value="html5" <?php echo $options['player_safari']=='html5'?"selected":""?>><?php _e('HTML5','video-share-vod'); ?></option>
  <option value="html5-mobile" <?php echo $options['player_safari']=='html5-mobile'?"selected":""?>><?php _e('HTML5 Mobile','video-share-vod'); ?></option>
   <option value="strobe-rtmp" <?php echo $options['player_safari']=='strobe-rtmp'?"selected":""?>><?php _e('Strobe RTMP','video-share-vod'); ?></option>
   <option value="html5-hls" <?php echo $options['player_safari']=='html5-hls'?"selected":""?>><?php _e('HTML5 HLS','video-share-vod'); ?></option>
</select>
<BR><?php _e('Safari requires user to confirm flash player load. Use HTML5 player to avoid this.','video-share-vod'); ?>

<h4><?php _e('Player on Firefox for MacOS','video-share-vod'); ?></h4>
<select name="player_firefox_mac" id="player_firefox_mac">
  <option value="html5" <?php echo $options['player_firefox_mac']=='html5'?"selected":""?>><?php _e('HTML5','video-share-vod'); ?></option>	
  <option value="strobe" <?php echo $options['player_firefox_mac']=='strobe'?"selected":""?>>Strobe</option>
   <option value="strobe-rtmp" <?php echo $options['player_firefox_mac']=='strobe-rtmp'?"selected":""?>><?php _e('Strobe RTMP','video-share-vod'); ?></option>
  <option value="html5-mobile" <?php echo $options['player_firefox_mac']=='html5-mobile'?"selected":""?>><?php _e('HTML5 Mobile','video-share-vod'); ?></option>
   <option value="html5-hls" <?php echo $options['player_firefox_mac']=='html5-hls'?"selected":""?>><?php _e('HTML5 HLS','video-share-vod'); ?></option>   
</select>
<BR><?php _e('Older Firefox for Mac did not support MP4 HTML5 playback. See <a href="https://bugzilla.mozilla.org/show_bug.cgi?id=851290">bug status</a>.','video-share-vod'); ?>

<h4><?php _e('Player on Android','video-share-vod'); ?></h4>
<select name="player_android" id="player_android">
  <option value="html5" <?php echo $options['player_android']=='html5'?"selected":""?>><?php _e('HTML5','video-share-vod'); ?></option>	
  <option value="html5-mobile" <?php echo $options['player_android']=='html5-mobile'?"selected":""?>><?php _e('HTML5 Mobile','video-share-vod'); ?></option>
  
  <option value="html5-hls" <?php echo $options['player_android']=='html5-hls'?"selected":""?>><?php _e('HTML5 HLS','video-share-vod'); ?></option>
  <option value="strobe" <?php echo $options['player_android']=='strobe'?"selected":""?>><?php _e('Flash Strobe','video-share-vod'); ?></option>
   <option value="strobe-rtmp" <?php echo $options['player_android']=='strobe-rtmp'?"selected":""?>><?php _e('Flash Strobe RTMP','video-share-vod'); ?></option>
</select>
<BR><?php _e('Latest Android no longer supports Flash in default browser, so HTML5 is recommended.','video-share-vod'); ?>

<h4>Video Post Template Filename</h4>
<input name="postTemplate" type="text" id="postTemplate" size="20" maxlength="64" value="<?php echo $options['postTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render webcam post page. Ex: page.php, single.php
<?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_template_directory() . '/' . $options['postTemplate'];
					echo '<br>' . $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.


<h4><?php _e('Video Width','video-share-vod'); ?></h4>
<input name="videoWidth" type="text" id="videoWidth" size="4" maxlength="4" value="<?php echo $options['videoWidth']?>"/>
<br><?php _e('Leave blank to use video width dynamically for player (for HD videos may be bigger than screen resolution and require scrolling). Does not apply for VideoJS as that uses adaptive fluid fill.','video-share-vod'); ?>

<h4><?php _e('Player Container CSS','video-share-vod'); ?></h4>
<textarea name="containerCSS" id="containerCSS" cols="64" rows="3"><?php echo $options['containerCSS']?></textarea>
<br>Defaults:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['containerCSS']?></textarea>

<h4><?php _e('Playlist Video Width','video-share-vod'); ?></h4>
<input name="playlistVideoWidth" type="text" id="playlistVideoWidth" size="4" maxlength="4" value="<?php echo $options['playlistVideoWidth']?>"/>

<h4><?php _e('Playlist List Width','video-share-vod'); ?></h4>
<input name="playlistListWidth" type="text" id="playlistListWidth" size="4" maxlength="4" value="<?php echo $options['playlistListWidth']?>"/>

<h4>Playlist Template Filename</h4>
<input name="playlistTemplate" type="text" id="playlistTemplate" size="20" maxlength="64" value="<?php echo $options['playlistTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render playlist post page. Ex: page.php, single.php
<?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_template_directory() . '/' . $options['playlistTemplate'];
					echo '<br>' . $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.

<h4><?php _e('Allow Debugging','video-share-vod'); ?></h4>
<select name="allowDebug" id="allowDebug">
  <option value="1" <?php echo $options['allowDebug']=='1'?"selected":""?>><?php _e('Yes','video-share-vod'); ?></option>
  <option value="0" <?php echo $options['allowDebug']=='0'?"selected":""?>><?php _e('No','video-share-vod'); ?></option>
</select>
<br><?php _e('Allows forcing players at runtime using url parameters like ?player=html5-hls&player_html=video-js','video-share-vod'); ?>


<h4><?php _e('Cross Domain Policy','video-share-vod'); ?></h4>
<textarea name="crossdomain_xml" id="crossdomain_xml" cols="100" rows="4"><?php echo $options['crossdomain_xml']?></textarea>
<br>This is required for Flash and Air based players to access videos and scripts from site.
<br>After updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>) this should become available as <a href="<?php echo $crossdomain_url ?>"><?php echo $crossdomain_url ?></a>.
<br>This works if file doesn't already exist. You can also create the file for faster serving.

<h4><?php _e('Disable','video-share-vod'); ?> X-Frame-Options: SAMEORIGIN</h4>
<select name="disableXOrigin" id="disableXOrigin">
  <option value="1" <?php echo $options['disableXOrigin']=='1'?"selected":""?>><?php _e('Yes','video-share-vod'); ?></option>
  <option value="0" <?php echo $options['disableXOrigin']=='0'?"selected":""?>><?php _e('No','video-share-vod'); ?></option>
</select>
<br>Disable X-Frame-Options: SAMEORIGIN security feature from /wp_admin (send_frame_options_header), to allow embeds from external IFRAMEs.

<h4><?php _e('Referral for Disable','video-share-vod'); ?> X-Frame-Options: SAMEORIGIN</h4>
<input name="disableXOriginRef" type="text" id="disableXOriginRef" size="100" maxlength="256" value="<?php echo $options['disableXOriginRef']?>"/>
<br>Disable 'send_frame_options_header' only for referrals that start with this string. Highly recommended when you just need to embed on one site you know. Ex: https://subdomain.embeddingsite.com

<?php
				break;

			case 'listings':
				//! display options

				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));
				$options['custom_post'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_post']));
				$options['custom_taxonomy'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_taxonomy']));

?>
<h3><?php _e('Listings','video-share-vod'); ?></h3>

<h4>Interface Class(es)</h4>
<input name="interfaceClass" type="text" id="interfaceClass" size="30" maxlength="128" value="<?php echo $options['interfaceClass']?>"/>
<br>Extra class to apply to interface (using Semantic UI). Use inverted when theme uses a dark mode (a dark background with white text) or for contrast. Ex: inverted
<br>Some common Semantic UI classes: inverted = dark mode or contrast, basic = no formatting, secondary/tertiary = greys, red/orange/yellow/olive/green/teal/blue/violet/purple/pink/brown/grey/black = colors . Multiple classes can be combined, divided by space. Ex: inverted, basic pink, secondary green, secondary 


<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable Star Reviews</h4>
<?php
				if (is_plugin_active('rate-star-review/rate-star-review.php')) echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>'; else echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php?s=videowhisper+rate+star+review&tab=search&type=term">Plugins > Add New</a>!';
?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['rateStarReview']?"selected":""?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.


<h4>Setup Pages</h4>
<select name="disableSetupPages" id="disableSetupPages">
  <option value="0" <?php echo $options['disableSetupPages']?"":"selected"?>>Yes</option>
  <option value="1" <?php echo $options['disableSetupPages']?"selected":""?>>No</option>
</select>
<br>Create pages for main functionality. Also creates a menu with these pages (VideoWhisper) that can be added to themes. If you delete the pages this option recreates these if not disabled.
<br>Additionally shows menus to pages in top bar user menu when enabled (disable this to hide menus).

<h4>Video Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="16" maxlength="32" value="<?php echo $options['custom_post']?>"/>
<br>Custom post name for videos (only alphanumeric, lower case). Will be used for video urls. Ex: video, clip, videosharevod
<br><a href="options-permalink.php">Save permalinks</a> to activate new url scheme.
<br>Warning: Changing post type name at runtime will hide previously added items. Previous posts will only show when their post type name is restored.

<h4>Video Post Taxonomy Name</h4>
<input name="custom_taxonomy" type="text" id="custom_taxonomy" size="12" maxlength="32" value="<?php echo $options['custom_taxonomy']?>"/>
<br>Special taxonomy for organising videos. Ex: playlist

<h4><?php _e('Default Order By','video-share-vod'); ?></h4>
<select name="order_by" id="order_by">
  <option value="post_date" <?php echo $options['order_by']=='post_date'?"selected":""?>>Date</option>
  <option value="video-views" <?php echo $options['order_by']=='video-views'?"selected":""?>>Views</option>
  <option value="video-lastview" <?php echo $options['order_by']=='video-lastview'?"selected":""?>>Recently Viewed</option>
<?php 
				if ($options['rateStarReview'])
				{
					echo '<option value="rateStarReview_rating"' . ($options['order_by'] == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'video-share-vod') . '</option>';
					echo '<option value="rateStarReview_ratingNumber"' . ($options['order_by'] == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Most Rated', 'video-share-vod') . '</option>';
					echo '<option value="rateStarReview_ratingPoints"' . ($options['order_by'] == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'video-share-vod') . '</option>';
				}
 ?> 
  <option value="rand" <?php echo $options['order_by']=='rand'?"selected":""?>>Random</option>
</select>

<h4><?php _e('Default Videos Per Page','video-share-vod'); ?></h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>

<h4><?php _e('Default Videos Per Row','video-share-vod'); ?></h4>
<input name="perRow" type="text" id="perRow" size="3" maxlength="3" value="<?php echo $options['perRow']?>"/>
<br>Leave 0 to show as many as container space permits.

<h4><?php _e('Thumbnail Width','video-share-vod'); ?></h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4><?php _e('Thumbnail Height','video-share-vod'); ?></h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>

<h4><?php _e('Custom CSS','video-share-vod'); ?></h4>
<textarea name="customCSS" id="customCSS" cols="64" rows="5"><?php echo $options['customCSS']?></textarea>
<BR><?php _e('Styling used in elements added by this plugin. Must include CSS container &lt;style type=&quot;text/css&quot;&gt; &lt;/style&gt; .','video-share-vod'); ?>
If a plugin update alters listings, just reset CSS to current defaults:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['customCSS']?></textarea>

<h4><?php _e('Show VideoWhisper Powered by','video-share-vod'); ?></h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>
<br><?php _e('Show a mention that videos were posted with VideoWhisper plugin.
','video-share-vod'); ?>
<?php
				break;

			case 'share':
				//! share options
				$current_user = wp_get_current_user();

?>
<h3><?php _e('Video Sharing','video-share-vod'); ?></h3>

<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="ID" <?php echo $options['userName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Used for default user playlists. Your username with current settings:
<?php
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				echo $username = $current_user->$userName;				
?>

<h4><?php _e('Users allowed to share videos','video-share-vod'); ?></h4>
<textarea name="shareList" cols="64" rows="2" id="shareList"><?php echo $options['shareList']?></textarea>
<BR><?php _e('Who can share videos: comma separated Roles, user Emails, user ID numbers.','video-share-vod'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','video-share-vod'); ?>

<h4><?php _e('Users allowed to directly publish videos','video-share-vod'); ?></h4>
<textarea name="publishList" cols="64" rows="2" id="publishList"><?php echo $options['publishList']?></textarea>
<BR><?php _e('Users not in this list will add videos as "pending".','video-share-vod'); ?>
<BR><?php _e('Who can publish videos: comma separated Roles, user Emails, user ID numbers.','video-share-vod'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','video-share-vod'); ?>

<h4><?php _e('Users allowed to get embed codes','video-share-vod'); ?></h4>
<textarea name="embedList" cols="64" rows="2" id="embedList"><?php echo $options['embedList']?></textarea>
<BR><?php _e('Who can see embed code for videos: comma separated Roles, user Emails, user ID numbers.','video-share-vod'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','video-share-vod'); ?>
<BR><?php _e('Add code below to your .htaccess file for successful resource embeds:','video-share-vod'); ?>
<BR># Apache config: allow embeds on other sites
<BR>Header set Access-Control-Allow-Origin "*"

<h3>Troubleshoot Uploads</h3>
PHP Limitations (for a request, script call):
<BR>post_max_size: <?php echo ini_get('post_max_size') ?>
<BR>upload_max_filesize: <?php echo ini_get('upload_max_filesize') ?> - The maximum size of an uploaded file.
<BR>memory_limit: <?php echo ini_get('memory_limit') ?> - This sets the maximum amount of memory in bytes that a script is allowed to allocate. This helps prevent poorly written scripts for eating up all available memory on a server.
<BR>max_execution_time: <?php echo ini_get('max_execution_time') ?> - This sets the maximum time in seconds a script is allowed to run before it is terminated by the parser. This helps prevent poorly written scripts from tying up the server. The default setting is 90.
<BR>max_input_time: <?php echo ini_get('max_input_time') ?>  - This sets the maximum time in seconds a script is allowed to parse input data, like POST, GET and file uploads.

<p>For adding big videos, best way is to upload by FTP and use <a href="admin.php?page=video-share-import">Import</a> feature. Trying to upload big files by HTTP (from web page) may result in failure due to request limitations, timeouts depending on client upload connection speed.</p>
<?php
				break;


			case 'vod':
				//! vod options
				$options['accessDenied'] = htmlentities(stripslashes($options['accessDenied']));

?>
<h3>Membership Video On Demand</h3>
<a target="_blank" href="https://videosharevod.com/features/video-on-demand/">About Video On Demand...</a>

<h4>Members allowed to watch video</h4>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo $options['watchList']?></textarea>
<BR>Global video access list: comma separated Roles, user Emails, user ID numbers. Ex: <i>Subscriber, Author, submit.ticket@videowhisper.com, 1</i>
<BR>"Guest" will allow everybody including guests (unregistered users) to watch videos.

<h4>Role Playlists</h4>
Enables access by role playlists: Assign video to a playlist that is a role name.
<br><select name="vod_role_playlist" id="vod_role_playlist">
  <option value="1" <?php echo $options['vod_role_playlist']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['vod_role_playlist']?"":"selected"?>>No</option>
</select>
<br>Multiple roles can be assigned to same video. User can have any of the assigned roles, to watch. If user has required role, access is granted even if not in global access list.
<br>Videos without role playlists are accessible as per global video access.

<h4>Exceptions</h4>
Assign videos to these Playlists:
<br><b>free</b> : Anybody can watch, including guests.
<br><b>registered</b> : All members can watch.
<br><b>unpublished</b> : Video is not accessible.

<h4>Access denied message</h4>
<textarea name="accessDenied" cols="64" rows="3" id="accessDenied"><?php echo $options['accessDenied']?>
</textarea>
<BR>HTML info, shows with preview if user does not have access to watch video.
<br>Including #info# will mention rule that was applied.

<h4>Paid Membership and Content</h4>
Solution was tested and developed in combination with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content</a>: Sell membership and content based on virtual wallet credits/tokens. Credits/tokens can be purchased with real money. 
<br> - Pay per Video: This plugin also allows users to sell individual videos (will get an edit button to set price and duration).
<br> - Pay per Channel: When using Broadcast Live Video - Live Streaming plugin, videos are associated to channel using a playlist with same name as channel. If channel requires payment, channel videos are only accessible if user paid for the channel.

<BR>Paid Membership and Content:
<?php

				if (is_plugin_active('paid-membership/paid-membership.php'))
				{
					echo '<a href="admin.php?page=paid-membership">Detected</a>';

					$optionsPM = get_option('VWpaidMembershipOptions');
					if ($optionsPM['p_videowhisper_content_edit']) $editURL = add_query_arg('editID', '', get_permalink($optionsPM['p_videowhisper_content_edit'])) . '=';


				}else echo 'Not detected. Please install and activate <a target="_mycred" href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content with Credits</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

?>


<h4>Frontend Contend Edit</h4>
<select name="editContent" id="editContent">
  <option value="0" <?php echo $options['editContent']?"":"selected"?>>No</option>
  <option value="all" <?php echo $options['editContent']?"selected":""?>>Yes</option>
</select>
<br>Allow owner and admin to edit content options like price for videos, from frontend. This will show an edit button on listings that can be edited by current user.

<h4>Edit Content URL</h4>
<input name="editURL" type="text" id="editURL" size="100" maxlength="256" value="<?php echo $options['editURL']?>"/>
<BR>Detected: <?php echo $editURL ?>

<?php

				break;

			case 'vast':
				//! vast options
				$options['vast'] = trim($options['vast']);

?>
<h3>Video Ad Serving Template (VAST) / Interactive Media Ads (IMA)</h3>
VAST/IMA is currently supported with Video.js HTML5 player.
<br>VAST data structure configures: (1) The ad media that should be played (2) How should the ad media be played (3) What should be tracked as the media is played. In example pre-roll video ads can be implemented with VAST.
<br>IMA enables ad requests to DoubleClick for Publishers (DFP), the Google AdSense network for Video (AFV) or Games (AFG) or any VAST-compliant ad server.

<h4>Video Ads</h4>
Enable ads for all videos.
<br><select name="adsGlobal" id="adsGlobal">
  <option value="1" <?php echo $options['adsGlobal']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['adsGlobal']?"":"selected"?>>No</option>
</select>
<br>Exception Playlists:
<br><b>sponsored</b>: Show ads.
<br><b>adfree</b>: Do not show ads.

<h4>VAST Mode</h4>
<select name="vastLib" id="vastLib">
  <option value="iab" <?php echo $options['vastLib']=='iab'?"":"selected"?>>Google Interactive Media Ads (IMA)</option>
  <option value="vast" <?php echo $options['vastLib']=='vast'?"selected":""?>>Video Ad Serving Template (VAST) </option>
</select>
<br>The Google Interactive Media Ads (IMA) enables publishers to display linear, non-linear, and companion ads in videos and games. Supports VAST 2, VAST 3, VMAP. Recommended: IMA

<h4>VAST compliant / IMA adTagUrl Address</h4>
<textarea name="vast" cols="64" rows="2" id="vast"><?php echo $options['vast']?>
</textarea>
<br>Ex: https://pubads.g.doubleclick.net/gampad/ads?sz=640x480&iu=/124319096/external/single_ad_samples&ciu_szs=300x250&impl=s&gdfp_req=1&env=vp&output=vast&unviewed_position_start=1&cust_params=deployment%3Ddevsite%26sample_ct%3Dskippablelinear&correlator=
<br>Try more <a href="https://developers.google.com/interactive-media-ads/docs/sdks/html5/tags">IMA samples</a>. Leave blank to disable video ads.

<h4>Premium Users List</h4>
<p>Premium uses watch videos without advertisements (exception for VAST).</p>
<textarea name="premiumList" cols="64" rows="3" id="premiumList"><?php echo $options['premiumList']?>
</textarea>
<BR>Ads excepted users: comma separated Roles, user Emails, user ID numbers. Ex: <i>Author, Editor, submit.ticket@videowhisper.com, 1</i>

<?php
				break;
			}

			if (!in_array($active_tab, array( 'shortcodes', 'reset', 'support')) ) submit_button(); ?>

</form>
</div>
	 <?php
		}

		function adminImport()
		{
			$options = VWvideoShare::setupOptions();
			$optionsDefault = VWvideoShare::adminOptionsDefault();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWvideoShareOptions', $options);
			}


			screen_icon(); ?>
<h2>Import Videos from Folder</h2>
	Use this to mass import any number of videos already existent on server.

<?php
			if (file_exists($options['importPath'])) echo do_shortcode('[videowhisper_import path="' . $options['importPath'] . '"]');
			else echo 'Import folder not found on server: '. $options['importPath'];
?>
* Some formats/codecs will not archive in FLV container (like VP8, Opus from WebRTC). Transcoding is required and archived transcoded streams (starting with "i_") can be used.

<h3>Import Settings</h3>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h4>Import Path</h4>
<p>Server path to import videos from</p>
<?php
if ($options['importPath'] == $optionsDefault['importPath'])  
if (file_exists(ABSPATH . 'streams')) 
{
$options['importPath'] =  ABSPATH . 'streams';
echo 'Save to apply! Detected: ' . $options['importPath'] . '<br>';
}
?>
<input name="importPath" type="text" id="importPath" size="100" maxlength="256" value="<?php echo $options['importPath']?>"/>
<br>Ex: /home/[youraccount]/public_html/streams/
<h4>Delete Original on Import</h4>
<select name="deleteOnImport" id="deleteOnImport">
  <option value="1" <?php echo $options['deleteOnImport']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['deleteOnImport']?"":"selected"?>>No</option>
</select>
<br>Remove original file after copy to new location.
<h4>Import Clean</h4>
<p>Delete videos older than:</p>
<input name="importClean" type="text" id="importClean" size="5" maxlength="8" value="<?php echo $options['importClean']?>"/>days
<br>Set 0 to disable automated cleanup (not recommended as an active site can fill up server disks with broadcast archives). Cleanup does not occur more often than 10h to prevent high load.
<?php submit_button(); ?>
</form>
	<?php



		}

		function getCurrentURL()
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

		function videoFilePath($video_id, $format)
		{
			$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();

			if ($alt = $videoAlts[$format])
				if (file_exists($alt['file']))
				{
					return $alt['file'];
				}
			return '';
		}

		function adminExport()
		{
			$options = VWvideoShare::setupOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWvideoShareOptions', $options);
			}



			$this_page = add_query_arg( array('page'=>'video-share-export'), VWvideoShare::getCurrentURL() );

			screen_icon(); ?>
<h2>Export Videos to Folder</h2>
	Use this tool to mass export and download videos.

<BR><a class="button" href="<?php echo add_query_arg( array('export'=>'current'), $this_page); ?>">Export Current</A> Export list of videos from their current location.
<BR><a class="button" href="<?php echo add_query_arg( array('export'=>'download'), $this_page); ?>">Export to Download Folder</A> Export after creating links to all videos in a download folder (also uses video name).
<BR><a class="button" href="<?php echo add_query_arg( array('export'=>'download-category'), $this_page); ?>">Export to Download Folder by Category</A> Export after creating links to all videos in a download folder organised by category (sub folders).

<?php

			$export = sanitize_file_name( $_GET['export']);

			if ($export)
			{
				echo '<h3>Exporting Current File List</h3>';


				if ($export == 'download')
				{
					if (!file_exists($options['exportPath'])) mkdir($options['exportPath'], 0777);
				}

				$args=array(
					'post_type' =>  $options['custom_post'],
					'posts_per_page' => $options['exportCount'],
					'order'            => 'DESC',
					'orderby' => 'post_date',
					'post_status' => 'any',
				);

				$postslist = get_posts( $args );

				$codePaths = '';
				$codeUrls = '';
				foreach ($postslist as $video)
				{
					$noVideos ++;
					$path =  VWvideoShare::videoFilePath($video->ID, 'high');

					if ($path)
					{
						$noPaths ++;
						if (file_exists($path)) if (filesize($path)>0)
							{
								$noFiles ++;
								switch ($export)
								{
								case 'current':
									$codePaths .= "\r\n" . $path;
									$codeUrls .= "\r\n" .VWvideoShare::path2url($path);
									break;

								case 'download':

									$newName = sanitize_file_name($video->post_title);
									if (!$newName) $newName =$video->ID;

									if ($newName)
									{
										$noLinks++;
										$newName .= '.mp4';
										$newPath = $options['exportPath'] . $newName;

										//remove previous link if exists
										if (file_exists($newPath)) if (is_link($newPath)) unlink($newPath);

											if (!file_exists($newPath)) link($path, $newPath);

											$codePaths .= "\r\n" . $newPath;
										$codeUrls .= "\r\n" .VWvideoShare::path2url($newPath);
									}
									break;

								case 'download-category':

									$newName = sanitize_file_name($video->post_title);
									if (!$newName) $newName =$video->ID;

									$categories = wp_get_post_categories( $video->ID, array('fields' => 'names') );

									if (!$categories) $categories = array('_NA');

									foreach ($categories as $category)
										if ($category)
										{
											$noLinks ++;
											$catLinks[$category]++;

											$newName .= '.mp4';
											$newPath = $options['exportPath'] .$category. '/'. $newName;

											if (!file_exists($options['exportPath'] .$category)) mkdir($options['exportPath'] .$category, 0777);

											//remove previous link if exists
											if (file_exists($newPath)) if (is_link($newPath)) unlink($newPath);
												if (!file_exists($newPath)) link($path, $newPath);

												$codePaths .= "\r\n" . $newPath;
											$codeUrls .= "\r\n" .VWvideoShare::path2url($newPath);
										}

									break;
								}
							}

					}
				}

				echo 'Video Paths (download by FTP, scripts, terminal):<br><textarea cols="150" rows="5">'.$codePaths.'</textarea>';
				echo '<br>Video URLs (use with a download manager):<br><textarea cols="150" rows="5">'.$codeUrls.'</textarea>';
				echo "<br>Videos: $noVideos<br>Paths (conversion queued): $noPaths<br>Files (conversion at least started, size>0): $noFiles<br>*Only existing files (generated by conversion) are listed.";

				if ($catLinks)
				{
					echo '<br>By category:';
					foreach ($catLinks as $cat => $no) echo "<br>$cat: $no";
				}
			}

?>
<h3>Export Settings</h3>
<form method="post" action="<?php echo $this_page; ?>">

<h4>Maximum Videos</h4>
Maximum video number to export
<br><input name="exportCount" type="text" id="exportCount" size="20" maxlength="32" value="<?php echo $options['exportCount']?>"/>
<br>Ex: 500


<h4>Videos Offset</h4>
Where to start listing from (if exporting in parts)
<br><input name="exportOffset" type="text" id="exportOffset" size="20" maxlength="32" value="<?php echo $options['exportOffset']?>"/>
<br>Ex: 0

<h4>Download Path</h4>
Server path where to create video file links for easy download
<br><input name="exportPath" type="text" id="exportPath" size="100" maxlength="256" value="<?php echo $options['exportPath']?>"/>
<br>Ex: /home/[your-account]/public_html/download/


<?php submit_button(); ?>
</form>


	<?php



		}



		function adminLiveStreaming()
		{
			$options = get_option( 'VWvideoShareOptions' );

			screen_icon(); ?>

<h3>Import Archived Channel Videos</h3>
This allows importing stream archives to playlist of their video channel. <a target="_blank" href="https://videosharevod.com/features/live-streaming/">About Live Streaming...</a><br>
<?php

			if ($channel_name = sanitize_file_name($_GET['playlist_import']))
			{

				$url  = add_query_arg( array( 'playlist_import' => $channel_name), admin_url('admin.php?page=video-share-ls') );


				echo '<form action="' . $url . '" method="post">';
				echo "<h4>Import Archived Videos to Playlist <b>" . $channel_name . "</b></h4>";
				echo VWvideoShare::importFilesSelect( $channel_name, VWvideoShare::extensions_flash(), $options['vwls_archive_path']);
				echo '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . sanitize_file_name($channel_name) . "' and post_type='channel' LIMIT 0,1" );

				if ($postID)
				{
					$channel = get_post( $postID );
					$owner = $channel->post_author;

					$cats = wp_get_post_categories( $postID);
					if (count($cats)) $category = array_pop($cats);
				}
				else
				{
					$current_user = wp_get_current_user();
					$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
					$username = $current_user->$userName;

					$owner = $current_user->ID;
					echo ' as ' . $username;
				}

				echo '<input type="hidden" name="playlist" id="playlist" value="' . $channel_name . '">';
				echo '<input type="hidden" name="owner" id="owner" value="' . $owner . '">';
				echo '<input type="hidden" name="category" id="category" value="' . $category . '">';

				echo ' <INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

				echo '</form>';
			}


			echo "<h4>Recent Activity</h4>";

			function format_age($t)
			{
				if ($t<30) return "LIVE";
				return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
			}

			global $wpdb;
			$table_name3 = $wpdb->prefix . "vw_lsrooms";
			$items =  $wpdb->get_results("SELECT * FROM `$table_name3` ORDER BY edate DESC LIMIT 0, 100");
			echo "<table class='wp-list-table widefat'><thead><tr><th>Channel</th><th>Videos</th><th>Actions</th><th>Last Access</th><th>Type</th></tr></thead>";
			if ($items) foreach ($items as $item)
					if (($fcount = VWvideoShare::importFilesCount( $item->name, VWvideoShare::extensions_flash(), $options['vwls_archive_path']))!='0 (0.00B)')
					{
						echo "<tr><th>" . $item->name . "</th>";

						echo "<td>". $fcount . "</td>";

						$link  = add_query_arg( array( 'playlist_import' => $item->name), admin_url('admin.php?page=video-share-ls') );

						echo '<td><a class="button button-primary" href="' .$link.'">Import</a></td>';
						echo "<td>".format_age(time() - $item->edate)."</td>";
						echo '<td>' . ($item->type==2?"Premium":"Standard") . '</td>';
						echo "</tr>";
					}
				echo '<tr><th>Total</th><th colspan="4">' . VWvideoShare::importFilesCount( '', VWvideoShare::extensions_flash(), $options['vwls_archive_path']) . '</th></tr>';
			echo "</table>";
		}
		//fc above
	}
}

//instantiate
if (class_exists("VWvideoShare")) {
	$videoShare = new VWvideoShare();
}

//Actions and Filters
if (isset($videoShare)) {

	register_activation_hook( __FILE__, array(&$videoShare, 'install' ) );
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );


	add_action( 'init', array(&$videoShare, 'init'),0);
	add_action('admin_menu', array(&$videoShare, 'admin_menu'));
	add_action( 'admin_bar_menu', array(&$videoShare, 'admin_bar_menu'),100 );

	add_action("plugins_loaded", array(&$videoShare , 'plugins_loaded'));

	add_action( 'parse_request', array(&$videoShare, 'parse_request'));

	//archive
	add_filter( 'archive_template', array('VWvideoShare','archive_template') ) ;


	//cron
	add_filter( 'cron_schedules', array(&$videoShare,'cron_schedules'));
	add_action( 'cron_4min_event', array(&$videoShare, 'convertProcessQueue' ) );

	add_action( 'init', array(&$videoShare, 'setup_schedule'));

	//page template
	add_filter( "single_template", array(&$videoShare,'single_template') );
}

//dev only: instead of Save Permalinks
// flush_rewrite_rules();

?>