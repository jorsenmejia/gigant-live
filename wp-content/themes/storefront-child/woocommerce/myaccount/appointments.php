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