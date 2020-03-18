<?php
/**
 * WCFM plugin view
 *
 * Marketplace WCfM Marketplace Support
 * This template can be overridden by copying it to yourtheme/wcfm/dashboard/
 * ADMIN
 * @author      WC Lovers
 * @package     wcfm/views/dashboard
 * @version   5.0.0
 */
 
global $WCFMmp, $WCFM, $wpdb;

$user_id = $current_user_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );

// Get products using a query - this is too advanced for get_posts :(
$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );

$query_from = apply_filters( 'wcfm_report_low_in_stock_query_from', "FROM {$wpdb->posts} as posts
    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
    INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
    WHERE 1=1
    AND posts.post_type IN ( 'product', 'product_variation' )
    AND posts.post_status = 'publish'
    AND posts.post_author = {$user_id}
    AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
    AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
    AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'
", $stock, $nostock );
$lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );

$query_from = apply_filters( 'wcfm_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
    INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
    WHERE 1=1
    AND posts.post_type IN ( 'product', 'product_variation' )
    AND posts.post_status = 'publish'
    AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
    AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$nostock}'
", $nostock );

$outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );

$today_date = @date('Y-m-d');
$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );

$wcfm_dashboard_sales_interval = apply_filters( 'wcfm_dashboard_sales_interval', 'month' );

// Total Sales Amount
$gross_sales = $WCFM->wcfm_vendor_support->wcfm_get_gross_sales_by_vendor( $current_user_id, $wcfm_dashboard_sales_interval );

// Total Earned Commission
$earned = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $current_user_id, $wcfm_dashboard_sales_interval );

// Admin Fee Mode Commission
if( $admin_fee_mode ) {
    $earned = $gross_sales - $earned;
}

// Total Received Commission
//$commission = $WCFM->wcfm_vendor_support->wcfm_get_commission_by_vendor( $current_user_id, 'month', true );

// Total item sold
$total_sell = $WCFM->wcfm_vendor_support->wcfm_get_total_sell_by_vendor( $current_user_id, $wcfm_dashboard_sales_interval );

// Counts
$order_count = 0;
$on_hold_count    = 0;
$processing_count = 0;

$sql = "SELECT commission.order_id, commission.order_status FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
$sql .= " WHERE 1=1";
$sql .= " AND commission.vendor_id = %d";
$sql .= " AND `is_refunded` != 1 AND `is_trashed` != 1";
$sql  = wcfm_query_time_range_filter( $sql, 'created', $wcfm_dashboard_sales_interval ); 
$sql .= " GROUP BY commission.order_id";

$vendor_orders = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
if( !empty($vendor_orders) ) {
    $order_count = apply_filters( 'wcfmmp_dashboard_vendor_order_count', count( $vendor_orders ), $vendor_orders, $user_id );
    foreach( $vendor_orders as $vendor_order ) {
        // Order exists check
        $order_post_title = get_the_title( $vendor_order->order_id );
        if( !$order_post_title ) continue;
        if( $vendor_order->order_id ) {
            if( $vendor_order->order_status == 'processing' ) $processing_count++;
            if( $vendor_order->order_status == 'on-hold' ) $on_hold_count++;
        }
    }
}

// unfulfilled_products
$unfulfilled_products = 0;
$sql  = "SELECT  COUNT(DISTINCT(commission.order_id)) FROM {$wpdb->prefix}wcfm_marketplace_orders AS commission";
$sql .= " WHERE 1=1";
$sql .= " AND commission.vendor_id = %d";
$sql .= " AND commission.shipping_status = 'pending'";
$sql  = wcfm_query_time_range_filter( $sql, 'created', $wcfm_dashboard_sales_interval ); 

$unfulfilled_products = $wpdb->get_var( $wpdb->prepare( $sql, $user_id ) );
if( !$unfulfilled_products ) $unfulfilled_products = 0;

