<?php
/*
Plugin Name: Paid Membership, Content, Downloads
Plugin URI: https://videochat-scripts.com/
Description: <strong>Paid Membership, Content, Downloads</strong>: Sell membership, content, downloads based on virtual wallet credits/tokens. Credits/tokens can be purchased with real money using MyCred and TeraWallet for WooCommerce. Control access to content (including custom posts) by membership. Enable uploading files for membership/paid downloads from dedicated frontend pages.  <a href='https://videowhisper.com/tickets_submit.php?about=paid-membership'>Contact Us</a>
Version: 1.6.6
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/
const VW_PM_DEVMODE = 0;

if (VW_PM_DEVMODE) ini_set('display_errors', 1);

if (!class_exists("VWpaidMembership"))
{
	class VWpaidMembership {

		function VWpaidMembership() { //constructor
		}

		//! Plugin Hooks
		function init()
		{
			//add_action('wp_loaded', array('VWpaidMembership','setupPages'));

			self::download_post();
		}

		function activation()
		{
			wp_schedule_event(time(), 'daily', 'cron_membership_update');
		}

		function deactivation()
		{
			wp_clear_scheduled_hook('cron_membership_update');
		}

		function plugins_loaded()
		{
			//settings link in plugins view
			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWpaidMembership','settings_link') );

			$options = get_option('VWpaidMembershipOptions');

			//shortcodes
			add_shortcode('videowhisper_membership_buy', array( 'VWpaidMembership', 'videowhisper_membership_buy'));
			add_shortcode('videowhisper_content_edit', array( 'VWpaidMembership', 'videowhisper_content_edit'));
			add_shortcode('videowhisper_my_wallet', array( 'VWpaidMembership', 'videowhisper_my_wallet'));

			//membership content
			add_action('add_meta_boxes', array('VWpaidMembership','add_meta_boxes') );
			add_action('save_post', array('VWpaidMembership','save_post') );
			add_filter('the_content', array('VWpaidMembership','the_content') );


			//downloads
			add_action( 'before_delete_post',  array( 'VWpaidMembership','download_delete') );

			//download post page
			if ($options['downloads']) add_filter( "the_content", array('VWpaidMembership','download_page'));


			//! download shortcodes
			add_shortcode('videowhisper_downloads', array( 'VWpaidMembership', 'videowhisper_downloads'));
			add_shortcode('videowhisper_download', array( 'VWpaidMembership', 'videowhisper_download'));
			add_shortcode('videowhisper_download_preview', array( 'VWpaidMembership', 'videowhisper_download_preview'));

			add_shortcode('videowhisper_download_upload', array( 'VWpaidMembership', 'videowhisper_download_upload'));
			add_shortcode('videowhisper_download_import', array( 'VWpaidMembership', 'videowhisper_download_import'));

			add_shortcode('videowhisper_postdownloads', array( 'VWpaidMembership', 'videowhisper_postdownloads'));
			add_shortcode('videowhisper_postdownloads_process', array( 'VWpaidMembership', 'videowhisper_postdownloads_process'));

			//! widgets
			wp_register_sidebar_widget( 'videowhisper_downloads', 'downloads',  array( 'VWpaidMembership', 'widget_downloads'), array('description' => 'List downloads and updates using AJAX.') );
			wp_register_widget_control( 'videowhisper_downloads', 'videowhisper_downloads', array( 'VWpaidMembership', 'widget_downloads_options') );

			//! downloads ajax

			//ajax downloads
			add_action( 'wp_ajax_vwpm_downloads', array('VWpaidMembership','vwpm_downloads'));
			add_action( 'wp_ajax_nopriv_vwpm_downloads', array('VWpaidMembership','vwpm_downloads'));


			//upload downloads
			add_action( 'wp_ajax_vwpm_upload', array('VWpaidMembership','vwpm_upload'));

		}

		static function download_delete($download_id)
		{
			$options = get_option('VWpaidMembershipOptions');
			if (get_post_type( $download_id ) != $options['custom_post']) return;

			//delete source & thumb files
			$filePath = get_post_meta($post_id, 'download-source-file', true);
			if (file_exists($filePath)) unlink($filePath);
			$filePath = get_post_meta($post_id, 'download-thumbnail', true);
			if (file_exists($filePath)) unlink($filePath);
		}
		
		
		function archive_template( $archive_template ) {
			global $post;

			$options = get_option('VWpaidMembershipOptions');

			if ( get_query_var( 'taxonomy' ) != $options['custom_taxonomy'] ) return $archive_template;

			if ($options['taxonomyTemplate'] == '+plugin')
			{
				$archive_template_new = dirname( __FILE__ ) . '/taxonomy-collection.php';
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
				'collection' => '',
				'order_by' => '',
				'category_id' => '',
				'select_category' => '1',
				'select_tags' => '1',
				'select_name' => '1',
				'select_order' => '1',
				'select_page' => '1',
				'include_css' => '0'

			);

			$options = get_option('VWpaidMembershipWidgetOptions');

			if (!empty($options)) {
				foreach ($options as $key => $option)
					$widgetOptions[$key] = $option;
			}

			update_option('VWpaidMembershipWidgetOptions', $widgetOptions);

			return $widgetOptions;
		}

		function widget_downloads_options($args=array(), $params=array())
		{

			$options = self::widgetSetupOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWpaidMembershipWidgetOptions', $options);
			}
?>

	<?php _e('Title','paid-membership'); ?>:<br />
	<input type="text" class="widefat" name="title" value="<?php echo stripslashes($options['title']); ?>" />
	<br /><br />

	<?php _e('Collection','paid-membership'); ?>:<br />
	<input type="text" class="widefat" name="collection" value="<?php echo stripslashes($options['collection']); ?>" />
	<br /><br />

	<?php _e('Category ID','paid-membership'); ?>:<br />
	<input type="text" class="widefat" name="category_id" value="<?php echo stripslashes($options['category_id']); ?>" />
	<br /><br />

 <?php _e('Order By','paid-membership'); ?>:<br />
	<select name="order_by" id="order_by">
  <option value="post_date" <?php echo $options['order_by']=='post_date'?"selected":""?>><?php _e('Date','paid-membership'); ?></option>
    <option value="download-views" <?php echo $options['order_by']=='download-views'?"selected":""?>><?php _e('Views','paid-membership'); ?></option>
    <option value="download-lastview" <?php echo $options['order_by']=='download-lastview'?"selected":""?>><?php _e('Recently Watched','paid-membership'); ?></option>
</select><br /><br />

	<?php _e('Downloads per Page','paid-membership'); ?>:<br />
	<input type="text" class="widefat" name="perpage" value="<?php echo stripslashes($options['perpage']); ?>" />
	<br /><br />

	<?php _e('Downloads per Row','paid-membership'); ?>:<br />
	<input type="text" class="widefat" name="perrow" value="<?php echo stripslashes($options['perrow']); ?>" />
	<br /><br />

 <?php _e('Category Selector','paid-membership'); ?>:<br />
	<select name="select_category" id="select_category">
  <option value="1" <?php echo $options['select_category']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_category']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Tags Selector','paid-membership'); ?>:<br />
	<select name="select_tags" id="select_order">
  <option value="1" <?php echo $options['select_tags']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_tags']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Name Selector','paid-membership'); ?>:<br />
	<select name="select_name" id="select_name">
  <option value="1" <?php echo $options['select_name']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_name']?"":"selected"?>>No</option>
</select><br /><br />

 <?php _e('Order Selector','paid-membership'); ?>:<br />
	<select name="select_order" id="select_order">
  <option value="1" <?php echo $options['select_order']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_order']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Page Selector','paid-membership'); ?>:<br />
	<select name="select_page" id="select_page">
  <option value="1" <?php echo $options['select_page']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['select_page']?"":"selected"?>>No</option>
</select><br /><br />

	<?php _e('Include CSS','paid-membership'); ?>:<br />
	<select name="include_css" id="include_css">
  <option value="1" <?php echo $options['include_css']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['include_css']?"":"selected"?>>No</option>
</select><br /><br />
	<?php
		}

		function widget_downloads($args=array(), $params=array())
		{

			$options = get_option('VWpaidMembershipWidgetOptions');

			echo stripslashes($args['before_widget']);

			echo stripslashes($args['before_title']);
			echo stripslashes($options['title']);
			echo stripslashes($args['after_title']);

			echo do_shortcode('[videowhisper_downloads collection="' . $options['collecion'] . '" category_id="' . $options['category_id'] . '" order_by="' . $options['order_by'] . '" perpage="' . $options['perpage'] . '" perrow="' . $options['perrow'] . '" select_category="' . $options['select_category'] . '" select_order="' . $options['select_order'] . '" select_page="' . $options['select_page'] . '" include_css="' . $options['include_css'] . '"]');

			echo stripslashes($args['after_widget']);
		}

		//widgets:end


		//! AJAX implementation
		function scripts()
		{
			wp_enqueue_script("jquery");
		}


		function vwpm_downloads()
		{
			$options = get_option('VWpaidMembershipOptions');

			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$collection = sanitize_file_name($_GET['collection']);

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


			if ($collection)  $args['collection'] = $collection;

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
			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpm_downloads&pp=' . $perPage .  '&pr=' .$perRow. '&collection=' . urlencode($collection) . '&sc=' . $selectCategory . '&so=' . $selectOrder . '&sn=' . $selectName .  '&sg=' . $selectTags. '&sp=' . $selectPage .  '&id=' . $id;

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
				$htmlCode .= '<div class="field">' . wp_dropdown_categories('show_count=0&echo=0&name=category' . $id . '&hide_empty=1&class=ui+dropdown&show_option_all=' . __('All', 'paid-membership') . '&selected=' . $category) . '</div>';
				$htmlCode .= '<script>var category' . $id . ' = document.getElementById("category' . $id . '"); 			category' . $id . '.onchange = function(){aurl' . $id . '=\'' . $ajaxurlO.'&cat=\'+ this.value; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading category...</div>\')}
			</script>';
			}

			if ($selectOrder)
			{
				$htmlCode .= '<div class="field"><select class="ui dropdown" id="order_by' . $id . '" name="order_by' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlC.'&ob=\'+ this.value; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Ordering downloads...</div>\')">';
				$htmlCode .= '<option value="">' . __('Order By', 'paid-membership') . ':</option>';
				$htmlCode .= '<option value="post_date"' . ($order_by == 'post_date'?' selected':'') . '>' . __('Date Added', 'paid-membership') . '</option>';
				$htmlCode .= '<option value="download-views"' . ($order_by == 'download-views'?' selected':'') . '>' . __('Views', 'paid-membership') . '</option>';
				$htmlCode .= '<option value="download-lastview"' . ($order_by == 'download-lastview'?' selected':'') . '>' . __('Viewed Recently', 'paid-membership') . '</option>';


				if ($options['rateStarReview'])
				{

					$htmlCode .= '<option value="rateStarReview_rating"' . ($order_by == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'paid-membership') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingNumber"' . ($order_by == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Ratings Number', 'paid-membership') . '</option>';
					$htmlCode .= '<option value="rateStarReview_ratingPoints"' . ($order_by == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'paid-membership') . '</option>';

				}

				$htmlCode .= '<option value="rand"' . ($order_by == 'rand'?' selected':'') . '>' . __('Random', 'paid-membership') . '</option>';


				$htmlCode .= '</select></div>';
			}

			if ($selectTags || $selectName)
			{

				$htmlCode .= '<div class="field"></div>'; //separator

				if ($selectTags)
				{
					$htmlCode .= '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __('Tags', 'paid-membership')  . '" value="' .htmlspecialchars($tags). '">
					</div></div>';
				}

				if ($selectName)
				{
					$htmlCode .= '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __('Name', 'paid-membership')  . '" value="' .htmlspecialchars($name). '">
  <div class="ui left corner label">
    <i class="asterisk icon"></i>
  </div>
					</div></div>';
				}

				//search button
				$htmlCode .= '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui icon button" type="submit" name="submit" id="submit" value="' . __('Search', 'paid-membership') . '" onclick="aurl' . $id . '=\'' . $ajaxurlCO .'&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching downloads...</div>\')"><i class="search icon"></i></button></div>';

			}

			//reload button
			if ($selectCategory || $selectOrder || $selectTags || $selectName) $htmlCode .= '<div class="field"></div> <div class="field" data-tooltip="Reload"><button class="ui icon button" type="submit" name="reload" id="reload" value="' . __('Reload', 'paid-membership') . '" onclick="aurl' . $id . '=\'' . $ajaxurlA .'\'; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Reloading downloads...</div>\')"><i class="sync icon"></i></button></div>';


			$htmlCode .= '</div></div>';


			//list
			if (count($postslist)>0)
			{
				$k = 0;
				foreach ( $postslist as $item )
				{
					if ($perRow) if ($k) if ($k % $perRow == 0) $htmlCode .= '<br>';

							$imagePath = get_post_meta($item->ID, 'download-thumbnail', true);

						$views = get_post_meta($item->ID, 'download-views', true) ;
					if (!$views) $views = 0;

					$age = self::humanAge(time() - strtotime($item->post_date));

					$info = '' . __('Title', 'paid-membership') . ': ' . $item->post_title . "\r\n" . __('Age', 'paid-membership') . ': ' . $age . "\r\n" . __('Views', 'paid-membership') . ": " . $views;
					$views .= ' ' . __('views', 'paid-membership');

					$canEdit = 0;
					if ($options['editContent'])
						if ($isAdministrator || $item->post_author == $isID) $canEdit = 1;


						$htmlCode .= '<div class="videowhisperDownload">';
					$htmlCode .= '<a href="' . get_permalink($item->ID) . '" title="' . $info . '"><div class="videowhisperDownloadTitle">' . $item->post_title. '</div></a>';
					$htmlCode .= '<div class="videowhisperDownloadDate">' . $age . '</div>';
					$htmlCode .= '<div class="videowhisperDownloadViews">' . $views . '</div>';


					$ratingCode = '';
					if ($options['rateStarReview'])
					{
						$rating = get_post_meta($item->ID, 'rateStarReview_rating', true);
						$max = 5;
						if ($rating > 0) $ratingCode = '<div class="ui star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
						$htmlCode .= '<div class="videowhisperDownloadRating">' . $ratingCode . '</div>';
					}


					if ($pmEnabled && $canEdit) $htmlCode .= '<a href="'. add_query_arg('editID', $item->ID, get_permalink($options['p_videowhisper_content_edit']) ) .'"><div class="videowhisperDownloadEdit">EDIT</div></a>';


					if (!$imagePath || !file_exists($imagePath)) //video thumbnail?
						{
						$imagePath = plugin_dir_path( __FILE__ ) . 'default_picture.png';
						self::updatePostThumbnail($item->ID);
					}
					else //what about featured image?
						{
						$post_thumbnail_id = get_post_thumbnail_id($item->ID);
						if ($post_thumbnail_id) $post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview') ;

						if (!$post_featured_image) self::updatePostThumbnail($item->ID);
					}



					$htmlCode .= '<a href="' . get_permalink($item->ID) . '" title="' . $info . '"><IMG src="' . self::path2url($imagePath) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px" ALT="' . $info . '"></a>';

					$htmlCode .= '</div>
					';

					$k++;
				}

			} else $htmlCode .= __("No downloads.",'paid-membership');

			//pagination
			if ($selectPage)
			{
				$htmlCode .= '<BR style="clear:both"><div class="ui form"><div class="inline fields">';

				if ($page>0) $htmlCode .= ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA .'&p='.($page-1). '\'; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading previous page...</div>\');"><i class="left arrow icon"></i> ' . __('Previous', 'paid-membership') . '</a> ';

				$htmlCode .= '<a class="ui labeled button" href="#"> ' . __('Page', 'paid-membership') . ' ' . ($page+1) . ' </a>' ;

				if (count($postslist) >= $perPage) $htmlCode .= ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlA .'&p='.($page+1). '\'; loadDownloads' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Loading next page...</div>\');">'  . __('Next', 'paid-membership') . ' <i class="right arrow icon"></i></a> ';
			}

			echo $htmlCode;

			//output end
			die;

		}

		//ajax:end

		//! Download Shortcodes

		function videowhisper_downloads($atts)
		{

			$options = get_option('VWpaidMembershipOptions');

			$atts = shortcode_atts(
				array(
					'perpage'=> $options['perPage'],
					'perrow' => '',
					'collection' => '',
					'order_by' => '',
					'category_id' => '',
					'select_category' => '1',
					'select_order' => '1',
					'select_page' => '1', //pagination
					'select_tags' => '1',
					'select_name' => '1',
					'include_css' => '1',
					'tags' => '',
					'name' => '',
					'id' => ''
				),
				$atts, 'videowhisper_downloads');


			$id = $atts['id'];
			if (!$id) $id = uniqid();

			self::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpm_downloads&pp=' . $atts['perpage'] . '&pr=' . $atts['perrow'] . '&collection=' . urlencode($atts['collection']) . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&sc=' . $atts['select_category'] . '&so=' . $atts['select_order'] . '&sp=' . $atts['select_page'] . '&sn=' . $atts['select_name'] .  '&sg=' . $atts['select_tags'] . '&id=' .$id . '&tags=' . urlencode($atts['tags']) . '&name=' . urlencode($atts['name']);

			$htmlCode = <<<HTMLCODE
<script type="text/javascript">
var aurl$id = '$ajaxurl';
var \$j = jQuery.noConflict();
var loader$id;

	function loadDownloads$id(message){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperDownloads$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: aurl$id,
			success: function(data) {
				\$j("#videowhisperDownloads$id").html(data);
				jQuery(".ui.dropdown").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");
			}
		});
	}


	\$j(function(){
		loadDownloads$id();
		setInterval("loadDownloads$id('')", 60000);
	});

</script>

<div id="videowhisperDownloads$id">
    Loading downloads...
</div>

HTMLCODE;

			if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['downloadsCSS']));

			return $htmlCode;
		}

		function videowhisper_download($atts)
		{
			$atts = shortcode_atts(array('download' => '0'), $atts, 'videowhisper_download');

			$download_id = intval($atts['download']);
			if (!$download_id) return 'shortcode_preview: Missing download id!';

			$download = get_post($download_id);
			if (!$download) return 'shortcode_preview: download #'. $download_id . ' not found!';

			$options = get_option( 'VWpaidMembershipOptions' );

			//Access Control
			$deny = '';

			//global
			if (!self::hasPriviledge($options['watchList'])) $deny = 'Your current membership does not allow accessing downloads.';

			//by collections
			$lists = wp_get_post_terms( $download_id, $options['custom_taxonomy'], array( 'fields' => 'names' ) );

			if (!is_array($lists))
			{
				if (is_wp_error($lists)) echo 'Error: Can not retrieve "' .$options['custom_taxonomy']. '" terms for video post: ' . $lists->get_error_message();

				$lists = array();
			}


			//collection role required?
			if ($options['role_collection'])
				foreach ($lists as $key=>$collection)
				{
					$lists[$key] = $collection = strtolower(trim($collection));

					//is role
					if (get_role($collection)) //video defines access roles
						{
						$deny = 'This download requires special membership. Your current membership: ' .self::getRoles() .'.' ;
						if (self::hasRole($collection)) //has required role
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
				else $deny = 'Only registered users can watch this download. Please login first.';

				if (in_array('unpublished', $lists)) $deny = 'This download has been unpublished.';

				if ($deny)
				{
					$htmlCode .= str_replace('#info#',$deny, html_entity_decode(stripslashes($options['accessDenied'])));
					$htmlCode .= '<br>';
					$htmlCode .= do_shortcode('[videowhisper_download_preview download="' . $download_id . '"]') . self::poweredBy();
					return $htmlCode;
				}

			//update stats
			$views = get_post_meta($download_id, 'download-views', true);
			if (!$views) $views = 0;
			$views++;
			update_post_meta($download_id, 'download-views', $views);
			update_post_meta($download_id, 'download-lastview', time());


			//display download:
			$thumbPath = get_post_meta($download_id, 'download-thumbnail', true);

			//download
			$downloadPath = get_post_meta($download_id, 'download-source-file', true);
			if ($downloadPath)
				if (file_exists($downloadPath))
					$downloadURL = self::path2url($downloadPath);

$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' segment">';


				$htmlCode .='<IMG SRC="' . self::path2url($thumbPath) . '" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px" /><br>'; 


				$htmlCode .= '<div class="ui divider"></div> <A class="ui button primary" HREF="' . $downloadURL . '"> <i class="cloud download icon"></i>' . __('Download', 'paid-membership') . '</A>';

$htmlCode .= '</div>';

			return $htmlCode;
		}


		//! update this
		function videowhisper_download_preview($atts)
		{
			$atts = shortcode_atts(array('download' => '0'), $atts, 'videowhisper_download_preview');

			$download_id = intval($atts['download']);
			if (!$download_id) return 'shortcode_preview: Missing download id!';

			$download = get_post($download_id);
			if (!$download) return 'shortcode_preview: download #'. $download_id . ' not found!';

			$options = get_option( 'VWpaidMembershipOptions' );

			//res
			$vWidth = $options['thumbWidth'];
			$vHeight = $options['thumbHeight'];

			//snap
			$imagePath = get_post_meta($download_id, 'download-snapshot', true);
			if ($imagePath)
				if (file_exists($imagePath))
					$imageURL = self::path2url($imagePath);
				else self::updatePostThumbnail($update_id);

				if (!$imagePath) $imageURL = self::path2url(plugin_dir_path( __FILE__ ) . 'default_picture.png');
				$download_url = get_permalink($download_id);

			$htmlCode = "<a href='$download_url'><IMG SRC='$imageURL' width='$vWidth' height='$vHeight'></a>";

			return $htmlCode;
		}



		function videowhisper_download_import($atts)
		{
			global $current_user;

			get_currentuserinfo();

			if (!is_user_logged_in())
			{
				return __('Login is required to import downloads!', 'paid-membership');

			}

			$options = get_option( 'VWpaidMembershipOptions' );
			
			if (!$options['downloads']) return __('Downloads are disabled from plugin settings!', 'paid-membership');


			if (!self::hasPriviledge($options['shareList'])) return __('You do not have permissions to share downloads!', 'paid-membership');

			$atts = shortcode_atts(array('category' => '', 'collection' => '', 'owner' => '', 'path' => '', 'prefix' => '', 'tag' => '', 'picture'=>'', 'description' => ''), $atts, 'videowhisper_download_import');

			if (!$atts['path']) return 'videowhisper_download_import: Path required!';

			if (!file_exists($atts['path'])) return 'videowhisper_download_import: Path not found!';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<label for="category">' . __('Category', 'paid-membership') . ': </label><div class="">' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown').'</div>';

			if ($atts['collection']) $collections = '<br><label for="collection">' . __('collection', 'paid-membership') . ': </label>' .$atts['collection'] . '<input type="hidden" name="collection" id="collection" value="'.$atts['collection'].'"/>';
			elseif ( current_user_can('edit_posts') ) $collections = '<br><label for="collection">collection(s): </label> <br> <input size="48" maxlength="64" type="text" name="collection" id="collection" value="' . $username .'"/> ' . __('(comma separated)', 'paid-membership');
			else $collections = '<br><label for="collection">' . __('collection', 'paid-membership') . ': </label> ' . $username .' <input type="hidden" name="collection" id="collection" value="' . $username .'"/> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else
				$owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<br><label for="collection">' . __('Tags', 'paid-membership') . ': </label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/>';
				else $tags = '<br><label for="tag">' . __('Tag(s)', 'paid-membership') . ': </label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value=""/> (comma separated)';

				if ($atts['picture'] != '_none' )
					if ($atts['picture']) $pictures = '<input type="hidden" name="picture" id="picture" value="'.$atts['picture'].'"/>';
					else $pictures = '<div class="field><label for="picture">' . __('Picture', 'paid-membership') . ' </label> ' . self::pictureDropdown($current_user->ID, 0).'</div>';
					else $pictures = '<input type="hidden" name="picture" id="picture" value="0"/>';

					if ($atts['description'] != '_none' )
						if ($atts['description']) $descriptions = '<br><label for="description">' . __('Description', 'paid-membership') . ': </label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/>';
						else $descriptions = '<br><label for="description">' . __('Description', 'paid-membership') . ': </label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value=""/>';


						$url  =  get_permalink();

					$htmlCode .= '<h3>' . __('Import downloads', 'paid-membership') . '</h3>' . $atts['path'] . $atts['prefix'];

				$htmlCode .=  '<form action="' . $url . '" method="post">';

			$htmlCode .= $categories;
			$htmlCode .= $collections;
			$htmlCode .= $tags;
			$htmlCode .= $pictures;
			$htmlCode .= $descriptions;
			$htmlCode .= $owners;

			$htmlCode .= '<br>' . self::importFilesSelect( $atts['prefix'], self::extensions_download(), $atts['path']);

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="import" id="import" value="Import">';

			$htmlCode .= '<INPUT class="button button-primary" TYPE="submit" name="delete" id="delete" value="Delete">';

			$htmlCode .= '</form>';

			//$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		static function pictureDropdown($userID, $default)
		{
			$htmlCode .= '<select class="ui dropdown fluid" name="picture" id="picture">';
			$htmlCode .= '<option value="0" ' . ($default?"":"selected") . '>' . __('Default', 'paid-membership') . '</option>';

			$optionsPictures = get_option('VWpictureGalleryOptions');

			if ($optionsPictures)
			{
				$args=array(
					'post_type' =>  $optionsPictures['custom_post'],
					'post_status' => 'publish',
					'posts_per_page' => 100,
					'post_author'    => $userID,
				);

				$postslist = get_posts( $args );

				if (count($postslist)>0)
				{
					$k = 0;
					foreach ( $postslist as $item )
					{
						$htmlCode .= '<option value="' .$item->ID. '" ' . ($default == $item->ID?'selected':'') . '>' . $item->post_title . '</option>';
					}
				}

			}
			else $htmlCode .= '<option value="0" disabled>' . __('Install Picture Gallery plugin', 'paid-membership') . '</option>';


			$htmlCode .= '</select>';

			return $htmlCode;
		}

		function videowhisper_download_upload($atts)
		{

			if (!is_user_logged_in())
			{
				return __('Login is required to add downloads!', 'paid-membership');
			}
			

			$current_user = wp_get_current_user();
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
			$username = $current_user->$userName;

			$options = get_option( 'VWpaidMembershipOptions' );
			
			if (!$options['downloads']) return __('Downloads are disabled from plugin settings!', 'paid-membership');


			if (!self::hasPriviledge($options['shareList'])) return __('You do not have permissions to share downloads!', 'paid-membership');


			$atts = shortcode_atts(
				array(
					'category' => '',
					'collection' => '',
					'owner' => '',
					'tag' => '',
					'picture' => '',
					'description' => ''
				), $atts, 'videowhisper_download_upload');


			self::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwpm_upload';

			if ($atts['category']) $categories = '<input type="hidden" name="category" id="category" value="'.$atts['category'].'"/>';
			else $categories = '<div class="field><label for="category">' . __('Category', 'paid-membership') . ' </label> ' . wp_dropdown_categories('show_count=0&echo=0&name=category&hide_empty=0&class=ui+dropdown+fluid').'</div>';

			if ($atts['collection']) $collections = '<label for="collection">' . __('collection', 'paid-membership') . '</label>' .$atts['collection'] . '<input type="hidden" name="collection" id="collection" value="'.$atts['collection'].'"/>';
			elseif ( current_user_can('edit_users') ) $collections = '<br><label for="collection">' . __('Collection(s)', 'paid-membership') . '</label> <br> <input size="48" maxlength="64" type="text" name="collection" id="collection" value="' . $username .'" class="text-input"/> (comma separated)';
			else $collections = '<label for="collection">' . __('collection', 'paid-membership') . '</label> ' . $username .' <input type="hidden" name="collection" id="collection" value="' . $username .'"/> ';

			if ($atts['owner']) $owners = '<input type="hidden" name="owner" id="owner" value="'.$atts['owner'].'"/>';
			else $owners = '<input type="hidden" name="owner" id="owner" value="'.$current_user->ID.'"/>';

			if ($atts['tag'] != '_none' )
				if ($atts['tag']) $tags = '<br><label for="collection">' . __('Tags', 'paid-membership') . '</label>' .$atts['tag'] . '<input type="hidden" name="tag" id="tag" value="'.$atts['tag'].'"/>';
				else $tags = '<br><label for="tag">' . __('Tag(s)', 'paid-membership') . '</label> <br> <input size="48" maxlength="64" type="text" name="tag" id="tag" value="" class="text-input"/> (comma separated)';


				if ($atts['picture'] != '_none' )
					if ($atts['picture']) $pictures = '<input type="hidden" name="picture" id="picture" value="'.$atts['picture'].'"/>';
					else $pictures = '<div class="field><label for="picture">' . __('Picture', 'paid-membership') . ' </label> ' . self::pictureDropdown($current_user->ID, 0).'</div>';
					else $pictures = '<input type="hidden" name="picture" id="picture" value="0"/>';

					if ($atts['description'] != '_none' )
						if ($atts['description']) $descriptions = '<br><label for="description">' . __('Description', 'paid-membership') . '</label>' .$atts['description'] . '<input type="hidden" name="description" id="description" value="'.$atts['description'].'"/>';
						else $descriptions = '<br><label for="description">' . __('Description', 'paid-membership') . '</label> <br> <input size="48" maxlength="256" type="text" name="description" id="description" value="" class="text-input"/>';



						$iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
					$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
				$iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
			$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");

			if ($iPhone || $iPad || $iPod || $Android) $mobile = true; else $mobile = false;

			if ($mobile)
			{
				//https://mobilehtml5.org/ts/?id=23
				$mobiles = 'capture="camera"';
				// $accepts = 'accept="image/*;capture=camera"';
				$multiples = '';
				$filedrags = '';
			}
			else
			{
				$mobiles = '';
				// $accepts = 'accept="image/*"';
				$multiples = 'multiple="multiple"';
				$filedrags = '<div id="filedrag">' . __('or Drag & Drop files to this upload area<br>(select rest of options first)', 'paid-membership') . '</div>';
			}

			wp_enqueue_script( 'vwpm-upload', plugin_dir_url(  __FILE__ ) . 'upload.js');

			$submits = '<div id="submitbutton">
	<button class="ui button" type="submit" name="upload" id="upload">' . __('Upload Files', 'paid-membership') . '</button>';

			$htmlCode .= <<<EOHTML
<form class="ui form" id="upload" action="$ajaxurl" method="POST" enctype="multipart/form-data">

<fieldset>
$categories
$collections
$tags
$pictures
$descriptions
$owners
<input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="128000000" />
EOHTML;

			$htmlCode .= '<legend><h3>' . __('Add Download: File Upload', 'paid-membership') . '</h3></legend><div> <label for="fileselect">' . __('Files to upload', 'paid-membership') . '</label>';

			$htmlCode .= <<<EOHTML
	<br><input class="ui button" type="file" id="fileselect" name="fileselect[]" $mobiles $multiples $accepts />
$filedrags
$submits
</div>
EOHTML;

			$htmlCode .=  '<p>'. __('Supported Extensions', 'paid-membership') . ': ' . $options['download_extensions'] . '</p>';

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

		//	$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;

		}

		function vwpm_upload()
		{
			ob_clean();

			echo 'Upload completed... ';

			$options = get_option( 'VWpaidMembershipOptions' );
			if (!$options['downloads']) return __('Downloads are disabled from plugin settings!', 'paid-membership');

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


			$collection = $_SERVER['HTTP_X_COLLECTION'] ? $_SERVER['HTTP_X_COLLECTION'] :$_POST['collection'];

			//if csv sanitize as array
			if (strpos($collection, ',') !== FALSE)
			{
				$collections = explode(',', $collection);
				foreach ($collections as $key => $value) $collections[$key] = sanitize_file_name(trim($value));
				$collection = $collections;
			}

			if (!$collection)
			{
				echo 'Collection required!';
				exit;
			}

			$category = $_SERVER['HTTP_X_CATEGORY'] ? sanitize_file_name($_SERVER['HTTP_X_CATEGORY']) : sanitize_file_name($_POST['category']);

			$picture = $_SERVER['HTTP_X_PICTURE'] ? sanitize_file_name($_SERVER['HTTP_X_PICTURE']) : sanitize_file_name($_POST['picture']);


			$tag = $_SERVER['HTTP_X_TAG'] ? $_SERVER['HTTP_X_TAG'] :$_POST['tag'];

			//if csv sanitize as array
			if (strpos($tag, ',') !== FALSE)
			{
				$tags = explode(',', $tag);
				foreach ($tags as $key => $value) $tags[$key] = sanitize_file_name(trim($value));
				$tag = $tags;
			}


			$description = sanitize_text_field( $_SERVER['HTTP_X_DESCRIPTION'] ? $_SERVER['HTTP_X_DESCRIPTION'] :$_POST['description'] );


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

				//unpredictable name
				return md5(uniqid($fn, true))  . '.' . $ext;
			}

			$path = '';

			if ($fn) //filename
			{

				$ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
				if (!in_array($ext, self::extensions_download() ))
				{
					echo 'Extension not allowed: ' . $ext;
					exit;
				}
				
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

				echo self::importFile($path, $title, $owner, $collection, $category, $tag, $description, $picture);

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
								
								$ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
								if (!in_array($ext, self::extensions_download() ))
								{
									echo 'Extension not allowed: ' . $ext;
									exit;
								}
				
								move_uploaded_file( $files['tmp_name'][$id], $path = $dir . generateName($fn) );
								$title = ucwords(str_replace('-', ' ', sanitize_file_name(array_shift(explode(".", $fn)))));

								echo sanitize_text_field($title) . ' ';

								echo self::importFile($path, $title, $owner, $collection, $category, $picture) . '<br>';
							}
						}

			}


			die;
		}

		public function importFile($path, $name, $owner, $collections, $category = '', $tags = '', $description = '', $picture = '')
		{
			if (!$owner) return "<br>Missing owner!";
			if (!$collections) return "<br>Missing collections!";

			$options = get_option( 'VWpaidMembershipOptions' );
			if (!self::hasPriviledge($options['shareList'])) return '<br>' . __('You do not have permissions to share downloads!', 'paid-membership');

			if (!file_exists($path)) return "<br>$name: File missing: $path";

			$download = intval($download);

			//handle one or many collections
			if (is_array($collections)) $collection = sanitize_file_name(current($collections));
			else $collection = sanitize_file_name($collections);

			if (!$collection) return "<br>Missing collection!";

			$htmlCode .= 'File import: ';

			//uploads/owner/collection/src/file
			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $owner;
			if (!file_exists($dir)) mkdir($dir);

			$dir .= '/' . $collection;
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

			if (!self::hasPriviledge($options['publishList']))
				$post['post_status'] = 'pending';

			$post_id = wp_insert_post( $post);
			if ($post_id)
			{
				update_post_meta( $post_id, 'download-source-file', $newPath );

				wp_set_object_terms($post_id, $collections, $options['custom_taxonomy']);

				if ($tags) wp_set_object_terms($post_id, $tags, 'post_tag');

				if ($category) wp_set_post_categories($post_id, array($category));

				update_post_meta( $post_id, 'download-picture', $picture );

				self::updatePostThumbnail($post_id, true, false);

				if ($post['post_status'] == 'pending') $htmlCode .= __('Download was submitted and is pending approval.','paid-membership');
				else
					$htmlCode .= '<br>' . __('Download was published', 'paid-membership') . ': <a href='.get_post_permalink($post_id).'> #'.$post_id.' '.$name.'</a>';
			}
			else $htmlCode .= '<br>Picture post creation failed!';

			return $htmlCode . ' .';
		}

		static function imagecreatefromfile( $filename ) {
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
		

		static function generateThumbnail($src, $dest, $post_id = 0)
		{
			//png with alpha
			if (!file_exists($src)) return;

			$options = get_option( 'VWpaidMembershipOptions' );

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$srcImage = self::imagecreatefromfile($src);
			if (!$srcImage) return;

			list($width, $height) = @getimagesize($src);
			if (!$width) return;

			$destImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
			 imagealphablending($destImage, false);
			 imagesavealpha($destImage,true);
			 $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
			 imagefilledrectangle($destImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);

			imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagepng($destImage, $dest);

			if ($post_id)
			{
				update_post_meta( $post_id, 'download-thumbnail', $dest );
				if ($width) update_post_meta( $post_id, 'download-width', $width );
				if ($height) update_post_meta( $post_id, 'download-height', $height );
			}

			//return source dimensions
			return array($width, $height);
		}


		static function updatePostThumbnail($post_id, $overwrite = false, $verbose = false)
		{
			$options = get_option( 'VWpaidMembershipOptions' );

			//update post image
			$picture =	get_post_meta( $post_id, 'download-picture', true );


			if ($picture) 
				$imagePath = get_post_meta(intval($picture), 'picture-source-file', true);
			
			if (!$imagePath) $imagePath = plugin_dir_path( __FILE__ ) . 'default_picture.png';
			
			$thumbPath = get_post_meta($post_id, 'download-thumbnail', true);

			if ($verbose)  echo "<br>Updating thumbnail ($post_id, $imagePath,  $thumbPath) uploadsPath=" . $options['uploadsPath'];

			if (!$imagePath) return;
			if (!file_exists($imagePath)) return;
			if (filesize($imagePath) < 5) return; //too small

			if ($overwrite || !$thumbPath || !file_exists($thumbPath))
			{
				//$path =  dirname($imagePath);
				//$thumbPath =  $path . '/' . $post_id . '_thumbDownload.jpg';			
				$thumbPath = $options['uploadsPath'] . '/' . $post_id . '_thumbDownload.jpg';
				
				list($width, $height) = self::generateThumbnail($imagePath, $thumbPath, $post_id);
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





		function videowhisper_postdownloads($atts)
		{



			$options = get_option( 'VWpaidMembershipOptions' );

			$atts = shortcode_atts(
				array(
					'post' => '',
					'perpage' => '8',
					'path' => '',
				), $atts, 'videowhisper_postdownloads');


			if (!$atts['post']) return 'No post id was specified, to manage post associated downloads.';

			if ($_GET['collection_upload']) $htmlCode .=  '<A class="ui button" href="'.remove_query_arg('collection_upload').'">Done Uploading Downloads</A>';
			else
			{

				$htmlCode .= '<div class="w-actionbox color_alternate"><h3>Manage Downloads</h3>';

				$channel = get_post( $atts['post'] );

				if ($atts['path']) $htmlCode .= '<p>Available '.$channel->post_title.' downloads: ' . self::importFilesCount( $channel->post_title, self::extensions_download(), $atts['path']) .'</p>';

				$link  = add_query_arg( array( 'collection_import' => $channel->post_title), get_permalink() );
				$link2  = add_query_arg( array( 'collection_upload' => $channel->post_title), get_permalink() );

				if ($atts['path']) $htmlCode .= ' <a class="ui button" href="' .$link.'">Import</a> ';
				$htmlCode .= ' <a class="ui button" href="' .$link2.'">Upload</a> ';

				$htmlCode .= '</div>';

			}

			$htmlCode .= '<h4>Downloads</h4>';

			$htmlCode .= do_shortcode('[videowhisper_downloads perpage="' . $atts['perpage'] . '" collection="'.$channel->post_name.'"]');


			return $htmlCode;
		}

		function videowhisper_postdownloads_process($atts)
		{

			$atts = shortcode_atts(
				array(
					'post' => '',
					'post_type' => '',
					'path' =>'',
				), $atts, 'videowhisper_postdownloads_process');

			self::importFilesClean();

			$htmlCode = '';

			if ($channel_upload = sanitize_file_name($_GET['collection_upload']))
			{
				$htmlCode .= do_shortcode('[videowhisper_download_upload collection="'.$channel_upload.'"]');
			}

			if ($channel_name = sanitize_file_name($_GET['collection_import']))
			{

				$options = get_option( 'VWpaidMembershipOptions' );

				$url  = add_query_arg( array( 'collection_import' => $channel_name), get_permalink() );


				$htmlCode .=  '<form id="videowhisperImport" name="videowhisperImport" action="' . $url . '" method="post">';

				$htmlCode .= "<h3>Import <b>" . $channel_name . "</b> Downloads to Collection</h3>";

				$htmlCode .= self::importFilesSelect( $channel_name, self::extensions_download(), $atts['path']);

				$htmlCode .=  '<input type="hidden" name="collection" id="collection" value="' . $channel_name . '">';

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



		//download shortcodes: end


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

				if (self::inList($userkeys, $csv)) return 1;
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

			$current_user = wp_get_current_user();

			return implode(", ", $current_user->roles);
		}

		static function poweredBy()
		{


			$options = get_option('VWpaidMembershipOptions');

			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';

			return '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Published with VideoWhisper <a href="https://videowhisper.com/">Paid Membership, Content, Downloads</a>.</p></div>';
		}


		//! Custom Post Page

		function single_template($single_template)
		{

			if (!is_single())  return $single_template;

			$options = get_option('VWpaidMembershipOptions');

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



		function download_page($content)
		{
			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			$options = get_option( 'VWpaidMembershipOptions' );

			if (get_post_type( $postID ) != $options['custom_post']) return $content;


			if ($options['pictureWidth']) $wCode = ' width="' . trim($options['pictureWidth']) . '"';
			else $wCode ='';

			$addCode .= '' . '[videowhisper_download download="' . $postID . '" embed="1"'.$wCode.']';

			//collection
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
								$addCode .= ' <a title="' . __('Channel', 'paid-membership') . ': '. $term->name .'" class="ui button" href="'. get_post_permalink( $channelID ) . '">' . $term->name . ' Channel</a> ' ;

						}
						
						if (class_exists("VWliveStreaming"))  if ($options['vwls_channel'])
						{

							$channelID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $term->slug . "' and post_type='channel' LIMIT 0,1" );

							if ($channelID)	
								{
								$addCode .= ' <a title="' . __('Channel', 'video-share-vod') . ': '. $term->name .'" class="ui videowhisper_playlist_channel button g-btn type_red size_small mk-button dark-color  mk-shortcode two-dimension small" href="'. get_post_permalink( $channelID ) . '">' . $term->name . ' Channel</a> ' ;
								
								if (!VWliveStreaming::userPaidAccess($current_user->ID, $channelID)) 
								return '<h4>Paid Channel Item</h4><p>This video is only accessible after paying for channel: <a class="button" href="' . get_permalink( $channelID ) . '">' . $term->slug . '</a></p>';
								}
						}
									
						

					$addCode .= ' <a title="' . __('Collection', 'paid-membership') . ': '. $term->name .'" class="ui button" href="'. get_term_link( $term->slug, $options['custom_taxonomy']) . '">' . $term->name . '</a> ' ;


				}
				$addCode .=  '</div>';

			}


			$views = get_post_meta($postID, 'download-views', true);
			if (!$views) $views = 0;

			$addCode .= '<div class="videowhisper_views">' . __('Download Views', 'paid-membership') . ': ' . $views . '</div>';

			//! show reviews
			if ($options['rateStarReview'])
			{
				//tab : reviews
				if (shortcode_exists("videowhisper_review"))
					$addCode .= '<h3>' . __('My Review', 'paid-membership') . '</h3>' . do_shortcode('[videowhisper_review content_type="picture" post_id="' . $postID . '" content_id="' . $postID . '"]' );
				else $addCode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';

				if (shortcode_exists("videowhisper_reviews"))
					$addCode .= '<h3>' . __('Reviews', 'paid-membership') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );

			}


			return $addCode . $content ;
		}

		//end: download page

		// Register Custom Post Type
		function download_post() {

			$options = get_option('VWpaidMembershipOptions');

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			if ($options['downloads'])
				if (!post_type_exists($options['custom_post']))
				{

					$labels = array(
						'name'                => _x( 'Downloads', 'Post Type General Name', 'paid-membership' ),
						'singular_name'       => _x( 'Download', 'Post Type Singular Name', 'paid-membership' ),
						'menu_name'           => __( 'Downloads', 'paid-membership' ),
						'parent_item_colon'   => __( 'Parent Download:', 'paid-membership' ),
						'all_items'           => __( 'All Downloads', 'paid-membership' ),
						'view_item'           => __( 'View Download', 'paid-membership' ),
						'add_new_item'        => __( 'Add New Download', 'paid-membership' ),
						'add_new'             => __( 'New Download', 'paid-membership' ),
						'edit_item'           => __( 'Edit Download', 'paid-membership' ),
						'update_item'         => __( 'Update Download', 'paid-membership' ),
						'search_items'        => __( 'Search Downloads', 'paid-membership' ),
						'not_found'           => __( 'No Downloads found', 'paid-membership' ),
						'not_found_in_trash'  => __( 'No Downloads found in Trash', 'paid-membership' ),
					);

					$args = array(
						'label'               => __( 'download', 'paid-membership' ),
						'description'         => __( 'Downloads', 'paid-membership' ),
						'labels'              => $labels,
						'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
						'taxonomies'          => array( 'category', 'post_tag' ),
						'hierarchical'        => false,
						'public'              => true,
						'show_ui'             => true,
						'show_in_menu'        => true,
						'show_in_nav_menus'   => true,
						'show_in_admin_bar'   => true,
						'menu_position'       => 6,
						'can_export'          => true,
						'has_archive'         => true,
						'exclude_from_search' => false,
						'publicly_queryable'  => true,
						'menu_icon' => 'dashicons-paperclip',
						'capability_type'     => 'post',
					);
					register_post_type( $options['custom_post'], $args );

					// Add new taxonomy, make it hierarchical (like categories)
					$labels = array(
						'name'              => _x( 'Collections', 'taxonomy general name' ),
						'singular_name'     => _x( 'Collection', 'taxonomy singular name' ),
						'search_items'      => __( 'Search Collections', 'paid-membership' ),
						'all_items'         => __( 'All Collections', 'paid-membership' ),
						'parent_item'       => __( 'Parent Collection' , 'paid-membership'),
						'parent_item_colon' => __( 'Parent Collection:', 'paid-membership' ),
						'edit_item'         => __( 'Edit Collection' , 'paid-membership'),
						'update_item'       => __( 'Update Collection', 'paid-membership' ),
						'add_new_item'      => __( 'Add New Collection' , 'paid-membership'),
						'new_item_name'     => __( 'New Collection Name' , 'paid-membership'),
						'menu_name'         => __( 'Collections' , 'paid-membership'),
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


		//! Content Access by Membership
		function add_meta_boxes()
		{

			$options = get_option('VWpaidMembershipOptions');

			$postTypes = explode(',', $options['postTypesRoles']);

			foreach ($postTypes as $postType)
				if (post_type_exists( trim($postType) ))
				{
					add_meta_box(
						'videowhisper_paid_membership',           // Unique ID
						'Requires Membership / Role - Paid Membership & Content',  // Box title
						array( 'VWpaidMembership','meta_box_html'),  // Content callback, must be of type callable
						trim($postType)                   // Post type
					);
				}
		}

		function meta_box_html($post)
		{

			$postRoles = get_post_meta( $post->ID, 'vwpm_roles', true );

			//var_dump($postRoles);

			if (!$postRoles) $postRoles = array();

			$checkedCode0 = '';
			if (empty($postRoles)) $checkedCode0 = 'checked';

			$checkedCode1 = '';
			if (in_array('any-member', $postRoles)) $checkedCode1 = 'checked';

?>
    <div>
    <input type="checkbox" id="vwpmRoles0" name="vwpmRoles[]" value="" class="postbox" <?php echo $checkedCode0?>>
    <label for="vwpmRoles0">No (Visitor) - Leave other boxes unchecked to activate this.</label>
  	</div>
    <div>
    <input type="checkbox" id="vwpmRoles1" name="vwpmRoles[]" value="any-member" class="postbox" <?php echo $checkedCode1?>>
    <label for="vwpmRoles1">Any (Member) - Any registered member, indifferent of role.</label>
  	</div>
<?php

			global $wp_roles;
			$all_roles = $wp_roles->roles;

			foreach ($all_roles as $roleName => $role)
			{
				$roleLabel = $role['name'];

				$checkedCode ='';
				if (in_array($roleName, $postRoles)) $checkedCode = 'checked';
?>
    <div>
    <input type="checkbox" id="vwpmRoles<?php echo $roleName?>" name="vwpmRoles[]" value="<?php echo $roleName?>" class="postbox" <?php echo $checkedCode?>>
    <label for="vwpmRoles<?php echo $roleName?>"><?php echo $roleName?> (<?php echo $roleLabel?>) </label>
  	</div>
<?php
			}

?>
	Use content Update button to save changes. Create new roles from <A href="admin.php?page=paid-membership&tab=membership">Paid Membership & Content levels</a>.
		<?php
		}

		function save_post($post_id)
		{
			if (array_key_exists('vwpmRoles', $_POST)) {

				$vwpmRoles = $_POST['vwpmRoles'];

				if (!is_array($vwpmRoles)) $vwpmRoles = array();

				foreach ($vwpmRoles as $key => $value) {
					$vwpmRoles[$key] = sanitize_file_name($value);
					if (!$vwpmRoles[$key]) unset($vwpmRoles[$key]);
				}

				update_post_meta( $post_id, 'vwpm_roles', $vwpmRoles);
			}
		}

		function any_in_array($array1, $array2)
		{
			foreach ($array1 as $value) if (in_array($value,$array2)) return true;
				return false;
		}

		function the_content($content)   //! content page
			{

			if (!is_single()&&!is_page()) return $content; //listings


			$postID = get_the_ID() ;

			$postRoles = get_post_meta( $postID, 'vwpm_roles', true );

			//return $content .'...'. $postRoles;

			if (!$postRoles) return $content;
			if (!is_array($postRoles)) return $content;
			if (empty($postRoles)) return $content;

			$options = get_option('VWpaidMembershipOptions');


			if (!is_user_logged_in()) return $options['visitorMessage'];
			else
			{
				if (in_array('any-member', $postRoles)) return $content;

				$current_user = wp_get_current_user();

				if (VWpaidMembership::any_in_array($postRoles, $current_user->roles)) return $content;
				else return $options['roleMessage'];
			}

		}

		//! Feature Pages and Menus
		static function setupPages()
		{
			$options = get_option('VWpaidMembershipOptions');
			if ($options['disableSetupPages']) return;

			//menu pages
			$pages = array(
				'videowhisper_membership_buy' => 'Membership',
				'videowhisper_my_wallet' => 'My Wallet',
				'videowhisper_content_edit' => 'Edit Content',
			);

			if ($options['downloads'])
			{
				$pages['videowhisper_downloads'] = 'Downloads';
				$pages['videowhisper_download_upload'] = 'Add Download';
		
			}

			$noMenu = array('videowhisper_content_edit');

			//create a menu and add pages
			$menu_name = 'VideoWhisper';
			$menu_exists = wp_get_nav_menu_object( $menu_name );

			if (!$menu_exists) $menu_id = wp_create_nav_menu($menu_name);
			else $menu_id = $menu_exists->term_id;


			$menuItems = wp_get_nav_menu_items($menu_id,  array('output' => ARRAY_A));

			//create pages if not created or existant
			foreach ($pages as $key => $value)
			{

				$pid = $options['p_'.$key];
				if ($pid) $page = get_post($pid);
				if (!$page) $pid = 0;

				if (!$pid)
				{
					//page exists (by shortcode title)
					global $wpdb;
					$pidE = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$value."'");

					if ($pidE) $pid = $pidE;
					else
					{
						$page = array();
						$page['post_type']    = 'page';
						$page['post_content'] = '['.$key.']';
						$page['post_parent']  = 0;
						$page['post_status']  = 'publish';
						$page['post_title']   = $value;
						$page['comment_status'] = 'closed';

						$pid = wp_insert_post ($page);
					}

					$options['p_'.$key] = $pid;
					$link = get_permalink( $pid);

					$foundID = 0;
					foreach ($menuItems as $menuitem) if ($menuitem->title == $value) $foundID = $menuitem->ID;


						if (!in_array($key, $noMenu))
							if ($menu_id) wp_update_nav_menu_item($menu_id, $foundID, array(
										'menu-item-title' =>  $value,
										'menu-item-url' => $link,
										'menu-item-status' => 'publish'));
				}


			}

			update_option('VWpaidMembershipOptions', $options);
		}

		static function get_current_user_role() {
			global $wp_roles;
			$current_user = wp_get_current_user();
			$roles = $current_user->roles;
			$role = array_shift($roles);
			return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
		}

		static  function getCurrentURL()
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
		
		static function path2url($file, $Protocol='http://')
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
				return get_home_url() . str_replace(get_home_path(), '/', $file);

			if (strstr($file, $_SERVER['DOCUMENT_ROOT']))
				return  $url . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);

			return $url . $file;
		}

		static function enqueueUI()
		{
			wp_enqueue_script("jquery");

			wp_enqueue_style( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.css');
			wp_enqueue_script( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.js', array('jquery'));
		}

		//! Shortcodes

		function videowhisper_content_edit($atts)
		{
			$options = get_option('VWpaidMembershipOptions');

			//checks
			if (!is_user_logged_in()) return 'Only registered users can edit their content!';

			$postID = (int) $_GET['editID'];
			$saveID = (int) $_POST['saveID'];

			if (!$postID) $postID = $saveID;
			if (!$postID) return 'This is a system page for editing content, called from other sections. Content ID is required!';

			$post = get_post($postID);
			if (!$post) return 'Content not found!';

			$current_user = wp_get_current_user();

			if ($post->post_author != $current_user->ID && !in_array('administrator', $current_user->roles))
				return 'Only owner and administrator can edit content!';

			//everything fine: edit

			$myCred = 1;

			//mycred
			if ($myCred)
			{
				if ($saveID)
				{
					$mCa = array(
						'status'       => 'enabled',
						'price'        => round($_POST['price'],2),
						'button_label' => 'Buy Now', // default button label
						'expire'       => (int) $_POST['duration'],
						'recurring'    => 0
					);

					update_post_meta( $postID, 'myCRED_sell_content', $mCa );
				}

				$mCa = get_post_meta( $postID, 'myCRED_sell_content', true );
				if ($mCa)
				{
					$oldPrice = $mCa['price'];
					$oldDuration = $mCa['expire'];
				}
			}

			$htmlCode = '<H4>Editing: '.$post->post_title.'</H4>';
			if ($saveID) $htmlCode .= 'Content was updated:';

			$this_page    =  VWpaidMembership::getCurrentURL();

			$htmlCode .=  '<form method="post" enctype="multipart/form-data" action="' . $this_page .'"  name="adminForm">';

			$htmlCode .=  '<h5>Sell Price</h5>
			<input name="price" type="text" id="price" value="'.$oldPrice.'" size="6" maxlength="6" />
			<br>Users need to pay this price to access. Set 0 for free access.';

			$htmlCode .=  '<h5>Access Duration</h5>
			<input name="duration" type="text" id="duration" value="'.$oldDuration.'" size="6" maxlength="6" /> hours.
			<br>Set 720 for 30 days, 0 for unlimited time access (one time flat fee).';

			$htmlCode .=  '<input name="saveID" type="hidden" id="saveID" value="'.$postID.'" />';

			$htmlCode .=  '<p><input class="ui button" type="submit" name="save" id="save" value="Save" /></p>
			</form>';

			$htmlCode .= '<a class="ui button" href="'.get_permalink($postID).'">View Content</A>';

			$post_thumbnail_id = get_post_thumbnail_id($postID);
			if ($postID) $post_featured_image = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview') ;

			if ($post_featured_image)
			{
				//correct url

				$upload_dir = wp_upload_dir();
				$uploads_url = VWpaidMembership::path2url($upload_dir['basedir']);

				$iurl = $post_featured_image[0];
				$relPath = substr($iurl,strlen($uploads_url));

				if (file_exists($relPath)) $rurl = VWpaidMembership::path2url($relPath);
				else $rurl = $iurl;

				$htmlCode .= '<IMG style="padding-bottom: 20px; padding-right:20px" SRC ="'.$rurl.'" WIDTH="'.$post_featured_image[1].'" HEIGHT="'.$post_featured_image[2].'" ALIGN="LEFT">';
			}

			return $htmlCode;


		}

		function videowhisper_my_wallet($atts)
		{
			$options = get_option('VWpaidMembershipOptions');

			VWpaidMembership::enqueueUI();

			if (!is_user_logged_in()) return __('Login to manage wallet!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

			$user_ID = get_current_user_id();

			$htmlCode .= '<div class="ui green segment form">';
			$htmlCode .= '<h4>' . __('Active balance', 'ppv-live-webcams') . ': ' .  $balance . VWpaidMembership::balance($user_ID) . '</h4>';
			$htmlCode .=  __('All Balances', 'ppv-live-webcams') . ': ' .  $balance . VWpaidMembership::balances($user_ID) ;
			$htmlCode .= '</div>';

			if (shortcode_exists('mycred_buy_form'))
				{ $htmlCode.= '<div class="ui segment form"><h4>MyCred</h4>' . do_shortcode( "[mycred_buy_form]") . '</div>';

				$htmlCode.= '<script>
var $jQ = jQuery.noConflict();
$jQ(document).ready(function(){
$jQ(":submit.btn").addClass("ui button");
$jQ("[name=mycred_buy]").addClass("ui dropdown");
});
</script>';

			}
			if (shortcode_exists('woo-wallet')) $htmlCode.= '<div class="ui segment form"><h4>WooWallet</h4>' .do_shortcode( "[woo-wallet]") . '</div>';;


			return $htmlCode;
		}

		function videowhisper_membership_buy($atts)
		{

			$options = get_option('VWpaidMembershipOptions');

			VWpaidMembership::enqueueUI();

			if (!is_user_logged_in()) return stripslashes($options['loginMessage']) . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

			$user_ID = get_current_user_id();

			$memberships = $options['memberships'];

			$htmlCode .= '<div class="ui segment form">';

			//setup membership
			if (isset($_POST['membership_id']))
			{
				$membership_id = (int) $_POST['membership_id'];
				if ($memberships[$membership_id])
				{
					$membership = $memberships[$membership_id];

					if( current_user_can('administrator') ) $htmlCode .= '<h4>Error: Administrators can not purchase different role as that can disable backend access!</h4>';
					elseif (!VWpaidMembership::membership_setup($membership, $user_ID)) $htmlCode .= '<h4>Error: Your balance does not cover this membership!</h4>';
					else $htmlCode .= '<h4>Membership was activated: ' . $membership['label'] . '</h4>';
				}
			}

			//cancel current membership
			if ($_GET['cancel_membership']=='1')
			{
				VWpaidMembership::membership_cancel($user_ID);
				$htmlCode .= '<h4>Membership was cancelled: automated renewal will no longer occur!</h4>';
			}

			global $wp;
			$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );


			//current user membership
			$memInfo = VWpaidMembership::membership_info($user_ID);
			if ($memInfo) $htmlCode .= 'Current Membership: ' . $memInfo;

			$membership = get_user_meta($user_ID, 'vw_paid_membership', true);
			if ($membership) if ($membership['recurring'])
				{
					$htmlCode .= '<BR><a href="' . add_query_arg('cancel_membership','1',$current_url) . '" class="ui button">Cancel Automated Renewal</a><BR>';
				}

			$htmlCode .= '<BR>Current Role: '. VWpaidMembership::get_current_user_role();
			$htmlCode .= '<BR>Current Balance: '.  VWpaidMembership::balance($user_ID);



			if (count($memberships))
			{
				$htmlCode .= '<BR><h4>Select Your Membership</h4>';
				if (!is_array($memberships)) $htmlCode .= 'No memberships defined, yet!';
				else
					foreach ($memberships as $i => $membership)
					{
						$htmlCode .= '<form class="paid_membership_listing ui segment" action="' .$current_url. '" method="post">';
						$htmlCode .= '<h4>' . $membership['label'] . '</h4>';
						$htmlCode .= 'Role: ' . $membership['role'];
						$htmlCode .= '<br>Duration: ' . $membership['expire'] . ' days';
						$htmlCode .= '<br>' . ($membership['recurring']?'Automated Renewal':'One Time');
						$htmlCode .= '<br>Price: ' . $membership['price'];
						$htmlCode .= '<input id="membership_id" name="membership_id" type="hidden" value="' . $i. '">';
						$htmlCode .= '<br><input class="ui button qbutton" id="submit" name="submit" type="submit" value="Buy Now">';
						$htmlCode .= '</form>';
					}


				$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));


			}
			else echo 'No memberships were setup from backend.';

			$htmlCode .= '</div>';

			return $htmlCode;
		}

		static function membership_info($user_ID)
		{
			$membership = get_user_meta($user_ID, 'vw_paid_membership', true);
			if (!$membership) return ;


			$htmlCode .= '<B>'.$membership['label'].'</B> : ';
			$htmlCode .= ' ' . $membership['role'];
			$htmlCode .= ', ' . $membership['expire'] . ' days';
			$htmlCode .= ' until ' . date('M j G:i:s T Y', $membership['expires']) . '';
			
			$htmlCode .= ', ' . ($membership['recurring']?'recurring':'no renew');
			if ($membership['lastCharge']) $htmlCode .= ', last paid ' . date('M j G:i:s T Y', $membership['lastCharge']) . '';

			return $htmlCode ;
		}

		//! Membership Processing

		static function membership_update_all()
		{
			$users = get_users(array(
					'meta_key'     => 'vw_paid_membership',
					'fields' => 'ID'
				));

			foreach ( $users as $user ) VWpaidMembership::membership_update($user);
		}

		//update membership: process recurring / end
		static function membership_update( $user_ID)
		{
			$membership = get_user_meta($user_ID, 'vw_paid_membership', true);

			if ($membership['expires']> time()) return 0; //still valid

			//end if not recurring
			if (!$membership['recurring'])
			{
				VWpaidMembership::membership_end($user_ID);
				return 0;
			}

			//recurr
			if (VWpaidMembership::membership_apply($membership, $user_ID))
			{
				$membership['lastCharge'] = time();
				$membership['expires'] = time() + ($membership['expire'] * 86400);

				update_user_meta( $user_ID, 'vw_paid_membership', $membership);

				return 1;
			}
			else
			{
				VWpaidMembership::membership_end($user_ID);
				return 0;
			}
		}

		static function membership_end($user_ID)
		{
			$options = get_option('VWpaidMembershipOptions');
			if (!$options['freeRole']) return;

			//create role if missing
			if (!get_role($options['freeRole'])) add_role($options['freeRole'], ucwords($options['freeRole']), array('read' => true) );

			$user_ID = wp_update_user( array( 'ID' => $user_ID, 'role' => $options['freeRole'] ) );

			delete_user_meta( $user_ID, 'vw_paid_membership');
		}

		static function smembership_cancel($user_ID)
		{
			$membership = get_user_meta($user_ID, 'vw_paid_membership', true);

			if (!$membership) return;
			if (!$membership['recurring']) return;

			$membership['recurring'] = 0;
			update_user_meta( $user_ID, 'vw_paid_membership', $membership);
		}

		static function membership_setup($membership, $user_ID)
		{
			if (VWpaidMembership::membership_apply($membership, $user_ID))
			{
				$membership['firstCharge'] = time();
				$membership['lastCharge'] = time();
				$membership['expires'] = time() + ($membership['expire'] * 86400);

				update_user_meta( $user_ID, 'vw_paid_membership', $membership);

				return 1;
			} else return 0;
		}


		static function membership_apply($membership, $user_ID)
		{
			$balance = VWpaidMembership::balance($user_ID);
			if ($membership['price']>$balance) return 0;

			//create role if missing
			if (!get_role($membership['role'])) add_role($membership['role'], ucwords($membership['role']), array('read' => true) );

			$user_ID = wp_update_user( array( 'ID' => $user_ID, 'role' => $membership['role'] ) );

			VWpaidMembership::transaction( "paid_membership", $user_ID, - $membership['price'], $membership['label'] . "- Paid Membership Fee.", null, $membership);
			return 1;
		}


		//! Billing Integration: MyCred, WooWallet

		static function balances($userID, $options = null)
		{
			//get html code listing balances
			if (!$options) $options = get_option('VWpaidMembershipOptions');
			if (!$options['walletMulti']) return ''; //disabled

			$balances = VWpaidMembership::walletBalances($userID,'', $options);

			$walletTransfer = sanitize_text_field( $_GET['walletTransfer'] );

			global $wp;
			foreach ($balances as $key=>$value)
			{
				$htmlCode .= '<br>'. $key . ': ' . $value;

				if ($options['walletMulti'] == 2 && $walletTransfer != $key && $options['wallet'] != $key && $value>0) $htmlCode .= ' <a class="ui button compact tiny" href=' . add_query_arg(array('walletTransfer'=>$key),$wp->request) . ' data-tooltip="Transfer to Active Balance">Transfer</a>';

				if ($walletTransfer == $key || ($value>0 && $options['walletMulti'] == 3 && $options['wallet'] != $key))
				{
					VWpaidMembership::walletTransfer($key, $options['wallet'], get_current_user_id(), $options);
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

			if (!$options) $options = get_option('VWpaidMembershipOptions');

			$balances = VWpaidMembership::walletBalances($userID, '', $options);

			if ($balances[$source] > 0)
			{
				VWpaidMembership::walletTransaction($destination, $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
				VWpaidMembership::walletTransaction($source, - $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
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

			if (!$options) $options = get_option('VWpaidMembershipOptions');

			$balance = 0;

			$balances = VWpaidMembership::walletBalances($userID, '', $options);

			if ($options['wallet'])
				if (array_key_exists($options['wallet'], $balances)) $balance = $balances[$options['wallet']];

				if ($live) //live ppv costs estimation
					{
					$temp = get_user_meta($userID, 'vw_ppv_temp', true);
					$balance = $balance - $temp; //deduct temporary charge
				}

			return $balance;
		}

		static function transaction($ref = "paid_membership", $user_id = 1, $amount = 0, $entry = "Paid Membership Transaction", $ref_id = null, $data = null, $options = null)
		{
			//ref = explanation ex. ppv_client_payment
			//entry = explanation ex. PPV client payment in room.
			//utils: ref_id (int|string|array) , data (int|string|array|object)

			if ($amount == 0) return; //nothing


			if (!$options) $options = get_option('VWpaidMembershipOptions');

			//active wallet
			if ($options['wallet']) $wallet = $options['wallet'];
			if (!$wallet) $wallet = 'MyCred';
			if (!function_exists('mycred_add')) if ($GLOBALS['woo_wallet']) $wallet = 'WooWallet';


				VWpaidMembership::walletTransaction($wallet, $amount, $user_id, $entry, $ref, $ref_id, $data);
		}



		//! Admin Side

		function admin_menu() {

			add_menu_page('Paid Items', 'Paid Items', 'manage_options', 'paid-membership', array('VWpaidMembership', 'adminOptions'), 'dashicons-awards',83);

			add_submenu_page("paid-membership", "Paid Membership", "Settings", 'manage_options', "paid-membership", array('VWpaidMembership', 'adminOptions'));

if ($options['downloads'])
{
			add_submenu_page("paid-membership", "Paid Membership", "Downloads Add", 'manage_options', "paid-membership-upload", array('VWpaidMembership', 'adminUpload'));
			add_submenu_page("paid-membership", "Paid Membership", "Downloads Import", 'manage_options', "paid-membership-import", array('VWpaidMembership', 'adminImport'));
}

			add_submenu_page("paid-membership", "Paid Membership", "Documentation", 'manage_options', "paid-membership-doc", array('VWpaidMembership', 'adminDocs'));

		}

		function settings_link($links) {
			$settings_link = '<a href="admin.php?page=paid-membership">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		function adminDocs()
		{
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
	<h2>Paid Membership, Content, Downloads by VideoWhisper</h2>
</div>

<h3>Membership & Content Shortcodes</h3>
<h4>[videowhisper_membership_buy]</h4>
Shows membership info and upgrade options for user.

<h4>[videowhisper_my_wallet]</h4>
Shows user wallet(s) and options to buy credits/tokens.

<h4>[videowhisper_content_edit]</h4>
Creates a page to edit content settings (like access price). Content id is passed by GET parameter editID.


<h3>Downloads Shortcodes</h3>
		<h4>[videowhisper_downloads collections="" category_id="" order_by="" perpage="" perrow="" select_category="1" select_tags="1" select_name="1" select_order="1" select_page="1" include_css="1" id=""]</h4>
		Displays downloads list. Loads and updates by AJAX. Optional parameters: download collection name, maximum downloads per page, maximum downloads per row.
		<br>order_by: post_date / download-views / download-lastview
		<br>select attributes enable controls to select category, order, page
		<br>include_css: includes the styles (disable if already loaded once on same page)
		<br>id is used to allow multiple instances on same page (leave blank to generate)

		<h4>[videowhisper_download_upload collection="" category="" owner="" picture=""]</h4>
		Displays interface to upload downloads.
		<br>collection: If not defined owner name is used as collection for regular users. Admins with edit_users capability can write any collection name. Multiple collections can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

	   <h4>[videowhisper_download_import path="" collection="" category="" owner=""]</h4>
		Displays interface to import downloads.
		<br>path: Path where to import from.
		<br>collection: If not defined owner name is used as collection for regular users. Admins with edit_users capability can write any collection name. Multiple collections can be provided as comma separated values.
		<br>category: If not define a dropdown is listed.
		<br>owner: User is default owner. Only admins with edit_users capability can use different.

		<h4>[videowhisper_download download="0" player="" width=""]</h4>
		Displays video player. Video post ID is required.
		<br>Player: html5/html5-mobile/strobe/strobe-rtmp/html5-hls/ blank to use settings & detection
		<br>Width: Force a fixed width in pixels (ex: 640) and height will be adjusted to maintain aspect ratio. Leave blank to use video size.

		<h4>[videowhisper_download_preview video="0"]</h4>
		Displays video preview (thumbnail) with link to download post. download post ID is required.

	<h4>[videowhisper_postdownloads post="post id"]</h4>
		Manage post associated downloads. Required: post

	<h4>[videowhisper_postdownloads_process post="" post_type=""]</h4>
		Process post associated downloads (needs to be on same page with [videowhisper_postdownloads] for that to work).

<?php
		}
		//! Options

		static function extensions_download()
		{
			//allowed file extensions
			$options = self::getOptions();

			if ($options['download_extensions'])
			{
				$extensions = explode(',', $options['download_extensions']);

				if (is_array($extensions))
				{
					foreach ($extensions as $key => $value) $extensions[$key] = trim($value);
					return $extensions;
				}
			}

			return array();
		}


		function adminOptionsDefault()
		{

			$upload_dir = wp_upload_dir();
			$root_url = plugins_url();
			$root_ajax = admin_url( 'admin-ajax.php?action=vmls&task=');

			return array(
				'interfaceClass' => '',
				'userName' => 'user_nicename',

				'downloads' => '',
				'download_extensions' => 'pdf,doc,docx,odt,rtf,tex,txt,ppt,pptx,key,odp,xls,xlsx,csv,sql,zip,tar,gz,rar,psd,ttf,otf,fon,fnt',
				'custom_post' => 'download',
				'custom_taxonomy' => 'collection',

				'rateStarReview' => '1',

				'editContent' => 'all',

				'vwls_collection' => '1',

				'importPath' => '/home/[your-account]/public_html/streams/',
				'importClean' => '45',
				'deleteOnImport' => '1',

				'vwls_channel' => '1',

				'postTemplate' => '+plugin',
				'taxonomyTemplate' => '+plugin',

				'pictureWidth' => '',

				'thumbWidth' => '256',
				'thumbHeight' => '256',
				'perPage' =>'12',

				'shareList' => 'Super Admin, Administrator, Editor, Author, Contributor',
				'publishList' => 'Super Admin, Administrator, Editor, Author',

				'role_collection' => '1',

				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber, Guest',
				'accessDenied' => '<h3>Access Denied</h3>
<p>#info#</p>',

				'uploadsPath' => $upload_dir['basedir'] . '/vw_downloads',



				'wallet' =>'MyCred',
				'walletMulti'=>'2',

				'freeRole' => 'subscriber',
				'memberships' => unserialize('a:3:{i:0;a:5:{s:5:"label";s:5:"Basic";s:4:"role";s:5:"Basic";s:5:"price";s:1:"8";s:6:"expire";s:2:"30";s:9:"recurring";s:1:"1";}i:1;a:5:{s:5:"label";s:8:"Standard";s:4:"role";s:8:"Standard";s:5:"price";s:2:"10";s:6:"expire";s:2:"30";s:9:"recurring";s:1:"1";}i:2;a:5:{s:5:"label";s:7:"Premium";s:4:"role";s:7:"Premium";s:5:"price";s:2:"12";s:6:"expire";s:2:"30";s:9:"recurring";s:1:"1";}}'),

				'disableSetupPages' => '0',
				'paid_handler' => 'videowhisper',
				'postTypesRoles' => 'page, post, channel, webcam, conference, presentation, videochat, video, picture, download',
				'loginMessage' => 'Upgrading membership is only available for existing users. Please <a class="ui button" href="' . wp_registration_url(). ' ">register</a> or <a class="ui button" href="' . wp_login_url(). ' ">login</a>!',
				'visitorMessage' =>'This content is only available for registered members.',
				'roleMessage' =>'This content is not available for your current membership.',
				'customCSS' => '
<style type="text/css">
<!--
.paid_membership_listing
{
padding: 5px;
margin: 5px;
border: solid 1px #AAA;
}


-->
</style>',
			'downloadsCSS' =>'
<style type="text/css">
<!--

			.videowhisperDownload
{
position: relative;
display:inline-block;

border:1px solid #aaa;
background-color:#777;
padding: 0px;
margin: 2px;

width: 256;
height: 256;
}

.videowhisperDownload:hover {
	border:1px solid #fff;
}

.videowhisperDownload IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperDownloadTitle
{
position: absolute;
top:0px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperDownloadEdit
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

.videowhisperDownloadDuration
{
position: absolute;
bottom:5px;
left:0px;
margin:8px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperDownloadDate
{
position: absolute;
bottom:5px;
right:0px;
margin: 8px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperDownloadViews
{
position: absolute;
bottom:16px;
right:0px;
margin: 8px;
font-size: 10px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperDownloadRating
{
position: absolute;
bottom: 5px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}
-->
</style>			');


		}

		function getOptions()
		{
			$options = get_option('VWpaidMembershipOptions');
			if (!$options) $options = self::adminOptionsDefault();

			return $options;
		}

		function getAdminOptions() {

			$adminOptions = self::adminOptionsDefault();

			$options = get_option('VWpaidMembershipOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}

			update_option('VWpaidMembershipOptions', $adminOptions);
			return $adminOptions;
		}

		function adminOptions()
		{
			$options = self::getAdminOptions();
			$optionsDefault = self::adminOptionsDefault();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = $_POST[$key];
					update_option('VWpaidMembershipOptions', $options);
			}

			VWpaidMembership::setupPages();

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';

?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>Paid Membership, Content, Downloads by VideoWhisper</h2>
</div>

<h2 class="nav-tab-wrapper">
	<a href="admin.php?page=paid-membership&tab=setup" class="nav-tab <?php echo $active_tab=='setup'?'nav-tab-active':'';?>">Setup</a>
	<a href="admin.php?page=paid-membership&tab=settings" class="nav-tab <?php echo $active_tab=='settings'?'nav-tab-active':'';?>">Membership Settings</a>
	<a href="admin.php?page=paid-membership&tab=membership" class="nav-tab <?php echo $active_tab=='membership'?'nav-tab-active':'';?>">Membership Levels</a>
	<a href="admin.php?page=paid-membership&tab=billing" class="nav-tab <?php echo $active_tab=='billing'?'nav-tab-active':'';?>">Billing Wallets</a>
	<a href="admin.php?page=paid-membership&tab=content-membership" class="nav-tab <?php echo $active_tab=='content-membership'?'nav-tab-active':'';?>">Content Membership</a>
	<a href="admin.php?page=paid-membership&tab=users" class="nav-tab <?php echo $active_tab=='users'?'nav-tab-active':'';?>">Users Membership</a>
	<a href="admin.php?page=paid-membership&tab=content" class="nav-tab <?php echo $active_tab=='content'?'nav-tab-active':'';?>">Paid Content</a>
		<a href="admin.php?page=paid-membership&tab=downloads" class="nav-tab <?php echo $active_tab=='downloads'?'nav-tab-active':'';?>">Downloads</a>
		<a href="admin.php?page=paid-membership&tab=share" class="nav-tab <?php echo $active_tab=='share'?'nav-tab-active':'';?>">Downloads Share</a>
		<a href="admin.php?page=paid-membership&tab=access" class="nav-tab <?php echo $active_tab=='access'?'nav-tab-active':'';?>">Downloads Access</a>

</h2>

<form method="post" action="<?php echo "admin.php?page=paid-membership&tab=".$active_tab; ?>">
<?php

			switch ($active_tab)
			{
			case 'setup':

?>
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

<?php

				break;

			case 'downloads':
				$options['custom_post'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_post']));
				$options['custom_taxonomy'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_taxonomy']));

?>
<h3><?php _e('Downloads','paid-membership'); ?></h3>
Enable downloads: uploading content (files) that can be accessed by membership or sold.

<h4>Downloads</h4>
<select name="downloads" id="downloads">
  <option value="0" <?php echo (!$options['downloads']?"selected":"")?>>Disabled</option>
  <option value="1" <?php echo ($options['downloads']?"selected":"")?>>Enabled</option>
</select>

<h4>Extensions Allowed</h4>
<textarea name="download_extensions" id="download_extensions" cols="100" rows="3"><?php echo $options['download_extensions']?></textarea>
<br>Depending on server configuration, allowing frontend users to upload files can result in security risks. Do not allow script extensions like php.
<br>Default: <?php echo $optionsDefault['download_extensions']?>

<h4>Download Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="12" maxlength="32" value="<?php echo $options['custom_post']?>"/>
<br>Custom post name for downloads (only alphanumeric, lower case). Will be used for download urls. Ex: download
<br><a href="options-permalink.php">Save permalinks</a> to activate new url scheme.
<br>Warning: Changing post type name at runtime will hide previously added items. Previous posts will only show when their post type name is restored.

<h4>Download Post Taxonomy Name</h4>
<input name="custom_taxonomy" type="text" id="custom_taxonomy" size="12" maxlength="32" value="<?php echo $options['custom_taxonomy']?>"/>
<br>Special taxonomy for organising downloads. Ex: collection


<h4>Download Post Template Filename</h4>
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


<h4>Collection Template Filename</h4>
<input name="taxonomyTemplate" type="text" id="taxonomyTemplate" size="20" maxlength="64" value="<?php echo $options['taxonomyTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render collection post page. Ex: page.php, single.php
<?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_template_directory() . '/' . $options['taxonomyTemplate'];
					echo '<br>' . $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
				
				
				$current_user = wp_get_current_user();
?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.

<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="ID" <?php echo $options['userName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Used for default user collection. Your username with current settings:
<?php
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				echo $username = $current_user->$userName;
?>

<h4><?php _e('Uploads Path','picture-gallery'); ?></h4>
<p><?php _e('Path where video files will be stored. Make sure you use a location outside plugin folder to avoid losing files on updates and plugin uninstallation.','paid-membership'); ?></p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<br>Ex: /home/-your-account-/public_html/wp-content/uploads/vw_downloads
<br>If you ever decide to change this, previous files must remain in old location.

<h3><?php _e('Plugin Integrations','paid-membership'); ?></h3>
<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable Reviews</h4>
<?php
				if (is_plugin_active('rate-star-review/rate-star-review.php')) echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>'; else echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['rateStarReview']?"selected":""?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.


<h4><?php _e('Show VideoWhisper Powered by','paid-membership'); ?></h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>
<br><?php _e('Show a mention that items were posted with VideoWhisper plugin.
','paid-membership'); ?>
<?php

				break;

			case 'share':
				//! share options
?>
<h3><?php _e('Download Sharing','paid-membership'); ?></h3>

<h4><?php _e('Users allowed to upload and share files','paid-membership'); ?></h4>
<textarea name="shareList" cols="64" rows="2" id="shareList"><?php echo $options['shareList']?></textarea>
<BR><?php _e('Who can share downloads: comma separated Roles, user Emails, user ID numbers.','paid-membership'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','paid-membership'); ?>

<h4><?php _e('Users allowed to directly publish downloads','paid-membership'); ?></h4>
<textarea name="publishList" cols="64" rows="2" id="publishList"><?php echo $options['publishList']?></textarea>
<BR><?php _e('Users not in this list will add downloads as "pending".','paid-membership'); ?>
<BR><?php _e('Who can publish items: comma separated Roles, user Emails, user ID numbers.','paid-membership'); ?>
<BR><?php _e('"Guest" will allow everybody including guests (unregistered users).','paid-membership'); ?>


<h4><?php _e('Default Downloads Per Page','paid-membership'); ?></h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>


<h4><?php _e('Thumbnail Width','paid-membership'); ?></h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4><?php _e('Thumbnail Height','paid-membership'); ?></h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>


<h4>Downloads Listings CSS</h4>
<?php
				$options['downloadsCSS'] = htmlentities(stripslashes($options['downloadsCSS']));

?>
<textarea name="downloadsCSS" id="downloadsCSS" cols="100" rows="8"><?php echo $options['downloadsCSS']?></textarea>
<br>Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['downloadsCSS']?></textarea>

<?php
				break;


			case 'access':
				//! vod options
				$options['accessDenied'] = htmlentities(stripslashes($options['accessDenied']));

?>
<h3>Membership / Content On Demand</h3>

<h4>Members allowed to access download</h4>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo $options['watchList']?></textarea>
<BR>Global download access list: comma separated Roles, user Emails, user ID numbers. Ex: <i>Subscriber, Author, submit.ticket@videowhisper.com, 1</i>
<BR>"Guest" will allow everybody including guests (unregistered users) to access downloads.

<h4>Role collections</h4>
Enables access by role collections: Assign download to a collection that is a role name.
<br><select name="role_collection" id="role_collection">
  <option value="1" <?php echo $options['role_collection']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['role_collection']?"":"selected"?>>No</option>
</select>
<br>Multiple roles can be assigned to same download. User can have any of the assigned roles, to watch. If user has required role, access is granted even if not in global access list.
<br>Downloads without role collections are accessible as per global download access.

<h4>Exceptions</h4>
Assign downloads to these collections:
<br><b>free</b> : Anybody can watch, including guests.
<br><b>registered</b> : All members can watch.
<br><b>unpublished</b> : Download is not accessible.

<h4>Access denied message</h4>
<textarea name="accessDenied" cols="64" rows="3" id="accessDenied"><?php echo $options['accessDenied']?>
</textarea>
<BR>HTML info, shows with preview if user does not have access to access download.
<br>Including #info# will mention rule that was applied.

<h4>Frontend Contend Edit</h4>
<select name="editContent" id="editContent">
  <option value="0" <?php echo $options['editContent']?"":"selected"?>>No</option>
  <option value="all" <?php echo $options['editContent']?"selected":""?>>Yes</option>
</select>
<br>Allow owner and admin to edit content options for videos, from frontend. This will show an edit button on listings that can be edited by current user.


<?php
				break;


				break;

			case 'settings':
				$options['loginMessage'] = htmlspecialchars(stripslashes($options['loginMessage']));

				$options['visitorMessage'] = htmlspecialchars(stripslashes($options['visitorMessage']));
				$options['roleMessage'] = htmlspecialchars(stripslashes($options['roleMessage']));

?>
<h3>Membership Settings</h3>
Configure general membership settings.

<h4>Login Message</h4>
<textarea name="loginMessage" id="loginMessage" cols="100" rows="3"><?php echo $options['loginMessage']?></textarea>
<br>Message for site visitors when trying to access membership upgrade without login.

<h4>Free Role</h4>
<input name="freeRole" type="text" id="freeRole" size="16" maxlength="64" value="<?php echo $options['freeRole']?>"/>
<BR>Role when membership expires. Ex: subscriber

<h4>Membership Listings CSS</h4>
<?php
				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));

?>
<textarea name="customCSS" id="customCSS" cols="100" rows="8"><?php echo $options['customCSS']?></textarea>
<br>Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['customCSS']?></textarea>

<?php
				break;

			case 'content-membership';
?>
<h3>Content Access by Membership</h3>
Configure content access by membership.

<h4>Content Types for Role Access Control</h4>
<input name="postTypesRoles" type="text" id="postTypesRoles" size="100" maxlength="250" value="<?php echo $options['postTypesRoles']?>"/>
<BR>Comma separated content/post types. Ex: page, post, video, picture, download
<BR>A special metabox will show up when editing these content types from backend, to configure access by membership.

<h4>Visitor Message</h4>
<textarea name="visitorMessage" id="visitorMessage" cols="100" rows="3"><?php echo $options['visitorMessage']?></textarea>
<br>Message for site visitors when trying to access content that requires login.

<h4>Role Message</h4>
<textarea name="roleMessage" id="roleMessage" cols="100" rows="3"><?php echo $options['roleMessage']?></textarea>
<br>Message for site visitors when trying to access content that requires specific membership.

<?php
				break;

			case 'membership':


				$memberships = $options['memberships'];

				if ($_POST['importMemberships'])
				{
					echo 'Importing Memberships... Save if everything shows fine.';
					$memberships = unserialize(stripslashes($_POST['importMemberships']));
				}
				if ($_POST['label_new'])
				{
					$i = count($memberships);


					foreach (array('label','role','price', 'expire','recurring') as $varName)
					{
						if (isset($_POST[$varName .  '_new'])) $memberships[$i][$varName] = $_POST[$varName .  '_new'];
					}


				}

				if ($_GET['add'])
				{
					$i = '_new';

?>

					 <h3>Add New Membership </h3>
<?php

?>
					 Label <input name="label<?php echo $i ?>" type="text" id="label<?php echo $i ?>" size="16" maxlength="64" value="Membership"/>
					 <BR>Role <input name="role<?php echo $i ?>" type="text" id="role<?php echo $i ?>" size="16" maxlength="64" value="Author"/>
					 <BR>Price <input name="price<?php echo $i ?>" type="text" id="price<?php echo $i ?>" size="4" maxlength="10" value="5"/>
					 <BR>Expire <input name="expire<?php echo $i ?>" type="text" id="expire<?php echo $i ?>" size="4" maxlength="10" value="30"/> days
					 <BR>Recurring <select name="recurring<?php echo $i ?>" id="recurring<?php echo $i ?>">
					  <option value="1" selected>Yes</option>
					  <option value="0" >No</option>
					</select>

<?php
				}
				else
				{
?>
<br><a class="button" href="admin.php?page=paid-membership&tab=membership&add=1">Add New Membership</a>

					 <h3>Current Memberships</h3>
<?php

					if (!is_array($memberships))
					{
						echo 'No memberships defined!';
						$memberships = array();
					}
					else
						foreach ($memberships as $i => $membership)
							if (isset($_POST['delete' . $i])) unset($memberships[$i]);
							else
							{

								foreach (array('label','role','price', 'expire','recurring') as $varName)
								{
									if (isset($_POST[$varName . $i])) $memberships[$i][$varName] = $_POST[$varName . $i];
								}

?>
					 <h4>Membership # <?php echo ($i + 1)?> </h4>
					 Label <input name="label<?php echo $i ?>" type="text" id="label<?php echo $i ?>" size="16" maxlength="64" value="<?php echo $memberships[$i]['label']?>"/>
					 <BR>Role <input name="role<?php echo $i ?>" type="text" id="role<?php echo $i ?>" size="16" maxlength="64" value="<?php echo $memberships[$i]['role']?>"/>
					 <BR>Price <input name="price<?php echo $i ?>" type="text" id="price<?php echo $i ?>" size="4" maxlength="10" value="<?php echo $memberships[$i]['price']?>"/>
					 <BR>Expire <input name="expire<?php echo $i ?>" type="text" id="expire<?php echo $i ?>" size="4" maxlength="10" value="<?php echo $memberships[$i]['expire']?>"/> days
					 <BR>Recurring <select name="recurring<?php echo $i ?>" id="recurring<?php echo $i ?>">
  <option value="1" <?php echo $memberships[$i]['recurring']=='1'?"selected":""?>>Yes</option>
  <option value="0" <?php echo $memberships[$i]['recurring']=='0'?"selected":""?>>No</option>
</select>
					<BR>Delete <input name="delete<?php echo $i ?>" type="checkbox" id="delete<?php echo $i ?>" />
					 <?php
							}


						$options['memberships'] = $memberships;
					update_option('VWpaidMembershipOptions', $options);
				}

?>

				<p>
				Recurring: Auto renew membership when it expires (if credits are available).
				<br>Label: Label to show to users.
				</p>

				<H4>Current Memberships Data (Export)</H4>
				<textarea readonly cols="120" rows="3"><?php echo htmlspecialchars(serialize($memberships))?></textarea>

				<H4>Default Memberships Data</H4>
				<textarea readonly cols="120" rows="3"><?php echo htmlspecialchars(serialize($optionsDefault['memberships']))?></textarea>
				<?php

				//echo '<br>m: ';
				//var_dump($optionsDefault['memberships']);


				?>;

				<H4>Import Memberships Data</H4>
				<textarea cols="120" name="importMemberships" id="importMemberships" rows="4"></textarea>
				<br>If everything shows fine after import, Save Changes to apply.

				<?php
				break;

			case 'billing':
?>
<h3>Billing Settings</h3>
Payments (real money) go into accounts configured by site owner, setup with billing gateways (like Paypal, Zombaio, Stripe).
<BR>Documentation:  <a target="_read" href="https://paidvideochat.com/features/pay-per-view-ppv/#billing">billing features and gateways</a>, <a target="_read" href="https://paidvideochat.com/features/quick-setup-tutorial/#ppv">billing setup</a>.


<h4>Active Wallet</h4>
<select name="wallet" id="wallet">
  <option value="MyCred" <?php echo $options['wallet']=='MyCred'?"selected":""?>>MyCred</option>
  <option value="WooWallet" <?php echo $options['wallet']=='WooWallet'?"selected":""?>>WooWallet</option>
</select>

<h4>Multi Wallet</h4>
<select name="walletMulti" id="walletMulti">
  <option value="0" <?php echo $options['walletMulti']=='0'?"selected":""?>>Disabled</option>
  <option value="1" <?php echo $options['walletMulti']=='1'?"selected":""?>>Show</option>
  <option value="2" <?php echo $options['walletMulti']=='2'?"selected":""?>>Manual</option>
  <option value="3" <?php echo $options['walletMulti']=='3'?"selected":""?>>Auto</option>
</select>
<BR>Show will display balances for available wallets, manual will allow transferring to active wallet, auto will automatically transfer all to active wallet.
<?php

				submit_button();
?>

<h3>WooCommerce Wallet (WooWallet)</h3>
<?php
				if (is_plugin_active('woo-wallet/woo-wallet.php'))
				{
					echo 'WooWallet Plugin Detected';

					if ($GLOBALS['woo_wallet'])
					{
						$wooWallet = $GLOBALS['woo_wallet'];

						echo '<br>Testing balance: You have: ' .  $wooWallet->wallet->get_wallet_balance( get_current_user_id() );

?>
	<ul>
		<li><a class="secondary button" href="admin.php?page=woo-wallet">User Credits History & Adjust</a></li>
		<li><a class="secondary button" href="users.php">User List with Balance</a></li>
	</ul>
					<?php

					}else echo 'Error: woo_wallet not found!';


				}
				else echo 'Not detected. Please install and activate <a target="_plugin" href="https://wordpress.org/plugins/woo-wallet/">WooCommerce Wallet</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

?>
WooCommerce Wallet plugin is based on WooCommerce plugin and allows customers to store their money in a digital wallet. The customers can add money to their wallet using various payment methods set by the admin, available in WooCommerce. The customers can also use the wallet money for purchasing products from the WooCommerce store.
<br> + Configure WooCommerce payment gateways from <a target="_gateways" href="admin.php?page=wc-settings&tab=checkout">WooCommerce > Settings, Payments tab</a>.
<br> + Enable payment gateways from <a target="_gateways" href="admin.php?page=woo-wallet-settings">Woo Wallet Settings</a>.
<br> + Setup a page for users to buy credits with shortcode [woo-wallet]. My Wallet section is also available in WooCommerce My Account page (/my-account).


<h3>myCRED Wallet (MyCred)</h3>

<h4>1) myCRED</h4>
<?php
				if (is_plugin_active('mycred/mycred.php')) echo 'MyCred Plugin Detected'; else echo 'Not detected. Please install and activate <a target="_mycred" href="https://wordpress.org/plugins/mycred/">myCRED</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

				if (function_exists( 'mycred_get_users_balance'))
				{
					echo '<br>Testing balance: You have ' . mycred_get_users_balance(get_current_user_id()) .' '. htmlspecialchars($options['currencyLong']) . '.';
?>
	<ul>
		<li><a class="secondary button" href="admin.php?page=mycred">Transactions Log</a></li>
		<li><a class="secondary button" href="users.php">User Credits History & Adjust</a></li>
	</ul>
					<?php
				}
?>
<a target="_mycred" href="https://wordpress.org/plugins/mycred/">myCRED</a> is a stand alone adaptive points management system that lets you award / charge your users for interacting with your WordPress powered website. The Buy Content add-on allows you to sell any publicly available post types, including webcam posts created by this plugin. You can select to either charge users to view the content or pay the post's author either the whole sum or a percentage.

	<br> + After installing and enabling myCRED, activate these <a href="admin.php?page=mycred-addons">addons</a>: buyCRED, Sell Content are required and optionally Notifications, Statistics or other addons, as desired for project.

	<br> + Configure in <a href="admin.php?page=mycred-settings ">Core Setting > Format > Decimals</a> at least 2 decimals to record fractional token usage. With 0 decimals, any transactions under 1 token will not be recorded.




<h4>2) myCRED buyCRED Module</h4>
 <?php
				if (class_exists( 'myCRED_buyCRED_Module' ) )
				{
					echo 'Detected';
?>
	<ul>
		<li><a class="secondary button" href="edit.php?post_type=buycred_payment">Pending Payments</a></li>
		<li><a class="secondary button" href="admin.php?page=mycred-purchases-mycred_default">Purchase Log</a> - If you enable BuyCred separate log for purchases.</li>
		<li><a class="secondary button" href="edit-comments.php">Troubleshooting Logs</a> - MyCred logs troubleshooting information as comments.</li>
	</ul>
					<?php
				} else echo 'Not detected. Please install and activate myCRED with <a href="admin.php?page=mycred-addons">buyCRED addon</a>!';
?>

<p> + myCRED <a href="admin.php?page=mycred-addons">buyCRED addon</a> should be enabled and at least 1 <a href="admin.php?page=mycred-gateways">payment gateway</a> configured, for users to be able to buy credits.
<br> + Setup a page for users to buy credits with shortcode <a target="mycred" href="http://codex.mycred.me/shortcodes/mycred_buy_form/">[mycred_buy_form]</a>.
<br> + Also "Thank You Page" should be set to "Webcams" and "Cancellation Page" to "Buy Credits" from <a href="admin.php?page=mycred-settings">buyCred settings</a>.</p>
<p>Troubleshooting: If you experience issues with IPN tests, check recent access logs (recent Visitors from CPanel) to identify exact requests from billing site, right after doing a test.</p>
<h4>3) myCRED Sell Content Module</h4>
 <?php
				if (class_exists( 'myCRED_Sell_Content_Module' ) ) echo 'Detected'; else echo 'Not detected. Please install and activate myCRED with <a href="admin.php?page=mycred-addons">Sell Content addon</a>!';
?>
<p>
myCRED <a href="admin.php?page=mycred-addons">Sell Content addon</a> should be enabled as it's required to enable certain stat shortcodes. Optionally select "<?php echo ucwords($options['custom_post'])?>" - I Manually Select as Post Types you want to sell in <a href="admin.php?page=mycred-settings">Sell Content settings tab</a> so access to webcams can be sold from backend. You can also configure payout to content author from there (Profit Share) and expiration, if necessary.


<h3>Brave Tips and Rewards in Cryptocurrencies</h3>
<a href="https://brave.com/vid857">Brave</a> is a special build of the popular Chrome browser, focused on privacy and speed, already used by millions. Users get airdrops and rewards from ads they are willing to watch and content creators (publishers) like site owners get tips and automated revenue from visitors. This is done in $BAT and can be converted to other cryptocurrencies like Bitcoin or withdrawn in USD, EUR.
	<p>How to receive contributions and tips for your site:
	<br>+ Get the <a href="https://brave.com/vid857">Brave Browser</a>. You will get a browser wallet, airdrops and get to see how tips and contributions work.
	<br>+ Join <a href="https://creators.brave.com/">Brave Creators Publisher Program</a> and add your site(s) as channels. If you have an established site, you may have automated contributions or tips already available from site users that accessed using Brave. Your site(s) will show with a Verified Publisher badge in Brave browser and users know they can send you tips directly.
	<br>+ You can setup and connect an Uphold wallet to receive your earnings and be able to withdraw to bank account or different wallet. You can select to receive your deposits in various currencies and cryptocurrencies (USD, EUR, BAT, BTC, ETH and many more).
</p>
<?php


				$hideSubmit=1;

				break;

			case 'users':
?>
<h3>Users</h3>
Users with membership managed with this plugin:
<br>
<?php


				if ($delete_membership = (int) $_GET['delete_membership'])
				{
					delete_user_meta( $delete_membership, 'vw_paid_membership');
					echo 'Deleted membership for: ' . $delete_membership;
				}

				VWpaidMembership::membership_update_all();

				$users = get_users(
					array(
						'meta_key'     => 'vw_paid_membership',
						'fields' => 'ID'
					));

				if (count($users))
					foreach ( $users as $user )
					{
						$user_info = get_userdata($user);
						echo '<br>' . $user_info->user_login . ' : ' ;
						echo VWpaidMembership::membership_info($user);
						echo ' - <a href="admin.php?page=paid-membership&tab=users&delete_membership=' . $user_info->ID . '">Delete Membership</a>';
					}
				else echo "<BR>Currently, no users have paid membership setup with this plugin.";

				$hideSubmit=1;

				break;


			case 'content':
?>
<h3>Paid Content</h3>
Content (posts) that require purchase for access.

<h4>Paid Content Handling</h4>
<select name="paid_handler" id="paid_handler">
  <option value="videowhisper" <?php echo $options['paid_handler']=='videowhisper'?"selected":""?>>VideoWhisper</option>
  <option value="mycred" <?php echo $options['paid_handler']=='mycred'?"selected":""?>>MyCred</option>
</select>
<br>VideoWhisper Paid Membership and Content supports custom pricing for some plugins (special discounts).
<?php
				$posts = get_posts(
					array(
						'meta_key'         => 'myCRED_sell_content',
						'post_type' => 'any',
						'post_status' => 'any',
						//      'post_type'     => array ( 'post', 'page', 'presentation', 'channel', 'webcam', 'video'),
						'orderby'          => 'date',
						'order'            => 'DESC',
						'suppress_filters' => true
					));

				foreach ($posts as $post)
				{
					$meta = get_post_meta($post->ID, 'myCRED_sell_content', true);
					echo '<p>';
					echo '<a href="' . get_post_permalink($post->ID).'">' . $post->post_name . '</a> ';
					echo ' Price: ' . $meta['price'] . ', Duration: '. ($meta['expire']?($meta['expire'].' h'):'unlimited') . ', ' . ($meta['recurring']?'recurring.':'one time fee.');
					echo '</p>';
				}

				$hideSubmit=1;

				break;
			}

			if (!$hideSubmit) submit_button();
			echo '</form>';

		}


		static function adminUpload()
		{
?>
		<div class="wrap">
<?php screen_icon(); ?>
		<h2>Upload / Downloads / Paid Membership & Content by VideoWhisper.com</h2>
		<?php
			echo do_shortcode("[videowhisper_download_upload]");
?>
		Use this page to upload one or multiple downloads to server. Configure category, collections and then choose files or drag and drop files to upload area.
		<br>Collection(s): Assign downloads to multiple collections, as comma separated values. Ex: subscriber, premium

		</div>
		<?php
		}

		static function adminImport()
		{
			$options = self::getOptions();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = trim($_POST[$key]);
					update_option('VWpaidMembershipOptions', $options);
			}


			screen_icon(); ?>
<h2>Import / Downloads / Paid Membership & Content by VideoWhisper.com</h2>
	Use this to mass import any number of files already existent on server, as downloads.

<?php
			if (file_exists($options['importPath'])) echo do_shortcode('[videowhisper_downloads_import path="' . $options['importPath'] . '"]');
			else echo 'Import folder not found on server: '. $options['importPath'];
?>
<h3>Import Settings</h3>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h4>Import Path</h4>
<p>Server path to import downloads from</p>
<input name="importPath" type="text" id="importPath" size="100" maxlength="256" value="<?php echo $options['importPath']?>"/>
<br>Ex: /home/[youraccount]/public_html/streams/
<h4>Delete Original on Import</h4>
<select name="deleteOnImport" id="deleteOnImport">
  <option value="1" <?php echo $options['deleteOnImport']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['deleteOnImport']?"":"selected"?>>No</option>
</select>
<br>Remove original file after copy to new location.
<h4>Import Clean</h4>
<p>Delete downloads older than:</p>
<input name="importClean" type="text" id="importClean" size="5" maxlength="8" value="<?php echo $options['importClean']?>"/>days
<br>Set 0 to disable automated cleanup. Cleanup does not occur more often than 10h to prevent high load.
<?php submit_button(); ?>
</form>
	<?php



		}

	}


}


//instantiate
if (class_exists("VWpaidMembership"))
{
	$paidMembership = new VWpaidMembership();
}

//Actions and Filters
if (isset($paidMembership))
{
	register_activation_hook( __FILE__, array(&$paidMembership, 'activation' ) );
	register_deactivation_hook( __FILE__, array(&$paidMembership, 'deactivation' ) );

	add_action('init', array(&$paidMembership, 'init'));
	add_action("plugins_loaded", array(&$paidMembership, 'plugins_loaded'));

	add_action('cron_membership_update', array(&$paidMembership, 'membership_update_all'));

	//admin
	add_action('admin_menu', array(&$paidMembership, 'admin_menu'));


}



?>