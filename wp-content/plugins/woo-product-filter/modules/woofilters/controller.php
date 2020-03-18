<?php
class woofiltersControllerWpf extends controllerWpf {

	protected $_code = 'woofilters';

	protected function _prepareTextLikeSearch($val) {
		$query = '(title LIKE "%'. $val. '%"';
		if(is_numeric($val)) {
			$query .= ' OR id LIKE "%'. (int) $val. '%"';
		}
		$query .= ')';
		return $query;
	}
	public function _prepareListForTbl($data){
		foreach($data as $key => $row){
			$id = $row['id'];
			$shortcode = "[".WPF_SHORTCODE." id=".$id."]";
			$showPrewiewButton = "<button data-id='".$id."' data-shortcode='".$shortcode."' class='button button-primary button-prewiew' style='margin-top: 1px;'>".__('Prewiew', WPF_LANG_CODE)."</button>";
            $titleUrl = "<a href=".$this->getModule()->getEditLink( $id ).">".$row['title']." <i class='fa fa-fw fa-pencil'></i></a> <a data-filter-id='".$id."' class='wpfDuplicateFilter' href='' title='".__('Duplicate filter', WPF_LANG_CODE)."'><i class='fa fa-fw fa-clone'></i></a>";

            $data[$key]['shortcode'] = $shortcode;
			$data[$key]['rewiew'] = $showPrewiewButton;
			$data[$key]['title'] = $titleUrl;
		}
		return $data;
	}

	public function drawFilterAjax(){
        $res = new responseWpf();
        $data = reqWpf::get('post');
        if (isset($data) && $data) {
			$html = '';

			$isPro = frameWpf::_()->isPro();

			$styles[] = 'css/frontend.woofilters.css';
			$styles[] = 'css/frontend.multiselect.css';
			$styles[] = 'css/loaders.css';
			$styles[] = 'css/jquery.slider.min.css';
			$styles[] = 'css/move.sidebar.css';

			$scripts[] = 'js/frontend.woofilters.js';
			$scripts[] = 'js/frontend.multiselect.js';
			$scripts[] = 'js/jquery_slider/jshashtable-2.1_src.js';
			$scripts[] = 'js/jquery_slider/jquery.numberformatter-1.2.3.js';
			$scripts[] = 'js/jquery_slider/tmpl.js';
			$scripts[] = 'js/jquery_slider/jquery.dependClass-0.1.js';
			$scripts[] = 'js/jquery_slider/draggable-0.1.js';
			$scripts[] = 'js/jquery_slider/jquery.slider.js';
			$scripts[] = 'js/jquery_slider/tmpl.js';

			foreach ($styles as $style) {
				$html .= "<link rel='stylesheet' href='". frameWpf::_()->getModule('woofilters')->getModPath(). $style. "' type='text/css' media='all' />";
			}
			foreach ($scripts as $script) {
				$html .= "<script type='text/javascript' src='". frameWpf::_()->getModule('woofilters')->getModPath(). $script. "'></script>";
			}

			if ($isPro) {
					$stylesPro[] = 'css/frontend.woofilters.pro.css';
					$stylesPro[] = 'css/jquery-ui-autocomplete.css';
					$stylesPro[] = 'css/common.woofilters.pro.css';

					$stylesPro[] = 'css/ion.rangeSlider.css';
					$scriptsPro[] = 'js/frontend.woofilters.pro.js';
					$scriptsPro[] = 'js/ion.rangeSlider.min.js';

					foreach ($stylesPro as $style) {
						$html .= "<link rel='stylesheet' href='". frameWpf::_()->getModule('woofilterpro')->getModPath(). $style. "' type='text/css' media='all' />";
					}
					foreach ($scriptsPro as $script) {
						$html .= "<script type='text/javascript' src='". frameWpf::_()->getModule('woofilterpro')->getModPath(). $script. "'></script>";
					}
			}

			$html .= frameWpf::_()->getModule('woofilters')->render($data);

            $res->setHtml($html);
        } else {
			$res->pushError($this->getModule('woofilters')->getErrors());
			//$res->pushError(__('Empty or invalid data procided', WCU_LANG_CODE));
		}

        $res->ajaxExec();
    }

