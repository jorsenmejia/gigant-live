<?php
global $WCFM, $WCFMu;
?>
<form id="wcfm_generate_voucher_code_form">
	<table>
		<tbody>
			<tr>
				<td class="wcfm_quick_edit_form_label"><?php _e( 'Number of Voucher Codes', 'woovoucher' ); ?></td>
				<td><input type="number" name="woo-vou-no-of-voucher" value="" /></td>
			</tr>
			<tr>
				<td class="wcfm_quick_edit_form_label"><?php _e( 'Prefix', 'woovoucher' ); ?></td>
				<td><input type="text" name="woo-vou-code-prefix" value="" placeholder="WPWeb" /></td>
			</tr>
			<tr>
				<td class="wcfm_quick_edit_form_label"><?php _e( 'Separator', 'woovoucher' ); ?></td>
				<td><input type="text" name="woo-vou-code-seperator" value="" placeholder="-" /></td>
			</tr>
			<tr>
				<td class="wcfm_quick_edit_form_label"><?php _e( 'Pattern', 'woovoucher' ); ?></td>
				<td>
				  <input type="text" name="woo-vou-code-pattern" value="" placeholder="LLDD" />
				  <span class="woo-vou-generate-pattern-example"><a href="http://wpweb.co.in/documents/woocommerce-pdf-vouchers/pdf-vouchers-setup-docs/#wpweb_generate_code_example" target="_blank"><?php _e('View Example', 'woovoucher'); ?></a></span><br />
				  <p class="popup_description">
				    <strong><?php _e( 'Prefix', 'woovoucher' ); ?></strong> - <?php _e( 'Prefix Text to appear before the code.', 'woovoucher' ); ?><br />
						<strong><?php _e( 'Separator', 'woovoucher' ); ?></strong> - <?php _e( 'Separator  symbol which appear between prefix and code.', 'woovoucher' ); ?><br />
						<strong><?php _e( 'Pattern', 'Pattern' ); ?></strong> - <?php _e( 'Unique pattern for code. You can define a pattern using following characters. ', 'woovoucher' ); ?>
						<strong><?php _e('L', 'woovoucher'); ?></strong> - <?php _e('Uppercase Letter, ', 'woovoucher'); ?><strong><?php _e('l', 'woovoucher'); ?></strong> - <?php _e('Lowercase Letter, ', 'woovoucher'); ?><strong><?php _e('D', 'woovoucher'); ?></strong> - <?php _e('Digit.', 'woovoucher') ?><br />
				  </p>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="wcfm-message" tabindex="-1"></div>
	<input type="button" class="wcfm_generate_voucher_code wcfm_submit_button" id="wcfm_generate_voucher_code_button" value="<?php _e( 'Generate Codes', 'woovoucher' ); ?>" />
</form>