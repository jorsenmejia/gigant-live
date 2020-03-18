



<?php
global $WCFM, $wp_query,$wpdb;
$searchUrl = $wp_query->query_vars['orderStatus'];
$searchOrder = $wp_query->query_vars['orderItemsBy'];

?>

<div class="collapse wcfm-collapse" id="wcfm_upgrade_listing">
    <!-- <?var_dump($searchOrder);?>
    <?var_dump($searchUrl);?> FILTER TEST-->
    <div class="wcfm-page-headig">
        <!-- <span class="fa fa-cubes"></span> -->
        <span class="wcfm-page-heading-text"><?php // _e( 'Upgrade', 'wcfm-custom-menus' ); ?></span>
        <?php do_action( 'wcfm_page_heading' ); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div id="wcfm_page_load"></div>
        <?php do_action( 'before_wcfm_upgrade' ); ?>
        
        <div class="wcfm-container wcfm-top-element-container">
            <h2><?php _e('Transaction', 'wcfm-custom-menus' ); ?></h2>
            <div class="wcfm-clearfix"></div>
      </div>
      <div class="wcfm-clearfix"></div><br />
        

        <div class="wcfm-container">
            <div id="wcfm_upgrade_listing_expander" class="wcfm-content">
            
                <!---- Add Content Here ----->
                
<!DOCTYPE html>
<html>

           <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />  
                      
           <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />  
<style>
    .transaction-revenue{
        float: right;
    }
    .transaction-filter{
        display: inline;
    }
</style>
<body >

        <?php
        global $WCFM, $wpdb;

    $items_per_page = 3;
    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $offset = ( $page * $items_per_page ) - $items_per_page;

    $order_count = 0;
    $on_hold_count    = 0;
    $processing_count = 0;

foreach ( wc_get_order_types( 'order-count' ) as $type ) {
    $counts           = (array) wp_count_posts( $type );
    $on_hold_count    += isset( $counts['wc-on-hold'] ) ? $counts['wc-on-hold'] : 0;
    $processing_count += isset( $counts['wc-processing'] ) ? $counts['wc-processing'] : 0;
    
    $order_count    += isset( $counts['wc-on-hold'] ) ? $counts['wc-on-hold'] : 0;
    $order_count    += isset( $counts['wc-processing'] ) ? $counts['wc-processing'] : 0;
    $order_count    += isset( $counts['wc-completed'] ) ? $counts['wc-completed'] : 0;
    $order_count    += isset( $counts['wc-pending'] ) ? $counts['wc-pending'] : 0;
}


// Get products using a query - this is too advanced for get_posts :(
$stock          = absint( max( get_option( 'woocommerce_notify_low_stock_amount' ), 1 ) );
$nostock        = absint( max( get_option( 'woocommerce_notify_no_stock_amount' ), 0 ) );
$transient_name = 'wc_low_stock_count';

if ( false === ( $lowinstock_count = get_transient( $transient_name ) ) ) {
    $query_from = apply_filters( 'woocommerce_report_low_in_stock_query_from', "FROM {$wpdb->posts} as posts
        INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
        INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
        WHERE 1=1
        AND posts.post_type IN ( 'product', 'product_variation' )
        AND posts.post_status = 'publish'
        AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
        AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$stock}'
        AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) > '{$nostock}'
    " );
    $lowinstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
    set_transient( $transient_name, $lowinstock_count, DAY_IN_SECONDS * 30 );
}

$transient_name = 'wc_outofstock_count';

