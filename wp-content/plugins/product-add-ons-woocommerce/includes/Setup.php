<?php

namespace ZAddons;

class Setup
{
	public function __construct()
	{
		new Activate();
		add_action('plugins_loaded', [$this, 'init'], 10);
	}

	public function init()
	{
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', function () {
				?>
				<div class="notice notice-error is-dismissible">
					<p>Product Add-Ons WooCommerce require WooCommerce</p>
				</div>
				<?php
			});
			return;
		}
        add_action( 'zaddon_get_plugin_path', function () {
            return PLUGIN_ROOT;
        });

        add_action( 'zaddon_get_plugin_url', function () {
            return plugins_url('', \ZAddons\PLUGIN_ROOT_FILE);
        });

        new Scripts();
		new Admin();
		new Frontend();
	}
}
