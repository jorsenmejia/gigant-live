<script>
jQuery(function(){
//my-custom-step is the class which we have assign to the wrapper DIV of our newly created element (See Above Example).
//#field_1, #field_2 and #field_3 are the unique ids of each input field
jQuery("#field_1, #field_2, #field_3").appendTo(".my-custom-step");
});
</script>
 <h1> Step Title</h1>
<div class="my-custom-step">
 <?php
 woocommerce_form_field('my_field_name', array(
 'type' => 'text',
 'required' => true,
 'class' => array('my-field-class form-row-wide'),
 'label' => __('Fill in this field'),
 'placeholder' => __('Enter something'),
 ), $checkout->get_value('my_field_name'));
 ?>
</div>
}