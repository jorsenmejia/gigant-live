<?php
/**
 * Plugin Name: Twilio Video
 * Plugin URI: http://twilio.com
 * Description: This plugin the ability to have a video conversation within a wordpress site.
 * Version: 1.0.0
 * Author: Devin Rader
 * Author URI: http://twilio.com
 * License: MIT
 */

define('TWILIO_VIDEO_PLUGIN_URL', plugin_dir_url(__FILE__));
 
add_action( 'wp_enqueue_scripts', 'twilio_video_enqueue_scripts' );
add_action( 'rest_api_init', 'twilio_video_register_api_routes' );

function twilio_video_enqueue_scripts() {
	wp_enqueue_style( 'twilio-video-css', TWILIO_VIDEO_PLUGIN_URL . 'css/twilio-video.css');
	
	wp_enqueue_script( 'jquery' );		
	wp_enqueue_script('twilio-video', 'https://media.twiliocdn.com/sdk/js/video/releases/2.0.0/twilio-video.min.js');
	wp_enqueue_script( 'twilio-common', 'https://media.twiliocdn.com/sdk/js/common/v0.1/twilio-common.min.js');
	wp_enqueue_script( 'twilio-conversations', 'https://media.twiliocdn.com/sdk/js/conversations/v0.13/twilio-conversations.min.js');	
    wp_enqueue_script( 'twilio-video-js', TWILIO_VIDEO_PLUGIN_URL . 'js/twilio-video.js' );
}

function twilio_video_register_api_routes() {

	$namespace = 'twilio-video-api/v1';

	register_rest_route( $namespace, '/twilio-video-token/', array(
	    'methods' => 'GET',
        'callback' => 'twilio_video_get_token') 
	);
}

function twilio_video_get_token() {

	require_once ('lib/Twilio/Twilio.php');
	require_once ('randos.php');

	// An identifier for your app - can be anything you'd like
	$appName = 'TwilioVideoDemo';

	// Create access token, which we will serialize and send to the client
	$accountSid = 'ACce3a53725efc8fd7801d1376399f42d3';
$apiKeySid = '7c23a57b616780cefd2fdece0181e732';
$apiKeySecret = 'SKac25682e800c44d4a2f4de50df8b73ce';

$identity = 'Pro';

// Create an Access Token
$token = new AccessToken(
    $accountSid,
    $apiKeySid,
    $apiKeySecret,
    3600,
    $identity
);

// Grant access to Video
$grant = new VideoGrant();
$grant->setRoom('Pro chat room');
$token->addGrant($grant);


	$return = array(
    'identity' => $identity,
    'token' => $token->toJWT(),
	);

	$response = new WP_REST_Response( $return );
	return $response;
}
?>