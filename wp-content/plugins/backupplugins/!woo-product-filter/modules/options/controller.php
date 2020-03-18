<?php
class optionsControllerWpf extends controllerWpf {
	public function saveGroup() {
		$res = new responseWpf();
		if($this->getModel()->saveGroup(reqWpf::get('post'))) {
			$res->addMessage(__('Done', WPF_LANG_CODE));
		} else
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			WPF_USERLEVELS => array(
				WPF_ADMIN => array('saveGroup')
			),
		);
	}
}

