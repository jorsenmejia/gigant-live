<?php
/*
Plugin Name: Picture Gallery - Frontend Image Uploads, AJAX Photo List
Plugin URI: https://videochat-scripts.com/picture-gallery-plugin/
Description: <strong>Picture Gallery - Frontend Image Uploads, AJAX Photo List</strong> plugin enables users to share pictures in galleries. Can integrate galleries for custom posts. <a href='https://videowhisper.com/tickets_submit.php?topic=Picture-Gallery'>Contact Us</a>
Version: 1.3.7
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("VWpictureGallery"))
{
	class VWpictureGallery {

		function VWpictureGallery() { //constructor

		}

		static function install() {
			// do not generate any output here
			VWpictureGallery::setupOptions();
			VWpictureGallery::picture_post();
			flush_rewrite_rules();
		}

		//! Supported extensions
		static function extensions_picture()
		{
			return array('jpg', 'png', 'gif');
		}


		// Register Custom Post Type
		function picture_post() {

			$options = get_option('VWpictureGalleryOptions');

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			if ($options['pictures'])
				if (!post_type_exists($options['custom_post']))
				{

					$labels = array(
						'name'                => _x( 'Pictures', 'Post Type General Name', 'picture-gallery' ),
						'singular_name'       => _x( 'Picture', 'Post Type Singular Name', 'picture-gallery' ),
						'menu_name'           => __( 'Pictures', 'picture-gallery' ),
						'parent_item_colon'   => __( 'Parent Picture:', 'picture-gallery' ),
						'all_items'           => __( 'All Pictures', 'picture-gallery' ),
						'view_item'           => __( 'View Picture', 'picture-gallery' ),
						'add_new_item'        => __( 'Add New Picture', 'picture-gallery' ),
						'add_new'             => __( 'New Picture', 'picture-gallery' ),
						'edit_item'           => __( 'Edit Picture', 'picture-gallery' ),
						'update_item'         => __( 'Update Picture', 'picture-gallery' ),
						'search_items'        => __( 'Search Pictures', 'picture-gallery' ),
						'not_found'           => __( 'No Pictures found', 'picture-gallery' ),
						'not_found_in_trash'  => __( 'No Pictures found in Trash', 'picture-gallery' ),
					);

					$args = array(
						'label'               => __( 'picture', 'picture-gallery' ),
						'description'         => __( 'Pictures', 'picture-gallery' ),
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
						'menu_icon' => 'dashicons-format-image',
						'capability_type'     => 'post',
					);
					register_post_type( $options['custom_post'], $args );

					// Add new taxonomy, make it hierarchical (like categories)
					$labels = array(
						'name'              => _x( 'Galleries', 'taxonomy general name' ),
						'singular_name'     => _x( 'Gallery', 'taxonomy singular name' ),
						'search_items'      => __( 'Search Galleries', 'picture-gallery' ),
						'all_items'         => __( 'All Galleries', 'picture-gallery' ),
						'parent_item'       => __( 'Parent Gallery' , 'picture-gallery'),
						'parent_item_colon' => __( 'Parent Gallery:', 'picture-gallery' ),
						'edit_item'         => __( 'Edit Gallery' , 'picture-gallery'),
						'update_item'       => __( 'Update Gallery', 'picture-gallery' ),
						'add_new_item'      => __( 'Add New Gallery' , 'picture-gallery'),
						'new_item_name'     => __( 'New Gallery Name' , 'picture-gallery'),
						'menu_name'         => __( 'Galleries' , 'picture-gallery'),
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
				}


		}

		//! Feature Pages and Menus
		function setupPages()
		{
			$options = get_option('VWpictureGalleryOptions');
			if ($options['disableSetupPages']) return;

			$pages = array(
				'videowhisper_pictures' => 'Pictures',
				'videowhisper_picture_upload' => 'Upload Pictures',
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

			update_option('VWpictureGalleryOptions', $options);
		}

		function picture_delete($picture_id)
		{
			$options = get_option('VWpictureGalleryOptions');
			if (get_post_type( $picture_id ) != $options['custom_post']) return;

			//delete source & thumb files
			$filePath = get_post_meta($post_id, 'picture-source-file', true);
			if (file_exists($filePath)) unlink($filePath);
			$filePath = get_post_meta($post_id, 'picture-thumbnail', true);
			if (file_exists($filePath)) unlink($filePath);
		}

		function adminMenu()
		{
			$options = get_option('VWpictureGalleryOptions');

			add_menu_page('Picture Gallery', 'Picture Gallery', 'manage_options', 'picture-gallery', array('VWpictureGallery', 'adminOptions'), 'dashicons-images-alt2',81);
			add_submenu_page("picture-gallery", "Picture Gallery", "Options", 'manage_options', "picture-gallery", array('VWpictureGallery', 'adminOptions'));
			add_submenu_page("picture-gallery", "Upload", "Upload", 'manage_options', "picture-gallery-upload", array('VWpictureGallery', 'adminUpload'));
			add_submenu_page("picture-gallery", "Import", "Import", 'manage_options', "picture-gallery-import", array('VWpictureGallery', 'adminImport'));
			add_submenu_page("picture-gallery", "Documentation", "Documentation", 'manage_options', "picture-gallery-docs", array('VWpictureGallery', 'adminDocs'));

		}

		function plugins_loaded()
		{
			$options = get_option('VWpictureGalleryOptions');

			add_action( 'wp_enqueue_scripts', array('VWpictureGallery','scripts') );

			//translations
			load_plugin_textdomain('picture-gallery', false, dirname(plugin_basename(__FILE__)) .'/languages');

			//prevent wp from adding <p> that breaks JS
			remove_filter ('the_content',  'wpautop');
			//move wpautop filter to BEFORE shortcode is processed
			add_filter( 'the_content', 'wpautop' , 1);
			//then clean AFTER shortcode
			add_filter( 'the_content', 'shortcode_unautop', 100 );


			add_filter( 'manage_'.$options['custom_post'].'_posts_columns', array( 'VWpictureGallery', 'columns_head_picture') , 10);
			add_action( 'manage_' .$options['custom_post']. '_posts_custom_column', array( 'VWpictureGallery', 'columns_content_picture') , 10, 2);

			add_action( 'before_delete_post',  array( 'VWpictureGallery','picture_delete') );

			//picture post page
			add_filter( "the_content", array('VWpictureGallery','picture_page'));


			//! shortcodes
			add_shortcode('videowhisper_pictures', array( 'VWpictureGallery', 'videowhisper_pictures'));
			add_shortcode('videowhisper_picture', array( 'VWpictureGallery', 'videowhisper_picture'));
			add_shortcode('videowhisper_picture_preview', array( 'VWpictureGallery', 'videowhisper_picture_preview'));

			add_shortcode('videowhisper_picture_upload', array( 'VWpictureGallery', 'videowhisper_picture_upload'));
			add_shortcode('videowhisper_picture_import', array( 'VWpictureGallery', 'videowhisper_picture_import'));

			add_shortcode('videowhisper_postpictures', array( 'VWpictureGallery', 'videowhisper_postpictures'));
			add_shortcode('videowhisper_postpictures_process', array( 'VWpictureGallery', 'videowhisper_postpictures_process'));


			//! widgets
			wp_register_sidebar_widget( 'videowhisper_pictures', 'Pictures',  array( 'VWpictureGallery', 'widget_pictures'), array('description' => 'List pictures and updates using AJAX.') );
			wp_register_widget_control( 'videowhisper_pictures', 'videowhisper_pictures', array( 'VWpictureGallery', 'widget_pictures_options') );

			//! ajax

			//ajax pictures
			add_action( 'wp_ajax_vwpg_pictures', array('VWpictureGallery','vwpg_pictures'));
			add_action( 'wp_ajax_nopriv_vwpg_pictures', array('VWpictureGallery','vwpg_pictures'));


			//upload pictures
			add_action( 'wp_ajax_vwpg_upload', array('VWpictureGallery','vwpg_upload'));


		}


		function archive_template( $archive_template ) {
			global $post;

			$options = get_option('VWpictureGalleryOptions');

			if ( get_query_var( 'taxonomy' ) != $options['custom_taxonomy'] ) return $archive_template;

			if ($options['taxonomyTemplate'] == '+plugin')
			{
				$archive_template_new = dirname( __FILE__ ) . '/taxonomy-gallery.php';
				if (file_exists($archive_template_new)) return $archive_template_new;
			}

			$archive_template_new = get_template_directory() . '/' . $options['taxonomyTemplate'];
			if (file_exists($archive_template_new)) return $archive_template_new;
			else return $archive_template;
		}

		//! Widgets

		function widgetSetupOptions()
		{
			$widgetOptions = array(
				'title' => '',
				'perpage'=> '8',
				'perrow' => '',
				'gallery' => '',
				'order_by' => '',
				'category_id' => '',
				'select_category' => '1',
				'select_tags' => '1',
				'select_name' => '1',				
				'select_order' => '1',
				'select_page' => '1',
				'include_css' => '0'

			);

			$options = get_option('VWpictureGalleryWidgetOptions');

			if (!empty($options)) {
				foreach ($options as $key => $option)
					$widgetOptions[$key] = $option;
			}

			update_option('VWpictureGalleryWidgetOptions', $widgetOptions);

			return $widgetOptions;
		}

		function widget_pictures_options($args=array(), $params=array())
		{

			$options = VWpictureGallery::widgetSetupOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWpictureGalleryWidgetOptions', $options);
			}
?>

	<?php _e('Title','picture-gallery'); ?>:<br />
	<input type="text" class="widefat" name="title" value="<?php echo stripslashes($options['title']); ?>" />
	<br /><br />

	<?php _e('Gallery','picture-gallery'); ?>:<br />
	<input type="text" class="widefat" name="gallery" value="<?php echo stripslashes($options['gallery']); ?>" />
	<br /><br />

	<?php _e('Category ID','picture-gallery'); ?>:<br />
	<input type="text" class="widefat" name="category_id" value="<?php echo stripslashes($options['category_id']); ?>" />
	<br /><br />

 <?php _e('Order By','picture-gallery'); ?>:<br />
	<select name="order_by" id="order_by">
  <option value="post_date" <?php echo $options['order_by']=='post_date'?"selected":""?>><?php _e('Date','picture-gallery'); ?></option>
    <option value="picture-views" <?php echo $options['order_by']=='picture-views'?"selected":""?>><?php _e('Views','picture-gallery'); ?></option>
    <option value="picture-lastview" <?php echo $options['order_by']=='picture-lastview'?"selected":""?>><?php _e('Recently Watched','picture-gallery'); ?></option>
</select><br /><br />

	<?php _e('Pictures per Page','picture-gallery'); ?>:<br />
	<input type="text" class="widefat" name="perpage" value="<?php echo stripslashes($options['perpage']); ?>" />
	<br /><br />

	<?php _e('Pictures per Row','picture-gallery'); ?>:<br />
	<input type="text" class="widefat" name="perrow" value="<?php echo stripslashes($options['perrow']); ?>" />
	<br /><br />

 <?php _e('Category Selector','picture-gallery'); ?>:<br />
	<select name="select_category" id="select_category">
  <option value="1" <?php echo $options['select_category']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_category']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Tags Selector','picture-gallery'); ?>:<br />
	<select name="select_tags" id="select_order">
  <option value="1" <?php echo $options['select_tags']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_tags']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Name Selector','picture-gallery'); ?>:<br />
	<select name="select_name" id="select_name">
  <option value="1" <?php echo $options['select_name']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_name']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Order Selector','picture-gallery'); ?>:<br />
	<select name="select_order" id="select_order">
  <option value="1" <?php echo $options['select_order']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_order']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Page Selector','picture-gallery'); ?>:<br />
	<select name="select_page" id="select_page">
  <option value="1" <?php echo $options['select_page']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_page']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Include CSS','picture-gallery'); ?>:<br />
	<select name="include_css" id="include_css">
  <option value="1" <?php echo $options['include_css']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['include_css']?"":"selected"?>>No</option>
</select><br /><br />
	<?php
		}

		function widget_pictures($args=array(), $params=array())
		{

			$options = get_option('VWpictureGalleryWidgetOptions');

			echo stripslashes($args['before_widget']);

			echo stripslashes($args['before_title']);
			echo stripslashes($options['title']);
			echo stripslashes($args['after_title']);

			echo do_shortcode('[videowhisper_pictures gallery="' . $options['gallery'] . '" category_id="' . $options['category_id'] . '" order_by="' . $options['order_by'] . '" perpage="' . $options['perpage'] . '" perrow="' . $options['perrow'] . '" select_category="' . $options['select_category'] . '" select_order="' . $options['select_order'] . '" select_page="' . $options['select_page'] . '" include_css="' . $options['include_css'] . '"]');

			echo stripslashes($args['after_widget']);
		}

		//! AJAX implementation
		function scripts()
		{
			wp_enqueue_script("jquery");
		}

		static function enqueueUI()
		{
			wp_enqueue_script("jquery");
			
			wp_enqueue_style( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.css');
			wp_enqueue_script( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.js', array('jquery'));
		}

		function vwpg_pictures()
		{
			$options = get_option('VWpictureGalleryOptions');

			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$gallery = sanitize_file_name($_GET['gallery']);

			$id = sanitize_file_name($_GET['id']);

			$category = (int) $_GET['cat'];

			$page = (int) $_GET['p'];
			$offset = $page * $perPage;

			$perRow = (int) $_GET['pr'];

			//order
			$order_by = sanitize_file_name($_GET['ob']);
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


			if ($gallery)  $args['gallery'] = $gallery;
			
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


			//get items

			$postslist = get_posts( $args );

			ob_clean();
			//output

			//var_dump ($args);
			//echo $order_by;
			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_pictures&pp=' . $perPage .  '&pr=' .$perRow. '&gallery=' . urlencode($gallery) . '&sc=' . $selectCategory . '&so=' . $selectOrder . '&sn=' . $selectName .  '&sg=' . $selectTags. '&sp=' . $selectPage .  '&id=' . $id;
			
			//without page: changing goes to page 1 but selection persists
			$ajaxurlC = $ajaxurl . '&cat=' . $category . '&ob='.$order_by . '&tags=' . urlencode($tags) . '&name=' . urlencode($name) ; //sel ord
			$ajaxurlO = $ajaxurl . '&ob='. $order_by . '&ob='.$order_by . '&tags=' . urlencode($tags) . '&name=' . urlencode($name); //sel cat
			
			$ajaxurlCO = $ajaxurl . '&cat=' . $category . '&ob='.$order_by ; //select tag name

			$ajaxurlA = $ajaxurl . '&cat=' . $category . '&ob='.$order_by . '&tags=' . urlencode($tags) . '&name=' . urlencode($name);


			//options
			//echo '<div class="videowhisperListOptions">';
			
			//$htmlCode .= '<div class="ui form"><div class="inline fields">';		
			$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' tiny equal width form"><div class="inline fields">';

			if ($selectCategory)
			{
				$htmlCode .= '<div class="field">' . wp_dropdown_categories('show_count=0&echo=0&name=category' . $id . '&hide_empty=1&class=ui+dropdown&show_option_all=' . __('All', 'picture-gallery') . '&selected=' . $category) . '</div>';
				$htmlCode .= '<script>var category' . $id . ' = document.getElementById("category' . $id . '"); 			category' . $id . '.onchange = function(){aurl' . $id . '=\'' . $ajaxurlO.'&cat=\'+ this.value; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading category...</div>\')}
			</script>';
			}

			if ($selectOrder)
			{
				$htmlCode .= '<div class="field"><select class="ui dropdown" id="order_by' . $id . '" name="order_by' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlC.'&ob=\'+ this.value; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Ordering pictures...</div>\')">';
				$htmlCode .= '<option value="">' . __('Order By', 'picture-gallery') . ':</option>';
				$htmlCode .= '<option value="post_date"' . ($order_by == 'post_date'?' selected':'') . '>' . __('Date Added', 'picture-gallery') . '</option>';
				$htmlCode .= '<option value="picture-views"' . ($order_by == 'picture-views'?' selected':'') . '>' . __('Views', 'picture-gallery') . '</option>';
				$htmlCode .= '<option value="picture-lastview"' . ($order_by == 'picture-lastview'?' selected':'') . '>' . __('Viewed Recently', 'picture-gallery') . '</option>';

				
				if ($options['rateStarReview'])
				{
					
					$htmlCode .= '<option value="rateStarReview_rating"' . ($order_by == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'picture-gallery') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingNumber"' . ($order_by == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Ratings Number', 'picture-gallery') . '</option>';					
					$htmlCode .= '<option value="rateStarReview_ratingPoints"' . ($order_by == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'picture-gallery') . '</option>';					
			
				}

				$htmlCode .= '<option value="rand"' . ($order_by == 'rand'?' selected':'') . '>' . __('Random', 'picture-gallery') . '</option>';

								
				$htmlCode .= '</select></div>';
			}
			
			if ($selectTags || $selectName)
			{

				$htmlCode .= '<div class="field"></div>'; //separator

				if ($selectTags)
				{
					$htmlCode .= '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __('Tags', 'picture-gallery')  . '" value="' .htmlspecialchars($tags). '">
					</div></div>';
				}

				if ($selectName)
				{
					$htmlCode .= '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __('Name', 'picture-gallery')  . '" value="' .htmlspecialchars($name). '">
  <div class="ui left corner label">
    <i class="asterisk icon"></i>
  </div>
					</div></div>';
				}

				//search button
				$htmlCode .= '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui icon button" type="submit" name="submit" id="submit" value="' . __('Search', 'picture-gallery') . '" onclick="aurl' . $id . '=\'' . $ajaxurlCO .'&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching Pictures...</div>\')"><i class="search icon"></i></button></div>';

			}

			//reload button
			if ($selectCategory || $selectOrder || $selectTags || $selectName)	$htmlCode .= '<div class="field"></div> <div class="field" data-tooltip="Reload"><button class="ui icon button" type="submit" name="reload" id="reload" value="' . __('Reload', 'picture-gallery') . '" onclick="aurl' . $id . '=\'' . $ajaxurlA .'\'; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Reloading Pictures...</div>\')"><i class="sync icon"></i></button></div>';

			
			$htmlCode .= '</div></div>';


			//list
			if (count($postslist)>0)
			{
				$k = 0;
				foreach ( $postslist as $item )
				{
					if ($perRow) if ($k) if ($k % $perRow == 0) $htmlCode .= '<br>';

							$imagePath = get_post_meta($item->ID, 'picture-thumbnail', true);

						$views = get_post_meta($item->ID, 'picture-views', true) ;
					if (!$views) $views = 0;

					$age = VWpictureGallery::humanAge(time() - strtotime($item->post_date));

					$info = '' . __('Title', 'picture-gallery') . ': ' . $item->post_title . "\r\n" . __('Age', 'picture-gallery') . ': ' . $age . "\r\n" . __('Views', 'picture-gallery') . ": " . $views;
					$views .= ' ' . __('views', 'picture-gallery');

					$canEdit = 0;
					if ($options['editContent'])
						if ($isAdministrator || $item->post_author == $isID) $canEdit = 1;


					$htmlCode .= '<div class="videowhisperPicture">';
					$htmlCode .= '<a href="' . get_permalink($item->ID) . '" title="' . $info . '"><div class="videowhisperPictureTitle">' . $item->post_title. '</div></a>';
					$htmlCode .= '<div class="videowhisperPictureDate">' . $age . '</div>';
					$htmlCode .= '<div class="videowhisperPictureViews">' . $views . '</div>';


					$ratingCode = '';
					if ($options['rateStarReview'])
					{
						$rating = get_post_meta($item->ID, 'rateStarReview_rating', true);
						$max = 5;
						if ($rating > 0) $ratingCode = '<div class="ui star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
						$htmlCode .= '<div class="videowhisperPictureRating">' . $ratingCode . '</div>';
					}


					if ($pmEnabled && $canEdit) $htmlCode .= '<a href="'.$options['editURL']. $item->ID .'"><div class="videowhisperPictureEdit">EDIT</div></a>';


					if (!$imagePath || !file_exists($imagePath)) //video thumbnail?
						{
						$imagePath = plugin_dir_path( __FILE__ ) . 'no_video.png';
						VWpictureGallery::updatePostThumbnail($item->ID);
					}
					else //what about featured image?
						{
						$post_thumbnail_id = get_post_thumbnail_id($item->ID);
						if ($post_thumbnail_id) $post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview') ;

						if (!$post_featured_image) VWpictureGallery::updatePostThumbnail($item->ID);
					}



					$htmlCode .= '<a href="' . get_permalink($item->ID) . '" title="' . $info . '"><IMG src="' . VWpictureGallery::path2url($imagePath) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px" ALT="' . $info . '"></a>';

					$htmlCode .= '</div>
					';

					$k++;
				}

			} else $htmlCode .= __("No pictures.",'picture-gallery');

			//pagination
			if ($selectPage)
			{
				$htmlCode .= '<BR style="clear:both"><div class="ui form"><div class="inline fields">';

				if ($page>0) $htmlCode .= ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA .'&p='.($page-1). '\'; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading previous page...</div>\');"><i class="left arrow icon"></i> ' . __('Previous', 'picture-gallery') . '</a> ';
				
				$htmlCode .= '<a class="ui labeled button" href="#"> ' . __('Page', 'picture-gallery') . ' ' . ($page+1) . ' </a>' ;
				
				if (count($postslist) >= $perPage) $htmlCode .= ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA .'&p='.($page+1). '\'; loadPictures' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading next page...</div>\');">'  . __('Next', 'picture-gallery') . ' <i class="right arrow icon"></i></a> ';
			}
			
			echo $htmlCode;
			
			//output end
			die;

		}


		//! Shortcodes

		function videowhisper_pictures($atts)
		{

			$options = get_option('VWpictureGalleryOptions');

			$atts = shortcode_atts(
				array(
					'perpage'=> $options['perPage'],
					'perrow' => '',
					'gallery' => '',
					'order_by' => '',
					'category_id' => '',
					'select_category' => '1',
					'select_order' => '1',
					'select_page' => '1',	//pagination
					'select_tags' => '1',
					'select_name' => '1',					
					'include_css' => '1',
					'tags' => '',
					'name' => '',					
					'id' => ''
				),
				$atts, 'videowhisper_pictures');


			$id = $atts['id'];
			if (!$id) $id = uniqid();

			VWpictureGallery::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_pictures&pp=' . $atts['perpage'] . '&pr=' . $atts['perrow'] . '&gallery=' . urlencode($atts['gallery']) . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&sc=' . $atts['select_category'] . '&so=' . $atts['select_order'] . '&sp=' . $atts['select_page'] . '&sn=' . $atts['select_name'] .  '&sg=' . $atts['select_tags'] . '&id=' .$id . '&tags=' . urlencode($atts['tags']) . '&name=' . urlencode($atts['name']);

			$htmlCode = <<<HTMLCODE
<script type="text/javascript">
var aurl$id = '$ajaxurl';
var \$j = jQuery.noConflict();
var loader$id;

	function loadPictures$id(message){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperPictures$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: aurl$id,
			success: function(data) {
				\$j("#videowhisperPictures$id").html(data);
				jQuery(".ui.dropdown").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");					
			}
		});
	}


	\$j(function(){
		loadPictures$id();
		setInterval("loadPictures$id('')", 60000);
	});

</script>

<div id="videowhisperPictures$id">
    Loading Pictures...
</div>

HTMLCODE;

			if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		function videowhisper_picture($atts)
		{
			$atts = shortcode_atts(array('picture' => '0'), $atts, 'videowhisper_picture');

			$picture_id = intval($atts['picture']);
			if (!$picture_id) return 'shortcode_preview: Missing picture id!';

			$picture = get_post($picture_id);
			if (!$picture) return 'shortcode_preview: picture #'. $picture_id . ' not found!';

			$options = get_option( 'VWpictureGalleryOptions' );

			//Access Control
			$deny = '';

			//global
			if (!VWpictureGallery::hasPriviledge($options['watchList'])) $deny = 'Your current membership does not allow watching pictures.';

			//by galleries
			$lists = wp_get_post_terms( $picture_id, $options['custom_taxonomy'], array( 'fields' => 'names' ) );

			if (!is_array($lists))
			{
				if (is_wp_error($lists)) echo 'Error: Can not retrieve "' .$options['custom_taxonomy']. '" terms for video post: ' . $lists->get_error_message();

				$lists = array();
			}


			//gallery role required?
			if ($options['role_gallery'])
				foreach ($lists as $key=>$gallery)
				{
					$lists[$key] = $gallery = strtolower(trim($gallery));

					//is role
					if (get_role($gallery)) //video defines access roles
						{
						$deny = 'This picture requires special membership. Your current membership: ' .VWpictureGallery::getRoles() .'.' ;
						if (VWpictureGallery::hasRole($gallery)) //has required role
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
				else $deny = 'Only registered users can watch this picture. Please login first.';

				if (in_array('unpublished', $lists)) $deny = 'This picture has been unpublished.';

				if ($deny)
				{
					$htmlCode .= str_replace('#info#',$deny, html_entity_decode(stripslashes($options['accessDenied'])));
					$htmlCode .= '<br>';
					$htmlCode .= do_shortcode('[videowhisper_picture_preview picture="' . $picture_id . '"]') . VWpictureGallery::poweredBy();
					return $htmlCode;
				}

			//update stats
			$views = get_post_meta($picture_id, 'picture-views', true);
			if (!$views) $views = 0;
			$views++;
			update_post_meta($picture_id, 'picture-views', $views);
			update_post_meta($picture_id, 'picture-lastview', time());


			//display picture:

			//res
			$vWidth = get_post_meta($picture_id, 'picture-width', true);
			if (!$vWidth)
			{
				VWpictureGallery::updatePostThumbnail($picture_id, true);
				$vWidth = get_post_meta($picture_id, 'picture-width', true);
			}
			$vHeight = get_post_meta($picture_id, 'picture-height', true);


			//picture
			$imagePath = get_post_meta($picture_id, 'picture-source-file', true);
			if ($imagePath)
				if (file_exists($imagePath))
					$imageURL = VWpictureGallery::path2url($imagePath);

				$htmlCode = "<IMG SRC='$imageURL' width='$vWidth' height='$vHeight'>";

			return $htmlCode;
		}


		//! update this
		function videowhisper_picture_preview($atts)
		{
			$atts = shortcode_atts(array('picture' => '0'), $atts, 'shortcode_preview');

			$picture_id = intval($atts['picture']);
			if (!$picture_id) return 'shortcode_preview: Missing picture id!';

			$picture = get_post($picture_id);
			if (!$picture) return 'shortcode_preview: picture #'. $picture_id . ' not found!';

			$options = get_option( 'VWpictureGalleryOptions' );

			//res
			$vWidth = $options['thumbWidth'];
			$vHeight = $options['thumbHeight'];

			//snap
			$imagePath = get_post_meta($picture_id, 'picture-snapshot', true);
			if ($imagePath)
				if (file_exists($imagePath))
					$imageURL = VWpictureGallery::path2url($imagePath);
				else VWpictureGallery::updatePostThumbnail($update_id);

				if (!$imagePath) $imageURL = VWpictureGallery::path2url(plugin_dir_path( __FILE__ ) . 'no_picture.png');
				$picture_url = get_permalink($picture_id);

			$htmlCode = "<a href='$picture_url'><IMG SRC='$imageURL' width='$vWidth' height='$vHeight'></a>";

			return $htmlCode;
		}



		function videowhisper_picture_import($atts)
		{
			global $current_user;

			if (!is_user_logged_in())
			{
				return __('Login is required to import pictures!', 'picture-gallery');
			}

			$current_user = wp_get_current_user();
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
			$username = $current_user->$userName;
			
			$options = get_option( 'VWpictureGalleryOptions' );
			if (!VWpictureGallery::hasPriviledge($options['shareList'])) return __('You do not have permissions to share pictures!', 'picture-gallery');

			$atts = shortcode_atts(array('category' => '', 'gallery' => '', 'owner' => '', 'path' => '', 'prefix' => '', 'tag' => '', 'description' => ''), $atts, 'videowhisper_picture_import');

			if (!$atts['path']) return 'videowhisper_picture_import: Path required!';

			if (!file_exists($atts['path'])) return 'videowhisper_picture_import: Path not found!';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<label for="category">' . __('Category', 'picture-gallery') . ': </label><div class="">' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown').'</div>';

			if ($atts['gallery']) $galleries = '<br><label for="gallery">' . __('Gallery', 'picture-gallery') . ': </label>' .$atts['gallery'] . '<input type="hidden" name="gallery" id="gallery" value="'.$atts['gallery'].'"/>';
			elseif ( current_user_can('edit_posts') ) $galleries = '<br><label for="gallery">Gallery(s): </label> <br> <input size="48" maxlength="64" type="text" name="gallery" id="gallery" value="' . $username .'"/> ' . __('(comma separated)', 'picture-gallery');
			else $galleries = '<br><label for="gallery">' . __('gallery', 'picture-gallery') . ': </label> ' . $username .' <input type="hidden" name="gallery" id="gallery" value="' . $username .'"/> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else
				$owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<br><label for="gallery">' . __('Tags', 'picture-gallery') . ': </label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/>';
				else $tags = '<br><label for="tag">' . __('Tag(s)', 'picture-gallery') . ': </label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value=""/> (comma separated)';

				if ($atts['description'] != '_none' )
					if ($atts['description']) $descriptions = '<br><label for="description">' . __('Description', 'picture-gallery') . ': </label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/>';
					else $descriptions = '<br><label for="description">' . __('Description', 'picture-gallery') . ': </label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value=""/>';


					$url  =  get_permalink();

				$htmlCode .= '<h3>' . __('Import Pictures', 'picture-gallery') . '</h3>' . $atts['path'] . $atts['prefix'];

			$htmlCode .=  '<form action="' . $url . '" method="post">';

			$htmlCode .= $categories;
			$htmlCode .= $galleries;
			$htmlCode .= $tags;
			$htmlCode .= $descriptions;
			$htmlCode .= $owners;

			$htmlCode .= '<br>' . VWpictureGallery::importFilesSelect( $atts['prefix'], VWpictureGallery::extensions_picture(), $atts['path']);

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

			$htmlCode .= '</form>';

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		function videowhisper_picture_upload($atts)
		{


			if (!is_user_logged_in())
			{
				return __('Login is required to upload pictures!', 'picture-gallery');
			}

			$current_user = wp_get_current_user();
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
			$username = $current_user->$userName;
			
			$options = get_option( 'VWpictureGalleryOptions' );
			if (!VWpictureGallery::hasPriviledge($options['shareList'])) return __('You do not have permissions to share pictures!', 'picture-gallery');


			$atts = shortcode_atts(array('category' => '', 'gallery' => '', 'owner' => '', 'tag' => '', 'description' => ''), $atts, 'videowhisper_picture_upload');


			VWpictureGallery::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpg_upload';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<div class="field><label for="category">' . __('Category', 'picture-gallery') . ' </label> ' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown').'</div>';

			if ($atts['gallery']) $galleries = '<label for="gallery">' . __('gallery', 'picture-gallery') . '</label>' .$atts['gallery'] . '<input type="hidden" name="gallery" id="gallery" value="'.$atts['gallery'].'"/>';
			elseif ( current_user_can('edit_users') ) $galleries = '<br><label for="gallery">' . __('Gallery(s)', 'picture-gallery') . '</label> <br> <input size="48" maxlength="64" type="text" name="gallery" id="gallery" value="' . $username .'" class="text-input"/> (comma separated)';
			else $galleries = '<label for="gallery">' . __('Gallery', 'picture-gallery') . '</label> ' . $username .' <input type="hidden" name="gallery" id="gallery" value="' . $username .'"/> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else $owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<br><label for="gallery">' . __('Tags', 'picture-gallery') . '</label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/>';
				else $tags = '<br><label for="tag">' . __('Tag(s)', 'picture-gallery') . '</label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value="" class="text-input"/> (comma separated)';

				if ($atts['description'] != '_none' )
					if ($atts['description']) $descriptions = '<br><label for="description">' . __('Description', 'picture-gallery') . '</label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/>';
					else $descriptions = '<br><label for="description">' . __('Description', 'picture-gallery') . '</label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value="" class="text-input"/>';



					$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
				$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
			$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
			$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

			if ($iPhone || $iPad || $iPod || $Android) $mobile = true; else $mobile = false;

			if ($mobile)
			{
				//https://mobilehtml5.org/ts/?id=23
				$mobiles = 'capture="camera"';
				$accepts = 'accept="image/*;capture=camera"';
				$multiples = '';
				$filedrags = '';
			}
			else
			{
				$mobiles = '';
				$accepts = 'accept="image/*"';
				$multiples = 'multiple="multiple"';
				$filedrags = '<div id="filedrag">' . __('or Drag & Drop files to this upload area<br>(select rest of options first)', 'picture-gallery') . '</div>';
			}

			wp_enqueue_script( 'vwpg-upload', plugin_dir_url(  __FILE__ ) . 'upload.js');

			$submits = '<div id="submitbutton">
	<button class="ui button" type="submit" name="upload" id="upload">' . __('Upload Files', 'picture-gallery') . '</button>';

			$htmlCode .= <<<EOHTML
<form class="ui form" id="upload" action="$ajaxurl" method="POST" enctype="multipart/form-data">

<fieldset>
$categories
$galleries
$tags
$descriptions
$owners
<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="128000000" />
EOHTML;

			$htmlCode .= '<legend><h3>' . __('Picture Upload', 'picture-gallery') . '</h3></legend><div> <label for="fileselect">' . __('Pictures to upload', 'picture-gallery') . '</label>';

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

		function vwpg_upload()
		{
			echo 'Upload completed... ';

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


			$gallery = $_SERVER['HTTP_X_GALLERY'] ? $_SERVER['HTTP_X_GALLERY'] :$_POST['GALLERY'];

			//if csv sanitize as array
			if (strpos($gallery, ',') !== FALSE)
			{
				$galleries = explode(',', $gallery);
				foreach ($galleries as $key => $value) $galleries[$key] = sanitize_file_name(trim($value));
				$gallery = $galleries;
			}

			if (!$gallery)
			{
				echo 'Gallery required!';
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
			}


			$description = sanitize_text_field( $_SERVER['HTTP_X_DESCRIPTION'] ? $_SERVER['HTTP_X_DESCRIPTION'] :$_POST['description'] );

			$options = get_option( 'VWpictureGalleryOptions' );

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

				if (!in_array($ext, VWpictureGallery::extensions_picture() ))
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
				$rawdata = $GLOBALS["HTTP_RAW_POST_DATA"];
				if (!$rawdata) $rawdata = file_get_contents("php://input");
				if (!$rawdata)
				{
					echo 'Raw post data missing!';
					exit;
				}

				file_put_contents($path = $dir . generateName($fn), $rawdata );

				$el = array_shift(explode(".", $fn));
				$title = ucwords(str_replace('-', ' ', sanitize_file_name($el) ));

				echo sanitize_text_field($title) . ' ';

				echo VWpictureGallery::importFile($path, $title, $owner, $gallery, $category, $tag, $description);

			}
			else
			{
				// form submit
				$files = $_FILES['fileselect'];

				if ($files['error']) if (is_array($files['error']))
						foreach ($files['error'] as $id => $err)
						{
							if ($err == UPLOAD_ERR_OK) {
								$fn = $files['name'][$id];
								move_uploaded_file( $files['tmp_name'][$id], $path = $dir . generateName($fn) );
								$title = ucwords(str_replace('-', ' ', sanitize_file_name(array_shift(explode(".", $fn)))));

								echo sanitize_text_field($title) . ' ';

								echo VWpictureGallery::importFile($path, $title, $owner, $gallery, $category) . '<br>';
							}
						}

			}


			die;
		}


		function videowhisper_postpictures($atts)
		{



			$options = get_option( 'VWpictureGalleryOptions' );

			$atts = shortcode_atts(
				array(
					'post' => '',
					'perpage' => '8',
					'path' => '',
				), $atts, 'videowhisper_postpictures');

			if (!$atts['post']) return 'No post id was specified, to manage post associated pictures.';

			$channel = get_post( $atts['post'] );				
			if ($channel) $gallery = $channel->post_name;
			
			if ($_GET['gallery_upload']) 
			{
			
			$htmlCode .=  '<br><A class="ui button" href="'.remove_query_arg('gallery_upload').'">Done Uploading Pictures</A>';
			//$gallery = sanitize_file_name($_GET['gallery_upload']);			
			}
			else
			{

			$htmlCode .= '<div class="w-actionbox color_alternate"><h3>Manage Pictures</h3>';

			if ($atts['path']) $htmlCode .= '<p>Available '.$channel->post_title.' pictures: ' . VWpictureGallery::importFilesCount( $channel->post_title, VWpictureGallery::extensions_picture(), $atts['path']) .'</p>';

			$link  = add_query_arg( array( 'gallery_import' => $channel->post_title), get_permalink() );
			$link2  = add_query_arg( array( 'gallery_upload' => $channel->post_title), get_permalink() );

			if ($atts['path']) $htmlCode .= ' <a class="ui button" href="' .$link.'">Import</a> ';
			$htmlCode .= ' <a class="ui button" href="' .$link2.'">Upload</a> ';
			}

			$htmlCode .= '</div>';




			$htmlCode .= '<h4>Pictures</h4>';

			$htmlCode .= do_shortcode('[videowhisper_pictures perpage="' . $atts['perpage'] . '" gallery="' . $gallery .'"]');


			return $htmlCode;
		}

		function videowhisper_postpictures_process($atts)
		{

			$atts = shortcode_atts(
				array(
					'post' => '',
					'post_type' => '',
					'path' =>'',
				), $atts, 'videowhisper_postpictures_process');

			VWpictureGallery::importFilesClean();

			$htmlCode = '';

			if ($channel_upload = sanitize_file_name($_GET['gallery_upload']))
			{
				$htmlCode .= do_shortcode('[videowhisper_picture_upload gallery="'.$channel_upload.'"]');
			}

			if ($channel_name = sanitize_file_name($_GET['gallery_import']))
			{

				$options = get_option( 'VWpictureGalleryOptions' );

				$url  = add_query_arg( array( 'gallery_import' => $channel_name), get_permalink() );


				$htmlCode .=  '<form id="videowhisperImport" name="videowhisperImport" action="' . $url . '" method="post">';

				$htmlCode .= "<h3>Import <b>" . $channel_name . "</b> Pictures to Gallery</h3>";

				$htmlCode .= VWpictureGallery::importFilesSelect( $channel_name, VWpictureGallery::extensions_picture(), $atts['path']);

				$htmlCode .=  '<input type="hidden" name="gallery" id="gallery" value="' . $channel_name . '">';

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

				$htmlCode .=   '<INPUT class="ui g-btn type_primary button button-primary" TYPE="submit" name="import" id="import" value="Import">';

				$htmlCode .=  ' <INPUT class="ui g-btn type_primary button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

				$htmlCode .=  '</form>';
			}

			return $htmlCode;
		}


		//!permission functions

		//if any key matches any listing
		 static function inList($keys, $data)
		{
			if (!$keys) return 0;

			$list = explode(",", strtolower(trim($data)));

			foreach ($keys as $key)
				foreach ($list as $listing)
					if ( strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}

		 static function hasPriviledge($csv)
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

				if (VWpictureGallery::inList($userkeys, $csv)) return 1;
			}

			return 0;
		}

		static function hasRole($role)
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

		static function poweredBy()
		{


			$options = get_option('VWpictureGalleryOptions');

			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';

			return '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Published with VideoWhisper <a href="https://videowhisper.com/">Picture Gallery - Frontent Image Uploads with AJAX</a>.</p></div>';
		}


		//! Custom Post Page

		function single_template($single_template)
		{

			if (!is_single())  return $single_template;

			$options = get_option('VWpictureGalleryOptions');

			$postID = get_the_ID();
			if (get_post_type( $postID ) != $options['custom_post']) return $single_template;

			if ($options['postTemplate'] == '+plugin')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-picture.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}

			$single_template_new = get_template_directory() . '/' . $options['postTemplate'];

			if (file_exists($single_template_new)) return $single_template_new;
			else return $single_template;
		}



		function picture_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			$options = get_option( 'VWpictureGalleryOptions' );

			if (get_post_type( $postID ) != $options['custom_post']) return $content;


			if ($options['pictureWidth']) $wCode = ' width="' . trim($options['pictureWidth']) . '"';
			else $wCode ='';

			$addCode .= '' . '[videowhisper_picture picture="' . $postID . '" embed="1"'.$wCode.']';

			//gallery
			global $wpdb;

			$terms = get_the_terms( $postID, $options['custom_taxonomy'] );

			if ( $terms && ! is_wp_error( $terms ) )
			{

				$addCode .=  '<div class="w-actionbox">';
				foreach ( $terms as $term )
				{

					if (class_exists("VWliveStreaming"))  if ($options['vwls_channel'])
						{

							$channelID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $term->slug . "' and post_type='channel' LIMIT 0,1" );
							
							if ($channelID)
								$addCode .= ' <a title="' . __('Channel', 'picture-gallery') . ': '. $term->name .'" class="ui button" href="'. get_post_permalink( $channelID ) . '">' . $term->name . ' Channel</a> ' ;
							
						}

					$addCode .= ' <a title="' . __('Gallery', 'picture-gallery') . ': '. $term->name .'" class="ui button" href="'. get_term_link( $term->slug, $options['custom_taxonomy']) . '">' . $term->name . '</a> ' ;


				}
				$addCode .=  '</div>';

			}


			$views = get_post_meta($postID, 'picture-views', true);
			if (!$views) $views = 0;

			$addCode .= '<div class="videowhisper_views">' . __('Picture Views', 'picture-gallery') . ': ' . $views . '</div>';
			
			//! show reviews
			if ($options['rateStarReview'])
			{
				//tab : reviews
			if (shortcode_exists("videowhisper_review"))
			$addCode .= '<h3>' . __('My Review', 'picture-gallery') . '</h3>' . do_shortcode('[videowhisper_review content_type="picture" post_id="' . $postID . '" content_id="' . $postID . '"]' );
			else $addCode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';
		
			if (shortcode_exists("videowhisper_reviews"))
			$addCode .= '<h3>' . __('Reviews', 'picture-gallery') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );
					
			}


			return $addCode . $content ;
		}

		function imagecreatefromfile( $filename ) {
			if (!file_exists($filename)) {
				throw new InvalidArgumentException('File "'.$filename.'" not found.');
			}
			
			switch ( strtolower( pathinfo( $filename, PATHINFO_EXTENSION ))) {
			case 'jpeg':
			case 'jpg':
				return $img = @imagecreatefromjpeg($filename);
				break;

			case 'png':
				return  $img = @imagecreatefrompng($filename);
				break;

			case 'gif':
				return  $img = @imagecreatefromgif($filename);
				break;

			default:
				throw new InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
				break;
			}
			
			return $img;
			
		}

		public function generateThumbnail($src, $dest, $post_id = 0)
		{
			if (!file_exists($src)) return;

			$options = get_option( 'VWpictureGalleryOptions' );

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$srcImage = VWpictureGallery::imagecreatefromfile($src);
			if (!$srcImage) return;

			list($width, $height) = @getimagesize($src);
			if (!$width) return;
			
			$destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagejpeg($destImage, $dest, 95);

			if ($post_id)
			{
				update_post_meta( $post_id, 'picture-thumbnail', $dest );
				if ($width) update_post_meta( $post_id, 'picture-width', $width );
				if ($height) update_post_meta( $post_id, 'picture-height', $height );
			}

			//return source dimensions
			return array($width, $height);
		}


		function updatePostThumbnail($post_id, $overwrite = false, $verbose = false)
		{
			//update post image
			$imagePath = get_post_meta($post_id, 'picture-source-file', true);
			$thumbPath = get_post_meta($post_id, 'picture-thumbnail', true);

			if ($verbose)  echo "<br>Updating thumbnail ($post_id, $imagePath,  $thumbPath)";

			if (!$imagePath) return;
			if (!file_exists($imagePath)) return;
			if (filesize($imagePath) < 5) return; //too small

			if ($overwrite || !$thumbPath || !file_exists($thumbPath))
			{
				$path =  dirname($imagePath);
				$thumbPath =  $path . '/' . $post_id . '_thumb.jpg';
				list($width, $height) = VWpictureGallery::generateThumbnail($imagePath, $thumbPath, $post_id);
				if (!$width) return;
				
				$thumbPath = get_post_meta($post_id, 'picture-thumbnail', true);
			}

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


			if (file_exists($thumbPath)) if (filesize($thumbPath)>5)
			{
						// Generate the metadata for the attachment, and update the database record.
						$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbPath );
						wp_update_attachment_metadata( $attach_id, $attach_data );
			
						if ($verbose) var_dump($attach_data);
			
			
						if ($width) update_post_meta( $post_id, 'picture-width', $width );
						if ($height) update_post_meta( $post_id, 'picture-height', $height );
			}			

		}

		function updatePicture($post_id, $overwrite = false)
		{
			//update size and thumb

			if (!$post_id) return;

			$src = get_post_meta($post_id, 'picture-source-file', true);
			if (!$src) return; //source missing

			$srcImage = VWpictureGallery::imagecreatefromfile($src);
			if (!$srcImage) return;

			list($width, $height) = getimagesize($src);

			if ($width) update_post_meta( $post_id, 'picture-width', $width );
			if ($height) update_post_meta( $post_id, 'picture-height', $height );


			$thumbPath = get_post_meta($post_id, 'picture-thumbnail', true);
			if (!$thumbPath || $overwrite)
			{
				$path =  dirname($src);
				$thumbPath =  $path . '/' . $post_id . '_thumb.jpg';

				VWpictureGallery::generateThumbnail($src, $thumbPath, $post_id);
			}
		}


		static function humanAge($t)
		{
			if ($t<30) return "NOW";
			return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
		}


		static function humanFilesize($bytes, $decimals = 2) {
			$sz = 'BKMGTP';
			$factor = floor((strlen($bytes) - 1) / 3);
			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
		}

		static  function path2url($file, $Protocol='https://')
		{
			$url = $Protocol.$_SERVER['HTTP_HOST'];


			//on godaddy hosting uploads is in different folder like /var/www/clients/ ..
			$upload_dir = wp_upload_dir();
			if (strstr($file, $upload_dir['basedir']))
				return  $upload_dir['baseurl'] . str_replace($upload_dir['basedir'], '', $file);

			if (strstr($file, $_SERVER['DOCUMENT_ROOT']))
				return  $url . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);

			return $url . $file;
		}

		static  function path2stream($path, $withExtension=true)
		{
			$options = get_option( 'VWpictureGalleryOptions' );

			$stream = substr($path, strlen($options['streamsPath']));
			if ($stream[0]=='/') $stream = substr($stream, 1);

			if (!file_exists($options['streamsPath'] . '/' . $stream)) return '';
			elseif ($withExtension) return $stream;
			else return pathinfo($stream, PATHINFO_FILENAME);
		}

		//! Import
		function importFilesClean()
		{
			$options = get_option( 'VWpictureGalleryOptions' );

			if (!$options['importClean']) return;
			if (!file_exists($options['importPath'])) return;
			if (!file_exists($options['uploadsPath'])) return;

			//last cleanup
			$lastFile = $options['uploadsPath'] . '/importCleanLast.txt';
			if (file_exists($lastFile)) $lastClean = file_get_contents($lastFile);

			//cleaned recently
			if ($lastClean > time()-36000) return;

			//start clean

			//save time
			$myfile = fopen($lastFile, "w");
			if (!$myfile) return;
			fwrite($myfile, time());
			fclose($myfile);

			//scan files and clean
			$folder = $options['importPath'];
			$extensions = VWpictureGallery::extensions_picture();
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
			if (!file_exists($folder)) return "<div class='error'>Picture folder not found: $folder !</div>";

			VWpictureGallery::importFilesClean();


			$htmlCode .= '';

			//import files
			if ($_POST['import'])
			{

				if (count($importFiles = $_POST['importFiles']))
				{

					$owner = (int) $_POST['owner'];

					global $current_user;
					get_currentuserinfo();

					if (!$owner) $owner = $current_user->ID;
					elseif ($owner != $current_user->ID && ! current_user_can('edit_users')) return "Only admin can import for others!";

					//handle one or many galleries
					$gallery = $_POST['gallery'];

					//if csv sanitize as array
					if (strpos($gallery, ',') !== FALSE)
					{
						$galleries = explode(',', $gallery);
						foreach ($galleries as $key => $value) $galleries[$key] = sanitize_file_name(trim($value));
						$gallery = $galleries;
					}

					if (!$gallery) return "Importing requires a gallery name!";

					//handle one or many tags
					$tag = $_POST['tag'];

					//if csv sanitize as array
					if (strpos($tag, ',') !== FALSE)
					{
						$tags = explode(',', $gallery);
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
						$pictureName = basename($fileName, '.' . $ext) .' '. date("M j", $ztime);

						$htmlCode .= VWpictureGallery::importFile($folder . $fileName, $pictureName, $owner, $gallery, $category, $tag, $description);
					}
				}else $htmlCode .= '<div class="warning">No files selected to import!</div>';

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
				$preview_url = VWpictureGallery::path2url($folder . $preview_name);
				$htmlCode .= '<h4>Preview '.$preview_name.'</h4>';
				$htmlCode .= '<IMG SRC="'.$preview_url. '">';
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

			if (!$fileList) $htmlCode .=  "<div class='warning'>No matching files found!</div>";
			else
			{
				$htmlCode .=
					'<script language="JavaScript">
function toggleImportBoxes(source) {
  var checkboxes = new Array();
  checkboxes = document.getElementsByName(\'importFiles\');
  for (var i = 0; i < checkboxes.length; i++)
    checkboxes[i].checked = source.checked;
}
</script>';
				$htmlCode .=  "<table class='widefat videowhisperTable'>";
				$htmlCode .=  '<thead class=""><tr><th><input type="checkbox" onClick="toggleImportBoxes(this)" /></th><th>File Name</th><th>Preview</th><th>Size</th><th>Date</th></tr></thead>';

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
					$link  = add_query_arg( array( 'gallery_import' => $prefix, 'import_preview' => $fileName), get_permalink() );

					$htmlCode .=  " <a class='button size_small g-btn type_blue' href='" . $link ."'>Play</a> ";
					echo '</td>';
					$htmlCode .=  '<td>' .  VWpictureGallery::humanFilesize($fsize) . '</td>';
					$htmlCode .=  '<td>' .  date('jS F Y H:i:s', filemtime($folder  . $fileName)) . '</td>';
					$htmlCode .=  '</tr>';
				}
				$htmlCode .=  '<tr><td></td><td>'.$tN.' files</td><td></td><td>'.VWpictureGallery::humanFilesize($tS).'</td><td></td></tr>';
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

			return $k . ' ('.VWpictureGallery::humanFilesize($kS).')';
		}


		public function importFile($path, $name, $owner, $galleries, $category = '', $tags = '', $description = '')
		{
			if (!$owner) return "<br>Missing owner!";
			if (!$galleries) return "<br>Missing galleries!";

			$options = get_option( 'VWpictureGalleryOptions' );
			if (!VWpictureGallery::hasPriviledge($options['shareList'])) return '<br>' . __('You do not have permissions to share pictures!', 'picture-gallery');

			if (!file_exists($path)) return "<br>$name: File missing: $path";


			//handle one or many galleries
			if (is_array($galleries)) $gallery = sanitize_file_name(current($galleries));
			else $gallery = sanitize_file_name($galleries);

			if (!$gallery) return "<br>Missing gallery!";

			$htmlCode .= 'File import: ';

			//uploads/owner/gallery/src/file
			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $owner;
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $gallery;
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
				//'post_date'   => $postdate,
				'post_content'   => $description
			);

			if (!VWpictureGallery::hasPriviledge($options['publishList']))
				$post['post_status'] = 'pending';

			$post_id = wp_insert_post( $post);
			if ($post_id)
			{
				update_post_meta( $post_id, 'picture-source-file', $newPath );

				wp_set_object_terms($post_id, $galleries, $options['custom_taxonomy']);

				if ($tags) wp_set_object_terms($post_id, $tags, 'post_tag');

				if ($category) wp_set_post_categories($post_id, array($category));

				VWpictureGallery::updatePicture($post_id, true);
				VWpictureGallery::updatePostThumbnail($post_id, true);

				if ($post['post_status'] == 'pending') $htmlCode .= __('Picture was submitted and is pending approval.','picture-gallery');
				else
					$htmlCode .= '<br>' . __('Picture was published', 'picture-gallery') . ': <a href='.get_post_permalink($post_id).'> #'.$post_id.' '.$name.'</a>' . __('Thumbnail will be processed shortly.', 'picture-gallery') ;
			}
			else $htmlCode .= '<br>Picture post creation failed!';

			return $htmlCode . ' .';
		}


		//! admin listing
		function columns_head_picture($defaults) {
			$defaults['featured_image'] = 'Thumbnail';
			return $defaults;
		}



		function columns_content_picture($column_name, $post_id)
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
						$uploads_url = VWpictureGallery::path2url($upload_dir['basedir']);

						$iurl = $post_featured_image[0];
						$relPath = substr($iurl,strlen($uploads_url));

						if (file_exists($relPath)) $rurl = VWpictureGallery::path2url($relPath);
						else $rurl = $iurl;

						echo '<img src="' . $rurl . '" />';
					}


					$url  = add_query_arg( array( 'updateThumb'  => $post_id), admin_url('admin.php?page=picture-manage') );
					echo '<br><a href="'.$url.'">' . __('Update Thumbnail', 'picture-gallery') . '</a>';


				}
				else
				{
					echo 'Generating ... ';
					VWpictureGallery::updatePostThumbnail($post_id);

				}
			}


		}


		function adminUpload()
		{
?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Upload - Picture Gallery by VideoWhisper.com</h2>
		<?php
			echo do_shortcode("[videowhisper_picture_upload]");
?>
		Use this page to upload one or multiple pictures to server. Configure category, galleries and then choose files or drag and drop files to upload area.
		<br>Gallery(s): Assign pictures to multiple galleries, as comma separated values. Ex: subscriber, premium

		</div>
		<?php
		}

		function adminManage()
		{

			$options = get_option( 'VWpictureGalleryOptions' );

?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Manage Picture - Picture Gallery by VideoWhisper.com</h2>
		<a href="edit.php?post_type=<?php echo $options['custom_post']; ?>">Manage from Pictures Menu</a>
		<BR>
		<?php

			if ( $update_id = (int) $_GET['updateInfo'])
			{
				echo '<BR>Updating Picture #' .$update_id. '... <br>';
				VWpictureGallery::updatePicture($update_id, true);
				unset($_GET['updateInfo']);

			}

			if ( $update_id = (int) $_GET['updateThumb'])
			{
				echo '<BR>Updating Thumbnail for Picture #' .$update_id. '... <br>';
				VWpictureGallery::updatePostThumbnail($update_id, true, true);
				unset($_GET['updateThumb']);
			}


		}

		//! Documentation
		function adminDocs()
		{
?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Documentation: Picture Gallery by VideoWhisper.com</h2>
		
		You can configure this plugin from <a href="admin.php?page=picture-gallery">Settings</a>.
		<br>This plugin creates 2 pages for your site users to access functionality: Pictures, Upload Pictures. You can add these to your menus or use shortcodes below to add functionality to own pages or posts.

<h3>Links</h3>
<UL>
<LI><a href="https://videowhisper.com/tickets_submit.php">Contact Developers: Support or Custom Development</a></LI>
<LI><a href="https://wordpress.org/plugins/picture-gallery/">WordPress Plugin Page</a></LI>
<LI><a href="https://wordpress.org/support/plugin/picture-gallery/">Plugin Forum: Discuss with other users</a></LI>
<LI><a href="https://wordpress.org/support/plugin/picture-gallery/reviews/#new-post">Review this Plugin</a></LI>
</UL>


		<h3>Shortcodes</h3>
		
		<h4>[videowhisper_pictures galleries="" category_id="" order_by="" perpage="" perrow="" select_category="1" select_tags="1" select_name="1" select_order="1" select_page="1" include_css="1" id=""]</h4>
		Displays pictures list. Loads and updates by AJAX. Optional parameters: picture gallery name, maximum pictures per page, maximum pictures per row.
		<br>order_by: post_date / picture-views / picture-lastview
		<br>select attributes enable controls to select category, order, page
		<br>include_css: includes the styles (disable if already loaded once on same page)
		<br>id is used to allow multiple instances on same page (leave blank to generate)

		<h4>[videowhisper_picture_upload gallery="" category="" owner=""]</h4>
		Displays interface to upload pictures.
		<br>gallery: If not defined owner name is used as gallery for regular users. Admins with edit_users capability can write any gallery name. Multiple galleries can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

	   <h4>[videowhisper_picture_import path="" gallery="" category="" owner=""]</h4>
		Displays interface to import pictures.
		<br>path: Path where to import from.
		<br>gallery: If not defined owner name is used as gallery for regular users. Admins with edit_users capability can write any gallery name. Multiple galleries can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

		<h4>[videowhisper_picture picture="0" player="" width=""]</h4>
		Displays video player. Video post ID is required.
		<br>Player: html5/html5-mobile/strobe/strobe-rtmp/html5-hls/ blank to use settings & detection
		<br>Width: Force a fixed width in pixels (ex: 640) and height will be adjusted to maintain aspect ratio. Leave blank to use video size.

		<h4>[videowhisper_picture_preview video="0"]</h4>
		Displays video preview (thumbnail) with link to picture post. Picture post ID is required.

	<h4>[videowhisper_postpictures post="post id"]</h4>
		Manage post associated pictures. Required: post

	<h4>[videowhisper_postpictures_process post="" post_type=""]</h4>
		Process post associated pictures (needs to be on same page with [videowhisper_postpictures] for that to work).

		<h3>Troubleshooting</h3>
		If galleries don't show up right on your theme, copy taxonomy-gallery.php from this plugin folder to your theme folder.

		</div>
		<?php
		}


		//! Settings
		function adminOptionsDefault()
		{
			$root_url = get_bloginfo( "url" ) . "/";
			$upload_dir = wp_upload_dir();

			return array(
				'allowDebug' => '1',
				
				'rateStarReview' => '1',

				'editURL' => $root_url . 'edit-content?editID=',
				'editContent' => 'all',

				'disableSetupPages' => '0',
				'vwls_gallery' => '1',

				'importPath' => '/home/[your-account]/public_html/streams/',
				'importClean' => '45',
				'deleteOnImport' => '1',

				'vwls_channel' => '1',

				'custom_post' => 'picture',
				'custom_taxonomy' => 'gallery',

				'pictures' => '1',

				'postTemplate' => '+plugin',
				'taxonomyTemplate' => '+plugin',

				'pictureWidth' => '',

				'thumbWidth' => '240',
				'thumbHeight' => '180',
				'perPage' =>'12',

				'shareList' => 'Super Admin, Administrator, Editor, Author, Contributor',
				'publishList' => 'Super Admin, Administrator, Editor, Author',

				'role_gallery' => '1',

				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber, Guest',
				'accessDenied' => '<h3>Access Denied</h3>
<p>#info#</p>',

				'uploadsPath' => $upload_dir['basedir'] . '/vw_pictures',

				'customCSS' => <<<HTMLCODE
<style type="text/css">

.videowhisperPicture
{
position: relative;
display:inline-block;

border:1px solid #aaa;
background-color:#777;
padding: 0px;
margin: 2px;

width: 240px;
height: 180px;
}

.videowhisperPicture:hover {
	border:1px solid #fff;
}

.videowhisperPicture IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperPictureTitle
{
position: absolute;
top:0px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureEdit
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
}

.videowhisperPictureDuration
{
position: absolute;
bottom:5px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureDate
{
position: absolute;
bottom:5px;
right:0px;
margin: 8px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureViews
{
position: absolute;
bottom:16px;
right:0px;
margin: 8px;
font-size: 10px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperPictureRating
{
position: absolute;
bottom: 5px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}

</style>

HTMLCODE
				,
				'videowhisper' => 0
			);

		}

		function setupOptions() {

			$adminOptions = VWpictureGallery::adminOptionsDefault();

			$options = get_option('VWpictureGalleryOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWpictureGalleryOptions', $adminOptions);

			return $adminOptions;
		}


		function adminOptions()
		{
			$options = VWpictureGallery::setupOptions();

			// if ($options['convertQueue']) $options['convertQueue'] = trim($options['convertQueue']);


			if (isset($_GET['cancelConversions']))
			{
				$options['convertQueue'] = '';
				update_option('VWpictureGalleryOptions', $options);
			}

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWpictureGalleryOptions', $options);
			}


			VWpictureGallery::setupPages();

			$optionsDefault = VWpictureGallery::adminOptionsDefault();

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'server';
?>


<div class="wrap">
<?php screen_icon(); ?>
<h2>Picture Gallery by VideoWhisper.com</h2>
For more details on using this plugin see <a href="admin.php?page=picture-gallery-docs">Documentation</a>.

<h2 class="nav-tab-wrapper">
	<a href="<?php echo get_permalink(); ?>admin.php?page=picture-gallery&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>"><?php _e('Server','picture-gallery'); ?></a>
	<a href="<?php echo get_permalink(); ?>admin.php?page=picture-gallery&tab=share" class="nav-tab <?php echo $active_tab=='share'?'nav-tab-active':'';?>"><?php _e('Share','picture-gallery'); ?></a>
	<a href="<?php echo get_permalink(); ?>admin.php?page=picture-gallery&tab=display" class="nav-tab <?php echo $active_tab=='display'?'nav-tab-active':'';?>"><?php _e('Display','picture-gallery'); ?></a>
	<a href="<?php echo get_permalink(); ?>admin.php?page=picture-gallery&tab=access" class="nav-tab <?php echo $active_tab=='access'?'nav-tab-active':'';?>"><?php _e('Access Control','picture-gallery'); ?></a>
</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<?php
			switch ($active_tab)
			{


				//! server options
			case 'server':
?>
<h3><?php _e('Server Configuration','picture-gallery'); ?></h3>

<h4><?php _e('Uploads Path','picture-gallery'); ?></h4>
<p><?php _e('Path where picture files will be stored. Make sure you use a location outside plugin folder to avoid losing files on updates and plugin uninstallation.','picture-gallery'); ?></p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<br>Ex: /home/-your-account-/public_html/wp-content/uploads/vw_pictureGallery
<br>If you ever decide to change this, previous files must remain in old location.

<h3><?php _e('Plugin Integrations','picture-gallery'); ?></h3>
<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable Reviews</h4>
<?php
				if (is_plugin_active('rate-star-review/rate-star-review.php')) echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>'; else echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['rateStarReview']?"selected":""?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.
<?php
				break;

			case 'display':
				//! display options

				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));
				$options['custom_post'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_post']));
				$options['custom_taxonomy'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_taxonomy']));

?>
<h3><?php _e('Display &amp; Listings','picture-gallery'); ?></h3>

<h4>Interface Class(es)</h4>
<input name="interfaceClass" type="text" id="interfaceClass" size="30" maxlength="128" value="<?php echo $options['interfaceClass']?>"/>
<br>Extra class to apply to interface (using Semantic UI). Use inverted when theme uses a dark mode (a dark background with white text) or for contrast. Ex: inverted
<br>Some common Semantic UI classes: inverted = dark mode or contrast, basic = no formatting, secondary/tertiary = greys, red/orange/yellow/olive/green/teal/blue/violet/purple/pink/brown/grey/black = colors . Multiple classes can be combined, divided by space. Ex: inverted, basic pink, secondary green, secondary 

<h4>Setup Pages</h4>
<select name="disableSetupPages" id="disableSetupPages">
  <option value="0" <?php echo $options['disableSetupPages']?"":"selected"?>>Yes</option>
  <option value="1" <?php echo $options['disableSetupPages']?"selected":""?>>No</option>
</select>
<br>Create pages for main functionality. Also creates a menu with these pages (VideoWhisper) that can be added to themes. If you delete the pages this option recreates these if not disabled.

<h4>Picture Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="12" maxlength="32" value="<?php echo $options['custom_post']?>"/>
<br>Custom post name for pictures (only alphanumeric, lower case). Will be used for picture urls. Ex: video
<br><a href="options-permalink.php">Save permalinks</a> to activate new url scheme.
<br>Warning: Changing post type name at runtime will hide previously added items. Previous posts will only show when their post type name is restored.

<h4>Picture Post Taxonomy Name</h4>
<input name="custom_taxonomy" type="text" id="custom_taxonomy" size="12" maxlength="32" value="<?php echo $options['custom_taxonomy']?>"/>
<br>Special taxonomy for organising pictures. Ex: gallery

<h4><?php _e('Default Pictures Per Page','picture-gallery'); ?></h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>


<h4><?php _e('Thumbnail Width','picture-gallery'); ?></h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4><?php _e('Thumbnail Height','picture-gallery'); ?></h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>

<h4>Picture Post Template Filename</h4>
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


<h4>Gallery Template Filename</h4>
<input name="taxonomyTemplate" type="text" id="taxonomyTemplate" size="20" maxlength="64" value="<?php echo $options['taxonomyTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render gallery post page. Ex: page.php, single.php
<?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_template_directory() . '/' . $options['taxonomyTemplate'];
					echo '<br>' . $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.


<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="ID" <?php echo $options['userName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Used for default user gallery. Your username with current settings:
<?php
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				echo $username = $current_user->$userName;				
?>

<h4><?php _e('Custom CSS','picture-gallery'); ?></h4>
<textarea name="customCSS" id="customCSS" cols="64" rows="5"><?php echo $options['customCSS']?></textarea>
<BR><?php _e('Styling used in elements added by this plugin. Must include CSS container &lt;style type=&quot;text/css&quot;&gt; &lt;/style&gt; .','picture-gallery'); ?>
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['customCSS']?></textarea>

<h4><?php _e('Show VideoWhisper Powered by','picture-gallery'); ?></h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>
<br><?php _e('Show a mention that pictures were posted with VideoWhisper plugin.
','picture-gallery'); ?>
<?php
				break;

			case 'share':
				//! share options
?>
<h3><?php _e('Picture Sharing','picture-gallery'); ?></h3>

<h4><?php _e('Users allowed to share pictures','picture-gallery'); ?></h4>
<textarea name="shareList" cols="64" rows="2" id="shareList"><?php echo $options['shareList']?></textarea>
<BR><?php _e('Who can share pictures: comma separated Roles, user Emails, user ID numbers.','picture-gallery'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','picture-gallery'); ?>

<h4><?php _e('Users allowed to directly publish pictures','picture-gallery'); ?></h4>
<textarea name="publishList" cols="64" rows="2" id="publishList"><?php echo $options['publishList']?></textarea>
<BR><?php _e('Users not in this list will add pictures as "pending".','picture-gallery'); ?>
<BR><?php _e('Who can publish pictures: comma separated Roles, user Emails, user ID numbers.','picture-gallery'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','picture-gallery'); ?>

<?php
				break;


			case 'access':
				//! vod options
				$options['accessDenied'] = htmlentities(stripslashes($options['accessDenied']));

?>
<h3>Membership / Content On Demand</h3>

<h4>Members allowed to watch picture</h4>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo $options['watchList']?></textarea>
<BR>Global picture access list: comma separated Roles, user Emails, user ID numbers. Ex: <i>Subscriber, Author, submit.ticket@videowhisper.com, 1</i>
<BR>"Guest" will allow everybody including guests (unregistered users) to watch pictures.

<h4>Role galleries</h4>
Enables access by role galleries: Assign picture to a gallery that is a role name.
<br><select name="role_gallery" id="role_gallery">
  <option value="1" <?php echo $options['role_gallery']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['role_gallery']?"":"selected"?>>No</option>
</select>
<br>Multiple roles can be assigned to same picture. User can have any of the assigned roles, to watch. If user has required role, access is granted even if not in global access list.
<br>Pictures without role galleries are accessible as per global picture access.

<h4>Exceptions</h4>
Assign pictures to these galleries:
<br><b>free</b> : Anybody can watch, including guests.
<br><b>registered</b> : All members can watch.
<br><b>unpublished</b> : Picture is not accessible.

<h4>Access denied message</h4>
<textarea name="accessDenied" cols="64" rows="3" id="accessDenied"><?php echo $options['accessDenied']?>
</textarea>
<BR>HTML info, shows with preview if user does not have access to watch picture.
<br>Including #info# will mention rule that was applied.


<h4>Paid Membership and Content</h4>
Solution was tested and developed in combination with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content</a>: Sell membership and content based on virtual wallet credits/tokens. Credits/tokens can be purchased with real money. This plugin also allows users to sell individual pictures (will get an edit button to set price and duration).
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
<br>Allow owner and admin to edit content options for videos, from frontend. This will show an edit button on listings that can be edited by current user.

<h4>Edit Content URL</h4>
<input name="editURL" type="text" id="editURL" size="100" maxlength="256" value="<?php echo $options['editURL']?>"/>
<BR>Detected: <?php echo $editURL ?>


<?php
				break;

			}

			if (!in_array($active_tab, array( 'shortcodes')) ) submit_button(); ?>

</form>
</div>
	 <?php
		}

		function adminImport()
		{
			$options = VWpictureGallery::setupOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWpictureGalleryOptions', $options);
			}


			screen_icon(); ?>
<h2>Import Pictures from Folder</h2>
	Use this to mass import any number of pictures already existent on server.

<?php
			if (file_exists($options['importPath'])) echo do_shortcode('[videowhisper_picture_import path="' . $options['importPath'] . '"]');
			else echo 'Import folder not found on server: '. $options['importPath'];
?>
<h3>Import Settings</h3>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h4>Import Path</h4>
<p>Server path to import pictures from</p>
<input name="importPath" type="text" id="importPath" size="100" maxlength="256" value="<?php echo $options['importPath']?>"/>
<br>Ex: /home/[youraccount]/public_html/streams/
<h4>Delete Original on Import</h4>
<select name="deleteOnImport" id="deleteOnImport">
  <option value="1" <?php echo $options['deleteOnImport']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['deleteOnImport']?"":"selected"?>>No</option>
</select>
<br>Remove original file after copy to new location.
<h4>Import Clean</h4>
<p>Delete pictures older than:</p>
<input name="importClean" type="text" id="importClean" size="5" maxlength="8" value="<?php echo $options['importClean']?>"/>days
<br>Set 0 to disable automated cleanup. Cleanup does not occur more often than 10h to prevent high load.
<?php submit_button(); ?>
</form>
	<?php



		}


	}

}


//instantiate
if (class_exists("VWpictureGallery")) {
	$pictureGallery = new VWpictureGallery();
}

//Actions and Filters
if (isset($pictureGallery)) {

	register_activation_hook( __FILE__, array(&$pictureGallery, 'install' ) );
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

	add_action( 'init', array(&$pictureGallery, 'picture_post'),0);
	add_action('admin_menu', array(&$pictureGallery, 'adminMenu'));
	add_action("plugins_loaded", array(&$pictureGallery , 'plugins_loaded'));

	//archive
	add_filter( 'archive_template', array('VWpictureGallery','archive_template') ) ;

	//page template
	add_filter( "single_template", array(&$pictureGallery,'single_template') );
}