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

/**/
/*PRO IMAGE*/
img.attachment-woocommerce_single.size-woocommerce_single {
    height: 185px!Important;
    width: 185px!Important;
    border:white solid 10px;
    background-color:white;
    border-radius: 50%!important;
}
span.elementor-icon-list-icon {
    display: none!important;
}
.tablist .elementor-icon-list-item:nth-child(-2 + 3){
    color: red !important;
}
/*start of listings*/
h2.woocommerce-loop-product__title {
    display: none;
}
span.price {
    display: none !important;
}
a.button.product_type_appointment.add_to_cart_button {
    display: none;
}
a.woocommerce-review-link {
    display: none;
}
p.reviewstar{
    visibility: visible;
}
h2.woocommerce-loop-product__title {
    display: none;
}
.grid-profilelist {
  display: grid;
  grid-template-columns: 70% 30%;
  background-color: #white;
}
.grid-content-profilelist:nth-child(1) {
  text-align: center;
      font-size: 19px;
    text-align: left;
    color: black;
    text-decoration: underline;
    font-weight: 600;
}
.grid-content-profilelist:nth-child(2) {padding-top: 22px;
}
.grid-content-profilelist .results, .grid-content-profilelist p, .grid-content-profilelist #rso{
    display: inline-block;
   font-family: Montserrat, sans-serif;
    font-size: 15px;
    font-weight: 400;
}
.grid-content-profilelist .results, .grid-content-profilelist #rso{
    margin-bottom: 21px;
}
.grid-content-profilelist {
  text-align: center;
}
.grid-profilelist-ratings {
  display: grid;
  grid-template-columns: auto auto auto auto;
      text-align: center;
    vertical-align: middle;
    line-height: 100%;
}
.grid-content-profilelist-ratings {
  text-align: center;
}
.grid-profilelist-description {
  display: grid;
  grid-template-columns: 100%;

}
.grid-profilelist-description-content {
  text-align: left;
}
.container-profile{
}

span.woocommerce-Price-amount.amount{font-weight:600;}
span.woocommerce-Price-amount.amount::after{
    content: "/hr";
}
span.woocommerce-Price-amount.amount::before{
    content: "Cost: ";
}
a.reviewstar:before {
    content: "\f005 ";
    font-family: "Font Awesome 5 Free";
    color: #F8C800;
    font-size: 20px;
}
li.product{
    border: .99px solid lightgray;
    padding-left:15px;
    margin: 10px !important;
    font-family: Montserrat, sans-serif;
    color:black;
}
a.read-more {
    color: white !important;
    background-color: #003399;
    padding: 9px;
    border-radius: 6px;
    width: 100%;
    cursor: pointer;
}
.storefront-sorting{
    margin-bottom:10px !important;
}
.wcfmmp_store_info_register_on{
    padding-left:20px;
    font-family: Montserrat, sans-serif;
    color:black;
}
/*end of listings*/
/*SAMPLE*/

/*PRO JOINED YEAR*/
.elementor-widget-container {
    font-family: montserrat!important;
    color: black!important;
}


/**/

.header_store_name {
    color: white!Important;
    font-family: montserrat!Important;
}

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
    
      <?php 
			if( !apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) {
				$WCFMmp->template->get_template( 'store/wcfmmp-view-store-sidebar.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
			}
			?>
			
			<div class="rgt right_side <?php if( !$WCFMmp->wcfmmp_vendor->is_store_sidebar() ) echo 'right_side_full'; ?>">
				<div id="tabsWithStyle" class="tab_area">
					
					<?php do_action( 'wcfmmp_before_store_tabs', $store_user->data, $store_info ); ?>
					
					<?php $WCFMmp->template->get_template( 'store/wcfmmp-view-store-tabs.php', array( 'store_user' => $store_user, 'store_info' => $store_info, 'store_tab' => $store_tab ) ); ?>
					
					<?php do_action( 'wcfmmp_after_store_tabs', $store_user->data, $store_info ); ?>
					
					<?php 
						switch( $store_tab ) {
							case 'about':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-about.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
				
							case 'policies':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-policies.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
								
							case 'reviews':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-reviews.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
								
							case 'followers':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-followers.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
								
							case 'followings':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-followings.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
								
						  case 'articles':
								$WCFMmp->template->get_template( 'store/wcfmmp-view-store-articles.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
								break;
								
							default:
								$WCFMmp->template->get_template( apply_filters( 'wcfmmp_store_default_template', apply_filters( 'wcfmp_store_default_template', 'store/wcfmmp-view-store-products.php', $store_tab ), $store_tab ), array( 'store_user' => $store_user, 'store_info' => $store_info ), '', apply_filters( 'wcfmp_store_default_template_path', '', $store_tab ) );
								break;
						}	
					?>
					
				</div><!-- .tab_area -->
			</div><!-- .right_side -->
			
			<?php 
			if( apply_filters( 'wcfmmp_is_allow_mobile_sidebar_at_bottom', true ) ) {
				$WCFMmp->template->get_template( 'store/wcfmmp-view-store-sidebar.php', array( 'store_user' => $store_user, 'store_info' => $store_info ) );
			}
			?>
			 
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