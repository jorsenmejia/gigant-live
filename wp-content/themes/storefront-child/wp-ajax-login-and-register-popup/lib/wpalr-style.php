<?php function wpalrp_dynamicCSS()
{ ?>
<style type="text/css">
form.ajax-auth, .ajax-auth{
  color: <?php $wpalrp_font_color = get_option('wpalrp_font_color'); if(!empty($wpalrp_font_color)) {echo $wpalrp_font_color;} else {echo "#666";}?>;
  font-size:<?php $wpalrp_font_size = get_option('wpalrp_font_size'); if(!empty($wpalrp_font_size)) {echo $wpalrp_font_size;} else {echo "12";}?>px;
}
.ajax-auth input#username,
.ajax-auth input#password,
.ajax-auth input#signonname,
.ajax-auth input#email,
.ajax-auth input#signonpassword,
.ajax-auth input#password2{
    border: 1px solid <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#b34336";}?>px;
	font-size:<?php $wpalrp_font_size = get_option('wpalrp_font_size'); if(!empty($wpalrp_font_size)) {echo $wpalrp_font_size;} else {echo "12";}?>px;
}
.ajax-auth input.submit_button{
    font-size: <?php $wpalrp_button_font_size = get_option('wpalrp_button_font_size'); if(!empty($wpalrp_button_font_size)) {echo $wpalrp_button_font_size;} else {echo "15";}?>px;
    color: <?php $wpalrp_button_font_color = get_option('wpalrp_button_font_color'); if(!empty($wpalrp_button_font_color)) {echo $wpalrp_button_font_color;} else {echo "#FFF";}?>;
    border: 1px solid <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>;
    background-color: <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>;
    text-shadow: 0 1px 0 <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>;
    background: -moz-linear-gradient(top, <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>, <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>);
    border-top: 1px solid <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>;
    border-bottom: 1px solid <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?>;
    box-shadow: 0 1px 0 <?php $wpalrp_button_bg = get_option('wpalrp_button_bg'); if(!empty($wpalrp_button_bg)) {echo $wpalrp_button_bg;} else {echo "#e25c4c";}?> inset;
}
</style>
<?php 
}
add_action('wp_footer','wpalrp_dynamicCSS', 100);
?>