if ( false === ( $outofstock_count = get_transient( $transient_name ) ) ) {
    $query_from = apply_filters( 'woocommerce_report_out_of_stock_query_from', "FROM {$wpdb->posts} as posts
        INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
        INNER JOIN {$wpdb->postmeta} AS postmeta2 ON posts.ID = postmeta2.post_id
        WHERE 1=1
        AND posts.post_type IN ( 'product', 'product_variation' )
        AND posts.post_status = 'publish'
        AND postmeta2.meta_key = '_manage_stock' AND postmeta2.meta_value = 'yes'
        AND postmeta.meta_key = '_stock' AND CAST(postmeta.meta_value AS SIGNED) <= '{$nostock}'
    " );
    $outofstock_count = absint( $wpdb->get_var( "SELECT COUNT( DISTINCT posts.ID ) {$query_from};" ) );
    set_transient( $transient_name, $outofstock_count, DAY_IN_SECONDS * 30 );
}

include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-sales-by-date.php' );

// For net sales block value
$wcfm_report_sales_by_date_block = new WCFM_Report_Sales_By_Date( '7day' );
$wcfm_report_sales_by_date_block->calculate_current_range( '7day' );
$report_data_block   = $wcfm_report_sales_by_date_block->get_report_data();

// For sales by date graph
$wcfm_report_sales_by_date = new WCFM_Report_Sales_By_Date( 'month' );
$wcfm_report_sales_by_date->calculate_current_range( 'month' );
$report_data   = $wcfm_report_sales_by_date->get_report_data();

// WCFM Analytics
include_once( $WCFM->plugin_path . 'includes/reports/class-wcfm-report-analytics.php' );
$wcfm_report_analytics = new WCFM_Report_Analytics();
$wcfm_report_analytics->chart_colors = apply_filters( 'wcfm_report_analytics_chart_colors', array(
            'view_count'       => '#C79810',
        ) );
$wcfm_report_analytics->calculate_current_range( '7day' );

$user_id = get_current_user_id();

$is_marketplace = wcfm_is_marketplace();

$admin_fee_mode = apply_filters( 'wcfm_is_admin_fee_mode', false );

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        var $rows = $('#transactionTable tr'); //table id
        $('#searchTable').keyup(function() { //input id
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            
            $rows.show().filter(function() {
                var text = $(this).find("td:nth-child(6)").text().replace(/\s+/g, ' ').toLowerCase(); //nth-child to specify which column to search, to search all columns, can remove .find("td:nth-child(6)")
                return !~text.indexOf(val);
            }).hide();

            if(val==""){
                PageClick(0); //if input is empty, run function to reset all rows.
            }
            
        });

      
        
    });
</script>


<div class="transaction">
    <div class="transaction-filter">

        <select id="filterStatus" onchange="changeHref()">
        <option value="" selected>Filter by Status</option>
        <option value="orderStatus=paid&" <?php echo ($searchUrl=='paid') ? "selected": "" ?>>Accepted</option>
        <option value="orderStatus=confirmed&" <?php echo ($searchUrl=='confirmed') ? "selected": "" ?>>Confirmed</option>
        <option value="orderStatus=pending-confirmation&" <?php echo ($searchUrl=='pending-confirmation') ? "selected": "" ?>>Pending Confirmation</option>    
        <option value="orderStatus=complete&" <?php echo ($searchUrl=='complete') ? "selected": "" ?>>Completed</option>
        <option value="orderStatus=cancelled&" <?php echo ($searchUrl=='cancelled') ? "selected": "" ?>>Refunded/Lapsed</option>
        </select>
        <select id="queryOrder" onchange="changeHref()">
        <option value="" selected>Sort by Date</option>
        <option value="orderItemsBy=ASC&" <?php echo ($searchOrder=='ASC') ? "selected": "" ?>>Ascending</option>
        <option value="orderItemsBy=DESC&" <?php echo ($searchOrder=='DESC') ? "selected": "" ?>>Descending</option>
        </select>
        <a id="filterLink" class="button" href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">Filter</a>
        <a id="resetFilter" class="button" href="javascript:resetHref()">All</a>
        <input type="text" id="searchTable" placeholder="Search Client Name">
    </div>


<?php /**FETCH APPOINTMENT DATA */ 
$user_id = get_current_user_id();
$user_roles = $user_meta->roles;

$user = wp_get_current_user();
 $roles = ( array ) $user->roles;
$current_page = 0;
if(!$searchOrder){$searchOrder='ASC';}
/**FILTER APPOINTMENT DATA */
        if($roles[0]==="administrator"){
            $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 1000 );
            
        $this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
            $user_roles,apply_filters('woocommerce_appointments_my_appointments_today_query_args',

            array(
                'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
                'order'       => $searchOrder,
                'offset'      => ( $current_page - 1 + 1 ) * $appointments_per_page,
                'limit'       => $appointments_per_page,
                )
                )
        ); 
        }else{

         $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 1000 );
         
        $this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
            $user_id,apply_filters('woocommerce_appointments_my_appointments_today_query_args',

            array(
                'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
                'order'       => 'ASC',
                'offset'      => ( $current_page - 1 + 1 ) * $appointments_per_page,
                'limit'       => $appointments_per_page,
                'status'      => 'paid',
                )
                )
        );echo apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'appointment' );
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

