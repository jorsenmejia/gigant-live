<?php
namespace VideoWhisper\LiveWebcams;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


define('VW_H5V_DEVMODE', 0);
define('VW_H5V_DEVMODE_COLLABORATION', 0);
define('VW_H5V_DEVMODE_CLIENT', 0);


if (VW_H5V_DEVMODE) ini_set('display_errors', 1); //debug only

trait H5Videochat {

	function videowhisper_cam_instant($atts)
	{
		//Shortcode: Instant Cam Setup & Access

		if (!is_user_logged_in())  return '<br><div id="performerDashboardMessage" class="ui orange segment">' .  __('Login is required to access your own videochat room!','ppv-live-webcams') . '<br><a class="ui button inverted primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button inverted secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a></div>';

		$options = self::getOptions();
		$current_user = wp_get_current_user();

		//approval required?
		if (!self::userEnabled($current_user, $options, 'Performer')) return $htmlCode . '<div id="performerDashboardMessage" class="ui yellow segment">' .  __('Your account is not currently enabled. Update your account records and wait for site admins to approve your account.','ppv-live-webcams') . '</div>' .  do_shortcode('[videowhisper_account_records]');

		//disabled by studio: no access
		$disabled = get_user_meta($current_user->ID, 'studioDisabled', true);
		if ($disabled) return $htmlCode . '<div id="performerDashboardMessage" class="ui red segment">' . __('Studio disabled your account: dashboard access is forbidden. Contact studio or site administrator!','ppv-live-webcams') . '</div>';

		//selected webcam?
		$postID = get_user_meta($current_user->ID, 'currentWebcam', true);
		if ($postID) $post = get_post($postID); if (!$post) $postID = 0;


		//any owned webcam?
		if (!$postID)
		{
			global $wpdb;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_author = \'' . $current_user->ID . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );
		}
		if ($postID) $post = get_post($postID); if (!$post) $postID = 0;


		//create a room
		if (!$postID) $postID = self::webcamPost(); //default cam
		if ($postID) $post = get_post($postID); if (!$post) $postID = 0;
		if (!$postID) return 'Error: Could not setup a webcam post!';

		return do_shortcode( "[videowhisper_videochat webcam_id=\"$postID\"]");

	}

	function videowhisper_cam_random($atts)
	{
		//Shortcode: Random Cam Videochat
		$options = self::getOptions();

		$userID = get_current_user_id(); // 0 if no user logged in
		$nextRoomID = self::nextRoomID($userID, $options);

		if ($nextRoomID)
		{
			return do_shortcode('[videowhisper_videochat webcam_id="' . $nextRoomID . '" title="' .'Random Performer Room'. '"]');
		}
		else return __('No random cam room found with current criteria!', 'ppv-live-webcams');;
	}


	function videowhisper_cam_app($atts)
	{
		//Shortcode: HTML5 Videochat

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
				'webcam_id' => $postID,
				'silent' => 0,
				'private' => 0, 
				'call' => '',
				'title' => '',
			), $atts, 'videowhisper_cam_app');

		if ($atts['room']) $room = $atts['room']; //parameter channel="name"
		if ($atts['webcam_id']) $postID = $atts['webcam_id'];

		$width=$atts['width']; if (!$width) $width = "100%";
		$height=$atts['height']; if (!$height)  $height = '360px';

		$room = sanitize_file_name($room);

			global $wpdb;

		//only room provided
		if (!$postID && $room)
		{
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
		}

		//only wecam_id provided
		if (!$room)
		{
			$post = get_post($postID);
			if (!$post)  return "VideoWhisper HTML5 App Error: Room not found! (#$postID)";
			$room = $post->post_title;
		}

		$roomID = $postID;
		$roomName = $room;

		$userID = get_current_user_id(); // 0 if no user logged in

		$isPerformer = 0;


		if ($userID)
		{
			$isPerformer = self::isPerformer($userID, $roomID);

			$user = get_userdata($userID);
			if ($isPerformer) $userName = self::performerName($user, $options);
			else $userName = self::clientName($user, $options);

			if ($isPerformer)
			{

				//performer publishing with HTML5 app - save info for responsive playback
				update_post_meta($postID, 'performer', $userName);
				update_post_meta($postID, 'performerUserID', $userID );

				update_post_meta($postID, 'stream-protocol', 'rtsp');
				update_post_meta($postID, 'stream-type', 'webrtc');
				update_post_meta($postID, 'roomInterface', 'html5app');
			}

		}
		else
		{

			//use a cookie for visitor username persistence
			if ($_COOKIE['htmlchat_username']) $userName = $_COOKIE['htmlchat_username'];
			else
			{
				$userName =  'G_' . base_convert(time()%36 * rand(0,36*36),10,36);
				//setcookie('htmlchat_username', $userName);
			}
			$isVisitor = 1 ;
		}

		//private call
		$isCall = 0;
		if ($_GET['call'])
		{
			$call = sanitize_text_field($_GET['call']);
			if ($atts['call']) $call = $atts['call'];
	
			if (!$userID) return '<div class="ui segment red">' . __('Login is required to access private calls.','ppv-live-webcams') . '<BR><a class="ui button primary qbutton" href="' . wp_login_url() . '">' . __('Login', 'ppv-live-webcams') . '</a>  <a class="ui button secondary qbutton" href="' . wp_registration_url() . '">' . __('Register', 'ppv-live-webcams') . '</a>'. '</div>';
			$table_private = $wpdb->prefix . "vw_vmls_private";
			
			$privateID = self::to10($call);
			
			$sql = "SELECT * FROM `$table_private` WHERE id = $privateID";
			$private = $wpdb->get_row($sql);
			
			if ($private)
			{
				if ($private->status > 0 ) return  '<div class="ui segment red">' . __('This private call was closed.', 'ppv-live-webcams') . '</div>';		
				if ($private->pid != $userID && $private->cid != $userID) return  '<div class="ui segment red">' . __('Private call is only available to performer and client, as setup.', 'ppv-live-webcams') . '</div>';
				
				$isCall = 1;
				if ($private->pid == $userID) $pJS = ', requestUID: ' . $private->cid . ", requestUsername: '" . $private->client . "'";
				else $pJS = ', requestUID: ' . $private->pid . ", requestUsername: '" . $private->performer . "'";
			}
			else return  '<div class="ui segment red">' . __('This private call does not exist.', 'ppv-live-webcams') . '</div>';
			
			
		}


		//create a session
		$session = self::sessionUpdate($userName, $roomName, $isPerformer, 11, 1, 1, 1, $options, $userID, $roomID, self::get_ip_address());
		$sessionID = $session->id;

		if (!$sessionID) $sessionID = 0;

		//var_dump($session);
		//echo("($userName, $roomName, $isPerformer, 11, 1, 1, 1, $options, $userID, $roomID)");

		$wlJS ='';
		if ($options['whitelabel']) $wlJS = ', checkWait: true, whitelabel: ' . $options['whitelabel'];
		
		//private show / call request
		if (!$isCall)
		{
		$pJS ='';
		if ($atts['private']) $pJS = ', requestUID: ' . get_post_meta( $postID, 'performerUserID', true ) . ", requestUsername: '" . get_post_meta( $postID, 'performer', true ) . "'";
		}
		
		$ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_app';
		$dataCode .= "window.VideoWhisper = {userID: $userID, sessionID: $sessionID, sessionKey: '$sessionKey', roomID: $roomID, performer: $isPerformer, serverURL: '$ajaxurl' $wlJS $pJS}";

		///
		wp_enqueue_style( 'semantic-app', '//cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css');

		$CSSfiles = scandir(dirname(dirname(  __FILE__ )) . '/static/css/');
		foreach($CSSfiles as $filename)
			if (strpos($filename,'.css')&&!strpos($filename,'.css.map'))
				wp_enqueue_style( 'vw-cams-app' . ++$k, dirname(plugin_dir_url(  __FILE__ )) . '/static/css/' . $filename);

			$JSfiles = scandir(dirname(dirname(  __FILE__ )) . '/static/js/');
		foreach ($JSfiles as $filename)
			if ( strpos($filename,'.js') && !strpos($filename,'.js.map')) // && !strstr($filename,'runtime~')
				wp_enqueue_script('vw-cams-app'. ++$k , dirname(plugin_dir_url(  __FILE__ )) . '/static/js/' . $filename, array(), '', true);

			/*
				$title = $room;
				if ($atts['title']) $title = $atts['title'];
				$htmlCode .='<div id="videowhisperHeader" class="ui ' . $options['interfaceClass'] .' segment header attached">' . $title . '</div>';
			*/

			$htmlCode .= <<<HTMLCODE
<!--VideoWhisper.com - HTML5 Videochat web app - uid:$userID p:$isPerformer r:$room postID:$postID s:$sessionID-->
<noscript>You need to enable JavaScript to run this app.</noscript>
<div style="display:block;min-height:725px;height:inherit;background-color:#eee;position:relative;z-index:102!important;"><div style="display:block;width:100%; height:100%; position:absolute;z-index:102!important;" id="videowhisperVideochat"></div></div>
<script>$dataCode;
</script>
HTMLCODE;

