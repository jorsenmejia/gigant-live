=== Rate Star Review - AJAX Reviews for Content, with Star Ratings ===
Contributors: videowhisper, VideoWhisper.com
Author: VideoWhisper.com
Author URI: https://videowhisper.com
Plugin Name: Rate Star Review
Plugin URI: https://videochat-scripts.com/star-rate-review-plugin-review-content-with-star-ratings/
Donate link: https://videowhisper.com/?p=Invest
Tags: rate, star, review, ajax, plugin, post, custom post, ratings
Requires at least: 2.7
Tested up to: 5.3
Stable tag: trunk

Enable site members to rate any type of content. Multiple ratings and reviews for content (including custom post types).

== Description ==

Enable site members to rate any type of content. Multiple ratings and reviews for content (including custom post types).

= Key Features = 
* Star Ratings, Review Title and Text Content
* AJAX review and lists (no page reload required)
* Unlimited review types associated by content type, content id, post id
* Update review (after adding review, it can be updated anytime with same form)
* Ratings by category (rate and also get stats by category)
* Shortcodes to add review, list reviews, display ratings
* Separately review multiple aspects and content type for an item
* Live update of review list on same page when adding, updating review
* Updates and can display average rating per post (meta)
* Custom maximum stars (ex: 3, 5, 10 stars)
* Configure post types to include reviews for (post, page)

= Recommended for use with these solutions =
* [Paid VideoChat](https://paidvideochat.com/ "Paid VideoChat - HTML5 Pay Per Minute Turnkey Site")
* [Broadcast Live Video](https://broadcastlivevideo.com/ "Broadcast Live Video - HTML5 Streaming Turnkey Site")
* [Video Share VOD](https://wordpress.org/plugins/video-share-vod/  "Video Share / Video On Demand Turnkey Site")
* [Picture Gallery](https://wordpress.org/plugins/picture-gallery/  "Picture Gallery – Frontend Image Uploads, AJAX Photo List") - Picture Gallery – Frontend Image Uploads, AJAX Photo List.


= Shortcodes =

[videowhisper_review post_id="" content_type="" content_id="" rating_max="5" id="" update_id=""]
Shows form to add and update review for specific post and content. AJAX based. Can also update reviews list if on same page.

[videowhisper_reviews post_id="" show_average="1" content_type="" content_id="" id=""]
Lists reviews for specific content (by post,content). At least post_id or content_id must be specified. AJAX based.

[videowhisper_rating post_id="" rating_max="5"]
Displays average rating for a post (average of all ratings for that post).

= Post Metas =

Updates these meta valuate when rating posts:
- rateStarReview_rating = average rating normalized as value between 0 and 1 (multiply with maximum to display)
- rateStarReview_ratingNumber = number of reviews
- rateStarReview_ratingPoints = sum of normalized ratings for easy sorting popular items (rating * ratingPoints)

Rating by category will update those for each rated category as:
- rateStarReview_rating_category$id
- rateStarReview_ratingNumber_category$id
- rateStarReview_ratingPoints_category$id

= How to use this? = 
In example, if you have a post presenting an electronic product and want site members to be able to review and rate separately different aspects like Features and Performance these can be content types.
A review form for each content type can be setup: 
[videowhisper_review content_type="Features" post_id="1"]
[videowhisper_review content_type="Performance" post_id="1"]
Then to show all reviews for that item, you can use [videowhisper_reviews post_id="1"] .

Another example, if an article is about a book with 2 parts, you can also use content_id to allow users to post a review for each part for each aspect (like Utility, Clarity).
[videowhisper_review content_type="Utility for Part" content_id="1" post_id="1"]
[videowhisper_review content_type="Utility for Part" content_id="2" post_id="1"]
[videowhisper_review content_type="Clarity for Part" content_id="1" post_id="1"]
[videowhisper_review content_type="Clarity for Part" content_id="2" post_id="1"]
Then list all reviews for all parts, [videowhisper_reviews post_id="1"] or just for an aspect or part.


== Screenshots ==
1. Review form and review list.

== Changelog ==

= 1.3 =
* Rate and display ratings by category (if enabled)
* Support for special characters

= 1.2 =
* Calculate and save average rating per post.

= 1.1.1 =
* First release.
