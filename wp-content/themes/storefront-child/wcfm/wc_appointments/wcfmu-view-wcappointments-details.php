    <?php
    /**
     * WCFM plugin view
     * /home/gigant/public_html/wp-content/plugins/wc-frontend-manager-ultimate/views/wc_appointments/wcfmu-view-wcappointments-details.php
     * WCFM Appointments Details View
     *
     * @author      WC Lovers
     * @package     wcfmu/view
     * @version   2.4.0
     */
     
    global $wp, $WCFM, $WCFMu, $theappointment, $wpdb;

    if( !current_user_can( 'manage_appointments' ) || !apply_filters( 'wcfm_is_allow_appointment_list', true ) ) {
        wcfm_restriction_message_show( "Appointments" );
        return;
    }

    if ( ! is_object( $theappointment ) ) {
        if( isset( $wp->query_vars['wcfm-appointments-details'] ) && !empty( $wp->query_vars['wcfm-appointments-details'] ) ) {
            $theappointment = get_wc_appointment( $wp->query_vars['wcfm-appointments-details'] );
        }
    }

    $appointment_id = $wp->query_vars['wcfm-appointments-details'];
    $post = get_post($appointment_id);
    $appointment = new WC_Appointment( $post->ID );
    $order             = $appointment->get_order();
    $product_id        = $appointment->get_product_id( 'edit' );
    $customer_id       = $appointment->get_customer_id( 'edit' );
    $product           = $appointment->get_product( $product_id );
    $customer          = $appointment->get_customer();
    $statuses          = array_unique( array_merge( get_wc_appointment_statuses( null, true ), get_wc_appointment_statuses( 'user', true ), get_wc_appointment_statuses( 'cancel', true ) ) );

    do_action( 'before_wcfm_appointments_details' );
    ?>
    <?php
    if(isset($_POST['submitreason'])){
    global $wpdb;
    
    $table=$wpdb->prefix.'reason';
    $id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
    $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
    $reason = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
    $appointment = new WC_Appointment( $appointment_id );
    $appointment_id = $wp->query_vars['wcfm-appointments-details'];
    $post_data=array(
        'id' => $id,
        'post_id' => $post_id,
        'reason' => $reason,
    );
    $wpdb->insert( $table, $post_data,array('%s','%s','%s'));
    $page_url = home_url( $wp->request );
    $redirect_to = add_query_arg($page_url);
    wp_safe_redirect( $redirect_to );
    exit;
    }
    ?>

      <div class="collapse wcfm-collapse" id="wcfm_appointment_details">

      <div class="wcfm-page-headig">
            <span class="wcfmfa fa-calendar-check"></span>
            <span class="wcfm-page-heading-text"><?php _e( 'Appointment Details', 'wc-frontend-manager-ultimate' ); ?></span>
            <?php do_action( 'wcfm_page_heading' ); ?>
        </div>
        <div class="wcfm-collapse-content">
            <div id="wcfm_page_load"></div>
            
            <div class="wcfm-container wcfm-top-element-container">
                <h2><?php _e( 'Appointment #', 'wc-frontend-manager-ultimate' ); echo $appointment_id; ?></h2>
                
                <?php
                if( $allow_wp_admin_view = apply_filters( 'wcfm_allow_wp_admin_view', true ) ) {
                    ?>
                    <a target="_blank" class="wcfm_wp_admin_view text_tip" href="<?php echo admin_url('post.php?post='.$appointment_id.'&action=edit'); ?>" data-tip="<?php _e( 'WP Admin View', 'wc-frontend-manager-ultimate' ); ?>"><span class="fab fa-wordpress fa-wordpress-simple"></span></a>
                    <?php
                }
                
                if( $wcfm_is_allow_appointment_calendar = apply_filters( 'wcfm_is_allow_appointment_calendar', true ) ) {
                    echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_appointments_calendar_url().'" data-tip="'. __('Calendar View', 'wc-frontend-manager-ultimate') .'"><span class="wcfmfa fa-calendar-alt"></span></a>';
                }
                echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_appointments_url().'" data-tip="' . __( 'Appointments List', 'wc-frontend-manager-ultimate' ) . '"><span class="wcfmfa fa-calendar"></span></a>';
                if( $wcfm_is_allow_manage_staff = apply_filters( 'wcfm_is_allow_manage_staff', true ) ) {
                    echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_appointments_staffs_url().'" data-tip="' . __( 'Manage Staff', 'wc-frontend-manager-ultimate' ) . '"><span class="wcfmfa fa-user"></span></a>';
                }
                
                if( $has_new = apply_filters( 'wcfm_add_new_product_sub_menu', true ) ) {
                    echo '<a class="add_new_wcfm_ele_dashboard text_tip" href="'.get_wcfm_edit_product_url().'" data-tip="' . __('Create Appointmentable', 'wc-frontend-manager-ultimate') . '"><span class="wcfmfa fa-cube"></span></a>';
                }
                ?>
                <div class="wcfm_clearfix"></div>
            </div>
          <div class="wcfm-clearfix"></div><br />
          

          <div class="full-details">
            <div class="informaiton-details">
                <div class="infos">
                <p class="appointment-legend">Appointment details</p>

                <p class="appointment-cancellation">Cancellation Policy: 
                     <?php
                $order = new WC_Order( $order_id );
                 $items = $order->get_items();
                 foreach ( $items as $item ) {
                      $product_id = $item['product_id']; 
                     }
                // var_dump(get_field('policy',  $product_id)); 
               // if (get_field('policy') == 'flexible') {
               //  // code to run if the above is true
               //  echo do_shortcode(get_field( 'flexible' ));
               //  } else if (get_field('policy') == 'moderate') {
               //      // more code
               //      echo do_shortcode(get_field( 'moderate' ));
               //  }
               //  else if (get_field('policy') == 'strict') {
               //      // more code
               //      echo do_shortcode(get_field( 'strict' ));
               //  }


                if (get_field('policy',  $product_id) == 'flexible') {
                echo "<p style='color: #43cf62;width: auto;font-weight: bold;font-size: 22px;text-align:center;padding-left: 5px;'</p>Flexible</p>";
                }
                else if (get_field('policy',  $product_id) == 'moderate'){
                    echo "<p style='color: #ffaf1dc4;width: auto;font-weight: bold;font-size: 22px;text-align:center;padding-left: 5px;'</p>Moderate</p>";
                }
                else if (get_field('policy',  $product_id) == 'strict') {
                    echo "<p style='color: #f91717c2;width: auto;font-weight: bold;font-size: 22px;text-align:center;padding-left: 5px;'</p>Strict</p>";                }
               ?>
                </p>
                                <!-- Trigger/Open The Modal -->
                <!-- Trigger/Open The Modal -->
                <button id="myBtn1" class="policy-details">(View Policy Details)</button>

                <!-- The Modal -->
                <div id="myModal1" class="modal">

                  <!-- Modal content -->
                  <div class="modal-content">
                    
                    <p>
                        <?php
                            echo "<h1 class='cancellation-title'>Cancellation Policy</h1><div class='policy-modal'>";
                                        if (get_field('policy',  $product_id) == 'flexible') {
                                        echo the_field('flexible',  $product_id); 
                                        }
                                        else if (get_field('policy',  $product_id) == 'moderate'){
                                            echo the_field('moderate',  $product_id); 
                                        }
                                        else if (get_field('policy',  $product_id) == 'strict') {
                                            echo the_field('strict',  $product_id);              }
                                    
                                        echo "</div>";
                        ?>
                        <button class='transac-buttons' onclick='closebutton1()'>Close</button>
                    </p>
                  </div>

                </div>

                
                <!--<p class="WooCommerce"><?php // the_field('flexible',  $product_id); ?><p>      -->
                <?php 
                //  }
                ?>
                </div>

                <div class="basic-infos">

                <p class="name-info">
                    <?php
                    echo apply_filters( 'wcfm_wca_customer_name_display',  $customer->full_name, $customer );
                    ?>
                </p>
                    <p class="request"> Sent you an appointment request!</p>
                    
                </div>
                <div class="stats"><p><b>Status:</b>
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
                 </p></div>
              </div>

              

            <div class="appointment-details">
                <div class="appointment-time">
                    <?php
                    $product_post = get_post($product->get_ID());
                            echo '<a class="wcfm_dashboard_item_title" href="' . get_permalink($product->get_ID()) . '" target="_blank">' . $product_post->post_title . '</a> | ';
                            echo date_i18n( wc_date_format(), $appointment->get_start( 'edit' ) ) . ' | '; 
                            echo "Time: " . date_i18n( wc_time_format(), $appointment->get_start( 'edit' ) ) .' - '. date_i18n( wc_time_format(), $appointment->get_end( 'edit' ) ) . ' - ₱',$appointment->get_order()->total  ;
                    ?>

                </div>
            </div>
          </div>
          <div class="notes">
          <div class="customer-notes"><?php echo $appointment->get_order()->customer_note; ?>            
          </div>

          </div>
          <div><hr></div>
          <?php $appointment_status = $appointment->get_status(); 
           $id = $user->ID;
           $appointment = new WC_Appointment( $appointment_id );
                    if ($appointment_status == "pending-confirmation") {
                        echo " 
                            <div class='transaction-buttons'>
                            <div class='btn-position'>
                                  <button id='accept' class='transac-buttons' onclick='transacUpdateStatus(".$appointment_id.")'>Accept</button>
                            </div>
                            <div class='btn-position'>
                                  <button class='transac-buttons' onclick='transacDeleteStatus(".$appointment_id.")'>Decline & Respond</button>
                            </div>
                            <div class='btn-position'>
                                  <button class='transac-buttons'>Refer & Respond</button>
                            </div>
                            <div class='btn-position'>
                                  <button class='transac-buttons'>Report for Abuse</button>
                            </div>
                        </div>";
                        }
                    else if ($appointment_status == "cancelled") {
                        echo "";
                    }
                    else if ($appointment_status == "completed") {
                        echo "";
                    }
                    else{
                        echo " 
                            <div class='transaction-buttons'>
                            <div class='btn-position'>
                                  <button class='transac-buttons'>Cancel & Refer</button>
                            </div>
                            <div class='btn-position'>

                                  <button id='myBtn' class='transac-buttons' onclick='transacDeleteStatus(".$appointment_id.")'>Cancel Appointment</button>
                                  <!-- The Modal -->
                                    <div id='myModal' class='modal'>
                                  <!-- Modal content -->
                                      <div class='modal-content'>";
                                       
                                       echo "<h1 class='cancellation-title'>Cancellation Policy</h1><div class='policy-modal'>";
                                        echo "<form action='' method ='POST' class='needs-validation' novalidate>
                                        <p>Please choose a reason for cancelling the appointment below:</p>
                                        <select id='reason' name='reason' required>
                                          <option value='I have an Emergency'>I have an Emergency</option>
                                          <option value='Option 1'>Option 1</option>
                                          <option value='Option 2'>Option 2</option>
                                          <option value='Option 3'>Option 3</option>
                                        </select>
                                        <input type='text' class='modal_postid'  id='validationCustom03' placeholder='Remarks' value=' ".$appointment_id."' name='post_id' required></input>
                                        <div class = 'policy-rules'>";
                                        
                                        if (get_field('policy',  $product_id) == 'flexible') {
                                        echo the_field('flexible',  $product_id); 
                                        }
                                        else if (get_field('policy',  $product_id) == 'moderate'){
                                            echo the_field('moderate',  $product_id); 
                                        }
                                        else if (get_field('policy',  $product_id) == 'strict') {
                                            echo the_field('strict',  $product_id);              }
                                        
                                        echo "
                                        </div></div><h1 class='confirmation'>Are you sure you want to cancel this appointment?</h1>
                                        <button name='submitreason' id='saveAppointment' class='transac-buttons' onclick='transacDeleteStatus(".$appointment_id.")'>Yes</button>
                                        <button class='transac-buttons' onclick='closebutton()'>No</button>
                                        </form>
                                      </div>

                                    </div>
                            </div>
                            
                        </div>";
                    }   
                    // echo $appointment_status;

                    ?>



                    <?php 
                    $addedtime = +60*60; // 10:09 + 2 hours;
                
                    $timestamp = strtotime($time) + $addedtime; 
                    $total = date('H:i', $timestamp);
                    

                    
                    ?>

                    <!-- <p id="tag" hidden="hidden"><?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), $appointment->get_date_created() ); ?></p>

                    <p id="timeline"><?php echo $datesum = date('h:i a', strtotime($total)); ?></p>
                    <p class="respond-label">Countdown <p class="respond-label" id="timely"></p>

                    <div class="respondtimer"><p class="respond-label">Respond within <p class="respond-label" id="time"></p></div>
 -->
                    <!DOCTYPE html>
