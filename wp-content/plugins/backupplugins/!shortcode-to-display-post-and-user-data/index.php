<?php

/*
  Plugin Name: Shortcode to display post and user data
  Description: Display post and user data on the frontend using a shortcode.
  Version: 1.1.1
  Author: WP Sheet Editor
  Author URI: https://wpsheeteditor.com/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=posts
  Plugin URI: https://wpsheeteditor.com/extensions/posts-pages-post-types-spreadsheet/?utm_source=wp-admin&utm_medium=plugins-list&utm_campaign=posts
  Author Email: josevega@wpsheeteditor.com
  License:

  Copyright 2011 JoseVega (josevega@vegacorp.me)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
require 'vendor/vg-plugin-sdk/index.php';
$vgds_plugin_sdk = new VG_Freemium_Plugin_SDK(array(
	'main_plugin_file' => __FILE__,
	'show_welcome_page' => true,
	'welcome_page_file' => __DIR__ . '/views/welcome-page-content.php',
	'plugin_name' => 'Shortcode to display post and user data',
	'plugin_prefix' => 'wpdspu_',
	'plugin_version' => '1.1.1',
		));
add_shortcode('vg_display_data', 'vg_display_object_data_shortcode');

function vg_display_object_data_shortcode($atts = array(), $content = '') {
	extract(wp_parse_args($atts, array(
		'object_id' => 'current', // current = current post id, query string key if object_id_type=query_string, function name if object_id_type=callable, key:value if object_id_type=find
		'object_id_type' => '', // query_string , callable , find
		'data_source' => 'post_meta', // post_data, post_meta, user_data, user_meta, post_terms
		'key' => '', // field key
		'template' => '{{var}}',
		'default' => '', // default value
		'joiner' => ' ', // if value is array, join using this
		'flag' => '', // file_url || image tag
		'wp_filter' => '', // i.e. the_content to replace shortcodes in the value
		'sanitization' => 'yes', // sanitize value before output. It runs wp_kses_post.
	)));

	if ($object_id_type === 'query_string' && !empty($object_id)) {
		$object_id = (isset($_GET[$object_id])) ? (int) $_GET[$object_id] : false;
	}
	if ($object_id_type === 'callable' && !empty($object_id)) {
		$object_id = (int) call_user_func($object_id);
	}

	if ($object_id === 'current') {
		if (strpos($data_source, 'post') !== false) {
			global $post;
			$object_id = $post->ID;
		} else {
			$object_id = get_current_user_id();
		}
	}

	if ($object_id_type === 'find' && !empty($object_id)) {
		$object_id_parts = explode(':', $object_id);

		if (count($object_id_parts) == 2) {
			if ($data_source === 'post_meta' || $data_source === 'post_data' || $data_source === 'post_terms') {
				$matching_items = new WP_Query(array(
					'meta_key' => $object_id_parts[0],
					'meta_value' => $object_id_parts[1],
					'fields' => 'ids',
					'posts_per_page' => 1,
					'post_type' => 'any',
				));

				if ($matching_items->have_posts()) {
					$object_id = current($matching_items->posts);
				}
			} elseif ($data_source === 'user_meta' || $data_source === 'user_data') {

				$matching_items = get_users(array(
					'meta_key' => $object_id_parts[0],
					'meta_value' => $object_id_parts[1],
					'fields' => 'ids',
					'number' => 1,
				));

				if (!empty($matching_items)) {
					$object_id = current($matching_items);
				}
			}
		}
	}

	$out = '';

	if (!$object_id || !$key) {
		return $out;
	}

	if (strpos($key, ',') !== false) {
		$keys = explode(',', $key);
		$data = array();

		foreach ($keys as $single_key) {
			$data[] = do_shortcode('[vg_display_data object_id="' . $object_id . '" data_source="' . $data_source . '" key="' . $single_key . '"]');
		}

		$out = implode($joiner, array_filter($data));
	} else {

		if ($data_source === 'post_data') {
			$object = get_post((int) $object_id);
		} elseif ($data_source === 'user_data') {
			$object = get_user_by('ID', (int) $object_id);
		} elseif ($data_source === 'post_meta') {
			$object = get_post_meta((int) $object_id, $key, true);
		} elseif ($data_source === 'user_meta') {
			$object = get_user_meta((int) $object_id, $key, true);
		} elseif ($data_source === 'post_terms') {
			$terms = wp_get_object_terms($object_id, $key, array(
				'fields' => 'names',
			));

			if (!is_wp_error($terms)) {
				$object = implode($joiner, $terms);
			}
		}


		if (!empty($object)) {
			if ($data_source === 'post_data' && isset($object->$key)) {
				$out = $object->$key;
			} elseif ($data_source === 'user_data' && isset($object->data->$key)) {
				$out = $object->data->$key;
			} elseif (!is_object($object)) {
				$out = $object;
			}
		}
	}

	if (empty($out) && !empty($default)) {
		$out = $default;
	}

	if ($flag && !empty($out)) {

		if ($flag === 'file_url' && is_numeric($out)) {
			$source = wp_get_attachment_url($out);
			if (!empty($source)) {
				$out = $source;
			}
		} elseif ($flag === 'image_tag' && is_numeric($out)) {
			$out = wp_get_attachment_image($out, 'full');
			$sanitization = false;
		} elseif ($flag === 'term_name') {
			$term_names = array();

			if (is_string($out) && strpos($out, ',') !== false) {
				$out = explode(',', $out);
			}
			if (is_array($out)) {
				foreach ($out as $term_id) {
					$term = get_term_by('id', $term_id, $key);
					$term_names[] = $term->name;
				}
				$out = implode(', ', array_filter($term_names));
			} else {
				$term = get_term_by('id', $out, $key);
				$out = $term->name;
			}
		}
	}

	if ($wp_filter && !empty($out)) {
		$out = apply_filters($wp_filter, $out, $object_id, $data_source);
	}
	if (!empty($template) && !empty($out)) {
		$out = str_replace('{{var}}', $out, $template);
	}

	if (!empty($sanitization) && $sanitization === 'yes') {
		$out = wp_kses_post($out);
	}

	return $out;
}
