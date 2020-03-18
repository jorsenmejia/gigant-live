<div class="wrap">
    <div class="supsystic-plugin">
        <?php /*?><header class="supsystic-plugin">
            <h1><?php echo WPF_WP_PLUGIN_NAME?></h1>
        </header><?php */?>
		<?php echo $this->breadcrumbs?>
        <section class="supsystic-content">
            <nav class="supsystic-navigation supsystic-sticky <?php dispatcherWpf::doAction('adminMainNavClassAdd')?>">
                <ul>
					<?php foreach($this->tabs as $tabKey => $tab) { ?>
						<?php if(isset($tab['hidden']) && $tab['hidden']) continue;?>
						<li class="supsystic-tab-<?php echo $tabKey;?> <?php echo (($this->activeTab == $tabKey || in_array($tabKey, $this->activeParentTabs)) ? 'active' : '')?>">
							<a href="<?php echo $tab['url']?>" title="<?php echo $tab['label']?>">
								<?php if(isset($tab['fa_icon'])) { ?>
									<i class="fa <?php echo $tab['fa_icon']?>"></i>
								<?php } elseif(isset($tab['wp_icon'])) { ?>
									<i class="dashicons-before <?php echo $tab['wp_icon']?>"></i>
								<?php } elseif(isset($tab['icon'])) { ?>
									<i class="<?php echo $tab['icon']?>"></i>
								<?php }?>
								<span class="sup-tab-label"><?php echo $tab['label']?></span>
							</a>
						</li>
					<?php }?>
                </ul>
            </nav>
            <div class="supsystic-container supsystic-<?php echo $this->activeTab?>">
				<?php echo $this->content?>
                <div class="clear"></div>
            </div>
        </section>
    </div>
</div>
<!--Option available in PRO version Wnd-->
<div id="wpfOpt" style="display: none;" title="qwe">

</div>

<div id="wpfAddDialog" style="display: none;" title="<?php _e('Enter product filter name', WPF_LANG_CODE)?>">
	<div style="">
		<form id="tableForm">
			<input id="addDialog_title" class="supsystic-text" type="text" style="width:100%;"/>
			<input type="hidden" id="addDialog_duplicateid" class="supsystic-text" style="width:100%;"/>
		</form>
		<div id="formError" style="color: red; display: none; float: left;">
			<p></p>
		</div>
		<!-- /#formError -->
	</div>
</div>
<!-- /#addDialog -->