	public function save(){
		$res = new responseWpf();
		if(($id = $this->getModel('woofilters')->save(reqWpf::get('post'))) != false) {
			$res->addMessage(__('Done', WPF_LANG_CODE));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
		} else
			$res->pushError ($this->getModel('woofilters')->getErrors());
		return $res->ajaxExec();
	}

	public function deleteByID(){
		$res = new responseWpf();

		if($this->getModel('woofilters')->delete(reqWpf::get('post')) != false){
			$res->addMessage(__('Done', WPF_LANG_CODE));
		}else{
			$res->pushError ($this->getModel('woofilters')->getErrors());
		}
		return $res->ajaxExec();
	}

	public function createTable(){
		$res = new responseWpf();
		if(($id = $this->getModel('woofilters')->save(reqWpf::get('post'))) != false) {
			$res->addMessage(__('Done', WPF_LANG_CODE));
			$res->addData('edit_link', $this->getModule()->getEditLink( $id ));
		} else
			$res->pushError ($this->getModel('woofilters')->getErrors());
		return $res->ajaxExec();
	}

    public function filtersShowAtts($settings, $filterQueryVars, $returnScript = true){
        $filterQueryArgs = $this->createArgsForFilteringBySettings($settings, $filterQueryVars);
        $filterQueryArgs['posts_per_page'] = -1;
        $filterQueryArgs['hide_empty'] = 1;
        $filterQueryArgs['fields'] = 'ids';
        $filterLoop = new WP_Query($filterQueryArgs);
        $existTerms = array();
        if ($filterLoop->have_posts()) {
            while ($filterLoop->have_posts()) : $filterLoop->the_post();
                $_product = wc_get_product( get_the_id() );
                $tags = get_the_terms( get_the_id(), 'product_tag' );
                if (!empty($tags)) {
                    foreach ($tags as $term) {
                        !isset($existTerms['tag']) && $existTerms['tag'] = array();
                        array_push($existTerms['tag'], $term->term_id);
                    }
                }
                foreach ($_product->get_attributes() as $attr_name => $attr) {
                    $attr_name = urldecode(str_replace('pa_', '', $attr_name));
                    $terms = $attr->get_terms();
                    if(is_array($terms)) {
                    	foreach( $attr->get_terms() as $term ){
                        	!isset($existTerms[$attr_name]) && $existTerms[$attr_name] = array();
                        	array_push($existTerms[$attr_name], $term->term_id);
                        }
                    }
                }
            endwhile;
        }

        $existTermsJson = json_encode($existTerms);
        if ($returnScript) {
            return '
			<script type="text/javascript">
				wpfShowHideFiltersAtts('.$existTermsJson.');
			</script>
			';
        } else {
            return $existTerms;
        }
    }

	public function filtersCounter($settings, $filterQueryVars, $returnScript = true){
		$filterQueryArgs = $this->createArgsForFilteringBySettings($settings, $filterQueryVars);
		$filterQueryArgs['posts_per_page'] = -1;
		$filterQueryArgs['hide_empty'] = 1;
		$filterQueryArgs['fields'] = 'ids';
		$filterLoop = new WP_Query($filterQueryArgs);
		$loopFoundPost = $filterLoop->found_posts;
		$existTerms = array();
		if ($filterLoop->have_posts()) {
			while ($filterLoop->have_posts()) : $filterLoop->the_post();
				$_product = wc_get_product( get_the_id() );
				$tags = get_the_terms( get_the_id(), 'product_tag' );
				if (!empty($tags)) {
					foreach ($tags as $tag) {
						$existTerms['product_tag'] = isset($existTerms['product_tag']) ? $existTerms['product_tag'] : array();
						$existTerms['product_tag'][$tag->term_id] = isset($existTerms['product_tag'][$tag->term_id]) ? $existTerms['product_tag'][$tag->term_id] : 0;
						$existTerms['product_tag'][$tag->term_id] += 1;
					}
				}
				$category = get_the_terms( get_the_id(), 'product_cat' );
				if (!empty($category)) {
					foreach ($category as $cat) {
						$existTerms['filter_cat'] = isset($existTerms['filter_cat']) ? $existTerms['filter_cat'] : array();
						$existTerms['filter_cat'][$cat->term_id] = isset($existTerms['filter_cat'][$cat->term_id]) ? $existTerms['filter_cat'][$cat->term_id] : 0;
						$existTerms['filter_cat'][$cat->term_id] += 1;
					}
				}
				if( $_product->has_attributes() ){
				    $attributes = array();
				    foreach( $_product->get_attributes() as $taxonomy => $attribute ){
						$attributeValues = $attribute['options'];
						foreach ($attributeValues as $key => $value) {
							$existTerms[urldecode($taxonomy)] = isset($existTerms[urldecode($taxonomy)]) ? $existTerms[urldecode($taxonomy)] : array();
							$existTerms[urldecode($taxonomy)][$value] = isset($existTerms[urldecode($taxonomy)][$value]) ? $existTerms[urldecode($taxonomy)][$value] : 0;
							$existTerms[urldecode($taxonomy)][$value] += 1;
						}
				    }
				}
			endwhile;
		}

		$existTermsJson = json_encode($existTerms);
		if ($returnScript) {
			return '
			<script type="text/javascript">
				wpfChangeFiltersCount('.$existTermsJson.');
			</script>
			';
		} else {
			return $existTerms;
		}
	}

