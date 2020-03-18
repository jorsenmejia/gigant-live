<?php
class woofilters_widgetViewWpf extends viewWpf {
    public function displayWidget($instance) {
		if(isset($instance['id']) && $instance['id']) {
//			if(is_product_category()){
//				$catObj = get_queried_object();
//				echo do_shortcode('['.WPF_SHORTCODE.' id='.$instance['id'].' prodcategory='.$catObj->term_id.']');
//			}
//			if(is_shop()){
//				echo do_shortcode('['.WPF_SHORTCODE.' id='.$instance['id'].']');
//			}
			if ( is_shop() || is_product_category() || is_product_tag() || is_customize_preview() ) {
				echo do_shortcode('['.WPF_SHORTCODE.' id='.$instance['id'].']');
			}
		}
    }
    public function displayForm($data, $widget) {
		frameWpf::_()->addStyle('woofilters_widget', $this->getModule()->getModPath(). 'css/gmap_widget.css');
		$filters = frameWpf::_()->getModule('woofilters')->getModel()->getFromTbl();
		$filtersOpts = array();
		if(empty($filters)) {
			$filtersOpts[0] = __('You have no filters', WPF_LANG_CODE);
		} else {
			$filtersOpts[0] = 'Select';
			foreach($filters as $filter) {
				$filtersOpts[ $filter['id'] ] = $filter['title'];
			}
		}
		$this->assign('filtersOpts', $filtersOpts);
        $this->displayWidgetForm($data, $widget);
    }
}
