<?php
namespace ZAddons;

use ZAddons\Model\AddOn;

class Activate
{
	public function __construct()
	{
		register_activation_hook(PLUGIN_ROOT_FILE, function () {
			DB::db_activate();
            AddOn::load_add_ons();
        });

        add_action( 'upgrader_process_complete', function() {
            DB::check_new_tables();
        }, 10, 2 );

    }
}
