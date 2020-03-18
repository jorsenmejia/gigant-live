<div>
	<table>
		<tr>
			<td>
				<label for="return_form_editor"><?php esc_html_e( 'Enable', 'woocommerce-refund-and-exchange-lite' ); ?></label>
			</td>
			<td>
				<input type="checkbox" name="mwb__refund_rules_editor_enable" id="mwb_wrma_refund_rules_editor_enable">
			</td>
		</tr>
		<tr>
			<div id="mwb_rnx_return_form_editor'">
			<td><label for="return_form_editor"><?php esc_html_e( 'Custom Rules ', 'woocommerce-refund-and-exchange-lite' ); ?></label></td>
			<td>
				<?

				$content = stripslashes(get_option('ced_rnx_return_editor', false));
				$editor_id = 'ced_rnx_return_editor';
				$settings = array(
					'media_buttons'    => true,
					'drag_drop_upload' => true,
					'dfw'              => true,
					'teeny'            => true,
					'editor_height'    => 200,
					'editor_class'	   => '',
					'textarea_name'    => "ced_rnx_return_editor"
				);
				wp_editor( $content, $editor_id, $settings );
				?>
			</td>
		</div>
		</tr>
		<tr>
			<td><input type="submit" class="button-primary" name="mwb_rnx_submit_refund_rules" value="Save changes"></td>
		</tr>
	</table>
</div>


