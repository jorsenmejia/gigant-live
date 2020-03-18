<?php
namespace ZAddons\Admin;
use ZAddons\Admin;
use ZAddons\Model\AddOn;
use ZAddons\Model\Group;
use ZAddons\DB;

class ListGroup
{
    private $groups_page;
    const DefaultHeaderText = "Checkout Add-ons";

    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu'], 1000);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_ajax_get_header_text', [$this, 'get_json_header_text_is_addon_active']);
        add_action('wp_ajax_zaddon_save_header_text', [$this, 'ajax_save_header_text']);
    }

    public function admin_menu()
    {
        $this->groups_page = add_submenu_page(
            'edit.php?post_type=product',
            'Product Add-Ons',
            'Product Add-Ons',
            'manage_woocommerce',
            'za_groups',
            [$this, 'process']
        );
    }

    public function admin_scripts()
    {
        if (get_current_screen()->base === $this->groups_page) {
            wp_enqueue_script('za_groups', plugins_url('assets/core/adminGroups.js', \ZAddons\PLUGIN_ROOT_FILE), ['zAddons']);
        }
        wp_enqueue_style('za_admin.css', plugins_url('assets/admin.css', \ZAddons\PLUGIN_ROOT_FILE));
    }

    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && method_exists($this, 'update')) {
            $this->update();
        } else {
            $this->render();
        }
    }

    protected function render()
    {
        $groups = Group::getAll();
        $groups = array_map(function (Group $group) {
            return $group->getData();
        }, $groups);
        $active_tab = "groups";
        if(isset($_GET["tab"]))
        {
            if($_GET["tab"] == "groups")
            {
                $active_tab = "groups";
            }
            else
            {
                $active_tab = "add-ons";
            }
        }
        $page_data = compact('groups');
        ?>
        <div class="wrap">
            <h1 class="nav-tab-wrapper woo-nav-tab-wrapper">
                <a href="<?= Admin::getGroupsUrl() ; ?>" class="nav-tab <?php if($active_tab === 'groups'){echo 'nav-tab-active';} ?>">
                    Groups
                </a>
                <a href="<?= Admin::getAddOnsUrl(); ?>" class="nav-tab <?php if($active_tab === 'add-ons'){echo 'nav-tab-active';} ?>">
                    Add-Ons
                </a>
                <a href="<?= (new Group())->getLink(); ?>" class="alignright page-title-action">
                    Add new
                </a>
            </h1>
            <?php
            if ($active_tab === 'groups') : ?>
                <div id="react-root"></div>
                <script>
                    window.SITE_URL = "<?= esc_url_raw(get_site_url()); ?>";
                    renderGroups(<?php echo json_encode($page_data); ?>, document.getElementById("react-root"));
                </script>
            <?php else : $this->add_ons_render(); endif;?>
        </div>
        <?php
    }

    public function add_ons_render()
    {
        $add_ons = AddOn::get_all_add_ons();
        wp_enqueue_style('za_admin.css', plugins_url('assets/admin.css', \ZAddons\PLUGIN_ROOT_FILE));
        ?>
        <h2>Plugins</h2>
        <div class="plugins-area">
            <?php
            foreach ($add_ons as $key => $plugin) {
                ?>
                <div class="card-box-plugin" id="<?= $key ?>">
                    <div class="card-box-header">
                        <?= $plugin->title ?>
                    </div>
                    <div class="card-box-description">
                        <?= $plugin->description ?>
                    </div>
                    <div class="card-box-footer">
                        <div class="card-box-left-footer">
                            <?php
                            if (!has_action($plugin->hook_name)) {
                                ?>
                                <span class="dot dot-enable"></span> <span><a href="<?= admin_url('plugins.php') ?>">Enable</a></span>
                                <?php
                            } else {
                                ?>
                                <span class="dot dot-active"></span><span>Active</span>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="card-box-right-footer">
                            <a href="<?= $plugin->plugin_link ?>">More info</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }

    public function get_json_header_text_is_addon_active()
    {
        echo json_encode(array(
            'headerTextCart' => $this->get_header_text_of(),
            'headerTextCheckout' => $this->get_header_text_of('checkout'),
            'isAddOnActive' => self::is_active_checkout_add_on(true)
        ));
        die();
    }

    public function get_header_text_of($type = 'cart')
    {
        global $wpdb;
        $prefix = $wpdb->prefix . DB::Prefix;
        $headers_table = $prefix . DB::Headers;
        $res = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT header_text FROM ${headers_table} 
                    WHERE header_type = %s
                    ORDER BY id DESC LIMIT 1
                    ", $type)

        );
        $res = $res ? $res : self::DefaultHeaderText;

        return $res;
    }

    public function ajax_save_header_text()
    {
        global $wpdb;
        echo json_encode($_GET);
        $header_text_cart = $_GET['header_cart_text'] ? str_replace('\\', '', $_GET['header_cart_text'] ) : self::DefaultHeaderText;
        $header_text_checkout = $_GET['header_checkout_text'] ? str_replace('\\', '', $_GET['header_checkout_text'] ) : self::DefaultHeaderText;
        $table_name = $wpdb->prefix . DB::Prefix . DB::Headers;
        $wpdb->query("TRUNCATE TABLE ${table_name}");
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO ${table_name}
                (header_text, header_type)
                 VALUES 
                 (%s, 'cart'),
                 (%s, 'checkout')
                 ", $header_text_cart, $header_text_checkout)
        );
        exit;
    }

    public static function is_active_checkout_add_on($as_string = false)
    {
        $is_add_on_active = has_filter('zproductaddon_is_loaded') ? 'yes' : 'no';
        if ($as_string)
            return $is_add_on_active;

        echo json_encode(array(
            'isAddOnActive' => $is_add_on_active
        ));

        die();
    }
}