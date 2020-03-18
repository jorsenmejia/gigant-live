<?php
class dateWpf {
	static public function _($time = NULL) {
		if(is_null($time)) {
			$time = time();
		}
		return date(WPF_DATE_FORMAT_HIS, $time);
	}
}