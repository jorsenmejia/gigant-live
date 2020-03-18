=== Display custom fields in the frontend - Post and User Profile Fields ===
Contributors: vegacorp,josevega
Tags: wp page templates, wordpress templates, acf, custom pages, custom fields
Tested up to: 5.2
Stable tag: 1.1.1
Requires at least: 4.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display post and user custom fields data anywhere on the frontend using a shortcode, including advanced custom fields (ACF) fields.

== Description ==

Display post and user custom fields data anywhere on the frontend using a shortcode, including advanced custom fields (ACF) fields.

- Do you want to display information from a user profile on the frontend?

- Do you want to display custom fields from a post on the frontend?

- Have you created metaboxes with Advanced Custom Fields and you want to display those fields?

Use this plugin to display any field for a post or user profile on the frontend.

= Basic examples =

* **Display email of the current user**: `[vg_display_data key="user_email" data_source="user_data"]`

* **Display first name of the current user:** `[vg_display_data key="first_name" data_source="user_meta"]`

* **Display full name of the current user:** `[vg_display_data key="first_name,last_name" data_source="user_meta"]`

* **Display the title of the current post:** `[vg_display_data key="post_title" data_source="post_data"]`

* **Display the excerpt of the current post:** `[vg_display_data key="post_excerpt" data_source="post_data"]`

* **Display the categories of the post ID = 20:** `[vg_display_data object_id="20" key="category" data_source="post_terms" template="<b>Categories:</b> {{var}}" joiner=", "]`

* **Get featured image url:** `[vg_display_data key="_thumbnail_id" template="<b>Image url:</b> {{var}}" flag="file_url"]`

* **Get featured image as `<img>` tag.:** `[vg_display_data key="_thumbnail_id" template="<b>Image:</b> {{var}}" flag="image_tag"]`

					

																																																		 

= Advanced examples =

* **Display the title for the post ID from the URL containing the parameter ?post_id=ANY_NUMBER:** `[vg_display_data object_id_type="query_string" object_id="post_id" key="post_title" data_source="post_data"]`

* **Get post or user ID from php function:** `[vg_display_data object_id_type="callable" object_id="function_name" key="post_title" data_source="post_data"]`

									   

																																							  

																					 

* **Get email of the current user with phone number = 1234 (meta_key=phone AND meta_value=1234):** `[vg_display_data object_id_type="find" object_id="phone:1234" key="user_email" data_source="user_data"]`


= Parameters =

- `object_id` = Post ID. Leave empty to use the current post. Possible values: (empty), current, number, query string key if object_id_type=query_string, function name if object_id_type=callable, meta_key:meta_value if object_id_type=find

- `object_id_type` = Leave empty if object_id is empty, or current, or is a number. Possible values: query_string , callable , find

- `data_source` = What database table to use to find the data. Default = post_meta. Possible values: post_data, post_meta, user_data, user_meta, post_terms.

- `key` = Field key. It accepts one or multiple keys separated by commas. For example, to display full name = first_name,last_name. Required.

- `template` = HTML fragment to use to display the field, if the field is empty the html is not displayed. Optional.

- `default` = Default value to use if the field is empty. Optional.

- `joiner` = If the field has multiple values, it will join the values with this string. Default " " (one space). Optional.

- `flag` = Use only if the field contains a file ID to conver the ID to URL or image tag. Default values: file_url , image_tag. Optional.

- `wp_filter` = Whether to run a wp filter on the field value before display. For example, "wpautop" to turn line breaks into paragraphs. Optional, only for advanced users.

- `sanitization` = Where to allow the display of any html. By default it allows only safe tags, it uses wp_kses_post. Possible values: yes , no. Default: yes. Optional.


== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "Shortcode to display post and user data! and click Search Plugins. Once you’ve found our plugin you can install it by simply clicking “Install Now”.



= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here.](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation)





== Frequently Asked Questions ==


== Changelog ==

1.1.1
- Fixed fatal error

1.1.0
- Added flag term_name to display term ID as friendly name
- Added welcome page after install

1.0.1
- Fix. Allow to use object_id=current for users
- Small tweaks

1.0.0

- Initial release.