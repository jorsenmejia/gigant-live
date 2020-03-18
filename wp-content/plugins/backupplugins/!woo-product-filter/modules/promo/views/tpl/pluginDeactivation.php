<style type="text/css">
	.wpfDeactivateDescShell {
		display: none;
		margin-left: 25px;
		margin-top: 5px;
	}
	.wpfDeactivateReasonShell {
		display: block;
		margin-bottom: 10px;
	}
	#wpfDeactivateWnd input[type="text"],
	#wpfDeactivateWnd textarea {
		width: 100%;
	}
	#wpfDeactivateWnd h4 {
		line-height: 1.53em;
	}
	#wpfDeactivateWnd + .ui-dialog-buttonpane .ui-dialog-buttonset {
		float: none;
	}
	.wpfDeactivateSkipDataBtn {
		float: right;
		margin-top: 15px;
		text-decoration: none;
		color: #777 !important;
	}
</style>
<div id="wpfDeactivateWnd" style="display: none;" title="<?php _e('Your Feedback', WPF_LANG_CODE)?>">
	<h4><?php printf(__('If you have a moment, please share why you are deactivating %s', WPF_LANG_CODE), WPF_WP_PLUGIN_NAME)?></h4>
	<form id="wpfDeactivateForm">
		<label class="wpfDeactivateReasonShell">
			<?php echo htmlWpf::radiobutton('deactivate_reason', array(
				'value' => 'not_working',
			))?>
			<?php _e('Couldn\'t get the plugin to work', WPF_LANG_CODE)?>
			<div class="wpfDeactivateDescShell">
				<?php printf(__('If you have a question, <a href="%s" target="_blank">contact us</a> and will do our best to help you'), 'https://woobewoo.com/contact-us/?utm_source=plugin&utm_medium=deactivated_contact&utm_campaign=popup')?>
			</div>
		</label>
		<label class="wpfDeactivateReasonShell">
			<?php echo htmlWpf::radiobutton('deactivate_reason', array(
				'value' => 'found_better',
			))?>
			<?php _e('I found a better plugin', WPF_LANG_CODE)?>
			<div class="wpfDeactivateDescShell">
				<?php echo htmlWpf::text('better_plugin', array(
					'placeholder' => __('If it\'s possible, specify plugin name', WPF_LANG_CODE),
				))?>
			</div>
		</label>
		<label class="wpfDeactivateReasonShell">
			<?php echo htmlWpf::radiobutton('deactivate_reason', array(
				'value' => 'not_need',
			))?>
			<?php _e('I no longer need the plugin', WPF_LANG_CODE)?>
		</label>
		<label class="wpfDeactivateReasonShell">
			<?php echo htmlWpf::radiobutton('deactivate_reason', array(
				'value' => 'temporary',
			))?>
			<?php _e('It\'s a temporary deactivation', WPF_LANG_CODE)?>
		</label>
		<label class="wpfDeactivateReasonShell">
			<?php echo htmlWpf::radiobutton('deactivate_reason', array(
				'value' => 'other',
			))?>
			<?php _e('Other', WPF_LANG_CODE)?>
			<div class="wpfDeactivateDescShell">
				<?php echo htmlWpf::text('other', array(
					'placeholder' => __('What is the reason?', WPF_LANG_CODE),
				))?>
			</div>
		</label>
		<?php echo htmlWpf::hidden('mod', array('value' => 'promo'))?>
		<?php echo htmlWpf::hidden('action', array('value' => 'saveDeactivateData'))?>
	</form>
	<a href="" class="wpfDeactivateSkipDataBtn"><?php _e('Skip & Deactivate', WPF_LANG_CODE)?></a>
</div>