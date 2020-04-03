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

  <?php do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

  <?php foreach ( $tables as $table_id => $table ) : ?>

    <!-- <h2><?php echo esc_html( $table['header'] ); ?></h2> -->

        <table class="shop_table my_account_appointments <?php echo esc_html( $table_id ) . '_appointments'; ?>">
        <thead>
          <tr class="appointment-client">
            <th>Status</th>
            <th>Listing</th>
            <th>Date</th> 
            <th>Time</th>                               
            <th>Pro Name</th>           
            <th>Notes</th>           
            <th>Price</th>
            <th>Actions</th>  
          </tr>            
        </thead>
        <tbody>
          <?php foreach ( $table['appointments'] as $appointment ) : ?>
            <?php $count++; ?>
          <tr class="appointment-row<?php if( $rowcount == $lastcount){echo " last-row";$rowcount = 0;}$count++;?>" id="<?php echo "appointment-page-".$count;?>">
            <!-- Status -->
            <td>
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
            <!-- Listing -->
            <td>
              <?php if ( $appointment->get_product() && $appointment->get_product()->is_type( 'appointment' ) ) : ?>
                <a href="<?php echo esc_url( get_permalink( $appointment->get_product_id() ) ); ?>">
                  <?php echo esc_html( $appointment->get_product_name() ); ?>
                </a>
              <?php endif; ?>
            </td>
            <!-- Date -->
            <td>
              <?php 
                $date =  new DateTime(esc_attr($appointment->get_start_date())); 
                echo $date->format('F d, Y');
                ?>
            </td>
            <!-- Time -->
            <td>
              <?php             
                $fromstart= new DateTime(esc_attr($appointment->get_start_date())); 
                $tostart= new DateTime(esc_attr($appointment->get_end_date())); 
                echo $fromstart->format('h:i a').' - '.$tostart->format('h:i a'); 
                ?>
            </td>
            <!-- Pro Name -->
            <td>
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
            <!-- Notes -->
            <td>
              <span class="customer-note"><?php echo $appointment->get_order()->customer_note; ?></span>
            </td>
            <!-- Price -->
            <td>
              <?php echo '₱',$appointment->get_order()->total; ?>
            </td>
            <!-- More Action -->
            <td>
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
          <?php if ( $appointment->get_order() && $appointment->get_status() === 'paid') : ?>
                                    <?php
                                    global $post;
                                    $video_link = get_post_meta( $appointment->get_id(), 'video_link', true );
                                    $product_id = get_post_meta( $appointment->get_id(), '_appointment_product_id', true );
                                    $product = get_post( $product_id ); 
                                    $slug = $product->post_name;
                                    $video_link = $video_link.'&slug='.$slug;
                                    if(metadata_exists('post', $appointment->get_id(), 'video_link')){
                                   ?>
                                      <a href="<?php echo $video_link;?>"><button>Access Now</button></a>
                                  <?php } endif;?>
            </td>
          </tr>
        </tbody>
        <?php endforeach; ?>
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

/**/

  tr.appointment-client {
    color: black!Important;
    font-family: Helvetica;
}

tbody.appointment-body {
    color: black!important;
    font-family: Helvetica;
}

/**/

 .modal-dialog.modal-sm {
    width: 50%!Important;
    margin-top: 280px!Important;
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
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
</head>
<body>

</body>
</html>