<html>
<head>
  <style>
select#reason {
    position: relative;
    margin-bottom: 4%;
}
input#validationCustom03 {
    display: none;
}
form.needs-validation {
    text-align: left;
}
    .stats p {
    display: inline;
}
    .policy-details:hover {
    background-color: transparent;
}
    .policy-details {
    background-color: #eeeeee00;
    border-color: #eeeeee;
    color: #17a2b8;
    padding: 0px;
    padding-left: 10px;
}
  .notes {
    background-color: white;
    padding-left: 15px;
    height: 484px;
    width: 100%;
}
#wcfm-main-contentainer .wcfm-collapse {
    overflow: hidden;
    width: auto;
    flex: 5 auto;
    vertical-align: top;
    background-color: #eceef2;
    visibility: visible!important;
    -moz-border-radius: 0 3px 3px 0;
    -webkit-border-radius: 0 3px 3px 0;
    border-radius: 0 3px 3px 0;
    margin-left: auto;
    margin-right: auto;
    padding-bottom: 3px;
}
.transaction-buttons {
    background-color: #1c2b36;
    padding-bottom: 50px;
    padding-top: 50px;
    text-align: center;
}
.customer-notes {
    background: #eceef2;
    width: 99%;
}
.customer-notes {
    background: #eceef2;
    width: 99%;
    height: 97%;
    padding: 10px;
}
p.respond-time2 {
    width: 100%;
}
.appointment-status-div-timer {
    display: flex;
    width: 100%;
    padding-right: 20px;
}
.appointment-content {
    width: 100%;
}
p.right-content {
    text-align: right;
}
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
    border: 1px solid #888;
    width: 80%;
    margin-left: 19%;
    margin-top: 10%;
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
<?php 
      $appointment = new WC_Appointment( $appointment_id ); 
      // echo $appointment;
      ?>
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script type='text/javascript'>
    // var secondsBeforeExpire = 10;
    var appointment_datetime = new Date("<?php echo date('Y-m-d H:i:s',$appointment->get_date_created())?>");
    console.log('date_created', appointment_datetime);
    
    // This will trigger your timer to begin

    var timer = setInterval(function(){
      var now = new Date();
      var difference = appointment_datetime.getTime()+(1*24*60*60*1000) - now;
      var hour_difference = Math.floor(difference /1000/60/60);
      difference -= hour_difference*1000*60*60;
      var minute_difference = Math.floor(difference/1000/60);
      difference -= minute_difference*1000*60; 
      var second_difference = Math.floor(difference/1000);

      // For 5 Mins sample

      // console.log(hour_difference);
      // console.log(now);
        // If the timer has expired, disable your button and stop the timer
        if(appointment_datetime.getTime()+(1*24*60*60*1000) <= now){
            
            clearInterval(timer);
            $("#accept").prop('disabled',true);
            $('#time-remaining').text(`Your Appointment is Expired`) ;
        }
        // Otherwise the timer should tick and display the results
        else{
            // Decrement your time remaining
            // secondsBeforeExpire--;
            // $("#time-remaining").text(secondsBeforeExpire); 
            $('#time-remaining').text(`Respond within ${hour_difference} hour/s ${minute_difference} minute/s and ${second_difference} second/s`) ;    
        }

    },1000);
  </script>
  <script>
    
  </script>
