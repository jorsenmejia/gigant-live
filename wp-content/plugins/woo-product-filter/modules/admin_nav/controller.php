<?php
class admin_navControllerWpf extends controllerWpf {
	public function getPermissions() {
		return array(
			WPF_USERLEVELS => array(
				WPF_ADMIN => array()
			),
		);
	}
}