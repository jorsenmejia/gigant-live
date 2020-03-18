<?php
namespace VideoWhisper\LiveWebcams;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//ini_set('display_errors', 1);

trait Options {
//define and edit settings

		
		function getOptions()
		{
			$options = get_option('VWliveWebcamsOptions');
			if (!$options) $options =  self::adminOptionsDefault();
			
			return $options;
		}
		
				//! Options

		static function adminOptionsDefault()
		{
			$upload_dir = wp_upload_dir();
			$root_url = plugins_url();
			$root_ajax = admin_url( 'admin-ajax.php?action=vmls&task=');

			return array(
				
				'messages' => 1,
				'messagesCost' => '5.00',
				
				'whitelabel' => 0,
				
				'appOptionsReset' => 0,
				'appOptions' => 1,

				'appComplexity' => 2,
				'appSiteMenu' => 0,
				'templateTypes' => 'page, post, channel, webcam, conference, presentation, videochat, video, picture, download',
				
				'geoBlocking' => '',
				'geoBlockingMessage' => 'This type of content is forbidden in your location.',
			
				'videochatNext' => '1',
				'videochatNextPaid' => '1',
				'videochatNextOnline' => '0',
				'videochatNextPool' => 32,

				'interfaceClass' => '',

				'layoutDefault' => 'grid',
				'corsACLO' =>'',

				'debugMode' => '0',
				'teaserOffline' => '0',

				'balancePage' => '',

				'rtmp_restrict_ip'=>'',
				'webStatus'=> 'auto',

				'balanceWarn1Amount' => '9',
				'balanceWarn1Message' => 'Warning: Your balance is low!',
				'balanceWarn1Sound' => 'warning',
				'balanceWarn2Amount' => '4',
				'balanceWarn2Message' => 'Warning: Your balance is critical! ',
				'balanceWarn2Sound' => 'critical',

				'privateSnapshots' => '1',
				'privateSnapshotsInterval' => '60',

				'performerProfile' => get_site_url() . '/author/',
				'webcamLink' => 'room',
				'webcamLinkDefault' => get_site_url() . '/webcam/',

				'freeTimeLimit' => '21600',
				'freeTimeLimitVisitor' => '1200',

				'webfilter' => '0',

				'videosharevod' => '1',
				'picturegallery' => '1',
				'rateStarReview' => '1',

				'filterRegex' => '(?i)(arsehole|fuck|cunt|shit)(?-i)',
				'filterReplace' => '*',


				'custom_post' => 'webcam',
				'postTemplate' => '+plugin',

				'disableSetupPages' => '0',
				'registrationFormRole' => '1',

				'roleClient' => 'client',
				'rolePerformer' => 'performer',
				'roleStudio' => 'studio',

				'studios' => '0',
				'studioPerformers' => '20',
				'studioWebcams' => '50',

				'performerWebcams' => '3',

				'userName' => 'user_login',
				'webcamName' => 'user_login',

				'thumbWidth' => '240',
				'thumbHeight' => '180',
				'perPage' =>'5',

				'wallet' =>'MyCred',
				'walletMulti'=>'2',

				'ppvGraceTime' => '30',
				'ppvPPM' => '0.50',
				'ppvPPMmin' => '0.10',
				'ppvPPMmax' => '5.00',

				'autoBalanceLimits' => 1,
				'ppvRatio' => '0.80',
				'ppvPerformerPPM' => '0.00',
				'ppvMinimum' => '1.50',
				'ppvMinInShow' => '0.30',

				'ppvCloseAfter' => '120',
				'ppvBillAfter' => '10',
				'ppvKeepLogs' => '31536000',
				'onlineTimeout' => '60',

				'rtmp_server' => 'rtmp://[your-rtmp-server-ip-or-domain]/videowhisper-chat',
				'rtmp_server_archive' => 'rtmp://[your-rtmp-server-ip-or-domain]/videowhisper-archive',
				'rtmp_server_record' => 'rtmp://[your-rtmp-server-ip-or-domain]/videowhisper-record',

				'rtmp_server_admin' => 'rtmp://[your-rtmp-server-ip-or-domain]/videowhisper-chat',

				'presentation' => '0',

				'rtmp_amf' => 'AMF3',

				'canWatch' => 'all',
				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber, Client, Student, Member',

				'performerOffline' => 'warn',
				'performerOfflineMessage' => '<H4>Performer is currently offline.</h4> Access is enabled: you can enter public room and wait for performer.',
				'viewersCount' => '1',
				'salesCount' => '1',

				'loaderGIF' => dirname(plugin_dir_url(__FILE__)) .'/videowhisper/loading.gif',

				'overLogo' => dirname(plugin_dir_url(__FILE__)) .'/videowhisper/logo.png',
				'overLink' => 'https://videowhisper.com',

				'loginLogo' => dirname(plugin_dir_url(__FILE__)) .'/videowhisper/pvc-logo-w.png',

				'tokenKey' => 'VideoWhisper',
				'webKey' => 'VideoWhisper',

				'multicamMax' => '2',
				'transcoding' => '0',
				'transcodingAuto' => '2',
				'htmlchat' => '1',
				'htmlchatTest' => '1',
				'externalKeys' => '1',
				'externalKeysTranscoder' => '1',

				'rtmp_server_hls' => 'rtmp://[your-rtmp-server-ip-or-domain]/videowhisper-x',
				'httpstreamer' => 'http://[your-rtmp-server-ip-or-domain]:1935/videowhisper-x/',
				'hls_vod' => '',

				'ffmpegPath' => '/usr/local/bin/ffmpeg',
				'ffmpegConfiguration' => '1',
				'ffmpegTranscode' => '-analyzeduration 0 -vcodec copy -acodec libfaac -ac 2 -ar 22050 -ab 96k',
				'transcodeRTC' => '0',
				'transcodeFromRTC' => '0',	
				'ffmpegTranscodeRTC' => '-c:v copy -c:a libopus', //transcode for RTC like ffmpeg -re -i source -acodec opus -vcodec libx264 -vprofile baseline -f rtsp rtsp://<wowza-instance>/rtsp-to-webrtc/my-stream //-c:v libx264 -profile:v baseline -c:a libopus

				'rtsp_server' => 'rtsp://[your-server]/videowhisper-x', //access WebRTC stream with sound from here
				'rtsp_server_publish' => 'rtsp://[user:password@][your-server]/videowhisper-x', //publish WebRTC stream here

				'webrtc' =>'0', //enable webrtc
				'wsURLWebRTC' => 'wss://[wowza-server-with-ssl]:[port]/webrtc-session.json', // Wowza WebRTC WebSocket URL (wss with SSL certificate)
				'applicationWebRTC' => '[application-name]', // Wowza Application Name (configured or WebRTC usage)

				'webrtcVideoCodec' =>'VP8',
				'webrtcAudioCodec' =>'opus',

				'webrtcVideoBitrate' => 1000,
				'webrtcAudioBitrate' => 64,

				'rtmp_restrict_ip'=>'',
				'webStatus'=> 'auto',

				'unoconvPath' => '/usr/bin/unoconv',
				'convertPath' => '/usr/bin/convert',

				'detect_hls' => 'ios',
				'detect_mpeg' => 'android',

				'transcodeShows' => '0',
				'rtmpSnapshotRest' => '30',

				'processTimeout' => '90',

				'streamsPath' => '/home/account/public_html/streams',
				'playlists' =>'0',


				'serverRTMFP' => 'rtmfp://stratus.adobe.com/f1533cc06e4de4b56399b10d-1a624022ff71/',
				'p2pGroup' => 'VideoWhisper',
				'supportRTMP' => '1',
				'supportP2P' => '0',
				'alwaysRTMP' => '1',
				'alwaysP2P' => '0',

				'disableBandwidthDetection' => '1',

				'videowhisper' => 0,

				'uploadsPath' => $upload_dir['basedir'] . '/vw-webcams',
				'adServer' => $root_ajax .'m_ads',

				'appSetup' => unserialize('a:2:{s:6:"Config";a:5:{s:8:"darkMode";s:0:"";s:7:"tabMenu";s:4:"full";s:19:"cameraAutoBroadcast";s:1:"1";s:14:"cameraControls";s:1:"1";s:13:"videoAutoPlay";s:0:"";}s:4:"Room";a:6:{s:16:"requests_disable";s:0:"";s:12:"room_private";s:0:"";s:10:"room_audio";s:0:"";s:15:"room_conference";s:0:"";s:10:"room_slots";s:1:"4";s:19:"vw_presentationMode";s:0:"";}}'),				
				'appSetupConfig' => '
; This configures HTML5 Videochat application and other apps that use same API.		

[Config]
darkMode = false 			; true/false : start app in dark mode
tabMenu = full 				; icon/text/full : menu type for tabs (in advanced/collaboration mode)
cameraAutoBroadcast = true	; true/false : start broadcast automatically
cameraControls = true 		; true/false : broadcast control panel	
videoAutoPlay = false 		; true/false : try to play video without broadcaster notification

[Room]						; Defaults for room options.
requests_disable = false	; true/false : Disable users from sending private call requests to room owner.
room_private = false		; true/false : Hide room from public listings. Can be accessed by room link.
room_audio = false      	; true/false : Audio only mode. Only microphone, no webcam video.
room_conference = false		; true/false : Enable owner to show multiple users streams at same time in split view. All users can publish webcam.
room_slots = 4				; 2/4/6 : Split display to show multiple media items, in conference mode.
vw_presentationMode = false ; true/false : Enable presentation and collaboration mode with multiple media, files, presentation.
				',

				'profileFields' => unserialize('a:10:{s:8:"About Me";a:2:{s:4:"type";s:8:"textarea";s:12:"instructions";s:29:"A few details about yourself.";}s:12:"Private Chat";a:2:{s:4:"type";s:8:"textarea";s:12:"instructions";s:41:"Describe what can you perform in private.";}s:8:"Schedule";a:2:{s:4:"type";s:8:"textarea";s:12:"instructions";s:36:"Describe when you plan to go online.";}s:9:"Languages";a:2:{s:4:"type";s:10:"checkboxes";s:7:"options";s:37:"English/Spanish/French/German/Italian";}s:9:"Interests";a:1:{s:4:"type";s:8:"textarea";}s:8:"Wishlist";a:1:{s:4:"type";s:8:"textarea";}s:6:"Gender";a:2:{s:4:"type";s:6:"select";s:7:"options";s:24:"Male/Female/Trans/Tester";}s:4:"Hair";a:2:{s:4:"type";s:10:"checkboxes";s:7:"options";s:46:"Long/Short/Blonde/Brunette/Readhead/Black hair";}s:3:"Age";a:2:{s:4:"type";s:6:"select";s:7:"options";s:21:"18-22/22-30/30-40/40+";}s:9:"Continent";a:2:{s:4:"type";s:6:"select";s:7:"options";s:56:"North America/South America/Europe/Asia/Africa/Australia";}}'),
				'profileFieldsConfig' => '
; This configures listing profile fields. Can contain comments that start with character ;

[About Me] 
type = textarea
instructions = A few details about yourself. 

[Private Chat] ; field name is defined in brackets
type = textarea ; type defines type of field: text/textarea/select/checkboxes
instructions = Describe what can you perform in private. ; instructions show user filling the form

[Schedule]
type = textarea
instructions = Describe when you plan to go online.

[Languages]
type = checkboxes ; multiple options can be selected at same time with checkboxes
options = English/Spanish/French/German/Italian ; separate options with character /

[Interests]
type = textarea

[Wishlist]
type = textarea

[Gender]
type = select ; only 1 option can be selected for select
options = Male/Female/Trans

[Hair]
type = checkboxes
options = Long/Short/Blonde/Brunette/Readhead/Black hair

[Age]
type = select
options = 18-22/22-30/30-40/40+

[Continent]
type = select
options = North America/South America/Europe/Asia/Africa/Australia
',
				'labels' => '',
				'labelsConfig' => '
All=All
Online=Online
Available=Online, Available
In Private=Online, In Private
Offline=Offline
',
				'groupModes' => unserialize('a:4:{s:9:"Free Chat";a:7:{s:3:"cpm";s:1:"0";s:4:"2way";s:1:"3";s:4:"cpm2";s:3:"0.5";s:6:"voyeur";s:1:"1";s:4:"cpmv";s:4:"0.25";s:9:"snapshots";s:1:"0";s:7:"archive";s:1:"0";}s:10:"Group Show";a:8:{s:3:"cpm";s:4:"1.50";s:4:"2way";s:1:"2";s:4:"cpm2";s:3:"2.0";s:6:"voyeur";s:1:"1";s:4:"cpmv";s:4:"1.75";s:9:"snapshots";s:1:"0";s:7:"archive";s:1:"0";s:13:"archiveImport";s:1:"0";}s:12:"Topless Show";a:9:{s:3:"cpm";s:4:"2.50";s:4:"2way";s:1:"2";s:4:"cpm2";s:3:"3.0";s:6:"voyeur";s:1:"1";s:4:"cpmv";s:4:"2.75";s:9:"snapshots";s:1:"1";s:17:"snapshotsInterval";s:3:"180";s:7:"archive";s:1:"0";s:13:"archiveImport";s:1:"0";}s:9:"Nude Show";a:9:{s:3:"cpm";s:4:"3.50";s:4:"2way";s:1:"2";s:4:"cpm2";s:3:"4.0";s:6:"voyeur";s:1:"1";s:4:"cpmv";s:4:"3.75";s:9:"snapshots";s:1:"1";s:17:"snapshotsInterval";s:3:"120";s:7:"archive";s:1:"1";s:13:"archiveImport";s:1:"1";}}'),
				'groupModesConfig' => '
[Free Chat]
cpm=0
2way=3
cpm2=0.5
voyeur=1
cpmv=0.25
snapshots=0
archive=0

[Group Show]
cpm=1.50
2way=2
cpm2=2.0
voyeur=1
cpmv=1.75
snapshots=0
archive=0
archiveImport=0

[Topless Show]
cpm=2.50
2way=2
cpm2=3.0
voyeur=1
cpmv=2.75
snapshots=1
snapshotsInterval=180
archive=0
archiveImport=0

[Nude Show]
cpm=3.50
2way=2
cpm2=4.0
voyeur=1
cpmv=3.75
snapshots=1
snapshotsInterval=120
archive=1
archiveImport=1

',
				'groupGraceTime' => '30',
				'voyeurAvailable' => 'always',

				'currency'=>'tk$',
				'currencypm'=>'tk$/m',
				'currencyLong' => 'tokens',

				'recordFields' =>unserialize('a:7:{s:9:"Full Name";a:2:{s:4:"type";s:4:"text";s:12:"instructions";s:15:"Real full name.";}s:13:"Date of Birth";a:1:{s:4:"type";s:4:"text";}s:18:"ID Type and Number";a:2:{s:4:"type";s:4:"text";s:12:"instructions";s:59:"The type and number for national ID providing proof of age.";}s:7:"Address";a:2:{s:4:"type";s:8:"textarea";s:12:"instructions";s:16:"Current address.";}s:12:"Phone Number";a:2:{s:4:"type";s:4:"text";s:12:"instructions";s:138:"Phone number where you can be reached if further details, verifications are required. Can be used to verify identity for account recovery.";}s:13:"Payout Method";a:2:{s:4:"type";s:6:"select";s:7:"options";s:37:"Paypal/Bitcoin/Ethereum/Litecoin/Hold";}s:14:"Payout Details";a:2:{s:4:"type";s:8:"textarea";s:12:"instructions";s:58:"\Details where payment should be sent (email or address).\";}}'),
				'recordFieldsConfig' =>'
[Full Name]
type = text
instructions = Real full name.

[Date of Birth]
type = text

[ID Type and Number]
type = text
instructions = The type and number for national ID providing proof of age.

[Address]
type = textarea
instructions = Current address.

[Phone Number]
type = text
instructions = Phone number where you can be reached if further details, verifications are required. Can be used to verify identity for account recovery.

[Payout Method]
type = select
options = Paypal/Bitcoin/Ethereum/Litecoin/Hold

[Payout Details]
type = textarea
instructions = "Details where payment should be sent (email or address)."
',

				'unverifiedPerformer' => 1,
				'unverifiedStudio' => 1,

				'tips' => 1,
				'tipRatio' => '0.90',
				'tipCooldown'=> '15',
				'tipOptions' => '<tips>
<tip amount="1" label="1$ Tip" note="Like!" sound="coins1.mp3" image="gift1.png" color="#33FF33"/>
<tip amount="2" label="2$ Tip" note="Big Like!" sound="coins2.mp3" image="gift2.png" color="#33FF33"/>
<tip amount="5" label="5$ Gift" note="Great!" sound="coins2.mp3" image="gift3.png" color="#33FF33"/>
<tip amount="10" label="10$ Gift" note="Excellent!" sound="register.mp3" image="gift4.png" color="#33FF33"/>
<tip amount="20" label="20$ Gift" note="Ultimate!" sound="register.mp3" image="gift5.png"  color="#33FF33"/>
<tip amount="custom" label="Custom Tip!" note="Custom Tip" sound="coins1.mp3" image="gift1.png" color="#33FF33"/>
</tips>',

				'dashboardMessage' => 'This is the performer dashboard area. This HTML content section can be edited from plugin settings and can include announcements, instructions, links to help. Can include HTML code: <a href="https://videowhisper.com/tickets_submit.php">Contact VideoWhisper</a>.',

				'dashboardMessageBottom' => 'This is the performer dashboard area, bottom section. This HTML content section can be edited from plugin settings and can include announcements, instructions, links to help.',

				'dashboardMessageStudio' => 'This is the studio dashboard area. This HTML content section can be edited from plugin settings and can include announcements, instructions, links to help.',

				'welcomePerformer' => 'Welcome to your performer room!',
				'welcomeClient' => 'Welcome!',

				'parametersPerformer' => '&usersEnabled=1&chatEnabled=1&videoEnabled=1&webcamSupported=1&webcamEnabled=1&toolbarEnabled=1&removeChatOnPrivate=0&requestPrivate=0&assignedPrivate=0&directPrivate=0&canHide=1&canDenyAll=1&hideOnPrivate=1&soundNotifications=1&camWidth=640&camHeight=480&camFPS=30&camBandwidth=75000&videoCodec=H264&codecProfile=main&codecLevel=3.1&soundCodec=Nellymoser&micRate=22&soundQuality=9&bufferLive=0.2&bufferFull=0.2&bufferLivePlayback=0.2&bufferFullPlayback=0.2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=250000&disableBandwidthDetection=0&disableUploadDetection=0&limitByBandwidth=1&configureSource=0&fillWindow=0&disableVideo=0&disableSound=0&floodProtection=2&writeText=1&statusInterval=30000&statusPrivateInterval=10000&externalInterval=9500&verboseLevel=2&loaderProgress=0&selectCam=1&selectMic=1&asYouType=1&disableEmoticons=0&presentation=0&restorePaused=1',

				'parametersPerformerPresentation' => '&usersEnabled=1&chatEnabled=1&videoEnabled=1&webcamSupported=1&webcamEnabled=1&toolbarEnabled=1&removeChatOnPrivate=0&requestPrivate=0&assignedPrivate=0&directPrivate=0&canHide=1&canDenyAll=1&hideOnPrivate=1&soundNotifications=1&camWidth=640&camHeight=480&camFPS=30&camBandwidth=75000&videoCodec=H264&codecProfile=main&codecLevel=3.1&soundCodec=Nellymoser&micRate=22&soundQuality=9&bufferLive=0.2&bufferFull=0.2&bufferLivePlayback=0.2&bufferFullPlayback=0.2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=250000&disableBandwidthDetection=0&disableUploadDetection=0&limitByBandwidth=1&configureSource=0&fillWindow=0&disableVideo=0&disableSound=0&floodProtection=2&writeText=1&statusInterval=30000&statusPrivateInterval=10000&externalInterval=9500&verboseLevel=2&loaderProgress=0&selectCam=1&selectMic=1&asYouType=1&disableEmoticons=0&presentation=1&videoControl=1&videoRecorder=0&webcamSlides=0&slideComments=1&files_enabled=1&regularWatch=1&slideShow=1&publicVideosAdd=1&file_delete=1&file_upload=1&internalOpen=0&change_background=1&externalStream=1&writeAnnotations=1&editAnnotations=1&restorePaused=0',


				'parametersClient' => '&usersEnabled=1&chatEnabled=1&videoEnabled=1&webcamSupported=1&webcamEnabled=0&toolbarEnabled=1&webcamOnPrivate=1&removeChatOnPrivate=1&removeVideoOnPrivate=1&removeUsersOnPrivate=1&maximizePrivate=1&assignedPrivate=1&requestPrivate=0&directPrivate=0&canHide=0&canDenyAll=0&hideOnPrivate=0&soundNotifications=0&camWidth=480&camHeight=360&camFPS=25&camBandwidth=50000&videoCodec=H264&codecProfile=main&codecLevel=3.1&soundCodec=Nellymoser&micRate=22&soundQuality=9&bufferLive=0.2&bufferFull=0.2&bufferLivePlayback=0.2&bufferFullPlayback=0.2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&disableBandwidthDetection=0&disableUploadDetection=0&limitByBandwidth=1&configureSource=0&fillWindow=0&disableVideo=0&disableSound=0&floodProtection=3&writeText=1&statusInterval=60000&statusPrivateInterval=10000&externalInterval=11500&verboseLevel=2&loaderProgress=0&asYouType=1&disableEmoticons=0&presentation=0&room_limit=1000&showMenu=1',


				'parametersClientPresentation' => '&usersEnabled=1&chatEnabled=1&videoEnabled=1&webcamSupported=1&webcamEnabled=0&toolbarEnabled=1&webcamOnPrivate=1&removeChatOnPrivate=1&removeVideoOnPrivate=1&removeUsersOnPrivate=1&maximizePrivate=1&assignedPrivate=1&requestPrivate=0&directPrivate=0&canHide=0&canDenyAll=0&hideOnPrivate=0&soundNotifications=0&camWidth=480&camHeight=360&camFPS=25&camBandwidth=50000&videoCodec=H264&codecProfile=main&codecLevel=3.1&soundCodec=Nellymoser&micRate=22&soundQuality=9&bufferLive=0.2&bufferFull=0.2&bufferLivePlayback=0.2&bufferFullPlayback=0.2&showCamSettings=1&advancedCamSettings=1&camMaxBandwidth=81920&disableBandwidthDetection=0&disableUploadDetection=0&limitByBandwidth=1&configureSource=0&fillWindow=0&disableVideo=0&disableSound=0&floodProtection=3&writeText=1&statusInterval=60000&statusPrivateInterval=10000&externalInterval=11500&verboseLevel=2&loaderProgress=0&asYouType=1&disableEmoticons=0&presentation=1&room_limit=1000&videoControl=0&videoRecorder=0&webcamSlides=0&slideComments=1&files_enabled=1&regularWatch=0&slideShow=0&publicVideosAdd=0&file_delete=0&file_upload=0&internalOpen=0&change_background=0&externalStream=0&writeAnnotations=0&editAnnotations=0&showMenu=1',

				'parametersVideo' => '&bufferLive=0.2&bufferFull=0.2&showCredit=0&disconnectOnTimeout=1&offlineMessage=Webcam+Offline&statusInterval=30000&noSound=0',

				'layoutCodePerformer' => 'id=0&label=Users&x=747&y=2&width=205&height=218&resize=true&move=true; id=1&label=Chat&x=747&y=225&width=451&height=432&resize=true&move=true; id=2&label=RichMedia&x=2&y=2&width=741&height=457&resize=true&move=true; id=3&label=Webcam&x=956&y=2&width=240&height=219&resize=true&move=true',

				'layoutCodePerformerPresentation' => 'id=0&label=Users&x=590&y=5&width=410&height=200&system=absolute&px=35&py=0.5&pwidth=24.5&pheight=28.5&resize=true&move=true&title=;
id=1&label=Chat&x=590&y=210&width=630&height=270&system=absolute&px=35&py=30&pwidth=37.5&pheight=38.5&resize=true&move=true&title=Chat;
id=2&label=RichMedia&x=5&y=5&width=580&height=474&system=absolute&px=0.5&py=0.5&pwidth=34.5&pheight=67.5&resize=true&move=true&title=;
id=3&label=Webcam&x=1005&y=5&width=215&height=200&system=absolute&px=60&py=0.5&pwidth=13&pheight=28.5&resize=true&move=true&title=;
id=4&label=Files&x=910&y=485&width=310&height=175&system=absolute&px=54&py=69.5&pwidth=18.5&pheight=25&resize=true&move=true&title=Files;
id=5&label=Form&x=100&y=100&width=410&height=150&system=absolute&px=6&py=14.5&pwidth=24.5&pheight=21.5&resize=true&move=true&title=External;
id=6&label=Video&x=470&y=460&width=240&height=219&system=absolute&px=28&py=65.5&pwidth=14.5&pheight=31.5&resize=true&move=true&title=;
id=7&label=Slides&x=590&y=485&width=315&height=175&system=absolute&px=35&py=69.5&pwidth=19&pheight=25&resize=true&move=true&title=SlideShow;
id=8&label=Comments&x=5&y=485&width=580&height=175&system=absolute&px=0.5&py=69.5&pwidth=34.5&pheight=25&resize=true&move=true&title=Annotations;',

				'layoutCodeClient' => 'id=0&label=Users&x=820&y=5&width=375&height=200&resize=true&move=true; id=1&label=Chat&x=820&y=210&width=375&height=440&resize=true&move=true; id=2&label=RichMedia&x=5&y=5&width=810&height=646&resize=true&move=true; id=3&label=Webcam&x=840&y=20&width=240&height=219&resize=true&move=true',

				'layoutCodeClientPresentation' => 'id=0&label=Users&x=1060&y=10&width=375&height=340&system=absolute&px=63&py=1.5&pwidth=22.5&pheight=48.5&resize=true&move=true&title=;
id=1&label=Chat&x=680&y=10&width=375&height=430&system=absolute&px=40.5&py=1.5&pwidth=22.5&pheight=61.5&resize=true&move=true&title=;
id=2&label=RichMedia&x=10&y=10&width=665&height=538&system=absolute&px=0.5&py=1.5&pwidth=39.5&pheight=77&resize=true&move=true&title=;
id=3&label=Webcam&x=840&y=20&width=240&height=219&system=absolute&px=50&py=3&pwidth=14.5&pheight=31.5&resize=true&move=true&title=;
id=4&label=Files&x=1060&y=360&width=380&height=285&system=absolute&px=63&py=51.5&pwidth=22.5&pheight=40.5&resize=true&move=true&title=Files;
id=5&label=Video&x=470&y=460&width=240&height=219&system=absolute&px=28&py=65.5&pwidth=14.5&pheight=31.5&resize=true&move=true&title=;
id=6&label=Comments&x=680&y=450&width=375&height=190&system=absolute&px=40.5&py=64.5&pwidth=22.5&pheight=27&resize=true&move=true&title=;',


				'layoutCodeClient2way' => 'id=0&label=Users&x=820&y=5&width=375&height=200&resize=true&move=true; id=1&label=Chat&x=820&y=210&width=375&height=440&resize=true&move=true; id=2&label=RichMedia&x=5&y=5&width=810&height=646&resize=true&move=true; id=3&label=Webcam&x=840&y=20&width=240&height=219&resize=true&move=true',

				'layoutCodePrivatePerformer' => 'id=0&label=Users&x=747&y=2&width=205&height=218&resize=true&move=true; id=1&label=Chat&x=747&y=225&width=451&height=432&resize=true&move=true; id=2&label=RichMedia&x=2&y=2&width=741&height=457&resize=true&move=true; id=3&label=Webcam&x=956&y=2&width=240&height=219&resize=true&move=true',

				'layoutCodePrivateClient' => 'id=0&label=Users&x=820&y=5&width=375&height=200&resize=true&move=true; id=1&label=Chat&x=820&y=210&width=375&height=440&resize=true&move=true; id=2&label=RichMedia&x=5&y=5&width=810&height=646&resize=true&move=true; id=3&label=Webcam&x=840&y=20&width=240&height=219&resize=true&move=true',

				'layoutCodePrivateClient2way' => 'id=0&label=Users&x=820&y=5&width=375&height=200&resize=true&move=true; id=1&label=Chat&x=820&y=210&width=375&height=440&resize=true&move=true; id=2&label=RichMedia&x=5&y=5&width=810&height=646&resize=true&move=true; id=3&label=Webcam&x=840&y=20&width=240&height=219&resize=true&move=true',

				'translationCode' => '<translations>
  <t text="Chat session was terminated for this user:" translation="Chat session was terminated for this user:"/>
  <t text="Chat panel was closed by:" translation="Chat panel was closed by:"/>
  <t text="Close this panel from top right corner to return." translation="Close this panel from top right corner to return."/>
  <t text="Hello, do you want to chat with me?" translation="Hello, do you want to chat with me?"/>
  <t text="Yes, Hello!" translation="Yes, Hello!"/>
  <t text="No, I am very busy." translation="No, I am very busy."/>
  <t text="Waiting" translation="Waiting"/>
  <t text="Close" translation="Close"/>
  <t text="Toggle Enter and Leave Alerts" translation="Toggle Enter and Leave Alerts"/>
  <t text="Sound Effects" translation="Sound Effects"/>
  <t text="Talk" translation="Talk"/>
  <t text="Toggle Microphone" translation="Toggle Microphone"/>
  <t text="Sound Enabled" translation="Sound Enabled"/>
  <t text="Underline" translation="Underline"/>
  <t text="Ban" translation="Ban"/>
  <t text="Toggle Webcam" translation="Toggle Webcam"/>
  <t text="Apply Settings" translation="Apply Settings"/>
  <t text="Italic" translation="Italic"/>
  <t text="Forced" translation="Forced"/>
  <t text="Select Microphone Device" translation="Select Microphone Device"/>
  <t text="Tune Streaming Bandwidth" translation="Tune Streaming Bandwidth"/>
  <t text="Assigned" translation="Assigned"/>
  <t text="Broadcasting to server" translation="Broadcasting to server"/>
  <t text="Sound Disabled" translation="Sound Disabled"/>
  <t text="HD" translation="HD"/>
  <t text="High" translation="High"/>
  <t text="Toggle Preview Compression" translation="Toggle Preview Compression"/>
  <t text="Full Screen" translation="Full Screen"/>
  <t text="Preview Shows as Captured" translation="Preview Shows as Captured"/>
  <t text="Bold" translation="Bold"/>
  <t text="Server subscribers" translation="Server subscribers"/>
  <t text="Broadcasting to P2P group" translation="Broadcasting to P2P group"/>
  <t text="Change Volume" translation="Change Volume"/>
  <t text="Room Video" translation="Room Video"/>
  <t text="iPhone 4" translation="iPhone 4"/>
  <t text="P2P group subscribers" translation="P2P group subscribers"/>
  <t text="DVD NTSC" translation="DVD NTSC"/>
  <t text="CD" translation="CD"/>
  <t text="Kick" translation="Kick"/>
  <t text="Low Cam" translation="Low Cam"/>
  <t text="Mobile 4:3" translation="Mobile 4:3"/>
  <t text="Radio" translation="Radio"/>
  <t text="Username" translation="Username"/>
  <t text="Very High" translation="Very High"/>
  <t text="Send" translation="Send"/>
  <t text="Open Here" translation="Open Here"/>
  <t text="Public" translation="Public"/>
  <t text="iPhone 1-3" translation="iPhone 1-3"/>
  <t text="SD" translation="SD"/>
  <t text="DVD PAL" translation="DVD PAL"/>
  <t text="Users Online" translation="Users Online"/>
  <t text="HDTV" translation="HDTV"/>
  <t text="Pause Broadcast" translation="Pause Broadcast"/>
  <t text="iPhone 5" translation="iPhone 5"/>
  <t text="Web 16:9" translation="Web 16:9"/>
  <t text="4K" translation="4K"/>
  <t text="Low" translation="Low"/>
  <t text="Select Webcam Device" translation="Select Webcam Device"/>
  <t text="Hide" translation="Hide"/>
  <t text="Emoticons" translation="Emoticons"/>
  <t text="Toggle External Encoder" translation="Toggle External Encoder"/>
  <t text="Main Webcam" translation="Main Webcam"/>
  <t text="Auto Deny Requests" translation="Auto Deny Requests"/>
  <t text="New Camera" translation="New Camera"/>
  <t text="Open In Browser" translation="Open In Browser"/>
  <t text="Video is Disabled" translation="Video is Disabled"/>
  <t text="Sound Fx" translation="Sound Fx"/>
  <t text="Rate" translation="Rate"/>
  <t text="FullHD" translation="FullHD"/>
  <t text="Webcam" translation="Webcam"/>
  <t text="Framerate" translation="Framerate"/>
  <t text="Sound is Disabled" translation="Sound is Disabled"/>
  <t text="Please wait. Connecting..." translation="Please wait. Connecting..."/>
  <t text="Drag to move" translation="Drag to move"/>
  <t text="HDCAM" translation="HDCAM"/>
  <t text="Drag to resize" translation="Drag to resize"/>
  <t text="Public Chat" translation="Public Chat"/>
  <t text="Available" translation="Available"/>
  <t text="Cinema" translation="Cinema"/>
  <t text="Away" translation="Away"/>
  <t text="no" translation="no"/>
  <t text="Busy" translation="Busy"/>
  <t text="Resolution" translation="Resolution"/>
  <t text="iPad" translation="iPad"/>
</translations>',
				'listingTemplate' => '
			<div class="videowhisperWebcam #performerStatus# layoutGrid">
			
			<div class="videowhisperTitle">#name#</div>
			<div class="videowhisperTime">#banLink# #age#</div>

			<div class="videowhisperCPM">Private: #clientCPM# #currency#/m</div>

			<a href="#url#">#preview#</a>
			<div class="videowhisperBrief">#roomBrief#</div>
			<div class="videowhisperTags">#roomTags#</div>
			<div class="videowhisperCategory">#roomCategory#</div>

			<div class="videowhisperGroupMode">Mode: #groupMode#</div>
			<div class="videowhisperGroupCPM">#groupCPM# #currency#/m</div>
			<div class="videowhisperPerformers">#performers#</div>

			<div class="videowhisperRating">#rating#</div>
			#featured#
			#enter#
			</div>
			',
				'listingBig'=>0,
				'listingTemplate2' => '
			<div class="videowhisperWebcam2 #performerStatus#">
			
			<div class="videowhisperTitle">#name#</div>
			<div class="videowhisperTime">#banLink# #age#</div>

			<div class="videowhisperCPM">Private: #clientCPM# #currency#/m</div>

			<a href="#url#">#preview#</a>
			<div class="videowhisperBrief">#roomBrief#</div>
			<div class="videowhisperTags">#roomTags#</div>
			<div class="videowhisperCategory">#roomCategory#</div>

			<div class="videowhisperGroupMode">Mode: #groupMode#</div>
			<div class="videowhisperGroupCPM">#groupCPM# #currency#/m</div>
			<div class="videowhisperPerformers">#performers#</div>

			<div class="videowhisperRating">#rating#</div>
			#featured#
			#enter#
			</div>
			',
			'listingTemplateList' => '
			<div class="videowhisperWebcam #performerStatus# layoutList">
			
			<div class="videowhisperTitle">#name#</div>
			<div class="videowhisperTime">#banLink# #age#</div>

			<div class="videowhisperCPM">Private: #clientCPM# #currency#/m</div>

			<a href="#url#">#preview#</a>
			<div class="videowhisperBrief">#roomBrief#</div>
			<div class="videowhisperTags">#roomTags#</div>
			<div class="videowhisperCategory">#roomCategory#</div>

			<div class="videowhisperGroupMode">Mode: #groupMode#</div>
			<div class="videowhisperGroupCPM">#groupCPM# #currency#/m</div>
			<div class="videowhisperPerformers">#performers#</div>

			<div class="videowhisperRating">#rating#</div>
			#featured#
			#enter#
			<div class="videowhisperDescription">#roomDescription#</div>

			<div class="videowhisperFeaturedReview">#featuredReview#</div>

			</div>
			',
				'dashboardCSS' => <<<HTMLCODE
<style>
.vwtabs {
  margin: 10px auto 10px auto;
}

.vwtabs:after {
  clear: both;
  content: '';
  display: table;
}

.vwtabs .vwtab {
  display: inline;
}


.vwtabs  .vwlabel {
  background: #eee;
  border: 1px solid #eee;
  border-bottom: 2px solid #ccc;
  padding: 10px;
  position: relative;
  vertical-align: bottom;
  display: inline-block;
  top: 0px;
  font-size:17px;
  margin-right:-6px;
}


.vwtabs .vwtab > [type=radio] {
  clip: rect(0 0 0 0);
  height: 1px;
  opacity: 0;
  position: fixed;
  width: 1px;
  z-index: -1;
}

.vwtabs .vwpanel {
  display: inline;
  display: inline-block;
  overflow: hidden;
  position: relative;
  height: 0;
  width: 0;
}

.vwtabs .vwcontent {
  display: block;
  float: left;
  margin-top: -1px;
  background: white;
  padding: 0 20px;
  border: 1px solid #ccc;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  width: 100%;
}

.vwtabs .vwtab [type=radio]:checked + .vwlabel {
  background: white;
  border-bottom: 2px solid #F76;
  padding-bottom: 11px;
  z-index: 1;
}

.vwtabs .vwtab [type=radio]:checked ~ .vwpanel {
  display: inline;
}


.videowhisperButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
	box-shadow:inset 0px 1px 0px 0px #ffffff;

	-webkit-border-top-left-radius:6px;
	-moz-border-radius-topleft:6px;
	border-top-left-radius:6px;
	-webkit-border-top-right-radius:6px;
	-moz-border-radius-topright:6px;
	border-top-right-radius:6px;
	-webkit-border-bottom-right-radius:6px;
	-moz-border-radius-bottomright:6px;
	border-bottom-right-radius:6px;
	-webkit-border-bottom-left-radius:6px;
	-moz-border-radius-bottomleft:6px;
	border-bottom-left-radius:6px;

	text-indent:0;
	border:1px solid #dcdcdc;
	display:inline-block;
	color:#666666;
	font-family:Verdana;
	font-size:15px;
	font-weight:bold;
	font-style:normal;
	padding:10px;
	width:200px;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #ffffff;
	background-color:#e9e9e9;

}

.videowhisperButton:hover {
	background-color:#f9f9f9;
}

</style>
HTMLCODE

				,
				'customCSS' => <<<HTMLCODE
<style type="text/css">


.videowhisperEnterButton {
    background-color: #8e3e41;
    border-radius: 3px;
    padding: 8px;
    
    color: white;
    font-size: 15px;

    border: none;
    cursor: pointer;
}
.videowhisperEnterDropdown:hover .videowhisperEnterButton {
    background-color: #AF4C50;    
}


.videowhisperEnterDropdown {
   position: absolute;
    display: inline-block;
    bottom: 2px;
    right: 2px;
}
.layoutList .videowhisperEnterDropdown
{
bottom: 2px;
left: 430px;
right: auto;
}

.videowhisperEnterDropdown-content {
    display: none;
    position: absolute;
    
    background-color: #f9f9f9;
    border-radius: 3px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    
    min-width: 150px;
    z-index: 15;
    bottom: 25px;
    right: 0px;
    text-align: right;

    font-size: 14px;
}

.videowhisperEnterDropdown-content a {
    color: black;
    padding: 6px 8px;
    text-decoration: none;
    display: block;
}

.videowhisperEnterDropdown-content a:hover
{
	    color: white;
		background-color: #8e3e41;
		border-radius: 3px;

}

.videowhisperEnterDropdown:hover .videowhisperEnterDropdown-content {
    display: block;
}



.videowhisperWebcam
{
	position: relative;
	display:inline-block;
	float: left;

	border:1px solid #666;
	background-color:#666;
	padding: 0px;
	margin: 2px;

	width: 240px;
        height: 240px;

	overflow: hidden;
	z-index: 0;
}

.videowhisperWebcam.layoutList
{
height: 180px;
width: 100%;
}

.videowhisperWebcam.offline{background-color:#866;}
.videowhisperWebcam.public{background-color:#686;}
.videowhisperWebcam.private{background-color:#668;}

.videowhisperWebcam:hover, .videowhisperWebcam2:hover {
	border:1px solid #333;
	background-color:#888;
}

.videowhisperWebcam2
{
position: relative;
display:inline-block;
float: left;

	border:1px solid #aaa;
	background-color:#666;
	padding: 0px;
	margin: 2px;

	width: 486px;
       height: 486px;

	overflow: hidden;
	z-index: 0;
}

.videowhisperSnap
{
       width: 240px;
       height: 180px;
}

.videowhisperSnap2
{
	width: 486px;
       height: 364px;
}


.videowhisperPreview
{
	width: 240px;
       height: 180px;

padding: 0px;
margin: 0px;
border: 0px;
z-index: 1;
}

.videowhisperPreview2
{
	width: 486px;
    height: 364px;

padding: 0px;
margin: 0px;
border: 0px;
z-index: 1;
}

.videowhisperDescription
{
position: absolute;
top:0px;
left: 490px;
right:240px;
font-size: 10px;
color: #eee;
text-shadow:1px 1px 1px #333;
z-index: 10;
height:240px;
overflow:auto;
}


.videowhisperFeaturedReview
{
position: absolute;
top:-10px;
right:2px;

width:240px;
z-index: 10;
}

.videowhisperTitle
{
position: absolute;
top:2px;
left:2px;
font-size: 14px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;

border-radius: 3px;
padding: 3px; 
background: #633;

white-space: nowrap;
overflow: hidden;
max-width:120px;
}
.layoutList .videowhisperTitle
{
left: 242px;
}

.videowhisperPerformers
{
position: absolute;
top: 25px;
left: 2px;
font-size: 12px;
color: #fee;
text-shadow:1px 1px 1px #333;
z-index: 10;

white-space: nowrap;
overflow: hidden;
max-width:120px;
}
.layoutList .videowhisperPerformers
{
left: 242px;
right: auto;
top: 25px;
}

.videowhisperTime
{
position: absolute;
bottom: 60px;
left:2px;
font-size: 11px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;

white-space: nowrap;
overflow: hidden;
max-width:120px;
}
.layoutList .videowhisperTime
{
top: 50px;
bottom: auto;
left:242px;
}

.videowhisperCategory
{
position: absolute;
bottom: 40px;
right: 2px;
font-size: 11px;
color: #fff;
text-shadow:1px 1px 1px #333;
z-index: 10;

white-space: nowrap;
overflow: hidden;
max-width:120px;
}
.layoutList .videowhisperCategory
{
top: 70px;
bottom:auto;
left: 242px;
}

.videowhisperTags
{
position: absolute;
bottom: 40px;
left: 2px;
font-size: 10px;
color: #fff;
text-shadow:1px 1px 1px #333;
z-index: 10;

white-space: nowrap;
overflow: hidden;
max-width:170px;
}
.layoutList .videowhisperTags
{
top: 90px;
bottom:auto;
left: 242px;
}

.videowhisperBrief
{
position: absolute;
bottom: 22px;
left: 2px;
font-size: 10px;
color: #eee;
text-shadow:1px 1px 1px #333;
z-index: 10;

white-space: nowrap;
overflow: hidden;
max-width:170px;
}
.layoutList .videowhisperBrief
{
top: 110px;
bottom:auto;
left: 242px;
}

.videowhisperGroupMode
{
position: absolute;
bottom: 2px;
left: 2px;
font-size: 12px;
color: #eef;
text-shadow:1px 1px 1px #333;
z-index: 10;
}
.layoutList .videowhisperGroupMode
{
top: 130px;
bottom: auto;
left: 242px;
}

.videowhisperGroupCPM
{
position: absolute;
bottom: 2px;
right: 60px;
font-size: 12px;
color: #efe;
text-shadow:1px 1px 1px #333;
z-index: 10;
}
.layoutList .videowhisperGroupCPM
{
top: 130px;
bottom: auto;
left: 350px;
right: auto;
}

.videowhisperCPM
{
position: absolute;
bottom: 62px;
right: 2px;
font-size: 12px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}
.layoutList .videowhisperCPM
{
top: 150px;
bottom: auto;
left: 242px;
right: auto;
}

.videowhisperRating
{
position: absolute;
bottom: 80px;
left:2px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}
.layoutList .videowhisperRating
{
top: 2px;
left: 400px;
right: auto;
bottom: auto;
}

.videowhisperFeatured {
  width: 140px;
  height: 21px;
  line-height: 21px;
  position: absolute;
  top: 21px;
  right: -35px;
  overflow: hidden;
  -webkit-transform: rotate(45deg);
  transform: rotate(45deg);
  box-shadow:0 0 0 3px #DD5743,  0px 21px 5px -18px rgba(0,0,0,0.6);
  background: #DD5743;
  color: #FFFFFF;
  text-align: center;
  opacity: 0.7;
  z-index: 11;
}


td {
    padding: 4px;
}

</style>
HTMLCODE
			);

		}

		static function getAdminOptions() {

			$adminOptions = self::adminOptionsDefault();

			$features = self::roomFeatures();
			foreach ($features as $key=>$feature) if ($feature['installed'])  $adminOptions[$key] = $feature['default'];

				$options = get_option('VWliveWebcamsOptions');

			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}

			update_option('VWliveWebcamsOptions', $adminOptions);

			return $adminOptions;
		}


	static function adminOptions()
		{
			$options = self::getAdminOptions();

			if (isset($_POST)) if (!empty($_POST))
				{

					$nonce = $_REQUEST['_wpnonce'];
					if ( ! wp_verify_nonce( $nonce, 'vwsec' ) )
					{
						echo 'Invalid nonce!';
						exit;
					}

					foreach ($options as $key => $value)
						if (isset($_POST[$key])) $options[$key] = $_POST[$key];

						//config parsing
						if (isset($_POST['profileFieldsConfig']))
							$options['profileFields'] = parse_ini_string(sanitize_textarea_field($_POST['profileFieldsConfig']), true);

						if (isset($_POST['groupModesConfig']))
							$options['groupModes'] = parse_ini_string(sanitize_textarea_field($_POST['groupModesConfig']), true);

						if (isset($_POST['appSetupConfig']))
							$options['appSetup'] = parse_ini_string(sanitize_textarea_field($_POST['appSetupConfig']), true);

						if (isset($_POST['recordFieldsConfig']))
							$options['recordFields'] = parse_ini_string(sanitize_textarea_field($_POST['recordFieldsConfig']), true);

						if (isset($_POST['labelsConfig']))
							$options['labels'] = parse_ini_string(sanitize_textarea_field($_POST['labelsConfig']), false);

						update_option('VWliveWebcamsOptions', $options);
				}

			$optionsDefault = self::adminOptionsDefault();

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'setup';

//	<span class="nav-tab dashicons dashicons-admin-generic"></span>

?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br></div>
<h2>VideoWhisper PPV Live Webcams - Paid Videochat Site Solution</h2>
</div>

<nav class="nav-tab-wrapper wp-clearfix">
	<a href="admin.php?page=live-webcams&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>">Streaming Server</a>
	<a href="admin.php?page=live-webcams&tab=hls" class="nav-tab <?php echo $active_tab=='hls'?'nav-tab-active':'';?>">FFMPEG / HLS</a>
	<a href="admin.php?page=live-webcams&tab=webrtc" class="nav-tab <?php echo $active_tab=='webrtc'?'nav-tab-active':'';?>">WebRTC</a>
	
	<a href="admin.php?page=live-webcams&tab=pages" class="nav-tab <?php echo $active_tab=='pages'?'nav-tab-active':'';?>">Pages</a>
	<a href="admin.php?page=live-webcams&tab=integration" class="nav-tab <?php echo $active_tab=='integration'?'nav-tab-active':'';?>">Integration</a>	
	<a href="admin.php?page=live-webcams&tab=appearance" class="nav-tab <?php echo $active_tab=='appearance'?'nav-tab-active':'';?>">Appearance</a>
	<a href="admin.php?page=live-webcams&tab=listings" class="nav-tab <?php echo $active_tab=='listings'?'nav-tab-active':'';?>">Cam Listings</a>
	
	
	<a href="admin.php?page=live-webcams&tab=app" class="nav-tab <?php echo $active_tab=='app'?'nav-tab-active':''; ?>">HTML5 Videochat / Apps</a>
	<a href="admin.php?page=live-webcams&tab=random" class="nav-tab <?php echo $active_tab=='random'?'nav-tab-active':'';?>">Random Chat</a>
	
	<a href="admin.php?page=live-webcams&tab=performer" class="nav-tab <?php echo $active_tab=='performer'?'nav-tab-active':'';?>">Performer</a>
	<a href="admin.php?page=live-webcams&tab=record" class="nav-tab <?php echo $active_tab=='record'?'nav-tab-active':'';?>">Account Records</a>
	<a href="admin.php?page=live-webcams&tab=features" class="nav-tab <?php echo $active_tab=='features'?'nav-tab-active':'';?>">Features</a>
	


	<a href="admin.php?page=live-webcams&tab=profile" class="nav-tab <?php echo $active_tab=='profile'?'nav-tab-active':'';?>">Profile</a>
	<a href="admin.php?page=live-webcams&tab=client" class="nav-tab <?php echo $active_tab=='client'?'nav-tab-active':'';?>">Client</a>
	<a href="admin.php?page=live-webcams&tab=geofencing" class="nav-tab <?php echo $active_tab=='geofencing'?'nav-tab-active':'';?>">GeoFencing</a>
	

	<a href="admin.php?page=live-webcams&tab=studio" class="nav-tab <?php echo $active_tab=='studio'?'nav-tab-active':'';?>">Studio</a>
	<a href="admin.php?page=live-webcams&tab=group" class="nav-tab <?php echo $active_tab=='group'?'nav-tab-active':'';?>">Group Modes</a>
	<a href="admin.php?page=live-webcams&tab=presentation" class="nav-tab <?php echo $active_tab=='presentation'?'nav-tab-active':'';?>">Presentation/Collaboration</a>

	<a href="admin.php?page=live-webcams&tab=billing" class="nav-tab <?php echo $active_tab=='billing'?'nav-tab-active':'';?>">Billing Wallets</a>

	<a href="admin.php?page=live-webcams&tab=ppv" class="nav-tab <?php echo $active_tab=='ppv'?'nav-tab-active':'';?>">Pay Per Minute</a>
    <a href="admin.php?page=live-webcams&tab=tips" class="nav-tab <?php echo $active_tab=='tips'?'nav-tab-active':'';?>">Tips / Gifts</a>
	<a href="admin.php?page=live-webcams&tab=messages" class="nav-tab <?php echo $active_tab=='messages'?'nav-tab-active':'';?>">Paid Messages</a>
    
	<a href="admin.php?page=live-webcams&tab=video" class="nav-tab <?php echo $active_tab=='video'?'nav-tab-active':'';?>">Videos Pictures Reviews</a>
	<a href="admin.php?page=live-webcams&tab=scheduler" class="nav-tab <?php echo $active_tab=='scheduler'?'nav-tab-active':'';?>">Playlists Scheduler</a>



	<a href="admin.php?page=live-webcams&tab=translate" class="nav-tab <?php echo $active_tab=='translate'?'nav-tab-active':'';?>">Translate</a>


	<a href="admin.php?page=live-webcams&tab=reset" class="nav-tab <?php echo $active_tab=='reset'?'nav-tab-active':'';?>">Reset</a>
	<a href="admin.php?page=live-webcams&tab=requirements" class="nav-tab <?php echo $active_tab=='requirements'?'nav-tab-active':''; ?>">Requirements Troubleshooting</a>

	<a href="admin.php?page=live-webcams&tab=support" class="nav-tab <?php echo $active_tab=='support'?'nav-tab-active':'';?>">Support</a>

    <a href="admin.php?page=live-webcams&tab=setup" class="nav-tab <?php echo $active_tab=='setup'?'nav-tab-active':'';?>">Setup</a>

</nav>

<form method="post" action="<?php echo wp_nonce_url($_SERVER["REQUEST_URI"], 'vwsec'); ?>">
<?php

			switch ($active_tab)
			{

			case 'messages';
?>
<h3><?php _e('Paid Questions and Messages','live-streaming'); ?></h3>
Client is charged when sending question and performer gets paid on reply. Performer earning ratio is applied on earnings, similar to pay per minute.

<h4>Enable Questions / Messages</h4>
<select name="messages" id="messages">
  <option value="0" <?php echo $options['messages']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['messages']?"selected":""?>>Yes</option>
</select>
<br>Clients can send questions/messages to webcam profiles and performers can answer.

<h4>Cost per Question</h4>

<input name="messagesCost" type="text" id="messagesCost" size="10" maxlength="16" value="<?php echo $options['messagesCost']?>"/>
<br>Can be 0 to allow free messages (not recommended as that can result in SPAM). Default: <?php echo $optionsDefault['messagesCost']?>

<?php
			
			break;

			case 'random';
?>
<h3><?php _e('Random Video Chat','live-streaming'); ?></h3>
HTML5 Videochat App: Enable clients to quickly move to a different performer room (without leaving chat interface). 
Random videochat can also be rendered with [videowhisper_cam_random] shortcode, that displays a random room based on these settings.


<h4>Next Button</h4>
<select name="videochatNext" id="videochatNext">
  <option value="0" <?php echo $options['videochatNext']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videochatNext']?"selected":""?>>Yes</option>
</select>
<BR>Next room is selected from rooms recently active online, by picking a room user did not enter or entered longest time ago (for rotation). Will not select rooms where performer is in private show.

<h4>Paid Rooms on Next</h4>
<select name="videochatNextPaid" id="videochatNextPaid">
  <option value="0" <?php echo $options['videochatNextPaid']?"":"selected"?>>Free</option>
  <option value="1" <?php echo $options['videochatNextPaid']?"selected":""?>>Any</option>
  <option value="2" <?php echo $options['videochatNextPaid']=='2'?"selected":""?>>Only Paid</option>
</select>
<br>Visitors always get free rooms. When users enter paid rooms, welcome message will contain details including group cost per minute and grace time. User is not charged if moving to next room or closing before grace time ends. Also paid room welcome message has a special icon showing payment. Default: Any
<br><IMG WIDTH="48px" SRC="<?php echo dirname(plugin_dir_url(__FILE__)) . '/images/cash.png' ?>">

<h4>Online Rooms on Next</h4>
<select name="videochatNextOnline" id="videochatNextOnline">
  <option value="0" <?php echo $options['videochatNextOnline']?"":"selected"?>>Any</option>
  <option value="1" <?php echo $options['videochatNextOnline']?"selected":""?>>Only Online</option>
</select>
<BR>On new sites with few online performers this should not be enabled as users will not find many online rooms. Default: Any
<?php
			
			break;

			case 'pages';
?>
<h3><?php _e('Setup Pages','live-streaming'); ?></h3>

<?php			
			if ($_POST['submit'])
			{
			echo 'Saving pages setup...';
			self::setupPages();
			}

submit_button( __('Update Pages','live-streaming') );
?>
Use this to setup pages on your site. Pages with main feature shortcodes are required to access main functionality. After setting up these pages you should add the feature pages to site menus for users to access.
A sample VideoWhisper menu will also be added when adding pages: can be configured to show in a menu section depending on theme.
<br>You can manage these anytime from backend: <a href="edit.php?post_type=page">pages</a> and <a href="nav-menus.php">menus</a>.
<BR><?php echo self::requirementRender('setup_pages') ?>

<h4>Setup Pages</h4>
<select name="disableSetupPages" id="disableSetupPages">
  <option value="0" <?php echo $options['disableSetupPages']?"":"selected"?>>Yes</option>
  <option value="1" <?php echo $options['disableSetupPages']?"selected":""?>>No</option>
</select>
<br>Create pages for main functionality. Also creates a menu with these pages (VideoWhisper) that can be added to themes.
<br>After login performers are redirected to the dashboard page and clients to webcams page.

<h4>Manage Balance Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits. Recommended: My Wallet (created by <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership & Content</a> Plugin)
<?php
			
			break;

				
				
				case 'setup':
?>
<h3><?php _e('Setup Overview','live-streaming'); ?></h3>


 1. Requirements: Before setting up, make sure you have necessary hosting requirements, for live video streaming. This plugin has <a href="https://videowhisper.com/?p=Requirements" title="Live Streaming Requirements" target="_requirements">requirements</a> beyond regular WordPress hosting specifications and needs specific live streaming services and video tools. Skip requirements review if you have a turnkey hosting plan from VideoWhisper as it provides all features.
<br> 2. Existing active site? This plugin is designed to setup a turnkey live streaming site, changing major WP blog features. Set it up on a development environment as it can alter functionality of existing sites. To be able to revert changes, before setting up, make a recovery backup using <a target="_backup" href="https://updraftplus.com/?afref=924">Updraft Plus</a> or other backup tool. You can skip backups if this is a new site.
<br> 3. Setup: To setup this plugin start from <a href="admin.php?page=live-webcams-doc">Backend Documentation</a>, check project page <a href="https://paidvideochat.com/features/quick-setup-tutorial/" target="_documentation">PaidVideoChat Setup Tutorial</a> for more details and then review requirements checkpoints list on this page.
<br>If not sure about how to proceed or need clarifications, <a href="https://videowhisper.com/tickets_submit.php">contact plugin developers</a>. 

<p><a class="button secondary" href="admin.php?page=live-webcams-doc">Backend Setup Tutorial</a></p>

<h3><?php _e('Setup Checkpoints','live-streaming'); ?></h3>

This section lists main requirements and checkpoints for setting up and using this solution. 
<?php
	
	
	//handle item skips
	$unskip = sanitize_file_name( $_GET['unskip']);
	if ($unskip) self::requirementUpdate($unskip, 0, 'skip');
	
	$skip = sanitize_file_name( $_GET['skip']);
	if ($skip) self::requirementUpdate($skip, 1, 'skip');
	
	$check = sanitize_file_name( $_GET['check']);
	if ($check) self::requirementUpdate($check, 0);

	$done = sanitize_file_name( $_GET['done']);
	if ($done) self::requirementUpdate($done, 1);
	
	//accessed setup page: easy
	self::requirementMet('setup');
	
	//list requirements
	$requirements = self::requirementsGet();
	
	$rDone = 0;


	foreach ($requirements as $label => $requirement) 
	{
		$html = self::requirementRender($label, 'overview', $requirement);
		
	$status = self::requirementStatus($requirement);
	$skip = self::requirementStatus($requirement, 'skip'); 
	
	
	if ($status) {$htmlDone .= $html; $rDone++;}
	elseif ($skip) $htmlSkip .= $html;
	else $htmlPending .= $html;
	}

		if ($htmlPending) echo '<h4>To Do:</h4>' . $htmlPending;
		if ($htmlSkip) echo '<h4>Skipped:</h4>' . $htmlSkip;		
		if ($htmlDone) echo '<h4>Done ('.$rDone.'):</h4>' . $htmlDone;
?>
* These requirements are updated with checks and checkpoints from certain pages, sections, scripts. Certain requirements may take longer to update (in example session control updates when there are live streams and streaming server calls the web server to notify). When plugin upgrades include more checks to assist in reviewing setup, these will initially show as required until checkpoint.
<?php	
	//var_dump($requirements);	
		break;

				
			case 'app':
			
			$options['appSetupConfig'] = htmlentities(stripslashes($options['appSetupConfig']));
?>
<h3>Apps</h3>
This section configures HTML5 Videochat app and external access (by external apps) using same API. Required when building external apps to work with solution.
<br>HTML5 videochat app uses WebRTC for live video streaming: <a href="admin.php?page=live-webcams&tab=webrtc">Configure HTML5 WebRTC</A>.


<h4>App Configuration</h4>
<textarea name="appSetupConfig" id="appSetupConfig" cols="120" rows="12"><?php echo $options['appSetupConfig']?></textarea>
<BR>Application setup parameters are delivered to app when connecting to server. Config section refers to application parameters and Room section refers to default room options (configurable from app at runtime).

Default:<br><textarea readonly cols="120" rows="6"><?php echo $optionsDefault['appSetupConfig']?></textarea>

<BR>Parsed configuration (should be an array or arrays):<BR>
<?php

				var_dump($options['appSetup']);
?>
<BR>Serialized:<BR>
<?php

				echo serialize($options['appSetup']);
?>

<h4>Reset Room Options</h4>
<select name="appOptionsReset" id="appOptionsReset">
	<option value="0" <?php echo (!$options['appOptionsReset']?"selected":"") ?>>No</option>
	<option value="1" <?php echo ($options['appOptionsReset']=='1'?"selected":"") ?>>Yes</option>
</select>
<br>Resets room options on each performer session start, forcing defaults. Disable to allow options configured at runtime to persist.


<h4>Show Room Options</h4>
<select name="appOptions" id="appOptions">
	<option value="0" <?php echo (!$options['appOptions']?"selected":"") ?>>No</option>
	<option value="1" <?php echo ($options['appOptions']=='1'?"selected":"") ?>>Yes</option>
</select>
<br>Show Options tab in Advanced interface, for performer to edit room options live.

<h4>App Interface Complexity</h4>
<select name="appComplexity" id="appComplexity">
	<option value="0" <?php echo (!$options['appComplexity']?"selected":"") ?>>Simple</option>
	<option value="1" <?php echo ($options['appComplexity']=='1'?"selected":"") ?>>Advanced</option>
	<option value="2" <?php echo ($options['appComplexity']=='2'?"selected":"") ?>>Advanced for Room Owner</option>
</select>
<br>Simple interface shows minimal panels (video, text chat, actions). 
<br>Advanced shows tabs with users list. Broadcaster has both camera tab and playback (preview) from server in advanced mode.
<br>Collaboration & Conference modes are always in advanced interface.
<br>Advanced for Room Owner will give owner ability to switch room live to Conference / Collaboration mode (and get everybody from Simple to Advanced interface).

<h4>Wallet Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits. Recommended: My Wallet (setup with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership & Content</a> plugin).


<h4>Content Types for Fullpage Videochat Template</h4>
<input name="templateTypes" type="text" id="templateTypes" size="100" maxlength="250" value="<?php echo $options['templateTypes']?>"/>
<BR>Comma separated content/post types, to show videochat template option. Ex: page, post, video, picture, download, webcam
<BR>A special metabox will show up when editing these content types from backend, to enable the videochat template.
<BR>Use this to load videochat in full page template on that page. Page contents must include the [videowhisper_videochat] shortcode (see documentation).

<h4>Site Menu in App</h4>
<select name="appSiteMenu" id="appSiteMenu">
	<option value="0" <?php echo (!$options['appSiteMenu']?"selected":"") ?>>None</option>
<?php
$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );

	foreach ($menus as $menu) echo '<option value="' . $menu->term_id . '" '. ($options['appSiteMenu'] == ($menu->term_id) ?"selected":"") .'>' . $menu->name . '</option>' . "\r\n";

?>	
</select>
<br>A site menu is useful for chat users to access site features, especially when running app in full page.

<h4>Whitelabel Mode: Remove Author Attribution Notices</h4>
<select name="whitelabel" id="whitelabel">
	<option value="0" <?php echo (!$options['whitelabel']?"selected":"") ?>>Disabled</option>
	<option value="1" <?php echo ($options['whitelabel']=='1'?"selected":"") ?>>Enabled</option>
</select>
<br>Embedded HTML5 Videochat application is branded with subtle attribution references to authors, similar to most software solutions in the world. Removing the default author attributions can be provided with a special licensing agreement, in addition to full mode. Whitelabelling is an extra option that can be added to full mode.
<br>Warning: Application will not start if whitelabel mode is enabled and explicit licensing agreement from authors is not available, to remove attribution notices.

<h4>CORS Access-Control-Allow-Origin</h4>

<input name="corsACLO" type="text" id="corsACLO" size="80" maxlength="256" value="<?php echo $options['corsACLO']?>"/>
<br>Enable external web access from these domains (CSV). Ex: http://localhost:3000


<?php
				break;
				
			case 'geofencing':
?>
<h4>GeoFencing</h4>
Block access to site content depending on location: globally for entire site (by admin) or per webcam listing (by performer).

<h4>GeoIP</h4>
GeoIP is required for admins and performers to ban certain countries, regions from accessing site or certain listings. This uses IP databases for location mapping without need for user to allow location detection in browser.
<BR><BR>

<?php
				$clientIP = self::get_ip_address();
				echo __('Client IP', 'ppv-live-webcams') . ': ' . $clientIP;

				if (self::detectLocation() === false)
					echo  '<br>' .__('ERROR: Can not detect location. GeoIP extension is required on host for this functionality.', 'ppv-live-webcams');
				else
				{
					echo  '<br><u>Detected:</u> GeoIP location seems to work. Detected location (precision depends on IP):';
					echo '<br>' . __('Continent', 'ppv-live-webcams') . ': ' . self::detectLocation('continent', $clientIP);
					echo '<br>' . __('Country', 'ppv-live-webcams') . ': ' . self::detectLocation('country', $clientIP);
					echo '<br>' . __('Region', 'ppv-live-webcams') . ': '. self::detectLocation('region', $clientIP);
					echo '<br>' . __('City', 'ppv-live-webcams') . ': ' . self::detectLocation('city', $clientIP);

				}
?>
<h4>Global Geo Blocking</h4>
<textarea name="geoBlocking" id="geoBlocking" cols="100" rows="3"><?php echo $options['geoBlocking'] ?></textarea>
<br>Comma separated continents, countries, regions, cities. Sample Geo Blocking:<br>
<textarea readonly cols="100" rows="2">Guyana, Bangladesh, India, South Korea, Saudi Arabia, Botswana, Nigeria, Sudan, Egypt, Afghanistan, Pakistan, Turkmenistan, Burma, Iran, Iraq, Jordan, Syria, Kuwait, Yemen, Bahrain, Oman, Qatar, Saudi Arabia</textarea>

<h4>Global Geo Blocking Message</h4>
<?php 
	$options['geoBlockingMessage'] = stripslashes($options['geoBlockingMessage']);
	wp_editor( $options['geoBlockingMessage'], 'geoBlockingMessage', $settings = array('textarea_rows'=>3) ); 
?> 

<p>Warning: GeoIP does not provide 100% accuracy. Also, certain users may use VPN or proxy services to access trough other locations (requires some technical skills and resources). Interdictions should also be enforced by site terms, additional verifications and processing of reports.</p>
<?php
			break;
			
			case 'requirements':
?>
<h3>Requirements and Troubleshooting</h3>
To be able to run this solution, make sure your hosting environment meets all <a href="https://videowhisper.com/?p=Requirements" target="_blank">requirements</a>.
<BR>Recommended Hosting that meets all requirements: <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting#features" target="_vwhost">VideoWhisper Wowza Turnkey Managed Hosting</a> - turnkey rtmp address, specific web + rtmp configuration for archiving, VOD, converting videos and documents, transcoding streams, delivery to mobiles as HLS / MPEG DASH, playlists scheduler (video loops as fake performers).
<?php	



echo "<h4>Web Host</h4>";
echo "Web Name: " . $_SERVER['SERVER_NAME'];
echo "<br>Web IP: " . $_SERVER['SERVER_ADDR'];
echo "<br>Site Path: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Server Hostname: " . gethostname();
echo "<br>Server OS: " . php_uname();
echo "<br>Web Server: " . $_SERVER['SERVER_SOFTWARE'];
echo "<br>Connection: " . $_SERVER['HTTP_CONNECTION'];
echo "<br>Client IP: " . $_SERVER['REMOTE_ADDR'];
echo "<br>Client Browser: " . $_SERVER['HTTP_USER_AGENT'];

echo "<br>Last Plugin DB Update: " . get_option( "vmls_db_version" );

?>
<h4>RTMP Streaming Server</h4>
<?php
					echo "RTMP Chat Address from settings: " . $options['rtmp_server'];

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
					$rtmp_server = $options['rtmp_server'] . '?'. urlencode($username) .'&'. urlencode($item->post_title) .'&'. $keyView . '&0&videowhisper';

					echo '<br>Using room #' . $item->ID . ' (' . $item->post_title . ') with address keys ' . $rtmp_server . ' to access RTMP streaming app.';
				}
				

				$swfurl = plugin_dir_url(dirname(__FILE__)) . "/videowhisper/vw_connectiontester.swf?ssl=1&rtmp=". urlencode($rtmp_server);

				?><object width="800" height="500" id="videowhisper_connectiontester1" type="application/x-shockwave-flash" data="<?php echo $swfurl ?>">
<param name="movie" value="<?php echo $swfurl ?>"></param><param bgcolor="#333333" /><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
<param name="base" value=""/><param name="wmode" value="transparent" /></object>


	           <p>+ This is a compulsory requirement for running the VideoWhisper videochat application. Application will not run without compatible rtmp hosting and VideoWhisper rtmp side deployed.
		       <BR>+ Read more about <a href="https://videowhisper.com/?p=RTMP+Hosting" target="_blank">RTMP Hosting</a>.
	           </p>


<h4>FFMPEG & Codecs</h4>
FFMPEG and specific codecs are required for transcoding live streams for Wowza mobile delivery, converting videos, extracting snapshots.
<BR><BR>
<?php
				$fexec = 0;
				echo "exec: ";
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

				echo '<br>PHP script owner: ' . get_current_user() . ' #'. getmyuid();
				echo '<br>Process effective owner: ' . posix_getpwuid(posix_geteuid())['name'] . ' #'. posix_geteuid();


if ($fexec)
{
				echo '<br>exec("whoami"): ';
				$cmd = "whoami";
				$output="";
				exec($cmd, $output, $returnvalue);
				foreach ($output as $outp) echo $outp;
				

				echo '<br>exec("which ffmpeg"): ';
				$cmd = "which ffmpeg";
				$output="";
				exec($cmd, $output, $returnvalue);
				foreach ($output as $outp) echo $outp;
	
	
				echo "<br>Path from settings: " . $options['ffmpegPath'] . '<br>';

				$cmd =$options['ffmpegPath'] . ' -version';
				$output="";
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
				{
					echo "FFMPEG Detected (127): ";
					
						echo $cmd . ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';	
				}

				$cmd =$options['ffmpegPath'] . ' -codecs';
				$output="";
				exec($cmd, $output, $returnvalue);

				//detect codecs
				$hlsAudioCodec = ''; //hlsAudioCodec
				if ($output) 
				if (count($output))
					{
						echo "<br>Codec libraries: ";
						echo $cmd . ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';	
						foreach (array('h264', 'vp6','speex', 'nellymoser', 'aacplus', 'vo_aacenc', 'faac', 'fdk_aac', 'vp8', 'vp9', 'opus') as $cod)
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


				echo '<BR>Auto config AAC Codec: '. $hlsAudioCodec;
}

?>
<BR><BR>You need only 1 AAC codec. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).

<h4>Unoconv</h4>
Unoconv is required for converting documents to accessible formats.
<BR><BR>
<?php
				echo "Path from settings: " . $options['unoconvPath'] . '<br>';

				$cmd =$options['unoconvPath'] . ' --version';
				$output = '';
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
				{
					echo "<u>Detected:</u>";
						echo $cmd . ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';	
				}
?>
<h4>ImageMagick Convert</h4>
ImageMagick Convert is required for converting documents to slides.
<BR><BR>
<?php
				echo "Path from settings: " . $options['convertPath'] . '<br>';

				$cmd =$options['convertPath'];
				$output = '';
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>"; else
				{
					echo "<u>Detected:</u>";
						echo $cmd . ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';	
				}

?>
<h4>Session Control</h4>
<?php

				if (in_array($options['webStatus'], array('enabled', 'strict', 'auto')))
					if (file_exists($path = $options['uploadsPath']. '/_rtmpStatus.txt'))
					{
						$url = self::path2url($path);
						echo 'Found: <a target=_blank href="'.$url.'">last status request</a> ' . date("D M j G:i:s T Y", filemtime($path)) ;
						echo '<br><textarea readonly cols="120" rows="4">' . file_get_contents($path) . '</textarea>';
					} else echo "Warning: Status log file not found!";
				else echo "Warning: webStatus not enabled/strict/auto!";
?>
<h4>GeoIP</h4>
GeoIP is required for performers to ban certain countries, regions from accessing their listings.
<BR><BR>

<?php
				$clientIP = self::get_ip_address();
				echo __('Client IP', 'ppv-live-webcams') . ': ' . $clientIP;

				if (self::detectLocation() === false)
					echo  '<br>' .__('ERROR: Can not detect location. GeoIP extension is required on host for this functionality.', 'ppv-live-webcams');
				else
				{
					echo  '<br><u>Detected:</u> GeoIP location seems to work. Detected location (precision depends on IP):';
					echo '<br>' . __('Continent', 'ppv-live-webcams') . ': ' . self::detectLocation('continent', $clientIP);
					echo '<br>' . __('Country', 'ppv-live-webcams') . ': ' . self::detectLocation('country', $clientIP);
					echo '<br>' . __('Region', 'ppv-live-webcams') . ': '. self::detectLocation('region', $clientIP);
					echo '<br>' . __('City', 'ppv-live-webcams') . ': ' . self::detectLocation('city', $clientIP);

				}


				break;

			case 'reset':
?>
<h3><?php _e('Reset Options','ppv-live-webcams'); ?></h3>
This resets some options to defaults. Useful when upgrading plugin and new defaults are available for new features and for fixing broken installations.
<?php

				$confirm = $_GET['confirm'];


				if ($confirm) echo '<h4>Resetting...</h4>';
				else echo '<p><A class="button" href="admin.php?page=live-webcams&tab=reset&confirm=1">Yes, Reset These Settings!</A></p>';

				$resetOptions = array('customCSS', 'dashboardCSS','listingTemplate', 'listingTemplate2', 'listingTemplateList','listingBig', 'supportRTMP', 'alwaysRTMP', 'supportP2P', 'alwaysP2P', 'parametersPerformer', 'parametersPerformerPresentation', 'parametersClient', 'parametersClientPresentation', 'ppvCloseAfter', 'ppvBillAfter', 'custom_post', 'detect_hls', 'detect_mpeg', 'tipOptions');


				foreach ($resetOptions as $opt)
				{
					echo '<BR> - ' . $opt;
					if ($confirm) $options[$opt] = $optionsDefault[$opt];
				}

				if ($confirm)  update_option('VWliveWebcamsOptions', $options);


				break;


			case 'presentation':


				$options['parametersPerformerPresentation'] = htmlentities(stripslashes($options['parametersPerformerPresentation']));
				$options['layoutCodePerformerPresentation'] = htmlentities(stripslashes($options['layoutCodePerformerPresentation']));


				$options['parametersClientPresentation'] = htmlentities(stripslashes($options['parametersClientPresentation']));
				$options['layoutCodeClientPresentation'] = htmlentities(stripslashes($options['layoutCodeClientPresentation']));


				?><h3>Presentation Mode</h3>
Presentation mode includes advanced tools specific to elearning, consultation, collaboration: slide show with annotations, file sharing. As this involves multiple panels, elements should only be used when necessary to avoid confusing users. Recommended for eLearning, tutoring sites.
Presentation mode is available with Video Chat Messenger interface (flash) and enables Collaboration mode in HTML5 Videochat interface.

<h4>Enable Presentation/Collaboration Mode</h4>
<select name="presentation" id="presentation">
  <option value="0" <?php echo $options['presentation']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['presentation']?"selected":""?>>Yes</option>
</select>
<BR>Default global setting (for all rooms on site). Room owners can setup their room to run in presentation mode or not, if their role allows presentation mode from <a href="admin.php?page=live-webcams&tab=features">Room Features</a>.

<h4>Video Chat Messenger - Presentation Mode</h4>
Customize parameters for Video Chat Messenger (Flash) application in presentation mode.

<h4>Parameters for Performer in Presentation Mode</h4>
<textarea name="parametersPerformerPresentation" id="parametersPerformerPresentation" cols="100" rows="10"><?php echo $options['parametersPerformerPresentation']?></textarea>
<br>Default Performer Presentation Parameters:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['parametersPerformerPresentation'])?></textarea>

<h4>Custom Layout Code for Performer in Presentation</h4>
<textarea name="layoutCodePerformerPresentation" id="layoutCodePerformer" cols="100" rows="5"><?php echo $options['layoutCodePerformerPresentation']?></textarea>
<br>Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodePerformerPresentation']?></textarea>

<h4>Parameters for Client Interface in Presentation</h4>
<textarea name="parametersClientPresentation" id="parametersClientPresentation" cols="100" rows="10"><?php echo $options['parametersClientPresentation']?></textarea>
<br>Default Client Presentation Parameters:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['parametersClientPresentation'])?></textarea>