//location.hash = "#vws-room";

		return $htmlCode;
	}


	static function nextRoomID($userID, $options = null)
	{
		//random videochat, returns next room or 0 if not found with configured criteria

		if (!$options) $options = self::getOptions();

		global $wpdb;
		$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

		$args=array(
			'post_type' => $options['custom_post'],
			'post_status' => 'publish',
			'posts_per_page' => 32,
			'offset'           => 0,
			'order'            => 'DESC',
			'author__not_in' => array($userID),
		);

		$args['orderby'] = 'meta_value_num';
		$args['meta_key'] = 'edate';

		//not if private shows
		$args['meta_query']['public'] = array('key' => 'privateShow', 'value' => '0');

		//hide private rooms
		$args['meta_query']['room_private'] = array(
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

		//free rooms (always for visitors)
		if (!$options['videochatNextPaid'] || !$userID) $args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0');
		if ($options['videochatNextPaid'] == '2' && $userID) $args['meta_query']['groupCPM'] = array('key' => 'groupCPM', 'value' => '0', 'compare' => '>');

		if ($options['videochatNextOnline']) $args['meta_query']['online'] = array('key' => 'edate', 'value' => time()-40, 'compare' => '>');


		$nextRoomID = 0; //no room found
		$postslist = get_posts($args);
		if (count($postslist)>0)
		{

			$roomAccessed = array();

			foreach ( $postslist as $item )
			{
				$roomAccessed[$item->ID] = 0;
			}

			$rIDs = implode(', ',array_keys($roomAccessed));

			$sql = "SELECT DISTINCT(rid), edate FROM $table_sessions WHERE uid = $userID AND rid IN ($rIDs) ORDER BY edate DESC";
			//$response['nextSQL'] = $sql;

			$accesses = $wpdb->get_results($sql);
			if ($wpdb->num_rows>0) foreach ($accesses as $access) $roomAccessed[$access->rid] = $access->edate;

				asort($roomAccessed);
			//$response['nextRoomAccessed'] = $roomAccessed;

			//access next room as client
			//skip room if performer there
			foreach ($roomAccessed as $iRoomID => $accessTime)
			{
				$isPerformer = self::isPerformer($userID, $iRoomID);

				if (!$isPerformer) $nextRoomID = intval($iRoomID);
				if (!$isPerformer) break;
			}

		}
		return $nextRoomID;
	}


	//! App
	//app

	static function appText()
	{
		//implement translations

		//returns texts
		return array(
			'Send' => __('Send', 'ppv-live-webcams'),
			'Type your message' => __('Type your message', 'ppv-live-webcams'),

			'Chat' => __('Chat', 'ppv-live-webcams'),
			'Camera' => __('Camera', 'ppv-live-webcams'),
			'Users' => __('Users', 'ppv-live-webcams'),
			'Options' => __('Options', 'ppv-live-webcams'),
			'Files' => __('Files', 'ppv-live-webcams'),
			'Presentation' => __('Presentation', 'ppv-live-webcams'),

			'Tap for Sound' => __('Tap for Sound', 'ppv-live-webcams'),
			'Enable Audio' => __('Enable Audio', 'ppv-live-webcams'),
			'Mute' => __('Mute', 'ppv-live-webcams'),
			'Reload' => __('Reload', 'ppv-live-webcams'),

			'Broadcast' => __('Broadcast', 'ppv-live-webcams'),
			'Stop Broadcast' => __('Stop Broadcast', 'ppv-live-webcams'),
			'Make a selection to start!' => __('Make a selection to start!', 'ppv-live-webcams'),

			'Lights On' => __('Lights On', 'ppv-live-webcams'),
			'Dark Mode' => __('Dark Mode', 'ppv-live-webcams'),
			'Enter Fullscreen' => __('Enter Fullscreen', 'ppv-live-webcams'),
			'Exit Fullscreen' => __('Exit Fullscreen', 'ppv-live-webcams'),

			'Site Menu' => __('Site Menu', 'ppv-live-webcams'),

			'Request Private' => __('Request Private', 'ppv-live-webcams'),
			'Request Private 2 Way Videochat Show' => __('Request Private 2 Way Videochat Show', 'ppv-live-webcams'),
			'Performer Disabled Private Requests' => __('Performer Disabled Private Requests', 'ppv-live-webcams'),
			'Performer is Busy in Private' => __('Performer is Busy in Private', 'ppv-live-webcams'),
			'Performer is Not Online' => __('Performer is Not Online', 'ppv-live-webcams'),
			'Nevermind' => __('Nevermind', 'ppv-live-webcams'),
			'Accept' => __('Accept', 'ppv-live-webcams'),
			'Decline' => __('Decline', 'ppv-live-webcams'),
			'Close Private' => __('Close Private', 'ppv-live-webcams'),

			'Next' => __('Next', 'ppv-live-webcams'),
			'Next: Random Videochat Room' => __('Next: Random Videochat Room', 'ppv-live-webcams'),

			'Name' => __('Name', 'ppv-live-webcams'),
			'Size' => __('Size', 'ppv-live-webcams'),
			'Age' => __('Age', 'ppv-live-webcams'),
			'Upload: Drag and drop files here, or click to select files' =>  __('Upload: Drag and drop files here, or click to select files', 'ppv-live-webcams'),
			'Uploading. Please wait...' =>  __('Uploading. Please wait...', 'ppv-live-webcams'),
			'Open' => __('Open', 'ppv-live-webcams'),
			'Delete' => __('Delete', 'ppv-live-webcams'),

			'Media Displayed' => __('Media Displayed', 'ppv-live-webcams'),
			'Remove' => __('Remove', 'ppv-live-webcams'),
			'Default' => __('Default', 'ppv-live-webcams'),
			'Empty' => __('Empty', 'ppv-live-webcams'),

			'Profile' => __('Profile', 'ppv-live-webcams'),
			'Show' => __('Show', 'ppv-live-webcams'),
		
			'Private Call' => __('Private Call', 'ppv-live-webcams'),
			'Exit' => __('Exit', 'ppv-live-webcams'),
	
			
		);
	}


	static function appTipOptions($options = null)
	{

		$tipOptions = stripslashes($options['tipOptions']);
		if ($tipOptions)
		{
			$p = xml_parser_create();
			xml_parse_into_struct($p, trim($tipOptions), $vals, $index);
			$error = xml_get_error_code($p);
			xml_parser_free($p);

			if (is_array($vals)) return $vals;
		}

		return array();

	}


	static function time2age($time)
	{
		$ret = '';

		$seconds = time() - $time;

		$days = intval(intval($seconds) / (3600*24));
		if($days) $ret .= $days . 'd ';
		if ($days>0) return $ret;

		$hours = (intval($seconds) / 3600) % 24;
		if($days||$hours)  $ret .= $hours . 'h ';

		if ($hours>0) return $ret;

		$minutes = (intval($seconds) / 60) % 60;
		if ($minutes > 3) $ret .= $minutes . 'm';
		else $ret .= __('New', 'ppv-live-webcams');


		return $ret;
	}


	static function appRoomFiles($room, $options)
	{

		$files = [];
		if (!$room) return $files;

		$dir=$options['uploadsPath'];
		if (!file_exists($dir)) mkdir($dir);
		$dir.="/$room";
		if (!file_exists($dir)) mkdir($dir);

		$handle=opendir($dir);
		while
		(($file = readdir($handle))!==false)
		{
			if (($file != ".") && ($file != "..") && (!is_dir("$dir/".$file)))
				$files[] = array(
					'name' =>$file,
					'size' => intval(filesize("$dir/$file")),
					'age' => self::time2age($ftime = filemtime("$dir/$file")),
					'time' => intval($ftime),
					'url' => self::path2url("$dir/$file"),
				);
		}
		closedir($handle);

		return $files;
	}


	static function is_true($val, $return_null=false){
		$boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
		return ( $boolval===null && !$return_null ? false : $boolval );
	}


	static function appRoomOptions($post, $session, $options)
	{
		$configuration = [];

		if (!$options['appOptions']) return $configuration;
		
		if ($session->broadcaster) //room owner
			{

			$fields = [
			'requests_disable' => [ 'name'=>'requests_disable', 'description' => __('Disable Call Requests', 'ppv-live-webcams'), 'details'=>__('Disable users from sending private call requests to room owner.', 'ppv-live-webcams'), 'type'=> 'toggle', 'value' => self::is_true(get_post_meta($post->ID, 'requests_disable', true )) ],
			'room_private' => [ 'name'=>'room_private', 'description' => __('Private Room', 'ppv-live-webcams'), 'details'=>__('Hide room from public listings. Can be accessed by room link.', 'ppv-live-webcams'), 'type'=> 'toggle', 'value' => self::is_true(get_post_meta($post->ID, 'room_private', true )) ],
			'room_audio' => [ 'name'=>'room_audio', 'description' => __('Audio Only', 'ppv-live-webcams'), 'details'=>__('Audio only mode. Only microphone, no webcam video. Applies both to group and private calls.', 'ppv-live-webcams'), 'type'=> 'toggle', 'value' => $audio = self::is_true(get_post_meta($post->ID, 'room_audio', true )) ],
			'room_conference' => [ 'name'=>'room_conference', 'description' => __('Conference Mode', 'ppv-live-webcams'), 'details'=>__('Enable owner to show multiple users streams at same time in split view. All users can publish webcam.', 'ppv-live-webcams'), 'type'=> 'toggle', 'value' => $conference = self::is_true(get_post_meta($post->ID, 'room_conference', true )) ],		
			];


			$collaboration = self::collaborationMode($post->ID, $options);
			$room_slots = get_post_meta($post->ID, 'room_slots', true );
			if (!$room_slots) $room_slots = 1;
			if ($collaboration || $conference) $fields['room_slots'] = [ 'name'=>'room_slots', 'description' => __('Display Slots', 'ppv-live-webcams'), 'details'=>__('Split display to show multiple media items, in conference mode.', 'ppv-live-webcams'), 'type'=> 'dropdown', 'value' => $room_slots, 'options' => [
				['value'=>'1', 'text'=>'1'],
				['value'=>'2', 'text'=>'2'],
				['value'=>'4', 'text'=>'4'],
				['value'=>'6', 'text'=>'6'],
				] ];

			if (!$options['presentation']) //enabled by default
				{

				$current_user = get_userdata($session->uid);
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
				}

				if (self::inList($userkeys, $options['presentationMode']))
				{
					$fields['vw_presentationMode'] = [ 'name'=>'vw_presentationMode', 'description' => __('Collaboration Mode', 'ppv-live-webcams'), 'details'=>__('Enable collaboration mode with multiple media, files, presentation.', 'ppv-live-webcams'), 'type'=> 'toggle', 'value' => self::is_true(get_post_meta($post->ID, 'vw_presentationMode', true )) ];
				}

			}

			$configuration['room'] =[ 'name' =>  __('Room Options', 'ppv-live-webcams') . ': '. $post->post_title, 'fields' => $fields];
		}

		$configuration['meta'] = [ 'time' => time()];

		return $configuration;

	}


	static function notificationMessage($message, $session, $privateUID = 0, $meta = null)
	{
		//adds a notification from server, only visible to user

		$ztime = time();

		global $wpdb;
		$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";

		if (!$meta) $meta = array();
		$metaS = serialize($meta);

		$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `room_id`, `message`, `mdate`, `type`, `user_id`, `meta`, `private_uid`) VALUES ('" .$session->username. "', '" .$session->room. "', '" .$session->rid. "', '$message', $ztime, '3', '" .$session->uid. "', '$metaS', '$privateUID')";
		$wpdb->query($sql);

		//todo maybe: also update chat log file

		return $sql;
	}


	static function autoMessage($message, $session, $privateUID = 0, $meta = null)
	{
		//adds automated user message from server, automatically generated by user action

		$ztime = time();

		global $wpdb;
		$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";

		if (!$meta) $meta = array();
		$meta['automated'] = true;
		$metaS = serialize($meta);
		
		$message = esc_sql($message);

		$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `room_id`, `message`, `mdate`, `type`, `user_id`, `meta`, `private_uid`) VALUES ('" .$session->username. "', '" .$session->room. "', '" .$session->rid. "', '$message', $ztime, '2', '" .$session->uid . "', '$metaS', '$privateUID')";
		$wpdb->query($sql);
		
		return $sql;
	}


	static function appPrivateRoom($post, $session, $privateUID, $actionID, $options)
	{
		//private call room parameters, specific for this user

		$room = array();

		$room['ID'] = $post->ID;

		if ($session->broadcaster) $privateExt = $session->uid  . '-' . $privateUID;
		else $privateExt = $privateUID  . '-' . $session->uid; //the other is performer

		$room['audioOnly'] = self::is_true(get_post_meta($post->ID, 'room_audio', true ));

		$room['name'] = $post->post_title . '_pr_' .  $privateExt;

		$room['screen'] = 'Way2Screen'; //private 2 way screen

		$room['privateUID'] = $privateUID;

		//special private streams based on user id
		$streamBroadcast = 'ps_' . $session->uid . '-' . $privateUID;
		$streamPlayback = 'ps_' . $privateUID . '-' . $session->uid;

		//in 2w always receive broadcast keys
		$room['streamBroadcast'] = self::webrtcStreamQuery($session->uid, $post->ID, 1, $streamBroadcast, $options, 0, $post->post_title, $privateUID);
		$room['streamPlayback'] = self::webrtcStreamQuery($session->uid, $post->ID, 0, $streamPlayback, $options, 0, $post->post_title, $privateUID);
		$room['streamUID'] = intval($privateUID);

		$room['actionPrivate'] = false;
		$room['actionPrivateClose'] = true;

		$room['actionID'] = $actionID;

		$room['welcome'] = sprintf('Welcome to private call, %s!', $session->username);
		$room['welcomeImage'] = dirname(plugin_dir_url(__FILE__)) . '/images/users2.png';
		
		//$other = get_userdata($privateUID);

		//configure tipping options for clients
		$room['tips'] = false;
		if ($options['tips'])
			if (!$session->broadcaster)
			{

				$tipOptions = self::appTipOptions($options);
				if (count($tipOptions))
				{
					$room['tipOptions'] = $tipOptions;
					$room['tips'] = true;
					$room['tipsURL'] = dirname(plugin_dir_url(__FILE__)). '/videowhisper/templates/messenger/tips/';
				}
			}

		//offline snapshot (poster)
		$room['snapshot'] = self::webcamThumbSrc($post->ID, $post->post_title, $options, self::format_age(time() -  $session->redate));

		//offline teaser video
		if ($options['teaserOffline'])
		{
			$video_teaser = get_post_meta($post->ID, 'video_teaser', true);
			if ($video_teaser) $room['videoOffline'] = self::vsvVideoURL($video_teaser, $options);
		}

		return $room;

	}


	static function collaborationMode($postID, $options)
	{
		$presentationMode = get_post_meta($postID, 'vw_presentationMode', true);

		if ($presentationMode == '' ||  empty($presentationMode)) return self::is_true($options['presentation']);
		else return self::is_true($presentationMode);
	}


	static function appStreamBroadcast($userID, $post, $options)
	{
		//broadcasting stream

		$user = get_userdata($userID);
		//$broadcaster = self::isPerformer($userID, $post->ID)
		$streamName = $user->user_login;

		return self::webrtcStreamQuery($userID, $post->ID, 1, $streamName, $options, 0, $post->post_title);
	}


	static function appStreamPlayback($userID, $performerID, $post, $options)
	{
		$user = get_userdata($performerID);
		//$broadcaster = self::isPerformer($userID, $post->ID);
		$streamName = $user->user_login;

		return self::webrtcStreamQuery($userID, $post->ID, 0, $streamName, $options, 0, $post->post_title);
	}


	static function appPublicRoom($post, $session, $options, $welcome ='', &$room = null, $requestUID = 0)
	{
		//public room parameters, specific for this user

		if (!$room) $room = array();

		$room['ID'] = $post->ID;
		$room['name'] = $post->post_title;

		$room['performer'] = get_post_meta($post->ID, 'performer', true);
		$room['performerID'] = intval(get_post_meta($post->ID, 'performerUserID', true ));
		if (!$room['performerID']) $room['performerID'] = intval($post->post_author);

		$collaboration = self::collaborationMode($post->ID, $options);
		if (VW_H5V_DEVMODE && VW_H5V_DEVMODE_COLLABORATION) $collaboration = true;

		$conference = self::is_true(get_post_meta($post->ID, 'room_conference', true ));
		
		$room['audioOnly'] = self::is_true(get_post_meta($post->ID, 'room_audio', true ));

		
		$appComplexity = ($options['appComplexity'] == '1' || ($session->broadcaster && $options['appComplexity'] == '2'));
		

		//screen
		if ($session->broadcaster) $roomScreen = 'BroadcastScreen';
		else $roomScreen = 'PlaybackScreen';
		if ($conference || $collaboration || $appComplexity) $roomScreen = 'CollaborationScreen';
		$room['screen'] = $roomScreen;

		$streamName = $room['performer'];

		//only performer receives broadcast keys in public room
		if ($session->broadcaster) $room['streamBroadcast'] = self::appStreamBroadcast($session->uid, $post, $options);
		else $room['streamBroadcast'] = '';

		$room['streamUID'] = intval($room['performerID']);
		$room['streamPlayback'] = self::appStreamPlayback($session->uid, $room['streamUID'] , $post, $options);

		$room['actionPrivate'] = !$session->broadcaster && !$requestUID;
		$room['actionPrivateClose'] = false;
		$room['privateUID'] = 0;
		$room['actionPrivateDisable'] = self::is_true(get_post_meta($post->ID, 'requests_disable', true ));
		
		if (!$session->broadcaster) //other clients: check if performer is in private
		{
			self::updatePrivateShow($post->ID, $room['performerID']); //update performer private status
			$room['actionPrivateBusy'] = self::is_true(get_post_meta($post->ID, 'privateShow', true )); 
		}

		$room['actionID'] = 0;

		$room['welcome'] = sprintf('Welcome to public room "%s", %s!', $post->post_title, $session->username);
		$room['welcomeImage'] = dirname(plugin_dir_url(__FILE__)) . '/images/chat.png';

		$private = self::is_true(get_post_meta($post->ID, 'room_private', true ));
		if ($private) $room['welcome'] .= "\n" .  __('This is a private room (not listed).', 'ppv-live-webcams');

		// $room['welcome'] .= "\n ID" . $room['ID'] . "name" .  $room['name'];


		$clientCPM = self::clientCPM($post->post_title, $options, $post->ID);
		$performerRatio = self::performerRatio($post->post_title, $options, $post->ID);

		$groupCPM = get_post_meta($post->ID, 'groupCPM', true);

		if ($groupCPM) //show group mode details
			{
			$groupMode = get_post_meta($post->ID, 'groupMode', true);

			$room['welcome'] .= "\n" .  __('This is a paid room:', 'ppv-live-webcams');

			$room['welcome'] .= "\n - " .  __('Group Mode', 'ppv-live-webcams') . ': ' .$groupMode;
			$room['welcome'] .= "\n - " .  __('Group session cost', 'ppv-live-webcams') . ': ' . ' ' . $groupCPM . ' ' . htmlspecialchars($options['currencypm']);
			if ($options['ppvGraceTime']) $room['welcome'] .= "\n - " . __('Charging starts after a grace time:', 'ppv-live-webcams') . ' ' . $options['ppvGraceTime'] . 's, if performer is online.';

			$room['welcomeImage'] = dirname(plugin_dir_url(__FILE__)) . '/images/cash.png';
		}

		//voyeur mode info
		if (!is_array($roomMeta = unserialize($session->roptions))) $roomMeta = array();	
		if (array_key_exists('userMode', $roomMeta)) if ($roomMeta['userMode'] == 'voyeur') 
		{
		 $room['welcome'] .= "\n" .  __('You are in Voyeur mode: hidden from user list. Participants will not be aware of your presence unless you write in text chat.', 'ppv-live-webcams');
		 if ($groupParameters['cpmv']) $room['welcome'] .= "\n - " .  __('Voyeur cost', 'ppv-live-webcams') . ': ' . $groupParameters['cpmv'] . $options['currencypm']; 
		}				

		if (!$session->uid) //visitor can't request private
			{
			$room['actionPrivate'] = 0;
			$room['welcome'] .= "\n". 'Your are not logged in: Please register and login to access more advanced features!';
		}
		elseif ($session->broadcaster) //member: performer
			{
			$room['welcome'] .= "\n".__('You are performer. Registered users can request private chats and you can accept:', 'ppv-live-webcams');

			if ($clientCPM) $room['welcome'] .= "\n - " . __('Private show cost per minute for client:', 'ppv-live-webcams') . ' ' . $clientCPM . ' ' . htmlspecialchars($options['currencypm']);
			if ($options['ppvGraceTime']) $room['welcome'] .= "\n - " . __('Charging starts after a grace time:', 'ppv-live-webcams') . ' ' . $options['ppvGraceTime'] . 's';
			if ($clientCPM && $performerRatio) $room['welcome'] .= "\n" . __('Private show earning per minute for performer:', 'ppv-live-webcams') . ' ' . number_format($clientCPM*$performerRatio, 2, '.','') . ' ' . htmlspecialchars($options['currencypm']);
		}
		else //member: client
			{
			if ($post->post_author == $session->uid) //room owner warning
				{
				$selectWebcam = get_user_meta($session->uid, 'currentWebcam', true);
				if ($selectWebcam) $room['welcome'] .= "\n Warning: This is your room but you are not accessing as performer. Your currently selected webcam: " . get_the_title($selectWebcam) ;
			}

			//can client request private show?
			$balancePending = self::balance($session->uid, true);
			if ($clientCPM) $ppvMinimum =  self::balanceLimit($clientCPM, 5,  $options['ppvMinimum'], $options);
			if ($ppvMinimum && $clientCPM)
				if ( $balancePending < $ppvMinimum)
				{
					$room['actionPrivate'] = 0;
					$room['welcome'] .= "\n" . __('You do not have enough credits to request a private show.', 'ppv-live-webcams') . " ($ppvMinimum)";
				}
			else
			{

				$room['welcome'] .= "\n". __('You can request private session from room performer when available:', 'ppv-live-webcams');

				if ($clientCPM) $room['welcome'] .= "\n - " . __('Private show cost per minute:', 'ppv-live-webcams') . ' ' . $clientCPM . ' ' . htmlspecialchars($options['currencypm']);
				if ($options['ppvGraceTime']) $room['welcome'] .= "\n - " . __('Charging starts after a grace time:', 'ppv-live-webcams') . ' ' . $options['ppvGraceTime'] . 's, if performer is online.';
			}

		}

		if ($options['videochatNext']) if (!$session->broadcaster && !$requestUID) $room['next'] = true;

			if ($welcome) $room['welcome'] .= "\n" . $welcome;

			//configure tipping options for clients
			$room['tips'] = false;
		if ($options['tips'])
			if (!$session->broadcaster)
			{
				$tipOptions = self::appTipOptions($options);
				if (count($tipOptions))
				{
					$room['tipOptions'] = $tipOptions;
					$room['tips'] = true;
					$room['tipsURL'] = dirname(plugin_dir_url(__FILE__)) . '/videowhisper/templates/messenger/tips/';
				}
			}

		//offline snapshot (poster)
		$room['snapshot'] = self::webcamThumbSrc($post->ID, $post->post_title, $options, self::format_age(time() -  $session->redate));

		//offline teaser video
		if ($options['teaserOffline'])
		{
			$video_teaser = get_post_meta($post->ID, 'video_teaser', true);
			if ($video_teaser) $room['videoOffline'] = self::vsvVideoURL($video_teaser, $options);
		}

		//panel reset
		$room['panelCamera'] = false;
		$room['panelUsers'] = false;
		$room['panelOptions'] = false;
		$room['panelFiles'] = false;
		$room['panelPresentation'] = false;

		//collaboration
		if ($collaboration)
		{
			$room['welcome'] .=  "\n " . __('Room is in collaboration mode, with a Files panel.', 'ppv-live-webcams');
			$room['files'] = self::appRoomFiles($room['name'], $options);

			$room['panelFiles'] = true;

			$room['filesUpload'] = VW_H5V_DEVMODE || is_user_logged_in();
			$room['filesDelete'] = boolval($session->broadcaster);

			$room['filesPresentation'] = boolval($session->broadcaster);
			$room['panelPresentation'] = boolval($session->broadcaster);
		}

		if ($conference || $collaboration)
		{
			//all users can broadcast
			if (VW_H5V_DEVMODE || is_user_logged_in())
			{
				$room['panelCamera'] = true;
				$room['streamBroadcast'] = self::appStreamBroadcast($session->uid, $post, $options);
			}

			//room media (split view)
			$room['media']  = self::appRoomMedia($post, $session, $options);

			//assign user to media slots
			$room['usersPresentation'] = boolval($session->broadcaster);
		} 
		elseif ($appComplexity) 		//advanced interface
		{
			if ($session->broadcaster) //camera for broadcaser
				{
				$room['panelCamera'] = true;
				$room['streamBroadcast'] = self::appStreamBroadcast($session->uid, $post, $options);
			}
		}

		//advanced interface: always for conference, collaboration
		if ($conference || $collaboration || $appComplexity)
		{
			//users list
			$room['panelUsers'] = true;

			//options
			if ($options['appOptions']) 
			{
			$room['panelOptions'] = boolval($session->broadcaster);
			$room['options'] = self::appRoomOptions($post, $session, $options);
			}
		}
		
		//also needed to check when user comes online in calls
		if ($requestUID || $conference || $collaboration || $appComplexity) $room['users'] = self::appRoomUsers($post, $options);


		return $room;
	}


	static function appRoomMedia($post, $session, $options)
	{
		$media = get_post_meta( $post->ID, 'presentationMedia', true);

		//always Main to show default room stream
		if (!is_array($media)) $media = ['Main' => [ 'name' => 'Main'] ];
		if (!count($media)) $media['Main'] = ['name' => 'Main'];

		//room slots
		$collaboration = self::collaborationMode($post->ID, $options);
		$conference = self::is_true(get_post_meta($post->ID, 'room_conference', true ));


		if ($collaboration || $conference) $room_slots = intval(get_post_meta($post->ID, 'room_slots', true ));
		if (!$room_slots) $room_slots = 1;

		$items = 0;
		foreach ($media as $placement => $content) if (++$items > $room_slots) unset($media[$placement]); //remove if too many

			while (count($media) < $room_slots) $media['Slot' . ++$items] = ['name' => 'Slot' . $items, 'type' => 'empty']; //add if missing

			return $media;
	}


	static function appFail($message = 'Request Failed', $response = null)
	{
		if (!$response) $response = array();

		//if (!$response[$obj]) $response[$obj] = array();

		//returns an error message
		$response['error'] = $message;

		$response['VideoWhisper'] = 'https://videowhisper.com';

		echo json_encode($response);

		die();
	}


	static function appRoomUsers($post, $options)
	{
		global $wpdb;
		$table_sessions = $wpdb->prefix . "vw_vmls_sessions";

		$ztime = time();

		//update room user list

		$items = array();

		$sql = "SELECT * FROM `$table_sessions` WHERE rid='" . $post->ID . "' AND status = 0 ORDER BY broadcaster DESC, username ASC";
		$sqlRows = $wpdb->get_results($sql);

		$no = 0;
		if ($wpdb->num_rows>0)
			foreach ($sqlRows as $sqlRow)
			{
				if (!is_array($userMeta = unserialize($sqlRow->meta))) $userMeta = array();
				if (!is_array($roomMeta = unserialize($sqlRow->roptions))) $roomMeta = array();
	

				$item = [];
				$item['userID'] = intval($sqlRow->uid);
				$item['userName'] = $sqlRow->username;
				if (!$item['userName']) $item['userName'] = '#' . $sqlRow->uid;

				$item['sdate'] = intval($sqlRow->sdate);
				$item['meta'] = $userMeta;
				$item['updated'] = intval($sqlRow->edate);
				$item['avatar'] = get_avatar_url($sqlRow->uid, array('default'=> dirname(plugin_dir_url(__FILE__)).'/images/avatar.png'));
				$item['url'] = get_author_posts_url($sqlRow->uid);

				if (array_key_exists('privateUpdate', $userMeta)) if ($ztime - intval($userMeta['privateUpdate']) < $options['onlineTimeout']) $item['hide'] = true; //in private
				//if ($ztime - intval($sqlRow->edate) < $options['onlineTimeout']) $item['hide'] = true; //offline
				
				if (array_key_exists('userMode', $roomMeta)) if ($roomMeta['userMode'] == 'voyeur') $item['hide'] = true; //voyeur
				
				$item['order'] = ++$no;

				$items[intval($sqlRow->uid)] = $item;
			}

		return $items;
	}


	//!App Ajax handlers
	function vmls_app()
	{


		$options = get_option('VWliveWebcamsOptions');
		//output clean
		ob_clean();

		//D: login, public room (1 w broadcaster/viewer), 2w private vc, status
		//TD: tips


		global $wpdb;
		$table_sessions = $wpdb->prefix . "vw_vmls_sessions";
		$table_chatlog = $wpdb->prefix . "vw_vmls_chatlog";
		$table_actions = $wpdb->prefix . "vw_vmls_actions";

		//all strings - comment echo in prod:
		if (VW_H5V_DEVMODE) $response['post'] = serialize( $_POST );
		if (VW_H5V_DEVMODE) $response['get'] = serialize( $_GET );

		$http_origin = get_http_origin();
		$response['http_origin'] = $http_origin;
		$response['VideoWhisper'] = 'https://videowhisper.com';


		$task = sanitize_file_name( $_POST['task']);
		$devMode = self::is_true($_POST['devMode']); //app in devMode

		$requestUID = intval($_POST['requestUID']); //directly requested private call

		//originally passed trough window after creating session
		//urlvar user_id > php var $userID

		//session info received trough VideoWhisper POST var
		if ($VideoWhisper = $_POST['VideoWhisper'])
		{
			$userID = intval($VideoWhisper['userID']);
			$sessionID = intval($VideoWhisper['sessionID']);
			$roomID = intval($VideoWhisper['roomID']);
			$sessionKey = intval($VideoWhisper['sessionKey']);

			$privateUID = intval($VideoWhisper['privateUID']); //in private call
			$roomActionID = intval($VideoWhisper['roomActionID']);	
		}


		if (VW_H5V_DEVMODE)
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
		}


		//devMode: assign a default user/room if not provided, only when app is in devMode
		if (VW_H5V_DEVMODE) if ($devMode)
		{
			//setup specific
			if (!$userID) $userID = 2136;
			if (!$roomID) $roomID = 5540;

			if (VW_H5V_DEVMODE_CLIENT) if ($userID == 2136) $roomID = 213; //different room => client, only in devMode
		}


		//room is post
		$postID = $roomID;
		$public_room = array();

		$post = get_post($roomID);
		if (!$post) self::appFail('Room not found: ' . $roomID);
		$roomName = $post->post_title;


		// Handling the supported tasks:

		$response['task'] = $task;

		//handle auth / session

		if ($task != 'login')
		{
			//check session
			if (! $session = self::sessionValid($sessionID, $userID)) self::appFail('Session not valid or closed. Please reload! Do not leave chat in background. Session #' . $sessionID . ' User #' .  $userID . ' Room #' . $roomID .' ' . $task );

			//update online for viewer
			if (!$session->broadcaster)
				if ($disconnect = self::updateOnlineViewer($session->username, $roomName, $postID , 11 , '', $options, $userID ))
					self::appFail('Viewer disconnected: ' . $disconnect);

				if ($session->broadcaster) self::updateOnlineBroadcaster($sessionID);

				//set session username
				$userName = $session->username;

			if (VW_H5V_DEVMODE)
			{
				//retrieve room info
				$groupCPM = get_post_meta($postID, 'groupCPM', true);
				$performer = get_post_meta($postID, 'performer', true);
				$sessionStart = get_post_meta($postID, 'sessionStart', true);
				$checkin = get_post_meta($postID, 'checkin', true);
				$privateShow = get_post_meta($postID, 'privateShow', true);


				$response['_dev']['clientGroupCost'] = self::clientGroupCost($session, $groupCPM, $sessionStart);
				$response['_dev']['groupCPM'] = $groupCPM;
				$response['_dev']['sessionStart'] = $sessionStart;
				$response['_dev']['session'] = $session;

			}

			$isPerformer = self::isPerformer($userID, $roomID);

		}


		if ($task == 'login')
		{
			//retrieve wp info
			$user = get_userdata($userID);
			if (!$user)
			{
				//
				$isVisitor =1;
				//self::appFail('User not found: ' . $userID);

				if ($_COOKIE['htmlchat_username']) $userName = $_COOKIE['htmlchat_username'];
				else
				{
					$userName =  'G_' . base_convert(time()%36 * rand(0,36*36),10,36);
					setcookie('htmlchat_username', $userName);
				}

				$isPerformer = 0;

			}else
			{
				$isPerformer = self::isPerformer($userID, $roomID);
				if ($isPerformer) $userName = self::performerName($user, $options);
				else $userName = self::clientName($user, $options);
			}



			//set/get room performer details
			if ($isPerformer)
			{
				update_post_meta($postID, 'performer', $userName);
				update_post_meta($postID, 'performerUserID', $userID);
			}

			//dev auto create session (at web login on production)
			if (VW_H5V_DEVMODE)
				if (!$sessionID)
				{
					$session = self::sessionUpdate($userName, $roomName, $isPerformer, 11, 1, 1, 1, $options, $userID, $roomID, self::get_ip_address());
					$sessionID = $session->id;

					$response['_dev']['isPerformer'] = $isPerformer;
					$response['_dev']['session'] = $session;
				}

			if (! $session = self::sessionValid($sessionID, $userID)) self::appFail('Login session failed: s#' . $sessionID . ' u#' .  $userID);

			//session valid, login


			//user session parameters and info, updates
			$response['user'] = [
			'ID'=> intval($userID),
			'name'=>$userName,
			'sessionID'=> intval($sessionID),
			'loggedIn'=> true,
			'balance' => number_format(self::balance($userID, true, $options),2),
			'avatar' => get_avatar_url($userID, array('default'=> dirname(plugin_dir_url(__FILE__)).'/images/avatar.png'))
			];


			//on login check if any private request was active to restore

			$sql = "SELECT * FROM `$table_actions` WHERE room_id='$roomID' AND action = 'privateRequest' AND status > 4 AND status < 7 AND (user_id='" . $session->uid . "' OR target_id='" . $session->uid . "') ORDER BY mdate DESC LIMIT 0, 1";
			$pAction = $wpdb->get_row($sql);
			
			//$response['sqlActions'] = $sql;
			$response['pAction'] = $pAction;
			
			if ($pAction)
			{
				$actionID = $pAction->id;
				$privateUID = 0;
				if ($pAction->user_id == $userID) $privateUID = $pAction->target_id;
				if ($pAction->target_id == $userID) $privateUID = $pAction->user_id;
				
				//disable other similar actions to prevent confusion and duplicate requests
				$sqlU = "UPDATE `$table_actions` SET status = '11' WHERE room_id='$roomID' AND action = 'privateRequest' AND status < 7 AND id <> " . $pAction->id;
				$wpdb->query($sqlU);
			}

			if ($privateUID) $response['room'] = self::appPrivateRoom($post, $session, $privateUID, $actionID, $options); //private room restore
			else $response['room'] = self::appPublicRoom($post, $session, $options, '', $public_room, $requestUID); //public room or lobby


			//config params, const
			$response['config'] = [
			'wss' => $options['wsURLWebRTC'],
			'application' => $options['applicationWebRTC'],

			'videoCodec' =>  $options['webrtcVideoCodec'],
			'videoBitrate' =>  $options['webrtcVideoBitrate'],
			'audioBitrate' =>  $options['webrtcAudioBitrate'],
			'audioCodec' =>  $options['webrtcAudioCodec'],
			'autoBroadcast' => false,
			'actionFullscreen' => true,
			'actionFullpage' => false,

			'serverURL' =>  $ajaxurl = admin_url() . 'admin-ajax.php?action=vmls_app',
			//'cameraSnapshot' => get_avatar_url($userID, ['size' => '256']),
			];

			//appMenu
			if ($options['appSiteMenu'])
			{
				$menus = wp_get_nav_menu_items($options['appSiteMenu']);
				//https://developer.wordpress.org/reference/functions/wp_get_nav_menu_items/

				$appMenu = array();
				if (is_array($menus)) if (count($menus))
					{
						foreach ( (array) $menus as $key => $menu_item )
						{
							$appMenuItem = array();
							$appMenuItem['title'] =  $menu_item->title;
							$appMenuItem['url'] =  $menu_item->url;
							$appMenuItem['ID'] =  intval($menu_item->ID);
							$appMenuItem['parentID'] =  intval($menu_item->menu_item_parent);
							$appMenu[] = $appMenuItem;
						}

						$appMenu[] = ['title'=>'END']; // menu end (last item ignored by app)

						$response['config']['siteMenu'] = $appMenu;
					}
			}

			//translations
			$response['config']['text'] = self::appText();


			$response['config']['exitURL'] = ( $url = get_permalink( $options['p_videowhisper_webcams'] ) ) ? $url : get_site_url() ;
			$response['config']['balanceURL'] =  ( $url = get_permalink( $options['balancePage'] ) ) ? $url : get_site_url() ;

			//pass app setup config parameters
			if (is_array($options['appSetup']))
				if (array_key_exists('Config', $options['appSetup']))
					if (is_array($options['appSetup']['Config']))
						foreach ($options['appSetup']['Config'] as $key => $value)
							$response['config'][$key] = $value;


						if (VW_H5V_DEVMODE)
						{
							$response['config']['cameraAutoBroadcast'] = '0';
							$response['config']['videoAutoPlay '] = '0';

						}

					if (!$isPerformer) $response['config']['cameraAutoBroadcast'] = '0';

		}


		// all, including login


		//update private session if in private mode
		if ($privateUID) $disconnectPrivate = self::privateSessionUpdate($session, $post, $isPerformer, $privateUID, $options);

		if ($disconnectPrivate)
		{
			$response['disconnectPrivate'] = $disconnectPrivate;
			$response['warning'] = $disconnectPrivate;
			//return user to public room
			$response['room'] = self::appPublicRoom($post, $session, $options, $disconnectPrivate, $public_room, $requestUID);
		}

		//room
		/*
			$room = sanitize_file_name($_POST['room']);
			if ($room) $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			if (!$postID) $response['error'] = 'HTML Chat: Room not found: ' . $room;
			*/

		$ztime = time();

		//td: remember in private, first message dup key bug (loaded twice when lastMsg 0)


		$needUpdate = array();

		//process app task (other than login)
		switch ($task)
		{

		case 'options':

			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);
			if (!$session->broadcaster)
			{
				$response['warning'] = __('Only room owner can edit room options.', 'ppv-live-webcams') ;
				break;
			}

			$name = sanitize_file_name($_POST['name']);
			$value = sanitize_file_name($_POST['value']);

			if (!in_array($name, ['requests_disable', 'room_private', 'room_slots', 'room_conference', 'room_audio', 'vw_presentationMode'])) self::appFail('Option Not Permitted!');

			update_post_meta($postID, $name, $value);

			update_post_meta($postID, 'updated_options', time());
			$needUpdate['options'] = 1;

			if (in_array($name, ['room_slots']))
			{
				update_post_meta($postID, 'updated_media' , time());
				$needUpdate['media'] = 1;
			}

			if (in_array($name, ['room_conference', 'room_audio', 'vw_presentationMode', 'requests_disable']))
			{
				update_post_meta($postID, 'updated_room' , time());
				$needUpdate['room'] = 1;
			}
			break;

		case 'update':
			//something changed - let everybody know (later implementation - selective updates, triggers)
			$update = sanitize_file_name($_POST['update']);
			update_post_meta($postID, 'updated_' . $update, time());
			$needUpdate[$update] = 1;

			break;

			//collaboration

		case 'user_presentation':
			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);

			//moderator
			if (!$session->broadcaster)
			{
				$response['warning'] = __('Only performer can moderate presentation.', 'ppv-live-webcams') ;
				break;
			}


			$userID = intval($_POST['userID']);
			$placement = sanitize_file_name($_POST['placement']);
			if (!$placement) $placement = 'Main';


			if (!$roomName) self::appFail('No room.');
			if (strstr($filename,".php")) self::appFail('Bad.');


			$user = get_userdata( $userID );

			if ($user)
			{
				$presentationMedia = self::appRoomMedia($post, $session, $options);

				$content = array(
					'type' => 'stream',
					'stream' => self::appStreamPlayback($userID, $userID, $post, $options),
					'name' => $user->user_login,
					'userID' => $userID,
					'userName' => $user->user_login,
				);
				$presentationMedia[$placement] = $content;

				update_post_meta( $postID, 'presentationMedia', $presentationMedia );

			} else $response['warning'] = __('User not found to display:', 'ppv-live-webcams') . $userID ;


			//update everybody and self
			update_post_meta($postID, 'updated_media' , time());
			$needUpdate['media'] = 1;

			break;


		case 'presentation_remove':
			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);

			//moderator
			if (!$session->broadcaster)
			{
				$response['warning'] = __('Only performer can moderate presentation.', 'ppv-live-webcams') ;
				break;
			}

			$placement = sanitize_file_name($_POST['placement']);


			$presentationMedia = self::appRoomMedia($post, $session, $options);

			if(array_key_exists($placement, $presentationMedia)) $presentationMedia[$placement] = [ 'name'=>$placement, 'type' => 'empty'];
			update_post_meta( $postID, 'presentationMedia', $presentationMedia );

			//update everybody and self
			update_post_meta($postID, 'updated_media' , time());
			$needUpdate['media'] = 1;


			break;

		case 'file_presentation':
			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);

			//moderator
			if (!$session->broadcaster)
			{
				$response['warning'] = __('Only performer can moderate presentation.', 'ppv-live-webcams') ;
				break;
			}


			$filename = sanitize_file_name($_POST['file_name']);
			$placement = sanitize_file_name($_POST['placement']);
			if (!$placement) $placement = 'Main';


			if (!$roomName) self::appFail('No room.');
			if (strstr($filename,".php")) self::appFail('Bad.');

			$destination = $options['uploadsPath'] . "/$roomName/";
			$file_path = $destination. $filename;


			if (file_exists($file_path))
			{
				$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

				$presentationMedia = self::appRoomMedia($post, $session, $options);

				$content = array(
					'type' => 'file',
					'url' => self::path2url($file_path),
					'filename' => $filename,
					'name' => $filename,
					'ext'=> $ext,
					'userID' => $userID,
					'userName' => $userName
				);
				$presentationMedia[$placement] = $content;

				update_post_meta( $postID, 'presentationMedia', $presentationMedia );

			} else $response['warning'] = __('File not found to display:', 'ppv-live-webcams') . ' ' . $filename ;


			//update everybody and self
			update_post_meta($postID, 'updated_media' , time());
			$needUpdate['media'] = 1;

			break;

		case 'file_delete':
			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);

			//moderator
			if (!$session->broadcaster)
			{
				$response['warning'] = __('Only performer can delete files.', 'ppv-live-webcams') ;
				break;
			}

			$filename = sanitize_file_name($_POST['file_name']);

			if (!$roomName) self::appFail('No room.');
			if (strstr($filename,".php")) self::appFail('Bad.');

			$destination = $options['uploadsPath'] . "/$roomName/";
			$file_path = $destination. $filename;


			if (file_exists($file_path))
			{
				unlink($file_path);
			} else $response['warning'] = __('File not found:', 'ppv-live-webcams') . ' ' . $filename ;

			//update list
			update_post_meta($postID, 'updated_files' , time());
			$needUpdate['files'] = 1;

			break;

		case 'file_upload':

			if (!VW_H5V_DEVMODE && !is_user_logged_in()) self::appFail('Denied visitor.' . VW_H5V_DEVMODE);

			//moderator
			/*
				if (!$session->broadcaster)
				{
				$response['warning'] = __('Only performer can manage files.', 'ppv-live-webcams') ;
				break;
				}
				*/

			$room = $roomName;
			if (!$room) self::appFail('No room.');
			if (strstr($filename,".php")) self::appFail('Bad.');

			$response['_FILES'] = $_FILES;
			//$response['files'] = $_POST['files'];

			$destination = $options['uploadsPath'] . "/$room/";
			if (!file_exists($destination)) mkdir($destination);

			$allowed = array('swf','jpg','jpeg','png','gif','txt','doc','docx','pdf', 'mp4', 'flv', 'avi', 'mpg', 'mpeg', 'ppt','pptx', 'pps', 'ppsx', 'doc', 'docx', 'odt', 'odf', 'rtf', 'xls', 'xlsx');

			$uploads = 0;

			if ($_FILES) if (is_array($_FILES))
					foreach ($_FILES as $ix => $file)
					{
						$filename = sanitize_file_name($file['name']);

						$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
						$response['uploadLastExt'] = $ext;
						$response['uploadLastF'] = $filename;

						if (in_array($ext,$allowed))
							if (file_exists($file['tmp_name']))
							{
								move_uploaded_file($file['tmp_name'], $destination . $filename);
								$response['uploadLast'] = $destination . $filename;

								$uploads++;
							}
					}

				$response['uploadCount'] = $uploads;

			/*
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


				}else echo 'uploadFailed=badExtension&';

				*/

			break;

			///collab

		case 'next':

			$nextRoomID = self::nextRoomID($userID, $options);

			if ($nextRoomID)
			{
				$response['nextRoomID'] = $nextRoomID;

				$nextPost = get_post( $nextRoomID );

				//create a new session
				$nextSession = self::sessionUpdate($userName, $nextPost->post_title, 0, 11, 1, 1, 1, $options, $userID, $nextRoomID, self::get_ip_address());
				$nextSessionID = $nextSession->id;

				$response['user'] = [
				'ID'=> intval($userID),
				'name'=>$userName,
				'sessionID'=> intval($nextSessionID),
				'loggedIn'=> true,
				'balance' => number_format(self::balance($userID, true, $options),2),
				'avatar' => get_avatar_url($userID, array('default'=> dirname(plugin_dir_url(__FILE__)).'/images/avatar.png'))
				];

				//create new session for new room
				//$response['room'] =;

				//move to next room
				$response['room'] = self::appPublicRoom($nextPost, $nextSession, $options, __('You can move a different room using Next button again.', 'ppv-live-webcams'));
			}
			else $response['warning'] = __('No next room found with current criteria!', 'ppv-live-webcams');

			break;

		case 'media':
			//notify user media (streaming) updates

			$connected = ($_POST['connected'] == 'true'?true:false);

			if ($session->meta) if (!is_array($userMeta = unserialize($session->meta))) $userMeta = array();


			$userMeta['connected'] = $connected;
			$userMeta['connectedUpdate'] = time();

			$userMetaS = serialize($userMeta);

			$sql = "UPDATE `$table_sessions` set meta='$userMetaS' WHERE id ='" . $session->id . "'";
			$wpdb->query($sql);

			$response['taskSQL'] = $sql;

			/*$usersMeta = get_post_meta( $postID, 'vws_usersMeta', true);
					if (!is_array($users)) $usersMeta = array();
					if (!array_key_exists($userID, $users)) $usersMeta[$userID] = array();


					$usersMeta[$userID]['connected'] = $connected;
					$usersMeta[$userID]['username'] = $session->username;
					$usersMeta[$userID]['updated'] = $ztime;

					update_post_meta( $postID, 'vws_usersMeta', $usersMeta);
					*/

			//if ($userID) update_user_meta($userID, 'html5_media', $ztime);
			break;

		case 'tip':

			$error = '';
			if (!$userID) $error = 'Only users can tip!';
			$response['warning'] = $error;
			if ($error) break;

			if ($options['tipCooldown'])
			{
				$lastTip = intval(get_user_meta($userID, 'vwTipLast', true));
				if ($lastTip + $options['tipCooldown'] > time()) $error = 'Cooldown Required: Already sent tip recently. Try again in few seconds!';
			}
			$response['warning'] = $error;
			if ($error) break;

			$tip = $_POST['tip'];
			$tipsURL = sanitize_text_field($_POST['tipsURL']);
			$targetID = intval($_POST['targetID']); //tip recipient

			$label = wp_encode_emoji(sanitize_text_field($tip['attributes']['LABEL']));
			$amount = intval($tip['attributes']['AMOUNT']);
			$note = wp_encode_emoji(sanitize_text_field($tip['attributes']['NOTE']));
			$sound = sanitize_text_field($tip['attributes']['SOUND']);
			$image = sanitize_text_field($tip['attributes']['IMAGE']);
			$color = sanitize_text_field($tip['attributes']['COLOR']);

			$meta = array();
			$meta['sound'] = $tipsURL . $sound;
			$meta['image'] = $tipsURL . $image;
			$meta['tip'] = true;

			if (!$label) $error = 'No tip message!';
			$response['warning'] = $error;

			if (!$error)
			{
				$message = $label . ': ' . $note;

				$message = preg_replace('/([^\s]{48})(?=[^\s])/', '$1'.'<wbr>', $message); //break long words <wbr>:Word Break Opportunity

				$private = 0;

				//tip

				$balance = self::balance($userID, true, $options);
				$response['tipSuccess'] = 1;
				$response['tipBalancePrevious'] = $balance;
				$response['tipAmount'] = $amount;


				if ($amount > $balance)
				{
					$response['tipSuccess'] = 0;
					$response['warning'] = "Tip amount ($amount) greater than available balance ($balance)! Not processed.";
				}
				else
				{

					$response['tipSQLmsg'] = self::autoMessage($message, $session, $privateUID, $meta );
					$response['tipMessage'] = $message;

					$ztime = time();

					//client cost
					$paid = number_format($amount, 2, '.', '');
					self::transaction('ppv_tip', $userID, - $paid, 'Tip for <a href="' . self::roomURL($post->post_title) . '">' . $room.'</a>. (' .$label.')' , $ztime);
					$response['tipPaid'] = $paid;

					//performer earning
					$received = number_format($amount * $options['tipRatio'], 2, '.', '');
					self::transaction('ppv_tip_earning', $targetID, $received , 'Tip from ' . $userName .' ('.$label.')', $ztime);

					//save last tip time
					update_user_meta($userID, 'vwTipLast', time());

					$response['tipTargetID'] = $targetID;
					$response['tipReceived'] = $received;

				}
			}


			break;

		case 'interaction-close':
			//any can close
			$action = $_POST['interaction'];
			$action_ID = intval($action['ID']);
			$action_status = intval($action['status']);
			$action_privateUID = intval($action['privateUID']);

			if ($action_status < 7) $action_status = 7;

			$sql="UPDATE `$table_actions` SET  status = '$action_status', mdate = '$ztime' WHERE `id` = '$action_ID'";
			$wpdb->query($sql);

			self::autoMessage('Closed private session.', $session, $action_privateUID);

			//return to public room
			$response['room'] = self::appPublicRoom($post, $session, $options, __('You closed private call.', 'ppv-live-webcams'), $public_room, $requestUID);
			break;

		case 'interaction-confirm':
			$action = $_POST['interaction'];
			$action_ID = intval($action['ID']);
			$action_status = intval($action['status']);
			if ($action_status < 5) $action_status = 5;

			$sql="UPDATE `$table_actions` SET  status = '$action_status', mdate = '$ztime' WHERE `id` = '$action_ID'";
			$wpdb->query($sql);
			break;

		case 'interaction-answer':
			//recipient answers (& executes)

			$action = $_POST['interaction'];
			//$response['interaction'] = $action;


			$action_ID = intval($action['ID']);
			$action_answer = sanitize_file_name( $action['answer'] );
			$action_status = intval($action['status']);


			//select action to answer from db
			$sqlS = "SELECT * from `$table_actions` WHERE `id` = '$action_ID'";
			$actionS = $wpdb->get_row($sqlS);
			$response['sqlS'] = $sqlS;

			$response['actionS'] = $actionS;

			if (!$actionS)
			{
				$response['warning'] = 'Action not found, to answer: '. $action_ID;
				break;
			}

			//sender requests 0, recipient received 1, recipient answered 2, recipient executed 3, sender received execution 4, sender executed 5,  closed 7

			if (!$action_status) $action_status = 1;

			if (array_key_exists('answer', $action) ) $sAnswer = " answer = '" . $action_answer. "',";

			$sql="UPDATE `$table_actions` SET $sAnswer status = '" . $action_status . "', mdate = '$ztime' WHERE `id` = '" . $action_ID . "'";
			$wpdb->query($sql);
			$response['sql'] = $sql;

			if  ($actionS->action == 'privateRequest' && $action_status == 3) //execute
				{
				//recipient (target_id) execute (3): move to private room //$response['room']
				//static function appPrivateRoom($post, $session, $privateUID, $actionID, $options)

				$response['room'] = self::appPrivateRoom($post, $session, $actionS->user_id, $action_ID, $options);

				if (!$requestUID) self::autoMessage('Accepted private session.', $session);
				self::autoMessage('Started private session.', $session, $actionS->user_id);
			}

			$response['updateID'] =  $wpdb->update_id;
			break;

		case 'interaction':
			//sender sends new interaction (0)
			
			//
			$action = $_POST['interaction'];
			$response['interaction'] = $action;
			
			//terminate other similar actions from user to prevent confusion
			$sqlU = "UPDATE `$table_actions` SET status = '12' WHERE user_id='" . $action['userID'] . "' AND room_id='" . $action['roomID'] . "' AND action = '" . $action['action'] . "' AND status=0";
			$wpdb->query($sqlU);

			$actionMeta = serialize($action['meta']);

			$sql="INSERT INTO `$table_actions` ( `user_id`, `room_id`, `target_id`, `action`, `meta`, `mdate`, `status`, `answer`) VALUES ('" . $action['userID'] . "', '" . $action['roomID'] . "', '" . $action['targetID'] . "', '" . $action['action'] . "', '$actionMeta', '$ztime', '0', '0')";
			$wpdb->query($sql);

			$response['sql'] = $sql;

			$response['insertID'] =  $wpdb->insert_id;

			if (!$requestUID) self::autoMessage('Request private session.', $session);


				
			break;

		case 'message':

			$message = $_POST['message']; //array

			if ($message)
			{
				$response['message'] = $message;
			}

			$messageText = esc_sql(wp_encode_emoji(sanitize_textarea_field($message['text'])));
			$messageUser = sanitize_text_field($message['userName']);
			$messageUserAvatar = esc_url_raw($message['userAvatar']);

			$meta = array( 'notification'=>$message['notification'], 'userAvatar' => $messageUserAvatar);
			$metaS = serialize($meta);

			if (!$privateUID)  $privateUID = 0; //public room

			//msg type: 2 web, 1 flash, 3 own notification
			$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `room_id`, `message`, `mdate`, `type`, `user_id`, `meta`, `private_uid`) VALUES ('$messageUser', '$roomName', '$roomID', '$messageText', $ztime, '2', '$userID', '$metaS', '$privateUID')";
			$wpdb->query($sql);

			$response['sql'] = $sql;

			$response['insertID'] =  $wpdb->insert_id;

			//also update chat log file
			if ($roomName) if ($messageText)
				{

					$messageText = strip_tags($messageText,'<p><a><img><font><b><i><u>');

					$messageText = date("F j, Y, g:i a", $ztime) . " <b>$userName</b>: $messageText";

					//generate same private room folder for both users
					if ($privateUID)
					{
						if ($isPerformer) $proom = $userID . "_" . $privateUID; //performer id first
						else $proom = $privateUID ."_". $userID;
					}

					$dir=$options['uploadsPath'];
					if (!file_exists($dir)) mkdir($dir);

					$dir.="/$roomName";
					if (!file_exists($dir)) mkdir($dir);

					if ($proom)
					{
						$dir.="/$proom";
						if (!file_exists($dir)) mkdir($dir);
					}

					$day=date("y-M-j",time());

					$dfile = fopen($dir."/Log$day.html","a");
					fputs($dfile,$messageText."<BR>");
					fclose($dfile);
				}



			break;
		}


		//update time
		$lastMessage = intval($_POST['lastMessage']);


		//retrieve only messages since user came online / updated
		$sdate = 0;
		if ($session) $sdate = $session->sdate;
		$startTime = max($sdate, $lastMessage);

		$response['startTime'] = $startTime;


		//!messages
		//SELECT * FROM `ksk_vw_vmls_chatlog` WHERE room='Video.Whispers' AND (type < 3 OR (type=3 AND user_id='2136' AND username='Video.Whispers')) AND private_uid ='1' AND (user_id = '2136' OR user_id = '1')

		//clean old chat logs
		$closeTime = time() - 900; //only keep for 15min
		$sql="DELETE FROM `$table_chatlog` WHERE mdate < $closeTime";
		$wpdb->query($sql);


		$items = array();

		$cndNotification = "AND (type < 3 OR (type=3 AND user_id='$userID' AND username='$userName'))"; //chat message or own notification (type 3)


		$cndPrivate = "AND private_uid = '0'";
		if ($privateUID) $cndPrivate = "AND ( (private_uid = '$privateUID' AND user_id = '$userID') OR (private_uid ='$userID' AND user_id = '$privateUID') )"; //messages in private from each to other

		$cndTime = "AND mdate > $startTime AND mdate <= $ztime";

		$sql = "SELECT * FROM `$table_chatlog` WHERE room='$roomName' $cndNotification $cndPrivate $cndTime ORDER BY mdate DESC LIMIT 0,100"; //limit to last 100 messages, until processed date
		$sql = "SELECT * FROM ($sql) items ORDER BY mdate ASC"; //but order ascendent

		//$response['sqlM'] = $sql;

		$sqlRows = $wpdb->get_results($sql);

		if ($wpdb->num_rows>0) foreach ($sqlRows as $sqlRow)
			{
				$item = [];

				$item['ID'] = intval($sqlRow->id);

				$item['userName'] = $sqlRow->username;
				$item['userID'] = intval($sqlRow->user_id);

				$item['text'] = html_entity_decode($sqlRow->message);
				$item['time'] = intval($sqlRow->mdate * 1000); //time in ms for js

				//avatar
				$uid  = $sqlRow->user_id;
				if (!$uid)
				{
					$wpUser = get_user_by($userName, $sqlRow->username);
					if (!$wpUser) $wpUser = get_user_by('login', $sqlRow->username);
					$uid = $wpUser->ID;
				}

				$item['userAvatar'] = get_avatar_url($uid);

				//meta
				if ($sqlRow->meta)
				{
					$meta = unserialize($sqlRow->meta);
					foreach ($meta as $key=>$value) $item[$key] = $value;

					$item['notification'] =  ($meta['notification'] == 'true'?true:false);
				}

				if ($sqlRow->type == 3) $item['notification'] = true;

				$items[] = $item;
			}

		$response['messages'] = $items; //messages list

		$response['timestamp'] = $ztime; //update time
		///update message


		//!actions
		//clean old actions
		$closeTime = time() - 900; //only keep for 15min
		$sql="DELETE FROM `$table_actions` WHERE mdate < $closeTime";
		$wpdb->query($sql);


		$items = array();

		//action request
		//status:
		//sender requests 0, recipient received 1, recipient answered 2, recipient executed 3, sender received execution 4, sender executed and closed 5
		//7,8 closed, 10 both closed

		//maybe status 1 (may not receive it - would need receipt confirmation from client to mark 1)

		$sTarget = "AND (target_id = '$userID' OR target_id='0' AND status < 3) OR (user_id = '$userID' AND status < 5)";

		$sql = "SELECT * FROM `$table_actions` WHERE room_id='$roomID' $sTarget AND mdate <= $ztime ORDER BY mdate DESC LIMIT 0,100"; //limit to last 100 messages, until processed date

		$sqlRows = $wpdb->get_results($sql);


		if ($wpdb->num_rows>0) foreach ($sqlRows as $sqlRow)
			{

				$item = [];

				$item['ID'] = intval($sqlRow->id);

				$item['userID'] = intval($sqlRow->user_id);
				$item['targetID'] = intval($sqlRow->target_id);

				$item['roomID'] = intval($sqlRow->room_id);

				$item['action'] = $sqlRow->action;
				$item['time'] = intval($sqlRow->mdate * 1000); //time in ms for js


				$item['status'] = intval($sqlRow->status);
				$item['answer'] = intval($sqlRow->answer);

				//meta
				if ($sqlRow->meta)
				{
					$item['meta']  = unserialize($sqlRow->meta);
				}


				// replying to sender and also moving sender to private room
				if ($sqlRow->user_id == $userID && $sqlRow->action == 'privateRequest' && $item['status'] == 3)        {

					//sender (user_id) execute : move sender to room
					$response['room'] = self::appPrivateRoom($post, $session, $sqlRow->target_id, $sqlRow->id, $options);

					//sender executed (5)
					$sql="UPDATE `$table_actions` SET status = 5, mdate = '$ztime' WHERE `id` = '" .$sqlRow->id. "'";
					$wpdb->query($sql);
				}

				if ($item['status']< 7)  $items[] = $item; //process action client side
			}

		$response['actions'] = $items; //messages list

		///actions

		///collaboration

		//check if private was closed and go back to main room, or update time if active
		if ($privateUID && $roomActionID)
		{
			//select action to answer from db
			$sqlS = "SELECT * from `$table_actions` WHERE `id` = '$roomActionID' AND status < 7";
			$actionS = $wpdb->get_row($sqlS);

			$goPublic =  0;
			if (!$actionS) $goPublic = 1;
			else $wpdb->query("UPDATE `$table_actions` SET mdate = '$ztime' WHERE `id` = '$roomActionID'");

			if ($goPublic)
			{
				self::notificationMessage(__('Private call was closed.', 'ppv-live-webcams'), $session, 0);
				$response['room'] = self::appPublicRoom($post, $session, $options, __('Private call was closed.', 'ppv-live-webcams'), $public_room, $requestUID);
			}
		}
		
