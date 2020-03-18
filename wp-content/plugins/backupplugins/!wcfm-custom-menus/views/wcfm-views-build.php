<?php
global $WCFM, $wp_query;

?>

<div class="collapse wcfm-collapse" id="wcfm_build_listing">
	
	<div class="wcfm-page-headig">
		<span class="fa fa-cubes"></span>
		<span class="wcfm-page-heading-text"><?php _e( 'Build', 'wcfm-custom-menus' ); ?></span>
		<?php do_action( 'wcfm_page_heading' ); ?>
	</div>
	<div class="wcfm-collapse-content">
		<div id="wcfm_page_load"></div>
		<?php do_action( 'before_wcfm_build' ); ?>
		
		<div class="wcfm-container wcfm-top-element-container">
			<h2><?php _e('Listing Manager', 'wcfm-custom-menus' ); ?></h2>
			<div class="wcfm-clearfix"></div>
	  </div>
	  <div class="wcfm-clearfix"></div><br />
		

		<div class="wcfm-container">
		    <select name="options" id="options" onchange="optionSels()">
                <option value="1">All</option>
                <option value="2">Listed</option>
                <option value="3">De-Listed</option>
                <option value="4">Processing</option>
            </select>
            [optionCategory]
			<div id="wcfm_build_listing_expander" class="wcfm-content">
			
				<!---- Add Content Here ----->
				
    			        [displaytolistingpage status="all"]
    			        <!--[list_products category="cook"]-->
    			        
				<div class="wcfm-clearfix"></div>
			</div>
			<div class="wcfm-clearfix"></div>
		</div>
	
		<div class="wcfm-clearfix"></div>
		<?php
		do_action( 'after_wcfm_build' );
		?>
	</div>
</div>