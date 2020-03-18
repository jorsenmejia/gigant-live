<?php
class mailViewWpf extends viewWpf {
	public function getTabContent() {
		frameWpf::_()->getModule('templates')->loadJqueryUi();
		frameWpf::_()->addScript('admin.'. $this->getCode(), $this->getModule()->getModPath(). 'js/admin.'. $this->getCode(). '.js');
		
		$this->assign('options', frameWpf::_()->getModule('options')->getCatOpts( $this->getCode() ));
		$this->assign('testEmail', frameWpf::_()->getModule('options')->get('notify_email'));
		return parent::getContent('mailAdmin');
	}
}