<h4>Custom Layout Code for Client in Presentation</h4>
<textarea name="layoutCodeClientPresentation" id="layoutCodeClientPresentation" cols="100" rows="5"><?php echo $options['layoutCodeClientPresentation']?></textarea>
<br>
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodeClientPresentation']?></textarea>

<?php
				break;
			case 'group':

				$options['groupModesConfig'] = htmlentities(stripslashes($options['groupModesConfig']));


				?><h3>Room Group Modes Setup</h3>
Configure group chat room modes. Performers can select room mode for webcam room when going live (in example free mode, paid mode). Paid room modes also work with HTML5 AJAX chat and HTML5 Videochat App.


<h4>Group Modes</h4>
<textarea name="groupModesConfig" id="groupModesConfig" cols="100" rows="12"><?php echo $options['groupModesConfig']?></textarea>
<BR>Configure modes as sections. Set cost per minute as "cpm", 2 way slots as "2way", 2 way cost as "cpm2".

Default:<br><textarea readonly cols="100" rows="6"><?php echo $optionsDefault['groupModesConfig']?></textarea>

<BR>Parsed configuration (should be an array or arrays):<BR>
<?php

				var_dump($options['groupModes']);
?>
<BR>Serialized:<BR>
<?php

				echo serialize($options['groupModes']);
