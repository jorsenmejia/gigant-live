<?php
class pagesViewWpf extends viewWpf {
    public function displayDeactivatePage() {
        $this->assign('GET', reqWpf::get('get'));
        $this->assign('POST', reqWpf::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqWpf::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqWpf::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}

