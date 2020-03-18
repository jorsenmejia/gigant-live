<?php

namespace ZAddons\Admin;

use ZAddons\Admin;
use ZAddons\Model\Group;
use ZAddons\Model\Type;
use ZAddons\Model\Value;

class SingleGroup
{
	private $group_page;

	public function __construct()
	{
		add_action('admin_menu', [$this, 'admin_menu'], 1000);
		add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_ajax_is_active_checkout_add_on_plugin', [ListGroup::class, 'is_active_checkout_add_on']);
    }

	public function admin_menu()
	{
		$this->group_page = add_submenu_page(
			null,
			'Create group',
			'Create group',
			'manage_woocommerce',
			'za_group',
			[$this, 'process']
		);
	}

	public function process()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$this->update();
		} else {
			$this->render();
		}
	}

	public function admin_scripts()
	{
		if (get_current_screen()->base === $this->group_page) {
			wp_enqueue_script('za_group', plugins_url('assets/core/adminGroup.js', \ZAddons\PLUGIN_ROOT_FILE), ['zAddons', 'wc-enhanced-select']);
		}
	}

	protected function render()
	{
        $data = isset($_GET) && isset($_GET['id'])? Group::getByID(intval($_GET['id']))->getData(true) : [];
        $data['zmodal'] = isset($_GET['zmodal']) ? 'true' : 'false';
		$categories = $this->getCategories();
		$page_data = compact('data', 'categories');
		?>
		<div class="wrap">
			<h1 class="nav-tab-wrapper woo-nav-tab-wrapper">
				<a href="<?= Admin::getGroupsUrl(); ?>" class="nav-tab nav-tab-active">
					Groups
				</a>
			</h1>
			<div id="react-root"></div>
			<script>
                window.SITE_URL = "<?= esc_url_raw(get_site_url()); ?>";
                renderGroup(<?php echo json_encode($page_data); ?>, document.getElementById("react-root"));
			</script>
		</div>
		<?php
	}

	protected function update()
	{
	    $data = stripslashes_deep($_POST);
		$group = ($id = filter_var($data['id'], FILTER_VALIDATE_INT))
			? Group::getByID($id)
			: new Group();

		if ($data['delete']) {
			$group->delete();
			header('Location: ' . Admin::getGroupsUrl());
			exit();
		}

		$group->title = esc_sql($data['title']);
		$group->priority = filter_var($data['priority'], FILTER_VALIDATE_INT);
		$group->apply_to = esc_sql($data['apply_to']);
		if ($group->apply_to === "all") {
			$group->products = [];
			$group->categories = [];
		} else {
			$group->products = array_map('intval', (array)$data['products']);
			$group->categories = array_map('intval', (array)$data['categories']);
		}

		$types = array_values((array)$data['types']);

		$group->types = array_map(function ($typeData) use ($group) {
			if ($typeData['id']) {
				$type = $group->types[$typeData['id']];
			} else {
				$type = new Type();
			}
			$type->type = $typeData['type'];
			$type->status = $typeData['status'];
			$type->accordion = $typeData['accordion'];
			$type->step = $typeData['step'];
			$type->title = $typeData['title'];
			$type->required = boolval($typeData['required']);
			$type->hide_description = boolval($typeData['hide_description']);
			$type->display_description_on_expansion = boolval($typeData['display_description_on_expansion']);
			$type->description = $typeData['description'];

            $values = array_values((array)$typeData['values']);

			$type->values = array_map(function ($valueData) use ($type) {
				if ($valueData['id']) {
					$value = $type->values[$valueData['id']];
				} else {
					$value = new Value();
				}

				$value->price = floatval($valueData['price']);
				$value->step = $valueData['step'];
				$value->hide = $valueData['hide'];
				$value->hide_description = $valueData['hide_description'];
				$value->title = $valueData['title'];
				$value->checked = boolval($valueData['checked']);
				$value->description = $valueData['description'];

				return $value;
			}, $values);

			return $type;
		}, $types);

		$group->save();

		$link = $group->getLink();

		if (isset($data['zmodal']) && $data['zmodal'] === "true") {
			$link = add_query_arg('zmodal', 'true', $link);
		}

		header('Location: ' . $link);
		exit();
	}

	protected function getCategories()
	{
		$all_terms = get_terms([
			'taxonomy' => 'product_cat',
			'hierarchical' => true,
			'childless' => false,
		]);
		$all_terms = array_map(function ($term) {
			$el = new \stdClass();
			$el->id = $term->term_id;
			$el->name = $term->name;
			$el->parent = $term->parent;
			return $el;
		}, $all_terms);

		return $this->getChildCategories($all_terms, 0);
	}

	protected function getChildCategories($all, $term_id)
	{
        $root_terms = array_filter($all, function ($term) use ($term_id) {
			return isset($term->parent ) && $term->parent === $term_id;
		});

		return array_values(array_map(function ($term) use ($all) {
			$term->child = $this->getChildCategories($all, $term->id);
			unset($term->parent);
			return $term;
		}, $root_terms));
	}
}
