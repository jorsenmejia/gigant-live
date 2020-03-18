<?php
function register_wpalrp_page() {
	add_submenu_page( 
	'options-general.php', 
	'AJAX Login Settings', 
	'AJAX Login Settings', 
	'manage_options', 
	'wpalrp-page', 
	'wpalrp_admin_settings' ); 
}
add_action('admin_menu', 'register_wpalrp_page');

function wpalrp_admin_settings() {
	
	echo '<div class="wpalrp_warp">';
		echo '<h1>AJAX Login Settings</h1>';
?>

<div id="wpalrp_left">
  <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>
    <div class="inside">
    <h3><?php echo esc_attr(__('Insert your settings')); ?></h3>
    <table class="form-table">
      <tr>
        <th><label for="wpalrp_font_size"><?php echo esc_attr(__('Font Size')); ?></label></th>
        <td><input type="text" name="wpalrp_font_size" value="<?php $wpalrp_font_size = get_option('wpalrp_font_size'); if(!empty($wpalrp_font_size)) {echo $wpalrp_font_size;} else {echo "12";}?>">
          px;</td>
      </tr>
     <tr>
        <th><label for="wpalrp_font_color"><?php echo esc_attr(__('Font Color')); ?></label></th>
        <td><input type="text" name="wpalrp_font_color" id="scrollbar_color" value="<?php $wpalrp_font_color = get_option('wpalrp_font_color'); if(!empty($wpalrp_font_color)) {echo $wpalrp_font_color;} else {echo "#666";}?>" class="color-picker wpalrp_font_color" /></td>
      </tr>
      <tr>
        <th><label for="wpalrp_button_bg"><?php echo esc_attr(__('Button Background Color')); ?></label></th>
        <td><input type="text" name="wpalrp_button_bg" id="scrollbar_color" value="<?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>" class="color-picker wpalrp_button_bg" /></td>
      </tr>
      <tr>
        <th><label for="wpalrp_button_font_size"><?php echo esc_attr(__('Button Font Size')); ?></label></th>
        <td><input type="text" name="wpalrp_button_font_size" id="scrollbar_color" value="<?php $wpalrp_button_font_size = get_option('wpalrp_button_font_size'); if(!empty($wpalrp_button_font_size)) {echo $wpalrp_button_font_size;} else {echo "15";}?>" class="wpalrp_button_font_size" /></td>
      </tr>
      <tr>
        <th><label for="wpalrp_button_font_color"><?php echo esc_attr(__('Button Font Color')); ?></label></th>
        <td><input type="text" name="wpalrp_button_font_color" id="scrollbar_color" value="<?php $wpalrp_button_font_color = get_option('wpalrp_button_font_color'); if(!empty($wpalrp_button_font_color)) {echo $wpalrp_button_font_color;} else {echo "#FFF";}?>" class="color-picker wpalrp_button_font_color" /></td>
      </tr>
    </table>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="wpalrp_font_size, wpalrp_font_color, wpalrp_button_bg, wpalrp_button_font_size, wpalrp_button_font_color" />
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" class="button button-primary" />
    </p>
  </div>
  </form>
</div>

<div id="wpalrp_right">
  <div class="wpalrp_widget">
    <h3>
      <?php _e('Donate and support the development.','nht') ?>
    </h3>
    <p>
      <?php _e('With your help I can make Simple Fields even better! $5, $10, $100 – any amount is fine! :)','nht') ?>
    </p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
      <input type="hidden" name="cmd" value="_s-xclick">
      <input type="hidden" name="hosted_button_id" value="82C6LTLMFLUFW">
      <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
      <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
    </form>
  </div>
  <div class="wpalrp_widget">
    <h3><?php echo esc_attr(__('About the Plugin')); ?></h3>
    <p>You can make my day by submitting a positive review on <a href="https://wordpress.org/support/view/plugin-reviews/wp-ajax-login-register-popup" target="_blank"><strong>WordPress.org!</strong> <img src="<?php bloginfo('url' ); echo"/wp-content/plugins/wp-ajax-login-register-popup/img/review.png"; ?>" alt="review" class="review"/></a></p>
    <div class="clrFix"></div>
  </div>
  <div class="wpalrp_widget">
    <div class="clrFix"></div>
    <h3>About the Author</h3>
    <p>I am a Web Developer, Expert WordPress Developer. I am regularly available for interesting freelance projects. If you ever need my help, do not hesitate to get in touch <a href="https://www.upwork.com/freelancers/~01bf79370d989b2033" target="_blank">me</a>.<br />
      <strong>Skype:</strong> cse.hasib<br />
      <strong>Email:</strong> cse.hasib@gmail.com<br />
      <strong>Web:</strong> <a href="https://www.upwork.com/freelancers/~01bf79370d989b2033">cse.hasib</a><br />
    <div class="clrFix"></div>
  </div>
</div>
<div class="clrFix"></div>
<?php		
	echo '</div>';
}