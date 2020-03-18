<?php
namespace VideoWhisper\LiveWebcams;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//ini_set('display_errors', 1); //debug only

trait Shortcodes {

	static function enqueueUI()
	{
		wp_enqueue_script("jquery");

		wp_enqueue_style( 'semantic', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/semantic/semantic.min.css');
		wp_enqueue_script( 'semantic', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/semantic/semantic.min.js', array('jquery'));
	}


	static function to62($num, $b=62) {
		$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$r = $num  % $b ;
		$res = $base[$r];
		$q = floor($num/$b);
		while ($q) {
			$r = $q % $b;
			$q =floor($q/$b);
			$res = $base[$r].$res;
		}
		return $res;
	}


	static function to10( $num, $b=62) {
		$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$limit = strlen($num);
		$res=strpos($base,$num[0]);
		for($i=1;$i<$limit;$i++) {
			$res = $b * $res + strpos($base,$num[$i]);
		}
		return $res;
	}


	//! Shortcode Implementation

	function videowhisper_cam_message($atts)
	{
		//send a (paid) question or message to webcam profile

		if (!is_user_logged_in()) return __('Login to send questions or messages!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

		$atts = shortcode_atts(array(
				'post_id'=> 0, //for setup
			), $atts, 'videowhisper_cam_message');

		//room id
		$postID = intval($atts['post_id']);
		if (!$postID) $postID = intval($_GET['webcam_id']); //pass by URL

		if ($postID) $post = get_post($postID);
		if (!$post) $postID = 0;

		if (!$postID) return 'Error: Could not find this webcam room! #' .  $postID;

		$options = get_option('VWliveWebcamsOptions');

		$sender_id = get_current_user_id();
		$balance = self::balance($sender_id);

		$messagesCost = $options['messagesCost'];

		$showNew = 1;

		if ($_POST['newmessage'])
		{
			$showNew = 0;

			if ($balance < $messagesCost) $htmlCode .=  '<div id="warnMessages" class="ui ' . $options['interfaceClass'] .' segment">' . __('You need cost of message in balance to send it.', 'ppv-live-webcams') . '<br>' . $messagesCost . '/'  . $balance . '<br><a class="ui button primary qbutton" href="' . get_permalink( $options['balancePage'] ) . '">' . __('Wallet', 'ppv-live-webcams') . '</a>' .'</div>' ;
			else
			{
				global $wpdb;
				$table_messages = $wpdb->prefix . "vw_vmls_messages";

				$ztime = time();
				$message = esc_sql(wp_encode_emoji(sanitize_textarea_field($_POST['message'])));

				$sql="INSERT INTO `$table_messages` ( `sender_id`, `webcam_id`, `reply_id`, `sdate`, `message`) VALUES ( '$sender_id', '$postID', '0', '$ztime', '$message')";

				$wpdb->query($sql);
				$messageID = $wpdb->insert_id;

				if (!$messageID) return 'Error: Sending message failed! ' . $sql;

				//pay for message
				if ($messagesCost) self::transaction($ref = "paid_message", $sender_id, - $messagesCost, __('Paid Message to', 'ppv-live-webcams') . ' <a href="' . self::roomURL($post->post_name) . '">' . $post->post_title.'</a>', $messageID, $sql, $options);


				$messageURL = add_query_arg( array('view' => 'messages', 'message'=> self::to62($messageID)), get_permalink( $postID));

				$htmlCode .=  '<div id="successMessage" class="ui ' . $options['interfaceClass'] .' segment">' . __('Your message was sent.', 'ppv-live-webcams') .
					'<br><a class="ui button primary qbutton" href="' . $messageURL . '">' . __('View Message', 'ppv-live-webcams') . '</a>' .'</div>' ;

			}


		}

		if ($showNew)
		{
			$this_page    =  self::getCurrentURL();
			$action = add_query_arg( array('view'=>'messages', 'messages'=>'add'), $this_page);
			$htmlCode .= '<form method="post" enctype="multipart/form-data" action="' . $action. '" name="messageForm" class="ui form segment">
	<textarea class="ui field fluid" name="message" id="message" placeholder="' . __('Your Question or Message', 'ppv-live-webcams') . '"></textarea>
	<input class="ui button videowhisperButton" type="submit" name="newmessage" id="newmessage" value="' . __('Send Message to', 'ppv-live-webcams') .' '. $post->post_title. '" />
	<input name="post_id" id="post_id" type="hidden" value="' . $postID .'">
	' .  __('Cost per question or message', 'ppv-live-webcams') . ': ' .  $messagesCost . '
	' .  __('Your current balance', 'ppv-live-webcams') . ': ' .  $balance . '
	</form>';
		}

		return $htmlCode;

	}


	static function messageReplies($messageCode)
	{

		$id = self::to10($messageCode);

		if (!$id) return 'Error: Incorrect message code! #' . $messageCode;


		global $wpdb;
		$table_messages = $wpdb->prefix . "vw_vmls_messages";


		$sqlr = "SELECT * FROM $table_messages WHERE id=$id ORDER BY sdate ASC, id DESC LIMIT 0, 100";
		$question = $wpdb->get_row($sqlr);
		if (!$question) return 'Error: Message not found! #' . $id;

		$post = get_post($question->webcam_id);
		if (!$question) return 'Error: Webcam listing not found! #' . $questio->webcam_id;
	


		$htmlCode .=   '<div class="ui segment ' . $options['interfaceClass'] .'">';
		$htmlCode .=   '<h4>'. ' <i class="users icon big"></i> ' . $post->post_title. ' / ' . ' #'. $messageCode . '</h4>';
		//list messages

		$sql = "SELECT * FROM $table_messages WHERE id=$id OR reply_id=$id ORDER BY sdate ASC, id DESC LIMIT 0, 100";

		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows>0)
		{
			$this_page    =  self::getCurrentURL();

			$htmlCode .= '<div class="ui list">';

			foreach ($results as $message)
			{
				$sender = get_userdata( $message->sender_id );

				$htmlCode .= '<div class="item"> <i class="mail icon big"></i>';
				$htmlCode .= '<div class="content">';
				$htmlCode .= '<div class="header">' . ' <i class="user icon"></i>' . $sender->user_nicename .  ' <i class="clock icon"></i>' . date(DATE_RFC2822, $message->sdate)  . '</div>'  ;
				$htmlCode .= '<div class="ui segment ' . $options['interfaceClass'] .'">' . $message->message . '</div>';
				$htmlCode .= '</div></div>';
			}

			$htmlCode .= '</div>';

		}
		else $htmlCode .=    __('No Messages', 'ppv-live-webcams');

		$htmlCode .= '</div>';

		return $htmlCode;
	}


	function videowhisper_cam_messages_performer($atts)
	{
		//show messages/questions to performer for answering

		if (!is_user_logged_in()) return __('Login to manage messages and replies!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

		$atts = shortcode_atts(array(
				'post_id'=> 0, //for setup
			), $atts, 'videowhisper_cam_messages_performer');

		//room id
		$postID = intval($atts['post_id']);
		if (!$postID) $postID = intval($_GET['webcam_id']); //pass by URL

		if ($postID) $post = get_post($postID);
		if (!$post) $postID = 0;

		if (!$postID) return 'Error: Could not find this webcam room! #' .  $postID;

		if ( !self::isAuthor($postID) )
		{
			echo 'Access not permitted (different webcam owner)! #' . $postID;
			die;
		}

		$options = get_option('VWliveWebcamsOptions');

		$this_page    =  self::getCurrentURL();


		if ( in_array( $_GET['messages'], array('view','reply') ) )
		{
			$messageCode = sanitize_file_name( $_GET['message']);




			//performer message reply
			if ($_POST['newmessage'])
			{

				global $wpdb;
				$table_messages = $wpdb->prefix . "vw_vmls_messages";

				$ztime = time();

				$sender_id = get_current_user_id();

				$id = self::to10($messageCode);
				if (!$id) return 'Error: Incorrect message code! #' . $messageCode;


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

				$message = esc_sql(wp_kses($_POST['message'], $allowedtags));

				$sql = "INSERT INTO `$table_messages` ( `sender_id`, `webcam_id`, `reply_id`, `sdate`, `message`) VALUES ( '$sender_id', '$postID', '$id', '$ztime', '$message')";
				$wpdb->query($sql);
				$messageID = $wpdb->insert_id;
				if (!$messageID) return 'Error: Sending message failed! ' . $sql;


				//update last message time
				$sqlU = "UPDATE `$table_messages` SET `ldate` = '$ztime' WHERE id = '$id'";
				$wpdb->query($sqlU);

				//get paid for first reply
				$sqlC = "SELECT COUNT(id) FROM `$table_messages` WHERE reply_id='$id'";
				$repliesCount = $wpdb->get_var($sqlC);
				if (!$repliesCount) return 'Error: No replies! ' . $sqlC;


				//get paid
				$messagesCost = $options['messagesCost'];
				$performerRatio = self::performerRatio($post->post_name, $options, $postID);

				//only get paid for first reply and  if cost enabled
				if ($repliesCount == 1 && $messagesCost)
				{
					$messageURL = add_query_arg( array('messages' => 'view', 'message'=> $messageCode), $this_page);

					self::transaction($ref = "paid_message_earn", $sender_id, $messagesCost * $performerRatio, __('Paid Message Earning from ', 'ppv-live-webcams') . ' <a href="' . $messageURL . '">' . $messageCode .'</a>', $messageID, $sql, $options);
					$htmlCode .=  '<div id="successMessage" class="ui ' . $options['interfaceClass'] .' segment">' . __('Your reply was sent and you got paid.', 'ppv-live-webcams') .'</div>' ;

				} else $htmlCode .=  '<div id="successMessage" class="ui ' . $options['interfaceClass'] .' segment">' . __('Your reply was sent.', 'ppv-live-webcams') .'</div>' ;



			}


			//show msg & replies
			$htmlCode .= self::messageReplies($messageCode );

			//reply form
			$tinymce_options = array(
				'plugins' => "lists,link,textcolor,hr",
				'toolbar1' => "cut,copy,paste,|,undo,redo,|,fontsizeselect,forecolor,backcolor,bold,italic,underline,strikethrough",
				'toolbar2' => "alignleft,aligncenter,alignright,alignjustify,blockquote,hr,bullist,numlist,link,unlink",
				'fontsize_formats' => '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt' );


			$action = add_query_arg( array( 'messages'=>'reply', 'message'=> $messageCode), $this_page);
			$htmlCode .= '<form method="post" enctype="multipart/form-data" action="' . $action. '" name="messageForm" class="ui form segment">';

			ob_start();
			wp_editor( '', 'message', $settings = array(
					'textarea_rows'=>3, 'media_buttons' => false, 'teeny'=>true,  'wpautop'=>false,
					'tinymce' => $tinymce_options,) );
			$htmlCode .= ob_get_clean();


			$htmlCode .='<input class="ui button videowhisperButton" type="submit" name="newmessage" id="newmessage" value="' . __('Send Reply', 'ppv-live-webcams') . '" />
	<input name="post_id" id="post_id" type="hidden" value="' . $postID .'">
	</form>';

		}

		global $wpdb;
		$table_messages = $wpdb->prefix . "vw_vmls_messages";

		//list messages

		$htmlCode .=   '<div class="ui segment">';

		$sql = "SELECT * FROM $table_messages WHERE webcam_id = $postID AND reply_id = 0 ORDER BY sdate DESC, id DESC LIMIT 0, 100";

		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows>0)
		{
			$htmlCode .= '<div class="ui list celled ' . $options['interfaceClass'] .'">';

			foreach ($results as $message)
			{
				$messageCode = self::to62($message->id);

				$messageURL = add_query_arg( array('messages' => 'view', 'message'=> $messageCode), $this_page);
				$sender = get_userdata( $message->sender_id );

				//replies
				$sqlC = "SELECT COUNT(id) FROM `$table_messages` WHERE reply_id='" . $message->id . "'";
				$repliesCount = $wpdb->get_var($sqlC);


				$htmlCode .= '<div class="item"> <i class="mail icon big"></i>';
				$htmlCode .= '<div class="content">';
				$htmlCode .= '<div class="header">'  . ' <i class="user icon"></i>' . $sender->user_nicename .  ' <i class="clock icon"></i>' . date(DATE_RFC2822, $message->sdate) . ' <i class="arrow right icon"></i> #' . $messageCode  . ($repliesCount?' <i class="check icon"></i> ' . __('Replied', 'ppv-live-webcams') . '':' <i class="spinner loading icon"></i> <i class="green bell icon"></i>').  '</div>'  ;
				$htmlCode .= '<div class="description">' . wp_trim_excerpt($message->message) . '</div>';
				$htmlCode .= '<div class="description"><a href="' . $messageURL . '" class="ui button compact"> <i class="zoom-in icon"></i> ' .__('View Message', 'ppv-live-webcams'). '</a></div>';

				$htmlCode .= '</div></div>';
			}

			$htmlCode .=    __('You receive payment for paid messages on reply. Clients can access their messages and replies from client dashboard and in messages section from webcam profile.', 'ppv-live-webcams');

			$htmlCode .= '</div>';

		}
		else $htmlCode .=    __('No Messages', 'ppv-live-webcams');
		$htmlCode .= '</div>';


		return $htmlCode;
	}


		function videowhisper_webcams_client($atts)
		{
			self::enqueueUI();

			if (!is_user_logged_in()) return __('Login to access as client!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

			$options = get_option('VWliveWebcamsOptions');

			$user_ID = get_current_user_id();

			$htmlCode .= '<div class="ui green segment form">';
			$htmlCode .= '<h4>' . __('Active balance', 'ppv-live-webcams') . ': ' .  $balance . self::balance($user_ID) . '</h4>';
			$htmlCode .=  __('All Balances', 'ppv-live-webcams') . ': ' .  $balance . self::balances($user_ID) ;
			$htmlCode .= '<br><A class="ui button primary" HREF="' . get_permalink( $options['balancePage']) . '">' . __('Manage Wallet', 'ppv-live-webcams') . '</A>';
			$htmlCode .= '</div>';



			$htmlCode .= '<h3 class="ui header">' . __('My Questions and Messages', 'ppv-live-webcams') . '</h3>';
			$htmlCode .= do_shortcode('[videowhisper_cam_messages]');


			$htmlCode .= '<h3 class="ui header">' . __('My Calls', 'ppv-live-webcams') . '</h3>';
			$htmlCode .= do_shortcode('[videowhisper_cam_calls]');



			return $htmlCode;
		}
		
	function videowhisper_cam_messages($atts)
	{
		
		//shows client messages, to check replies
		
		if (!is_user_logged_in()) return __('Login to manage messages and replies!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';

		$atts = shortcode_atts(array(
				'post_id'=> 0, // 0 shows all
			), $atts, 'videowhisper_cam_messages_performer');

		//room id
		$postID = intval($atts['post_id']);
		if (!$postID) $postID = intval($_GET['webcam_id']); //pass by URL

		if ($postID) $post = get_post($postID);
		if (!$post) $postID = 0;

		//if (!$postID) return 'Error: Could not find this webcam room! #' .  $postID;


		$options = get_option('VWliveWebcamsOptions');

		$this_page    =  self::getCurrentURL();

		$sender_id = get_current_user_id();


		if ( $_GET['message'] && $_GET['view'] == 'messages'  )
		{
			$messageCode = sanitize_file_name( $_GET['message']);

			//show msg & replies
			$htmlCode .= self::messageReplies($messageCode );
		}	
		
		global $wpdb;
		$table_messages = $wpdb->prefix . "vw_vmls_messages";

		//list messages
		$htmlCode .=   '<div class="ui segment">';

		$cnd = '';
		if ($postID) $cnd = "AND webcam_id='$postID'";
		$sql = "SELECT * FROM $table_messages WHERE sender_id = $sender_id $cnd AND reply_id = 0 ORDER BY sdate DESC, id DESC LIMIT 0, 100";

		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows>0)
		{
			$htmlCode .= '<div class="ui list celled ' . $options['interfaceClass'] .'">';

			foreach ($results as $message)
			{
				
				//replies
				$sqlC = "SELECT COUNT(id) FROM `$table_messages` WHERE reply_id='" . $message->id . "'";
				$repliesCount = $wpdb->get_var($sqlC);

				
				$messageCode = self::to62($message->id);

				$messageURL = add_query_arg( array('view' => 'messages', 'message'=> $messageCode), $this_page);
				$sender = get_userdata( $message->sender_id );

				$htmlCode .= '<div class="item"> <i class="mail icon big"></i>';
				$htmlCode .= '<div class="content">';
				$htmlCode .= '<div class="header">'  . ' <i class="user icon"></i>' . $sender->user_nicename .  ' <i class="clock icon"></i>' . date(DATE_RFC2822, $message->sdate) .($repliesCount?' <i class="check icon"></i> ' . __('Replied', 'ppv-live-webcams') . '':' <i class="spinner loading icon"></i>'). '</div>'  ;

				$htmlCode .= '<div class="description">' . wp_trim_excerpt($message->message) . '</div>';
				$htmlCode .= '<div class="description"><a href="' . $messageURL . '" class="ui button compact"> <i class="zoom-in icon"></i> ' .__('View Message', 'ppv-live-webcams'). '</a></div>';

				$htmlCode .= '</div></div>';
			}

			$htmlCode .= '</div>';

		}
		else $htmlCode .=    __('No Messages', 'ppv-live-webcams');
		$htmlCode .= '</div>';


		return $htmlCode;
	}



	function videowhisper_cam_calls($atts)
	{
		//setup and list video calls

		if (!is_user_logged_in()) return __('Login to manage calls!','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>';


		$atts = shortcode_atts(array(
				'post_id'=> 0, //for setup
			), $atts, 'videowhisper_cam_calls');

		//room id
		$postID = intval($atts['post_id']);
		if ($postID) $post = get_post($postID);
		if (!$post) $postID = 0;

		$options = get_option('VWliveWebcamsOptions');

		//user info
		//performer (role)
		$current_user = wp_get_current_user();
		//access keys
		$userkeys = $current_user->roles;
		$userkeys[] = $current_user->user_login;
		$userkeys[] = $current_user->ID;
		$userkeys[] = $current_user->user_email;


		global $wpdb;
		$table_private = $wpdb->prefix . "vw_vmls_private";


		$roleS = implode(',', $current_user->roles);
		if ($postID) //only for a room
		if (self::any_in_array( array( $options['rolePerformer'], 'administrator', 'super admin'), $current_user->roles)) //can setup rooms/calls
			{
			//main webcam room/post

			/*
			//any owned webcam?
			if (!$postID)
			{
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_author = \'' . $current_user->ID . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
			}
			//create a room
			if (!$postID) $postID = self::webcamPost(); //default cam
			if ($postID && !$post) $post = get_post($postID);
			if (!$post) $postID = 0;
			if (!$postID) return 'Error: Could not find or setup a webcam room for this user!';
			*/

			$showNew = 1;

			if ($_GET['calls'] == 'new')
			{
				$htmlCode .=   '<div class="ui segment">';

				$client = trim(sanitize_text_field($_POST['client']));
				if ($client)
					if (filter_var($client, FILTER_VALIDATE_EMAIL))
					{
						$user = get_user_by('email', $client);
						$login = $user->user_login;
						$email = $client;
					}
				else
				{
					$user = get_user_by('login', $client);
					$login = $client;
					$email = $user->user_email;
				}

				if ($user)
				{
					$performer = self::performerName($current_user, $options);

					$meta = array( 'time' => time(), 'client' => $client, 'email' => $email, 'username' => $login);
					$metaS = serialize($meta);
					$room_name = $post->post_name;

					$sql="INSERT INTO `$table_private` ( `call`, `performer`, `pid`, `client`, `cid`, `rid`, `room`, `status`, `meta` ) VALUES ( '1', '$performer', '" . $current_user->ID . "', '" . $user->user_login . "', '" . $user->ID . "', '$postID', '$room_name', 0, '$metaS')";

					$wpdb->query($sql);
					$privateID = $wpdb->insert_id;

					$callURL = add_query_arg( array('call'=> self::to62($privateID)), get_permalink( $postID ));
					$htmlCode .= __('New call was setup', 'ppv-live-webcams') . ': <br>' . $callURL;

					$showNew = 0;
				}
				else
				{
					$htmlCode .= __('Error: Client was not found by email or username! User needs to register to be able to login and access the private call.', 'ppv-live-webcams');
					$htmlCode .= '<br>"' . $client.'"';
				}

				$htmlCode .=   '</div>';
			}

			if ($showNew && $postID)
			{
				$this_page    =  self::getCurrentURL();
				$action = add_query_arg( array('calls'=>'new'), $this_page);
				$htmlCode .= '<form method="post" enctype="multipart/form-data" action="' . $action. '" name="callForm" class="ui form inline segment">
	<input class="ui button videowhisperButton" type="submit" name="newcall" id="newcall" value="' . __('Setup Call', 'ppv-live-webcams') . '" />
	<input class="ui field" size=16 name="client" id="client" value="" placeholder="' . __('Client Username/Email', 'ppv-live-webcams') . '">
	<input name="post_id" id="post_id" type="hidden" value="' . $postID .'">
';
			$htmlCode .= '<p>' .__('Setup locked private calls that can only be accessed by client using dedicated call link. Clients can access their persistent calls list in client dashboard.  ', 'ppv-live-webcams') . '</p> 
			</form>';

			}
		}


		//list calls

		$htmlCode .=   '<div class="ui segment">';

		$uid = $current_user->ID;

		if ($postID) $sql = "SELECT * FROM $table_private WHERE rid = $postID AND `call` > 0 ORDER BY status ASC, id DESC LIMIT 0, 100";
		else $sql = "SELECT * FROM $table_private WHERE (cid = $uid OR pid = $uid) AND `call` > 0 ORDER BY status ASC, id DESC LIMIT 0, 100";

		$results = $wpdb->get_results($sql);
		if ($wpdb->num_rows>0)
		{
			$htmlCode .= '<div class="ui list">';

			foreach ($results as $private)
			{
				$callCode = self::to62($private->id);
				$callURL = add_query_arg( array('call'=> $callCode), get_permalink( $private->rid ));

				if ($private->meta) //call meta
					{
					$meta = unserialize($private->meta);
					$metaInfo = $meta['email'];
				}

				$htmlCode .= '<div class="item"> <i class="phone icon big"></i>';
				$htmlCode .= '<div class="content">';
				$htmlCode .= '<div class="header">'. $private->room . ' #' . $callCode . ' <i class="user icon"></i>' . ($current_user->ID ==  $private->pid?$private->client:$private->performer) . '</div>'  ;
				$htmlCode .= '<div class="description">' . $callURL . '</div>';
				if ($private->status == 0) $htmlCode .= '<div class="description"><a href="' . $callURL . '" class="ui button compact"> <i class="phone volume icon"></i> ' .__('Access', 'ppv-live-webcams'). '</a></div>';
				else $htmlCode .= '<div class="description">' . __('Closed', 'ppv-live-webcams') . '</div>';

				$htmlCode .= '</div></div>';
			}

			$htmlCode .= '</div>';

		}
		else $htmlCode .=    __('No Calls', 'ppv-live-webcams');

		$htmlCode .=   '</div>';


		return $htmlCode;
	}


	function videowhisper_camvideo($atts)
	{

		$atts = shortcode_atts(array(
				'cam' => '',
				'width' => '640px',
				'height' => '480px',
				'html5' => 'auto',
				'post_id'=> 0,
			), $atts, 'videowhisper_camvideo');

		$options = get_option('VWliveWebcamsOptions');

		$webcamName = $atts['cam'];
		if (!$webcamName) $webcamName = sanitize_file_name($_GET['cam']);

		if ($atts['post_id']) $postID = intval($atts['post_id']);

		if (!$postID)
		{
			global $wpdb;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $webcamName . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

		}

		$roomInterface =  get_post_meta( $postID, 'roomInterface', true); // html5/flash
		$streamType = get_post_meta( $postID, 'stream-type', true); // webrtc/flash/external/playlist

		$stream = self::webcamStreamName($webcamName, $postID);

		$width=$atts['width']; if (!$width) $width = "480px";
		$height=$atts['height']; if (!$height)  $height = '360px';

		if (!$stream)
		{
			return "Watch Video Error: Missing webcam stream name!";
		}

		//HLS if iOS/Android detected
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$Android = stripos($agent,"Android");
		$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
		$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));

		$htmlCode .= "<!--CamVideo:$webcamName|$postID|$roomInterface|$stream|$Android|$iOS|$Safari dH:" . $options['detect_hls'] . "-->";

		$showTeaser = 0;
		if ($options['teaserOffline'] && !self::webcamOnline($postID)) $showTeaser =1; //show offline teaser to client


		//webrtc
		if ($streamType == 'webrtc' && !$showTeaser)
			return $htmlCode.'<!--H5-WebRTC-->'. do_shortcode("[videowhisper_cam_webrtc_playback room=\"$webcamName\" webcam_id=\"$postID\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");

		//always
		if ($atts['html5'] == 'always')
		{
			if ($iOS || $Safari) return $htmlCode.'<!--H5-HLS-->'. do_shortcode("[videowhisper_camhls webcam=\"$webcamName\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
			else return $htmlCode.'<!--H5-MPEG-->'. do_shortcode("[videowhisper_cammpeg webcam=\"$webcamName\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
		}

		//detect for video interface
		if ( ($Android && in_array($options['detect_mpeg'], array('android', 'all'))) || (!$iOS && in_array($options['detect_mpeg'], array('all'))) || (!$iOS && !$Safari && in_array($options['detect_mpeg'], array('nonsafari'))) )
			return $htmlCode .'<!--MPEG-->'. do_shortcode("[videowhisper_cammpeg webcam=\"$webcamName\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");

		if ( (($Android||$iOS) && in_array($options['detect_hls'], array('mobile','safari', 'all'))) || ($iOS && $options['detect_hls'] == 'ios') || ($Safari && in_array($options['detect_hls'], array('safari', 'all'))) ) return $htmlCode .'<!--HLS-->'. do_shortcode("[videowhisper_camhls webcam=\"$webcamName\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");

		$afterCode = <<<HTMLCODE
<style type="text/css">
<!--

#videowhisper_container_$stream
{
position: relative;
width: $width;
height: $height;
border: solid 1px #999;
}

-->
</style>
HTMLCODE;

		return $htmlCode . self::app_video($stream, $webcamName, $width, $height) . $afterCode;

	}


	function app_video($stream, $room, $width = "100%", $height = '360px')
	{

		$stream = sanitize_file_name($stream);

		$swfurl = dirname(plugin_dir_url(__FILE__)) . "/videowhisper/live_video.swf?ssl=1&s=" . urlencode($stream) . '&n='. urlencode($room);
		$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vmls&task=');
		$swfurl .= '&extension='.urlencode('_none_');
		$swfurl .= '&ws_res=' . urlencode( dirname(plugin_dir_url(__FILE__)) . '/videowhisper/');

		$bgcolor="#333333";

		$htmlCode = <<<HTMLCODE
<div id="videowhisper_container_$stream">
<object id="videowhisper_video_$stream" width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>
HTMLCODE;

		$htmlCode .= self::flash_warn();

		return $htmlCode;

	}


	function videowhisper_cammpeg($atts)
	{
		//[videowhisper_cammpeg webcam="webcam name" width="480px" height="360px"]

		$stream = '';

		$options = get_option('VWliveWebcamsOptions');

		if (is_single())
			if (get_post_type( $postID = get_the_ID() ) == $options['custom_post']) $webcamName = get_the_title(get_the_ID());

			$atts = shortcode_atts(array('webcam' => $webcamName, 'width' => '480px', 'height' => '360px'), $atts, 'videowhisper_cammpeg');


		if (!$webcamName) $webcamName = $atts['webcam']; //parameter webcam="name"
		if (!$webcamName) $webcamName = $_GET['n'];


		if (!$postID)
		{
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($webcamName) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}

		$stream = self::webcamStreamName($webcamName, $postID, $options);

		$width=$atts['width']; if (!$width) $width = "480px";
		$height=$atts['height']; if (!$height) $height = "360px";

		if (!$stream)
		{
			return "Watch MPEG Dash Error: Missing channel name!";
		}

		$webcamOnline = self::webcamOnline($postID);
		if ($options['teaserOffline'] && !$webcamOnline) //offline: play teaser
			{
			$streamName = '';
			$streamPath = '';

			$video_teaser = get_post_meta($postID, 'video_teaser', true);
			$streamURL = self::vsvVideoURL($video_teaser, $options);
		}

		if ($options['transcodingAuto'] && $webcamOnline) //transcode if online
			{
			$streamName = self::transcodeStream($stream, 1, $webcamName, 2, 1, $options, $postID); //require transcoding name
		}

		if ($streamName)
		{
			$streamURL = $options['httpstreamer'] . $streamName .'/manifest.mpd';
		}

		if ($streamURL)
		{
			if (strstr($streamURL,'http://')) wp_enqueue_script('dashjs', 'http://cdn.dashjs.org/latest/dash.all.min.js');
			else wp_enqueue_script('dashjs', 'https://cdn.dashjs.org/latest/dash.all.min.js');

			//poster
			$thumbUrl = self::webcamThumbSrc($postID, $stream, $options);

			$htmlCode = <<<HTMLCODE
<!--MPEG Pi:$postID Wo:$webcamOnline Wn:$webcamName S:$stream Sn:$streamName T:$video_teaser Su:$streamURL-->
<video id="videowhisper_mpeg_$stream" class="videowhisper_htmlvideo" width="$width" height="$height" data-dashjs-player autobuffer autoplay loop playsinline muted="muted" controls="true" poster="$thumbUrl" src="$streamURL">
    <div class="fallback" style="display:none">
    <IMG SRC="$thumbUrl">
	    <p>HTML5 MPEG Dash capable browser (i.e. Chrome) is required to open this live stream: $streamURL</p>
	</div>
</video>
<br>MPEG-DASH: Enable sound from browser controls. Transcoding and HTTP based delivery technology may involve high latency.
HTMLCODE;
		}
		else $htmlCode = '<div class="warning">MPEG Dash format is not currently available for this stream: '. $stream.'</div>';

		return $htmlCode;
	}


	function videowhisper_camhls($atts)
	{

		//[videowhisper_camhls webcam="webcam name" width="480px" height="360px"]

		$stream = '';

		$options = get_option('VWliveWebcamsOptions');

		if (is_single())
			if (get_post_type( $postID = get_the_ID() ) == $options['custom_post']) $webcamName = get_the_title(get_the_ID());

			$atts = shortcode_atts(array('webcam' => $webcamName, 'width' => '480px', 'height' => '360px'), $atts, 'videowhisper_camhls');


		if (!$webcamName) $webcamName = $atts['webcam']; //parameter webcam="name"
		if (!$webcamName) $webcamName = $_GET['n'];

		if (!$postID)
		{
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($webcamName) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}

		$stream = self::webcamStreamName($webcamName, $postID, $options);


		$width=$atts['width']; if (!$width) $width = "480px";
		$height=$atts['height']; if (!$height) $height = "360px";

		if (!$stream)
		{
			return "Watch HLS Error: Missing channel name!";
		}



		$webcamOnline = self::webcamOnline($postID);
		if ($options['teaserOffline'] && !$webcamOnline) //offline: play teaser
			{
			$streamName = '';
			$streamPath = '';

			$video_teaser = get_post_meta($postID, 'video_teaser', true);
			$streamURL = self::vsvVideoURL($video_teaser, $options);
		}

		//auto transcoding
		if ($options['transcodingAuto'] && $webcamOnline) //transcode if online
			{
			$streamName = self::transcodeStream($stream, 1, $webcamName, 2, 1, $options, $postID); //require transcoding name
		}

		if ($streamName)
		{
			$streamURL = $options['httpstreamer'] . $streamName . '/playlist.m3u8';
		}

		if ($streamURL)
		{
			//poster
			$thumbUrl = self::webcamThumbSrc($postID, $stream, $options);

			$htmlCode = <<<HTMLCODE
<!--HLS:$postID:$webcamName:$stream-->
<video id="videowhisper_hls_$stream" class="videowhisper_htmlvideo" width="$width" height="$height" autobuffer autoplay loop controls="true" poster="$thumbUrl">
 <source src="$streamURL" type='video/mp4'>
    <div class="fallback" style="display:none">
    <IMG SRC="$thumbUrl">
	    <p>HTML5 HLS capable browser (i.e. Safari) is required to open this live stream: $streamURL</p>
	</div>
</video>
<br>HLS: Enable sound from your browser controls. Transcoding and HTTP based delivery technology may involve higher latency.

<br>
HTMLCODE;
		}
		else $htmlCode = '<div class="warning">HLS format is not currently available for this stream: '. $stream.'</div>';

		return $htmlCode;
	}


	function videowhisper_campreview($atts)
	{

		$options = get_option('VWliveWebcamsOptions');

		$atts = shortcode_atts(array('status' => 'online', 'order_by' => 'rand', 'category' => '', 'perPage' => 1, 'perRow' => 2, 'width' => '480px', 'height' => '360px'), $atts, 'videowhisper_campreview');

		$perPage = $atts['perPage'];
		$perRow = $atts['perRow'];

		$pstatus = $atts['status'];
		$order_by = $atts['order_by'];
		$category = $atts['category'];

		$width=$atts['width']; if (!$width) $width = "480px";
		$height=$atts['height']; if (!$height)  $height = '360px';

		$args=array(
			'post_type' => $options['custom_post'],
			'post_status' => 'publish',
			'posts_per_page' => $perPage,
			'offset'           => 0,
			'order'            => 'DESC',
			'meta_query' => array(
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
			)
		);

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

		$postslist = get_posts($args);

		// list cam previews

		$code = '';

		if (count($postslist)>0)
		{
			$k = 0;
			foreach ( $postslist as $item )
			{
				if ($perRow) if ($k) if ($k % $perRow == 0) echo '<br>';

						$webcamName = sanitize_file_name($item->post_title);

					$code .= do_shortcode("[videowhisper_camvideo cam=\"$webcamName\" width=\"$width\" height=\"$height\"]");
			}
		} else
		{
			$code .= "No cams currently match preview criteria.";
			//$code .= var_export($args);
		}

		return $code;
	}


	function videowhisper_camprofile($atts)
	{
		$atts = shortcode_atts(array('cam' => $stream, 'post_id' => 0), $atts, 'videowhisper_camprofile');

		$stream = $atts['cam'];
		if (!$stream) $stream = $_GET['cam'];
		$stream = sanitize_file_name($stream);

		$options = get_option('VWliveWebcamsOptions');

		$post_id = intval($atts['post_id']);

		if ($post_id) $postID = $post_id;
		elseif ($stream)
		{
			global $wpdb;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $stream . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
		}

		if (!$postID) return "videowhisper_camprofile error: Webcam not found ($stream/$post_id)!";

		return  self::webcamProfile($postID, $options);
	}


	function videowhisper_camcontent($atts)
	{
		$atts = shortcode_atts(array('cam' => $stream, 'post_id' => 0), $atts, 'videowhisper_camcontent');

		$stream = $atts['cam'];
		if (!$stream) $stream = $_GET['cam'];
		$stream = sanitize_file_name($stream);

		$options = get_option('VWliveWebcamsOptions');

		$post_id = intval($atts['post_id']);

		if ($post_id) $postID = $post_id;
		elseif ($stream)
		{
			global $wpdb;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $stream . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
		}

		if ($postID && !$stream)
		{
			global $wpdb;
			$stream = $wpdb->get_var( $sql = 'SELECT post_title FROM ' . $wpdb->posts . ' WHERE ID = \'' . $postID . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
		}

		if (!$postID || !$stream) return "videowhisper_camcontent error: Webcam not found ($stream/$post_id)!";


		return self::webcamContent($postID, $stream, $options);
	}


	function videowhisper_caminfo($atts)
	{
		$atts = shortcode_atts(array('cam' => '', 'info' => 'cpm', 'format' => 'csv' ), $atts, 'videowhisper_caminfo');

		$stream = $atts['cam'];
		if (!$stream) $stream = $_GET['cam'];
		$stream = sanitize_file_name($stream);

		if (!$stream) return 'No cam name!';

		$options = get_option('VWliveWebcamsOptions');

		//custom room cost per minute
		global $wpdb;
		$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $stream . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

		if (!$postID) return "Cam '$stream' not found!";

		switch ($atts['info'])
		{
		case 'cpm':
			$result = self::clientCPM($stream, $options, $postID);
			break;

		case 'online':
			$edate =  get_post_meta($postID, 'edate', true);
			$result = self::format_age(time() -  $edate);
			break;

		case 'label':
			$result = get_post_meta($postID, 'vw_roomLabel', true);
			break;

		case 'brief':
			$result = get_post_meta($postID, 'vw_roomBrief', true);
			break;

		case 'tags':
			$tags = wp_get_post_tags($postID, array( 'fields' => 'names' ));
			$roomTags = '';
			if ( ! empty( $tags ) ) if ( ! is_wp_error( $tags ) ) $result = $tags;
				break;

		case 'performers':
			$checkin = get_post_meta($postID, 'checkin', true);
			if ($checkin) foreach ($checkin as $performerID) $result[] = self::performerLink($performerID, $options);

				break;

		case 'groupMode':
			$result = get_post_meta($postID, 'groupMode', true);
			break;

		case 'groupCPM':
			$result = get_post_meta($postID, 'groupCPM', true);
			break;

		default:
			$result = 'Info type incorrect!';
		}

		switch ($atts['format'])
		{
		case 'csv':

			if (is_array($result))
			{
				foreach( $result as $tag )  $roomTags .= ($roomTags?', ':'') . $tag;
				return $roomTags;
			}
			else return $result;

			break;

		case 'serialize':
			return serialize($result);
			break;

		default:
			return $result;

			break;
		}

	}


	function videowhisper_webcams($atts)
	{
		$options = get_option('VWliveWebcamsOptions');

		//shortocode attributes
		$atts = shortcode_atts(
			array(
				'perPage'=>$options['perPage'],
				'ban' => '0',
				'perrow' => '',
				'order_by' => 'default',
				'category_id' => '',
				'pstatus' => '',
				'select_category' => '1',
				'select_tags' => '1',
				'select_name' => '1',
				'select_order' => '1',
				'select_status' => '1',
				'select_page' => '1',
				'include_css' => '1',
				'url_vars' => '1',
				'url_vars_fixed' => '1',
				'studioID' => '',
				'tags' => '',
				'name' => '',
				'id' => ''
			), $atts, 'videowhisper_webcams');

		$id = $atts['id'];
		if (!$id) $id = 1; //uniqid();

		//get vars from url
		if ($atts['url_vars'])
		{
			$cid = (int) $_GET['cid'];
			if ($cid)
			{
				$atts['category_id'] = $cid;
				if ($atts['url_vars_fixed']) $atts['select_category'] = '0';
			}
		}

		//semantic ui : listings
		self::enqueueUI();

		//ajax url
		$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_cams&pp=' . $atts['perPage']. '&pr=' . $atts['perrow'] . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&st=' . $atts['pstatus'] . '&sc=' . $atts['select_category'] . '&so=' . $atts['select_order'] . '&ss=' . $atts['select_status'] . '&sp=' . $atts['select_page'] . '&sn=' . $atts['select_name'] .  '&sg=' . $atts['select_tags'] . '&id=' .$id . '&tags=' . urlencode($atts['tags']) . '&name=' . urlencode($atts['name']);

		if ($atts['studioID']) $ajaxurl .= '&studioID=' . $atts['studioID'];
		if ($atts['ban']) $ajaxurl .= '&ban=' . $atts['ban'];


		$loadingMessage = '<div class="ui active inline text large loader">' . __('Loading Live Webcams','ppv-live-webcams') . '...</div>';

		$htmlCode = <<<HTMLCODE
<script>
var aurl$id = '$ajaxurl';
var \$j = jQuery.noConflict();
var loader$id;

	function loadWebcams$id(message){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperWebcams$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: aurl$id,
			data: "interfaceid=$id",
			success: function(data) {
				\$j("#videowhisperWebcams$id").html(data);
				jQuery(".ui.dropdown").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");
			}
		});
	}

	jQuery(document).ready(function(){
		loadWebcams$id();
		setInterval("loadWebcams$id()", 15000);
	});

</script>

<div id="videowhisperWebcams$id" class="videowhisperWebcams">
    $loadingMessage
</div>
<div style="clear:both" />
HTMLCODE;

		if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

		return $htmlCode;
	}


	function videowhisper_cam_webrtc_playback($atts)
	{
		$stream = '';
		$postID = 0;
		$options = get_option('VWliveWebcamsOptions');

		if (is_single())
		{
			$postID = get_the_ID();
			if (get_post_type( $postID ) ==  $options['custom_post'] ) $room = get_the_title($postID);
			else $postID = 0;
		}

		if (!$room) $room = $_GET['room'];

		$atts = shortcode_atts(array(
				'room' => $room,
				'width' => '480px',
				'height' => '360px',
				'webcam_id' => $postID,
				'silent' => 0,
			), $atts, 'videowhisper_cam_webrtc_playback');

		if ($atts['room']) $room = $atts['room']; //parameter channel="name"
		if ($atts['webcam_id']) $postID = $atts['webcam_id'];
		$width=$atts['width']; if (!$width) $width = "100%";
		$height=$atts['height']; if (!$height)  $height = '360px';

		$room = sanitize_file_name($room);

		if (!$postID && $room)
		{
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}

		if (!$room)
		{
			return "WebRTC Playback Error: Missing webcam room name!";
		}


		$stream = self::webcamStreamName($room, $postID);


		$userID = 0;
		if (is_user_logged_in())
		{
			$userName =  $options['userName']; if (!$userName) $userName='user_login';
			$current_user = wp_get_current_user();
			if ($current_user->$userName) $username = sanitize_file_name($current_user->$userName);
			$userID = $current_user->ID;
		}

		//detect browser
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$Android = stripos($agent,"Android");
		$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
		$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
		$Firefox = stripos($agent,"Firefox");

		$codeMuted = '';
		if (!$Firefox)
		{
			$codeMuted = 'muted';
		}

		//WebRTC playback: detect source type and transcode if necessary
		$streamProtocol = get_post_meta($postID, 'stream-protocol', true); //rtmp/webrtc
		$streamMode = get_post_meta($postID, 'stream-mode', true); //direct/safari_pc

		if ($streamProtocol == 'rtsp' && $streamMode == 'direct') $stream_webrtc = $stream;
		else $stream_webrtc = self::transcodeStreamWebRTC($stream, $postID, $options);

		if (!$stream_webrtc) $htmlCode .= 'Error: WebRTC stream name not available!';


		$streamQuery = self::webrtcStreamQuery($userID, $postID, 0, $stream_webrtc, $options, 0, $room);

		self::enqueueUI();

		wp_enqueue_script( 'webrtc-adapter', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/adapter.js', array('jquery'));
		wp_enqueue_script( 'videowhisper-webrtc-playback', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/vwrtc-playback.js', array('jquery', 'webrtc-adapter'));

		$wsURLWebRTC = $options['wsURLWebRTC'];
		$applicationWebRTC = $options['applicationWebRTC'];



		$htmlCode .= <<<HTMLCODE
		<div class="videowhisper-webrtc-video">
		<video id="remoteVideo" class="videowhisper_htmlvideo" autoplay playsinline controls $codeMuted style="width:$width; height:$height"></video>
		<!--$streamProtocol|$stream|$stream_webrtc-->
		</div>

		<div>
			<span id="sdpDataTag"></span>
		</div>

		<script type="text/javascript">

			var videoBitrate = 360;
			var audioBitrate = 64;
			var videoFrameRate = "29.97";
			var videoChoice = "$videoCodec";
			var audioChoice = "$audioCodec";

			var userAgent = navigator.userAgent;
		    var wsURL = "$wsURLWebRTC";
			var streamInfo = {applicationName:"$applicationWebRTC", streamName:"$streamQuery", sessionId:"[empty]"};
			var userData = {param1:"value1","videowhisper":"webrtc-playback"};

		jQuery( document ).ready(function() {
 		browserReady();
});

		</script>
HTMLCODE;

		if (!$atts['silent'])
		{
			if ($codeMuted) $htmlCode .=  "Playback is muted to allow auto play: enable audio from player controls.";
		}
		return $htmlCode;
	}


	function videowhisper_cam_webrtc_broadcast($atts)
	{
		$stream = '';

		if (!is_user_logged_in()) return "<div class='error'>" . __('Broadcasting not allowed: Only logged in users can broadcast!', 'ppv-live-webcams') . '</div>';

		$options = get_option('VWliveWebcamsOptions');


		//1. webcam post
		$postID = get_the_ID();
		if (is_single())
			if (get_post_type( $postID ) == $options['custom_post'])
			{
				$room = get_the_title($postID);
			}

		//2. shortcode param
		//$stream = get_post_meta($postID, 'performer', true);

		$atts = shortcode_atts(array(
				'room' => $room,
				'webcam_id' => $postID,
			), $atts, 'videowhisper_cam_webrtc_broadcast');

		if ($atts['room']) $room = $atts['room'];

		$room = sanitize_file_name($room);

		if (!$room) return "<div class='error'>Can't load application: Missing room name!</div>";

		$postID = $atts['webcam_id'];

		if ($room && !$postID)
		{
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}

		$post = get_post( $postID);
		if (!$post) return "<div class='error'>Webcam post not found!</div>";

		//performer check
		$current_user = wp_get_current_user();

		$loggedin=0;
		$msg="";
		$performer=0;
		$balance=0;
		$uid = 0;


		//user info
		if ($current_user) if ($current_user->ID >0)
			{

				//identify if user can be room performer
				if (self::isRoomPerformer($post, $current_user)) $performer =1;
				else $performer = 0;
				if (!$performer) return "<div class='error'>Only room performers are allowed to broadcast!</div>";

				$performerName = self::performerName($current_user, $options);
				$stream = $performerName;

				//$debug .= "p_a:".$post->post_author .".uID:".$current_user->ID.".pName:$performerName.s:$stream";
				$uid = $current_user->ID;

				if ($uid)
				{
					self::billSessions($uid);
					$balance = self::balance($uid);
					$balancePending = $balance;
				}

			}

		$streamQuery = self::webrtcStreamQuery($current_user->ID, $postID, 1, $stream, $options, 0, $room);

		//detect browser
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$Android = stripos($agent,"Android");
		$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
		$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
		$Firefox = stripos($agent,"Firefox");

		//publishing as WebRTC - save info for responsive playback
		update_post_meta($postID, 'performer', $stream);
		update_post_meta($postID, 'performerUserID', $current_user->ID );

		update_post_meta($postID, 'stream-protocol', 'rtsp');
		update_post_meta($postID, 'stream-type', 'webrtc');
		update_post_meta($postID, 'roomInterface', 'html5');

		if (!$iOS && $Safari) update_post_meta($postID, 'stream-mode', 'safari_pc'); //safari on pc encoding profile issues
		else update_post_meta($postID, 'stream-mode', 'direct');

		self::enqueueUI();

		wp_enqueue_script( 'webrtc-adapter', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/adapter.js', array('jquery'));
		wp_enqueue_script( 'videowhisper-webrtc-broadcast', dirname(plugin_dir_url(  __FILE__ )) . '/scripts/vwrtc-publish.js', array('jquery', 'webrtc-adapter'));


		$wsURLWebRTC = $options['wsURLWebRTC'];
		$applicationWebRTC = $options['applicationWebRTC'];

		$videoCodec = $options['webrtcVideoCodec']; //42e01f
		$audioCodec = $options['webrtcAudioCodec']; //opus

		$videoBitrate = (int) $options['webrtcVideoBitrate'];
		if (!$videoBitrate) $videoBitrate = 400; //400 max for tcp with Wowza

		$htmlCode .= "<!--WebRTC_Broadcast|r:$room|p:$postID|$agent|i:$iOS|a:$Android|Sa:$Safari|Ff:$Firefox-->";

		$broadcastCode = <<<HTMLCODE
		<div class="videowhisper-webrtc-camera">
		<video id="localVideo" class="videowhisper_htmlvideo" autoplay playsinline muted style="widht:640px;height:480px;"></video>
		</div>

		<div class="ui segment form">
			<span id="sdpDataTag">Connecting...</span>

<hr class="divider" />
    <div class="field inline">
        <label for="videoSource">Video Source </label><select class="ui dropdown" id="videoSource"></select>
    </div>

    <div class="field inline">
        <label for="videoResolution">Video Resolution </label><select class="ui dropdown" id="videoResolution"></select>
    </div>

	 <div class="field inline">
        <label for="audioSource">Audio Source </label><select class="ui dropdown" id="audioSource"></select>
    </div>

    		</div>


		<script type="text/javascript">

			var userAgent = navigator.userAgent;
		    var wsURL = "$wsURLWebRTC";
			var streamInfo = {applicationName:"$applicationWebRTC", streamName:"$streamQuery", sessionId:"[empty]"};
			var userData = {param1:"value1","videowhisper":"webrtc-broadcast"};
			var videoBitrate = $videoBitrate;
			var audioBitrate = 64;
			var videoFrameRate = "29.97";
			var videoChoice = "$videoCodec";
			var audioChoice = "$audioCodec";

		jQuery( document ).ready(function() {
 		browserReady();
 		jQuery(".ui.dropdown").dropdown();

});
		</script>
HTMLCODE;


		//AJAX Chat for WebRTC broadcasting

		//htmlchat ui
		//css
		wp_enqueue_style( 'jScrollPane', dirname(plugin_dir_url(__FILE__)) .'/htmlchat/js/jScrollPane/jScrollPane.css');
		wp_enqueue_style( 'htmlchat', dirname(plugin_dir_url(__FILE__)) .'/htmlchat/css/chat-broadcast.css');

		//js
		wp_enqueue_script("jquery");
		wp_enqueue_script( 'jScrollPane-mousewheel', dirname(plugin_dir_url(  __FILE__ )) . '/htmlchat/js/jScrollPane/jquery.mousewheel.js');
		wp_enqueue_script( 'jScrollPane', dirname(plugin_dir_url(  __FILE__ )) . '/htmlchat/js/jScrollPane/jScrollPane.min.js');
		wp_enqueue_script( 'htmlchat', dirname(plugin_dir_url(  __FILE__ )) . '/htmlchat/js/script.js', array('jquery','jScrollPane'));

		$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_htmlchat&room=' . urlencode(sanitize_file_name($stream));

		$loginCode = '<a href="' . wp_login_url() . '">Login is required to chat!</a>';
		$buttonSFx = dirname(plugin_dir_url(__FILE__)) . '/videowhisper/templates/messenger/message.mp3';
		$tipsSFx = dirname(plugin_dir_url(__FILE__)) . '/videowhisper/templates/messenger/tips/';


		if ($options['tips'])
		{

			// broacaster: only balance

			$tipbuttonCodes = '<p>Viewers can send you tips. Balance will update shortly after receiving a tip.</p>';


			$tipsCode =<<<TIPSCODE
<div id="tips" class="ui segment form">
<div class="inline fields">

<div class="ui label olive large">
  <i class="money bill alternate icon large"></i>Balance: <span id="balanceAmount" class="inline"> - </span>
</div>

$tipbuttonCodes
</div>
</div>
TIPSCODE;
		}



		$htmlCode .= <<<HTMLCODE
<div id="videochatContainer">
<!--Room:$stream-->
<div id="streamContainer">
$broadcastCode
</div>

<div id="chatContainer">

    <div id="chatUsers" class="ui segment"></div>

    <div id="chatLineHolder"></div>

    <div id="chatBottomBar" class="ui segment">
    	<div class="tip"></div>

        <form id="loginForm" method="post" action="" class="ui form">
Login is required to chat!
		</form>

        <form id="submitForm" method="post" action="" class="ui form">
            <input id="chatText" name="chatText" class="rounded" maxlength="255" />
            <input type="submit" class="ui button" value="Submit" />
        </form>

    </div>
</div>
</div>
$tipsCode

<script>
var vwChatAjax= '$ajaxurl';
var vwChatButtonSFx =  '$buttonSFx';
var vwChatTipsSFx =  '$tipsSFx';
</script>


HTMLCODE;

		if ($options['transcodingWarning']>=1) $htmlCode .= '<p class="info"><small>Warning: WebRTC will play directly where possible, depending on settings and viewer device. If transcoding is needed for playback, it may take up to a couple of minutes for transcoder to start and WebRTC published stream to become available for RTMP and HLS/MPEG DASH playback.
<BR>For advanced features use advanced web broadcasting interface available in PC browser with Flash plugin.</small></p>' .
				'<p><a class="ui button secondary" href="' . add_query_arg(array('flash-broadcast'=>''), get_permalink($postID)) . '">Try Advanced Flash Broadcast (PC)</a></p>';

		return $htmlCode;


	}


	static function flash_warn()
	{

		$agent = $_SERVER['HTTP_USER_AGENT'];
		$Android = stripos($agent,"Android");
		$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
		$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));

		$extraInfo = '';
		if ($Safari && !$iOS && !$Android) $extraInfo .= '<u><A href="https://helpx.adobe.com/flash-player/kb/enabling-flash-player-safari.html" rel="nofollow" target="_flash">Follow these instructions to enable Flash plugin in PC Safari!</A></u>';


		$htmlCode = <<<HTMLCODE
<div id="flashWarning"></div>

<script>
	function detectflash(){
    if (navigator.plugins != null && navigator.plugins.length > 0){
        return navigator.plugins["Shockwave Flash"] && true;
    }
    if(~navigator.userAgent.toLowerCase().indexOf("webtv")){
        return true;
    }
    if(~navigator.appVersion.indexOf("MSIE") && !~navigator.userAgent.indexOf("Opera")){
        try{
            return new ActiveXObject("ShockwaveFlash.ShockwaveFlash") && true;
        } catch(e){}
    }
    return false;
}

//var hasFlash = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));
var hasFlash = detectflash();

var flashWarn = '<small>Using the Flash web based interface requires <a rel="nofollow" target="_flash" href="https://get.adobe.com/flashplayer/">latest Flash plugin</a> and <a rel="nofollow" target="_flash" href="https://helpx.adobe.com/flash-player.html">activating plugin in your browser</a>. Flash apps are recommended on PC for best latency and most advanced features. $extraInfo</small>'

if (!hasFlash) document.getElementById("flashWarning").innerHTML = flashWarn;

</script>
HTMLCODE;


		return $htmlCode;
	}


	function videowhisper_videochat($atts)
	{
		//Shortcode: shows videochat interface depending on mode, device

		$stream = ''; //room name

		$options = get_option('VWliveWebcamsOptions');

		//username used with application
		/*
			$userName =  $options['userName'];
			if (!$userName) $userName='user_nicename';
			global $current_user;
			get_currentuserinfo();
			if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);
			$postID = 0;
			*/

		//1. webcam post
		$postID = 0;
		if (is_single())
			if (get_post_type( $postID = get_the_ID() ) == $options['custom_post'])
			{
				$room = get_the_title($postID);
				$stream = $room;
			} else $postID = 0; //post or page?

		$atts = shortcode_atts(
			array(
				'room' => $stream,
				'flash' => '0',
				'html5' => '0',
				'webcam_id' => $postID,
			), $atts, 'videowhisper_videochat');

		//2. shortcode param
		//$stream = get_post_meta($postID, 'performer', true);
		if (!$room) $room = $atts['room'];


		$room = sanitize_file_name($room);
		$postID = $atts['webcam_id'];

		global $wpdb;

		if (!$room && $postID)
		{
			$room = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE ID = '" . $postID . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}


		if (!$room) return "<div class='ui segment red'>Can't load application: Missing room name!</div>";

		if ($room && !$postID)
		{
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}


		//login

		if (is_user_logged_in())
		{
			$current_user = wp_get_current_user();
			$uid = $current_user->ID;

			$post = get_post($postID);
			$performer = 0;
			if (self::isRoomPerformer($post, $current_user)) $performer =1;
		}
		else
		{
			$uid = 0;
			$performer = 0;

			//paid room requires login
			$groupCPM = get_post_meta($postID, 'groupCPM', true);
			if ($groupCPM) return  "<div class='ui segment red'>Only registered and logged in users can access rooms in paid mode.</div>";

		}


		$attsPrivate = '';


		if (!$performer)
		{
			//free mode limits: check before rendering videochat room
			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vmls_sessions";


			if ($uid) $cnd = "uid='$uid'";
			else
			{
				if (!$clientIP) $clientIP = self::get_ip_address();
				$cnd = "ip='$clientIP' AND uid='0'";
			}

			$h24 = time() - 86400;
			$sqlC = "SELECT SUM(edate-sdate) FROM `$table_sessions` WHERE $cnd AND sdate > $h24";
			$freeTime = $wpdb->get_var($sqlC);

			if ($uid) if ($freeTime > $options['freeTimeLimit']) $disconnect = __("Free chat daily time limit reached: You can only access paid group rooms today!", "ppv-live-webcams") . ' (' . $freeTime .'s > '. $options['freeTimeLimit'] . 's)';
				if (!$uid) if ($freeTime > $options['freeTimeLimitVisitor']) $disconnect = __("Free chat daily visitor time limit reached: Register and login for more chat time today!", "ppv-live-webcams") . ' (' . $freeTime .'s > '. $options['freeTimeLimitVisitor'] . 's)';

					if ($disconnect) return  "<div class='ui segment red'>$disconnect</div>";
		}



		//flash/webrtc room interface
		$roomInterface = get_post_meta($postID, 'roomInterface', true); //flash/html5/html5app
		$playlistActive = get_post_meta($postID, 'vw_playlistActive', true);


		//special modes (for clients)
		$isVoyeur = 0;
		$is2way = 0;
		$isPrivate = 0;

		if (!$specialMode) $specialMode = $_GET['vwsm'];

		if ($specialMode && is_user_logged_in())
		{

			$groupParameters =  get_post_meta($postID, 'groupParameters', true);
			//var_dump($groupParameters);

			//enable mode list for room and capabilities for user:

			switch ($specialMode)
			{

			case 'private':
				$isPrivate = 1;
				if (!$attsPrivate) $attsPrivate = 'private="1"';

				break;

			case '2way':
				$mode2way = get_post_meta( $postID, 'mode2way', true );

				if (!is_array($mode2way)) $mode2way = array();

				//add user if slots available
				if (count($mode2way) < $groupParameters['2way'])
				{
					$mode2way[$uid] = time();
					update_post_meta( $postID, 'mode2way', $mode2way);
					$is2way = 1;
				}
				break;

			case 'voyeur':

				$modeVoyeur = get_post_meta( $postID, 'modeVoyeur', true );
				if (!is_array($modeVoyeur)) $modeVoyeur = array();

				if ($groupParameters['voyeur'])
				{
					$modeVoyeur[$uid] = time();
					update_post_meta( $postID, 'modeVoyeur', $modeVoyeur);
					$isVoyeur = 1;
				}
				break;
			}


		}


		//HLS if iOS/Android detected
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$Android = strstr($agent,"Android");
		$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
		$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));

		$htmlCode .= "<!--VideoChat:$roomInterface room:$room postID:$postID Performer:$performer SpecialMode:$specialMode 2Way:$is2way Voyeur:$isVoyeur Device:$Android|$iOS|$Safari($agent) FallbackStart -->";

		$fallback = 0;

		if (!$atts['flash'] && ($roomInterface != 'flash' || !$performer)) //unless forced to use flash or performer wants flash
			{

			if ($options['webrtc']>2)
				if ($_GET['html5app'] || $roomInterface == 'html5app' || !self::webcamOnline($postID) )
				{
					$htmlCode .= do_shortcode("[videowhisper_cam_app room=\"$room\" webcam_id=\"$postID\" $attsPrivate]");
					$htmlCode .= apply_filters("vw_plw_videochat", '', $postID)  ;
					return $htmlCode;
				}


			//performer
			if ($performer)
				if ($roomInterface == 'html5' || $Android || $iOS || ($_GET['html5'] && $options['htmlchatTest']) || $atts['html5'])
				{
					$htmlCode .= do_shortcode("[videowhisper_cam_webrtc_broadcast room=\"$room\" webcam_id=\"$postID\"]");
					$htmlCode .= apply_filters("vw_plw_videochat", '', $postID)  ;
					return $htmlCode;
				}
			else
			{
				$showHTML5 = 0; //performer in flash mode
				$fallback = 0;
			}
			else //client
				{

				if ($options['teaserOffline'] && !self::webcamOnline($postID)) //show offline teaser to client
					{
					$showHTML5 = 1;
					$comment .= ' Teaser';
					$fallback = 1;
					$showTeaser = 1;
				}

				//client in webrtc room
				if ($roomInterface == 'html5' && !$showTeaser)
				{
					if (!$isVoyeur) $htmlCode .= do_shortcode("[videowhisper_htmlchat room=\"$room\" post_id=\"$postID\" width=\"$width\" height=\"$height\"]");
					else $htmlCode .= do_shortcode("[videowhisper_cam_webrtc_playback room=\"$room\" webcam_id=\"$postID\" width=\"$width\" height=\"$height\"]");

					$htmlCode .= apply_filters("vw_plw_videochat", '', $postID)  ;
					return $htmlCode;
				}

				if ($options['transcoding']>=3) //adaptive or preferred html5
					{
					if ($playlistActive && !$performer)
					{
						$showHTML5 = 1;
						$comment .= ' Playlist';
						$fallback = 1;
					}
				}

				//detection for client videochat interface
				if ( ($Android && in_array($options['detect_mpeg'], array('android', 'all'))) || (!$iOS && in_array($options['detect_mpeg'], array('all'))) || (!$iOS && !$Safari && in_array($options['detect_mpeg'], array('nonsafari'))) )
				{
					$showHTML5 = 1;
					$fallback = 1;
					$comment .= ' detectMPEG';
				}

				/*
						if ($options['htmlchat'] && !$isVoyeur)
						{
							$htmlCode .= do_shortcode("[videowhisper_htmlchat room=\"$room\" post_id=\"$postID\" width=\"$width\" height=\"$height\"]");
							$fallback = 1;
						}
					else
					{
						$htmlCode .= do_shortcode("[videowhisper_cammpeg webcam=\"$room\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
						$fallback = 2;
					}
*/

				if ( (($Android||$iOS) && in_array($options['detect_hls'], array('mobile','safari', 'all'))) || ($iOS && $options['detect_hls'] == 'ios') || ($Safari && in_array($options['detect_hls'], array('safari', 'all'))) )
				{
					$showHTML5 = 1;
					$fallback = 1;
					$comment .= ' detectHLS';
				}
				/*
						if ($options['htmlchat'] && !$isVoyeur)
						{
							$htmlCode .=  do_shortcode("[videowhisper_htmlchat room=\"$room\" post_id=\"$postID\" width=\"$width\" height=\"$height\" ]");
							$fallback = 1;
						}
					else
					{
						$htmlCode .=  do_shortcode("[videowhisper_camhls webcam=\"$room\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
						$fallback = 3;
					}
					*/


				if ($showHTML5)
				{
					$htmlCode .='<!--$showHTML5: ' . $comment .' -->';
					if (!$isVoyeur) $htmlCode .= do_shortcode("[videowhisper_htmlchat room=\"$room\" post_id=\"$postID\" width=\"$width\" height=\"$height\"]");
					else $htmlCode .= '<H4>Voyeur Mode</H4>'.do_shortcode('[videowhisper_camvideo post_id="'.$postID.'" cam="'.$room.'" html5="always"]');
				}


			}



			//forced testing, even when  should show flash
			if ($options['htmlchatTest'] && ($_GET['htmlchat']||$_GET['html5']))
			{
				$htmlCode .= '<!--Test Chat-->' . do_shortcode("[videowhisper_htmlchat post_id=\"$postID\" room=\"$room\"]");
				$fallback = 1;
			}

			if ($options['htmlchatTest'] && $_GET['hls'])
			{
				$htmlCode .=  '<!--Test HLS-->' . do_shortcode("[videowhisper_camhls webcam=\"$room\" width=\"$width\" height=\"$height\"]");
				$fallback = 3;
			}

			if ($options['htmlchatTest'] && $_GET['mpeg'])
			{
				$htmlCode .=  '<!--Test MPEG-->' . do_shortcode("[videowhisper_cammpeg webcam=\"$room\" width=\"$width\" height=\"$height\"]");
				$fallback = 2;
			}

			if ($fallback) $htmlCode .= '<!--HTML5 Videochat End-->';
		}

		//Flash app
		if (!$fallback)
		{
			if ($isVoyeur)
				$htmlCode .= '<H4>Voyeur Mode</H4>'.do_shortcode('[videowhisper_camvideo cam="'.$room.'"]');
			else
			{

				$swfurl = dirname(plugin_dir_url(__FILE__)) . "/videowhisper/videomessenger.swf?ssl=1&room=" . urlencode($room);
				$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vmls&task=');
				$swfurl .= '&extension='.urlencode('_none_');
				$swfurl .= '&ws_res=' . urlencode( dirname(plugin_dir_url(__FILE__)) . '/videowhisper/');

				$bgcolor="#333333";

				$htmlCode .= <<<HTMLCODE
<div id="videowhisper_container">
<object id="videowhisper_flash" width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
<!-- $stream : $agent -->
</div>

<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_flash
{
width: 100%;
height: 700px;
max-height: 700px;
border: solid 3px #999;
display: block;
}

-->
</style>

HTMLCODE;

				$htmlCode .= self::flash_warn();

				$htmlCode .= '<p>';
				if ($performer && $options['webrtc'] >= 2) $htmlCode .= '<a class="ui button secondary" href="' . add_query_arg(array('html5app'=>'1'), get_permalink($postID)) . '">Try HTML5 App</a>';

				if ($options['transcoding'] >= 2 ||  ($performer && $options['webrtc'] >= 2))
				{
					if ($options['htmlchatTest'])
						$htmlCode .= '<a class="ui button secondary" href="' . add_query_arg(array('html5'=>'1'), get_permalink($postID)) . '">Try HTML5 Streaming</a>';
				}

				$htmlCode .= '</p>';


			}
		} else $htmlCode .= 'Error: Fallback disabled!';

		$htmlCode .= apply_filters("vw_plw_videochat", '', $postID)  ;

		return $htmlCode;
	}


	function videowhisper_htmlchat($atts)
	{
		//[videowhisper_htmlchat room="webcam name" width="480px" height="360px"]

		$atts = shortcode_atts(array('room' => '', 'post_id'=>'0', 'videowidth' => '480px', 'videoheight' => '360px'), $atts, 'videowhisper_htmlchat');

		$room = $atts['room'];
		if (!$room) $room = $_GET['room'];

		$room = sanitize_file_name($room);

		$postID = intval($atts['post_id']);


		$options = get_option('VWliveWebcamsOptions');

		if (!$postID)
		{
			global $wpdb;

			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

		}

		$videowidth=$atts['videowidth']; if (!$videowidth) $videowidth = "480px";
		$videoheight=$atts['videoheight']; if (!$videoheight)  $videoheight = '360px';

		if (!$room)
		{
			return "HTML AJAX Chat Error: Missing room name!";
		}

		$isPerformer = self::isAuthor($postID); //is current user performer?


		self::enqueueUI();

		//htmlchat ui
		//css
		wp_enqueue_style( 'jScrollPane', dirname(plugin_dir_url(__FILE__)) .'/htmlchat/js/jScrollPane/jScrollPane.css');
		wp_enqueue_style( 'htmlchat', dirname(plugin_dir_url(__FILE__)) .'/htmlchat/css/chat-watch.css');

		//js
		wp_enqueue_script("jquery");
		wp_enqueue_script( 'jScrollPane-mousewheel', dirname(plugin_dir_url(__FILE__)) . '/htmlchat/js/jScrollPane/jquery.mousewheel.js');
		wp_enqueue_script( 'jScrollPane', dirname(plugin_dir_url(__FILE__)) . '/htmlchat/js/jScrollPane/jScrollPane.min.js');
		wp_enqueue_script( 'htmlchat', dirname(plugin_dir_url(__FILE__)) . '/htmlchat/js/script.js', array('jquery','jScrollPane'));

		$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_htmlchat&room=' . urlencode($room);


		$videoCode = do_shortcode('[videowhisper_camvideo cam="'.$room.'" post_id="'.$postID.'" html5="always"]');

		$loginCode = '<a class="ui button" href="' . wp_login_url() . '">Login is required to chat!</a>';

		$buttonSFx = dirname(plugin_dir_url(__FILE__)) . '/videowhisper/templates/messenger/message.mp3';
		$tipsSFx = dirname(plugin_dir_url(__FILE__)) . '/videowhisper/templates/messenger/tips/';

		if ($options['tips'])
		{

			//tip options
			$tipOptions = stripslashes($options['tipOptions']);
			if ($tipOptions)
			{
				$p = xml_parser_create();
				xml_parse_into_struct($p, trim($tipOptions), $vals, $index);
				$error = xml_get_error_code($p);
				xml_parser_free($p);

				if (is_array($vals)) foreach ($vals as $tKey=>$tip)
						if ($tip['tag'] == 'TIP')
							if ($tip['attributes']['AMOUNT']!='custom') //custom tips not yet supported in html interface
								{
								//var_dump($tip['attributes']);
								$amount = intval($tip['attributes']['AMOUNT']);
								if (!$amount) $amount = 1;
								$label = $tip['attributes']['LABEL'];
								if (!$label) $label = '$1 Tip';
								$note = $tip['attributes']['NOTE'];
								if (!$note) $label = 'Tip';
								$sound = $tip['attributes']['SOUND'];
								if (!$sound) $sound = 'coins1.mp3';
								$image = $tip['attributes']['IMAGE'];
								if (!$image) $image = 'gift1.png';

								$imageURL = $tipsSFx . $image;

								$tipbuttonCodes .=<<<TBCODE
	<div class="tipButton ui labeled button small" tabindex="0" amount="$amount" label="$label" note="$note" sound="$sound" image="$image">
  <div class="ui button">
    <img class="mini image avatar" src="$imageURL"> $label
  </div>
  <a class="ui basic label small">
    $amount
  </a>
</div>
TBCODE;
							}
			}

			//
			$balanceURL = '#';
			if ($options['balancePage']) $balanceURL = get_permalink( $options['balancePage']);

			$tipsCode =<<<TIPSCODE
<div id="tips" class="ui segment form">
<div class="inline fields">

<a href="$balanceURL" target="_balance" class="ui label olive large">
  <i class="money bill alternate icon large"></i>Balance: <span id="balanceAmount" class="inline"> - </span>
</a>

$tipbuttonCodes
</div>
</div>
TIPSCODE;
		}



		$htmlCode = <<<HTMLCODE
<div id="videochatContainer">
<!--$room-->
<div id="streamContainer">
$videoCode
</div>

<div id="chatContainer">
    <div id="chatUsers" class="ui segment"></div>

    <div id="chatLineHolder"></div>

    <div id="chatBottomBar" class="ui segment">

    	<div class="tip"></div>

        <form id="loginForm" method="post" action="" class="ui form">
$loginCode
		</form>

        <form id="submitForm" method="post" action="" class="ui form">
            <input id="chatText" name="chatText" class="rounded" maxlength="255" />
            <input id="submit" type="submit" class="ui button" value="Submit" />
        </form>

    </div>

</div>
</div>
$tipsCode

<script>
var vwChatAjax= '$ajaxurl';
var vwChatButtonSFx =  '$buttonSFx';
var vwChatTipsSFx =  '$tipsSFx';

var \$jQ = jQuery.noConflict();
\$jQ(document).ready(function(){
\$jQ('.tipButton').popup();
});
</script>

HTMLCODE;

		return $htmlCode;

	}



}
