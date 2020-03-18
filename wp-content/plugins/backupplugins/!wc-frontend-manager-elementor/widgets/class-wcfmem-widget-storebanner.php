<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Image;

class WCFM_Elementor_StoreBanner extends Widget_Image {

	use PositionControls;

	/**
	 * Widget name
	 *
	 * @return string
	 */
	public function get_name() {
			return 'wcfmem-store-banner';
	}

	/**
	 * Widget title
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Store Banner', 'wc-frontend-manager-elementor' );
	}

	/**
	 * Widget icon class
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-image-box';
	}

	/**
	 * Widget categories
	 *
	 * @return array
	 */
	public function get_categories() {
		return [ 'wcfmem-store-elements-single' ];
	}

	/**
	 * Widget keywords
	 *
	 * @return array
	 */
	public function get_keywords() {
		return [ 'wcfm', 'store', 'vendor', 'banner', 'picture', 'image', 'avatar' ];
	}

	/**
	 * Register widget controls
	 *
	 * @return void
	 */
	protected function _register_controls() {
		global $WCFM, $WCFMem;
		
		parent::_register_controls();

		$this->update_control(
				'section_image',
				[
						'label' => __( 'Banner', 'wc-frontend-manager-elementor' ),
				]
		);

		$this->update_control(
				'image',
				[
						'dynamic' => [
								'default' => $WCFMem->wcfmem_elementor()->dynamic_tags->tag_data_to_tag_text( null, 'wcfmem-store-banner' ),
						],
						'selectors' => [
								'{{WRAPPER}} > .elementor-widget-container > .elementor-image > img' => 'width: 100%;',
						]
				],
				[
						'recursive' => true,
				]
		);

		$this->update_control(
				'caption_source',
				[
						'type' => Controls_Manager::HIDDEN,
				]
		);

		$this->update_control(
				'caption',
				[
						'type' => Controls_Manager::HIDDEN,
				]
		);

		$this->update_control(
				'link_to',
				[
						'type' => Controls_Manager::HIDDEN,
				]
		);

		$this->add_position_controls();
	}

	/**
	 * Html wrapper class
	 *
	 * @return string
	 */
	protected function get_html_wrapper_class() {
		return parent::get_html_wrapper_class() . ' elementor-widget-' . parent::get_name();
	}
}
