<?php
/*
Plugin Name: Webcam 2 Way Videochat
Plugin URI: https://videowhisper.com/?p=WordPress-Webcam-2Way-VideoChat
Description: <strong>Webcam 2 Way VideoChat</strong> provides instant web based 1 on 1 private video call rooms.
Version: 4.41.25
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com
*/

if (!class_exists("VWvideoChat"))
{
	class VWvideoChat {

		function VWvideoChat() { //constructor


		}

		function settings_link($links) {
			$settings_link = '<a href="options-general.php?page=webcam-2way-videochat.php">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		function plugins_loaded()
		{
			//translations
			load_plugin_textdomain('vw2wvc', false, dirname(plugin_basename(__FILE__)) .'/languages');


			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWvideoChat','settings_link') );

			wp_register_sidebar_widget('videoChatWidget','VideoWhisper Videochat', array('VWvideoChat', 'widget') );

			//shortcodes
			add_shortcode('videowhisper_videochat_manage',array( 'VWvideoChat', 'videochat_room'));

			$options = VWvideoChat::getAdminOptions();
			$page_id = get_option("vw_2vc_page_room");
			if (!$page_id || ($page_id=="-1" && $options['disablePage']=='0'))
				add_action('wp_loaded', array('VWvideoChat','updatePages'));

			add_action( 'wp_ajax_v2wvc', array('VWvideoChat','v2wvc_callback') );
			add_action( 'wp_ajax_nopriv_v2wvc', array('VWvideoChat','v2wvc_callback') );

			//check db
			$vw2vc_db_version = "1.2";

			global $wpdb;
			$table_name = $wpdb->prefix . "vw_2wsessions";
			$table_name3 = $wpdb->prefix . "vw_2wrooms";

			$installed_ver = get_option( "vw2vc_db_version" );

			if( $installed_ver != $vw2vc_db_version )
			{
				$wpdb->flush();

				$sql = "DROP TABLE IF EXISTS `$table_name`;
		CREATE TABLE `$table_name` (
		  `id` int(11) NOT NULL auto_increment,
		  `session` varchar(64) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `message` text NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `room` (`room`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_name3`;
		CREATE TABLE `$table_name3` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(64) NOT NULL,
		  `owner` int(11) NOT NULL,
		  `access` varchar(255) NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `owner` (`owner`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Video Whisper: Rooms - 2009@videowhisper.com' AUTO_INCREMENT=1;

		INSERT INTO `$table_name3` ( `name`, `owner`, `access`, `sdate`, `edate`, `capacity`, `status`, `type`) VALUES ( 'Lobby', 'All', '1', NOW(), NOW(), '100' ,'1', '1');
		";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

				if (!$installed_ver) add_option("vw2vc_db_version", $vw2vc_db_version);
				else update_option( "vw2vc_db_version", $vw2vc_db_version );

				$wpdb->flush();

				//update permalinks
				flush_rewrite_rules();
			}

		}

		//rewrite eula.txt

		function init()
		{
			add_rewrite_rule( 'eula.txt$', 'index.php?vw2wvc_eula=1', 'top' );
			add_rewrite_rule( 'crossdomain.xml$', 'index.php?vw2wvc_crossdomain=1', 'top' );
			add_rewrite_rule('^videochat/([0-9a-zA-Z\.\-\s_]+)/?', 'index.php?vw2wvc_room=$matches[1]', 'top');

		}

		function query_vars( $query_vars )
		{
			$query_vars[] = 'vw2wvc_eula';
			$query_vars[] = 'vw2wvc_crossdomain';
			$query_vars[] = 'vw2wvc_room';
			return $query_vars;
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

					$options = get_option('VWvideoChatOptions');

					if (!$options['loginRedirect']) return $redirect_to;
					if ($redirectPage = get_permalink($options['loginRedirect'])) return $redirectPage;
					return $redirect_to;


				}
			} else {
				return $redirect_to;
			}
		}

		function parse_request( &$wp )
		{
			if ( array_key_exists( 'vw2wvc_eula', $wp->query_vars ) ) {
				$options = get_option('VWvideoChatOptions');
				echo html_entity_decode(stripslashes($options['eula_txt']));
				exit();
			}

			if ( array_key_exists( 'vw2wvc_crossdomain', $wp->query_vars ) ) {
				$options = get_option('VWvideoChatOptions');
				echo html_entity_decode(stripslashes($options['crossdomain_xml']));
				exit();
			}

			if ( array_key_exists( 'vw2wvc_room', $wp->query_vars ) ) {
				$options = get_option('VWvideoChatOptions');

				$r = sanitize_file_name($wp->query_vars['vw2wvc_room']);


				//HLS if iOS detected
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$Android = stripos($agent,"Android");
				$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));

				if ($Android||$iOS)
				{
					echo '<p>Mobile app is required to access video call room from mobile device.</p>';
					if ($options['appEnabled']) if ($options['appSchema']) echo '<A HREF="'.$options['appSchema'].'://call?room='.urlencode($r).'">Mobile App: '.$r.'</A>';

						exit;
				}

				//display Flash app
				$baseurl=plugin_dir_url( __FILE__ ) . '2wvc/';

				$swfurl= $baseurl . "2wvc.swf?ssl=1&room=" . urlencode($r). "&extension=_none_&prefix=" . urlencode( admin_url() . "admin-ajax.php?action=v2wvc&task=");

				$bgcolor="#333333";
				$wmode="transparent";

?>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>2 Way Video Chat by VideoWhisper.com</title>
<style type="text/css">
<!--
BODY
{
margin:0px;
	background-color: #333;
}

#2wayvideochat
{
width:100%;
height:700px;
	z-index:0;
	vertical-align: middle;
	text-align: center;
}
-->
</style>
<SCRIPT language="JavaScript">
//the code below allows activating certain functions from javascript
function getFlashMovie(movieName) {

   if (navigator.appName.indexOf("Microsoft") != -1) {
        //alert("IE");
        if (typeof (window[movieName].videowhisperToActionscript) == 'function') {
            // alert("< IE9");
            movie = window[movieName];
        }
        else if (typeof (document[movieName].videowhisperToActionscript) == 'function') {
            // alert(">= IE9");
            movie = document[movieName];
        }
    }
    else {
        // alert("NON IE");
        movie = document[movieName];
    }

    return movie;
}

//flash = flash html object name (ie "videowhisper_chat")
//action = next / snapshot / snapshot_self / buzz / p2p_toggle
function videowhisperCallActionscript(flash, action)
{
    var movie = getFlashMovie(flash);
	if (movie == null || movie == undefined) window.alert("Flash element not found:" + flash + " :" + movie);
	else movie.videowhisperToActionscript(action);
}
</SCRIPT>
</head>
<BODY>
<CENTER>

<div id="2wayvideochat">

<object id="videowhisper_chat" width="1000" height="700" type="application/x-shockwave-flash" data="<?php echo $swfurl?>">
<param name="movie" value="<?php echo $swfurl?>" /><param name="bgcolor" value="<?php echo $bgcolor?>" /><param name="salign" value="lt" /><param name="scale" value="noscale" /><param name="allowFullScreen" value="true" /><param name="allowscriptaccess" value="always" /> <param name="base" value="<?php echo $baseurl?>" /> <param name="wmode" value="<?php echo $wmode?>" />
</object>

<noscript>
<p align="center"><strong>This content requires the Adobe Flash Player:
<a href="https://get.adobe.com/flashplayer/">Get Latest Flash</a></strong>!</p>
<p align="center">For alternate solutions including mobile apps for iOS / Android see
<a href="https://videowhisper.com/">VideoWhisper Live Video Communications</a>.</p>
</noscript>
</div>
</CENTER>

<div id="flashWarning"></div>

<script>
var hasFlash = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));

var flashWarn = '<small>Using the Flash web based interface requires <a rel="nofollow" target="_flash" href="https://get.adobe.com/flashplayer/">latest Flash plugin</a> and <a rel="nofollow" target="_flash" href="https://helpx.adobe.com/flash-player.html">activating plugin in your browser</a>. Flash apps are recommended on PC for best latency and most advanced features.</small>'

if (!hasFlash) document.getElementById("flashWarning").innerHTML = flashWarn;
</script>

</BODY>
</html>
<?php
	
				exit();
			}

			return;
		}



		//if any key matches any listing
		function inList($keys, $data)
		{
			if (!$keys) return 0;
			if (!trim($data)) return 0;
			if (strtolower(trim($data)) == 'all') return 1;
			if (strtolower(trim($data)) == 'none') return 0;

			$list=explode(",", strtolower(trim($data)));
			if (in_array('all', $list)) return 1;

			foreach ($keys as $key)
				foreach ($list as $listing)
					if (strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}

		function path2url($file, $Protocol='http://')
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

		//! App Calls
		function v2wvc_callback()
		{

			function sanV(&$var, $file=1, $html=1, $mysql=1) //sanitize variable depending on use
				{
				if (!$var) return;

				if (get_magic_quotes_gpc()) $var = stripslashes($var);

				if ($file)
				{
					$var=preg_replace("/\.{2,}/","",$var); //allow only 1 consecutive dot
					$var=preg_replace("/[^0-9a-zA-Z\.\-\s_]/","",$var); //do not allow special characters
				}

				if ($html&&!$file)
				{
					$var=strip_tags($var);
					$forbidden=array("<", ">");
					foreach ($forbidden as $search)  $var=str_replace($search,"",$var);
				}

				if ($mysql&&!$file)
				{
					$forbidden=array("'", "\"", "´", "`", "\\", "%");
					foreach ($forbidden as $search)  $var=str_replace($search,"",$var);
					$var=mysql_real_escape_string($var);
				}
			}


			ob_clean();

			switch ($_GET['task'])
			{

				//! vw_extregister
			case 'vw_extregister':

				$user_name = base64_decode($_GET['u']);
				$password =  base64_decode($_GET['p']);
				$user_email = base64_decode($_GET['e']);

				if (!$_GET['videowhisper']) exit;

				$msg = '';

				$user_name = sanitize_file_name($user_name);

				$loggedin=0;
				if (username_exists($user_name)) $msg .= __('Username is not available. Choose another!');
				if (email_exists($user_email)) $msg .= __('Email is already registered.');

				if (!is_email( $user_email )) $msg .= __('Email is not valid.');


				if ($msg=='' && $user_name && $user_email && $password)
				{
					$user_id = wp_create_user( $user_name, $password, $user_email );
					$loggedin = 1;


					$msg .= __('Account created: ') . $user_name ;
				} else $msg .= __('Could not register account.');

				?>firstParameter=fix&msg=<?php echo urlencode($msg); ?>&loggedin=<?php echo $loggedin;?><?php

				break;
				//! vw_extlogin
			case 'vw_extlogin':


				//esternal login GET u=user, p=password
				$creds = array();
				$creds['user_login'] = base64_decode($_GET['u']);
				$creds['user_password'] = base64_decode($_GET['p']);
				$creds['remember'] = true;
				if (!$_GET['videowhisper']) exit;

				remove_all_actions('wp_login'); //disable redirects or other output
				$current_user = wp_signon( $creds, false );

				if( is_wp_error($current_user))
				{
					$msg = urlencode($current_user->get_error_message()) ;
					$debug = $msg;
					echo "loggedin=0&msg=".$msg;
					$loggedin=0;
					break;
				}
				else
				{
					//logged in
					$msg = 'Login Successful.';
					$list_rooms = 1;
					$loggedin=1;

					$options = get_option('VWvideoChatOptions');
					$layoutCode = urlencode(html_entity_decode($options['layoutCodeMobile']));
					$parameters = html_entity_decode($options['parametersMobile']);

				}

				//proceed to regular login info
				// break;


				//! 2_login
			case '2_login':

				if (!$options) $options = get_option('VWvideoChatOptions');

				$rtmp_server = $options['rtmp_server'];
				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				$canWatch = $options['canWatch'];
				$watchList = $options['watchList'];

				$webKey = $options['webKey'];

				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];
				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaystRTMP = $options['alwaystRTMP'];
				$alwaystP2P = $options['alwaystP2P'];
				$disableBandwidthDetection = $options['disableBandwidthDetection'];


				//room
				if (!$room) $room=$_GET['room_name'];
				$room = sanitize_file_name($room);

				if (!$loggedin)
				{
					global $current_user;
					get_currentuserinfo();
				} //else already got from vw_extlogin

				$loggedin=0;


				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				sanV($username);

				//access keys
				$userkeys = $current_user->roles;
				$userkeys[] = $current_user->user_login;
				$userkeys[] = $current_user->ID;
				$userkeys[] = $current_user->user_email;
				$userkeys[] = $current_user->display_name;

				switch ($canWatch)
				{
				case "all":
					$loggedin=1;
					if (!$username)
					{
						$username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
						$visitor=1; //ask for username
					}
					break;
				case "members":
					if ($username) $loggedin=1;
					else $msg.=urlencode(__('Please login first or register an account if you do not have one! Click here to return to website.','vw2wvc'));
					break;
				case "list";
					if ($username)
						if (VWvideoChat::inList($userkeys, $watchList)) $loggedin=1;
						else $msg.=urlencode(__('You are not in the allowed users list!','vw2wvc'));
						else $msg.=urlencode( __('Please login first or register an account if you do not have one!', 'vw2wvc') );
						break;
				}

				//room
				global $wpdb;
				$table_name = $wpdb->prefix . "vw_2wsessions";
				$table_name3 = $wpdb->prefix . "vw_2wrooms";

				//clean online users
				$ztime=time();
				$exptime=$ztime-$options['sessionExpire'];
				$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
				$wpdb->query($sql);



				if (in_array($options['autoRoom'], array('login','always') ) ) VWvideoChat::createRoom($username, $current_user);

		

				//! list rooms
				$wpdb->flush();

				$sql = "SELECT * FROM $table_name3 where owner='".$current_user->ID."'";
				$rooms=$wpdb->get_results($sql);
				
				
				//room list
				if ($list_rooms)
				{
					//get accessible rooms


					$roomList = '<rooms>';
					$k = 0;
					
					$items1 =  $wpdb->get_results("SELECT * FROM `$table_name3` where status='1' AND owner='".$current_user->ID."' ORDER BY edate DESC, sdate DESC");
					$items2 =  $wpdb->get_results("SELECT * FROM `$table_name3` where status='1' AND owner<>'".$current_user->ID."' ORDER BY edate DESC, sdate DESC");

					$items = array_merge($items1, $items2);

					if ($items)
						foreach ($items as $item)
						{
							if (VWvideoChat::inList($userkeys, $item->access))
							{
								if (!$room) $room = $item->name;

								$owner = get_userdata( $item->owner);

								$rm=$wpdb->get_row("SELECT count(*) as no, group_concat(username separator ', ') as users, room as room FROM `$table_name` where status='1' and type='1' AND room='" . $item->name . "' GROUP BY room");

								if ($rm->no < 2) $roomList .= '<room name="'.$item->name.'" owner="'. htmlspecialchars($owner->user_login) . '" online="'.($rm->no>0?$rm->users:'-').'" count="' .$rm->no. '"/>';
								$k++;
							}
						}
						
					$roomList .= '</rooms>';
				}

				//main room
				if (!$room)
				{
					$loggedin=0;
					$msg.=urlencode("<a href=\"/\">Room missing!</a>");
				}
				else
				{
					$rm = $wpdb->get_row("SELECT * FROM `$table_name3` where status='1' AND name = '$room'");

					if (!$rm)
					{
						$loggedin=0;
						$msg.=urlencode("<a href=\"/\">Room $room is not available!</a>");
					}
					else if (!VWvideoChat::inList($userkeys, $rm->access))
						{
							$loggedin=0;
							$msg.=urlencode("<a href=\"/\">Access is not permitted in this room ($room)!</a>");
						}
				}

				if (!$username)
				{
					$loggedin=0;
					$msg.=urlencode("<a href=\"/\">Can't enter: Username missing!</a> Access: " . $canWatch);
				}

				if ($loggedin)
				{
					//this generates a session file record for rtmp login check
					$uploadsPath = $options['uploadsPath'];
					if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }

					sanV($username);

					if ($username)
					{
						$ztime=time();
						$info = "VideoWhisper=1&login=1&webKey=$webKey&start=$ztime&canKick=$canKick";

						$dir=$uploadsPath . "/";
						if (!file_exists($dir)) mkdir($dir);
						//@chmod($dir, 0777);
						$dir.="/_sessions";
						if (!file_exists($dir)) mkdir($dir);
						//@chmod($dir, 0777);

						$dfile = fopen($dir."/$username","w");
						fputs($dfile,$info);
						fclose($dfile);
						$debug = "$username-sessionCreated";
					}
				}

				//replace bad words or expression
				$filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
				$filterReplace=urlencode(" ** ");

				$uploadsPath = $options['uploadsPath'];
				if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }

				$day=date("y-M-j",time());
				$chat = VWvideoChat::path2url($uploadsPath."/$room/Log$day.html");
				$chatlog = __('The transcript of this conversation, including snapshots is available at:','vw2wvc') . " <U><A HREF=\"$chat\" TARGET=\"_blank\">$chat</A></U>.";

				$chatTextColor = "#";
				for ($i=0;$i<3;$i++) $chatTextColor .= rand(0,70);

				if (!$layoutCode) $layoutCode = urlencode(html_entity_decode($options['layoutCode']));
				if (!$parameters) $parameters = html_entity_decode($options['parameters']);

				//welcome message
				$welcome = html_entity_decode($options['welcome']) . $chatlog;

				//warn if HTTPS missing
				if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
					$welcome.= '<br><B>Warning: HTTPS not detected. Some browsers like Chrome will not permit webcam access when accessing without SSL!</B>';


				?>fixOutput=decoy&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&serverRTMFP=<?php echo $rtmfp_server?>&room=<?php echo urlencode($room)?>&welcome=<?php echo urlencode($welcome); ?>&username=<?php echo $username?>&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&camBandwidth=<?php echo $options['camBandwidth']?>&camMaxBandwidth=<?php echo $options['camMaxBandwidth']?>&disableBandwidthDetection=<?php echo $disableBandwidthDetection?>&disableUploadDetection=<?php echo $disableBandwidthDetection?>&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo $options['codecProfile']?>&codecLevel=<?php echo $options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo $options['micRate']?>&filterRegex=<?php echo $filterRegex?>&filterReplace=<?php echo $filterReplace?>&enableP2P=<?php echo $supportP2P?>&enableServer=<?php echo $supportRTMP?>&chatTextColor=<?php echo $chatTextColor?>&adServer=<?php echo urlencode($options['adServer'])?><?php echo $parameters; ?>&layoutCode=<?php echo $layoutCode; ?>&roomList=<?php echo urlencode($roomList)?>&loadstatus=1&ajax=
<?php

				break;
				// end 2_login
				//! 2_status
			case '2_status':

				$room=$_POST[r];
				$session=$_POST[s];
				$username=$_POST[u];

				$currentTime=$_POST[ct];
				$lastTime=$_POST[lt];

				$maximumSessionTime=0; //900000ms=15 minutes (in free mode this is forced)

				$redirect_url=urlencode(""); //disconnect and redirect to url
				$disconnect=urlencode(""); //disconnect with that message to standard disconnect page
				$message=urlencode(""); //show this message to user
				$send_message=urlencode(""); //user sends this message to room
				$next_room=urlencode(""); //user is moved to this room

				$s=$_POST['s'];
				$u=$_POST['u'];
				$r=$_POST['r'];
				$m=$_POST['m'];

				//sanitize variables
				sanV($s);
				sanV($u);
				sanV($r);
				sanV($m, 0, 0);

				//exit if no valid session name or room name
				if (!$s) exit;
				if (!$r) exit;

				$options = get_option('VWvideoChatOptions');

				global $wpdb;
				$table_name = $wpdb->prefix . "vw_2wsessions";
				$wpdb->flush();

				$ztime=time();

				$sql = "SELECT * FROM $table_name where session='$s' and status='1'";
				$session = $wpdb->get_row($sql);
				if (!$session)
				{
					$sql="INSERT INTO `$table_name` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 1)";
					$wpdb->query($sql);
				}
				else
				{
					$sql="UPDATE `$table_name` set edate=$ztime, room='$r', username='$u', message='$m' where session='$s' and status='1' LIMIT 1";
					$wpdb->query($sql);
				}

				//clean online sessions
				$exptime=$ztime-$options['sessionExpire'];
				$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
				$wpdb->query($sql);


				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $currentTime?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&message=<?php echo $message?>&send_message=<?php echo $send_message?>&redirect_url=<?php echo $redirect_url?>&loadstatus=1&ajax=<?php
				break;

			case 'chatfilter':
				$message = $_POST['m'];
				$session=$_POST['s'];
				$username=$_POST['u'];

				$filtered = ucwords($message) . " (web filter test - ucwords)";
				$filtered = urlencode($filtered);

				?>m=<?php echo $filtered; ?>&ajax=<?php
				break;

			case 'vc_chatlog':
				//Public and private chat logs
				$private=$_POST['private']; //private chat username, blank if public chat
				$username=$_POST['u'];
				$session=$_POST['s'];
				$room=$_POST['r'];
				$message=$_POST['msg'];
				$time=$_POST['msgtime'];

				$options = get_option('VWvideoChatOptions');
				$uploadsPath = $options['uploadsPath'];
				if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }

				//do not allow uploads to other folders
				sanV($room);
				sanV($private);
				sanV($session);
				if (!$room) exit;

				//generate same private room folder for both users
				if ($private)
				{
					if ($private>$session) $proom=$session ."_". $private; else $proom=$private ."_". $session;
				}

				//create folder to store logs
				$dir=$uploadsPath;
				if (!file_exists($dir)) mkdir($dir);
				//@chmod($dir, 0777);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				//@chmod($dir, 0777);
				if ($proom) $dir.="/$proom";
				if (!file_exists($dir)) mkdir($dir);
				//@chmod($dir, 0777);

				$day=date("y-M-j",time());

				$dfile = fopen($dir."/Log$day.html","a");
				fputs($dfile,$message."<BR>");
				fclose($dfile);
				?>loadstatus=1&ajax=<?php
				break;
				//! vc_snapshots
			case 'vc_snapshots':
				if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
				{
					$room=$_GET['room'];
					$stream=$_GET['name'];


					sanV($stream);
					sanV($room);
					if (!$stream) exit;
					if (!$room) exit;

					$options = get_option('VWvideoChatOptions');
					$uploadsPath = $options['uploadsPath'];
					if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }


					//create folder to store logs
					$dir=$uploadsPath;
					if (!file_exists($dir)) mkdir($dir);
					//@chmod($dir, 0777);
					$dir.="/$room";
					if (!file_exists($dir)) mkdir($dir);
					//@chmod($dir, 0777);

					// get bytearray
					$jpg = $GLOBALS["HTTP_RAW_POST_DATA"];

					// save file
					$filename=$stream.".".time().".jpg";
					$picture=$dir."/".$filename;
					$fp=fopen($picture,"w");
					if ($fp)
					{
						fwrite($fp,$jpg);
						fclose($fp);
					}

					//add it to chat log
					$message="<IMG SRC=\"$filename\" ALT=\"$stream\" TITLE=\"$stream\" ALIGN=\"RIGHT\">";
					//get daily log name
					$day=date("y-M-j",time());

					$chat=$dir."/Log$day.html";
					$dfile = fopen($chat,"a");
					fputs($dfile,$message."<BR>");
					fclose($dfile);

					$chat=urlencode($chat);
					$picture=urlencode($picture);
				}
				?>chat=<?php echo $chat?>&picture=<?php echo $pic?>&loadstatus=1&ajax=<?php
				break;

			case 'rtmp_login':
				//rtmp server should check login like rtmp_login.php?s=$session
				$session = $_GET['s'];
				sanV($session);
				if (!$session) exit;

				$options = get_option('VWvideoChatOptions');
				$uploadsPath = $options['uploadsPath'];
				if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }

				$filename1 = uploadsPath."/_sessions/$session";
				if (file_exists($filename1))
				{
					echo implode('', file($filename1));
				}
				else
				{
					echo "VideoWhisper=1&login=0&ajax=";
				}
				break;

			case 'rtmp_logout':
				//rtmp server notifies client disconnect here
				$session = $_GET['s'];
				sanV($session);
				if (!$session) exit;

				$options = get_option('VWvideoChatOptions');
				$uploadsPath = $options['uploadsPath'];
				if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/2wvc'; }

				echo "logout=";
				$filename1 = $uploadsPath. "/_sessions/$session";
				if (file_exists($filename1))
				{
					echo unlink($filename1) .'&ajax=';
				}
				break;

				//! 2_ads
			case '2_ads':
				/* Sample local ads serving script ; Or use http://adinchat.com compatible ads server to setup http://adinchat.com/v/your-campaign-id

POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)

*/

				$room=$_POST[r];
				$session=$_POST[s];
				$username=$_POST[u];

				$currentTime=$_POST[ct];
				$lastTime=$_POST[lt];

				$cam=$_POST['cam'];
				$mic=$_POST['mic'];

				$webcam=0;
				if ($cam==2) $webcam=1;

				$ztime=time();

				//fill ad to show
				$ad="<B>Sample Ad</B><BR>See <a href=\"http://www.adinchat.com\" target=\"_blank\"><U><B>AD in Chat</B></U></a> compatible ad management server.";

				?>x=1&ad=<?php echo urlencode($ad)?>&loadstatus=1&ajax=<?php
				break;

			case '2_report':
				//report user

				$room=$_POST['r'];
				$session=$_POST['s'];
				$username=$_POST['u'];
				$other=$_POST['o'];

				$cam=$_POST['cam'];
				$mic=$_POST['mic'];

				$next_room='';
				
				//these produce actions if defined
				$redirect_url=urlencode(""); //disconnect and redirect to url
				$disconnect=urlencode(""); //disconnect with that message to standard disconnect page
				$message=urlencode("User was reported: $other."); //show this message to user
				
				$send_message=urlencode("Report: $session reported $other in room $room at " . date("y-M-j H:m:s",time())); //user sends this message to room
				
				$next_room=urlencode($next_room); //user moves to this room
				?>firstParameter=1&next_room=<?php echo $next_room?>&message=<?php echo $message?>&send_message=<?php echo $send_message?>&redirect_url=<?php echo $redirect_url?>&disconnect=<?php echo $disconnect?>&loadstatus=1&ajax=<?php
				break;
	
	
			case '2_next':
				/*
This script implements a custom Next button function that can be used for various implementations.

POST Variables:
u=Username
s=Session, usually same as username
r=Room
cam, mic = 0 none, 1 disabled, 2 enabled
*/

				$room=$_POST['r'];
				$session=$_POST['s'];
				$username=$_POST['u'];
				$other=$_POST['o'];

				$cam=$_POST['cam'];
				$mic=$_POST['mic'];

				$next_room="next_test";
				$day=date("y-M-j",time());
				$chat="uploads/$next_room/Log$day.html";
				$chatlog="The transcript of this conversation, including snapshots is available at <U><A HREF=\"$chat\" TARGET=\"_blank\">$chat</A></U>.";

				//these produce actions if defined
				$redirect_url=urlencode(""); //disconnect and redirect to url
				$disconnect=urlencode(""); //disconnect with that message to standard disconnect page
				$message=urlencode("Next button pressed. This feature can be programmed from 2_next.php or disabled from 2_login.php parameters. $chatlog"); //show this message to user
				$send_message=urlencode("I pressed next."); //user sends this message to room
				$next_room=urlencode($next_room); //user moves to this room
				?>firstParameter=1&next_room=<?php echo $next_room?>&message=<?php echo $message?>&send_message=<?php echo $send_message?>&redirect_url=<?php echo $redirect_url?>&disconnect=<?php echo $disconnect?>&loadstatus=1&ajax=<?php
				break;
				//! translation
			case 'translation':
