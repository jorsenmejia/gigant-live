<?php

namespace ZAddons\Frontend;

use ZAddons\Model\Group;

class Product
{
    public function __construct()
    {
        add_action('woocommerce_before_add_to_cart_button', [$this, 'show_product_options']);

        add_filter('woocommerce_add_cart_item', [$this, 'add_cart_item'], 20, 1);

        add_filter('woocommerce_add_cart_item_data', [$this, 'add_cart_item_data'], 10, 2);

        add_action('woocommerce_cart_item_restored', [$this, 'cart_item_restored'], 10, 2);

        add_filter('woocommerce_get_cart_item_from_session', [$this, 'get_cart_item_from_session'], 20, 2);

        add_action('woocommerce_new_order_item', [Product::class, 'order_item_meta'], 10, 2);

        add_filter('woocommerce_get_item_data', [Product::class, 'add_item_data'], 10, 2);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'hidden_order_itemmeta']);

        add_action('admin_init', [$this, 'add_tab_wc_product']);

        add_action('woocommerce_process_product_meta', array($this, 'addons_fields_save'));

        add_action( 'woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'), 1, 2);

    }

    public function show_product_options()
    {
        global $product;
        if (is_object($product) && $product->get_id() > 0 && !$product instanceof \WC_Product_Grouped) {
            $groups = Group::getByProduct($product);
            foreach ($groups as $group) {
                foreach ($group->types as $type) {
                    if (self::is_shown_children_options($type->values)) {
                        include __DIR__ . '/templates/type.php';
                    }
                }
            }
            ?>
            <input type="hidden" id="zaddon_base_price" value="<?= $product->get_price(); ?>">
            <input type="hidden" id="zaddon_currency" value="<?= \get_woocommerce_currency(); ?>">
            <input type="hidden" id="zaddon_locale" value="<?= get_locale(); ?>">

            <div class="zaddon_data">
                <div class="zaddon_subtotal">
                    <h4>Subtotal:</h4>
                    <span class="woocommerce-Price-amount amount"></span>
                </div>
                <div class="zaddon_additional">
                    <h4>Add-ons total:</h4>
                    <p>+&nbsp;<span class="woocommerce-Price-amount amount"></span></p>
                </div>
                <div class="zaddon_total">
                    <h4>Total:</h4>
                    <span class="woocommerce-Price-amount amount"></span>
                </div>
            </div>
            <?php
        }
    }

    public function add_cart_item($cart_item)
    {
        $cart_item = $this->cart_adjust_price($cart_item);
        return $cart_item;
    }

    public function add_cart_item_data($cart_item_meta, $product_id, $post_data = null)
    {
        if (is_null($post_data)) {
            $post_data = $_POST;
        }
        $zaddon = isset($post_data['zaddon'] ) ? $post_data['zaddon'] : array();
        $groups = Group::getByProduct($product_id);
        if (count($groups) === 0) return $cart_item_meta;
        $groupIDs = array_map(function ($group) {
            return $group->getID();
        }, $groups);
        $zaddon = array_filter($zaddon, function ($id) use ($groupIDs) {
            return in_array(intval($id), $groupIDs);
        }, ARRAY_FILTER_USE_KEY);
        $zaddon = array_map(function ($group) {
            return array_map(function ($type) {
                switch ($type['type']) {
                    case 'select':
                    case 'radio':
                        {
                            $type['value'] = isset($type['value'] ) ? intval($type['value']) : array();
                            return $type;
                        }
                    case 'checkbox':
                        {
                            $type['value'] = isset($type['value'] ) ? array_map('intval', (array)$type['value']) : array();
                            return $type;
                        }
                    case 'text':
                    default:
                        {
                            $type['value'] = array_map('esc_sql', (array)$type['value']);
                            return $type;
                        }
                }
            }, $group);
        }, $zaddon);
        $zaddon = json_encode($zaddon);
        $cart_item_meta['_zaddon_values'] = $zaddon;
        return $cart_item_meta;
    }

    public function cart_adjust_price($cart_item)
    {
        if (!isset($cart_item['_zaddon_values']))
            return $cart_item;
        $zaddon = json_decode($cart_item['_zaddon_values'], true);
        if (!empty($zaddon)) {
            $product = $cart_item['variation_id'] ? wc_get_product($cart_item['variation_id']) : wc_get_product($cart_item['product_id']);
            $groups = Group::getByProduct($cart_item['product_id']);
            $groups = array_map(function ($group) {
                return $group->getData();
            }, $groups);
            $additional = array_reduce($groups, function ($total, $group) use ($zaddon) {
                $groupAddon = sizeof($group['types']) > 0 && isset($zaddon[$group['id']])? $zaddon[$group['id']] : [];
                return $groupAddon
                    ? array_reduce($group['types'], function ($total, $type) use ($groupAddon) {
                        $typeAddon = $groupAddon[$type['id']];
                        switch ($type['type']) {
                            case 'select':
                            case 'radio':
                                {
                                    return array_reduce($type['values'], function ($total, $value) use ($typeAddon) {
                                        return ($value['id'] === $typeAddon['value']) ? $total + $value['price'] : $total;
                                    }, $total);
                                }
                            case 'checkbox':
                                {
                                    return array_reduce($type['values'], function ($total, $value) use ($typeAddon) {
                                        return in_array($value['id'], $typeAddon['value']) ? $total + $value['price'] : $total;
                                    }, $total);
                                }
                            case 'text':
                            default:
                                {
                                    return array_reduce($type['values'], function ($total, $value) use ($typeAddon) {
                                        return !empty($typeAddon['value'][$value['id']]) ? $total + $value['price'] : $total;
                                    }, $total);
                                }
                        }
                    }, $total)
                    : $total;
            }, 0);
            $cart_item['_zaddon_additional'] = $additional;

            $cart_item['data']->set_price($cart_item['data']->get_price() + $additional);
        }
        return $cart_item;
    }

    public function cart_item_restored($cart_item_key, $cart)
    {
        if (isset($cart->cart_contents[$cart_item_key])) {
            $cart_item = $cart->cart_contents[$cart_item_key];
            $cart_item = $this->cart_adjust_price($cart_item);
        }
    }

    public static function order_item_meta($item_id, $item)
    {
        if (!$item instanceof \WC_Order_Item_Product) return;
        $exist = property_exists($item, 'legacy_values');
        if ($exist) {
            header('typer: legacy');
            $item_data = $item->legacy_values;
            $product_id = $item_data['product_id'];
            $zaddon = $item_data['_zaddon_values'];
            $additional = $item_data['_zaddon_additional'];
        } else {
            header('typer: get');
            $product_id = $item->get_product_id();
            $zaddon = $item->get_meta('_zaddon_values');
            $additional = $item->get_meta('_zaddon_additional');
        }
        self::add_meta_to_item($product_id, $zaddon, $additional, $item);
        if ($exist) {
            $item->save();
        }
    }

    public static function add_meta_to_item($product_id, $zaddon, $additional, $item)
    {
        $item->add_meta_data('_zaddon_values', $zaddon, true);
        $zaddon_meta = self::item_meta($product_id, $zaddon);
        array_walk($zaddon_meta, function ($meta, $key) use ($item) {
            $item->add_meta_data($key, $meta, true);
        });
        if ($additional > 0) {
            $item->add_meta_data('_zaddon_additional', $additional, true);
            $item->add_meta_data('Additional', wc_price($additional), true);
        }
    }

    public static function add_item_data($item_data, $cart_item)
    {
        if (!isset($cart_item['_zaddon_values']))
            return $item_data;
        $zaddon_meta = self::item_meta($cart_item['product_id'], $cart_item['_zaddon_values']);
        $zaddon_meta = array_map(function ($display, $key) {
            $key = strip_tags($key);
            $display = strip_tags($display);
            return compact('display', 'key');
        }, $zaddon_meta, array_keys($zaddon_meta));
        $item_data = array_merge($item_data, $zaddon_meta);
        if ($cart_item['_zaddon_additional'] > 0) {
            $item_data[] = [
                'display' => strip_tags(wc_price($cart_item['_zaddon_additional'])),
                'key' => 'Additional'
            ];
        }
        return $item_data;
    }

    protected static function item_meta($product_id, $zaddon)
    {
        if (!$zaddon) return [];
        $zaddon = json_decode($zaddon, true);
        $groups = Group::getByProduct($product_id);
        $groups = array_map(function ($group) {
            return $group->getData();
        }, $groups);
        $zaddon_meta = array_reduce($groups, function ($groups, $group) use ($zaddon) {
            $groupAddon = sizeof($group['types']) > 0 && isset($zaddon[$group['id']])? $zaddon[$group['id']] : [];
            $types = $groupAddon ? array_reduce($group['types'], function ($types, $type) use ($groupAddon) {
                $typeAddon = $groupAddon[$type['id']];
                $valuesMeta = [];
                switch ($type['type']) {
                    case 'select':
                    case 'radio':
                        {
                            $valuesMeta = array_reduce($type['values'], function ($acc, $value) use ($typeAddon) {
                                if ($value['id'] === $typeAddon['value']) $acc[$value['id']] = $value['title'] . ($value['price'] ? ' (' . wc_price($value['price']) . ')' : '');
                                return $acc;
                            }, $valuesMeta);
                            break;
                        }
                    case 'checkbox':
                        {
                            $valuesMeta = array_reduce($type['values'], function ($acc, $value) use ($typeAddon) {
                                if (in_array($value['id'], $typeAddon['value'])) $acc[$value['id']] = $value['title'] . ($value['price'] ? ' (' . wc_price($value['price']) . ')' : '');
                                return $acc;
                            }, $valuesMeta);
                            break;
                        }
                    case 'text':
                    default:
                        {
                            $valuesMeta = array_reduce($type['values'], function ($acc, $value) use ($typeAddon) {
                                if (!empty($typeAddon['value'][$value['id']])) $acc[$value['id']] = $value['title'] . ' ' . esc_sql($typeAddon['value'][$value['id']]) . ($value['price'] ? ' (' . wc_price($value['price']) . ')' : '');
                                return $acc;
                            }, $valuesMeta);
                            break;
                        }
                }
                $keysMeta = array_map(function ($metaKey) use ($type) {
                    return '<span id="' . $metaKey . '">' . $type['title'] . '</div>';
                }, array_keys($valuesMeta));
                return array_merge($types, array_combine($keysMeta, $valuesMeta));
            }, []) : [];
            return array_merge($groups, $types);
        }, []);
        return $zaddon_meta;
    }

    public function get_cart_item_from_session($cart_item, $values)
    {
        if (!empty($values['_zaddon_values'])) {
            $cart_item['_zaddon_values'] = $values['_zaddon_values'];
            $cart_item['_zaddon_additional'] = $values['_zaddon_additional'];
            $cart_item = $this->add_cart_item($cart_item);
        }
        return $cart_item;
    }

    public function enqueue_scripts()
    {
        if (is_single() && is_product()) {
            wp_enqueue_script('za_product.js', plugins_url('assets/product.js', \ZAddons\PLUGIN_ROOT_FILE), ['jquery']);
            wp_enqueue_style('za_product.css', plugins_url('assets/product.css', \ZAddons\PLUGIN_ROOT_FILE));
        }
    }

    public function hidden_order_itemmeta($meta)
    {
        $meta[] = '_zaddon_additional';
        $meta[] = '_zaddon_values';
        return $meta;
    }

    public function add_tab_wc_product()
    {
        if (isset($_GET['zmodal']) && $_GET['zmodal'] === 'true') {
            add_filter('admin_body_class', function ($classes) {
                return $classes . ' zmodal';
            });
            wp_enqueue_style('za_admin.css', plugins_url('assets/admin.css', \ZAddons\PLUGIN_ROOT_FILE));
        }
        if (!is_admin('post.php') && get_post_type() !== "product" && !get_the_ID()) return;

        add_action('woocommerce_product_data_tabs', function ($product_data_tabs) {
            $product_data_tabs['zaddon-product-options'] = [
                'label' => 'Product Add-Ons',
                'target' => 'product_zaddons',
            ];
            return $product_data_tabs;
        });

        add_action('wp_ajax_zaddon_save_group', [$this, 'ajax_save_group']);

        add_action('woocommerce_product_data_panels', function () {
            global $post;
            $this->wc_tab_addons($post);
        });

        add_action('admin_footer', function () {
            global $post; ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#zaddon_group_name").val("");
                    $("#product_zaddons").on("click", "#zaddon_submit", function(e) {
                        e.preventDefault();
                        var data = {
                            "action": "zaddon_save_group",
                            "group_name": $("#zaddon_group_name").val(),
                            "post_id": <?= isset($post->ID) ? $post->ID : 0; ?>
                        };
                        $.post(ajaxurl, data, function(response) {
                            $("#product_zaddons").html($(response).html());
                        });
                    });
                });
            </script><?php
        });
    }

    public function wc_tab_addons($post)
    {
        $product = new \WC_Product($post);
        $id = $product->get_id();
        $groups = Group::getByProduct($product, true);
        ?>

        <div id="product_zaddons" class="panel woocommerce_options_panel">
            <div class="options_group" style="padding: 10px">
                <label>Name</label>
                <input name="zaddon_group_name" id="zaddon_group_name" placeholder="Group name" value="" style="width: 200px;"
                       type="text">
                <input id="zaddon_submit" class="button button-primary" value="Add Group" type="button">
            </div>

            <?php if (!empty($groups)) { ?>
                <div class="options_group">
                    <table class="wp-list-table widefat fixed striped posts" style="border: 0">
                        <tbody>
                        <?php foreach ($groups as $group) { ?>
                            <tr class="no-items">
                                <td>
                                    <span class="dashicons dashicons-exerpt-view" style="margin: 5px 5px 0px 0px;"></span>
                                    <strong style="margin: 5px 5px 0px 0px; min-height: 20px; display: inline-block;">
                                        <?= $group->title; ?>
                                    </strong>
                                </td>
                                <td style="text-align: right; width: 60px">
                                    <a href="<?= add_query_arg([
                                        'zmodal' => 'true',
                                        'KeepThis' => 'true',
                                        'TB_iframe' => 'true',
                                        'width' => 755,
                                        'height' => 340
                                    ], $group->getLink()); ?>" onclick="return false;" class="thickbox button">Edit</a></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <div class="options_group">
                <p class="form-field">
                    <label for="_zaddon_disable_global">Disable globals</label>
                    <input
                            class="checkbox"
                            name="_zaddon_disable_global"
                            id="_zaddon_disable_global"
                            value="yes"
                            type="checkbox"
                        <?php checked('yes', get_post_meta($id, '_zaddon_disable_global', true)) ?>
                    >
                    <span class="description">
						Check this box if you want to disable global groups and use the above ones only!
					</span>
                </p>
            </div>
        </div>
        <?php
    }

    public function has_values($values) {
        return array_reduce($values, function ($has_values, $value) {
            return $has_values || !empty($value);
        }, false);
    }
    public function add_to_cart_validation($status, $product_id )
    {
        $product_item = wc_get_product($product_id);

        if (is_object($product_item) && $product_item->get_id() > 0) {
            $groups = Group::getByProduct($product_item);
            foreach ($groups as $group) {
                foreach ($group->types as $type) {
                    if (!self::is_shown_children_options($type->values)) {
                        continue;
                    }
                    $option_values = $_POST['zaddon'][$group->getID()][$type->getID()];
                    if ( $type->required && !isset($option_values['value'])
                        || $type->type === 'text' && $type->required && !$this->has_values($option_values['value'])) {
                        wc_add_notice(' Option ' . $type->title . ' is required ', 'error');
                        $status = false;
                    }
                }
            }
        }
        return $status;

    }


    public function ajax_save_group()
    {
        $name = esc_sql($_POST['group_name']);
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        if ($post) {
            $group = new Group();
            $group->title = $name;
            $group->products = [$post_id];
            $group->apply_to = 'custom';
            $group->save();
        }
        $this->wc_tab_addons($post);
        exit;
    }

    public function addons_fields_save($post_id)
    {
        // Checkbox
        $woocommerce_checkbox = isset($_POST['_zaddon_disable_global']) ? 'yes' : 'no';
        update_post_meta($post_id, '_zaddon_disable_global', $woocommerce_checkbox);
    }

    public static function is_shown_children_options($options) {
        return array_reduce($options, function ($exists, $option) {
            return $exists || !$option->hide;
        }, false);
    }
}