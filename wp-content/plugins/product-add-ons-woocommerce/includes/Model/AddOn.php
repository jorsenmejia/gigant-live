<?php


namespace ZAddons\Model;

use ZAddons\DB;

class AddOn
{
    private static function add_ons_list() {
        return array(
            array(
                'title' => 'Product Checkout Add-Ons',
                'description' => 'Add Customized Product Checkout Add-Ons for WooCommerce',
                'hook_name' => 'zproductaddon_is_loaded',
                'plugin_link' => 'https://www.bizswoop.com/wp/productaddons/checkout'
            )
        );
    }

    public static function get_all_add_ons() {
        global $wpdb;
        $prefix = $wpdb->prefix . DB::Prefix;
        $add_on_table = $prefix . DB::AddOns;
        return $wpdb->get_results(
            "SELECT * FROM ${add_on_table}"
        );
    }

    public static function load_add_ons() {
        global $wpdb;
        $prefix = $wpdb->prefix . DB::Prefix;
        $add_on_table = $prefix . DB::AddOns;
        $wpdb->query("DELETE FROM ${add_on_table}" );
        $add_ons = self::add_ons_list();

        foreach ($add_ons as $add_on) {
            $wpdb->query(
                $wpdb->prepare(
                    "INSERT INTO ${add_on_table}
                 SET title=%s, description=%s, hook_name=%s, plugin_link=%s", $add_on['title'], $add_on['description'], $add_on['hook_name'], $add_on['plugin_link']
                )
            );
        }
    }
}