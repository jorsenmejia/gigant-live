<?php
class mailModelWpf extends modelWpf {
	public function testEmail($email) {
		$email = trim($email);
		if(!empty($email)) {
			if($this->getModule()->send($email, 
				__('Test email functionality', WPF_LANG_CODE), 
				sprintf(__('This is a test email for testing email functionality on your site, %s.', WPF_LANG_CODE), WPF_SITE_URL))
			) {
				return true;
			} else {
				$this->pushError( $this->getModule()->getMailErrors() );
			}
		} else
			$this->pushError (__('Empty email address', WPF_LANG_CODE), 'test_email');
		return false;
	}
}