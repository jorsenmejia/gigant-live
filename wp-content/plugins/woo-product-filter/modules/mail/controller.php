<?php
class mailControllerWpf extends controllerWpf {
	public function testEmail() {
		$res = new responseWpf();
		$email = reqWpf::getVar('test_email', 'post');
		if($this->getModel()->testEmail($email)) {
			$res->addMessage(__('Now check your email inbox / spam folders for test mail.'));
		} else 
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function saveMailTestRes() {
		$res = new responseWpf();
		$result = (int) reqWpf::getVar('result', 'post');
		frameWpf::_()->getModule('options')->getModel()->save('mail_function_work', $result);
		$res->ajaxExec();
	}
	public function saveOptions() {
		$res = new responseWpf();
		$optsModel = frameWpf::_()->getModule('options')->getModel();
		$submitData = reqWpf::get('post');
		if($optsModel->saveGroup($submitData)) {
			$res->addMessage(__('Done', WPF_LANG_CODE));
		} else
			$res->pushError ($optsModel->getErrors());
		$res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			WPF_USERLEVELS => array(
				WPF_ADMIN => array('testEmail', 'saveMailTestRes', 'saveOptions')
			),
		);
	}
}
