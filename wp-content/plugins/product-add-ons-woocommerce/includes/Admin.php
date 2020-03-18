<?php

namespace ZAddons;

class Admin
{
	public function __construct()
	{
		new Admin\ListGroup();
		new Admin\SingleGroup();
	}

	public static function getGroupsUrl()
	{
		return add_query_arg([
			'post_type' => 'product',
			'page' => 'za_groups',
            'tab' => 'groups'
		], admin_url('edit.php'));
	}

	public static function getAddOnsUrl()
    {
        return add_query_arg([
            'post_type' => 'product',
            'page' => 'za_groups',
            'tab' => 'add-ons'
        ], admin_url('edit.php'));
    }
}