?>
<h4>Group Chat Grace Time</h4>
<input name="groupGraceTime" id="groupGraceTime" type="text" size="5" maxlength="10" value="<?php echo $options['groupGraceTime']?>"/>s
<br>On paid sessions users only get billed if they stay longer than this time (free stay for evaluation).

<h4>Voyeur Mode Availability</h4>
<select name="voyeurAvailable" id="voyeurAvailable">
  <option value="always" <?php echo $options['voyeurAvailable']=='always'?"selected":""?>>Always</option>
  <option value="private" <?php echo $options['voyeurAvailable']=='private'?"selected":""?>>In Private</option>
  <option value="public" <?php echo $options['voyeurAvailable']=='public'?"selected":""?>>In Public</option>
</select>
<BR>When is voyeur mode available: always or only when performer is during private or public chat.

<h4>Clarifications</h4>
+ For each group chat mode: Set cost per minute as "cpm", 2 way slots as "2way", 2 way cost as "cpm2". Setting cpm=0 configures free chat mode. Performers can be allowed from Features to setup their own group mode CPM and 2Way slots.
<BR>+ Users are no longer charged in group mode when performer enters a private show (paid session is ended and a new paid session is started when performer returns). An user that requests and enters a private show is only charged the private show cost per minute during that show. Paid group session fee is charged again, for each client, when private session ends.
<BR>+ Users can select advanced interaction modes when entering group chat, as configured:
<BR>* 2 Way Mode:
A limited number of users can start their webcams during group chat (for easier communication with performer). Only performer can see their stream and they have to pay extra for this privilege. Publishing webcams involves extra server streaming load.
<BR>Configure 2 way slots with "2way" setting.
<BR>Configure cost per minute for users in 2 way mode as "cpm2" setting.
<BR>* Voyeur Mode:
Users can watch performers without participating in chat. Also their username during live session is obfuscated. Performer does not know at live time who is watching as voyeur, but voyeur username shows in transactions section, after session. Great for clients that want to participate in public sessions but don't want to interact.
<BR>Configure voyeur=1 to enable.
<BR>Configure cost per minute for users watching as voyeur with "cpmv" setting.
<BR>+ Snapshots & Archive:
<BR>Performer snapshots can automatically be imported to webcam picture gallery if "snapshots" is enabled.
<BR>Broadcasts can also be archived with "archive" setting. When enabled, archiving is active for entire session, including when going to private mode. Changing and applying settings from webcam panel causes a new archive file to be created (on each publishing session).
<BR>Archives can be imported automatically (not recommended, as reviewing what to publish is better in most cases). Keeping videos and pictures requires space and causes extra load on server so should only be used for paid modes.
<?php
				break;

			case 'record':
				$options['recordFieldsConfig'] = htmlentities(stripslashes($options['recordFieldsConfig']));
				?><h3>Account Administrative Records and Verification/Approval</h3>