<?php $cancelledctr=0;?>
<?php $paidctr=0;?>
<?php $pendingconfirmation=0;?>
<?php $pendingpayment=0;?>
<?php $requestsctr=0;?>
<?php $confirmedctr=0;?>
<?php $upcomingctr=0;?>
<?php $completedctr=0;?>
<?php $allctr=0;?>
<?php  if ( ! empty( $tables ) ) : ?>
    <?php foreach ( $tables as $table_id => $table ) : ?>

        <?php foreach ( $table['appointments'] as $appointment ) : ?>

            <?php if ( 'complete' === $appointment->get_status()): ?>
                <?php $allctr++; ?>
             <?php endif; ?>
            <?php if ( 'cancelled' === $appointment->get_status()): ?>
                <?php $cancelledctr++; ?>
            <?php endif; ?>
            <?php if ( 'paid' === $appointment->get_status()): ?>
                <?php $paidctr++; ?>
            <?php endif; ?>
            <?php if ( 'confirmed' === $appointment->get_status()): ?>
                <?php $pendingpayment++;$requestsctr++; ?>
            <?php endif; ?>
            <?php if ( 'pending-confirmation' === $appointment->get_status()): ?>
                <?php $pendingconfirmation++;$requestsctr++; ?>
            <?php endif; ?>
            <?php if ( 'complete' === $appointment->get_status()): ?>
                <?php $completedctr+= $appointment->get_order()->total; ?>
            <?php endif; ?>
            <?php if ( 'paid' === $appointment->get_status()): ?>
                <?php $upcomingctr+= $appointment->get_order()->total; ?>
            <?php $sum = $completedctr + $upcomingctr;
            ?>
            <?php endif; ?>

        <?php endforeach;?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="transaction-revenue">
        <p>
        <p class="total-revenue">Total Revenue:
            <?php echo '₱' .number_format($sum, 2, '.', ','); 
            // wc_price( $report_data_block->total_sales ); ?><br />
        </p>
        <p>Completed Transactions: 
            <?php echo '₱' .number_format($completedctr, 2, '.', ','); ?>
        </p>
        <p>Upcoming Transactions:
            <?php echo '₱' .number_format($upcomingctr, 2, '.', ','); ?>
        </p>
    </div>
</div>

