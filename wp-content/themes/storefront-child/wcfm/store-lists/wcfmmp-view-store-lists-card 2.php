
<?php
/**
 * The Template for displaying store sidebar category.
 *
 * @package WCfM Markeplace Views Store List Card
 *
 * For edit coping this to yourtheme/wcfm/store-lists
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp, $wpdb;

if( !$store_id ) return;
if( !wcfm_is_vendor( $store_id ) ) return;

if( !apply_filters( 'wcfmmp_store_list_card_valid', $store_id ) ) return;

$is_store_offline = get_user_meta( $store_id, '_wcfm_store_offline', true );
if ( $is_store_offline ) return;

$is_disable_vendor = get_user_meta( $store_id, '_disable_vendor', true );
if ( $is_disable_vendor ) return;

$store_user      = wcfmmp_get_store( $store_id );
$store_info      = $store_user->get_shop_info();
$gravatar        = $store_user->get_avatar();
$banner_type     = $store_user->get_list_banner_type();
if( $banner_type == 'video' ) {
  $banner_video = $store_user->get_list_banner_video();
} else {
  $banner          = $store_user->get_list_banner();
  if( !$banner ) {
    $banner = isset( $WCFMmp->wcfmmp_marketplace_options['store_list_default_banner'] ) ? $WCFMmp->wcfmmp_marketplace_options['store_list_default_banner'] : $WCFMmp->plugin_url . 'assets/images/default_banner.jpg';
    $banner = apply_filters( 'wcfmmp_list_store_default_bannar', $banner );
  }
}
$store_name      = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'wc-multivendor-marketplace' );
$store_name      = apply_filters( 'wcfmmp_store_title', $store_name , $store_id );
$store_url       = wcfmmp_get_store_url( $store_id );
$store_address   = $store_user->get_address_string(); 
$store_description = $store_user->get_shop_description();
?>


<div class="wrapper">
<!--Image-->
  <div class="image gridcell"><img class="img-size wcfmmp_sold_by_logos" src="<?php echo $gravatar; ?>" alt="Logo"/></div>
<!--Image-->

<!--name-->
  <b><div class="one gridcell">
     <?php if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?>
      <a href="<?php echo $store_url; ?>"><?php echo $store_name; ?></a>
    <?php } else { ?>
      <a href="#" onclick="return false;"><?php echo $store_name; ?></a>
    <?php } ?>
     </b>
    <br>Palayaw
 
  </div>
<!--name-->


<!--Follow Icon-->
  <div class="two gridcell">
    
          <?php do_action( 'before_wcfmmp_store_header_actions', $store_user->get_id() ); ?>
        
          <?php do_action( 'wcfmmp_store_before_enquiry', $store_user->get_id() ); ?>
          
          <?php if( apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfmmp_is_allow_store_header_enquiry', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'enquiry' ) ) { ?>
            <?php do_action( 'wcfmmp_store_enquiry', $store_user->get_id() ); ?>
          <?php } ?>
          
          <?php do_action( 'wcfmmp_store_after_enquiry', $store_user->get_id() ); ?>
          <?php do_action( 'wcfmmp_store_before_follow_me', $store_user->get_id() ); ?>
          
          <?php 
          if( apply_filters( 'wcfm_is_pref_vendor_followers', true ) && apply_filters( 'wcfm_is_allow_store_followers', true ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_follower' ) ) {
            do_action( 'wcfmmp_store_follow_me', $store_user->get_id() );
          }
          ?>
               <?php do_action('wcfmmp_sold_by_label_product_page_after', $vendor_id );?>
          <?php do_action( 'wcfmmp_store_after_follow_me', $store_user->get_id() ); ?>
          
          <?php do_action( 'after_wcfmmp_store_header_actions', $store_user->get_id() ); ?>
    
  </div>

<!--Follow Icon-->


<!--Nick Name-->
  <div class="three gridcell"></div>
<!--Nick Name-->

<!--address--->
  <div class="four gridcell"><?php echo $store_address;?></div>
  <div class="five gridcell"><span class="fa fa-star checked"></span> (4.93) <span class="fadedtext">(192) - Super Pro</span></div>
  <div class="six  gridcell"><i class="fa fa-handshake-o" style="font-size:10px padding:5px;"></i> Refferal </div>
  <div class="seven gridcell">
    
     <!--Drop Down Button-->
          <div class="toggleBtn">View Featured Listings</div>
     <!--Drop Down Button-->


<div class="below">
  
 <div class="wrapper2">
        <div class="con1 gridcell">
          <?php
          $eachID = $store_user->get_id();
          $listing = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_status = 'publish' AND post_type = 'product' AND post_author=".$eachID." LIMIT 1");
          foreach($listing as $fld){
            $listing_name = $fld->post_title;
            $listing_description = $fld->post_excerpt;
          }

          ?>
           <b><span class="service-title"><?php echo $listing_name; ?> </span> </b><br>
            <span><?php echo $listing_description; ?>  <?php echo $store_user->get_id(); ?></span>

        </div>

        <div class="con2 gridcell">
           <span>Star</span><br>
           <span>54 Streak</span><br>
           <span>Next: P 500/hr</span><br>
        </div>
        <div>
             <?php if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) { ?>
      <a href="<?php echo $store_url; ?>">View all Listing</a>
    <?php } else { ?>
      <a href="#" onclick="return false;"></a>
    <?php } ?>
        </div>
        </div>    
  </div>
  
</div>
         
          
         
         
          



</div>

<style>

/*Grid Style*/
.uk-text-justify.toggle-text{
  word-break: break-all;
}


  .wrapper {
  width:  38%;
  padding:2px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 10px;
  grid-auto-rows: minmax(0px, auto);
  border:1px solid black;

    float: left;
    width: 48% !important;
    margin: 5px;
}

.gridcell{
  border:1px solid black;

}

.image {
  grid-column: 1;
  grid-row: 1 / 4;
  width: 120px;
  height:165px;


}

.one {
  grid-column: 2;
  grid-row: 1;

}
.two {
  grid-column: 3;
  grid-row: 1;
 
}

.three {
  grid-column: 2;
  grid-row: 2;

}
.four {
  grid-column: 3;
  grid-row: 2;

}

.five {
  grid-column: 2;
  grid-row:3;

}


.six {
  grid-column: 3;
  grid-row: 3;

}


.seven {
  grid-column: 4/2;
  grid-row: 4;

}

 /*Dropdown Content grid*/
  .wrapper2 {
  width:100%;
  padding:2px;
  display: grid;
  grid-template-columns: repeat(1fr 1fr);    
  grid-gap: 10px;
  grid-auto-rows: minmax(100px, auto);

}
  .toggle-text {
  display: none;
}



.con1 {
  grid-column: 1;
  grid-row: 1;

}
.con2 {
  grid-column: 2;
  grid-row: 1;
 
}

.con3 {
  grid-column: 1;
  grid-row: 2;
}

.con4 {
  grid-column: 2;
  grid-row: 2;

}

.con5 {
  grid-column: 1;
  grid-row: 3;
}

.con6 {
  grid-column: 2;
  grid-row: 3;

}


</style>