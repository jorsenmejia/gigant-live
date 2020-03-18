<?php
$textname = 'vg_shortcode_display_data';
?>
<p><?php _e('Thank you for installing our plugin. This plugin is completely free.', $textname); ?></p>
<script>var demoVideo = '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/QW5zRv9dwDY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';</script>

<?php
$steps = array();
$steps['read_docs'] = '<p>' . sprintf(__('You can read our documentation to see examples of the shortcodes and available parameters:  <a href="%s" class="button" target="_blank">Read documentation</a>', $textname), 'https://wordpress.org/plugins/shortcode-to-display-post-and-user-data/') . '</p>';
$steps['recommended_plugin'] = '<p>' . sprintf(__('We have another free plugin that you might like. Its a spreadsheet where you can view and edit all the posts and pages quickly:  <button class="button" onclick="jQuery(this).parent().after(demoVideo);">View a demo video</button> - <a href="%s" class="button" target="_blank">Install it</a>', $textname), $this->get_plugin_install_url('wp-sheet-editor-bulk-spreadsheet-editor-for-posts-and-pages')) . '</p>';

$steps = apply_filters('vg_sheet_editor/wpds/welcome_steps', $steps);

if (!empty($steps)) {
	echo '<ol class="steps">';
	foreach ($steps as $key => $step_content) {
		?>
		<li><?php echo $step_content; ?></li>		
		<?php
	}

	echo '</ol>';
}