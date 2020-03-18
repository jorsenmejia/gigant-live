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
<div class="home-header-item">
<p class="home-upcoming-appointments my-account-bold">
<?php 
global $wpdb;
$posts_table = $wpdb->prefix.'posts';
$query = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'confirmed'";
$query1 = "SELECT COUNT(*) AS count FROM ".$wpdb->prefix."posts WHERE post_type LIKE 'wc_appointment' AND post_status LIKE 'pending-confirmation'";
$result = $wpdb->get_results($query); //confirmed
$result1 = $wpdb->get_results($query1); //pending
//var_dump($result);

//echo $result[0]->count; // This line will disaply the number 
echo $result[0]->count;

?>

</p>
<p class="home-header-label">Upcoming Appointments</p>
</div>
<div class="home-header-item  tooltip">
<span class="tooltiptext">
<div class="tooltiptext-container">
Pending Consultant Confirmation: 
<a class="plain-link" href="https://gigant.com.ph/my-account/appointments/">
<span class="pending-confirmation">1</span>
</a><br> 
Pending Your Payment: 
<a class="plain-link" href="https://gigant.com.ph/my-account/orders/">
<span class="pending-payment my-account-bold">2</span>
</a>
</div>
</span>
<p class="home-appointment-requests my-account-bold">3</p>
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


<?php /**FETCH APPOINTMENT DATA */ 
$user_id = get_current_user_id();


/**FILTER APPOINTMENT DATA */
$appointments_per_page = apply_filters( 'woocommerce_appointments_my_appointments_per_page', 10 );
$this_week_appointments = WC_Appointment_Data_Store::get_appointments_for_user(
	$user_id,
	apply_filters(
	'woocommerce_appointments_my_appointments_today_query_args',
	array(
		'order_by'    => apply_filters( 'woocommerce_appointments_my_appointments_today_order_by', 'start_date' ),
		'order'       => 'ASC',
		'date_after'  => strtotime( 'monday this week'),
		'date_before' => strtotime( 'next monday', current_time( 'timestamp' ) ),
		'offset'      => ( $current_page - 1 + 1 ) * $appointments_per_page,
		'limit'       => $appointments_per_page,
		)
		)
);
		
/**ASSIGN APPOINTMENTS TO TABLE */		
$tables = array();
if ( ! empty( $this_week_appointments ) ) {
	$tables['today'] = array(
		'header'       => __( '', 'woocommerce-appointments' ),
		'appointments' => $this_week_appointments,
	);
}
if ( ! empty( $upcoming_appointments ) ) {
	$tables['upcoming'] = array(
		'header'       => __( 'Upcoming', 'woocommerce-appointments' ),
		'appointments' => $upcoming_appointments,
	);
}
/**FETCH APPOINTMENT DATA END */ ?>


<?php $count = 0; if ( ! empty( $tables ) ) : ?>
	<?php do_action( 'woocommerce_before_account_orders', $has_orders ); ?>
	<?php foreach ( $tables as $table_id => $table ) : ?>
		<table class="shop_table my_account_appointments <?php echo esc_html( $table_id ) . '_appointments'; ?>">
			<!-- <thead>
				<tr> TABLE HEADER DISABLED
					<th scope="col" class="appointment-date"><?php esc_html_e( 'Date', 'woocommerce-appointments' ); ?></th>
					<th scope="col" class="appointment-details"><?php esc_html_e( 'Details', 'woocommerce-appointments' ); ?></th>
					<th scope="col" class="appointment-status"><?php esc_html_e( 'Status', 'woocommerce-appointments' ); ?></th>
					<th scope="col" class="appointment-actions"><?php esc_html_e( 'Appointment Actions', 'woocommerce-appointments' ); ?></th>
				</tr>
			</thead> -->
			<tbody>
			<?php foreach ( $table['appointments'] as $appointment ) : ?>
			<?php $count++; ?>
				<tr>
					<td class="appointment-date"><!--DATE -->
						<?php $date =  new DateTime(esc_attr($appointment->get_start_date()));?>
						<span class="date-of-month"><?php echo $date->format('j');?></span><br>
						<span class="day-of-week"><?php echo $date->format('D');?></span>
					</td>
					<td class="appointment-details"><!--DETAILS -->
						<?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
							<a class="appointment-listing-name" href="<?php echo $appointment->get_order()->get_view_order_url(); ?>">
								<?php echo esc_html( $appointment->get_product_name() ); ?>
							</a>
						<?php endif; ?> with 
						<?php if ( $appointment->get_order() ) : ?>
							<?php if ( 'pending-confirmation' === $appointment->get_status() ) : ?>
								<?php
								global $wpdb;
								$performerID='';
								$table_users = $wpdb->prefix . "users";
								$table_posts = $wpdb->prefix . "posts";
								$selectedProduct = $appointment->get_product_id();
								
								$sql1 = "SELECT $table_users.* FROM wp_posts,$table_users where $table_posts.post_author = $table_users.ID and $table_posts.ID = $selectedProduct";
								$results1 = $wpdb->get_results($sql1);
								
								foreach ($results1 as $fld) {
								$performerID = $fld->ID;
								echo $fld->display_name;
								}
								?>
							<?php else : ?>
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
							<?php endif; ?>
						<?php endif; ?><br>
						<?php echo $appointment->get_order()->customer_note; ?>
					</td>
					<td class="appointment-status"><!--STATUS -->
						<?php echo esc_html( wc_appointments_get_status_label( $appointment->get_status() ) ); ?>
					</td>
					<td class="appointment-actions"><!--ACTIONS AND TIME UNTIL CALL -->
					<?php
					$now = date("Y-m-d-H-i-s");
					list ($cy, $cm, $cd, $ch, $ci, $cs) = explode("-", $now);
					$c_timestamp = mktime($ch,$ci,$cs,$cm,$cd,$cy);

					$future = date("Y-m-d-H-i-s", mktime($ch+7,$ci+6,$cs+3,$cm,$cd+70,$cy));
					list ($fy, $fm, $fd, $fh, $fi, $fs) = explode("-", $future);
					$f_timestamp = mktime($fh,$fi,$fs,$fm,$fd,$fy);

					$days = $hours = $minutes = $seconds = 0;
					while($f_timestamp - $c_timestamp >= 60*60*24)
					{
							$days++;
							$f_timestamp = $f_timestamp - 60*60*24;
					}
					while($f_timestamp - $c_timestamp >= 60*60)
					{
							$hours++;
							$f_timestamp = $f_timestamp - 60*60;
					}
					while($f_timestamp - $c_timestamp >= 60)
					{
							$minutes++;
							$f_timestamp = $f_timestamp - 60;
					}
					while($f_timestamp - $c_timestamp > 0)
					{
							$seconds++;
							$f_timestamp--;
					}
					echo "\n$days Days - $hours Hours - $minutes Minutes - $seconds Seconds\n";
					?>
					</td>
				</tr>
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
<p class="home-body-label">THIS WEEK'S ACTIVITY</p>
<div class="notification-container">

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