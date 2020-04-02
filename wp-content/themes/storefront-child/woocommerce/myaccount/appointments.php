<?php
/**
 * My Appointments
 *
 * Shows customer appointments on the My Account > Appointments page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/appointments.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @version     4.5.7
 * @since       3.4.0
 */

// Exit if accessed directly.

defined( 'ABSPATH' ) || exit;

$count = 0;

if ( ! empty( $tables ) ) : ?>
<style>
    table:not( .has-background ) th {
    background-color: #17A2B8!Important;
    color: white;
    font-family: montserrat!important;
    /*border-bottom: 2px solid black!Important;*/
}

tbody {
    font-family: montserrat!Important;
    color: black!important;
}
</style>
  <?php do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

  <?php foreach ( $tables as $table_id => $table ) : ?>

    <!-- <h2><?php echo esc_html( $table['header'] ); ?></h2> -->

        <table class="shop_table my_account_appointments <?php echo esc_html( $table_id ) . '_appointments'; ?>">
        <thead>
            <!--  <th scope="col" class="appointment-id"><?php esc_html_e( 'ID', 'woocommerce-appointments' ); ?></th> -->
            <th class="appointment-status"><?php esc_html_e( 'Status', 'woocommerce-appointments' ); ?></th>
            <th class="scheduled-product"><?php esc_html_e( 'Listing', 'woocommerce-appointments' ); ?></th>
            <th class="appointment-when"><?php esc_html_e( 'Date', 'woocommerce-appointments' ); ?></th>
            <th class="appointment-duration"><?php esc_html_e( 'Time', 'woocommerce-appointments' ); ?></th>
            <th class="order-number"><?php esc_html_e( 'Pro Name', 'woocommerce-appointments' ); ?></th>
            <th class="notes">My Notes for the Pro</th>      
            <th class="price">Price</th>
            <th class="access-here">Access Here!</th>
            <th class="more-action">More Actions</th>              
            <!-- <th scope="col" class="appointment-actions"><?php esc_html_e( 'Appointment Actions', 'woocommerce-appointments' ); ?></th>
            <th scope="col" class="appointment-actions"><?php esc_html_e( 'Order Actions', 'woocommerce-appointments' ); ?></th>
            <th scope="col" class="appointment-actions"><?php esc_html_e( 'Call Now', 'woocommerce-appointments' ); ?></th> -->
        </thead>
        <tbody>
          <?php foreach ( $table['appointments'] as $appointment ) : ?>
            <?php $count++; ?>
            <tr class="appointment-row<?php if( $rowcount == $lastcount){echo " last-row";$rowcount = 0;}$count++;?>" id="<?php echo "appointment-page-".$count;?>">
              <!-- <td class="appointment-id"><?php echo esc_html( $appointment->get_id() ); ?></td> -->
              <!-- Status -->
                                <!-- <td class="appointment-status"><!--DETAILS -->
                                <!-- <?php //echo '<a href="'.get_home_url().'/professional-manager/appointment-details/'.$appointment->get_id().'">'.$appointment->get_id().'</a>';?> -->
                            <!--</td> -->
                            <!-- Status -->
                            <td class="appointment-date"><!--DATE -->
                                <?php  echo $appointment->get_order()->get_status();?>
                                <?php if ($appointment->get_order()->get_status() === 'pending-confirmation') {
                                    echo "string";
                                }
                                ?>
                                <!-- <?php // if (  $appointment->get_order()->get_status() === 'confirmed') :?>
                                <?php // echo "<p style='background-color:#ffaf1dc4;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Confirmed";?> 
                                <?php // elseif ( $appointment->get_order()->get_status() === 'paid') :?>
                                    <?php // echo "<p style='background-color:#43cf62;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Accepted";?> 
                                <?php // elseif ( $appointment->get_order()->get_status() === 'unpaid') :?>
                                    <?php // echo "<p style='background-color:#49a589;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Awaiting Payment";?> 
                                <?php // elseif ( $appointment->get_order()->get_status() === 'complete') :?>
                                    <?php // echo "<p style='background-color:grey;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Completed</p>";?> 
                                <?php // elseif ( $appointment->get_order()->get_status() === 'cancelled') :?>
                                    <?php // echo "<p style='background-color:#f91717c2;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Refunded/Lapsed</p>";?> 
                                <?php // elseif ( $appointment->get_order()->get_status() === 'pending-confirmation') :?>
                                    <?php // echo "<p style='background-color:#337ab7;color:white;border-radius:50px;padding: 6px;width: 150px;text-align:center;'>Pending Confirmation</p>";?> 
                                <?php // else: ?>  
                                    <?php // echo esc_html( wc_appointments_get_status_label( $appointment->get_order()->get_status() ) ); ?>
                                <?php // endif; ?> -->
                                
                            </td>
              <td class="scheduled-product">
                <?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
                <a href="<?php echo esc_url( get_permalink( $appointment->get_product_id() ) ); ?>">
                  <?php echo esc_html( $appointment->get_product_name() ); ?>
                </a>
              <?php endif; ?>
            </td>
            <td class="appointment-when">
                <?php 
                $date =  new DateTime(esc_attr($appointment->get_start_date())); 
                echo $date->format('F d, Y');

                ?>
                
            </td>   
            <td class="appointment-duration">
                <?php 
                
                $fromstart= new DateTime(esc_attr($appointment->get_start_date())); 
                $tostart= new DateTime(esc_attr($appointment->get_end_date())); 
                echo $fromstart->format('h:i a').' - '.$tostart->format('h:i a'); 

                ?>
            </td>        
            <td class="order-number">
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
                <?php endif; ?>
              </td>

              <td>
                                    <span class="customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
                                </td>
                                <td>
                                    <?php echo '₱',$appointment->get_order()->total; ?>
                                </td>
          
                  <!--Cancel Appointment  -->
               <td class="appointment-actions">
               <div class="container">
  
  <!-- Trigger the modal with a button -->
  <!-- <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Cancel Appointment</button> -->

  <!-- Modal -->
   <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Modal Header</h4>
        </div>
        <div class="modal-body">
         <span class="customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
        </div>

        <div class="modal-footer">
          
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          
      </div>
    </div>
  </div>
