<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$general_options = array(

	'general' => array(

		'section_general_settings'          => array(
			'name' => __( 'General settings', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
			'id'   => 'ywar_section_general',
		),
		'review_settings_enable_title'      => array(
			'name'    => __( 'Show title', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'checkbox',
			'desc'    => __( 'Add a title field in the reviews.', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_enable_review_title',
			'default' => 'yes',
		),
		'review_settings_enable_attachment' => array(
			'name'    => __( 'Show attachments', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'checkbox',
			'desc'    => __( 'Add an attachment section in the reviews.', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_enable_attachments',
			'default' => 'yes',
		),
		'ywar_attachment_type'              => array(
			'name'    => __( 'Attachment type', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'text',
			'desc'    => __( 'Set the file type allowed as the review attachment', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_attachment_type',
			'default' => 'jpg,png',
		),

		'review_settings_attachment_limit' => array(
			'name'    => __( 'Multiple attachment limit', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'number',
			'desc'    => __( 'Set the maximum number of attachments that can be selected (0 = no limit).', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_max_attachments',
			'default' => '0',
		),
		'ywar_attachment_max_size'         => array(
			'name'             => __( 'Attachment max size', 'yith-woocommerce-advanced-reviews' ),
			'type'             => 'number',
			'desc'             => __( 'Set the file max size allowed in MB. Set to 0 for no size limit.', 'yith-woocommerce-advanced-reviews' ),
			'id'               => 'ywar_attachment_max_size',
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 1,
			),
			'default'          => 0,
		),

		'ywar_tab_selector'            => array(
			'name'    => __( 'Review content selector', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'text',
			'desc'    => __( 'Set the id or CSS class that matches the review tab content (default: #tab-reviews).', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_tab_selector',
			'default' => '#tab-reviews',
		),

		'ywar_enable_recaptcha'             => array (
			'name'    => __ ( 'reCaptcha', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'checkbox',
			'desc'    => __ ( 'Enable reCaptcha on review submitting', 'yith-woocommerce-advanced-reviews' ),
			'id'      => 'ywar_enable_recaptcha',
			'default' => 'no',
		),
		'ywar_recaptcha_site_key'           => array (
			'name' => __ ( 'reCaptcha site key', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'text',
			'desc' => __ ( 'Insert your reCaptcha site key', 'yith-woocommerce-advanced-reviews' ),
			'id'   => 'ywar_recaptcha_site_key',
			'css'  => 'min-width:50%;',

		),
		'ywar_recaptcha_secret_key'         => array (
			'name' => __ ( 'reCaptcha secret key', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'text',
			'desc' => __ ( 'Insert your reCaptcha secret key', 'yith-woocommerce-advanced-reviews' ),
			'id'   => 'ywar_recaptcha_secret_key',
			'css'  => 'min-width:50%;',
		),
        'ywar_recaptcha_message_error'         => array (
            'name' => __ ( 'reCaptcha message error', 'yith-woocommerce-advanced-reviews' ),
            'type' => 'textarea',
            'desc' => __ ( 'Insert your message error when the review is submitted without recaptcha', 'yith-woocommerce-advanced-reviews' ),
            'id'   => 'ywar_recaptcha_message_error',
            'css'  => 'min-width:50%;',
            'default'   =>  'You have entered an incorrect reCAPTCHA value.<br> Click the BACK button on your browser and try again.'
        ),
		'review_settings_import'       => array(
			'name'    => __( 'Previous reviews', 'yith-woocommerce-advanced-reviews' ),
			'type'    => 'ywar_import_previous_reviews',
			'id'      => 'ywar_import_review',
			'default' => 'yes',
		),
		'section_general_settings_end' => array(
			'type' => 'sectionend',
			'id'   => 'ywar_section_general_end',
		),
	),
);

return $general_options;