	public function filtersFrontend(){
		$res = new responseWpf();
		$params = reqWpf::get('post');
		$filterSettings = utilsWpf::jsonDecode(stripslashes($params['settings']));
		$settings = utilsWpf::jsonDecode(stripslashes($params['options']));
        $generalSettings = utilsWpf::jsonDecode(stripslashes($params['general']));
		$queryvars = utilsWpf::jsonDecode(stripslashes($params['queryvars']));
		$filterQueryVars = utilsWpf::jsonDecode(stripslashes($params['queryvars']));
		$curUrl = $params['currenturl'];
        $queryvars['posts_per_page'] = isset($filterSettings['count_product_shop']) && !empty($filterSettings['count_product_shop']) ? $filterSettings['count_product_shop'] : $queryvars['posts_per_page'];
		$args = $this->createArgsForFilteringBySettings($settings, $queryvars, $filterSettings, $generalSettings);
		//$paged = empty($params['runbyload']) || empty($queryvars['paged']) ? 1 : $queryvars['paged'];
		$paged = empty($queryvars['paged']) ? 1 : $queryvars['paged'];
		if(empty($params['runbyload']) && empty($queryvars['pagination'])) $paged = 1;

		$args['paged'] = $paged;
		class_exists('WC_pif') && add_filter('post_class', array($this->getModule(), 'WC_pif_product_has_gallery'));
		//get products
		ob_start();
		$loop = new WP_Query($args);
		$loopFoundPost = $loop->found_posts;
		if ($loop->have_posts()) {
			while ($loop->have_posts()) : $loop->the_post();
				wc_get_template_part('content', 'product');
			endwhile;
		} else {
			echo $filterSettings['text_no_products'];
		}
		$productsHtml = ob_get_clean();
		if (isset($filterSettings['filter_recount']) && $filterSettings['filter_recount']) {
			$productsHtml .= $this->filtersCounter($settings, $filterQueryVars);
		}
        $productsHtml .= $this->filtersShowAtts($settings, $filterQueryVars);
		//get result count
		ob_start();
		$args = array(
			'total'    => $loopFoundPost,
			'per_page' => $queryvars['posts_per_page'],
			'current'  => 1,//$queryvars['paged'],
		);
		wc_get_template( 'loop/result-count.php', $args );
		$resultCountHtml = ob_get_clean();

		//get pagination
		ob_start();
		$base    =  $queryvars['base'];

		//get query params
		$curUrl = explode( '?', $curUrl );
		$curUrl = isset($curUrl[1]) ? $curUrl[1] : '';

		// $getArray = array();
		// parse_str($curUrl, $getArray);
		// $getArray['product-page'] = '%#%';
		// $curUrl = http_build_query($getArray);
		// $curUrl = urldecode($curUrl);

		//add quary params to base url
		$fullBaseUrl =  $base . '?' . $curUrl;

		$format  = '';
		$total = ceil($loopFoundPost / $queryvars['posts_per_page']);

		//after filtering we always start from 1 page
		$args = array(
			'base'         => $fullBaseUrl,
			'format'       => $format,
			'add_args'     => false,
			'current'      => $paged,//1,//$queryvars['paged'],
			'total'        => $total,
			'prev_text'    => '&larr;',
			'next_text'    => '&rarr;',
			'type'         => 'list',
			'end_size'     => 3,
			'mid_size'     => 3,
		);
		wc_get_template( 'loop/pagination.php', $args );
		$paginationHtml = ob_get_clean();
		wp_reset_postdata();
		//Prepare params for WooCommerce Shop and Category template variants.
		$categoryHtml = '';
		$pageDisplay = get_option( 'woocommerce_shop_page_display', '' );
		$archiveDisplay = get_option( 'woocommerce_category_archive_display', '' );
		$shopPageId = wc_get_page_id( 'shop' );
		$currentPageId = isset($queryvars['page_id']) ? $queryvars['page_id'] : 0;
		$categoryPageId = isset($queryvars['product_category_id']) ? $queryvars['product_category_id'] : 0;
		$productTag = isset($productTag) ? $productTag : false;
		if ($productTag) {
			$termProductCategory = get_product_tag($categoryPageId);
		} else {
			$termProductCategory = get_term_by('id', $categoryPageId, 'product_category');
		}
		//Get exist categories from filter
		$productCounter = $this->filtersCounter($settings, $filterQueryVars, false);

		$productCounter = isset($productCounter['filter_cat']) ? $productCounter['filter_cat'] : array();
		$categoryIn = array();
		//Get all categories from filter by term_id
		foreach ($productCounter as $key => $category) {
			$categoryIn[] = get_term($key);
		}
		//Get parent categories of Shop. Template from WooCommerce.
		if ( $shopPageId == $currentPageId ) {
			if ($pageDisplay == 'subcategories') {
				ob_start();
				foreach ( $categoryIn as $category ) {
					if ($category->parent == 0) {
						wc_get_template( 'content-product_cat.php', array(
							'category' => $category,
						) );
					}
				}
				$categoryHtml .= ob_get_clean();
				$productsHtml = '';
				$paginationHtml = '';
			}
			if ($pageDisplay == 'both') {
				ob_start();
				foreach ( $categoryIn as $category ) {
					if ($category->parent == 0) {
						wc_get_template( 'content-product_cat.php', array(
							'category' => $category,
						) );
					}
				}
				$categoryHtml .= ob_get_clean();
			}
		}
		//Get subcategories of current category. Template from WooCommerce.
		if ( $termProductCategory ) {
			if ($archiveDisplay == 'subcategories') {
				ob_start();
				foreach ( $categoryIn as $category ) {
					if ($category->parent == $termProductCategory->term_id) {
						wc_get_template( 'content-product_cat.php', array(
							'category' => $category,
						) );
					}
				}
				$categoryHtml .= ob_get_clean();
				$productsHtml = '';
				$paginationHtml = '';
			}
			if ($archiveDisplay == 'both') {
				ob_start();
				foreach ( $categoryIn as $category ) {
					if ($category->parent == $termProductCategory->term_id) {
						wc_get_template( 'content-product_cat.php', array(
							'category' => $category,
						) );
					}
				}
				$categoryHtml .= ob_get_clean();
			}
		}
		$res->addData('categoryHtml', $categoryHtml);
		$res->addData('productHtml', $productsHtml);
		$res->addData('paginationHtml', $paginationHtml);
		$res->addData('resultCountHtml', $resultCountHtml);

		return $res->ajaxExec();
	}