</div>
  
</div>
                <!-- The Modal -->
                                    
                                  <!-- Modal content -->
            </td> 
            <?php
            // $appointment->get_order()->get_cancel_order_url()
            $pay_now_url = $appointment->get_order()->get_checkout_payment_url('/checkout/order-pay/{{order_number}}/?pay_for_order=true&key={{order_key}}');
            $order = new WC_Order($order_id);
             echo '<td class="appointment-actions" style="box-sizing: border-box;">
                <select id="myselect" onchange="window.location=this.value">
                    <option value='. $appointment->get_order()->get_view_order_url().'>View</option>
                    <option value='. $pay_now_url.'>Pay</option>
                    <option value="secondoption">Cancel</option>
                   
                </select>
                <!-- Trigger/Open The Modal -->
                <button id="myBtnCancel" data-target="#myModalCancel">Cancel</button>

                <!-- The Modal -->
                <div id="myModalCancel" class="modal">

                  <!-- Modal content -->
                  <div class="modal-content">
                    <span class="close">&times;</span>
                    <p>'.$appointment->get_order()->get_view_order_url().'</p>
                  </div>

                </div>
                ';

                ?>
              <?php

              if ( $appointment->get_order() ) : 

                $actions = wc_get_account_orders_actions( $appointment->get_order());
                if ( ! empty( $actions ) ) {
              foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
                echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name']) . '</a>';
              }
            }

          endif;
          ?>    
            
        </td>

        
         <td class="appointment-actions">
          <?php if ( $appointment->get_order() && $appointment->get_status() === 'paid') : ?>
              <?php
              global $wpdb;
               
              $count=0;
              $postID=get_the_ID(); 
              $options = get_option('VWliveWebcamsOptions');
      // performer (role)
              $current_user = wp_get_current_user();
    // access keys
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

      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<?php do_action( 'woocommerce_before_account_appointments_pagination' ); ?>

<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
  <?php if ( 1 !== $page ) : ?>
    <a href="<?php echo esc_url( wc_get_endpoint_url( $endpoint, $page - 1 ) ); ?>" class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"><?php esc_html_e( 'Previous', 'woocommerce-appointments' ); ?></a>
  <?php endif; ?>

  <?php if ( $count >= $appointments_per_page ) : ?>
    <a href="<?php echo esc_url( wc_get_endpoint_url( $endpoint, $page + 1 ) ); ?>" class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"><?php esc_html_e( 'Next', 'woocommerce-appointments' ); ?></a>
  <?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_appointments_pagination' ); ?>

<?php endforeach; ?>

<?php else : ?>
  <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
    <a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
      <?php esc_html_e( 'Book', 'woocommerce-appointments' ); ?>
    </a>
    <?php esc_html_e( 'No appointments scheduled yet.', 'woocommerce-appointments' ); ?>
  </div>
  <?php endif; ?>

<!DOCTYPE html>
<html>
<head>
 <style>
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
    background-color: #fefefe;
    padding: 20px;
    margin-top: 10%;
    margin-left: 22%;
    border: 1px solid #888;
    width: 73%;
    z-index: 10000000 !important;
}

/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}
 .modal-dialog.modal-sm {
    width: 50%!Important;
    margin-top: 280px!Important;
}
a.woocommerce-button.button.pay {
    background-color: #43cf62;
    border-radius: 5px;
    padding: 8px;
    color: white;
    font-size: 14px;
    display: block;
}
a.woocommerce-button.button.view {
    display: block;
}

h1.cancellation-title {
    padding-bottom: 29px;
}
 </style>
 <script>
function closebutton() {
  document.getElementById("myModal").style.display = "none";
}
function closebutton1() {
  document.getElementById("myModal1").style.display = "none";
}
</script>
<script>
// Get the modal
var modalcancel = document.getElementById("myModalCancel");

// Get the button that opens the modal
var btncancel = document.getElementById("myBtnCancel");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btncancel.onclick = function() {
  modalcancel.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
  <script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
<script>
// Get the modal
var modal1 = document.getElementById("myModal1");

// Get the button that opens the modal
var btn1 = document.getElementById("myBtn1");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn1.onclick = function() {
  modal1.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal1.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
</head>
<body>

</body>
</html>