if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) {
    include_once( $WCFM->plugin_path . 'includes/reports/class-wcfmmarketplace-report-sales-by-date.php' );
    $wcfm_report_sales_by_date = new WCFM_Marketplace_Report_Sales_By_Date( 'month' );
    $wcfm_report_sales_by_date->calculate_current_range( 'month' );
    $report_data   = $wcfm_report_sales_by_date->get_report_data();
}

// WCFM Analytics
if( $wcfm_is_allow_analytics = apply_filters( 'wcfm_is_allow_analytics', true ) ) {
    include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-analytics.php' );
    $wcfm_report_analytics = new WCFM_Report_Analytics();
    $wcfm_report_analytics->chart_colors = apply_filters( 'wcfm_report_analytics_chart_colors', array(
                'view_count'       => '#C79810',
            ) );
    $wcfm_report_analytics->calculate_current_range( '7day' );
}

?>
<?php /**FETCH APPOINTMENT DATA */ 
$user_id = get_current_user_id();
$user_roles = $user_meta->roles;

$user = wp_get_current_user();
 $roles = ( array ) $user->roles;
$current_page = 0;
/**FILTER APPOINTMENT DATA */
        if($roles[0]==="administrator"){
            $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 1000 );
            
        $this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
            $user_roles,
            apply_filters(
            'woocommerce_appointments_my_appointments_today_query_args',
            array(
                'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
            'order'       => 'ASC',
            'date_after'  => strtotime('last monday', strtotime('tomorrow')),
            'date_before' => strtotime( 'next monday', current_time( 'timestamp' ) ),
            'offset'      => ( $current_page - 1 + 1 ) * $appointments_per_page,
            'limit'       => $appointments_per_page,
                )
                )
        );
        }else{
         $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 1000 );
        $this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
            $user_id,
            apply_filters(
            'woocommerce_appointments_my_appointments_today_query_args',
            array(
                'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
                'order'       => 'ASC',
        
                'offset'      => ( $current_page - 1 + 1 ) * $appointments_per_page,
                'limit'       => $appointments_per_page,

                )
                )
        );
        }


        
/**ASSIGN APPOINTMENTS TO TABLE */      
$tables = array();
if ( ! empty( $this_week_appointments ) ) {
    $tables['today'] = array(
        'header'       => __( '', 'woocommerce-appointments' ),
        'appointments' => $this_week_appointments,
    );
}
/**FETCH APPOINTMENT DATA END */ ?>


