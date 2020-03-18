<?php
class woofilters_widgetWpf extends moduleWpf {
	public function init() {
        parent::init();
        add_action('widgets_init', array($this, 'registerWidget'));
    }
    public function registerWidget() {
        return register_widget('wpfWoofiltersWidget');
    }
}
/**
 * Maps widget class
 */
class wpfWoofiltersWidget extends WP_Widget {
    public function __construct() {
        $widgetOps = array(
            'classname' => 'wpfWoofiltersWidget',
            'description' => __('Displays Filters', WPF_LANG_CODE)
        );
		parent::__construct( 'wpfWoofiltersWidget', WPF_WP_PLUGIN_NAME, $widgetOps );
    }
    public function widget($args, $instance) {
		extract($args);
	   	extract($instance);
		frameWpf::_()->getModule('woofilters_widget')->getView()->displayWidget($instance);
    }
    public function form($instance) {
		extract($instance);
		frameWpf::_()->getModule('woofilters_widget')->getView()->displayForm($instance, $this);
    }
	public function update($new_instance, $old_instance) {
		//frameGmp::_()->getModule('promo')->getModel()->saveUsageStat('map.widget.update');
		return $new_instance;
	}
}
