<?php
/**
 * The Template for displaying store.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp;

$wcfm_store_url    = get_option( 'wcfm_store_url', 'store' );
$wcfm_store_name   = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
if ( empty( $wcfm_store_name ) ) return;
$seller_info       = get_user_by( 'slug', $wcfm_store_name );
if( !$seller_info ) return;

$store_user        = wcfmmp_get_store( $seller_info->ID );
$store_info        = $store_user->get_shop_info();

$store_sidebar_pos = isset( $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_sidebar_pos'] : 'left';

$wcfm_store_wrapper_class = apply_filters( 'wcfm_store_wrapper_class', '' );

$wcfm_store_color_settings = get_option( 'wcfm_store_color_settings', array() );
$mob_wcfmmp_header_background_color = ( isset($wcfm_store_color_settings['header_background']) ) ? $wcfm_store_color_settings['header_background'] : '#3e3e3e';

get_header( 'shop' );
?>

<?php if( $WCFMmp->wcfmmp_vendor->is_store_sidebar() && ($store_sidebar_pos != 'left' ) ) { ?>
	<style>
		#wcfmmp-store .right_side{float:left !important;}
		#wcfmmp-store .left_sidebar{float:right !important;}
	</style>
<?php } ?>
<style>
@media screen and (max-width: 480px) {
	#wcfmmp-store .header_right {
		background: <?php echo $mob_wcfmmp_header_background_color; ?>;
	}
}

i.wcfmfa.fa-map-marker {
    display: none!important;
}

i.wcfmfa.fa-envelope {
    display: none!important;
}

.store_info_parallal {
    display: none!important;
}

/*#wcfmmp-store .address span a {*/
/*    vertical-align: top;*/
/*    display: none!Important;*/
/*}*/
/*#wcfmmp-store .logo_area a img {*/
/*    position: absolute;*/
/*    top: 50%;*/
/*    left: 50%;*/
/*    border-radius: 0%!important;*/
/*    transform: translate(-50%,-50%);*/
/*    -moz-transform: translate(-50%,-50%);*/
/*    -ms-transform: translate(-50%,-50%);*/
/*    -webkit-transform: translate(-50%,-50%);*/
/*    -o-transform: translate(-50%,-50%);*/
/*    width: 113%;*/
/*    height: 113%;*/
/*}*/
/*#wcfmmp-store .logo_area {*/
/*    width: 150px;*/
/*    height: 150px;*/
/*    position: absolute;*/

/*}*/
/*h1.wcfm_store_title {*/
/*    margin-right: 296px!important;*/
/*    margin-left: -130px!important;*/
/*    margin-top: 8px!Important;*/
/*}*/

/*span.address {*/
/*    margin-left: -418px!important;*/
/*    margin-top: 15px!Important;*/
/*    font-family: montserrat!important;*/
/*    color: white;*/
/*    margin-bottom: 12px!important;*/
/*}*/
/*#wcfm_store_header .wcfmmp-store-rating{*/
/*    overflow: hidden;*/
/*    position: relative;*/
/*    height: 1.618em;*/
/*    line-height: 1.618;*/
/*    font-size: 1em;*/
/*    width: 6em!important;*/
/*    font-family: 'Font Awesome 5 Free'!important;*/
/*    font-weight: 900;*/
/*    margin-left: -133px!important;*/
    /* margin-right: 53px!Important; */
/*    marin-top: 12px!important;*/
/*}*/

/*img.attachment-woocommerce_thumbnail.size-woocommerce_thumbnail {*/
/*    display: none!important;*/
/*}*/

/*.body_area {*/
/*    margin-left: 250px!important;*/
/*}*/
/*.wcfm-store-about {*/
/*    font-family: montserrat!important;*/
/*    color: black!important;*/
/*}*/

/*#wcfmmp-store del {*/
/*    color: red;*/
/*    display: none!important;*/
/*    font-size: 17px;*/
/*}*/

/*#wcfmmp-store .sidebar_heading h4, #wcfmmp-store .reviews_heading, #wcfmmp-store h2, #wcfmmp-store .user_name {*/
/*    color: #003399!important;*/
/*    font-family: montserrat!important;*/
/*    font-weight: 600;*/
/*}*/

/*.wcfmmp-store-rating {*/
/*    margin-top: -25px!important;*/
/*}*/

</style>

<?php //do_action( 'woocommerce_before_main_content' ); ?>
<?php echo '<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'; ?>
<?php do_action( 'wcfmmp_before_store', $store_user->data, $store_info ); ?>