<?php $cancelledctr=0;?><!--COUNTERS FOR ROW DATE GROUPS-->
<?php $paidctr=0;?>
<?php $pendingconfirmation=0;?>
<?php $pendingpayment=0;?>
<?php $requestsctr=0;?>
<?php $pendingrevenue=0;?>
<?php ?>
<?php  if ( ! empty( $tables ) ) : ?>
    <?php foreach ( $tables as $table_id => $table ) : ?>
        <?php foreach ( $table['appointments'] as $appointment ) : ?>
            <?php if ( 'cancelled' === $appointment->get_status()): ?>
                <?php $cancelledctr++; ?>
            <?php endif; ?>
            <?php if ( 'paid' === $appointment->get_status()): ?>
                <?php $paidctr++; ?>
            <?php endif; ?>
            <?php if ( 'confirmed' === $appointment->get_status()): ?>
                <?php $pendingpayment++;$requestsctr++; $pendingrevenue += $appointment->get_order()->total;?>
            <?php endif; ?>
            <?php if ( 'pending-confirmation' === $appointment->get_status()): ?>
                <?php $pendingconfirmation++;$requestsctr++; ?>
            <?php endif; ?>
        <?php endforeach;?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="collapse wcfm-collapse" id="wcfm_order_details">

  <div class="wcfm-page-headig">
        <span class="wcfmfa fa-chalkboard"></span>
        <span class="wcfm-page-heading-text"><?php _e( 'Dashboard', 'wc-frontend-manager' ); ?></span>
        <?php do_action( 'wcfm_page_heading' ); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>
        
        <?php do_action( 'begin_wcfm_dashboard' ); ?>
        
        
        
        <?php if( apply_filters( 'wcfm_is_pref_stats_box', true ) ) { ?>
            <!--<div class="wcfm_dashboard_stats">
                <?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
                    <?php if( apply_filters( 'wcfm_sales_report_is_allow_gross_sales', true ) && apply_filters( 'wcfm_is_allow_stats_block_gross_sales', true ) ) { ?>
                        <div class="wcfm_dashboard_stats_block">
                            <a href="<?php echo get_wcfm_reports_url( 'month' ); ?>">
                                <span class="wcfmfa fa-currency"><?php echo get_woocommerce_currency_symbol() ; ?></span>
                                <div>
                                    <strong><?php echo apply_filters( 'wcfm_vendor_dashboard_gross_sales', wc_price( $gross_sales ) ); ?></strong><br />
                                    <?php _e( 'gross sales in this month', 'wc-frontend-manager' ); ?>
                                </div>
                            </a>
                        </div>
            -->
                    <?php } ?>
                    <?php do_action( 'wcfm_dashboard_stats_block_after_gross_sales', $user_id ); ?>
                    <?php if( apply_filters( 'wcfm_is_allow_view_commission', true ) && apply_filters( 'wcfm_is_allow_stats_block_commission', true ) ) { ?>
                        <!--<div class="wcfm_dashboard_stats_block">
                            <a href="<?php echo get_wcfm_reports_url( ); ?>">
                                <span class="wcfmfa fa-money fa-money-bill-alt"></span>
                                <div>
                                    <strong><?php echo apply_filters( 'wcfm_vendor_dashboard_commission', wc_price( $earned ) ); ?></strong><br />
                                    <?php if( $admin_fee_mode ) { _e( 'admin fees in this month', 'wc-frontend-manager' ); } else { _e( 'earnings in this month', 'wc-frontend-manager' ); } ?>
                                </div>
                            </a>
                        </div>
                        -->
                    <?php } ?>
                    <!--<?php do_action( 'wcfm_dashboard_stats_block_after_commission', $user_id ); ?>
                    <?php if( apply_filters( 'wcfm_is_allow_stats_block_sold_item', true ) ) { ?>
                        <div class="wcfm_dashboard_stats_block">
                            <a href="<?php echo apply_filters( 'sales_by_product_report_url', get_wcfm_reports_url( ), '' ); ?>">
                                <span class="wcfmfa fa-cube"></span>
                                <div>
                                    <?php printf( _n( "<strong>%s Appointments</strong>", "<strong>%s Appointments</strong>", $total_sell, 'wc-frontend-manager' ), $total_sell ); ?>
                                    <br /><?php _e( 'sold in this month', 'wc-frontend-manager' ); ?>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                    -->
                    <?php do_action( 'wcfm_dashboard_stats_block_after_sold_item', $user_id ); ?>
                <?php } ?>
                <!--<?php if( apply_filters( 'wcfm_is_allow_orders', true ) && apply_filters( 'wcfm_is_allow_stats_block_orders', true ) ) { ?>
                    <div class="wcfm_dashboard_stats_block">
                        <a href="<?php echo get_wcfm_orders_url( ); ?>">
                            <span class="wcfmfa fa-cart-plus"></span>
                            <div>
                                <?php printf( _n( "<strong>%s Project</strong>", "<strong>%s Projects</strong>", $order_count, 'wc-frontend-manager' ), $order_count ); ?>
                                <br /><?php _e( 'received in this month', 'wc-frontend-manager' ); ?>
                            </div>
                        </a>
                    </div>
                <?php } ?>
                -->
                <?php do_action( 'wcfm_dashboard_stats_block_after_orders', $user_id ); ?>
            </div>
            <div class="wcfm-clearfix"></div>
        <?php } ?>
        <?php do_action( 'wcfm_after_dashboard_stats_box', $user_id ); ?>
        <?php if( $wcfm_is_allow_reports = apply_filters( 'wcfm_is_allow_reports', true ) ) { ?>
            <div class="wcfm_dashboard_wc_reports_sales">
                <div class="wcfm-container">
                    <div class="last-login"><?php // $WCFM->template->get_template( 'dashboard/wcfm-view-dashboard-welcome-box.php' ); ?></div>
                    <div class="dashboard-container">
                    <div class="dashboard-item"><p class="dashboard-title">
                    <p class="this-week">
                    This Week
                    </p></p>
                    <p class="dashboard-title" id="">
                    <?php
                            $monday = strtotime('monday this week');
                            $sunday = strtotime('sunday this week');
                            echo $this_week_sd = date("M d",$monday)." - ";
                            echo $this_week_ed = date("M d",$sunday);
                            ?>
                    </p></div>
                      <div class="dashboard-item"><p class="dashboard-title"><?php //printf( _n( "<strong>%s </strong>", "<strong>%s </strong>", $total_sell, 'wc-frontend-manager' ), $order_count+$total_sell ); ?>
                      <?php 
                    global $wpdb;
                    $posts_table = $wpdb->prefix.'posts';
                    $query = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'confirmed'";
                    $query1 = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'pending-confirmation'";
                    $result = $wpdb->get_results($query); //confirmed
                    $result1 = $wpdb->get_results($query1); //pending
                    //var_dump($result);
                    
                    //echo $result[0]->count; // This line will disaply the number 
                    ?>
                    <?php
                    
                      if($result1[0]->count == true){
                       echo '<div class="tooltipgig"><p style=color:red;>';
                       echo $result[0]->count + $result1[0]->count ;
                       echo '<p id="rowstext"></p>';
                       echo '<span class="tooltiptext">';
                       echo '<a href="https://gigant.com.ph/professional-manager/appointments/?appointment_status=pending-confirmation">', 'Pending: ',$result1[0]->count, '</a><br>';
                       echo 'Confirmed: ', $result[0]->count;
                       echo '</span>';
                       echo '</p></div>';
                      } 
                       else{
                        echo '<div class="tooltipgig"><p style=color:black;>';
                       echo $result[0]->count + $result1[0]->count ;
                       echo '<span class="tooltiptext">';
                       echo 'Pending: ', $result1[0]->count , '<br>';
                       echo 'Confirmed: ', $result[0]->count;
                       echo '</span>';
                       echo '</p></div>';
                    }
                      ?>
                      <p class="dashboard-title">
                        <?php
                        
                        if($result1[0]->count == true){
                            echo '<div class="tooltip1"><p class="dashboard-title">Upcoming Appointment/s</p> <i class="fas fa-exclamation-circle" style="font-size:20px; color:red;"></i>';
                            echo '</div>' ;
                        }
                        else{
                        echo '<div class="tooltip1"><p class="dashboard-title">Upcoming Appointment/s</p> <i class="fas fa-exclamation-circle" style="font-size:20px; color:red;"></i>';
                        echo '</div>' ;
                        }
                        ?>
                        </div>  
                      <div class="dashboard-item"><p class="dashboard-title"><p class="lapsed">0
                            <p class="dashboard-title">Lapsed</p>
                            <?php
                            
                            ?>
                            </div>
                      <div class="dashboard-item"><p class="dashboard-title"><?php printf( _n( "%s ", "%s ", $total_sell, 'wc-frontend-manager' ), $total_sell ); ?>
                        <p class="dashboard-title">Accomplished</p>
                        </div>  
                      <div class="dashboard-item"><p class="dashboard-title"><?php echo apply_filters( 'wcfm_vendor_dashboard_gross_sales', wc_price( $gross_sales ) ); ?>
                        <p>
                                <p class="dashboard-title">Accomplished Revenue</p>
                        </p>
                      </div>
                      <div class="dashboard-item"><?php echo "₱".number_format($pendingrevenue, 2, '.', ',');?>
                    <p class="dashboard-title">Pending Revenue</p>
                    </div>
                    
                    </div>
                    <div class="appointment-container">
                      <div class="appointment-item"><p class="schedule">Schedule</p>
                      <div class="appointment-schedule-table">
                      <?php  if ( ! empty( $tables ) ) : ?>
    
    <?php do_action( 'woocommerce_before_account_orders', $has_orders ); ?>
    <?php foreach ( $tables as $table_id => $table ) : ?>
        <table class="shop_table my_account_appointments <?php echo esc_html( $table_id ) . '_appointments'; ?>">
			<thead>
				<tr class="table-header-row">
					<td class="table-header-cell">
					Date
					</td>
					<td class="table-header-cell">
					Details
					</td>
					<td class="table-header-cell">
					Status
					</td>
					<td class="table-header-cell">
					Schedule
					</td>
				</tr>
			</thead>
			<tbody>
                <?php $count = 0; ?>
                <?php foreach ( $table['appointments'] as $appointment ) : ?>
                    <?php $count++; ?>
                    <?php if ( !('cancelled' === $appointment->get_status())): ?>
                    <?php $date = new DateTime(esc_attr($appointment->get_start_date()));?>
                    
                    <?php $lastcount = 0; ?>
                    <?php foreach ( $table['appointments'] as $appointmentctr ) : ?>
                    <?php $lastdate = new DateTime(esc_attr($appointmentctr->get_start_date())); ?>
                        <?php if($date->format('Y-m-d') == $lastdate->format('Y-m-d')){$lastcount++;} ?>
                    <?php endforeach; ?>

                        <tr class="home-appointment-row<?php if( $count == $lastcount){echo " last-row";$count = 0;} ?>">
                            <td class="home-appointment-date"><!--DATE -->
                                <?php if ( $pdate != $date->format('Y-m-d')): ?>
                                    <span class="home-date-of-month"><?php echo $date->format('j');?></span><br>
                                    <span class="home-day-of-week"><?php echo $date->format('D');?></span>
                                <?php endif; ?>     
                            </td>
                            <td class="home-appointment-details"><!--DETAILS -->
                                <?php if($roles[0]==="administrator"):  ?>
                                    <span class="home-customer-name">
                                    <?php $customer=get_user_by('id', $appointment->customer_id);
                                    echo $customer->first_name." ".$customer->last_name; ?>  
                                    </span>
                                    <?php echo " booked ";?>
                                <?php endif; ?>
                                <?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
                                    <span class="home-listing-pro-name"><a class="home-appointment-listing-name" href="<?php echo $appointment->get_order()->get_view_order_url(); ?>">
                                        <?php echo esc_html( $appointment->get_product_name() ); ?>
                                    </a>
                                <?php endif; ?> with 
                                <?php if ( $appointment->get_order() ) : ?>
                                    <?php
                                    global $wpdb;
                                    $table_users = $wpdb->prefix . "users";
                                    $table_posts = $wpdb->prefix . "posts";
                                    $selectedProduct = $appointment->get_product_id();
                                    $sql1 = "SELECT $table_users.* FROM wp_posts,$table_users where $table_posts.post_author = $table_users.ID and $table_posts.ID = $selectedProduct";
                                    $results1 = $wpdb->get_results($sql1);

                                    foreach ($results1 as $fld) {
                                        $performerID = $fld->ID;
                                    echo "<a href=".$appointment->get_order()->get_view_order_url().">".$fld->display_name."</a>";
                                    }
                                    ?>
                                <?php endif; ?><br></span>
                                <span class="home-customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
                            </td>
                            <td class="home-appointment-status"><!--STATUS -->
                                <?php if ( $appointment->get_status() === 'confirmed') :?>
                                    <?php echo "Pending Payment";?> 
                                <?php else: ?>  
                                    <?php echo esc_html( wc_appointments_get_status_label( $appointment->get_status() ) ); ?>
                                <?php endif; ?>
                            </td>
                            <td class="home-appointment-actions"><!--ACTIONS AND CALL SCHEDULE -->
                                <span class='home-appointment-countdown'>
                                <?php //call shcedule
                                
                                $fromstart= new DateTime(esc_attr($appointment->get_start_date())); 
                                $tostart= new DateTime(esc_attr($appointment->get_end_date())); 
                                echo $fromstart->format('h:i a').' - '.$tostart->format('h:i a'); 
                                
                                ?><br>
                                </span>
                                
                                <?php if ( $appointment->get_status() === 'confirmed') :?>
                                    <?php //pay now button
                                    if ( $appointment->get_order() ) : 
                                        $actions = wc_get_account_orders_actions( $appointment->get_order());
                                        if ( ! empty( $actions ) ) {
                                            foreach ( $actions as $key => $action ) { 
                                                if($action['name']=='Pay'){
                                                    echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">Pay Now</a>';
                                                }
                                            }
                                        }
                                    endif;
                                    ?>

                                <?php elseif ( $appointment->get_order() && $appointment->get_status() === 'paid') : ?>
                                    <?php
                                    global $wpdb;
                                    
                                    $count=0;
                                    $postID=get_the_ID(); 
                                    $options = get_option('VWliveWebcamsOptions');
                                    //performer (role)
                                    $current_user = wp_get_current_user();
                                    //access keys
                                    $userkeys = $current_user->roles;
                                    $userkeys[] = $current_user->user_login;
                                    $userkeys[] = $current_user->ID;
                                    $userkeys[] = $current_user->user_email; 
                                    
                                    $table_private = $wpdb->prefix . "vw_vmls_private";
                                    $table_appointment = $wpdb->prefix . "posts";
                                    $table_user = $wpdb->prefix . "users";
                                    $uid = $current_user->ID;
                                    $sql = "SELECT $table_private.*,$table_user.user_nicename FROM $table_private,$table_appointment,$table_user where $table_private.rid = $table_appointment.ID AND $table_appointment.post_author = $table_user.ID AND $table_private.pid = $performerID AND $table_private.cid = $uid AND $table_private.rid=$postID";
                                    $results = $wpdb->get_results($sql);

                                    foreach ($results as $private){
                                        $callCode = toBase64($private->id);
                                        $callURL = add_query_arg( array('call'=> $callCode), get_permalink( $private->rid ));

                                        if ($private->meta){
                                        $meta = unserialize($private->meta);
                                        $metaInfo = $meta['email'];
                                        }
                                        echo  "<a href='https://gigant.com.ph/webcam/".$private->user_nicename."/?call=".$callCode."' class='button'>Access</a>";
                                    }
                                        // echo do_shortcode('[videowhisper_cam_calls post_id="' . $postID . '"]'); 
                                    
                                    ?> 
                                
                                <?php endif;?>
                            </td>
                            <?php $pdate = $date->format('Y-m-d');?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else : ?><!--ELSE NO APPOINTMENTS -->
    <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
    <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
    <?php esc_html_e( 'Book', 'woocommerce-appointments' ); ?>
    </a>
    <?php esc_html_e( 'No appointments scheduled yet.', 'woocommerce-appointments' ); ?>
    </div>
<?php endif; ?>
                       
                      </div> 
                      </div>
                      <div class="appointment-item"><?php do_action( 'after_wcfm_dashboard_product_stats' ); ?></div>
                    </div>
                    <!--<div id="wcfm_dashboard_wc_reports_expander_sales" class="wcfm-content">
                        <div id="poststuff" class="woocommerce-reports-wide">
                            <div class="postbox">
                                <div class="inside">
                                    <a class="chart_holder_anchor" href="<?php // echo get_wcfm_reports_url( 'month' ); ?>">
                                        <?php // $wcfm_report_sales_by_date->get_main_chart(0); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    -->
                </div>
            </div>
            <div class="wcfm-clearfix"></div>
        <?php } ?>
        
       
                
                <?php do_action('after_wcfm_dashboard_zone_analytics'); ?>
           
                
            <?php do_action( 'after_wcfm_dashboard_right_col' ); ?>
        </div>
    </div>
</div>