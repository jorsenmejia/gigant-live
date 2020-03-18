<?php
class admin_navViewWpf extends viewWpf {
	public function getBreadcrumbs() {
		$this->assign('breadcrumbsList', dispatcherWpf::applyFilters('mainBreadcrumbs', $this->getModule()->getBreadcrumbsList()));
		return parent::getContent('adminNavBreadcrumbs');
	}
}
