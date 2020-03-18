<?php
/**
 * The Template for displaying store.
 *
 * @package WCfM Markeplace Views Store Sold By Simple
 *
 * For edit coping this to yourtheme/wcfm/sold-by
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $WCFM, $WCFMmp,$wpdb,$wp_query;
global $product, $post;
$product = new WC_Product($post->ID); 
$selectNextAvails=$wpdb->get_results("SELECT * FROM wp_wc_appointments_availability where kind_id = ".$product->get_id()." AND from_date >= CURDATE() AND from_date <> '' AND from_date IS NOT NULL ORDER BY from_date");
$searchUrl = $wp_query->query_vars['services'];
$searchFrom = $wp_query->query_vars['from'];
$searchTo = $wp_query->query_vars['to'];
		$searchKey='';
        if(isset($searchUrl)){
            $searchKey .= $searchUrl;
        }else if(isset($searchFrom)){
            $searchKey .= $searchFrom;
        }else if(isset($searchTo)){
            $searchKey .= $searchTo;
        }
        
        $arrayList = strtolower($product->get_name());
         foreach($selectNextAvails as $fld){
             if($fld->from_date !== null ){
                $arrayList .=','.strtolower(date("Y-m-d",strtotime($fld->from_date)));
                $arrayList .=','.strtolower(date("Y-m-d",strtotime($fld->to_date)));
             }
         }
        $ismatch = preg_match_all("/".$searchKey."/i",$arrayList);
        // var_dump($arrayList);
        if($ismatch){
if( empty($product_id) && empty($vendor_id) ) return;

if( empty($vendor_id) && $product_id ) {
	$vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $product_id );
}
if( !$vendor_id ) return;
	
if( $vendor_id ) {
	if( apply_filters( 'wcfmmp_is_allow_sold_by', true, $vendor_id ) && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $vendor_id, 'sold_by' ) ) {
		// Check is store Online
		$is_store_offline = get_user_meta( $vendor_id, '_wcfm_store_offline', true );
		if ( $is_store_offline ) {
			return;
		}
		
		$sold_by_text = $WCFMmp->wcfmmp_vendor->sold_by_label( absint($vendor_id) );
		if( apply_filters( 'wcfmmp_is_allow_sold_by_linked', true ) ) {
			$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_by_vendor( absint($vendor_id) );
		} else {
			$store_name = $WCFM->wcfm_vendor_support->wcfm_get_vendor_store_name_by_vendor( absint($vendor_id) );
		}
	
		echo '<div class="wcfmmp_sold_by_container">';
		echo '<div class="wcfm-clearfix"></div>';
		do_action('before_wcfmmp_sold_by_label_product_page', $vendor_id );
		echo '<div class="wcfmmp_sold_by_wrapper">';
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_label', true ) ) {
			echo '<span class="wcfmmp_sold_by_label">' . $sold_by_text . ':&nbsp;</span>';
		}
		
		if( apply_filters( 'wcfmmp_is_allow_sold_by_logo', true ) ) {
			$store_logo = $WCFM->wcfm_vendor_support->wcfm_get_vendor_logo_by_vendor( $vendor_id );
			if( !$store_logo ) {
				$store_logo = apply_filters( 'wcfmmp_store_default_logo', $WCFM->plugin_url . 'assets/images/wcfmmp-blue.png' );
			}
		}
		echo '<div class="grid-container">';
		echo '<div class="grid-item item1">'; 
		echo '<img class="wcfmmp_sold_by_logos" src="' . $store_logo . '"/>';
		echo "<p class='professional-name'";
		echo $store_name;
		echo $store_address;
		echo "</p>";
		echo "</div>";

		echo '<div class="grid-item">'; 
		echo '<div class="grid-item-title">'; 
		echo $product->get_name();
		if( apply_filters( 'wcfmmp_is_allow_sold_by_badges', true ) ) {
			if( apply_filters( 'wcfm_is_allow_badges_with_store_name', false ) ) {
				echo '<div class="wcfmmp_sold_by_badges_with_store_name" style="display:inline-block;margin-left:10px;">';
				do_action('wcfmmp_store_mobile_badges', $vendor_id );
				echo '</div>';
			}
		}
		echo  '<div class="star-alignment">';
		if( apply_filters( 'wcfmmp_is_allow_sold_by_badges', true ) ) {
			if( !apply_filters( 'wcfm_is_allow_badges_with_store_name', false ) ) {
				do_action('wcfmmp_store_mobile_badges', $vendor_id );
			}
		}
		echo '</div>'; 
		echo "</div>";
		echo '<div class="short-des" itemprop="short-des">';
		/*Short Description under name*/
		    	echo apply_filters( 'woocommerce_short_description', $product->get_short_description() );
		echo "</div>";
		
		echo '<div class="inner-grid-container">';
		echo '<div class="inner-grid-item">';
		?>
		<?php
		if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
		    return;
		}

		$rating_count = $product->get_rating_count();
		$review_count = $product->get_review_count();
		$average      = $product->get_average_rating();

		if ( $rating_count >= 0 ) : ?>

		            <?php echo wc_get_rating_html($average, $rating_count); ?>
		        <?php if ( comments_open() ): ?><a href="<?php echo get_permalink() ?>#reviews" style="color:#CECECE !important;" class="woocommerce-review-link" rel="nofollow">(<?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a><?php endif ?>


		<?php endif; 
		?>
		<?php /*
		if ( apply_filters( 'wcfmmp_is_allow_sold_by_review', true ) ) {
			echo '<div class="wcfm-clearfix"></div>';
			if( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) { $WCFMmp->wcfmmp_reviews->show_star_rating( 0, $vendor_id ); }
			echo '<div class="wcfm-clearfix"></div>';
		}
		*/
		
		echo "</div>";

		echo '<div class="inner-grid-item">';
		echo "54<i class='fas fa-star'></i> Streak";
		echo "</div>";

		echo '<div class="inner-grid-item">';
		echo "3 <i class='fa fa-handshake-o' aria-hidden='true'></i> Referral";
		echo "</div>";
		echo "</div>";

		
		echo "</div>";


		echo '<div class="grid-item">'; 
		echo '<div class="book-now-listing">'; 
		echo '<a role="button" class="read-more" onclick=location.href="' . esc_attr( $product->get_permalink() ) . '">' . __( "<f class='booknow'>Book Now</f>" ) . '</a>';;
		
		echo "</div>";
		echo "</div>";

		
		echo '<div class="grid-item"><p class="pro-label">';
		
	    $selectNextAvail=$wpdb->get_results("SELECT * FROM wp_wc_appointments_availability where kind_id = ".$product->get_id()." AND from_date >= CURDATE() AND from_date <> '' AND from_date IS NOT NULL ORDER BY from_date LIMIT 1");
	    $countRows=$wpdb->get_var("SELECT * FROM wp_wc_appointments_availability where kind_id = ".$product->get_id()." AND from_date >= CURDATE() AND from_date <> '' AND from_date IS NOT NULL  LIMIT 1");
         if($countRows>0){
            foreach($selectNextAvail as $fld){
             if($fld->from_date !== null ){
                echo '<b class="inner-grid-item-content">Next Available: '.date("M d, Y",strtotime($fld->from_date)).'  '.date("g:i A",strtotime($fld->from_range)).' - '.date("g:i A",strtotime($fld->to_range)).'</b><br>';
               
             }
         }
         }else{
                 echo '<b class="inner-grid-item-content">No Available Dates...</b><br>';
             }
         
		//echo 'Pro Answer to the Question, Why do people like working with you? Hire you? Etc.';
		//echo '<p class="more" itemprop="description">';
		//echo "</p>";
		//echo apply_filters( 'woocommercemmerce_short_description', $product->get_short_description() );
		echo "</div>";

		echo '<div class="grid-item">';
		$settings = get_post_meta($product->get_id(),'_wc_appointment_pricing');
    	foreach($settings as $fld =>$value){
    	    echo wc_price($product->get_price_including_tax(1,$value[0]['base_cost']));
    	}
    	
		echo "</div>";
		echo '<div class="grid-item-vendor1">';
		echo '<div class="grid-item">';
		echo "</div>";
		echo "</div>";
		echo '<div class="grid-item-vendor">';
			echo '<div class="grid-item">';
				    echo  '<?php echo $store_address; ?><a class="toggleBtn"><i class="fas fa-angle-down"></i></a>';
     	echo '<div class="below">';
    	echo '<div class="grid-container-description">';
		echo '<div class="grid-item-description">';
		/*Short description 2*/
		echo apply_filters( 'the_content', $product->post->post_content );
         "</div>";
         

		echo "</div>";
		echo "</div>";
		do_action('wcfmmp_sold_by_label_product_page_after', $vendor_id );
		
		echo '<div class="wcfm-clearfix"></div>';
		echo '</div>';

echo "</div>";
			echo "</div>";
		
		echo "</div>";
		echo '</div>';
  
	}
}
}