	public function order_by_popularity_post_clauses_clone( $args ) {
		global $wpdb;
		$args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
		return $args;
	}

	public function getTaxonomyTerms(){
		$res = new responseWpf();
		$attrId = reqWpf::getVar('attr_id');

		$data = array();
		if(!is_null($attrId)) {

			$attrName = wc_attribute_taxonomy_name_by_id((int)$attrId);
			$args = array(
				'hide_empty' => false,
			);
			$terms = get_terms( $attrName, $args);
			foreach($terms as $term ){
				if(!empty($term->term_id)){
					$data[] = array('id' => $term->term_id, 'name' => $term->name);
				}
			}
		}
		$res->addData('terms', $data);
		return $res->ajaxExec();
	}

	public function createArgsForFilteringBySettings($settings, $queryvars, $filterSettings = array(), $generalSettings = array()){
		$queryvars['product_tag'] = isset($queryvars['product_tag']) ? $queryvars['product_tag'] : false;
        $queryvars['product_brand'] = isset($queryvars['product_brand']) ? $queryvars['product_brand'] : false;
        $asDefaultCats = array();
        $settingIds = array_column($settings, 'id');
        $settingCats = array_keys($settingIds, 'wpfCategory');
        if (!count($settingCats)) {
            foreach ($generalSettings as $generalSingle) {
                if ($generalSingle['id'] == 'wpfCategory' && $generalSingle['settings']['f_filtered_by_selected'] && !empty($generalSingle['settings']['f_mlist[]'])) {
                    $asDefaultCats = array_merge($asDefaultCats, explode(',', $generalSingle['settings']['f_mlist[]']));
                    break;
                }
            }
        }
		$args = array(
			'post_status' => 'publish',
			'post_type' => 'product',
			'paged' => 1,
			'posts_per_page' => $queryvars['posts_per_page'],
			'ignore_sticky_posts' => true,
			'tax_query' => array()
		);
		$args['tax_query'] = $this->getModule()->addHiddenFilterQuery($args['tax_query']);
		if( ( isset($queryvars['product_category_id']) || $asDefaultCats ) && !$queryvars['product_tag'] && !$queryvars['product_brand'] ){
			$args["tax_query"][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'id',
				'terms'    => isset($queryvars['product_category_id']) ? $queryvars['product_category_id'] : $asDefaultCats,
				'include_children' => true
			);
		} elseif ($queryvars['product_tag']) {
			$args["tax_query"][] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'id',
				'terms'    => $queryvars['product_tag'],
				'include_children' => true
			);
		} elseif ($queryvars['product_brand']) {
            $args["tax_query"][] = array(
                'taxonomy' => 'product_brand',
                'field'    => 'id',
                'terms'    => $queryvars['product_brand'],
                'include_children' => true
            );
        }
		$temp = array();
		foreach ($settings as $setting){
			if(!empty($setting['settings'])) {
				switch ($setting['id']){
					case 'wpfPrice':
						$priceStr = $setting['settings'][0];
						$priceVal = explode(',', $priceStr);
						if($priceVal[0] !== false && $priceVal[1]){
							$temp['wpfPrice']['min_price'] = $priceVal[0];
							$temp['wpfPrice']['max_price'] = $priceVal[1];
						}
						break;
					case 'wpfPriceRange':
						$priceStr = $setting['settings'][0];
						$priceVal = explode(',', $priceStr);
						if($priceVal[0] !== false && $priceVal[1]){
							$temp['wpfPrice']['min_price'] = $priceVal[0];
							$temp['wpfPrice']['max_price'] = $priceVal[1];
						}
						break;
					case 'wpfSortBy':
						switch ( $setting['settings'] ) {
							case 'title':
								$args['orderby'] = 'title';
								$args["order"] = 'ASC';
								break;
							case 'rand':
								$args['orderby'] = 'rand';
								break;
							case 'date':
								$args['orderby'] = 'date';
								break;
							case 'price':
								$args['meta_key'] = '_price';
								$args['orderby'] = 'meta_value_num';
								$args['order'] = 'ASC';
								break;
							case 'price-desc':
								$args['meta_key'] = '_price';
								$args['orderby'] = 'meta_value_num';
								$args['order'] = 'DESC';
								break;
							case 'popularity':
								$args['orderby'] = 'meta_value_num';
								$args['order'] = 'DESC';
								$args['meta_key'] = 'total_sales';
								break;
							case 'rating':
								$args['meta_key'] = '_wc_average_rating'; // @codingStandardsIgnoreLine
								$args['orderby']  = array(
									'meta_value_num' => 'DESC',
									'ID'             => 'ASC',
								);
								break;
						}
						break;
					case 'wpfCategory':
						$categoryIds = $setting['settings'];
						$args["tax_query"][] = array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $categoryIds,
							'operator' => ((isset($setting['logic']) && $setting['logic'] == 'or') || sizeof($categoryIds) <= 1 ? 'IN' : 'AND'),
							'include_children' => (isset($setting['children']) && $setting['children'] == '1'),
						);
						break;
					case 'wpfTags':
						$tagsIdStr = $setting['settings'];
						if($tagsIdStr){
							$args["tax_query"][] = array(
								'taxonomy' => 'product_tag',
								'field'    => 'id',
								'terms'    => $tagsIdStr,
								'operator' => ((isset($setting['logic']) && $setting['logic'] == 'or') || sizeof($tagsIdStr) <= 1 ? 'IN' : 'AND'),
							);
						}
						break;
					case 'wpfAttribute':
						$attrIds = $setting['settings'];
						if($attrIds){
                            $taxonomy = '';
                            foreach ($attrIds as $attr) {
                                $term = get_term( $attr );
                                $taxonomy = $term->taxonomy;
                                break;
                            }
							$args["tax_query"][] = array(
								'taxonomy' => $taxonomy,
								'field'    => 'id',
								'terms'    => $attrIds,
								'operator' => (isset($setting['logic']) && $setting['logic'] == 'or' ? 'IN' : 'AND')
							);
						}
						break;
					case 'wpfAuthor':
						$authorId = $setting['settings'][0];
						if($authorId){
							$args['author'] = $authorId;
						}
						break;
					case 'wpfFeatured':
						$enable = $setting['settings'][0];
						if($enable === '1'){
							$args["tax_query"][] = array(
								'taxonomy' => 'product_visibility',
								'field'    => 'name',
								'terms'    => 'featured',
								'operator' => 'IN',
							);
						}
						break;
					case 'wpfOnSale':
						$enable = $setting['settings'][0];
						if($enable === '1'){
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
							$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
						}
						break;
					case 'wpfInStock':
						$stockstatus = $setting['settings'];
                        $temp['wpfInStock'] = true;
							switch ( $stockstatus ) {
								case 'instock':
									$metaQuery = array(
										'key' => '_stock_status',
										'value' => 'instock'
									);
									$args['meta_query'][] = $metaQuery;
									break;
								case 'outofstock':
									$metaQuery = array(
										'key' => '_stock_status',
										'value' => 'outofstock'
									);
									$args['meta_query'][] = $metaQuery;
									break;
								case 'onbackorder':
									$metaQuery = array(
										'key' => '_stock_status',
										'value' => 'onbackorder'
									);
									$args['meta_query'][] = $metaQuery;
									break;
								}
						break;
					case 'wpfRating':
						$ratingRange = $setting['settings'];
						if(is_array($ratingRange)){
							foreach($ratingRange as $range){
								$range = explode('-', $range);
								break;
							}
							if(intval($range[1]) !== 5){
								$range[1] = $range[1] - 0.001;
							}
							if($range[0] && $range[1]){
								$metaQuery = array(
									'key' => '_wc_average_rating',
									'value' => array($range[0], $range[1]),
									'type' => 'DECIMAL',
									'compare' => 'BETWEEN'
								);
								$args['meta_query'][] = $metaQuery;
							}
						}
						break;
                    case 'wpfBrand':
                        $brandsIdStr = $setting['settings'];
                        if($brandsIdStr){
                            $args["tax_query"][] = array(
                                'taxonomy' => 'product_brand',
                                'field'    => 'id',
                                'terms'    => $brandsIdStr,
                                'operator' => "AND"
                            );
                        }
                        break;
				}
			}
		}
		dispatcherWpf::doAction('addArgsForFilteringBySettings', $settings);

		if(isset($args["tax_query"]) && !empty($args["tax_query"])) {
			$args["tax_query"]['relation'] = 'AND';
		}
		if(isset($temp['wpfPrice'])) {
			$args['meta_query'][] = $this->getModule()->preparePriceFilter($temp['wpfPrice']['min_price'], $temp['wpfPrice']['max_price']);
		}
		/*if (!isset($temp['wpfInStock'])) {
            $args['meta_query'][] = array('key' => '_stock_status', 'value' => 'instock');
        }*/
		$filterSettings['sort_by_title'] = !empty( $filterSettings['sort_by_title'] ) ? $filterSettings['sort_by_title'] : false;
		if ( $filterSettings['sort_by_title'] ) {
			$args['orderby'] = !empty( $args['orderby'] ) ? $args['orderby'].' title' : 'title';
		}
        $args['order'] = !empty( $args['order'] ) ? $args['order'] : 'ASC';
        $args['orderby'] = !empty($args['orderby']) ? $args['orderby'] : 'menu_order';

        $args = $this->getModule()->addWooOptions($args);

		return $args;
	}



}
