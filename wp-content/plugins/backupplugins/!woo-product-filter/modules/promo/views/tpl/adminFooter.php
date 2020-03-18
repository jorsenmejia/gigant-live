<div class="wpfAdminFooterShell wpfHidden">
	<div class="wpfAdminFooterCell">
		<?php echo WPF_WP_PLUGIN_NAME?>
		<?php _e('Version', WPF_LANG_CODE)?>:
		<a target="_blank" href="http://wordpress.org/plugins/popup-by-supsystic/changelog/"><?php echo WPF_VERSION?></a>
	</div>
	<div class="wpfAdminFooterCell">|</div>
	<?php  if(!frameWpf::_()->getModule(implode('', array('l','ic','e','ns','e')))) {?>
	<div class="wpfAdminFooterCell">
		<?php _e('Go', WPF_LANG_CODE)?>&nbsp;<a target="_blank" href="<?php echo $this->getModule()->getMainLink();?>"><?php _e('PRO', WPF_LANG_CODE)?></a>
	</div>
	<div class="wpfAdminFooterCell">|</div>
	<?php } ?>
	<div class="wpfAdminFooterCell">
		<a target="_blank" href="http://wordpress.org/support/plugin/popup-by-supsystic"><?php _e('Support', WPF_LANG_CODE)?></a>
	</div>
	<div class="wpfAdminFooterCell">|</div>
	<div class="wpfAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/popup-by-supsystic?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>