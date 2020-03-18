<?php
/*
Plugin Name: Rate Star Review - AJAX Reviews for Content, with Star Ratings
Plugin URI: https://videochat-scripts.com/
Description: <strong>Rate Star Review - AJAX Reviews for Content, with Star Ratings</strong>: Multiple ratings and reviews for content (including custom post types) using AJAX. <a href='https://videowhisper.com/tickets_submit.php?topic=Rate-Star-Review'>Contact Us</a>
Version: 1.3.6
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
Text Domain: rate-star-review
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!class_exists("VWrateStarReview"))
{
	class VWrateStarReview {

		public function __construct()
		{
		}

		public function VWrateStarReview() { //constructor
			self::__construct();

		}

		static function install() {

			// do not generate any output here

			VWrateStarReview::review_post();

			flush_rewrite_rules();
		}

		function init()
		{
			//setup post
			VWrateStarReview::review_post();
		}

		function plugins_loaded()
		{
			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWrateStarReview','settings_link') );

			add_filter('the_content', array('VWrateStarReview','the_content'));


			//shortcodes
			add_shortcode('videowhisper_review', array( 'VWrateStarReview', 'videowhisper_review'));
			add_shortcode('videowhisper_reviews', array( 'VWrateStarReview', 'videowhisper_reviews'));
			add_shortcode('videowhisper_rating', array( 'VWrateStarReview', 'videowhisper_rating'));
			add_shortcode('videowhisper_ratings', array( 'VWrateStarReview', 'videowhisper_ratings'));
			add_shortcode('videowhisper_review_featured', array( 'VWrateStarReview', 'videowhisper_review_featured'));


			//web app ajax calls
			add_action( 'wp_ajax_vwrsr_review', array('VWrateStarReview','vwrsr_review') );
			add_action( 'wp_ajax_nopriv_vwrsr_review', array('VWrateStarReview','vwrsr_review') );
			add_action( 'wp_ajax_vwrsr_reviews', array('VWrateStarReview','vwrsr_reviews') );
			add_action( 'wp_ajax_nopriv_vwrsr_reviews', array('VWrateStarReview','vwrsr_reviews') );
		}


		function the_content($content)
		{

			if (!is_single()) return $content;


			$options = VWrateStarReview::getOptions();
			if (!$options['review_posts']) return $content;

			$review_posts = explode(',',$options['review_posts']);
			if (!is_array($review_posts)) return $content;;

			$postID = get_the_ID() ;
			$post_type = get_post_type( $postID );

			foreach ($review_posts as $review_post)
				if ( $post_type == trim($review_post) )
				{
					$addCode .= '<h3>' . __('My Review', 'rate-star-review') . '</h3>' . do_shortcode('[videowhisper_review content_type="'. $post_type .'" post_id="' . $postID . '" content_id="' . $postID . '"]' );

					$addCode .= '<h3>' . __('Reviews', 'rate-star-review') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );

					return  $content . $addCode;
				}

			return $content;

		}

		static function updatePostRating($post_id)
		{

			if (!$post_id) return;

			$options = VWrateStarReview::getOptions();

			$args = array(
				'post_type'    => $options['custom_post'], //review
				'meta_query' => array(
					'relation'  => 'AND',
					'post_id'   => array('key'     => 'post_id', 'value' => $post_id),
				),

			);

			// $htmlCode .=  $post_id .' update ratings:' . serialize($args);

			$postslist = get_posts($args); //the ratings

			if (count($postslist))
			{
				$ratingsCount = 0;
				$ratingsSum = 0;

				$categoryCount = array();
				$categorySum = array();

				foreach ( $postslist as $item )
				{
					$post = get_post($item);

					$rating =  get_post_meta( $post->ID, 'rating', true);
					$rating_max =  get_post_meta( $post->ID, 'rating_max', true);
					if (!$rating_max) $rating_max = $options['rating_max'];

					$category=0;
					$cats = wp_get_post_categories( $post->ID);
					if (count($cats)) $category = array_pop($cats);

					//totals
					$ratingsSum += $rating / $rating_max ;
					$ratingsCount++;

					//by categories
					$categorySum[$category] += $rating / $rating_max ;
					$categoryCount[$category]++ ;
				}

				if ($ratingsCount)
				{
					$rating_average = number_format($ratingsSum / $ratingsCount, 2); // 2 decimals

					update_post_meta($post_id, 'rateStarReview_rating', $rating_average);
					update_post_meta($post_id, 'rateStarReview_ratingNumber', $ratingsCount);
					update_post_meta($post_id, 'rateStarReview_ratingPoints', $ratingsSum);
				}

				//set empty categories
				$categories = get_categories();
				foreach ($categories as $category)
					if (!array_key_exists($category->term_id, $categoryCount))
					{
						$cat = $category->term_id;
						delete_post_meta($post_id, 'rateStarReview_rating_category' . $cat);
						delete_post_meta($post_id, 'rateStarReview_ratingNumber_category'. $cat);
						delete_post_meta($post_id, 'rateStarReview_ratingPoints_category'. $cat);
					}

				//
				if (!empty($categoryCount))
					foreach ($categoryCount as $cat=>$value)
					{
						$rating_average = number_format($categorySum[$cat] / $categoryCount[$cat], 2); // 2 decimals

						update_post_meta($post_id, 'rateStarReview_rating_category' . $cat, $rating_average);
						update_post_meta($post_id, 'rateStarReview_ratingNumber_category'. $cat, $categoryCount[$cat]);
						update_post_meta($post_id, 'rateStarReview_ratingPoints_category'. $cat, $categorySum[$cat]);
					}



				return  $rating_average;
			}

			return 0;
		}

		static function enqueueScripts()
		{

			wp_enqueue_script("jquery");

			wp_enqueue_style( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.css');
			wp_enqueue_script( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.js', array('jquery'));

		}

static function reviewCard($post, $rating_max = 5, $contentType = true)
{
					$rating_max = intval($rating_max);
					
					$rating =  get_post_meta( $post->ID, 'rating', true);
					$category = '';
					$cats = wp_get_post_categories( $post->ID);
					
					if (count($cats)) $category = array_pop($cats);
					
					$htmlCode .= '<div class="card">';

					$htmlCode .= '<div class="content">
    <div class="right floated header">' . $rating . '/' . $rating_max . '</div>
   <div id="rating" class="ui large star rating readonly" data-rating="' . $rating . '" data-max-rating="' . $rating_max . '"></div>
  </div>';

					$htmlCode .= '<div class="content">';
					$htmlCode .= '<div class="header">  ' . $post->post_title . ' </div>';
				
					if ($contentType)
					{
					$content_type =  get_post_meta( $post->ID, 'content_type', true);						
					$htmlCode .= '<div class="extra">Content Type: ' . $content_type . '</div>';
					}
				
					if ($category != '')
					{
						$cat = get_category($category);
						if ($cat) $htmlCode .= '<div class="extra">Category: ' . $cat->name . '</div>';
					}

					$htmlCode .= '<div class="description" style="max-height:40px; overflow-x: hidden; overflow-y: auto;"><p>' . $post->post_content . '</p></div>';

					$htmlCode .='</div>';

					$user = get_userdata($post->post_author);

					$htmlCode .= '<div class="extra content">
    <div class="right floated author">
      <img class="ui avatar image" src="' . get_avatar_url($post->post_author). '"> ' . $user->user_nicename. '
    </div>
          <span class="date">' . get_the_time("j M Y",$post->ID) . '
      </span>

  </div>';

					$htmlCode .='</div>';
					
					return $htmlCode;
}
		//!shortcodes
		
			function videowhisper_review_featured($atts)
			{
				//displays a featured review

			$options = VWrateStarReview::getOptions();


			if (is_single()) $postID = get_the_ID(); //is on a post page
			else $postID = 0;

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'post_id'=> $postID, //associated with (to display for)
					'rating_max' => $options['rating_max'], //maximum rating
				), $atts, 'videowhisper_review_featured');

				$post_id = intval($atts['post_id']);
				
				$args = array(
					'post_type'    => $options['custom_post'], //review
					'orderby'          => 'meta_value_num',
					'meta_key' 			=> 'rating',					
					'order'             => 'DESC',
					'posts_per_page'    => 1,			
					'meta_query' => array(
						'relation'  => 'AND',
						'post_id'   => array('key'     => 'post_id', 'value' => $post_id),
					),
				);
				
				$postslist = get_posts($args); //the ratings
				if (empty($postslist)) $htmlCode = 'No reviews.';
				else foreach ($postslist as $item) 
				{
					$post = get_post($item);
					$htmlCode = self::reviewCard($post, $atts['rating_max'], false);
				}
				
				return $htmlCode;
			}
			
			
		function videowhisper_rating($atts)
		{
			$options = VWrateStarReview::getOptions();

			if (is_single()) $postID = get_the_ID(); //is on a post page
			else $postID = 0;

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'post_id'=> $postID, //associated with (to display for)
					'rating_max' => $options['rating_max'], //maximum rating
					'category' => '',
				), $atts, 'videowhisper_rating');

			if ($atts['rating_max'] <= 0) return 'Invalid rating_max!';
			if ($atts['post_id'] <= 0) return 'Invalid post_id!';

			VWrateStarReview::enqueueScripts();

			$max = intval($atts['rating_max']);

			if ($atts['category'] == '') $rating = get_post_meta($atts['post_id'], 'rateStarReview_rating', true);
			else $rating = get_post_meta($atts['post_id'], 'rateStarReview_rating_category' . $atts['category'], true);

			if ($rating)
				$htmlCode .= '<label>' . number_format($rating * $max, 2) . '/' . $max . '</label> <div class="ui huge star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>';
			else $htmlCode .= 'No rating, yet!';

			$htmlCode .= '<script>
	jQuery(document).ready(function(){ 	jQuery(\'.ui.rating.readonly\').rating(\'disable\'); });
</script>';

			return $htmlCode;
		}


		function videowhisper_ratings($atts)
		{
			$options = VWrateStarReview::getOptions();

			if (is_single()) $postID = get_the_ID(); //is on a post page
			else $postID = 0;

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'post_id'=> $postID, //associated with (to display for)
					'rating_max' => $options['rating_max'], //maximum rating
					'category' => '',
				), $atts, 'videowhisper_rating');

			if ($atts['rating_max'] <= 0) return 'Invalid rating_max!';
			if ($atts['post_id'] <= 0) return 'Invalid post_id!';

			VWrateStarReview::enqueueScripts();
			$max = intval($atts['rating_max']);

			$htmlCode .= '<div class="ui message">';
			$htmlCode .= '<div class="ui header"> Average Rating: ';
			$rating = get_post_meta($atts['post_id'], 'rateStarReview_rating', true);
			if ($rating)
				$htmlCode .= '<label>' . number_format($rating * $max, 2) . '/' . $max . '</label> <div class="ui huge star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>';
			else $htmlCode .= 'No rating, yet!';
			$htmlCode .= '</div>';

			//by category
			$categories = get_categories();
			foreach ($categories as $category)
			{
				$rating = get_post_meta($atts['post_id'], 'rateStarReview_rating_category' . $category->term_id, true);

				if ($rating) $htmlCode .= '<div><label>'. $category->name . ': '. number_format($rating * $max, 2) . '/' . $max . '</label> <div class="ui small star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div></div>';
			}
			$htmlCode .= '</div>';

			return $htmlCode;
		}

		function videowhisper_review($atts)
		{
			$options = VWrateStarReview::getOptions();

			if (is_single()) $postID = get_the_ID(); //is on a post page
			else $postID = 0;

			if ($postID) $content_type = get_post_type( $postID);
			else $content_type = 'default';

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'content_type'=> $content_type, //content reviewed: post type, session
					'content_id'=> $postID, //id of content reviewed (session, post)
					'post_id'=> $postID, //associated with (to display for)
					'rating_max' => $options['rating_max'], //maximum rating
					'update_id' => '', //id of reviews list to update
					'id' => '' //id of review form
				), $atts, 'videowhisper_review');

			if (!$atts['id']) $id = 'Review';
			else $id = 'Review' . $atts['id'];

			if (!$atts['update_id']) $updateid = 'Reviews';
			else $updateid = 'Reviews' . $atts['id'];

			if (!$atts['content_id']) return 'No content_id!';
			if (!$atts['rating_max']) return 'Invalid rating_max!';

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwrsr_review&content_id=' . urlencode($atts['content_id']) . '&content_type=' . urlencode($atts['content_type']) . '&post_id=' . urlencode($atts['post_id']) . '&rating_max=' . urlencode($atts['rating_max']) . '&id=' . urlencode($id). '&uid=' . urlencode($updateid);

			VWrateStarReview::enqueueScripts();

			$loadingMessage = '<div class="ui active inline text large loader">' . __('Review Form','rate-star-review') . '...</div>';

			$htmlCode = <<<HTMLCODE
<script>
var aurl$id = '$ajaxurl';
var loader$id;

	function loadContent$id(message = '', vars = ''){

	if (message)
	if (message.length > 0)
	{
	  jQuery("#videowhisperContainer$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = jQuery.ajax({
			url: aurl$id,
			data: "interfaceid=$id" + vars,
			success: function(data) {
				jQuery("#videowhisperContainer$id").html(data);
				jQuery('.ui.rating.active').rating();
			}
		});
	}

	jQuery(document).ready(function(){
		loadContent$id();
	});

</script>

<div id="videowhisperContainer$id" class="videowhisperContainer ui segment">
    $loadingMessage
</div>
HTMLCODE;


			return $htmlCode;

		}

		function vwrsr_review()
		{
			$options = VWrateStarReview::getOptions();

			$id =  sanitize_file_name($_GET['id']); //used in JS function naming, may be used in file form caching
			$uid = sanitize_file_name($_GET['uid']); //update $id


			//output clean (clear 0)
			ob_clean();

			if (!is_user_logged_in())
			{
				echo __('Login to review content!','rate-star-review') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'rate-star-review') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'rate-star-review') . '</a>';
				die();
			}

			//$htmlCode .= '$_POST: ' . serialize($_POST);
			//$htmlCode .= '$_GET: ' . serialize($_GET);

			$current_user = wp_get_current_user();

			$content_type = sanitize_text_field($_GET['content_type']);
			$content_id = intval($_GET['content_id']);
			$post_id = intval($_GET['post_id']);

			$rating_max = intval($_GET['rating_max']);
			if (!$rating_max) $rating_max = 5;

			$form = sanitize_key($_GET['form']);

			if ($form == 'insert' || $form == 'update')
			{
				//save
				$title = sanitize_text_field($_GET['title']);
				$content = sanitize_textarea_field($_GET['content']);
				$rating = intval($_GET['rating']);

				$rating_id = (int) $_GET['rating_id'];

				//if not provided use default category
				$category = intval($_GET['category']);
				if ($_GET['category']=='') if ($post_id)
					{
						$cats = wp_get_post_categories( $post_id);
						if (count($cats)) $category = array_pop($cats);
					}


				$post = array(
					'post_title'     => $title,
					'post_author'    => $current_user->ID,
					'post_content'   => $content,
					'post_type'      => $options['custom_post'],
					'post_status'    => 'publish',
				);

				if ($form == 'insert' ) $rating_id = wp_insert_post($post);


				if ($form == 'update' )
				{
					$post['ID'] = $rating_id;
					wp_update_post($post);
				}

				//update rating
				update_post_meta($rating_id, 'rating', $rating);
				update_post_meta($rating_id, 'rating_max', $rating_max);

				update_post_meta($rating_id, 'content_id', $content_id);
				update_post_meta($rating_id, 'content_type', $content_type);
				update_post_meta($rating_id, 'post_id', $post_id);

				if($category) wp_set_post_categories($rating_id, array($category));

				if ($post_id) VWrateStarReview::updatePostRating($post_id);

				//$htmlCode .= ' Post Saved:' . serialize($post);
				$loadingMessage = '<div class="ui active inline text large loader">' . __('Updating Reviews','rate-star-review') . '...</div>';

				$htmlCode .= <<<HTMLCODE
				<script>
if (typeof loadContent$uid === "function") { loadContent$uid('$loadingMessage'); }
				</script>
HTMLCODE;
			}

			//check if already reviewed
			$args = array(
				'author'     => $current_user->ID,
				'post_type'    => $options['custom_post'], //review

				'meta_query' => array(
					'relation'    => 'AND',
					'content_id' => array(
						'key' => 'content_id',
						'value' => $content_id
					),
					'content_type'    => array(
						'key'     => 'content_type',
						'value' => $content_type
					),
					'post_id'    => array(
						'key'     => 'post_id',
						'value' => $post_id
					),
				),

			);

			//$htmlCode .= ' Search Posts:' . serialize($args);

			$postslist = get_posts($args);

			if (count($postslist)>1) $htmlCode .= 'Integration Error: Multiple reviews found for this context!';

			if (count($postslist))
				foreach ( $postslist as $item ) //update my review(s)
					{
					$post = get_post($item);

					$rating =  get_post_meta( $post->ID, 'rating', true);

					$category = 0;
					if ($post_id) //match post category as default
						{
						$cats = wp_get_post_categories( $post_id);
						if (count($cats)) $category = array_pop($cats);
					}

					//rating category
					$cats = wp_get_post_categories( $post->ID);
					if (count($cats)) $category = array_pop($cats);

					$htmlCode .= '<div class="ui form">';
					$htmlCode .= '<div class="field"><label>Rating</label><div id="rating" class="ui massive star rating active" data-rating="' . $rating . '" data-max-rating="' . $options['rating_max'] . '"></div></div>';

					if ($options['category_select'])
						$htmlCode .= '<div class="field"><label>Category</label>' . wp_dropdown_categories('echo=0&name=reviewCategory' . '&hide_empty=0&class=ui+dropdown&selected=' . $category).'</div>';

					$htmlCode .= '<div class="field"><label>Title</label><input type="text" id="reviewTitle" value="' . $post->post_title . '"></div>';
					$htmlCode .= '<div class="field"><label>Details</label><textarea id="reviewContent" rows="2">' . $post->post_content . '</textarea></div>';
					$htmlCode .= '<button class="ui button" type="submit" onclick="loadContent' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Updating Review...</div>\', formVars(\'&form=update&rating_id=' . $post->ID . '\'))">Update Review</button></div>';
				}
			else //add my review
				{

				$category = 0;
				if ($post_id) //match post category as default
					{
					$cats = wp_get_post_categories( $post_id);
					if (count($cats)) $category = array_pop($cats);
				}

				$htmlCode .= '<div class="ui form">';
				$htmlCode .= '<div class="field"><label>Rating</label><div id="rating" class="ui massive star rating active" data-rating="3" data-max-rating="' . $options['rating_max'] . '"></div></div>';

				if ($options['category_select'])
					$htmlCode .= '<div class="field"><label>Category</label>' . wp_dropdown_categories('echo=0&name=reviewCategory' . '&hide_empty=0&class=ui+dropdown&selected=' . $category).'</div>';

				$htmlCode .= '<div class="field"><label>Title</label><input type="text" placeholder="Review Heading" id="reviewTitle" value=""></div>';
				$htmlCode .= '<div class="field"><label>Details</label><textarea id="reviewContent" rows="2"></textarea></div>';
				$htmlCode .= '<button class="ui button" type="submit" onclick="loadContent' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Saving Review...</div>\', formVars(\'&form=insert\'))">Add Review</button></div>';
			}


			$htmlCode .= '<script>
			function formVars(params)
			{
			var vars = params;
			vars = vars + \'&rating=\' + jQuery(\'#rating\').rating(\'get rating\');
			vars = vars + \'&title=\' + encodeURIComponent(jQuery(\'#reviewTitle\').val());
			vars = vars + \'&content=\' + encodeURIComponent(jQuery(\'#reviewContent\').val());
			vars = vars + \'&category=\' + encodeURIComponent(jQuery(\'#reviewCategory\').val());

			return vars;
			}

			</script>';

			echo $htmlCode;
			die();
		}


		function videowhisper_reviews($atts)
		{
			$options = VWrateStarReview::getOptions();

			if (is_single()) $postID = get_the_ID(); //is on a post page
			else $postID = 0;

			//shortocode attributes
			$atts = shortcode_atts(
				array(
					'content_type'=> '', //content reviewed: post type, session
					'content_id'=> '', //id of content reviewed (session, post)
					'post_id'=> $postID, //associated with (to display for)
					'show_average'=> '1', //show average rating if post_id available
					'id' => ''
				), $atts, 'videowhisper_reviews');

			if (!$atts['id']) $id = 'Reviews';
			else $id = 'Reviews' . $atts['id'];

			if (!$atts['content_id'] && !$atts['post_id']) return 'At least one required: content_id or post_id!';

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwrsr_reviews&content_id=' . urlencode($atts['content_id']) . '&content_type=' . urlencode($atts['content_type']) . '&post_id=' . urlencode($atts['post_id']) . '&show_average=' . urlencode($atts['show_average']) . '&id=' . urlencode($id);


			VWrateStarReview::enqueueScripts();

			$loadingMessage = '<div class="ui active inline text large loader">' . __('Loading Reviews','rate-star-review') . '...</div>';

			$htmlCode = <<<HTMLCODE
<script>
var aurl$id = '$ajaxurl';
var loader$id;

	function loadContent$id(message = '', vars = ''){

	if (message)
	if (message.length > 0)
	{
	  jQuery("#videowhisperContainer$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = jQuery.ajax({
			url: aurl$id,
			data: "interfaceid=$id" + vars,
			success: function(data) {
				jQuery("#videowhisperContainer$id").html(data);
				//jQuery('.ui.rating').rating();
				jQuery('.ui.rating.readonly').rating('disable');
			}
		});
	}

	jQuery(document).ready(function(){
		loadContent$id();
	});

</script>

<div id="videowhisperContainer$id" class="videowhisperContainer ui container">
    $loadingMessage
</div>

HTMLCODE;


			return $htmlCode;

		}

		function vwrsr_reviews()
		{
			$options = VWrateStarReview::getOptions();

			$id = sanitize_file_name($_GET['id']); //used in JS function naming, may be used in file form caching

			//output clean (clear 0)
			ob_clean();

			$post_id = intval($_GET['post_id']);
			$show_average = intval($_GET['show_average']);

			$content_type = sanitize_text_field($_GET['content_type']);
			$content_id = intval($_GET['content_id']);

			//check if already reviewed
			$args = array(
				'post_type'    => $options['custom_post'], //review

				'meta_query' => array(
					'relation'    => 'AND'
				),

			);

			if ($post_id) $args['meta_query']['post_id'] = array('key'     => 'post_id', 'value' => $post_id);
			if ($content_id) $args['meta_query']['content_id'] = array('key'     => 'content_id', 'value' => $content_id);
			if ($content_type) $args['meta_query']['content_type'] = array('key'     => 'content_type', 'value' => $content_type);

			//$htmlCode .= ' Search Posts:' . serialize($args);

			if ($post_id>0 && $show_average) $htmlCode .=  do_shortcode('[videowhisper_ratings post_id="' . $post_id . '"]')  . '</div>';

			$postslist = get_posts($args);

			if (count($postslist))
			{
				$htmlCode .= '<div class="ui four stackable cards">';

				foreach ( $postslist as $item )
				{
					
					$post = get_post($item);

					$htmlCode .= self::reviewCard($post, $options['rating_max']);
					
/*
					$rating =  get_post_meta( $post->ID, 'rating', true);
					$content_type =  get_post_meta( $post->ID, 'content_type', true);

					$category = '';
					$cats = wp_get_post_categories( $post->ID);
					if (count($cats)) $category = array_pop($cats);



					$htmlCode .= '<div class="card">';

					$htmlCode .= '<div class="content">
    <div class="right floated header">' . $rating . '/5</div>
   <div id="rating" class="ui large star rating readonly" data-rating="' . $rating . '" data-max-rating="' . $options['rating_max'] . '"></div>
  </div>';

					$htmlCode .= '<div class="content">';
					$htmlCode .= '<div class="header">  ' . $post->post_title . ' </div>';
					$htmlCode .= '<div class="extra">Content Type: ' . $content_type . '</div>';
					if ($category != '')
					{
						$cat = get_category($category);
						if ($cat) $htmlCode .= '<div class="extra">Category: ' . $cat->name . '</div>';
					}

					$htmlCode .= '<div class="description"><p>' . $post->post_content . '</p></div>';

					$htmlCode .='</div>';

					$user = get_userdata($post->post_author);

					$htmlCode .= '<div class="extra content">
    <div class="right floated author">
      <img class="ui avatar image" src="' . get_avatar_url($post->post_author). '"> ' . $user->user_nicename. '
    </div>
          <span class="date">' . get_the_time("j M Y",$post->ID) . '
      </span>

  </div>';

					$htmlCode .='</div>';
					*/

				}
				$htmlCode .='</div>';
			}
			else
			{
				$htmlCode .=  'No reviews, yet.';
			}

			echo $htmlCode;
			die();
		}

		function settings_link($links) {
			$settings_link = '<a href="admin.php?page=rate-star-review">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}


		//! Register Custom Post Type
		static function review_post() {
			$options = VWrateStarReview::getOptions();

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			$labels = array(
				'name'                => _x( 'Reviews', 'Post Type General Name', 'rate-star-review' ),
				'singular_name'       => _x( 'Review', 'Post Type Singular Name', 'rate-star-review' ),
				'menu_name'           => __( 'Reviews', 'rate-star-review' ),
				'parent_item_colon'   => __( 'Parent Review:', 'rate-star-review' ),
				'all_items'           => __( 'All Reviews', 'rate-star-review' ),
				'view_item'           => __( 'View Review', 'rate-star-review' ),
				'add_new_item'        => __( 'Add New Review', 'rate-star-review' ),
				'add_new'             => __( 'New Review', 'rate-star-review' ),
				'edit_item'           => __( 'Edit Review', 'rate-star-review' ),
				'update_item'         => __( 'Update Review', 'rate-star-review' ),
				'search_items'        => __( 'Search Reviews', 'rate-star-review' ),
				'not_found'           => __( 'No Reviews found', 'rate-star-review' ),
				'not_found_in_trash'  => __( 'No Reviews found in Trash', 'rate-star-review' ),
			);

			$args = array(
				'label'               => __( 'Review', 'rate-star-review' ),
				'description'         => __( 'Browse Reviews', 'rate-star-review' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'comments', 'custom-fields', 'page-attributes', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => true,
				'map_meta_cap'        => true,
				'menu_icon' => 'dashicons-star-half',
				'capability_type'     => 'post',
				'capabilities' => array(
					'create_posts' => false
				)
			);
			register_post_type( $options['custom_post'], $args );


		}

		//! Settings

		static function getOptions()
		{
			$options = get_option('VWrateStarReview');
			if (!$options)  return VWrateStarReview::adminOptionsDefault();
			if (empty($options))  return VWrateStarReview::adminOptionsDefault();
			return $options;
		}

		static function adminOptionsDefault()
		{
			$root_url = get_bloginfo( "url" ) . "/";
			return array(
				'review_posts' => 'post, presentation, conference',
				'custom_post' => 'review',
				'category_select' =>1,
				'rating_max' => 5,
				'videowhisper' => 0
			);
		}

		function admin_menu() {

			$options = VWrateStarReview::getOptions();

			add_menu_page('Rate Star Review', 'Rate Star Review', 'manage_options', 'rate-star-review', array('VWrateStarReview', 'optionsPage'), 'dashicons-star-half',83);
			add_submenu_page("rate-star-review", "Settings", "Settings", 'manage_options', "rate-star-review", array('VWrateStarReview', 'optionsPage'));
			add_submenu_page("rate-star-review", "Documentation", "Documentation", 'manage_options', "rate-star-review-doc", array('VWrateStarReview', 'docsPage'));
		}


		function docsPage()
		{
?>


<div class="wrap">
<?php screen_icon(); ?>
<h2>Documentation: Star Rate Review - Review Content with Star Ratings,  by VideoWhisper.com</h2>

Any type of content (posts, pages, custom posts) can be reviewed with this review system and reviews can be listed as necessary.
You can configure this plugin from <a href="admin.php?page=rate-star-review">Settings</a>.

<h3>Links</h3>
<UL>
<LI><a href="https://videowhisper.com/tickets_submit.php">Contact Developers: Support or Custom Development</a></LI>
<LI><a href="https://wordpress.org/plugins/rate-star-review/">WordPress Plugin Page</a></LI>
<LI><a href="https://wordpress.org/support/plugin/rate-star-review/">Plugin Forum: Discuss with other users</a></LI>
<LI><a href="https://wordpress.org/support/plugin/rate-star-review/reviews/#new-post">Review this Plugin</a></LI>
</UL>

Plugin can work with various custom post types. Some plugins manage ratings from own integration settings: <a href="https://videosharevod.com">VideoShareVOD</a> (video), <a href="https://paidvideochat.com">PaidVideochat</a> (webcam), <a href="https://broadcastlivevideo.com">BroadcastLiveVideo</a> (channel), <a href="https://wordpress.org/plugins/picture-gallery/">Picture Gallery</a> (picture).
 
<h3>Shortcodes</h3>

<h4>[videowhisper_review post_id="" content_type="" content_id="" rating_max="5" id="" update_id=""]</h4>
Shows form to add and update review for specific post and content. AJAX based. Can also update reviews list if on same page.
<br>content_type = content type or aspect reviewed, ex: post, page, session, part, chapter, aspects (ex: Readability, Performance, Value, Design, Features) [string]
<br>content_id = id of content to use in combination with content type if necessary (ex: post id, session id, part number, chapter) [integer]
<br>post_id = id of post (if associated to a post, page or other custom post type) [integer]
<br>rating_max = maximum number of stars [integer]
<br>id = form id
<br>update_id = id of list to update

<h4>[videowhisper_reviews post_id="" show_average="1" content_type="" content_id="" id=""]</h4>
Lists reviews for specific content (by post,content). At least post_id or content_id must be specified. AJAX based.
<br>show_average = show rating average if post_id available, set 0 or blank to disable
<br>id = list id

<h4>[videowhisper_rating post_id="" rating_max="5" category="]</h4>
Displays average rating for a post (average of all ratings for that post). If no category is specified (as category id) overall rating will be shown. Static (not AJAX).

<h4>[videowhisper_ratings post_id="" rating_max="5" "]</h4>
Displays average ratings, by category. Static (not AJAX).

<h4>[videowhisper_review_featured post_id="" rating_max="5" "]</h4>
Displays a featured review card for that content (a review with top available rating). Static (not AJAX).

<h4>How to use this?</h4>
In example, if you have a post presenting an electronic product and want site members to be able to review and rate separately different aspects like Features and Performance these can be content types.
<BR>A review form for each content type can be setup: [videowhisper_review content_type="Features" post_id="1"] [videowhisper_review content_type="Performance" post_id="1"].
<BR>Then to show all reviews for that item, you can use [videowhisper_reviews post_id="1"] .
<BR>Another example, if an article is about a book with 2 parts, you can also use content_id to allow users to post a review for each part for each aspect.
</div>
<?php
		}

		static function setupOptions()
		{

			$adminOptions = VWrateStarReview::adminOptionsDefault();

			$options = get_option('VWrateStarReview');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWrateStarReview', $adminOptions);


			return $adminOptions;
		}

		function optionsPage()
		{
			$options = VWrateStarReview::setupOptions();
			$optionsDefault = VWrateStarReview::adminOptionsDefault();

			if (isset($_POST))
			{
				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = $_POST[$key];

					update_option('VWrateStarReview', $options);
			}

?>
<div class="wrap">
<?php screen_icon(); ?>
<h2>Settings : Star Rate Review - Review Content with Star Ratings, by VideoWhisper.com</h2>

For more details about using this plugin see <a href="admin.php?page=rate-star-review-doc">Documentation</a>.

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">

<h4>Review Post Types</h4>
<input name="review_posts" type="text" id="review_posts" size="50" maxlength="200" value="<?php echo strtolower($options['review_posts'])?>"/>
<br>Post types that can be reviewed as comma separated values. Ex: post, page Default: <?php echo $optionsDefault['review_posts']?>
<br>Should automatically show review AJAX box and previous reviews after content, on the post page (not on archive pages with multiple items).
<br>Warning: Some plugins manage this from own integration settings: <a href="https://videosharevod.com">VideoShareVOD</a> (video), <a href="https://paidvideochat.com">PaidVideochat</a> (webcam), <a href="https://broadcastlivevideo.com">BroadcastLiveVideo</a> (channel), <a href="https://wordpress.org/plugins/picture-gallery/">Picture Gallery</a> (picture) and their posts don't need to be configured here.


<h4>Maximum Stars Rating</h4>
<input name="rating_max" type="text" id="rating_max" size="12" maxlength="32" value="<?php echo strtolower($options['rating_max'])?>"/>
<br>Default maximum rating. Ex: 5 Default: <?php echo $optionsDefault['rating_max']?>
<BR>Setup before using. Changing this will not change value of previous ratings: switching from 5 to 10 stars will leave a previous 4/5 stars reviews as 4/5.

<h4>Review Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="12" maxlength="32" value="<?php echo strtolower($options['custom_post'])?>"/>
<br>Custom post name for reviews (only alphanumeric, lower case). Will be used for review urls. Ex: review Default: <?php echo $optionsDefault['custom_post']?>
<br>Recommended: Do not change unless that custom post type is already in use.

<h4>Category Select</h4>
<select name="category_select" id="category_select">
  <option value="1" <?php echo $options['category_select']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['category_select']?"":"selected"?>>No</option>
</select>
<br>User selects a category for rating.

<?php

			submit_button();

			echo '</form></div>';

		}

	}
}

//instantiate
if (class_exists("VWrateStarReview")) {
	$rateStarReview = new VWrateStarReview();
}

//Actions and Filters
if (isset($rateStarReview)) {

	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	register_activation_hook( __FILE__, array(&$rateStarReview, 'install' ) );

	add_action( 'init', array(&$rateStarReview, 'init'));

	add_action("plugins_loaded", array(&$rateStarReview, 'plugins_loaded'));
	add_action('admin_menu', array(&$rateStarReview, 'admin_menu'));

}

?>