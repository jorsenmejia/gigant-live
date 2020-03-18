=== YITH WooCommerce Advanced Reviews Premium ===

Contributors: yithemes
Tags: reviews, woocommerce, products, themes, yit, yith, e-commerce, shop, advanced reviews, reviews attachments, rating summary, product comment, review replies, advanced comments, product comments, vote review, vote comment, amazon, amazon style, amazon reviews, review report, review reports, most voted reviews, best reviews, rate review, rate product
Requires at least: 4.0.0
Tested up to: 4.9.x
Stable tag: 1.5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Documentation: https://docs.yithemes.com/yith-woocommerce-advanced-reviews/

== Changelog ==

= Version 1.5.6 - Released: Oct 22, 2018 =

* Update: plugin framework

= Version 1.5.5 - Released: Oct 17, 2018 =

* New: Support to WooCommerce 3.5.0
* Tweak: new action links and plugin row meta in admin manage plugins page
* Update: updating the .pot
* Update: Updating the German translation, thanks to Alexander Cekic.
* Fix: Avoid spam for user reviews who have not purchased the product
* Dev: added filter yith_ywar_add_filtering_link
* Dev: check about recapcha only on products
* Dev: added a new filter to the tab title
* Dev: added a new filter to check if is a custom user


= Version 1.5.4 - Released: Jun 28, 2018 =

* New: Added screen options to reviews screen
* Update: Spanish language
* Update: Italian translation
* Update: Dutch translation
* Updated: updated the official documentation url of the plugin
* Fix: mark comments created from a yith review as imported
* Dev: checking YITH_Privacy_Plugin_Abstract for old plugin-fw versions
* Dev: including 'wp-admin/includes/plugin.php'
* Dev: Improvement of the performance on product page


* GDPR:
  - New: exporting user review data info
  - New: erasing user review data info
  - New: privacy policy content
* New: recaptcha
* New: added Persian ( farsi ) translation to the plugin, thanks to BanuSara Group
* Tweak: SEO Improvements (added rel="nofollow" to several links)
* Tweak: add variable $review on filter yith_ywar_review_author_data

* Update: Dutch translation
* Update: Documentation link of the plugin
* Fix: Capatibility changed from manage_option to manage_woocommerce
* Fix: fixed visual issue in a string
* Fix: adding product item to aggregateRating
* Fix: recaptcha
* Dev: adding new filters

= Version 1.5.3 - Released: May 25, 2018 =

* GDPR:
  - New: exporting user review data info
  - New: erasing user review data info
  - New: privacy policy content
* New: recaptcha
* New: added Persian ( farsi ) translation to the plugin, thanks to BanuSara Group
* Tweak: SEO Improvements (added rel="nofollow" to several links)
* Tweak: add variable $review on filter yith_ywar_review_author_data
* Update: Italian translation
* Update: Dutch translation
* Update: Documentation link of the plugin
* Fix: Capatibility changed from manage_option to manage_woocommerce
* Fix: fixed visual issue in a string
* Fix: adding product item to aggregateRating
* Fix: recaptcha
* Dev: adding new filters

= Version 1.5.2 - Released: Feb 16, 2018 =

* Fix: Version in template ywar-rating.php
* Fix: Converting comments into reviews

= Version 1.5.1 - Released: Jan 30, 2018 =

* New: support to WooCommerce 3.3.0-RC2
* Update: plugin framework 3.0.10
* Fix: review form is showed to non-verified owners even if the related WooCommerce option is enabled
* Dev: new filter 'filter yith_ywar_review_author_data'


= Version 1.5.0 - Released: Jan 04, 2018 =

* New: dutch translation
* New: attachments of review images are now loading in a lightbox
* New: support to WooCommerce 3.2.6
* Fix: deny to load posts inside review section
* Update: plugin framework to the version 3.0.5
* Dev: new filter 'ywar_arg_for_reviews'
* Dev: new filter 'yith_wc_advance_reviews_ywar_script'


= Version 1.4.7 - Released: Nov 08, 2017 =

* Fix: remove testing strings

= Version 1.4.6 - Released: Nov 07, 2017 =

* New: support to WooCommerce 3.2.3
* Add: Option to show the Most Helpful Reviews tab selected by default
* Fix: Option "Number of reviews to display" did not work properly

= Version 1.4.5 - Released: Jul 20, 2017 =

* Fix: reinitialize PrettyPhoto on load more
* Update: YITH Plugin Framework

= Version 1.4.4 - Released: Jul 06, 2017 =

* New: support for WooCommerce 3.1.
* New: tested up to WordPress 4.8.
* Update: YITH Plugin Framework

= Version 1.4.3 - Released: Jun 27, 2017  =

