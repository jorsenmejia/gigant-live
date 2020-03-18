﻿=== YITH WooCommerce Points and Rewards  ===

Contributors: yith
Tags: points, rewards, Points and Rewards, point, woocommerce, yith, point collection, reward, awards, credits, multisite, advertising, affiliate, beans, coupon, credit, Customers, discount, e-commerce, ecommerce, engage, free, incentive, incentivize, loyalty, loyalty program, marketing, promoting, referring, retention, woocommerce, woocommerce extension, WooCommerce Plugin
Requires at least: 3.5.1
Tested up to: 4.9.6
Stable tag: 1.5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YITH WooCommerce Points and Rewards allows you to add a rewarding program to your site and encourage your customers collecting points.

== Description ==

Have you ever started collecting shopping points? What was your reaction? Most of us are really motivated in storing as many points as possible, because so we can get more and often we do not care about spending more because, if we do, we can have a better reward. Hasn't this happened to you too? That's what you get by putting into your site a point and reward programme: loyalising your customers, encouraging them to buy always from your shop and being rewarded for their loyalty.
If you think that reward programmes were only prerogative of big shopping centres or supermarkets, you're have to change your mind, because now you can add such a programme to your own e-commerce shop too. How? Simple, with YITH WooCommerce Points and Rewards: easy to setup and easy to understand for your customers!


