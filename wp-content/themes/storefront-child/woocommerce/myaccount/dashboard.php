<?php
/**
* My Account Dashboard
*
* Shows the first intro screen on the account dashboard.
*
* This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
*
* HOWEVER, on occasion WooCommerce will need to update template files and you
* (the theme developer) will need to copy the new files to your theme to
* maintain compatibility. We try to do this as little as possible, but it does
* happen. When this occurs the version of the template file will be bumped and
* the readme will list any important changes.
*
* @see         https://docs.woocommerce.com/document/template-structure/
* @package     WooCommerce/Templates
* @version     2.6.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<p class="user"><?php
/* translators: 1: user display name 2: logout url */
printf(
	__( 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ),
	'<strong class="out">' . esc_html( $current_user->display_name ) . '</strong>',
	esc_url( wc_logout_url( wc_get_page_permalink( 'myaccount' ) ) )
);

?>

</p>

<!DOCTYPE html>
<html>
<body>
<style>.woo-wallet-sidebar,
.woo-wallet-transactions-items, .woo-wallet-content-h3{
	display:none;
}
.woo-wallet-price{
	float:center !important;
	margin:0px !important;
}
.woo-wallet-my-wallet-container{
	border:0 !important;
	display:block !important;
	padding:0;
}
.woo-wallet-price{
	font-size: 18px;
	font-weight: 500;
	margin: 0;
	padding: 0; 
	float:none !important;
}
.home-header-item:nth-child(4)>p:nth-child(3){
	display:none;
}
.woo-wallet-content{
	width: 100% !important;
	float: none !important;
	min-height: 0px !important;
	padding: 0px !important;
}
span.woocommerce-Price-amount.amount {
	font-size: 18px;
}
.woo-wallet-content {
	font-size: 0px;
}

/*Grid Style Start*/

.grid-container {
	display: grid;
	grid-template-columns: auto auto auto auto;
	padding: 10px;
}
.grid-item {
	border: 1px solid black;
	padding: 20px;
	font-size: 30px;
	text-align: center;
}

/*Grid Style End*/


</style>

<div class="my-account-container">
<div class="home-grid-container">

<div class="home-container">
<div class="home-header-container">
<div class="home-header-item">
<p class="home-week-label">This Week</p>
<p class="home-header-label">
<?php
$monday = strtotime('monday this week');
$sunday = strtotime('sunday this week');
echo $this_week_sd = date("M d",$monday)." - ";
echo $this_week_ed = date("M d",$sunday);
?> 
</p>
</div>


<?php /**FETCH APPOINTMENT DATA */ 
$user_id = get_current_user_id();
$user_roles = $user_meta->roles;

$user = wp_get_current_user();
 $roles = ( array ) $user->roles;
/**FILTER APPOINTMENT DATA */
if($roles[0]==="administrator"){
    $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 10 );
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
 $appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 10 );
$this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
    $user_id,
    apply_filters(
    'woocommerce_appointments_my_appointments_today_query_args',
    array(
        'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
        'order'       => 'DESC',
		'date_after'  => strtotime('last monday', strtotime('tomorrow')),
		'date_before' => strtotime( 'next monday', current_time( 'timestamp' ) ),
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
				<?php $pendingpayment++;$requestsctr++; ?>
			<?php endif; ?>
			<?php if ( 'pending-confirmation' === $appointment->get_status()): ?>
				<?php $pendingconfirmation++;$requestsctr++; ?>
			<?php endif; ?>
		<?php endforeach;?>
	<?php endforeach; ?>
<?php endif; ?>

<div class="home-header-item">
<p class="home-upcoming-appointments my-account-bold">
<?php echo $paidctr; ?>
</p>
<p class="home-header-label">Upcoming Appointments</p>
</div>
<div class="home-header-item  tooltip">
<span class="tooltiptext">
<div class="tooltiptext-container">
Pending Confirmation: 
<a class="plain-link" href="https://gigant.com.ph/my-account/appointments/">
<span class="pending-confirmation"><?php echo $pendingconfirmation; ?></span>
</a><br> 
Pending Your Payment: 
<a class="plain-link" href="https://gigant.com.ph/my-account/orders/">
<span class="pending-payment my-account-bold"><?php if($pendingpayment>0){echo $pendingpayment."<span style='color:red;'>!</span>";}else{echo $pendingpayment;} ?></span>
</a>
</div>
</span>
<p class="home-appointment-requests my-account-bold"><?php echo $requestsctr; ?></p>
<p class="home-header-label">Appointment Requests</p>
</div>
<div class="home-header-item">
<p class="home-wallet my-account-bold"><?php echo do_shortcode( '[woo-wallet]' ); ?></p>
<p class="home-header-label"><a href="https://gigant.com.ph/my-account/woo-wallet/">My Wallet</a></p>
</div>
</div>
<div class="home-body-container">
<div class="home-body-item">
<p class="home-body-label">THIS WEEK'S SCHEDULE</p>
<div class="schedule-container">





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
								<?php  if($roles[0]==="administrator"):  ?>
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
								
								?>
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
<div class="home-body-item">
<p class="home-body-label">NOTIFICATIONS</p>
<div class="notification-container">
<?php do_action( 'after_wcfm_dashboard_product_stats' ); ?>
</div>

</div>
</div>
</div>

</div>
</div>
</body>
</html>

<?php
/**
* My Account dashboard.
*
* @since 2.6.0
*/
do_action( 'woocommerce_account_dashboard' );

/**
* Deprecated woocommerce_before_my_account action.
*
* @deprecated 2.6.0
*/
do_action( 'woocommerce_before_my_account' );

/**
* Deprecated woocommerce_after_my_account action.
*
* @deprecated 2.6.0
*/
do_action( 'woocommerce_after_my_account' );

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */