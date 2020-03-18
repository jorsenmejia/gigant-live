<?php
class woofiltersWpf extends moduleWpf {
	public $mainWCQuery = '';
	public function init() {
		dispatcherWpf::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_shortcode(WPF_SHORTCODE, array($this, 'render'));
		add_shortcode(WPF_SHORTCODE_PRODUCTS, array($this, 'renderProductsList'));
		if(is_admin()) {
			add_action('admin_notices', array($this, 'showAdminErrors'));
		}
		frameWpf::_()->addScript('jquery-ui-autocomplete', '', array('jquery'), false, true);

		add_action('woocommerce_product_query', array($this, 'loadProductsFilter'));
		add_filter('woocommerce_product_query_tax_query', array($this, 'customProductQueryTaxQuery'), 10, 2);

		$options = frameWpf::_()->getModule('options')->getModel('options')->getAll();
		add_filter('loop_shop_per_page', array($this, 'newLoopShopPerPage'), 20 );

        class_exists( 'WC_pif' ) && add_filter( 'post_class', array( $this, 'WC_pif_product_has_gallery' ) );
	}

	public function newLoopShopPerPage($count) {
		$options = frameWpf::_()->getModule('options')->getModel('options')->getAll();
		if(isset($options['count_product_shop']) && isset($options['count_product_shop']['value']) && !empty($options['count_product_shop']['value'])){
			$count  = $options['count_product_shop']['value'];
		}
		return $count ;
	}

	public function addWooOptions($args) {
		if(get_option('woocommerce_hide_out_of_stock_items') == 'yes') {
			$args['meta_query'][] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => '!='
				)
			);
		}
		return $args;
	}

	public function loadProductsFilter($q){
		$metaQuery = $this->preparePriceFilter(reqWpf::getVar('min_price'), reqWpf::getVar('max_price'));
		if($metaQuery != false) {
			//$q->set('meta_query', array_merge(WC()->query->get_meta_query(), $metaQuery));
			$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
			remove_filter( 'posts_clauses', array(WC()->query, 'price_filter_post_clauses' ), 10, 2);
		}
		if(reqWpf::getVar('pr_stock')) {
			$metaQuery = array(
				array(
					'key'     => '_stock_status',
					'value'   => reqWpf::getVar('pr_stock'),
					'compare' => '='
				)
			);
			//$q->set( 'meta_query', array_merge( WC()->query->get_meta_query(), $metaQuery ) );
			$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
		}
		if(reqWpf::getVar('pr_onsale')) {
			/*$metaQuery = array(
				'relation' => 'OR',
				array( // Simple products type
					'key'           => '_sale_price',
					'value'         => 0,
					'compare'       => '>',
					'type'          => 'numeric'
				),
				array( // Variable products type
					'key'           => '_min_variation_sale_price',
					'value'         => 0,
					'compare'       => '>',
					'type'          => 'numeric'
				)
			);
			$args['meta_query'][] = $metaQuery;*/
			$q->set('post__in', array_merge(array(0), wc_get_product_ids_on_sale()));
		}

		if(reqWpf::getVar('pr_author')) {
			$author_obj = get_user_by('slug', reqWpf::getVar('pr_author'));
			if(isset($author_obj->ID)){
				$q->set( 'author', $author_obj->ID );
			}
		}
		if(reqWpf::getVar('pr_rating')) {
			$ratingRange = reqWpf::getVar('pr_rating');
			$range = explode('-', $ratingRange);
			if(intval($range[1] ) !== 5){
				$range[1] = $range[1] - 0.001;
			}
			$metaQuery = array(
				array( // Simple products type
					'key' => '_wc_average_rating',
					'value' => array($range[0], $range[1]),
					'type' => 'DECIMAL',
					'compare' => 'BETWEEN'
				)
			);
			//$q->set( 'meta_query', array_merge( WC()->query->get_meta_query(), $metaQuery ) );
			$q->set('meta_query', array_merge($q->get('meta_query'), $metaQuery));
		}
		$this->mainWCQuery = $q;
	}
	public function customProductQueryTaxQuery($tax_query) {
		foreach($tax_query as $i => $tax) {
			if(is_array($tax) && isset($tax['field']) && $tax['field'] == 'slug') {
				$name = str_replace('pa_', 'filter_', $tax['taxonomy']);
				$param = reqWpf::getVar($name);
				if(!is_null($param)) {
					$slugs = explode('|', $param);
					if(sizeof($slugs) > 1) {
						$tax_query[$i]['terms'] = $slugs;
						$tax_query[$i]['operator'] = 'IN';
					}
				}
			}
		}
		if(reqWpf::getVar('pr_featured')) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured'
			);
		}
		$getGet = reqWpf::get('get');
		foreach ($getGet as $key => $value) {
		    if (strpos($key, 'filter_cat_list') !== false) {
                $param = reqWpf::getVar($key);
                if(!is_null($param)) {
                    $idsAnd = explode(',', $param);
                    $idsOr = explode('|', $param);
                    $isAnd = sizeof($idsAnd) > sizeof($idsOr);
                    $tax_query[] = array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $isAnd ? $idsAnd : $idsOr,
                        'operator' => $isAnd ? 'AND' : 'IN',
                        'include_children' => false,
                    );
                }
            } elseif (strpos($key, 'filter_cat') !== false) {
                $param = reqWpf::getVar($key);
                if(!is_null($param)) {
                    $idsAnd = explode(',', $param);
                    $idsOr = explode('|', $param);
                    $isAnd = sizeof($idsAnd) > sizeof($idsOr);
                    $tax_query[] = array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $isAnd ? $idsAnd : $idsOr,
                        'operator' => $isAnd ? 'AND' : 'IN',
                        'include_children' => true,
                    );
                }
            } else if(strpos($key, 'product_tag') !== false) {
                $param = reqWpf::getVar($key);
                if(!is_null($param)) {
					$param = str_replace(' ', '+', $param);
                    $idsOr = explode('+', $param);
                    $tax_query[] = array(
                        'taxonomy' => 'product_tag',
                        'field'    => 'slug',
                        'terms'    => $idsOr,
                        'operator' => 'IN',
                        'include_children' => true,
                    );
                }
            } 

        }

		return $tax_query;
	}
	public function addAdminTab($tabs) {
		$tabs[ $this->getCode(). '#wpfadd' ] = array(
			'label' => __('Add New Filter', WPF_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-plus-circle', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode(). '_edit' ] = array(
			'label' => __('Edit', WPF_LANG_CODE), 'callback' => array($this, 'getEditTabContent'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() ] = array(
			'label' => __('Show All Filters', WPF_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-list', 'sort_order' => 20, //'is_main' => true,
		);
		return $tabs;
	}
	public function getCurrencyPrice($price) {
		return apply_filters('raw_woocommerce_price', $price);
	}
	public function preparePriceFilter($minPrice = null, $maxPrice = null, $rate = null) {
		if(is_null($minPrice) && is_null($maxPrice)) return false;

		if(is_null($rate)) {
			$rate = $this->getCurrentRate();
		}
		$metaQuery = array('key' => '_price', 'price_filter' => true, 'type' => ($rate == 1 ? 'NUMERIC' : 'DECIMAL'));
		if(is_null($minPrice)) {
			$metaQuery['compare'] = '<=';
			$metaQuery['value'] = $minPrice / $rate;
		} elseif(is_null($maxPrice)) {
			$metaQuery['compare'] = '>=';
			$metaQuery['value'] = $maxPrice / $rate;
		} else {
			$metaQuery['compare'] = 'BETWEEN';
			$metaQuery['value'] = array($minPrice / $rate, $maxPrice / $rate);
		}

		return array('price_filter' => $metaQuery);
	}
	public function getCurrentRate() {
		$price = 1000;
		$newPrice = $this->getCurrencyPrice($price);
		return $newPrice / $price;
	}
	public function addHiddenFilterQuery($query) {
		if($hidden_term = get_term_by('name', 'exclude-from-catalog', 'product_visibility')) {
			$query[] = array(
				'taxonomy' => 'product_visibility',
				'field' => 'term_taxonomy_id',
				'terms' => array($hidden_term->term_taxonomy_id),
				'operator' => 'NOT IN'
			);
		}
		return $query;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getEditTabContent() {
		$id = reqWpf::getVar('id', 'get');
		return $this->getView()->getEditTabContent( $id );
	}
	public function getEditLink($id, $tableTab = '') {
		$link = frameWpf::_()->getModule('options')->getTabUrl( $this->getCode(). '_edit' );
		$link .= '&id='. $id;
		if(!empty($tableTab)) {
			$link .= '#'. $tableTab;
		}
		return $link;
	}
	public function render($params){
		return $this->getView()->renderHtml($params);
	}
	public function renderProductsList($params){
		return $this->getView()->renderProductsListHtml($params);
	}
	public function showAdminErrors() {
		// check WooCommerce is installed and activated
		if(!$this->isWooCommercePluginActivated()) {
			// WooCommerce install url
			$wooCommerceInstallUrl = add_query_arg(
				array(
					's' => 'WooCommerce',
					'tab' => 'search',
					'type' => 'term',
				),
				admin_url( 'plugin-install.php' )
			);
			$tableView = $this->getView();
			$tableView->assign('errorMsg',
				$this->translate('For work with "')
				. WPF_WP_PLUGIN_NAME
				. $this->translate('" plugin, You need to install and activate <a target="_blank" href="' . $wooCommerceInstallUrl . '">WooCommerce</a> plugin')
			);
			// check current module
			if(reqWpf::getVar('page') == WPF_SHORTCODE) {
				// show message
				echo $tableView->getContent('showAdminNotice');
			}
		}
	}
	public function isWooCommercePluginActivated() {
		return class_exists('WooCommerce');
	}

    public function WC_pif_product_has_gallery( $classes ) {
        global $product;

        $post_type = get_post_type( get_the_ID() );

        if ( wp_doing_ajax() ) {

            if ( $post_type == 'product' ) {

                if ( is_callable( 'WC_Product::get_gallery_image_ids' ) ) {
                    $attachment_ids = $product->get_gallery_image_ids();
                } else {
                    $attachment_ids = $product->get_gallery_attachment_ids();
                }

                if ( $attachment_ids ) {
                    $classes[] = 'pif-has-gallery';
                }
            }
        }

        return $classes;
    }
}