== Installation ==
Important: First of all, you have to download and activate WooCommerce plugin, which is mandatory for YITH WooCommerce Points and Rewards to be working.

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH WooCommerce Points and Rewards` from Plugins page.


== Changelog ==

= Version 1.5.4 - Released: Aug 22, 2018 =
New: Support to WordPress 4.9.8
Dev: New filter 'ywpar_discount_applied_message'
Dev: New filter 'ywpar_approx_function'
Dev: New filter ywpar_update_wp_cache and force update user meta replacing the wp user cache
Update: Plugin Core 3.0.21
Update: Language files
Fix: Issue with rewards coupons with WooCommerce Multilingual
Fix: Issue with PHP 7.2
Fix: Fixed non-numeric value on adding points to user for first time when $pointsvar is empty
Fix: Counter on repeating rules for extra points
Fix: Added missing string for WPML

= Version 1.5.3 - Released: May 17, 2018 =
New: Support to WordPress 4.9.6 RC2
New: Support to WooCommerce 3.4.0 RC1
New: Integration with WooCommerce Currency Switcher version 1.2.4
Dev: New filter 'ywpar_points_earned_in_category', 'ywpar_coupon_label to change coupon label', 'ywpar_before_currency_loop'
Dev: New filters 'ywpar_get_point_earned_price', 'ywpar_before_rewards_message','ywpar_calculate_rewards_discount_max_discount_fixed'
Update: Plugin Core 3.0.15
Update: Language files
Fix: Amount redeemed when order cancelled or failed
Fix: Get back redeemed points when an order is cancelled
Fix: Percentual symbol Reward Percentual Conversion Rate
Fix: Free shipping on redemption
Fix: Empty value warning

= Version 1.5.2 - Released: Mar 13, 2018 =
Update: Language Files
Fix: option 'Hide points message for guest' wasn't working correctly

= Version 1.5.1 - Released: Feb 28, 2018 =
Tweak: Conversion update
Fix: Dashboard Widget 

= Version 1.5.0 - Released: Feb 23, 2018 =
New: My Account Page Endpoint
New: Integration with WooCommerce Multilingual from version 4.2.9
New: Integration with Aelia Currency Switcher for WooCommerce from version 4.5.14
Update: Plugin Core 3.0.12
Update: Language Files
Fix: Earnings points message doesn't update in checkout page after a points coupons applied
Fix: Coupon Rewards Points issue
Fix: Wrong processing status slug

= Version 1.4.3 - Released: Feb 04, 2018 =
Update: Plugin Core 3.0.11
Fix: Minimized javascript file on administrator panel
Fix: Load scripts only on settings panel

= Version 1.4.2 - Released: Jan 29, 2018 =
New: Support to WooCommerce 3.3 RC2
Update: Plugin Core 3.0.10
Dev: New filter 'ywpar_calculate_rewards_discount_max_discount'
Dev: New filter 'ywpar_calculate_rewards_discount_max_points'
Fix: Dutch support
Fix: Calculation Worth price
Fix: Points redeeming issue

= Version 1.4.1 - Released: Dec 21, 2017 =
Update: Plugin Core 3.0.1
Dev: Added filter 'ywpar_change_coupon_type_discount'
Fix: Subtotal calculation
Fix: Calculation percentual discount

= Version 1.4.0 - Released: Dec 11, 2017 =
Update: Plugin Core 3.0
Fix: Points earned for order
Fix: Points not displayed if a variation has 0
Fix: Calculation discount in reward points percentual
Fix: Rewards points calculation


= Version 1.3.1 - Released: Aug 17, 2017 =
New: Support to WooCommerce 3.2.0 RC2
New: Dutch support
Dev: Added 'ywpar_rewards_conversion_rate'
Update: Core Framework
Fix: Shortcode point list
Fix: Double points issue when an order pass from cancelled to completed status
Fix: Fix max discount amount in percentage redeem points

= Version 1.3.0 - Released: Aug 17, 2017 =
New: Support to WooCommerce 3.1.2
New: Option to choose how use WooCommerce Coupons and Rewards Points
New: Export points
New: German support by Alexander Cekic
Fix: Rewrite expiration system
Fix: Show/Hide Messages to Guest
Fix: Variable products points calculation
Fix: Product points calculation
Dev: New filter 'yith_par_messages_class' to customize woocommerce messages class
Dev: New filter 'ywpar_hide_messages' to customize show/hide messages
Dev: New filter 'ywpar_previous_orders_statuses' to add custom order statuses for previous order points redeem


= Version 1.2.7 - Released: May 26, 2017 =
New: Export user/points from database
Fix: Show message in cart to reedeem points

= Version 1.2.6 - Released: May 26, 2017 =
Fix: Method to calculate price worth

= Version 1.2.5 - Released: May 25, 2017 =
New: Support to WooCommerce 3.0.7
Dev: moved filter ywpar_set_max_discount_for_minor_subtotal
Dev: added filter ywpar_set_percentage_cart_subtotal
Dev: added wrapper for my-account elements
Fix: Coupons to Redeem points
Fix: Fix previuos orders price
Fix: Removed earning points in YITH Multivendor Suborders when vendor's orders are synchronized
Fix: Message in single product page for variable products


= Version 1.2.4 - Released: May 05, 2017 =
New: Support to WooCommerce 3.0.5
New: Added option to reassign redeemed points for total refund
Fix: Import points from previous orders
Fix: Readded options to enable point removal for total or partial refund
Fix: Shop Manager capabilities


= Version 1.2.3 - Released: Apr 28, 2017 =
New: Support to WooCommerce 3.0.4
Fix: Filter of customer in Customer Points tab
Update: Core Framework

= Version 1.2.2 - Released: Apr 12, 2017 =
New: Support to WooCommerce 3.0.1
Fix: Error with coupons
Fix: Remove points redeemed
Update: Core Framework

= Version 1.2.1 - Released: Apr 04, 2017 =
New: Support to WooCommerce 3.0
Tweak: Changed registration date with local registration date
Dev: Added filter 'ywpar_points_registration_date'
Fix: Error with php 5.4
Update: Core Framework

= Version 1.2.0 - Released: Mar 16, 2017 =
New: Support to WooCommerce 3.0 RC 1
New: Compatibility with AutomateWoo - Referrals Add-on 1.3.5
New: Spanish translation
Tweak: Refresh of messages after cart updates
Fix: Update messages on the cart page
Update: Core Framework


= Version 1.1.4  - Released: Jan 25, 2017 =
Fix: Calculation points when the category overrides the global conversion
Fix: Calculation price discount in fixed conversion value
Dev: Changed the style class 'product_point' with 'product_point_loop'
Dev: Added method 'calculate_price_worth' in class YITH_WC_Points_Rewards_Redemption
Dev: Added method 'get_price_from_point_earned' in class YITH_WC_Points_Rewards_Earning

= Version 1.1.3  - Released: Dec 21, 2016 =
Added: Option to enable shop manager to edit points
Added: A placeholder {price_discount_fixed_conversion} for message in single product page
Added: An option to change the label of button "Apply Discount"
Added: An option to select the rules that earning the points
Added: An option to select the rules that redeem the points
Added: An option to show points in loop
Added: Message to show points earned in order pay
Added: A filter 'ywpar_enabled_user' to enable or disable user
Added: An option to choose if free shipping allowed to redeem
Tweak: Compatibility with YITH WooCommerce Email Template
Tweak: Calculation points on older orders if product doesn't exists
Fixed: Overriding of points earned in variations
Fixed: Removed earning points in YITH Multivendor Suborders
Fixed: Update points to redeem when the cart is updated
Fixed: Email expiring content
Fixed: Earning point message on cart if a totally discount coupon is applied

= Version 1.1.2  - Released: Mar 24, 2016 =
Added: The return of points redeemed to the cancellation of the order
Added: Options on products and categories to override the rewards conversion discounts
Fixed: Javascript error in frontend.js
Tweak: Improvement Product Points calculation changed floor by round

= Version 1.1.1  - Released: Mar 14, 2016 =
Added: Button to reset points
Added: Change points values when variation select change
Tweak: Improvement Product Points calculation
Udated: Label of options in administrator panel

= Version 1.1.0 - Released: Mar 08, 2016 =
Fixed: Calculation earned points is a Dynamic Pricing and Discount rule is applied
Fixed: Moved ob_start() function in update send_email_update_points() method
Fixed: Update merge of default options with options from free version
Updated: Plugin Framework

= Version 1.0.9 - Released: Feb 29, 2016 =
Added: Option to redeem points with percentual discount
Added: Option to remove the possibility to redeem points
Added: Option to add a minimum amount discount to redeem points

= Version 1.0.8 - Released: Feb 11, 2016 =
Added: filter ywpar_get_product_point_earned that let third party plugin to set the point earned by specific product

= 1.0.7 - Released: Feb 05, 2016 =
Added: Shortcode yith_ywpar_points_list to show the list of points of a user
Added: Option to hide points in my account page
Fixed: Pagination on Customer's Points list

= 1.0.6 - Released: Feb 01, 2016 =
Fixed: Calculation points when coupons are used

= 1.0.5 - Released: Jan 26, 2016 =
Added: Option to remove points when coupons are used
Added: Earning Points in a manual order
Added: In Customer's Points tab all customers are showed also without points
Added: Compatibility with YITH WooCommerce Multi Vendor Premium hidden the points settings on products for vendors
Fixed: Removed Fatal in View Points if the order do not exists
Fixed: Conflict js with YITH Dynamic Pricing and Discounts
Fixed: Refund points calculation for partial refund
Fixed: Extra points double calculation

= 1.0.4 - Released: Jan 07, 2016 =
Added: Compatibility with WooCommerce 2.5 RC1
Fixed: Redeem points also if the button "Apply discount" is not clicked
Fixed: Calculation points on a refund order
Fixed: Update Points content

= 1.0.3 - Released: Dec 14, 2015 =
Added: Compatibility with Wordpress 4.4
Fixed: Extra points options
Fixed: Reviews assigment points for customers
Fixed: String translations
Updated: Changed Text Domain from 'ywpar' to 'yith-woocommerce-points-and-rewards'
Updated: Plugin Framework

= 1.0.2 - Released: Nov 30, 2015 =
Fixed: Enable/Disable Option
Fixed: Double points assigment
Update: Plugin Framework


= 1.0.1 - Released: Sept 23, 2015 =
Added: Minimun amount to reedem
Added: Italian Translation

= 1.0.0 - Released: Sept 17, 2015 =
Initial release