<?php  if ( ! empty( $tables ) ) : ?>
    
    <?php do_action( 'woocommerce_before_account_orders', $has_orders ); $itemsperpage=10;?>
    <?php foreach ( $tables as $table_id => $table ) : ?>
        
        <table id="transactionTable" class="shop_table my_account_appointments <?php echo esc_html( $table_id ) . '_appointments'; ?>">
                        <th class="appointment-id">ID</th>
                        <th>Status</th>
                        <th>Listing</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Client</th>
                        <th>Comments</th>
                        <th>Price</th>
                        <th>Action</th>
            <tbody>
                <?php $rowcount = 0;$count = 0; ?>
                <?php foreach ( $table['appointments'] as $appointment ) : ?>
                    <?php $rowcount++; ?>
                    <?php if ( isset($searchUrl)): ?><!--if user filters -->
                        <?php if ( ($searchUrl === $appointment->get_status())): ?>
                            <?php $date = new DateTime(esc_attr($appointment->get_start_date()));?>  <!--print table rows -->                         
                            <?php $lastcount = 0; ?>
                            <?php foreach ( $table['appointments'] as $appointmentctr ) : ?>
                                <?php $lastdate = new DateTime(esc_attr($appointmentctr->get_start_date())); ?>
                                <?php if($date->format('Y-m-d') == $lastdate->format('Y-m-d')){$lastcount++;} ?>
                            <?php endforeach; ?>

                            <tr class="appointment-row<?php if( $rowcount == $lastcount){
                                echo " last-row";$rowcount = 0;}$count++;?>" id="<?php echo "appointment-page-".$count;?>">
                                
                                <!-- ID -->

                                <td class="appointment-status"><!--DETAILS -->
                                <?php echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">'.$appointment->get_id().'</a>';?>
                                </td>
                                <!-- Status -->
                                <td class="appointment-date"><!--DATE -->
                                    <?php if ( $appointment->get_status() === 'confirmed') :?>
                                <?php echo "<p style='background-color:#ffaf1dc4;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Confirmed";?> 
                                <?php elseif ( $appointment->get_status() === 'paid') :?>
                                    <?php echo "<p style='background-color:#43cf62;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Accepted";?> 
                                <?php elseif ( $appointment->get_status() === 'unpaid') :?>
                                    <?php echo "<p style='background-color:#49a589;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Awaiting Payment";?> 
                                <?php elseif ( $appointment->get_status() === 'complete') :?>
                                    <?php echo "<p style='background-color:grey;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Completed</p>";?> 
                                <?php elseif ( $appointment->get_status() === 'cancelled') :?>
                                    <?php echo "<p style='background-color:#f91717c2;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Refunded/Lapsed</p>";?> 
                                <?php elseif ( $appointment->get_status() === 'pending-confirmation') :?>
                                    <?php echo "<p style='background-color:#337ab7;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Pending Confirmation</p>";?> 
                                <?php else: ?>  
                                    <?php echo esc_html( wc_appointments_get_status_label( $appointment->get_status() ) ); ?>
                                <?php endif; ?>
                                
                                </td>

                                <!-- Listing name -->
                                <td class="appointment-details"><!--DETAILS -->
                                    <?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
                                        <span class="listing-pro-name"><a class="appointment-listing-name">
                                            <?php echo esc_html( $appointment->get_product_name() ); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ( $appointment->get_order() ) : ?>
                                        
                                </td>


                                <!-- Date -->
                                <td class="appointment-status"><!--STATUS -->
                                    <?php if ( $pdate != $date->format('Y-m-d')): ?>
                                        <span class="date-of-month"><?php echo $date->format('F d, Y');?></span><br>
                                        <!-- <span class="day-of-week"><?php echo $date->format('D');?></span> -->
                                    <?php endif; ?>
                                </td>

                                <!-- Time -->
                                <td>
                                    <?php //call shcedule
                                    
                                    $fromstart= new DateTime(esc_attr($appointment->get_start_date())); 
                                    $tostart= new DateTime(esc_attr($appointment->get_end_date())); 
                                    echo $fromstart->format('h:i a').' - '.$tostart->format('h:i a'); 
                    
                                    ?>
                                
                                    
                                    <?php if ( $appointment->get_status() === 'confirmed') :?>
                                        

                                    <?php elseif ( $appointment->get_order() && $appointment->get_status() === 'paid') : ?>
                                        <!-- <?php
                                        global $wpdb;
                                        
                                        $rowcount=0;
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
                                        ?>  -->
                                    
                                    <?php endif;?>
                                </td>
                                <!-- Time -->
                            

                                <!-- Client -->
                                <td>
                                    <?php if($roles[0]==="administrator"):  ?>
                                        <span class="customer-name">
                                        <?php $customer=get_user_by('id', $appointment->customer_id);
                                        echo $customer->first_name." ".$customer->last_name; ?>  
                                        </span>
                                    
                                    <?php endif; ?>
                                    <?php
                                        global $wpdb;
                                        $table_users = $wpdb->prefix . "users";
                                        $table_posts = $wpdb->prefix . "posts";
                                        $selectedProduct = $appointment->get_product_id();
                                        $sql1 = "SELECT $table_users.* FROM wp_posts,$table_users where $table_posts.post_author = $table_users.ID and $table_posts.ID = $selectedProduct";
                                        $results1 = $wpdb->get_results($sql1);

                                        foreach ($results1 as $fld) {
                                            $performerID = $fld->ID;
                                        // echo "<a href=".$appointment->get_order()->get_view_order_url().">".$fld->display_name."</a>";
                                        }
                                        ?>
                                    <?php endif; ?>
                                    
                                </td>
                                <td>
                                    <span class="customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
                                </td>
                                <td>
                                    <?php echo '₱',$appointment->get_order()->total; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($appointment->get_status() === 'pending-confirmation') {
                                        echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">For Approval</a>';
                                    }
                                    else{
                                        echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">View Details</a>';
                                    }
                                    ?>

                                </td>

                                <?php $pdate = $date->format('Y-m-d');?>
                            </tr>
                        <?php endif; ?>
                    <?php else: ?><!--default, no filters -->
                        <?php $date = new DateTime(esc_attr($appointment->get_start_date()));?>  <!--print table rows -->                          
                        <?php $lastcount = 0; ?>
                        <?php foreach ( $table['appointments'] as $appointmentctr ) : ?>
                            <?php $lastdate = new DateTime(esc_attr($appointmentctr->get_start_date())); ?>
                            <?php if($date->format('Y-m-d') == $lastdate->format('Y-m-d')){$lastcount++;} ?>
                        <?php endforeach; ?>

                        <tr class="appointment-row<?php if( $rowcount == $lastcount){echo " last-row";$rowcount = 0;}$count++;?>" id="<?php echo "appointment-page-".$count;?>">
                            
                            <!-- ID -->
                            <td class="appointment-status"><!--DETAILS -->
                                <?php echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">'.$appointment->get_id().'</a>';?>
                            </td>
                            <!-- Status -->
                            <td class="appointment-date"><!--DATE -->
                                <?php if ( $appointment->get_status() === 'confirmed') :?>
                                <?php echo "<p style='background-color:#ffaf1dc4;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Confirmed";?> 
                                <?php elseif ( $appointment->get_status() === 'paid') :?>
                                    <?php echo "<p style='background-color:#43cf62;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Accepted";?> 
                                <?php elseif ( $appointment->get_status() === 'unpaid') :?>
                                    <?php echo "<p style='background-color:#49a589;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Awaiting Payment";?> 
                                <?php elseif ( $appointment->get_status() === 'complete') :?>
                                    <?php echo "<p style='background-color:grey;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Completed</p>";?> 
                                <?php elseif ( $appointment->get_status() === 'cancelled') :?>
                                    <?php echo "<p style='background-color:#f91717c2;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Refunded/Lapsed</p>";?> 
                                <?php elseif ( $appointment->get_status() === 'pending-confirmation') :?>
                                    <?php echo "<p style='background-color:#337ab7;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Pending Confirmation</p>";?> 
                                <?php else: ?>  
                                    <?php echo esc_html( wc_appointments_get_status_label( $appointment->get_status() ) ); ?>
                                <?php endif; ?>
                            
                            </td>

                            <!-- Listing name -->
                            <td class="appointment-details"><!--DETAILS -->
                                <?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
                                    <span class="listing-pro-name"><a class="appointment-listing-name">
                                        <?php echo esc_html( $appointment->get_product_name() ); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $appointment->get_order() ) : ?>
                                    
                            </td>


                            <!-- Date -->
                            <td class="appointment-status"><!--STATUS -->
                                <?php if ( $pdate != $date->format('Y-m-d')): ?>
                                    <span class="date-of-month"><?php echo $date->format('F d, Y');?></span><br>
                                    <!-- <span class="day-of-week"><?php echo $date->format('D');?></span> -->
                                <?php endif; ?>
                            </td>

                            <!-- Time -->
                            <td>
                                <?php //call shcedule
                                
                                $fromstart= new DateTime(esc_attr($appointment->get_start_date())); 
                                $tostart= new DateTime(esc_attr($appointment->get_end_date())); 
                                echo $fromstart->format('h:i a').' - '.$tostart->format('h:i a'); 
                
                                ?>
                            
                                
                                <?php if ( $appointment->get_status() === 'confirmed') :?>
                                    

                                <?php elseif ( $appointment->get_order() && $appointment->get_status() === 'paid') : ?>
                                    <!-- <?php
                                    global $wpdb;
                                    
                                    $rowcount=0;
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
                                    ?>  -->
                                
                                <?php endif;?>
                            </td>
                            <!-- Time -->
                        

                            <!-- Client -->
                            <td>
                                <?php if($roles[0]==="administrator"):  ?>
                                    <span class="customer-name">
                                    <?php $customer=get_user_by('id', $appointment->customer_id);
                                    echo $customer->first_name." ".$customer->last_name; ?>  
                                    </span>
                                
                                <?php endif; ?>
                                <?php
                                    global $wpdb;
                                    $table_users = $wpdb->prefix . "users";
                                    $table_posts = $wpdb->prefix . "posts";
                                    $selectedProduct = $appointment->get_product_id();
                                    $sql1 = "SELECT $table_users.* FROM wp_posts,$table_users where $table_posts.post_author = $table_users.ID and $table_posts.ID = $selectedProduct";
                                    $results1 = $wpdb->get_results($sql1);

                                    foreach ($results1 as $fld) {
                                        $performerID = $fld->ID;
                                    // echo "<a href=".$appointment->get_order()->get_view_order_url().">".$fld->display_name."</a>";
                                    }   
                                    ?>
                                <?php endif; ?>
                                
                            </td>
                            <td>
                                <span class="customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
                            </td>
                            <td>
                                <?php echo '₱',$appointment->get_order()->total; ?>
                            </td>
                            <td>
                                <?php
                                if ($appointment->get_status() === 'pending-confirmation') {
                                    echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">For Approval</a>';
                                }
                                else{
                                    echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">View Details</a>';
                                }
                                ?>

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
<div class="pagination">
    <a href="#page" onclick="PrevPageClick()">&laquo;</a>
    <?php  $pagecount=ceil($count/$itemsperpage);?>
    <?php for($i=0;$i<=$pagecount-1;$i++):?>
        <a id="page-<?echo $i;?>" href="#page" onclick="PageClick(<?php echo $i;?>)"><?php echo $i+1;?></a>
    <?php endfor;?>
    
    <a href="#page" onclick="NextPageClick()">&raquo;</a>