//balance and room updates, except on login
if ($task != 'login')
{
		//update balance
		if (!array_key_exists('user', $response))
			$response['user'] =
				[
			'loggedIn'=> true,
			'balance' => number_format(self::balance($userID, true, $options),2),
			];
		///balance



		//update room
		$lastRoomUpdate = intval($_POST['lastRoomUpdate']);

		//items that need update: for everybody
		foreach ( ['files', 'media', 'options', 'room'] as $update)
			if (!array_key_exists($update, $needUpdate)) //unless already marked for udpdate
				{
				$updateTime = get_post_meta($postID, 'updated_' . $update, true);
				if ($updateTime) if ($updateTime > $lastRoomUpdate) $needUpdate[$update] = 1; //change after last msg: need update
			}

		//$needUpdate[] - send items marked for update
		if ($needUpdate['room'] && !$privateUID) $response['roomUpdate'] = self::appPublicRoom($post, $session, $options, '', $response['roomUpdate'], $requestUID); //no room update during private
		else //update room in full or just sections
			{
			if ($needUpdate['files']) $response['roomUpdate']['files'] = self::appRoomFiles($roomName, $options);
			if ($needUpdate['media']) $response['roomUpdate']['media'] = self::appRoomMedia($post, $session, $options);
			if ($needUpdate['options']) $response['roomUpdate']['options'] = self::appRoomOptions($post, $session, $options);
		}

		$response['roomUpdate']['users'] = self::appRoomUsers($post, $options);
		$response['roomUpdate']['updated'] = $ztime;
}

		echo json_encode($response);
		die();

	}



}