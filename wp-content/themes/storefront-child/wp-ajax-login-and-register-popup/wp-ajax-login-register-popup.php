<?php 
/*
Plugin Name: WP AJAX Login and Register Popup
Plugin URI: https://wordpress.org/plugins/wp-ajax-login-register-popup/
Description: WP AJAX Login and Register Popup is a WordPress plugin to login and register on your website using popup box!  Use this shortcode <strong>[WPALRP-LOGIN]</strong> in the widget where you want to display login/register button.
Version: 1.0.1
Author: Hasibul Islam Badsha
Author URI: https://www.bdtrips.com/
Copyright: 2019
License URI: license.txt
Text Domain: wpalrp
*/


#######################	WP AJAX Login and Register Popup ###############################


/**
	Register Stylesheet and Javascript. 
**/
function wpalrp_ajax_plugin_wp() {
	wp_enqueue_script('jquery');
}
add_action('init', 'wpalrp_ajax_plugin_wp');

/**
	Define plugin base url
**/
define('wpalrp_ajax_plugin_path', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );

/**
	Register Admin Stylesheet and Javascript.
**/
function register_wpalrp_admin_style()
{
	wp_enqueue_style( 'wpalrp-admin', plugins_url('/css/wpalrp-admin.css', __FILE__) );
	wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1 );
	wp_enqueue_script( 'cp-active', plugins_url('/js/cp-active.js', __FILE__), array('jquery'), '', true );
}
add_action( 'admin_enqueue_scripts', 'register_wpalrp_admin_style' ); 


/**
	Register Stylesheet and Javascript. 
**/
function wpalrp_ajax_auth_init(){	
	wp_register_style( 'wpalrp-style', wpalrp_ajax_plugin_path.'css/wpalrp.css' );
	wp_enqueue_style('wpalrp-style');
	
	wp_register_script('validate-script', wpalrp_ajax_plugin_path.'js/jquery.validate.js', array('jquery') ); 
    wp_enqueue_script('validate-script');

    wp_register_script('ajax-auth-script', wpalrp_ajax_plugin_path.'js/ajax-auth-script.js', array('jquery') ); 
    wp_enqueue_script('ajax-auth-script');

    wp_localize_script( 'ajax-auth-script', 'ajax_auth_object', array( 
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));

    // Enable the user with no privileges to run wpalrp_ajax_login() in AJAX
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'wpalrp_ajax_login' );
	// Enable the user with no privileges to run wpalrp_ajax_register() in AJAX
	add_action( 'wp_ajax_nopriv_ajaxregister', 'wpalrp_ajax_register' );
}

// Execute the action only if the user isn't logged in
  add_action('init', 'wpalrp_ajax_auth_init');
  
  
function wpalrp_ajax_login(){
    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
  	// Call wpalrp_auth_user_login
	wpalrp_auth_user_login($_POST['username'], $_POST['password'], 'Login'); 
	
    die();
}

/**
	Send user data. 
**/
function wpalrp_ajax_register(){
    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-register-nonce', 'security' );
		
    // Nonce is checked, get the POST data and sign user on
    $info = array();
  	$info['user_nicename'] = $info['nickname'] = $info['display_name'] = $info['first_name'] = $info['user_login'] = sanitize_user($_POST['username']) ;
    $info['user_pass'] = sanitize_text_field($_POST['password']);
	$info['user_email'] = sanitize_email( $_POST['email']);
	
	// Register the user
    $user_register = wp_insert_user( $info );
 	if ( is_wp_error($user_register) ){	
		$error  = $user_register->get_error_codes()	;
		
		if(in_array('empty_user_login', $error))
			echo json_encode(array('loggedin'=>false, 'message'=>__($user_register->get_error_message('empty_user_login'))));
		elseif(in_array('existing_user_login',$error))
			echo json_encode(array('loggedin'=>false, 'message'=>__('This username is already registered.')));
		elseif(in_array('existing_user_email',$error))
        echo json_encode(array('loggedin'=>false, 'message'=>__('This email address is already registered.')));
    } else {
	  wpalrp_auth_user_login($info['nickname'], $info['user_pass'], 'Registration');       
    }

    die();
}

/**
	login with user and pass. 
**/
function wpalrp_auth_user_login($user_login, $password, $login)
{
	$info = array();
    $info['user_login'] = $user_login;
    $info['user_password'] = $password;
    $info['remember'] = true;
	
	$user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
		echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
		wp_set_current_user($user_signon->ID); 
        echo json_encode(array('loggedin'=>true, 'message'=>__($login.' successful, redirecting...')));
    }
	
	die();
}