Configure fields, questions for performing users, as necessary: details required for approval, verification, payouts.
<BR><a href="admin.php?page=live-webcams-records">Review Records and Approve Accounts</a>


<h4>Fields / Questions</h4>

<textarea name="recordFieldsConfig" id="recordFieldsConfig" cols="100" rows="12"><?php echo $options['recordFieldsConfig']?></textarea>
<?php 	
if ($options['recordFieldsConfig'] && !$options['recordFields']) echo '<br><b>Warning: Configuration syntax error! Please review & correct.</b>'; 
?>
<BR>Save to setup. Configure fields as sections with type (text/textarea/select/checkboxes), instructions, options (separated by /), as necessary.
<br>If a value  contains any non-alphanumeric characters it needs to be enclosed in double-quotes ("").

<BR>Parsed records configuration (should be an array or arrays):<BR>
<?php

				var_dump($options['recordFields']);

?>
<BR>Serialized:<BR>
<?php

				echo serialize($options['recordFields']);

?>
<h4>Enable Performers Without Verification</h4>
<select name="unverifiedPerformer" id="unverifiedPerformer">
  <option value="0" <?php echo $options['unverifiedPerformer']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['unverifiedPerformer']?"selected":""?>>Yes</option>
</select>

<h4>Enable Studios Without Verification</h4>
<select name="unverifiedStudio" id="unverifiedStudio">
  <option value="0" <?php echo $options['unverifiedStudio']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['unverifiedStudio']?"selected":""?>>Yes</option>
</select>
<?php

				break;

			case 'profile':

				$options['profileFieldsConfig'] = htmlentities(stripslashes($options['profileFieldsConfig']));


				?><h3>Webcam Room Listing - Profile Setup</h3>
Configure fields, questions for webcam listings. These can be configured by performer and will show on webcam listing profile page.
<br>Warning: These are not for user profiles. A performer can setup and use multiple webcam rooms. Each webcam room has a listing (and profile with these fields).

<h4>Fields / Questions</h4>
<textarea name="profileFieldsConfig" id="profileFieldsConfig" cols="100" rows="12"><?php echo $options['profileFieldsConfig']?></textarea>
<?php 	
if ($options['profileFieldsConfig'] && !$options['profileFields']) echo '<br><b>Warning: Configuration syntax error! Please review & correct.</b>'; 
?>
<BR>Save to setup. Configure fields as sections with type (text/textarea/select/checkboxes), instructions, options (separated by /), as necessary.
<br>If a value  contains any non-alphanumeric characters it needs to be enclosed in double-quotes ("").

Default:<br><textarea readonly cols="100" rows="6"><?php echo $optionsDefault['profileFieldsConfig']?></textarea>

<BR>Parsed fields configuration (should be an array or arrays):<BR>
<?php

				var_dump($options['profileFields']);
?><BR>Serialized:<BR>
<?php

				echo serialize($options['profileFields']);
				break;

			case 'studio':
				?><h3>Studio Settings</h3>
Configure studio options. A studio can create and manage multiple performer accounts and webcam listings. Webmasters can <a href="admin.php?page=live-webcams-studio">assign existing users to studios, as performers</a>.

<h4>Enable Studio Registrations</h4>
<select name="studios" id="studios">
  <option value="0" <?php echo $options['studios']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['studios']?"selected":""?>>Yes</option>
</select>
<br>Enables studios registration role (so user can register as studios). If disabled you will have to promote users to studio role using a different path (manual, paid membership).


<h4>Studio Role Name</h4>
<p>This is used as registration role option and access to studio dashboard page (redirection on login). Administrators can also manually access dashboard page for testing.</p>
<input name="roleStudio" type="text" id="roleStudio" size="20" maxlength="64" value="<?php echo $options['roleStudio']?>"/>
<br>Ex: studio, group, company
<br>Your roles (for troubleshooting):
<?php
				global $current_user;
				foreach($current_user->roles as $role) echo $role;
?>

<h4>Studio Performers</h4>
<input name="studioPerformers" id="studioPerformers" type="text" size="5" maxlength="10" value="<?php echo $options['studioPerformers']?>"/>
<br>Specify maximum number of performers each studio can have. To prevent flood of items, name reservation.

<h4>Studio Webcams</h4>
<input name="studioWebcams" id="studioWebcams" type="text" size="5" maxlength="10" value="<?php echo $options['studioWebcams']?>"/>
<br>Specify maximum number of webcam listings each studio can have. When reached, studio can no longer create new ones, to prevent flood of items, name reservation.

<h4>Studio Dashboard Message (Brief Instructions, News)</h4>
<textarea name="dashboardMessageStudio" id="dashboardMessageStudio" cols="100" rows="4"><?php echo $options['dashboardMessageStudio']?></textarea>
<br>Shows in studio dashboard. Could contain instructions, announcements, links to support.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['dashboardMessageStudio']?></textarea>

<?php
				break;
			case 'video':
?>
<h3>Videos, Pictures, Reviews</h3>
Solution integrates advanced plugins to manage videos, pictures, reviews.

<h4><a target="_plugin" href="https://videosharevod.com/">Video Share VOD</a> - Enable Videos</h4>
<?php
				if (is_plugin_active('video-share-vod/video-share-vod.php')) echo 'Detected:  <a href="admin.php?page=video-share">Configure</a> | <a href="https://videosharevod.com/features/quick-start-tutorial/">Tutorial</a>'; else echo 'Not detected. Please install and activate <a target="_videosharevod" href="https://wordpress.org/plugins/video-share-vod/">VideoShareVOD Plugin</a> from <a href="plugin-install.php">Plugins > Add New</a>!';
?>
<BR><select name="videosharevod" id="videosharevod">
  <option value="0" <?php echo $options['videosharevod']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videosharevod']?"selected":""?>>Yes</option>
</select>
<br>Enables VideoShareVOD integration that allows performers to add videos to their videochat page. Performer role should be allowed to share/publish videos from VSV settings.

<h4>Teaser Offline</h4>
 <select name="teaserOffline" id="teaserOffline">
  <option value="0" <?php echo $options['teaserOffline']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['teaserOffline']?"selected":""?>>Yes</option>
</select>
Play teaser while offline. When selecting teaser also disables any scheduling if enabled.

<h4>VOD HTTP Streaming URL (HLS/MPEG-Dash)</h4>
<input name="hls_vod" type="text" id="hls_vod" size="100" maxlength="256" value="<?php echo $options['hls_vod']?>"/>
<br>This is used for live streaming video files trough streaming server (instead of web server). Available with <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting">Wowza SE Hosting</a> plans. Ex: https://[your-rtmp-server-ip-or-domain]:1935/videowhisper-vod/
<br>Leave blank to play directly trough web server (recommended).

<h4><a target="_plugin" href="https://wordpress.org/plugins/picture-gallery/">Picture Gallery</a> - Enable Pictures</h4>
<?php
				if (is_plugin_active('picture-gallery/picture-gallery.php')) echo 'Detected:  <a href="admin.php?page=picture-gallery">Configure</a>'; else echo 'Not detected. Please install and activate Picture Gallery by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
?>
<BR><select name="picturegallery" id="picturegallery">
  <option value="0" <?php echo $options['picturegallery']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['picturegallery']?"selected":""?>>Yes</option>
</select>
<br>Enables Picture Gallery integration that allows performers to add videos to their videochat page. Performer role should be allowed to share/publish pictures from plugin settings.



<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable Reviews</h4>
<?php
				if (is_plugin_active('rate-star-review/rate-star-review.php')) echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>'; else echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php">Plugins > Add New</a>!';
?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['rateStarReview']?"selected":""?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.


<h3>Integration Settings</h3>

<h4>Save Snapshot Pictures in Private</h4>
<select name="privateSnapshots" id="privateSnapshots">
  <option value="0" <?php echo $options['privateSnapshots']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['privateSnapshots']?"selected":""?>>Yes</option>
</select>
<br>Saves snapshots during private shows. For group shows this is configured for each group from <a href="/admin.php?page=live-webcams&tab=group">group modes</a>.

<h4>Snapshots Saving Interval in Private</h4>
<input name="privateSnapshotsInterval" type="text" id="privateSnapshotsInterval" size="8" maxlength="16" value="<?php echo $options['privateSnapshotsInterval']?>"/>s
<BR>This specifies a minimum interval for saving snapshots. Feature also depends on application settings for generating snapshots (usually 60s).
<?php
				break;
			case 'scheduler':
?>
<h3>Video Playlists Scheduler</h3>
Performers can setup playlists of videos to automatically play in their room while they're not broadcasting live.
<BR>Special Requirements: This feature requires Wowza Streaming Engine as streaming server and hosting both web and rtmp on same physical server, so scripts can write the playlists/upload videos and streaming server can read the playlists and load the videos as scheduled for live streaming.
<BR>Recommended Hosting: <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting#features" target="_vwhost">VideoWhisper Wowza Turnkey Managed Hosting</a> - turnkey rtmp address, configuration for archiving, transcoding streams, delivery to mobiles as HLS, playlists scheduler (video loops as fake performers).

<h4>Enable Performer Video Scheduler (Fake Live Performers)</h4>
<select name="playlists" id="playlists">
  <option value="1" <?php echo $options['playlists']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['playlists']?"":"selected"?>>No</option>
</select>
<BR>Allows performer to schedule playlists (play video as if performer was online). Feature also needs to be enabled for webcam room owners from <a href='admin.php?page=live-webcams&tab=features'>Webcam Room Features</a> : Video Scheduler .
<BR>Requires Video Share VOD for video management, selection.
<BR>This feature requires Wowza Streaming Engine and <a href="https://www.wowza.com/forums/content.php?145-How-to-schedule-streaming-with-Wowza-Streaming-Engine-(StreamPublisher)#installation">specific setup</a>: for VideoWhisper managed <a href="https://videowhisper.com/?p=wowza+media+server+hosting">hosting plans</a> and <a href="https://videowhisper.com/?p=Dedicated+Servers">servers</a> submit a support request for setting this up.

<?php
				if ($disablePlaylist = intval($_GET['disablePlaylist']))
				{
					echo '<h4>Disabling Playlists</h4>';

					$roomPost = get_post($disablePlaylist);
					if (!$roomPost) echo 'Not found: '.$disablePlaylist;
					else
					{
						$stream = get_post_meta( $roomPost->ID, 'performer', true );
						self::updatePlaylist($stream, 0);
						update_post_meta( $roomPost->ID, 'vw_playlistUpdated', time());
						update_post_meta( $roomPost->ID, 'vw_playlistActive', '0');

						echo 'Room: ' . $roomPost->post_title . ' Performer Stream: ' . $stream;
					}
				}
?>

<h4>Streams Path</h4>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<BR>Used for .smil playlists (should be same as streams path configured in VideoShareVOD for RTMP delivery).
<BR> <?php
				echo $options['streamsPath'] . ' : ';
				if (file_exists($options['streamsPath']))
				{
					echo 'Found. ';
					if (is_writable($options['streamsPath'])) echo 'Writable. (OK)';
					else echo 'NOT writable.';
				}
				else echo '<b>NOT found!</b>';

				// update when saving
				if (isset($_POST['playlists']))
				{
					echo '<BR><BR>SMIL updated on settings save.';
					self::updatePlaylistSMIL();
				}

				$streamsPath = self::fixPath($options['streamsPath']);
				$smilPath = $streamsPath . 'playlist.smil';

				if (file_exists($smilPath))
				{
					echo '<br><br>Playlist found: ' . $smilPath;
					$smil = file_get_contents($smilPath);
					echo '<br><textarea readonly cols="100" rows="10">' .htmlentities($smil). '</textarea>';

					self::playlistsTroubleshoot(true, false);
				}

