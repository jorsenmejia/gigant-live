=== Video Share VOD - Turnkey Video Site Builder ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: Video Share VOD
Plugin URI: https://videosharevod.com
Donate link: https://videowhisper.com/?p=Invest
Tags: video, share, VOD, HTML5, RTMP, HLS, MP4, mbr, Strobe, player, video on demand, media, snapshot, thumbnail, FFMPEG, playlist, netflix, hulu, SVOD, membership, paid, subscription, mobile, upload, stream, turnkey, shortcode, page, Post, posts, admin, VAST, AVOD, pre-roll, ads, over-the-top, OTT, on-demand, HD, 4K, widget, IMA, GAN, DFP, DoubleClick, AdSense, recorder
Requires at least: 2.7
Tested up to: 5.2
Stable tag: trunk

Video Share / Video on Demand (VOD) plugin allows WordPress users and admins to share videos and others to watch from various devices.

== Description ==

[Live Video Site Demo](https://demo.videosharevod.com/)

= Key Features =
* adds video post type to WordPress site
* shortcodes, pages with video site features: browse videos, upload, import
* extracts thumbnail, generates feature image
* generates thumbnail sized short video preview (play on mouse hover)
* extracts info: duration, resolution, bitrate, file size
* multiple playback methods
* playlist taxonomy, listing of videos with rest of posts in categories, tags, searches
* shortcodes for listing videos, displaying player, upload form, import form
* HTML5 VAST (video ad serving template) support for video ads
* HTML5 Google IMA support: DoubleClick & AdSense support
* premium users that don't see ads
* mass video upload
* mass video import (from server)
* setup user types that can share videos
* pending video / approval for user types that can't publish directly
* conversion queue for server load control
* configure codecs and encoding options, formats, bitrate
* see more [Video Share VOD Features](https://videosharevod.com/features/
 "Video Share VOD Features") ...
 
= Listings = 
* AJAX display and update of video list (does not reload site for filter, sort, next page)
* video preview in list (play on hover)
* Filter by category, tags, name search
* Sort by date, views, recently viewed, random, rating, rating number, rate popularity
* integrates [Rate Star Review - AJAX Reviews for Content, with Star Ratings](https://wordpress.org/plugins/rate-star-review/ "Rate Star Review - AJAX Reviews for Content, with Star Ratings")


= VOD Access Control : Membership, Sales =
* define global video access list (roles, user emails & ids)
* role playlists: assign videos as accessible by certain roles
* exception playlists: free, registered, unpublished
* show preview and custom message when inaccessible
* read more about [Video On Demand](https://videosharevod.com/features/video-on-demand/ "Video On Demand") ...
* integrates [Paid Membership and Content](https://wordpress.org/plugins/paid-membership/ "Paid Membership and Content") plugin to allow selling items

= Players =
* HTML5 video conversion and playback support
* RTMP playback support (fast skip, no direct access to video files)
* HLS playback support (rtmp alternative for iOS)
* HD video support (player adapts to video size)
* HTML5 native tag player
* Video.js player with VAST support
* MediaElement.js (WordPress default video player)
* Strobe Flash player

= HTML5 Video Uploader =
* Drag & Drop
* AJAX (no Submit, page reload required to upload more videos)
* multi video support
* status / progress bar for each upload
* upredictable secure upload file names
* fallback to standard upload for older browsers
* mobile video upload (iOS6+, Android 3+)
* backend multi upload menu
* read more about [Video Uploader](http:s//videosharevod.com/features/video-uploader/ "Video Uploader") ...

= Live Streaming Plugin =
* integrates with [VideoWhisper Live Streaming](https://wordpress.org/plugins/videowhisper-live-streaming-integration/ "VideoWhisper Live Streaming") channels plugin
* import archived video streams (previous broadcasts)
* upload additional videos for each channel
* list videos on channel page
* channel button on video page (if channel exists)
* read more about [Live Streaming](https://videosharevod.com/features/live-streaming/ "Live Streaming") ...
* see [Broadcast Live Video](https://broadcastlivevideo.com/ "Broadcast Live Video Camera Script") turnkey solution ...

= Webcam Recording Plugin =
* integrates with [VideoWhisper Video Posts Webcam Recorder](https://wordpress.org/plugins/video-posts-webcam-recorder/ "VideoWhisper Video Posts Webcam Recorder") for video recording
* integrates with [VideoWhisper Video Comments Webcam Recorder](https://wordpress.org/plugins/video-comments-webcam-recorder/ "Video Comments Webcam Recorder
") for video recording comments (including in BuddyPress activity)
* recorder access shortcode "videowhisper_recorder" integrates VideoShareVOD sharing permissions
* read more about [Video Posts Webcam Recorder](https://www.videowhisper.com/?p=WordPress+Video+Recorder+Posts+Comments "Video Posts Webcam Recorder") ...

= Special Requirements =
* FFMPEG and codecs are required to generate snapshots and convert videos.
* Conversions require important resources like CPU time, memory, long process time (not available on budget shared hosting).
* Optionally, to use RTMP playback, RTMP hosting is required.
* Optionally, for HLS playback, a server with HLS support like Wowza is required.
* read more about [Video Share VOD Hosting](https://videosharevod.com/hosting/ "Video Share VOD Hosting") ...


== Screenshots ==
1. Video list (AJAX load and update, pagination, info)
2. HTML5 video upload (Multi file, AJAX, Drag & Drop, fallback (standard upload as backup), iOS & Android support)
3. RTMP player support (fast search, no direct file access, HD)
4. HTML5 player (plain and HLS, video conversion for mobile)
5. Admin settings (VOD setup)
6. VOD access roles playlists, custom message
7. Live Streaming channel management (with archive import and video upload)
8. Select category, order by date/views/watch time, move to another page with AJAX

== Documentation ==
* [Video Site Plugin Homepage](https://videosharevod.com)
* [Turnkey Video Site Installation Tutorial](https://videosharevod.com/features/quick-start-tutorial/)
* [Developer Contact](https://videowhisper.com/tickets_submit.php)
* [Recommended Hosting](https://videosharevod.com/hosting/)

= Shortcodes =
* videowhisper_videos playlist="" perpage="" perrow="" - Video list.
* videowhisper_upload playlist="" category="" owner="" - Upload form.
* videowhisper_player video="0" - Video player.
* videowhisper_preview video="0" - Preview only.
* videowhisper_player_html source="" source_type="" poster="" width="" height="" - HTML file player.
* videowhisper_embed_code source="" source_type="" poster="" width="" height="" - Embed code HTML player.

For more details see Video Share VOD - Documentation menu after installing plugin.

== Demo ==
[Live Video Site Demo](https://demo.videosharevod.com/)


== Frequently Asked Questions ==
* Q: How much does this video plugin cost?
A: This plugin is FREE. 

* Q: Does this video plugin work on mobiles?
A: Yes, videos are converted to formats accessible on mobiles and displayed with special players.
Uploading videos also works on latest mobiles. In example on iOS6+ user will be prompted to record a video or select on from camera roll when pressing Choose Files button.

* Q: Can I run this video site plugin on my shared hosting plan?
A: Only if plan includes FFMPEG with support for codecs you use in your videos and H264, AAC for html5 conversion. Also resource limits should permit running FFMPEG and good bitrate for delivering your videos - may not work on low cost budget hosting.
Special functionality like RTMP and HLS playback demands hosting that support it.

* Q: What exactly is VOD?
A: Video On Demand allow users to select and watch/listen to video or audio content when they choose to, rather than having to watch at a specific broadcast time. For more details see https://videosharevod.com/video-on-demand/ .

* Q: How does a VOD site generate income?
A: By selling access to individual videos, subscriptions / membership to access all or bundled content and advertising.


== Changelog ==

= 2.3 = 
* Upgraded Video JS to 7.4+
* Upgraded playlists, ads scripts

= 2.2 = 
* Admin bar menus for quick turnkey site feature access
* Automated configuration of AAC codec based on detection

= 2.1 =
* Integrated Rate Star Review AJAX ratings
* Filter by tags, search by name
* Multi instance widgets (show multiple lists on same page)
* Default sorting setting (including ratings)

= 1.9 =
* Integrated Semantic UI interface
* Updates and adjustments for latest browsers, PHP 7

= 1.8 =
* Video preview in listings (on hover)
* Integration Support: Assign video to post (ex. video teaser for PPV Live Webcams plugin)
* Troubleshoot conversions from video list

= 1.7 =
* Space usage statistics (by conversion type)
* Cleanup tool to delete source files and HLS segments
* Improved listings
* Export video links

= 1.6 =
* Conversion watermark hard coded in video
* Auto convert high format when original exceeds recommend bitrate level
* Cloudlinux requirements
* Custom video page template and fixed video width support
* Custom vide post type name (and slug)
* Support for integration: Post videos (ex. PPV Live Webcams plugin webcam videos)

= 1.5 =
* Adaptive bitrate for Strobe RTMP using sources from /mbr/rtmp/#videoid.f4m
* Adaptive bitrate for VideoJS HLS using sources from /mbr/hls/#videoid.m3u8
* Static conversion segmentation for HLS playback as .ts files with .m3u8 index (alternate solution to using a HLS server)

= 1.4.12=
* Clean old videos from import path after a number of days (useful on live streaming sites where archiving is enabled)

= 1.4.1 =
* Conversion queue, cron and settings for load control

= 1.3.1 =
* Embed code with download links and permissions

= 1.2.4 =
* TV Shows custom post type allows managing TV shows. Videos can be assigned as episode to TV Show playlists.

= 1.2.3 =
* VideoJS Google IMA Support: ad requests to  DoubleClick for Publishers (DFP), the Google AdSense network for Video (AFV) or Games (AFG) or any VAST-compliant ad server

= 1.2.2 =
* Easy translation support with POT file for most important texts
* Setup user types that can share videos / publish directly
* Pending videos and approval on admin side

= 1.2.1 =
* custom playlist template with videojs html5 player

= 1.1.8 =
* widget to list videos with various options
* ajax controls to select category, order

= 1.1.7 =
* Mass import videos
* Premium users (no video ads)

= 1.1.5 =
* Video.js HTML5 player with VAST support

= 1.1.5 =
* VOD global access list
* VOD role playlists
* VOD free, registered, unpublished playlist exceptions
* multi video upload from backend
* WP default player (MediaElement.js)

= 1.1.4 =
* ** HTML5 Video Uploader *
* Drag & Drop
* AJAX (no Submit, page reload required to upload more videos)
* multi video support
* status / progress bar for each upload
* upredictable secure upload file names
* fallback to standard upload for older browsers
* mobile video upload (iOS6+, Android 3+)

= 1.1.1 =
* First public release.