</div>

<script>
    var curpage=0;
    var itemsperpage=10;
    var pagecount=<?php echo $pagecount;?>;
    var count=<?php echo $count;?>;
    function PageClick(x) {
        curpage=x;
        for(var i=1;i<=count;i++){
            document.getElementById("appointment-page-"+i).style.display = "none";
        }
        for(var j=1+curpage*itemsperpage;j<=itemsperpage+(curpage*itemsperpage);j++){
            if(j<=count){
            document.getElementById("appointment-page-"+j).style.display = "table-row";
            }
        }
        for(var k=0;k<=pagecount-1;k++){
            document.getElementById('page-'+k).className = "default";
        }
        document.getElementById('page-'+curpage).className = "active";
    }
    PageClick(0);
    function PrevPageClick() {
        if(curpage-1>=0){
            curpage-=1;
            for(var i=1;i<=count;i++){
                document.getElementById("appointment-page-"+i).style.display = "none";
            }
            for(var j=1+curpage*itemsperpage;j<=itemsperpage+(curpage*itemsperpage);j++){
                if(j<=count){
                document.getElementById("appointment-page-"+j).style.display = "table-row";
                }
            }
            for(var k=0;k<=pagecount-1;k++){
                document.getElementById('page-'+k).className = "default";
            }
            document.getElementById('page-'+curpage).className = "active";
        }
    }
    function NextPageClick() {
        if(curpage+1<=pagecount-1){
            curpage+=1;
            for(var i=1;i<=count;i++){
                document.getElementById("appointment-page-"+i).style.display = "none";
            }
            for(var j=1+curpage*itemsperpage;j<=itemsperpage+(curpage*itemsperpage);j++){
                if(j<=count){
                document.getElementById("appointment-page-"+j).style.display = "table-row";
                }
            }
            for(var k=0;k<=pagecount-1;k++){
                document.getElementById('page-'+k).className = "default";
            }
            document.getElementById('page-'+curpage).className = "active";
        }
    }

    
    document.getElementById('filterLink').href="<?php echo get_home_url(); ?>/professional-manager/transaction-page/?"+statusQuery+orderQuery;

    function changeHref(){
        var statusQuery=document.getElementById('filterStatus').value;
        var orderQuery=document.getElementById('queryOrder').value;
        document.getElementById('filterLink').href="<?php echo get_home_url(); ?>/professional-manager/transaction-page/?"+statusQuery+orderQuery;
    
    }
    function resetHref(){
        document.getElementById('filterLink').href="<?php echo get_home_url(); ?>/professional-manager/transaction-page/";    
    }

    
</script>

            
                <div class="wcfm-clearfix"></div>
            </div>
            <div class="wcfm-clearfix"></div>
        </div>
    
        <div class="wcfm-clearfix"></div>
        <?php
        do_action( 'after_wcfm_upgrade' );
        ?>
    </div>
</div>