?>
<h4>Active Playlists</h4>
Currently scheduled playlists:
	<?php
				//query
				$args=array(
					'post_type' =>  $options['custom_post'],
					'orderby'       =>  'post_date',
					'order'            => 'DESC',
					'meta_key'   => 'vw_playlistActive',
					'meta_value' => '1',
				);

				$posts = get_posts( $args );

				if (is_array($posts)) if (count($posts))
						foreach ($posts as $post)
						{
							echo '<br> - ' . $post->post_title . ' <a href="admin.php?page=live-webcams&tab=scheduler&disablePlaylist='. $post->ID . '">Disable</a>';
						} else echo 'No active playlists scheduled.';

					break;
			case 'server':
?>
<h3>Server Settings</h3>
Configure hosting options (web and streaming server).
<BR>To be able to run this solution, make sure your hosting environment meets all <a href="https://videowhisper.com/?p=Requirements" target="_blank">requirements</a>, including RTMP hosting.
<BR>Recommended Hosting: <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting#features" target="_vwhost">VideoWhisper Wowza Turnkey Managed Hosting</a> - turnkey rtmp address, configuration for archiving, transcoding streams, delivery to mobiles as HLS, playlists scheduler (video loops as fake performers).
<BR>If you just need plain videochat functionality (without advanced features like archiving, transcoding, video loops, mobile delivery), you can test with <a href="https://hostrtmp.com/" target="_vwhost">HostRTMP</a> turnkey remote plans from $9/month.

<h4>RTMP Address</h4>
<input name="rtmp_server" type="text" id="rtmp_server" size="100" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>*
<BR>A public accessible rtmp hosting server is required by Flash interface and other RTMP clients, with custom videowhisper rtmp side. Ex: rtmp://your-server/videowhisper
<BR><?php echo self::requirementRender('rtmp_server_configure') ?>
<BR>If you don't have a videowhisper rtmp address yet (from a managed rtmp host), but have your own supported RTMP server (see requirements), go to <a href="https://videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application  Setup</a> for  installation details.
<BR>The custom VideoWhisper RTMP side functionality is compulsory as it manages advanced functionality like chat, online user lists, interactions, webcam/microphone status, advanced session control.

<?php
submit_button();
?>

<h4>RTMP Address for Archiving</h4>
<?php
if ($options['rtmp_server'] != $optionsDefault['rtmp_server']) //propagate
if ($options['rtmp_server_archive'] == $optionsDefault['rtmp_server_archive']) 
{
$options['rtmp_server_archive'] = $options['rtmp_server'];
echo 'Save to apply! Suggested: ' . $options['rtmp_server_archive'] . '<br>';
}
?>
<input name="rtmp_server_archive" type="text" id="rtmp_server_archive" size="100" maxlength="256" value="<?php echo $options['rtmp_server_archive']?>"/>
<BR>An address that also archives broadcasts. Recording live stream requires space and causes additional load on server. Use same RTMP address if not needed or using session control.

<h4>RTMP Address for Recording</h4>
<?php
if ($options['rtmp_server'] != $optionsDefault['rtmp_server']) //propagate
if ($options['rtmp_server_record'] == $optionsDefault['rtmp_server_record']) 
{
$options['rtmp_server_record'] = $options['rtmp_server'] . '-record';
echo 'Save to apply! Suggested: ' . $options['rtmp_server_record'] . '<br>';
}
?>
<input name="rtmp_server_record" type="text" id="rtmp_server_record" size="100" maxlength="256" value="<?php echo $options['rtmp_server_record']?>"/>
<BR>An address configured for recording. Used for recording in presentation mode, if enabled by parameters.

<h4>Admin Tool RTMP Address</h4>
<select name="rtmp_server_admin" id="rtmp_server_admin">
  <option value="<?php echo $options['rtmp_server']?>" <?php echo $options['rtmp_server_admin']==$options['rtmp_server']?'selected':''?>>Chat</option>
  <option value="<?php echo $options['rtmp_server_archive']?>" <?php echo ($options['rtmp_server_admin']==$options['rtmp_server_archive'] && $options['rtmp_server']!=$options['rtmp_server_archive'])?'selected':''?>>Archive</option>
  <option value="<?php echo $options['rtmp_server_record']?>" <?php echo $options['rtmp_server_admin']==$options['rtmp_server_record']?'selected':''?>>Record</option>
</select>
<BR>Which RTMP side app will <a href="admin.php?page=live-webcams-admin">live admin tool</a> connect. Current Setting:  <?php echo $options['rtmp_server_admin']?>


<h4>Disable Bandwidth Detection</h4>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h4>Secure Token Key</h4>
<input name="tokenKey" type="text" id="tokenKey" size="32" maxlength="64" value="<?php echo $options['tokenKey']?>"/>
<BR>A <a href="https://videowhisper.com/?p=RTMP+Applications#settings">secure token</a> can be used with ModuleSecureToken in Wowza Streaming Engine. Module needs to be enabled and matching key needs to be configured on RTMP side. Leave as default or setup key to match after setting up RTMP side.

<h4>Web Key, Web Login/Status, Session Control</h4>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="https://videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a>. Configure as documented on <a href="https://videowhisper.com/?p=RTMP-Session-Control#configure">RTMP Session Control Configuration</a>. Application.xml settings in &lt;Root&gt;&lt;Application&gt;&lt;Properties&gt; :<br>

<textarea readonly cols="100" rows="4">
<?php
				$admin_ajax = admin_url() . 'admin-ajax.php';
				$webLogin = htmlentities($admin_ajax."?action=vmls&task=rtmp_login&s=");
				$webLogout = htmlentities($admin_ajax."?action=vmls&task=rtmp_logout&s=");
				$webStatus = htmlentities($admin_ajax."?action=vmls&task=rtmp_status");

				echo  htmlspecialchars("<!-- VideoWhisper.com: RTMP Session Control https://videowhisper.com/?p=rtmp-session-control -->
<Property>
<Name>acceptPlayers</Name>
<Value>true</Value>
</Property>
<Property>
<Name>webLogin</Name>
<Value>$webLogin</Value>
</Property>
<Property>
<Name>webKey</Name>
<Value>".$options['webKey']."</Value>
</Property>
<Property>
<Name>webLogout</Name>
<Value>$webLogout</Value>
</Property>
<Property>
<Name>webStatus</Name>
<Value>$webStatus</Value>
</Property>
")
?>
</textarea>
<BR>This can be configured for multiple applications (chat, archive), that will find confirmation of authorized user on web server by webLogin and will update their sessions using webStatus.
<BR><?php echo self::requirementRender('rtmp_status') ?>


<h4>Web Status, Session Control</h4>
<select name="webStatus" id="webStatus">
  <option value="auto" <?php echo $options['webStatus']=='auto'?"selected":""?>>Auto</option>
  <option value="enabled" <?php echo $options['webStatus']=='enabled'?"selected":""?>>Enabled</option>
  <option value="strict" <?php echo $options['webStatus']=='strict'?"selected":""?>>Strict</option>
  <option value="disabled" <?php echo $options['webStatus']=='disabled'?"selected":""?>>Disabled</option>
</select>
<BR>Auto will automatically enable first time webLogin successful authentication occurs for a broadcaster. Will also configure the server IP restriction.
In Strict mode additional IPs can't be added by webLogin authorisation (not recommended as streaming server may have multiple IPs).
<BR>Warning: webStatus will not work on 3rd party servers without a full mode license for RTMP side (channel online status will not update).
<BR>Benefits of using <a href="https://videowhisper.com/?p=RTMP-Session-Control">RTMP Session Control</a>: advanced support for external encoders like OBS (shows channels as live on site, generates snapshots, usage stats, transcoding), protect rtmp address from external usage (broadcast and playback require the secret keys associated with active site channels), faster availability and updates for transcoding/snapshots.
<BR>Broadcaster can't connect at same time from web broadcasting interface and external encoder with session control (as session name will be rejected as duplicate).
<BR>Certain services or firewalls like Cloudflare may reject access of streaming server. Make sure configured web requests can be called by streaming server.

<h4>Web Status Server IP Restriction</h4>
<input name="rtmp_restrict_ip" type="text" id="rtmp_restrict_ip" size="100" maxlength="512" value="<?php echo $options['rtmp_restrict_ip']?>"/>
<BR>Allow status updates only from configured IP(s). If not defined will configure automatically when first successful webLogin authorisation occurs for a broadcaster. Web status will not work if this is empty or not configured right.
<BR>Some streaming servers use different IPs. All must be added as comma separated values.
<?php

				if (in_array($options['webStatus'], array('enabled', 'strict', 'auto')))
					if (file_exists($path = $options['uploadsPath']. '/_rtmpStatus.txt'))
					{
						$url = self::path2url($path);
						echo 'Found: <a target=_blank href="'.$url.'">last status request</a> ' . date("D M j G:i:s T Y", filemtime($path)) ;
					}
?>

<h4>RTMFP Address</h4>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is
required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<h4>P2P Group</h4>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>
<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams published for recording). Also reduces latency associated with stream start.</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<br>Recommended.

<h4>Support P2P Streaming</h4>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>
<br>Not recommended as P2P is highly dependant on client network and ISP restrictions. Often results in video streaming failure or huge latency.
P2P may be suitable when all clients are in same network or broadcasters have server grade connection (with high upload and dedicated public IP accessible externally).
<BR>Warning: Streaming only P2P disables archiving, transcoding and mobile delivery (HLS) as streams no longer go trough server.

<h4>Always do P2P Streaming</h4>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?php echo $options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysP2P']?"selected":""?>>Yes</option>
</select>

<h4>Uploads Path</h4>
<p>Path where logs and snapshots will be uploaded. Make sure you use a location outside plugin folder to avoid losing logs on updates and plugin uninstallation.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
			<?php
				echo '<br>Default: ' . $optionsDefault['uploadsPath'];
				echo '<br>WordPress Path: ' . get_home_path();
				$upload_dir = wp_upload_dir();
				echo '<br>Uploads Path: ' . $upload_dir['basedir'];
				if (!strstr($options['uploadsPath'], get_home_path() )) echo '<br><b>Warning: Uploaded files may not be accessible by web.</b>';
				echo '<br>WordPress URL: ' . get_site_url();
?>
<br>Windows sample path: C:/Inetpub/vhosts/yoursite.com/httpdocs/wp-content/uploads/vw_webcams

<h4>Streams Path</h4>
<?php
if ($options['streamsPath'] == $optionsDefault['streamsPath'])  
if (file_exists(ABSPATH . 'streams')) 
{
$options['streamsPath'] =  ABSPATH . 'streams';
echo 'Save to apply! Detected: ' . $options['streamsPath'] . '<br>';
}
?>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<BR>Used for .smil playlists (should be same as streams path configured in VideoShareVOD for RTMP delivery).
<BR>Ex: /home/[account]/public_html/streams
<?php
				break;


			case 'webrtc':

				/*
	//?? profile-level-id=64C029

	 "avc1.66.30": {profile:"Baseline", level:3.0, max_bit_rate:10000}
	 //iOS friendly variation (iOS 3.0-3.1.2)
	 "avc1.42001e": {profile:"Baseline", level:3.0, max_bit_rate:10000} ,
	 "avc1.42001f": {profile:"Baseline", level:3.1, max_bit_rate:14000}
	 //other variations ,
	 "avc1.77.30": {profile:"Main", level:3.0, max_bit_rate:10000}
	 //iOS friendly variation (iOS 3.0-3.1.2) ,
	 "avc1.4d001e": {profile:"Main", level:3.0, max_bit_rate:10000} ,
	 "avc1.4d001f": {profile:"Main", level:3.1, max_bit_rate:14000} ,
	 "avc1.4d0028": {profile:"Main", level:4.0, max_bit_rate:20000} ,
	 "avc1.64001f": {profile:"High", level:3.1, max_bit_rate:17500} ,
	 "avc1.640028": {profile:"High", level:4.0, max_bit_rate:25000} ,
	 "avc1.640029": {profile:"High", level:4.1, max_bit_rate:62500}
*/
			
?>
<h3>WebRTC</h3>
WebRTC can be used to broadcast and playback live video in HTML5 browsers. WebRTC is a new real time video communication technology under development and with specific requirements and limitations. Warning: Enabling this without proper server configuration can make streaming functionality unavailable. HTML5 WebRTC is implemented with 2 interfaces: HTML5 Live Streaming & HTML5 Videochat App (recommended).

<h4>Wowza WebRTC WebSocket URL</h4>
<input name="wsURLWebRTC" type="text" id="wsURLWebRTC" size="100" maxlength="256" value="<?php echo $options['wsURLWebRTC']?>"/>
<BR>Wowza WebRTC WebSocket URL (wss with SSL certificate). Formatted as wss://[wowza-server-with-ssl]:[port]/webrtc-session.json .
<BR><?php echo self::requirementRender('wsURLWebRTC_configure') ?>
<BR>Requires a relay WebRTC streaming server  with a SSL certificate. Such setup is available with the <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_vwhost">Turnkey Complete Hosting plans</a>.

<?php 
	submit_button(); 
	
	$wsURLWebRTC_configure = self::requirementDisabled('wsURLWebRTC_configure');
	if ($wsURLWebRTC_configure) $options['webrtc'] = 0;		
?>

<h4>WebRTC</h4>
<select name="webrtc" id="webrtc" <?php echo $wsURLWebRTC_configure ?>>
  <option value="0" <?php echo $options['webrtc']?"":"selected"?>>Disabled</option>
  <option value="1" <?php echo $options['webrtc']=='1'?"selected":""?>>Enabled</option>
  <option value="2" <?php echo $options['webrtc']=='2'?"selected":""?>>Available</option>
  <option value="3" <?php echo $options['webrtc']=='3'?"selected":""?>>Adaptive</option>
  <option value="4" <?php echo $options['webrtc']=='4'?"selected":""?>>Preferred</option>
  <option value="5" <?php echo $options['webrtc']=='5'?"selected":""?>>Only HTML5</option>
  <option value="6" <?php echo $options['webrtc']=='6'?"selected":""?>>Only App</option>
 </select>
<BR>Showing WebRTC published channels as live and snapshots requires RTMP Session Control feature. Warning: Web Status must be enabled, configured and Auto requires accessing with flash applications once to configure server restriction.
<BR>Web Broadcasting: Enabled shows this option for iOS/Android (Auto). Available will show option for broadcast under Flash interface. Adaptive will use depending on source. If Preferred will be used instead of Flash, in Auto mode. Only HTML5 will hide Flash interface option and Only app will show only HTML5 Videochat App option.


<h4>Wowza WebRTC Application Name</h4>
<input name="applicationWebRTC" type="text" id="applicationWebRTC" size="100" maxlength="256" value="<?php echo $options['applicationWebRTC']?>"/>
<BR>Wowza Application Name (configured or WebRTC usage). Ex: videowhisper-webrtc
<BR>Server and application must match RTMP server settings, for streams to be available across protocols. Streams published with WebRTC can be played using advanced Flash player watch interface or as plain live video in browsers that support that.

<h4>RTSP Playback Address</h4>
<input name="rtsp_server" type="text" id="rtsp_server" size="100" maxlength="256" value="<?php echo $options['rtsp_server']?>"/>
<BR>For retrieving WebRTC streams. Ex: rtsp://[your-server]:1935/videowhisper-x
<BR>Access WebRTC (RTSP) stream for snapshots, transcoding for RTMP/HLS/MPEGDASH playback. Use same port 1935 as RTMP for maximum compatibility.

<h4>RTSP Publish Address</h4>
<input name="rtsp_server_publish" type="text" id="rtsp_server_publish" size="100" maxlength="256" value="<?php echo $options['rtsp_server_publish']?>"/>
<BR>For publishing WebRTC streams. Usually requires publishing credentials (for Wowza configured in conf/publish.password). Use same port 1935 as RTMP for maximum compatibility. Ex: rtsp://[user:password@][your-server]:1935/videowhisper-x

<h4>Video Codec</h4>
<select name="webrtcVideoCodec" id="webrtcVideoCodec">
  <option value="42e01f" <?php echo $options['webrtcVideoCodec']=='42e01f'?"selected":""?>>H.264 Profile 42e01f</option>
  <option value="VP8" <?php echo $options['webrtcVideoCodec']=='VP8'?"selected":""?>>VP8</option>
 <!--

     <option value="VP8" <?php echo $options['webrtcVideoCodec']=='VP8'?"selected":""?>>VP8</option>
  <option value="VP9" <?php echo $options['webrtcVideoCodec']=='VP9'?"selected":""?>>VP9</option>

  <option value="420010" <?php echo $options['webrtcVideoCodec']=='420010'?"selected":""?>>H.264 420010</option>
  <option value="420029" <?php echo $options['webrtcVideoCodec']=='420029'?"selected":""?>>H.264 420029</option>

  -->
</select>
<br>Safari supports VP8 from version 12.1 for iOS & PC and H264 in older versions. Because Safari uses hardware encoding for H264, profile may not be suitable for playback without transcoding, depending on device: VP8 is recommended when broadcasting with latest Safari. H264 can also playback directly in HLS, MPEG, Flash without additional transcoding (only audio is transcoded). Using hardware encoding (when functional) involves lower device resource usage and longer battery life.

<h4>Maximum Video Bitrate</h4>
<input name="webrtcVideoBitrate" type="text" id="webrtcVideoBitrate" size="10" maxlength="16" value="<?php echo $options['webrtcVideoBitrate']?>"/> kbps
<BR>Ex: 800. Max 400 for TCP. HTML5 Videochat app will adjust default bitrate and options depending on selected resolution. Very high bitrate setting may be discarded by browsers or result in failures or interruptions due to user connection limits. Application may have lower restrictions. Default: <?php echo $optionsDefault['webrtcVideoBitrate']?>

<h4>Audio Codec</h4>
<select name="webrtcAudioCodec" id="webrtcAudioCodec">
  <option value="opus" <?php echo $options['webrtcAudioCodec']=='opus'?"selected":""?>>Opus</option>
  <option value="vorbis" <?php echo $options['webrtcAudioCodec']=='vorbis'?"selected":""?>>Vorbis</option>
</select>
<BR>Recommended: Opus. 

<h4>Maximum Audio Bitrate</h4>
<input name="webrtcAudioBitrate" type="text" id="webrtcAudioBitrate" size="10" maxlength="16" value="<?php echo $options['webrtcAudioBitrate']?>"/> kbps
<br>Ex: 64 Default: <?php echo $optionsDefault['webrtcAudioBitrate']?>

<h4>FFMPEG Transcoding Parameters for WebRTC Playback (H264 + Opus)</h4>
<input name="ffmpegTranscodeRTC" type="text" id="ffmpegTranscodeRTC" size="100" maxlength="256" value="<?php echo $options['ffmpegTranscodeRTC']?>"/>
<BR>This should convert RTMP stream to H264 baseline restricted video and Opus audio, compatible with most WebRTC supporting browsers.
<br>For most browsers including Chrome, Safari, Firefox: -c:v libx264 -profile:v baseline -level 3.0 -c:a libopus -tune zerolatency
<br>For some browsers like Chrome, Firefox, not Safari, when broadcasting H264 baseline from flash client video can play as is: -c:v copy -c:a libopus

<h4>Transcode streams to WebRTC</h4>
<select name="transcodeRTC" id="transcodeRTC">
  <option value="0" <?php echo $options['transcodeRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Make streams from Safari PC and different sources available for WebRTC playback. Involves processing resources (high CPU & memory load). 

<h4>Transcode streams From WebRTC</h4>
<select name="transcodeFromRTC" id="transcodeFromRTC">
  <option value="0" <?php echo $options['transcodeFromRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeFromRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Not currently in use. Make streams from WebRTC available for HLS/MPEG/RTMP playback. Involves processing resources (high CPU & memory load). 

<h4>WebRTC Implementation</h4>
WebRTC streaming is done trough media server, as relay, for reliability and scalability needed for these solutions.
Conventional out-of-the-box WebRTC solutions require each client to establish and maintain separate connections with every other participant in a complicated network where the bandwidth load increases exponentially as each additional participant is added. For P2P, streaming broadcasters need server grade connections to live stream to multiple users and using a regular home ADSL connection (that has has higher download and bigger upload) causes real issues. These solutions use the powerful streaming server as WebRTC node to overcome scalability and reliability limitations. Solution combines WebRTC HTML5 streaming with relay server streaming for a production ready setup.

<h4>Current Implementation Support and Limitations</h4>
As WebRTC is a new technology under development, implementation support varies depending on browsers and settings. These may change with solution development and technology improvements. Here is current status:
<UL>
	<LI>Chrome: Functional on Android and PC. Supports broadcast and playback. Stream broadcast with Chrome is available in most HTML5 browsers, including Safari as direct WebRTC.</LI>
	<LI>Firefox: Functional, supports broadcasting and playback over UDP. </LI>
	<LI>Other supported browsers: Brave, Tor.</LI>
	<LI>Safari: Functional on iOS. On PC, stream broadcast from Safari may be encoded with high profile setting so transcoding is required.<LI>
</UL>
<?php
				break;

			case 'hls':
				//! HLS & Transcoding
?>
<h3>HTML5: Stream Snapshots, Transcoding, HTML5 HLS / MPEG-Dash Delivery</h3>
Configure HTML5 support for mobile devices and PC. HTML5 interfaces require FFMPEG to extract snapshots.
Plain performer video from Advanced PC interface (Flash Video Chat Messenger) chat can be broadcast to mobile browsers (without the advanced interface or other interactions) as HTML5 HLS/MPEG, trough Wowza SE, after FFMPEG transcoding of non HTML5 stream. 

<h4>FFMPEG</h4>
Special Requirements: This functionality requires FFMPEG with involved codecs on web host and publishing trough Wowza SE server to deliver transcoded streams as HLS / MPEG. Also required to extract snapshots from streams (for listings) and stream codec analysis (to detect if transcoding is necessary).
<BR>Recommended Hosting: <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_vwhost">Complete Turnkey Streaming Hosting</a> - turnkey streaming for all protocols, configuration for archiving, transcoding streams, delivery to mobiles as HLS, WebRTC, playlists scheduler.
<?php

		
		$fexec = 0;
					echo "<br>- exec: ";
				if(function_exists('exec'))
				{
					echo "function is enabled";

					if(exec('echo EXEC') == 'EXEC')
					{
						echo ' and works';
						$fexec =1;
					}
					else echo ' <b>but does not work</b>';

				}else 
				{
					echo '<b>function is not enabled</b><BR>PHP function "exec" is required to run FFMPEG. Current hosting settings are not compatible with this functionality.';
					self::requirementUpdate('ffmpeg',0);		

				}

if ($fexec)
{
				$output = '';
				echo "<BR>- FFMPEG: ";
				$cmd =$options['ffmpegPath'] . ' -version';
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  
				{
					echo "<b>Warning: not detected: $cmd</b>";
					self::requirementUpdate('ffmpeg',0);		
				}
				else
				{
					echo "found";

					if ($returnvalue != 126)
					{	
						if (!$output) echo '<b>Error: No output from FFMPEG, no codecs detected!</b>';
						else self::requirementUpdate('ffmpeg',1);

					}
					else
					{
						echo ' but is NOT executable by current user: ' . $processUser;
						self::requirementUpdate('ffmpeg',0);
					}	

				}
}				
?>
<BR><?php echo self::requirementRender('ffmpeg') ?>

<h4>Live Transcoding</h4>
<?php


				$processUser = get_current_user();
				echo "This section shows transcoding and snapshot retrieval processes currently run by account '$processUser'.<BR>";

if ($fexec)
{
				$cmd = "ps aux | grep 'ffmpeg'";
				$process = exec($cmd, $output, $returnvalue);

				// $processp = explode(' ', $process);
				// $processId = $processp[3];

				// var_dump($processId);


				$transcoders = 0;
				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						if ($processUser == $columns[0] && (!in_array($columns[10],array('sh','grep'))))
						{

							echo "Process #".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3].' Start: '.$columns[8].' CPU Time: '.$columns[9]. ' Cmd: ';
							for ($n=10; $n<24; $n++) echo $columns[$n].' ';

							if ($_GET['kill']== $columns[1])
							{
								$kcmd = 'kill -KILL ' . $columns[1];
								exec($kcmd, $koutput, $kreturnvalue);
								echo ' <B>Killing process...</B>';
							}
							else echo ' <a href="admin.php?page=live-webcams&tab=hls&kill='.$columns[1].'">Kill</a>';

							echo '<br>';
							$transcoders++;
						}
					}

				if (!$transcoders) echo 'No live transcoding/snapshot processes detected.';
				else echo '<BR>Total processes for transcoding/snapshot: ' . $transcoders;

				self::processTimeout('ffmpeg', true, true);
}	

			$ffmpegDisabled = self::requirementDisabled('ffmpeg');
			if ($ffmpegDisabled) $options['transcoding'] = 0;		
