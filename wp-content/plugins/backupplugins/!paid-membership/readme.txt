=== MicroPayments - Paid Membership, Content, Downloads ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: MicroPayments - Paid Membership, Content, Downloads
Plugin URI: http://videochat-scripts.com/paid-membership-plugin/
Donate link: https://videowhisper.com/?p=Invest
Tags: mycred, woocommerce, terawallet, videowhisper, tokens, credits, subscription, paypal, zombaio, bitcoin, sell post, pay per view
Requires at least: 2.7
Tested up to: 5.3
Stable tag: trunk

Micropayments enable users to do low value transactions from site wallet, without using a billing site each time and friction/fees involved with that. Setup paid membership (roles), custom content, downloads. Use tokens from MyCred and TeraWallet for WooCommerce. 

== Description ==

= Key Features =
* Control access to content (including pages, posts, customizable post types) by membership/role.
* Sell Membership: Users can obtain roles (membership) by purchase or subscription.
* Sell Content: Provides an edit content page in frontend for users to be able to sell individual post items (integrates automatically with VideoShareVOD plugin videos, Picture Gallery plugin pictures).
* Wallet (Tokens/Credits) Based: This plugin uses tokens from the myCred, WooWallet plugins as currency.
These tokens can be purchased using multiple payment gateways like Paypal, Skrill (Moneybookers) NETbilling, Zombaio, BitPay (bitcoin)  or earned with site activities, depending on setup.
* WooWallet integration (with WooCommerce billing options)
* Multi wallet support (MyCred + TeraWallet WooCommerce)
* Wallet user page with shortcode [videowhisper_my_wallet]
* Membership upgrade page with shortcode [videowhisper_membership_buy]
* Downloads management: Digital media downloads

= Downloads: Digital Media Management = 
* Enable file uploads from backend and frontend (with publisher access list)
* Restrict access by membership roles
* Sell downloads per item (with MyCred Sell Content addon)
* Restrict allowed extensions (server side)
* Obfuscated file name on server to prevent naming exploits


= Recommended for use with these solutions =
* [Paid VideoChat](https://paidvideochat.com/ "Paid VideoChat Script") - Pay Per Minute Videochat site solution.
* [Video Share VOD](https://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand Script") - Video Share / Video On Demand site solution.
* [Broadcast Live Video](https://broadcastlivevideo.com/ "Broadcast Live Video Camera Script") - Broadcast Live Video Channels site solution.
* [Picture Gallery](https://wordpress.org/plugins/picture-gallery/  "Picture Gallery – Frontend Image Uploads, AJAX Photo List") - Picture Gallery – Frontend Image Uploads, AJAX Photo List.

= Benefits of using tokens include: =
* less transaction fees (clients fund their account once for multiple purchases)
* cost control (clients can have added peace of mind and sensation of control for the fixed amount they pay),
* payment in advance (clients prepay for future services) ,
* increased sales (once the have the tokens they will put them to use faster than real money)


== Installation ==
* Install and activate plugin
* Setup membership packages from Paid Membership - Settings - Membership Levels
* Go to Paid Membership - Settings - Billing section and make sure mycred is installed, active and configured
* Use [videowhisper_membership_buy] shortcode to list packages in frontend to users
* Use [videowhisper_my_wallet] shortcode to show wallet with buy credits options

== Screenshots ==
1. Users can purchase membership with credits.
2. Control access to content by membership/role.


== Documentation ==
https://videochat-scripts.com/paid-membership-plugin/


== Demo ==
* See WordPress integration (after login):
https://videochat-scripts.com/buy-membership/


== Extra ==
More information, the latest updates, other plugins and non-WordPress editions can be found at https://videowhisper.com/ .


== Changelog ==

= 1.6 =
* Downloads manager
* Publishers can upload files
* Files access can be restricted by membership
* Pay per download

= 1.5 =
* TeraWallet/WooWallet integration (with WooCommerce billing options)
* Multi wallet support (MyCred + WooWallet)
* Wallet user page with shortcode [videowhisper_my_wallet]
* Semantic UI frontend interface integration

= 1.4 =
* Control access to content by membership / role, from backend contend editor
* Delete membership for user
* Protect administrator accounts from changing role with membership

= 1.3 =
* Edit content on a custom page /edit-content/?editID=[post id]
* Set paid content with mycred plugin
* Automatically detected and integrated for VideoShareVOD videos
* Only owner and administrator can edit

= 1.2 =
* Improvements

= 1.1 =
* Original release