?>
			<translations>
			<?php
				$options = get_option('VWvideoChatOptions');
				echo html_entity_decode(stripslashes($options['translationCode']));
?>
			</translations>
			<?php
				break;
				//! 2_logout
			case '2_logout':
				wp_redirect( home_url());
				exit;
				break;

			default:
				echo sanitize_file_name($_GET['task']) . '&ajax=';
			}

			die();
		}



		function roomLink($room)
		{
			$options = get_option('VWvideoChatOptions');

			switch ($options['roomLink'])
			{
			case 'rewrite':
				return site_url() . '/videochat/' . urlencode($room);
				break;

			case 'plugin':
				return plugin_dir_url( __FILE__ ) . '2wvc/?r=' . urlencode($room);
				break;
			}
		}

		function videochat_room()
		{

?>

		<script language="JavaScript">
		function censorName()
			{
				document.adminForm.room.value = document.adminForm.room.value.replace(/^[\s]+|[\s]+$/g, '');
				document.adminForm.room.value = document.adminForm.room.value.replace(/[^0-9a-zA-Z_\-]+/g, '-');
				document.adminForm.room.value = document.adminForm.room.value.replace(/\-+/g, '-');
				document.adminForm.room.value = document.adminForm.room.value.replace(/^\-+|\-+$/g, '');
				if (document.adminForm.room.value.length>0) return true;
				else
				{
				alert("A room name is required!");
				document.adminForm.button.disabled=false;
				document.adminForm.button.value="Create";
				return false;
				}
			}
			</script>

		<?php

			global $wpdb;

			$this_page    =   $_SERVER['REQUEST_URI'];

			//can user create room?
			$options = get_option('VWvideoChatOptions');
			$canBroadcast = $options['canBroadcast'];
			$broadcastList = $options['broadcastList'];
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$loggedin=0;

			global $current_user;
			get_currentuserinfo();
			if ($current_user->$userName) $username=urlencode($current_user->$userName);

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;
			$userkeys[] = $current_user->display_name;

			switch ($canBroadcast)
			{

			case "members":
				if ($username) $loggedin=1;
				else $msg=urlencode(__('Please login first or register an account if you do not have one!','vw2wvc'));
				break;
			case "list";
				if ($username)
					if (VWvideoChat::inList($userkeys, $broadcastList)) $loggedin=1;
					else $msg=urlencode(__('You are not allowed to setup rooms!','vw2wvc'));
					else $msg=urlencode( __('Please login first or register an username if you do not have one!', 'vw2wvc') );
					break;
			}


			if (!$loggedin)
			{
				echo '<p>' . urldecode($msg) . '</p>';
				echo '<p>' . __('This pages allows creating and managing video chat rooms for register members that have this feature enabled.') . '</p>';
			}

			if ($loggedin)
			{
				$table_name = $wpdb->prefix . "vw_2wsessions";
				$table_name3 = $wpdb->prefix . "vw_2wrooms";

				//delete
				if ($delid=(int) $_GET['delete'])
				{
					$sql = $wpdb->prepare("DELETE FROM $table_name3 where owner='".$current_user->ID."' AND id='%d'", array($delid));
					$wpdb->query($sql);
					$wpdb->flush();
					echo "<div class='update'>Room #$delid was deleted.</div>";
				}

				//! create room
				$room = sanitize_file_name($_POST['room']);
				if ($room) echo VWvideoChat::createRoom($room, $current_user, sanitize_text_field($_POST['access']) );

				//! auto room
				if (in_array($options['autoRoom'], array('manage','always') ) ) VWvideoChat::createRoom($username, $current_user);

				//clean online users
				$ztime=time();
				$exptime=$ztime-$options['sessionExpire'];
				$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
				$wpdb->query($sql);

				//! list rooms
				$wpdb->flush();

				$sql = "SELECT * FROM $table_name3 where owner='".$current_user->ID."'";
				$rooms=$wpdb->get_results($sql);

				$roomsNo = count($rooms);

				if ($options['maxRooms']) $roomsNo .=  '/' . $options['maxRooms'];

				echo "<H3>My Rooms ($roomsNo)</H3>";
				if (count($rooms))
				{
					echo "<table>";
					echo '<tr><th>' . __('Room', 'vw2wvc') . '</th><th>' . __('Link to Share', 'vw2wvc') . '</th><th>' . __('Online', 'vw2wvc') . '</th><th>' . __('Access', 'vw2wvc') . '</th><th>' . __('Manage', 'vw2wvc') . '</th></tr>';
					$root_url = plugins_url() . "/";
					foreach ($rooms as $rd)
					{
						$rm=$wpdb->get_row("SELECT count(*) as no, group_concat(username separator ' <BR> ') as users, room as room FROM `$table_name` where status='1' and type='1' AND room='".$rd->name."' GROUP BY room");

						echo '<tr> <td><a href="' . VWvideoChat::roomLink($rd->name) . '"><B>' . $rd->name . '</B></a> </td> <td>' ;
						if ($options['appEnabled']) if ($options['appSchema']) echo ' <br>Web: ';
							echo  VWvideoChat::roomLink($rd->name);
						if ($options['appEnabled']) if ($options['appSchema']) echo ' <br>App: '.$options['appSchema'].'://call?room='.urlencode($rd->name).'';
							echo  '</td> <td>' . ($rm->no>0?$rm->users:'0') . '</td> <td>' . ($rd->access) . '</td> <td><a href="' . $this_page . (strstr($this_page,'?')?'&':'?') . 'delete=' . $rd->id.'">' . __('Delete', 'vw2wvc') . '</a> <BR><a href="' . VWvideoChat::path2url($options['uploadsPath']) . '/' . urlencode($rd->name) . '/">' . __('Logs', 'vw2wvc') . '</a> </td> </tr>';
					}
					echo "</table>";

				}
				else _e('You do not currently have any rooms.','vw2wvc');


				//! create room form
				if (!$room && (!$options['maxRooms'] || count($rooms) < $options['maxRooms']) )
					echo '<h3>' . __('Setup a New Room', 'vw2wvc') . '</h3><form method="post" action="' . $this_page .'"  name="adminForm">
		  ' . __('Name', 'vw2wvc') . ' <input name="room" type="text" id="room" value="Room_'.base_convert((time()-1225000000).rand(0,10),10,36).'" size="20" maxlength="64" onChange="censorName()"/>
		  <br>' . __('Access List', 'vw2wvc') . ' <input name="access" type="text" id="access" value="all" size="64" maxlength="255""/>
  <BR><input type="submit" name="button" id="button" value="' . __('Create', 'vw2wvc') . '" onclick="censorName(); adminForm.submit();"/>
		</form>
		';
			}

		}


		function createRoom($room, $user, $access = 'all')
		{

			$room = sanitize_file_name($room);

			if (!$room) return  '<div class="error">' . __('No room name. Use valid characters!', 'vw2wvc') . '</div>';

			$access = sanitize_text_field($access);

			global $wpdb;
			$table_name3 = $wpdb->prefix . "vw_2wrooms";

			$wpdb->flush();
			$ztime=time();

			$sql = $wpdb->prepare("SELECT owner FROM $table_name3 where name='%s'", array($room));
			$rdata = $wpdb->get_row($sql);
			if (!$rdata)
			{
				$sql=$wpdb->prepare("INSERT INTO `$table_name3` ( `name`, `access`, `owner`, `sdate`, `edate`, `status`, `type`) VALUES ('%s', '%s','".$user->ID."', '$ztime', '0', 1, 1)", array($room, $access));
				$wpdb->query($sql);
				$wpdb->flush();
				return '<div class="update">' . sprintf(__('Room "%s" was created.', 'vw2wvc'), $room) . '</div>';
			}
			else
			{
				return '<div class="error">' .sprintf( __('Room name "%s" is already in use. Please choose another name!', 'vw2wvc'),$room) . '</div>';
				$room="";
			}

		}

		function user_register($user_id)
		{

			//create room when user registers

			$options = get_option('VWvideoChatOptions');

			if (!in_array($options['autoRoom'], array('register','always') ) ) return; //not enabled

			//can user create room?
			$canBroadcast = $options['canBroadcast'];
			$broadcastList = $options['broadcastList'];
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$loggedin=0;

			$current_user = get_userdata($user_id);

			if ($current_user->$userName) $username=urlencode($current_user->$userName);

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;
			$userkeys[] = $current_user->display_name;

			switch ($canBroadcast)
			{

			case "members":
				if ($username) $loggedin=1;
				else $msg=urlencode(__('Please login first or register an account if you do not have one!','vw2wvc'));
				break;
			case "list";
				if ($username)
					if (VWvideoChat::inList($userkeys, $broadcastList)) $loggedin=1;
					else $msg=urlencode(__('You are not allowed to setup rooms!','vw2wvc'));
					else $msg=urlencode( __('Please login first or register an username if you do not have one!', 'vw2wvc') );
					break;
			}

			if ($loggedin) if (in_array($options['autoRoom'], array('register','always') ) ) VWvideoChat::createRoom($username, $current_user);

		}

		//! Pages
		function updatePages()
		{

			global $user_ID;
			$page = array();
			$page['post_type']    = 'page';
			$page['post_content'] = '[videowhisper_videochat_manage]';
			$page['post_parent']  = 0;
			$page['post_author']  = $user_ID;
			$page['post_status']  = 'publish';
			$page['post_title']   = 'Video Chat';

			$page_id = get_option("vw_2vc_page_room");
			if ($page_id>0) $page['ID'] = $page_id;

			$pageid = wp_insert_post ($page);

			update_option( "vw_2vc_page_room", $pageid);
		}

		function deletePages()
		{
			$page_id = get_option("vw_2vc_page_room");
			if ($page_id > 0)
			{
				wp_delete_post($page_id);
				update_option( "vw_2vc_page_room", -1);
			}
		}


		//! Widget
		function widgetContent()
		{
			global $wpdb;
			$table_name = $wpdb->prefix . "vw_2wsessions";

			$root_url = plugins_url();

			$options = get_option('VWvideoChatOptions');

			//clean online sessions
			$exptime=time()-$options['sessionExpire'];
			$sql="DELETE FROM `$table_name` WHERE edate < $exptime";
			$wpdb->query($sql);
			$wpdb->flush();

			$items =  $wpdb->get_results("SELECT count(*) as no, group_concat(username separator ', ') as users, room as room FROM `$table_name` where status='1' and type='1' GROUP BY room");

			echo "<ul>";

			if ($items)
				foreach ($items as $item)
				{
					if ($item->no<2) echo "<li><a href='" . $root_url ."webcam-2way-videochat/2wvc/?r=".urlencode($item->room)."'><B>".$item->room."</B> (".($item->users).") ".($item->message?": ".$item->message:"") ."</a></li>";
					else echo "<li><B>".$item->room."</B> (".($item->users).") ".($item->message?": ".$item->message:"") ."</li>";
				}
			else echo "<li>No users online.</li>";
			echo "</ul>";

			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';
			echo '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="https://videowhisper.com/?p=WordPress-Webcam-2Way-VideoChat">WordPress VideoChat</a>.</p></div>';
		}

		function widget($args) {
			extract($args);
			echo $before_widget;
			echo $before_title;?>Video Chat<?php echo $after_title;
			VWvideoChat::widgetContent();
			echo $after_widget;
		}

		function menu() {
			add_options_page('VideoChat Options', 'Video Chat', 9, basename(__FILE__), array('VWvideoChat', 'options'));
		}

		//! Options
		function getAdminOptions() {

			$upload_dir = wp_upload_dir();

			$root_url = plugins_url();
			//$root_path = plugin_dir_path( __FILE__ );
			$root_ajax = admin_url( 'admin-ajax.php?action=v2wvc&task=');

			$adminOptions = array(
				'userName' => 'display_name',
				'rtmp_server' => 'rtmp://localhost/videowhisper',
				'rtmp_amf' => 'AMF3',

				'canBroadcast' => 'members',
				'broadcastList' => 'Super Admin, Administrator, Editor, Author',
				'canWatch' => 'all',
				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',
				'autoRoom' => 'manage',
				'maxRooms' => '5',
				'welcome'=> 'Welcome to video chat room! High quality snapshots of other person can be taken on request.',
				'loginRedirect' => '0',
				'sessionExpire' => '60',
				'parameters' => '&limitByBandwidth=1&camPicture=0&showCamSettings=1&silenceLevel=0&silenceTimeout=0&micGain=50&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=640&camHeight=480&camFPS=30&disableEmoticons=0&showTextChat=1&sendTextChat=1&webfilter=0&serverProxy=best&verboseLevel=4&disableVideo=0&disableSound=0&bufferLive=0&bufferFull=0&bufferLivePlayback=0&bufferFullPlayback=0&autoSnapshots=1&snapshotsTime=300000&configureConnection=1&configureSource=0&enableNext=0&enableBuzz=1&enableSoundFx=1&requestSnapshot=1&enableButtonLabels=1&enableFullscreen=1&enableSwap=1&enableLogout=1&enableLogo=1&enableHeaders=1&enableTitles=1&videoW=480&videoH=365&video2W=480&video2H=365&adsInterval=600000&adsTimeout=20000&pushToTalk=0&silenceToTalk=0',
				'layoutCode' => 'id=soundfx&x=766&y=571; id=bFul&x=15&y=105; id=VideoSlot2&x=510&y=140; id=ChatSlot&x=250&y=505; id=VideoSlot1&x=10&y=140; id=TextInput&x=250&y=670; id=head2&x=510&y=100; id=logo&x=389&y=25; id=bSnd&x=920&y=107; id=head&x=10&y=100; id=next&x=186&y=521; id=bVid&x=885&y=109; id=connection&x=186&y=571; id=bLogout&x=950&y=10; id=bFul2&x=955&y=105; id=bSwap&x=120&y=111; id=bSwap2&x=850&y=111; id=snapshot&x=766&y=621; id=camera&x=186&y=621; id=bCam&x=85&y=109; id=bMic&x=50&y=107; id=buzz&x=766&y=521',
				'layoutCodeMobile' => 'id=VideoBackground2&x=0&y=130&w=1280&h=720&z=2; id=head&x=1280&y=40&w=640&h=70&z=11; id=TextInput&x=1280&y=980&w=480&h=60&z=6; id=bVid&x=30&y=30&w=70&h=70&z=17&m=1.5; id=ChatSlot&x=1280&y=470&w=640&h=500&z=5; id=soundfx&x=660&y=980&w=240&h=60&z=25&m=2.0; id=head2&x=0&y=60&w=1280&h=70&z=12; id=bSend&x=1790&y=974&w=140&h=60&z=21&m=1.4; id=bSwap&x=120&y=111&w=48&h=48&z=19; id=snapshot&x=340&y=980&w=240&h=60&z=27&m=2.0; id=bSwap2&x=850&y=111&w=48&h=48&z=20; id=VideoSlot2&x=0&y=130&w=1280&h=720&z=4; id=logo&x=500&y=0&w=520&h=60&z=22; id=undefined&x=0&y=0&w=1920&h=1080&z=0; id=Timers&x=980&y=880&w=200&h=23.45&z=8; id=bCam&x=1720&y=20&w=70&h=70&z=15&m=1.5; id=VideoBackground1&x=1280&y=110&w=640&h=360&z=1; id=bMic&x=1820&y=20&w=70&h=70&z=16&m=1.5; id=title2&x=180&y=66&w=1100&h=60&z=24; id=bSnd&x=130&y=30&w=70&h=70&z=18&m=1.5; id=title&x=1280&y=46&w=460&h=60&z=23; id=VideoSlot1&x=1280&y=110&w=640&h=360&z=3; id=buzz&x=980&y=980&w=240&h=60&z=26&m=2.0; id=bLogout&x=1150&y=30&w=64&h=64&z=24&m=1.5; id=flag&x=50&y=880&w=82.5h=32&z=27&m=1.5;',
				'parametersMobile' => '&limitByBandwidth=0&camPicture=0&showCamSettings=0&silenceLevel=0&silenceTimeout=0&micGain=50&showTimer=1&showCredit=1&disconnectOnTimeout=1&camWidth=1280&camHeight=720&camFPS=15&disableEmoticons=0&showTextChat=1&sendTextChat=1&chatTextSize=32&webfilter=0&serverProxy=best&verboseLevel=4&disableVideo=0&disableSound=0&bufferLive=0&bufferFull=0&bufferLivePlayback=0&bufferFullPlayback=0&autoSnapshots=1&snapshotsTime=600000&configureConnection=0&configureSource=0&enableNext=0&enableBuzz=1&enableSoundFx=1&requestSnapshot=1&enableButtonLabels=1&enableFullscreen=1&enableSwap=1&enableLogout=1&enableLogo=1&enableHeaders=1&enableTitles=1&videoW=640&videoH=360&video2W=1280&video2H=720&adsInterval=0&adsTimeout=0&pushToTalk=0&silenceToTalk=0',
				'translationCode' => '<t text="Successfully connected to RTMFP server." translation="Successfully connected to RTMFP server."/>
<t text="External Encoder" translation="External Encoder"/>
<t text="Toggle Sound Effects" translation="Toggle Sound Effects"/>
<t text="Buzz!" translation="Buzz!"/>
<t text="Swap Panels" translation="Swap Panels"/>
<t text="LogOut" translation="LogOut"/>
<t text="Sound Effects" translation="Sound Effects"/>
<t text="Toggle Audio" translation="Toggle Audio"/>
<t text="Camera" translation="Camera"/>
<t text="Toggle Video" translation="Toggle Video"/>
<t text="Next!" translation="Next!"/>
<t text="Server Connection" translation="Server Connection"/>
<t text="Microphone" translation="Microphone"/>
<t text="Server / P2P" translation="Server / P2P"/>
<t text="Entering room" translation="Entering room"/>
<t text="Successfully connected to RTMP server." translation="Successfully connected to RTMP server."/>
<t text="Connecting to RTMFP server." translation="Connecting to RTMFP server."/>
<t text="FullScreen" translation="FullScreen"/>
<t text="Toggle Microphone" translation="Toggle Microphone"/>
<t text="Toggle Webcam" translation="Toggle Webcam"/>
<t text="joined" translation="joined"/>
<t text="Save Photo in Logs" translation="Save Photo in Logs"/>
<t text="Translation XML was copied to clipboard. Just paste it in a text editor." translation="Translation XML was copied to clipboard. Just paste it in a text editor."/>
<t text="Snapshot" translation="Snapshot"/>
<t text="No Connection" translation="No Connection"/>
<t text="Webcam / External Encoder" translation="Webcam / External Encoder"/>',

				'camBandwidth' => '75000',
				'camMaxBandwidth' => '200000',

				'videoCodec'=>'H263',
				'codecProfile' => 'main',
				'codecLevel' => '3.1',

				'soundCodec'=> 'Nellymoser',
				'soundQuality' => '9',
				'micRate' => '22',

				'overLogo' => $root_url .'webcam-2way-videochat/2wvc/logo.png',
				'overLink' => 'https://videowhisper.com',

				'tokenKey' => 'VideoWhisper',
				'webKey' => 'VideoWhisper',

				'serverRTMFP' => 'rtmfp://stratus.adobe.com/f1533cc06e4de4b56399b10d-1a624022ff71/',
				'p2pGroup' => 'VideoWhisper',
				'supportRTMP' => '1',
				'supportP2P' => '0',
				'alwaysRTMP' => '1',
				'alwaysP2P' => '0',
				'disableBandwidthDetection' => '1',
				'videowhisper' => 0,
				'disablePage' => '0',
				'uploadsPath' => $upload_dir['basedir'] . '/2wvc',
				'adServer' => $root_ajax .'2_ads',
				'roomLink' => 'rewrite',
				'appEnabled' => '0',
				'appSchema' => 'vw2wvc',
				'eula_txt' =>'The following Terms of Use (the "Terms") is a binding agreement between you, either an individual subscriber, customer, member, or user of at least 18 years of age or a single entity ("you", or collectively "Users") and owners of this application, service site and networks that allow for the distribution and reception of video, audio, chat and other content (the "Service").

By accessing the Service and/or by clicking "I agree", you agree to be bound by these Terms of Use. You hereby represent and warrant to us that you are at least eighteen (18) years of age or and otherwise capable of entering into and performing legal agreements, and that you agree to be bound by the following Terms and Conditions. If you use the Service on behalf of a business, you hereby represent to us that you have the authority to bind that business and your acceptance of these Terms of Use will be treated as acceptance by that business. In that event, "you" and "your" will refer to that business in these Terms of Use.

Prohibited Conduct

The Services may include interactive areas or services (" Interactive Areas ") in which you or other users may create, post or store content, messages, materials, data, information, text, music, sound, photos, video, graphics, applications, code or other items or materials on the Services ("User Content" and collectively with Broadcaster Content, " Content "). You are solely responsible for your use of such Interactive Areas and use them at your own risk. BY USING THE SERVICE, INCLUDING THE INTERACTIVE AREAS, YOU AGREE NOT TO violate any law, contract, intellectual property or other third-party right or commit a tort, and that you are solely responsible for your conduct while on the Service. You agree that you will abide by these Terms of Service and will not:

use the Service for any purposes other than to disseminate or receive original or appropriately licensed content and/or to access the Service as such services are offered by us;

rent, lease, loan, sell, resell, sublicense, distribute or otherwise transfer the licenses granted herein;

post, upload, or distribute any defamatory, libelous, or inaccurate Content;

impersonate any person or entity, falsely claim an affiliation with any person or entity, or access the Service accounts of others without permission, forge another persons digital signature, misrepresent the source, identity, or content of information transmitted via the Service, or perform any other similar fraudulent activity;

delete the copyright or other proprietary rights notices on the Service or Content;

make unsolicited offers, advertisements, proposals, or send junk mail or spam to other Users of the Service, including, without limitation, unsolicited advertising, promotional materials, or other solicitation material, bulk mailing of commercial advertising, chain mail, informational announcements, charity requests, petitions for signatures, or any of the foregoing related to promotional giveaways (such as raffles and contests), and other similar activities;

harvest or collect the email addresses or other contact information of other users from the Service for the purpose of sending spam or other commercial messages;

use the Service for any illegal purpose, or in violation of any local, state, national, or international law, including, without limitation, laws governing intellectual property and other proprietary rights, and data protection and privacy;

defame, harass, abuse, threaten or defraud Users of the Service, or collect, or attempt to collect, personal information about Users or third parties without their consent;

remove, circumvent, disable, damage or otherwise interfere with security-related features of the Service or Content, features that prevent or restrict use or copying of any content accessible through the Service, or features that enforce limitations on the use of the Service or Content;

reverse engineer, decompile, disassemble or otherwise attempt to discover the source code of the Service or any part thereof, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation;

modify, adapt, translate or create derivative works based upon the Service or any part thereof, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation;

intentionally interfere with or damage operation of the Service or any user enjoyment of them, by any means, including uploading or otherwise disseminating viruses, adware, spyware, worms, or other malicious code;

relay email from a third party mail servers without the permission of that third party;

use any robot, spider, scraper, crawler or other automated means to access the Service for any purpose or bypass any measures we may use to prevent or restrict access to the Service;

manipulate identifiers in order to disguise the origin of any Content transmitted through the Service;

interfere with or disrupt the Service or servers or networks connected to the Service, or disobey any requirements, procedures, policies or regulations of networks connected to the Service;use the Service in any manner that could interfere with, disrupt, negatively affect or inhibit other users from fully enjoying the Service, or that could damage, disable, overburden or impair the functioning of the Service in any manner;

use or attempt to use another user account without authorization from such user and us;

attempt to circumvent any content filtering techniques we employ, or attempt to access any service or area of the Service that you are not authorized to access; or

attempt to indicate in any manner that you have a relationship with us or that we have endorsed you or any products or services for any purpose.

Further, BY USING THE SERVICE, INCLUDING THE INTERACTIVE AREAS YOU AGREE NOT TO post, upload to, transmit, distribute, store, create or otherwise publish through the Service any of the following:

Content that would constitute, encourage or provide instructions for a criminal offense, violate the rights of any party, or that would otherwise create liability or violate any local, state, national or international law or regulation;

Content that may infringe any patent, trademark, trade secret, copyright or other intellectual or proprietary right of any party. By posting any Content, you represent and warrant that you have the lawful right to distribute and reproduce such Content;

Content that is unlawful, libelous, defamatory, obscene, pornographic, indecent, lewd, suggestive, harassing, threatening, invasive of privacy or publicity rights, abusive, inflammatory, fraudulent or otherwise objectionable;

Content that impersonates any person or entity or otherwise misrepresents your affiliation with a person or entity;

private information of any third party, including, without limitation, addresses, phone numbers, email addresses, Social Security numbers and credit card numbers;

viruses, corrupted data or other harmful, disruptive or destructive files; and

Content that, in the sole judgment of Service moderators, is objectionable or which restricts or inhibits any other person from using or enjoying the Interactive Areas or the Service, or which may expose us or our users to any harm or liability of any type.

Service takes no responsibility and assumes no liability for any Content posted, stored or uploaded by you or any third party, or for any loss or damage thereto, nor is liable for any mistakes, defamation, slander, libel, omissions, falsehoods, obscenity, pornography or profanity you may encounter. Your use of the Service is at your own risk. Enforcement of the user content or conduct rules set forth in these Terms of Service is solely at Service discretion, and failure to enforce such rules in some instances does not constitute a waiver of our right to enforce such rules in other instances. In addition, these rules do not create any private right of action on the part of any third party or any reasonable expectation that the Service will not contain any content that is prohibited by such rules. As a provider of interactive services, Service is not liable for any statements, representations or Content provided by our users in any public forum, personal home page or other Interactive Area. Service does not endorse any Content or any opinion, recommendation or advice expressed therein, and Service expressly disclaims any and all liability in connection with Content. Although Service has no obligation to screen, edit or monitor any of the Content posted in any Interactive Area, Service reserves the right, and has absolute discretion, to remove, screen or edit any Content posted or stored on the Service at any time and for any reason without notice, and you are solely responsible for creating backup copies of and replacing any Content you post or store on the Service at your sole cost and expense. Any use of the Interactive Areas or other portions of the Service in violation of the foregoing violates these Terms and may result in, among other things, termination or suspension of your rights to use the Interactive Areas and/or the Service.
',
				'crossdomain_xml' =>'<cross-domain-policy>
<allow-access-from domain="*"/>
<site-control permitted-cross-domain-policies="master-only"/>
</cross-domain-policy>'
			);

			$options = get_option('VWvideoChatOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWvideoChatOptions', $adminOptions);
			return $adminOptions;
		}

		function options()
		{

			$options = VWvideoChat::getAdminOptions();

			if (isset($_POST))
			{

				foreach ($options as $key => $value)
					if (isset($_POST[$key])) $options[$key] = $_POST[$key];

					$page_id = get_option("vw_2vc_page_room");
				if ($page_id != '-1' && $options['disablePage']!='0') VWvideoChat::deletePages();

				update_option('VWvideoChatOptions', $options);
			}

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'server';

?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>VideoWhisper Webcam 2 Way VideoChat Settings</h2>
</div>

<h2 class="nav-tab-wrapper">
	<a href="options-general.php?page=webcam-2way-videochat.php&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>"><?php _e('Server','vw2wvc'); ?></a>
	<a href="options-general.php?page=webcam-2way-videochat.php&tab=setup" class="nav-tab <?php echo $active_tab=='setup'?'nav-tab-active':'';?>"><?php _e('Room Setup & Access','vw2wvc'); ?></a>
    <a href="options-general.php?page=webcam-2way-videochat.php&tab=video" class="nav-tab <?php echo $active_tab=='video'?'nav-tab-active':'';?>"><?php _e('Video','vw2wvc'); ?></a>
	<a href="options-general.php?page=webcam-2way-videochat.php&tab=integration" class="nav-tab <?php echo $active_tab=='integration'?'nav-tab-active':'';?>"><?php _e('Integration','vw2wvc'); ?></a>
	<a href="options-general.php?page=webcam-2way-videochat.php&tab=app" class="nav-tab <?php echo $active_tab=='app'?'nav-tab-active':'';?>"><?php _e('Mobile App','vw2wvc'); ?></a>

</h2>

<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<?php
			switch ($active_tab)
			{
			case 'server':
?>
<h3>Streaming Server</h3>
<h4>RTMP Address</h4>
<p><?php _e('To run this, make sure your hosting environment meets all <a href="https://videowhisper.com/?p=Requirements" target="_blank">requirements</a>.  If you do not have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="https://videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application Setup</a> for  installation details.','vw2wvc'); ?></p>
<input name="rtmp_server" type="text" id="rtmp_server" size="80" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>*
<br><?php _e('This is usually the only setting you need to change to run this plugin.','vw2wvc'); ?>

<h4>Disable Bandwidth Detection</h4>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h4>Web Key</h4>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="http://www.videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a>.
<?php
				$root_ajax = admin_url( 'admin-ajax.php?action=v2wvc&task=');
				//$root_url = plugins_url() . "/webcam-2way-videochat/2wvc/";
				echo "<BR>webLogin: $root_ajax"."rtmp_login&s=";
				echo "<BR>webLogout: $root_ajax"."rtmp_logout&s=";
?>


<h4>RTMFP Address</h4>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<h4>P2P Group</h4>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>
<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<h4>Support P2P Streaming</h4>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>
<br>Although application has option to detect and also allow manual toggle between server and p2p stream, on most cases P2P should be disabled.
<br>P2P is highly dependant on client network and ISP restrictions. Often results in video streaming failure or huge latency.
P2P may be suitable when all clients are in same network or broadcasters have server grade connection (with high upload and dedicated public IP accessible externally). Also P2P does not work for mobile clients.
<?php
				break;

			case 'setup':
				//! room setup & access
?>
<h3><?php _e('Room Setup & Access','vw2wvc'); ?></h3>
<h4><?php _e('Who can create rooms','vw2wvc'); ?></h4>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?php echo $options['canBroadcast']=='members'?"selected":""?>><?php _e('All Members','vw2wvc'); ?></option>
  <option value="list" <?php echo $options['canBroadcast']=='list'?"selected":""?>><?php _e('Members in List','vw2wvc'); ?> *</option>
</select>
<h4>* <?php _e('Members in List: allowed to broadcast video (comma separated user names, roles, emails, IDs)','vw2wvc'); ?></h4>
<textarea name="broadcastList" cols="64" rows="3" id="broadcastList"><?php echo $options['broadcastList']?>
</textarea>

<h4>Auto Create a Room for Each User</h4>
<select name="autoRoom" id="autoRoom">
  <option value="0" <?php echo $options['autoRoom']?"":"selected"?>><?php _e('Disabled','vw2wvc'); ?></option>
  <option value="register" <?php echo $options['autoRoom']=='register'?"selected":""?>><?php _e('On Registration','vw2wvc'); ?></option>
  <option value="login" <?php echo $options['autoRoom']=='login'?"selected":""?>><?php _e('On Login','vw2wvc'); ?></option>
  <option value="manage" <?php echo $options['autoRoom']=='manage'?"selected":""?>><?php _e('On Management Page','vw2wvc'); ?></option>
  <option value="always" <?php echo $options['autoRoom']=='always'?"selected":""?>><?php _e('Always','vw2wvc'); ?></option>
</select>
<br>Automatically creates a room for each user with same name as user. Only creates rooms for users with this ability configured above (even on registration).
<br>Recommended: Disabled or on management page to avoid creating many rooms for users that don't need this functionality.

<h4>Maximum Rooms</h4>
<input name="maxRooms" type="text" id="maxRooms" size="3" maxlength="6" value="<?php echo $options['maxRooms']?>"/>
<br>Maximum number of videochat rooms each user can create. Set 0 for unlimited.

<h3><?php _e('Participants','vw2wvc'); ?></h3>
<h4><?php _e('Who can enter videochat','vw2wvc'); ?></h4>
<select name="canWatch" id="canWatch">
  <option value="all" <?php echo $options['canWatch']=='all'?"selected":""?>><?php _e('Anybody','vw2wvc'); ?></option>
  <option value="members" <?php echo $options['canWatch']=='members'?"selected":""?>><?php _e('All Members','vw2wvc'); ?></option>
  <option value="list" <?php echo $options['canWatch']=='list'?"selected":""?>><?php _e('Members in List','vw2wvc'); ?> *</option>
</select>
<h4>* <?php _e('Members in List: Allowed to participate (comma separated user names, roles, emails, IDs)','vw2wvc'); ?></h4>
<textarea name="watchList" cols="64" rows="3" id="watchList"><?php echo $options['watchList']?>
</textarea>
<?php
				break;

			case 'integration':
				//! Integration
				$options['welcome'] = htmlentities(stripslashes($options['welcome']));
				$options['layoutCode'] = htmlentities(stripslashes($options['layoutCode']));
				$options['parameters'] = htmlentities(stripslashes($options['parameters']));
				$options['translationCode'] = htmlentities(stripslashes($options['translationCode']));

?>
<h3>Integration Settings</h3>



<h4>Redirect after Login</h4>
<select name="loginRedirect" id="loginRedirect">
   <option value="<?php echo $pid=get_option("vw_2vc_page_room"); ?>" <?php echo ($options['loginRedirect'] == $pid)?"selected":""?>>Videochat Setup (#<?php echo $pid?>)</option>
    <option value="<?php echo $pid=get_option( 'page_on_front' ); ?>" <?php echo ($options['loginRedirect'] == $pid)?"selected":""?>>Front Page (#<?php echo $pid?>)</option>
    <option value="<?php echo $pid=get_option( 'page_for_posts' ); ?>" <?php echo ($options['loginRedirect'] == $pid)?"selected":""?>>Posts Page (#<?php echo $pid?>)</option>
  <option value="0" <?php echo (!$options['loginRedirect'])?"selected":""?>>Default (#0)</option>
</select>
<br>Redirect users (except admins), after login, to a frontend section.

<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (Username)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename</option>
</select>

<h4>Page</h4>
<p>Add videochat management page (Page ID <a href='post.php?post=<?php echo get_option("vw_2vc_page_room"); ?>&action=edit'><?php echo get_option("vw_2vc_page_room"); ?></a>) with shortcode [videowhisper_videochat_manage]</p>
<select name="disablePage" id="disablePage">
  <option value="0" <?php echo $options['disablePage']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePage']=='1'?"selected":""?>>No</option>
</select>


<h4>Room Link Type</h4>
<select name="roomLink" id="roomLink">
  <option value="rewrite" <?php echo $options['roomLink']=='rewrite'?"selected":""?>>Rewrite (/videochat/$Room)</option>
  <option value="plugin" <?php echo $options['roomLink']=='plugin'?"selected":""?>>Plugin (wp-content/plugins...)</option>
</select>
<BR>If rewrite doesn't work, try updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>).

<h4>Welcome Message</h4>
<textarea name="welcome" id="welcome" cols="64" rows="8"><?php echo $options['welcome']?></textarea>
<br>Shows in chatbox when entering video chat.

<h4>Custom Layout Code</h4>
<textarea name="layoutCode" id="layoutCode" cols="64" rows="8"><?php echo $options['layoutCode']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in chat (contains graphic element positions). Copy and paste code here.

<h4><?php _e('Application Translation Code','vw2wvc'); ?></h4>
<textarea name="translationCode" id="translationCode" cols="64" rows="8"><?php echo $options['translationCode']?></textarea>
<br><?php _e('Generate by writing and sending "/videowhisper translation" in application chat (contains xml tags with text and translation attributes). Texts are added to list only after being shown once in interface. If any texts do not show up in generated list you can manually add new entries for these.','vw2wvc'); ?>

<h4>Parameters</h4>
<textarea name="parameters" id="parameters" cols="64" rows="8"><?php echo $options['parameters']?></textarea>
<br>Documented on <a href="https://videowhisper.com/?p=PHP+2+Way+Video+Chat#customize">PHP 2 Way Video Chat</a> edition page.
<br>Recommended low latency buffering: 0 (s)
<BR>When using H.264-encoded video, any buffer setting greater than zero may introduce a latency of at least 2 to 3 seconds with video encoded at 30 fps, and even higher at lower frame rates. Although zero gives you the best possible latency, it might not give you the smoothest playback. So you may need to increase the buffer time to a value slightly greater than zero (such as .1 or .25) and use H.263 video codec.

<h4>Session Expiration</h4>
<input name="sessionExpire" type="text" id="sessionExpire" size="3" maxlength="6" value="<?php echo $options['sessionExpire']?>"/>s
<br>Session expiration time. After this time of absence online session is deleted (user no longer online). Should be bigger that interval between status updates.

<h4>Show VideoWhisper Powered by</h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>

<h4>Uploads Path</h4>
<p>Path where logs and snapshots will be uploaded. You can use a location outside plugin folder to avoid losing logs on updates and plugin uninstallation.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<?php
				echo '<br>WordPress Path: ' . get_home_path();
				if (!strstr($options['uploadsPath'], get_home_path() )) echo '<br><b>Warning: Uploaded files may not be accessible by web.</b>';
				echo '<br>WordPress URL: ' . get_site_url();
?>
<br>wp_upload_dir()['basedir'] : <?php $wud= wp_upload_dir(); echo $wud['basedir'] ?>
<br>$_SERVER['DOCUMENT_ROOT'] : <?php echo $_SERVER['DOCUMENT_ROOT'] ?>

<h4>Ad Server</h4>
<p>URL to serve ads from in chatbox. See <a href='http://adinchat.com/'>Ad in Chat</a> ads server and rotator.</p>
<input name="adServer" type="text" id="adServer" size="80" maxlength="256" value="<?php echo $options['adServer']?>"/>
<?php
				break;

			case 'app':
				// !App
				$options['eula_txt'] = htmlentities(stripslashes($options['eula_txt']));
				$options['crossdomain_xml'] = htmlentities(stripslashes($options['crossdomain_xml']));

				$options['layoutCodeMobile'] = htmlentities(stripslashes($options['layoutCodeMobile']));
				$options['parametersMobile'] = htmlentities(stripslashes($options['parametersMobile']));

				$eula_url = site_url() . '/eula.txt';
				$crossdomain_url = site_url() . '/crossdomain.xml';
?>
<h3>Application Settings</h3>
<p>This section is for configuring settings related to remote apps (iOS/Android/Desktop) that can be used in combination with this web based solution. Such apps can be <a href="https://videowhisper.com/?p=iPhone-iPad-Apps">custom made</a> for each site.</p>

<h4>Enable App Links</h4>
<select name="appEnabled" id="appEnabled">
  <option value="0" <?php echo $options['appEnabled']=='0'?"selected":""?>>No</option>
  <option value="1" <?php echo $options['appEnabled']=='1'?"selected":""?>>Yes</option>
</select>
<BR> Show app links to owner and on room page if iOS/Android detected.

<h4>App Schema</h4>
<input name="appSchema" type="text" id="appSchema" size="16" maxlength="32" value="<?php echo $options['appSchema']?>"/>
<BR>URL schema used to open app if installed on user's device. Ex. "vw2wvc" will link to room Lobby as vw2wvc://call?room=Lobby

<h4>Custom Layout Code</h4>
<p>App uses a special skin from 2wvc/templates/2wvc-app/ folder. By default graphics are provided for a 16:9 1920x1080 layout and app resizes elements depending on available device screen space.</p>
<textarea name="layoutCodeMobile" id="layoutCodeMobile" cols="100" rows="8"><?php echo $options['layoutCodeMobile']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in chat (contains graphic element positions). Copy and paste code here.

<h4>Parameters</h4>
<textarea name="parametersMobile" id="parametersMobile" cols="100" rows="8"><?php echo $options['parametersMobile']?></textarea>
<br>Documented on <a href="https://videowhisper.com/?p=PHP+2+Way+Video+Chat#customize">PHP 2 Way Video Chat</a> edition page.

<h4><?php _e('End User License Agreement','vw2wvc'); ?></h4>
<textarea name="eula_txt" id="eula_txt" cols="100" rows="8"><?php echo $options['eula_txt']?></textarea>
<br>Users are required to accept this agreement before registering from app.
<br>After updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>) this should become available as <a href="<?php echo $eula_url ?>"><?php echo $eula_url ?></a>. This works if file doesn't already exist. You can also create the file for faster serving.

<h4><?php _e('Cross Domain Policy','vw2wvc'); ?></h4>
<textarea name="crossdomain_xml" id="crossdomain_xml" cols="100" rows="4"><?php echo $options['crossdomain_xml']?></textarea>
<br>This is required for applications to access interface and scripts on site.
<br>After updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>) this should become available as <a href="<?php echo $crossdomain_url ?>"><?php echo $crossdomain_url ?></a>. This works if file doesn't already exist. You can also create the file for faster serving.
<?php
				break;

			case 'video':
?>
<h3>Streaming Settings</h3>

<h4>Video Stream Size</h4>
<input name="camBandwidth" type="text" id="camBandwidth" size="8" maxlength="8" value="<?php echo $options['camBandwidth']?>"/> (bytes/s) Higher bandwidth means higher quality but must be supported by client connection.
<h4>Maximum Video Stream Size</h4>
<input name="camMaxBandwidth" type="text" id="camMaxBandwidth" size="8" maxlength="8" value="<?php echo $options['camMaxBandwidth']?>"/> (bytes/s) Maximum bandwidth that can be configured at runtime.

<h4>Video Codec</h4>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?php echo $options['videoCodec']=='H264'?"selected":""?>>H.264</option>
  <option value="H263" <?php echo $options['videoCodec']=='H263'?"selected":""?>>H.263</option>
</select>
<BR>H.263 may produce better latency and allow buffering adjustments for smooth playback.
<BR>H.264 will produce better quality per bitrate but may introduce 2-3 seconds latency for any buffering different than 0.

<h4>H264 Video Codec Profile</h4>
<select name="codecProfile" id="codecProfile">
  <option value="main" <?php echo $options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?php echo $options['codecProfile']=='baseline'?"selected":""?>>baseline</option>
</select>

<h4>H264 Video Codec Level</h4>
<input name="codecLevel" type="text" id="codecLevel" size="32" maxlength="64" value="<?php echo $options['codecLevel']?>"/> (1, 1b, 1.1, 1.2, 1.3, 2, 2.1, 2.2, 3, 3.1, 3.2, 4, 4.1, 4.2, 5, 5.1)

<h4>Sound Codec</h4>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?php echo $options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>

<h4>Speex Sound Quality</h4>
<input name="soundQuality" type="text" id="soundQuality" size="3" maxlength="3" value="<?php echo $options['soundQuality']?>"/> (0-10)

<h4>Nellymoser Sound Rate</h4>
<input name="micRate" type="text" id="micRate" size="3" maxlength="3" value="<?php echo $options['micRate']?>"/> (11/22/44)

<?php
				break;

			}

			submit_button();
?>


</form>


	 <?php
		}

	}
}

//instantiate
if (class_exists("VWvideoChat"))
{
	$videoChat = new VWvideoChat();
}

//Actions and Filters
if (isset($videoChat))
{
	add_action("plugins_loaded", array(&$videoChat, 'plugins_loaded'));
	add_action('admin_menu', array(&$videoChat, 'menu'));

	add_action( 'init', array(&$videoChat, 'init'));
	add_filter( 'query_vars', array(&$videoChat, 'query_vars'));
	add_action( 'parse_request', array(&$videoChat, 'parse_request'));

	add_filter( 'login_redirect', array('VWvideoChat','login_redirect'), 10, 3 );

	add_action( 'user_register', array('VWvideoChat','user_register'), 10, 1 );

	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
}



?>