* New: hide review bars if 'Product ratings' option is not set in WooCommerce settings page.
* Dev: updated template ywar-single-product-reviews.php.
* Dev: added template ywar-review-bars.

= Version 1.4.2 - Released: May 19, 2017  =

* Fix: privacy issue, removed user email from the Gravatar alt attribute

= Version 1.4.1 - Released: Mar 23, 2017  =

* New: Support WooCommerce 3.0
* Fix: review list corrupted if one product with reviews was deleted.
* Fix: YITH Plugin Framework initialization.

= Version 1.4.0 - Released: Mar 16, 2017  =

* New:  Support to WooCommerce 2.7.0-RC1
* Update: YITH Plugin Framework

= Version 1.3.17 - Released: Feb 13, 2017  =

* Fix: wrong average rating calculation when an approved review is deleted.
* Fix: review's replies disappear when the review is set as featured.
* Dev: filter 'yith_advanced_reviews_review_container_start' in template file ywar-product-reviews.php.
* Dev: filter 'yith_ywar_login_url' lets third party to set the login URL for guest users.

= Version 1.3.16 - Released: Jan 11, 2017 =

* Fix: with YITH WooCommerce Points and Rewards plugin, points for reviews not credited properly

= Version 1.3.15 - Released: Jan 03, 2017 =

* Fix: max file size for attachments not working properly
* Update: changed the position of the field 'Review title' for guest users

= Version 1.3.14 - Released: Dec 07, 2016 =

* New: ready for WordPress 4.7

= Version 1.3.13 - Released: Nov 07, 2016 =

* New: new option for the file max size allowed
* New: attachments of the same review are shown in a lightbox

= Version 1.3.12 - Released: Nov 02, 2016 =

* New: choose front end colors from plugin options

= Version 1.3.11 - Released: Oct 31, 2016 =

* New: a new option for entering the id or CSS class of the main review container
* New: all script and CSS belong to the optionable container and not to a fixed id
* New: in reviews table, show the content and a link to the parent review for ever reply

= Version 1.3.10 - Released: Aug 08, 2016 =

* Update: moved the set_approved_status method at the end of the review creation process in order to fire only when all data are stored
* New: trigger the 'wp_set_comment_status' action when the approval status of the review changes

= Version 1.3.9 - Released: Jun 13, 2016 =

* Update: WooCommerce 2.6 100% compatible

= Version 1.3.8 - Released: May 16, 2016 =

* New: italian localization file
* New: spanish localization file
* New: filter 'yith_advanced_reviews_avatar_email' let you change the displayed author email on ywar-review.php template file

= Version 1.3.7 - Released: Apr 22, 2016 =

* Fix: reviews do not shown author data edited from admin edit review page

= Version 1.3.6 - Released: Apr 06, 2016 =

* New: new section in edit review page for author information and management
* Fix: reviews table issue in a multilingual environment

= Version 1.3.5 - Released: Mar 18, 2016 =

* Update: removed color properties in CSS file so dark theme will not have visual issues
* Fix: missing argument caused a warning
* Update: changed capability for manage Reviews to 'manage_woocommerce'
* New: filter yith_ywar_manage_reviews_capability for let third party plugin to change the capability for managing the Reviews

= Version 1.3.4 - Released: Feb 29, 2016 =

* Fix: transient not deleted in all the needed cases give temporary wrong review values
* Fix: current object selected in wrong way in ywar-attachments.js
* Fix: string in ywar-attachments.js not localizable
* New: new action 'yith_ywar_product_reviews_updated' reporting an update for a product review

= Version 1.3.3 - Released: Jan 28, 2016 =

* Update: reviews are shown even if comments_open is false
* Fix: menu item shown twice

= Version 1.3.2 - Released: Jan 27, 2016 =

* Fix: admin edit link for reviews do not work

= Version 1.3.1 - Released: Jan 26, 2016 =

* Fix: conflict with YITH WooCommerce Gift Cards that prevents the sending of attachments
* Fix: the google rich snippet about the review date do not shows the review time

= Version 1.3.0 - Released: Jan 04, 2016 =

* Update: ready for WooCommerce 2.5
* New: action ywar_woocommerce_review_before_comment_text on review.php as replace for woocommerce_review_before_comment_text for WooCommerce 2.5+
* New: action ywar_woocommerce_review_after_comment_text on review.php as replace for woocommerce_review_after_comment_text for WooCommerce 2.5+

= Version 1.2.3 - Released: Dec 21, 2015 =

* Fix: author name shown twice on reviews table
* Fix: warning shown on WooCommerce reviews widget
* Update: improved reviews rating query performance

= Version 1.2.2 - Released: Nov 26, 2015 =

* Fix: review not submitted when "Ratings are required to leave a review" option is not set

= Version 1.2.1 - Released: Nov 16, 2015 =