/**
	Register and Login Form. 
**/
function wpalrp_get_data_ajax_settings() {?>

<style type="text/css">

  label {

    font-family: Montserrat, sans-serif;
    font-size: 15px;

  }

</style>
<form style="margin-top:30px;" id="login" class="ajax-auth" action="login" method="post">
  <h3><?php echo esc_attr(__('New to Gigant?')); ?> <a id="pop_signup" href=""><?php echo esc_attr(__('Create an Account')); ?></a></h3>
  <hr />
  <h1><?php echo esc_attr(__('Login')); ?></h1>
  <p class="status"></p>
  <?php wp_nonce_field('ajax-login-nonce', 'security'); ?>
  <label for="username"><?php echo esc_attr(__('Username')); ?></label>
  <input id="username" type="text" class="required" name="username" palceholder>
  <label for="password"><?php echo esc_attr(__('Password')); ?></label>
  <input id="password" type="password" class="required" name="password">
  <a class="text-link" href="<?php echo wp_lostpassword_url(); ?>"><?php echo esc_attr(__('Lost Password?')); ?></a>
  <input class="submit_button" type="submit" value="LOGIN">
  <a class="close" href=""><img class="cancel" src="<?php echo wpalrp_ajax_plugin_path. 'img/Cancel-v2.png'; ?>" /></a>
</form>
<form id="register" class="ajax-auth"  action="register" method="post">
  <h3><?php echo esc_attr(__('Already have an account? ')); ?><a id="pop_login"  href=""><?php echo esc_attr(__('Login')); ?></a></h3>
  <hr />
  <h1><?php echo esc_attr(__('Signup')); ?></h1>
  <p class="status"></p>
  <?php wp_nonce_field('ajax-register-nonce', 'signonsecurity'); ?>
  <label for="signonname"><?php echo esc_attr(__('Username')); ?></label>
  <input id="signonname" type="text" name="signonname" class="required">
  <label for="email"><?php echo esc_attr(__('Email')); ?></label>
  <input id="email" type="text" class="required email" name="email">
  <label for="signonpassword"><?php echo esc_attr(__('Password')); ?></label>
  <input id="signonpassword" type="password" class="required" name="signonpassword" >
  <label for="password2"><?php echo esc_attr(__('Confirm Password')); ?></label>
  <input type="password" id="password2" class="required" name="password2">
  <input class="submit_button" type="submit" value="SIGNUP">
  <a class="close" href=""><img class="cancel" src="<?php echo wpalrp_ajax_plugin_path. 'img/Cancel-v2.png'; ?>" /></a>
</form>
<?php
}
add_action('wp_footer', 'wpalrp_get_data_ajax_settings');

/**
	Register short code. 
**/
function wpalrp_popup_functions()
{
	return wpalrp_get_data_from_settings();
}
add_shortcode('WPALRP-LOGIN', 'wpalrp_get_data_from_settings');

/**
	Register and Login Form. 
**/
function wpalrp_get_data_from_settings() {?>
<div class="wpalrp-login-btn">
  <?php if (is_user_logged_in()) { ?>
  <a href="<?php echo wp_logout_url( home_url() ); ?>">Logout</a>
  <?php } else { get_template_part('ajax', 'auth'); ?>
  <a class="login_button" id="show_login" href="">Login</a> <a class="login_button" id="show_signup" href="">Signup</a>
  <?php } ?>
</div>
<?php
}


/**
	Get all php file.
**/
foreach ( glob( plugin_dir_path( __FILE__ )."lib/*.php" ) as $php_file )
    include_once $php_file;


/**
	Redirect to plugin settings page.
**/
register_activation_hook(__FILE__, 'wpalrp_plugin_activate');
add_action('admin_init', 'wpalrp_plugin_redirect');

function wpalrp_plugin_activate() {
    add_option('wpalrp_plugin_do_activation_redirect', true);
}

function wpalrp_plugin_redirect() {
    if (get_option('wpalrp_plugin_do_activation_redirect', false)) {
        delete_option('wpalrp_plugin_do_activation_redirect');
        if(!isset($_GET['activate-multi']))
        {
            wp_redirect("options-general.php?page=wpalrp-page");
        }
    }
}