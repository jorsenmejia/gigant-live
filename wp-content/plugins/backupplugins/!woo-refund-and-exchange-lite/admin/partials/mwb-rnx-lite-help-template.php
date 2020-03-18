<?php
/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_POST['ced_rnx_send_suggestion'] ) ) {
	if(isset(  $_REQUEST['ced-rnx-nonce'] ) ){

		if ( wp_verify_nonce( sanitize_key ($_REQUEST['ced-rnx-nonce'] ), 'ced-rnx-nonce' ) ) {
			$ced_rnx_mail_subject = isset( $_POST['ced_rnx_mail_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_rnx_mail_subject'] ) ) : 'WooCommerce Refund & Exchange Client Needed Help';
			$ced_rnx_mail_content = isset( $_POST['ced_rnx_mail_content'] ) ? sanitize_text_field( wp_unslash( $_POST['ced_rnx_mail_subject'] ) ) : 'This is default messege please contact me fast so i will feel free from this stress.';

			$ced_rnx_admin_email = get_option( 'admin_email' );

			$statuses = wp_mail( 'support@makewebbetter.com', $ced_rnx_mail_subject, $ced_rnx_mail_content );
			if ( $statuses ) {
				$messege = __( 'Your request is submitted successfully. Our team will respond as soon as possible.', 'woocommerce-refund-and-exchange-lite' );
				$class = 'ced_rnx_mail_success_messege';
			} else {
				$messege = __( 'Your request is not submitted. Please try again.', 'woocommerce-refund-and-exchange-lite' );
				$class = 'ced_rnx_mail_unsuccess_messege';
			}
		}
	}
}
?>
<div class="ced_rnx_help_wrapper">
	<ul class="ced_rnx_help-link-wrap">
		<li>
			<a class="ced_rnx_help-link" href="http://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite/" target="_blank"><?php esc_html_e( 'Documentation', 'woocommerce-refund-and-exchange-lite' ); ?></a>
		</li>
		<li>
			<a class="ced_rnx_help-link" href="https://makewebbetter.freshdesk.com/solution/folders/26000054491" target="_blank"><?php esc_html_e( 'FAQ', 'woocommerce-refund-and-exchange-lite' ); ?></a>
		</li>
	</ul>
	<?php
	if ( isset( $messege ) ) {
		?>
		<div class="<?php esc_html_e( $class); ?>"> <?php esc_html__( $messege ); ?></div>
		<?php
	}
	?>
	<form method="POST" action="">
		<div class="ced-rnx-suggestion">
			<h2><?php esc_html_e( 'Suggestion or Query', 'woocommerce-refund-and-exchange-lite' ); ?></h2>
			<div class="ced_rnx_help_input-wrap">
				<label><?php esc_html_e( 'Enter Suggestion or Query title here', 'woocommerce-refund-and-exchange-lite' ); ?></label>
				<div class="ced_rnx_help_input">
					<input class="ced_rnx_help_form-control text-field" type="text" name='ced_rnx_mail_subject'>
				</div>
			</div>
			<div class="ced_rnx_help_input-wrap">
				<label><?php esc_html_e( 'Enter Suggestion or Query detail here', 'woocommerce-refund-and-exchange-lite' ); ?></label>
				<div class="ced_rnx_help_input">
					<textarea  class="ced_rnx_help_form-control" name="ced_rnx_mail_content"></textarea>
					<input type="hidden" name="ced-rnx-nonce" name="ced-rnx-nonce" value="<?php wp_create_nonce( 'ced-rnx-nonce' ); ?>">
				</div>
			</div>
			<div class="ced_rnx_hekp-sned-suggetion-button-wrap">
				<input type="submit" name="ced_rnx_send_suggestion" value="<?php esc_html_e( 'Send Suggestion', 'woocommerce-refund-and-exchange-lite' ); ?>" class="button-primary ced_rnx_hekp-sned-suggetion-button">
			</div>
		</div>
	</form>
</div>