?>

<h4>Enable HTML5 Transcoding</h4>
<select name="transcoding" id="transcoding" <?php echo $ffmpegDisabled ?>>
  <option value="0" <?php echo $options['transcoding']?"":"selected"?>>Disabled</option>
  <option value="1" <?php echo $options['transcoding'] == 1 ?"selected":""?>>Enabled</option>
  <option value="2" <?php echo $options['transcoding'] == 2 ?"selected":""?>>Available</option>
  <option value="3" <?php echo $options['transcoding'] == 3 ?"selected":""?>>Adaptive</option>
  <option value="4" <?php echo $options['transcoding'] == 4 ?"selected":""?>>Preferred</option>
</select>

<BR>This enables account level transcoding based on FFMPEG.
<BR>HTML5 Playback: If transcoding is enabled will be played on mobiles (Auto). If Available will be also shown to PC users as option. Adaptive will try to show interface depending on source. If Preferred will be used instead of Flash, in Auto mode.
<BR>Transcoding is required for re-encoding live streams broadcast using web client to new re-encoded streams accessible by iOS/Android using HLS / MPEG Dash. This requires high server processing power for each stream.
<BR>HLS support is also required on RTMP server and this is usually available with <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting#features">Wowza SE Hosting</a> .
<BR>Account level transcoding is not required when stream is already broadcast with external encoders in appropriate formats (H264, AAC with supported settings) or using Wowza Transcoder Addon to transcode streams (usually on dedicated servers) - not currently supported by plugin (can't control access during private shows if HLS is automatically available for all streams).

<h4>Enable HTML AJAX Chat</h4>
<select name="htmlchat" id="htmlchat">
  <option value="0" <?php echo $options['htmlchat']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['htmlchat']?"selected":""?>>Yes</option>
</select>
<BR>Include a HTML AJAX based chat with HTML5 transcoded stream. This involves increased latency and limited interactions compared to main application. Provides access to public chat for mobile users.
<br>Only works on html5 mobile browsers / devices that show the html5 live streaming inline (with other content on same screen).
<BR>Warning: HTML5 solution does not support interactions like requesting private pay per minute show, broadcasting own camera, tips, system messages. Do NOT enable for PC, except for testing purposes.
<BR>Warning: Group modes (including paid group mode) and free time limitations work in HTML mode but involve risks especially on PC (users may keep access to stream with JS browser extensions or exploit tools). For increased access control security, MPEG Dash / HLS Device targeting should not be enabled for PC and used only for mobiles.

<h4>Enable HTML Chat Testing</h4>
<select name="htmlchatTest" id="htmlchatTest">
  <option value="0" <?php echo $options['htmlchatTest']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['htmlchatTest']?"selected":""?>>Yes</option>
</select>
<BR>Test by adding ?htmlchat=1 to webcam url for force HTML5 view. Also test HLS with ?hls=1 or MPEG Dash with ?mpeg=1.
<BR>Disable at production time, to increase security of sessions (users may keep access to stream with JS browser extensions or exploit tools).

<h4>Auto Transcoding</h4>
<select name="transcodingAuto" id="transcodingAuto">
  <option value="0" <?php echo $options['transcodingAuto']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodingAuto']=='1'?"selected":""?>>HLS</option>
  <option value="2" <?php echo $options['transcodingAuto']=='2'?"selected":""?>>Always</option>
</select>
<BR>HLS starts transcoder when HLS is requested (by a mobile user) and Always when broadcast occurs. As HLS latency is usually several seconds, first viewer may not be able to access stream right away.
<BR>Always will also check transcoding status from time to time (when broadcaster updates status). For external broadcasters (desktop/mobile), <a href="https://videowhisper.com/?p=RTMP-Session-Control#configure">RTMP Session Control</a> is required to activate web transcoding - not currently implemented for this plugin.
<BR>Auto transcoding will work only if webcam post <a href="admin.php?page=live-webcams&tab=features">Transcode Feature</a> is enabled.


<h4>Transcode Private Shows</h4>
<select name="transcodeShows" id="transcodeShows">
  <option value="0" <?php echo $options['transcodeShows']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeShows']=='1'?"selected":""?>>Yes</option>
</select>
<BR>Transcoding private shows would allow mobile users to view the stream while performer is in private show (not recommended unless you setup access per room). When disabled transcoding is stopped when performer goes in private so existing and new mobile viewers can't watch. Not required for paid group shows.

<h4>MPEG Dash Device Target</h4>
<select name="detect_mpeg" id="detect_mpeg">
  <option value="" <?php echo $options['detect_mpeg']?"":"selected"?>>None</option>
  <option value="android" <?php echo $options['detect_mpeg']=='android'?"selected":""?>>Android</option>
  <option value="nonsafari" <?php echo $options['detect_mpeg']=='nonsafari'?"selected":""?>>Except Safari</option>
  <option value="all" <?php echo $options['detect_mpeg']=='all'?"selected":""?>>Android & PC</option>
</select>
<BR>Show MPEG Dash for certain types of devices. Does not run on iOS. Do not enable for PC at production time.

<h4>HLS Device Target</h4>
<select name="detect_hls" id="detect_hls">
  <option value="" <?php echo $options['detect_hls']?"":"selected"?>>None</option>
  <option value="ios" <?php echo $options['detect_hls']=='ios'?"selected":""?>>iOS</option>
  <option value="mobile" <?php echo $options['detect_hls']=='mobile'?"selected":""?>>iOS & Android</option>
  <option value="safari" <?php echo $options['detect_hls']=='safari'?"selected":""?>>iOS & PC Safari</option>
  <option value="all" <?php echo $options['detect_hls']=='all'?"selected":""?>>Mobile & PC Safari</option>
</select>
<BR>Show HLS for certain types of devices. Does not overwrite MPEG Dash if enabled. Mobile covers iOS & Android. Do not enable for PC at production time.


<h4>RTMP Address for Publishing Transcoded Stream</h4>
<?php
if ($options['rtmp_server'] != $optionsDefault['rtmp_server']) //propagate
if ($options['rtmp_server_hls'] == $optionsDefault['rtmp_server_hls']) 
{
$options['rtmp_server_hls'] = $options['rtmp_server'];
echo 'Save to apply! Suggested: ' . $options['rtmp_server_hls'] . '<br>';
}
?>
A publicly accessible rtmp hosting server that can also deliver streams over HLS. Available with <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting">HTML5 Streaming Plans</a>. Can be same as main rtmp server used by web applications, if configured to deliver HLS.<BR>
<input name="rtmp_server_hls" type="text" id="rtmp_server_hls" size="100" maxlength="256" value="<?php echo $options['rtmp_server_hls']?>"/>*
<br>Both application and transcoding rtmp address need to be accessible by FFMPEG (domain protection can disable access).
<br>Ex: rtmp://your-server/videowhisper-x


<h4>HTTP Streaming URL (HLS/MPEG-Dash)</h4>
This is used for accessing transcoded streams on HLS playback. Usually available with <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting">HTML5 Streaming Plans</a> .<br>
<input name="httpstreamer" type="text" id="httpstreamer" size="100" maxlength="256" value="<?php echo $options['httpstreamer']?>"/>
<br>HTTPS (SSL) is required by latest browsers.
<BR>External players and encoders (if enabled) are not monitored or controlled by this plugin, unless special <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> is available.
<BR>Application folder must match rtmp application. Ex. http://localhost:1935/videowhisper-x/ works when publishing to rtmp://localhost/videowhisper-x .


<h4>FFMPEG Path</h4>
<input name="ffmpegPath" type="text" id="ffmpegPath" size="100" maxlength="256" value="<?php echo $options['ffmpegPath']?>"/>
<BR> Path to latest FFMPEG. Required for transcoding of web based streams, generating snapshots for external broadcasting applications (requires <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp session control</a> to notify plugin about these streams).
<?php
if ($fexec)
{
				echo '<br>FFMPEG path detection (which ffmpeg): ';
				$cmd = "which ffmpeg";
				$output="";
				exec($cmd, $output, $returnvalue);
				foreach ($output as $outp) echo $outp;
			
	
				$cmd =$options['ffmpegPath'] . ' -codecs';
				exec($cmd, $output, $returnvalue);

						
				//detect codecs
				$hlsAudioCodec = ''; //hlsAudioCodec
				if ($output) if (count($output))
					{
						echo "<br>Codec libraries: ";
							foreach (array('h264', 'vp6','speex', 'nellymoser', 'aacplus', 'vo_aacenc', 'faac', 'fdk_aac', 'vp8', 'vp9', 'opus') as $cod)
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
}					
?>
<BR>You need only 1 AAC codec. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).

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
					$options['ffmpegTranscode'] = "-c:v copy -c:a $hlsAudioCodec -b:a 96k -tune zerolatency ";
					$hlsAudioCodecReadOnly = 'readonly';
				}
?>

<h4>FFMPEG Transcoding Parameters</h4>
<input name="ffmpegTranscode" type="text" id="ffmpegTranscode" size="100" maxlength="256" value="<?php echo $options['ffmpegTranscode']?>" <?php echo $hlsAudioCodecReadOnly ?>/>
<BR>For lower server load and higher performance, web clients should be configured to broadcast video already suitable for target device (H.264 Baseline 3.1 for most iOS devices) so only audio needs to be encoded.
<BR>Ex.(transcode audio for iOS using latest FFMPEG with libfdk_aac): -c:v copy -c:a libfdk_aac -b:a 96k
<BR>Ex.(transcode audio for iOS using latest FFMPEG with native aac): -c:v copy -c:a aac -b:a 96k
<BR>Ex.(transcode audio for iOS using older FFMPEG with libfaac): -vcodec copy -acodec libfaac -ac 2 -ar 22050 -ab 96k
<BR>Ex.(transcode video+audio): -vcodec libx264 -s 480x360 -r 15 -vb 512k -x264opts vbv-maxrate=364:qpmin=4:ref=4 -coder 0 -bf 0 -analyzeduration 0 -level 3.1 -g 30 -maxrate 768k -acodec libfaac -ac 2 -ar 22050 -ab 96k
<BR>For advanced settings see <a href="https://developer.apple.com/library/ios/technotes/tn2224/_index.html#//apple_ref/doc/uid/DTS40009745-CH1-SETTINGSFILES">iOS HLS Supported Codecs<a> and <a href="https://trac.ffmpeg.org/wiki/Encode/AAC">FFMPEG AAC Encoding Guide</a>.

<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Streaming trough the relay RTMP server is most reliable and compulsory for some features like HLS, external player delivery.

<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams
published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Warning: Disabling this can disable HLS delivery and increase starting latency for streams. This should be available as backup streaming solution even if P2P is used (in specific conditions).

<h4>External Transcoder Keys</h4>
<select name="externalKeysTranscoder" id="externalKeysTranscoder">
  <option value="0" <?php echo $options['externalKeysTranscoder']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['externalKeysTranscoder']?"selected":""?>>Yes</option>
</select>
<BR>Direct authentication parameters will be used for transcoder, external stream thumbnails in case webLogin is enabled.

<h4>Web Key</h4>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="https://videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a> - not currently implemented.

<h4>RTMP Snapshot Rest</h4>
<input name="rtmpSnapshotRest" type="text" id="rtmpSnapshotRest" size="32" maxlength="64" value="<?php echo $options['rtmpSnapshotRest']?>"/>
<br>Minimum time to wait before refreshing a snapshot by rtmp (for scheduled streams).

<h4>Unoconv Path</h4>
<input name="unoconvPath" type="text" id="unoconvPath" size="100" maxlength="256" value="<?php echo $options['unoconvPath']?>"/>
<BR>This is required for converting documents to accessible formats, for presentation mode.

<h4>ImageMagick Convert Path</h4>
<input name="convertPath" type="text" id="convertPath" size="100" maxlength="256" value="<?php echo $options['convertPath']?>"/>
<BR>This is required for converting documents to slides, for presentation mode.

<?php
				break;

			case 'support':
				//! Support
				
				self::requirementMet('resources');

?>

<h3>Support Resources</h3>
Solution resources: documentation, tutorials, support.
<p><a href="https://videowhisper.com/tickets_submit.php" class="button primary" >Contact VideoWhisper</a></p>

<h3>Hosting Requirements</h3>
<UL>
<LI><a href="https://videowhisper.com/?p=Requirements">Hosting Requirements</a> This advanced software requires web hosting and rtmp hosting.</LI>
<LI><a href="https://videowhisper.com/?p=RTMP+Hosting">Estimate Hosting Needs</a> Evaluate hosting needs: volume and features.</LI>
<LI><a href="https://hostrtmp.com/compare/">Compare Hosting Options</a> Hosting options starting from $9/month.</LI>
</UL>

<h3>Solution Documentation</h3>
<UL>
<LI><a href="admin.php?page=live-webcams-doc">Backend Documentation</a> Includes tutorial with local links to configure main features, menus, pages.</LI>
<LI><a href="https://paidvideochat.com/features/quick-setup-tutorial/">PaidVideochat Tutorial</a> Setup a turnkey ppv live videochat site.</LI>
<LI><a href="https://videowhisper.com/?p=WordPress-PPV-Live-Webcams">VideoWhisper Plugin Homepage</a> Plugin and application documentation.</LI>
</UL>

<a name="plugins"></a>

<h3>Feature Integration Plugins (Recommended)</h3>

<UL>
<LI><a href="https://wordpress.org/plugins/video-share-vod/">Video Share VOD</a> Add webcam videos, teaser video. Videos can be used to schedule stream while performer is offline, sell video on demand.</LI>
<li><a href="https://wordpress.org/plugins/rate-star-review/" title="Rate Star Review - AJAX Reviews for Content with Star Ratings">Rate Star Review – AJAX Reviews for Content with Star Ratings</a> plugin, integrated for webcam reviews and ratings.</li>
<LI><a href="https://wordpress.org/plugins/picture-gallery/">Picture Gallery</a> Add performer picture galleries, automated snapshots from shows.</LI>
<LI><a href="https://wordpress.org/plugins/paid-membership/">Paid Membership and Content</a> Sell videos (per item) from frontend, sell membership subscriptions. Based on mycred tokens that can be purchased with real money gateways or earned on site.</LI>
<li><a href="https://wordpress.org/plugins/mycred/">myCRED</a> and/or <a href="https://wordpress.org/plugins/woo-wallet/">WooCommerce TeraWallet</a>, integrated for tips.  Configure as described in Tips settings tab.</li>
<LI><a href="https://wordpress.org/plugins/video-posts-webcam-recorder/">Webcam Video Recorder</a> Site users can record videos from webcam. Can also be used to setup reaction recording: record webcam while playing an Youtube video.</LI>
</UL>

<h3>Optimization Plugins (Recommended)</h3>
<UL>
<li><a href="https://wordpress.org/plugins/wp-super-cache/">WP Super Cache</a> (configured to not cache for known users or GET parameters, great for protecting against bot or crawlers eating up site resources)</li>
<li><a href="https://wordpress.org/plugins/wordfence/">WordFence</a> plugin with firewall. Configure to protect by limiting failed login attempts, bot attacks / flood request, scan for malware or vulnerabilities.</li>
<li><a href="https://jetpack.com/?aff=18336&amp;cid=2828082">JetPack</a>: site optimizations (performance, security, monitoring, social media publishing)</li>
<li>HTTPS redirection plugin like <a href="https://wordpress.org/plugins/really-simple-ssl/">Really Simple SSL</a>&nbsp;, if you have a SSL certificate and HTTPS configured (as on VideoWhisper plans). HTTPS is required to broadcast webcam, in latest browsers like Chrome. If you also use HTTP urls (not recommended), disable “Auto replace mixed content” option to avoid breaking external HTTP urls (like HLS).</li>
<li>A SMTP mailing plugin like <a href="https://wordpress.org/plugins/easy-wp-smtp/">Easy WP SMTP</a> and setup a real email account from your hosting backend (setup an email from CPanel) or external (Gmail or other provider), to send emails using SSL and all verifications. This should reduce incidents where users don’t find registration emails due to spam filter triggering. Also instruct users to check their spam folders if they don’t find registration emails. To prevent spam, an <a href="https://wordpress.org/plugins/search/user-verification/">user verification plugin</a> can be added.</li>
 	<li>For basic search engine indexing, make sure your site does not discourage search engine bots from Settings &gt; Reading   (discourage search bots box should not be checked).
Then install a plugin like <a href="https://wordpress.org/plugins/google-sitemap-generator/">Google XML Sitemaps</a> for search engines to quickly find main site pages.</li>
 	<li>For sites with adult content, an <a href="https://wordpress.org/plugins/tags/age-verification/">age verification / confirmation plugin</a> should be deployed. Such sites should also include a page with details for 18 U.S.C. 2257 compliance. For other suggestions related to adult sites, see <a href="https://paidvideochat.com/adult-videochat-business-setup/">Adult Videochat Business Setup</a>.</li>
 	
<li><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> – Automated WordPress backup plugin. Free for local storage.
</UL>

<h3>Turnkey Features Plugins</h3>
<ul>
 	<li><a href="https://woocommerce.com/?aff=18336&amp;cid=2828082">WooCommerce</a> : <em>ecommerce</em> platform</li>
 	<li><a href="https://buddypress.org/">BuddyPress</a> : <em>community</em> (member profiles, activity streams, user groups, messaging)</li>
 	<li><a href="https://woocommerce.com/products/sensei/?aff=18336&amp;cid=2828082">Sensei LMS</a> : <em>learning</em> management system</li>
 	<li><a href="https://bbpress.org/">bbPress</a>: clean discussion <em>forums</em></li>
</ul>

<h3>Premium Plugins / Addons</h3>
<ul>
	<LI><a href="http://themeforest.net/popular_item/by_category?category=wordpress&ref=videowhisper">Premium Themes</a> Professional WordPress themes.</LI>
	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&amp;cid=2828082">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&amp;cid=2828082">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>

<li><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&amp;cid=2828082">WooCommerce Bookings</a> Setup booking products with calendar, <a href="https://woocommerce.com/products/bookings-availability/?aff=18336&amp;cid=2828082">availability</a>, <a href="https://woocommerce.com/products/woocommerce-deposits/?aff=18336&amp;cid=2828082">booking deposits</a>, confirmations for 1 on 1 or group bookings. Include performer room link.</li>

	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&amp;cid=2828082">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>
	
		<LI><a href="https://woocommerce.com/products/product-vendors/?aff=18336&amp;cid=2828082">WooCommerce Product Vendors</a> Allow multiple vendors to sell via your site and in return take a commission on sales. Leverage with <a href="https://woocommerce.com/products/woocommerce-product-reviews-pro/?aff=18336&amp;cid=2828082">Product Reviews Pro</a>.</LI>


	<LI><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> Automated WordPress backup plugin. Free for local storage. For production sites external backups are recommended (premium).</LI>
</ul>

<h3>Contact and Feedback</h3>
<a href="https://videowhisper.com/tickets_submit.php">Sumit a Ticket</a> with your questions, inquiries and VideoWhisper support staff will try to address these as soon as possible.
<br>Although the free license does not include any services (as installation and troubleshooting), VideoWhisper staff can clarify requirements, features, installation steps or suggest additional services like customisations, hosting you may need for your project.

<h3>Review and Discuss</h3>
You can publicly <a href="https://wordpress.org/support/view/plugin-reviews/ppv-live-webcams">review this WP plugin</a> on the official WordPress site (after <a href="https://wordpress.org/support/register.php">registering</a>). You can describe how you use it and mention your site for visibility. You can also post on the <a href="https://wordpress.org/support/plugin/ppv-live-webcams">WP support forums</a> - these are not monitored by support so use a <a href="https://videowhisper.com/tickets_submit.php">ticket</a> if you want to contact VideoWhisper.
<BR>If you like this plugin and decide to order a commercial license or other services from <a href="https://videowhisper.com/">VideoWhisper</a>, use this coupon code for 5% discount: giveme5

<h3>News and Updates</h3>
You can also get connected with VideoWhisper and follow updates using <a href="https://twitter.com/videowhisper"> Twitter </a>.


				<?php
				break;

			case 'translate':
?>

<h3>Translations</h3>
Translate solution in different language.

Software is composed of applications and integration code (plugin) that shows features on WP pages.

<h4>Translation for Solution Features, Pages, HTML5 Videochat</h4>


This plugin is translation ready and can be easily translated started from 'pot' file from languages folder. You can translate for own use and also <a href="https://translate.wordpress.org/projects/wp-plugins/ppv-live-webcams/">contributing translations</a>. 

<br>Sample translations for plugin are available in "languages" plugin folder and you can edit/adjust or add new languages using a translation plugin like <a href="https://wordpress.org/plugins/loco-translate/">Loco Translate</a> : From Loco Translate > Plugins > Paid Videochat Turnkey Site - HTML5 PPV Live Webcams you can edit existing languages or add new languages.
<br>You can also start with an automated translator application like Poedit, translate more texts with Google Translate and at the end have a human translator make final adjustments. You can contact VideoWhisper support and provide links to new translation files if you want these included in future plugin updates. 

<BR>Some customizable labels, custom content and features can be translated from plugin settings, including tabs like <a href="admin.php?page=live-webcams&tab=listings">Cam Listings</a>, <a href="admin.php?page=live-webcams&tab=profile">Profile Fields</a>, <a href="admin.php?page=live-webcams&tab=record">Account Records</a>, <a href="admin.php?page=live-webcams&tab=group">Group Modes</a>.