<div id="wcfmmp-store" class="wcfmmp-single-store-holder <?php echo $wcfm_store_wrapper_class; ?>">
	<div id="wcfmmp-store-content" class="wcfmmp-store-page-wrap woocommerce" role="main">
			
		<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-banner.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) ); ?>
		
		<?php 
		if( apply_filters( 'wcfmmp_is_allow_legacy_header', false ) ) {
			$WCFMmp->template->get_template( 'store/legacy/wcfmmp-view-store-header.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
		} else {
			$WCFMmp->template->get_template( 'store/wcfmmp-view-store-header.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
		}
		?>

		<?php do_action( 'wcfmmp_after_store_header', $store_user->data, $store_info ); ?>
            
            <div class="body_area">
                <div class="w3-bar w3-black">
                    <button class="w3-bar-item w3-button" onclick="openCity('Profile')">Profile</button>
                    <button class="w3-bar-item w3-button" onclick="openCity('Listing')">Listing</button>
                    <button class="w3-bar-item w3-button" onclick="openCity('Review')">Reviews</button>
                </div>

            <div id="Profile" class="w3-container city">
                
                <?php
            /**
             * The Template for displaying all store description.
             *
             * @package WCfM Markeplace Views Description
             *
             * For edit coping this to yourtheme/wcfm/store 
             *
             */
    
            if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
            
            global $WCFM, $WCFMmp;
    
            $wcfm_shop_description = apply_filters( 'woocommerce_short_description', $store_user->get_shop_description() );
        ?>
            <div class="_area" id="wcfmmp_store_about">
            	<div class="wcfmmp-store-description">
            	 
            	  <?php do_action( 'wcfmmp_store_before_about', $store_user->get_id() ); ?>
            	
            		<?php if( $wcfm_shop_description ) { ?>
            			<div class="wcfm-store-about">
            				<?php echo $wcfm_shop_description; ?>
            			</div>
            		<?php } ?>
            		
            		<?php do_action( 'wcfmmp_store_after_about', $store_user->get_id() ); ?>
            	</div>
            </div>
                
            </div>


            <div id="Listing" class="w3-container city" style="display:none">
             
             <?php
            /**
             * The Template for displaying all store products.
             *
             * @package WCfM Markeplace Views Store/products
             *
             * For edit coping this to yourtheme/wcfm/store 
             *
             */

        if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
        
        global $WCFM, $WCFMmp, $avia_config;
        
        $counter = 0;
        
        wc_set_loop_prop( 'is_filtered', true );
        
        // Enfold Theme Compatibility
        if( $avia_config && is_array( $avia_config ) ) {
        	$avia_config['overview'] = true;
        }
        ?>
        
        <?php do_action( 'wcfmmp_store_before_products', $store_user->get_id() ); ?>
        
        <div class="" id="products">
        	<div class="product_area">
        	  <div id="products-wrapper" class="products-wrapper">
        	
        			<?php do_action( 'wcfmmp_before_store_product', $store_user->get_id(), $store_info ); ?>
        			
        			<?php if ( woocommerce_product_loop() ) { ?>
        				
        				<?php do_action( 'wcfmmp_woocommerce_before_shop_loop_before', $store_user->get_id(), $store_info ); ?>
        				<?php do_action( 'woocommerce_before_shop_loop' ); ?>
        				<?php do_action( 'wcfmmp_woocommerce_before_shop_loop_after', $store_user->get_id(), $store_info ); ?>
        				
        				<?php do_action( 'flatsome_category_title_alt'); // Flatsome Catalog support ?>
        				<?php do_action( 'wcfmmp_before_store_product_loop', $store_user->get_id(), $store_info ); ?>
        				
        				<?php woocommerce_product_loop_start(); ?>
        				
        					<?php if ( wc_get_loop_prop( 'total' ) ) { ?>
        						
        						<?php do_action( 'wcfmmp_after_store_product_loop_start', $store_user->get_id(), $store_info ); ?>
        						
        						<?php while ( have_posts() ) { the_post(); ?>
        							
        							<?php do_action( 'wcfmmp_store_product_loop_in_before', $store_user->get_id(), $store_info, $counter ); ?>
        							
        							<?php wc_get_template_part( 'content', 'product' ); ?>
        							
        							<?php do_action( 'wcfmmp_store_product_loop_in_after', $store_user->get_id(), $store_info, $counter ); ?>
        							
        							<?php $counter++; ?>
        			
        						<?php }  ?>
        						
        						<?php do_action( 'wcfmmp_before_store_product_loop_end', $store_user->get_id(), $store_info ); ?>
        						
        					<?php } ?>
        					
        				<?php if( function_exists( 'listify_php_compat_notice') ) { ?>
        					</div>
        				<?php } else { ?>
        					<?php woocommerce_product_loop_end(); ?>
        				<?php } ?>
        				
        				<?php do_action( 'wcfmmp_after_store_product_loop', $store_user->get_id(), $store_info ); ?>
        				
        				<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_before', $store_user->get_id(), $store_info ); ?>
        				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
        				<?php do_action( 'wcfmmp_woocommerce_after_shop_loop_after', $store_user->get_id(), $store_info ); ?>
        				
        				<?php //wcfmmp_content_nav( 'nav-below' ); ?>
        		
        			<?php } else { ?>
        				<?php do_action( 'woocommerce_no_products_found' ); ?>
        			<?php } ?>
        			
        			<?php do_action( 'wcfmmp_after_store_product', $store_user->get_id(), $store_info ); ?>
        			
        		</div><!-- .products-wrapper -->
        	</div><!-- #products -->
        </div><!-- .product_area -->
              
            </div>


            <div id="Review" class="w3-container city" style="display:none">
              <?php
/**
 * The Template for displaying all store reviews.
 *
 * @package WCfM Markeplace Views Store
 *
 * For edit coping this to yourtheme/wcfm/store 
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $post;

if( $post ) {
	$pagination_base = str_replace( $post->ID, '%#%', esc_url( get_pagenum_link( $post->ID ) ) );
} else {
	$pagination_base = str_replace( 1, '%#%', esc_url( get_pagenum_link( 1 ) ) );
}

$paged  = max( 1, get_query_var( 'paged' ) );
$length = 10;
$offset = ( $paged - 1 ) * $length;

$wcfm_review_categories = get_wcfm_marketplace_active_review_categories();

$total_review_count = $store_user->get_total_review_count();
$latest_reviews     = $store_user->get_lastest_reviews( $offset, $length );
?>

<div class="_area" id="reviews">

  <?php do_action( 'wcfmmp_store_before_reviews', $store_user->get_id() ); ?>
  
  <?php do_action( 'wcfmmp_store_before_new_review', $store_user->get_id() ); ?>

  <?php
  // New Review form
  if( apply_filters( 'wcfm_is_allow_new_review', true, $store_user->get_id() ) ) {
  	$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-new.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'wcfm_review_categories' => $wcfm_review_categories ) );
  }
  ?>
  
  <?php do_action( 'wcfmmp_store_after_new_review', $store_user->get_id() ); ?>
			
	<div class="reviews_area">
		<div class="reviews_heading"><?php _e( 'reviews', 'wc-multivendor-marketplace' ); ?></div>
		
		<div class="recent_reviews">
		
		  <?php do_action( 'wcfmmp_store_before_review_stat', $store_user->get_id() ); ?>
			
			<?php
			// Reviews latest stat
			$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-latest-stat.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'total_review_count' => $total_review_count, 'latest_reviews' => $latest_reviews ) );
			?>
			
			<?php do_action( 'wcfmmp_store_after_review_stat', $store_user->get_id() ); ?>
					
			<div class="bd_rating_sec">
			
				<?php do_action( 'wcfmmp_store_before_rating', $store_user->get_id() ); ?>
				
				<?php
				// Review category ratings
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-category-ratings.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'wcfm_review_categories' => $wcfm_review_categories ) );
				?>
				
				<?php
				// Review total rating
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-ratings.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
				?>
				
				<?php do_action( 'wcfmmp_store_after_rating', $store_user->get_id() ); ?>
				
				<div class="spacer"></div>    
			</div>
								
			<div class="bd_review_section">
			  <?php do_action( 'wcfmmp_store_before_latest_reviews', $store_user->get_id() ); ?>
			  
			  <?php
				// Reviews latest review
				$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-latest-review.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'total_review_count' => $total_review_count, 'latest_reviews' => $latest_reviews ) );
				
				if( $total_review_count > $length ) {
					$num_of_pages = ceil( $total_review_count / $length );

					$args = array(
							'paged'           => $paged,
							'pagination_base' => $pagination_base,
							'num_of_pages'    => $num_of_pages,
					);
					$WCFMmp->template->get_template( 'reviews/wcfmmp-view-reviews-pagination.php', $args );
				}
				?>
				
				<?php do_action( 'wcfmmp_store_after_latest_reviews', $store_user->get_id() ); ?>
			</div>
		</div>
	</div>
	
	<?php do_action( 'wcfmmp_store_after_reviews', $store_user->get_id() ); ?>
	
</div>
              
            </div>
            
            
                
			 
			<div class="spacer"></div>
    </div><!-- .body_area -->

    <div class="wcfm-clearfix"></div>
	</div><!-- .wcfmmp-store-page-wrap -->
	<div class="wcfm-clearfix"></div>
</div><!-- .wcfmmp-single-store-holder -->

<div class="wcfm-clearfix"></div>

<?php do_action( 'wcfmmp_after_store', $store_user->data, $store_info ); ?>
<?php //do_action( 'woocommerce_after_main_content' ); ?>
<?php echo '</main></div>'; ?>

<?php get_footer( 'shop' ); ?>

<script>
function openCity(cityName) {
  var i;
  var x = document.getElementsByClassName("city");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";  
  }
  document.getElementById(cityName).style.display = "block";  
}
</script>