</head>
<body>
    
</body>
</html>
    <div class="appointment-stats">
    <!-- <p class="respond-time2">Respond within <span id='time-remaining'></span> seconds<p/> -->

                    <!-- <html>
                    <body>
                    <p id="demo"></p>

                    <script>
                      var time = jQuery('#tag').html();
                      document.getElementById('time').innerHTML= time;
                    </script>   
                    
                    <script> 
                    var countDownDate = jQuery('#timeline').html();
                    var breakdown = document.getElementById('timely').innerHTML= countDownDate;
                    var deadline = new Date(countDownDate).getTime();

                    var x = setInterval(function() { 
                    var now = new Date().getTime(); 
                    var t = deadline - now; 
                    var days = Math.floor(t / (1000 * 60 * 60 * 24)); 
                    var hours = Math.floor((t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60)); 
                    var minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60)); 
                    var seconds = Math.floor((t % (1000 * 60)) / 1000); 
                    document.getElementById("timely").innerHTML = days + "d " 
                    + hours + "h " + minutes + "m " + seconds + "s "; 
                        if (t < 0) { 
                            clearInterval(x); 
                            document.getElementById("timely").innerHTML = "EXPIRED"; 
                        } 
                    }, 1000); 
                    </script> 
                    </body>
                    </html> -->
            


            
            <div class="appointment-status-div-timer">
                <div class="appointment-content"><p class="left-content">
                    <?php
                        if ($appointment_status == "pending-confirmation") {
                                    echo "<p>Appointment Created:";
                                    echo date_i18n( wc_date_format() . ' @' . wc_time_format(), $appointment->get_date_created() );
                                    echo "<br><span id='time-remaining'></span>";
                                
                                    }
                        ?>
                </p></div>
                <div class="appointment-content"><p class="right-content">
                    <?php
                    echo apply_filters( 'wcfm_wca_customer_name_display',  $customer->full_name, $customer );
                     ?>
                                &nbspwas referred to you by Atty. Ramon
                </p></div>
            </div>
          <?php do_action( 'begin_wcfm_appointments_details' ); ?>
            
            <!-- collapsible
            <div class="page_collapsible appointments_details_general" id="wcfm_general_options">
                <?php _e('Overview', 'wc-frontend-manager-ultimate'); ?><span></span>
            </div>
            <div class="wcfm-container">
                <div id="appointments_details_general_expander" class="wcfm-content">
                
                    <?php if ( $order ) { do_action( 'begin_wcfm_appointments_details_overview', $appointment_id, $order->get_order_number() ); } ?>
                    
                    <p class="form-field form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'Appointment Created:', 'wc-frontend-manager-ultimate' ) ?></strong></span>
                        <?php echo date_i18n( wc_date_format() . ' @' . wc_time_format(), $appointment->get_date_created() ); ?>
                    </p>
                    
                    <p class="form-field form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'Order Number:', 'wc-frontend-manager-ultimate' ) ?></strong></span>
                        <?php
                        if ( $order ) {
                            if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $order->get_order_number() ) ) {
                                echo '<span class="appointment-orderno"><a href="' . get_wcfm_view_order_url( $order->get_order_number(), $order ) . '">#' . $order->get_order_number() . '</a></span> &ndash; ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '(' . date_i18n( wc_date_format(), strtotime( $order->get_date_created() ) ) . ')';
                            } else {
                                echo '<span class="appointment-orderno">#' . $order->get_order_number() . ' - ' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </p>

                    <?php if( apply_filters( 'wcfm_is_allow_appointment_status_update', true ) ) { ?>
                        <div id="wcfm_appointment_status_update_wrapper" class="wcfm_appointment_status_update_wrapper">
                            <p class="form-field form-field-wide">
                                <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'Appointment Status:', 'woocommerce-appointments' ); ?></strong></span>
                                <select id="wcfm_appointment_status" name="appointment_status">
                                    <?php
                                        foreach ( $statuses as $key => $value ) {
                                            echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $post->post_status, false ) . '>' . esc_html__( $value, 'woocommerce-appointments' ) . '</option>';
                                        }

                                    ?>
                                </select>
                                <button class="wcfm_modify_appointment_status button" id="wcfm_modify_appointment_status" data-appointmentid="<?php echo $appointment_id; ?>"><?php _e( 'Update', 'wc-frontend-manager-ultimate' ); ?></button>
                            </p>
                            <div class="wcfm-message" tabindex="-1"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="wcfm_clearfix"></div>
            <br /> -->
            <!-- collapsible End -->
            
            <!-- collapsible -->
            <!-- <div class="page_collapsible appointments_details_appointment" id="wcfm_appointment_options">
                <?php _e('Appointment', 'wc-frontend-manager-ultimate'); ?><span></span>
            </div>
            <div class="wcfm-container">
                <div id="appointments_details_appointment_expander" class="wcfm-content">
                    
                    <p class="form-field appointmented_product form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'Product:', 'woocommerce-appointments' ) ?></strong></span>
                        <?php
                        
                        if ( $product ) {
                            $product_post = get_post($product->get_ID());
                            echo '<a class="wcfm_dashboard_item_title" href="' . get_permalink($product->get_ID()) . '" target="_blank">' . $product_post->post_title . '</a>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </p>
                    
                    <p class="form-field appointmented_product_quantity form-field-wide">
                      <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php echo apply_filters( 'wcfm_appointments_qty_label', __( 'Quantity', 'woocommerce-appointments' ) ); ?>:</strong></span>
                      <?php echo $appointment->get_qty(); ?>
                    </p>
                      
                    <?php 
                    $product_staffs     = $appointment->get_staff_ids( 'edit' );
                    $product_staffs  = ! is_array( $product_staffs ) ? array( $product_staffs ) : '';
                    if( $product_staffs ) { 
                    ?>
                        <p class="form-field appointmented_staff form-field-wide">
                            <span for="appointment_date" class="wcfm-title wcfm_title"></strong><?php _e( 'Staff:', 'woocommerce-appointments' ) ?></strong></span>
                            <?php
                              foreach ( $product_staffs as $staff_id ) {
                                $staff            = new WC_Product_Appointment_Staff( $staff_id );
                                echo $staff->display_name;
                                }
                            ?>
                        </p>
                    <?php } ?>
                    
                    <p class="form-field appointment_date_start form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'Start Date:', 'woocommerce-appointments' ) ?></strong></span>
                        <?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), $appointment->get_start( 'edit' ) ); ?>
                    </p>
                    
                    <p class="form-field appointment_date_end form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'End Date:', 'woocommerce-appointments' ) ?></strong></span>
                        <?php echo date_i18n( wc_date_format() . ' ' . wc_time_format(), $appointment->get_end( 'edit' ) ); ?>
                    </p>
                    <p class="form-field appointment_date_duration form-field-wide">
                        <span for="appointment_date" class="wcfm-title wcfm_title"><strong><?php _e( 'All day', 'wc-frontend-manager-ultimate' ) ?>:</strong></span>
                        <?php echo $appointment->get_all_day( 'edit' ) ? __( 'Yes', 'woocommerce-appointments' ) : __( 'No', 'woocommerce-appointments' ); ?>
                    </p>
                    
                    <?php if ( $appointment_addons = $appointment->get_addons() ) { ?>
                        <div class="appointment_data_container appointment_data_addons">
                            <div class="wcfm_clearfix"></div><br/>
                            <div class="appointment_data_column data_column_wide">
                                <h2><?php esc_html_e( 'Add-ons', 'woocommerce-appointments' ); ?></h2>
                                <div class="wcfm_clearfix"></div>
                                <?php echo $appointment_addons; ?>
                            </div>
                            <div class="wcfm_clearfix"></div>
                        </div>
                    <?php } ?>
             </div>
            </div>
            <div class="wcfm_clearfix"></div>
            <br /> -->
            <!-- collapsible End -->
            
            <?php // if ( $order ) { do_action( 'before_wcfm_appointments_customer_details', $appointment_id, $order->get_order_number() ); } ?>
            
            <!-- collapsible -->
            <!-- <div class="page_collapsible appointments_details_customer" id="wcfm_customer_options">
                <?php // _e('Customer', 'woocommerce-appointments'); ?><span></span>
            </div>
            <div class="wcfm-container">
                <div id="appointments_details_customer_expander" class="wcfm-content">
                    <?php
                    $order_id    = $post->post_parent;
                    $has_data    = false;
            
                    echo '<table class="appointment-customer-details">';
            
                    if ( $customer && $customer->full_name ) {
                        echo '<tr>';
                            echo '<th><span for="appointment_date" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Name:', 'woocommerce-appointments' ) . '</strong></span></th>';
                            echo '<td>';
                            if( apply_filters( 'wcfm_is_allow_view_customer', true ) ) {
                                printf( __( apply_filters( 'wcfm_wca_customer_name_display',  '%s' . $customer->full_name . '%s', $customer ) ), '<a target="_blank" href="' . get_wcfm_customers_details_url($customer->user_id) . '" class="wcfm_dashboard_item_title">', '</a>' );
                            } else {
                                echo apply_filters( 'wcfm_wca_customer_name_display',  $customer->full_name, $customer );
                            }
                            echo '</td>';
                        echo '</tr>';
                        
                        if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
                            echo '<tr>';
                                echo '<th><span for="appointment_date" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Email:', 'woocommerce-appointments' ) . '</strong></span></th>';
                                echo '<td>';
                                echo '<a href="mailto:' . esc_attr( $customer->email ) . '">' . esc_html( $customer->email ) . '</a>';
                                echo '</td>';
                            echo '</tr>';
                        }
                
                        $has_data = true;
                    }
            
                    if ( $order_id && ( $order = wc_get_order( $order_id ) ) ) {
                        if( apply_filters( 'wcfm_allow_customer_billing_details', true ) ) {
                            echo '<tr>';
                                echo '<th><span for="appointment_date" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Address:', 'woocommerce-appointments' ) . '</strong></span></th>';
                                echo '<td>';
                                if ( $order->get_formatted_billing_address() ) {
                                    echo wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) );
                                } else {
                                    echo __( 'No billing address set.', 'woocommerce-appointments' );
                                }
                                echo '</td>';
                            echo '</tr>';
                        }
                        
                        if( apply_filters( 'wcfm_allow_view_customer_email', true ) ) {
                            echo '<tr>';
                                echo '<th><span for="appointment_date" class="wcfm-title wcfm_title" style="width:95%;"><strong>' . __( 'Billing Email:', 'wc-frontend-manager-ultimate' ) . '</strong></span></th>';
                                echo '<td>';
                                echo '<a href="mailto:' . esc_attr( $order->get_billing_email() ) . '">' . esc_html( $order->get_billing_email() ) . '</a>';
                                echo '</td>';
                            echo '</tr>';
                            echo '<tr>';                                    
                                echo '<th>' . __( 'Billing Phone:', 'wc-frontend-manager-ultimate' ) . '</th>';
                                echo '<td>';
                                echo esc_html( $order->get_billing_phone() );
                                echo '</td>';
                            echo '</tr>';
                        }
                        
                        if( apply_filters( 'wcfm_is_allow_order_details', true ) && $WCFM->wcfm_vendor_support->wcfm_is_order_for_vendor( $order_id ) ) {
                            echo '<tr class="view">';
                                echo '<th>&nbsp;</th>';
                                echo '<td>';
                                echo '<a class="button" target="_blank" href="' . get_wcfm_view_order_url( $order_id ) . '">' . __( 'View Order', 'wc-frontend-manager-ultimate' ) . '</a>';
                                echo '</td>';
                            echo '</tr>';
                        }
            
                        $has_data = true;
                    }
            
                    if ( ! $has_data ) {
                        echo '<tr>';
                            echo '<td colspan="2">' . __( 'N/A', 'woocommerce-appointments' ) . '</td>';
                        echo '</tr>';
                    }
                    
                    if ( $order ) { do_action( 'end_wcfm_appointments_details', $appointment_id, $order->get_order_number() ); }
                    
                    echo '</table>';
                    ?>
                </div>
            </div>
            <?php if ( $order ) { do_action( 'after_wcfm_appointments_details', $appointment_id, $order->get_order_number() ); } ?>
        </div>
    </div> -->