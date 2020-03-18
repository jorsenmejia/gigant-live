<?php

/*
Plugin Name: Consolto VideoChat Plugin
Plugin URI: www.consolto.com
Description: The #1 Video-chat Plugin, heavily used 1-stop-shop solution for consultants/advisers. Enables Video-calls, payment collection via paypal, in-app chat, Whatsapp, Facebook Messenger, scheduling appointments and more... 
Author: Consolto Team
License: GPLv2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Version: 2.0.2
Author URI: https://www.consolto.com
*/

/**
  * Register the plugin settings panel
*/

	add_action('admin_menu', 'etwp_admin_panel_menu');

    function etwp_admin_panel_menu() {
		add_menu_page( __('Consolto Plugin','Consolto Plugin Installer' ), __( 'Consolto Plugin','Consolto Plugin Installer' ), 'manage_options', 'ConsoltoPlugin', 'etwp_embed_script', 'dashicons-welcome-widgets-menus', 90 );  
	}
	function etwp_embed_script(){
		if( isset($_POST['submit']) ) {
			$widget_id_before_regex =   $_POST['consolto_widget_link'] ;
			echo $widget_id_before_regex;
			$pattern ='/data-widgetId=\\\"([^"]*)\\\"/';

			if (preg_match($pattern, $widget_id_before_regex, $match))
			$widget_id =  $match[1];



			$data = array(
				'first_name'  => sanitize_text_field( $_POST['first_name'] ),
				'last_name' => sanitize_text_field( $_POST['last_name'] ),
				//'profile_name' => "MY_PROFILE_NAME",
				'profile_name' => sanitize_text_field( $_POST['profile_name'] ),
				'widget_id' => sanitize_text_field($widget_id),
				'consolto_widget_link' => $widget_id_before_regex,
				'link' => sanitize_text_field( $_POST['link'] ),
				'headline' => sanitize_text_field( $_POST['headline'] ),
				'text'   => sanitize_text_field( $_POST['text'] )
			);
			
			//entering data into options table
			update_option( 'etwp_my_option_key', $data );
			add_action('form_message', 'etwp_write_here_show_success_messages' );
		} ?>
	<?php
		//having data from options table
		$etwp_db_values = get_option( 'etwp_my_option_key' );
		//setting empty values to avoid 'undefined index' warning
		$etwp_first_name = '';
		$etwp_last_name = '';
		$etwp_profile_name = '';
		$etwp_consolto_widget_link = ''; 
		$etwp_widget_id = '';
		$etwp_link = '';
		$etwp_headline = '';
		$etwp_text = '';

		//if there's any data in options table, updating our variables with relevant data
		if( $etwp_db_values ) {
			$etwp_first_name = $etwp_db_values['first_name'] ? $etwp_db_values['first_name'] : '';
			$etwp_last_name = $etwp_db_values['last_name'] ? $etwp_db_values['last_name'] : '';
			$etwp_profile_name = $etwp_db_values['profile_name'] ? $etwp_db_values['profile_name'] : '';
			$etwp_consolto_widget_link= $etwp_db_values['consolto_widget_link'] ? $etwp_db_values['consolto_widget_link'] : '';
			
			$etwp_widget_id = $etwp_db_values['widget_id'] ? $etwp_db_values['widget_id'] : '';
			$etwp_link = $etwp_db_values['link'] ? $etwp_db_values['link'] : '';
			$etwp_headline = $etwp_db_values['headline'] ? $etwp_db_values['headline'] : '';
			$etwp_text = $etwp_db_values['text'] ? $etwp_db_values['text'] : '';
		}
		?>
	<div>
		 <?php screen_icon(); ?>
		<h1>Consolto Plugin Installer</h1>
		<h2>Welcome aboard. We're so happy you're here!</h2>
		<p>In a few moments you'll have the <a href="https://www.consolto.com" target="_blank">Consolto.com</a>  Video-Chat widget embedded on your website. <br>Excited? We definitely are...</p>
		<h2> 	 <?php	 echo $etwp_db_values['widget_id']?"Consolto plugin is successfully installed. Your widgetId is ".$etwp_db_values['widget_id']:"Please paste the script you received from the Consolto dashboard"; ?></h2>
		<form method="post" action="">
		 <?php settings_fields( 'etwp_my_option_key' ); ?>
			<table>
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label>User First Name</label></th>-->
				<!--<td> <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : $etwp_first_name; ?>" required /></td>-->
				<!--</tr>-->
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label>User Last Name</label></th>-->
				<!--<td> <input type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : $etwp_last_name; ?>" required /></td>-->
				<!--</tr>-->
			
				<tr valign="top">
				<th scope="row"> <label>Widget Script</label></th>
				<td> <input type="text" name="consolto_widget_link" value="<?php echo isset($_POST['consolto_widget_link']) ? "": ""; ?>" required /></td>
				</tr>
				
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label> Profile Name </label></th>-->
				<!--<td> <input type="text" name="profile_name" value="<?php echo isset($_POST['profile_name']) ? $etwp_profile_name: $etwp_profile_name; ?>" required /></td>-->
				<!--</tr>-->

			
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label>Link</label></th>-->
				<!--<td> <input type="text" name="link" value="<?php echo isset($_POST['link']) ? $_POST['link'] : $etwp_link; ?>"></td>-->
				<!--</tr>-->
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label>Headline</label></th>-->
				<!--<td> <input type="text" name="headline" value="<?php echo isset($_POST['headline']) ? $_POST['headline'] : $etwp_headline; ?>" required /></td>-->
				<!--</tr>-->
				<!--<tr valign="top">-->
				<!--<th scope="row"> <label>Text</label></th>-->
				<!--<td> <textarea cols="50" rows="4"  name="text" required /><?php echo isset($_POST['text']) ? $_POST['text'] : $etwp_text; ?> </textarea></td>-->
				<!--</tr>-->
			</table>
			<p>	The widget script can be found in: <a href="https://app.consolto.com/expertHome/widgetCenter" target="_blank">app.consolto.com/expertHome/widgetCenter</a> </p>

			<p>Not registered yet? Go to  <a href="https://www.consolto.com" target="_blank">Consolto.com</a> and get your widget. </p>
			<p>Registered and have questions? <a href="https://www.consolto.com/installation-instructions/wordpress" target="_blank">Detailed instructions</a></p>
			<p>Still have questions? We're here to hold your hand. Feel free to contact us: <a href="mailto:support@consolto.com">support@consolto.com</a></p>
			

		  <?php  submit_button(); ?>		<?php do_action('form_message'); ?>		

		</form>
		<p> 	 <?php	 echo $etwp_db_values['widget_id']?"Consolto plugin is successfully installed. Your widgetId is ".$etwp_db_values['widget_id']:""; ?></p>
	</div>
	<?php
	}
	function etwp_write_here_show_success_messages( ) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
			<p><strong>Settings saved.</strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
	<?php }
	
	function etwp_insert_script_heade() {
		$etwp_db_values = get_option( 'etwp_my_option_key' );
	?>
    <?php if ($etwp_db_values['widget_id']){

    } else {
    }?>   
    
    <div id="et-iframe" data-version="0.5" data-wp-site="true" data-test="false" <?php echo $etwp_db_values['widget_id']?"data-widgetId=".$etwp_db_values['widget_id']:"data-profilename=".$etwp_db_values['profile_name']; ?>></div>

	<?php wp_enqueue_script( 'ConsoltoInserter', 'https://client.consolto.com/iframeApp/iframeApp.js', false );?>

	<?php }
	add_action( 'wp_footer', 'etwp_insert_script_heade' );
?>