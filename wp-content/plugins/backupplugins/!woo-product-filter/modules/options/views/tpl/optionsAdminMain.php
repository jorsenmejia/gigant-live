<style type="text/css">
	.wpfAdminMainLeftSide {
		width: 56%;
		float: left;
	}
	.wpfAdminMainRightSide {
		width: <?php echo (empty($this->optsDisplayOnMainPage) ? 100 : 40)?>%;
		float: left;
		text-align: center;
	}
	#wpfMainOccupancy {
		box-shadow: none !important;
	}
</style>
<section>
	<div class="supsystic-item supsystic-panel">
		<div id="containerWrapper">
			<?php _e('Main page Go here!!!!', WPF_LANG_CODE)?>
		</div>
		<div style="clear: both;"></div>
	</div>
</section>