<h4>Translation Code for Legacy Flash Application</h4>
<?php
				$options['translationCode'] = htmlentities(stripslashes($options['translationCode']));
?>
<textarea name="translationCode" id="translationCode" cols="100" rows="5"><?php echo $options['translationCode']?></textarea>

<br>Flash application texts can be translated from this section. Generate translation code by writing and sending "/videowhisper translation" in chat (contains xml tags with text and translation attributes). Texts are added to list only after being shown once in interface. If any texts don't show up in generated list you can manually add new entries for these. Same translation file is used for all interfaces so setting should cumulate all translation texts.
As translations are configured using XML, any strings containing special chars should be <a target="_xmlencoder" href="http://coderstoolbox.net/string/#!encoding=xml&action=encode&charset=us_ascii">XML Encoded</a>. Make sure translation items are enclosed in a tag (&lt;translations&gt; ... translation item tags ... &lt;/translations&gt;).
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['translationCode']?></textarea>

<?php
				break;


			case 'integration':
				//! Integration Settings
				//preview
				global $current_user;
				get_currentuserinfo();

				$options['custom_post'] = preg_replace('/[^\da-z]/i', '', strtolower($options['custom_post']));


?>

<h3>General Integration Settings</h3>
Customize WordPress integration options for videochat application, including multiple hooks. Each room has an associated webcam listing (custom WP post type).


<h4>Default Webcam Listing Name</h4>
<select name="webcamName" id="webcamName">
  <option value="display_name" <?php echo $options['webcamName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['webcamName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['webcamName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="user_email" <?php echo $options['webcamName']=='user_email'?"selected":""?>>Email (<?php echo $current_user->user_email;?>)</option>
  <option value="ID" <?php echo $options['webcamName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Webcam room name (in webcam listings). Will be used in webcam vanity url (in single camera mode when users can not use custom webcam names).


<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="user_email" <?php echo $options['userName']=='user_email'?"selected":""?>>Email (<?php echo $current_user->user_email;?>)</option>
  <option value="ID" <?php echo $options['userName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Shows as username in chat. In videochat, 2 users can not connect with same username (duplicates will be rejected).


<h4>Webcam Post Name</h4>
<input name="custom_post" type="text" id="custom_post" size="12" maxlength="32" value="<?php echo strtolower($options['custom_post'])?>"/>
<br>Custom post name for webcams (only alphanumeric, lower case). Will be used for webcams urls. Ex: webcam
<br>Save <a href="options-permalink.php">Settings > Permalinks</a> to apply new URL structure and make new post types accessible.
<br>Warning: New settings only applies for new posts. Previous posts (with previous custom type) will no longer be accessible and performers will need to configure new listings. Should be configured before going live. Restoring a previous type, will also restore that.

<h4>Webcam Post Template Filename</h4>
<input name="postTemplate" type="text" id="postTemplate" size="20" maxlength="64" value="<?php echo $options['postTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render webcam post page. Ex: page.php, single.php, full_width.php (templates available with current site theme).
<br><?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_template_directory() . '/' . $options['postTemplate'];
					echo $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
?>
<br>Set "+plugin" to use a minimal template with theme header and footer provided by this plugin, instead of theme templates.
<br>Set "+app" a full page template without theme header/footer.

<h4>Registration Form Roles</h4>
<select name="registrationFormRole" id="registrationFormRole">
  <option value="1" <?php echo $options['registrationFormRole']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['registrationFormRole']?"":"selected"?>>No</option>
</select>
<br>Add roles to registration form so users can register as client or performer. Disable only if you use other roles and assignation system (ie. with a membership plugin).
<br>BuddyPress: This option disabled redirect to BP registration form because that does not include the roles.

<?php
				$options['filterRegex'] = htmlentities(stripslashes($options['filterRegex']));
				$options['filterReplace'] = htmlentities(stripslashes($options['filterReplace']));
?>

<h4>Chat Filter Regex</h4>
<textarea name="filterRegex" id="filterRegex" cols="100" rows="3"><?php echo $options['filterRegex']?></textarea>
<BR>Filter <a href="http://help.adobe.com/en_US/ActionScript/3.0_ProgrammingAS3/WS5b3ccc516d4fbf351e63e3d118a9b90204-7fdb.html" target="_AS3">regular expressions</a>. Default:<?php echo $optionsDefault['filterRegex']?>

<h4>Chat Filter Replace</h4>
<input name="filterReplace" type="text" id="filterReplace" size="100" maxlength="256" value="<?php echo $options['filterReplace']?>"/>
<BR>Default:<?php echo $optionsDefault['filterReplace']?>

<h4>Banned Words in Names</h4>
<textarea name="bannedNames" cols="64" rows="3" id="bannedNames"><?php echo $options['bannedNames']?>
</textarea>
<br>Users trying to broadcast/access rooms using these words will be disconnected.


<h4>Webfilter Chat Processing</h4>
<select name="webfilter" id="webfilter">
  <option value="1" <?php echo $options['webfilter']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['webfilter']?"":"selected"?>>No</option>
</select>
<BR>Enable processing user submitted chat texts trough web server. Can have a negative performance impact. Not required for regex filtering.

	 <?php
				break;

case 'appearance':		

	self::requirementMet('appearance');
		
?>
<h3>Appearance</h3>
Customize appearance, styling.			

<h4>Registration and Login Logo</h4>
<input name="loginLogo" type="text" id="loginLogo" size="100" maxlength="200" value="<?php echo $options['loginLogo']?>"/>
<br>Logo image to show on registration & login form, replacing default WordPress logo for a turnkey site. Leave blank to disable. Recommended size: 240x80.
<?php echo $options['loginLogo']?"<BR><img src='".$options['loginLogo']."'>":'';?>

<h4>Interface Class(es)</h4>
<input name="interfaceClass" type="text" id="interfaceClass" size="30" maxlength="128" value="<?php echo $options['interfaceClass']?>"/>
<br>Extra class to apply to interface (using Semantic UI). Use inverted when theme uses a dark mode (a dark background with white text) or for contrast. Ex: inverted
<br>Some common Semantic UI classes: inverted = dark mode or contrast, basic = no formatting, secondary/tertiary = greys, red/orange/yellow/olive/green/teal/blue/violet/purple/pink/brown/grey/black = colors . Multiple classes can be combined, divided by space. Ex: inverted, basic pink, secondary green, secondary basic
<h4>Floating Logo / Watermark</h4>
<input name="overLogo" type="text" id="overLogo" size="100" maxlength="256" value="<?php echo $options['overLogo']?>"/>
<br>Shows over live video. Default: <?php echo $optionsDefault['overLogo']?>

<?php echo $options['overLogo']?"<BR><img src='".$options['overLogo']."'>":'';?>
<h4>Logo Link</h4>
<input name="overLink" type="text" id="overLink" size="100" maxlength="256" value="<?php echo $options['overLink']?>"/>

<h4>Loader GIF</h4>
<input name="loaderGIF" type="text" id="loaderGIF" size="100" maxlength="256" value="<?php echo $options['loaderGIF']?>"/>
<br>Animated GIF to show in videochat app while interface elements are loaded.
<?php echo $options['loaderGIF']?"<BR><img src='".$options['loaderGIF']."'>":'';?>

<?php submit_button(); ?>

<p> + <strong>Theme</strong>: Get a <a href="http://themeforest.net/popular_item/by_category?category=wordpress&amp;ref=videowhisper">professional WordPress theme</a> to skin site, change design.<br>
A theme with wide content area (preferably full page width) should be used so videochat interface can use most of the space.<br>
Also plugin hooks into WP registration to implement a role selector: a theme that manages registration in a different custom page should be compatible with WP hooks to show the role option, unless you manage roles in a different way.<br>
Tutorial: <a href="https://en.support.wordpress.com/themes/uploading-setting-up-custom-themes/">Upload and Setup Custom WP Theme</a><br>
Sample themes: <a href="http://themeforest.net/item/jupiter-multipurpose-responsive-theme/5177775?ref=videowhisper">Jupiter</a>, <a href="http://themeforest.net/item/impreza-retina-responsive-wordpress-theme/6434280?ref=videowhisper">Impreza</a>, <a href="http://themeforest.net/item/elision-retina-multipurpose-wordpress-theme/6382990?ref=videowhisper">Elision</a>, <a href="http://themeforest.net/item/sweet-date-more-than-a-wordpress-dating-theme/4994573?ref=videowhisper">Sweet Date 4U</a>, <a href="https://themeforest.net/item/aeroland-responsive-app-landing-and-website-wordpress-theme/23314522?ref=videowhisper">AeroLand </a>. Most premium themes should work fine, these are just some we deployed in some projects.</p>

<p> + <strong>Logo</strong>: You can start from a <a href="http://graphicriver.net/search?utf8=%E2%9C%93&amp;order_by=sales&amp;term=video&amp;page=1&amp;category=logo-templates&amp;ref=videowhisper">professional logo template</a>. Logos can be configured from plugin settings, Integration tab and by default load from images in own installation.</p>

<p> + <strong>Design/Interface adjustments</strong>:
After selecting a theme to start from, that can be customized by a web designer experienced with WP themes. A WP designer can also create a custom theme (that meets WP coding requirements and standards).
Solution specific CSS (like for listings and user dashboards) can be edited in plugin backend.
Content on videochat page is generated by shortcodes from multiple plugins: videochat, profile fields, videos, pictures, ratings. There are multiple settings and CSS. Shortcodes are documented in plugin backend and can be added to pages, posts, templates.
Flash videochat skin graphics can be edited by replacing interface images in a templates folder as described in plugin backend. Videochat application layout and functional parameters can be edited in plugin settings.
HTML5 interface elements can customized by extra CSS. A lot of core styling is done with Semantic UI.
VideoWhisper developers can add additional options, settings to ease up customizations, for additional fees depending on exact customization requirements.
</p>
<?
break;
			case 'listings':
				//! Listings

				$options['labelsConfig'] = htmlentities(stripslashes($options['labelsConfig']));

?>
<h3>Webcam Listings</h3>
Customize listings (mainly for [videowhisper_webcams] shortcode).
<BR><a href="edit.php?post_type=<?php echo $options['custom_post']?>">Manage webcam listings</a> created by performers: "Quick Edit" to set custom earning ratio, custom cost per minute for private show, featured listings.

<h4>Default Listing Layout</h4>
<select name="layoutDefault" id="layoutDefault">
  <option value="grid" <?php echo $options['layoutDefault']=='grid'?"selected":""?>>Preview Grid</option>
  <option value="list" <?php echo $options['layoutDefault']=='list'?"selected":""?>>Full Row List</option>
</select>
<br>Preview Grid layout shows brief info over webcam preview (for video centered sites). 
<br>Full Row List layout shows each room on a row, with more details like full room description, a featured (top available) review for (information/consultation centered sites).

<h4>Webcam Thumb Width</h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4>Webcam Thumb Height</h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>

<h4>Default Webcams Per Page</h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>

<h4>Credits/Tokens Currency Label</h4>
<input name="currency" type="text" id="currency" size="8" maxlength="12" value="<?php echo $options['currency']?>"/>

<h4>Credits/Tokens Currency Per Minute Label</h4>
<input name="currencypm" type="text" id="currencypm" size="8" maxlength="20" value="<?php echo $options['currencypm']?>"/>


<h4>Labels</h4>
<textarea name="labelsConfig" id="labelsConfig" cols="100" rows="5"><?php echo $options['labelsConfig']?></textarea>
<BR>Configure custom labels.

Default:<br><textarea readonly cols="100" rows="5"><?php echo $optionsDefault['labelsConfig']?></textarea>

<BR>Parsed configuration (should be an array):<BR>
<?php

				var_dump($options['labels']);
?>
<BR>Serialized:<BR>
<?php

				echo serialize($options['labels']);
?>

<h4>AJAX Listings CSS</h4>
<?php
				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));


?>
<textarea name="customCSS" id="customCSS" cols="100" rows="8"><?php echo $options['customCSS']?></textarea>
<br>
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['customCSS']?></textarea>

<h4>Listing Template: Grid</h4>
<?php
				$options['listingTemplate'] = htmlentities(stripslashes($options['listingTemplate']));

?>
<textarea name="listingTemplate" id="listingTemplate" cols="100" rows="8"><?php echo $options['listingTemplate']?></textarea>
<br>These tags are supported in all listing templates: '#name#', '#age#', '#clientCPM#', '#roomBrief#', '#roomTags#', '#url#', '#snapshot#','#thumbWidth#', '#thumbHeight#', '#banLink#', '#groupMode#', '#groupCPM#', '#performers#', '#currency#', '#preview#', '#enter#', '#paidSessionsPrivate#', '#paidSessionsGroup#', '#rating#', '#performerStatus#', '#roomCategory#', '#featuredReview#', '#roomDescription#', '#featured#'.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['listingTemplate']?></textarea>
<br>#performerStatus# : offline/public/private (use for applying different css, not translated)
<br>#paidSessionsPrivate#, #paidSessionsGroup#' : number of paid sessions (depending on logging settings)
<br>#clientCPM#, #groupCPM# : cost per minute in private and group sessions
<br>#age# : last time online (LIVE for live performers)
<br>#enter#  : enter button

<h4>Emphasized Listings</h4>
<select name="listingBig" id="listingBig">
  <option value="0" <?php echo $options['listingBig']=='0'?"selected":""?>>No</option>
  <option value="1" <?php echo $options['listingBig']=='1'?"selected":""?>>First</option>
  <option value="5" <?php echo $options['listingBig']=='5'?"selected":""?>>1 emphasized, 4 normal</option>
  <option value="9" <?php echo $options['listingBig']=='9'?"selected":""?>>1 emphasized, 8 normal</option>
</select>
<br>Show some listings emphasized on page to bring more attention. These can have special CSS.


<h4>Listing Template: Grid, Emphasized</h4>
<?php
				$options['listingTemplate2'] = htmlentities(stripslashes($options['listingTemplate2']));

?>
<textarea name="listingTemplate2" id="listingTemplate2" cols="100" rows="8"><?php echo $options['listingTemplate2']?></textarea>
<br>Template for emphasized listing on each page. Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['listingTemplate2']?></textarea>


<h4>Listing Template: List</h4>
<?php
				$options['listingTemplateList'] = htmlentities(stripslashes($options['listingTemplateList']));

?>
<textarea name="listingTemplateList" id="listingTemplateList" cols="100" rows="8"><?php echo $options['listingTemplateList']?></textarea>
<br>Template for listing in List layout. Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['listingTemplateList']?></textarea>



<h4>Max Viewers Count on Webcam Page</h4>
<select name="viewersCount" id="viewersCount">
  <option value="1" <?php echo $options['viewersCount']=='1'?"selected":""?>>Show</option>
  <option value="0" <?php echo $options['viewersCount']=='0'?"selected":""?>>Hide</option>
</select>
<br>Display maximum viewers ever count on webcam page.

<h4>Paid Session Sales Count on Webcam Page</h4>
<select name="salesCount" id="salesCount">
  <option value="1" <?php echo $options['salesCount']=='1'?"selected":""?>>Show</option>
  <option value="0" <?php echo $options['salesCount']=='0'?"selected":""?>>Hide</option>
</select>
<br>Display paid sessions (sales count) on webcam page. Based on session logs (configurable time to keep).

<h4>Webcam Link for Enter</h4>
<select name="webcamLink" id="webcamLink">
  <option value="room" <?php echo $options['webcamLink']=='room'?"selected":""?>>Room</option>
  <option value="custom" <?php echo $options['webcamLink']=='custom'?"selected":""?>>Custom</option>
  <option value="auto" <?php echo $options['webcamLink']=='auto'?"selected":""?>>Auto</option>
</select>
<br>Enter room button can link to default room page or to a custom link. Auto will link to custom room link only if defined.

<h4>Default Custom Webcam Link Prefix</h4>
<input name="webcamLinkDefault" type="text" id="webcamLinkDefault" size="80" maxlength="128" value="<?php echo $options['webcamLinkDefault']?>"/>
<br>Custom link can be configured for webcam room Enter button. Can be set by admin with quick edit from <a href="edit.php?post_type=webcam">webcams listings</a> in backend.
<br>Ex: https://yoursite.com/webcam/ (if webcam is at https://yoursite.com/webcam/[webcam_name]

<h4>Debug Mode</h4>
<select name="debugMode" id="debugMode">
  <option value="1" <?php echo $options['debugMode']=='1'?"selected":""?>>On</option>
  <option value="0" <?php echo $options['debugMode']=='0'?"selected":""?>>Off</option>
</select>
<BR>Outputs debugging info, like query parameters when there are no listings to show.

	 <?php
				break;

			case 'performer':

				//! Performer Settings
				$options['layoutCodePerformer'] = htmlentities(stripslashes($options['layoutCodePerformer']));
				$options['layoutCodePrivatePerformer'] = htmlentities(stripslashes($options['layoutCodePrivatePerformer']));
				$options['parametersPerformer'] = htmlentities(stripslashes($options['parametersPerformer']));
				$options['welcomePerformer'] = htmlentities(stripslashes($options['welcomePerformer']));

				$options['dashboardCSS'] = htmlentities(stripslashes($options['dashboardCSS']));

				$options['rolePerformer'] = sanitize_file_name( $options['rolePerformer'] );
				$options['rolePerformer'] = preg_replace('/[^\da-z]/i', '', strtolower($options['rolePerformer']));


?>
<h3>Performer Settings</h3>

<h4>Performer Role Name</h4>
<p>This is used as registration role option and access to performer dashboard page (redirection on login). Administrators can also manually access dashboard page and setup/access webcam page for testing. Should be 1 role name, without special characters.</p>
<input name="rolePerformer" type="text" id="rolePerformer" size="20" maxlength="64" value="<?php echo $options['rolePerformer']?>"/>
<br>Sample possible values: performer, expert, professional, teacher, trainer, tutor, provider, model, author, expert, artist, medium
<br>Performer role should be configured with other integrated plugins for necessary capabilities (like sharing videos, pictures).
<br>Your roles (for troubleshooting):
<?php
				global $current_user;
				foreach($current_user->roles as $role) echo $role;
?>
<br>Depending of project, performer role should also be configured/updated in settings for other plugins that configure permissions by role (Video Share VOD, Picture Gallery).
<br>Warning: Changing role name will allow only users with new role to access performer dashboard. New role is assigned to <a href="admin.php?page=live-webcams&tab=integration">new registrations if enabled</a>. <a href="users.php">Previously registered users</a> need to be assigned to new role manually.

<h4>Dashboard Welcome Message for Performers</h4>
<?php 
	$options['dashboardMessage'] = stripslashes($options['dashboardMessage']);
	wp_editor( $options['dashboardMessage'], 'dashboardMessage', $settings = array('textarea_rows'=>3) ); 
?> 
<br>Shows in performer dashboard at top. Could contain announcements, instructions, links to support.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['dashboardMessage']?></textarea>


<h4>Dashboard Bottom Message for Performers</h4>
<?php 
	$options['dashboardMessageBottom'] = stripslashes($options['dashboardMessageBottom']);
	wp_editor( $options['dashboardMessageBottom'], 'dashboardMessageBottom', $settings = array('textarea_rows'=>4) ); 
?> 
<br>Shows in performer dashboard at bottom. Could contain instructions, links to support.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['dashboardMessageBottom']?></textarea>



<h4>Webcam Listings per Performer</h4>
<input name="performerWebcams" id="performerWebcams" type="text" size="5" maxlength="10" value="<?php echo $options['performerWebcams']?>"/>
<br>Specify maximum number of webcam listings each performer can have. Limit to prevent flood of items, name reservation.
<br>Set 0 for single cam mode (1 webcam listing per performer automatically generated and selected) and 1 or more to allow multiple webcam select.


<h4>Welcome Message for Performer in Videochat Room</h4>
<textarea name="welcomePerformer" id="welcomePerformer" cols="100" rows="2"><?php echo $options['welcomePerformer']?></textarea>
<br>Shows in chat area when entering own room.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['welcomePerformer']?></textarea>


<h4>Performer Profile Link Prefix</h4>
<input name="performerProfile" type="text" id="performerProfile" size="80" maxlength="128" value="<?php echo $options['performerProfile']?>"/>
<br>Checked in performer links are available in webcam listings. Set blank to disable.
<br>Ex: https://yoursite.com/author/ (if profile is https://yoursite.com/author/[user_nicename]
<BR>Your user_nicename based link (for troubleshooting):
<?php
				echo $options['performerProfile'] . $current_user->user_nicename;
?>

<h4>Parameters for Performer Videochat Interface</h4>
<textarea name="parametersPerformer" id="parametersPerformer" cols="100" rows="10"><?php echo $options['parametersPerformer']?></textarea>
<br>For more details see <a href="https://videowhisper.com/?p=php+video+messenger#integrate">PHP Video Messenger documentation</a>.
<br>The camBandwidth parameter specifies bitrate in b/s (100000b/s = 800kbps) and should not be higher that performer upload connection speed. Do a speed test from broadcasting location to a location near your streaming server (rtmp) using <a href="http://www.speedtest.net">www.speedtest.net</a> (drag & zoom map to server location).
<br>Recommended low latency buffering: 0.2
<br>If activated, disableEmoticons will load CSS from wp-content/plugins/ppv-live-webcams/videowhisper/templates/messenger/styles.css .
Default Parameters:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['parametersPerformer'])?></textarea>


<h4>Custom Layout Code for Performer</h4>
<textarea name="layoutCodePerformer" id="layoutCodePerformer" cols="100" rows="5"><?php echo $options['layoutCodePerformer']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in public chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodePerformer']?></textarea>




<h4>Custom Layout Code in Private for Performer</h4>
<textarea name="layoutCodePrivatePerformer" id="layoutCodePrivatePerformer" cols="100" rows="5"><?php echo $options['layoutCodePrivatePerformer']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in private chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodePrivatePerformer']?></textarea>

<h4>Multiple Camera Views (Webcams) in Videochat</h4>
<input name="multicamMax" id="multicamMax" type="text" size="5" maxlength="10" value="<?php echo $options['multicamMax']?>"/>
<br>Specify number of cameras or 0 to disable. Allows performer to publish additional cameras (streams) from same application, computer (in example integrated webcam + 1 or more usb webcams). Viewers can select stream (angle) to watch from icons bar.

<h4>Dashboard CSS</h4>
<textarea name="dashboardCSS" id="dashboardCSS" cols="100" rows="6"><?php echo $options['dashboardCSS']?></textarea>
<br>
Same as for studio dashboard. Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['dashboardCSS']?></textarea>
	 <?php
				break;


			case 'client':
				//! Client Settings

				$options['layoutCodeClient'] = htmlentities(stripslashes($options['layoutCodeClient']));
				$options['layoutCodePrivateClient'] = htmlentities(stripslashes($options['layoutCodePrivateClient']));

				$options['layoutCodeClient2way'] = htmlentities(stripslashes($options['layoutCodeClient2way']));
				$options['layoutCodePrivateClient2way'] = htmlentities(stripslashes($options['layoutCodePrivateClient2way']));

				$options['parametersClient'] = htmlentities(stripslashes($options['parametersClient']));
				$options['welcomeClient'] = htmlentities(stripslashes($options['welcomeClient']));

				$options['parametersVideo'] = htmlentities(stripslashes($options['parametersVideo']));
				
				$options['roleClient'] = preg_replace('/[^\da-z]/i', '', strtolower($options['roleClient']));


?>
<h3>Client Settings</h3>
Settings for client role and client interface in chat.

<h4>Client Role Name</h4>
<input name="roleClient" type="text" id="roleClient" size="20" maxlength="64" value="<?php echo $options['roleClient']?>"/>
<br>This is used as registration role option.  Should be 1 role name, without special characters.
<br>Sample values: client, customer, student, member, subscriber
<br>Warning: New role is only assigned to <a href="admin.php?page=live-webcams&tab=integration">new registrations if enabled</a>. <a href="users.php">Previously registered users</a> need to be assigned to new role manually.

<h4>Who can access public (free) chat</h4>
<select name="canWatch" id="canWatch">
  <option value="all" <?php echo $options['canWatch']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatch']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatch']=='list'?"selected":""?>>Members in List</option>