* Update: YITH plugin framework loading starts on plugins_loaded instead of after_setup_theme
* New: review_label CSS class on ywar-product-reviews.php template file
* Fix: edit reviews fails
* Fix: user cannot edit if reply functionality was not set

= Version 1.2.0 - Released: Nov 05, 2015 =

* Update: YITH plugin framework
* Update: add CSS class "clearfix" on single review template
* New: optionally limit a verified customer from submitting multiple reviews for the same product
* New: user can edit a previous review
* Fix: don't show "Reply" button if the user has not permission to write a review
* Update: changes to ywar-product-reviews.php template for stopping multiple reviews from a single verified customer
* Update: in ywar-product-reviews.php template all elements are wrapped inside a div with id "ywar_reviews"
* New: author information on back end reviews table.

= Version 1.1.14 - Released: Oct 14, 2015 =

* Fix: name of the user not shown if the reviews is submitted by a guest not logged in.

= Version 1.1.13 - Released: Oct 07, 2015 =

* Update: text-domain changed to yith-woocommerce-advanced-reviews.

= Version 1.1.12 - Released: Sep 23, 2015 =

* New: improved query performance for low resources server.
* Fix: sometimes items was not shown clicking on a view on reviews back end page.

= Version 1.1.11 - Released: Sep 21, 2015 =

* New: admins can reply to review from site front end even if woocommerce setting - Only allow reviews from "verified owners" - is checked.
* Fix: replies from admins written from site front end are shown without moderation.
* New: check for empty content before trying to submit a review

= Version 1.1.10 - Released: Sep 03, 2015 =

* Fix: CSS issue on pages other than "Reviews" page.

= Version 1.1.9 - Released: Aug 28, 2015 =

* Fix: Review average rating was calculated including also unapproved reviews.

= Version 1.1.8 - Released: Aug 27, 2015 =

* Tweak: update YITH Plugin framework.

= Version 1.1.7 - Released: Jul 20, 2015 =

* Fix: blog comments conflict.

= Version 1.1.6 - Released: Jul 17, 2015 =

* Fix: wrong product average rating.

= Version 1.1.5 - Released: Jul 14, 2015 =

* Fix: review title not shown.

= Version 1.1.4 - Released: May 21 , 2015 =

* New: new filter before showing review content elements.

= Version 1.1.3 - Released: May 12 , 2015 =

* Fix: when the review author is unknown, it was shown admin user as content author.

= Version 1.1.2 - Released: May 11 , 2015 =

* New: Custom template are fully overwritable from theme files.

= Version 1.1.1 - Released: May 07 , 2015 =

* Fix: Call to undefined function session_status for previous PHP version.

= Version 1.1.0 - Released: May 06 , 2015 =

* New: advanced reviews custom post type.
* New: you can set the reviews as featured, in this way these will be showed before the others and highlighted compared to a normal review
* New: a report to view the statistics about the value of the reviews, with minimum, maximum and average rate.
* New: allow users to report inappropriate contents.
* New: hide temporarily a review if this receives a number of inappropriate reports higher than a customized value
* New: check the review status from a single page, choosing if a review is approved, featured, inappropriate, with blocked answers and so on.
* New: filter the reviews by status or update the status with bulk actions
* New: sort reviews by received rates, both positive and negative.

= Version 1.0.11 - Released: Apr 10, 2015 =

* Fix: some string was not correctly translated.

= Version 1.0.10 - Released: Apr 09, 2015 =

* New: new option let admin to manually approve reviews before they are shown on product page.

= Version 1.0.9 - Released: Mar 05, 2015 =

* New: support WPML
* Fix: Minor bugs
* Added : new option for allowing anonymous users to vote the reviews.
* New: admins can interact with reviews from product page on back-end.

= Version 1.0.8 - Released: Feb 26, 2015 =

* Fix: adding a rating to a reviews failed after a "reply to" attempt.

= Version 1.0.7 - Released: Feb 12, 2015 =

* New: Woocommerce 2.3 support
* Tweak: String translation

= Version 1.0.6 - Released: Feb 06, 2015 =

* Tweak: Buttons with WooCommerce style
* Fix: "Load more" button style strong appearance
* Tweak: Review summary overwritten by theme
* Fix: Css issues
* Fix: Duplicate load more button
* Fix: Submit form disappears after Reply to

= Version 1.0.5 - Released: Feb 04, 2015 =

* Tweak: Plugin core framework

= Version 1.0.4 - Released: Feb 02, 2015 =

* Fix: Minor bugs

= Version 1.0.3 - Released: Jan 30, 2015 =

* Tweak: Plugin core framework
* Tweak: Theme integration

= Version 1.0.0 - Released: Jan 07, 2015 =

* Initial release