<div class="wpfPopupOptRow">
	<label>
		<a target="_blank" href="<?php echo $this->promoLink?>" class="sup-promolink-input">
			<?php echo htmlWpf::checkbox('layered_style_promo', array(
				'checked' => 1,
				//'attrs' => 'disabled="disabled"',
			))?>
			<?php _e('Enable Layered PopUp Style', WPF_LANG_CODE)?>
		</a>
		<a target="_blank" class="button" style="margin-top: -8px;" href="<?php echo $this->promoLink?>"><?php _e('Available in PRO', WPF_LANG_CODE)?></a>
	</label>
	<div class="description"><?php _e('By default all PopUps have modal style: it appears on user screen over the whole site. Layered style allows you to show your PopUp - on selected position: top, bottom, etc. and not over your site - but right near your content.', WPF_LANG_CODE)?></div>
</div>
<span>
	<div class="wpfPopupOptRow">
		<span class="wpfOptLabel"><?php _e('Select position for your PopUp', WPF_LANG_CODE)?></span>
		<br style="clear: both;" />
		<div id="wpfLayeredSelectPosShell">
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="top_left"><span class="wpfLayeredPosCellContent"><?php _e('Top Left', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 40%;" data-pos="top"><span class="wpfLayeredPosCellContent"><?php _e('Top', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="top_right"><span class="wpfLayeredPosCellContent"><?php _e('Top Right', WPF_LANG_CODE)?></span></div>
			<br style="clear: both;"/>
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="center_left"><span class="wpfLayeredPosCellContent"><?php _e('Center Left', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 40%;" data-pos="center"><span class="wpfLayeredPosCellContent"><?php _e('Center', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="center_right"><span class="wpfLayeredPosCellContent"><?php _e('Center Right', WPF_LANG_CODE)?></span></div>
			<br style="clear: both;"/>
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="bottom_left"><span class="wpfLayeredPosCellContent"><?php _e('Bottom Left', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 40%;" data-pos="bottom"><span class="wpfLayeredPosCellContent"><?php _e('Bottom', WPF_LANG_CODE)?></span></div>
			<div class="wpfLayeredPosCell" style="width: 30%;" data-pos="bottom_right"><span class="wpfLayeredPosCellContent"><?php _e('Bottom Right', WPF_LANG_CODE)?></span></div>
			<br style="clear: both;"/>
		</div>
		<?php echo htmlWpf::hidden('params[tpl][layered_pos]')?>
	</div>
</span>
<style type="text/css">
	#wpfLayeredSelectPosShell {
		max-width: 560px;
		height: 380px;
	}
	.wpfLayeredPosCell {
		float: left;
		cursor: pointer;
		height: 33.33%;
		text-align: center;
		vertical-align: middle;
		line-height: 110px;
	}
	.wpfLayeredPosCellContent {
		border: 1px solid #a5b6b2;
		margin: 5px;
		display: block;
		font-weight: bold;
		box-shadow: -3px -3px 6px #a5b6b2 inset;
		color: #739b92;
	}
	.wpfLayeredPosCellContent:hover, .wpfLayeredPosCell.active .wpfLayeredPosCellContent {
		background-color: #e7f5f6; /*rgba(165, 182, 178, 0.3);*/
		color: #00575d;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
		var proExplainContent = jQuery('#wpfLayeredProExplainWnd').dialog({
			modal:    true
		,	autoOpen: false
		,	width: 460
		,	height: 180
		});
		jQuery('.wpfLayeredPosCell').click(function(){
			proExplainContent.dialog('open');
		});
	});
</script>
<!--PRO explanation Wnd-->
<div id="wpfLayeredProExplainWnd" style="display: none;" title="<?php _e('Improve Free version', WPF_LANG_CODE)?>">
	<p>
		<?php printf(__('This functionality and more - is available in PRO version. <a class="button button-primary" target="_blank" href="%s">Get it</a> today for 29$', WPF_LANG_CODE), $this->promoLink)?>
	</p>
</div>