</select>
<br>Performers can access their own rooms even if they don't have permissions to access free chat.

<h4>Members allowed to watch video (comma separated usernames, roles, IDs)</h4>
<textarea name="watchList" cols="100" rows="2" id="watchList"><?php echo $options['watchList']?>
</textarea>

<h4>Free Chat Time Limit</h4>
<input name="freeTimeLimit" type="text" id="freeTimeLimit" size="11" maxlength="64" value="<?php echo $options['freeTimeLimit']?>"/>s
<BR>Maximum time (seconds) per day a user can participate in free chat. Calculated based on chat session logs. 
<br>Should be big (ex. 6h) as it's required for users to access lobbies and private shows. Should be lower than 86400 (24h) to prevent users and bots staying in multiple rooms at same time.
<br>Warning: Do not set very low. When reached, user can only visit paid group rooms or wait until next day. Can't enter free lobby to request paid private shows. Default: <?php echo $optionsDefault['freeTimeLimit']?>

<h4>Free Chat Time Limit for Visitors</h4>
<input name="freeTimeLimitVisitor" type="text" id="freeTimeLimitVisitor" size="11" maxlength="64" value="<?php echo $options['freeTimeLimitVisitor']?>"/>s
<BR>Maximum time (seconds) per day a visitor can participate in free chat. Calculated based on chat session logs. Tracked by IP.
<br>Can be lower (ex. 20min) so users register to get more time. When reached, visitor can register or wait until next day. Default: <?php echo $optionsDefault['freeTimeLimitVisitor']?>

<h4>Welcome Message for Client</h4>
<textarea name="welcomeClient" id="parametersPerformer" cols="100" rows="2"><?php echo $options['welcomeClient']?></textarea>

<h4>Parameters for Client Interface</h4>
<textarea name="parametersClient" id="parametersClient" cols="100" rows="10"><?php echo $options['parametersClient']?></textarea>
<br>For more details see <a href="https://videowhisper.com/?p=php+video+messenger#integrate">PHP Video Messenger documentation</a>.
<br>Recommended low latency buffering: 0.2
<br>If activated, disableEmoticons will load CSS from wp-content/plugins/ppv-live-webcams/videowhisper/templates/messenger/styles.css .
Default Parameters:<br><textarea readonly cols="100" rows="3"><?php echo htmlentities($optionsDefault['parametersClient'])?></textarea>

<h4>Custom Layout Code for Client</h4>
<textarea name="layoutCodeClient" id="layoutCodeClient" cols="100" rows="5"><?php echo $options['layoutCodeClient']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in client public chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodeClient']?></textarea>

<h4>Custom Layout Code in Private for Client</h4>
<textarea name="layoutCodePrivateClient" id="layoutCodePrivateClient" cols="100" rows="5"><?php echo $options['layoutCodePrivateClient']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in client private chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodePrivateClient']?></textarea>

<h4>Custom Layout Code for Client in 2 Way</h4>
<textarea name="layoutCodeClient2way" id="layoutCodeClient2way" cols="100" rows="5"><?php echo $options['layoutCodeClient2way']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in client public chat when in 2 way mode (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodeClient2way']?></textarea>

<h4>Custom Layout Code in Private for Client in 2 Way</h4>
<textarea name="layoutCodePrivateClient2way" id="layoutCodePrivateClient2way" cols="100" rows="5"><?php echo $options['layoutCodePrivateClient2way']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in client private chat when in 2 way mode (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['layoutCodePrivateClient2way']?></textarea>


<h4>When Performer Offline</h4>
<select name="performerOffline" id="performerOffline">
  <option value="show" <?php echo $options['performerOffline']=='show'?"selected":""?>>Show Chat</option>
  <option value="warn" <?php echo $options['performerOffline']=='warn'?"selected":""?>>Warn & Show</option>
  <option value="hide" <?php echo $options['performerOffline']=='hide'?"selected":""?>>Hide Chat</option>
</select>
<br>Controls if chat room interface is shown when performer (host) is offline.
<br>When performer is offline the adaptive interface is displayed (HTML5 Videochat if configured). When performer is online, the interface used by performer is displayed. 


<h4>Performer Offline Warning</h4>
<?php
				$options['performerOfflineMessage'] = stripslashes($options['performerOfflineMessage']);
				wp_editor( $options['performerOfflineMessage'], 'performerOfflineMessage', $settings = array('textarea_rows'=>3) ); 

?>
<br>Show this when performer is offline (if enabled).
<br>Does not show for performers rooms with <a href="admin.php?page=live-webcams&tab=video">scheduled videos</a> (fake live performers with video loops).


<h4>Parameters for Plain Video</h4>
<textarea name="parametersVideo" id="parametersVideo" cols="100" rows="2"><?php echo $options['parametersVideo']?></textarea>
<br>This integrates the plain video application from VideoWhisper Live Streaming for [videowhisper_camvideo] and [videowhisper_campreview] shortcodes.<br>Disable sound with noSound=1 . For more details about supported parameters see <a href="https://videowhisper.com/?p=php+live+streaming#integrate">PHP Live Streaming documentation</a>. These parameters apply only to legacy PC Flash app.
<br>Lates versions display HTML5 video stream if configured.
<?php
				break;
			case 'features':
				//! Webcam Room Features
?>
<h3>Webcam Room Features</h3>
Enable webcam room features, accessible by performer.
<br>Specify comma separated list of user roles, emails, logins able to setup these features for their rooms.
<br>Use All to enable for everybody and None or blank to disable.
<?php

				$features = self::roomFeatures();

				foreach ($features as $key=>$feature) if ($feature['installed'])
					{
						echo '<h3>' . $feature['name'] . '</h3>';
						echo '<textarea name="'.$key.'" cols="64" rows="2" id="'.$key.'">'.trim($options[$key]).'</textarea>';
						echo '<br>' . $feature['description'];
					}

				break;


			case 'tips':
?>
<h3>Tips</h3>
<a target="_read" href="https://paidvideochat.com/features/tips/">Read about performer tips ...</a>

<h4>Enable Tips</h4>
<select name="tips" id="tips">
  <option value="1" <?php echo $options['tips']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['tips']?"":"selected"?>>No</option>
</select>
<br>Allows clients to tip performers.

<h4>Tip Options</h4>
<?php
				$tipOptions = stripslashes($options['tipOptions']);
				$options['tipOptions'] = htmlentities(stripslashes($options['tipOptions']));
?>
<textarea name="tipOptions" id="tipOptions" cols="100" rows="8"><?php echo $options['tipOptions']?></textarea>
<br>List of tip options as XML. Sounds and images must be deployed in videowhisper/templates/messenger/tips folder.
Set amount="custom" to allow user to select amount. Custom tips only work in flash App.
Default:<br><textarea readonly cols="100" rows="3"><?php echo $optionsDefault['tipOptions']?></textarea>

<br>Tips data parsed:
<?php

				if ($tipOptions)
				{
					$p = xml_parser_create();
					xml_parse_into_struct($p, trim($tipOptions), $vals, $index);
					$error = xml_get_error_code($p);
					xml_parser_free($p);

					if ($error) echo '<br>Error:' . xml_error_string($error);

					if (is_array($vals)) foreach ($vals as $tKey=>$tip)
							if ($tip['tag'] == 'TIP')
							{
								echo '<br>- ';
								var_dump($tip['attributes']);
							}

				}
?>

<h4>Performer Tip Earning Ratio</h4>
<input name="tipRatio" type="text" id="tipRatio" size="10" maxlength="16" value="<?php echo $options['tipRatio']?>"/>
<br>Performer receives this ratio from client tip.
<br>Ex: 0.9; Set 0 to disable (performer receives nothing). Set 1 for performer to get full amount paid by client.
<br>Site earns depending on performer earning ration (ex. 0.90 ratio for performer leaves 10% to site).
<br>Using  different rates for show minutes and tips could be an incentive for performers to encourage clients to contribute in a certain way.

<h4>Client Tip Cooldown</h4>
<input name="tipCooldown" type="text" id="tipCooldown" size="10" maxlength="16" value="<?php echo $options['tipCooldown']?>"/>s
<BR>A minimum time client has to wait before sending a new tip. This prevents accidental multi tipping and overspending. Set 0 to disable (not recommended).

<h4>Manage Balance Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits.

<?php submit_button(); ?>

<a name="brave"></a>

<h3>Brave Tips and Rewards in Cryptocurrencies</h3>
<a href="https://brave.com/pai553">Brave</a> is a special build of the popular Chrome browser, focused on privacy and speed (by not loading ads), already used by millions. Why use Brave? In addition to privacy and speed, users get grants, airdrops and rewards from ads they are willing to watch. Content creators (publishers) like site owners get tips and automated revenue from visitors. This is done in $BAT and can be converted to other cryptocurrencies like Bitcoin or withdrawn in USD, EUR.
<br>Additionally, with Brave you can easily test if certain site features are disabled by privacy features, cookie restrictions or common ad blocking rules. 
	<p>How to receive contributions and tips for your site:
	<br>+ Get the <a href="https://brave.com/pai553">Brave Browser</a>. You will get a browser wallet, airdrops and get to see how tips and contributions work.
	<br>+ Join <a href="https://creators.brave.com/">Brave Creators Publisher Program</a> and add your site(s) as channels. If you have an established site, you may have automated contributions or tips already available from site users that accessed using Brave. Your site(s) will show with a Verified Publisher badge in Brave browser and users know they can send you tips directly.
	<br>+ You can setup and connect an Uphold wallet to receive your earnings and be able to withdraw to bank account or different wallet. You can select to receive your deposits in various currencies and cryptocurrencies (USD, EUR, BAT, BTC, ETH and many more).
</p>

	<?php
				break;

			case 'ppv':
				//! Pay Per View Settings
?>
<h3>Pay Per Minute Settings</h3>
<a target="_read" href="https://paidvideochat.com/features/pay-per-view-ppv/">Read about pay per view (PPV) and pay per minute (PPM) ...</a>

<h4>Manage Balance Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits. Recommended: My Wallet (setup with <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership & Content</a> plugin).

<h3>Private Show</h3>
For on request private shows (1 on 1) between performer and client.

<h4>Grace Time</h4>
<p>Private video chat is charged per minute after this time.</p>
<input name="ppvGraceTime" type="text" id="ppvGraceTime" size="10" maxlength="16" value="<?php echo $options['ppvGraceTime']?>"/>s
<br>Ex: 30; Set 0 to disable.

<h4>Pay Per Minute Cost for Client</h4>
<p>Paid by client in private video chat.</p>
<input name="ppvPPM" type="text" id="ppvPPM" size="10" maxlength="16" value="<?php echo $options['ppvPPM']?>"/>
<br>Ex: 0.5; Set 0 to disable.
<br>This is default value. If <a href="admin.php?page=live-webcams&tab=features">Cost Per Minute feature is enabled</a>, performers can also setup their own custom CPM.
<br>Admins can edit performer Custom CPM with Quick Edit from <a href="edit.php?post_type=<?php echo $options['custom_post']?>">Webcams</a> list.

<h4>Minimum Pay Per Minute Cost for Client</h4>
<p>Minimum cost per minute configurable by performer (if permitted). Limits both private/group CPM.</p>
<input name="ppvPPMmin" type="text" id="ppvPPMmin" size="10" maxlength="16" value="<?php echo $options['ppvPPMmin']?>"/>
<br>Ex: 0.1

<h4>Maximum Pay Per Minute Cost for Client</h4>
<p>Maximum cost per minute configurable by performer (if permitted). Limits both private/group CPM.</p>
<input name="ppvPPMmax" type="text" id="ppvPPMmax" size="10" maxlength="16" value="<?php echo $options['ppvPPMmax']?>"/>
<br>Ex: 5

<h3>Common PPM Settings</h3>
Apply for private and group pay per view videochat.

<h4>Performer Earning Ratio</h4>
<p>Performer receives this ratio from client charge.</p>
<input name="ppvRatio" type="text" id="ppvRatio" size="10" maxlength="16" value="<?php echo $options['ppvRatio']?>"/>
<br>Ex: 0.8; Set 0 to disable. Set 1 for performer to get full amount paid by client.
<br>Admins can edit Custom Earning Ratio per performer, with Quick Edit from <a href="edit.php?post_type=<?php echo $options['custom_post']?>">Webcams</a> list.
<br>Site earns depending on performer earning ration (ex. 0.80 ratio for performer leaves 20% to site).


<h4>Auto Balance Limitations</h4>
<p>Automatically requires some minimum balance based on the custom CPM settings, to avoid negative balances and big calculation errors related to limitations of how system monitors sessions and calculates billing. </p>
<select name="autoBalanceLimits" id="autoBalanceLimits">
  <option value="1" <?php echo $options['autoBalanceLimits']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['autoBalanceLimits']?"":"selected"?>>No</option>
</select>
<BR>Recommended: Yes. Will calculate Minimum Balance for Show at 5 minutes of CPM and Minimum Balance in Show at 2 minutes of CPM. Will use maximum between calculated and configured limit.

<h4>Minimum Balance for Show</h4>
<p>Only clients that have a minimum balance can request private shows.</p>
<input name="ppvMinimum" type="text" id="ppvMinimum" size="10" maxlength="16" value="<?php echo $options['ppvMinimum']?>"/>
<br>Recommended 3-10 minutes worth of credit. Ex: 1.5; Set 0 to disable.


<h4>Minimum Balance in Show / for Tips</h4>
<p>Only users that have this minimum balance can continue private show or send tips. This reduces negative balance situations (overspending) due to session check/processing delay. Applies both for performer / client when cost exists.</p>
<input name="ppvMinInShow" type="text" id="ppvMinInShow" size="10" maxlength="16" value="<?php echo $options['ppvMinInShow']?>"/>
<br>Recommended 30s-60s worth of credit. Ex: 0.25;

<h4>Pay Per Minute Cost for Performer</h4>
<p>Performers can also be charged for the private video chat time.</p>
<input name="ppvPerformerPPM" type="text" id="ppvPerformerPPM" size="10" maxlength="16" value="<?php echo $options['ppvPerformerPPM']?>"/>s
<br>Ex: 0.10; Set 0 to disable.

<h4>Balance Warning Level</h4>
<input name="balanceWarn1Amount" type="text" id="balanceWarn1Amount" size="10" maxlength="16" value="<?php echo $options['balanceWarn1Amount']?>"/>
<br>Notifies client when balance is low. Does not notify users with 0 balance. Recommended 10-20min worth of credit. Ex: 9.00;

<h4>Balance Warning Message</h4>
<input name="balanceWarn1Message" type="text" id="balanceWarn1Message" size="80" maxlength="250" value="<?php echo $options['balanceWarn1Message']?>"/>

<h4>Balance Critical Level</h4>
<input name="balanceWarn2Amount" type="text" id="balanceWarn2Amount" size="10" maxlength="16" value="<?php echo $options['balanceWarn2Amount']?>"/>
<br>Notifies client when balance is very low. Does not notify users with 0 balance. Recommended 5-10min worth of credit. Ex: 4.00;

<h4>Balance Critical Message</h4>
<input name="balanceWarn2Message" type="text" id="balanceWarn2Message" size="80" maxlength="250" value="<?php echo $options['balanceWarn2Message']?>"/>

<h4>Bill After</h4>
<p>Closed sessions are billed after a minimum time, required for both client computers to update usage time. There's one transaction for entire private session, not for each minute or second. Session durations are aproximated depending on web status calls.</p>
<input name="ppvBillAfter" type="text" id="ppvBillAfter" size="10" maxlength="16" value="<?php echo $options['ppvBillAfter']?>"/>s
<br>Ex. 10s

<h4>Close Sessions</h4>
<p>After some time, close sessions terminated abruptly and delete sessions where users did not enter both, due to client error. After closing, billing can occur for valid sessions. Also used for cleaning online viewers count.</p>
<input name="ppvCloseAfter" type="text" id="ppvCloseAfter" size="10" maxlength="16" value="<?php echo $options['ppvCloseAfter']?>"/>s
<br>Minimum: 30 (lower that statusInterval would cause errors on viewer count and calculations). Ex. 120s

<h4>Keep Logs</h4>
<input name="ppvKeepLogs" type="text" id="ppvKeepLogs" size="10" maxlength="16" value="<?php echo $options['ppvKeepLogs']?>"/>s
<br>Time to keep session logs. Ex: 31536000 (365 days) or 2592000 (30 days). Minimum: 180s.

<h4>Online Timeout</h4>
<input name="onlineTimeout" type="text" id="onlineTimeout" size="10" maxlength="16" value="<?php echo $options['onlineTimeout']?>"/>s
<BR>Should be greater that statusInterval for performers (default 30s). After timeout session can be closed and then can be billed (based on Bill After setting).
<BR>Web applications are monitored by web server with status calls (as configured from <a href="admin.php?page=live-webcams&tab=performer">performer parameters</a>). Sessions length approximation depends on the statusInterval parameter. Having calls too often can cause high load on web server and reduce performance / user capacity so statusInterval should be balanced.

	<?php
				break;

			case 'billing':
?>
<h3>Billing Settings</h3>
Clients can prepay credits (tokens) that show in a site wallet and can be used anytime later in chat to pay for private shows per minute, send tips to performers or access paid content. Tokens can be used for services from different performers and content from different owners, anytime after deposit.
Payments (real money) go into accounts configured by site owner, setup with billing gateways (like Paypal, Zombaio, Stripe).
<BR>Documentation:  <a target="_read" href="https://paidvideochat.com/features/pay-per-view-ppv/#billing">billing features and gateways</a>, <a target="_read" href="https://paidvideochat.com/features/quick-setup-tutorial/#ppv">billing setup</a>.


<h4>Active Wallet</h4>
<select name="wallet" id="wallet">
  <option value="MyCred" <?php echo $options['wallet']=='MyCred'?"selected":""?>>MyCred</option>
  <option value="WooWallet" <?php echo $options['wallet']=='WooWallet'?"selected":""?>>WooWallet</option>
</select>
<BR>Select wallet to use with videochat solution for paid chat and tips.

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

<h3>WooCommerce Wallet (TeraWallet / WooWallet)</h3>
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
WooCommerce Wallet plugin is based on <a href="https://woocommerce.com/?aff=18336&amp;cid=2828082">WooCommerce</a> plugin and allows customers to store their money in a digital wallet. The customers can add money to their wallet using various payment methods set by the admin, available in WooCommerce. The customers can also use the wallet money for purchasing products from the WooCommerce store.
<br> + Configure WooCommerce payment gateways from <a target="_gateways" href="admin.php?page=wc-settings&tab=checkout">WooCommerce > Settings, Payments tab</a>.
<br> + Enable payment gateways from <a target="_gateways" href="admin.php?page=woo-wallet-settings">Woo Wallet Settings</a>.
<br> + Setup a page for users to buy credits with shortcode [woo-wallet]. My Wallet section is also available in WooCommerce My Account page (/my-account).


<h4>Premium WooCommerce Plugins</h4>
<ul>
	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&amp;cid=2828082">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&amp;cid=2828082">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&amp;cid=2828082">WooCommerce Bookings</a> Let your customers book reservations, appointments on their own.</LI>

	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&amp;cid=2828082">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>

</ul>


<h3>myCRED Wallet (MyCred)</h3>

<h4>1) myCRED</h4>
<?php
				if (is_plugin_active('mycred/mycred.php')) echo 'MyCred Plugin Detected'; else echo 'Not detected. Please install and activate <a target="_mycred" href="https://wordpress.org/plugins/mycred/">myCRED</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

				if (function_exists( 'mycred_get_users_balance'))
				{
					$balance = mycred_get_users_balance(get_current_user_id());

					echo '<br>Testing balance: You have ' . $balance  .' '. htmlspecialchars($options['currencyLong']) . '. ';

					if (!strlen($balance)) echo 'Warning: No balance detected! Unless this account is excluded, there should be a MyCred balance. MyCred plugin may not be configured/enabled correctly.';
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
<?php
				break;


			}
			if (!in_array($active_tab, array( 'setup','support', 'reset', 'requirements', 'billing', 'tips', 'appearance')) ) submit_button();

			echo '</form>';
			echo '<style>
.vwInfo
{
background-color: #fffffa;
padding: 8px;
margin: 8px;
border-radius: 4px;	
display:block;
border: #999 1px solid;
box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
</style>';

		}


		//! Feature Pages and Menus
		static function setupPages()
		{
			$options = get_option('VWliveWebcamsOptions');
			if ($options['disableSetupPages']) return;

			//shortcode pages
			$pages = array(
				'videowhisper_webcams' => __('Webcams', 'ppv-live-webcams'),
				'videowhisper_webcams_performer' => __('Performer Dashboard', 'ppv-live-webcams'),
				'mycred_buy_form' => __('Buy Credits', 'ppv-live-webcams'),
				'videowhisper_webcams_studio' => __('Studio Dashboard', 'ppv-live-webcams'),
				'videowhisper_webcams_logout' => __('Chat Logout', 'ppv-live-webcams'),
				'videowhisper_cam_random' =>  __('Random Cam', 'ppv-live-webcams'),
				'videowhisper_webcams_client' => __('Client Dashboard', 'ppv-live-webcams'),
			);

			$noMenu = array('videowhisper_webcams_logout');

			//create a menu and add pages
			$menu_name = 'VideoWhisper';
			$menu_exists = wp_get_nav_menu_object( $menu_name );

			if (!$menu_exists) $menu_id = wp_create_nav_menu($menu_name);
			else $menu_id = $menu_exists->term_id;

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
					$page['post_status']  = 'publish';
					$page['post_title']   = $value;
					$page['comment_status'] = 'closed';

					$pid = wp_insert_post ($page);

					$options['p_'.$key] = $pid;
					$link = get_permalink( $pid);

					if (!in_array($key, $noMenu))
						if ($menu_id) wp_update_nav_menu_item($menu_id, 0, array(
									'menu-item-title' =>  $value,
									'menu-item-url' => $link,
									'menu-item-status' => 'publish'));

				}

			}

			update_option('VWliveWebcamsOptions', $options);
		}

}