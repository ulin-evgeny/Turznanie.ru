<?php

class AdminController extends Controller {

	public function before() {
		parent::before();
		if (!Auth::instance()->logged_in('admin')) {
			$this->go_home();
		}
	}

}
